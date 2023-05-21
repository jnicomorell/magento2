<?php
declare(strict_types=1);

namespace Ceg\UserRole\Model;

use Ceg\UserRole\Api\Data\AdditionalRolesInterface;
use Ceg\UserRole\Model\ResourceModel\AdditionalRoles as AdditionalRolesResourceModel;
use Magento\Framework\Model\AbstractModel;

class AdditionalRoles extends AbstractModel implements AdditionalRolesInterface
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(AdditionalRolesResourceModel::class);
    }

    public function getUserId(): int
    {
        return (int) $this->getData('user_id');
    }

    public function setUserId(int $value): void
    {
        $this->setData('user_id', $value);
    }

    public function getRoleIds(): ?string
    {
        return $this->getData('role_ids');
    }

    public function setRoleIds(string $value): void
    {
        $this->setData('role_ids', $value);
    }
}
