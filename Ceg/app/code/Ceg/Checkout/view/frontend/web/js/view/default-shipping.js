define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/view/shipping'
    ],
    function(
        $,
        ko,
        Component
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Ceg_Checkout/shipping'
            },

            initialize: function () {
                this._super();
            }
        });
    }
);
