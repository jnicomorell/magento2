<?php
declare(strict_types=1);

namespace Ceg\Backend\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\State;
use Magento\Framework\HTTP\Client\Curl;
use Ceg\Core\Logger\Logger as CegLogger;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Config extends AbstractHelper
{

    const CONFIG_PATH = 'ceg_backend';

    const CONFIG_API_URL =  'auth0_settings/management_api/domain';
    const CONFIG_API_CLIENT_ID = 'auth0_settings/management_api/client_id';
    const CONFIG_API_CLIENT_SECRET = 'auth0_settings/management_api/client_secret';
    const CONFIG_API_AUDIENCE = 'auth0_settings/management_api/audience';
    const CONFIG_API_SCOPE = 'auth0_settings/general/scope';

    const CONFIG_COMPANY_EMAIL = self::CONFIG_PATH.'/staff_config/staff_company_email';
    const CONFIG_COMPANY_ROLE = self::CONFIG_PATH.'/staff_config/staff_company_role';
    const CONFIG_BACKEND_ROLE = self::CONFIG_PATH.'/staff_config/staff_backend_role';

    const URL_BACKEND_LOGIN = 'admin/auth/auth0/';
    /**
     * @var Json
     */
    protected $json;
    /**
     * @var State
     */
    protected $appMode;
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var $accessToken
     */
    protected $accessToken;

    /**
     * @var CegLogger
     */
    protected $logger;

    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param Json               $json
     * @param State              $appMode
     * @param Curl               $curl
     * @param CegLogger          $logger
     * @param UrlInterface       $urlInterface
     * @param EncryptorInterface $encryptor
     * @param Context            $context
     */
    public function __construct(
        Json $json,
        State $appMode,
        Curl $curl,
        CegLogger $logger,
        UrlInterface $urlInterface,
        EncryptorInterface $encryptor,
        Context $context
    ) {
        $this->json = $json;
        $this->appMode = $appMode;
        $this->curl = $curl;
        $this->logger = $logger;
        $this->urlInterface = $urlInterface;
        $this->encryptor = $encryptor;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->urlInterface->getUrl(self::URL_BACKEND_LOGIN, []);
    }

    /**
     * @return bool
     */
    public function inDevMode()
    {
        $mode = $this->appMode->getMode();
        return ($mode === 'developer') ? true : false;
    }

    /**
     * @return mixed
     */
    public function getTokenUrl()
    {
        return 'https://'.$this->scopeConfig->getValue(self::CONFIG_API_URL).'/oauth/token';
    }

    /**
     * @return string
     */
    public function getInfoUrl()
    {
        return 'https://'.$this->scopeConfig->getValue(self::CONFIG_API_URL).'/userinfo';
    }

    /**
     * @return mixed
     */
    public function getClientId()
    {
        return $this->scopeConfig->getValue(self::CONFIG_API_CLIENT_ID);
    }

    /**
     * @return mixed
     */
    public function getClientSecret()
    {
        return $this->scopeConfig->getValue(self::CONFIG_API_CLIENT_SECRET);
    }

    /**
     * @return mixed
     */
    public function getAudience()
    {
        return $this->scopeConfig->getValue(self::CONFIG_API_AUDIENCE);
    }

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->scopeConfig->getValue(self::CONFIG_API_SCOPE);
    }

    /**
     * @return mixed
     */
    public function getCompanyEmail()
    {
        return $this->scopeConfig->getValue(self::CONFIG_COMPANY_EMAIL);
    }

    /**
     * @return mixed
     */
    public function getCompanyRole()
    {
        return $this->scopeConfig->getValue(self::CONFIG_COMPANY_ROLE);
    }

    /**
     * @return mixed
     */
    public function getBackendRole()
    {
        return $this->scopeConfig->getValue(self::CONFIG_BACKEND_ROLE);
    }

    /**
     * @param $user
     * @param $password
     *
     * @return mixed|null
     */
    protected function generateToken($user, $password)
    {
            $url = $this->getTokenUrl();
            $grantType = 'password';
            $clientId = $this->getClientId();
            $clientSecret = $this->getClientSecret();
            $audience = $this->getAudience();
            $scope = $this->getScope();

            $body = [
                'grant_type' => $grantType,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'audience' => $audience,
                'username' => $user,
                'password' => $password,
                'scope' => $scope
            ];

            $this->curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
            try {
                $this->curl->post($url, $body);
            } catch (Exception $exception) {
                $this->logger->info($exception);
            }

            if ($this->curl->getStatus() != 200) {
                $this->logger->info('Error al intentar obtener el token');
                return $this->accessToken = null;
            }
            $response = $this->json->unserialize($this->curl->getBody());
            if (!is_array($response) || empty($response['access_token'])) {
                $this->logger->info('Error al intentar obtener el token '. json_encode($response));
                return $this->accessToken = null;
            }
            return $this->accessToken = $response['access_token'];
    }

    /**
     * @param $user
     * @param $password
     *
     * @return array|false|void
     */
    public function getUserInfo($user, $password)
    {
        $this->accessToken = $this->generateToken($user, $password);

        if (!isset($this->accessToken)) {
            return false;
        }

        $url = $this->getInfoUrl();
        $this->curl->addHeader('Authorization', 'Bearer '. $this->accessToken);
        $this->curl->get($url);
        $response = json_decode($this->curl->getBody(), true);

        if (isset($response)) {

            $nickname = $response['nickname'] ?? null;
            $userMetaData = json_decode(json_encode($response['https://ceg/user_metadata']), true) ?? null;
            $userRole = json_decode(json_encode($response['https://ceg/roles']), true)[0] ?? null;

            return [
                'nickname' => $nickname,
                'metadata' => $userMetaData,
                'role' => $userRole
            ];
        }
    }

    /**
     * Hash customer password
     *
     * @param string $password
     * @param bool|int|string $salt
     * @return string
     */
    public function hashPassword($password, $salt)
    {
        return $this->encryptor->getHash($password, $salt);
    }
}
