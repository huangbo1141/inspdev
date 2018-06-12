function init() {
    if ($("#msg_alert").html() != '') {
        setTimeout(hideAlert, 2000);
    }
}

function scheduling() {
    location.href = $("#basePath").val() + "scheduling/energy";
}
function check_job_number_for_pulte_duct() {
    var v = $("#job_number").val();
    v = v.replace(/_/g, ""); //.replace(/X/g, "");
    showLoading();
    $.ajax({
        type: "POST",
        url: 'check_job_number_for_pulte_duct',
        data: {
            job_number: v
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();
            if (data.exist_ins_inspection == 1) {
                submit_data();
//                showAlert("submit_data");
            } else {
                showAlert("Building not in Database");
                
            }
        },
        error: function () {

            hideLoading();
            showAlert(Message.SERVER_ERROR);
            //                $('form').bootstrapValidator('resetForm', false);
        }
    });
}
function submit_data() {

    //var p = $("input[name='field_manager']").val();
    var field_manager = $("#field_manager option:selected").text();
    var field_manager_id = $("#field_manager option:selected").val();
    var data = {
        id: $("#requested_id").val(),
        manager_id: field_manager_id,

        date_requested: $("#date_requested").val(),
        job_number: $("#job_number").val(),
        lot: $("#lot").val(),

        community: $("#community").val(),
        address: $("#address").val(),
        city: $("#city").val(),
        area: $("#area").val(),
        volume: $("#volume").val(),
        wall_area: $("#wall_area").val(),
        ceiling_area: $("#ceiling_area").val(),

        design_location: $("#design_location").val(),
        field_manager: field_manager,
        qn: $("#qn").val(),

        document_person: $("#document_person").val(),
        category: 4
    }
    var req_id = $("#requested_id").val();
    var fname = "update_duct_leakage_inspection_requested";
    if (req_id.length > 0) {
        fname = "update_duct_leakage_inspection_requested2";
    } else {
        fname = "update_duct_leakage_inspection_requested";
    }

    // return;

    showLoading();
    $.ajax({
        type: "POST",
        url: fname,
        data: data,
        dataType: 'json',
        success: function (data) {
            hideLoading();
            showAlert(data.err_msg);

            if (data.err_code == 0) {
                setTimeout(scheduling, 700);
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

function check_job_number() {
    var v = $("#job_number").val();
    v = v.replace(/_/g, ""); //.replace(/X/g, "");
    if (v == "" || v.length < 9) {

    } else {
        showLoading();
        $.ajax({
            type: "POST",
            url: 'get_field_manager_list_for_job_number',
            data: {
                job_number: v
            },
            dataType: 'json',
            success: function (data) {

                if (data.err_code == 0) {
                    $("#field_manager").html("");
                    $("#field_manager").append('<option value="0">NONE</option>');

                    if (data.fm.has == 1) {
                        $.each(data.fm.list, function (index, row) {
                            $("#field_manager").append('<option ' + (data.fm.manager_id == row.id ? "selected" : "") + ' value="' + row.id + '">' + row.first_name + ' ' + row.last_name + '</option>');
                        });
                    }
                    if(data.inspection != null){
                        $("#lot").val(data.inspection.lot==null?"":data.inspection.lot);
                        $("#community").val(data.inspection.community==null?"":data.inspection.community);
                        $("#address").val(data.inspection.address==null?"":data.inspection.address);
                        $("#city").val(data.inspection.city==null?"":data.inspection.city);
                    }
                    if(data.building != null) {
                        $("#address").val(data.building.address==null?"":data.building.address);
                    }
                } else {
                    $("#field_manager").html("");
                    $("#field_manager").append('<option value="0">NONE</option>');
                    showAlert("Job Number Not Found");
                }

                hideLoading();
            },
            error: function () {

                hideLoading();
                showAlert(Message.SERVER_ERROR);
                //                $('form').bootstrapValidator('resetForm', false);
            }
        });
    }
}

jQuery(document).ready(function () {
    $('.date-picker').datepicker({
        autoclose: true,
        format: 'yyyy-mm-dd'
    });

    $('form').bootstrapValidator({
        feedbackIcons: {
            valid: 'has-success',
            invalid: 'has-error',
            validating: ''
        },
        fields: {
            date_requested: {
                validators: {
                    notEmpty: {
                        message: 'Select the date'
                    },
                }
            },
            job_number: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Job Number'
                    },
                    greaterThan: {
                        value: 1,
                        message: 'Enter the Number greater than 1',
                    }
                }
            },
            lot: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Lot'
                    },
                    numeric: {
                        message: 'Enter the Number',
                    },
                    greaterThan: {
                        value: 1,
                        message: 'Enter the Number greater than 1',
                    }
                }
            },
            community: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Community'
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
            area: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Area'
                    },
                    integer: {
                        message: 'Enter the Number',
                    },
                    greaterThan: {
                        value: 1,
                        message: 'Enter the Number greater than 1',
                    }
                }
            },
            volume: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Volume'
                    },
                    integer: {
                        message: 'Enter the Number',
                    },
                    greaterThan: {
                        value: 1,
                        message: 'Enter the Number greater than 1',
                    }
                }
            },
            wall_area: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Wall Area'
                    },
                    integer: {
                        message: 'Enter the Number',
                    },
                    greaterThan: {
                        value: 1,
                        message: 'Enter the Number greater than 1',
                    }
                }
            },
            ceiling_area: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Ceiling Area'
                    },
                    integer: {
                        message: 'Enter the Number',
                    },
                    greaterThan: {
                        value: 1,
                        message: 'Enter the Number greater than 1',
                    }
                }
            },
            design_location: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Design Location'
                    },
                }
            },
            // field_manager: {
            //   validators: {
            //     notEmpty: {
            //       message: 'Enter the Field Manager Email Address'
            //     },
            //     emailAddress: {
            //       message: 'Enter the Valid Email Address'
            //     },
            //   }
            // },
            // browser: {
            //   validators: {
            //     notEmpty: {
            //       message: 'Enter the Field Manager Email Address'
            //     },
            //     emailAddress: {
            //       message: 'Enter the Valid Email Address'
            //     },
            //   }
            // },
            qn: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Volume'
                    },
                    numeric: {
                        message: 'Enter the Number',
                    },
                    greaterThan: {
                        value: 0.01,
                        message: 'Enter the Number greater than 0.01',
                    },
                    lessThan: {
                        value: 0.99,
                        message: 'Enter the Number less than 0.99',
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

            var v = $("#job_number").val();
            var addr = $("#address").val();
            var lot = $("#lot").val();

            bootbox.confirm({
                title: 'Are you sure?',
                message: 'An Inspection for this lot (' + lot + ') for this address (' + addr + ') for this job number(' + v + ') will be requested.<br>Please confirm:',
                buttons: {
                    'cancel': {
                        label: 'No',
                        className: 'btn-default'
                    },
                    'confirm': {
                        label: 'Yes',
                        className: 'btn-danger'
                    }
                },
                callback: function (result) {
                    if (result) {
                        check_job_number_for_pulte_duct();
                    } else {
                        $('form').bootstrapValidator('resetForm', false);
                    }
                }
            });
        }
    });

    $('#job_number').change(function (e) {
        check_job_number();
    });

    init();
});
