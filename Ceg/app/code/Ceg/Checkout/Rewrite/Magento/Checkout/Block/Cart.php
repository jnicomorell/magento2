<?php
declare(strict_types=1);

namespace Ceg\Checkout\Rewrite\Magento\Checkout\Block;

use Ceg\CatalogPermissions\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Http\Context as httpContext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template\Context;

class Cart extends \Magento\Checkout\Block\Cart
{
    /**
     * @var string
     */
    private $quoteStatus;

    /**
     * @var Data|null
     */
    private $helperData;

    /**
     * @var TimezoneInterface|mixed
     */
    private $timezoneInterface;

    /**
     * Cart constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param CheckoutSession $checkoutSession
     * @param Url $catalogUrlBuilder
     * @param CartHelper $cartHelper
     * @param httpContext $httpContext
     * @param array $data
     * @param Data|null $helperData
     * @param TimezoneInterface $timezoneInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        CheckoutSession $checkoutSession,
        Url $catalogUrlBuilder,
        CartHelper $cartHelper,
        httpContext $httpContext,
        TimezoneInterface $timezoneInterface,
        Data $helperData = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $catalogUrlBuilder,
            $cartHelper,
            $httpContext,
            $data
        );
        $this->quoteStatus = $this->_checkoutSession->getQuote()->getStatus();
        $this->helperData = $helperData ?? ObjectManager::getInstance()->get(Data::class);
        $this->timezoneInterface = $timezoneInterface ?? ObjectManager::getInstance()->get(TimezoneInterface::class);
    }

    /**
     * @return bool
     */
    public function isClosed(): bool
    {
        return $this->quoteStatus === \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_CLOSED;
    }

    /**
     * @return bool
     */
    public function isOpen(): bool
    {
        return ($this->quoteStatus === \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_NEW ||
                $this->quoteStatus === \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_OPEN);
    }

    /**
     * @return bool
     */
    public function isReopen(): bool
    {
        return ($this->quoteStatus === \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_CLONED ||
                $this->quoteStatus === \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_REOPEN);
    }

    /**
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->quoteStatus === \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_APPROVED;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isValidImpo(): bool
    {
        return $this->helperData->isValidImpo();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function hasInvalidImpo(): bool
    {
        return $this->helperData->quoteHasInvalidImpo($this->getQuote());
    }

    /**
     * @return string
     */
    public function getExpiredImpoTitle(): string
    {
        return $this->helperData->getExpiredImpoTitle();
    }

    /**
     * @return string
     */
    public function getExpiredImpoMessage(): string
    {
        return $this->helperData->getExpiredImpoMessage();
    }

    /**
     * @return string
     */
    public function getReopenedMessage(): string
    {
        return $this->helperData->getReopenedMessage();
    }

    /**
     * @return string
     */
    public function getClosedMessage(): string
    {
        return $this->helperData->getClosedMessage();
    }

    /**
     * @return string
     */
    public function getReopenMessage(): string
    {
        return $this->helperData->getReopenMessage();
    }

    /**
     * @return string
     */
    public function getCannotReopenMessage(): string
    {
        return $this->helperData->getCannotReopenMessage();
    }

    /**
     * @return string
     */
    public function getCheckoutNotAllowedMessage(): string
    {
        return $this->helperData->getCustomerRestrictionCheckoutErrorMessage();
    }

    /**
     * @return string
     */
    public function getReopenButtonMessage(): string
    {
        return $this->helperData->getReopenButtonMessage();
    }

    /**
     * @return string
     */
    public function getInvalidImpoMessage(): string
    {
        return $this->helperData->getInvalidImpoMessage();
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isCheckoutAllowed(): bool
    {
        return $this->helperData->isAvailableAddToCart();
    }

    /**
     * @return string
     */
    public function getBackButtonText()
    {
        return __('Go to Home Page');
    }

    /**
     * @return string
     */
    public function getHomeUrl(): string
    {
        return $this->getUrl('/');
    }

    /**
     * @return object
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }
}
