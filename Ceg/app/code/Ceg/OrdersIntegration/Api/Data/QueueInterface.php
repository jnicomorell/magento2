<?php
declare(strict_types=1);

namespace Ceg\OrdersIntegration\Api\Data;

interface QueueInterface
{
    const ID = 'entity_id';
    const QUOTE_ID = 'quote_id';
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
    public function getQuoteId();

    /**
     * @param int $value
     * @return $this
     */
    public function setQuoteId($value);

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
