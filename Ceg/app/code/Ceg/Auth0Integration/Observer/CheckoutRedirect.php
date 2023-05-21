<?php
declare(strict_types=1);

namespace Ceg\Auth0Integration\Observer;

use Ceg\Auth0Integration\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;

class CheckoutRedirect implements ObserverInterface
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
     * @var Session
     */
    private $customerSession;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * CheckoutRedirect constructor.
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     * @param Session $customerSession
     * @param Data $helperData
     */
    public function __construct(
        ResponseFactory $responseFactory,
        UrlInterface $url,
        Session $customerSession,
        Data $helperData
    ) {
        $this->responseFactory = $responseFactory;
        $this->url = $url;
        $this->customerSession = $customerSession;
        $this->helperData = $helperData;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer): CheckoutRedirect
    {
        if (!$this->helperData->isEnabled() || $this->customerSession->isLoggedIn()) {
            return $this;
        }

        $redirectionUrl = $this->url->getUrl('auth0/prelogin/index');
        $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();

        return $this;
    }
}
