var config = {
    paths: {
        'validate-form': 'Formax_Formularios/js/validate-form',
        'haztesocio-form': 'Formax_Formularios/js/hazte-socio',
        'dap-form': 'Formax_Formularios/js/dap'
    },
    shim: {
        'validate-form': {
            deps: ['format-currency-min']
        }
    }
};