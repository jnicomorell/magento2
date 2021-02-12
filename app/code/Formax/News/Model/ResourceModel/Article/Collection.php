<?php
namespace Formax\News\Model\ResourceModel\Article;

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
        $this->_init('Formax\News\Model\Article', 'Formax\News\Model\ResourceModel\Article');
    }
}
