<?php
declare(strict_types=1);

namespace Ceg\Impo\Block\Adminhtml\Publication\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Ceg\Impo\Model\ResourceModel\Impo\RelatedProductFactory;
use Ceg\Impo\Helper\DataFactory as HelperFactory;

class RelatedProducts extends Extended
{
    const SELECTION_COLUMN = 'in_impo';

    /**
     * @var HelperFactory
     */
    protected $helperFactory;

    /**
     * @var RelatedProductFactory
     */
    protected $productFactory;

    /**
     * @var CollectionFactory
     */
    protected $productCollFactory;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param RelatedProductFactory $productFactory
     * @param CollectionFactory $productCollFactory
     * @param HelperFactory $helperFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        RelatedProductFactory $productFactory,
        CollectionFactory $productCollFactory,
        HelperFactory $helperFactory,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->productCollFactory = $productCollFactory;
        $this->helperFactory = $helperFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('impo_related_products');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == self::SELECTION_COLUMN) {
            $productIds = $this->getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } elseif (!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
            }
            return $this;
        }

        parent::_addColumnFilterToCollection($column);
        return $this;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _prepareCollection()
    {
        $productIds = $this->getSelectedProducts();
        if (!empty($productIds)) {
            $this->setDefaultFilter([self::SELECTION_COLUMN => 1]);
        }

        $collection = $this->productCollFactory->create();
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('brand');
        $collection->addAttributeToSelect('model');
        $collection->setOrder('title', 'DESC');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            self::SELECTION_COLUMN,
            [
                'type' => 'checkbox',
                'name' => self::SELECTION_COLUMN,
                'values' => $this->getSelectedProducts(),
                'index' => 'entity_id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction'
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'sortable' => true,
                'index' => 'name'
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku'
            ]
        );
        $this->addColumn(
            'brand',
            [
                'header' => __('Brand'),
                'index' => 'brand'
            ]
        );
        $this->addColumn(
            'model',
            [
                'header' => __('Model'),
                'index' => 'model'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @inheritdoc
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('selected_products');
        if ($products === null) {
            $helper = $this->helperFactory->create();
            $currentImpo = $helper->getCurrentImpo();
            if ($currentImpo) {
                $relatedProduct = $this->productFactory->create();
                $products = $relatedProduct->getRelatedProductIds($currentImpo);
            }
        }
        return $products;
    }
}
