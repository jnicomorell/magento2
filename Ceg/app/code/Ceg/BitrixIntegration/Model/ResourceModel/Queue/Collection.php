<?php
declare(strict_types=1);

namespace Ceg\BitrixIntegration\Model\ResourceModel\Queue;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(
            \Ceg\BitrixIntegration\Model\Queue::class,
            \Ceg\BitrixIntegration\Model\ResourceModel\Queue::class
        );
    }
}
