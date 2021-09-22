define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
     
    function displayPrice(config, element) {
    	let productIds = [];
    	console.log("Price Url: " + config.priceUrl+ " Page: "+ config.page + " limit : "+config.limit);
    	let priceBaseUrl = config.priceUrl;
    	let pageNo = config.page;
    	let limit = config.limit;
		$(".price-box").each(function() {
		    var product_id = $(this).attr('data-product-id');

		    if ($.inArray(product_id, productIds) == -1) {
		        productIds.push(product_id);
		    }
		});
		var loaderImg = config.loaderImg;
		var loaderHtml = '<div data-role="pannel" class="price-panel" style="position: absolute;"><div data-role="loader" class="loading-mask" style="position: relative;"><div class="loader"><img style="position: relative;" src="'+loaderImg+'" alt="loading" width="30px"></div></div>';

            
        $('.price-box').before(loaderHtml);
        $('.price-box .price').hide();
    	
    	$.ajax({
                url: priceBaseUrl,
                type: 'POST',
                dataType: 'json',
                showLoader: false,
                data: {
                    productIds : productIds.toString(),
                    page: pageNo,
                    limit: limit                    
                },
                complete: function(response) {
                    console.log(response.responseJSON);

                    $.each(response.responseJSON,  function(key, value) {
						
						let product_id = value.productId;
						let price = value.price;
						let qty = value.qty;
						if(product_id){

						    $('.product-item-details .price-panel').hide();
						    $('.price-box .price').show();
						    let loaderClass = "div.price-panel-"+product_id;
						    $("#price-panel-"+product_id).hide();   


						    
							$('#product-price-'+product_id+' .price').show();
							$('#old-price-'+product_id+' .price').show();
							$('.loader').hide();

							if(price != '$0.00'){
								$('#product-price-'+product_id+' .price').html(price);      
							}
							else {
								$('#product-price-'+product_id+' .price').html('call for pricing');
								$('.tocart-'+product_id).hide();
							}				       
						}
              		});                       
                },
                error: function (xhr, status, errorThrown) {
                    console.log('Error happens. Try again.');
                }
            });
	};
	return displayPrice;    
});