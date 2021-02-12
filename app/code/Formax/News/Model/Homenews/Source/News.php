<?php

namespace Formax\News\Model\Homenews\Source;

use Magento\Framework\Data\OptionSourceInterface;

class News implements OptionSourceInterface
{
    /**
     * @var \Formax\News\Model\ArticleFactory
     */
    protected $news;

    /**
     * Constructor
     *
     * @param \Formax\News\Model\ArticleFactory $news
     */
    public function __construct(\Formax\News\Model\ArticleFactory $news)
    {
        $this->news = $news;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $collection = $this->news->create()->getCollection()->setOrder('created_at', 'desc');;
        $options = [];
        $options[] = ['value' => '0', 'label' => __('Select...')];
        foreach ($collection as $key => $value) {
            $options[] = [
                'label' => $value->getTitle(),
                'value' => $value->getId(),
            ];
        }
        return $options;
    }
}
