<?php
declare(strict_types=1);

namespace Ceg\ImportExport\Controller\Adminhtml\Export;

use Ceg\ImportExport\Helper\Data;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface, HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helperData
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->helperData = $helperData;
    }

    /**
     * Index action
     *
     * @return ResponseInterface|Redirect|Page
     * @throws \Exception
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ceg_Core::export');
        $resultPage->getConfig()->getTitle()->prepend(__('Export Quotes'));

        $status = $this->getRequest()->getParam('status'); // string 'approved'

        $fromDate = $this->getRequest()->getParam('quote_from'); // string '11/01/2020'
        $fromDate = !empty($fromDate) ? $fromDate : '01/01/2020';

        $toDate = $this->getRequest()->getParam('quote_to');     // string '11/30/2020'
        $toDate = !empty($toDate) ? $toDate : date('m/d/Y');

        $cegId = $this->getRequest()->getParam('impo_ceg_id'); // string ceg_id

        if ($this->getRequest()->getMethod() == 'POST') {
            $file = $this->helperData->getExportFile($fromDate, $toDate, $status, $cegId);
            if (empty($file)) {
                $this->messageManager->addErrorMessage(__('There is\'nt any quote in range date selected.'));

                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*');
            }
        }

        return $resultPage;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
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
