/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/quote'
    ],
    function ($, ko, Component, payment, customerData, quote) {
        'use strict';

        var configValues = window.checkoutConfig;

        return Component.extend({
            defaults: {
                template: 'DCKAP_CheckoutCustomisation/checkout/payment/additional-data'
            },

            getCustomerPaymentTerms: function(){
                return configValues.paymentterms;
            },

            getCustomerPaymentType: function(){
                return configValues.paymentterms.includes('Net');
            }
            
        });
    }
);