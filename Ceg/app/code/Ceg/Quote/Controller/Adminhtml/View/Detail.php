<?php
declare(strict_types=1);

namespace Ceg\Quote\Controller\Adminhtml\View;

class Detail extends AbstractAction
{
    const ADMIN_RESOURCE = 'Ceg_Core::quote_detail';

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $quote = $this->initQuote();
        if ($quote) {
            try {
                $resultPage = $this->resultPageFactory->create();
                $resultPage->setActiveMenu('Ceg_Core::view');
                $resultPage->getConfig()->getTitle()->prepend(__('View Quotes'));
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(__('Exception occurred during quote load'));
                $resultRedirect->setPath('quote/view/index');
                return $resultRedirect;
            }
            $title = sprintf("Quote ID %s", $quote->getId());
            if (!empty($quote->getReservedOrderId())) {
                $title = sprintf("Quote # %s", $quote->getReservedOrderId());
            }
            $resultPage->getConfig()->getTitle()->prepend($title);
            return $resultPage;
        }
        $resultRedirect->setPath('quote/view/index');
        return $resultRedirect;
    }
}
