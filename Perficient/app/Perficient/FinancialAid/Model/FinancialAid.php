<?php
namespace Perficient\FinancialAid\Model;
 
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class FinancialAid extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Perficient\FinancialAid\Model\ResourceModel\FinancialAid');
    }
}
