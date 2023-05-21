<?php
namespace Formax\BenefitsCarousel\Block\Type;

class Color extends \Magento\Config\Block\System\Config\Form\Field {

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context, array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * add color picker in admin configuration fields
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string script
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        $html = $element->getElementHtml();
        $value = $element->getData('value');

        $html .= '<script type="text/javascript">
            require(["jquery"], function ($) {
                $(document).ready(function () {
                    var $cp = $("#'.$element->getHtmlId().'");
                    var hex = "'.$value.'";
                    if(!hex.includes("#")){
                        hex = "#"+hex;
                    }
                    $cp.css("backgroundColor", hex);

                    $cp.colpick({
                        layout:"hex",
                        submit:0,
                        colorScheme:"dark",
                        color: "#' . $value . '",
                        onChange:function(hsb,hex,rgb,el,bySetColor) {
                            $(el).css("background-color","#"+hex);
                            if(!bySetColor) $(el).val("#"+hex);
                        }
                    }).keyup(function(){
                        var hex = this.value;
                        if(!hex.includes("#")){
                            hex = "#"+hex;
                        }
                        $(this).colpickSetColor(hex);
                    });
                });
            });
            </script>';

        return $html;
    }

}