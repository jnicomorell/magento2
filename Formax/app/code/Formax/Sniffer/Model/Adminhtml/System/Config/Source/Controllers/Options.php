<?php


namespace Formax\Sniffer\Model\Adminhtml\System\Config\Source\Controllers;


class Options implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'search_term_popular', 'label' => __('search_term_popular')],
            ['value' => 'cms_index_index', 'label' => __('cms_index_index')],
            ['value' => 'cms_page_view', 'label' => __('cms_page_view')],
            ['value' => 'cms_noroute_index', 'label' => __('cms_noroute_index')],
            ['value' => 'customer_account_create', 'label' => __('customer_account_create')],
            ['value' => 'cms_noroute_index', 'label' => __('cms_noroute_index')],
            ['value' => 'catalog_category_view', 'label' => __('catalog_category_view')],
            ['value' => 'catalog_product_view', 'label' => __('catalog_product_view')],
            ['value' => 'customer_account_login', 'label' => __('customer_account_login')],
            ['value' => 'checkout_cart_index', 'label' => __('checkout_cart_index')],
            ['value' => 'checkout_index_index', 'label' => __('checkout_index_index')],
            ['value' => 'checkout_onepage_success', 'label' => __('checkout_onepage_success')]

        ];
    }
}