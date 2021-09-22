/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'uiComponent',
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'mage/translate'
], function (
    $,
    _,
    Component,
    ko,
    quote,
    stepNavigator,
    paymentService,
    methodConverter,
    getPaymentInformation,
    checkoutDataResolver,
    $t
) {
    'use strict';

    /** Set payment methods to collection */
    paymentService.setPaymentMethods(methodConverter(window.checkoutConfig.paymentMethods));

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/payment',
            activeMethod: ''
        },
        isVisible: ko.observable(quote.isVirtual()),
        quoteIsVirtual: quote.isVirtual(),
        isPaymentMethodsAvailable: ko.computed(function () {
            return paymentService.getAvailablePaymentMethods().length > 0;
        }),

        /** @inheritdoc */
        initialize: function () {
            this._super();
            checkoutDataResolver.resolvePaymentMethod();

            var customVisibility = true;
            var quoteType = this.getUrlParams('type');
            if (quoteType == 'quote') {
                customVisibility = false;
            }

            if (customVisibility) {
                stepNavigator.registerStep(
                    'payment',
                    null,
                    $t('Payment'),
                    this.isVisible,
                    _.bind(this.navigate, this),
                    20
                );
            }
            return this;
        },

        /**
         * Navigate method.
         */
        navigate: function () {
            var self = this;

            getPaymentInformation().done(function () {
                self.isVisible(true);
            });
        },

        /**
         * @return {*}
         */
        getFormKey: function () {
            return window.checkoutConfig.formKey;
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
        }
    });
});
