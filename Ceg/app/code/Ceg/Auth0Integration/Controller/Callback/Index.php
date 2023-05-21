<?php
declare(strict_types=1);

namespace Ceg\Auth0Integration\Controller\Callback;

use Auth0\SDK\Exception\ApiException;
use Auth0\SDK\Exception\CoreException;
use Ceg\Auth0Integration\Helper\Data;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\CustomerFactory as MagentoCustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Auth0\SDK\Auth0;
use Ceg\Auth0Integration\Exception\CustomerDoesNotExistException;
use Ceg\Auth0Integration\Exception\AccessTokenIntegrityException;
use Ceg\Auth0Integration\Exception\EmailFormatException;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\Layout;
use Magento\Store\Model\StoreIsInactiveException;
use Magento\Store\Model\StoreManagerInterface;

use Magento\Store\Model\Store;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;

class Index extends Action implements HttpGetActionInterface
{
    const CUSTOMER_GROUP_STAFF = 6;

    const CUSTOMER_EMAIL_STAFF = '@comprandoengrupo.net';

    /**
     * @var Auth0
     */
    protected $auth0;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var MagentoCustomerFactory
     */
    protected $customerResourceFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var array
     */
    protected $user;

    /**
     * @var string|int|null
     */
    protected $user_id = null;

    /**
     * @var false|mixed
     */
    private $user_metadata;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var StoreCookieManagerInterface
     */
    private $storeCookieManager;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * Index constructor.
     * @param Context $context
     * @param Customer $customer
     * @param CustomerFactory $customerFactory
     * @param MagentoCustomerFactory $customerResourceFactory
     * @param Session $customerSession
     * @param Data $helperData
     * @param StoreManagerInterface $storeManager
     * @param HttpContext $httpContext
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param StoreRepositoryInterface $storeRepository
     * @throws CoreException
     */
    public function __construct(
        Context $context,
        Customer $customer,
        CustomerFactory $customerFactory,
        MagentoCustomerFactory $customerResourceFactory,
        Session $customerSession,
        Data $helperData,
        StoreManagerInterface $storeManager,
        HttpContext $httpContext,
        StoreCookieManagerInterface $storeCookieManager,
        StoreRepositoryInterface $storeRepository
    ) {
        parent::__construct($context);

        $this->customer = $customer;
        $this->customerFactory = $customerFactory;
        $this->customerResourceFactory = $customerResourceFactory;
        $this->customerSession = $customerSession;
        $this->helperData = $helperData;
        $this->auth0 = $this->helperData->getAuth0();
        $this->storeManager = $storeManager;
        $this->httpContext = $httpContext;
        $this->storeCookieManager = $storeCookieManager;
        $this->storeRepository = $storeRepository;
    }

    /**
     * @return ResponseInterface|ResultInterface|Layout
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $this->helperData->getLogger()->info("Login Response: " . PHP_EOL . $this->getResponse());

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->helperData->getCallbackUrl());

        $params = $this->getRequest()->getParams();
        if (isset($params['error'], $params['error_description'])) {
            $this->messageManager->addErrorMessage(__($params['error_description']));
            return $resultRedirect;
        }

        try {
            // Logged user
            $this->user = $this->auth0->getUser();
            $this->customerSession->setIdToken($this->auth0->getIdToken());

            $this->user_metadata = $this->helperData->getUserMetadata($this->user);
            $this->helperData->logAuth0Info();

            // Check if email is set and its format
            $this->helperData->checkEmailOrFail($this->user['email']);

            // Get customer
            $customer = $this->getCustomer($this->user['email']);

            // Customer Group switch
            // TODO: * Temporal option: Group will be obtained from BITRIX service
            $groupId = (int) $this->helperData->getNewCustomerGroupId();

            $this->setCustomerGroup($customer, $groupId);

            // Customer metadata
            $this->setCustomerMetadata($customer);

            // Customer Login
            $this->setCustomerAsLoggedIn($customer);

        } catch (AccessTokenIntegrityException $e) {
            // Check Access Token Integrity Fails
            $this->helperData->getLogger()->error(__("Check Access Token Integrity Fails"));
            $this->tokenFailAction($e->getMessage());
        } catch (EmailFormatException $e) {
            // Check email Fails
            $this->helperData->getLogger()->error(__("Check email Fails"));
            $this->emailFailAction($e->getMessage());
        } catch (CustomerDoesNotExistException $e) {
            // If customer does not exist then...
            $this->helperData->getLogger()->warning(__("The customer does not exist."));
            $this->customerDoesNotExistAction();
        } catch (CoreException $coreException) {
            // Catch every auth0 exception
            $this->helperData->getLogger()->warning(__("Auth0 exception: " . $coreException->getMessage()));
            $this->auth0->logout();
        }

        return $resultRedirect;
    }

    /**
     * @param Customer $customer
     * @throws LocalizedException
     */
    protected function setCustomerAsLoggedIn(Customer $customer)
    {

        try {
            // set current store
            $storeCode = $this->helperData->getStore()->getCode();
            $store = $this->storeRepository->getActiveStoreByCode($storeCode);
            $this->storeCookieManager->setStoreCookie($store);
            $this->storeManager->setCurrentStore($storeCode);

            // and log in
            $this->customerSession->setCustomerAsLoggedIn($customer);
            $this->customerSession->setCustomerGroupId($customer->getGroupId());
            $this->customerSession->setCustomerId($customer->getId());

            /**
             * This solves cache problems
             * vendor/magento/framework/App/Http/Context.php::getVaryString line 111
             * vendor/magento/module-customer/Model/App/Action/ContextPlugin.php::beforeExecute line 47
             */
            if ($this->customerSession->isLoggedIn()) {
                $this->httpContext->setValue(
                    CustomerContext::CONTEXT_GROUP,
                    $customer->getGroupId(), //$this->customerSession->getCustomerGroupId(),
                    0
                );
                $this->httpContext->setValue(CustomerContext::CONTEXT_AUTH, true, 0);
            }
            $this->httpContext->setValue(Store::ENTITY, $storeCode, $storeCode);

        } catch (StoreIsInactiveException | NoSuchEntityException $e) {
            // show error message
            $this->helperData->getLogger()->error("Login fail with customer ID ".$customer->getId().".");
            $this->messageManager->addErrorMessage("Something went wrong!");
            $this->auth0->logout();
        }

        if (!$this->customerSession->isLoggedIn()) {
            // show error message
            $this->helperData->getLogger()->error("Login fail with customer ID ".$customer->getId().".");
            $this->messageManager->addErrorMessage("Something went wrong!");
            $this->auth0->logout();
        }
    }

