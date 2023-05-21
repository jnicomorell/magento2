<?php
declare(strict_types=1);

namespace Ceg\Impo\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Ceg\Impo\Api\Data\ImpoInterface;

class Impo extends AbstractModel implements ImpoInterface
{
    /**
     * @var ResourceModel\Impo
     */
    protected $impoResource;

    /**
     * @param ResourceModel\Impo $impoResource
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        ResourceModel\Impo $impoResource,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->impoResource = $impoResource;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(
            ResourceModel\Impo::class
        );
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritdoc
     */
    public function setId($value)
    {
        $this->setData(self::ID, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getWebsiteId()
    {
        return $this->getData(self::WEBSITE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setWebsiteId($value)
    {
        $this->setData(self::WEBSITE_ID, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCegId()
    {
        return $this->getData(self::CEG_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCegId($value)
    {
        $this->setData(self::CEG_ID, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function setIsActive($value)
    {
        $this->setData(self::IS_ACTIVE, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFreeOnBoard()
    {
        return $this->getData(self::FREE_ON_BOARD);
    }

    /**
     * @inheritdoc
     */
    public function setFreeOnBoard($value)
    {
        $this->setData(self::FREE_ON_BOARD, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setTitle($value)
    {
        $this->setData(self::TITLE, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStartAt()
    {
        return $this->getData(self::START_AT);
    }

    /**
     * @inheritdoc
     */
    public function setStartAt($value)
    {
        $this->setData(self::START_AT, (string) $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFinishAt()
    {
        return $this->getData(self::FINISH_AT);
    }

    /**
     * @inheritdoc
     */
    public function setFinishAt($value)
    {
        $this->setData(self::FINISH_AT, (string) $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->_getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return $this->_getData(self::UPDATED_AT);
    }

    public function getRelatedProducts()
    {
        $products = $this->getData(self::RELATED_PRODUCTS);
        if ($products === null) {
            $products = $this->impoResource->getRelatedProducts($this);
            $this->setData(self::RELATED_PRODUCTS, $products);
        }
        return $products;
    }

    public function setRelatedProducts($value)
    {
        $this->setData(self::RELATED_PRODUCTS, $value);
        return $this;
    }
}
