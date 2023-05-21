<?php
declare(strict_types=1);

namespace Perficient\FinancialAid\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class CustomerGpColumn extends Select
{

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    private $customerGp;

    public function __construct(
        Context $context,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGp,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerGp = $customerGp;
    }

    public function setInputName($value)
    {
        return $this->setName($value . '[]');
    }

    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getCustomerGps());
        }
        $this->setExtraParams('multiple="multiple"');
        return parent::_toHtml();
    }

    public function getCustomerGps()
    {
        $gps = $this->customerGp->toOptionArray();
        return $gps;
    }
}
