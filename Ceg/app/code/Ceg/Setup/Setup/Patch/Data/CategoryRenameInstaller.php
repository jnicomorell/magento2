<?php
declare(strict_types=1);

namespace Ceg\Setup\Setup\Patch\Data;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Psr\Log\LoggerInterface;

class CategoryRenameInstaller implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var WriterInterface
     */
    private $writerInterface;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param WriterInterface $writerInterface
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManager $storeManager
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        WriterInterface $writerInterface,
        CategoryRepositoryInterface $categoryRepository,
        StoreManager $storeManager,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->writerInterface = $writerInterface;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $categoryId = 3;
        $stores = $this->storeManager->getStores(true);
        $defaultStoreView = $this->storeManager->getDefaultStoreView();

        foreach ($stores as $store) {
            $this->updateCategory($categoryId, $store->getId());
        }

        // @ref https://github.com/magento/magento2/issues/15215
        $this->storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);
        $this->updateCategory($categoryId, $defaultStoreView->getId());

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @param $categoryId
     * @param $storeId
     */
    public function updateCategory($categoryId, $storeId)
    {
        try {
            $category = $this->categoryRepository->get($categoryId, $storeId);
            $category->setName('Catálogo');
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
