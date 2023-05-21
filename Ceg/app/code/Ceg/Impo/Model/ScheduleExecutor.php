<?php
declare(strict_types=1);

namespace Ceg\Impo\Model;

use Ceg\StagingSchedule\Api\ExecutorInterface;
use Ceg\Quote\Rewrite\Magento\Quote\Model\Quote;
use Ceg\Impo\Api\ImpoRepositoryInterfaceFactory;
use Ceg\Core\Logger\Logger as CegLogger;
use Ceg\Impo\Model\ResourceModel\Impo\RelatedProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory as QuoteItemCollectionFactory;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Quote\Api\CartRepositoryInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Ceg\Impo\Helper\DataFactory as HelperFactory;

class ScheduleExecutor implements ExecutorInterface
{
    const ENTITY = 'IMPO';

    /** @var ImpoRepositoryInterfaceFactory */
    protected $impoRepoFactory;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var QuoteItemCollectionFactory */
    protected $itemCollFactory;

    /** @var QuoteCollectionFactory */
    protected $quoteCollFactory;

    /** @var CartRepositoryInterfaceFactory */
    protected $quoteRepoFactory;

    /** @var CegLogger */
    private $cegLogger;

    /**
     * @var RelatedProductFactory
     */
    protected $productFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;
    protected HelperFactory $helperFactory;

    /**
     * @param ImpoRepositoryInterfaceFactory $impoRepoFactory
     * @param StoreManagerInterface $storeManager
     * @param QuoteItemCollectionFactory $itemCollFactory
     * @param QuoteCollectionFactory $quoteCollFactory
     * @param CartRepositoryInterfaceFactory $quoteRepoFactory
     * @param CegLogger $cegLogger
     * @param RelatedProductFactory $productFactory
     * @param ProductRepository $productRepository
     */
    public function __construct(
        ImpoRepositoryInterfaceFactory $impoRepoFactory,
        StoreManagerInterface $storeManager,
        QuoteItemCollectionFactory $itemCollFactory,
        QuoteCollectionFactory $quoteCollFactory,
        CartRepositoryInterfaceFactory $quoteRepoFactory,
        CegLogger $cegLogger,
        RelatedProductFactory $productFactory,
        ProductRepository $productRepository,
        HelperFactory $helperFactory
    ) {
        $this->impoRepoFactory = $impoRepoFactory;
        $this->storeManager = $storeManager;
        $this->itemCollFactory = $itemCollFactory;
        $this->quoteCollFactory = $quoteCollFactory;
        $this->quoteRepoFactory = $quoteRepoFactory;
        $this->cegLogger = $cegLogger;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->helperFactory = $helperFactory;
    }

    public function executeStart($entityId, array $params)
    {
        $impoRepository = $this->impoRepoFactory->create();
        $impo = $impoRepository->getById($entityId);
        $cegId = $impo->getCegId();

        $message = __('Impo %1 (Ceg Id %2): START: has been opened ', $entityId, $cegId);
        $this->cegLogger->info($message);

        $impoRepository->open($impo);
        $impoRepository->save($impo);
        $this->productSettings($impo, 'start');

        $message = __('Impo %1 (Ceg Id %2): FINISH', $entityId, $cegId);
        $this->cegLogger->info($message);
        return $this;
    }

    public function executeStop($entityId, array $params)
    {
        $quoteRepository = $this->quoteRepoFactory->create();
        $impoRepository = $this->impoRepoFactory->create();

        $impo = $impoRepository->getById($entityId);
        $cegId = $impo->getCegId();

        $quotes = [];
        $impo->setIsActive(false);
        $impoRepository->closed($impo);
        $impoRepository->save($impo);
        $this->clearCache();
        $this->productSettings($impo, 'stop');
        $message = __('Impo %1 (Ceg Id %2): START: has been set inactive', $entityId, $cegId);
        $this->cegLogger->info($message);

        $itemCollection = $this->getQuoteItemsByImpoId($entityId);
        foreach ($itemCollection as $item) {
            $quotes = $this->prepareElements($quotes, $item);
        }
        foreach ($quotes as $quote) {
            $quoteRepository->save($quote);
            $this->cegLogger->info($quote->getProcessMessage());
            foreach ($quote->getProcessItemMessages() as $itemMessage) {
                $this->cegLogger->info($itemMessage);
            }
        }
        $this->clearCache();
        $message = __('Impo %1 (Ceg Id %2): FINISH', $entityId, $cegId);
        $this->cegLogger->info($message);
        return $this;
    }

