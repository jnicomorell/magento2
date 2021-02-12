<?php

namespace Formax\News\Ui\Component\Listing\Column\Category;

use Magento\Framework\Escaper;
use Magento\Framework\Data\OptionSourceInterface;
use Formax\News\Model\CategoryFactory;

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
     * @var CategoryFactory
     */
    protected $categoryFactory;

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
     * @param CategoryFactory $categoryFactory
     * @param Escaper $escaper
     */
    public function __construct(CategoryFactory $categoryFactory, Escaper $escaper)
    {
        $this->categoryFactory = $categoryFactory;
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
     * Prepare Categorys
     *
     * @return array
     */
    private function getAvailableCategories()
    {
        $collection = $this->categoryFactory->create()->getCollection();
        $result = [];
        foreach ($collection as $category) {
            $result[] = ['value' => $category->getId(), 'label' => $this->escaper->escapeHtml($category->getName())];
        }
        return $result;
    }
}
