<?php

namespace Ceg\Sales\Block\Order;

class Recent extends \Magento\Sales\Block\Order\Recent
{
    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _toHtml()
    {
        if (!$this->getTemplate()) {
            return '';
        }
        return $this->fetchView($this->getTemplateFile());
    }
}
