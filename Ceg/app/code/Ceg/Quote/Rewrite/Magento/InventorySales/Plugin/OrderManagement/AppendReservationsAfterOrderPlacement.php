<?php
declare(strict_types=1);

namespace Ceg\Quote\Rewrite\Magento\InventorySales\Plugin\OrderManagement;

use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory;
use Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesEventExtensionFactory;
use Magento\InventorySalesApi\Api\Data\SalesEventExtensionInterface;
use Magento\InventorySales\Model\CheckItemsQuantity;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Ceg\Quote\Rewrite\Magento\Quote\Model\Quote as QuoteModel;
use Magento\Quote\Api\CartRepositoryInterface as QuoteRepository;

/**
 * Add reservation during order placement
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AppendReservationsAfterOrderPlacement
{
    /**
     * @var PlaceReservationsForSalesEventInterface
     */
    private $placeReservations;

    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelFactory;

    /**
     * @var SalesEventInterfaceFactory
     */
    private $salesEventFactory;

    /**
     * @var ItemToSellInterfaceFactory
     */
    private $itemsToSellFactory;

    /**
     * @var CheckItemsQuantity
     */
    private $checkItemsQuantity;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockIdResolver;

    /**
     * @var GetProductTypesBySkusInterface
     */
    private $getProdTypesBySkus;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isItemMngmtAllowed;

    /**
     * @var SalesEventExtensionFactory;
     */
    private $salesEventExtFactory;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @param PlaceReservationsForSalesEventInterface $placeReservations
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param SalesChannelInterfaceFactory $salesChannelFactory
     * @param SalesEventInterfaceFactory $salesEventFactory
     * @param ItemToSellInterfaceFactory $itemsToSellFactory
     * @param CheckItemsQuantity $checkItemsQuantity
     * @param StockByWebsiteIdResolverInterface $stockIdResolver
     * @param GetProductTypesBySkusInterface $getProdTypesBySkus
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isItemMngmtAllowed
     * @param SalesEventExtensionFactory $salesEventExtFactory
     * @param QuoteRepository $quoteRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        PlaceReservationsForSalesEventInterface $placeReservations,
        GetSkusByProductIdsInterface $getSkusByProductIds,
        WebsiteRepositoryInterface $websiteRepository,
        SalesChannelInterfaceFactory $salesChannelFactory,
        SalesEventInterfaceFactory $salesEventFactory,
        ItemToSellInterfaceFactory $itemsToSellFactory,
        CheckItemsQuantity $checkItemsQuantity,
        StockByWebsiteIdResolverInterface $stockIdResolver,
        GetProductTypesBySkusInterface $getProdTypesBySkus,
        IsSourceItemManagementAllowedForProductTypeInterface $isItemMngmtAllowed,
        SalesEventExtensionFactory $salesEventExtFactory,
        QuoteRepository $quoteRepository
    ) {
        $this->placeReservations = $placeReservations;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->websiteRepository = $websiteRepository;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->salesEventFactory = $salesEventFactory;
        $this->itemsToSellFactory = $itemsToSellFactory;
        $this->checkItemsQuantity = $checkItemsQuantity;
        $this->stockIdResolver = $stockIdResolver;
        $this->getProdTypesBySkus = $getProdTypesBySkus;
        $this->isItemMngmtAllowed = $isItemMngmtAllowed;
        $this->salesEventExtFactory = $salesEventExtFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Add reservation before place order
     *
     * In case of error during order placement exception add compensation
     *
     * @param OrderManagementInterface $subject
     * @param callable $proceed
     * @param OrderInterface $order
     * @return OrderInterface
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundPlace(
        OrderManagementInterface $subject,
        callable $proceed,
        OrderInterface $order
    ): OrderInterface {
        $itemsById = $itemsBySku = $itemsToSell = [];
        foreach ($order->getItems() as $item) {
            if (!isset($itemsById[$item->getProductId()])) {
                $itemsById[$item->getProductId()] = 0;
            }
            $itemsById[$item->getProductId()] += $item->getQtyOrdered();
        }
        $productSkus = $this->getSkusByProductIds->execute(array_keys($itemsById));
        $productTypes = $this->getProdTypesBySkus->execute($productSkus);

        foreach ($productSkus as $productId => $sku) {
            if (false === $this->isItemMngmtAllowed->execute($productTypes[$sku])) {
                continue;
            }

            $itemsBySku[$sku] = (float)$itemsById[$productId];
            $itemsToSell[] = $this->itemsToSellFactory->create([
                'sku' => $sku,
                'qty' => -(float)$itemsById[$productId]
            ]);
        }

        $websiteId = (int)$order->getStore()->getWebsiteId();
        $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();
        $stockId = (int)$this->stockIdResolver->execute((int)$websiteId)->getStockId();

        $quote = $this->quoteRepository->get((int)$order->getQuoteId());
        if ($quote->getStatus() != QuoteModel::STATUS_CLOSED) {
            $this->checkItemsQuantity->execute($itemsBySku, $stockId);
        }

        /** @var SalesEventExtensionInterface */
        $salesEventExtension = $this->salesEventExtFactory->create([
            'data' => ['objectIncrementId' => (string)$order->getIncrementId()]
        ]);

        /** @var SalesEventInterface $salesEvent */
        $salesEvent = $this->salesEventFactory->create([
            'type' => SalesEventInterface::EVENT_ORDER_PLACED,
            'objectType' => SalesEventInterface::OBJECT_TYPE_ORDER,
            'objectId' => (string)$order->getEntityId()
        ]);
        $salesEvent->setExtensionAttributes($salesEventExtension);
        $salesChannel = $this->salesChannelFactory->create([
            'data' => [
                'type' => SalesChannelInterface::TYPE_WEBSITE,
                'code' => $websiteCode
            ]
        ]);

        $this->placeReservations->execute($itemsToSell, $salesChannel, $salesEvent);

        try {
            $order = $proceed($order);
        } catch (\Exception $e) {
            //add compensation
            foreach ($itemsToSell as $item) {
                $item->setQuantity(-(float)$item->getQuantity());
            }

            /** @var SalesEventInterface $salesEvent */
            $salesEvent = $this->salesEventFactory->create([
                'type' => SalesEventInterface::EVENT_ORDER_PLACE_FAILED,
                'objectType' => SalesEventInterface::OBJECT_TYPE_ORDER,
                'objectId' => (string)$order->getEntityId()
            ]);
            $salesEvent->setExtensionAttributes($salesEventExtension);

            $this->placeReservations->execute($itemsToSell, $salesChannel, $salesEvent);

            throw $e;
        }
        return $order;
    }
}
