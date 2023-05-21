<?php

namespace Ceg\Setup\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;

class StaffGroupUpdateInstaller implements DataPatchInterface
{

    const CUSTOMER_GROUP_STAFF = 6;

    const CUSTOMER_EMAIL_STAFF = '@comprandoengrupo.net';

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Magento\Customer\Model\CustomerFactory;
     */
    protected $customerFactory;


    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        CustomerRepositoryInterface $customerRepository,
        CollectionFactory $customerFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnections
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->resourceConnections = $resourceConnections;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $customersCEG =  $this->getStaffCustomers();

        foreach ($customersCEG as $customer) {

            $customerStaff = $this->customerRepository->getById($customer->getId());

            $this->updateStreetAddress();

            if (str_ends_with($customer->getEmail(), self::CUSTOMER_EMAIL_STAFF)) {
                $customerStaff->setGroupId(self::CUSTOMER_GROUP_STAFF);
                try {
                    $this->customerRepository->save($customerStaff);
                } catch (\Exception $exception) {
                    $exception->getMessage();
                }
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     *
     */
    protected function updateStreetAddress()
    {
        $connection = $this->resourceConnections->getConnection();
        // @codingStandardsIgnoreStart
        $query = "update customer_address_entity set street = replace(street,'\n',',')";
        // @codingStandardsIgnoreEnd

        try {
            $connection->query($query);
        } catch (\Exception $exception) {
            $exception->getMessage();
        }
    }

    /**
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected function getStaffCustomers()
    {
        $staffEmail = '%'.self::CUSTOMER_EMAIL_STAFF;

        $customerCollection = $this->customerFactory->create();
        $customerCollection->addFieldToSelect('*');
        $customerCollection->addFieldToFilter('email', ['like' => $staffEmail]);

        return $customerCollection->load();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
