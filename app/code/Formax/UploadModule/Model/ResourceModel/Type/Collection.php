<?php
namespace Formax\UploadModule\Model\ResourceModel\Type;

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
        $this->_init('Formax\UploadModule\Model\Type', 'Formax\UploadModule\Model\ResourceModel\Type');
    }
}
