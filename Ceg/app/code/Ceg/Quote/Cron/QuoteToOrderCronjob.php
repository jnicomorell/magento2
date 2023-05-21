<?php
declare(strict_types=1);

namespace Ceg\Quote\Cron;

use Exception;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteToOrderCronjob
{
    /**
     * @var CartRepositoryInterface|null
     */
    protected $cartRepository;

    /**
     * @var SearchCriteriaInterface|null
     */
    protected $criteriaBuilder;

    /**
     * @var FilterGroup|null
     */
    protected $filterGroupBuilder;

    /**
     * @var FilterBuilder|null
     */
    protected $filterBuilder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CartManagementInterface
     */
    protected $cartInterface;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * QuoteToOrderCronjob constructor.
     * @param CartManagementInterface $cartInterface
     * @param CartRepositoryInterface|null $cartRepository
     * @param FilterGroupBuilder|null $filterGroup
     * @param SearchCriteriaBuilder|null $criteriaBuilder
     * @param FilterBuilder|null $filterBuilder
     * @param LoggerInterface $logger
     * @param QuoteFactory $quoteFactory
     * @param DateTimeFactory $dateTimeFactory
     * @param EventManager $eventManager
     */
    public function __construct(
        CartManagementInterface $cartInterface,
        CartRepositoryInterface $cartRepository,
        FilterGroupBuilder $filterGroup,
        SearchCriteriaBuilder $criteriaBuilder,
        FilterBuilder $filterBuilder,
        LoggerInterface $logger,
        QuoteFactory $quoteFactory,
        DateTimeFactory $dateTimeFactory,
        EventManager $eventManager
    ) {
        $this->cartInterface = $cartInterface;
        $this->cartRepository = $cartRepository;
        $this->filterGroupBuilder = $filterGroup;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->logger = $logger;
        $this->quoteFactory = $quoteFactory;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->eventManager = $eventManager;
    }

    /**
     * Cronjob Description
     *
     * @return void
     * @throws Exception
     */
    public function execute()
    {
        $quotes = $this->getPendingQuotes();
        $date = $this->dateTimeFactory->create()->gmtDate('Y-m-d H:m:s');

        foreach ($quotes->getItems() as $quote) {
            try {
                if (count($quote->getAllVisibleItems()) == 0) {
                    $quote->cancel();
                    continue;
                }
                $lastOrderId = $this->cartInterface->placeOrder($quote->getId(), $quote->getPayment());
                $this->eventManager->dispatch('ceg_save_order_quote_in_bitrix_queue', ['quote' => $quote]);
                $this->logger->info('Order created by cronjob: ' . $lastOrderId);
                $quote->setConvertedAt($date)->save();
            } catch (LocalizedException $exception) {
                $quoteId = $quote->getId();
                $msg = $exception->getMessage();
                $this->logger->critical(
                    __('Something went wrong while converting Quote ID '.$quoteId.' to Order: '.$msg)
                );
            }
        }
    }

    protected function getPendingQuotes()
    {
        $statuses = [
            \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_CLOSED
        ];
        $quoteCollection = $this->quoteFactory->create();
        $quoteCollection->addFieldToSelect('*');
        $quoteCollection->addFieldToFilter('main_table.converted_at', ['null' => true]);
        $quoteCollection->addFieldToFilter('main_table.status', ['in' => $statuses]);
        $quoteCollection->setOrder('main_table.entity_id', 'asc');

        $quoteCollection->getSelect()
            ->joinLeft(
                ["order" => $quoteCollection->getTable('sales_order')],
                "main_table.reserved_order_id = order.increment_id",
                ["increment_id" => "order.increment_id"]
            )->columns(
                "order.increment_id"
            )->where("IFNULL(reserved_order_id,'') != IFNULL(increment_id,'')");

        return  $quoteCollection->load();
    }
}
