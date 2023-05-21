<?php
declare(strict_types=1);

namespace Ceg\Quote\Controller\Adminhtml\View;

use Magento\Framework\App\Action\HttpPostActionInterface;

class Cancel extends AbstractAction implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Ceg_Core::quote_cancel';

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->isValidPostRequest()) {
            $this->messageManager->addErrorMessage(__('You have not canceled the item.'));
            return $resultRedirect->setPath('quote/view/index');
        }
        $quote = $this->initQuote();
        if ($quote) {
            try {
                $this->quoteRepository->cancel($quote);
                $this->messageManager->addSuccessMessage(__('You canceled the quote.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('You have not canceled the item.'));
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            }
            return $resultRedirect->setPath('quote/view/detail', ['id' => $quote->getId()]);
        }
        return $resultRedirect->setPath('quote/view/index');
    }
}
