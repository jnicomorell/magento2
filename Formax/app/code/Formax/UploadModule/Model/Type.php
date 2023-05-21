<?php

namespace Formax\UploadModule\Model;

use Formax\UploadModule\Model\Type\FileInfo;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

class Type extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Type cache tag
     */
    const CACHE_TAG = 'uploadmodule_type';

    /**#@+
     * Type's statuses
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
        $this->_init('Formax\UploadModule\Model\ResourceModel\Type');
    }

    /**
     * Prepare type's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
}
