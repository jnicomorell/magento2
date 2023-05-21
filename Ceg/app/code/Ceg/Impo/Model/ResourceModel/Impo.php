<?php
declare(strict_types=1);

namespace Ceg\Impo\Model\ResourceModel;

use Datetime;
use DatetimeZone;
use InvalidArgumentException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Ceg\Impo\Model\ResourceModel\Impo\RelatedProductFactory;
use Ceg\Impo\Model\Impo as ImpoModel;

class Impo extends AbstractDb
{
    /**
     * @var TimezoneInterface
     */
    private $timezoneInterface;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollFactory;

    /**
     * @var RelatedProductFactory
     */
    protected $productFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param TimezoneInterface $timezoneInterface
     * @param RelatedProductFactory $productFactory
     * @param ProductCollectionFactory $productCollFactory
     * @param StoreManagerInterface $storeManager
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        TimezoneInterface $timezoneInterface,
        RelatedProductFactory $productFactory,
        ProductCollectionFactory $productCollFactory,
        StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->timezoneInterface = $timezoneInterface;
        $this->productFactory = $productFactory;
        $this->productCollFactory = $productCollFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _construct()
    {
        $this->_init('ceg_impo_entity', 'entity_id');
    }

    /**
     * @param AbstractModel $object
     * @return $this
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $this->setDateFieldValue($object, 'start_at', 00, 00, 00);
        $this->setDateFieldValue($object, 'finish_at', 23, 59, 59);
        return parent::_beforeSave($object);
    }

    public function setDateFieldValue($impo, $field, $hour, $min, $seg)
    {
        $date = $impo->getData($field);
        $currentTimeZone = new DatetimeZone(
            $this->timezoneInterface->getConfigTimezone('website', $impo->getWebsiteId())
        );
        $dateTime = new DateTime($date, $currentTimeZone);
        if ($impo->getData($field)) {
            if ($impo->getData($field) != $impo->getOrigData($field)) {
                try {
                    $dateTime->setTime($hour, $min, $seg);
                    $dateTime->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    throw new InvalidArgumentException('Invalid date string provided', $e->getCode(), $e);
                }
            }
        }
        $impo->setData($field, $dateTime);
    }

    /**
     * @param AbstractModel|DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _afterSave(AbstractModel $object)
    {
        $parentId = $object->getId();
        $productIds = $object->getRelatedProductIds();

        $relatedProduct = $this->productFactory->create();
        $relatedProduct->saveMultiple($parentId, $productIds);

        return parent::_afterSave($object);
    }

    /**
     * @param ImpoModel $impo
     * @return ProductCollection
     */
    public function getRelatedProducts($impo)
    {
        $relatedProduct = $this->productFactory->create();
        $relatedProductIds = $relatedProduct->getRelatedProductIds($impo);
        if (empty($relatedProductIds)) {
            $relatedProductIds = 0;
        }
        $productCollection = $this->productCollFactory->create();
        $productCollection->addAttributeToSelect('name');
        $productCollection->addFieldToFilter('entity_id', ['in' => $relatedProductIds]);
        return $productCollection;
    }
}
