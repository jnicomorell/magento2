<?php
namespace Perficient\FinancialAid\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class FinancialAid extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('perficient_financialaid', 'id');
    }
}
