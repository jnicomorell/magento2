<?php
namespace Formax\News\Model\ResourceModel\Homenews;

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
        $this->_init('Formax\News\Model\Homenews', 'Formax\News\Model\ResourceModel\Homenews');
    }
}
