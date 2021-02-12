<?php
namespace Formax\FormCrud\Model\ResourceModel\FormCrud;



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
        $this->_init('Formax\FormCrud\Model\FormCrud', 'Formax\FormCrud\Model\ResourceModel\FormCrud');
    }
}