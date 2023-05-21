<?php
declare(strict_types=1);

namespace Ceg\Quote\ViewModel;

use Datetime;
use Ceg\Quote\Rewrite\Magento\Quote\Model\Quote;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class QuoteData implements ArgumentInterface
{
    /**
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var Currency|null
     */
    protected $baseCurrency = null;

    /**
     * @var CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @param Registry                  $registry
     * @param TimezoneInterface         $localeDate
     * @param StoreManagerInterface     $storeManager
     * @param GroupRepositoryInterface  $groupRepository
     * @param CurrencyFactory           $currencyFactory
     */
    public function __construct(
        Registry $registry,
        TimezoneInterface $localeDate,
        StoreManagerInterface $storeManager,
        GroupRepositoryInterface $groupRepository,
        CurrencyFactory $currencyFactory
    ) {
        $this->coreRegistry = $registry;
        $this->localeDate = $localeDate;
        $this->storeManager = $storeManager;
        $this->groupRepository = $groupRepository;
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * @return Quote
     */
    public function getQuote()
    {
        return $this->coreRegistry->registry('current_quote');
    }

    public function getQuoteId()
    {
        return $this->getQuote() ? $this->getQuote()->getId() : null;
    }

    public function getTimezoneForStore($store)
    {
        return $this->localeDate->getConfigTimezone(
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
    }

    public function getQuoteAdminDate($createdAt)
    {
        return $this->localeDate->date(new DateTime($createdAt));
    }

    public function getStatusLabel($code)
    {
        return ucwords($code);
    }

    public function getQuoteStoreName()
    {
        if ($this->getQuote()) {
            $storeId = $this->getQuote()->getStoreId();
            if ($storeId === null) {
                $deleted = __(' [deleted]');
                return nl2br($this->getQuote()->getStoreName()) . $deleted;
            }
            $store = $this->storeManager->getStore($storeId);
            $name = [$store->getWebsite()->getName(), $store->getGroup()->getName(), $store->getName()];
            return implode('<br/>', $name);
        }

        return null;
    }

    public function getCustomerGroupName()
    {
        if ($this->getQuote()) {
            $customerGroupId = $this->getQuote()->getCustomerGroupId();
            try {
                if ($customerGroupId !== null) {
                    return $this->groupRepository->getById($customerGroupId)->getCode();
                }
            } catch (NoSuchEntityException $e) {
                return '';
            }
        }

        return '';
    }

    public function getCustomerTaxvat()
    {
        if ($this->getQuote()) {
            $customer = $this->getQuote()->getCustomer();
            $customerTaxvat = $customer->getTaxvat();
            if ($customerTaxvat !== null) {
                return $customerTaxvat;
            }
        }
        return '';
    }

    public function getCustomerCompanyName()
    {
        if ($this->getQuote()) {
            $customer = $this->getQuote()->getCustomer();
            $companyName = $customer->getCustomAttribute('company_name');
            if (!empty($companyName)) {
                return $companyName->getValue();
            }
        }
        return '';
    }

    public function getFormattedAddress($address)
    {
        return $address->format('html');
    }

    public function formatPrice($price, $precision = 3)
    {
        if ($this->baseCurrency === null) {
            $currencyCode = $this->getQuote()->getBaseCurrencyCode();
            $this->baseCurrency = $this->currencyFactory->create()->load($currencyCode);
        }
        return $this->baseCurrency->formatPrecision($price, $precision);
    }
}
