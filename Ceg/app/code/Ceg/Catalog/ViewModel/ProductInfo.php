<?php
declare(strict_types=1);

namespace Ceg\Catalog\ViewModel;

use Magento\Directory\Model\Country;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Pricing\PriceCurrencyInterfaceFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
class ProductInfo implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    protected $countryFactory;

    /**
     * resource
     *
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var PriceCurrencyInterfaceFactory
     */
    private $priceHelperFactory;

    protected $_stockRegistry;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Framework\Registry $registry
     * @param ResourceConnection $resource
     * @param PriceCurrencyInterfaceFactory $priceHelperFactory
     * @param StockRegistryInterface $stockRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Registry $registry,
        ResourceConnection $resource,
        PriceCurrencyInterfaceFactory $priceHelperFactory,
        StockRegistryInterface $stockRegistry,
        array $data = []
    ) {
        $this->countryFactory = $countryFactory;
        $this->coreRegistry = $registry;
        $this->resource = $resource;
        $this->priceHelperFactory = $priceHelperFactory;
        $this->_stockRegistry = $stockRegistry;

    }

    /**
     * @return  array|float
     */
    public function getPrice()
    {
        /** @var Product $product */
        $product = $this->coreRegistry->registry('product');
        return $product->getFormattedPrice();
    }

    public function getCurrentProduct()
    {
        return $this->coreRegistry->registry('current_product');
    }

    public function getMaxTierPrice($product = null)
    {
        if (!isset($product)) {
            $product = $this->getCurrentProduct();
        }
        $maxTierPrice = [];
        if ($product != null) {
            $tierPrices = $product->getData('tier_price');
            $firstTierPrice = true;
            if (isset($tierPrices) && !empty($tierPrices) && is_array($tierPrices)) {
                foreach ($tierPrices as $tierPrice) {
                    if ($firstTierPrice) {
                        $firstTierPrice = false;
                        $maxTierPrice = $tierPrice;
                    } elseif ($maxTierPrice['price_qty'] < $tierPrice['price_qty']) {
                        $maxTierPrice = $tierPrice;
                    }
                }
                $maxTierPrice['price_qty'] = $product->getData('qtyinbox') * $maxTierPrice['price_qty'];
            }
        }
        return $maxTierPrice;
    }

    public function getLastImpoDate($product = null): array
    {
        if($product == null) {
            $product = $this->getCurrentProduct();
        }
        if($product !== null) {
            return ['last_impo_date' => $product->getData('last_impo_date')];
        }
        return [];
    }

    /**
     * country full name
     *
     * @return string
     */
    public function getCountryName($countryId): string
    {
        $countryName = '';
        $country = $this->countryFactory->create()->loadByCode($countryId);
        if ($country) {
            $countryName = $country->getName();
        }
        return $countryName;
    }

        /**
     * @param $productId
     * @return array
     */
    public function getTierPrice($productId)
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            'catalog_product_entity_tier_price',
            ['price'=>'value', 'price_qty'=>'qty']
        )
            ->where('entity_id = ?', $productId);

        return $connection->fetchAll($select);
    }
    /**
     * getTax.
     *
     * @param    mixed    $taxClassId
     * @return    mixed
     */
    public function getTax($taxClassId)
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            ['tcr' => 'tax_calculation_rate'],
            ['rate']
        )->joinRight(
            ['tc' => 'tax_calculation'],
            'tc.tax_calculation_rate_id = tcr.tax_calculation_rate_id',
            []
        )->where(
            'tc.product_tax_class_id = ?',
            $taxClassId
        );

        return $connection->fetchOne($select);
    }
    public function convertAndFormatPrice($value, $precision = 2)
    {
        $priceHelper = $this->priceHelperFactory->create();
        return $priceHelper->convertAndFormat($value, true, $precision);
    }

    public function getProductStockData($product)
    {
        return $this->_stockRegistry->getStockItem($product->getEntityId());
    }

    public function printQtyTo($key, $count_prices, $prduct_tierprice, $qtyInBox, $price, $qtyFrom)
    {
        $message = "";
        $qtyTo = (int)$price['price_qty'] * $qtyInBox;
        if ($key-1<$count_prices) {
            $qtyTo = (((int)$prduct_tierprice[$key - 1]['price_qty'] * $qtyInBox) - $qtyInBox);
        }
        if($qtyFrom != $qtyTo) {
            $message = ' '.__('to').' '.$qtyTo;
        }
        return $message;
    }
}
