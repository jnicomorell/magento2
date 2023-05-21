<?php
declare(strict_types=1);

namespace Ceg\OrdersIntegration\Model;

use Ceg\OrdersIntegration\Api\QueueRepositoryInterface;
use Ceg\OrdersIntegration\Api\Data\QueueInterfaceFactory;
use Ceg\OrdersIntegration\Api\Data\QueueInterface;
use Ceg\OrdersIntegration\Model\ResourceModel\QueueFactory as QueueResourceFactory;
use Ceg\OrdersIntegration\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;

use Exception;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

class QueueRepository implements QueueRepositoryInterface
{
    /**
     * @var QueueInterfaceFactory
     */
    protected $queueFactory;

    /**
     * @var QueueCollectionFactory
     */
    protected $queueCollFactory;

    /**
     * @var QueueResourceFactory
     */
    protected $queueResFactory;

    /**
     * @var TimezoneInterface
     */
    private $timezoneInterface;

    /**
     * @param QueueInterfaceFactory $queueFactory
     * @param QueueResourceFactory $queueResFactory
     * @param QueueCollectionFactory $queueCollFactory
     * @param TimezoneInterface $timezoneInterface
     */
    public function __construct(
        QueueInterfaceFactory $queueFactory,
        QueueResourceFactory $queueResFactory,
        QueueCollectionFactory $queueCollFactory,
        TimezoneInterface $timezoneInterface
    ) {
        $this->queueFactory = $queueFactory;
        $this->queueResFactory = $queueResFactory;
        $this->queueCollFactory = $queueCollFactory;
        $this->timezoneInterface = $timezoneInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function getByQuoteId($quoteId)
    {
        $queue = $this->queueFactory->create();
        $queueResource = $this->queueResFactory->create();
        $queueResource->load($queue, $quoteId, QueueInterface::QUOTE_ID);
        return $queue;
    }

    /**
     * {@inheritdoc}
     */
    public function save(QueueInterface $queue)
    {
        try {
            $queueResource = $this->queueResFactory->create();
            $queueResource->save($queue);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__('Could not save the Queue: %1.', $exception->getMessage()));
        }
        return $queue;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(QueueInterface $queue)
    {
        try {
            $queueResource = $this->queueResFactory->create();
            $queueResource->delete($queue);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    public function getPendingQueues()
    {
        $queueCollection = $this->queueCollFactory->create();
        $queueCollection->addFieldToFilter(QueueInterface::STATUS, ['eq' => QueueInterface::STATUS_PENDING]);

        return $queueCollection;
    }

    public function getOldQueues($daysAgo)
    {
        $format = 'Y-m-d 0:0:0';
        $daysAgo = '-'.$daysAgo.' day';
        $today = $this->timezoneInterface->date()->format($format);
        $toDate = date(strtotime($daysAgo, strtotime($today)), $format);

        $queueCollection = $this->queueCollFactory->create();
        $queueCollection->addFieldToFilter(QueueInterface::DATETIME, ['lteq' => $toDate]);

        return $queueCollection;
    }
}
