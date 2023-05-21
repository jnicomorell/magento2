<?php
declare(strict_types=1);

namespace Ceg\BitrixIntegration\Model;

use Ceg\BitrixIntegration\Api\QueueRepositoryInterface;
use Ceg\BitrixIntegration\Api\Data\QueueInterfaceFactory;
use Ceg\BitrixIntegration\Api\Data\QueueInterface;
use Ceg\BitrixIntegration\Model\ResourceModel\QueueFactory as QueueResourceFactory;
use Ceg\BitrixIntegration\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;

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
    public function getByTypeElementId($type, $elementId)
    {
        $queue = $this->queueFactory->create();
        $queueResource = $this->queueResFactory->create();

        $queueCollection = $this->queueCollFactory->create();
        $queueCollection->addFieldToFilter(QueueInterface::TYPE, ['eq' => $type]);
        $queueCollection->addFieldToFilter(QueueInterface::ELEMENT_ID, ['eq' => $elementId]);

        $queueId = 0;
        if ($queueCollection->count() > 0) {
            $queueId = $queueCollection->getFirstItem()->getId();
        }

        $queueResource->load($queue, $queueId, QueueInterface::ID);
        if (!$queue->getId()) {
            $queue->setType($type);
            $queue->setElementId($elementId);
        }

        return $queue;
    }

    /**
     * {@inheritdoc}
     */
    public function save(QueueInterface $queue)
    {
        try {
            $queueResource = $this->queueResFactory->create();
            $datetime = strftime('%Y-%m-%d %H:%M:%S', $this->timezoneInterface->date()->getTimestamp());
            $queue->setDatetime($datetime);
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

    public function getOldQueues($daysAgo = 30)
    {
        $format = 'Y-m-d 0:0:0';
        $daysAgo = sprintf("-%s day", $daysAgo);
        $today = $this->timezoneInterface->date()->format($format);
        $toDate = date($format, strtotime($daysAgo, strtotime($today)));

        $queueCollection = $this->queueCollFactory->create();
        $queueCollection->addFieldToFilter(QueueInterface::DATETIME, ['lteq' => $toDate]);

        return $queueCollection;
    }

    public function getAllQueues()
    {
        $queueCollection = $this->queueCollFactory->create();
        $queueCollection->getSelect()->order('status');
        $queueCollection->getSelect()->order('datetime DESC');
        return $queueCollection;
    }
}
