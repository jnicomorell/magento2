<?php

namespace Formax\News\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class ArticleImage extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Url path
     */
    const URL_PATH_EDIT = 'news/article/edit';

    /**
     * @var \Formax\News\Model\Article
     */
    protected $article;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Formax\News\Model\Article $article
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Formax\News\Model\Article $article,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->article = $article;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $article = new \Magento\Framework\DataObject($item);
                $item[$fieldName . '_src'] = $this->article->getImageUrl($article['image']);
                $item[$fieldName . '_orig_src'] = $this->article->getImageUrl($article['image']);
                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                    self::URL_PATH_EDIT,
                    ['id' => $article['id']]
                );
                $item[$fieldName . '_alt'] = $article['name'];
            }
        }
        return $dataSource;
    }
}
