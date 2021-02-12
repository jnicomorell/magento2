var config = {
    map: {
        '*':{
            'form-hipotecarios': 'Formax_SimulatorHipotecario/js/form-hipotecarios'
        }   
    },
    shim: {
        'form-hipotecarios': {
            deps: ['format-currency-min']
        }
    }
};