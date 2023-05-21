<?php
declare(strict_types=1);

namespace Ceg\Impo\Plugin;

class CatalogLayer
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Ceg\Impo\Api\ImpoRepositoryInterfaceFactory
     */
    private $impoRepoFactory;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Ceg\Impo\Api\ImpoRepositoryInterfaceFactory $impoRepoFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ceg\Impo\Api\ImpoRepositoryInterfaceFactory $impoRepoFactory
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->impoRepoFactory = $impoRepoFactory;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundPrepareProductCollection(
        \Magento\Catalog\Model\Layer $subject,
        \Closure $proceed,
        $collection
    ) {
        $moduleName = $this->request->getModuleName();
        if ($moduleName === 'impo') {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            $impoRepository = $this->impoRepoFactory->create();
            $collection = $impoRepository->filterCollectionByActiveImpo($collection, $websiteId);
        }
        return $proceed($collection);
    }
}
