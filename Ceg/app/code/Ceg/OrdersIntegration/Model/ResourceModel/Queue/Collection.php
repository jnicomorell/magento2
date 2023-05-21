<?php
declare(strict_types=1);

namespace Ceg\OrdersIntegration\Model\ResourceModel\Queue;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'quote_id';

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(
            \Ceg\OrdersIntegration\Model\Queue::class,
            \Ceg\OrdersIntegration\Model\ResourceModel\Queue::class
        );
    }
}
