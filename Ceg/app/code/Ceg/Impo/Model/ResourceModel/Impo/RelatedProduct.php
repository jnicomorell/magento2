<?php
declare(strict_types=1);

namespace Ceg\Impo\Model\ResourceModel\Impo;

use \Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Ceg\Impo\Api\Data\RelatedProductInterface as Columns;
use Ceg\Impo\Model\ResourceModel\Impo\RelatedProduct\CollectionFactory;
use Ceg\Impo\Model\Impo as ImpoModel;

class RelatedProduct extends AbstractDb
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $relationComposite,
        CollectionFactory $collectionFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $entitySnapshot, $relationComposite, $connectionName);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init('ceg_impo_product', 'entity_id');
    }

    public function saveMultiple($parentId, $productIds)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();

        try {
            $connection->delete(
                $this->getTable($this->_mainTable),
                [Columns::IMPO_ID.'=?' => $parentId]
            );

            $insertData = [];
            if (!empty($productIds)) {
                if (is_array($productIds)) {
                    foreach ($productIds as $productId) {
                        $insertData[] = [
                            Columns::IMPO_ID => $parentId,
                            Columns::PRODUCT_ID => $productId
                        ];
                    }
                }
            }

            if (!empty($insertData)) {
                $connection->insertMultiple(
                    $this->getTable($this->_mainTable),
                    $insertData
                );
            }
            $connection->commit();

        } catch (\Exception $exception) {
            $connection->rollBack();
        }
    }

    /**
     * @param ImpoModel $impo
     * @return array
     */
    public function getRelatedProductIds($impo)
    {
        $relatedProductIds = [];
        $impoId = $impo;
        if(is_object($impo)) {
            $impoId = $impo->getId();
        }

        if ((int)$impoId > 0) {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(\Ceg\Impo\Api\Data\RelatedProductInterface::IMPO_ID, $impoId);
            if ($collection->count() > 0) {
                foreach ($collection->getItems() as $item) {
                    $relatedProductIds[] = $item->getProductId();
                }
            }
        }
        return $relatedProductIds;
    }
}
