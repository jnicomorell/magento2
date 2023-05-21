<?php
namespace Ceg\Impo\Plugin;

use Ceg\Impo\Api\ImpoRepositoryInterfaceFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;

class Quote
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ImpoRepositoryInterfaceFactory
     */
    private $impoRepoFactory;

    /**
     * @param Json $json
     * @param ImpoRepositoryInterfaceFactory $impoRepoFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Json $json,
        ImpoRepositoryInterfaceFactory $impoRepoFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->json = $json;
        $this->impoRepoFactory = $impoRepoFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAddItem(
        \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote $subject,
        \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote\Item $item
    ) {
        $product = $item->getProduct();

        $qtyinbox = (int)$product->getQtyinbox() > 0 ? (int)$product->getQtyinbox() : 1;
        $item->setQtyinbox($qtyinbox);

        $fobSubtotal = $product->getFobSubtotal();
        $item->setFobSubtotal($fobSubtotal);

        $fobUnit = $product->getFobUnit();
        $item->setFobUnit($fobUnit);

        $impoId = $product->getImpoId();
        $item->setImpoId($impoId);

        return [$item];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function beforeBeforeSave(
        \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote $subject
    ) {
        $impoIdsJson = $this->getImpoIdsJson($subject->getAllVisibleItems());
        $subject->setImpoIds($impoIdsJson);
        return [$subject];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllVisibleItems(
        \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote $subject,
        $result
    ) {
        $impoRepository = $this->impoRepoFactory->create();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();

        $validImpos = $impoRepository->getListActiveImpo($websiteId)->getItems();
        foreach ($result as $item) {
            $item->setIsValidImpo(true);
            $item->setIsValidProduct(true);
            if (!array_key_exists($item->getImpoId(), $validImpos)) {
                $item->setIsValidImpo(false);
            }
            $validProducts = $impoRepository->getProductIdsForActiveImpo($websiteId);
            if (!array_key_exists($item->getProductId(), $validProducts)) {
                $item->setIsValidProduct(false);
            }
        }
        return $result;
    }

    public function afterCreateChildQuote(
        \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote $subject,
        $result
    ) {
        $parentItems = $subject->getAllVisibleItems();
        $childItems = $result->getAllVisibleItems();
        foreach ($childItems as $childItem) {
            foreach ($parentItems as $parentItem) {
                if ($childItem->getProductId() == $parentItem->getProductId()) {
                    $childItem->setImpoId($parentItem->getImpoId());
                }
            }
        }
        $result->setImpoIds($this->getImpoIdsJson($childItems));
        $result->save();
        return $result;
    }

    private function getImpoIdsJson($items)
    {
        $impoIds = [];
        if (is_array($items) || is_object($items)) {
            foreach ($items as $item) {
                if (!$item->isDeleted()) {
                    if (!empty($item->getImpoId())) {
                        if (!in_array($item->getImpoId(), $impoIds)) {
                            array_push($impoIds, $item->getImpoId());
                        }
                    }
                }
            }
        }
        return $this->json->serialize($impoIds);
    }
}
