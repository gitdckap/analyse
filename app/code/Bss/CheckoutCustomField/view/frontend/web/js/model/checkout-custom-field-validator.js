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
define(
    [
        'jquery',
        'uiRegistry',
        'Bss_CheckoutCustomField/js/model/save-custom-field-information',
        'mage/validation'
    ],
    function ($, registry) {
        'use strict';

        return {

            /**
             * Validate checkout agreements
             *
             * @returns {Boolean}
             */
            validate: function () {
                var source = registry.get('checkoutProvider'),
                    validateshipping = true,
                    validatePayment = true;
                source.set('params.invalid', false);
                if (typeof source.get('shippingAddressLogin') !== "undefined" && $('[name^=shippingAddressLogin]').length > 0) {
                    source.trigger('shippingAddressLogin.data.validate');
                    if(source.get('params.invalid')) {
                        source.set('params.invalid', false);
                        validateshipping = false;
                    }
                }
                if (typeof source.get('paymentAftermethods') !== "undefined" && $('[name^=paymentAftermethods]').length > 0) {
                    source.trigger('paymentAftermethods.data.validate');
                    if(source.get('params.invalid')) {
                        source.set('params.invalid', false);
                        validatePayment = false;
                    }
                }
                if (validateshipping == true && validatePayment == true) {
                    return true;
                }
                return false;
            }
        };
    }
);