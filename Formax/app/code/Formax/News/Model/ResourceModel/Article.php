<?php

namespace Formax\News\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ObjectManager;
use Formax\News\Model\Article\ImageUploader;

class Article extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * ImageUploader
     *
     * @var \Formax\News\Model\Article\ImageUploader
     */
    protected $_imageUploader;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('formax_news_article', 'id');
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $title = $object->getTitle();
        $url = $object->getUrl();
        $image = $object->getImage();
        $categories = $object->getCategories();
        $relatedNews = $object->getRelatedNews();

        if (empty($title)) {
            throw new LocalizedException(__('The article title is required.'));
        }

        if (empty($categories)) {
            throw new LocalizedException(__('The category is required.'));
        }

        if (!empty($categories)) {
            $categories = explode(",", $categories);
            if (count($categories) > 5)
            throw new LocalizedException(__('Maximum 5 categories.'));
        }

        // if the URL not null then check the URL
        if (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new LocalizedException(__('The URL Link is invalid.'));
        }

        if (is_array($image)) {
            $object->setImage($image[0]['name']);
        }

        if (empty($relatedNews)) {
            throw new LocalizedException(__('Minimum 1 related news.'));
        }

        if (!empty($relatedNews)) {
            $relatedNews = explode(",", $relatedNews);
            if (count($relatedNews) > 3)
            throw new LocalizedException(__('Maximum 3 related news.'));
        }

        return $this;
    }

    /**
     * Perform actions after object delete
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $imageName = $object->getImage();
        $this->_getImageUploader()->deleteImage($imageName);

        return $this;
    }

    /**
     * Get ImageUploader instance
     *
     * @return ImageUploader
     */
    private function _getImageUploader()
    {
        if ($this->_imageUploader === null) {
            $this->_imageUploader = ObjectManager::getInstance()->get(ImageUploader::class);
        }
        return $this->_imageUploader;
    }
}
