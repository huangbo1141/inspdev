function submit_data() {
    showLoading();

    var k = $("#kind").val();
    var data = {
            kind: k,
            user_id: $("#user_id").val(),
    };

    if (k=='add' || k=='profile') {
        data.first_name = $("#first_name").val();
        data.last_name = $("#last_name").val();
        data.email = $("#email").val();
        data.phone_number = $("#phone_number").val();
        data.address = $("#address").val();
        data.fee = $("#inspector_fee").val();
    }
    
    if (k=='add' || k=='password') {            
        data.password = $("#password").val();
    }
    

    $.ajax({
        type: "POST",
        url: 'update',
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
    location.href = "inspectors.html";
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
//            address: {
//                validators: {
//                    notEmpty: {
//                        message: 'Enter the address'
//                    },
//                }
//            },
            phone_number: {
                validators: {
                    notEmpty: {
                        message: 'Enter the phone number'
                    },
                }
            },
            inspector_fee: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Inspector Fee'
                    },
                    numeric: {
                        message: 'Enter the Number',
                    },
                    greaterThan: {
                        value: 0.0,
                        message: 'Enter the Number greater than 0.00',
                    },
                }
            },
        };        
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

});
