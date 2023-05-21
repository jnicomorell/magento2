<?php

namespace Ceg\Checkout\Plugin\CustomerData;

class Cart
{
    protected $checkoutHelper;

    /**
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     */
    public function __construct(
        \Magento\Checkout\Helper\Data $checkoutHelper
    ) {
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param                                     $result
     *
     * @return mixed
     */
    public function afterGetSectionData(
        \Magento\Checkout\CustomerData\Cart $subject,
        $result
    ) {
        $grand_total =  $this->checkoutHelper->getQuote()->getGrandTotal();
        $result['grand_total'] = $grand_total ? $this->checkoutHelper->formatPrice($grand_total) : '';
        return $result;
    }
}
