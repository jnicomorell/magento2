<?php
declare(strict_types=1);

namespace Ceg\Impo\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class Pricedecimals implements ArrayInterface
{
    /**
     * Options getter
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '1', 'label' => '1'],
            ['value' => '2', 'label' => '2'],
            ['value' => '3', 'label' => '3'],
            ['value' => '4', 'label' => '4']
        ];
    }
}