    public function executeUpdate($entityId, array $params)
    {
        $impoRepository = $this->impoRepoFactory->create();

        $impo = $impoRepository->getById($entityId);
        $cegId = $impo->getCegId();

        $this->updateProductSettings($params['toAdd'], 1);
        $this->updateProductSettings($params['toRemove'], 0);

        $this->clearCache();
        $message = __('Impo %1 (Ceg Id %2): UPDATE: has been updated', $entityId, $cegId);
        $this->cegLogger->info($message);
        return $this;
    }

    public function getQuoteItemsByImpoId($impoId, $isactive = 1)
    {
        $itemCollection = $this->itemCollFactory->create();
        $quoteCollection = $this->quoteCollFactory->create();

        $itemCollection->addFieldToFilter(\Ceg\Impo\Api\Data\ImpoInterface::QUOTEITEM_IMPO_ID, $impoId);
        $itemCollection->setOrder('main_table.quote_id');

        $condition1 = vsprintf('%s.is_active = '.$isactive, [$quoteCollection->getMainTable()]);
        $condition2 = vsprintf('main_table.quote_id = %s.entity_id', [$quoteCollection->getMainTable()]);
        $itemCollection->getSelect()->join(
            [$quoteCollection->getMainTable()],
            vsprintf('(%s) AND (%s)', [$condition1, $condition2]),
            ['quote_status' =>  vsprintf('%s.status', [$quoteCollection->getMainTable()])]
        );
        return $itemCollection;
    }

    public function prepareElements($quotes, $item)
    {
        $quoteId = $item->getQuoteId();
        if (empty($quotes[$quoteId])) {
            $quoteRepository = $this->quoteRepoFactory->create();
            $quotes[$quoteId] = $quoteRepository->get($quoteId);
        }
        $quote = $quotes[$quoteId];
        switch ($quote->getStatus()) {
            case Quote::STATUS_NEW:
            case Quote::STATUS_OPEN:
            case Quote::STATUS_CLONED:
            case Quote::STATUS_REOPEN:
                $quote->removeItem($item->getId());
                $quote->setParentQuoteId(null);
                $quote->setImpoIds(null);
                $quote->setStatus(Quote::STATUS_NEW);
                break;
        }
        $quotes[$quoteId] = $this->setQuoteMessages($quote, $item);
        return $quotes;
    }

    public function setQuoteMessages($quote, $item)
    {
        $itemId = $item->getId();
        $quoteId = $quote->getId();
        $status = $quote->getStatus();
        $itemMessages = empty($quote->getProcessItemMessages()) ? [] : $quote->getProcessItemMessages();
        switch ($status) {
            case Quote::STATUS_NEW:
            case Quote::STATUS_OPEN:
            case Quote::STATUS_CLONED:
            case Quote::STATUS_REOPEN:
                $quote->setProcessMessage(__('Quote %1 (%2): has been cleaned', $quoteId, $status));
                array_push($itemMessages, __('Quote %1: Item %2 : has been removed', $quoteId, $itemId));
                break;
        }
        $quote->setProcessItemMessages($itemMessages);
        return $quote;
    }

    protected function productSettings($impo, $type)
    {
        $relatedProduct = $this->productFactory->create();
        $products = $relatedProduct->getRelatedProductIds($impo);
        $isInImpo = ($type=='start') ? 1 : 0;
        foreach ($products as $pid) {
            $product=$this->productRepository->getById($pid);
            $product->setCustomAttribute('is_in_impo', $isInImpo);
            $product->save();
        }
    }

    protected function updateProductSettings($products, $isInImpo)
    {
        foreach ($products as $pid) {
            $product=$this->productRepository->getById($pid);
            $product->setCustomAttribute('is_in_impo', $isInImpo);
            $product->save();
        }
    }

    protected function clearCache() {
        $helper = $this->helperFactory->create();
        $helper->clearCache([
            'config',
            'layout',
            'block_html',
            'collections',
            'reflection',
            'db_ddl',
            'eav',
            'config_integration',
            'config_integration_api',
            'full_page',
            'translate',
            'config_webservice'
        ]);
    }
}
