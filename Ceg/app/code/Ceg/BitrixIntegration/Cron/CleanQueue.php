<?php
declare(strict_types=1);

namespace Ceg\BitrixIntegration\Cron;

use Ceg\BitrixIntegration\Helper\DataFactory as HelperFactory;
use Ceg\BitrixIntegration\Model\QueueRepositoryFactory;
use Psr\Log\LoggerInterfaceFactory;

class CleanQueue
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
                        if ($queue->getStatus() == \Ceg\BitrixIntegration\Model\Queue::STATUS_PENDING) {
                            $logger->warning(__('Bitrix Queue # %1 is old but still pending.', $queue->getId()));
                            continue;
                        }
                        $queueRepository->delete($queue);
                    } catch (\Exception $exception) {
                        $message = 'Something went wrong while clean Bitrix Queue # %1: %2';
                        $logger->critical(__($message, $queue->getQueueId(), $exception->getMessage()));
                    }
                }
            }
        }
    }
}
