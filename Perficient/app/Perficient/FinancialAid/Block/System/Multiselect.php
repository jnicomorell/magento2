<?php

namespace Perficient\FinancialAid\Block\System;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Multiselect
 * @package Perficient\FinancialAid\Block\System
 */
class Multiselect extends Field
{
    /**
     * Backend URL instance
     *
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_url;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\UrlInterface $url,
        array $data = []
    ) {
        $this->_url = $url;
        parent::__construct($context, $data);
    }

    /**
     * @return null|string
     */
    public function getSecretKey()
    {

        return $this->_url->getSecretKey('financialaid', 'school', 'ajaxLoadSchools');
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        // @codingStandardsIgnoreLine
        $script = ' <script>
        require([
            "jquery",
            "mage/multiselect"
        ], function ($) {
            "use strict";
            const selectedSchools = [];
            $("#'.$element->getId().' option").each(function(){
                selectedSchools.push(jQuery(this).val());
            })
            var options = {
                mselectContainer: "#'.$element->getId().' + section.mselect-list",
                toggleAddButton:true,
                parse: null,
                nextPageUrl: "/admin/financialaid/school/ajaxLoadSchools/key/'.$this->getSecretKey().'/",
                selectedValues: selectedSchools,
                mselectCheckedClass:"checked_custom",
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
            var schools = $("#'.$element->getId().'"),
                schoolsField = schools.parent(),
                schoolsForm = $("#tax-rate-form");


                schools.multiselect2(options);
        })
    </script>';
        return parent::_getElementHtml($element) . $script ;
    }
}
