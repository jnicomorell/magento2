<?php
declare(strict_types=1);

namespace Ceg\UserRole\Model\Repository;

use Ceg\UserRole\Api\Data\AdditionalRolesResultsInterface;
use Ceg\UserRole\Model\AdditionalRoles;
use Ceg\UserRole\Model\ResourceModel\AdditionalRoles as AdditionalRolesResource;
use Ceg\UserRole\Model\AdditionalRolesFactory as AdditionalRolesModel;
use Ceg\UserRole\Model\ResourceModel\AdditionalRoles\CollectionFactory as AdditionalRolesCollection;
use Ceg\UserRole\Api\Data\AdditionalRolesResultsInterfaceFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class AdditionalRolesRepository
{
    /**
     * @var AdditionalRolesResource
     */
    protected $additionalRolesResource;

    /**
     * @var AdditionalRolesModel
     */
    protected $additionalRolesModel;

    /**
     * @var AdditionalRolesCollection
     */
    protected $additionalRolesCollection;

    /**
     * @var AdditionalRolesResultsInterfaceFactory
     */
    protected $additionalRolesSearchResults;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * AdditionalRolesRepository constructor.
     * @param AdditionalRolesResource $additionalRolesResource
     * @param AdditionalRolesModel $additionalRolesModel
     * @param AdditionalRolesCollection $additionalRolesCollection
     * @param AdditionalRolesResultsInterfaceFactory $additionalRolesSearchResults
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        AdditionalRolesResource $additionalRolesResource,
        AdditionalRolesModel $additionalRolesModel,
        AdditionalRolesCollection $additionalRolesCollection,
        AdditionalRolesResultsInterfaceFactory $additionalRolesSearchResults,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->additionalRolesResource = $additionalRolesResource;
        $this->additionalRolesModel = $additionalRolesModel;
        $this->additionalRolesCollection = $additionalRolesCollection;
        $this->additionalRolesSearchResults = $additionalRolesSearchResults;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Save Additional Roles
     * @param AdditionalRoles $additionalRoles
     * @return AdditionalRoles
     * @throws CouldNotSaveException
     */
    public function save(AdditionalRoles $additionalRoles): AdditionalRoles
    {
        try {
            $this->additionalRolesResource->save($additionalRoles);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $additionalRoles;
    }

    /**
     * Load Additional Roles data by given User Identity
     *
     * @param int $userId
     * @return AdditionalRoles
     * @throws NoSuchEntityException
     */
    public function getByUserId(int $userId): AdditionalRoles
    {
        $additionalRole = $this->additionalRolesModel->create();
        $this->additionalRolesResource->load($additionalRole, $userId, 'user_id');

        if (!$additionalRole->getId()) {
            throw new NoSuchEntityException(__('The additional roles of user ID "%1" doesn\'t exist.', $userId));
        }

        return $additionalRole;
    }

    /**
     * Load Additional Roles data collection by given search criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @return AdditionalRolesResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria): AdditionalRolesResultsInterface
    {
        /** @var AdditionalRolesResource\Collection $collection */
        $collection = $this->additionalRolesCollection->create();

        $this->collectionProcessor->process($criteria, $collection);

        /** @var AdditionalRolesResultsInterface $searchResults */
        $searchResults = $this->additionalRolesSearchResults->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * Delete Additional Roles
     *
     * @param AdditionalRoles $additionalRoles
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(AdditionalRoles $additionalRoles): bool
    {
        try {
            $this->additionalRolesResource->delete($additionalRoles);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }

        return true;
    }

    /**
     * Delete Additional Roles by given User Identity
     *
     * @param int $userId
     * @return bool
     * @throws CouldNotDeleteException|NoSuchEntityException
     */
    public function deleteByUserId(int $userId): bool
    {
        return $this->delete($this->getByUserId($userId));
    }
}
