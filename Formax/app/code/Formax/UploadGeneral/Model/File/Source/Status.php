<?php

namespace Formax\UploadGeneral\Model\File\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * @var \Formax\UploadGeneral\Model\File
     */
    protected $file;

    /**
     * Constructor
     *
     * @param \Formax\UploadGeneral\Model\File $file
     */
    public function __construct(\Formax\UploadGeneral\Model\File $file)
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
