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
var config = {
    config: {
        mixins: {
            "Magento_Ui/js/lib/validation/rules" : {
                "Bss_CheckoutCustomField/js/lib/validation/rules": true
            },
            "Magento_Checkout/js/view/shipping" : {
                "Bss_CheckoutCustomField/js/view/shipping": true
            },
            "Magento_Paypal/js/order-review" : {
                "Bss_CheckoutCustomField/js/view/order-review": true
            },
            "Magento_Checkout/js/action/place-order" : {
                "Bss_CheckoutCustomField/js/action/place-order": true
            },
            "Magento_Checkout/js/action/set-payment-information" : {
                "Bss_CheckoutCustomField/js/action/set-payment-information": true
            },
        }
    }
};
