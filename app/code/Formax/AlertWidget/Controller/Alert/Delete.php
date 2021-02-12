<?php

namespace Formax\AlertWidget\Controller\Alert;

use Magento\Framework\App\RequestInterface;

class Delete extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    protected $_requestInterface;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        RequestInterface $requestInterface
    ) {
        $this->_cookieManager = $cookieManager;
        $this->_requestInterface = $requestInterface;
        parent::__construct($context);
    }
    public function execute()
    {

        $hash = $this->_requestInterface->getParam('hash');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $cookie = $objectManager->create('Formax\AlertWidget\Cookie\AlertWidget');
        $cookie->delete(86400);
    }
}
