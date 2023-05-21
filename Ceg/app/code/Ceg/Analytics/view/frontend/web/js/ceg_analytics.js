define(['jquery'], function($) {
    "use strict";

    return function config(data) {

        let credentials = data.credentials;
        window.inDebugMode = data.debug;
        ga('create', credentials, 'auto');

        /** Eventos del Header */
        headerNavigationEvents();

        /** Eventos por configuracion */
        triggerEvents(data.events);

        /** Eventos de categorias */
        categoryClick();
        impoCategoryClick();
        var cartContinueBtn = window.cartContinueBtn = false;

        $(window).on('hashchange', function() {
            var hashValue = location.hash;

            switch (hashValue) {
                case '#shipping':
                    let event = 'user-action';
                    let category = 'checkout-cart';
                    let action = 'click-continue-order-buton';
                    if (!cartContinueBtn) {
                        cartContinueBtn = sendEvent(event, category, action, "");
                    }
                    sendPageView();
                    break;
                case '#payment':
                    sendPageView();
                    break;
                default:
                    break;
            }
        });
    }

    function triggerEvents(events) {
        $.each(events, function(index, value) {
            var action = value.action_ga;
            var category = value.category.replaceAll('_', '-');
            var event = value.event.replaceAll('_', '-');
            var classAction = value.to_class;
            var label = value.label;

            if (action.startsWith('pageview')) {
                if (classAction.includes('#')) {
                    var path = location.pathname;
                    var pathHash = location.hash;
                    var completePath = path.concat(pathHash);
                    if (classAction === completePath) {
                        sendPageView();
                    }
                }
                if (classAction === location.pathname) {
                    sendPageView();
                }
            } else {

                var exists = classExists(classAction);

                if (exists) {
                    $(document).on('click', classAction, function() {
                        console.log(classAction + 'Clicked');
                        sendEvent(event, category, action, label);
                    });
                }
            }
        });
    }

    /** verifica si existe la clase que llega por config */
    function classExists(toClass) {
        if (document.getElementsByClassName(toClass)) {
            return true;
        }
        return false;
    }

    /** eventos del header */
    function headerNavigationEvents() {
        let userAction = 'user-action';
        let category = 'header-navegation';
        let action = 'click-header-navegation-';

        /** Click Carrito */
        $('.action.showcart').click(function(event) {
            event.preventDefault();
            action = action + "cart";
            var isSend = sendEvent(userAction, category, action, "");
            if (isSend) {
                window.location = $(this).attr('href');
            }
        });

        /** click links mi cuenta */
        $('.header.links li a').click(function(event) {
            if (this.id) {
                event.preventDefault();
                event.stopPropagation();
                action = action + "my-account";

                var isSend = sendEvent(userAction, category, action, "");
                if (isSend) {
                    window.location = $(this).attr('href');
                }
            }
        });

        /**  Click Cerrar Sesion */
        $('.authorization-link').click(function(event) {
            if (!$(this).hasClass('processed')) {
                event.preventDefault();
                event.stopPropagation();
                action = action + "logout";
                var isSend = sendEvent(userAction, category, action, "");
                if (isSend) {
                    $(this).unbind(event);
                    $(this).addClass('processed').find('a').click();
                }
            }
        });

        /** datos de la busqueda de productos */
        $("#search_mini_form").submit(function(e) {
            e.preventDefault();
            var searchQuery = $('#search').val();
            action = action + "search";
            if (searchQuery.length > 0) {
                sendEvent(userAction, category, action, searchQuery);
            }
            return true;
        });
    }

    /** category Click */
    function categoryClick() {
        let userAction = 'user-action';
        let category = 'shopp-results-catalog';
        let action = 'click-categories-tree';
        $('.category-ga-option').click(function(event) {
            event.preventDefault();
            event.stopPropagation();
            var label = $(this).text();
            //Remuevo espacios y saltos de linea
            label = label.replaceAll('  ', '');
            label = label.replaceAll(/(\r\n|\n|\r)/gm, '');
            var isSend = sendEvent(userAction, category, action, label);
            if (isSend) {
                window.location = $(this).attr('href');
            }
        });
    }

    /**  Impo category Click */
    function impoCategoryClick() {
        let userAction = 'user-action';
        let category = 'shopp-results-import';
        let action = 'click-categories-tree';
        $('.filter-options-content li.item a').click(function(event) {
            event.preventDefault();
            event.stopPropagation();
            var label = $(this).context.childNodes[0];
            var isSend = sendEvent(userAction, category, action, label);
            if (isSend) {
                window.location = $(this).attr('href');
            }
        })
    }

    /** send Track Event */
    function sendEvent(event, category, action, label = '') {
        ga('send', {
            hitType: event,
            eventCategory: category,
            eventAction: action,
            eventLabel: (label ? label : '')
        });
        if (window.inDebugMode) {
            console.log('Event = ' + event + ' Category = ' + category + ' Action = ' + action + ' Label = ' + label);
        }
        return true;
    }

    /** send PageView Event */
    function sendPageView() {
        let hash = location.hash;
        let dataLocation = location.pathname
        if (hash.length > 0) {
            dataLocation = dataLocation.concat(hash);
        }
        ga('send', 'pageview', dataLocation);
        if (window.inDebugMode) {
            console.log('Pageview = ' + dataLocation);
        }
    }

});
