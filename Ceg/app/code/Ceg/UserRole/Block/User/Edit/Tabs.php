<?php
declare(strict_types=1);

namespace Ceg\UserRole\Block\User\Edit;

use Exception;
use Ceg\UserRole\Block\User\Edit\Tab\AdditionalRoles;
use Magento\Framework\Exception\LocalizedException;
use Magento\User\Block\User\Edit\Tabs as MagentoTabs;

class Tabs extends MagentoTabs
{
    /**
     * @throws LocalizedException
     * @throws Exception
     */
    protected function _beforeToHtml(): Tabs
    {
        $this->addTabAfter(
            'additional_roles_section',
            [
               'label' => __('Additional Roles'),
               'title' => __('Additional Roles'),
               'content' => $this->getLayout()->createBlock(
                   AdditionalRoles::class,
                   'additional.roles.grid'
               )->toHtml()
            ],
            'roles_section'
        );

        return parent::_beforeToHtml();
    }
}
