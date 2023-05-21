define([
    'jquery'
], function ($) {
    'use strict';

    return function (config, element) {
        $(element).click(function (event) {
            event.preventDefault();

            location.href = config.checkoutUrl;
        });
    };
});
