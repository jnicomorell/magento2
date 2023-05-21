<?php
declare(strict_types=1);

namespace Ceg\Impo\Api\Data\Integration;

interface ImpoInterface
{
    const CEG_ID = 'ceg_id';
    const WEBSITE_ID = 'website_id';
    const IS_ACTIVE = 'is_active';
    const FOB = 'free_on_board';
    const TITLE = 'title';
    const START_AT = 'start_at';
    const FINISH_AT = 'finish_at';
    const PRODUCTS = 'products';

    /**
     * @return string
     */
    public function getCegId();

    /**
     * @param string $value
     * @return $this
     */
    public function setCegId($value);

    /**
     * @return int|null
     */
    public function getWebsiteId();

    /**
     * @param int $value
     * @return $this
     */
    public function setWebsiteId($value);

    /**
     * @return int
     */
    public function getIsActive();

    /**
     * @param int $value
     * @return $this
     */
    public function setIsActive($value);

    /**
     * @return float|null
     */
    public function getFob();

    /**
     * @param float $value
     * @return $this
     */
    public function setFob($value);

    /**
     * @return string|null
     */
    public function getTitle();

    /**
     * @param string $value
     * @return $this
     */
    public function setTitle($value);

    /**
     * @return string|null
     */
    public function getStartAt();

    /**
     * @param string $value
     * @return $this
     */
    public function setStartAt($value);

    /**
     * @return string|null
     */
    public function getFinishAt();

    /**
     * @param string $value
     * @return $this
     */
    public function setFinishAt($value);

    /**
     * @return \Ceg\Impo\Api\Data\Integration\ProductInterface[]
     */
    public function getProducts();

    /**
     * @param \Ceg\Impo\Api\Data\Integration\ProductInterface[] $value
     * @return  $this
     */
    public function setProducts($value);
}
