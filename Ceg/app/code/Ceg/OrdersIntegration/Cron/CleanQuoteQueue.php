<?php
declare(strict_types=1);

namespace Ceg\OrdersIntegration\Cron;

use Ceg\OrdersIntegration\Helper\DataFactory as HelperFactory;
use Ceg\OrdersIntegration\Model\QueueRepositoryFactory;
use Psr\Log\LoggerInterfaceFactory;

class CleanQuoteQueue
{
    /**
     * @var HelperFactory
     */
    private $helperFactory;

    /**
     * @var QueueRepositoryFactory
     */
    protected $queueRepoFactory;

    /**
     * @var LoggerInterfaceFactory
     */
    private $loggerFactory;

    /**
     * QuoteToOrderCronjob constructor.
     * @param HelperFactory $helperFactory
     * @param QueueRepositoryFactory $queueRepoFactory
     * @param LoggerInterfaceFactory $loggerFactory
     */
    public function __construct(
        HelperFactory $helperFactory,
        QueueRepositoryFactory $queueRepoFactory,
        LoggerInterfaceFactory $loggerFactory
    ) {
        $this->helperFactory = $helperFactory;
        $this->queueRepoFactory = $queueRepoFactory;
        $this->loggerFactory = $loggerFactory;
    }

    public function execute()
    {
        $helper = $this->helperFactory->create();
        if ($helper->isActive()) {
            $queueRepository = $this->queueRepoFactory->create();
            $oldQueues = $queueRepository->getOldQueues();
            if ($oldQueues->count() > 0) {
                $logger = $this->loggerFactory->create();
                foreach ($oldQueues as $queue) {
                    try {
                        if ($queue->getStatus() == \Ceg\OrdersIntegration\Model\Queue::STATUS_PENDING) {
                            $logger->warning(__('Queue # %1 is old but still pending.', $queue->getQuoteId()));
                            continue;
                        }
                        $queueRepository->delete($queue);
                    } catch (\Exception $exception) {
                        $message = 'Something went wrong while resend Queue # %1: %2';
                        $logger->critical(__($message, $queue->getQuoteId(), $exception->getMessage()));
                    }
                }
            }
        }
    }
}
