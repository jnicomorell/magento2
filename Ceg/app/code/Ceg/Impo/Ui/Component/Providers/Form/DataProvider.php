<?php
declare(strict_types=1);

namespace Ceg\Impo\Ui\Component\Providers\Form;

use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Ceg\Impo\Model\ResourceModel\Impo\CollectionFactory as ImpoCollectionFactory;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var PoolInterface
     */
    protected $modifierPool;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ImpoCollectionFactory $collectionFactory
     * @param PoolInterface $modifierPool
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ImpoCollectionFactory $collectionFactory,
        PoolInterface $modifierPool,
        array $meta = [],
        array $data = []
    ) {
        $this->collection   = $collectionFactory->create();
        $this->modifierPool = $modifierPool;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function getData()
    {
        foreach ($this->getCollection()->getItems() as $itemId => $item) {
            $item->getResource()->afterLoad($item);
            $this->data[$itemId] = $item->toArray();
        }

        foreach ($this->modifierPool->getModifiersInstances() as $modifier) {
            $this->data = $modifier->modifyData($this->data);
        }
        return $this->data;
    }
}
