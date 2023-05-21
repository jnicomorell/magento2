<?php
declare(strict_types=1);

namespace Ceg\UserRole\Model\ResourceModel\AdditionalRoles;

use Ceg\UserRole\Model\AdditionalRoles as AdditionalRolesAliasModel;
use Ceg\UserRole\Model\ResourceModel\AdditionalRoles as AdditionalRolesAliasResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(AdditionalRolesAliasModel::class, AdditionalRolesAliasResourceModel::class);
    }
}
