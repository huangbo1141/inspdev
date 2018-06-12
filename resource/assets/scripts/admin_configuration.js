
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
    
    var twilio_sid = $('#twilio_sid').val();
    if (twilio_sid=='') {
        showAlert("Please Enter Sid");
        return false;
    }
    
    
    var twilio_token = $('#twilio_token').val();
    if (twilio_token=='') {
        showAlert("Please Enter Token");
        return false;
    }
    
    
    var twilio_phone1 = $('#twilio_phone1').val();
    if (twilio_phone1=='') {
        showAlert("Please Enter PhoneNumber");
        return false;
    }
    
    
    var twilio_reply_text = $('#twilio_reply_text').val();
    if (twilio_reply_text=='') {
        showAlert("Please Enter Reply Text");
        return false;
    }
    
    
    var twilio_send_text = $('#twilio_send_text').val();
    if (twilio_send_text=='') {
        showAlert("Please Enter Send Text");
        return false;
    }
    
    var checklist_online_link = $('#checklist_online_link').val();
    if (checklist_online_link=='') {
        showAlert("Please Enter Embeded Link for CheckList");
        return false;
    }
    
    showLoading();

    $.ajax({
        type: "POST",
        url: 'update_configuration',
        data: {
            report_keep_day: report_keep_day,
            reinspection_allowed:reinspection_allowed,
            twilio_sid:twilio_sid,
            twilio_token:twilio_token,
            twilio_phone1:twilio_phone1,
            twilio_reply_text:twilio_reply_text,
            twilio_send_text:twilio_send_text,
            checklist_online_link:checklist_online_link,
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
