<?php
declare(strict_types=1);

namespace Ceg\Quote\Ui\Component\Listing\Filter;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * @return array|array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_OPEN, 'label' => __('Open')],
            ['value' => \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_APPROVED, 'label' => __('Approved')],
            ['value' => \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_REOPEN, 'label' => __('Reopen')],
            ['value' => \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_CLOSED, 'label' => __('Closed')],
            ['value' => \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_CANCELED, 'label' => __('Canceled')],
        ];
    }
}
