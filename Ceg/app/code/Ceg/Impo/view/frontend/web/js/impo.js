define([
        'jquery',
        'mage/translate',
        'mage/url',
        'Magento_Ui/js/modal/modal',
        'Magento_Customer/js/customer-data',
    ],
    function ($, $t, url, modal,customerData) {

        return function(config) {
            $(document).ready(function() {
                navigationImpo();
                getItemsCart(config.currency);
                if(!$("body").hasClass("checkout-cart-index")){
                    getItemsCartDetail(config.currency);
                }
                loadPrices(config.currency);
                setInterval(function() {
                    minusQtyButton();
                }, 100);
                updateLabels(config.currency);
                $(document).on("click", ".updatecart", function(e) {
                    updateImpoView(e,config.currency);
                });
                closePopup();
                // Skeleton placeholder init
                if($(".pre-loading")){
                    setTimeout(preLoadingDisabled, 2000);
                }


            });
        }

        // Skeleton placeholder fun
        function preLoadingDisabled() {
            $(".pre-loading").removeClass('pre-loading')
        }
        // End skeleton fun


        function getItemsCart(currency) {
            console.log(Date.now());
            let sectionLoadUrl = url.build('customer/section/load');
            let paramSection = 'sections=cart';
            let paramTimestamp = 'force_new_section_timestamp=false&_=' + Date.now();
            jQuery.getJSON(sectionLoadUrl + '?' + paramSection + '&' + paramTimestamp, function(result) {
                $.each(result.cart.items, function(key, cartdata) {
                    let productId = cartdata.product_id;
                    $('#lblatc_' + productId).show();
                    $('#card-'+productId).addClass('product-added');

                    let element = $('#card-'+productId);
                    let inputQty = element.find("input[type=number].qty");
                    let minimumQty = inputQty.attr("min");
                    inputQty.closest("form").attr({
                        "action": url.build("checkout/cart/updatePost/")
                    })
                    inputQty.closest("form").removeAttr("data-role");
                    inputQty.val(cartdata.qty)
                    inputQty.attr({
                        "name": "cart[" + cartdata.item_id + "][qty]"
                    })
                    inputQty.attr({
                        "id": "cart-" + cartdata.item_id + "-qty"
                    })
                    inputQty.attr({
                        "data-current-value": cartdata.qty
                    })
                    inputQty.attr({
                        "data-cart-item-id": $(".ceg-theme.catalog-product-view .page-main .page-title").text()
                    })
                    inputQty.attr({
                        "data-qtyinbox": $("input[name='qtyInbox']").val()
                    })

                    let $button = $(inputQty).closest(".product_prices").find(".actionadd");
                    $button.removeClass("actionadd");
                    $button.addClass("actionupdate");
                    $button.addClass("disabled");
                    $button.html($button.attr('data-update-text'));
                    let qty_box = parseInt($(inputQty).data("qtyinbox"), 10);
                    let newValue = parseInt($(inputQty).val(), 10);
                    let calcNewValue = newValue * qty_box;
                    $(inputQty).closest(".product_item").find(".qty_box > span").text(calcNewValue)
                    $(".ceg-impo.addtocart_box .action_buttons button.action.tocart.actionadd").addClass("hidden");
                    $(".ceg-impo.addtocart_box .action_buttons button.action.tocart.actionupdate").removeClass("hidden");
                    updateElementClass(inputQty.val(), minimumQty, $(element).parent().find(".minusQty"));
                    updatePrices(inputQty, currency);
                })
            });

        }


       function getItemsCartDetail (currency) {
            url.setBaseUrl(window.BASE_URL);
            let sectionLoadUrl = url.build('customer/section/load');
            let paramSection = 'sections=cart';
            let paramTimestamp = 'force_new_section_timestamp=false&_=' + Date.now();
            jQuery.getJSON(sectionLoadUrl + '?' + paramSection + '&' + paramTimestamp, function(result) {

                $.each(result.cart.items, function (key, cartdata) {
                    $.each(cartdata, function (key, value) {
                        let uencstr = $(".addtocart form").attr("data-add-action");
                        const dataaction = uencstr.split("uenc/");
                        const dataaction2 = dataaction[1].split("/product");
                        let uenc = dataaction2[0];
                        var itemDelete = {};
                        itemDelete.action = url.build("checkout/cart/delete/");
                        itemDelete.data = {};
                        itemDelete.data.id = cartdata.item_id;
                        itemDelete.data.uenc = uenc;
                        const actiondelete = JSON.stringify(itemDelete);
                        //console.log(itemDelete);
                        let isLast = true;
                        if ((result.cart.items).length > 1) {
                            isLast = false;
                        }
                        if (key === 'product_id') {
                            if (value === $("#product_id").val()) {
                                $(".added-to-cart").removeClass("hidden");
                                $(".catalog-product-view .action-delete-item").removeClass("hidden");
                                $(".catalog-product-view .action-delete-item").attr({"data-action-delete": actiondelete});
                                $.ajax({
                                    url: url.build("impo/ajax/orderdata"),
                                    success: function (response) {
                                        console.log(response.quote.order_id);
                                        if (isLast && response.quote.order_id != 0) {
                                            $(".catalog-product-view .action-delete-item").attr({
                                                "data-is-last": true,
                                                "data-order-id": response.quote.order_id
                                            })
                                        }
                                    }
                                })

                                let inputQty = $("input[type=number].qty");
                                inputQty.closest("form").attr({
                                    "action": url.build("checkout/cart/updatePost/")
                                })
                                inputQty.closest("form").removeAttr("data-role");
                                inputQty.val(cartdata.qty)
                                inputQty.attr({
                                    "name": "cart[" + cartdata.item_id + "][qty]"
                                })
                                inputQty.attr({
                                    "id": "cart-" + cartdata.item_id + "-qty"
                                })
                                inputQty.attr({
                                    "data-current-value": cartdata.qty
                                })
                                inputQty.attr({
                                    "data-cart-item-id": $(".ceg-theme.catalog-product-view .page-main .page-title").text()
                                })
                                inputQty.attr({
                                    "data-qtyinbox": $("input[name='qtyInbox']").val()
                                })
                                $('#lblatc_' + cartdata.product_id).show();
                                $('#card-' + cartdata.product_id).addClass('product-added');
                                let $button = $(this).closest(".product_prices").find(".actionadd");
                                $button.removeClass("actionadd");
                                $button.addClass("actionupdate");
                                $button.addClass("disabled");
                                $button.html($button.attr('data-update-text'));
                                let qty_box = parseInt($(inputQty).data("qtyinbox"), 10);
                                let newValue = parseInt($(inputQty).val(), 10);
                                let calcNewValue = newValue * qty_box;
                                $(inputQty).closest(".product_item").find(".qty_box > span").text(calcNewValue)
                                $(".ceg-impo.addtocart_box .action_buttons button.action.tocart.actionadd").addClass("hidden");
                                $(".ceg-impo.addtocart_box .action_buttons button.action.tocart.actionupdate").removeClass("hidden");
                                $(inputQty).each(function () {
                                    let qtyInput = $(this);
                                    let qtyValue = qtyInput.val();
                                    let minimumQty = qtyInput.attr("min");
                                    updateElementClass(qtyValue, minimumQty, $(this).parent().find(".minusQty"));
                                    updatePrices(this, currency);
                                });
                            }
                        }
                    });
                });
            });
        }

        function navigationImpo() {
            $(".navigation").each(function() {
                $(this).find("li").each(function(index) {
                    let split_classes = $(this).attr("class").split(' ');
                    if (split_classes.indexOf("ceg-impo-menu") > 0) {
                        $(this).addClass("active");
                    } else {
                        $(this).removeClass("active");
                    }
                });
            });
        }

        function minusQtyButton() {
            $(".qty").each(function() {
                let $currentQty = $(this).attr('data-current-value');
                $currentQty = isNaN($currentQty) ? 0 : $currentQty;
                let $button = $(this).parent().parent().find("button.action.tocart");
                if (parseInt($currentQty) === parseInt($(this).attr('value'))) {
                    $button.addClass("disabled");
                } else {
                    $button.removeClass("disabled");
                }

                if ($(this).val() === $(this).attr('min')) {
                    $(this).parent().find(".minusQty").addClass("disable")
                } else {
                    $(this).parent().find(".minusQty").removeClass("disable")
                }
            })
        }

        function updateLabels(currency) {
            $(document).on("click", ".minusQty, .plusQty", function () {

                let qtyInput = this.parentNode.querySelector('input[type=number].qty');
                let qtyStep = parseInt(qtyInput.step);
                let qtyValue = parseInt($(qtyInput).val());
                let minimumQty = parseInt($(qtyInput).attr('min'));

                if ($(this).hasClass('plusQty')) {
                    if (qtyValue === 0 && qtyStep < minimumQty) {
                        let newValue = (qtyStep * Math.ceil(minimumQty / qtyStep));
                        $(qtyInput).val(newValue);
                    } else {
                        qtyInput.stepUp()
                    }
                    qtyValue = parseInt($(qtyInput).val());
                    let qty_box = parseInt($(qtyInput).data("qtyinbox"), 10);
                    let newValue = parseInt($(qtyInput).val(), 10);
                    let calcNewValue = newValue * qty_box;
                    $(qtyInput).closest(".product_item").find(".qty_box > span").text(calcNewValue)
                }
                if ($(this).hasClass('minusQty')) {
                    if ((qtyValue - qtyStep) < minimumQty) {
                        $(qtyInput).val(minimumQty);
                    } else {
                        qtyInput.stepDown()
                    }
                    let qty_box = parseInt($(qtyInput).data("qtyinbox"), 10);
                    let newValue = parseInt($(qtyInput).val(), 10);
                    let calcNewValue = newValue * qty_box;
                    $(qtyInput).closest(".product_item").find(".qty_box > span").text(calcNewValue)
                }
                updatePrices(this, currency);
            });
        }

        function updatePrices(element, currency) {
            let elementInput = $(element).closest(".product_prices");
            let elementQty = elementInput.find("input[type=number].qty");
            let currentValue = parseInt($(elementQty).data("currentValue"));
            let minQty = parseInt($(elementQty).attr("min"));
            let qty = elementQty.val();

            if(minQty > qty){
                qty = minQty;
                elementQty.val(minQty);
            }

            let price = $(element).closest(".product_item").find(".tierprices").filter(function() {
                return $(this).data("qty-from") <= qty && qty <= $(this).data("qty-to");
            }).first().find('span[data-price]').data('price');

            $(element).closest(".product_item").find(".tierprices").removeClass('selectedTier');
            $(element).closest(".product_item").find(".tierprices").filter(function() {
                return $(this).data("qty-from") <= qty && qty <= $(this).data("qty-to");
            }).addClass('selectedTier');

            let tax = $(element).closest(".product_item").find(".tierprices").filter(function() {
                return $(this).data("qty-from") <= qty && qty <= $(this).data("qty-to");
            }).first().find('small[data-tax]').data('tax');

            let qtyInBox = elementQty.data('qtyinbox');

            let newPrice = parseFloat(price) * parseFloat(qty);
            let newTax = parseFloat(tax) * parseFloat(qty);
            let newPriceNet = (parseFloat(price) * parseFloat(qty)) - parseFloat(newTax);
            let taxPriceAppr = newTax / qty;
            let netPriceAppr = (newPrice / qty) - taxPriceAppr;

            if (isNaN(newPrice))
                newPrice = 0;
            if (isNaN(newTax))
                newTax = 0;
            if (isNaN(newPriceNet))
                newPriceNet = 0;
            if (isNaN(netPriceAppr))
                netPriceAppr = 0;

            netPriceAppr = netPriceAppr / qtyInBox;
            elementInput.find(".tocartmini").addClass("activecart");
            elementInput.find(".cartqty").html("+" + qty);
            elementInput.find(".product_price .price").html(formatPrice(currency, newPrice.toFixed(2)));
            elementInput.find(".netprice_tax .price").html(formatPrice(currency, newPriceNet.toFixed(2)));
            elementInput.find(".netprice_tax .tax").html(formatPrice(currency, newTax.toFixed(2)));
            elementInput.find(".net-price-appr > span").html(formatPrice(currency, netPriceAppr.toFixed(3)));
            elementInput.find(".actionadd").removeClass("disabled");

            if($("body").hasClass("checkout-cart-index")){

                let updateEl = $('.cart-summary .checkout-methods-items .item > button.update-cart');
                let checkoutEl = $('.cart-summary .checkout-methods-items .item > button.checkout');
                let hasChanged = false;
                $(".product_item").each(function() {
                    let elementQty = $(this).find("input[type=number].qty");
                    let currentValue = parseInt($(elementQty).data("currentValue"));
                    let qty = elementQty.val();
                    if(currentValue != qty && !isNaN(currentValue) && !isNaN(qty)){
                        hasChanged = true;
                    }
                    if(hasChanged === true){
                        updateEl.removeAttr("disable");
                        checkoutEl.attr({"disable":"disable"});
                    }else{
                        updateEl.attr({"disable":"disable"});
                        checkoutEl.removeAttr("disable");
                    }
                });
            }
        }

        function formatPrice(currency, price) {
            let currencySymbol, entPrice, cents
            currencySymbol = "<span class='currencySymbol'>" + currency + "</span>";
            entPrice = "<span class='entPrice'>" + thousandsWithCommas(Math.floor(price)) + "</span>";
            cents = "<span class='cents'>" + (price + "").split(".")[1] + "</span>";

            return currencySymbol + entPrice + cents;
        }

        function thousandsWithCommas(price) {
            return price.toLocaleString('en-US', {maximumFractionDigits:2})
        }

        function loadPrices(currency) {
            $("input[type=number].qty").each(function() {
                updatePrices(this, currency);
                checkNeedToApprove();
            });
        }

        function checkNeedToApprove() {
            $.ajax({
                url: url.build("impo/ajax/data"),
                success: function(response) {
                    if (response.quote.need_to_approve) {
                        $(".ceg-theme.impo-view-index .page-header").addClass("product_added");
                    }
                }
            })
        }

        function ajaxUpdate(el, update = true) {
            let qty = $(el).closest(".product_prices").find("input[type=number].qty").val();
            let minOrder = $(el).closest(".product_prices").find("input[type=number].qty").attr('min');
            let form = $(el).closest('form');

            if (update) {
                if (form.attr("data-role") == 'tocart-form' && qty > 0) {
                    form.submit();
                    form.attr({
                        "action": url.build("checkout/cart/updatePost/")
                    });
                    form.removeAttr("data-role");
                    form.find("input.qty").attr({
                        "data-role": "cart-item-qty"
                    });
                    $.ajax({
                        url: url.build("impo/ajax/index"),
                        data: {
                            "productid": $(el).closest('form').attr("data-product-id")
                        },
                        success: function(res) {
                            form.find("input.qty").attr({
                                "name": "cart[" + res.quoteid + "][qty]"
                            });
                            form.find("input.qty").attr({
                                "id": "cart-" + res.quoteid + "-qty"
                            });
                            form.find("input.qty").attr({
                                "data-current-value": qty
                            });

                            let $button = $(el).closest(".product_prices").find(".actionadd");
                            let $addedToCart = form.attr("data-product-id");
                            $('#lblatc_' + $addedToCart).show();
                            $('#card-'+$addedToCart).addClass('product-added');
                            $button.removeClass("actionadd");
                            $button.addClass("actionupdate");
                            $button.addClass("disabled");
                            $button.html($button.attr('data-update-text'));
                            setTimeout(function() {
                                checkNeedToApprove();
                            }, 10000);
                        }
                    })
                } else if (parseInt(qty) === parseInt(minOrder)) {
                   cartAjaxQtyupdate(form);
                   form.attr({
                        "action": form.attr('data-add-action')
                    });
                    form.attr({
                        "data-role": 'tocart-form'
                    });
                    form.find("input.qty").removeAttr("data-role");
                    form.find("input.qty").attr({
                        "name": "qty"
                    });
                    form.find("input.qty").removeAttr("id");
                    form.find("input.qty").removeAttr("data-current-value");

                    let $button = $(el).closest(".product_prices").find(".actionupdate");
                    let $addedToCart = form.attr("data-product-id");
                    $('#lblatc_' + $addedToCart).hide();
                    $('#card-'+$addedToCart).removeClass('product-added');
                    $button.removeClass("actionupdate");
                    $button.addClass("actionadd");
                    $button.addClass("disabled");
                    $button.html($button.attr('data-add-text'));
                } else {
                    cartAjaxQtyupdate(form);
                }
            }
        }

        function closePopup(){
            $(document).on("click", ".close_msg", function(e) {
                $('.msg_product_added').remove();
            });
        }

       function cartAjaxQtyupdate(element) {
            let form = element;
            $.ajax({
                url: form.attr('action'),
                data: form.serialize(),
                showLoader: true,
                success: function (res) {
                    let pimg, ptit, pqty, minOrder, pqtyToCompare;
                    pimg = form.closest(".product_item").find(".product_image img").attr("src");
                    if(pimg === undefined)
                        pimg = $(".ceg-theme.catalog-product-view .product.media img.fotorama__img").attr("src");
                    ptit = form.closest(".product_item").find(".product_details h3").text();
                    if(ptit==='')
                        ptit = $(".ceg-theme.catalog-product-view .page-main .page-title > span").text();
                    pqtyToCompare = form.closest(".product_item").find("input.qty").val();
                    pqty = form.closest(".product_item").find(".qty_box span").text();
                    if($("body").hasClass("catalog-product-view")){
                        pqty = form.closest(".product_item").find(".qty_box span").text();
                    }
                    form.closest(".product_item").find("input.qty").attr({"data-current-value":pqty});
                    minOrder = form.closest(".product_item").find("input.qty").attr('min');
                    if(pqtyToCompare>minOrder){
                        $("<div class='msg_product_added'><a class='close_msg'>X</a><div class='pimg'><img src='"+pimg+"' /></div><div class='pdetail'><h3><span></span>"+ptit+"</h3><span>"+pqty+" "+$.mage.__('Units')+"</span></div></div>").prependTo("#maincontent");
                        form.closest(".product_item").find(".added-to-cart").show();
                    }else{
                        form.closest(".product_item").find(".added-to-cart").remove();
                    }

                    customerData.reload(['cart'], true);

                    setTimeout(function(){
                        $(".msg_product_added").addClass("show");
                    },100);

                    setTimeout(function(){
                        $(".msg_product_added").removeClass("show").addClass("hide");
                    },10000);

                    setTimeout(function(){
                        $(".msg_product_added").remove();
                        checkNeedToApprove();
                    },10500);
                },
            });
        }

       function updateElementClass(qty, minimumQty, element) {
                (parseInt(qty) === parseInt(minimumQty))
                    ? element.addClass("removeItem")
                    : element.removeClass("removeItem");
        }

       function updateImpoView(e,currency){
           e.preventDefault();

           let productQty = $(e.currentTarget).closest(".product_item").find("input.qty").val();
           let cartMin =$(e.currentTarget).closest(".product_item").find(".varMinQty").val();
           let minQty = $(e.currentTarget).closest(".product_item").find('input[name="qtyInbox"]').val()* cartMin;
           let minOrder = $(e.currentTarget).closest(".product_prices").find("input[type=number].qty").attr('min')

           if(productQty === minOrder) {
               ajaxUpdate(e.currentTarget,true);
               updatePrices(this,currency);
           } else {
               if( parseInt(cartMin) > parseInt(productQty) ){
                   $("<div class='msg_product_added'><a class='close_msg'>X</a><div class='pdetail'>"+$.mage.__('The fewest you may purchase is')+" "+minQty+" "+$.mage.__('Units')+"</div></div>").prependTo("#maincontent");
                   setTimeout(function(){
                       $(".msg_product_added").addClass("show");
                   },100);

                   setTimeout(function(){
                       $(".msg_product_added").removeClass("show").addClass("hide");
                   },10000);
                   return;
               }else{
                   ajaxUpdate(e.currentTarget,true);
                   updatePrices(this,currency);
               }
           }
       }
    });
