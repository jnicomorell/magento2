<?php
namespace Ceg\Catalog\Block\Rewrite\Product\ProductList;

/**
 * Product list toolbar
 */
class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
    {
    /**
     * Products collection
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected $_collection = null;

    /**
     * Set collection to pager
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;
        $this->_collection
        ->addAttributeToFilter(
            [
                ['attribute'=> 'is_in_impo','null' => true],
                ['attribute'=> 'is_in_impo','eq' => ''],
                ['attribute'=> 'is_in_impo','eq' => '0']
            ]);
        $ids=$this->_collection->getAllIds();
        $this->_collection->addFieldToFilter('entity_id', ['in' => $ids]);

        $this->_collection->setCurPage(parent::getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = (int)parent::getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }
        if (parent::getCurrentOrder()) {
            $this->_collection->setOrder(parent::getCurrentOrder(), parent::getCurrentDirection());
                $this->_collection->addAttributeToSort(
                    parent::getCurrentOrder(),
                    parent::getCurrentDirection()
                );
        }
        
        return $this;       
    }
}
