<?php

declare(strict_types=1);

namespace Ceg\StagingSchedule\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface StagingScheduleSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return StagingScheduleInterface[]
     */
    public function getItems();

    /**
     * @param StagingScheduleInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
