<?php
declare(strict_types=1);

namespace Ceg\Impo\ViewModel;

use Ceg\CatalogPermissions\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Block\Product\ListProductFactory;
use Magento\CatalogInventory\Model\Stock\StockItemRepositoryFactory;
use Magento\Framework\Pricing\PriceCurrencyInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Ceg\Impo\Api\ImpoRepositoryInterfaceFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;
use Magento\Framework\Locale\Resolver;
use DateTime;
class ImpoData implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    protected $cartItems;

    /**
     * Custom Date format
     */
    const FORMAT_DATE = 'dd MMM y';

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var ImpoRepositoryInterfaceFactory
     */
    protected $impoRepoFactory;

    /**
     * @var Data
     */
    private $cpHelper;

    /**
     * resource
     *
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * cart
     *
     * @var Cart
     */
    private $cart;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ListProductFactory
     */
    private $listBlockFactory;

    /**
     * @var StockItemRepositoryFactory
     */
    private $stockItemFactory;

    /**
     * @var PriceCurrencyInterfaceFactory
     */
    private $priceHelperFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    protected $_stockRegistry;

    protected $locale;

    /**
     *
     * @param Context $context
     * @param Data $cpHelper
     * @param ResourceConnection $resource
     * @param Cart $cart
     * @param ListProductFactory $listBlockFactory
     * @param StockItemRepositoryFactory $stockItemFactory
     * @param PriceCurrencyInterfaceFactory $priceHelperFactory
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param QuoteFactory $quoteFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param ImpoRepositoryInterfaceFactory $impoRepoFactory
     * @param DateTimeFactory $dateTimeFactory
     * @param DateTimeFormatterInterface $dateTimeFormatter
     * @param array $data
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Context $context,
        Data $cpHelper,
        ResourceConnection $resource,
        Cart $cart,
        ListProductFactory $listBlockFactory,
        StockItemRepositoryFactory $stockItemFactory,
        PriceCurrencyInterfaceFactory $priceHelperFactory,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        QuoteFactory $quoteFactory,
        CustomerRepositoryInterface $customerRepository,
        ImpoRepositoryInterfaceFactory $impoRepoFactory,
        DateTimeFactory $dateTimeFactory,
        DateTimeFormatterInterface $dateTimeFormatter,
        Resolver $locale,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->cpHelper = $cpHelper;
        $this->resource = $resource;
        $this->cart = $cart;
        $this->listBlockFactory = $listBlockFactory;
        $this->stockItemFactory = $stockItemFactory;
        $this->priceHelperFactory = $priceHelperFactory;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->quoteFactory = $quoteFactory;
        $this->customerRepository = $customerRepository;
        $this->impoRepoFactory = $impoRepoFactory;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->locale = $locale;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->_stockRegistry = $stockRegistry;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function hasValidImpoAccess()
    {
        return $this->cpHelper->isValidImpo();
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function hasImpoAccess()
    {
        return $this->cpHelper->isCatalogAvailable();
    }

    /**
     * Method getLegendPrice
     *
     * @param $type=1 $type [explicite description]
     *
     * @return void
     */
    public function getLegendPrice($type = 1)
    {
        if ($type==1) {
            return $this->cpHelper->_getConfig('impo/sellby/unit_text');
        }
        if ($type==2) {
            return $this->cpHelper->_getConfig('impo/sellby/box_text');
        }
    }

    /**
     * Method getPriceDecimals
     *
     * @param $type=1 $type [explicite description]
     *
     * @return void
     */
    public function getPriceDecimals()
    {
        $decimals = '1';
        if($this->cpHelper->_getConfig('impo/general/pricedecimals')) {
            $decimals = $this->cpHelper->_getConfig('impo/general/pricedecimals');
        }
        return $decimals;
    }

    public function getCartItems()
    {
        if(!$this->cartItems) {
            $this->cartItems = [];
            foreach ($this->cart->getQuote()->getAllItems() as $cartItem) {
                $this->cartItems[$cartItem->getProductId()] = $cartItem;
            }
        }
        return $this->cartItems;
    }

    /**
     * Method getQuoteId
     *
     * @param $productId
     * @return void
     */
    public function getQuoteId($productId)
    {
        $items = $this->getCartItems();
        return $items[$productId]->getId();
    }

    /**
     * Method getQuoteItem
     *
     * @param $productId
     * @return void
     */
    public function getQuoteItem($productId)
    {
        $items = $this->getCartItems();
        return $items[$productId];
    }

    /**
     * Method productInCart
     *
     * @param $productId $productId [explicite description]
     *
     * @return void
     */
    public function productInCart($productId)
    {
        $items = $this->getCartItems();
        $itemsIds = [];
        foreach ($items as $cartItem) {
            array_push($itemsIds, $cartItem->getProduct()->getId());
        }

        return in_array($productId, $itemsIds);
    }

    /**
     * Method productInCart
     *
     * @param $productId $productId [explicite description]
     *
     * @return void
     */
    public function lastProductInCart($productId)
    {
        $items = $this->getCartItems();
        $itemsIds = [];
        foreach ($items as $cartItem) {
            array_push($itemsIds, $cartItem->getProduct()->getId());
        }
        $key = array_search($productId, $itemsIds);
        if ($key !== false) {
            unset($itemsIds[$key]);
        }
        return !empty($itemsIds)?false:true;
    }
    /**
     * Method productInCart
     *
     * @param $productId $productId [explicite description]
     *
     * @return void
     */
    public function productInCartQty($productId)
    {
        $result = $this->getCartItems();

        //TODO: Revisar flujo con session de auth0...
        if (isset($this->customerSession->getCustomer()->getData()['email'])) {
            $cart = $this->getCartByEmail($this->customerSession->getCustomer()->getData()['email']);
            $result = $cart->getAllVisibleItems();
        }
        foreach ($result as $cartItem) {
            if ($cartItem->getProduct()->getId() == $productId) {
                return $cartItem->getQty();
            }
        }
    }
    /**
     * Method getButtonTxt
     *
     * @param $type=1 $type [explicite description]
     *
     * @return void
     */
    public function getButtonTxt($type = 'add')
    {
        if ($type=='add') {
            return $this->cpHelper->_getConfig('impo/button/add');
        }
        if ($type=='update') {
            return $this->cpHelper->_getConfig('impo/button/update');
        }
    }

    public function getAddToCartUrl($product)
    {
        $listBlock = $this->listBlockFactory->create();
        return $listBlock->getAddToCartUrl($product);
    }

    public function getProductStockData($product)
    {
        return $this->_stockRegistry->getStockItem($product->getEntityId());
    }

    public function convertAndFormatPrice($value, $precision = 2)
    {
        $priceHelper = $this->priceHelperFactory->create();
        return $priceHelper->convertAndFormat($value, true, $precision);
    }

    public function getCurrencySymbol()
    {
        $priceHelper = $this->priceHelperFactory->create();
        return $priceHelper->getCurrencySymbol();
    }

    /**
     * @return bool
     */
    public function isCustomerEnableToCheckout(): bool
    {
        $customer = $this->customerSession->getCustomer();
        if ($customer->getCheckoutDisabled()) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    /**
     * @return string
     */
    public function getCartUpdateUrl()
    {
        return $this->urlBuilder->getUrl('checkout/cart/updatePost/');
    }

    /**
     * @param $impoId
     * @return bool|string
     */
    public function endDate($impoId)
    {
        $result = false;
        try {
            $impoRepository = $this->impoRepoFactory->create();
            $impo = $impoRepository->getById($impoId);
            $staticDate = new DateTime($impo->getFinishAt());
            $result = $this->dateTimeFormatter->formatObject($staticDate, self::FORMAT_DATE, $this->locale->getLocale());

        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $result = __('Not available');
        }
        return $result;
    }

    /**
     * @param $productId
     * @return array
     */
    public function getLastImpoIdByProductId($productId)
    {
        $date = $this->dateTimeFactory->create()->gmtDate('Y-m-d H:m:00');
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            ['impo_entity'=>'ceg_impo_entity'],
            ['ceg_id', 'entity_id as impo_id']
        )
            ->joinInner(
                ['impo_product' => 'ceg_impo_product'],
                'impo_product.impo_id = impo_entity.entity_id'
            )
            ->where('impo_entity.start_at < ?', $date)
            ->where('impo_entity.finish_at > ?', $date)
            ->where('impo_entity.website_id = ?', $websiteId)
            ->where('impo_product.product_id = ?', $productId)
            ->order('impo_product.impo_id DESC')
            ->limit(1);
        $result = $connection->fetchAll($select);

        $impoId = [];
        if (isset($result[0]['impo_id'])) {
            $impoId = $result[0]['impo_id'];
        }

        return $impoId;
    }

    /**
     * @param $productId
     * @return bool
     */
    public function isSaleableProduct($productId)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $impoRepository = $this->impoRepoFactory->create();
        $validProducts = $impoRepository->getProductIdsForActiveImpo($websiteId);
        if (array_key_exists($productId, $validProducts)) {
            return true;
        }
        return false;
    }

    /**
     * @return object
     */
    public function getCartByEmail($email)
    {
        $customer = $this->getCustomer($email);
        return $this->quoteFactory->create()->loadByCustomer($customer);
    }

    /**
     * @return object
     */
    public function getCustomer($email)
    {
        return $this->customerRepository->get($email);
    }

    /**
     * @return object
     */
    public function getParentOrderId()
    {
        return $this->cart->getQuote()->getParentOrderId();
    }

    /**
     * @param $key
     * @param $count_prices
     * @param $product_tierprice
     * @param $qtyIncrements
     * @param $price
     *
     * @return int
     */
    function dataQtyTo($key, $countPrices, $productTierprice, $qtyIncrements, $price)
    {
        $priceQty = 99999999;
        if ($key!=0) {
            $priceQty = ($key-1<$countPrices ? (int)$productTierprice[$key-1]['price_qty']-$qtyIncrements : (int)$price['price_qty']);
        }

        return $priceQty;
    }

}
