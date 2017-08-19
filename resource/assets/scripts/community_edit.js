function submit_data() {
    showLoading();

    $.ajax({
        type: "POST",
        url: 'update',
        data: {
            kind: $("#kind").val(),
            community_id: $("#community_id").val(),
            community_idv: $("#community_idv").val(),
            community_name: $("#community_name").val(),
            city: $("#city").val(),
            region: $("#region").val(),
            builder: $("#builder").val(),
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();
            showAlert(data.err_msg);

            if (data.err_code == 0) {
                setTimeout(go_list, 700);
            } else {
                $('form').bootstrapValidator('resetForm', false);
            }
        },
        error: function () {
            hideLoading();
            showAlert(Message.SERVER_ERROR);
            $('form').bootstrapValidator('resetForm', false);
        }
    });            
}

function go_list() {
    var type = $("#type").val();
    location.href = "home.html";
}

jQuery(document).ready(function () {
    if ($("#msg_alert").html() != '') {
        setTimeout(hideAlert, 2000);
    }
    
    $("#community_idv").inputmask("9999", {
        placeholder: 'x'
    });    

    $('form').bootstrapValidator({
        feedbackIcons: {
            valid: 'has-success',
            invalid: 'has-error',
            validating: ''
        },
        fields: {
            community_name: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Community Name'
                    },
                }
            },
            city: {
                validators: {
                    notEmpty: {
                        message: 'Enter the City'
                    },
                }
            },
            region : {
                validators: {
                    notEmpty: {
                        message: 'Select Region'
                    },
                }
            }    
        }
    })
    .on('success.field.bv', function (e, data) {
        if (data.bv.isValid()) {
            data.bv.disableSubmitButtons(false);
        }
    });

    $('form').on('submit', function (e) {
        if (e.isDefaultPrevented()) {

        } else {
            e.preventDefault();
            
            var v = $("#community_idv").val();
            v = v.replace(/x/g, "").replace(/X/g, "");
            if (v=="" || v.length!=4) {
                showAlert("Enter the Community ID");
                
            } else {
                submit_data();
            }
        }
    });

});
