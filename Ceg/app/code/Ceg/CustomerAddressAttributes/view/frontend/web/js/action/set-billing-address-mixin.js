define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper,quote) {
    'use strict';

    return function (setBillingAddressAction) {
        return wrapper.wrap(setBillingAddressAction, function (originalAction, messageContainer) {

            var billingAddress = quote.billingAddress();
            if(billingAddress != undefined) {
                if (billingAddress['extension_attributes'] === undefined) {
                    billingAddress['extension_attributes'] = {};
                }
                billingAddress['extension_attributes']["same_as_shipping"] = $('input[name=billing-address-same-as-shipping]').is(':checked');

                if (billingAddress.customAttributes != undefined) {
                    billingAddress.customAttributes.forEach(function callback(value, index) {
                        billingAddress['extension_attributes'][value["attribute_code"]] = value["value"];
                        billingAddress['customAttributes'][value["attribute_code"]] = value["value"];
                    });
                }

            }
            return originalAction(messageContainer);
        });
    };
});
