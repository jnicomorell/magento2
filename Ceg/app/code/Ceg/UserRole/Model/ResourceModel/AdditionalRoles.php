<?php
declare(strict_types=1);

namespace Ceg\UserRole\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class AdditionalRoles extends AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('authorization_additional_roles', 'entity_id');
    }
}
