<?php

declare(strict_types=1);

namespace Ceg\StagingSchedule\Model;

use Ceg\StagingSchedule\Api\Data\StagingScheduleInterface;
use Ceg\StagingSchedule\Api\StagingScheduleRepositoryInterface;
use Ceg\StagingSchedule\Model\ResourceModel\StagingSchedule as ResourceStagingSchedule;
use Ceg\StagingSchedule\Model\ResourceModel\StagingSchedule\CollectionFactory;
use Ceg\StagingSchedule\Api\Data\StagingScheduleSearchResultsInterfaceFactory;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\FilterBuilderFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StagingScheduleRepository implements StagingScheduleRepositoryInterface
{
    /** @var ResourceStagingSchedule */
    protected $resource;

    /** @var ExtensibleDataObjectConverter */
    protected $extObjectConverter;

    /** @var StagingScheduleSearchResultsInterfaceFactory */
    protected $searchResultsFactory;

    /** @var CollectionFactory */
    protected $scheduleCollFactory;

    /** @var StagingScheduleFactory */
    protected $scheduleFactory;

    /** @var CollectionProcessorInterface */
    protected $collectionProcessor;

    /** @var SearchCriteriaBuilderFactory */
    protected $criteriaBuilFact;

    /** @var FilterBuilderFactory */
    protected $filterBuilFact;

    /** @var  SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /**
     * @param ResourceStagingSchedule                      $resource
     * @param StagingScheduleFactory                       $scheduleFactory
     * @param CollectionFactory                            $scheduleCollFactory
     * @param StagingScheduleSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface                 $collectionProcessor
     * @param SearchCriteriaBuilderFactory                 $criteriaBuilFact
     * @param FilterBuilderFactory                         $filterBuilFact
     * @param ExtensibleDataObjectConverter                $extObjectConverter
     * @param SearchCriteriaBuilder                        $searchCriteriaBuilder
     */
    public function __construct(
        ResourceStagingSchedule $resource,
        StagingScheduleFactory $scheduleFactory,
        CollectionFactory $scheduleCollFactory,
        StagingScheduleSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchCriteriaBuilderFactory $criteriaBuilFact,
        FilterBuilderFactory $filterBuilFact,
        ExtensibleDataObjectConverter $extObjectConverter,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->resource = $resource;
        $this->scheduleFactory = $scheduleFactory;
        $this->scheduleCollFactory = $scheduleCollFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->criteriaBuilFact = $criteriaBuilFact;
        $this->filterBuilFact = $filterBuilFact;
        $this->extObjectConverter = $extObjectConverter;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function save(StagingScheduleInterface $stagingSchedule)
    {
        $stagingScheduleData = $this->extObjectConverter
            ->toNestedArray($stagingSchedule, [], StagingScheduleInterface::class);

        $stagingScheduleModel = $this->scheduleFactory->create()->setData($stagingScheduleData);

        try {
            $this->resource->save($stagingScheduleModel);
        } catch (\Exception $exception) {
            $message = __('Could not save the stagingSchedule: %1', $exception->getMessage());
            throw new CouldNotSaveException($message);
        }
        return $stagingScheduleModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($stagingId)
    {
        $stagingSchedule = $this->scheduleFactory->create();
        $this->resource->load($stagingSchedule, $stagingId);
        if (!$stagingSchedule->getId()) {
            $message = __('staging_schedule with id "%1" does not exist.', $stagingId);
            throw new NoSuchEntityException($message);
        }
        return $stagingSchedule->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->scheduleCollFactory->create();
        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    public function getListByEntity($entity, $entityId)
    {
        $criteriaBuilder = $this->criteriaBuilFact->create();
        $filterBuilder = $this->filterBuilFact->create();

        $filter1 = $filterBuilder
            ->setField(StagingScheduleInterface::ENTITY)
            ->setValue($entity)
            ->create();

        $filter2 = $filterBuilder
            ->setField(StagingScheduleInterface::ENTITY_ID)
            ->setValue($entityId)
            ->create();

        $criteriaBuilder->addFilters([$filter1, $filter2]);

        return $this->getList($criteriaBuilder->create());
    }

    public function getPendingSchedules()
    {
        $criteriaBuilder = $this->criteriaBuilFact->create();
        $filterBuilder = $this->filterBuilFact->create();

        $filter1 = $filterBuilder
            ->setField(StagingScheduleInterface::STATUS)
            ->setValue(StagingScheduleInterface::STATUS_PENDING)
            ->create();

        $criteriaBuilder->addFilters([$filter1]);

        return $this->getList($criteriaBuilder->create());
    }

    public function getRunningSchedules($lastHourDateTime)
    {
        $this->searchCriteriaBuilder->addFilter(StagingScheduleInterface::STATUS,StagingScheduleInterface::STATUS_RUNNING);
        $this->searchCriteriaBuilder->addFilter(StagingScheduleInterface::DATETIME, $lastHourDateTime, 'lteq');
        $searchCriteria = $this->searchCriteriaBuilder->create();
        return $this->getList($searchCriteria)->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(StagingScheduleInterface $stagingSchedule)
    {
        try {
            $stagingScheduleModel = $this->scheduleFactory->create();
            $this->resource->load($stagingScheduleModel, $stagingSchedule->getStagingId());
            $this->resource->delete($stagingScheduleModel);
        } catch (\Exception $exception) {
            $message = __('Could not delete the staging_schedule: %1', $exception->getMessage());
            throw new CouldNotDeleteException($message);
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($stagingId)
    {
        return $this->delete($this->get($stagingId));
    }
}
