<?php
declare(strict_types=1);

namespace Ceg\BitrixIntegration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Queue extends AbstractDb
{
    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _construct()
    {
        $this->_init('ceg_bitrix_queue', 'entity_id');
    }
}
