<?php
declare(strict_types=1);

namespace Ceg\Impo\Model\Impo;

use Ceg\Impo\Api\Data\RelatedProductInterface;
use Magento\Framework\Model\AbstractModel;

class RelatedProduct extends AbstractModel implements RelatedProductInterface
{
    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(
            \Ceg\Impo\Model\ResourceModel\Impo\RelatedProduct::class
        );
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return (int)$this->getData(self::ID);
    }

    /**
     * @inheritdoc
     */
    public function setId($value)
    {
        return $this->setData(self::ID, $value);
    }

    /**
     * @inheritdoc
     */
    public function getImpoId(): int
    {
        return (int)$this->getData(self::IMPO_ID);
    }

    /**
     * @inheritdoc
     */
    public function setImpoId($value)
    {
        return $this->setData(self::IMPO_ID, $value);
    }

    /**
     * @inheritdoc
     */
    public function getProductId(): int
    {
        return (int)$this->getData(self::PRODUCT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setProductId($value)
    {
        return $this->setData(self::PRODUCT_ID, $value);
    }
}
