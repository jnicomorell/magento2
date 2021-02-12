<?php

namespace Formax\Campaigns\Helper;

use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;
use Formax\Campaigns\Model\CampaignFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Formax\Campaigns\Model\ResourceModel\CreditCard\CollectionFactory as CreditCardCollectionFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_logger;
    protected $_storeManager;
    protected $_resource;
    protected $_fileFactory;
    protected $_creditCardCollectionFactory;
    const XML_PATH_CAMPAIGNS = 'campaigns/';
   

    public function __construct(
            LoggerInterface $logger,
            StoreManagerInterface $store,
            ResourceConnection $resource,
            CampaignFactory $fileFactory,
            ScopeConfigInterface $scopeConfig,
            CreditCardCollectionFactory $creditCardCollectionFactory
    ) {
        $this->_logger            = $logger;
        $this->_storeManager      = $store;
        $this->_resource          = $resource;
        $this->_fileFactory       = $fileFactory;
        $this->scopeConfig        = $scopeConfig;
        $this->_creditCardCollectionFactory = $creditCardCollectionFactory;
    }
	public function getConfigValue($field, $storeId = null)
	{
		return $this->scopeConfig->getValue(
			$field, ScopeInterface::SCOPE_STORE, $storeId
		);
	}

	public function getGeneralConfig($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_CAMPAIGNS .'general/'. $code, $storeId);
    }    

    public function getCreditCardCollection($rut = null)
    {
        try {
            $collection = $this->_creditCardCollectionFactory->create();
            $collection->filterCampaign($rut);
            $collection->setPageSize(1);
            return $collection;
        } catch (\Exception $e) {
            $this->_logger->info(__CLASS__."/".__FUNCTION__.": ". $e->getMessage());
        }
    }

    public function getCampaignCollection($rut = null)
    {
        try {
            $collection = $this->_fileFactory->create()->getCollection();
            $collection->getCampaign($rut);
            $collection->setPageSize(1);
            return $collection;
        } catch (\Exception $e) {
            $this->_logger->info(__CLASS__."/".__FUNCTION__.": ". $e->getMessage());
        }
    }

}