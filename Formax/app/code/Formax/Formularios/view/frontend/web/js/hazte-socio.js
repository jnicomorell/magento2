
require(['jquery', 'jquery-rut'], function($) {

    $(document).ready(function(){

        // Validamos el ingreso de caracteres para el campo nombre
        $('[name=name], [name=lastname]').on('input',function(e){
            $(this).val($(this).val().replace(/[^a-zA-ZáéíóúÁÉÍÓÚäëïöüÄËÏÖÜñÑ\s-]+$/, ""));
        });

        // Validamos el ingreso de caracteres para el campo rut
        $('[name=rut]').on('input',function(e){
            $(this).val($(this).val().replace(/[^0-9kK\-\.]+$/, ""));
        });

        $("[name=rut]").rut({formatOn: 'keyup keypress blur'});

        // Validamos el ingreso de caracteres para el campo telefono
        $('[name=phone]').on('input',function(e){
            $(this).val($(this).val().replace(/[^0-9]+$/, ""));
        });

        // Gatillamos funcion clic en btn enviar
        $('form').submit(function(){
            if (!verifyName() ||
                !verifyLname() ||
                !verifyRut() ||
                !verifyPhone() ||
                !verifyEmail() )
                    {
                        return false;
                    }
            
            $('input[type=submit]').addClass('disabled');
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
        $('.form-field').blur(function(){
            var name = $(this).attr('name');
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
              }
        });

    });

});
