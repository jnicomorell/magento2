<?php
namespace Formax\UploadGeneral\Model\ResourceModel\File;

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
        $this->_init('Formax\UploadGeneral\Model\File', 'Formax\UploadGeneral\Model\ResourceModel\File');
    }
}
