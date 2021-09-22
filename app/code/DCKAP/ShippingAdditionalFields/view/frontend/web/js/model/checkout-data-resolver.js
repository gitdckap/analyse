define([
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/action/select-payment-method',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/action/create-billing-address',
    'mage/utils/wrapper',
    'underscore'
], function (
    addressList,
    quote,
    checkoutData,
    createShippingAddress,
    selectShippingAddress,
    selectShippingMethodAction,
    paymentService,
    selectPaymentMethodAction,
    addressConverter,
    selectBillingAddress,
    createBillingAddress,
    wrapper,
    _
) {
    'use strict';

    return function (target) {

        var resolveBillingAddress = wrapper.wrap(target.resolveBillingAddress, function(){
            target.applyBillingAddress();
        });

        var applyBillingAddress = wrapper.wrap(target.applyBillingAddress, function(){
            var shippingAddress;

            if (quote.billingAddress()) {
                selectBillingAddress(quote.billingAddress());

                return;
            }
            shippingAddress = quote.shippingAddress();

            if (shippingAddress &&
                shippingAddress.canUseForBilling() &&
                (shippingAddress.isDefaultShipping() || !quote.isVirtual())
            ) {
                //set billing address same as shipping by default if it is not empty
                // selectBillingAddress(quote.shippingAddress());
            }
        });

        target.resolveBillingAddress = resolveBillingAddress;
        target.applyBillingAddress = applyBillingAddress;

        return target;
    };
});