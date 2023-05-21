<?php
declare(strict_types=1);

namespace Ceg\CatalogPermissions\Helper;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Ceg\Impo\Api\ImpoRepositoryInterfaceFactory;

/**
 * Helper
 */
class Data extends AbstractHelper
{
    const XML_CONFIG_HIDE_ADD_TO_CART = 'catalog_permissions/available/hide_add_to_cart';

    const XML_CONFIG_HIDE_ADD_TO_CART_GROUPS = 'catalog_permissions/available/hide_add_to_cart_groups';

    const XML_CONFIG_HIDE_PRICE = 'catalog_permissions/available/hide_price';

    const XML_CONFIG_HIDE_PRICE_GROUPS = 'catalog_permissions/available/hide_price_groups';

    const XML_CONFIG_HIDE_CATALOG_GROUPS = 'catalog_restrict/customer_group/hide_for_groups';

    const XML_CONFIG_CATALOG_RESTRICT_ENABLE = 'catalog_restrict/customer_group/enable';

    const XML_CONFIG_CATALOG_RESTRICT_PRODUCT_ERR_MSG = 'catalog_restrict/customer_group/product_err_msg';

    const XML_CONFIG_CATALOG_RESTRICT_CATEGORY_ERR_MSG = 'catalog_restrict/customer_group/category_err_msg';

    const XML_CONFIG_CATALOG_RESTRICT_CART_ERR_MSG = 'catalog_restrict/customer_group/cart_err_msg';

    const XML_CONFIG_CATALOG_RESTRICT_CHECKOUT_ERR_MSG = 'catalog_restrict/customer_group/cart_not_allowed_msg';

    const XML_CONFIG_CHECKOUT_APPROVED_CART_MSG = 'ceg_checkout/general/approved_cart_message';

    const XML_CONFIG_CHECKOUT_CLOSED_CART_MSG = 'ceg_checkout/general/closed_cart_message';

    const XML_CONFIG_CHECKOUT_REOPEN_CART_MSG = 'ceg_checkout/general/reopen_message';

    const XML_CONFIG_CHECKOUT_CANNOT_REOPEN_CART_MSG = 'ceg_checkout/general/cannot_reopen_message';

    const XML_CONFIG_CHECKOUT_REOPENED_CART_MSG = 'ceg_checkout/general/reopened_message';

    const XML_CONFIG_CHECKOUT_REOPEN_BUTTON_CART_MSG = 'ceg_checkout/general/reopen_button_message';

    const XML_CONFIG_CHECKOUT_WITH_INVALID_IMPO_MSG = 'ceg_checkout/general/invalid_impo_message';

    const XML_CONFIG_IMPO_EXPIRED_TITLE = 'impo/general/expired_title';

    const XML_CONFIG_IMPO_EXPIRED_MSG = 'impo/general/expired_message';

    /**
     * Customer session
     *
     * @var Session
     */
    protected $_session;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ImpoRepositoryInterfaceFactory
     */
    protected $impoRepoFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * Initialize helper
     *
     * @param Context $context
     * @param Session $session
     * @param CustomerRepositoryInterface $customerRepository
     * @param ImpoRepositoryInterfaceFactory $impoRepoFactory
     * @param StoreManagerInterface $storeManager
     * @param Json $json
     */
    public function __construct(
        Context $context,
        Session $session,
        CustomerRepositoryInterface $customerRepository,
        ImpoRepositoryInterfaceFactory $impoRepoFactory,
        StoreManagerInterface $storeManager,
        Json $json
    ) {
        $this->_session = $session;
        $this->customerRepository = $customerRepository;
        $this->impoRepoFactory = $impoRepoFactory;
        $this->storeManager = $storeManager;
        $this->json = $json;
        parent::__construct($context);
    }

    /**
     * @return string|null
     */
    public function isCatalogRestrictionEnable()
    {
        return $this->_getConfig(self::XML_CONFIG_CATALOG_RESTRICT_ENABLE) ? true : false;
    }

