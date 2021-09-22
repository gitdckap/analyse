define([
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals'

], function (ko, Component, quote, priceUtils, totals) {
    'use strict';
    var show_hide_adult_signature_fee_block = window.checkoutConfig.show_hide_adult_signature_fee_block;
    var adult_signature_fee_label = window.checkoutConfig.adult_signature_fee_label;
    var adult_signature_fee_amount = window.checkoutConfig.adult_signature_fee_amount;

    return Component.extend({

        totals: quote.getTotals(),

        canVisibleExtrafeeBlock: show_hide_adult_signature_fee_block,

        // getFormattedPrice: ko.observable(priceUtils.formatPrice(adult_signature_fee_amount, quote.getPriceFormat())),
        getFormattedPrice: function () {
            return priceUtils.formatPrice(this.getValue(), quote.getPriceFormat())
        },

        getFeeLabel:ko.observable(adult_signature_fee_label),

        isDisplayed: function () {
            return this.getValue() != 0;
        },

        getValue: function() {
            var price = 0;
            if (this.totals() && totals.getSegment('adult_signature_fee')) {
                price = totals.getSegment('adult_signature_fee').value;
            }
            return price;
        }
    });
});
