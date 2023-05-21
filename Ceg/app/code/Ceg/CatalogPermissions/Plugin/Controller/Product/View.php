<?php
declare(strict_types=1);

namespace Ceg\CatalogPermissions\Plugin\Controller\Product;

use Ceg\CatalogPermissions\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;

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
     * Configure constructor.
     * @param ResultFactory $resultFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $messageManager
     * @param Registry $registry
     * @param Data $helper
     */
    public function __construct(
        ResultFactory $resultFactory,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $messageManager,
        Registry $registry,
        Data $helper
    ) {
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
        $this->registry = $registry;
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
            $errorMessage = $this->helper->getCustomerRestrictionProductErrorMessage();
            if (!empty($errorMessage)) {
                $this->messageManager->addErrorMessage(__($errorMessage));
            }
            $result = $resultRedirect->setPath('/');
        }

        return $result;
    }
}
