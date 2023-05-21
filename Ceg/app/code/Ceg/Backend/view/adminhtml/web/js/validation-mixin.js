define(['jquery'], function ($) {
    'use strict';
    return function () {
        $.widget('mage.validation', $.mage.validation, {
            _init: function () {
                let self = this;
                $(document).on("click", ".auth0-login", function(e) {
                    let form = self.element;
                    form[0].action = window.auth0Url;
                });
            }
        });
        return $.mage.validation;
    };
});
