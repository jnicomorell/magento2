<?php
declare(strict_types=1);

namespace Ceg\Quote\Cron;

use Exception;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magento\Sales\Model\ResourceModel\Collection\ExpiredQuotesCollection;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Cron job for cleaning expired Quotes
 */
class CleanExpiredQuotes extends \Magento\Sales\Cron\CleanExpiredQuotes
{
    /**
     * @var ExpiredQuotesCollection
     */
    private $expiredCollection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ExpiredQuotesCollection $expiredCollection
     * @param QuoteRepository $quoteRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ExpiredQuotesCollection $expiredCollection,
        QuoteRepository $quoteRepository,
        LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->expiredCollection = $expiredCollection;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
    }

    /**
     * Clean expired quotes (cron process)
     *
     * @return void
     */
    public function execute()
    {
        $stores = $this->storeManager->getStores(true);
        foreach ($stores as $store) {
            /** @var $quoteCollection QuoteCollection */
            $quoteCollection = $this->expiredCollection->getExpiredQuotes($store);
            $quoteCollection->setPageSize(50);

            // Last page returns 1 even when we don't have any results
            $lastPage = $quoteCollection->getSize() ? $quoteCollection->getLastPageNumber() : 0;

            for ($currentPage = $lastPage; $currentPage >= 1; $currentPage--) {
                $quoteCollection->setCurPage($currentPage);
                //todo redefine quote deletion
                //$this->deleteQuotes($quoteCollection);
            }

            /** @var $approvedCollection ApprovedQuoteCollection */
            $approvedCollection = $this->expiredCollection->getApprovedQuotes($store);
            $approvedCollection->setPageSize(50);

            // Last page returns 1 even when we don't have any results
            $lastPage = $approvedCollection->getSize() ? $approvedCollection->getLastPageNumber() : 0;

            for ($currentPage = $lastPage; $currentPage >= 1; $currentPage--) {
                $approvedCollection->setCurPage($currentPage);
                //todo redefine quote deletion
                //$this->deleteQuotes($approvedCollection);
            }
        }
    }

    /**
     * Deletes all quotes in collection
     *
     * @param QuoteCollection $quoteCollection
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function deleteQuotes(QuoteCollection $quoteCollection): void
    {
        foreach ($quoteCollection as $quote) {
            try {
                $this->quoteRepository->delete($quote);
            } catch (Exception $exception) {
                $message = sprintf(
                    'Unable to delete expired quote (ID: %s): %s',
                    $quote->getId(),
                    (string)$exception
                );
                $this->logger->error($message);
            }
        }

        $quoteCollection->clear();
    }
}
