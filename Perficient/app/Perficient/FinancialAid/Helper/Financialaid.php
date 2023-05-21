<?php

declare(strict_types=1);

namespace Perficient\FinancialAid\Helper;

/**
 * Class Financialaid
 * @package Perficient\FinancialAid\Helper
 */
class Financialaid
{
    protected $_resource;

    /**
     * @var \Perficient\FinancialAid\Model\Config $financialAidConfig
     */
    private $financialAidConfig;

    /**
     * NonRefundableProduct constructor.
     * @param \Perficient\FinancialAid\Model\Config $financialAidConfig
     */
    public function __construct(
        \Perficient\FinancialAid\Model\Config $financialAidConfig,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->financialAidConfig = $financialAidConfig;
        $this->resource = $resource;
        $this->connection = $this->resource->getConnection();
    }


    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return bool
     */
    public function getFinancialAid(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        if ($this->financialAidConfig->getEnabled()) {
            $financialaid = $product->getCustomAttribute('financialaid');
            $attributes = $product->getAttributes();
            /*
             * select row_id from catalog_product_entity where entity_id = 9646;
                select attribute_id from eav_attribute where attribute_code = 'financialaid';
                select value from catalog_product_entity_int where attribute_id = 343;
             */
            //var_dump($product->getSku());
            //var_dump($product->getFinancialaid());
            $select = $this->connection->select()->from(
                'catalog_product_entity',
                'row_id'
            )->where(
                'entity_id = ?',
                $product->getId()
            );
            $rowId = $this->connection->fetchOne($select);
            $select2 = $this->connection->select()->from(
                'eav_attribute',
                'attribute_id'
            )->where(
                'attribute_code = ?',
                'financialaid'
            );
            $attrId = $this->connection->fetchOne($select2);
            $select3 = $this->connection->select()->from(
                'catalog_product_entity_int',
                'value'
            )->where(
                'attribute_id = ?',
                $attrId
            )->where(
                'row_id = ?',
                $rowId
            );
            $financialaid = $this->connection->fetchOne($select3);

            if (isset($financialaid)) {
                return (bool)$financialaid;
            }
        }
        return false;
    }
}
