<?php

namespace Formax\UploadModule\Ui\Component\Listing\Column\Type;

use Magento\Framework\Escaper;
use Magento\Framework\Data\OptionSourceInterface;
use Formax\UploadModule\Model\TypeFactory;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    /**
     * Escaper
     *
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var TypeFactory
     */
    protected $typeFactory;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $currentOptions = [];

    /**
     * Constructor
     *
     * @param TypeFactory $typeFactory
     * @param Escaper $escaper
     */
    public function __construct(TypeFactory $typeFactory, Escaper $escaper)
    {
        $this->typeFactory = $typeFactory;
        $this->escaper = $escaper;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $this->options = $this->getAvailableCategories();

        return $this->options;
    }

    /**
     * Prepare Types
     *
     * @return array
     */
    private function getAvailableCategories()
    {
        $collection = $this->typeFactory->create()->getCollection();
        $result = [];
        foreach ($collection as $type) {
            $result[] = ['value' => $type->getId(), 'label' => $this->escaper->escapeHtml($type->getName())];
        }
        return $result;
    }
}
