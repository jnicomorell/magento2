<?php
declare(strict_types=1);

namespace Ceg\Analytics\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;

class CategoryColumn extends Select
{
    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    private function getSourceOptions(): array
    {
        return [
            ['label' => 'Header Navegation', 'value' => 'header_navegation'],
            ['label' => 'Shop Results Import', 'value' => 'shopp_results_import'],
            ['label' => 'Shop Details Import', 'value' => 'shopp_detail_import'],
            ['label' => 'Shop Results Catalog', 'value' => 'shopp_results_catalog'],
            ['label' => 'Checkout Cart', 'value' => 'checkout_cart'],
            ['label' => 'Checkout Cart Shipping', 'value' => 'checkout_cart_shipping'],
            ['label' => 'Checkout Cart Billing', 'value' => 'checkout_cart_billing'],
            ['label' => 'Thanks Page', 'value' => 'thanks_page'],
        ];
    }
}
