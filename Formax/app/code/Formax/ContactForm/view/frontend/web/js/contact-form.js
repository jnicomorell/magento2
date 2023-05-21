
require(['jquery', 'jquery-rut'], function($) {

    $(document).ready(function(){

        // Validamos el ingreso de caracteres para el campo nombre
        $('#cf-name').on('input',function(e){
            $(this).val($(this).val().replace(/[^a-zA-ZáéíóúÁÉÍÓÚäëïöüÄËÏÖÜñÑ\s-]+$/, ""));
        });

        // Validamos el ingreso de caracteres para el campo rut
        $('#cf-rut').on('input',function(e){
            $(this).val($(this).val().replace(/[^0-9kK\-\.]+$/, ""));
        });

        $("#cf-rut").rut({formatOn: 'keyup keypress blur'});

        // Validamos el ingreso de caracteres para el campo telefono
        $('#cf-phone').on('input',function(e){
            $(this).val($(this).val().replace(/[^0-9]+$/, ""));
        });

        // Validamos el ingreso de caracteres para el campo nombre
        $('#cf-message').on('input',function(e){
            $(this).val($(this).val().replace(/[^0-9a-zA-ZáéíóúÁÉÍÓÚäëïöüÄËÏÖÜñÑ¿?!¡_$%:.,\s-]+$/, ""));
        });

        // Gatillamos funcion clic en btn enviar
        $('#submit-form-btn').click(function(){
            var name  = $('#cf-name').val(),
                rut   = $('#cf-rut').val(),
                code  = $('#cf-code').attr('data-value'),
                phone = $('#cf-phone').val(),
                email = $('#cf-email').val(),
                sub   = $('#cf-subject').attr('data-value'),
                msg   = $('#cf-message').val();
            
            var isValidName  = verifyName(name),
                isValidRut   = verifyRut(rut),
                isValidPhone = verifyPhone(phone),
                isValidEmail = verifyEmail(email),
                isValidSub   = verifySubject(sub),
                isValidMsg   = verifyMsg(msg);

            if (isValidName && isValidRut && isValidPhone && isValidEmail && isValidSub && isValidMsg) {

                $('#submit-form-btn').addClass('disabled');
                $('#coo-contact-form').submit();

            }
        });

        function verifyName(name){
            if($.trim(name) == ''){
                $('.name-error-msg').remove();
                $('#cf-name').parents('.field').append('<div for="name" generated="true" class="mage-error name-error-msg" >Por favor, rellene este campo.</div>');
                return false;
            } else {
                $('.name-error-msg').remove();
                return true;
            }
        }

        function verifyRut(rut){
            if(!$.validateRut(rut, null, { minimumLength: 7 }) || !validateRutVeracity(rut) || rut == ''){
                $('.rut-error-msg').remove();
                $('#cf-rut').parents('.field').append('<div for="rut" generated="true" class="mage-error rut-error-msg" >Ingrese un rut válido.</div>');
                return false;
            } else {
                $('.rut-error-msg').remove();
                return true;
            }
        }

        function verifyPhone(phone){
            var length  = phone.length,
                max = $("#cf-phone").attr("maxlength");
            if (length != max) {
                $(".phone-error-msg").remove();
                $('#cf-phone').parents('.field').append('<div for="phone" generated="true" class="mage-error phone-error-msg" >Ingrese un teléfono válido.</div>');
                return false;
            } else {
                $(".phone-error-msg").remove();
                return true;
            }
        }

        function verifyEmail(email){
            if (!validateEmailFormat(email) || email == '') {
                $(".email-error-msg").remove();
                $('#cf-email').parents('.field').append('<div for="email" generated="true" class="mage-error email-error-msg" >Ingrese un email válido.</div>');
                return false;
            } else {
                $(".email-error-msg").remove();
                return true;
            }
        }

        function verifySubject(sub){
            if (sub == 0) {
                $(".sub-error-msg").remove();
                $('#cf-subject').parents('.field').append('<div for="subject" generated="true" class="mage-error sub-error-msg" >Por favor, seleccione el asunto.</div>');
                return false;
            } else {
                $(".sub-error-msg").remove();
                return true;
            }
        }

        function verifyMsg(msg) {
            if($.trim(msg) == '') {
                $('.message-error-msg').remove();
                $('#cf-message').parents('.field').append('<div for="msg" generated="true" class="mage-error message-error-msg" >Por favor, rellene este campo.</div>');
                return false;
            } else {
                $('.message-error-msg').remove();
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

        $(".drop-subject").click(function() {
            sub = $('#cf-subject').attr('data-value');
            $('[name=formato-id]').val(sub);
            verifySubject(sub)
        });

        // Validamos el campo cuando pierde el foco
        $('.cf-field').blur(function(){
            var ex = $(this).attr('id');
            var vl = $(this).val();
            switch(ex) {
                case 'cf-name':
                    verifyName(vl);
                    break;
                case 'cf-rut':
                    verifyRut(vl);
                    break;
                case 'cf-phone':
                    verifyPhone(vl);
                    break;
                case 'cf-email':
                    verifyEmail(vl);
                    break;
                case 'cf-message':
                    verifyMsg(vl);
                    break;
              }
        });

    });

});
