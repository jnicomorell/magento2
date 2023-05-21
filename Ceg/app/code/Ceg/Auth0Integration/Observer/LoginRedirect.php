<?php
declare(strict_types=1);

namespace Ceg\Auth0Integration\Observer;

use Ceg\Auth0Integration\Helper\Data;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class LoginRedirect implements ObserverInterface
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
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * LoginRedirect constructor.
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     * @param Data $helperData
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResponseFactory $responseFactory,
        UrlInterface $url,
        Data $helperData,
        StoreManagerInterface $storeManager
    ) {
        $this->responseFactory = $responseFactory;
        $this->url = $url;
        $this->helperData = $helperData;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer): LoginRedirect
    {
        if (!$this->helperData->isEnabled()) {
            return $this;
        }

        //$store = $this->storeManager->getStore();
        $redirectionUrl = $this->url->getUrl('auth0/prelogin/index');
        $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();

        return $this;
    }
}
