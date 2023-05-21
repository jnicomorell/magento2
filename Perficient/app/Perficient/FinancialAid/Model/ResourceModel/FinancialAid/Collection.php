<?php
namespace Perficient\FinancialAid\Model\ResourceModel\FinancialAid;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Perficient\FinancialAid\Model\FinancialAid', 'Perficient\FinancialAid\Model\ResourceModel\FinancialAid');
    }
}
