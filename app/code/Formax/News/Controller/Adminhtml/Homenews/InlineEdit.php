<?php

namespace Formax\News\Controller\Adminhtml\Homenews;

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
                foreach (array_keys($postItems) as $homenewsId) {
                    /** @var \Formax\News\Model\Homenews $model */
                    $model = $this->_objectManager->create('Formax\News\Model\Homenews');
                    $model->load($homenewsId);
                    try {
                        $model->setData(array_merge($model->getData(), $postItems[$homenewsId]));
                        $model->save();
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithHomenewsId(
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
     * Add homenews name to error message
     *
     * @param \Formax\News\Model\Homenews $homenews
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithHomenewsId(\Formax\News\Model\Homenews $homenews, $errorText)
    {
        return '[Homenews ID: ' . $homenews->getId() . '] ' . $errorText;
    }

    /**
     * Authorization level of a basic admin session
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Formax_News::homenews_create') ||
        $this->_authorization->isAllowed('Formax_News::homenews_update');
    }
}
