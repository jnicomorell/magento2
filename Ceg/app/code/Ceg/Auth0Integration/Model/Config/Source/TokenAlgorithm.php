<?php
declare(strict_types=1);

namespace Ceg\Auth0Integration\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class TokenAlgorithm implements ArrayInterface
{
    /**
     * Options getter
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'RS256', 'label' => 'RS256'],
            ['value' => 'HS256', 'label' => 'HS256']
        ];
    }
}
