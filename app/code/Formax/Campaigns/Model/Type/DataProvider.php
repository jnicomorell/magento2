<?php

namespace Formax\Campaigns\Model\Type;

use Formax\Campaigns\Model\ResourceModel\Type\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Formax\Campaigns\Model\Type\FileInfo;
use Magento\Framework\Filesystem;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Formax\Campaigns\Model\ResourceModel\Type\Collection
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
     * @param CollectionFactory $typeCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $typeCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $typeCollectionFactory->create();
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
        /** @var \Formax\Campaigns\Model\Type $type */
        foreach ($items as $type) {
            $type = $this->convertValues($type);
            $type = $this->convertIcon($type);
            $this->loadedData[$type->getId()] = $type->getData();
        }

        // Used from the Save action
        $data = $this->dataPersistor->get('type');
        if (!empty($data)) {
            $type = $this->collection->getNewEmptyItem();
            $type->setData($data);
            $this->loadedData[$type->getId()] = $type->getData();
            $this->dataPersistor->clear('type');
        }

        return $this->loadedData;
    }

    /**
     * Converts image data to acceptable for rendering format
     *
     * @param \Formax\Campaigns\Model\Type $type
     * @return \Formax\Campaigns\Model\Type $type
     */
    private function convertValues($type)
    {
        $fileName = $type->getImage();
        $image = [];
        if ($this->getFileInfo()->isExist($fileName)) {
            $stat = $this->getFileInfo()->getStat($fileName);
            $mime = $this->getFileInfo()->getMimeType($fileName);
            $image[0]['name'] = $fileName;
            $image[0]['url'] = $type->getImageUrl();
            $image[0]['size'] = isset($stat) ? $stat['size'] : 0;
            $image[0]['type'] = $mime;
        }
        $type->setImage($image);

        return $type;
    }

    /**
     * Converts icon data to acceptable for rendering format
     *
     * @param \Formax\Campaigns\Model\Type $type
     * @return \Formax\Campaigns\Model\Type $type
     */
    private function convertIcon($type)
    {
        $fileName = $type->getIcon();
        $icon = [];
        if ($this->getFileInfo()->isExist($fileName)) {
            $stat = $this->getFileInfo()->getStat($fileName);
            $mime = $this->getFileInfo()->getMimeType($fileName);
            $icon[0]['name'] = $fileName;
            $icon[0]['url'] = $type->getIconUrl();
            $icon[0]['size'] = isset($stat) ? $stat['size'] : 0;
            $icon[0]['type'] = $mime;
        }
        $type->setIcon($icon);

        return $type;
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
