<?php

namespace Ceg\Setup\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;
use Exception;
use Magento\Framework\App\State;

class ProductQtyInBoxInstaller implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollection;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var State;
     */
    protected $state;
    /**
     * @param ModuleDataSetupInterface   $moduleDataSetup
     * @param ProductCollectionFactory   $productCollection
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface            $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ProductCollectionFactory $productCollection,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger,
        State $state
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->productCollection = $productCollection;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

        $products = $this->getProductCollection();

        foreach ($products as $productData) {
            try {
                $product = $this->productRepository->getById($productData->getId());
                $product->setCustomAttribute('qtyinbox', 1);
                $product->save();
            } catch (Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function getProductCollection()
    {
        $collection = $this->productCollection->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('qtyinbox',['eq' => 0]);
        $collection->addAttributeToFilter('qtyinbox',['eq' => null]);
        return $collection->load();
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
