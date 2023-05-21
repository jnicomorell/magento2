<?php
declare(strict_types=1);

namespace Ceg\Impo\Model\Integration;

use Ceg\Impo\Api\Data\Integration\ImpoInterface;
use Magento\Framework\Model\AbstractModel;

class Impo extends AbstractModel implements ImpoInterface
{
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
    public function getFob()
    {
        return $this->getData(self::FOB);
    }

    /**
     * @inheritdoc
     */
    public function setFob($value)
    {
        $this->setData(self::FOB, $value);
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
        $this->setData(self::START_AT, $value);
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
        $this->setData(self::FINISH_AT, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProducts()
    {
        return $this->getData(self::PRODUCTS);
    }

    /**
     * @inheritdoc
     */
    public function setProducts($value)
    {
        $this->setData(self::PRODUCTS, $value);
        return $this;
    }
}
