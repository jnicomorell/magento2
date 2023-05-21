define(['jquery','mage/translate'], function ($) {
    "use strict";
    var itemMessages = [];
    var itemElement = '';
    var defaultmin;

    $(document).on('click', '.cart .update-action .action-edit', function (e) {
        e.preventDefault();
        cartAjaxQtyupdate(e.target);
    });
    $(document).on("click", ".action-delete-item", function(e){
        e.preventDefault();
        if($("body").hasClass("catalog-product-view")){
            if($(this).attr("data-is-last")){
                openModal(this,'clearcart');
                $(".catalog-product-view .modal_container h3").text($.mage.__('Do you want to empty your cart?'));
                $(".catalog-product-view .modal_container > span .modalTextStart").html($.mage.__('You are about to remove'));
                $(".catalog-product-view .modal_container > span .modalTextEnd").html($.mage.__('from your cart.')+' '+$.mage.__('and you are going to empty your cart.')+'<br>'+$.mage.__('This implies that you are going to cancel your order.')+' Nº '+ $(this).attr("data-order-id"));
                $(".catalog-product-view .modal_container .modal-actions button.action.primary.remove-product span").text($.mage.__('Empty cart'));
            }else{
                openModal(this,'remove');
            }
        }
        if($(".product_item").length > 1){
            openModal(this,'remove');
        }else{
            openModal(this,'clearcart');
        }
    })
    $(document).on("click", ".action-clear-cart", function(e){
        e.preventDefault();
        openModal(this,'remove');
    })
    if($("body").hasClass("catalog-product-view")){
        $(document).on("click", "button.remove-product", function(e){
            e.preventDefault();
            if($(".product_item").length > 0){
                cartAjaxDelete(this, false, true);
            }else{
                cartAjaxDelete(this,true,true);
            }
        })
    }else{
        $(document).on("click", "button.remove-product", function(e){
            e.preventDefault();
            if($(".product_item").length > 0){
                cartAjaxDelete(this);
            }else{
                cartAjaxDelete(this,true);
            }
        })

    }
    $(document).on("click", ".close-modal, .modal_wrapper button.secondary", function(){
        closeModal();
    })
    $(document).ready(function(){
        let updateElSelector = '.cart-summary .checkout-methods-items .item > button.update-cart';
        let checkoutElSelector = '.cart-summary .checkout-methods-items .item > button.checkout';
        setInterval(function(){
            if (checkItemsError()) {
                $(checkoutElSelector).attr({"disable":"disable"});
            } else {
                if ($(updateElSelector).attr("disable") !== undefined) {
                    $(checkoutElSelector).removeAttr("disable");
                }
            }
        },1000)
        setTimeout(function(){
            if($(".ceg-impo.addtocart_box .action_buttons input").val()<=$(".ceg-impo.addtocart_box .action_buttons input").attr('min')){
                $(".ceg-impo .product_item .product_actions .action_buttons a.minusQty").trigger("click")
            }
        })
        $('<li class="item"><button type="button" data-role="update-cart" title="'+$.mage.__('Update units')+'" class="action primary update-cart" disable><span>'+$.mage.__('Update units')+'</span></button></li>').prependTo(".cart-summary .checkout-methods-items")
    })
    $(document).on("click", "button.action.primary.update-cart", function(){
        $(".form-cart#form-validate button.action.update").trigger("click");
    })

    function checkItemsError(){
        let result = false;
        $(".product_item").each(function(){
            if($(this).find('.cart.item.message.error').length > 0){
                result = true;
            }
        })
        return result;
    }

    function openModal(el,type){
        var pname, ptoremove;
        pname = $(el).closest(".product_item").find(".product_details h3").text();
        ptoremove = $(el).attr("data-action-delete");
        $(".modal_wrapper").removeClass("hide");
        $(".modal_wrapper .modal_"+type).removeClass("hide");
        $(".modal_wrapper span.product-to-remove").text(pname);
        $(".modal_wrapper button.remove-product").attr({"data-remove-product":ptoremove})
    }
    function closeModal(){
        $(".modal_wrapper").addClass("hide");
        $(".modal_container").addClass("hide");
    }

    function cartAjaxQtyupdate(element) {
        var form = $('form#form-validate');
        $.ajax({
            url: form.attr('action'),
            data: form.serialize(),
            showLoader: true,
            success: function (res) {
                var parsedResponse = $.parseHTML(res);
                var result = $(parsedResponse).find("#form-validate");
                var sections = ['cart'];

                $("#form-validate").replaceWith(result);

                require(['Magento_Checkout/js/action/get-totals',
                    'Magento_Customer/js/customer-data'], function (getTotalsAction, customerData) {
                    // The mini cart reloading
                    customerData.reload(sections, true);

                    // The totals summary block reloading
                    var deferred = $.Deferred();
                    getTotalsAction([], deferred);

                    //Display error if found after jquery
                    var messages = $.cookieStorage.get('mage-messages');
                    itemMessages = messages;
                    itemElement = element;
                    if (!_.isEmpty(messages)) {
                        customerData.set('messages', {messages: messages});
                        $.cookieStorage.set('mage-messages', '');
                    }
                });
            },
            error: function (xhr, status, error) {
                var err = eval("(" + xhr.responseText + ")");
                console.log(err.Message);
            },
        });
    }
    var elementjson,elementdataObj,actionUrl;
    function cartAjaxDelete(element,clear=false,view=false) {
        elementjson=$(element).attr("data-remove-product");
        elementdataObj = $.parseJSON(elementjson);
        console.log(elementdataObj);
        actionUrl=elementdataObj.action;
        actionUrl=actionUrl.replace("cart", "ajax");
        $.ajax({
            url: actionUrl,
            data: elementdataObj.data,
            dataType: 'json',
            showLoader: true,
            success: function (res) {
                console.log(res);
                closeModal();
                var ptit;
                var sections = ['cart'];
                if(view){
                    ptit = $(".ceg-theme.catalog-product-view .page-main .page-title > span").html();
                }else{
                    ptit = $(".product_item#product_"+elementdataObj.data.id).find(".product_details h3").text();
                }

                $("<div class='msg_product_added'><a class='close_msg'>X</a><div class='pdetail'><h3><span></span>"+$.mage.__('You removed a product')+"</h3><span>"+ptit+".</span></div></div>").prependTo("#maincontent");
                $(".product_item#product_"+elementdataObj.data.id).remove();
                setTimeout(function(){
                    $(".msg_product_added").addClass("show")
                },100);
                setTimeout(function(){
                    $(".msg_product_added").removeClass("show")
                    $(".msg_product_added").addClass("hide")
                },10000)
                setTimeout(function(){
                    $(".msg_product_added").remove()
                },10500)
                if(clear || $("body").hasClass("catalog-product-view")){
                    location.reload();
                }
                $(".cart_count span").text($(".product_item").length)
                require(['Magento_Checkout/js/action/get-totals',
                    'Magento_Customer/js/customer-data'], function (getTotalsAction, customerData) {
                    // The mini cart reloading
                    customerData.reload(sections, true);

                    // The totals summary block reloading
                    var deferred = $.Deferred();
                    getTotalsAction([], deferred);

                    //Display error if found after jquery
                    var messages = $.cookieStorage.get('mage-messages');
                    itemMessages = messages;
                    itemElement = element;
                    if (!_.isEmpty(messages)) {
                        customerData.set('messages', {messages: messages});
                        $.cookieStorage.set('mage-messages', '');
                    }
                });
            },
            error: function (xhr, status, error) {
                var err = eval("(" + xhr.responseText + ")");
                console.log(err.Message);
            },
        });
    }

    $(document).ajaxComplete(function(e){
        e.preventDefault();
        if (!_.isEmpty(itemMessages)) {
            itemMessages.forEach(function(msg){
                jQuery('.page .message').alert({
                    title: $.mage.__('Attention'),
                    content: $.mage.__(msg.text),
                    actions: {
                        always: function(){}
                    }
                });
            });
        }
    });
});
