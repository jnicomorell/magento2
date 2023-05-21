<?php
declare(strict_types=1);

namespace Ceg\Impo\Model\Integration;

use Magento\Framework\Model\AbstractModel;
use Ceg\Impo\Api\Data\Integration\ProductInterface;

class Product extends AbstractModel implements ProductInterface
{
    /**
     * @inheritdoc
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * @inheritdoc
     */
    public function setSku($value)
    {
        $this->setData(self::SKU, $value);
        return $this;
    }
}
