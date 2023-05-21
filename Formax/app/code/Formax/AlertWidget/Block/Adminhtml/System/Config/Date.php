<?php

namespace Formax\AlertWidget\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Date extends Field
{
    public function render(AbstractElement $element)
    {
        $element->setDateFormat(\Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT);
        $element->setTimeFormat('HH:mm');
        $element->setShowsTime(true);
        return parent::render($element);
    }
}
