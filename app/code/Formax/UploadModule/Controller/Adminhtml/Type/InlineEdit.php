<?php

namespace Formax\UploadModule\Controller\Adminhtml\Type;

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
                foreach (array_keys($postItems) as $typeId) {
                    /** @var \Formax\UploadModule\Model\Type $model */
                    $model = $this->_objectManager->create('Formax\UploadModule\Model\Type');
                    $model->load($typeId);
                    try {
                        $model->setData(array_merge($model->getData(), $postItems[$typeId]));
                        $model->save();
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithTypeId(
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
     * Add type name to error message
     *
     * @param \Formax\UploadModule\Model\Type $type
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithTypeId(\Formax\UploadModule\Model\Type $type, $errorText)
    {
        return '[Type ID: ' . $type->getId() . '] ' . $errorText;
    }

    /**
     * Authorization level of a basic admin session
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Formax_UploadModule::type_create') ||
        $this->_authorization->isAllowed('Formax_UploadModule::type_update');
    }
}
