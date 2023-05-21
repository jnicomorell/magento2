
require(['jquery', 'jquery-rut', 'format-currency-min'], function($) {

    $(document).ready(function(){

        var valid;

        // Validamos el ingreso de caracteres para el campo nombre
        $('[name=name], [name=lastname]').on('keyup keypress paste',function(e){
            $(this).val($(this).val().replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚ\s-]+$/, ""));
        });

        // Validamos el ingreso de caracteres para el campo rut
        $('[name=rut]').on('keyup keypress paste',function(e){
            $(this).val($(this).val().replace(/[^0-9kK\-\.]+$/, ""));
        });

        $("[name=rut]").rut({formatOn: 'keyup keypress blur'});

        // Validamos campos con digitos
        $(".digit").on('keyup keypress paste', function(e) {
            //if the letter is not digit then display error and don't type anything
            if (e.which !== 8 && e.which !== 0 && (e.which < 48 || e.which > 57)) {
                return false;
            }
        });

        $(".money").on('keyup keypress paste', function(e) {
            var amount = $(this).val().replace(/[^\d]/g, '');
            if (amount) {
               $(this).val(amount);
               $(this).formatCurrency();
            }
            var length = amount.length;
            if (length >= 9) {
                return false;
            }
        });

        $('.money').formatCurrency();

        $('#check-down-payment-no').prop('checked', false);
        $('#check-down-payment-yes').prop('checked', false);

        $("#check-down-payment-yes").on( 'change', function() {
            if( $(this).is(':checked') ) {
                // Si el checkbox ha sido seleccionado
                $('#down-payment').show();
                if( $('#check-down-payment-no').is(':checked') )
                    $('#check-down-payment-no').prop('checked', false);
            } else {
                // Si el checkbox ha sido deseleccionado
                $('#down-payment').hide();
                $('#down-payment').val('');
                $(".down-payment-error-msg").remove();
            }
        });

        $("#check-down-payment-no").on( 'change', function() {
            if( $(this).is(':checked') ) {
                // Si el checkbox ha sido seleccionado
                $('#down-payment').hide();
                $('#down-payment').val('');
                $(".down-payment-error-msg").remove();
                if( $('#check-down-payment-yes').is(':checked') )
                    $('#check-down-payment-yes').prop('checked', false);
            } else {
                // Si el checkbox ha sido deseleccionado
            }
        });

        // Enviamos formulario credito hipotecario
        $('form').on('submit', function(e) {
                if (!verifyName() ||
                    !verifyLname() ||
                    !verifyRut() ||
                    !verifyPhone() ||
                    !verifyEmail() ||
                    !verifyAmount('monthly-income') ||
                    !verifyAmount('amount') ||
                    !verifyCheckbox() )
                    {
                        return false;
                    }
                $('.send-form').addClass('disabled');
        });

        function verifyName(){
            name = $('[name=name]').val();
            if($.trim(name) == ''){
                $('.name-error-msg').remove();
                $('[name=name]').parents('.control').append('<div for="name" generated="true" class="mage-error name-error-msg" >Por favor, rellene este campo.</div>');
                return false;
            } else {
                $('.name-error-msg').remove();
                return true;
            }
        }

        function verifyLname(){
            lastname = $('[name=lastname]').val();
            if($.trim(lastname) == ''){
                $('.lname-error-msg').remove();
                $('[name=lastname]').parents('.control').append('<div for="name" generated="true" class="mage-error lname-error-msg" >Por favor, rellene este campo.</div>');
                return false;
            } else {
                $('.lname-error-msg').remove();
                return true;
            }
        }

        function verifyRut(){
            rut = $('[name=rut]').val();
            if(!$.validateRut(rut, null, { minimumLength: 7 }) || !validateRutVeracity(rut) || rut == ''){
                $('.rut-error-msg').remove();
                $('[name=rut]').parents('.control').append('<div for="rut" generated="true" class="mage-error rut-error-msg" >Ingrese un rut válido.</div>');
                return false;
            } else {
                $('.rut-error-msg').remove();
                return true;
            }
        }

        function verifyPhone(){
            phone = $('[name=phone]').val();
            var length  = phone.length,
                max = $('[name=phone]').attr("maxlength");
            if (length != max) {
                $(".phone-error-msg").remove();
                $('[name=phone]').parents('.control').append('<div for="phone" generated="true" class="mage-error phone-error-msg" >Ingrese un teléfono válido.</div>');
                return false;
            } else {
                $(".phone-error-msg").remove();
                return true;
            }
        }

        function verifyEmail(){
            email = $('[name=email]').val();
            if (!validateEmailFormat(email) || email == '') {
                $(".email-error-msg").remove();
                $('[name=email]').parents('.control').append('<div for="email" generated="true" class="mage-error email-error-msg" >Ingrese un email válido.</div>');
                return false;
            } else {
                $(".email-error-msg").remove();
                return true;
            }
        }

        function verifyCheckbox(){
            checkYes = $('#check-down-payment-yes').prop('checked');
            checkNo  = $('#check-down-payment-no').prop('checked');
            if (!checkYes && !checkNo){
                $(".down-payment-error-msg").remove();
                $('#down-payment').parents('.control').append('<div for="subject" generated="true" class="mage-error down-payment-error-msg" >Por favor, seleccione una opción.</div>');
                return false;
            } else if ( checkYes ) {
                if ( verifyAmount('down-payment') )
                    return true;
                else
                    return false;
            } else {
                $(".down-payment-error-msg").remove();
                return true;
            }
        }

        function verifyAmount(id){
            amount =  $('#'+id).val();
            var num = amount.replace(/[^\d]/g, '');
            var error = id + '-error-msg';
            if (num) {
                $('#'+id).val(num);
                $('#'+id).formatCurrency();
            }
            if (num <= 0) {
                $("."+error).remove();
                $('#'+id).parents('.control').append('<div for="subject" generated="true" class="mage-error ' + error +'" >Por favor, ingrese un monto.</div>');
                return false;
            } else {
                $("."+error).remove();
                return true;
            }
        }

        // Verificamos que el rut no tenga numeros consecutivos
        function validateRutVeracity(rutCompleto){
            rut = rutCompleto.replace(/\./g, '').split('-');
            rut = rut[0];

            if(/(\d)\1{7}/.test(rut)){
                return false;
            }
            return true;
        }

        // Verificamos que el email tenga formato válido
        function validateEmailFormat(email){
            if (/^[-\w.%+]{1,64}@(?:[A-Z0-9-]{1,63}\.){1,125}[A-Z]{2,63}$/i.test(email)){
                return true;
            } else {
                return false;
            }
        }

        // Validamos el campo cuando pierde el foco
        $('.fm-field').blur(function(){
            var name = $(this).attr('name');
            var id = $(this).attr('id');
            switch(name) {
                case 'name':
                    verifyName();
                    break;
                case 'lastname':
                    verifyLname();
                    break;
                case 'rut':
                    verifyRut();
                    break;
                case 'phone':
                    verifyPhone();
                    break;
                case 'email':
                    verifyEmail();
                    break;
                case id:
                    verifyAmount(id);
                    break;
              }
        });

    });

});
