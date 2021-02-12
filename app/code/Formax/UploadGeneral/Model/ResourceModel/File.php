<?php

namespace Formax\UploadGeneral\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ObjectManager;
use Formax\UploadGeneral\Model\File\ImageUploader;
use Formax\UploadGeneral\Model\File\FileUploader;


class File extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * ImageUploader
     *
     * @var \Formax\UploadGeneral\Model\File\ImageUploader
     */
    protected $_imageUploader;

    /**
     * FileUploader
     *
     * @var \Formax\UploadGeneral\Model\File\FileUploader
     */
    protected $_fileUploader;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('formax_uploadgeneral_file', 'id');
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
        $file = $object->getFile();

        if (empty($title)) {
            throw new LocalizedException(__('The file title is required.'));
        }

        // if the URL not null then check the URL
        if (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new LocalizedException(__('The URL Link is invalid.'));
        }

        if (is_array($image)) {
            $object->setImage($image[0]['name']);
        }

        if (is_array($file)) {
            $object->setImage($file[0]['name']);
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

        $fileName = $object->getFile();
        $this->_getFileUploader()->deleteFile($fileName);

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

    /**
     * Get FileUploader instance
     *
     * @return FileUploader
     */
    private function _getFileUploader()
    {
        if ($this->_fileUploader === null) {
            $this->_fileUploader = ObjectManager::getInstance()->get(FileUploader::class);
        }
        return $this->_fileUploader;
    }
}
