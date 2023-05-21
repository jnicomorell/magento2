<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Perficient\FinancialAid\Controller\Adminhtml\School;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Perficient\FinancialAid\Model\School\Provider as SchoolProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class AjaxLoadSchools is intended to load existing
 * Tax rates as options for a select element.
 */
class AjaxLoadSchools extends Action
{
    /**
     * @var SchoolProvider
     */
    private $schoolProvider;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param Context $context
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SchoolProvider $schoolProvider
     */
    public function __construct(
        Context $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SchoolProvider $schoolProvider
    ) {
        parent::__construct($context);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->schoolProvider = $schoolProvider;
    }

    /**
     * Get school page via AJAX
     *
     * @return Json
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        $schoolPage = (int) $this->getRequest()->getParam('p');
        $schoolFilter = trim($this->getRequest()->getParam('s'));

        try {
            if (!empty($schoolFilter)) {
                $this->searchCriteriaBuilder->addFilter(
                    'name',
                    '%'.$schoolFilter.'%',
                    'like'
                );
            }

            $searchCriteria = $this->searchCriteriaBuilder
                ->setPageSize($this->schoolProvider->getPageSize())
                ->setCurrentPage($schoolPage)
                ->create();

            $options = $this->schoolProvider->toOptionArray($searchCriteria);

            $response = [
                'success' => true,
                'errorMessage' => '',
                'result'=> $options,
            ];
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'errorMessage' => __('An error occurred while loading schools.')
            ];
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($response);

        return $resultJson;
    }
}
