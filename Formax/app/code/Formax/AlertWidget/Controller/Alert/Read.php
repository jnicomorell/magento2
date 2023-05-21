<?php

namespace Formax\AlertWidget\Controller\Alert;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;


class Read extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
    ) {
        $this->_cookieManager = $cookieManager;
        parent::__construct($context);
    }
    public function execute()
    {
        $cookieValue = $this->_cookieManager->getCookie(\Formax\AlertWidget\Cookie\AlertWidget::COOKIE_NAME);
        echo ($cookieValue);
    }
}
