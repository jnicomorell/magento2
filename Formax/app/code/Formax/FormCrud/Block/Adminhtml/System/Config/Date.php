<?php

namespace Formax\FormCrud\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Date extends Field
{
    public function render(AbstractElement $element)
    {
        $element->setDateFormat('dd-MM-Y');
        $element->setShowsTime(false);
        return parent::render($element);
    }
}
