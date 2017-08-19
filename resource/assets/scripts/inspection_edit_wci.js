var preventRunDefault = false;

function init() {
    calculate_result();
}

function empty_result(type) {
    if (type==1) {
        $("#qn_out").html("");
        $("#result_duct_leakage").removeClass('label-default').removeClass('label-success').removeClass('label-warning').removeClass('label-danger');
        $("#result_duct_leakage").addClass('label-default');
        $("#result_duct_leakage").html("None");
    }
    if (type==2) {
        $("#ach50").html("");
        $("#result_envelop_leakage").removeClass('label-default').removeClass('label-success').removeClass('label-warning').removeClass('label-danger');
        $("#result_envelop_leakage").addClass('label-default');
        $("#result_envelop_leakage").html("None");
    }
}

function calculate_result() {
    var qn = parseFloat($("#qn").html());
    var area = parseFloat($("#area").html());
    var volume = parseFloat($("#volume").html());
    var wall_area = parseFloat($("#wall_area").html());
    var ceiling_area = parseFloat($("#ceiling_area").html());
    
    var unit=0;
    var unit1=0;
    var unit2=0;
    var unit3=0;
    var unit4=0;
    
    var v = $("#unit1_supply").val();
    if (!isNaN(v)) {
        unit1 += parseFloat(v);
    }
    v = $("#unit1_return").val();
    if (!isNaN(v)) {
        unit1 += parseFloat(v);
    }
    
    v = $("#unit2_supply").val();
    if (v!="" && !isNaN(v)) {
        unit2 += parseFloat(v);
    }
    v = $("#unit2_return").val();
    if (v!="" && !isNaN(v)) {
        unit2 += parseFloat(v);
    }

    v = $("#unit3_supply").val();
    if (v!="" && !isNaN(v)) {
        unit3 += parseFloat(v);
    }
    v = $("#unit3_return").val();
    if (v!="" && !isNaN(v)) {
        unit3 += parseFloat(v);
    }
    
    v = $("#unit4_supply").val();
    if (v!="" && !isNaN(v)) {
        unit4 += parseFloat(v);
    }
    v = $("#unit4_return").val();
    if (v!="" && !isNaN(v)) {
        unit4 += parseFloat(v);
    }
    
    unit = unit1+unit2+unit3+unit4;
    if (unit==0) {
        empty_result(1);
    } else {
        var qn_out = (unit/2)/area;
        $("#qn_out").html(qn_out.toFixed(2));

        $("#result_duct_leakage").removeClass('label-default').removeClass('label-success').removeClass('label-warning').removeClass('label-danger');
        if (qn_out<=qn) {
            $("#result_duct_leakage").addClass('label-success');
            $("#result_duct_leakage").html("Pass");
        } else {
            $("#result_duct_leakage").addClass('label-danger');
            $("#result_duct_leakage").html("Fail");
        }
    }
    
    var house_pressure = 0;
    v = $("#house_pressure").val();
    if (v!="" && !isNaN(v)) {
        house_pressure = parseFloat(v);
    }
    
    var flow = 0;
    v = $("#flow").val();
    if (v!="" && !isNaN(v)) {
        flow = parseFloat(v);
    }

    if (house_pressure>0 && flow>0) {
        var c = flow / Math.pow(house_pressure, 0.65);
        var cfm = c * 12.7154;
        var ach50 = (cfm * 60) / volume;
        $("#ach50").html(ach50.toFixed(2));
        
        $("#result_envelop_leakage").removeClass('label-default').removeClass('label-success').removeClass('label-warning').removeClass('label-danger');
        if (ach50>7) {
            $("#result_envelop_leakage").addClass('label-danger');
            $("#result_envelop_leakage").html("Fail");
        } else if (ach50<=3) {
            $("#result_envelop_leakage").addClass('label-warning');
            $("#result_envelop_leakage").html("Pass (Mechanical Ventilation Required)");
        } else {
            $("#result_envelop_leakage").addClass('label-success');
            $("#result_envelop_leakage").html("Pass");
        }
    } else {
        empty_result(2);
    }
}

