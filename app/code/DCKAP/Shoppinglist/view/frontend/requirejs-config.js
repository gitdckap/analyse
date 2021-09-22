/**
  * @author     DCKAP <extensions@dckap.com>
  * @package    DCKAP_Shoppinglist
  * @copyright  Copyright (c) 2016 DCKAP Inc (http://www.dckap.com)
  * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

var config = {
    map: {
        '*': {
            addToShoppinglist: 'DCKAP_Shoppinglist/js/add-to-shoppinglist',
            ui_autocomplete_html: 'DCKAP_Shoppinglist/js/jquery.ui.autocomplete.html',           
            select2: 'DCKAP_Shoppinglist/js/select2.min',
            "jquery/ui": 'DCKAP_Shoppinglist/js/jquery-ui.min'
        }
    },
    paths: {
        'select2': 'js/select2.min',
         "jquery/ui": 'js/jquery-ui.min'
    },
    shim: {
        'select2': {
        deps: ['jquery'],
        }
      }
};

require(['jquery','jquery/ui', 'Magento_Ui/js/modal/modal'],
    function($, modal) {
        require(['ui_autocomplete_html'], function($) {
            enableAutocomplete(jQuery('#shoppinglist-form-add'));
        });
    }
);


function enableAutocomplete(pSelector) {
    var cache = {};
    var ac_min_chars = 3;
    jQuery('#shoppinglist-form-add').find('input[name="product_name[]"]').autocomplete({
        minLength: ac_min_chars,
        delay: 500,
        html: true,
        source: function(request, response) {
            var searchKeyword = request.term;
            if (jQuery.trim(searchKeyword) != '') {
                if (searchKeyword in cache) {
                    response(cache[searchKeyword]);
                    return;
                }
                var ac_item = this.element;

                jQuery.ajax({
                    cache: true,
                    dataType: 'json',
                    method: 'POST',
                    url: Base_url + 'shoppinglist/index/getproduct',
                    data: {
                        query: searchKeyword
                    },
                    crossDomain: false,
                    success: function(datasuggestions) {

                        cache[searchKeyword] = datasuggestions;
                        response(datasuggestions);

                    }
                });

            }
        },
        focus: function(event, ui) {
            if (jQuery.trim(ui.item.value) != 'no-matches') {
                var itemTitle = jQuery('<textarea />').html(ui.item.label).text();
                jQuery(this).val(itemTitle);
            }
            return false;
        },
        select: function(event, ui) {
            if (ui.item.value == 'no-matches')
                return false;
            var ac_element = jQuery(this);
            ac_element.siblings('.item_loader').show();
            var elmParent = jQuery(this).parents('tr');
            var parentTd = jQuery(this).parents('td');

            var prodId = ui.item.value;
            var itemTitle = jQuery('<textarea />').html(ui.item.label).text();
            jQuery(this).val(itemTitle);
            jQuery(this).siblings('input:hidden').val(ui.item.productid);
            elmParent.find('.txt-sku').html(ui.item.sku);
            elmParent.find('.pqty').html(ui.item.price);
            elmParent.find('.txt-qty').val(1);
            parentTd.find('.confBlock').remove();

            elmParent.addClass('simple');
            elmParent.find('.txt-qty').show();
            ac_element.siblings('.item_loader').hide();
            return false;
        }
    }).click(function() {
        jQuery(this).autocomplete('search');
    });
    return false;
}

function jsleep(milliseconds) {
    var start = new Date().getTime();
    for (var i = 0; i < 1e7; i++) {
        if ((new Date().getTime() - start) > milliseconds) {
            break;
        }
    }
}
