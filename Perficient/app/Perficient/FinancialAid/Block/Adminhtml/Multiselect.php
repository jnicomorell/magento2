<?php
 
namespace Perficient\FinancialAid\Block\Adminhtml;

class Multiselect extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
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
            var options = {
                mselectContainer: "#'.$element->getId().' + section.mselect-list",
                toggleAddButton:false,
                addText: "{$block->escapeJs(__(\'Add New Tax Rate\'))}",
                parse: null,
                nextPageUrl: "{$block->escapeJs($block->getTaxRatesPageUrl())}",
                selectedValues: this.settings.selected_values,
                mselectInputSubmitCallback: function (value, options) {
                    var select = $("#'.$element->getId().'");
                        select.append("<option value=\'\' selected=\'selected\'>" + value + "</option>");
                    var mselectItemHtml = $(options.item.replace(/%value%|%label%/gi, value)
                        .replace(/%mselectCheckedClass%|%mselectDisabledClass%|%iseditable%|%isremovable%/gi, "")
                        .replace(/%checked%|%disabled%/gi, "")
                        .replace(/%mselectListItemClass%/gi, options.mselectListItemClass))
                        .find("[type=checkbox]")
                        .attr("checked", true)
                        .addClass(options.mselectCheckedClass)
                        .end();
                    var itemsWrapper = select.nextAll("section.block:first")
                        .find("." + options.mselectItemsWrapperClass + "");
                    itemsWrapper.children("." + options.mselectListItemClass + "").length
                        ? itemsWrapper.children("." + options.mselectListItemClass + ":last").after(mselectItemHtml)
                        : itemsWrapper.prepend(mselectItemHtml);
                }
            };
            var taxRate = $("#tax_rate"),
                taxRateField = taxRate.parent(),
                taxRateForm = $("#tax-rate-form"),
                taxRateFormElement = $("#{$formElementId}");

            if (!this.isEntityEditable) {
                // Override default layout of editable multiselect
                options["layout"] = <section class="block %mselectListClass%">
                        + "<div class="block-content"><div class='%mselectItemsWrapperClass%'>"
                        + "%items%"
                        + "</div></div>"
                        + "<div class=\'%mselectInputContainerClass%\'>"
                        + "<input type=\'text\' class=\'%mselectInputClass%\' title=\'%inputTitle%\'/>"
                        + "<span class=\'%mselectButtonCancelClass%\' title=\'%cancelText%\'></span>"
                        + "<span class=\'%mselectButtonSaveClass%\' title=\'Add\'></span>"
                        + "</div>"
                        + "</section>";
                options["mselectInputSubmitCallback"] = null;
            }

            taxRate.multiselect2(options);
        })
    </script>';
        return parent::_getElementHtml($element) . $script;
    }
}
