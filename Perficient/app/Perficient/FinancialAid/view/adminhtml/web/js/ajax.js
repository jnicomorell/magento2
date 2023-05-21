require(
    [
        'jquery',
        'mage/translate'
    ],
    function ($,_translate) {
        $(document).on("change", "select.status_select", function(){
            var url = $(this).attr("data-save-url");
            var id = $(this).attr("data-id");
            var status = $(this).val();
            $.ajax({
                url: url,
                showLoader: true,
                type: 'post',
                data: {id: id,status:status}
            }).then((function (response) {
            }).bind(this));
    
        })
    }
);