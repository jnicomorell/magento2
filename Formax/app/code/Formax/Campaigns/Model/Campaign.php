<?php

namespace Formax\Campaigns\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

class Campaign extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Campaign cache tag
     */
    const CACHE_TAG = 'campaigns_campaign';

    /**#@+
     * Campaign's statuses
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
        $this->_init('Formax\Campaigns\Model\ResourceModel\Campaign');
    }

    /**
     * Prepare Campaign's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }


    /**
     * Get StoreManagerInterface instance
     *
     * @return StoreManagerInterface
     */
    private function _getStoreManager()
    {
        if ($this->_storeManager === null) {
            $this->_storeManager = ObjectManager::getInstance()->get(StoreManagerInterface::class);
        }
        return $this->_storeManager;
    }
}
