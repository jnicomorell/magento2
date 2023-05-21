<?php

namespace Formax\Campaigns\Model\Type\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * @var \Formax\Campaigns\Model\Type
     */
    protected $type;

    /**
     * Constructor
     *
     * @param \Formax\Campaigns\Model\Type $type
     */
    public function __construct(\Formax\Campaigns\Model\Type $type)
    {
        $this->type = $type;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->type->getAvailableStatuses();
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
