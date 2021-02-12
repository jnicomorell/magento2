require([
        'jquery',
        'mage/translate',
        'jquery/validate'
    ],
    function($) {


        $.validator.addMethod(
            'validate-initial-date',
            function(v) {
                var initialDate = v;
                var currentDate = getActualFullDate();
                if (initialDate >= currentDate) {
                    return true;
                }
            }, $.mage.__('You must select a greater date than today.'));
        $.validator.addMethod(
            'validate-ending-date',
            function(v) {
                var endingDate = v;
                var initialDate = $('#header_coopeuch_alert_widget_initial_date').val();

                if (endingDate > initialDate) {
                    return true;
                }

            }, $.mage.__('Ending date must be greater than initial date.'));
    }
);

function getActualFullDate() {
    var d = new Date();
    var day = addZero(d.getDate());
    var month = addZero(d.getMonth() + 1);
    var year = addZero(d.getFullYear());
    return year + "-" + month + "-" + day;
}

function addZero(i) {
    if (i < 10) {
        i = "0" + i;
    }
    return i;
}