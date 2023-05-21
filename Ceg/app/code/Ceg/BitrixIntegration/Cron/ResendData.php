<?php
declare(strict_types=1);

namespace Ceg\BitrixIntegration\Cron;

use Ceg\BitrixIntegration\Helper\DataFactory as HelperFactory;
use Ceg\BitrixIntegration\Model\Integration\QueueElementFactory;
use Ceg\BitrixIntegration\Model\QueueRepositoryFactory;
use Ceg\BitrixIntegration\Api\IntegrationRepositoryInterfaceFactory;
use Ceg\Impo\Api\ImpoRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterfaceFactory;

class ResendData
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
     * @var IntegrationRepositoryInterfaceFactory
     */
    private $intRepoFactory;

    /**
     * @var LoggerInterfaceFactory
     */
    private $loggerFactory;

    /**
     * @var QueueElementFactory
     */
    private $elementFactory;

    /**
     * @var ImpoRepositoryInterface
     */
    protected $impoRepository;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param HelperFactory                         $helperFactory
     * @param QueueRepositoryFactory                $queueRepoFactory
     * @param QueueElementFactory                    $elementFactory
     * @param ImpoRepositoryInterface               $impoRepository
     * @param CartRepositoryInterface               $quoteRepository
     * @param IntegrationRepositoryInterfaceFactory $intRepoFactory
     * @param LoggerInterfaceFactory                $loggerFactory
     */
    public function __construct(
        HelperFactory $helperFactory,
        QueueRepositoryFactory $queueRepoFactory,
        QueueElementFactory $elementFactory,
        ImpoRepositoryInterface $impoRepository,
        CartRepositoryInterface $quoteRepository,
        IntegrationRepositoryInterfaceFactory $intRepoFactory,
        LoggerInterfaceFactory $loggerFactory
    ) {
        $this->helperFactory = $helperFactory;
        $this->queueRepoFactory = $queueRepoFactory;
        $this->elementFactory = $elementFactory;
        $this->impoRepository = $impoRepository;
        $this->quoteRepository = $quoteRepository;
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
                $integrationRepo = $this->intRepoFactory->create();
                $logger = $this->loggerFactory->create();
                foreach ($pendingQueues as $queue) {
                    try {
                        $element = $this->getElement($queue);
                        if (is_object($element)) {
                            $element->setType($queue->getType());
                        }
                        $integrationRepo->resendData($element, $queue);
                    } catch (\Exception $exception) {
                        $message = 'Something went wrong while resend Bitrix Queue # %1: %2';
                        $logger->critical(__($message, $queue->getQueueId(), $exception->getMessage()));
                    }
                }
            }
        }
    }

    private function getElement($queue)
    {
        switch ($queue->getType()) {
            case \Ceg\BitrixIntegration\Model\Queue::ELEMENT_TYPE_IMPO:
                $model = $this->impoRepository->getById($queue->getElementId());
                $element = $this->elementFactory->create();
                $element->setModel($model);
                return $element;
            case \Ceg\BitrixIntegration\Model\Queue::ELEMENT_TYPE_ORDER:
                $model = $this->quoteRepository->get($queue->getElementId());
                $element = $this->elementFactory->create();
                $element->setModel($model);
                return $element;
            default:
                return null;
        }
    }
}
