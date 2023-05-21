<?php

namespace Formax\FormCrud\Controller\Adminhtml\FormCrud;

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
                foreach (array_keys($postItems) as $termId) {
                    /** @var \Formax\MortgageSimulator\Model\Term $model */
                    $model = $this->_objectManager->create('Formax\FormCrud\Model\FormCrud');
                    $model->load($termId);
                    try {
                        $model->setData(array_merge($model->getData(), $postItems[$termId]));
                        $model->save();
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithTermId(
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
     * Add term name to error message
     *
     * @param \Formax\MortgageSimulator\Model\Term $term
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithTermId(\Formax\MortgageSimulator\Model\Term $term, $errorText)
    {
        return '[FormCrud ID: ' . $term->getId() . '] ' . $errorText;
    }

    /**
     * Authorization level of a basic admin session
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Formax_FormCrud::formcrud_create') ||
        $this->_authorization->isAllowed('Formax_FormCrud::formcrud_update');
    }
}
