<?php
declare(strict_types=1);

namespace Ceg\Auth0Integration\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Setup\CustomerSetupFactory;

class CustomerAttributesInstaller implements DataPatchInterface
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

            $companyId = $customerSetup->addAttribute(
                Customer::ENTITY,
                'company_id',
                [
                    'type' => 'varchar',
                    'label' => 'Company Id',
                    'input' => 'text',
                    'source' => '',
                    'required' => false,
                    'visible' => true,
                    'position' => 500,
                    'system' => false,
                    'backend' => ''
                ]
            );

            $companyName = $customerSetup->addAttribute(
                Customer::ENTITY,
                'company_name',
                [
                    'type' => 'varchar',
                    'label' => 'Company Name',
                    'input' => 'text',
                    'source' => '',
                    'required' => false,
                    'visible' => true,
                    'position' => 500,
                    'system' => false,
                    'backend' => ''
                ]
            );

            $rfc = $customerSetup->addAttribute(
                Customer::ENTITY,
                'rfc',
                [
                    'type' => 'varchar',
                    'label' => 'RFC',
                    'input' => 'text',
                    'source' => '',
                    'required' => false,
                    'visible' => true,
                    'position' => 500,
                    'system' => false,
                    'backend' => ''
                ]
            );

            $disallowedToCheckout = $customerSetup->addAttribute(
                Customer::ENTITY,
                'checkout_disabled',
                [
                    'type' => 'int',
                    'label' => 'Disable Checkout',
                    'input' => 'boolean',
                    'source' => '',
                    'required' => false,
                    'visible' => true,
                    'position' => 0,
                    'system' => false,
                    'backend' => ''
                ]
            );

            $companyIdAttribute = $companyId->getEavConfig()
                ->getAttribute(
                    Customer::ENTITY,
                    'company_id'
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

            $companyNameAttribute = $companyName->getEavConfig()
                ->getAttribute(
                    Customer::ENTITY,
                    'company_name'
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

            $rfcAttribute = $rfc->getEavConfig()
                ->getAttribute(
                    Customer::ENTITY,
                    'rfc'
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

            $disallowedAttribute = $disallowedToCheckout->getEavConfig()
                ->getAttribute(
                    Customer::ENTITY,
                    'checkout_disabled'
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

            $companyIdAttribute->save();
            $companyNameAttribute->save();
            $rfcAttribute->save();
            $disallowedAttribute->save();

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
