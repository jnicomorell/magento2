<?php
declare(strict_types=1);

namespace Ceg\CustomerAddressAttributes\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CustomerDefaultAddressesInstaller implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var Customer
     */
    protected $customerModel;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param Customer                 $customerModel
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Customer $customerModel
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerModel = $customerModel;
    }

    /**
     * @return CustomerDefaultAddressesInstaller|void
     * @throws \Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        try {
            // Set last customer address as default adderess
            $customers = $this->customerModel->getCollection()->addAttributeToSelect("*")->load();
            foreach ($customers as $customer) {
                $lastAddressId = 0;
                foreach ($customer->getAddresses() as $address) {
                    if ($address->getId() > $lastAddressId) {
                        $lastAddressId = $address->getId();
                    }
                }
                if ($lastAddressId > 0) {
                    $customerTable = $this->moduleDataSetup->getTable('customer_entity');
                    $bind = ['default_billing' => $lastAddressId, 'default_shipping' => $lastAddressId];
                    $where = ['entity_id = ?' => $customer->getId()];
                    $this->moduleDataSetup->getConnection()->update($customerTable, $bind, $where);
                }
            }
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
