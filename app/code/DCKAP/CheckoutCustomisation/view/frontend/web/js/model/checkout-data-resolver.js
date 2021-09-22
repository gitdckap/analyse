define(function () {
    'use strict';

    var mixin = {

        /**
         *
         * @param {Column} elem
         */
        isDisabled: function (elem) {
            return elem.blockVisibility || this._super();
        },

        /**
         * @param {Object} ratesData
         */
        resolveShippingRates: function (ratesData) {
            var selectedShippingRate = checkoutData.getSelectedShippingRate(),
                availableRate = false;

            if (ratesData.length === 1) {
                //set shipping rate if we have only one available shipping rate
                selectShippingMethodAction(ratesData[0]);

                return;
            }

            if (ratesData.length > 1) {
		        selectShippingMethodAction(ratesData[0]);

		        return;
		    }

            if (quote.shippingMethod()) {
                availableRate = _.find(ratesData, function (rate) {
                    return rate['carrier_code'] == quote.shippingMethod()['carrier_code'] && //eslint-disable-line
                        rate['method_code'] == quote.shippingMethod()['method_code']; //eslint-disable-line eqeqeq
                });
            }

            if (!availableRate && selectedShippingRate) {
                availableRate = _.find(ratesData, function (rate) {
                    return rate['carrier_code'] + '_' + rate['method_code'] === selectedShippingRate;
                });
            }

            if (!availableRate && window.checkoutConfig.selectedShippingMethod) {
                availableRate = window.checkoutConfig.selectedShippingMethod;
                selectShippingMethodAction(window.checkoutConfig.selectedShippingMethod);

                return;
            }

            //Unset selected shipping method if not available
            if (!availableRate) {
                selectShippingMethodAction(null);
            } else {
                selectShippingMethodAction(availableRate);
            }
        }
    };

    return function (target) { // target == Result that Magento_Ui/.../columns returns.
        return target.extend(mixin); // new result that all other modules receive
    };


});