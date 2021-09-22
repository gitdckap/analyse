/**
 * Cayan Payments
 *
 * @package Cayan\Payment
 * @author Igor Miura
 * @author Joseph Leedy
 * @copyright Copyright (c) 2017 Cayan (https://cayan.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals'
], function (Component, quote, priceUtils, totals) {
    'use strict';
    var mixin = {
        getValue: function () {
            var price = 0;
            if (this.totals()) {
                price = (totals.getSegment('grand_total') != null) ? totals.getSegment('grand_total').value : totals.totals._latestValue.grand_total;
            }
            var baseSubtotal = totals.totals._latestValue.base_subtotal_with_discount;
            var shippingTotal = totals.totals._latestValue.base_shipping_incl_tax;
            if(parseFloat(window.checkoutConfig.payment.giftcard_total) === parseFloat(window.checkoutConfig.payment.cayancard.giftcard_discount))
                var giftDiscount = window.checkoutConfig.payment.cayancard.giftcard_discount;
            else
                var giftDiscount = window.checkoutConfig.payment.cayancard.giftcard_total;
            var total1 = parseFloat(baseSubtotal) + parseFloat(shippingTotal) - parseFloat(giftDiscount);
            var total2 = totals.totals._latestValue.base_grand_total;
            total1 = parseFloat(total1).toFixed(2);
            total2 = parseFloat(total2).toFixed(2);
            if (!isNaN(total1) && !isNaN(total2) && total1 !== total2) {
                price = total1;
            }
            if(price < 0)
                price = 0;
            return this.getFormattedPrice(price);
        }
    };
    return function (target) {
        return target.extend(mixin);
    };
});