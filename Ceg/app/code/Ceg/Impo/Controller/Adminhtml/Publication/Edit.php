<?php
declare(strict_types=1);

namespace Ceg\Impo\Controller\Adminhtml\Publication;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Ceg\Impo\Api\ImpoRepositoryInterface;
use Ceg\Impo\Helper\DataFactory as HelperFactory;

class Edit extends Action implements HttpGetActionInterface
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ceg_Impo::impo_view';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var HelperFactory
     */
    protected $helperFactory;

    /**
     * @var ImpoRepositoryInterface
     */
    protected $impoRepository;

    /**
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param ImpoRepositoryInterface $impoRepository
     * @param HelperFactory $helperFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        ImpoRepositoryInterface $impoRepository,
        HelperFactory $helperFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->impoRepository = $impoRepository;
        $this->helperFactory = $helperFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        $helper = $this->helperFactory->create();
        $helper->clearCurrentImpo();

        $title = __('New Impo');
        $breadcrumb = __('New Impo');
        $impoId = $this->getRequest()->getParam('id');
        if ($impoId) {
            $breadcrumb = __('Edit Impo');
            $impo = $this->impoRepository->getById($impoId);
            if (!$impo->getId()) {
                $this->messageManager->addErrorMessage(__('This impo no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
            $helper->setCurrentImpo($impo);
            $title = $impo->getTitle();
        }

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ceg_Impo::ceg_impo');
        $resultPage->addBreadcrumb(__('Impo'), __('Impo'));
        $resultPage->addBreadcrumb(__('Publications'), __('Publications'));
        $resultPage->addBreadcrumb($breadcrumb, $breadcrumb);
        $resultPage->getConfig()->getTitle()->prepend(__('Publications'));
        $resultPage->getConfig()->getTitle()->prepend(__('Impo'));
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _isAllowed()
    {
        return true;
    }
}
