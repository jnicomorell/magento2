<?php
declare(strict_types=1);

namespace Ceg\Auth0Integration\Controller\Logout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Layout;
use Magento\Store\Model\StoreManagerInterface;

class Index extends Action implements HttpGetActionInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Index constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        $this->resultFactory = $context->getResultFactory();
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Layout
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $redirectUrl = $this->storeManager->getStore()->getBaseUrl();
        $resultRedirect->setPath($redirectUrl);

        return $resultRedirect;
    }
}
