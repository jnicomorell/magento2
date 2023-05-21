<?php
declare(strict_types=1);

namespace Ceg\StagingSchedule\Helper;

use Ceg\Core\Helper\Datetime as DatetimeHelper;
use Ceg\StagingSchedule\Api\ExecutorInterface;
use Ceg\StagingSchedule\Logger\Logger;
use Ceg\StagingSchedule\Api\Data\StagingScheduleInterface;
use Ceg\StagingSchedule\Model\Executor\Demo;
use Ceg\StagingSchedule\Model\StagingScheduleRepositoryFactory;
use Ceg\StagingSchedule\Api\Data\StagingScheduleInterfaceFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;

class Scheduler extends AbstractHelper
{
    /** @var StagingScheduleRepositoryFactory */
    protected $scheduleRepoFac;

    /** @var StagingScheduleInterfaceFactory */
    protected $scheduleFac;

    /** @var DatetimeHelper */
    protected $datetimeHelper;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var Logger */
    protected $logger;

    /** @var Json */
    protected $json;

    private $currentExecutionEntity = '';
    private $currentExecutionEntityId = '';

    /**
     * @param Context                          $context
     * @param StagingScheduleRepositoryFactory $scheduleRepoFac
     * @param StagingScheduleInterfaceFactory  $scheduleFac
     * @param DatetimeHelper                   $datetimeHelper
     * @param ObjectManagerInterface           $objectManager
     * @param Logger                           $logger
     * @param Json                             $json
     */
    public function __construct(
        Context $context,
        StagingScheduleRepositoryFactory $scheduleRepoFac,
        StagingScheduleInterfaceFactory $scheduleFac,
        DatetimeHelper $datetimeHelper,
        ObjectManagerInterface $objectManager,
        Logger $logger,
        Json $json
    ) {
        parent::__construct($context);
        $this->scheduleRepoFac = $scheduleRepoFac;
        $this->scheduleFac = $scheduleFac;
        $this->datetimeHelper = $datetimeHelper;
        $this->objectManager = $objectManager;
        $this->logger = $logger;
        $this->json = $json;
    }

    /**
     * @param          $websiteId
     * @param          $entity
     * @param          $entityId
     * @param          $startAt
     * @param          $stopAt
     * @param string   $instance
     * @param array    $params
     */
    public function setSchedule(
        $websiteId,
        $entity,
        $entityId,
        $startAt,
        $stopAt,
        $instance = Demo::class,
        Array $params = []
    ) {
        $this->logger->info(__('Set Schedule: %1 (%2)', $entity, $entityId));
        try {
            $this->validateInstance($instance);

            $scheduleRepo = $this->scheduleRepoFac->create();
            $list = $scheduleRepo->getListByEntity($entity, $entityId);

            $action = StagingScheduleInterface::ACTION_START;
            $datetimeUTC = $this->datetimeHelper->convertDateFromWebsiteToUtc($startAt, $websiteId);
            $start = $this->getItem($list, $action, $entity, $entityId, $datetimeUTC, $instance, $params);
            $startSaved = $this->saveSchedule($start);

            $action = StagingScheduleInterface::ACTION_STOP;
            $datetimeUTC = $this->datetimeHelper->convertDateFromWebsiteToUtc($stopAt, $websiteId);
            $stop = $this->getItem($list, $action, $entity, $entityId, $datetimeUTC, $instance, $params);
            $stopSaved = $this->saveSchedule($stop);

            $now = $this->datetimeHelper->getNowUtc();
            if ((!$startSaved && $stopSaved) && ($stopAt > $now) && ($startAt < $now)) {
                $action = StagingScheduleInterface::ACTION_UPDATE;
                $datetimeUTC = $this->datetimeHelper->getNowUtc('+15 Min');
                $update = $this->getItem($list, $action, $entity, $entityId, $datetimeUTC, $instance, $params);
                $this->saveSchedule($update);
                $this->logger->info(__('Set Schedule: %1 (%2) - UPDATE ', $entity, $entityId));
            }
        } catch (\Exception $exception) {
            $this->logger->error(__('Set Schedule: %1 (%2) - ERROR: %3', $entity, $entityId, $exception->getMessage()));
        }
    }

    public function validateInstance($instance)
    {
        $model = $this->objectManager->create($instance);
        if (!$model instanceof ExecutorInterface) {
            $message = __('%1 should be an instance of %2.', $instance, ExecutorInterface::class);
            throw new LocalizedException($message);
        }
    }

    public function getItem($scheduleList, $action, $entity, $entityId, $datetime, $instance, $params)
    {
        $formattedDatetime = $datetime->format('m/d/Y H:i:s');
        $paramsJson = $this->json->serialize($params);

        foreach ($scheduleList->getItems() as $schedule) {
            if ($schedule->getAction() == $action && $schedule->getEntity() == $entity && $schedule->getEntityId() == $entityId) {
                switch ($action) {
                    case StagingScheduleInterface::ACTION_START:
                    case StagingScheduleInterface::ACTION_STOP:
                        $schedule->setInstance($instance);
                        $schedule->setDatetime($formattedDatetime);
                        $schedule->setParams($paramsJson);
                        return $schedule;

                    case StagingScheduleInterface::ACTION_UPDATE:
                        if ($schedule->getStatus() == StagingScheduleInterface::STATUS_PENDING) {
                            $schedule->setInstance($instance);
                            $schedule->setDatetime($formattedDatetime);
                            $schedule->setParams($paramsJson);
                            return $schedule;
                        }
                }
            }
        }

        $schedule = $this->scheduleFac->create();
        $schedule->setStatus(StagingScheduleInterface::STATUS_PENDING);
        $schedule->setAction($action);
        $schedule->setEntity($entity);
        $schedule->setEntityId($entityId);
        $schedule->setInstance($instance);
        $schedule->setDatetime($formattedDatetime);
        $schedule->setParams($paramsJson);

        return $schedule;
    }

    public function saveSchedule($schedule)
    {
        $scheduleRepo = $this->scheduleRepoFac->create();
        if (!$schedule->getStagingId() ||
            ($schedule->getStagingId() && $schedule->getStatus() == StagingScheduleInterface::STATUS_PENDING)) {
            $scheduleRepo->save($schedule);
            return true;
        }
        return false;
    }

    public function lockNewScheduleFor($entity, $entityId)
    {
        $this->currentExecutionEntity = $entity;
        $this->currentExecutionEntityId = $entityId;
    }

    public function unlockNewSchedule()
    {
        $this->currentExecutionEntity = '';
        $this->currentExecutionEntityId = '';
    }

    public function canSaveNewSchedule($entity, $entityId)
    {
        return ($this->currentExecutionEntity != $entity && $this->currentExecutionEntityId != $entityId);
    }
}
