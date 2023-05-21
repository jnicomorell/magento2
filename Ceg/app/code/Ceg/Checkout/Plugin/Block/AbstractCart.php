<?php

namespace Ceg\Checkout\Plugin\Block;

class AbstractCart
{
    /**
     * @param \Magento\Checkout\Block\Cart\AbstractCart $subject
     * @param                                           $result
     *
     * @return mixed
     */
    public function afterGetItemRenderer(
        \Magento\Checkout\Block\Cart\AbstractCart $subject,
        $result
    ) {
        $result->setTemplate('Ceg_Checkout::cart/item/default.phtml');

        return $result;
    }
}
