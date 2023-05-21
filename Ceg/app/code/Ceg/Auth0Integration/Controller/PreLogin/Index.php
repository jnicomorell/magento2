<?php
declare(strict_types=1);

namespace Ceg\Auth0Integration\Controller\PreLogin;

use Auth0\SDK\Auth0;
use Auth0\SDK\Exception\CoreException;
use Auth0\SDK\Exception\ApiException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
use Ceg\Auth0Integration\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Layout;

class Index extends Action implements HttpGetActionInterface
{
    /**
     * @var Auth0
     */
    private $auth0;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Index constructor.
     * @param Context $context
     * @param Data $helperData
     * @param Session $customerSession
     * @throws CoreException
     */
    public function __construct(
        Context $context,
        Data $helperData,
        Session $customerSession
    ) {
        parent::__construct($context);

        $this->customerSession = $customerSession;
        $this->helperData = $helperData;
        $this->auth0 = $this->helperData->getAuth0();
    }

    /**
     * @return ResponseInterface|ResultInterface|Layout
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $this->helperData->setCallbackUrl($this->_redirect->getRefererUrl());

        if ($this->auth0 === null) {
            $this->messageManager->addErrorMessage(__("Auth0 configuration is wrong!"));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->helperData->getCallbackUrl());
            return $resultRedirect;
        }

        // Redirect to Auth0 login
        $this->auth0->login();
    }
}
