<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Perficient\FinancialAid\Controller\Adminhtml\Index;
 
class Save extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Perficient\FinancialAid\Model\FinancialAid $financialaid
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->financialaid = $financialaid;
    }

    public function execute()
    {
        $model = $this->financialaid;
        $model->setData('id', $this->getRequest()->getParam('id'));
        $model->setData('status', $this->getRequest()->getParam('status'));
        $model->save();

        if ($this->getRequest()->isAjax()) {
         

            return  $this->resultJsonFactory->create()->setData(['Test-Message' => 'hi']);
        }

         return  $this->resultJsonFactory->create();
    }
}
