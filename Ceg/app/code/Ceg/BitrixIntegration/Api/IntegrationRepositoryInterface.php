<?php
declare(strict_types=1);

namespace Ceg\BitrixIntegration\Api;

use Ceg\BitrixIntegration\Api\Data\Integration\AbstractElementInterface;
use Ceg\BitrixIntegration\Api\Data\Integration\ResultInterface;
use Ceg\BitrixIntegration\Model\Queue;

interface IntegrationRepositoryInterface
{
    /**
     * @param AbstractElementInterface $element
     * @return ResultInterface
     */
    public function sendData($element);

    /**
     * @param AbstractElementInterface $element
     * @param Queue $queue
     * @return ResultInterface
     */
    public function resendData($element, $queue);
}
