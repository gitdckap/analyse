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
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/url',
        'Magento_Checkout/js/model/quote'
    ],
    function (
        $,
        ko,
        Component
    ) {
        return Component.extend({
            defaults: {
                template: 'Bss_CheckoutCustomField/checkout-custom-field'
            },
            
            initialize: function () {
                this._super();
            },
        });
    }
);
