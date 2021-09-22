var config = {
    "map": {
        "*": {
            "Magento_Checkout/js/model/shipping-save-processor/default" : "Dckap_ShippingAdditionalFields/js/shipping-save-processor"
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Dckap_ShippingAdditionalFields/js/view/shipping': true
            },
            'Magento_Checkout/js/view/shipping-information': {
                'Dckap_ShippingAdditionalFields/js/view/shipping-information': true
            },
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'Dckap_ShippingAdditionalFields/js/model/checkout-data-resolver': true
            }
        }
    }
};