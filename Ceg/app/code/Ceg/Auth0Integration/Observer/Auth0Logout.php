<?php
declare(strict_types=1);

namespace Ceg\Auth0Integration\Observer;

use Ceg\Auth0Integration\Helper\Data;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\Customer\Model\Session;

class Auth0Logout implements ObserverInterface
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
     * Customer session
     *
     * @var Session
     */
    protected $customerSession;

    /**
     * Auth0Logout constructor.
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     * @param Data $helperData
     * @param Session $customerSession
     */
    public function __construct(
        ResponseFactory $responseFactory,
        UrlInterface $url,
        Data $helperData,
        Session $customerSession
    ) {
        $this->responseFactory = $responseFactory;
        $this->url = $url;
        $this->helperData = $helperData;
        $this->customerSession = $customerSession;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer): Auth0Logout
    {
        if ($this->helperData->isEnabled()) {
            try {
                $auth0 = $this->helperData->getAuth0();
                $auth0->logout();
                $this->customerSession->logout();
                $redirectionUrl = $this->url->getUrl('auth0/prelogin/index');
                $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
            } catch (\Exception $e) {
                $this->helperData->getLogger()->error(__("Auth0 redirect to logout fails"));
            }
        }
        return $this;
    }
}
