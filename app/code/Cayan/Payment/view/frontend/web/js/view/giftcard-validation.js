/**
 * Cayan Payments
 *
 * @package Cayan\Payment
 * @author Igor Miura
 * @author Joseph Leedy
 * @copyright Copyright (c) 2017 Cayan (https://cayan.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

/**
 * Gift Card Validator
 *
 * @package Cayan\Payment\Observer
 * @author Igor Miura
 *
**/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Cayan_Payment/js/model/validator'
    ],
    function (Component, additionalValidators, giftValidator) {
        'use strict';
        additionalValidators.registerValidator(giftValidator);
        return Component.extend({});
    }
);