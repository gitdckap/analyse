/**
 * Cayan Payments
 *
 * @package Cayan\Payment
 * @author Igor Miura
 * @author Joseph Leedy
 * @copyright Copyright (c) 2017 Cayan (https://cayan.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

define(
    [
        'jquery',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function ($, Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Cayan_Payment/checkout/totals/gift_card_discount'
            },
            getGiftValue: function() {
                return parseFloat(window.checkoutConfig.payment.cayancard.giftcard_discount);
            },
            isVisible: function () {
                if(this.getGiftValue() > 0)
                    return true;
                else
                    return false;
            },
            getGiftTotal : function () {
                if(parseFloat(window.checkoutConfig.payment.giftcard_total) === parseFloat(window.checkoutConfig.payment.cayancard.giftcard_discount))
                    var giftDiscount = window.checkoutConfig.payment.cayancard.giftcard_discount;
                else
                    var giftDiscount = window.checkoutConfig.payment.cayancard.giftcard_total;
                var baseSubtotal = totals.totals._latestValue.base_subtotal_with_discount;
                var shippingTotal = totals.totals._latestValue.base_shipping_incl_tax;
                var total = parseFloat(baseSubtotal) + parseFloat(shippingTotal);
                if(giftDiscount > total)
                    return this.getFormattedPrice(total);
                else
                    return this.getFormattedPrice(giftDiscount);
            }
        });
    }
);