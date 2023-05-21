<?php
declare(strict_types=1);

namespace Ceg\BitrixIntegration\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Grid extends \Magento\Backend\App\Action
{
    /**
     * @const acl
     */
    const ADMIN_RESOURCE = 'Ceg_BitrixIntegration::bitrixintegration';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Backend::system');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Bitrix Queues'));
        $this->_view->renderLayout();
    }
}
