<?php
declare(strict_types=1);

namespace Ceg\StagingSchedule\Api;

use Ceg\StagingSchedule\Api\Data\StagingScheduleInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface StagingScheduleRepositoryInterface
{

    /**
     * Save staging_schedule
     * @param StagingScheduleInterface $stagingSchedule
     * @return StagingScheduleInterface
     * @throws LocalizedException
     */
    public function save(
        StagingScheduleInterface $stagingSchedule
    );

    /**
     * Retrieve staging_schedule
     * @param string $stagingId
     * @return StagingScheduleInterface
     * @throws LocalizedException
     */
    public function get($stagingId);

    /**
     * Retrieve staging_schedule matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete staging_schedule
     * @param StagingScheduleInterface $stagingSchedule
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        StagingScheduleInterface $stagingSchedule
    );

    /**
     * Delete staging_schedule by ID
     * @param string $stagingId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($stagingId);
}
