/**
 * Cayan Payments
 *
 * @package Cayan\Payment
 * @author Igor Miura
 * @author Joseph Leedy
 * @copyright Copyright (c) 2017 Cayan (https://cayan.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */
define(
    [
        'Magento_Payment/js/view/payment/cc-form',
        'jquery',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'Magento_Vault/js/view/payment/vault-enabler',
        'Magento_Checkout/js/model/payment/additional-validators',
        'cayanCheckoutPlus'
    ],
    function (Component, $, validator, VaultEnabler, validators) {
        'use strict';
        var PaymentComponent = undefined;
        var cardNumberBeforeSubmit = "";
        var ccvNumberBeforeSubmit = "";
        return Component.extend({
            initialize: function () {
                this._super();
                this.vaultEnabler = new VaultEnabler();
                this.vaultEnabler.setPaymentCode(this.getVaultCode());
                return this;
            },
            defaults: {
                template: 'Cayan_Payment/credit/form',
                creditCardType: '',
                creditCardExpYear: '',
                creditCardExpMonth: '',
                creditCardNumber: '',
                creditCardVerificationNumber: '',
                creditCardHolder: '',
                paymentMethodNonce: null
            },
            getData: function () {
                var data = this._super();

                data = {
                    'method': this.item.method,
                    'additional_data': {
                        'payment_method_nonce': this.paymentMethodNonce,
                        'cc_holder_name': this.creditCardHolder
                    }
                };

                if (this.isVaultEnabled()) {
                    data['additional_data']['cc_number'] = this.creditCardNumber();
                    data['additional_data']['cc_exp_year'] = (this.creditCardExpYear() !== undefined) ? this.creditCardExpYear().slice(-2) : this.creditCardExpYear();
                    data['additional_data']['cc_exp_month'] = this.adjustMonth(this.creditCardExpMonth());
                }

                this.vaultEnabler.visitAdditionalData(data);
                return data;
            },
            successCallback: function (tokenResponse) {
                console.log('validou');
                $('#cayancc_cc_number').val(cardNumberBeforeSubmit);
                $('#cayancc_cc_cid').val(ccvNumberBeforeSubmit);
                if (window.checkoutConfig.payment.cayancc.debug)
                    console.log(tokenResponse);
                PaymentComponent.paymentMethodNonce = tokenResponse.token;
                if (window.checkoutConfig.payment.cayancc.debug)
                    console.log(PaymentComponent.getData());
                $('button.action.primary.checkout').removeAttr("disabled");
                PaymentComponent.placeOrder();
            },
            failureCallback: function (tokenResponse) {
                if (window.checkoutConfig.payment.cayancc.debug) {
                    console.log("Token Failed");
                    console.log(tokenResponse);
                }
                $('button.action.primary.checkout').removeAttr("disabled");
                alert("Payment declined, please check your card information and try again.");
            },
            adjustMonth: function (month) {
                if (month < 10)
                    return "0" + month;
                else
                    return month;
            },
            getCode: function () {
                return "cayancc";
            },
            getCcMonthsValues: function () {
                return _.map(window.checkoutConfig.payment.cayancc.ccmonths, function (value, key) {
                    return {
                        'value': value.value,
                        'month': value.month
                    };
                });
            },
            getCcYearsValues: function () {
                return _.map(window.checkoutConfig.payment.cayancc.ccyears, function (value, key) {
                    return {
                        'value': value.value,
                        'year': value.year
                    };
                });
            },
            getCvvImageUrl: function () {
                return window.checkoutConfig.payment.cayancc.ccv_image;
            },
            isActive: function () {
                return true;
            },
            validate: function () {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },
            createCayanToken: function () {
                if (this.validate() && validators.validate()) {
                    cardNumberBeforeSubmit = $('#cayancc_cc_number').val();
                    ccvNumberBeforeSubmit = $('#cayancc_cc_cid').val();
                    // Prevent the user from double-clicking
                    $('button.action.primary.checkout').prop('disabled', true);
                    // Create the payment token
                    CayanCheckoutPlus.setWebApiKey(window.checkoutConfig.payment.cayancc.webApiKey);
                    PaymentComponent = this;
                    CayanCheckoutPlus.createPaymentToken({
                        success: this.successCallback,
                        error: this.failureCallback
                    });
                }
            },
            isVaultEnabled: function () {
                return this.vaultEnabler.isVaultEnabled();
            },
            getVaultCode: function () {
                return "cayancc_vault";
            }
        });
    }
);