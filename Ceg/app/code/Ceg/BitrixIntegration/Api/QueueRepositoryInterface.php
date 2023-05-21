<?php
declare(strict_types=1);

namespace Ceg\BitrixIntegration\Api;

use Ceg\BitrixIntegration\Api\Data\QueueInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;

interface QueueRepositoryInterface
{
    /**
     * @param string $type
     * @param int $elementId
     * @return QueueInterface
     * @throws NoSuchEntityException
     */
    public function getByTypeElementId($type, $elementId);

    /**
     * @param  QueueInterface $queue
     * @return mixed
     */
    public function save(QueueInterface $queue);

    /**
     * @param  QueueInterface $queue
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(QueueInterface $queue);
}
