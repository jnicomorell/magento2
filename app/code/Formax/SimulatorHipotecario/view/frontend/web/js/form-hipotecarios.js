require(['jquery', 'jquery-rut', 'format-currency-min'], function($) {

    $(document).ready(function(){

         // Gatillamos funcion clic en btn enviar
         $('#submit-mf-form').click(function(){
            var name  = $('#mf-name').val(),
                lname = $('#mf-lname').val(),
                rut   = $('#mf-rut').val(),
                code  = $('#mf-code').attr('data-value'),
                phone = $('#mf-phone'),
                email = $('#mf-email').val(),
                salary = $('#mf-salary').val(),
                amount = $('#mf-amount').val(),
                pie = $('#mf-pie').val(),
                plazo  = $('#plazo').val();
                
            var comment = `Plazo {${plazo}}, tengo el 20% de financiamiento {si + ${$('#mf-pie').val()}}, no {No, tengo + ${$('#mf-pie').val()}}`
            var isValidName  = verifyName(name),
                isValidLname = verifyLname(lname),
                isValidRut   = verifyRut(rut),
                isValidPhone = verifyPhone(phone),
                isValidEmail = verifyEmail(email),
                isValidSalary = verifyAmount(salary, 'mf-salary'),
                isValidAmount = verifyAmount(amount, 'mf-amount')
                isValidPie = verifyPie(pie, 'mf-pie')
                
                var $checkStatus = $('#checkstatus').val();
                if($checkStatus == 'true'){
                   var isValidPie = verifyPie(pie, 'mf-pie')
                }else{
                    var isValidPie = true
                }

               
         
            if (isValidName && isValidLname &&  isValidPie && isValidRut && isValidAmount && isValidPhone && isValidEmail && isValidSalary && isValidPie) {
                $('#submit-mf-form').prop('disabled', true);
                $('.loader').show()
                // Cuando el socio hace click en Simular
                window.dataLayer = window.dataLayer || [];
                dataLayer.push({
                    'event': 'trigger_event',
                    'monto': amount,
                    'cuotas': plazo,
                    'event-config': {
                        'eve-acc' : '/simuladorhipotecario/datos-personales',
                        'eve-cat' : 'Click',
                        'eve-lab' : 'Simular'
                    }
                });
                $('#hipotecarioform').submit();
            }
        });

        $('.solicitar_credito_show').click( function(event) {
            event.preventDefault();

            let index = $(this).attr('data-index');
            let phone = $(`#mf-phone-${index}`);
            let code  = $(`[name=code-${index}]`);

            if ( verifyPhone(phone) ) {
                $('#phone').val(phone.val());
                $('#code').val(code.val());

                // Cuando el socio haga click en Contáctenme
                dataLayer.push({
                    'event': 'trigger_event',
                    'event-config': {
                        'eve-acc': '/simuladorhipotecario/contacto',
                        'eve-cat': 'Click',
                        'eve-lab': 'Contactenme'
                    }
                });

                $(this).addClass('disabled');
                $("#hipotecarioformsolicitud").submit();
            }

        })
        
        $('#mf-name, #mf-lname').on('keyup keypress paste',function(e){
            $(this).val($(this).val().replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚ\s-]+$/, ""));
        });

        // Validamos el ingreso de caracteres para el campo rut
        $('#mf-rut').on('keyup keypress paste',function(e){
            $(this).val($(this).val().replace(/[^0-9kK\-\.]+$/, ""));
        });

        $("#mf-rut").rut({formatOn: 'keyup keypress blur'});

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


        function verifyName(name){
            if($.trim(name) == ''){
                $('.name-error-msg').remove();
                $('#mf-name').parents('.control').append('<div for="name" generated="true" class="mage-error name-error-msg" >Por favor, rellene este campo.</div>');
                return false;
            } else {
                $('.name-error-msg').remove();
                return true;
            }
        }

        function verifyLname(lname){
            if($.trim(lname) == ''){
                $('.lname-error-msg').remove();
                $('#mf-lname').parents('.control').append('<div for="name" generated="true" class="mage-error lname-error-msg" >Por favor, rellene este campo.</div>');
                return false;
            } else {
                $('.lname-error-msg').remove();
                return true;
            }
        }

        function verifyRut(rut){
            if(!$.validateRut(rut, null, { minimumLength: 7 }) || !validateRutVeracity(rut) || rut == ''){
                $('.rut-error-msg').remove();
                $('#mf-rut').parents('.control').append('<div for="rut" generated="true" class="mage-error rut-error-msg" >Ingrese un rut válido.</div>');
                return false;
            } else {
                $('.rut-error-msg').remove();
                return true;
            }
        }

        function verifyPhone(el){
            var length  = el.val().length,
                max = el.attr("maxlength");
            if (length != max) {
                $(".phone-error-msg").remove();
                el.parents('.control').append('<div for="phone" generated="true" class="mage-error phone-error-msg" >Ingrese un teléfono válido.</div>');
                return false;
            } else {
                $(".phone-error-msg").remove();
                return true;
            }
        }

        function verifyEmail(email){
            if (!validateEmailFormat(email) || email == '') {
                $(".email-error-msg").remove();
                $('#mf-email').parents('.control').append('<div for="email" generated="true" class="mage-error email-error-msg" >Ingrese un email válido.</div>');
                return false;
            } else {
                $(".email-error-msg").remove();
                return true;
            }
        }

        function verifyAmount(amount, id){
            
            var num = amount.replace(/[^\d]/g, '');
            var error = id + '-error-msg';
            var minamount = 750*$(".ufvalue").val();
            var minamountreq = minamount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            if (num) {
                $('#'+id).val(num);
                $('#'+id).formatCurrency();
            }
            if (num <= 0) {
                $("."+error).remove();
                $('#'+id).parents('.control').append('<div for="subject" generated="true" class="mage-error ' + error +'" >Por favor, ingrese un monto.</div>');
                return false;
            }else if (id == 'mf-amount' && num < minamount) {
                $("."+error).remove();
                $('#'+id).parents('.control').append('<div for="subject" generated="true" class="mage-error ' + error +'" >El precio de venta no puede ser inferior a $'+minamountreq+'</div>');
                return false;
            } else {
                $("."+error).remove();
                return true;
            }
        }

        function verifyPie(amount, id){
            if ($('#moreno').prop("checked")) {
                var amount = $('#mf-pie').val()
                var num = amount.replace(/[^\d]/g, '');
                var error = id + '-error-msg';
                var cleanamount = $('#mf-amount').val().replace(/[^\d]/g, '');
                var minamountpie = Number(cleanamount)-Number(num);
                var minufdif = minamountpie/$(".ufvalue").val();
                var minamount = 350*$(".ufvalue").val();
    
                var minamountreq = minamount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                if (num <= 0) {
                    $("."+error).remove();
                    $('#'+id).parents('.control').append('<div for="subject" generated="true" class="mage-error ' + error +'" >Por favor, ingrese un monto.</div>');
                    return false;
                }else if (Number(num) >= Number(cleanamount)) {
                    $("."+error).remove();
                    $('#'+id).parents('.control').append('<div for="subject" generated="true" class="mage-error ' + error +'" >Pie no debe ser mayor al precio de venta</div>');
                    return false;
                }else if (minufdif <= 300) {
                    $("."+error).remove();
                    $('#'+id).parents('.control').append('<div for="subject" generated="true" class="mage-error ' + error +'" >Monto a financiar debe ser igual o mayor a $'+minamountreq+'</div>');
                    return false;
                } else {
                    $("."+error).remove();
                    return true;
                }
            }
            return true;
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
        function ifPie(){
            var $checkStatus = $('#checkstatus').val()
            if($checkStatus == 'true'){
                var vl = $('#mf-pie').val();
                verifyAmount(vl, 'mf-pie');
            }
        }
        // Validamos el campo cuando pierde el foco
        $('.mf-field').blur(function(){
            var el = $(this);
            var ex = el.attr('id');
            var vl = el.val();
            switch(ex) {
                case 'mf-name':
                    verifyName(vl);
                    break;
                case 'mf-lname':
                    verifyLname(vl);
                    break;
                case 'mf-rut':
                    verifyRut(vl);
                    break;
                case 'mf-phone':
                    verifyPhone(el);
                    break;
                case 'mf-email':
                    verifyEmail(vl);
                    break;
                case 'mf-salary':
                    verifyAmount(vl, 'mf-salary');
                    break;
                case 'mf-pie':
                    if ($('#moreno').prop("checked")) {
                        ifPie()
                        verifyPie(vl, 'mf-pie')
                    }
                    break;
                case 'mf-amount':
                    verifyAmount(vl, 'mf-amount');
                    if ($('#moreno').prop("checked")) {
                        var amountpie = $('#mf-pie').val()
                        var pie = amountpie.replace(/[^\d]/g, '');
                        if(pie>0){
                            $('#mf-pie').trigger('blur')
                        }
                    }
                    break;
              }
        });

    });

});
