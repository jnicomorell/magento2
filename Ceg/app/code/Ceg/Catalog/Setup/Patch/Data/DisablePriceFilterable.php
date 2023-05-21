<?php

namespace Ceg\Catalog\Setup\Patch\Data;

use Magento\Eav\Model\Config;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

class DisablePriceFilterable implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;
    private $eavConfig;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetup $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetup $eavSetupFactory,
        Config $eavConfig
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavAttribute = $this->eavConfig->getAttribute('catalog_product', 'price');
        $this->eavSetupFactory->updateAttribute($eavAttribute->getEntityTypeId(), $eavAttribute->getAttributeId(), 'is_filterable', '0');
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavAttribute = $this->eavConfig->getAttribute('catalog_product', 'price');
        $this->eavSetupFactory->updateAttribute($eavAttribute->getEntityTypeId(), $eavAttribute->getAttributeId(), 'is_filterable', '1');
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
