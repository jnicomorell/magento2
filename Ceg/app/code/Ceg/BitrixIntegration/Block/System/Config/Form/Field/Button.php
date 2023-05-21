<?php
declare(strict_types=1);

namespace Ceg\BitrixIntegration\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Button extends Field
{
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $link = rtrim($this->_urlBuilder->getUrl('bitrixintegration/queue/grid'), '/');
        return sprintf('<a class="action-default" href ="%s">%s</a>', $link, __('View Queues'));
    }
}
