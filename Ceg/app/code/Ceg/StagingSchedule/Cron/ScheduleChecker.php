<?php
declare(strict_types=1);

namespace Ceg\StagingSchedule\Cron;

use Ceg\Core\Helper\Datetime as DatetimeHelper;
use Ceg\StagingSchedule\Api\Data\StagingScheduleInterface;
use Ceg\StagingSchedule\Api\ExecutorInterface;
use Ceg\StagingSchedule\Logger\Logger;
use Ceg\StagingSchedule\Model\StagingScheduleRepositoryFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Ceg\StagingSchedule\Helper\Scheduler;

class ScheduleChecker
{
    /** @var StagingScheduleRepositoryFactory */
    protected $scheduleRepoFac;

    /** @var DatetimeHelper */
    protected $datetimeHelper;

    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var Logger */
    protected $logger;

    /** @var Json */
    protected $json;

    /** @var Scheduler */
    private $scheduler;

    /**
     * @param StagingScheduleRepositoryFactory $scheduleRepoFac
     * @param DatetimeHelper                   $datetimeHelper
     * @param ObjectManagerInterface           $objectManager
     * @param Logger                           $logger
     * @param Json                             $json
     * @param Scheduler                        $scheduler
     */
    public function __construct(
        StagingScheduleRepositoryFactory $scheduleRepoFac,
        DatetimeHelper $datetimeHelper,
        ObjectManagerInterface $objectManager,
        Logger $logger,
        Json $json,
        Scheduler $scheduler
    ) {
        $this->scheduleRepoFac = $scheduleRepoFac;
        $this->datetimeHelper = $datetimeHelper;
        $this->objectManager = $objectManager;
        $this->logger = $logger;
        $this->json = $json;
        $this->scheduler = $scheduler;
    }

    /**
     * Execute the cron
     * @return void
     */
    public function execute()
    {
        $this->logger->info(__("Schedule Checker: START"));
        try {
            $scheduleRepo = $this->scheduleRepoFac->create();

            $runningSchedule = $scheduleRepo->getRunningSchedules($this->datetimeHelper->getNowUtc("-1 Hour"));
            foreach ($runningSchedule as $schedule) {
                $this->saveSchedule($schedule, StagingScheduleInterface::STATUS_PENDING);
            }

            $scheduleList = $scheduleRepo->getPendingSchedules();
            foreach ($scheduleList->getItems() as $schedule) {

                $currentDateTime = $this->datetimeHelper->getNowUtc();
                $scheduleDateTime = $this->datetimeHelper->convertStringToDatetime($schedule->getDatetime());
                if ($currentDateTime >= $scheduleDateTime) {

                    $entity = $schedule->getEntity();
                    $entityId = $schedule->getEntityId();
                    $instance = $schedule->getInstance();
                    $action = $schedule->getAction();
                    try {
                        $this->scheduler->lockNewScheduleFor($entity, $entityId);

                        $paramsArr = $this->json->unserialize($schedule->getParams());
                        $callback = $this->getCallback($instance, $action);

                        $this->saveSchedule($schedule, StagingScheduleInterface::STATUS_RUNNING);

                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        call_user_func_array($callback, [$entityId, $paramsArr]);

                        $this->saveSchedule($schedule, StagingScheduleInterface::STATUS_PROCESSED);

                        $this->scheduler->unlockNewSchedule();

                    } catch (\Exception $exception) {
                        $message = $exception->getMessage();
                        $this->saveSchedule($schedule, StagingScheduleInterface::STATUS_PROCESSED, $message);

                        $logMessage = __("Schedule Checker: ERROR - Entity %1 (%2) - %3", $entity, $entityId, $message);
                        $this->logger->error($logMessage);
                    }
                }
            }
        } catch (\Exception $exception) {
            $message = __("Schedule Checker: ERROR - %1", $exception->getMessage());
            $this->logger->error($message);
        }
        $message = __(__("Schedule Checker: FINISH"));
        $this->logger->info($message);
    }

    public function saveSchedule($schedule, $status, $message = '')
    {
        $scheduleRepo = $this->scheduleRepoFac->create();
        $schedule->setStatus($status);
        $schedule->setMessage($message);
        $scheduleRepo->save($schedule);
    }

    public function getCallback($instance, $action)
    {
        $model = $this->objectManager->create($instance);

        $method = '';
        switch ($action) {
            case StagingScheduleInterface::ACTION_START:
                $method = ExecutorInterface::METHOD_START;
                break;
            case StagingScheduleInterface::ACTION_STOP:
                $method = ExecutorInterface::METHOD_STOP;
                break;
            case StagingScheduleInterface::ACTION_UPDATE:
                $method = ExecutorInterface::METHOD_UPDATE;
                break;
        }

        $callback = [$model, $method];
        if (!is_callable($callback)) {
            $message = __('Invalid callback: %1::%2 can\'t be called', $instance, $method);
            throw new LocalizedException($message);
        }
        return $callback;
    }
}
