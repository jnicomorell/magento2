<?php
declare(strict_types=1);

namespace Ceg\OrdersIntegration\Model\Integration;

use Ceg\OrdersIntegration\Helper\Data as Helper;
use Ceg\OrdersIntegration\Helper\DataFactory as HelperFactory;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;

class Provider
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var string
     */
    protected $currentToken = '';

    /**
     * @param HelperFactory $helperFactory
     * @param CurlFactory $curlFactory
     * @param Json $json
     */
    public function __construct(
        HelperFactory $helperFactory,
        CurlFactory $curlFactory,
        Json $json
    ) {
        $this->helper = $helperFactory->create();
        $this->curlFactory = $curlFactory;
        $this->json = $json;
    }

    /**
     * @param $apiUrl
     * @param $data
     * @param $storeId
     * @return Provider
     * @throws AuthorizationException|LocalizedException
     */
    public function send($apiUrl, $data, $storeId)
    {
        $curl = $this->curlFactory->create();
        $curl->addHeader('accept', 'application/json');
        $curl->addHeader('Authorization', 'Bearer ' . $this->getToken($storeId));
        $curl->addHeader('Content-Type', 'application/json');
        $curl->setTimeout($this->helper->getTimeout());
        $bodyJson = $this->json->serialize($data);
        $curl->post($apiUrl, $bodyJson);
        $response = $this->json->unserialize($curl->getBody());

        if (!is_array($response) || empty($response)) {
            throw new LocalizedException(__('Could not get data from Orders API.'));
        }

        return $this;
    }

    /**
     * @param $websiteId
     *
     * @return mixed|string
     * @throws AuthorizationException
     */
    public function getToken($websiteId)
    {
        if (empty($this->currentToken)) {
            $tokenUrl = $this->helper->getTokenUrl($websiteId);
            $tokenClientId = $this->helper->getTokenClientId($websiteId);
            $tokenClientSecret = $this->helper->getTokenClientSecret($websiteId);
            $tokenAudience = $this->helper->getTokenAudience($websiteId);
            $tokenGrantType = 'client_credentials';

            $body = [
                'grant_type' => $tokenGrantType,
                'client_id' => $tokenClientId,
                'client_secret' => $tokenClientSecret,
                'audience' => $tokenAudience
            ];

            $curl = $this->curlFactory->create();
            $curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
            $curl->post($tokenUrl, $body);

            if ($curl->getStatus() != 200) {
                throw new AuthorizationException(__('Could not login to Auth0 API. Token generation error'));
            }
            $response = $this->json->unserialize($curl->getBody());
            if (!is_array($response) || empty($response['access_token'])) {
                throw new AuthorizationException(__('Could not login to Auth0 API. Token response bad format'));
            }
            $this->currentToken = $response['access_token'];
        }
        return $this->currentToken;
    }
}
