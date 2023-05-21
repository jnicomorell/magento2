<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ceg\Checkout\Rewrite\Magento\Checkout\Controller\Cart;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Action Delete.
 *
 * Deletes item from cart.
 */
class Delete extends \Magento\Checkout\Controller\Cart\Delete
{
    /**
     * Delete shopping cart item action
     *
     * @return string
     */
    public function execute()
    {
        //if (!$this->_formKeyValidator->validate($this->getRequest())) {
        //    return $this->resultRedirectFactory->create()->setPath('*/*/');
        //}

        $id = (int)$this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->cart->removeItem($id);
                // We should set Totals to be recollected once more because of Cart model as usually is loading
                // before action executing and in case when triggerRecollect setted as true recollecting will
                // executed and the flag will be true already.
                $this->cart->getQuote()->setTotalsCollectedFlag(false);
                $this->cart->save();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('We can\'t remove the item.'));
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            }
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData(["deleted" => true, "success" => true]);
        return $resultJson;

        //$defaultUrl = $this->_objectManager->create(\Magento\Framework\UrlInterface::class)->getUrl('*/*');
        //return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl($defaultUrl));
    }
}
