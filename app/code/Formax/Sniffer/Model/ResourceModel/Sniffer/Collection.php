<?php

namespace Formax\Sniffer\Model\ResourceModel\Sniffer;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'sniffer_grid_collection';
    protected $_eventObject = 'sniffer_grid_collection';

    /**Formax_Sniffer
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('Formax\Sniffer\Model\Sniffer', 'Formax\Sniffer\Model\ResourceModel\Sniffer');
    }

}
