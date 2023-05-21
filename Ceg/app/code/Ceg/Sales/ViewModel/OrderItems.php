<?php

namespace Ceg\Sales\ViewModel;

use Magento\GiftMessage\Helper\Message;

class OrderItems implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    protected $message;

    /**
     * OrderItems constructor.
     *
     * @param Message $message
     */
    public function __construct(
        Message $message
    ) {
        $this->message = $message;
    }

    public function isMessagesAllowed($item)
    {
        return $this->message->isMessagesAllowed('order_item', $item);
    }

    public function getGiftMessageForEntity($item)
    {
        return $this->message->getGiftMessageForEntity($item);
    }

    public function getEscapedGiftMessage($item)
    {
        return $this->message->getEscapedGiftMessage($item);
    }
}
