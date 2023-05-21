<?php

namespace Ceg\Backend\Model\Config\Source\Admin;

use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory;
use Magento\Authorization\Model\Acl\Role\Group;
use Magento\Framework\Data\OptionSourceInterface;

class UserRoles implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $roleCollection;

    /**
     * @param CollectionFactory $roleCollection
     */
    public function __construct(
        CollectionFactory $roleCollection
    ) {
        $this->roleCollection = $roleCollection;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $roles = $this->roleCollection->create()
            ->toArray();

        $options = [];

        foreach ($roles['items'] as $role) {
            if ($role['role_type'] === Group::ROLE_TYPE) {
                $options[] = ['value' => $role['role_id'], 'label' => $role['role_name']];
            }
        }

        return $options;
    }
}
