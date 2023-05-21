<?php
declare(strict_types=1);

namespace Ceg\Impo\Ui\Component\Providers;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Customer's website view model
 */
class Status implements OptionSourceInterface
{
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_OPEN = 'open';
    const STATUS_CLOSED = 'closed';
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'scheduled', 'label' => 'Scheduled'],
            ['value' => 'open', 'label' => 'Open'],
            ['value' => 'closed', 'label' => 'Closed']
        ];
    }
}
