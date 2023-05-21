<?php

namespace Ceg\Sales\Block\Order;

use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Http\Context as HtmlContext;
use Magento\Payment\Helper\Data;
use Magento\Quote\Model\Quote;

/**
 * Sales Order view block
 *
 * @api
 * @since 100.0.2
 */
class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $template = 'Ceg_Sales::order/view.phtml';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @var HtmlContext
     * @since 101.0.0
     */
    protected $httpContext;

    /**
     * @var Data
     */
    protected $paymentHelper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param HtmlContext $httpContext
     * @param Data $paymentHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        HtmlContext $httpContext,
        Data $paymentHelper,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->coreRegistry = $registry;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->priceCurrency = $priceCurrency;
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
        if ($quotePayment->getPaymentId() !==null) {
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
     * Return back url for logged in and guest users
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->httpContext->getValue(CustomerContext::CONTEXT_AUTH)) {
            return $this->getUrl('sales/order/history');
        }
        return $this->getUrl('sales/order/form');
    }

    /**
     * Return back title for logged in and guest users
     *
     * @return Phrase
     */
    public function getBackTitle()
    {
        if ($this->httpContext->getValue(CustomerContext::CONTEXT_AUTH)) {
            return __('Back to My Orders');
        }
        return __('View Another Order');
    }

    /**
     * Get visible items for current page.
     *
     * To be called from templates(after _prepareLayout()).
     *
     * @return DataObject[]
     * @since 100.1.7
     */
    public function getItems()
    {
        return $this->getQuote()->getAllVisibleItems();
    }

    /**
     * Format Price
     *
     * @param float $price
     * @return string
     * @throws NoSuchEntityException
     */
    public function formatPrice($price)
    {
        return $this->priceCurrency->format(
            $price,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->_storeManager->getStore()
        );
    }

    /**
     * @param DataObject $item
     * @return string
     * @throws NoSuchEntityException
     */
    public function getModelValue($item)
    {
        /** @var  $product \Magento\Catalog\Model\Product */
        $product = $item->getProduct();
        $product->getResource()->load($product, $product->getId());
        return $product->getModel();
    }
}
