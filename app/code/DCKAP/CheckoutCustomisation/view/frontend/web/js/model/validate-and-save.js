define(
    ['jquery'],
    function ($) {
        'use strict';
        return {
            /**
             * Validate something
             *
             * @returns {boolean}
             */
            validate: function() {

            	var reqMsg = "<div class='mage-error'>This is required field</div>";
                var errFormat = "<div class='mage-error'>Please provide file with the supported formats</div>";
                var sizeError = "<div class='mage-error'>Please provide file with size less than 2 MB</div>";
                
            	$(".payment-custom-fields .mage-error").remove();


                var poNumber = $("#customer_po_number_req").val();


                // if($("#customer_po_number_req").length > 0)
                // {    
                //     if(!poNumber)
                //     {   
                //         $("#customer_po_number_req").parent().closest('.field').addClass("required");
                //         $("#customer_po_number_req").parent().closest('.field').append(reqMsg);
                //         $('html, body').animate({
                //             scrollTop: $("#customer_po_number_req").parent().closest('.field').offset().top
                //         }, 1000);
                //         return false;
                //     }
                // }

                var poFile = $("input[name='customer_po_file']").val();


                if($("input[name='customer_po_file']").length > 0)
                {
                    if(poFile)
                    {
                        var fileSize = $('#po_document')[0].files[0].size;
                        if(fileSize > 2000000) {
                            $("input[name='customer_po_file']").parent().closest('.field').append(sizeError);
                            $('html, body').animate({
                                scrollTop: $("#customer_po_number_req").parent().closest('.field').offset().top
                            }, 1000);
                            return false;
                        };
                    
                        var filePath = poFile.toString();
                        var fileName =  filePath.substr((filePath.lastIndexOf('\\') +1) );
                        var fileExt = fileName.substr((fileName.lastIndexOf('.') +1) ); 

                        fileExt = fileExt.toLowerCase();

                        if(fileExt == "pdf" || fileExt == "png" || fileExt == "jpeg")
                        {
                            return true;
                        }
                        else
                        {
                            $("input[name='customer_po_file']").parent().closest('.field').addClass("required");
                            $("input[name='customer_po_file']").parent().closest('.field').append(errFormat);
                            $('html, body').animate({
                                scrollTop: $("input[name='customer_po_file']").parent().closest('.field').offset().top
                            }, 1000);
                            return false;    
                        }
                    }
                }
	            
                return true;
            }
        }
    }
);