<?php

namespace Formax\UploadModule\Model\Type;

use Formax\UploadModule\Model\ResourceModel\Type\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Formax\UploadModule\Model\ResourceModel\Type\Collection
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
        /** @var \Formax\UploadModule\Model\Type $type */
        foreach ($items as $type) {
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

}
