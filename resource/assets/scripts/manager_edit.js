function submit_data() {
    showLoading();

    var t = $("#type").val();
    var k = $("#kind").val();
    var data = {
        type: t,
        kind: k,
        user_id: $("#user_id").val(),
    };

    if (k=='add' || k=='profile') {
        data.first_name = $("#first_name").val();
        data.last_name = $("#last_name").val();
        data.address = $("#address").val();
        data.email = $("#email").val();
        data.cell_phone = $("#cell_phone").val();
        data.other_phone = $("#other_phone").val();

        if (t=='2' || t=='3' || t=='4') {
            data.region=$("#region").val();
            data.builder=$("#builder").val();
        }
    }

    if (k=='add' || k=='password') {            
        data.password = $("#password").val();
    }

    $.ajax({
        type: "POST",
        url: $("#basePath").val() + 'manager/update_user.html',
        data: data,
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
    if (type=='1') {
        location.href = "admin.html";
    }
    if (type=='2') {
        location.href = "field.html";
    }
    if (type=='3') {
        location.href = "construction.html";
    }
    if (type=='4') {
        location.href = "scheduler.html";
    }
}

jQuery(document).ready(function () {
    if ($("#msg_alert").html() != '') {
        setTimeout(hideAlert, 2000);
    }

    var validation_fields = {};
    
    if ($("#kind").val() == 'add' || $("#kind").val() == 'profile') {
        validation_fields = {
            first_name: {
                validators: {
                    notEmpty: {
                        message: 'Enter the first name'
                    },
                }
            },
            last_name: {
                validators: {
                    notEmpty: {
                        message: 'Enter the last name'
                    },
                }
            },
            email: {
                validators: {
                    notEmpty: {
                        message: 'Enter the email'
                    },
                    emailAddress: {
                        message: 'Enter valid email'
                    }
                }
            },
            address: {
                validators: {
                    notEmpty: {
                        message: 'Enter the address'
                    },
                }
            },
            cell_phone: {
                validators: {
                    notEmpty: {
                        message: 'Enter the phone number'
                    },
                }
            },
        };        
        
        if ($("#type").val() == '2' || $("#type").val() == '3' || $("#type").val() == '4') {
            validation_fields.region = {
                validators: {
                    notEmpty: {
                        message: 'Select Region'
                    },
                }
            };
        }
    }

    if ($("#kind").val() == 'add' || $("#kind").val() == 'password') {
        validation_fields.password = {
            validators: {
                notEmpty: {
                    message: 'Enter the password'
                },
            }
        };

        validation_fields.confirm_password = {
            validators: {
                notEmpty: {
                    message: 'Confirm the password'
                },
                identical: {
                    field: 'password',
                    message: 'Enter correct password'
                }
            }
        };
    }
    

    $('form').bootstrapValidator({
        feedbackIcons: {
            valid: 'has-success',
            invalid: 'has-error',
            validating: ''
        },
        fields: validation_fields
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
            submit_data();
        }
    });


    if ($("#type").val()=="1") {
    } else {
        $('#region').selectpicker({    
            liveSearch: false,
        });
    }
    
});
