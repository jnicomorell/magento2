<?php
declare(strict_types=1);

namespace Ceg\Auth0Integration\Observer;

use Ceg\Auth0Integration\Helper\Data;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionFlag;
use \Magento\Framework\View\Element\Context;

class CheckLoginPersistent implements ObserverInterface
{
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ActionFlag
     */
    protected $actionFlag;

    /**
     * @var /Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * LoginRedirect constructor.
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     * @param Data $helperData
     * @param Session $customerSession
     * @param ActionFlag $actionFlag
     * @param Context $context
     */
    public function __construct(
        ResponseFactory $responseFactory,
        UrlInterface $url,
        Data $helperData,
        Session $customerSession,
        ActionFlag $actionFlag,
        Context $context
    ) {
        $this->responseFactory = $responseFactory;
        $this->url = $url;
        $this->helperData = $helperData;
        $this->customerSession = $customerSession;
        $this->actionFlag = $actionFlag;
        $this->urlBuilder = $context->getUrlBuilder();
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        if (!$this->helperData->isEnabled()) {
            return $this;
        }

        $moduleName = $observer->getEvent()->getRequest()->getModuleName();
        $actionName = $observer->getEvent()->getRequest()->getFullActionName();

        // Allow full modules
        $openModules = [
            'auth0',
            'loginascustomer',
        ];

        // Allow specific actions
        $openActions = [
            'loginascustomer_login_index',
        ];

        if (in_array($moduleName, $openModules) || in_array($actionName, $openActions)) {
            return $this; // if in allowed actions do nothing.
        }

        if ($this->helperData->isRestricted() && !$this->customerSession->isLoggedIn()) {
            $redirectionUrl = $this->url->getUrl('auth0/prelogin/index');
            $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
            $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
        }
        return $this;
    }
}
