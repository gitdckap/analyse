define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function (_, uiRegistry, select, modal) {
    'use strict';

    return select.extend({

        /**
         * Extends instance with defaults, extends config with formatted values
         *     and options, and invokes initialize method of AbstractElement class.
         *     If instance's 'customEntry' property is set to true, calls 'initInput'
         */
        initialize: function () {
            this._super();

            return this;
        },

        /**
         * On value change handler.
         * Handles warehouse select event in checkout page to check product stock.
         * @param {String} value
         */
        onUpdate: function (value) {
            var customData = window.checkoutConfig.warehouse[value];
            if(customData)
                this.notice(customData);
            else
                this.notice(false);
           this._super();
        }

    });
});