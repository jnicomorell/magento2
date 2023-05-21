<?php

namespace Formax\UploadModule\Controller\Adminhtml\File;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class InlineEdit extends \Magento\Backend\App\Action
{
    /** @var JsonFactory  */
    protected $jsonFactory;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $fileId) {
                    /** @var \Formax\UploadModule\Model\File $model */
                    $model = $this->_objectManager->create('Formax\UploadModule\Model\File');
                    $model->load($fileId);
                    try {
                        $model->setData(array_merge($model->getData(), $postItems[$fileId]));
                        $model->save();
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithFileId(
                            $model,
                            __($e->getMessage())
                        );
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add file name to error message
     *
     * @param \Formax\UploadModule\Model\File $file
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithFileId(\Formax\UploadModule\Model\File $file, $errorText)
    {
        return '[File ID: ' . $file->getId() . '] ' . $errorText;
    }

    /**
     * Authorization level of a basic admin session
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Formax_UploadModule::file_create') ||
        $this->_authorization->isAllowed('Formax_UploadModule::file_update');
    }
}
