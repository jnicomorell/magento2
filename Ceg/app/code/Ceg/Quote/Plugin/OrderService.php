<?php
declare(strict_types=1);

namespace Ceg\Quote\Plugin;

use Magento\Quote\Api\CartRepositoryInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;

class OrderService
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var CartRepositoryInterfaceFactory
     */
    protected $cartRepoFactory;

    /**
     * @var OrderInterface
     */
    protected $currentOrder;

    /**
     * @param OrderRepositoryInterface       $orderRepository
     * @param CartRepositoryInterfaceFactory $cartRepoFactory
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterfaceFactory $cartRepoFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->cartRepoFactory = $cartRepoFactory;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function beforeCancel(
        \Magento\Sales\Model\Service\OrderService $subject,
        $orderId
    ) {
        $this->currentOrder = $this->orderRepository->get($orderId);
        return [$orderId];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function afterCancel(
        \Magento\Sales\Model\Service\OrderService $subject,
        $result
    ) {
        if ($result) {
            $cartRepository = $this->cartRepoFactory->create();
            $quote = $cartRepository->get($this->currentOrder->getQuoteId());
            $quote->cancel();
        }
        return $result;
    }
}
