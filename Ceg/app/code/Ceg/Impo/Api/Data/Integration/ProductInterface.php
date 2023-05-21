<?php
declare(strict_types=1);

namespace Ceg\Impo\Api\Data\Integration;

interface ProductInterface
{
    const SKU = 'sku';

    /**
     * @return string
     */
    public function getSku();

    /**
     * @param string $value
     * @return $this
     */
    public function setSku($value);
}
