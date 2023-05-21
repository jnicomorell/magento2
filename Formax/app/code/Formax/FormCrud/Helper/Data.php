<?php

namespace Formax\FormCrud\Helper;

use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;
use Formax\FormCrud\Model\FormCrudFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_logger;
    protected $_storeManager;
    protected $_resource;
    protected $_formCrudFactory;
    const XML_PATH_CAMPAIGNS = 'rates/';
   

    public function __construct(
        LoggerInterface $logger,
        StoreManagerInterface $store,
        ResourceConnection $resource,
        FormCrudFactory $formCrudFactory,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
) {
    $this->_logger = $logger;
    $this->_storeManager = $store;
    $this->_resource = $resource;
    $this->_termFactory = $formCrudFactory;
    $this->scopeConfig = $scopeConfig;
    $this->date = $date;
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
    
    public function getTermCollection($date=null)
    {
        try {
            $collection=$this->_formCrudFactory->create()->getCollection();
            $storeId = $this->_storeManager->getStore()->getId(); 
            $collection->setPageSize(100)->setCurPage(1);
            return $collection;
        } catch (\categoriesId $ex) {
            $this->_logger->info(__CLASS__."/".__FUNCTION__.": ". $ex->getMessage());
        }
    }

}