<?php
namespace Ceg\Checkout\Plugin\Model;

class Cart
{
    /**
     * @param \Magento\Checkout\Model\Cart $subject
     * @param                              $result
     *
     * @return int
     */
    public function afterGetItemsCount(
        \Magento\Checkout\Model\Cart $subject,
        $result
    ) {
        $quote = $subject->getQuote();
        return $this->getValidValues($quote, true);
    }

    /**
     * @param \Magento\Checkout\Model\Cart $subject
     * @param                              $result
     *
     * @return int
     */
    public function afterGetItemsQty(
        \Magento\Checkout\Model\Cart $subject,
        $result
    ) {
        $quote = $subject->getQuote();
        return $this->getValidValues($quote, false);
    }

    /**
     * @param $quote
     * @param $isCount
     *
     * @return int
     */
    public function getValidValues($quote, $isCount)
    {
        $validItemsCount = $quote->getItemsCount();
        $validItemsQty = $quote->getItemsQty();
        foreach ($quote->getAllVisibleItems() as $item) {
            if (!$item->getIsValidImpo() || !$item->getIsValidProduct()) {
                $validItemsCount--;
                $validItemsQty -= $item->getQtyOrdered();
            }
        }
        if ($isCount) {
            return $validItemsCount;
        }
        return $validItemsQty;
    }
}
