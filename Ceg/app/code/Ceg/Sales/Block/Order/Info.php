<?php

namespace Ceg\Sales\Block\Order;

use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;

/**
 * Invoice view  comments form
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $template = 'Ceg_Sales::order/info.phtml';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var AddressConfig
     */
    protected $addressConfig;

    /**
     * @param TemplateContext $context
     * @param Registry $registry
     * @param PaymentHelper $paymentHelper
     * @param AddressConfig $addressConfig
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        PaymentHelper $paymentHelper,
        AddressConfig $addressConfig,
        array $data = []
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->coreRegistry = $registry;
        $this->_isScopePrivate = true;
        $this->addressConfig = $addressConfig;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Order # %1', $this->getQuote()->getReservedOrderId()));
        $quotePayment = $this->getQuote()->getPayment();
        if ($quotePayment->getPaymentId() !== null) {
            $infoBlock = $this->paymentHelper->getInfoBlock($quotePayment, $this->getLayout());
            $this->setChild('payment_info', $infoBlock);
        }
    }

    /**
     * @return string
     */
    public function getPaymentInfoHtml()
    {
        return $this->getChildHtml('payment_info');
    }

    /**
     * Retrieve current order model instance
     *
     * @return Quote;
     */
    public function getQuote()
    {
        return $this->coreRegistry->registry('current_quote');
    }

    /**
     * Returns string with formatted address
     *
     * @param Address $address
     * @return null|string
     */
    public function getFormattedAddress(Address $address)
    {
        $this->addressConfig->setStore($this->getQuote()->getStoreId());
        $formatType = $this->addressConfig->getFormatByCode('html');
        if (!$formatType || !$formatType->getRenderer()) {
            return null;
        }
        return $formatType->getRenderer()->renderArray($address->getData());
    }
}
