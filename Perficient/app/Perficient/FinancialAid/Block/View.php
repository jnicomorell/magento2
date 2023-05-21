<?php
namespace Perficient\FinancialAid\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

class View extends Template
{

    /**
     * @param array $data
     */

    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
}
