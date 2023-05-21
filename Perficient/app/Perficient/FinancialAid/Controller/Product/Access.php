<?php
namespace Perficient\FinancialAid\Controller\Product;

class Access extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_customerSession = $customerSession;
        return parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            $this->_redirect('store/school/laketahoe/');
        } else {
            return $this->_pageFactory->create();
        }
    }
}
