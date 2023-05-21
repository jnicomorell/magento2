<?php
namespace Ceg\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product\Action;
use Magento\Checkout\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Api\TierPriceStorageInterface;

class ProductSaveAfter implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var Action
     */
    private $productAction;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TierPriceStorageInterface
     */
    private $tierPrice;

    public function __construct(
        Session $_checkoutSession,
        Action $productAction,
        StoreManagerInterface $storeManager,
        TierPriceStorageInterface $tierPrice
    ) {
        $this->_checkoutSession = $_checkoutSession;
        $this->productAction = $productAction;
        $this->storeManager = $storeManager;
        $this->tierPrice = $tierPrice;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        $result = $this->getTierPrice([$product->getSku()]);
        if (count($result)) {
            $prices=[];
            foreach ($result as $item) {
                $prices[]=$item->getData()['price'];
            }
            sort($prices);
            $tierprice = $prices[0];
            $storeId = $this->storeManager->getStore()->getId();

            $qtyInBox= (int)$product->getQtyinbox() > 0 ? (int)$product->getQtyinbox() : 1;
            $unitPrice = number_format($tierprice / $qtyInBox, 2);
            $pid = $product->getId();
            $this->productAction->updateAttributes([$pid], ['unit_price' => $unitPrice], $storeId);
        }

		return $this;

    }
    /**
     * tier price result
     *
     * @param array $sku
     * @return TierPriceInterface[]
     */
    public function getTierPrice(array $sku)
    {
        $result = [];
        try {
             $result = $this->tierPrice->get($sku);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return $result;
    }

}
