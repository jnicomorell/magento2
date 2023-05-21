<?php
declare(strict_types=1);

namespace Ceg\Auth0Integration\Helper;

use Auth0\SDK\API\Management;
use Auth0\SDK\Auth0;
use Auth0\SDK\Exception\ApiException;
use Auth0\SDK\JWTVerifier;
use Auth0\SDK\Exception\CoreException;
use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Ceg\Auth0Integration\Exception\EmailFormatException;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{
    /**
     * @var Config
     */
    protected $configData;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Auth0|null
     */
    protected $auth0 = null;

    /**
     * @var JWTVerifier|null
     */
    protected $jwt_verifier = null;

    /**
     * @var array|null
     */
    protected $decoded_token = null;

    /**
     * @var ClientFactory
     */
    protected $httpClientFactory;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Data constructor.
     * @param Context $context
     * @param SessionManagerInterface $sessionManager
     * @param StoreManagerInterface $storeManager
     * @param Config $configData
     * @param ClientFactory $httpClientFactory
     * @param Curl $curl
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $sessionManager,
        StoreManagerInterface $storeManager,
        Config $configData,
        ClientFactory $httpClientFactory,
        Curl $curl,
        WebsiteRepositoryInterface $websiteRepository
    ) {
        parent::__construct($context);

        $this->configData = $configData;
        $this->sessionManager = $sessionManager;
        $this->storeManager = $storeManager;
        $this->httpClientFactory = $httpClientFactory;
        $this->curl = $curl;
        $this->websiteRepository = $websiteRepository;
        $this->urlBuilder = $context->getUrlBuilder();
    }

    /**
     * @return void
     * @throws CoreException
     */
    protected function initAuth0()
    {
        // @TODO cambiar por uso de DI
        $this->auth0 = new Auth0($this->getAuth0Config());
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function logoutAuth0Url(): string
    {
        $url = 'https://'.$this->configData->getLogoutUrl();
        $url .= "?client_id=" . $this->configData->getClientId();
        $url .= "&returnTo=" . $this->storeManager->getStore()->getUrl('customer/account/logoutSuccess');

        return $url;
    }

    /**
     * @return array
     */
    private function getAuth0Config(): array
    {
        $redirectUri = $this->urlBuilder->getUrl($this->configData->getRedirectUri());
        return [
            'domain' => $this->configData->getDomain(),
            'client_id' => $this->configData->getClientId(),
            'client_secret' => $this->configData->getClientSecret(),
            'redirect_uri' => $redirectUri,
            'audience' => $this->configData->getAudience(),
            'scope' => "email " . $this->configData->getScope(),
            'persist_id_token' => $this->configData->getPersistIdToken(),
            'persist_access_token' => $this->configData->getPersistAccessToken(),
            'persist_refresh_token' => $this->configData->getPersistRefreshToken(),
            'grant_type' => 'refresh_token'
        ];
    }

    /**
     * @return array[]
     * @throws Exception
     */
    private function getJWTVerifierConfig(): array
    {
        try {
            $tokenAlg = $this->configData->getTokenAlgorithm();
            $config = [
                // Array of allowed algorithms; never pass more than what is expected.
                'supported_algs' => [ $tokenAlg ],
                // Array of allowed "aud" values.
                'valid_audiences' => [ $this->configData->getAudience() ],
            ];


            if ($tokenAlg === Config::RS256) {
                // RS256 tokens require a valid issuer.
                $config['authorized_iss'] = [ "https://" . $this->configData->getDomain() . "/" ];
            }
            if ($tokenAlg === Config::HS256) {
                // HS256 tokens require the Client Secret to decode.
                $config['client_secret'] = $this->configData->getClientSecret();
                $config['secret_base64_encoded'] = false;
            }
        } catch (Exception $exception) {
            throw new LocalizedException(__("Token algorithm is wrong configured.", $exception->getMessage()));
        }
        return $config;
    }

    /**
     * @return mixed
     */
    public function getNewCustomerGroupId()
    {
        // TODO: * Temporal option: Group will be obteined from BITRIX service
        return $this->configData->getNewCustomerGroupId();
    }

    /**
     * @return Auth0
     * @throws CoreException
     */
    public function getAuth0(): Auth0
    {
        if ($this->auth0 === null) {
            $this->initAuth0();
        }
        return $this->auth0;
    }

    /**
     * @param $auth0
     * @return mixed
     */
    public function setAuth0($auth0)
    {
        return $this->auth0 = $auth0;
    }

    /**
     * @return mixed
     * @throws CoreException
     * @throws ApiException
     */
    public function getAccessToken()
    {
        return $this->getAuth0()->getAccessToken();
    }

    /**
     * @return mixed
     * @throws ApiException
     * @throws CoreException
     */
    public function getUser()
    {
        return $this->getAuth0()->getUser();
    }

    /**
     * @return JWTVerifier|null
     * @throws Exception
     */
    public function getJWTVerifier(): JWTVerifier
    {
        if ($this->jwt_verifier === null) {
            $this->jwt_verifier = new JWTVerifier($this->getJWTVerifierConfig());
        }
        return $this->jwt_verifier;
    }

    /**
     * @return array|null
     * @throws Exception
     */
    public function getDecodedToken(): array
    {
        if (!$this->decoded_token) {
            $this->decoded_token = $this->getJWTVerifier()->verifyAndDecode($this->getAuth0()->getAccessToken());
        }
        return $this->decoded_token;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCallbackUrl(): string
    {
        return $this->sessionManager->getCallbackUrl() ?? $this->getBaseUrl();
    }

    /**
     * @param $callback_url

     */
    public function setCallbackUrl($callback_url)
    {
        return $this->sessionManager->setCallbackUrl($callback_url);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        // @todo $this->isModuleOutputEnabled()
        return $this->configData->getActive();
    }

    /**
     * @return bool
     */
    public function isRestricted()
    {
        return (bool)$this->isEnabled() && (bool)$this->configData->getRestricted();
    }

    /**
     * @return bool|string
     */
    public function getManagementToken()
    {
        $this->curl->post(
            'https://'.$this->configData->getDomain().'/oauth/token',
            $this->setManagementConfig()
        );
        $response = $this->curl->getBody();

        return (!empty($response)) ? json_decode($response) : false;
    }

    /**
     * @return array
     */
    public function setManagementConfig()
    {
        return [
            'client_id' => $this->configData->getManagementClientId(),
            'client_secret' => $this->configData->getManagementClientSecret(),
            'audience' => $this->configData->getManagementAudience(),
            'grant_type' => 'client_credentials'
        ];
    }

    /**
     * @param  null|array
     * @return false|mixed
     * @throws ApiException
     * @throws CoreException
     */
    public function getUserMetadata($user = [])
    {
        $managementToken = $this->getManagementToken();

        if ($managementToken) {
            if (isset($managementToken->access_token)) {
                $managementApi = new Management($managementToken->access_token, $this->configData->getDomain());

                $currentUserId = $this->getUser()['sub'];
                $user = $managementApi->users()->get($currentUserId);
            }
        }

        if (!empty($user['user_metadata'])) {
            return $user['user_metadata'];
        } elseif (!empty($user['https://ceg/user_metadata'])) {
            // this solves the stdClass to array issue
            return json_decode(json_encode($user['https://ceg/user_metadata']), true);
        }

        return false;
    }

    /**
     * @param $email
     * @return bool
     * @throws EmailFormatException
     */
    public function checkEmailOrFail($email): bool
    {
        if (!isset($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new EmailFormatException(__("The e-mail is empty or invalid."));
        }
        return true;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
    }

    /**
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getStoreId();
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * @return false|string
     */
    public function getRemoteAddress()
    {
        return $this->_remoteAddress->getRemoteAddress();
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->_logger;
    }

    /**
     * @param string $code
     * @return int|null
     */
    public function getWebsiteIdByCode(string $code): ?int
    {
        $websiteId = 1;
        try {
            $website = $this->websiteRepository->get($code);

            return (int) $website->getId();

        } catch (Exception $exception) {
            $this->_logger->error($exception->getMessage());
        }

        return $websiteId;
    }

    /**
     * Use just for debug
     */
    public function logAuth0Info()
    {
        // @codingStandardsIgnoreStart
        $this->getLogger()->info(PHP_EOL .
            "Current Store: " . $this->getStore()->getCode() . PHP_EOL .
            "Access Token: " . $this->getAccessToken() . PHP_EOL .
            "User: " . print_r($this->getUser(), true) . PHP_EOL .
            "User Metadata: " . print_r($this->getUserMetadata(), true));
        // @codingStandardsIgnoreEnd
    }

    public function decodedIdToken($idToken): array
    {
        $value = $this->getAuth0()->decodeIdToken($idToken);
        $metadata = [];

        if (!empty($value['user_metadata'])) {
            $metadata = $value['user_metadata'];
        } elseif (!empty($value['https://ceg/user_metadata'])) {
            $metadata= json_decode(json_encode($value['https://ceg/user_metadata']), true);
        }

        $value['user_metadata'] = $metadata;
        return $value;
    }
}
