var config = {
    map: {
        '*': {
            'loginValidator': 'Ceg_Auth0Integration/js/login-validator'
        }
    },
    shim: {
        loginValidator: {
            deps: [
                'jquery',
                'Magento_Customer/js/customer-data'
            ]
        }
    }
};
