<?php
declare(strict_types=1);

namespace Ceg\Setup\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Setup\CustomerSetupFactory;

class ContactIdAttributeCustomer implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * InstallData constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * @return CustomerGroupsInstaller|void
     * @throws \Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        try {
            $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

            $contactId = $customerSetup->addAttribute(
                Customer::ENTITY,
                'contact_id',
                [
                    'type' => 'int',
                    'label' => 'Contact ID',
                    'input' => 'text',
                    'source' => '',
                    'required' => false,
                    'visible' => true,
                    'position' => 500,
                    'system' => false,
                    'backend' => ''
                ]
            );

            $contactIdAttribute = $contactId->getEavConfig()
                ->getAttribute(
                    Customer::ENTITY,
                    'contact_id'
                )
                ->addData(
                    ['used_in_forms' => [
                        'adminhtml_customer',
                        'adminhtml_checkout',
                        'customer_account_create',
                        'customer_account_edit'
                    ]
                    ]
                );

            $contactIdAttribute->save();

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
