<?php
declare(strict_types=1);

namespace Ceg\StagingSchedule\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class StagingSchedule extends AbstractDb
{
    /**
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init('staging_schedule', 'staging_id');
    }
}
