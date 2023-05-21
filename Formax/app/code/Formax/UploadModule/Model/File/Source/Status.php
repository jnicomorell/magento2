<?php

namespace Formax\UploadModule\Model\File\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * @var \Formax\UploadModule\Model\File
     */
    protected $file;

    /**
     * Constructor
     *
     * @param \Formax\UploadModule\Model\File $file
     */
    public function __construct(\Formax\UploadModule\Model\File $file)
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