function submit_data() {
    showLoading();
    
    var job_number = $("#job_number").val();
    var lot = $("#lot").val();
    var community = $("#community").val();
    var address = $("#address").val();
    var house_ready = $("#house_ready").val();

    var front_picture = $("#front_image").attr('src');
    var testing_setup = $("#testing_image").attr('src');
    var manometer = $("#manometer_image").attr('src');

    var overall_comment = $("#overall_comment").val();

    var qn = parseFloat($("#qn").html());
    var area = parseFloat($("#area").html());
    var volume = parseFloat($("#volume").html());
    var wall_area = parseFloat($("#wall_area").html());
    var ceiling_area = parseFloat($("#ceiling_area").html());
    
    var unit=0;
    var unit1=0;
    var unit2=0;
    var unit3=0;
    var unit4=0;
    
    var v = $("#unit1_supply").val();
    if (!isNaN(v)) {
        unit1 += parseFloat(v);
    }
    v = $("#unit1_return").val();
    if (!isNaN(v)) {
        unit1 += parseFloat(v);
    }
    
    v = $("#unit2_supply").val();
    if (v!="" && !isNaN(v)) {
        unit2 += parseFloat(v);
    }
    v = $("#unit2_return").val();
    if (v!="" && !isNaN(v)) {
        unit2 += parseFloat(v);
    }

    v = $("#unit3_supply").val();
    if (v!="" && !isNaN(v)) {
        unit3 += parseFloat(v);
    }
    v = $("#unit3_return").val();
    if (v!="" && !isNaN(v)) {
        unit3 += parseFloat(v);
    }
    
    v = $("#unit4_supply").val();
    if (v!="" && !isNaN(v)) {
        unit4 += parseFloat(v);
    }
    v = $("#unit4_return").val();
    if (v!="" && !isNaN(v)) {
        unit4 += parseFloat(v);
    }

    var qn_out=0;
    var result_duct_leakage = 0;
    unit = unit1+unit2+unit3+unit4;
    if (unit==0) {
    } else {
        qn_out = (unit/2)/area;

        if (qn_out<=qn) {
            result_duct_leakage = 1;
        } else {
            result_duct_leakage = 3;
        }
    }
    
    var house_pressure = 0;
    v = $("#house_pressure").val();
    if (v!="" && !isNaN(v)) {
        house_pressure = parseFloat(v);
    }
    
    var flow = 0;
    v = $("#flow").val();
    if (v!="" && !isNaN(v)) {
        flow = parseFloat(v);
    }

    var ach50=0;
    var result_envelop_leakage = 0;
    if (house_pressure>0 && flow>0) {
        var c = flow / Math.pow(house_pressure, 0.65);
        var cfm = c * 12.7154;
        ach50 = (cfm * 60) / volume;
        
        if (ach50>7) {
            result_envelop_leakage = 3;
        } else if (ach50<=3) {
            result_envelop_leakage = 2;
        } else {
            result_envelop_leakage = 1;
        }
    }
    
    $.ajax({
        type: "POST",
        url: 'update_wci',
        data: { 
            inspection_id: $("#inspection_id").val() ,
            
            address: address,
            house_ready: house_ready,
            
            front_picture: front_picture,
            testing_setup: testing_setup,
            manometer: manometer,
            
            comment: overall_comment,
            
            unit1_supply: $("#unit1_supply").val(),
            unit1_return: $("#unit1_return").val(),
            unit2_supply: $("#unit2_supply").val(),
            unit2_return: $("#unit2_return").val(),
            unit3_supply: $("#unit3_supply").val(),
            unit3_return: $("#unit3_return").val(),
            unit4_supply: $("#unit4_supply").val(),
            unit5_return: $("#unit4_return").val(),
            
            house_pressure: house_pressure,
            flow: flow,
            
            qn_out: qn_out,
            ach50: ach50,
            
            result_duct_leakage: result_duct_leakage,
            result_envelop_leakage: result_envelop_leakage,
            
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();
            
            if (data.err_code == 0) {
                showAlert("Successfully updated!");
                setTimeout(go_list, 1000);
            } else {
                showAlert("Failed to update!");
            }
        },
        error: function () {
            hideLoading();
            showAlert(Message.SERVER_ERROR);
        }
    });
}

function go_list() {
    location.href = "energy.html";
}


