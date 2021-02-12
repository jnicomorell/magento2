<?php

namespace Formax\Campaigns\Model\CreditCard\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * @var \Formax\Campaigns\Model\CreditCard
     */
    protected $file;

    /**
     * Constructor
     *
     * @param \Formax\Campaigns\Model\CreditCard $file
     */
    public function __construct(\Formax\Campaigns\Model\CreditCard $file)
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
