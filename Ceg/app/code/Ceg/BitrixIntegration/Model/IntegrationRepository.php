<?php
declare(strict_types=1);

namespace Ceg\BitrixIntegration\Model;

use Ceg\BitrixIntegration\Api\IntegrationRepositoryInterface;
use Ceg\BitrixIntegration\Api\QueueRepositoryInterfaceFactory;
use Ceg\BitrixIntegration\Helper\DataFactory as HelperFactory;
use Ceg\BitrixIntegration\Api\Data\Integration\ResultInterfaceFactory;
use Ceg\BitrixIntegration\Api\Data\Integration\ResultInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Ceg\Core\Model\CurlFactory;

class IntegrationRepository implements IntegrationRepositoryInterface
{
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';

    /**
     * @var QueueRepositoryInterfaceFactory
     */
    protected $queueRepoFactory;

    /**
     * @var ResultInterfaceFactory
     */
    protected $resultFactory;

    /**
     * @var HelperFactory
     */
    protected $helperFactory;

    /**
     * @var Json
     */
    protected Json $json;

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var string
     */
    protected $currentToken = '';

    /**
     * @param QueueRepositoryInterfaceFactory $queueRepoFactory
     * @param ResultInterfaceFactory          $resultFactory
     * @param HelperFactory                   $helperFactory
     * @param CurlFactory                     $curlFactory
     * @param Json                            $json
     */
    public function __construct(
        QueueRepositoryInterfaceFactory $queueRepoFactory,
        ResultInterfaceFactory $resultFactory,
        HelperFactory $helperFactory,
        CurlFactory $curlFactory,
        Json $json
    ) {
        $this->queueRepoFactory = $queueRepoFactory;
        $this->resultFactory = $resultFactory;
        $this->helperFactory = $helperFactory;
        $this->curlFactory = $curlFactory;
        $this->json = $json;
    }

    /**
     * {@inheritdoc}
     */
    public function sendData($element)
    {
        $queueRepository = $this->queueRepoFactory->create();
        $queue = $queueRepository->getByTypeElementId($element->getType(), $element->getId());
        $queue->setAction($this->getAction($queue));
        return $this->process($element, $queue);
    }

    /**
     * {@inheritdoc}
     */
    public function resendData($element, $queue)
    {
        $queue->setAction($this->getAction($queue));
        return $this->process($element, $queue);
    }

    /**
     * @param $element
     * @param $action
     * @return ResultInterface
     */
    public function process($element, $queue)
    {
        $action = $queue->getAction();
        $result = $this->resultFactory->create();
        $helper = $this->helperFactory->create();
        if ($element->isEnabled()) {
            try {
                $response = $this->sendRequest($action, $element, $helper->getTimeout(), $result);
                $response = $this->json->unserialize($response);
                $element->validateResponse($response);
                $result->setSuccessStatus();
            } catch (\Exception $exception) {
                $result->setErrorStatus();
                $result->addMessage($exception->getMessage());
            }
            $serializedJson = $this->json->serialize($element->getRequestData());
            $this->saveQueue($result, $queue, $serializedJson);
        }

        return $result;
    }

    public function sendRequest($action, $element, $timeout, $result)
    {
        $url = $element->getUrl();
        $curl = $this->curlFactory->create();
        $curl->setTimeout($timeout);
        $curl->addHeader('accept', 'application/json');
        $curl->addHeader('Content-Type', 'application/json');

        if ($element->useAccessToken()) {
            $token = $this->getToken($element->getWebsiteId());
            $curl->addHeader('Authorization', 'Bearer ' . $token);
        }

        $bodyJson = $this->json->serialize($element->getRequestData());
        $response = '';
        switch ($action) {
            case self::ACTION_CREATE:
                $curl->post($url, $bodyJson);
                $status = $curl->getStatus();
                $response = $curl->getBody();

                if ($status == 422) {
                    $message = __('%1 Id already exists in Bitrix db: Send Update Action', ucfirst(strtolower($element->getType())));
                    $result->addMessage($message);
                    $response = $this->sendRequest(self::ACTION_UPDATE, $element, $timeout, $result, $url);
                    $status = 201;
                }
                if ($status != 201) {
                    $message = __('Could not Create data on Bitrix API. Curl status error: %1', $curl->getBody());
                    throw new AuthorizationException($message);
                }
                break;

            case self::ACTION_UPDATE:
                $curl->patch($url, $bodyJson);
                $status = $curl->getStatus();
                $response = $curl->getBody();

                if ($status != 200) {
                    $message = __('Could not Update data on Bitrix API. Curl status error: %1', $curl->getBody());
                    throw new AuthorizationException($message);
                }
                break;
        }
        return $response;
    }

    public function getToken($websiteId)
    {
        if (empty($this->currentToken)) {
            $helper = $this->helperFactory->create();
            $timeout = $helper->getTimeout();
            $tokenUrl = $helper->getTokenUrl($websiteId);
            $tokenClientId = $helper->getTokenClientId($websiteId);
            $tokenClientSecret = $helper->getTokenClientSecret($websiteId);
            $tokenAudience = $helper->getTokenAudience($websiteId);
            $tokenGrantType = 'password';
            $tokenAuth0Username = $helper->getTokenAuth0User($websiteId);
            $tokenAuth0Password = $helper->getTokenAuth0Password($websiteId);
            $tokenScope = 'openid';
            $tokenConnection = 'Username-Password-Authentication';

            $bodyJson = $this->json->serialize([
                'client_id' => $tokenClientId,
                'client_secret' => $tokenClientSecret,
                'audience' => $tokenAudience,
                'grant_type' => $tokenGrantType,
                'username' => $tokenAuth0Username,
                'password' => $tokenAuth0Password,
                'scope' => $tokenScope,
                'connection' => $tokenConnection
            ]);

            $curl = $this->curlFactory->create();
            $curl->addHeader('accept', 'application/json');
            $curl->addHeader('Content-Type', 'application/json');
            $curl->addHeader('cache-control', 'no-cache');
            $curl->setTimeout($timeout);
            $curl->post($tokenUrl, $bodyJson);

            if ($curl->getStatus() != 200) {
                throw new AuthorizationException(__('Could not login to Bitrix API. Token generation error'));
            }
            $response = $this->json->unserialize($curl->getBody());
            if (!is_array($response) || empty($response['access_token'])) {
                throw new AuthorizationException(__('Could not login to Bitrix API. Token response bad format'));
            }
            $this->currentToken = $response['access_token'];
        }
        return $this->currentToken;
    }

    public function saveQueue($result, $queue, $json)
    {
        $queueRepository = $this->queueRepoFactory->create();

        $message = $result->getMessage();
        $message.= $json;
        $status = Queue::STATUS_SENDED;
        if ($result->isError()) {
            $status = Queue::STATUS_PENDING;
        }

        $queue->setStatus($status);
        $queue->setMessage($message);
        $queueRepository->save($queue);
    }

    private function getAction($queue)
    {
        $action = self::ACTION_CREATE;
        if ($queue->getId()) {
            if ($queue->getStatus() == Queue::STATUS_PENDING) {
                $action = $queue->getAction();
            }
            if ($queue->getStatus() == Queue::STATUS_SENDED) {
                $action = self::ACTION_UPDATE;
            }
        }
        return $action;
    }
}
