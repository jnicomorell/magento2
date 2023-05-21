<?php
declare(strict_types=1);

namespace Ceg\Impo\Model\ResourceModel\Impo\RelatedProduct;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(
            \Ceg\Impo\Model\Impo\RelatedProduct::class,
            \Ceg\Impo\Model\ResourceModel\Impo\RelatedProduct::class
        );
    }
}
