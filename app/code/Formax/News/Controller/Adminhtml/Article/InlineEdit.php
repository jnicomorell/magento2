<?php

namespace Formax\News\Controller\Adminhtml\Article;

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
                foreach (array_keys($postItems) as $articleId) {
                    /** @var \Formax\News\Model\Article $model */
                    $model = $this->_objectManager->create('Formax\News\Model\Article');
                    $model->load($articleId);
                    try {
                        $model->setData(array_merge($model->getData(), $postItems[$articleId]));
                        $model->save();
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithArticleId(
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
     * Add article name to error message
     *
     * @param \Formax\News\Model\Article $article
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithArticleId(\Formax\News\Model\Article $article, $errorText)
    {
        return '[Article ID: ' . $article->getId() . '] ' . $errorText;
    }

    /**
     * Authorization level of a basic admin session
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Formax_News::article_create') ||
        $this->_authorization->isAllowed('Formax_News::article_update');
    }
}
