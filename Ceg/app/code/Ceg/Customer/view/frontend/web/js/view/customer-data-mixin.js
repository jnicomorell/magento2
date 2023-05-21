define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    let mixin = {
        getExpiredSectionNames: function (originFn) {
            let expiredSections = originFn();
            expiredSections.push('cart');
            return _.uniq(expiredSections);
        }
    };

    return function (target) {

        return wrapper.extend(target, mixin);
    };
});