    /**
     * Check whether the customer allows add to cart
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isAvailableAddToCart()
    {
        if ($this->_getConfig(self::XML_CONFIG_HIDE_ADD_TO_CART)) {

            if ($this->_session->isLoggedIn()) {
                $customer = $this->customerRepository->getById($this->_session->getCustomer()->getEntityId());

                if ($customer->getCustomAttribute('checkout_disabled')->getValue()) {
                    return false;
                }
            }

            return !in_array(
                $this->_session->getCustomerGroupId(),
                explode(',', $this->_getConfig(self::XML_CONFIG_HIDE_ADD_TO_CART_GROUPS))
            );
        }
        return true;
    }

    /**
     * Check whether the customer allows price
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isAvailablePrice()
    {
        if ($this->_getConfig(self::XML_CONFIG_HIDE_PRICE)) {
            return !in_array(
                $this->_session->getCustomerGroupId(),
                explode(',', $this->_getConfig(self::XML_CONFIG_HIDE_PRICE_GROUPS))
            );
        }
        return true;
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isCatalogAvailable()
    {
        if ($this->_getConfig(self::XML_CONFIG_CATALOG_RESTRICT_ENABLE)) {
            return !in_array(
                $this->_session->getCustomerGroupId(),
                explode(',', $this->_getConfig(self::XML_CONFIG_HIDE_CATALOG_GROUPS))
            );
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isValidImpo(): bool
    {
        $impoRepository = $this->impoRepoFactory->create();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $impoCollection = $impoRepository->getListActiveImpo($websiteId);

        if ($impoCollection->count() > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param Quote $quote
     * @return bool
     * @throws Exception
     */
    public function quoteHasInvalidImpo($quote): bool
    {
        $result = false;
        $impoRepository = $this->impoRepoFactory->create();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $impoCollection = $impoRepository->getListActiveImpo($websiteId);
        $quoteImpoIds = $quote->getImpoIds() ? $quote->getImpoIds() : '[]';
        $impoIds = $this->json->unserialize($quoteImpoIds);
        foreach ($impoIds as $impoId) {
            $invalidImpo = true;
            foreach ($impoCollection as $impo) {
                if ($impoId == $impo->getId()) {
                    $invalidImpo = false;
                }
            }
            if ($invalidImpo) {
                return true;
            }
        }
        return $result;
    }

    /**
     * @return string|null
     */
    public function getCustomerRestrictionProductErrorMessage(): ?string
    {
        return $this->_getConfig(self::XML_CONFIG_CATALOG_RESTRICT_PRODUCT_ERR_MSG);
    }

    /**
     * @return string|null
     */
    public function getCustomerRestrictionCategoryErrorMessage(): ?string
    {
        return $this->_getConfig(self::XML_CONFIG_CATALOG_RESTRICT_CATEGORY_ERR_MSG);
    }

    /**
     * @return string|null
     */
    public function getCustomerRestrictionCartErrorMessage(): ?string
    {
        return $this->_getConfig(self::XML_CONFIG_CATALOG_RESTRICT_CART_ERR_MSG);
    }

    /**
     * @return string|null
     */
    public function getCustomerRestrictionCheckoutErrorMessage(): ?string
    {
        return $this->_getConfig(self::XML_CONFIG_CATALOG_RESTRICT_CHECKOUT_ERR_MSG);
    }

    /**
     * @return string|null
     */
    public function getExpiredImpoTitle()
    {
        return $this->_getConfig(self::XML_CONFIG_IMPO_EXPIRED_TITLE);
    }

    /**
     * @return string|null
     */
    public function getExpiredImpoMessage(): ?string
    {
        return $this->_getConfig(self::XML_CONFIG_IMPO_EXPIRED_MSG);
    }

    /**
     * @return string|null
     */
    public function getApprovedCartMessage(): ?string
    {
        return $this->_getConfig(self::XML_CONFIG_CHECKOUT_APPROVED_CART_MSG);
    }

    /**
     * @return string|null
     */
    public function getClosedMessage(): ?string
    {
        return $this->_getConfig(self::XML_CONFIG_CHECKOUT_CLOSED_CART_MSG);
    }

    /**
     * @return string|null
     */
    public function getReopenMessage(): ?string
    {
        return $this->_getConfig(self::XML_CONFIG_CHECKOUT_REOPEN_CART_MSG);
    }

    /**
     * @return string|null
     */
    public function getCannotReopenMessage(): ?string
    {
        return $this->_getConfig(self::XML_CONFIG_CHECKOUT_CANNOT_REOPEN_CART_MSG);
    }

    /**
     * @return string|null
     */
    public function getReopenedMessage(): ?string
    {
        return $this->_getConfig(self::XML_CONFIG_CHECKOUT_REOPENED_CART_MSG);
    }

    /**
     * @return string|null
     */
    public function getReopenButtonMessage(): ?string
    {
        return $this->_getConfig(self::XML_CONFIG_CHECKOUT_REOPEN_BUTTON_CART_MSG);
    }

    /**
     * @return string|null
     */
    public function getInvalidImpoMessage(): ?string
    {
        return $this->_getConfig(self::XML_CONFIG_CHECKOUT_WITH_INVALID_IMPO_MSG);
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCurrentCustomerGroupId(): int
    {
        return $this->_session->getCustomerGroupId();
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function _getConfig(string $path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }
}
