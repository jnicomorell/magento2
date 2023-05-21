<?php

namespace Ceg\Quote\Rewrite\Magento\Quote\Model;

use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Ceg\Quote\Rewrite\Magento\Quote\Model\Quote as QuoteModel;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Quote\Api\Data\CartSearchResultsInterfaceFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;

class QuoteRepository extends \Magento\Quote\Model\QuoteRepository
{
    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $scBuilderFactory;

    /**
     * Constructor
     *
     * @param QuoteFactory                      $quoteFactory
     * @param StoreManagerInterface             $storeManager
     * @param QuoteCollection                   $quoteCollection
     * @param CartSearchResultsInterfaceFactory $srDataFactory
     * @param JoinProcessorInterface            $extAttJoinProcessor
     * @param CollectionProcessorInterface|null $collectionProcessor
     * @param QuoteCollectionFactory|null       $quoteCollFactory
     * @param CartInterfaceFactory|null         $cartFactory
     * @param SearchCriteriaBuilderFactory|null $scBuilderFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        StoreManagerInterface $storeManager,
        QuoteCollection $quoteCollection,
        CartSearchResultsInterfaceFactory $srDataFactory,
        JoinProcessorInterface $extAttJoinProcessor,
        CollectionProcessorInterface $collectionProcessor = null,
        QuoteCollectionFactory $quoteCollFactory = null,
        CartInterfaceFactory $cartFactory = null,
        SearchCriteriaBuilderFactory $scBuilderFactory = null
    ) {
        $this->scBuilderFactory = $scBuilderFactory ?: ObjectManager::getInstance()
            ->get(SearchCriteriaBuilderFactory::class);

        parent::__construct(
            $quoteFactory,
            $storeManager,
            $quoteCollection,
            $srDataFactory,
            $extAttJoinProcessor,
            $collectionProcessor,
            $quoteCollFactory,
            $cartFactory
        );
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getActive($cartId, array $sharedStoreIds = [])
    {
        $quote = $this->get($cartId, $sharedStoreIds);
        if (!$quote->getIsActive() && $quote->getStatus() != QuoteModel::STATUS_CLOSED) {
            throw NoSuchEntityException::singleField('cartId', $cartId);
        }
        return $quote;
    }

    public function cancel($quote)
    {
        if ($quote->isApproved()) {
            $scBuilder = $this->scBuilderFactory->create();
            $scBuilder->addFilter('parent_quote_id', $quote->getId());
            $searchCriteria = $scBuilder->create();
            $quoteChilds = $this->getList($searchCriteria);
            if ($quoteChilds->getTotalCount() > 0) {
                $child = $quoteChilds->getItems()[0];
                $child->open();
            }
        }
        $quote->cancel();
        return $quote;
    }
}
