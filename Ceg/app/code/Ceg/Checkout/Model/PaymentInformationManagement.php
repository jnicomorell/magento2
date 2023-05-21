<?php
declare(strict_types=1);

namespace Ceg\Checkout\Model;

use Magento\Checkout\Api\PaymentProcessingRateLimiterInterface;
use Magento\Checkout\Model\PaymentDetailsFactory;
use Magento\Checkout\Model\PaymentInformationManagement as MagentoPaymentInformationManagement;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\AddressFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;

class PaymentInformationManagement extends MagentoPaymentInformationManagement
{
    /**
     * @var PaymentProcessingRateLimiterInterface|mixed
     */
    private $quoteRepository;

    /**
     * @var Session|mixed
     */
    private $checkoutSession;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * PaymentInformationManagement constructor.
     * @param BillingAddressManagementInterface $billingAddressManagement
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param CartManagementInterface $cartManagement
     * @param PaymentDetailsFactory $paymentDetailsFactory
     * @param CartTotalRepositoryInterface $cartTotalsRepository
     * @param PaymentProcessingRateLimiterInterface|null $paymentRateLimiter
     * @param CartRepositoryInterface|null $quoteRepository
     * @param Session|null $checkoutSession
     * @param AddressFactory|null $addressFactory
     */
    public function __construct(
        BillingAddressManagementInterface $billingAddressManagement,
        PaymentMethodManagementInterface $paymentMethodManagement,
        CartManagementInterface $cartManagement,
        PaymentDetailsFactory $paymentDetailsFactory,
        CartTotalRepositoryInterface $cartTotalsRepository,
        ?PaymentProcessingRateLimiterInterface $paymentRateLimiter = null,
        ?CartRepositoryInterface $quoteRepository = null,
        Session $checkoutSession = null,
        AddressFactory $addressFactory = null
    ) {
        parent::__construct(
            $billingAddressManagement,
            $paymentMethodManagement,
            $cartManagement,
            $paymentDetailsFactory,
            $cartTotalsRepository,
            $paymentRateLimiter
        );
        $this->checkoutSession = $checkoutSession  ?? ObjectManager::getInstance()->get(Session::class);
        $this->quoteRepository = $quoteRepository ?? ObjectManager::getInstance()->get(CartRepositoryInterface::class);
        $this->addressFactory = $addressFactory ?? ObjectManager::getInstance()->get(AddressFactory::class);
    }

    /**
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function savePaymentInformationAndPlaceOrder(
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $this->savePaymentInformation($cartId, $paymentMethod, $billingAddress);

        $quote = $this->quoteRepository->get($cartId);
        $quote->approve();
        $quote->clone();

        $this->checkoutSession->setLastSuccessQuoteId($cartId);
        $this->checkoutSession->setLastQuoteId($cartId);
        $this->checkoutSession->setLastOrderId($quote->getId());
        $this->checkoutSession->setLastReservedId($quote->getReservedOrderId());

        return $quote->getReservedOrderId();
    }
}
