<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ceg\CatalogPermissions\Block\Rewrite\Account\Dashboard;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Block\Form\Register;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Helper\View;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Directory\Model\Country;
use Magento\Directory\Model\CountryFactory;
/**
 * Dashboard Customer Info
 *
 * @api
 * @since 100.0.2
 */
class Info extends \Magento\Customer\Block\Account\Dashboard\Info
{
    /**
     * @var Country
     */
    public $countryFactory;

    /**
     * Cached subscription object
     *
     * @var Subscriber
     */
    protected $_subscription;

    /**
     * @var SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @var View
     */
    protected $_helperView;

    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * Constructor
     *
     * @param Context $context
     * @param CurrentCustomer $currentCustomer
     * @param SubscriberFactory $subscriberFactory
     * @param View $helperView
     * @param array $data
     */
    public function __construct(
        Context $context,
        CurrentCustomer $currentCustomer,
        SubscriberFactory $subscriberFactory,
        View $helperView,
        CountryFactory $countryFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $currentCustomer,
            $subscriberFactory,
            $helperView,
            $data);
        $this->countryFactory = $countryFactory;
    }

    /**
     * country full name
     *
     * @return string
     */
    public function getCountryName($countryId): string
    {
        $countryName = '';
        $country = $this->countryFactory->create()->loadByCode($countryId);
        if ($country) {
            $countryName = $country->getName();
        }
        return $countryName;
    }
}
