<?php
declare(strict_types=1);

namespace Ceg\CatalogPermissions\Plugin\Cart;

use Ceg\CatalogPermissions\Helper\Data as Helper;
use Magento\Checkout\Model\Session as MagentoSession;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class LoginRedirect
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * LoginRedirect constructor.
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param Helper $helper
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        Helper $helper
    ) {
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
    }

    public function afterLoadCustomerQuote(
        MagentoSession $subject,
        $result
    ) {
        $quote = $subject->getQuote();
        $loginUrl = $this->storeManager->getStore()->getUrl('customer/account/login');
        (!$this->helper->isAvailableAddToCart()) ?
            $this->checkForItems($quote) :
            $this->customerSession->setBeforeAuthUrl($loginUrl);
    }

    protected function checkForItems ($quote)
    {
        if (count($quote->getAllItems()) > 0) {
            $this->customerSession->setBeforeAuthUrl($this->storeManager->getStore()->getUrl('checkout'));
        }
    }
}
