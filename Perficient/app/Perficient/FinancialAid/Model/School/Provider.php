<?php
namespace Perficient\FinancialAid\Model\School;

use Magento\Framework\Convert\DataObject as Converter;
use Vhl\Salesforce\Api\SchoolRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

class Provider
{
    /**
     * @var SchoolRepositoryInterface
     */
    private $schoolRepository;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var int
     */
    private $pageSize = 100;

    /**
     * Initialize dependencies.
     *
     * @param SchoolRepositoryInterface $schoolRepository
     * @param Converter $converter
     */
    public function __construct(
        SchoolRepositoryInterface $schoolRepository,
        Converter $converter
    ) {
        $this->schoolRepository = $schoolRepository;
        $this->converter = $converter;
    }

    /**
     * Retrieve all schools as an options array.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return array
     */
    public function toOptionArray(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->schoolRepository->getList($searchCriteria);

        return $this->converter->toOptionArray(
            $searchResults->getItems(),
            'name',
            'name'
        );
    }

    /**
     * Returns predefined size of schools list
     *
     * @return int
     */
    public function getPageSize()
    {
        return (int) $this->pageSize;
    }
}
