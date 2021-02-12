require(['jquery', 'jquery-rut'], function($) {

    $(document).ready(function(){

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

        $('.solicitar_credito').click(function(e){
            e.preventDefault();

            let cuotas = $(this).attr('data-cuotas')
            let monto = $('#cuotas option:selected').text()
            $('input[name=comment]').val(`monto: ${monto}, cuotas: ${cuotas}`);

            $('#modal-solicitud').show();
        });

        $('.closeModal').click(function(){
            $('#modal-solicitud').hide();
        });

        // Enviamos formulario cyber
        $('form').on('submit', function(e) {
            if (!verifyRut() ||
                !verifyName() ||
                !verifyPhone() ||
                !verifyEmail() )
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
    
        // Verificamos que el rut no tenga numeros consecutivos
        function validateRutVeracity(rutCompleto){
            rut = rutCompleto.replace(/\./g, '').split('-');
            rut = rut[0];
        
            if(/(\d)\1{7}/.test(rut)){
                return false;
            }
            return true;
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
        
        // Verificamos que el email tenga formato válido
        function validateEmailFormat(email){
            if (/^[-\w.%+]{1,64}@(?:[A-Z0-9-]{1,63}\.){1,125}[A-Z]{2,63}$/i.test(email)){
                return true;
            } else {
                return false;
            }
        }
        
        // Validamos el campo cuando pierde el foco
        $('.mf-field').blur(function(){
            var ex = $(this).attr('id');
            var vl = $(this).val();
            switch(ex) {
                case 'mf-name': 
                console.log('mf-name')
                    verifyName(vl);
                    break;
                case 'mf-rut':
                    console.log('mf-rut')
                    verifyRut(vl);  
                    break;
                case 'mf-phone':
                    console.log('mf-phone')
                    verifyPhone(vl);
                    break;
                case 'mf-email':
                    console.log('mf-email')
                    verifyEmail(vl);
                    break;
            }
        });

    });

});
