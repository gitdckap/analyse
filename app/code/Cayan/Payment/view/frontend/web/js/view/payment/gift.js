/**
 * Cayan Payments
 *
 * @package Cayan\Payment
 * @author Igor Miura
 * @author Joseph Leedy
 * @copyright Copyright (c) 2017 Cayan (https://cayan.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

/**
 * Gift card checkout JS
 *
 * @package Cayan\Payment\view
 * @author Igor Miura
 */
define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Ui/js/modal/alert',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Catalog/js/price-utils',
    'jquery/ui',
    'mage/translate'
], function ($, ko, Component, quote, alert, getPaymentInformationAction, totals, fullScreenLoader, priceUtils) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Cayan_Payment/payment/gift'
        },

        giftNumber: "",
        pinNumber: "",

        /**
         * Coupon code application procedure
         */
        applyGiftCard: function () {
            if (this.validate()) {
                fullScreenLoader.startLoader();
                var formUrl = window.checkoutConfig.payment.cayancard.giftcard_add_url;
                $.ajax({
                    url: formUrl,
                    data: {isAjax: true, giftcode: this.giftNumber, pin: this.pinNumber},
                    type: "POST",
                    success: function (response) {
                        var deferred;
                        if (parseInt(response.status) === 1) {
                            deferred = $.Deferred();
                            totals.isLoading(true);
                            getPaymentInformationAction(deferred);
                            $.when(deferred).done(function () {
                                fullScreenLoader.stopLoader();
                                totals.isLoading(false);
                                alert({
                                    title: $.mage.__('Gift Card'),
                                    content: $.mage.__('Gift card applied successfully.')
                                });
                                var availableAmount = response.applied_discount;
                                window.checkoutConfig.payment.cayancard.giftcard_discount = availableAmount;
                                var subtotalWithDiscount = totals.totals._latestValue.base_subtotal_with_discount;
                                var shipping = totals.totals._latestValue.shipping_incl_tax;
                                var finalAmount = (subtotalWithDiscount + shipping - availableAmount);
                                if ($('.giftcard-totals .mark').length) {
                                    $('.totals.sub.giftcard-totals .mark:last-child').text(priceUtils.formatPrice(availableAmount, quote.getPriceFormat()));
                                } else {
                                    var giftCardTitle = $.mage.__("Gift Card:");
                                    var giftCardDiscount = priceUtils.formatPrice(availableAmount, quote.getPriceFormat());
                                    var giftDiscountElement = "<tr class=\"totals sub giftcard-totals\" data-bind=\"if: isVisible()\">\n" +
                                        "    <th class=\"mark title\">" + giftCardTitle + "</th>\n" +
                                        "    <th class=\"mark\" data-bind=\"text: getGiftTotal()\" " +
                                        "style=\"text-align: right;\">" + giftCardDiscount + "</th>\n" +
                                        "</tr>";
                                    $('.totals.sub.giftcard-totals').replaceWith(giftDiscountElement);
                                }
                                $('.grand.totals .amount .price').text(priceUtils.formatPrice(finalAmount, quote.getPriceFormat()));
                            });
                        } else {
                            fullScreenLoader.stopLoader();
                            totals.isLoading(false);
                            alert({
                                title: $.mage.__('Gift Card'),
                                content: $.mage.__('Invalid gift card, please try again.')
                            });
                        }
                    },
                    error: function (xhr) {
                        fullScreenLoader.stopLoader();
                        totals.isLoading(false);
                        alert({
                            title: $.mage.__('Gift Card'),
                            content: $.mage.__('An error occurred while trying to add the gift card, please try again.')
                        });
                    },
                    async: true
                });
            }
        },

        isPINEnabled: function () {
            return (parseInt(window.checkoutConfig.payment.cayancard.pin_enabled) === 1);
        },

        getInputMaxLength: function () {
            return parseInt(window.checkoutConfig.payment.cayancard.giftcard_max_length);
        },

        validatePIN: function(data, event){
            return event.charCode >= 48 && event.charCode <= 57;
        },

        /**
         * Coupon form validation
         *
         * @returns {Boolean}
         */
        validate: function () {
            var form = '.form-cayancard';
            return $(form).validation() && $(form).validation('isValid');
        }
    });
});
