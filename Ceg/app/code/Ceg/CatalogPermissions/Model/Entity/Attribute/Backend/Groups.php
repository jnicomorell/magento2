<?php
declare(strict_types=1);

namespace Ceg\CatalogPermissions\Model\Entity\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\DataObject;

class Groups extends AbstractBackend
{
    /**
     * @param DataObject $object
     * @return $this|Groups
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();

        if ($attributeCode == 'ceg_customer_group') {
            $data = $object->getData($attributeCode);
            if (!is_array($data)) {
                $data = [];
            }
            $object->setData($attributeCode, implode(',', $data));
        }

        if (!$object->hasData($attributeCode)) {
            $object->setData($attributeCode, null);
        }

        return $this;
    }
}
