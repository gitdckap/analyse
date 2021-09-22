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
    'jquery'
    ],
    function ($) {
    'use strict';

    return function (Component) {
        var result = Component;
        result["validate-one-required"] = {
            handler : function (value) {
                return value.length > 0;
            },
            message : $.mage.__('Please select one of the options above.')
        };
        
        return result;
    }
});
