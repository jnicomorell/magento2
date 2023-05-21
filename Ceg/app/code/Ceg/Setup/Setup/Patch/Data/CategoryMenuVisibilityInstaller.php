<?php
declare(strict_types=1);

namespace Ceg\Setup\Setup\Patch\Data;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Psr\Log\LoggerInterface;

class CategoryMenuVisibilityInstaller implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CollectionFactory
     */
    private $categoryFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        CollectionFactory $categoryFactory,
        CategoryRepositoryInterface $categoryRepository,
        ModuleDataSetupInterface $moduleDataSetup,
        StoreManager $storeManager,
        LoggerInterface $logger
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $stores = $this->storeManager->getStores(true);
        $defaultStoreView = $this->storeManager->getDefaultStoreView();

        $categories = $this->categoryFactory->create()
            ->addFieldToFilter('entity_id', ['nin' => [1, 2, 3]])
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToSelect('is_active');
        foreach ($categories as $item) {
            foreach ($stores as $store) {
                $this->updateCategory($item->getId(), $store->getId(), $item->getIsActive());
            }
            $this->storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);
            $this->updateCategory($item->getId(), $defaultStoreView->getId(), $item->getIsActive());
        }

        $categories = $this->categoryFactory->create()
            ->addFieldToFilter('entity_id', ['nin' => [1, 2, 3]])
            ->addAttributeToFilter('is_active', 0)
            ->addAttributeToSelect('is_active');
        foreach ($categories as $item) {
            foreach ($stores as $store) {
                $this->updateCategory($item->getId(), $store->getId(), $item->getIsActive());
            }
            $this->storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);
            $this->updateCategory($item->getId(), $defaultStoreView->getId(), $item->getIsActive());
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @param $categoryId
     * @param $storeId
     * @param $isActive
     */
    public function updateCategory($categoryId, $storeId, $isActive)
    {
        try {
            $category = $this->categoryRepository->get($categoryId, $storeId);
            $category->setIsActive($isActive);
            $category->setIncludeInMenu(0);
            $this->categoryRepository->save($category);
        } catch (NoSuchEntityException $e) {
            $this->logger->debug('Category doesn\'t exists: ' . $e->getMessage());
        } catch (CouldNotSaveException $e) {
            $this->logger->debug('Can\'t save category: ' . $e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies() :array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases() :array
    {
        return [];
    }
}
