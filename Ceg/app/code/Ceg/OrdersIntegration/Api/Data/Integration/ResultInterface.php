<?php
declare(strict_types=1);

namespace Ceg\OrdersIntegration\Api\Data\Integration;

interface ResultInterface
{
    const MESSAGES_KEY = 'messages';
    const STATUS_KEY = 'status';
    const STATUS_ERROR = 'error';
    const STATUS_SUCCESS = 'success';
    const STATUS_PARTIAL_SUCCESS = 'partial-success';

    /**
     * @return string|null
     */
    public function getStatus();

    /**
     * @return string[]
     */
    public function getMessages();
}
