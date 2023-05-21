<?php

namespace Ceg\Sales\ViewModel;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Context;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Helper\Reorder;
use Magento\Framework\Data\Helper\PostHelper;
use Ceg\Checkout\Helper\Config;

class Customer implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @var Reorder
     */
    private $reorderHelper;

    /**
     * @var PostHelper
     */
    private $postHelper;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param Session $customerSession
     * @param PriceCurrencyInterface $priceCurrency
     * @param StoreManagerInterface $storeManager
     * @param Config $configHelper
     * @param Reorder $reorderHelper
     * @param PostHelper $postHelper
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Session $customerSession,
        PriceCurrencyInterface $priceCurrency,
        StoreManagerInterface $storeManager,
        Config $configHelper,
        Reorder $reorderHelper,
        PostHelper $postHelper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->customerSession = $customerSession;
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
        $this->configHelper = $configHelper;
        $this->reorderHelper = $reorderHelper;
        $this->postHelper = $postHelper;
        $this->urlBuilder = $context->getUrlBuilder();
    }

    /**
     * Get approved quotes
     */
    public function getCustomerQuotes()
    {
        $statuses = [
            \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_APPROVED,
            \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_CLOSED
        ];
        $quoteCollection = $this->collectionFactory->create();
        $quoteCollection->addFieldToSelect('*');
        $quoteCollection->addFieldToFilter('main_table.customer_id', $this->customerSession->getCustomerId());
        $quoteCollection->addFieldToFilter('main_table.store_id', $this->storeManager->getStore()->getId());
        $quoteCollection->addFieldToFilter('main_table.converted_at', ['null' => true]);
        $quoteCollection->addFieldToFilter('main_table.status', ['in' => $statuses]);
        $quoteCollection->setOrder('main_table.created_at', 'desc');

        $quoteCollection->getSelect()
            ->joinLeft(
                ["order" => $quoteCollection->getTable('sales_order')],
                "main_table.reserved_order_id = order.increment_id",
                ["increment_id" => "order.increment_id"]
            )->columns(
                "order.increment_id"
            )->where("IFNULL(reserved_order_id,'') != IFNULL(increment_id,'')");

        return  $quoteCollection->load();
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
            $this->storeManager->getStore()
        );
    }

    /**
     * Get order view URL
     *
     * @param object $quote
     * @return string
     */
    public function getViewUrl($quote)
    {
        return $this->urlBuilder->getUrl('cegsales/order/view', ['quote_id' => $quote->getId()]);
    }

    /**
     * Get Quote Status label
     *
     * @param string $status
     * @return string
     */
    public function getQuoteStatusLabel($status)
    {
        return $this->configHelper->getQuoteStatusLabel($status);
    }

    /**
     * Check is it possible to reorder
     *
     * @param int $orderId
     * @return bool
     */
    public function canReorder($orderId)
    {
        return $this->reorderHelper->canReorder($orderId);
    }

    /**
     * get data for post by javascript in format acceptable to $.mage.dataPost widget
     *
     * @param string $url
     * @param array $data
     * @return string
     */
    public function getPostData($url, array $data = [])
    {
        return $this->postHelper->getPostData($url, $data);
    }
}
