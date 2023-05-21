<?php
namespace Ceg\Catalog\Plugin\Model;
use Magento\Store\Model\StoreManagerInterface;
class Config
{
    protected $_storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;

    }

    /**
     * Adding custom options and changing labels
     *
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param [] $options
     * @return []
     */
    public function afterGetAttributeUsedForSortByArray(\Magento\Catalog\Model\Config $catalogConfig, $options)
    {
        unset($options['price']);
        $options['unit_price'] = __('Price');

        return $options;
    }
}
