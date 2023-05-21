define(['jquery', 'Magento_Theme/js/model/breadcrumb-list'], function ($, breadcrumbList) {
    'use strict';

    return function (widget) {
        $.widget('mage.breadcrumbs', widget, {
            options: {
                categories: [],
            },
            _resolveCategoryCrumbs: function () {
                var menuItem = this._resolveCategoryMenuItem(),
                    categoryCrumbs = [];

                while (menuItem) {
                    categoryCrumbs.unshift(this._getCategoryCrumb(menuItem));
                    menuItem = this._getParentMenuItem(menuItem);
                }

                return categoryCrumbs;
            },
            _getCategoryCrumb: function (menuItem) {
                return {
                    name: 'category',
                    label: menuItem.name,
                    link: menuItem.url,
                    title: '',
                };
            },
            _getParentMenuItem: function (menuItem) {
                return this.options.categories.find(
                    (category) => category.id == menuItem.parent_id
                );
            },
            _resolveCategoryMenuItem: function () {
                var categoryUrl = this._resolveCategoryUrl();
                return this.options.categories.find((category) => category.url == categoryUrl);
            },
        });

        return $.mage.breadcrumbs;
    };
});
