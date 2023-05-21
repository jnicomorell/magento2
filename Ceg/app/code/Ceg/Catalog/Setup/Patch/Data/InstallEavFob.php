<?php

namespace Ceg\Catalog\Setup\Patch\Data;

use Magento\Catalog\Model\Product\Attribute\Backend\Price;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;;
use Psr\Log\LoggerInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use \Magento\Framework\Exception\LocalizedException;
/**
 * Class InstallBudgetedCost
 *
 * @package Vendor\Module\Setup\Patch\Data
 */
class InstallEavFob implements DataPatchInterface, PatchRevertableInterface
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

    /**
     * @var LoggerInterface
     */
    private $logger;

    protected $productCollectionFactory;

    /** @var \Magento\Framework\App\State **/
    private $state;


    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        ProductCollectionFactory $productCollectionFactory,
        LoggerInterface $logger,
        \Magento\Framework\App\State $state
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->logger = $logger;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'fob',
            [
                'type' => 'decimal',
                'label' => 'FOB',
                'input' => 'price',
                'backend' => Price::class,
                'required' => false,
                'user_defined' => true,
                'position' => 5,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'apply_to' => 'simple,virtual',
                'group' => 'Advanced Pricing',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => true,
            ]
        );

        $this->updateFobValues();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'budgeted_cost');

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }

    public function updateFobValues()
    {
        try {
            $productCollection = $this->productCollectionFactory->create()->addAttributeToSelect('*');
            foreach ($productCollection as $item) {
                $item->setFob($item->getCost());
            }
            $productCollection->save();

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

}
