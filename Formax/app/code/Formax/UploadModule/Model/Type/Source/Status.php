<?php

namespace Formax\UploadModule\Model\Type\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * @var \Formax\UploadModule\Model\Type
     */
    protected $type;

    /**
     * Constructor
     *
     * @param \Formax\UploadModule\Model\Type $type
     */
    public function __construct(\Formax\UploadModule\Model\Type $type)
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
