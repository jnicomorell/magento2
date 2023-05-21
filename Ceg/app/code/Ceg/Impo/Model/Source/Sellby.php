<?php
 
namespace Ceg\Impo\Model\Source;
 
class Sellby extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('Unit'), 'value' => 1],
                ['label' => __('Box'), 'value' => 2]
            ];
        }
        return $this->_options;
    }
}
