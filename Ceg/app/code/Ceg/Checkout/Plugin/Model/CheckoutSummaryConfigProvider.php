<?php
namespace Ceg\Checkout\Plugin\Model;

use Ceg\Checkout\Helper\Config;
use Magento\Checkout\Model\Session;
use Magento\Store\Model\StoreManagerInterface;

class CheckoutSummaryConfigProvider
{
    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CheckoutSummaryConfigProvider constructor.
     *
     * @param Config                $configHelper
     * @param Session               $checkoutSession
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $configHelper,
        Session $checkoutSession,
        StoreManagerInterface $storeManager
    ) {
        $this->configHelper = $configHelper;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
    }

    public function afterGetConfig(
        \Magento\Checkout\Model\Cart\CheckoutSummaryConfigProvider $subject,
        $result
    ) {
        $result['productsQtyInSummary'] = (int) $this->configHelper->getProductsQtyInSummary();
        $result['cartProductModels'] = $this->getModelForAllProducts();

        return $result;
    }

    public function getModelForAllProducts()
    {
        $result = [];
        $quote = $this->checkoutSession->getQuote();
        foreach ($quote->getAllVisibleItems() as $item) {
            $currentProduct = $item->getProduct();
            $resource = $currentProduct->getResource();
            $store = $this->storeManager->getStore();
            $model = $resource->getAttributeRawValue($currentProduct->getId(), 'model', $store->getId());
            $result[$item->getId()] = $model;
        }
        return $result;
    }
}
