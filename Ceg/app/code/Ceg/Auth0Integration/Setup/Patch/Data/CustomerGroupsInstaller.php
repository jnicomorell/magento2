<?php
declare(strict_types=1);

namespace Ceg\Auth0Integration\Setup\Patch\Data;

use Magento\Customer\Model\Group;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Model\GroupFactory;

class CustomerGroupsInstaller implements DataPatchInterface
{
    const DEFAULT_TAX_CLASS_ID = 3;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * InstallData constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param GroupFactory $groupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        GroupFactory $groupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->groupFactory = $groupFactory;
    }

    /**
     * @return CustomerGroupsInstaller|void
     * @throws \Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        try {
            /** @var Group $prospectGroup */
            $prospectGroup = $this->groupFactory->create();
            $prospectGroup
                ->setCode('Cliente Prospecto')
                ->setTaxClassId(self::DEFAULT_TAX_CLASS_ID)
                ->save();

            /** @var Group $approvedGroup */
            $approvedGroup = $this->groupFactory->create();
            $approvedGroup
                ->setCode('Cliente Asociado')
                ->setTaxClassId(self::DEFAULT_TAX_CLASS_ID)
                ->save();
        } catch (\Exception $e) {
            // @codingStandardsIgnoreStart
            echo $e->getMessage();
            // @codingStandardsIgnoreEnd
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array
     */
    public static function getDependencies():array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases():array
    {
        return [];
    }
}
