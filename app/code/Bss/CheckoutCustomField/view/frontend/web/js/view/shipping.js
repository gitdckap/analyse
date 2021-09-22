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
define(['jquery'],
    function ($) {
        'use strict';

        return function (Component) {
            return Component.extend({
                validateShippingInformation: function () {
                    if (this.source.get('shippingAddressLogin') || $('[name^=shippingAddressLogin]').length > 0) {
                        this.source.set('params.invalid', false);
                        this.source.trigger('shippingAddressLogin.data.validate');
                        if(this.source.get('params.invalid')) return false;
                    }
                    return this._super();
                }
            });
        }
    });
