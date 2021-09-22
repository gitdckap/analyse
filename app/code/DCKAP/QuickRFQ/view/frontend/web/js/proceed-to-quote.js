/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Customer/js/model/authentication-popup',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/quote',
    'mage/url'
], function ($, authenticationPopup, customerData, quote, urlBuilder) {
    'use strict';

    return function (config, element) {
        $(element).click(function (event) {
            var cart = customerData.get('cart'),
                customer = customerData.get('customer');

            event.preventDefault();

            if (!customer().firstname && cart().isGuestCheckoutAllowed === false) {
                authenticationPopup.showModal();

                return false;
            }
            $(element).attr('disabled', true);
            location.href = config.quoteUrl + '?type=quote';
            /*console.log('cart items data');
            console.log(window.customerData);
            console.log(quote.getItems());
            var currentCustomerData = JSON.stringify(window.customerData);
            var quoteData = JSON.stringify(quote.getItems());
            // console.log(currentCustomerData);
            // console.log(quoteData);
            var serviceurl = urlBuilder.build('dckapcheckout/ajax/index');
            $.ajax({
                url: serviceurl,
                type: 'POST',
                data: 'quoteData='+quoteData+'&customerData='+currentCustomerData,
                dataType: 'JSON',
                success: function (res) {
                    console.log(res);
                    console.log(JSON.parse(res.data.customerData));
                }
            });*/
        });

    };
});
