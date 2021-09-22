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
    "jquery",
    "mage/calendar"
], function ($) {
    "use strict";

    return function (config, element) {
        var currentYear = (new Date).getFullYear();
        if(config.showsTime) {
            $(element).calendar({
                dateFormat:'mm/dd/yy',
                changeYear: true,
                changeMonth: true,
                minDate: new Date(),
                yearRange: (currentYear-100) + ":" + (currentYear + 100),
                showsTime:{
                    showTime: config.showsTime,
                    showHour: config.showsTime,
                    showMinute: config.showsTime
                }
            });
        }else {
            $(element).calendar({
                dateFormat:'mm/dd/yy',
                changeYear: true,
                changeMonth: true,
                minDate: new Date(),
                yearRange: (currentYear-100) + ":" + (currentYear + 100)
            });
        }
        
    }
});
