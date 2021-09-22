/**
 * Copyright © 2017 DCKAP. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'mage/url',
        'Magento_Checkout/js/model/quote',
        'Magento_Ui/js/modal/alert',
        'mage/cookies',
    ],
    function (Component,$, urlBuilder, quote, alert) {
        'use strict';


        return Component.extend({
            defaults: {
                template: 'Dckap_Elementpayment/payment/elementpayment',
                purchaseOrderNumber: ''
            },

            initialize: function () {
                this._super();
                /*var serviceurl1 = urlBuilder.build('dckapcheckout/index/transportkey');
                var quote_id = quote.getQuoteId();
                $.ajax({
                    url: serviceurl1,
                    type: 'POST',
                    data: "quote_id="+quote_id,
                    dataType: 'JSON',
                    showLoader: true,
                    success: function (data) {
                        console.log('transport key');
                        console.log(data);
                        var iframeUrl = "https://transport.merchantware.net/v4/transportweb.aspx?transportKey="+data.success.key.TransportKey;
                        var $iframe = $('#iframe_transaction');
                        $iframe.attr('src', iframeUrl);
                        return iframeUrl;
                    }
                });*/
                $(document).on('change', '#elementpayment-method input#elementpayment', function (e) {
                    $('#elementpayment-method .payment-method-content').show();
                });
            },

            initObservable: function () {
                this._super()
                    .observe('purchaseOrderNumber');
                return this;
            },
            additionalData: {},

            /** Returns send check to info */
            getMailingAddress: function() {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },

            getData: function () {
                var data = {
                    'method': this.getCode(),
                    'additional_data': {
                        'status': $('#status').val(),
                        'cc_holder': $('#cc_holder').val(),
                        'cc_number': $('#cc_number').val(),
                        'cc_type': $('#cc_type').val(),
                        'cc_auth_code': $('#cc_auth_code').val(),
                        'cc_token': $('#cc_token').val(),
                        'cc_exp_date': $('#cc_exp_date').val(),
                        'cc_validation_key': $('#cc_validation_key').val(),
                        'cc_amount_approved': $('#cc_amount_approved').val()
                    }
                };
                return data;
            },

            getDataFromIframe: function () {
                try {
                    if ($('#iframe_transaction').contents()) {
                        var transactionDetails = $('#iframe_transaction').contents().find('#cc-transaction-result');
                        if (transactionDetails.length) {
                            var stat = transactionDetails.find('#status').val();
                            if (stat == 'User_Cancelled') {
                                alert({
                                    title: "Transaction Cancelled",
                                    content: "Please try again later",
                                    autoOpen: true,
                                    clickableOverlay: false,
                                    focus: "",
                                    actions: {
                                        always: function(){
                                            console.log("modal closed");
                                            setTimeout(function () {
                                                window.location.href = urlBuilder.build('checkout/cart/');
                                            }, 10000);
                                        }
                                    }
                                });
                                // alert('Transaction Cancelled. Please refresh the page and try again.');
                            } else if (stat == 'APPROVED' || stat == 'DECLINED,DUPLICATE;1110;duplicate transaction') {
                                $('#status').val(transactionDetails.find('#status').val());
                                $('#cc_holder').val(transactionDetails.find('#cc_holder').val());
                                $('#cc_number').val(transactionDetails.find('#cc_number').val());
                                $('#cc_type').val(transactionDetails.find('#cc_type').val());
                                $('#cc_auth_code').val(transactionDetails.find('#cc_auth_code').val());
                                $('#cc_token').val(transactionDetails.find('#cc_token').val());
                                $('#cc_exp_date').val(transactionDetails.find('#cc_exp_date').val());
                                $('#cc_validation_key').val(transactionDetails.find('#cc_validation_key').val());
                                $('#cc_amount_approved').val(transactionDetails.find('#cc_amount_approved').val());
                                $('#elementpayment-method #placeorder').click();
                            } else {
                                alert({
                                    title: stat,
                                    content: "Please try again later",
                                    autoOpen: true,
                                    clickableOverlay: false,
                                    focus: "",
                                    actions: {
                                        always: function(){
                                            console.log("modal closed");
                                            setTimeout(function () {
                                                window.location.href = urlBuilder.build('checkout/cart/');
                                            }, 10000);
                                        }
                                    }
                                });
                            }
                        }
                    } else {
                        alert({
                            title: "Something went wrong",
                            content: "Please try again later",
                            autoOpen: true,
                            clickableOverlay: false,
                            focus: "",
                            actions: {
                                always: function(){
                                    console.log("modal closed");
                                    setTimeout(function () {
                                        window.location.href = urlBuilder.build('checkout/cart/');
                                    }, 10000);
                                }
                            }
                        });
                        // alert('Something went wrong. Please try again later.');
                    }
                } catch (e) {
                    // alert(e.message);
                    alert({
                        title: "Invalid Details",
                        content: "Please complete the payment details before clicking Place Order",
                        autoOpen: true,
                        clickableOverlay: false,
                        focus: "",
                        actions: {
                            always: function(){
                                console.log("modal closed");
                                setTimeout(function () {
                                    window.location.href = urlBuilder.build('checkout/cart/');
                                }, 10000);
                            }
                        }
                    });
                    // alert('Please enter the details and then Place Order.');
                }
            },

            reloadIframe: function () {
                alert({
                    title: "Transaction Failure",
                    content: "Transaction has been cancelled due to insufficient balance. Please pay with a different card.",
                    autoOpen: true,
                    clickableOverlay: false,
                    focus: "",
                    actions: {

                    }
                });
                var serviceurl1 = urlBuilder.build('dckapcheckout/index/transportkey');
                var quote_id = quote.getQuoteId();
                var taxAmount = window.checkoutConfig.checkoutReviewData.orderDetails.taxTotal;
                taxAmount = taxAmount.replace('$', '');
                var amount = window.checkoutConfig.checkoutReviewData.orderDetails.orderTotal;
                amount = amount.replace('$', '');
                $.ajax({
                    url: serviceurl1,
                    type: 'POST',
                    data: "quote_id="+quote_id+"&tax_amount="+taxAmount+"&amount="+amount,
                    dataType: 'JSON',
                    showLoader: true,
                    success: function (data) {
                        console.log('transport key');
                        console.log(data);
                        var iframeUrl = "https://transport.merchantware.net/v4/transportweb.aspx?transportKey="+data.success.key.TransportKey;
                        var $iframe = $('#iframe_transaction');
                        $iframe.attr('src', iframeUrl);
                        return iframeUrl;
                    }
                });
            }
        });
    }
);
