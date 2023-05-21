<?php

namespace Formax\UploadModule\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ObjectManager;

class Type extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('formax_uploadmodule_type', 'id');
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
            throw new LocalizedException(__('The type name is required.'));
        }

        if (!empty($order) && !is_numeric($order)) {
            throw new LocalizedException(__('The Sort Order must be a numeric.'));
        }

        return $this;
    }
    
}
