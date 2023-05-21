<?php
declare(strict_types=1);

namespace Ceg\OrdersIntegration\Model\Integration;

use Magento\Framework\Model\AbstractModel;
use Ceg\OrdersIntegration\Api\Data\Integration\ProductInterface;

class Product extends AbstractModel implements ProductInterface
{
    /**
     * @inheritdoc
     */
    public function getItemId()
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * @inheritdoc
     */
    public function setItemId($value)
    {
        $this->setData(self::ITEM_ID, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setProductId($value)
    {
        $this->setData(self::PRODUCT_ID, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQuantity()
    {
        return $this->getData(self::QUANTITY);
    }

    /**
     * @inheritdoc
     */
    public function setQuantity($value)
    {
        $this->setData(self::QUANTITY, $value);
        return $this;
    }
}
