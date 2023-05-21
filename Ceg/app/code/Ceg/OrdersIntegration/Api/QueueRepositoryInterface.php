<?php
declare(strict_types=1);

namespace Ceg\OrdersIntegration\Api;

use Ceg\OrdersIntegration\Api\Data\QueueInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;

interface QueueRepositoryInterface
{
    /**
     * @param int $quoteId
     * @return QueueInterface
     * @throws NoSuchEntityException
     */
    public function getByQuoteId($quoteId);

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
