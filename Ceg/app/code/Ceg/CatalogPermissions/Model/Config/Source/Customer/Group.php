<?php
declare(strict_types=1);

namespace Ceg\CatalogPermissions\Model\Config\Source\Customer;

use Magento\Framework\Option\ArrayInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;

/**
 * Customer groups source
 */
class Group implements ArrayInterface
{
    /**
     * Customer groups option
     *
     * @var null|array
     */
    protected $_options;

    /**
     * Customer group collection factory
     *
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Initialize source
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * Retrieve customer groups as options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (null === $this->_options) {
            $groups = $this->_collectionFactory->create();
            $this->_options = $groups->toOptionArray();
        }
        return $this->_options;
    }
}
