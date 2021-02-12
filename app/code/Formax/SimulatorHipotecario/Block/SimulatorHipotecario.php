<?php

namespace Formax\SimulatorHipotecario\Block;

use Formax\SimulatorHipotecario\Helper\Data;
use Psr\Log\LoggerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class SimulatorHipotecario extends Template {

    protected $_helper;
    protected $_logger;
    protected $_context;
	protected $_prefix;
	protected $_phone;
	protected $_rut;
    protected $_amount;
    protected $_helperApi;
    protected $_settings;
    protected $_campaign;
    protected $_regions;
    protected $_comunas;

    public function __construct(
            Context $context,
            Data $helperData,
            LoggerInterface $logger,
            \Formax\CoreApiCoopeuch\Helper\Data $helperApi
    ) {
        $this->_context = $context;
        $this->_helper = $helperData;
        $this->_helperApi = $helperApi;
        $this->_logger = $logger;
        parent::__construct($context);
        $this->init();
        $this->setSettingsInputs();
    }

    public function init()
    {
    }

    public function setSettingsInputs()
    {
        $this->_settings = array(
            'monto_step' => $this->_helperApi->getConfig('ss_step_amount', 'ss'),
            'monto_min' => $this->_helperApi->getConfig('ss_min_amount', 'ss'),
            'monto_max' => $this->_helperApi->getConfig('ss_max_amount', 'ss'),
            'cuotas_step' => $this->_helperApi->getConfig('ss_step_months', 'ss'),
            'cuotas_min' => $this->_helperApi->getConfig('ss_min_months', 'ss'),
            'cuotas_max' => $this->_helperApi->getConfig('ss_max_months', 'ss')
        );
        return $this;
    }

    public function getPrefix(){
        return $this->_prefix;
    }
    public function getPhone(){
        return $this->_phone;
    }
    public function getCompletePhone(){
        return $this->_prefix.$this->_phone;
    }
    public function getRut(){
        return $this->_rut;
    }
    public function getAmount(){
        return $this->_amount;
    }
    public function getSettingsInputs()
    {
        return $this->_settings;
    }
    public function getCampaign(){
        return $this->_campaign;
    }

    public function getTitle()
    {
        return $this->_helper->getHipotecaConfig('title');
    }
    
    public function getTextDownload()
    {
        return $this->_helper->getHipotecaConfig('text_download');
    }

    public function getLegalTerms()
    {
        return $this->_helper->getHipotecaConfig('legal_terms');
    }
    public function getValorActualUf(){
        $this->rateblock= $this->getLayout()->createBlock('Formax\MortgageSimulator\Block\Rates');
        $rates = $this->rateblock->getRateCollection();
		$uf = $this->rateblock->getUfCollection();
		$ufData="";
		foreach($uf as $ufvalue){
			$ufData=$ufvalue->getUfValue();
		}
		if(empty($ufData)){
			$ufData=28700;
		}
		return $ufData;
    }

}
