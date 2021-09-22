/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define(
    [
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/text'
    ],
    function (_, registry, Text) {
        'use strict';

        return Text.extend(
            {

                /**
                 * Cloras Text Disable component constructor.
                 *
                 * @returns {exports}
                 */
                initialize: function () {
                    this._super();

                    this.disable(true);

                    return this;
                }
            }
        );
    }
);
