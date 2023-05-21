<?php

namespace Ceg\Catalog\Setup\Patch\Data;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Ceg\Impo\Model\ResourceModel\Impo\CollectionFactory as ImpoCollectionFactory;
use Ceg\Impo\Model\ResourceModel\Impo\RelatedProduct\CollectionFactory as ImpoProductCollectionFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

/**
 * Class InstallBudgetedCost
 *
 * @package Vendor\Module\Setup\Patch\Data
 */
class InstallLastImpoDate implements DataPatchInterface, PatchRevertableInterface
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
    protected $impoCollectionFactory;
    protected $impoProductCollectionFactory;

    /** @var State **/
    protected State $state;
    protected ProductRepositoryInterface $productRepository;


    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        ImpoCollectionFactory $impoCollectionFactory,
        ImpoProductCollectionFactory $impoProductCollectionFactory,
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        State $state
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->impoProductCollectionFactory = $impoProductCollectionFactory;
        $this->impoCollectionFactory = $impoCollectionFactory;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            'last_impo_date',
            [
                'type' => 'datetime',
                'label' => 'Last Impo Date',
                'input' => 'date',
                'required' => false,
                'user_defined' => true,
                'position' => 5,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to' => 'simple,virtual',
                'group' => 'Product Details',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => true,
                'used_in_product_listing' => true,
            ]
        );
        $this->updateLastImpoDate();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(Product::ENTITY, 'last_impo_date');
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
        return [

        ];
    }

    /**
     * @throws NoSuchEntityException
     */
    public function updateLastImpoDate()
    {
        try {
            $impoProductCollection = $this->impoProductCollectionFactory->create()->addFieldToSelect('*')
                ->setOrder('impo_id', 'ASC');
            foreach($impoProductCollection as $impoProduct) {
                $product = $this->productRepository->getById($impoProduct->getData('product_id'));
                if($product) {
                    $impoCollection = $this->impoCollectionFactory->create()->addFieldToSelect('*')
                        ->addFieldToFilter('entity_id', $impoProduct->getData('impo_id'));
                    foreach($impoCollection as $impo) {
                        $product->setLastImpoDate($impo->getData('start_at'));
                    }
                    $this->productRepository->save($product);
                }
            }
        } catch(NoSuchEntityException | CouldNotSaveException | InputException | StateException $e) {
            $this->logger->debug($e->getMessage());
        }
    }
}
