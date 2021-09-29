var config = {
    "map": {
        "*": {
            "Magento_Checkout/js/model/shipping-save-processor/default" : "DCKAP_ShippingAdditionalFields/js/shipping-save-processor"
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'DCKAP_ShippingAdditionalFields/js/view/shipping': true
            },
            'Magento_Checkout/js/view/shipping-information': {
                'DCKAP_ShippingAdditionalFields/js/view/shipping-information': true
            },
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'DCKAP_ShippingAdditionalFields/js/model/checkout-data-resolver': true
            }
        }
    }
};