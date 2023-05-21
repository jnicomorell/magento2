<?php
declare(strict_types=1);

namespace Ceg\Impo\Controller\Adminhtml\Publication;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Ceg\Impo\Api\ImpoRepositoryInterfaceFactory;
use Ceg\Impo\Helper\DataFactory as HelperFactory;

class Delete extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ceg_Impo::impo_delete';

    /**
     * @var ImpoRepositoryInterfaceFactory
     */
    protected $impoRepoFactory;

    /**
     * @var HelperFactory
     */
    protected $helperFactory;

    /**
     * @param Action\Context $context
     * @param ImpoRepositoryInterfaceFactory $impoRepoFactory
     * @param HelperFactory $helperFactory
     */
    public function __construct(
        Action\Context $context,
        ImpoRepositoryInterfaceFactory $impoRepoFactory,
        HelperFactory $helperFactory
    ) {
        parent::__construct($context);
        $this->impoRepoFactory = $impoRepoFactory;
        $this->helperFactory = $helperFactory;
    }

    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $impoRepository = $this->impoRepoFactory->create();
        $helper = $this->helperFactory->create();

        $impoId = $this->getRequest()->getParam('id');
        if ($impoId) {
            try {
                $impo = $impoRepository->getById($impoId);
                if (!$impo->getId()) {
                    $this->messageManager->addErrorMessage(__('This Impo no longer exists.'));
                    $helper->clearCurrentImpo();
                    return $resultRedirect->setPath('*/*/');
                }
                $impoRepository->delete($impo);
                $helper->clearCurrentImpo();
                $this->messageManager->addSuccessMessage(__('The Impo has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $impoId]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a Impo to delete.'));
        $helper->clearCurrentImpo();
        return $resultRedirect->setPath('*/*/');
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
