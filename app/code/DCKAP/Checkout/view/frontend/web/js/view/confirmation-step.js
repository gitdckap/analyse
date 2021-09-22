define(
    [
        'ko',
        'uiComponent',
        'underscore',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Customer/js/model/customer',
        'jquery',
        'Magento_Ui/js/modal/modal',
        'mage/url',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/checkout-data'
    ],
    function (
        ko,
        Component,
        _,
        stepNavigator,
        customer,
        $,
        modal,
        urlBuilder,
        quote,
        checkoutData
    ) {
        'use strict';
        /**
         * check-login - is the name of the component's .html template
         */
        return Component.extend({
            defaults: {
                template: 'Dckap_Checkout/confirmation-step'
            },

            //add here your logic to display step,
            isVisible: ko.observable(true),
            isLogedIn: customer.isLoggedIn(),
            //step code will be used as step content id in the component template
            stepCode: 'confirmation-step',
            //step title value
            stepTitle: 'Review',

            /**
             *
             * @returns {*}
             */
            initialize: function () {
                this._super();
                // register your step
                stepNavigator.registerStep(
                    this.stepCode,
                    //step alias
                    null,
                    this.stepTitle,
                    //observable property with logic when display step or hide step
                    this.isVisible,

                    _.bind(this.navigate, this),

                    /**
                     * sort order value
                     * 'sort order value' < 10: step displays before shipping step;
                     * 10 < 'sort order value' < 20 : step displays between shipping and payment step
                     * 'sort order value' > 20 : step displays after payment step
                     */
                    15
                );


                return this;
            },

            /**
             * The navigate() method is responsible for navigation between checkout step
             * during checkout. You can add custom logic, for example some conditions
             * for switching to your custom step
             */
            navigate: function () {

            },

            compareKeyValuePair: function(pair, param) {
                var key_value = pair.split('=');
                var decodedKey = decodeURIComponent(key_value[0]);
                var decodedValue = decodeURIComponent(key_value[1]);
                if(decodedKey == param) return decodedValue;
                return null;
            },

            getUrlParams: function(param) {
                var self = this;
                var search = window.location.search.substring(1);
                var quoteType = null;
                if(search.indexOf('&') > -1) {
                    var params = search.split('&');
                    for(var i = 0; i < params.length; i++) {
                        quoteType = self.compareKeyValuePair(params[i], param);
                        if(quoteType !== null) {
                            break;
                        }
                    }
                } else {
                    quoteType = self.compareKeyValuePair(search, param);
                }
                return quoteType;
            },

            /**
             * @returns void
             */
            navigateToNextStep: function () {
                var self = this;
                var quoteType = self.getUrlParams('type');

                /*console.log('checkout data');
                console.log(quote);
                console.log('shipping address');
                console.log(quote.shippingAddress());
                console.log('shipping method');
                console.log(quote.shippingMethod());
                console.log('quote items');
                console.log(quote.getItems());
                console.log('quote id');
                console.log(quote.getQuoteId());*/
                // console.log(checkoutData);
                // console.log(checkoutData.getSelectedShippingAddress());
                var shipping_address = JSON.stringify(quote.shippingAddress());
                var shipping_method = JSON.stringify(quote.shippingMethod());
                var quote_items = JSON.stringify(quote.getItems());
                var quote_id = quote.getQuoteId();
                var po_number = $('input[name="bss_custom_field[purchase_order_number]"]').val();
                var exp_delivery_date = $('input[name="bss_custom_field[expected_delivery_date]"]').val();
                var special_ins = $('textarea[name="bss_custom_field[special_instructions]"]').val();
                var storepickup_email = $('input[name="ddi_store_pickup[ddi_delivery_contact_email]"]').val();
                var storepickup_no = $('input[name="ddi_store_pickup[ddi_delivery_contact_no]"]').val();
                var storepickup_warehouse = $('select[name="ddi_store_pickup[ddi_pref_warehouse]"]').val();
                var storepickup_date = $('input[name="ddi_store_pickup[ddi_pickup_date]"]').val();

                /* check whether quote request or normal order */
                if (quoteType == 'quote') {
                    var serviceurl = urlBuilder.build('dckapcheckout/ajax/index');
                    $.ajax({
                        url: serviceurl,
                        type: 'POST',
                        data: "shipping_address="+shipping_address+"&shipping_method="+shipping_method+"&quote_items="+quote_items+"&quote_id="+quote_id+"&review_type=quote&po_number="+po_number,
                        dataType: 'JSON',
                        showLoader: true,
                        success: function (data) {
                            /* after getting confirmation from ERP quote request saved in Magento as order with Quote Request flag */
                            var serviceurl1 = urlBuilder.build('quickrfq/quote/submit');
                            $.ajax({
                                url: serviceurl1,
                                type: 'POST',
                                data: "shipping_address="+shipping_address+"&shipping_method="+shipping_method+"&quote_items="+quote_items+"&quote_id="+quote_id+"&po_number="+po_number+"&exp_delivery_date="+exp_delivery_date+"&special_ins="+special_ins+"&storepickup_email="+storepickup_email+"&storepickup_no="+storepickup_no+"&storepickup_warehouse="+storepickup_warehouse+"&storepickup_date="+storepickup_date,
                                dataType: 'JSON',
                                showLoader: true,
                                success: function (data) {
                                    /* redirect to quote request success page */
                                    if(data.ErpMessage){
                                        alert(data.ErpMessage);
                                    }else {
                                        var quoteRequestPopup = {
                                            type: 'popup',
                                            responsive: true,
                                            innerScroll: true,
                                            buttons: false,
                                            title: "Thanks for Requesting a Quote",
                                            modalClass: "popup-center",
                                            clickableOverlay: true,
                                            heightStyle: "content",
                                            content: ""
                                        };
                                        modal(quoteRequestPopup, $('.price-request-response'));
                                        $('.price-request-response').trigger('openModal').on('modalclosed', function () {
                                            window.location.href = urlBuilder.build('');
                                        });
                                    }


                                    //window.location.href = urlBuilder.build('quickrfq/quote/success');
                                }
                            });
                        }
                    });
                } else {
                    /* For normal checkout process */
                    /*var serviceurl = urlBuilder.build('dckapcheckout/ajax/index');
                    $.ajax({
                        url: serviceurl,
                        type: 'POST',
                        data: "shipping_address="+shipping_address+"&shipping_method="+shipping_method+"&quote_items="+quote_items+"&quote_id="+quote_id+"&review_type=checkout_review&po_number="+po_number,
                        dataType: 'JSON',
                        showLoader: true,
                        success: function (data) {
                            console.log('navigate');
                            console.log(data);
                            /!* redirect to quote request success page *!/
                            if ((data.response === 0 || data.response === '0') || data.response.data.isValid === 'no') {
                                $('.order-review-response').html('<p>'+data.response.data.errorMessage+'</p>');
                                var reviewErrorPopup = {
                                    type: 'popup',
                                    responsive: true,
                                    innerScroll: true,
                                    buttons: false,
                                    title: "We have a small problem after reviewing this order:",
                                    modalClass: "popup-center",
                                    clickableOverlay: true,
                                    heightStyle: "content",
                                    content: ""
                                };
                                modal(reviewErrorPopup, $('.order-review-response'));
                                $('.order-review-response').trigger('openModal').on('modalclosed', function () {
                                    window.location.href = urlBuilder.build('checkout/cart/');
                                });
                            } else {
                                /!* logic to add tax price in order/payment process *!/
                                window.checkoutConfig.checkoutReviewData = data.response.data;
                                var serviceurl1 = urlBuilder.build('dckapcheckout/index/transportkey');
                                var quote_id = quote.getQuoteId();
                                var taxAmount = window.checkoutConfig.checkoutReviewData.orderDetails.taxTotal;
                                var taxHtml = '<tr class="totals shipping excl"><th class="mark" scope="row"><span class="label">Tax</span></th><td class="amount"><span class="price" data-th="Shipping">'+taxAmount+'</span></td></tr>';
                                $('.opc-block-summary .table-totals .grand.totals').before(taxHtml);

                                taxAmount = taxAmount.replace('$', '');
                                var amount = window.checkoutConfig.checkoutReviewData.orderDetails.orderTotal;
                                $('.opc-block-summary .table-totals .grand.totals .amount .price').text(amount);
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
                                // stepNavigator.next();
                                $('form.before-review').hide();
                                $('form.after-review').show();
                                self.navigateToPayment();
                            }
                        }
                    });*/
                    self.navigateToPayment();
                }
            },

            navigateToPayment: function () {
                var strOrderAmount = window.checkoutConfig.checkoutReviewData.orderDetails.orderTotal;
                var intTotalAmount = strOrderAmount.replace(/[\$£€]/g, '');
                var serviceurl = urlBuilder.build('dckapcheckout/ajax/user');
                $.ajax({
                    url: serviceurl + "?totalAmount="+ intTotalAmount,
                    type: 'GET',
                    data: "",
                    dataType: 'JSON',
                    showLoader: true,
                    success: function (data) {
                        if (data.allowOnAccount == 'no' || data.allowOnAccount == '' || data.is_b2b == '0') {
                            $('input#cashondelivery').parents('.payment-method').remove();
                        }
                        $('.payment-methods .payment-method').each(function(e) {
                            if($(this).find('input#cashondelivery').length) {
                                if (data.order_approval) {
                                    strbtnTitle = "Submit Order";
                                } else {
                                    var strbtnTitle = "Request Order Approval";
                                }
                                $(this).find('.payment-method-content .actions-toolbar .action.primary.checkout span').text(strbtnTitle);
                                $(this).find('.payment-method-content .actions-toolbar .action.primary.checkout').prop('title',strbtnTitle);
                            }
                        });

                        var priceformat = window.checkoutConfig.basePriceFormat.pattern;
                        if(priceformat.includes("%s")) {
                            priceformat = priceformat.replace("%s", "");
                        }

                       /* var subtotal = window.checkoutConfig.checkoutReviewData.orderDetails.merchandiseTotal;
                        var msubtotal = window.checkoutConfig.totalsData.subtotal;
                        if(subtotal.includes("$")) {
                            subtotal = subtotal.replace("$", "");
                        }
                        subtotal = parseFloat(subtotal);*/
                        $('.total-change-info').remove();

                        var shipto = getCookie("ship-to");
                        if(shipto =="changed"){
                            $('.opc-block-summary .title').append("<span class='total-change-info' >The price for item(s) in your cart has changed based on shipping destination.</span>");
                        }

                        function getCookie(cookieName) {
                            var name = cookieName + "=";
                            var allCookieArray = document.cookie.split(';');
                            for (var i = 0; i < allCookieArray.length; i++) {
                                var temp = allCookieArray[i].trim();
                                if (temp.indexOf(name) == 0)
                                    return temp.substring(name.length, temp.length);
                            }
                            return "";
                        }

                        /*subtotal = priceformat+subtotal;*/
                        /*$('.opc-block-summary .table-totals .totals.sub .amount .price').text(subtotal);*/
                        var taxAmount = window.checkoutConfig.checkoutReviewData.orderDetails.taxTotal;
                        if(taxAmount.includes("$")) {
                            taxAmount = taxAmount.replace("$", "");
                        }
                        taxAmount = priceformat+taxAmount;
                        var taxHtml = '<tr class="totals shipping excl"><th class="mark" scope="row"><span class="label">Tax</span></th><td class="amount"><span class="price" data-th="Shipping">'+taxAmount+'</span></td></tr>';
                        $('.opc-block-summary .table-totals .grand.totals').before(taxHtml);
                        var amount = window.checkoutConfig.checkoutReviewData.orderDetails.orderTotal;
                        if(amount.includes("$")) {
                            amount = amount.replace("$", "");
                        }
                        amount = priceformat+amount;
                        $('.opc-block-summary .table-totals .grand.totals .amount .price').text(amount);
                    },
                    failure: function (err) {
                        var taxAmount = window.checkoutConfig.checkoutReviewData.orderDetails.taxTotal;
                        if(taxAmount.includes("$")) {
                            taxAmount = taxAmount.replace("$", "");
                        }
                        taxAmount = priceformat+taxAmount;
                        var taxHtml = '<tr class="totals shipping excl"><th class="mark" scope="row"><span class="label">Tax</span></th><td class="amount"><span class="price" data-th="Shipping">'+taxAmount+'</span></td></tr>';
                        $('.opc-block-summary .table-totals .grand.totals').before(taxHtml);
                        var amount = window.checkoutConfig.checkoutReviewData.orderDetails.orderTotal;
                        if(amount.includes("$")) {
                            amount = amount.replace("$", "");
                        }
                        amount = priceformat+amount;
                        $('.opc-block-summary .table-totals .grand.totals .amount .price').text(amount);
                    }
                });
                if (checkoutData.getSelectedPaymentMethod() === 'elementpayment') {
                    $('#elementpayment-method').find('.payment-method-content').css('display', 'block');
                }
                stepNavigator.next();
            },

            isQuoteRequest: function () {
                var self = this;
                var quoteType = self.getUrlParams('type');
                if (quoteType == 'quote') {
                    return true;
                } else {
                    return false;
                }
            },

            getPriceRequestUrl: function () {
                return urlBuilder.build('quickrfq/quote/index');
            },

            getDashboardUrl: function () {
                return urlBuilder.build('customer/account');
            },

            getHomeUrl: function () {
                return urlBuilder.build('');
            }
        });
    }
);
