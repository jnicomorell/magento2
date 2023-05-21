<?php
namespace Perficient\FinancialAid\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
    
class Groups extends AbstractFieldArray
{
    /**
     * @var \Magento\Framework\View\Element\BlockInterface
     */
    protected $paymentMethod;
    /**
     * @var \Magento\Framework\View\Element\BlockInterface
     */
    protected $customerGpRenderer;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
   
        $this->_request = $request;
   
        parent::__construct($context, $data);
    }
    protected function _prepareToRender()
    {
        // For Customer Groups
        $this->addColumn('customer_gp', [
            'label' => __('Customer Group'),
            'renderer' => $this->getCustomerGpRenderer(),
            'extra_params' => 'multiple="multiple"'
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    protected function _prepareArrayRow(DataObject $row)
    {
        $options = [];
        $customerGps = $row->getCustomerGp();
        if (($customerGps)) {
            foreach ($customerGps as $gp) {
                $options['option_' . $this->getCustomerGpRenderer()->calcOptionHash($gp)]
                    = 'selected="selected"';
            }
        }
        $row->setData('option_extra_attrs', $options);
    }
    /**
     * Retrieve Element HTML fragment
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $script = ' <script>
        require([
            "jquery",
            "mage/multiselect"
        ], function ($) {
            "use strict";
            $("#'.$element->getId().' select").multiselect2("{nextPageUrl: [2, 3]}");
        })
    </script>';
        return parent::_getElementHtml($element) . $script;
    }


    protected function getCustomerGpRenderer()
    {
        if (!$this->customerGpRenderer) {
            $this->customerGpRenderer = $this->getLayout()->createBlock(
                \Perficient\FinancialAid\Block\Adminhtml\Form\Field\CustomerGpColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->customerGpRenderer;
    }
}
