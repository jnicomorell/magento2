<?php

namespace Ceg\Core\ViewModel;

use \Magento\Framework\App\Http\Context as HttpContext;
use \Magento\Framework\View\Element\Block\ArgumentInterface;
use \Magento\Customer\Model\Session;

class CustomerSession implements ArgumentInterface
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * LoggedIn ViewModel
     * @param Session $customerSession
     * @param HttpContext $httpContext
     */
    public function __construct(
        Session $customerSession,
        HttpContext $httpContext
    ) {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn() ||
            (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * @return bool
     */
    public function canViewCatalog()
    {
        // todo: add logic to check for enabled catalog restriction
        return (bool)$this->isLoggedIn();
    }
}
