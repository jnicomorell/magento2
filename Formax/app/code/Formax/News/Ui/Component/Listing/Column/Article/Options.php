<?php

namespace Formax\News\Ui\Component\Listing\Column\Article;

use Magento\Framework\Escaper;
use Magento\Framework\Data\OptionSourceInterface;
use Formax\News\Model\ArticleFactory;

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
     * @var ArticleFactory
     */
    protected $articleFactory;

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
     * @param ArticleFactory $articleFactory
     * @param Escaper $escaper
     */
    public function __construct(ArticleFactory $articleFactory, Escaper $escaper)
    {
        $this->articleFactory = $articleFactory;
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

        $this->options = $this->getAvailableArticles();

        return $this->options;
    }

    /**
     * Prepare Articles
     *
     * @return array
     */
    private function getAvailableArticles()
    {
        $collection = $this->articleFactory->create()->getCollection();
        $result = [];
        foreach ($collection as $article) {
            $result[] = ['value' => $article->getId(), 'label' => $this->escaper->escapeHtml($article->getTitle())];
        }
        return $result;
    }
}
