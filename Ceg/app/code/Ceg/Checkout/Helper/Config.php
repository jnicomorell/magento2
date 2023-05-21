<?php
declare(strict_types=1);

namespace Ceg\Checkout\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Config extends AbstractHelper
{
    const CONFIG_PATH = 'ceg_checkout';

    const ACTIVE = 'active';

    const SUCCESS_MESSAGE = 'success_message';

    const QUOTE_STATUS_LABEL = 'quote_status_label_';

    const PRODUCTS_QTY_IN_SUMMARY = 'products_qty_in_summary';

    /**
     * @param $path
     * @return mixed
     */
    protected function getModuleConfig($path)
    {
        $realPath = self::CONFIG_PATH . '/general/';
        return $this->scopeConfig->getValue($realPath . $path);
    }

    /**
     * @return string
     */
    public function getActive()
    {
        return $this->getModuleConfig(self::ACTIVE);
    }

    /**
     * @return string
     */
    public function getSuccessMessage()
    {
        return $this->getModuleConfig(self::SUCCESS_MESSAGE);
    }

    /**
     * @param $status
     * @return string
     */
    public function getQuoteStatusLabel($status)
    {
        $field = self::QUOTE_STATUS_LABEL . $status;
        return $this->getModuleConfig($field);
    }

    /**
     * @return int
     */
    public function getProductsQtyInSummary()
    {
        $field = self::PRODUCTS_QTY_IN_SUMMARY;
        return $this->getModuleConfig($field);
    }
}
