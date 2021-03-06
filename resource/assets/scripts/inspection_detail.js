
jQuery(document).ready(function () {
    showAlert($("#msg_alert").html());

    $("img.for-preview").on('click', function(e) {
        e.preventDefault();
        
        if ($(this).hasClass('signature')) {
            $.swipebox( [ {
                    href: $(this).attr('src')   ,
                    title : '',
            }], {
                afterOpen: function() {
                    $("#swipebox-container .slide.current").addClass('white-background');
                },
                afterClose: function() {
                    $("#swipebox-container .slide.current").removeClass('white-background');
                }
            });         
        } else if ($(this).hasClass('google-map')) {
            $.swipebox( [ {
                    href: $(this).attr('data-src')   ,
                    title : '',
            }]);         
        } else {
            $.swipebox( [ {
                    href: $(this).attr('src')   ,
                    title : '',
            }]);         
        }
    });


    $("#btn_report").on('click', function(e) {
        e.preventDefault();
        $.fileDownload($("#basePath").val()+"api/export/inspection?id="+$(this).attr('data-id')+"&type=full");
    });

    $("#btn_report_pass").on('click', function(e) {
        e.preventDefault();
        $.fileDownload($("#basePath").val()+"api/export/inspection?id="+$(this).attr('data-id')+"&type=pass");
    });
    
});
