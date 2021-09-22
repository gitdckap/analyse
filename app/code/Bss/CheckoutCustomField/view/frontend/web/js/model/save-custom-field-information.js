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
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/full-screen-loader',
    'mage/url',
    'Magento_Checkout/js/model/quote'
], function () {
    return {
        setNameAttributes : function(selfChild, extension_attributes) {
            var name = jQuery(selfChild).attr("name");
            var name = name.replace('bss_custom_field[', '');
            var name = name.replace(']', '');
            if (jQuery(selfChild).attr("type") == 'radio') {
                if (jQuery(selfChild).prop("checked")) {
                    extension_attributes[name] = jQuery(selfChild).val();
                }
            } else if (jQuery(selfChild).attr("type") == 'checkbox') {
                if (jQuery(selfChild).prop("checked")) {
                    name = name.replace('[]', '');
                    if (typeof extension_attributes[name] === "undefined")
                        extension_attributes[name] = [];
                    extension_attributes[name].push(jQuery(selfChild).val());
                }
            } else {
                extension_attributes[name] = jQuery(selfChild).val();
            }

            return extension_attributes;
        }
    };
});

