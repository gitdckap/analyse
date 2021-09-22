/**
 * Copyright © 2017 DCKAP. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'elementpayment',
                component: 'Dckap_Elementpayment/js/view/payment/method-renderer/elementpayment-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);