    /**
     * @param $email
     * @return Customer
     * @throws CustomerDoesNotExistException
     */
    protected function getCustomer($email)
    {
        // Load customer
        $customer = $this->customerFactory->create();
        $customerResource = $this->customerResourceFactory->create();
        $customer->setWebsiteId($this->helperData->getWebsiteIdByCode($this->storeManager->getWebsite()->getCode()));
        $customerResource->loadByEmail($customer, $email);

        if ($customer->getId() === null) {
            throw new CustomerDoesNotExistException("Customer $email does not exist.");
        }

        return $customer;
    }

    /**
     * @throws \Exception
     */
    protected function customerDoesNotExistAction()
    {
        try {
            // create before login
            $customer = $this->createCustomer();
            $this->setCustomerAsLoggedIn($customer);
        } catch (\Exception $e) {
            $this->helperData->getLogger()->error(__($e->getMessage()));
            $this->messageManager->addErrorMessage(__($e->getMessage()));
            $this->auth0->logout();
        }
    }

    /**
     * @return Customer
     */
    private function createCustomer()
    {
        $customerResource = $this->customerResourceFactory->create();
        $customer = $this->customerFactory->create();
        $customerGroupId = $this->helperData->getNewCustomerGroupId();
        if (str_ends_with($this->user['email'], self::CUSTOMER_EMAIL_STAFF)) {
            $customerGroupId = self::CUSTOMER_GROUP_STAFF;
        }
        $customer
            ->setWebsiteId($this->helperData->getWebsiteIdByCode($this->storeManager->getWebsite()->getCode()))
            ->setGroupId($customerGroupId)
            ->setFirstname($this->user_metadata['first_name'] ?? "")
            ->setLastname($this->user_metadata['last_name'] ?? "")
            ->setRfc($this->user_metadata['company']['RFC'] ?? "")
            ->setCompanyId($this->user_metadata['company']['id'] ?? "")
            ->setCompanyName($this->user_metadata['company']['name'] ?? "")
            ->setCheckoutDisabled($this->user_metadata['checkoutDisabled'] ?? 0)
            ->setEmail($this->user['email'])
        ;
        $customerResource->save($customer);

        // @TODO add customer address here?

        return $customer;
    }

    public function setCustomerGroup(Customer $customer, int $groupId): void
    {
        if ($customer) {
            try {
                if (str_ends_with($customer->getEmail(), self::CUSTOMER_EMAIL_STAFF)) {
                    $groupId = self::CUSTOMER_GROUP_STAFF;
                }
                $customer->setGroupId($groupId);

                $customer->save();

            } catch (LocalizedException $exception) {
                $this->helperData->getLogger()->error(__($exception->getMessage()));
                $this->auth0->logout();
            }
        }
    }

    /**
     * @param $customer
     */
    public function setCustomerMetadata($customer)
    {
        if ($customer) {
            try {
                $customer->setWebsiteId(
                    $this->helperData->getWebsiteIdByCode($this->storeManager->getWebsite()->getCode())
                );

                if (!empty($this->user_metadata['company']['RFC'])) {
                    $customer->setRfc($this->user_metadata['company']['RFC']);
                }
                if (!empty($this->user_metadata['company']['id'])) {
                    $customer->setCompanyId($this->user_metadata['company']['id']);
                }
                if (!empty($this->user_metadata['company']['name'])) {
                    $customer->setCompanyName($this->user_metadata['company']['name']);
                }
                if (!empty($this->user_metadata['contact_id'])) {
                    $customer->setContactId($this->user_metadata['contact_id']);
                }
                if (!empty($this->user_metadata['checkoutDisabled'])) {
                    $customer->setCheckoutDisabled($this->user_metadata['checkoutDisabled']);
                }

                $customer->save();
            } catch (LocalizedException $exception) {
                $this->helperData->getLogger()->error(__($exception->getMessage()));
            }
        }
    }

    /**
     * @param string $message
     */
    protected function emailFailAction($message = "Something went wrong!")
    {
        $this->messageManager->addErrorMessage(__($message));
        $this->auth0->logout();
    }

    /**
     * @param string $message
     */
    protected function tokenFailAction($message = "Something went wrong!")
    {
        $this->messageManager->addErrorMessage(__($message));
        $this->auth0->logout();
    }
}
