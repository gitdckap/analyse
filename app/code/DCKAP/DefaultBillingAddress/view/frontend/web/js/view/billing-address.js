/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama
 */
define([
    'ko',
    'Magento_Checkout/js/view/billing-address',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/address-list',
    'mage/translate'
], function (ko, Component, customerData, quote, addressList, $t) {
    'use strict';
    var lastSelectedBillingAddress = null,
        newAddressOption = {
            /**
             * Get new address label
             * @returns {String}
             */
            getAddressInline: function () {
                return $t('New Address');
            },
            customerAddressId: null
        },
        countryData = customerData.get('directory-data'),
        addressOptions = addressList().filter(function (address) {
            return address.getType() == 'customer-address';
        });



    addressOptions.push(newAddressOption);

    return Component.extend({
        initialize: function () {
            this._super();
            this.setDefaultBillingAddress();
        },

        setDefaultBillingAddress: function () {
            var self = this;
            quote.billingAddress.subscribe(function () {
                if (quote.shippingAddress()) {
                    var subscriber = this;
                    addressList().filter( function (address) {
                        if (address.hasOwnProperty('isDefaultBilling')
                            && typeof address.isDefaultBilling() !== 'undefined'
                            && address.isDefaultBilling()
                        ) {

                            if (address.customerAddressId != quote.shippingAddress().customerAddressId) {
                                // The subscriber must be disposed to prevent an infinite recursive loop
                                // before the billing address is updated.
                                subscriber.dispose();
                                self.selectedAddress(address);
                                self.updateAddress();
                            }
                        }
                    });
                }
            });
        }
    });
});