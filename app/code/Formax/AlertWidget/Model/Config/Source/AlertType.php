<?php

namespace Formax\AlertWidget\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class AlertType implements ArrayInterface
{
    /*
 * Option getter
 * @return array
 */
    public function toOptionArray()
    {
        $arr = $this->toArray();
        $ret = [];
        foreach ($arr as $key => $value) {
            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }
        return $ret;
    }

    /*
 * Get options in "key-value" format
 * @return array
 */
    public function toArray()
    {
        $choose = [
            '1' => __('Success'),
            '2' => __('Info'),
            '3' => __('Warning'),
            '4' => __('Error')

        ];
        return $choose;
    }
}
