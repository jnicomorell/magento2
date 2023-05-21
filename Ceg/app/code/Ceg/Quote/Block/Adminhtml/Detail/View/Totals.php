<?php
declare(strict_types=1);

namespace Ceg\Quote\Block\Adminhtml\Detail\View;

use Ceg\Quote\ViewModel\QuoteData;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Totals extends Template
{
    /**
     * @var array
     */
    protected $totals;

    /**
     * @var QuoteData
     */
    protected $quoteData = null;

    /**
     * @param Context $context
     * @param array $data
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        $this->quoteData = $data['quote_data'];
        parent::__construct($context, $data);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _beforeToHtml()
    {
        $this->initTotals();
        foreach ($this->getLayout()->getChildBlocks($this->getNameInLayout()) as $child) {
            if (method_exists($child, 'initTotals') && is_callable([$child, 'initTotals'])) {
                $child->initTotals();
            }
        }
        return parent::_beforeToHtml();
    }

    protected function initTotals()
    {
        $this->totals = [];
        $quote = $this->quoteData->getQuote();
        $this->totals['subtotal'] = new DataObject(
            [
                'code' => 'subtotal',
                'value' => $quote->getSubtotal(),
                'label' => __('Subtotal')
            ]
        );

        $this->totals['tax'] = new DataObject(
            [
                'code' => 'tax',
                'value' => ($quote->getGrandTotal() - $quote->getSubtotal()),
                'label' => __('Tax')
            ]
        );

        $this->totals['grand_total'] = new DataObject(
            [
                'code' => 'grand_total',
                'field' => 'grand_total',
                'strong' => true,
                'value' => $quote->getGrandTotal(),
                'label' => __('Grand Total'),
            ]
        );

        return $this;
    }

    public function getTotals($area = null)
    {
        if ($area === null) {
            return $this->totals;
        }

        $totals = [];
        $area = (string)$area;
        foreach ($this->totals as $total) {
            $totalArea = (string)$total->getArea();
            if ($totalArea == $area) {
                $totals[] = $total;
            }
        }
        return $totals;
    }

    public function formatValue($total)
    {
        return $this->quoteData->formatPrice($total->getValue());
    }
}
