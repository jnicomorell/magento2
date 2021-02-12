<?php

namespace Formax\Formularios\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
	const XML_PATH_CREDHIPOTECARIO = 'cred_hipotecario_config/';
	const XML_PATH_HAZTESOCIO 	   = 'haztesocio_config/';
	const XML_PATH_AHORRO 	   	   = 'ahorro_config/';
	const XML_PATH_DAP 	   = 'dap_config/';

	public function getConfigValue($field, $storeId = null)
	{
		return $this->scopeConfig->getValue(
			$field, ScopeInterface::SCOPE_STORE, $storeId
		);
	}

	public function getCredHipotecarioConfig($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_CREDHIPOTECARIO .'general/'. $code, $storeId);
	}
    
    public function getCredHipotecarioVoucher($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_CREDHIPOTECARIO .'voucher/'. $code, $storeId);
	}

	public function getHazteSocioConfig($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_HAZTESOCIO . $code, $storeId);
	}

	public function getAhorroConfig($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_AHORRO . $code, $storeId);
	}

	public function getDapConfig($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_DAP . $code, $storeId);
	}

}