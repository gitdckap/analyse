
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
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data'
    ],
    function (Component, selectPaymentMethod, checkoutData) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Cayan_Payment/vault/form'
            },

            /**
             * @returns {exports.initObservable}
             */
            initObservable: function () {
                this._super()
                    .observe([]);
                return this;
            },

            /**
             * @returns
             */
            selectPaymentMethod: function () {
                selectPaymentMethod(
                    {
                        method: this.getId()
                    }
                );
                checkoutData.setSelectedPaymentMethod(this.getId());
                return true;
            },

            /**
             * @returns {String}
             */
            getTitle: function () {
                return window.checkoutConfig.vault.cayancc_vault.title;
            },

            /**
             * @returns {String}
             */
            getToken: function () {
                var allVaultData = window.checkoutConfig.payment.vault;
                var currentId = this.getId();
                var publicHash = undefined;
                Object.keys(allVaultData).forEach(function (key) {
                    if(key == currentId){
                        var currentVaultItem = allVaultData[key];
                        publicHash = currentVaultItem.config.publicHash;
                    }
                });
                return publicHash;
            },

            /**
             * @returns {String}
             */
            getId: function () {
                return this.index;
            },

            /**
             * @returns {String}
             */
            getCode: function () {
                return this.code;
            },

            /**
             * Get last 4 digits of card
             * @returns {String}
             */
            getMaskedCard: function () {
                var allVaultData = window.checkoutConfig.payment.vault;
                var currentId = this.getId();
                var maskedCC = undefined;
                Object.keys(allVaultData).forEach(function (key) {
                    if(key == currentId){
                        var currentVaultItem = allVaultData[key];
                        maskedCC = currentVaultItem.config.details.maskedCC;
                    }
                });
                return maskedCC;
            },

            /**
             * Return card flag image.
             * @returns {*}
             */
            getImage: function(){
                var allVaultData = window.checkoutConfig.payment.vault;
                var currentId = this.getId();
                var type = undefined;
                Object.keys(allVaultData).forEach(function (key) {
                    if(key == currentId){
                        var currentVaultItem = allVaultData[key];
                        type = parseInt(currentVaultItem.config.details.type);
                    }
                });
                var flags = window.checkoutConfig.payment.cayancc.flags_image;
                return flags[type];
            },

            /**
             * Get expiration date
             * @returns {String}
             */
            getExpirationDate: function () {
                var allVaultData = window.checkoutConfig.payment.vault;
                var currentId = this.getId();
                var expiration = undefined;
                Object.keys(allVaultData).forEach(function (key) {
                    if(key == currentId){
                        var currentVaultItem = allVaultData[key];
                        expiration = currentVaultItem.config.expiration_date;
                    }
                });
                return expiration;
            },

            /**
             * Get card type
             * @returns {String}
             */
            getCardType: function () {
                var allVaultData = window.checkoutConfig.payment.vault;
                var currentId = this.getId();
                var cardFlag = undefined;
                Object.keys(allVaultData).forEach(function (key) {
                    if(key == currentId){
                        var currentVaultItem = allVaultData[key];
                        var cardType = currentVaultItem.config.details.type;
                        var flags = window.checkoutConfig.payment.cayancc.flags;
                        cardFlag = flags[cardType];
                    }
                });
                return cardFlag;
            },

            /**
             * @param {String} type
             * @returns {Boolean}
             */
            getIcons: function (type) {
                return window.checkoutConfig.payment.ccform.icons.hasOwnProperty(type) ?
                    window.checkoutConfig.payment.ccform.icons[type]
                    : false;
            },

            /**
             *
             * Return credit card holder name.
             *
             * @returns {undefined}
             */
            getHolderName: function(){
                var allVaultData = window.checkoutConfig.payment.vault;
                var currentId = this.getId();
                var publicHash = undefined;
                Object.keys(allVaultData).forEach(function (key) {
                    if(key == currentId){
                        var currentVaultItem = allVaultData[key];
                        publicHash = currentVaultItem.config.details.CardHolder;
                    }
                });
                return publicHash;
            },

            /**
             * @returns {*}
             */
            getData: function () {
                var data = {
                    method: this.getCode()
                };

                data['additional_data'] = {};
                data['additional_data']['public_hash'] = this.getToken();
                data['additional_data']['holder_name'] = this.getHolderName();
                if(window.checkoutConfig.payment.cayancc.debug)
                    console.log(data);
                return data;
            }
        });
    }
);
