<?php

namespace Formax\SimulatorHipotecario\Controller\Hipotecario;

class Mail extends \Magento\Framework\App\Action\Action
{

    protected $_resultJsonFactory;
    protected $_helper;
    protected $_logger;
    protected $_helperCoreApi;

    public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
            \Psr\Log\LoggerInterface $logger,
            \Formax\SimulatorHipotecario\Helper\Data $helper,
            \Formax\CoreApiCoopeuch\Helper\Data $helperCoreApi
    )
    {
        $this->_resultJsonFactory = $jsonResultFactory;
        $this->_logger = $logger;
        $this->_helper = $helper;
        $this->_helperCoreApi = $helperCoreApi;
        return parent::__construct($context);
    }

    public function execute()
    {
        if ($this->getRequest()->isAjax()) 
        {
            $emailto = $this->getRequest()->getParam('emailto');
            $name = $this->getRequest()->getParam('name');
            $lastname = $this->getRequest()->getParam('lastname');
            $rut = $this->getRequest()->getParam('rut');
            $phone = $this->getRequest()->getParam('phone');
            $email = $this->getRequest()->getParam('email');
            $amount = $this->getRequest()->getParam('amount');

            if ($this->_helperCoreApi->validadorRut($rut))
            {
                try {
                    $this->_helper->sendEmail($emailto,$name,$lastname,$rut,$phone,$email,$amount);
                } catch (\Exception $ex) {
                    $this->_logger->error("Error send mail Return : " . $ex->getMessage());
                }
            }
            return false;
        }
    }
}