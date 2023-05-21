var config = {
    map: {
        '*': {
            'Magento_Checkout/template/shipping-address/address-renderer/default.html':
                'Ceg_CustomerAddressAttributes/template/shipping-address/address-renderer/default.html'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'Ceg_CustomerAddressAttributes/js/action/set-shipping-information-mixin': true
            },
            'Magento_Checkout/js/action/set-billing-address': {
                'Ceg_CustomerAddressAttributes/js/action/set-billing-address-mixin': true
            },
            'Magento_Checkout/js/action/place-order': {
                'Ceg_CustomerAddressAttributes/js/action/set-billing-address-mixin': true
            },
            'Magento_Checkout/js/action/create-billing-address': {
                'Ceg_CustomerAddressAttributes/js/action/set-billing-address-mixin': true
            },
            // 'Magento_Checkout/js/view/billing-address': {
            //     'Ceg_CustomerAddressAttributes/js/view/billing-address-mixin': true
            // },
            'Magento_Checkout/js/model/address-converter': {
                'Ceg_CustomerAddressAttributes/js/model/address-converter-mixin': true
            }
        }
    }
};
