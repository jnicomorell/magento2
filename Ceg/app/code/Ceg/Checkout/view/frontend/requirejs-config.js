var config = {
    map: {
        '*': {
            'Magento_Checkout/js/proceed-to-checkout': 'Ceg_Checkout/js/cart/proceed-to-checkout',
            'CartAjaxQtyUpdate': 'Ceg_Checkout/js/cart/cart-ajax-qty-update',
            'Magento_OfflinePayments/template/payment/banktransfer.html':
                'Ceg_Checkout/template/payment.html',
            'Magento_Checkout/template/estimation.html':
                'Ceg_Checkout/template/estimation.html'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'Ceg_Checkout/js/model/checkout-data-resolver': true
            }
        }
    },
    shim: {
        CartAjaxQtyUpdate: {
            deps: ['jquery']
        }
    }
};
