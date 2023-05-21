<?php
declare(strict_types=1);

namespace Ceg\CatalogPermissions\Plugin\Controller\Category;

use Ceg\CatalogPermissions\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Store\Model\StoreManagerInterface;

class View
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var CustomerContext
     */
    private $customerContext;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Configure constructor.
     * @param ResultFactory $resultFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $messageManager
     * @param Registry $registry
     * @param Data $helper
     * @param HttpContext $httpContext
     * @param CustomerContext $customerContext
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResultFactory $resultFactory,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $messageManager,
        Registry $registry,
        Data $helper,
        HttpContext $httpContext,
        CustomerContext $customerContext,
        StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
        $this->registry = $registry;
        $this->httpContext = $httpContext;
        $this->customerContext = $customerContext;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterExecute($subject, $result)
    {
        $isEnabled = $this->helper->isCatalogRestrictionEnable();
        if (!$isEnabled) {
            return $result;
        }

        if (!$this->helper->isCatalogAvailable()) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $errorMessage = $this->helper->getCustomerRestrictionCategoryErrorMessage();
            if (!empty($errorMessage)) {
                $this->messageManager->addErrorMessage(__($errorMessage));
            }
            $baseUrl = $this->storeManager->getStore()->getBaseUrl();
            $path = str_replace('://', '', $baseUrl);
            $path = explode('/', $path);
            if (strpos($path[0], '.') !== false) {
                $path[0] = '';
            }
            $path = implode('/', $path);
            $result = $resultRedirect->setUrl($path);
        }

        return $result;
    }
}
