require(['jquery','jquery-ui-modules/datepicker'],
    function($,ui) {
        "use strict";
        let globalFrom = false;
        let globalTo = false;
        let saveButton = $('.ceg-export-index #save');

        $(saveButton).on('click', function () {
            setTimeout(function (){
                $('body').trigger('processStop');
            },2500);
        });

        $(document).on('change', $("input[type='text']"), function() {
            var fromInput = $('.ceg-export-index input[name="quote_from"]');
            var toInput = $('.ceg-export-index input[name="quote_to"]');
            var dateFrom = fromInput.datepicker('getDate');
            var dateTo = toInput.datepicker('getDate');

            if (dateFrom && !globalFrom) {
                globalFrom = true;
                fromInput.val($.datepicker.formatDate('d/m/yy', new Date(dateFrom)))
            }
            if (dateTo && !globalTo) {
                globalTo = true;
                toInput.val($.datepicker.formatDate('d/m/yy', new Date(dateTo)))
            }

            if ((dateFrom != null && dateFrom !== 'undefined') && (dateTo != null && dateFrom !== 'undefined')) {
                dateValidation(dateFrom, dateTo);
            }

        })

        function dateValidation(dateFrom, dateTo) {
            var errorMessage = $(".date-validation-message").remove();
            if (dateFrom > dateTo) {
                saveButton.prop('disabled', true);
                $(".quote-to-validation").find(".admin__field-control").append(
                    "<span class='admin__field-error date-validation-message'>Debe ingresar una fecha de fin superior o igual a la de inicio</span>")
            } else {
                saveButton.prop('disabled', false);
                errorMessage.remove();
            }
        }
    }
);
