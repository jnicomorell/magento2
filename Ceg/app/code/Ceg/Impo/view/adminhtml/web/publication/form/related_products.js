define(
    [ 'mage/adminhtml/grid'],
    function () {
        'use strict';

        return function (config) {
            let gridJsObject = window[config.gridJsObjectName];
            let productsHidden = window[config.hiddenInputName];
            let impoProducts = config.selectedProducts;

            function init() {
                productsHidden.value = Object.toJSON(impoProducts);
                gridJsObject.rowClickCallback = impoProductRowClick;
                gridJsObject.checkboxCheckCallback = registerImpoProduct;
            }
            
            function registerImpoProduct(grid, element, checked) {
                let productId = parseInt(element.value, 10);
                if (checked) {
                    impoProducts.push(productId);
                } else {
                    impoProducts.splice(impoProducts.indexOf(productId), 1);
                }
                productsHidden.value = Object.toJSON(impoProducts);
                grid.reloadParams = {
                    'selected_products[]': impoProducts
                }
            }

            function impoProductRowClick(grid, event) {
                let trElement = Event.findElement(event, 'tr');
                let eventElement = Event.element(event);

                if (eventElement.tagName === 'LABEL' &&
                    trElement.querySelector('#' + eventElement.htmlFor) &&
                    trElement.querySelector('#' + eventElement.htmlFor).type === 'checkbox'
                ) {
                    event.stopPropagation();
                    trElement.querySelector('#' + eventElement.htmlFor).trigger('click');
                    return;
                }

                if (trElement) {
                    let checkbox = Element.getElementsBySelector(trElement, 'input');
                    if (checkbox[0]) {
                        let isInputCheckbox = eventElement.tagName === 'INPUT' && eventElement.type === 'checkbox';
                        let checked = isInputCheckbox ? checkbox[0].checked : !checkbox[0].checked;
                        gridJsObject.setCheckboxChecked(checkbox[0], checked);
                    }
                }
            }
            
            init();
        };
    }
);
