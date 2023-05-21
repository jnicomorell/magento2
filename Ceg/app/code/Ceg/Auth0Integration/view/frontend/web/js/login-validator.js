require([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    let getCustomerInfo = function () {
        let customer = customerData.get('customer');

        return customer();
    };

    let isLoggedIn = function (customerInfo) {
        customerInfo = customerInfo || getCustomerInfo();

        return customerInfo && customerInfo.firstname;
    };

    $(document).ready(function () {
        let deferred = $.Deferred();
        let customerInfo = getCustomerInfo();

        if (customerInfo && customerInfo.data_id) {
            deferred.resolve(isLoggedIn(customerInfo));
        } else {
            customerData.reload(['customer'], false)
                .done(function () {
                    deferred.resolve(isLoggedIn());
                })
                .fail(function () {
                    deferred.reject();
                });
        }
    });

});
