<?php
declare(strict_types=1);

namespace Ceg\OrdersIntegration\Api\Data\Integration;

interface ProductInterface
{
    const ITEM_ID = 'item_id';
    const PRODUCT_ID = 'product_id';
    const QUANTITY = 'quantity';

    /**
     * @return string|null
     */
    public function getItemId();

    /**
     * @param string $value
     * @return $this
     */
    public function setItemId($value);

    /**
     * @return string|null
     */
    public function getProductId();

    /**
     * @param string $value
     * @return $this
     */
    public function setProductId($value);

    /**
     * @return int|null
     */
    public function getQuantity();

    /**
     * @param int $value
     * @return $this
     */
    public function setQuantity($value);
}
