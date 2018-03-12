
function submit_data() {
    var report_keep_day = $('#report_keep_day').val();
    if (report_keep_day=='') {
        showAlert("Please Enter Keep Days for PDF Report!");
        return false;
    }
    
    if (isNaN(report_keep_day)) {
        showAlert("Please Enter Correct Keep Days for PDF Report!");
        return false;
    }
    
    var reinspection_allowed = $('#reinspection_allowed').val();
    if (reinspection_allowed=='') {
        showAlert("Please Enter Number of Re-Inspections Allowed");
        return false;
    }
    
    if (isNaN(reinspection_allowed)) {
        showAlert("Please Enter Correct Re-Inspections Allowed");
        return false;
    }
    
    showLoading();

    $.ajax({
        type: "POST",
        url: 'update_configuration',
        data: {
            report_keep_day: report_keep_day,
            reinspection_allowed:reinspection_allowed,
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();

            if (data.code == 0) {
                showAlert("Successfully Updated!");
            } else {
                showAlert(data.message);
            }
        },
        error: function () {
            hideLoading();
            showAlert(Message.SERVER_ERROR);
        }
    });            
}

jQuery(document).ready(function () {
    if ($("#msg_alert").html() != '') {
        setTimeout(hideAlert, 2000);
    }
    
    $(".btn-submit").on('click', function(e) {
        e.preventDefault();
        
        submit_data();
    });

});
