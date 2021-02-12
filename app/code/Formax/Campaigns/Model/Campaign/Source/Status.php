<?php

namespace Formax\Campaigns\Model\Campaign\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * @var \Formax\Campaigns\Model\Campaign
     */
    protected $file;

    /**
     * Constructor
     *
     * @param \Formax\Campaigns\Model\Campaign $file
     */
    public function __construct(\Formax\Campaigns\Model\Campaign $file)
    {
        $this->file = $file;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->file->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
