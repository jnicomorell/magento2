<?php

namespace Ceg\Analytics\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Ceg\Analytics\Helper\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\Client\Curl;

class AnalyticsData implements ArgumentInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var RequestInterface
     */
    private $httpRequest;

    /**
     * @var Curl
     */
    private $curl;

    public function __construct(
        Config $config,
        UrlInterface $urlInterface,
        PriceCurrencyInterface $priceCurrency,
        RequestInterface $httpRequest,
        Curl $curl
    ) {
        $this->config = $config;
        $this->urlInterface = $urlInterface;
        $this->priceCurrency = $priceCurrency;  
        $this->httpRequest = $httpRequest;  
        $this->curl = $curl;
    }

    public function getConfig($code) {
        return $this->config->getConfig($code);
    }

    public function getConfigSuccess() {
        return $this->config->getConfigSuccess();
    }

    public function getEventsConfig(){
        return json_encode($this->config->getEventsConfig(),JSON_FORCE_OBJECT);
    }

    public function getFbConfigSuccess()
    {
        return $this->config->getFbConfigSuccess();
    }

    public function getFbToken()
    {
        return $this->config->getFbToken();
    }

    public function isDebug(){
        return $this->config->isDebug();
    }

    public function getCurrentUrl()
    {
        return $this->urlInterface->getCurrentUrl();
    }

    public function getCurrencySymbol()
    {
        return $this->priceCurrency->getCurrencySymbol();
    }
    public function getServer($data)
    {
        return $this->httpRequest->getServer($data);
    }
    public function fbPostApi($data){

        $post = http_build_query($data);
        $this->curl->post('https://graph.facebook.com/v11.0/'.
        $this->config->getFbConfigSuccess().
        '/events', $post);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOption(CURLOPT_POST, true);
        $this->curl->getBody();

    }
}
