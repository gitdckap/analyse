define([
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/sidebar'
], function ($, Component, quote, stepNavigator, sidebarModel) {
    'use strict';

    var mixin = {
        defaults: {
            template: 'Dckap_ShippingAdditionalFields/shipping-information'
        },
        isStorePickup: function () {
            return quote.shippingMethod().method_code == "ddistorepickup";
        },
        getEditOrder: function () {
            var editOrder = window.checkoutConfig.is_edit_order;
            if (editOrder) {
                return true;
            }
        },
    };

    return function (target) {
        return target.extend(mixin);
    }
});