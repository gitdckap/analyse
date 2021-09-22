/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'mage/url',
    'mage/utils/wrapper'
], function ($, customUrlBuilder, wrapper) {
    'use strict';

    return function (placeOrderAction) {
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
           
            var custom_po_number = null;
            var po_document_data  = null;
            var order_comment = null;

            custom_po_number = $(".section.po_number input").val();

            po_document_data = $(".section.upload_po_number input").val();

            order_comment = $("#order_comment").val();

            var fileName = null;

            var payment_additional_data = {};

            
            if(po_document_data)
            {
                var fileInfo = $('#po_document')[0].files[0];
                var formData = new FormData();
                formData.append('image', $('input[type=file]')[0].files[0]); 
                
                if(fileInfo != undefined)
                {   
                    var serviceurl = customUrlBuilder.build('customdata/Index/SaveImage');
                    jQuery.ajax({
                        url: serviceurl,
                        type: 'POST',
                        data:  formData,
                        async: false,
                        contentType: false,
                        processData: false,
                        showLoader: true,
                        success: function (data) 
                        {
                            fileName = data['fieldId'];
                            payment_additional_data = {
                                "po_number": custom_po_number,
                                "po_document": fileName,
                                "order_comment": order_comment
                            };
                            paymentData.additional_data = payment_additional_data;
                        }
                    });
                }
            }
            else
            {
                payment_additional_data = {
                    "po_number": custom_po_number,
                    "po_document": fileName,
                    "order_comment": order_comment
                };
                paymentData.additional_data = payment_additional_data;
            }
            
            paymentData.additional_data = payment_additional_data;
        

            return originalAction(paymentData, messageContainer);
            
        });
    };
});
