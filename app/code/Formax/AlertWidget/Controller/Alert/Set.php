<?php

namespace Formax\AlertWidget\Controller\Alert;

use Magento\Framework\App\Action\Context;
use Formax\AlertWidget\Cookie\AlertWidget;
use Formax\AlertWidget\Api\AlertWidgetInterface;
use Magento\Framework\App\RequestInterface;

class Set extends \Magento\Framework\App\Action\Action
{
    protected $_alertWidgetInterface;

    protected $_requestInterface;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct(
        Context $context,
        AlertWidgetInterface $alertWidgetInterface,
        RequestInterface $requestInterface
    ) {
        $this->_alertWidgetInterface = $alertWidgetInterface;
        $this->_requestInterface = $requestInterface;
        parent::__construct($context);
    }

    /**
     * execute
     *
     * @return void
     */
    public function execute()
    {
        $hash = $this->_requestInterface->getParam('hash');

        $this->_alertWidgetInterface->set($hash, 86400);
    }
}
