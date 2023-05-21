<?php
declare(strict_types=1);

namespace Ceg\Theme\Block\Html;

use Ceg\OrdersIntegration\Helper\DataFactory as OrdersHelperFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Header as MagentoHeader;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\ObjectManager;

class Header extends MagentoHeader
{
    const XML_PATH_BILLING_URL = 'ceg/apps/billing_url';
    const XML_PATH_BILLING_TITLE = 'ceg/apps/billing_title';

    protected $_template = 'Ceg_Theme::html/header.phtml';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var OrdersHelperFactory|mixed
     */
    protected $orderHelpFactory;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        OrdersHelperFactory $orderHelpFactory = null,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
        $this->orderHelpFactory = $orderHelpFactory ??
            ObjectManager::getInstance()->get(OrdersHelperFactory::class);
    }

    /**
     * @return mixed
     */
    public function getStoreTitle()
    {
        return __('Store');
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrdersURL()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $ordersHelper = $this->orderHelpFactory->create();
        return $ordersHelper->getAppUrl($storeId);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrdersTitle()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $ordersHelper = $this->orderHelpFactory->create();
        return $ordersHelper->getAppTitle($storeId);
    }

    /**
     * @return mixed
     */
    public function getBillingURL()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_BILLING_URL);
    }

    /**
     * @return mixed
     */
    public function getBillingTitle()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_BILLING_TITLE);
    }

    /**
     * @return string
     */
    public function getStoreURL(): string
    {
        return $this->getBaseUrl();
    }
}
