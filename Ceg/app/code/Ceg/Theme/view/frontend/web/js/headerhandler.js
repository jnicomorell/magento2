define([
    'jquery'
],
function ($) {
    'use strict';

    function handleMenuEvent(e, el, tar) {
        e.preventDefault();
        e.stopImmediatePropagation();
        let url = $(el).attr('href');
        window.open(url, tar);
        $(el).trigger("click.toggleDropdown");
        $('.action.nav-toggle').trigger("click");
    }

    return function (config) {
        $(config.menuItemStore.selector).on('click', function (e) {
            handleMenuEvent(e, this, config.menuItemStore.target)
        });
        $(config.menuItemStore.selector).on('tap', function (e) {
            handleMenuEvent(e, this, config.menuItemStore.target)
        });

        $(config.menuItemBilling.selector).on('click', function (e) {
            handleMenuEvent(e, this, config.menuItemBilling.target)
        });
        $(config.menuItemBilling.selector).on('tap', function (e) {
            handleMenuEvent(e, this, config.menuItemBilling.target)
        });

        $(config.menuItemOrders.selector).on('click', function (e) {
            handleMenuEvent(e, this, config.menuItemOrders.target)
        });
        $(config.menuItemOrders.selector).on('tap', function (e) {
            handleMenuEvent(e, this, config.menuItemOrders.target)
        });




    }
});
