<?php
namespace Formax\Campaigns\Model\ResourceModel\Type;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Formax\Campaigns\Model\Type', 'Formax\Campaigns\Model\ResourceModel\Type');
    }
}
