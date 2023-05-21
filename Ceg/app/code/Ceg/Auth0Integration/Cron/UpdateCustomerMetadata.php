<?php

namespace Ceg\Auth0Integration\Cron;

use ceg\Auth0Integration\Helper\Data;
use ceg\Auth0Integration\Helper\Config;
use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Auth0\SDK\API\Management;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\App\ResourceConnection;

class UpdateCustomerMetadata
{

    /**
     * @var Data
     */
    protected $auth0Data;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var $pagination
     */
    protected $pagination;
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var array
     */
    protected $customersArray = [];

    /**
     * @var Int
     */
    protected $totalCustomers;

    /**
     * @param Data                        $auth0Data
     * @param CustomerRepositoryInterface $customerRepository
     * @param Config                      $config
     */
    public function __construct(
        Data $auth0Data,
        CustomerRepositoryInterface $customerRepository,
        Config $config,
        ResourceConnection $resource
    ) {
        $this->auth0Data = $auth0Data;
        $this->customerRepository = $customerRepository;
        $this->config = $config;
        $this->resource = $resource;
    }

    public function execute()
    {

        $countries = $this->config->getCountries();

        $this->pagination = $this->config->getPagination();

        foreach ($countries as $websiteCode => $auth0Country) {
            $this->getAllCustomers(0, $this->pagination, $websiteCode, $auth0Country) ;
        }
    }

    /**
     * @param $page
     * @param $perPage
     * @param $websiteCode
     * @param $auth0Country
     *
     * @return void|null
     * @throws LocalizedException
     * @throws InputException
     * @throws InputMismatchException
     */
    protected function getAllCustomers($page, $perPage, $websiteCode, $auth0Country)
    {
        $managementToken = $this->auth0Data->getManagementToken();
        if ($managementToken) {
            if (isset($managementToken->access_token)) {
                $managementApi = new Management($managementToken->access_token, $this->config->getDomain());
                $query = $this->createLuceneQuery();
                $searchParams = [
                    'q' => 'email:'.$query,
                    'page' => 0,
                    'per_page' => $perPage,
                    'include_totals' => true
                ];
                $usersAuth0 =  $managementApi->users()->getAll($searchParams);
                $paginationMax = $this->pagination;
                $totalPages = (int)ceil(($this->totalCustomers / $paginationMax ));

                if ($page <= $totalPages) {
                    $this->updateCustomer($usersAuth0, $websiteCode);
                    if ($totalPages == 0) {
                        return null;
                    }
                    ++$page;
                    $this->getAllCustomers($page, $perPage, $websiteCode, $auth0Country);
                }
            }
        }
        return null;
    }

    /**
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws InputException
     */
    protected function updateCustomer($users, $websiteCode)
    {
        foreach ($users['users'] as $user) {
            $customer = $this->getCustomer($user['email'], $websiteCode);
            if ($customer) {
                $this->customerUpdate($user, $customer);
            }
        }
    }

    /**
     * @param $users
     * @param $customer
     *
     * @throws LocalizedException
     * @throws InputException
     * @throws InputMismatchException
     */
    protected function customerUpdate($users, $customer)
    {
        $customerData = [];

        $customerData['Firstname']['auth0'] = $users['user_metadata']['first_name'] ?? '';
        $customerData['Lastname']['auth0'] = $users['user_metadata']['last_name'] ?? '';
        $customerData['Rfc']['auth0'] = $users['user_metadata']['company']['RFC'] ?? '';
        $customerData['CompanyId']['auth0'] = $users['user_metadata']['company']['id'] ?? '';
        $customerData['CompanyName']['auth0'] = $users['user_metadata']['company']['name'] ?? '';
        $customerData['ContactId']['auth0'] = $users['user_metadata']['contact_id'] ?? '';

        $customerRfc = $customer->getCustomAttribute('rfc');
        $customerCompanyId = $customer->getCustomAttribute('company_id');
        $customerCompany = $customer->getCustomAttribute('company_name');
        $customerContact = $customer->getCustomAttribute('contact_id');
        $customerData['Firstname']['stored'] = $customer->getFirstName();
        $customerData['Lastname']['stored']  = $customer->getLastName();
        $customerData['Rfc']['stored'] = is_object($customerRfc) ? $customerRfc->getValue() : '' ;
        $customerData['CompanyId']['stored'] = is_object($customerCompanyId)? $customerCompanyId->getValue() : '';
        $customerData['CompanyName']['stored'] = is_object($customerCompany)? $customerCompany->getValue() : '';
        $customerData['ContactId']['stored'] = is_object($customerContact)? $customerContact->getValue() : '';

        $customerIsModified = false;

        foreach ($customerData as $attr) {
            if (empty($attr['stored']) || $attr['stored'] != $attr['auth0']) {
                    $customerIsModified = true;
            }
        }

        if ($customerIsModified) {
            $customer
            ->setFirstname($customerData['Firstname']['auth0'])
            ->setLastname($customerData['Lastname']['auth0'])
            ->setCustomAttribute('rfc', $customerData['Rfc']['auth0'])
            ->setCustomAttribute('company_id', $customerData['CompanyId']['auth0'])
            ->setCustomAttribute('company_name', $customerData['CompanyName']['auth0'])
            ->setCustomAttribute('contact_id', $customerData['ContactId']['auth0']);
            $this->saveCustomer($customer);
        }
    }

    /**
     * @param $customer
     */
    protected function saveCustomer($customer)
    {
        try {
            $this->customerRepository->save($customer);
        } catch (Exception $exception) {
            $this->auth0Data->getLogger()->error("customer cannot be modified ". json_encode($exception));
        }
    }

    /**
     * @param $email
     * @param $websiteCode
     *
     * @return false|\Magento\Customer\Api\Data\CustomerInterface
     */
    protected function getCustomer($email, $websiteCode)
    {
        try {
            $customer = $this->customerRepository->get($email, $this->auth0Data->getWebsiteIdByCode($websiteCode));
        } catch (NoSuchEntityException | LocalizedException $exception) {
            return false;
        }

        return $customer;
    }

    /**
     * @return array
     */
    protected function getStoredCustomers()
    {

        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('customer_entity');

        $selectCustomers = $connection->select()
            ->from(
                ['customer' => $table],
                ['customer.email']
            )
            ->where(
                'customer.is_active=?',
                1
            );

        $customers = $connection->fetchAll($selectCustomers);
        (int)$this->totalCustomers = count($customers);
        return $customers;
    }

    /**
     * @return string
     */
    protected function createLuceneQuery()
    {
        if (empty($this->customersArray)) {
            $this->customersArray = $this->getStoredCustomers();
        }
        $totalInQuery = 0;
        $totalPagination = (int)$this->config->getPagination();
        $query = '';

        foreach ($this->customersArray as $key => $customerData) {
            $query .= '"'.$customerData['email'] .'" or ';
            unset($this->customersArray[$key]);
            $totalInQuery++;
            if ($totalInQuery === $totalPagination) {
                break;
            }
        }
        if (empty($this->customersArray)) {
            $this->totalCustomers = 0;
        }
        return (rtrim($query, " or ")) ;
    }
}
