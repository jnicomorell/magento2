<?php

namespace Formax\Campaigns\Model\CreditCard;

use Formax\Campaigns\Model\ResourceModel\CreditCard\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Formax\Campaigns\Model\ResourceModel\CreditCard\Collection
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
     * @param CollectionFactory $campaignCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $campaignCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $campaignCollectionFactory->create();
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
        /** @var \Formax\Campaigns\Model\CreditCard $file */
        foreach ($items as $file) {
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

}
