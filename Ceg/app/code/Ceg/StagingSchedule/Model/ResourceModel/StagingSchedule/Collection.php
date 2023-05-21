<?php
declare(strict_types=1);

namespace Ceg\StagingSchedule\Model\ResourceModel\StagingSchedule;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'staging_id';

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(
            \Ceg\StagingSchedule\Model\StagingSchedule::class,
            \Ceg\StagingSchedule\Model\ResourceModel\StagingSchedule::class
        );
    }
}
