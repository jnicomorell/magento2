<?php

namespace Formax\UploadGeneral\Model\File;

use Formax\UploadGeneral\Model\ResourceModel\File\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Formax\UploadGeneral\Model\File\FileInfo;
use Magento\Framework\Filesystem;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Formax\UploadGeneral\Model\ResourceModel\File\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var Filesystem
     */
    private $fileInfo;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $fileCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $fileCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $fileCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var \Formax\UploadGeneral\Model\File $file */
        foreach ($items as $file) {
            $file = $this->convertValues($file);
            $this->loadedData[$file->getId()] = $file->getData();
        }

        // Used from the Save action
        $data = $this->dataPersistor->get('file');
        if (!empty($data)) {
            $file = $this->collection->getNewEmptyItem();
            $file->setData($data);
            $this->loadedData[$file->getId()] = $file->getData();
            $this->dataPersistor->clear('file');
        }

        return $this->loadedData;
    }

    /**
     * Converts image data to acceptable for rendering format
     *
     * @param \Formax\UploadGeneral\Model\File $file
     * @return \Formax\UploadGeneral\Model\File $file
     */
    private function convertValues($file)
    {
        $fileName = $file->getImage();
        $image = [];
        if ($this->getFileInfo()->isExist($fileName)) {
            $stat = $this->getFileInfo()->getStat($fileName);
            $mime = $this->getFileInfo()->getMimeType($fileName);
            $image[0]['name'] = $fileName;
            $image[0]['url'] = $file->getImageUrl();
            $image[0]['size'] = isset($stat) ? $stat['size'] : 0;
            $image[0]['type'] = $mime;
        }
        $file->setImage($image);


        $fileName = $file->getFile();
        $filefile = [];
        if ($this->getFileInfo()->isExist($fileName)) {
            $stat = $this->getFileInfo()->getStat($fileName);
            $mime = $this->getFileInfo()->getMimeType($fileName);
            $filefile[0]['name'] = $fileName;
            $filefile[0]['url'] = $file->getFileUrl();
            $filefile[0]['size'] = isset($stat) ? $stat['size'] : 0;
            $filefile[0]['type'] = $mime;
        }
        $file->setFile($filefile);

        return $file;
    }

    /**
     * Get FileInfo instance
     *
     * @return FileInfo
     *
     * @deprecated 101.1.0
     */
    private function getFileInfo()
    {
        if ($this->fileInfo === null) {
            $this->fileInfo = ObjectManager::getInstance()->get(FileInfo::class);
        }
        return $this->fileInfo;
    }
}
