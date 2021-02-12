<?php

namespace Formax\News\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

class Homenews extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Homenews cache tag
     */
    const CACHE_TAG = 'news_homenews';

    /**#@+
     * Homenews's statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Formax\News\Model\ResourceModel\Homenews');
    }

    /**
     * Prepare homenews's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
}
