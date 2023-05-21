<?php
declare(strict_types=1);

namespace Ceg\OrdersIntegration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Queue extends AbstractDb
{
    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @return void
     */
    public function _construct()
    {
        $this->_init('ceg_quote_queue', 'entity_id');
    }
}
