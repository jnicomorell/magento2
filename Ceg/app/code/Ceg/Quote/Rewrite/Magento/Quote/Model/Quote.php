<?php
declare(strict_types=1);

namespace Ceg\Quote\Rewrite\Magento\Quote\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Sales\Model\OrderIncrementIdChecker;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Model\ProductFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use DatetimeZone;
use Datetime;
use Magento\Framework\Event\ManagerInterface;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Quote extends \Magento\Quote\Model\Quote
{
    const STATUS_NEW = 'new';
    const STATUS_OPEN = 'open';
    const STATUS_CLONED = 'cloned';
    const STATUS_REOPEN = 'reopen';
    const STATUS_APPROVED = 'approved';
    const STATUS_CLOSED = 'closed';
    const STATUS_CANCELED = 'canceled';

    /**
     * @var OrderIncrementIdChecker
     */
    private $incrementIdChecker;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Cart
     */
    protected $checkoutCart;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Quote
     */
    protected $parentQuote;

    /**
     * @var TimezoneInterface
     */
    private $timezoneInterface;

    /**
     * @var Date
     */
    protected $date;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @param \Magento\Framework\Model\Context                                   $context
     * @param \Magento\Framework\Registry                                        $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory                  $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory                       $attributeFactory
     * @param \Magento\Quote\Model\QuoteValidator                                $quoteValidator
     * @param \Magento\Catalog\Helper\Product                                    $catalogProduct
     * @param \Magento\Framework\App\Config\ScopeConfigInterface                 $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface                         $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface                 $config
     * @param \Magento\Quote\Model\Quote\AddressFactory                          $quoteAddressFactory
     * @param \Magento\Customer\Model\CustomerFactory                            $customerFactory
     * @param \Magento\Customer\Api\GroupRepositoryInterface                     $groupRepository
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory    $itemCollFactory
     * @param \Magento\Quote\Model\Quote\ItemFactory                             $quoteItemFactory
     * @param \Magento\Framework\Message\Factory                                 $messageFactory
     * @param \Magento\Sales\Model\Status\ListFactory                            $statusListFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                    $productRepository
     * @param \Magento\Quote\Model\Quote\PaymentFactory                          $quotePaymentFactory
     * @param \Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory $paymentCollFactory
     * @param DataObject\Copy                                                    $objectCopyService
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface               $stockRegistry
     * @param \Magento\Quote\Model\Quote\Item\Processor                          $itemProcessor
     * @param DataObject\Factory                                                 $objectFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface                   $addressRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                       $criteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder                               $filterBuilder
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory                 $addressDataFactory
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory                $customerDataFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface                  $customerRepository
     * @param \Magento\Framework\Api\DataObjectHelper                            $dataObjectHelper
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter               $dataObjectConverter
     * @param \Magento\Quote\Model\Cart\CurrencyFactory                          $currencyFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface   $attJoinProcessor
     * @param \Magento\Quote\Model\Quote\TotalsCollector                         $totalsCollector
     * @param \Magento\Quote\Model\Quote\TotalsReader                            $totalsReader
     * @param \Magento\Quote\Model\ShippingFactory                               $shippingFactory
     * @param \Magento\Quote\Model\ShippingAssignmentFactory                     $shippingAssigFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null       $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null                 $resourceCollection
     * @param array                                                              $data
     * @param OrderIncrementIdChecker|null                                       $incrementIdChecker
     * @param \Magento\Directory\Model\AllowedCountries|null                     $allowedCountries
     * @param Session|null                                                       $checkoutSession
     * @param Cart|null                                                          $checkoutCart
     * @param QuoteFactory|null                                                  $quoteFactory
     * @param ProductFactory|null                                                $productFactory
     * @param TimezoneInterface                                                  $timezoneInterface
     * @param ManagerInterface                                                   $eventManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $attributeFactory,
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollFactory,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Framework\Message\Factory $messageFactory,
        \Magento\Sales\Model\Status\ListFactory $statusListFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Model\Quote\PaymentFactory $quotePaymentFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory $paymentCollFactory,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Quote\Model\Quote\Item\Processor $itemProcessor,
        \Magento\Framework\DataObject\Factory $objectFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $dataObjectConverter,
        \Magento\Quote\Model\Cart\CurrencyFactory $currencyFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $attJoinProcessor,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Magento\Quote\Model\Quote\TotalsReader $totalsReader,
        \Magento\Quote\Model\ShippingFactory $shippingFactory,
        \Magento\Quote\Model\ShippingAssignmentFactory $shippingAssigFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        OrderIncrementIdChecker $incrementIdChecker = null,
        \Magento\Directory\Model\AllowedCountries $allowedCountries = null,
        Session $checkoutSession = null,
        Cart $checkoutCart = null,
        QuoteFactory $quoteFactory = null,
        ProductFactory $productFactory = null,
        TimezoneInterface $timezoneInterface,
        ManagerInterface $eventManager
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $attributeFactory,
            $quoteValidator,
            $catalogProduct,
            $scopeConfig,
            $storeManager,
            $config,
            $quoteAddressFactory,
            $customerFactory,
            $groupRepository,
            $itemCollFactory,
            $quoteItemFactory,
            $messageFactory,
            $statusListFactory,
            $productRepository,
            $quotePaymentFactory,
            $paymentCollFactory,
            $objectCopyService,
            $stockRegistry,
            $itemProcessor,
            $objectFactory,
            $addressRepository,
            $criteriaBuilder,
            $filterBuilder,
            $addressDataFactory,
            $customerDataFactory,
            $customerRepository,
            $dataObjectHelper,
            $dataObjectConverter,
            $currencyFactory,
            $attJoinProcessor,
            $totalsCollector,
            $totalsReader,
            $shippingFactory,
            $shippingAssigFactory,
            $resource,
            $resourceCollection,
            $data,
            $incrementIdChecker,
            $allowedCountries
        );
        $this->incrementIdChecker = $incrementIdChecker
            ?:ObjectManager::getInstance()->get(OrderIncrementIdChecker::class);
        $this->checkoutCart = $checkoutCart
            ?:ObjectManager::getInstance()->get(Cart::class);
        $this->checkoutSession = $checkoutSession
            ?:ObjectManager::getInstance()->get(Session::class);
        $this->quoteFactory = $quoteFactory
            ?:ObjectManager::getInstance()->get(QuoteFactory::class);
        $this->productFactory = $productFactory
            ?:ObjectManager::getInstance()->get(ProductFactory::class);
        $this->parentQuote = false;
        $this->timezoneInterface = $timezoneInterface;
        $this->_storeManager = $storeManager;
        $this->eventManager = $eventManager;
    }

    public function open()
    {
        $this->setParentQuoteId(null);
        $this->setParentOrderId(0);
        $this->setData('status', self::STATUS_OPEN);
        $this->save();

        return $this;
    }

    public function clone()
    {
        $this->createChildQuote(self::STATUS_CLONED);
        $this->setData('is_active', false);
        $this->save();

        return $this;
    }

    public function reopen()
    {
        $this->setData('status', self::STATUS_REOPEN);
        $this->save();

        return $this;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function approve()
    {
        $this->replaceParentQuote();
        $WebsiteId = $this->_storeManager->getStore()->getWebsiteId();
        $currentTimeZone = new DatetimeZone($this->timezoneInterface->getConfigTimezone('website', $WebsiteId));
        $dateTime = new DateTime('now', $currentTimeZone);
        $this->checkoutSession->setData('parentOldStatus', $this->getStatus());
        $this->setData('status', self::STATUS_APPROVED);
        $this->setData('tos_at',$dateTime);
        $this->save();

        return $this;
    }

    public function close()
    {
        $this->setData('status', self::STATUS_CLOSED);
        $this->setData('is_active', false);
        $this->save();

        return $this;
    }

    public function cancel()
    {
        $this->setData('status', self::STATUS_CANCELED);
        $this->setData('is_active', false);
        $this->save();

        return $this;
    }

    public function isApproved()
    {
        return $this->getData('status') == self::STATUS_APPROVED;
    }

    public function needToApprove()
    {
        return $this->getData('status') == self::STATUS_REOPEN;
    }

    /**
     * @return bool
     */
    public function isParentFirstOrder()
    {
        $parentOldStatus = $this->checkoutSession->getData('parentOldStatus');
        if (isset($parentOldStatus)) {
            switch ($parentOldStatus) {
                case self::STATUS_NEW:
                case self::STATUS_OPEN:
                    return true;
            }
        }
        return false;
    }

    /**
     * @param $status
     *
     * @return \Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createChildQuote($status)
    {
        $childQuote = $this->quoteFactory->create();

        $childQuote->setParentQuoteId($this->getId());
        $childQuote->setParentOrderId($this->getReservedOrderId());
        $childQuote->setStatus($status);
        $childQuote->setStoreId($this->getStoreId());
        $childQuote->setCustomerId($this->getCustomerId());
        $childQuote->setCustomerTaxClassId($this->getCustomerTaxClassId());
        $childQuote->setCustomerGroupId($this->getCustomerGroupId());
        $childQuote->setCustomerEmail($this->getCustomerEmail());
        $childQuote->setCustomerPrefix($this->getCustomerPrefix());
        $childQuote->setCustomerFirstname($this->getCustomerFirstname());
        $childQuote->setCustomerMiddlename($this->getCustomerMiddlename());
        $childQuote->setCustomerLastname($this->getCustomerLastname());
        $childQuote->setCustomerSuffix($this->getCustomerSuffix());
        $childQuote->setFobTotal($this->getFobTotal());

        $this->checkoutSession->replaceQuote($childQuote);
        $this->checkoutCart->unsetData('quote');

        $items = $this->getAllVisibleItems();
        foreach ($items as $item) {
            $options = $item->getProduct()->getTypeInstance()->getOrderOptions($item->getProduct());
            $options['info_buyRequest']['qty'] = $item->getQty();
            $request1 = (new DataObject())->setData($options['info_buyRequest']);
            $product = $this->productFactory->create()->load($item->getProductId());
            $product->setFobSubtotal($product->getFob() * $item->getQty());
            $product->setFobUnit($product->getFob());
            $product->setImpoId($item->getImpoId());
            $this->checkoutCart->addProduct($product, $request1);
        }

        $this->checkoutCart->save();
        return $this->checkoutCart->getQuote();
    }

    /**
     * @return false|object
     */
    public function getParentQuote()
    {
        $parentQuoteId = $this->getParentQuoteId();
        if (!empty($parentQuoteId) && !is_object($this->parentQuote)) {
            $this->parentQuote = $this->quoteFactory->create()->load($parentQuoteId);
        }

        return $this->parentQuote;
    }

    /**
     * @param $lastItemId
     *
     * @return bool
     * @throws \Exception
     */
    public function cancelParentQuote($lastItemId)
    {
        $parentQuote = $this->getParentQuote();
        $this->eventManager->dispatch(
            'ceg_before_cancel_parent_quote',
            ['quote' => $parentQuote]
        );
        if ($parentQuote && $parentQuote->getStatus() == self::STATUS_APPROVED
            && in_array($this->getStatus(), [self::STATUS_REOPEN,self::STATUS_CLONED])) {
            $items = $this->getAllVisibleItems();
            $this->eventManager->dispatch(
                'ceg_update_parent_quote',
                ['quote' => $parentQuote, 'items' => $items]
            );
            if (count($items) == 1 && $items[0]->getItemId() == $lastItemId) {
                $parentQuote->setStatus(self::STATUS_CANCELED);
                $parentQuote->save();

                $this->setStatus(self::STATUS_OPEN);
                $this->setParentQuoteId(null);
                $this->setFobTotal(null);
                $this->setImpoIds('[]');
                $this->setParentOrderId(0);
                $this->removeItem($lastItemId);
                $this->eventManager->dispatch(
                    'ceg_after_cancel_parent_quote',
                    ['quote' => $parentQuote]
                );

                return true;
            }
        }
        return false;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    private function replaceParentQuote()
    {
        $parentQuoteId = $this->getParentQuoteId();
        if (empty($parentQuoteId)) {
            $this->reserveOrderId();
            return $this;
        }

        $parentQuote = $this->getParentQuote();
        if ($parentQuote->getStatus() != self::STATUS_APPROVED) {
            $this->setParentQuoteId(null);
            $this->reserveOrderId();
            return $this;
        }

        $reservedOrderId = $parentQuote->getReservedOrderId();
        $parentQuote->delete();

        if (empty($reservedOrderId)) {
            $this->reserveOrderId();
            $reservedOrderId = $this->getReserveOrderId();
        }
        $this->setReservedOrderId($reservedOrderId);
        $this->setParentQuoteId(null);

        return $this;
    }

    /**
     * Retrieve quote items collection
     *
     * @param bool $useCache
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getItemsCollection($useCache = true)
    {
        if ($this->hasItemsCollection() && $useCache) {
            return $this->getData('items_collection');
        }
        if (null === $this->_items || !$useCache) {
            $this->_items = $this->_quoteItemCollectionFactory->create();
            $this->extensionAttributesJoinProcessor->process($this->_items);
            $this->_items->setQuote($this);
            $this->_items->getSelect()->joinleft(
                ['impo' => $this->_items->getTable('ceg_impo_entity')],
                'main_table.impo_id = impo.entity_id',
                ['import_id' => 'impo.ceg_id']
            );
        }
        return $this->_items;
    }
}
