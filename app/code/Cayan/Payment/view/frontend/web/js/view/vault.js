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
    'Cayan_Payment/js/view/payment/method-renderer/vault'
], function (VaultComponent) {
    'use strict';
    return VaultComponent.extend({
        defaults: {
            template: 'Cayan_Payment/vault/form'
        }
    });
});