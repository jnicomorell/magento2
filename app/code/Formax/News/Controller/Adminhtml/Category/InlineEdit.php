<?php

namespace Formax\News\Controller\Adminhtml\Category;

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
                foreach (array_keys($postItems) as $categoryId) {
                    /** @var \Formax\News\Model\Category $model */
                    $model = $this->_objectManager->create('Formax\News\Model\Category');
                    $model->load($categoryId);
                    try {
                        $model->setData(array_merge($model->getData(), $postItems[$categoryId]));
                        $model->save();
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithCategoryId(
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
     * Add category name to error message
     *
     * @param \Formax\News\Model\Category $category
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithCategoryId(\Formax\News\Model\Category $category, $errorText)
    {
        return '[Category ID: ' . $category->getId() . '] ' . $errorText;
    }

    /**
     * Authorization level of a basic admin session
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Formax_News::category_create') ||
        $this->_authorization->isAllowed('Formax_News::category_update');
    }
}
