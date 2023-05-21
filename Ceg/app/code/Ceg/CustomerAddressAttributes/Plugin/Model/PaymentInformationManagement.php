<?php
namespace Ceg\CustomerAddressAttributes\Plugin\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;

class PaymentInformationManagement
{
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var AttributeValueFactory
     */
    private $attrValueFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @param CartRepositoryInterface     $quoteRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRepositoryInterface  $addressRepository
     * @param AttributeValueFactory       $attrValueFactory
     * @param Session                     $customerSession
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
        AttributeValueFactory $attrValueFactory,
        Session $customerSession
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->attrValueFactory = $attrValueFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * @param                       $cartId
     * @param PaymentInterface      $paymentMethod
     * @param AddressInterface|null $billing
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function beforeSavePaymentInformation(
        \Magento\Checkout\Model\PaymentInformationManagement $subject,
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billing = null
    ): array
    {
        $quote = $this->quoteRepository->getActive($cartId);
        $billing->setNumero($billing->getExtensionAttributes()->getNumero());
        $billing->setColonia($billing->getExtensionAttributes()->getColonia());
        $billing->setObservaciones($billing->getExtensionAttributes()->getObservaciones());

        $lastAddressId = $this->processBilling($quote, $billing);
        $lastAddressId = $this->processShipping($quote, $billing, $lastAddressId);

        if ($lastAddressId) {
            $customer = $this->customerRepository->getById($quote->getCustomerId());
            $customer->setDefaultBilling($lastAddressId);
            $customer->setDefaultShipping($lastAddressId);
            $this->customerSession->setCustomerData($customer);
        }

        return [$cartId, $paymentMethod, $billing];
    }

    /**
     * @param $quote
     * @param $billing
     *
     * @return false|mixed
     * @throws LocalizedException
     */
    protected function processBilling($quote, $billing)
    {
        $lastAddressId = false;
        if ($billing && !$billing->getCustomerAddressId()) {
            $billingAddress = $billing->exportCustomerAddress();
            if (isset($billingAddress)) {
                $customAttributes = $this->getExtensionAttributes($billing->getExtensionAttributes());
                $billingAddress->setCustomAttributes($customAttributes);
                $billingAddress->setCustomerId($quote->getCustomerId());
                $billingAddress->setIsDefaultBilling(true);
                $billingAddress->setIsDefaultShipping(true);
                $this->addressRepository->save($billingAddress);
                $billing->setCustomerAddressId($billingAddress->getId());
                $billing->setSaveInAddressBook(true);
                $lastAddressId = $billingAddress->getId();
            }
        }
        return $lastAddressId;
    }

    /**
     * @param $quote
     * @param $billing
     * @param $lastAddressId
     *
     * @return mixed
     * @throws LocalizedException
     */
    protected function processShipping($quote, $billing, $lastAddressId)
    {
        $shipping = $quote->getShippingAddress();
        if ($shipping && !$shipping->getCustomerAddressId()) {
            if ($billing->getExtensionAttributes()->getSameAsShipping()) {
                $shipping->setCustomerAddressId($billing->getCustomerAddressId());
                $quote->setShippingAddress($shipping);
            }
            $shippingAddress = $shipping->exportCustomerAddress();
            if (!$billing->getExtensionAttributes()->getSameAsShipping() && isset($shippingAddress)) {
                $customAttributes = $this->getExtensionAttributes($shipping->getExtensionAttributes());
                $shippingAddress->setCustomAttributes($customAttributes);
                $shippingAddress->setCustomerId($quote->getCustomerId());
                $shippingAddress->setIsDefaultBilling(true);
                $shippingAddress->setIsDefaultShipping(true);
                $this->addressRepository->save($shippingAddress);
                $quote->addCustomerAddress($shippingAddress);
                $shipping->setCustomerAddressData($shippingAddress);
                $shipping->setCustomerAddressId($shippingAddress->getId());
                $quote->setShippingAddress($shipping);
                $lastAddressId = $shippingAddress->getId();
            }
        }
        return $lastAddressId;
    }

    /**
     * @param $extensionAttributes
     *
     * @return array
     */
    public function getExtensionAttributes($extensionAttributes)
    {
        $extAttributesArray = [
            "numero" => $extensionAttributes->getNumero(),
            "colonia" => $extensionAttributes->getColonia(),
            "observaciones" => $extensionAttributes->getObservaciones()
        ];

        $customAttributes = [];
        foreach ($extAttributesArray as $key => $value) {
            $attribute = $this->attrValueFactory->create();
            $attribute->setAttributeCode($key);
            $attribute->setValue($value);
            $customAttributes[$key] = $attribute;
        }
        return ($customAttributes);
    }
}
