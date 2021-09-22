define(
    [
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/select-billing-address'
    ],
    function (
        ko,
        quote,
        resourceUrlManager,
        storage,
        paymentService,
        methodConverter,
        errorProcessor,
        fullScreenLoader,
        selectBillingAddressAction
    ) {
        'use strict';

        return {
            saveShippingInformation: function () {
                var payload;

                var shippingMethod = quote.shippingMethod().method_code+'_'+quote.shippingMethod().carrier_code;

                var ddi_delivery_contact_email = null;
                var ddi_delivery_contact_no = null;
                var ddi_pref_warehouse = null;
                var ddi_pickup_date = null;
                
                if (shippingMethod == "ddistorepickup_ddistorepickup") {
                    ddi_delivery_contact_email = jQuery('[name="ddi_store_pickup[ddi_delivery_contact_email]"]').val();
                    ddi_delivery_contact_no = jQuery('[name="ddi_store_pickup[ddi_delivery_contact_no]"]').val();
                   ddi_pref_warehouse = jQuery('#warehouselist').val();
                   ddi_pickup_date = jQuery('#ddi_pickup_date').val();
                }

                if (!quote.billingAddress()) {
                    selectBillingAddressAction(quote.shippingAddress());
                }
                payload = {
                    addressInformation: {
                        shipping_address: quote.shippingAddress(),
                        billing_address: quote.billingAddress(),
                        shipping_method_code: quote.shippingMethod().method_code,
                        shipping_carrier_code: quote.shippingMethod().carrier_code,
                        extension_attributes: {
                            ddi_delivery_contact_email : ddi_delivery_contact_email,
                            ddi_delivery_contact_no: ddi_delivery_contact_no,
                            ddi_pref_warehouse: ddi_pref_warehouse,
                            ddi_pickup_date: ddi_pickup_date
                        }
                    }
                };
                fullScreenLoader.startLoader();
                return storage.post(
                    resourceUrlManager.getUrlForSetShippingInformation(quote),
                    JSON.stringify(payload)
                ).done(
                    function (response) {
                        quote.setTotals(response.totals);
                        paymentService.setPaymentMethods(methodConverter(response.payment_methods));
                        fullScreenLoader.stopLoader();
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                        fullScreenLoader.stopLoader();
                    }
                );
            }
        };
    }
);
