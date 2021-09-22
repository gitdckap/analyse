/**
 * Cayan Payments
 *
 * @package Cayan\Payment
 * @author Igor Miura
 * @author Joseph Leedy
 * @copyright Copyright (c) 2017 Cayan (https://cayan.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

var config = {
    paths: {
        'cayanCheckoutPlus': 'https://ecommerce.merchantware.net/v1/CayanCheckoutPlus'
    },
    shim: {
        'cayanCheckoutPlus': {
            "deps": ["jquery"],
            "exports": "CayanCheckoutPlus"
        }
    },
    config: {
        mixins: {
            'Magento_Tax/js/view/checkout/summary/grand-total': {
                'Cayan_Payment/js/view/checkout/summary/grand-total': true
            }
        }
    }
};