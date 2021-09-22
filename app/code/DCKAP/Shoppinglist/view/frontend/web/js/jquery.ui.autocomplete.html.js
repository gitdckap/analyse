/**
  * @author     DCKAP <extensions@dckap.com>
  * @package    DCKAP_Shoppinglist
  * @copyright  Copyright (c) 2016 DCKAP Inc (http://www.dckap.com)
  * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/*
 * jQuery UI Autocomplete HTML Extension
 *
 * Copyright 2010, Scott González (http://scottgonzalez.com)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 *
 * http://github.com/scottgonzalez/jquery-ui-extensions
 */
(function($) {

    var proto = $.ui.autocomplete.prototype,
        initSource = proto._initSource;

    function filter(array, term) {
        var matcher = new RegExp($.ui.autocomplete.escapeRegex(term), "i");
        return $.grep(array, function(value) {
            return matcher.test($("<div>").html(value.label || value.value || value).text());
        });
    }

    $.extend(proto, {
        _initSource: function() {
            if (this.options.html && $.isArray(this.options.source)) {
                this.source = function(request, response) {
                    response(filter(this.options.source, request.term));
                };
            } else {
                initSource.call(this);
            }
        },
        _renderItem: function(ul, item) {
            ul.addClass('shopping-list');
            var currentproduct = '';
            if (jQuery.trim(item.value) == 'no-matches') {
                currentproduct = '<div style="padding:5px; overflow:hidden;" class="pitem">' +
                    '<div style="float: left; margin-left: 5px">' +
                    '<div style="font-weight: bold; color: #333; font-size: 10px; line-height: 24px;"><strong>' + item.title + '</strong></div>' +
                    '</div>' +
                    '</div>';
            } else {
                currentproduct = '<div style="padding:5px; overflow:hidden;" class="pitem">' +
                    '<div style="float: left;"><img width="32" height="32" src="' + item.pimage + '" /></div>' +
                    '<div style="float: left; margin-left: 5px; width: 85%; padding-left: 5px">' +
                    '<div style="font-weight: bold; color: #333; font-size: 10px; line-height: 24px;"><strong>' + item.title + '</strong></div>' +
                    '<div style="color: #999; font-size: 9px">' + item.sku + '</div>' +
                    '</div>' +
                    '</div>';
            }
            return $("<li></li>")
                .data("item.autocomplete", item)
                .append(currentproduct)
                .appendTo(ul);
        },
        _resizeMenu: function() {
            this.menu.element.css({
                'width': '459px',
                'overflow-x': 'hidden',
                'overflow-y': 'hidden',
                'height': 'auto',
                'padding-left': '1px',
                'background': '#fff',
                'border': '1px solid #ccc'
            });
            if (parseInt(this.menu.element.css('height')) >= 180) {
                this.menu.element.css({
                    'height': '180px',
                    'overflow-y': 'scroll'
                });
            }
        }
    });
})(jQuery);
