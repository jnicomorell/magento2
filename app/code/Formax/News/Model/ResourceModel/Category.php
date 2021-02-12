<?php

namespace Formax\News\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ObjectManager;

class Category extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('formax_news_category', 'id');
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $name = $object->getName();
        $order = $object->getSortOrder();

        if (empty($name)) {
            throw new LocalizedException(__('The category name is required.'));
        }

        if (!empty($order) && !is_numeric($order)) {
            throw new LocalizedException(__('The Sort Order must be a numeric.'));
        }

        return $this;
    }
    
}
