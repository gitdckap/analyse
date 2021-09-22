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
 * Gift Card Validator
 *
 * @package Cayan\Payment\Observer
 * @author Igor Miura
 */
define(
    [
        'jquery',
        'Magento_Ui/js/modal/alert',
        'Magento_Checkout/js/action/get-totals',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/model/totals',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/quote',
        'jquery/ui',
        'mage/translate'
    ],
    function ($, alert, getTotalsAction, fullScreenLoader, getPaymentInformationAction, totals, priceUtils, quote) {
        'use strict';
        return {
            /**
             * Validate if applied gift card amount is valid.
             *
             * @returns {boolean}
             */
            validate: function() {
                var giftCardAmount = parseFloat(window.checkoutConfig.payment.cayancard.giftcard_discount);
                if (giftCardAmount > 0) {
                    var checkUrl = window.checkoutConfig.payment.cayancard.giftcard_check_url;
                    var result = false;
                    var availableAmount = 0;
                    var formattedAvailableAmount = 0;
                    $.ajax({
                        url: checkUrl,
                        type: "POST",
                        success: function (response) {
                            result = response.result;
                            availableAmount = response.available;
                            formattedAvailableAmount = priceUtils.formatPrice(availableAmount, quote.getPriceFormat());
                            window.checkoutConfig.payment.cayancard.giftcard_discount = availableAmount;
                            if (!result) {
                                alert({
                                    title: $.mage.__('Gift Card'),
                                    content: '<p>'+$.mage.__("The requested amount is not available.")+'</p>' +
                                    '<p>'+$.mage.__("The current available amount is")+' ' +
                                    '<strong>' + formattedAvailableAmount + '</strong>' +
                                    '</p>',
                                    actions: {
                                        always: function(){
                                            //Reload components
                                            var deferred = $.Deferred();
                                            totals.isLoading(true);
                                            getPaymentInformationAction(deferred);
                                            $.when(deferred).done(function () {
                                                totals.isLoading(false);
                                                //Update totals
                                                var subtotalWithDiscount = totals.totals._latestValue.base_subtotal_with_discount;
                                                var shipping = totals.totals._latestValue.shipping_incl_tax;
                                                var finalAmount = (subtotalWithDiscount + shipping - availableAmount);
                                                $('.totals.sub.giftcard-totals .mark:last-child').text(formattedAvailableAmount);
                                                $('.grand.totals .amount .price').text(priceUtils.formatPrice(finalAmount, quote.getPriceFormat()));
                                            });
                                        }
                                    }
                                });
                            }
                        },
                        error: function (xhr) {
                            alert({
                                title: $.mage.__('Gift Card'),
                                content: $.mage.__('An error occurred while validating the gift card, please try again.')
                            });
                        },
                        async: false
                    });
                    return (result === 1);
                }
                return true;
            }
        }
    }
);