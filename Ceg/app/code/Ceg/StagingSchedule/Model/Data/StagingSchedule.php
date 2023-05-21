<?php
declare(strict_types=1);

namespace Ceg\StagingSchedule\Model\Data;

use Ceg\StagingSchedule\Api\Data\StagingScheduleInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

class StagingSchedule extends AbstractExtensibleObject implements StagingScheduleInterface
{
    /**
     * Get staging_id
     * @return string|null
     */
    public function getStagingId()
    {
        return $this->_get(self::STAGING_ID);
    }

    /**
     * Set staging_id
     * @param string $stagingId
     * @return StagingScheduleInterface
     */
    public function setStagingId($stagingId)
    {
        return $this->setData(self::STAGING_ID, $stagingId);
    }

    /**
     * Get entity
     * @return string|null
     */
    public function getEntity()
    {
        return $this->_get(self::ENTITY);
    }

    /**
     * Set entity
     * @param string $entity
     * @return StagingScheduleInterface
     */
    public function setEntity($entity)
    {
        return $this->setData(self::ENTITY, $entity);
    }

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId()
    {
        return $this->_get(self::ENTITY_ID);
    }

    /**
     * Set entity_id
     * @param string $entityId
     * @return StagingScheduleInterface
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get action
     * @return string|null
     */
    public function getAction()
    {
        return $this->_get(self::ACTION);
    }

    /**
     * Set action
     * @param string $action
     * @return StagingScheduleInterface
     */
    public function setAction($action)
    {
        return $this->setData(self::ACTION, $action);
    }

    /**
     * Get datetime
     * @return string|null
     */
    public function getDatetime()
    {
        return $this->_get(self::DATETIME);
    }

    /**
     * Set datetime
     * @param string $datetime
     * @return StagingScheduleInterface
     */
    public function setDatetime($datetime)
    {
        return $this->setData(self::DATETIME, $datetime);
    }

    /**
     * Get params
     * @return string|null
     */
    public function getParams()
    {
        return $this->_get(self::PARAMS);
    }

    /**
     * Set params
     * @param string $params
     * @return StagingScheduleInterface
     */
    public function setParams($params)
    {
        return $this->setData(self::PARAMS, $params);
    }

    /**
     * Get instance
     * @return string|null
     */
    public function getInstance()
    {
        return $this->_get(self::INSTANCE);
    }

    /**
     * Set instance
     * @param string $instance
     * @return StagingScheduleInterface
     */
    public function setInstance($instance)
    {
        return $this->setData(self::INSTANCE, $instance);
    }

    /**
     * Get status
     * @return string|null
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return StagingScheduleInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get message
     * @return string|null
     */
    public function getMessage()
    {
        return $this->_get(self::MESSAGE);
    }

    /**
     * Set message
     * @param string $message
     * @return StagingScheduleInterface
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }
}
