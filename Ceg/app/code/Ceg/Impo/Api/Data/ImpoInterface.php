<?php
declare(strict_types=1);

namespace Ceg\Impo\Api\Data;

interface ImpoInterface
{
    const ID = 'entity_id';
    const WEBSITE_ID = 'website_id';
    const CEG_ID = 'ceg_id';
    const IS_ACTIVE = 'is_active';
    const FREE_ON_BOARD = 'free_on_board';
    const TITLE = 'title';
    const START_AT = 'start_at';
    const FINISH_AT = 'finish_at';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const RELATED_PRODUCTS = 'related_products';
    const QUOTE_IMPO_IDS = 'impo_ids';
    const QUOTEITEM_IMPO_ID = 'impo_id';
    const ORDER_IMPO_IDS = 'impo_ids';
    const ORDERITEM_IMPO_ID = 'impo_id';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $value
     * @return $this
     */
    public function setId($value);

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
     * @return string
     */
    public function getCegId();

    /**
     * @param string $value
     * @return $this
     */
    public function setCegId($value);

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
    public function getFreeOnBoard();

    /**
     * @param float $value
     * @return $this
     */
    public function setFreeOnBoard($value);

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
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @return string|null
     */
    public function getUpdatedAt();
}
