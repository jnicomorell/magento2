<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Perficient\FinancialAid\Controller\Adminhtml\Index;
 
class FinancialAid extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customer FinancialAid grid
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->initCurrentCustomer();
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
