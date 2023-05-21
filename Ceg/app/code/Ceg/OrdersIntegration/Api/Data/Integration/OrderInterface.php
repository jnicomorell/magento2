<?php
declare(strict_types=1);

namespace Ceg\OrdersIntegration\Api\Data\Integration;

interface OrderInterface
{
    const ORDER_ID = 'order_id';
    const PRODUCTS = 'products';

    /**
     * @return string|null
     */
    public function getOrderId();

    /**
     * @param string $value
     * @return $this
     */
    public function setOrderId($value);

    /**
     * @return \Ceg\OrdersIntegration\Api\Data\Integration\ProductInterface[]
     */
    public function getProducts();

    /**
     * @param \Ceg\OrdersIntegration\Api\Data\Integration\ProductInterface[] $value
     * @return $this
     */
    public function setProducts($value);
}
