<?php

namespace Ceg\Sales\Block\Order;

use Magento\Framework\Cache\ConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Quote\Model\Quote;
use Ceg\Checkout\Helper\Config;

/**
 * Invoice view  comments form
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Header extends \Magento\Framework\View\Element\Template
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
     * @var Config
     */
    private $configHelper;

    /**
     * @param TemplateContext $context
     * @param Registry $registry
     * @param PaymentHelper $paymentHelper
     * @param Config $configHelper
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        PaymentHelper $paymentHelper,
        Config $configHelper,
        array $data = []
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->coreRegistry = $registry;
        $this->_isScopePrivate = true;
        $this->configHelper = $configHelper;
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
     * Retrieve current quote model instance
     *
     * @return Quote
     */
    public function getQuote()
    {
        return $this->coreRegistry->registry('current_quote');
    }

    public function getQuoteStatusLabel($status)
    {
        return $this->configHelper->getQuoteStatusLabel($status);
    }
}
