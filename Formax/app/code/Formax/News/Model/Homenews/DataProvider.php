<?php

namespace Formax\News\Model\Homenews;

use Formax\News\Model\ResourceModel\Homenews\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Formax\News\Model\ResourceModel\Homenews\Collection
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
     * @param CollectionFactory $homenewsCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $homenewsCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $homenewsCollectionFactory->create();
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
        /** @var \Formax\News\Model\Homenews $homenews */
        foreach ($items as $homenews) {
            $this->loadedData[$homenews->getId()] = $homenews->getData();
        }

        // Used from the Save action
        $data = $this->dataPersistor->get('homenews');
        if (!empty($data)) {
            $homenews = $this->collection->getNewEmptyItem();
            $homenews->setData($data);
            $this->loadedData[$homenews->getId()] = $homenews->getData();
            $this->dataPersistor->clear('homenews');
        }

        return $this->loadedData;
    }

}
