<?php
declare(strict_types=1);

namespace Ceg\OrdersIntegration\Cron;

use Ceg\OrdersIntegration\Helper\DataFactory as HelperFactory;
use Ceg\OrdersIntegration\Model\QueueRepositoryFactory;
use Ceg\OrdersIntegration\Api\IntegrationRepositoryInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterfaceFactory;
use Psr\Log\LoggerInterfaceFactory;

class ResendQuoteToOrders
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
     * @var CartRepositoryInterfaceFactory
     */
    private $cartRepoFactory;

    /**
     * @var IntegrationRepositoryInterfaceFactory
     */
    private $intRepoFactory;

    /**
     * @var LoggerInterfaceFactory
     */
    private $loggerFactory;

    /**
     * QuoteToOrderCronjob constructor.
     * @param HelperFactory $helperFactory
     * @param QueueRepositoryFactory $queueRepoFactory
     * @param CartRepositoryInterfaceFactory $cartRepoFactory
     * @param IntegrationRepositoryInterfaceFactory $intRepoFactory
     * @param LoggerInterfaceFactory $loggerFactory
     */
    public function __construct(
        HelperFactory $helperFactory,
        QueueRepositoryFactory $queueRepoFactory,
        CartRepositoryInterfaceFactory $cartRepoFactory,
        IntegrationRepositoryInterfaceFactory $intRepoFactory,
        LoggerInterfaceFactory $loggerFactory
    ) {
        $this->helperFactory = $helperFactory;
        $this->queueRepoFactory = $queueRepoFactory;
        $this->cartRepoFactory = $cartRepoFactory;
        $this->intRepoFactory = $intRepoFactory;
        $this->loggerFactory = $loggerFactory;
    }

    public function execute()
    {
        $helper = $this->helperFactory->create();
        if ($helper->isActive()) {
            $queueRepository = $this->queueRepoFactory->create();
            $pendingQueues = $queueRepository->getPendingQueues();
            if ($pendingQueues->count() > 0) {
                $cartRepository = $this->cartRepoFactory->create();
                $integrationRepo = $this->intRepoFactory->create();
                $logger = $this->loggerFactory->create();
                foreach ($pendingQueues as $queue) {
                    try {
                        $quote = $cartRepository->get($queue->getQuoteId());
                        $integrationRepo->resendOrder($queue, $quote);
                    } catch (\Exception $exception) {
                        $message = 'Something went wrong while resend Queue # %1: %2';
                        $logger->critical(__($message, $queue->getQuoteId(), $exception->getMessage()));
                    }
                }
            }
        }
    }
}
