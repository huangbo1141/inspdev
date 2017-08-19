var is_first = true;

function init() {
    $("#region").trigger('change');
}

function get_community() {
    showLoading();

    $("#community").html('');
    $("#community").append('<option value="">&nbsp;</option>');
    
    $.ajax({
        type: "POST",
        url: 'get_community',
        data: {
            region: $("#region").val(),
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();
            
            if (data.err_code==0) {
                var html = "";
                $.each(data.community, function(index, row) {
                    if (is_first && $("#community_id").val()!="" && $("#community_id").val()==row.community_id+"") {
                        html += '<option selected value="'+row.community_id+'">'+row.community_id + ' - ' + row.community_name+'</option>'; 
                    } else {
                        html += '<option value="'+row.community_id+'">'+row.community_id + ' - ' + row.community_name+'</option>'; 
                    }
                });

                if (is_first) {
                    is_first = false;
                }
                
                $("#community").append(html);
            }else {
                showAlert("Failed to load community!");
            }            
            
            $("#community").selectpicker('refresh');
        },
        error: function () {
            hideLoading();
            showAlert(Message.SERVER_ERROR);
        }
    });
}

function submit_data() {
    showLoading();

    $.ajax({
        type: "POST",
        url: 'update',
        data: {
            kind: $("#kind").val(),
            job_number: $("#job_number").val(),
            community: "", // $("#community").val(),
            address: $("#address").val(),
            builder: $("#builder").val(),
            unit_id: $("#unit_id").val(),
            field_manager: $("#field_manager").length>0 ? $("#field_manager").val() : "",
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
    
    if ($("#kind").val()=='edit') {
        $("#job_number").attr('readonly', 'readonly');
        
    } else {
        $("#job_number").inputmask("9999-99999", {
            placeholder: 'x'
        });    
    }

    if ($("#field_manager").length>0) {
        $("#field_manager").typeahead({
            minLength: 0,
            source: function (query, process) {
                var res = [];

                $.ajax({
                    type: "POST",
                    url: 'get_field_manager',
                    data: {
                        input: query 
                    },
                    dataType: 'json',
                    success: function (data) {
                        if (data.code==0) {
                            process(data.result);
                        }
                    },
                    error: function () {
                    }
                });
            }
        });
    }

    $('.select-picker').selectpicker({    
        liveSearch: true,
    });
    
    $("#region").change(function(e) {
        get_community();
    });

    $('form').bootstrapValidator({
        feedbackIcons: {
            valid: 'has-success',
            invalid: 'has-error',
            validating: ''
        },
        fields: {
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
            field_manager: {
                validators: {
                    notEmpty: {
                        message: 'Enter the Field Manager'
                    },
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
            v = v.replace(/x/g, "").replace(/X/g, "").replace(/-/g, "");
            if (v=="" || v.length!=9) {
                showAlert("Enter the Job Number");
                
            } else {
                submit_data();
            }
        }
    });

    init();
});
