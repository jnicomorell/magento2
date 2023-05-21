<?php

namespace Formax\News\Model\Homenews\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * @var \Formax\News\Model\Homenews
     */
    protected $category;

    /**
     * Constructor
     *
     * @param \Formax\News\Model\Homenews $category
     */
    public function __construct(\Formax\News\Model\Homenews $category)
    {
        $this->category = $category;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->category->getAvailableStatuses();
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
