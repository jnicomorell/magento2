<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ceg\Customer\Block\Rewrite\Widget;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Customer\Model\Options;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
/**
 * Widget for showing customer company.
 *
 * @method CustomerInterface getObject()
 * @method Name setObject(CustomerInterface $customer)
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Company extends \Magento\Customer\Block\Widget\Company
{

    /**
     * the attribute code
     */
    const ATTRIBUTE_CODE = 'company';

    /**
     * @var AddressMetadataInterface
     */
    protected $addressMetadata;

    /**
     * @var Options
     */
    protected $options;

    /**
     * @var Session
     */
    protected $customer;

    /**
     * @param Context $context
     * @param AddressHelper $addressHelper
     * @param CustomerMetadataInterface $customerMetadata
     * @param Options $options
     * @param AddressMetadataInterface $addressMetadata
     * @param array $data
     * @param Session $customer
     */
    public function __construct(
        Context $context,
        AddressHelper $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        Options $options,
        AddressMetadataInterface $addressMetadata,
        Session $customer,
        array $data = [])
    {
        parent::__construct(
            $context, 
            $addressHelper, 
            $customerMetadata, 
            $options,
            $addressMetadata,
            $data);
        $this->customer = $customer;

    }
    public function getCompanyName()
    {
        $customer = $this->customer;
        $companyName = $customer->getCustomerData()->getCustomAttribute('company_name')->getValue();
        return $companyName;

    }
}
