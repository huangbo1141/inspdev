function submit_data() {
    showLoading();
    
    var fee = [];
    $("#fees tbody tr").each(function(index, row) {
        fee[index] = {
            type: $(this).attr('data-type'),
            inspection_fee: $(this).find('.inspection_fee').val(),
            re_inspection_fee: $(this).find('.re_inspection_fee').val(),
        };
    });

    $.ajax({
        type: "POST",
        url: 'update',
        data: {
            kind: $("#kind").val(),
            builder_id: $("#builder_id").val(),
            name: $("#builder_name").val(),
            contact: $("#contact").val(),
            address: $("#address").val(),
            city: $("#city").val(),
            state: $("#state").val(),
            zip: $("#zip").val(),
            phone: $("#phone").val(),
            email: $("#email").val(),
            fee: JSON.stringify(fee),
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
    location.href = "home.html";
}

jQuery(document).ready(function () {
    if ($("#msg_alert").html() != '') {
        setTimeout(hideAlert, 2000);
    }

    $('form').bootstrapValidator({
        feedbackIcons: {
            valid: 'has-success',
            invalid: 'has-error',
            validating: ''
        },
        fields: {
            builder_name: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Builder Name'
                    },
                }
            },
            contact: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Contact'
                    },
                }
            },
            address: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Address'
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
            state: {
                validators: {
                    notEmpty: {
                        message: 'Enter the State'
                    },
                }
            },
            phone: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Phone Number'
                    },
                }
            },
            email: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Email'
                    },
                    emailAddress : {
                        message: 'Enter valid email'
                    }
                }
            },
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
            submit_data();
        }
    });

});