jQuery(document).ready(function () {
    showAlert($("#msg_alert").html());

    $(".page_content").on('click', 'img.for-preview', function(e) {
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
            if ($(this).hasClass('thumb-image')) {
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
        }
    });

    $('#takeFileUpload_front').fileupload({
        dataType: 'json',
        formData: {
        },
        beforeSend: function () {
            showLoading();
        },
        done: function (e, data) {
            hideLoading();
            if (data.result.code == 0) {
                $("#front_image").attr('src', data.result.url);
            } else {
                showAlert("Failed to upload!");
            }
        },
        progressall: function (e, data) {
        }, 
        fail: function(e) {
            hideLoading();
            showAlert(Message.SERVER_ERROR);
        }
    });
    
    $("#front_btn_add").on('click', function(e) {
        e.preventDefault();
        $('#takeFileUpload_front').trigger('click');
    });
    
    $("#front_btn_delete").on('click', function(e) {
        e.preventDefault();
        $("#front_image").attr('src', '');
    });

    
    $('#takeFileUpload_testing').fileupload({
        dataType: 'json',
        formData: {
        },
        beforeSend: function () {
            showLoading();
        },
        done: function (e, data) {
            hideLoading();
            if (data.result.code == 0) {
                $("#testing_image").attr('src', data.result.url);
            } else {
                showAlert("Failed to upload!");
            }
        },
        progressall: function (e, data) {
        }, 
        fail: function(e) {
            hideLoading();
            showAlert(Message.SERVER_ERROR);
        }
    });
    
    $("#testing_btn_add").on('click', function(e) {
        e.preventDefault();
        $('#takeFileUpload_testing').trigger('click');
    });
    
    $("#testing_btn_delete").on('click', function(e) {
        e.preventDefault();
        $("#testing_image").attr('src', '');
    });

    
    $('#takeFileUpload_manometer').fileupload({
        dataType: 'json',
        formData: {
        },
        beforeSend: function () {
            showLoading();
        },
        done: function (e, data) {
            hideLoading();
            if (data.result.code == 0) {
                $("#manometer_image").attr('src', data.result.url);
            } else {
                showAlert("Failed to upload!");
            }
        },
        progressall: function (e, data) {
        }, 
        fail: function(e) {
            hideLoading();
            showAlert(Message.SERVER_ERROR);
        }
    });
    
    $("#manometer_btn_add").on('click', function(e) {
        e.preventDefault();
        $('#takeFileUpload_manometer').trigger('click');
    });
    
    $("#manometer_btn_delete").on('click', function(e) {
        e.preventDefault();
        $("#manometer_image").attr('src', '');
    });

    
    $('form').bootstrapValidator({
        feedbackIcons: {
            valid: 'has-success',
            invalid: 'has-error',
            validating: ''
        },
        fields: {
            job_number: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Job Number'
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
            lot: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Lot'
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
            unit1_supply: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Supply'
                    },
                    integer: {
                        message: 'Enter the Number',
                    },
                    greaterThan: {
                        value: 0,
                        message: 'Enter the Number greater than 0',
                    },
                    lessThan: {
                        value: 999,
                        message: 'Enter the Number less than 999',
                    }
                }
            },
            unit1_return: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Supply'
                    },
                    integer: {
                        message: 'Enter the Number',
                    },
                    greaterThan: {
                        value: 0,
                        message: 'Enter the Number greater than 0',
                    },
                    lessThan: {
                        value: 999,
                        message: 'Enter the Number less than 999',
                    }
                }
            },

            unit2_supply: {
                validators: {
                    integer: {
                        message: 'Enter the Number',
                    },
                    greaterThan: {
                        value: 0,
                        message: 'Enter the Number greater than 0',
                    },
                    lessThan: {
                        value: 999,
                        message: 'Enter the Number less than 999',
                    }
                }
            },
            unit2_return: {
                validators: {
                    integer: {
                        message: 'Enter the Number',
                    },
                    greaterThan: {
                        value: 0,
                        message: 'Enter the Number greater than 0',
                    },
                    lessThan: {
                        value: 999,
                        message: 'Enter the Number less than 999',
                    }
                }
            },
            
            unit3_supply: {
                validators: {
                    integer: {
                        message: 'Enter the Number',
                    },
                    greaterThan: {
                        value: 0,
                        message: 'Enter the Number greater than 0',
                    },
                    lessThan: {
                        value: 999,
                        message: 'Enter the Number less than 999',
                    }
                }
            },
            unit3_return: {
                validators: {
                    integer: {
                        message: 'Enter the Number',
                    },
                    greaterThan: {
                        value: 0,
                        message: 'Enter the Number greater than 0',
                    },
                    lessThan: {
                        value: 999,
                        message: 'Enter the Number less than 999',
                    }
                }
            },
            
            unit4_supply: {
                validators: {
                    integer: {
                        message: 'Enter the Number',
                    },
                    greaterThan: {
                        value: 0,
                        message: 'Enter the Number greater than 0',
                    },
                    lessThan: {
                        value: 999,
                        message: 'Enter the Number less than 999',
                    }
                }
            },
            unit4_return: {
                validators: {
                    integer: {
                        message: 'Enter the Number',
                    },
                    greaterThan: {
                        value: 0,
                        message: 'Enter the Number greater than 0',
                    },
                    lessThan: {
                        value: 999,
                        message: 'Enter the Number less than 999',
                    }
                }
            },
            
            house_pressure: {
                validators: {
                    notEmpty: {
                        message: 'Enter the House Pressure'
                    },
                    numeric: {
                        message: 'Enter the Number',
                    },
                    greaterThan: {
                        value: 0.1,
                        message: 'Enter the Number greater than 0.1',
                    },
                    lessThan: {
                        value: 99.9,
                        message: 'Enter the Number less than 99.9',
                    }
                }
            },
            flow: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Flow'
                    },
                    numeric: {
                        message: 'Enter the Number',
                    },
                    greaterThan: {
                        value: 0.1,
                        message: 'Enter the Number greater than 0.1',
                    },
                    lessThan: {
                        value: 9999.9,
                        message: 'Enter the Number less than 9999.9',
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
    
    $('input.wci-result-input').on('keypress', function(e) {
        calculate_result();
    });

    $('form').on('submit', function (e) {
        if (e.isDefaultPrevented()) {
        } else {
            e.preventDefault();
            
            submit_data();
        }
    });

    
    init();
});
