/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'ko',
    'mageUtils',
    'uiComponent',
    'uiLayout',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/checkout-data',
    'uiRegistry',
    'mage/translate',
    'Magento_Checkout/js/model/shipping-rate-service'
], function ($, _, ko, utils, Component, layout, addressList, addressConverter,
             customer,
             quote,
             createShippingAddress,
             selectShippingAddress,
             shippingRatesValidator,
             formPopUpState,
             shippingService,
             selectShippingMethodAction,
             rateRegistry,
             setShippingInformationAction,
             stepNavigator,
             modal,
             checkoutDataResolver,
             checkoutData,
             registry,
             $t) {
    'use strict';

    var newShippingAddressOption = {
            getAddressInline: function () {
                return $t('New Address');
            },
            customerAddressId: null
        },

        shippingAddressOptions = addressList().filter(function (address) {
            return address.getType() == 'customer-address';
        });

    shippingAddressOptions.push(newShippingAddressOption);

    var defaultRendererTemplate = {
        parent: '${ $.$data.parentName }',
        name: '${ $.$data.name }',
        component: 'Magento_Checkout/js/view/shipping-address/address-renderer/default'
    };

    return Component.extend({
        defaults: {
            template: 'DCKAP_CheckoutCustomisation/shipping-address/list',
            visible: addressList().length > 0,
            rendererTemplates: []
        },
        isFormPopUpVisible: formPopUpState.isVisible,
        currentShippingAddress: quote.shippingAddress,
        shippingAddressOptions: shippingAddressOptions,
        customerHasShippinhAddresses: shippingAddressOptions.length > 1,

        /** @inheritdoc */
        initialize: function () {
            this._super()
                .initChildren();

            var refreshIntervalId = setInterval(contentOnload, 1000);

            function contentOnload()
            {
             if($(".checkout-index-index .field.addresses").length)
              {
                    $(".checkout-index-index .field.addresses .shipping-address-items .shipping-address-item").hide();
                    $("#custom-address-items").hide();
                    $(".checkout-index-index .field.addresses .shipping-address-items .shipping-address-item").css("pointer-events","none");
                    clearInterval(refreshIntervalId);
                }
            }

             
            addressList.subscribe(function (changes) {
                    var self = this;

                    changes.forEach(function (change) {
                        if (change.status === 'added') {
                            self.createRendererComponent(change.value, change.index);
                        }
                    });
                },
                this,
                'arrayChange'
            );

            return this;
        },

        /**
         * @return {exports.initObservable}
         */
        initObservable: function () {
            this._super()
                .observe({
                    selectedShippingAddress: null,
                    isShippingAddressDetailsVisible: quote.shippingAddress() != null,
                    isShippingAddressFormVisible: !customer.isLoggedIn() || shippingAddressOptions.length === 1,
                    saveInAddressBook: 1
                });

            quote.shippingAddress.subscribe(function (newShippingAddress) {
                if (newShippingAddress != null && newShippingAddress.saveInAddressBook !== undefined) {
                    this.saveInAddressBook(newShippingAddress.saveInAddressBook);
                } else {
                    this.saveInAddressBook(1);
                }
                this.isShippingAddressDetailsVisible(true);
            }, this);

            return this;
        },

        /**
         * @param {Object} address
         * @return {*}
         */
        shippingaddressOptionsText: function (address) {
            return address.getAddressInline();
        },

        /**
         * @param {Object} address
         */
        onShippingAddressChange: function (address) {
            
            this.isShippingAddressFormVisible(address == newShippingAddressOption); //eslint-disable-line eqeqeq
            if(address == newShippingAddressOption)
            {
                this.showFormPopUp();
            }
            else
            {
                this.updateAddress();
                $("#custom-address-items").hide();
                $("#add").val(address);
                $(".checkout-index-index .field.addresses .shipping-address-items .shipping-address-item").hide();
                checkoutData.setSelectedShippingAddress(address.getKey());
            }
        },

        /**
         * Show address form popup
         */
        showFormPopUp: function () {
            this.isFormPopUpVisible(true);
        },

        /**
         * Update address action
         */
        updateAddress: function () {

            var addressData, newShippingAddress;
            if (this.selectedShippingAddress() && this.selectedShippingAddress() != newShippingAddressOption) { //eslint-disable-line eqeqeq
                selectShippingAddress(this.selectedShippingAddress());
                checkoutData.setSelectedShippingAddress(this.selectedShippingAddress().getKey());
            } else {
                this.source.set('params.invalid', false);
                this.source.trigger(this.dataScopePrefix + '.data.validate');

                if (this.source.get(this.dataScopePrefix + '.custom_attributes')) {
                    this.source.trigger(this.dataScopePrefix + '.custom_attributes.data.validate');
                }

                if (!this.source.get('params.invalid')) {
                    addressData = this.source.get(this.dataScopePrefix);

                    if (customer.isLoggedIn() && !this.customerHasAddresses) { //eslint-disable-line max-depth
                        this.saveInAddressBook(1);
                    }
                    addressData['save_in_address_book'] = this.saveInAddressBook() ? 1 : 0;
                    newShippingAddress = createShippingAddress(addressData);

                    // New address must be selected as a billing address
                    selectedShippingAddress(newShippinhAddress);
                    checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());
                    checkoutData.setNewCustomerShippingAddress(addressData);
                }
            }
            // this.updateShippingAddresses();
        },

        /**
         * Trigger action to update shipping and billing addresses
         */
        updateShippingAddresses: function () {
            setShippingAddressAction(globalMessageList);
        },

        /** @inheritdoc */
        initConfig: function () {
            this._super();
            // the list of child components that are responsible for address rendering
            this.rendererComponents = [];

            return this;
        },

        /** @inheritdoc */
        initChildren: function () {
            _.each(addressList(), this.createRendererComponent, this);

            return this;
        },

        /**
         * Create new component that will render given address in the address list
         *
         * @param {Object} address
         * @param {*} index
         */
        createRendererComponent: function (address, index) {
            var rendererTemplate, templateData, rendererComponent;

            if (index in this.rendererComponents) {
                this.rendererComponents[index].address(address);
            } else {
                // rendererTemplates are provided via layout
                rendererTemplate = address.getType() != undefined && this.rendererTemplates[address.getType()] != undefined ? //eslint-disable-line
                    utils.extend({}, defaultRendererTemplate, this.rendererTemplates[address.getType()]) :
                    defaultRendererTemplate;
                templateData = {
                    parentName: this.name,
                    name: index
                };
                rendererComponent = utils.template(rendererTemplate, templateData);
                utils.extend(rendererComponent, {
                    address: ko.observable(address)
                });
                layout([rendererComponent]);
                this.rendererComponents[index] = rendererComponent;
            }
        }
    });
});
