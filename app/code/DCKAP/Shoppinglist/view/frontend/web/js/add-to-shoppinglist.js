/**
  * @author     DCKAP <extensions@dckap.com>
  * @package    DCKAP_Shoppinglist
  * @copyright  Copyright (c) 2016 DCKAP Inc (http://www.dckap.com)
  * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui",
    "select2"
], function($){
    "use strict";
    $(document).ready(function() {
         var baseUrl=$("#baseUrl").val();
        function shoppinglisttrigger(){  
            var productid=$('#productid').val();
                if(productid){
                    $.ajax({
                    //sending product id
                    url : baseUrl+'shoppinglist/index/ajaxshoppinglist?id='+productid,
                    type : 'GET',
                    dataType:'json',
                    success : function(data) {
                       $('#loadingimage').hide();
                        $('#shopping_list_id').show();
                        var url_string = window.location.href;
                        var url = new URL(url_string);
                        $("#shopping_list_id").empty();
                        var result = jQuery.parseJSON(data);
                         var selected_list = [];
                        $.each(result, function(key,value) {
                            if(value.is_select){
                                 selected_list.push(value.list_name);
                                $("#shopping_list_id").append('<option value='+value.list_id+' selected="selected">'+value.list_name+'</option>');
                            } else{
                                $("#shopping_list_id").append('<option value='+value.list_id+'>'+value.list_name+'</option>');
                            }
                        });
                          if(selected_list.length){
                            $('<div><font color="green">Shopping List Updated In:'+selected_list.join(',')+'</font></div>').insertAfter('.add-to-shoppinglist-section').delay(3000).fadeOut();
                            $('#add_to_list').text("Added in:"+selected_list.join(','));
                        }else{
                             $('#add_to_list').text("Add To List");
                        }
                        $("#shopping_list_id").append('<optgroup name="create_new_list" label="Create New List" id="create_new_list"></optgroup>');  
                    },
                    
                    });
                }
        }
        
        $('#shopping_list_id').select2();
        $('#choose_option').hide();
        $('#shopping_list_action').hide();
        $('.add-to-shoppinglist-content').toggle('slow');
       jQuery(document).on('click', '.select2-results__group', function(e) {
            jQuery('#shopping_list_name_content').show();
            jQuery('#shopping_list_action').show();
            jQuery('#create_new_list').hide();
            jQuery('.select2-results__options').hide();
            jQuery('#shopping_list_existing_name_content').hide();
            jQuery('#shopping_list_choose').val('add_new');
            jQuery('#shopping_list_action').slideDown('slow');
            jQuery('#shopping_list_action').text("Create List");
            jQuery('.catalog-shopping-list-form .actions-toolbar').append('<button class="cancelcretatelist" id="cancel">cancel</button>');
        });
       jQuery(".catalog-shopping-list-form").on('click', '.cancelcretatelist', function () {
            jQuery('#shopping_list_choose').val('choose_existing');
            jQuery('#shopping_list_action').hide();
            jQuery('#shopping_list_id').select2();
            jQuery('#create_new_list').show();
            jQuery('#shopping_list_existing_name_content').show();
            jQuery('#shopping_list_name_content').hide();
            jQuery("#cancel").remove();
        });
       var getshippinglist=$('#listempty').val();
       if(getshippinglist==1){
        $('#shopping_list_choose').val('choose_existing');
        $('#shopping_list_existing_name_content').slideDown('slow');
        //$('#shopping_list_action').show();
       }else{
        //$('#shopping_list_action').show();
       jQuery('#shopping_list_name_content').show();
            jQuery('#shopping_list_action').show();
            jQuery('#create_new_list').hide();
            jQuery('.select2-results__options').hide();
            jQuery('#shopping_list_existing_name_content').hide();
            jQuery('#shopping_list_choose').val('add_new');
            jQuery('#shopping_list_action').slideDown('slow');
            jQuery('#shopping_list_action').text("Create List");
            jQuery('catalog-shopping-list-form .actions-toolbar').append('<button class="cancelcretatelist" id="cancel">cancel</button>');
       
        }

        $(document).on('change', '#shopping_list_choose', function(e) {
            if(this.value == 'choose_existing') {
                $('#shopping_list_existing_name_content').slideDown('slow');
            }
            else {
                $('#shopping_list_existing_name_content').slideUp('slow');
            }

            if(this.value == 'add_new') {
                $('#shopping_list_name_content').slideDown('slow');
            }
            else {
                $('#shopping_list_name_content').slideUp('slow');
            }
        });

        $('#shopping_list_action').click(function(){
            var err = 0;
            if($('#shopping_list_choose').val() == 'choose_existing' && $('#shopping_list_id').val() == null) {
                err++;
                $('#shopping_list_id-error').show();
            }
            else {
                $('#shopping_list_id-error').hide();
            }

            if($('#shopping_list_choose').val() == 'add_new' && $('#shopping_list_name').val().trim() == '') {
                err++;
                $('#shopping_list_name-error').show();
            }
            else {
                $('#shopping_list_name-error').hide();
            }
            if(err == 0) {

                if ($('#dynamic_shopping_list_id').length) {
                    $('#dynamic_shopping_list_id').val($('#shopping_list_id').val());
                } else {
                    $('<input type="hidden" name="shopping_list_id" id="dynamic_shopping_list_id" value="' + $('#shopping_list_id').val() + '" />').appendTo('#product_addtocart_form');
                }

                if ($('#dynamic_shopping_list_name').length) {
                    $('#dynamic_shopping_list_name').val($('#shopping_list_name').val());
                } else {
                    $('<input type="hidden" name="shopping_list_name" id="dynamic_shopping_list_name" value="' + $('#shopping_list_name').val() + '" />').appendTo('#product_addtocart_form');
                }

                if ($('#dynamic_slid').length) {
                    $('#dynamic_slid').val($('#slid').val());
                } else {
                    $('<input type="hidden" name="slid" id="dynamic_slid" value="' + $('#slid').val() + '" />').appendTo('#product_addtocart_form');
                }

                if ($('#dynamic_shopping_list_choose').length) {
                    $('#dynamic_shopping_list_choose').val($('#shopping_list_choose').val());
                } else {
                    $('<input type="hidden" name="shopping_list_choose" id="dynamic_shopping_list_choose" value="' + $('#shopping_list_choose').val() + '" />').appendTo('#product_addtocart_form');
                }
                
                if($('#shopping_list_choose').val() == 'add_new'){
                    var action = $(".toshoppinglist").attr('data-post');
                    $('#product_addtocart_form').attr('action', action).submit();
                }else{
                    
                   $.ajax({
                        type: "POST",
                        data: $('#product_addtocart_form').serialize(), 
                        url: baseUrl+'shoppinglist/index/updateproducttolist',
                        success: function(result) {
                            if(result){
                                shoppinglisttrigger();
                                //alert("Shopping list updated Successfully");
                            }
                        }
                   });
                }   
            }            
        });
    });
});
