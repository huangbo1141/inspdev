var check_id = "";

function edit(id) {
    check_id = id;
    
    if (id=="") {
        $("#edit_dialog .modal-title").html("Add Received Check");
        
        $("#builder_edit").val("1");
        $("#check_amount").val("0.00");
        $("#check_number").val("0");
        $("#check_date").val(App.get_formatted_date());
        
        $("#edit_dialog").modal('show');
    } else {
        get_check();
    }
}

function get_check() {
    showLoading();

    $.ajax({
        type: "POST",
        url: 'get_check',
        data: {
            id: check_id,
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();

            if (data.code == 0) {
                var row = data.result;
                
                $("#edit_dialog .modal-title").html("Edit Received Check");

                $("#builder_edit").val(row.builder);
                $("#check_amount").val(row.check_amount);
                $("#check_number").val(row.check_number);
                $("#check_date").val(row.check_date);

                $("#edit_dialog").modal('show');
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

function save_check(amount, number, date, builder) {
    showLoading();

    $.ajax({
        type: "POST",
        url: 'save_check',
        data: {
            id: check_id,
            builder: builder,
            amount: amount,
            number: number,
            date: date,
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();

            if (data.code == 0) {
                showAlert("Successfully Saved!");
                $('#table_content').dataTable().api().ajax.reload();
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

function remove(id) {
    bootbox.confirm("Are you sure to delete this check?", function (result) {
        if (result) {
            delete_check(id);
        }
    });
}

function delete_check(id) {
    showLoading();

    $.ajax({
        type: "POST",
        url: 'delete_check',
        data: {
            id: id,
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();

            if (data.code == 0) {
                showAlert("Successfully Deleted!");
                $('#table_content').dataTable().api().ajax.reload();
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
    showAlert($("#msg_alert").html());

    $(".select-picker").selectpicker({
        container: 'body',
        liveSearch: true,
        mobile: true,
    });

    $('.date-picker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
//        endDate: get_formatted_date(),
        todayBtn: true,
        todayHighlight: true,
    });


    $('#table_content').dataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "ajax": {
            "url": "load_received_check",
            "type": "POST",
            data: function(d) {
                d.builder = $("#builder").val();
                d.start_date = $("#start_date").val();
                d.end_date = $("#end_date").val();
            }
        },
//        'searching' : false,
        pageLength: 25,
        "order": [[0, "desc"]],
        "columnDefs": [
            {
                "targets": [-1],
                "orderable": false
            },
            {
                "targets": "_all",
                "searchable": false
            }
        ],
        "columns": [
            {
                "data": "check_date",
            },
            {
                "data": "builder_name",
            },
            {
                "data": "check_number",
            },
            {
                "data": "check_amount",
                "render": function (data, type, row, meta) {
                    var d = "";

                    var p = parseFloat(data);
                    d = '<span class="label label-success"><i class="fa fa-dollar"></i> '+p.toFixed(2)+'</span>';
                    
                    return d;
                }
            },
            {
                "data": "additional",
                "render": function (data, type, row, meta) {
                    var data = "";
                    
                    if ($("#user_permission").val()=='1') {
                        data += '<div class="btn-group"> ' +
                                '<button type="button" class="btn default dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true"> Action<i class="fa fa-angle-down"></i> </button>' +
                                '<ul class="dropdown-menu bottom-up pull-right" role="menu">' +
                                '<li class="divider"></li>';

                        data += '<li><a href="javascript:edit(\'' + row.id + '\')"><i class="fa fa-edit"></i> Edit</a></li>';
                        data += '<li><a href="javascript:remove(\'' + row.id + '\')"><i class="fa fa-trash-o"></i> Delete</a></li>';

                        data += '<li class="divider"></li>';
                        data += '</ul>' +
                                '</div>' +
                                '';
                    }
                    
                    return data;
                }
            }
        ]
    });

    $('#table_content').on('draw.dt', function () {
        $('#table_content').removeClass('display').addClass('table table-striped table-bordered');
        $('#table_content tr td:nth-child(1)').addClass('center');
        $('#table_content tr td:nth-child(2)').addClass('center');
        $('#table_content tr td:nth-child(3)').addClass('center');
        $('#table_content tr td:nth-child(4)').addClass('center');
        $('#table_content tr td:nth-child(5)').addClass('center');
    });


    $(".table-filter select, .table-filter input").on('change keypress', function(e) {
        $('#table_content').dataTable().api().ajax.reload();
    });
    
    $("#btn_search").on('click', function(e) {
        e.preventDefault();
        $('#table_content').dataTable().api().ajax.reload();
    });

    $("#btn_add").on('click', function(e) {
        e.preventDefault();
        edit('');
    });


    $("#edit_dialog").on('click', '.btn-primary', function(e) {
        e.preventDefault();
        
        var date = $("#check_date").val();
        if (date=="") {
            App.showMessage("Please Select Check Date");
            return false;
        }

        var number = $("#check_number").val();
        if (number=="") {
            App.showMessage("Please Input Check Number");
            return false;
        }
        
        var amount = $("#check_amount").val();
        if (amount=="") {
            App.showMessage("Please Input Check Amount");
            return false;
        }
        
        if (isNaN(amount)) {
            App.showMessage("Please Input Correct Check Amount");
            return false;
        }

//        if (isNaN(number)) {
//            App.showMessage("Please Input Correct Check Number");
//            return false;
//        }
        
        if (parseFloat(amount)<=0) {
            App.showMessage("Please Input Correct Check Amount");
            return false;
        }

//        if (parseInt(number)<=0) {
//            App.showMessage("Please Input Correct Check Number");
//            return false;
//        }
        
        $("#edit_dialog").modal('hide');
        save_check(amount, number, date, $("#builder_edit").val());
    });

    $("#btn_import").on('click', function(e) {
        e.preventDefault();
        $("#upload_dialog").modal('show');
    });

    $("#upload_dialog").on('click', '.btn-primary', function(e) {
        e.preventDefault();
        
        $("#upload_dialog").modal('hide');
        $('#takeFileUpload').trigger('click');
    });

    $('#takeFileUpload').fileupload({
        singleFileUploads: false,

        dataType: 'json',
        formData: {
        },
        beforeSend: function () {
            showLoading();
        },
        done: function (e, data) {
            hideLoading();
            if (data.result.code == 0) {
                showAlert("Successfully Imported!");
                $('#table_content').dataTable().api().ajax.reload();
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
    
});
