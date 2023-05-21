<?php
declare(strict_types=1);

namespace Ceg\Impo\Plugin;

use Ceg\Impo\Api\ImpoRepositoryInterfaceFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Smile\ElasticsuiteCore\Search\Request\Builder;

class QueryBuilder
{
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ImpoRepositoryInterfaceFactory
     */
    private $impoRepoFactory;

    /**
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     * @param ImpoRepositoryInterfaceFactory $impoRepoFactory
     */
    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        ImpoRepositoryInterfaceFactory $impoRepoFactory
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->impoRepoFactory = $impoRepoFactory;
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function beforeCreate(
        Builder $subject,
        $storeId,
        $containerName,
        $from,
        $size,
        $query = null,
        $sortOrders = [],
        $filters = [],
        $queryFilters = [],
        $facets = []
    ) {
        $moduleName = $this->request->getModuleName();
        if ($moduleName === 'impo') {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            $impoRepository = $this->impoRepoFactory->create();
            $productIds = [0];
            foreach ($impoRepository->getProductIdsForActiveImpo($websiteId) as $productId => $impoId) {
                if (!in_array($productId, $productIds)) {
                     array_push($productIds, $productId);
                }
            }
            $filters['entity_id'] = ['in' => $productIds];
        }
        return [$storeId, $containerName, $from, $size, $query, $sortOrders, $filters, $queryFilters, $facets];
    }
}
