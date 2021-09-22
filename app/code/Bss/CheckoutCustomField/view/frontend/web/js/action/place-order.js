/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CheckoutCustomField
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'Magento_Checkout/js/model/quote',
    'mage/utils/wrapper',
    'Bss_CheckoutCustomField/js/model/save-custom-field-information'
], function (quote, wrapper, saveCustomField) {
    'use strict';

    return function (placeOrderAction) {

        /** Override default place order action and add bss_customfield to payment data */
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            var custom_extension_attributes = {};
            if (quote.isVirtual()) {
                jQuery('.checkout-payment-method .control [name*=bss_custom_field]').each(function () {
                    var selfChild = this;
                    custom_extension_attributes = saveCustomField.setNameAttributes(selfChild, custom_extension_attributes);
                });
            } else {
                jQuery('.control [name*=bss_custom_field]').each(function () {
                    var selfChild = this;
                    custom_extension_attributes = saveCustomField.setNameAttributes(selfChild, custom_extension_attributes);
                });
            }
            if (paymentData['extension_attributes'] === undefined) {
                paymentData['extension_attributes'] = {};
            }
            paymentData['extension_attributes']['bss_customfield'] = custom_extension_attributes;

            return originalAction(paymentData, messageContainer);
        });
    };
});
