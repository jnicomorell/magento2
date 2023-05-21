<?php
namespace Formax\FormCrud\Model\FormCrud;
 
use Formax\FormCrud\Model\ResourceModel\FormCrud\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
 
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $employeeCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $employeeCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $employeeCollectionFactory->create();
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
        /** @var \Formax\News\Model\Category $term */
        foreach ($items as $term) {
            $this->loadedData[$term->getId()] = $term->getData();
        }

        // Used from the Save action
        $data = $this->dataPersistor->get('formcrud');
        if (!empty($data)) {
            $term = $this->collection->getNewEmptyItem();
            $term->setData($data);
            $this->loadedData[$term->getId()] = $term->getData();
            $this->dataPersistor->clear('formcrud');
        }

        return $this->loadedData;
    }
}