<?php
declare(strict_types=1);

namespace Ceg\OrdersIntegration\Api;

interface IntegrationRepositoryInterface
{
    /**
     * @param \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote $quote
     * @return \Ceg\OrdersIntegration\Api\Data\Integration\ResultInterface
     */
    public function sendOrder($quote);

    /**
     * @param \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote $quote
     * @return \Ceg\OrdersIntegration\Api\Data\Integration\ResultInterface
     */
    public function deleteOrder($quote);

    /**
     * @param \Ceg\OrdersIntegration\Model\Queue $queue
     * @param \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote $quote
     * @return \Ceg\OrdersIntegration\Api\Data\Integration\ResultInterface
     */
    public function resendOrder($queue, $quote);

    /**
     * @param  \Ceg\OrdersIntegration\Api\Data\Integration\OrderInterface $order
     * @return \Ceg\OrdersIntegration\Api\Data\Integration\ResultInterface
     */
    public function setFinalQuantity($order);

    /**
     * @param  \Ceg\OrdersIntegration\Api\Data\Integration\OrderInterface[] $orders
     * @return \Ceg\OrdersIntegration\Api\Data\Integration\ResultInterface
     */
    public function setFinalQuantityBulk($orders);
}
