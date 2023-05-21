<?php
namespace Ceg\Auth0Integration\Plugin;

use Ceg\Auth0Integration\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Ceg\Core\Logger\Logger as CegLogger;
class PaymentInformationManagement
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Data
     */
    protected $helperData;

    protected $decodedToken = [];
    protected $currentCustomer;
    protected $saveCustomer = false;

    private $cegLogger;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param Session $customerSession
     * @param Data $helperData
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession,
        Data $helperData,
        CegLogger $cegLogger
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->helperData = $helperData;
        $this->cegLogger = $cegLogger;
    }

    /**
     * @param \Magento\Checkout\Model\PaymentInformationManagement $subject
     * @param $result
     * @return mixed
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterSavePaymentInformation(
        \Magento\Checkout\Model\PaymentInformationManagement $subject,
        $result
    ) {
        try {
            $idToken = $this->customerSession->getIdToken();
            if (!empty($idToken)) {
                $this->decodedToken = $this->helperData->decodedIdToken($idToken);
                $this->currentCustomer = $this->customerRepository->getById($this->customerSession->getCustomerId());

                $this->saveCustomer = false;
                $this->verifyData('rfc', 'RFC');
                $this->verifyData('company_id', 'id');
                $this->verifyData('company_name', 'name');
                if ($this->saveCustomer) {
                    $this->customerRepository->save($this->currentCustomer);
                }
            }
        } catch (\Exception $exception) {
            $this->cegLogger->info($exception->getMessage());
        }
        return $result;
    }

    public function verifyData($attributeField, $metadataField)
    {
        $value = null;
        $attribute = $this->currentCustomer->getCustomAttribute($attributeField);
        if (!empty($attribute)) {
            $value = $attribute->getValue();
        }
        if (empty($value)) {
            $metadataCompanyValue = $this->decodedToken['user_metadata']['company'][$metadataField];
            if (!empty($metadataCompanyValue)) {
                $this->currentCustomer->setCustomAttribute($attributeField, $metadataCompanyValue);
                $this->saveCustomer = true;
            }
        }
    }
}
