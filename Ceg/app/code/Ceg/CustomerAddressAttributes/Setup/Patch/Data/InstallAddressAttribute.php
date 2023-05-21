<?php

namespace Ceg\CustomerAddressAttributes\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

/**
 * Class InstallAddressAttribute
 * @package Vendor\Module\Setup\Patch\Data
 */
class InstallAddressAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    protected $attributeSetFactory;

    /**
     * InstallAddressAttribute constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer_address');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $attributesInfo = [
            'numero' => [
                'label' => 'Número',
                'type'  => 'varchar',
                'input' => 'text',
                'source' => '',
                'position' => 80,
                'visible' => true,
                'system' => false,
                'required' => false,
                'user_defined' => true,
            ],
            'colonia' => [
                'label' => 'Colonia',
                'type'  => 'varchar',
                'input' => 'text',
                'source' => '',
                'position' => 81,
                'visible' => true,
                'system' => false,
                'required' => false,
                'user_defined' => true,
            ],
            'observaciones' => [
                'label' => 'Observaciones',
                'type'  => 'varchar',
                'input' => 'text',
                'source' => '',
                'position' => 250,
                'visible' => true,
                'system' => false,
                'required' => false,
                'user_defined' => true,
            ]
        ];

        foreach ($attributesInfo as $attributeCode => $attributeParams) {
            $customerSetup->addAttribute('customer_address', $attributeCode, $attributeParams);
            $customerSetup->addAttributeToSet('customer_address', $attributeSetId, $attributeGroupId, $attributeCode);
            $customerSetup->updateAttribute('customer_address', $attributeCode, 'used_in_forms', [
                'adminhtml_checkout',
                'adminhtml_customer',
                'adminhtml_customer_address',
                'checkout_register',
                'customer_account_create',
                'customer_account_edit',
                'customer_address_edit',
                'customer_register_address'
            ]);
        }
        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }
    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
