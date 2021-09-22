define([
    'jquery',
    "mage/url",
], function ($, url) {
    'use strict';
    $.widget('dckap.addToCartPopup', {
        options: {
            bindSubmit: true
        },
        _create: function () {
            var self = this;
            if (this.options.bindSubmit) {
                this._bindSubmit();
            }
        },
        _bindSubmit: function () {
            //Trigger Go to cart and Add to cart popup
            $(".place-order").click(function (e) {
                e.preventDefault();
                var id = $(this).attr('id'); // $(this) refers to button that was clicked
                $('.ddiOrderId').val(id);

                var popupoptions = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    modalClass: 'add-to-cart-popup',
                    title: '',
                    buttons: false,
                    fixedPopup: true
                };
                var popup = $('#add-to-cart-popup').modal(popupoptions);
                popup.modal("openModal");

            });

            //Redirect to cart page
            $(".go-to-cart").click(function () {
                window.location = url.build('checkout/cart');
            });

            //Ajax add to cart
            $(".add-to-cart").click(function () {
                var orderNumber = $('.ddiOrderId').val();
                if (orderNumber != '') {
                    $('body').trigger('processStart');
                    var postUrl = url.build('quickrfq/quote/order/id/' + orderNumber);
                    $.ajax({
                        method: 'post',
                        url: postUrl,
                        success: function (response) {
                            $('body').trigger('processStop');
                            console.log(response);
                             if( response === '' ){
                                 var link =  url.build('customer/account/login');
                                 window.location.assign( link );
                                 jQuery(".modal-header >  .action-close").click();
                                 return false;
                            }
                            window.location = url.build(response.backurl);
                        }
                    });
                }

            });
        },
    });
    return $.dckap.addToCartPopup;
});