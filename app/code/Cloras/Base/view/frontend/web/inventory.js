define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
     
    function displayInventory(config, element) {
    	let productIds = [];
    	
        console.log("inventoryUrl: " + config.inventoryUrl+ " Page: "+ config.page + " limit : "+config.limit);
       
        let pageNo = config.page;
        let limit = config.limit;

    	let inventoryUrl = config.inventoryUrl;
    	
		$(".price-box").each(function() {
		    var product_id = $(this).attr('data-product-id');

		    if ($.inArray(product_id, productIds) == -1) {
		        productIds.push(product_id);
		    }
		});

    	$.ajax({
                url: inventoryUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    productIds : productIds.toString(),
                    page: pageNo,
                    limit: limit
                },
                complete: function(response) {
                	
 					$.each(response.responseJSON,  function(key, value) {
                        
                        let product_id = value.productId;
                        
                        let qty = value.qty;

                        if(product_id){
                        	if (qty==0){
								qty='Call for Availability';
							}
                            $("#product-qty-"+product_id).html(qty);                              
                        }
                    });                          
                },
                error: function (xhr, status, errorThrown) {
                    console.log('Error happens. Try again.');
                }
            });
	};
	return displayInventory;    
});