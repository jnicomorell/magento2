<?php
declare(strict_types=1);

namespace Ceg\BitrixIntegration\Api\Data;

interface QueueInterface
{
    const ID = 'entity_id';
    const ELEMENT_ID = 'element_id';
    const TYPE = 'type';
    const STATUS = 'status';
    const ACTION = 'action';
    const MESSAGE = 'message';
    const DATETIME = 'datetime';
    const STATUS_SENDED = 'sended';
    const STATUS_PENDING = 'pending';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $value
     * @return $this
     */
    public function setId($value);

    /**
     * @return int|null
     */
    public function getElementId();

    /**
     * @param int $value
     * @return $this
     */
    public function setElementId($value);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $value
     * @return $this
     */
    public function setType($value);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $value
     * @return $this
     */
    public function setStatus($value);

    /**
     * @return string
     */
    public function getAction();

    /**
     * @param string $value
     * @return $this
     */
    public function setAction($value);

    /**
     * @return string|null
     */
    public function getMessage();

    /**
     * @param string $value
     * @return $this
     */
    public function setMessage($value);

    /**
     * @return string|null
     */
    public function getDatetime();

    /**
     * @param string $value
     * @return $this
     */
    public function setDatetime($value);
}
