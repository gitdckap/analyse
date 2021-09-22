/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CheckoutCustomField
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/utils/wrapper',
    'jquery/ui',
    'mage/translate',
    'mage/mage',
    'mage/validation'
    ],
    function ($) {
    'use strict';
    return function (widget) {

        $.widget('mage.orderReview', widget, {
            options: {
                customfield: '#customfield',
            },

            _create: function () {
                var self = this;

                this._super();

                $(this.options.customfield).on('change', function() {
                    if(!$(self.options.customfield).validation().valid()) {
                        self._updateOrderSubmit(true);
                    }else {
                        self._ajaxCustomFieldUpdate();
                    }
                });
            },

            _validateForm: function () {
                var result = this._super();
                if($(this.options.customfield).mage('validation', {})) {
                    return $(this.options.customfield).validation().valid() && result;
                }
                return result
            },

            _ajaxCustomFieldUpdate: function() {
                $.ajax({
                    url: $(this.options.customfield).prop('action'),
                    type: 'post',
                    context: this,
                    data: $(this.options.customfield).serialize(),
                    dataType: 'json',
                    beforeSend: this._ajaxBeforeSend,
                    complete: this._ajaxComplete,

                    /** @inheritdoc */
                    success: function (response) {
                        this._ajaxComplete();
                        if(!$.trim($(this.options.shippingSelector).val())) {
                            this._updateOrderSubmit(true);
                        } else {
                            this._updateOrderSubmit(false);
                        }                        
                    }
                });
            }
        });

        return $.mage.SwatchRenderer;
    }
});
