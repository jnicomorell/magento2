<?php

namespace Formax\News\Model;

use Formax\News\Model\Category\FileInfo;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

class Category extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Category cache tag
     */
    const CACHE_TAG = 'news_category';

    /**#@+
     * Category's statuses
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
        $this->_init('Formax\News\Model\ResourceModel\Category');
    }

    /**
     * Prepare category's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
}
