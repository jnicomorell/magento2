<?php
declare(strict_types=1);

namespace Ceg\Core\Model\Config;

use Magento\Framework\Option\ArrayInterface;

class ScheduleOptions implements ArrayInterface
{
    /**
     * Options getter
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '*/10 * * * *', 'label' => 'At every 10th minute.'],
            ['value' => '*/15 * * * *', 'label' => 'At every 15th minute.'],
            ['value' => '*/20 * * * *', 'label' => 'At every 20th minute.'],
            ['value' => '0 * * * *', 'label' => 'Hourly at minute 0.'],
            ['value' => '5 * * * *', 'label' => 'Hourly at minute 5.'],
            ['value' => '0 */2 * * *', 'label' => 'At minute 0 past every 2nd hour.'],
            ['value' => '5 */2 * * *', 'label' => 'At minute 5 past every 2nd hour.']
        ];
    }
}
