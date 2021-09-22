define(
    [
        'Dckap_ShippingAdditionalFields/js/warehouse/service/resource-url-manager',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'mage/storage',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (resourceUrlManager, quote, customer, storage, shippingService, errorProcessor) {
        'use strict';

        return {
            getWarehouseList: function (shipping) {
                var payload;

                payload = {
                    addressInformation: {
                        'shipping_address': quote.shippingAddress(),
                        'shipping_method_code': quote.shippingMethod()['method_code'],
                        'shipping_carrier_code': quote.shippingMethod()['carrier_code']
                    }
                };

                shippingService.isLoading(true);
                var serviceUrl = resourceUrlManager.getUrlForWarehouseList(quote);

                return storage.post(
                    serviceUrl, JSON.stringify(payload)
                ).done(
                    function (result) {
                        shipping.setWarehouseCollectionList(result);
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                    }
                ).always(
                    function () {
                        shippingService.isLoading(false);
                    }
                );
                
            }
        };
    }
);