<?php
declare(strict_types=1);

namespace Ceg\Checkout\Plugin\Block\Cart;

class ItemRenderer
{
    public function afterGetMessages(
        \Magento\Checkout\Block\Cart\Item\Renderer $subject,
        $result
    ) {
        $quoteItem = $subject->getItem();
        if ($quoteItem->getIsValidImpo() !== null && !$quoteItem->getIsValidImpo()) {
            $message = __("The associated Import has finished. Please, remove this product from the cart.");
            $result[] = [
                'text' => $message->__toString(),
                'type' => 'error'
            ];
        }
        if ($quoteItem->getIsValidProduct() !== null && !$quoteItem->getIsValidProduct()) {
            $message = __("This product no longer belongs to a valid import.");
            $result[] = [
                'text' => $message->__toString(),
                'type' => 'error'
            ];
        }
        return $result;
    }
}
