<?php

namespace Formax\FormCrud\Block;

use Formax\FormCrud\Helper\Data;
use Psr\Log\LoggerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

class FromCrud extends Template {

    protected $_helperData;
    protected $_logger;
    protected $_context;
    protected $_storeManager;
    

    public function __construct(
            Context $context,
            Data $helperData,
            LoggerInterface $logger,
            StoreManagerInterface $store
    ) {
        $this->_context = $context;
        $this->_helperData = $helperData;
        $this->_logger = $logger;
        $this->_storeManager = $store;
        parent::__construct($context);
    }

    public function getYear()
	{	
        return date("Y");
    }

    public function getformatDay($date){
        $time = date("d", strtotime($date)) ;

        return $time;
    }
    public function getformatMoth($date){
        $months=array(
            "January"=>"Enero",
            "February"=>"Febrero",
            "March"=>"Marzo",
            "April"=>"Abril",
            "May"=>"Mayo",
            "June"=>"Junio",
            "July"=>"Julio",
            "August"=>"Agosto",
            "September"=>"Septiembre",
            "October"=>"Octubre",
            "November"=>"Noviembre",
            "December"=>"Diciembre"
        );
         $time = date("F", strtotime($date));
         $time =$months[$time];
         return $time;
    }
    public function getformatYear($date){
        $time = date("Y", strtotime($date)) . ' ';

        return $time;
    }
    public function getformatTime($date){
        $time = date('G:i', strtotime($date)) . ' horas' ;

        return $time;
    }
    public function getTermCollection()
    {
        return $this->_helperData->getTermCollection();
    }
}
