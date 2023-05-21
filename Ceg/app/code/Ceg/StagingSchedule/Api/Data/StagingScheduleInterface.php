<?php
declare(strict_types=1);

namespace Ceg\StagingSchedule\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface StagingScheduleInterface extends ExtensibleDataInterface
{
    const STAGING_ID = 'staging_id';
    const ENTITY_ID = 'entity_id';
    const MESSAGE = 'message';
    const ACTION = 'action';
    const ENTITY = 'entity';
    const INSTANCE = 'instance';
    const PARAMS = 'params';
    const STATUS = 'status';
    const DATETIME = 'datetime';

    const ACTION_START = 'start';
    const ACTION_STOP = 'stop';
    const ACTION_UPDATE = 'update';

    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_PROCESSED = 'processed';

    /**
     * Get staging_id
     * @return string|null
     */
    public function getStagingId();

    /**
     * @param string $stagingId
     * @return StagingScheduleInterface
     */
    public function setStagingId($stagingId);

    /**
     * Get entity
     * @return string|null
     */
    public function getEntity();

    /**
     * Set entity
     * @param string $entity
     * @return StagingScheduleInterface
     */
    public function setEntity($entity);

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return StagingScheduleInterface
     */
    public function setEntityId($entityId);

    /**
     * Get action
     * @return string|null
     */
    public function getAction();

    /**
     * Set action
     * @param string $action
     * @return StagingScheduleInterface
     */
    public function setAction($action);

    /**
     * Get datetime
     * @return string|null
     */
    public function getDatetime();

    /**
     * Set datetime
     * @param string $datetime
     * @return StagingScheduleInterface
     */
    public function setDatetime($datetime);

    /**
     * Get params
     * @return string|null
     */
    public function getParams();

    /**
     * Set params
     * @param string $params
     * @return StagingScheduleInterface
     */
    public function setParams($params);

    /**
     * Get instance
     * @return string|null
     */
    public function getInstance();

    /**
     * Set instance
     * @param string $instance
     * @return StagingScheduleInterface
     */
    public function setInstance($instance);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return StagingScheduleInterface
     */
    public function setStatus($status);

    /**
     * Get message
     * @return string|null
     */
    public function getMessage();

    /**
     * Set message
     * @param string $message
     * @return StagingScheduleInterface
     */
    public function setMessage($message);
}
