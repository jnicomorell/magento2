/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/create-billing-address',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/checkout-data'
],
function (
    customer,
    quote,
    createBillingAddress,
    selectBillingAddress,
    checkoutData
) {
    'use strict';
    var mixin = {
        updateAddress: function () {
            var addressData, newBillingAddress;

            if (this.selectedAddress() && !this.isAddressFormVisible()) {
                selectBillingAddress(this.selectedAddress());
                checkoutData.setSelectedBillingAddress(this.selectedAddress().getKey());
            } else {
                this.source.set('params.invalid', false);
                this.source.trigger(this.dataScopePrefix + '.data.validate');

                if (this.source.get(this.dataScopePrefix + '.custom_attributes')) {
                    this.source.trigger(this.dataScopePrefix + '.custom_attributes.data.validate');
                }

                if (!this.source.get('params.invalid')) {
                    addressData = this.source.get(this.dataScopePrefix);

                    //Cambio a realizar//
                    addressData['custom_attributes'] = this.source.get('billingAddress.custom_attributes');

                    if (customer.isLoggedIn() && !this.customerHasAddresses) { //eslint-disable-line max-depth
                        this.saveInAddressBook(1);
                    }
                    addressData['save_in_address_book'] = this.saveInAddressBook() ? 1 : 0;
                    newBillingAddress = createBillingAddress(addressData);
                    // New address must be selected as a billing address
                    selectBillingAddress(newBillingAddress);
                    checkoutData.setSelectedBillingAddress(newBillingAddress.getKey());
                    checkoutData.setNewCustomerBillingAddress(addressData);
                }
            }
            this.updateAddresses();
        },
    }
    return function (target) {
        return target.extend(mixin);
    };
});
