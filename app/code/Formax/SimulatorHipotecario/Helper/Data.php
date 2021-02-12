<?php

namespace Formax\SimulatorHipotecario\Helper;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

	const XML_PATH_HIPOTECAFORM = 'simulatorhipotecario_form_configuration/';
    protected $_logger;
     /**
     * @var  \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $_transportBuilder;

    private $storeManager;
    private $inlineTranslation;

    public function __construct( 
                ScopeConfigInterface $scopeConfig,
                \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
                \Magento\Store\Model\StoreManagerInterface $storeManager,
                \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
                LoggerInterface $logger){

        $this->_transportBuilder = $transportBuilder;
        $this->storeManager=$storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->inlineTranslation=$inlineTranslation;
        $this->_logger = $logger;
    }  

    public function getConfigValue($field, $storeId = null)
	{
		return $this->scopeConfig->getValue(
			$field, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
		);
    }
    
    public function getHipotecaSuccessConfig($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_HIPOTECAFORM .'proof_success/'. $code, $storeId);
    }
    
    public function getHipotecaConfig($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_HIPOTECAFORM .'general/'. $code, $storeId);
    }
}