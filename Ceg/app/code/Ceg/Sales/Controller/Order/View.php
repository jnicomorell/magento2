<?php

namespace Ceg\Sales\Controller\Order;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\QuoteFactory;

class View extends Action implements HttpGetActionInterface
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param QuoteFactory $quoteFactory
     * @param Registry $registry
     * @param ForwardFactory $resultForwardFactory
     * @param RedirectFactory $redirectFactory
     * @param UrlInterface $url
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        QuoteFactory $quoteFactory,
        Registry $registry,
        ForwardFactory $resultForwardFactory,
        RedirectFactory $redirectFactory,
        UrlInterface $url,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->quoteFactory = $quoteFactory;
        $this->registry = $registry;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->redirectFactory = $redirectFactory;
        $this->url = $url;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Order view page
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $quoteId = (int)$this->_request->getParam('quote_id');
        if (!$quoteId) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }

        $quote = $this->quoteFactory->create()->load($quoteId);
        if (!$this->canView($quote)) {
            $resultRedirect = $this->redirectFactory->create();
            return $resultRedirect->setUrl($this->url->getUrl('*/*/history'));
        }

        $this->registry->register('current_quote', $quote);

        $resultPage = $this->resultPageFactory->create();
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/order/history');
        }
        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    public function canView(\Magento\Quote\Model\Quote $quote)
    {
        $customerId = $this->customerSession->getCustomerId();
        if ($quote->getId()
            && $quote->getCustomerId()
            && $quote->getCustomerId() == $customerId
        ) {
            return true;
        }
        return false;
    }
}
