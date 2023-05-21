<?php

namespace Ceg\Impo\Observer;

use Magento\Framework\DataObject\Copy;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class BeforeSaveQuoteToOrderObserver implements ObserverInterface
{
    protected $objectCopyService;

    public function __construct(
        Copy $objectCopyService
    ) {
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getData('order');
        $quote = $observer->getEvent()->getData('quote');

        $this->objectCopyService->copyFieldsetToTarget(
            'sales_convert_quote',
            'to_order',
            $quote,
            $order
        );

        foreach ($order->getAllItems() as $orderItem) {
            foreach ($quote->getAllItems() as $quoteItem) {
                if ($orderItem->getQuoteItemId() == $quoteItem->getId()) {
                    $orderItem = $this->objectCopyService->copyFieldsetToTarget(
                        'quote_convert_item',
                        'to_order_item',
                        $quoteItem,
                        $orderItem
                    );

                }
            }
        }

        return $this;
    }
}
