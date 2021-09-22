var config = {
    paths: {
        'cayanCheckoutPlus': 'https://ecommerce.merchantware.net/v1/CayanCheckoutPlus'
    },
    shim: {
        'cayanCheckoutPlus': {
            "deps": ["jquery"],
            "exports": "CayanCheckoutPlus"
        }
    }
};