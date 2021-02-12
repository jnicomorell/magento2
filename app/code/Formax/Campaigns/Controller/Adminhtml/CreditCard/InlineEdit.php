<?php

namespace Formax\Campaigns\Controller\Adminhtml\CreditCard;

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
                    /** @var \Formax\Campaigns\Model\CreditCard $model */
                    $model = $this->_objectManager->create('Formax\Campaigns\Model\CreditCard');
                    $model->load($fileId);
                    try {
                        $model->setData(array_merge($model->getData(), $postItems[$fileId]));
                        $model->save();
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithCreditCardId(
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
     * @param \Formax\Campaigns\Model\CreditCard $file
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithCreditCardId(\Formax\Campaigns\Model\CreditCard $file, $errorText)
    {
        return '[CreditCard ID: ' . $file->getId() . '] ' . $errorText;
    }

    /**
     * Authorization level of a basic admin session
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Formax_Campaigns::credit_card_campaign_create') ||
        $this->_authorization->isAllowed('Formax_Campaigns::credit_card_campaign_update');
    }
}
