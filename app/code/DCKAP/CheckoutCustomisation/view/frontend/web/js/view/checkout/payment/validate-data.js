define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'DCKAP_CheckoutCustomisation/js/model/validate-and-save'
    ],
    function (Component, additionalValidators, validateData) {
        'use strict';
        additionalValidators.registerValidator(validateData);
        return Component.extend({});
    }
);