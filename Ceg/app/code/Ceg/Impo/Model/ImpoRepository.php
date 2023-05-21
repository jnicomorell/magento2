<?php
declare(strict_types=1);

namespace Ceg\Impo\Model;

use Exception;
use Zend_Db_Expr;
use Datetime;
use DatetimeZone;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Catalog\Model\ResourceModel\ProductFactory as ProductResourceFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Ceg\Impo\Model\ResourceModel\Impo\CollectionFactory as ImpoCollectionFactory;
use Ceg\Impo\Model\ImpoFactory as ImpoModelFactory;
use Ceg\Impo\Model\ResourceModel\ImpoFactory as ImpoResourceFactory;
use Ceg\Impo\Api\Data\ImpoInterface;
use Ceg\Impo\Api\ImpoRepositoryInterface;
use Ceg\StagingSchedule\Helper\Scheduler;
use Ceg\Impo\Ui\Component\Providers\Status;
use Ceg\Impo\Model\ResourceModel\Impo\RelatedProductFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImpoRepository implements ImpoRepositoryInterface
{
    /**
     * @var ImpoModelFactory
     */
    protected $impoFactory;

    /**
     * @var ImpoCollectionFactory
     */
    protected $impoCollFactory;

    /**
     * @var ImpoResourceFactory
     */
    protected $impoResourceFactory;

    /**
     * @var ProductResourceFactory
     */
    protected $productResFactory;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var TimezoneInterface
     */
    private $timezoneInterface;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollFactory;

    /**
     * @var Scheduler
     */
    private $scheduler;

    /**
     * @var RelatedProductFactory
     */
    protected $relatedProduct;

    /**
     * @param Json                     $json
     * @param ImpoModelFactory         $impoFactory
     * @param ImpoResourceFactory      $impoResourceFactory
     * @param ProductResourceFactory   $productResFactory
     * @param ProductCollectionFactory $productCollFactory
     * @param ImpoCollectionFactory    $impoCollFactory
     * @param TimezoneInterface        $timezoneInterface
     * @param Scheduler                $scheduler
     * @param RelatedProductFactory    $relatedProduct
     */
    public function __construct(
        Json $json,
        ImpoModelFactory $impoFactory,
        ImpoResourceFactory $impoResourceFactory,
        ProductResourceFactory $productResFactory,
        ProductCollectionFactory $productCollFactory,
        ImpoCollectionFactory $impoCollFactory,
        TimezoneInterface $timezoneInterface,
        Scheduler $scheduler,
        RelatedProductFactory $relatedProduct
    ) {
        $this->json = $json;
        $this->impoFactory = $impoFactory;
        $this->impoResourceFactory = $impoResourceFactory;
        $this->productResFactory = $productResFactory;
        $this->productCollFactory = $productCollFactory;
        $this->impoCollFactory = $impoCollFactory;
        $this->timezoneInterface = $timezoneInterface;
        $this->scheduler = $scheduler;
        $this->relatedProduct = $relatedProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($impoId)
    {
        $impo = $this->impoFactory->create();
        $impoResource = $this->impoResourceFactory->create();
        $impoResource->load($impo, $impoId);
        if (!$impo->getId()) {
            throw new NoSuchEntityException(__('The Impo with the "%1" ID doesn\'t exist.', $impoId));
        }
        return $impo;
    }

    /**
     * {@inheritdoc}
     */
    public function getByCegId($cegId)
    {
        $impo = $this->impoFactory->create();
        $impoResource = $this->impoResourceFactory->create();
        $impoResource->load($impo, $cegId, ImpoInterface::CEG_ID);
        if (!$impo->getId()) {
            throw new NoSuchEntityException(__('The Impo with the "%1" CEG ID doesn\'t exist.', $cegId));
        }
        return $impo;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ImpoInterface $impo)
    {
        try {
            $impoResource = $this->impoResourceFactory->create();
            $relatedProduct = $this->relatedProduct->create();
            $oldProductsIds = $relatedProduct->getRelatedProductIds($impo);

            $strNewProductsIds = str_replace(['[', ']'], "", $impo->getGridProducts());
            $newProductsIds = (explode(",", $strNewProductsIds));
            foreach ($newProductsIds as $key => $value)
            {
                $newProductsIds[$key] = (int)$value;
            }

            $productsIdToRemove = array_diff($oldProductsIds, $newProductsIds);
            $productsIdToAdd = array_diff($newProductsIds, $oldProductsIds);

            $productsRelated = [
                'toAdd' => $productsIdToAdd,
                'toRemove' => $productsIdToRemove
            ];

            $impoResource->save($impo);
            $this->saveSchedule($impo, $productsRelated);

        } catch (Exception $exception) {
            throw new CouldNotSaveException(__('Could not save the impo: %1.', $exception->getMessage()));
        }
        return $impo;
    }

    /**
     * {@inheritdoc}
     */
    public function open(ImpoInterface $impo)
    {
        $impo->setStatus(Status::STATUS_OPEN);
        return $impo;
    }

    /**
     * {@inheritdoc}
     */
    public function closed(ImpoInterface $impo)
    {
        $impo->setStatus(Status::STATUS_CLOSED);
        return $impo;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ImpoInterface $impo)
    {
        try {
            $impoResource = $this->impoResourceFactory->create();
            $impoResource->delete($impo);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    public function getListActiveImpo($websiteId)
    {
        $today = $this->timezoneInterface->date()->format('Y-m-d H:i:s');

        $impoCollection = $this->impoCollFactory->create();
        $impoCollection->addFieldToFilter(ImpoInterface::WEBSITE_ID, ['eq' => $websiteId]);
        $impoCollection->addFieldToFilter(ImpoInterface::IS_ACTIVE, ['eq' => 1]);
        $impoCollection->addFieldToFilter(ImpoInterface::START_AT, ['lteq' => $today]);
        $impoCollection->addFieldToFilter(ImpoInterface::FINISH_AT, ['gteq' => $today]);

        return $impoCollection;
    }

    public function getListImpoToClose($websiteId)
    {
        $currentTimeZone = new DatetimeZone($this->timezoneInterface->getConfigTimezone('website', $websiteId));
        $dateTime = new DateTime('now', $currentTimeZone);
        $impoCollection = $this->impoCollFactory->create();
        $impoCollection->addFieldToFilter(ImpoInterface::WEBSITE_ID, ['eq' => $websiteId]);
        $impoCollection->addFieldToFilter(ImpoInterface::IS_ACTIVE, 1);
        $impoCollection->addFieldToFilter(ImpoInterface::FINISH_AT, ['lteq' => $dateTime]);

        return $impoCollection;
    }

    public function getProductIdsForActiveImpo($websiteId)
    {
        $productIds = [];
        foreach ($this->getListActiveImpo($websiteId) as $impo) {
            $impoId = $impo->getId();
            foreach ($impo->getRelatedProducts() as $product) {
                $productId = $product->getId();
                if (!array_key_exists($productId, $productIds)) {
                    $productIds[$productId] = $impoId;
                }
            }
        }
        return $productIds;
    }

    public function filterCollectionByActiveImpo($collection, $websiteId)
    {
        $productIds = [0];
        $subqueryArr= ["SELECT 0 as product_id, '' as impo_id"];
        foreach ($this->getProductIdsForActiveImpo($websiteId) as $productId => $impoId) {
            if (!in_array($productId, $productIds)) {
                $query = sprintf("SELECT %s as product_id, '%s' as impo_id", $productId, $impoId);
                array_push($subqueryArr, $query);
                array_push($productIds, $productId);
            }
        }
        $subquery = new Zend_Db_Expr(sprintf('(%s)', implode(' UNION ALL ', $subqueryArr)));

        $collection->clear();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('entity_id', ['in', $productIds]);
        $collection->getSelect()
            ->join(
                ['cegData' => $subquery],
                sprintf('%s.entity_id = cegData.product_id', $collection::MAIN_TABLE_ALIAS),
                ['impo_id' => 'cegData.impo_id']
            );
        return $collection;
    }

    public function saveSchedule($impo, $params = [])
    {
        $entity = \Ceg\Impo\Model\ScheduleExecutor::ENTITY;
        $executorClass = \Ceg\Impo\Model\ScheduleExecutor::class;
        $executorParams = $params;

        $this->scheduler->setSchedule(
            $impo->getWebsiteId(),
            $entity,
            $impo->getId(),
            $impo->getStartAt(),
            $impo->getFinishAt(),
            $executorClass,
            $executorParams
        );
    }
}
