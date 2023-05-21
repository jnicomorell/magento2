<?php

namespace Ceg\Sales\ViewModel;

use Ceg\OrdersIntegration\Helper\DataFactory as HelperFactory;
use Magento\Framework\View\Element\Context;

class Order implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    protected $helper;
    protected $scopeConfig;
    protected $urlBuilder;

    /**
     * @param Context $context
     * @param HelperFactory $helperFactory
     */
    public function __construct(
        Context $context,
        HelperFactory $helperFactory
    ) {
        $this->helper = $helperFactory->create();
        $this->scopeConfig = $context->getScopeConfig();
        $this->urlBuilder = $context->getUrlBuilder();
    }

    /**
     * @return mixed
     */
    public function getAppGoProductTrackingUrl()
    {
        return $this->scopeConfig->getValue(
            \Ceg\OrdersIntegration\Helper\Data::CONFIG_PATH_APP_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getAppGoBackUrl()
    {
        return $this->urlBuilder->getUrl('sales/order/history');
    }
}
