<?php
declare(strict_types=1);

namespace Ceg\Auth0Integration\Controller\Account;

use Ceg\Auth0Integration\Helper\Data;
use Magento\Customer\Controller\Account\Logout as MagentoLogout;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;

class Logout extends MagentoLogout implements HttpGetActionInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Logout constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param Data|null $helperData
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Data $helperData = null
    ) {
        $this->helperData = $helperData ?? ObjectManager::getInstance()->create(Data::class);
        parent::__construct($context, $customerSession);
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $resultRedirect = parent::execute();

        // Redirect to auth0 logout with logoutSuccess as callback
        try {
            $resultRedirect->setUrl($this->helperData->logoutAuth0Url());
        } catch (NoSuchEntityException $e) {
            $this->helperData->getLogger()->error(__("Auth0 redirect to logout fails"));
        }

        return $resultRedirect;
    }
}
