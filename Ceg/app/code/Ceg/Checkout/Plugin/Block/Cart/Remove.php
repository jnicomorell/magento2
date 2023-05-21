<?php

namespace Ceg\Checkout\Plugin\Block\Cart;

use Magento\Checkout\Helper\Cart;
use Magento\Framework\View\Element\Template;

class Remove
{
    public function afterGetDeletePostJson(
        \Magento\Checkout\Block\Cart\Item\Renderer\Actions\Remove $subject,
        $result
    ) {
        $jsonResult = json_decode($result, true);
        $jsonResult['data']['confirmation'] = true;
        $jsonResult['data']['confirmationMessage'] = __('Are you sure you want to remove this item?');

        return json_encode($jsonResult);
    }
}
