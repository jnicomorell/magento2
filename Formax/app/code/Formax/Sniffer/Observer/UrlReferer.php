<?php

namespace Formax\Sniffer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Formax\Sniffer\Model\SnifferFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;
use Psr\Log\LoggerInterface;

use Magento\Framework\App\Cache\Manager as CacheManager;

class UrlReferer implements ObserverInterface {

    const HOME = 'cms_index_index';

    const ALLOWED_URLS = 'sniffer_coopeuch/sniffer/allowed_urls';
    const PATH_ENABLE  = 'sniffer_coopeuch/sniffer/enable';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Formax\Sniffer\Model\SnifferFactory
     */
    protected $sniffer;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var 
     */
    private $storeManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $_coreSession;
    protected $_catalogSession;
    protected $_customerSession;
    protected $_checkoutSession;
    protected $_logger;
    protected $_cacheManager;

    /**
     * UrlReferer constructor
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        UrlInterface $url,
        SnifferFactory $sniffer,
        RedirectInterface $redirect,
        RemoteAddress $remoteAddress,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CoreSession $coreSession,
        CatalogSession $catalogSession,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        CacheManager $cacheManager,
        LoggerInterface $logger
    ) {
        $this->url = $url;
        $this->sniffer = $sniffer;
        $this->redirect = $redirect;
        $this->remoteAddress = $remoteAddress;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->_coreSession = $coreSession;
        $this->_catalogSession = $catalogSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_logger = $logger;
        $this->_cacheManager = $cacheManager;
    }



    /**
     * Controller action predispatch observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {


        $this->request = $observer->getRequest();
        $urls = array_map('trim', $this->getAllowedUrls());
        $actionName = $this->request->getFullActionName();
        $urlName = $this->getPageUrl();

        if ( $this->isActive() )
        {
            if ( $actionName === self::HOME || $this->strposa($urlName, $urls)) {
                $url_name = $this->getCoreSession()->getMyValue();
                if($url_name != $urlName){
                    $this->_cacheManager->setEnabled(["full_page"], false);
                    $data_json = json_encode(array_merge($this->getPostData(), $this->getParamsData()));
                    $server = $this->getServerData();
                    $data = [
                        'store_id' => $this->getStoreId(),
                        'id_tracing' => md5(time()),
                        'entity' => $actionName,
                        'browser' => $server['HTTP_USER_AGENT'],
                        'referer' => $this->redirect->getRefererUrl(),
                        'uri' => $this->getPageUrl(),
                        'ip_address' => $this->remoteAddress->getRemoteAddress(),
                        'additional_data' => $data_json
                    ];
    
                    $this->saveToDb($data);
                    $this->getCoreSession()->setMyValue($urlName);
                    $this->_cacheManager->setEnabled(["full_page"], true);
                }
            }
        }
    }

    /**
     * Get site url
     * @return string
     */
    public function getPageUrl() {
        return $this->url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
    }

    /**
     * Post data
     * @return array
     */
    public function getPostData() {
        return $this->request->getPostValue();
    }

    /**
     * Params data
     * @return array
     */
    public function getParamsData() {
        return $this->request->getParams();
    }

    /**
     * Server data
     */
    public function getServerData() {
        return $_SERVER;
    }

    /**
     * Store id
     */
    public function getStoreId() {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Store code
     */
    public function getStoreCode() {
        return $this->storeManager->getStore()->getCode();
    }

    /**
     * Save to db
     * @param array $data
     */
    private function saveToDb($data) {
        $sniffer = $this->sniffer->create();

        foreach ($data as $key => $value) {
            $sniffer->setData($key, $value);
        }

        try {
            $sniffer->save();
        } catch (\Exception $e) {
            $this->_logger->error(__CLASS__ . "_" . __FUNCTION__ . " " . $e->getMessage());
        }
    }

    private function getAllowedUrls(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $urls = $this->scopeConfig->getValue(self::ALLOWED_URLS, $storeScope);
        return explode(PHP_EOL, $urls);
    }

    private function isActive()
    {
        return $this->scopeConfig->getValue(
            self::PATH_ENABLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getCoreSession()
    {
        return $this->_coreSession;
    }

    public function getCatalogSession()
    {
        return $this->_catalogSession;
    }

    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    public function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    public function strposa($haystack, $needles = array()) {
        $found = false;
        foreach ($needles as $needle) {
            if (!empty($needle)) {
                $res = strpos($haystack, $needle);
                if ($res !== false) {
                    $found = true;
                }
            }
        }
        return $found;
    }
}
