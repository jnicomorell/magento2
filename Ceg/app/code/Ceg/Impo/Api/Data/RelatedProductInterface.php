<?php
declare(strict_types=1);

namespace Ceg\Impo\Api\Data;

interface RelatedProductInterface
{
    const ID = 'entity_id';
    const IMPO_ID = 'impo_id';
    const PRODUCT_ID = 'product_id';
    
    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $value
     * @return self
     */
    public function setId($value);

    /**
     * @return int|null
     */
    public function getImpoId(): int;

    /**
     * @param int $value
     * @return self
     */
    public function setImpoId($value);

    /**
     * @return int
     */
    public function getProductId(): int;

    /**
     * @param int $value
     * @return self
     */
    public function setProductId($value);
}
