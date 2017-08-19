var payment_id = "";

function proceed(id) {
    payment_id = id;
    get_payroll('process');
}

function get_payroll(kind) {
    showLoading();

    $.ajax({
        type: "POST",
        url: 'get_payroll',
        data: {
            id: payment_id,
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();

            if (data.code == 0) {
                var row = data.result;
                
                if (kind=='process') {
                    $("#payment_confirm_dialog .modal-body p.description").html('' +
                            '' + row.inspector_name + "'s Check during " + row.start_date + " ~ " + row.end_date + ' for Check Number('+row.check_number+'), Check Amount($'+row.check_amount+') will be proceed to PAID.' +
                            '<br> Are you sure?' +
                            '');

                    $("#payment_confirm_dialog").modal('show');
                }
                
                if (kind=="edit") {
                    $("#check_amount").val(row.check_amount);
                    $("#check_number").val(row.check_number);
                    $("#transaction_date_edit").val(row.transaction_date);
                    
                    $("#payment_edit_dialog").modal('show');
                }
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

function proceed_all() {
    payment_id = "all";
    
    $("#payment_confirm_dialog .modal-body p.description").html("All Pending Inspector's Check(Filtered Only) will be proceed to PAID. <br> Are you sure?");
    
    $("#payment_confirm_dialog").modal('show');
}

function process_payment(date) {
    showLoading();

    $.ajax({
        type: "POST",
        url: 'process_inspector_payment',
        data: {
            id: payment_id,
            date: date,
            
            inspector: $("#inspector").val(),
            period: $("#pay_period").val(),
            start_date: $("#start_date").val(),
            end_date: $("#end_date").val(),
            status: $("#status").val(),
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();

            if (data.code == 0) {
                showAlert("Successfully Proceed!");
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

function edit(id) {
    payment_id = id;
    get_payroll('edit');
}

function save_payment(amount, number, date) {
    showLoading();

    $.ajax({
        type: "POST",
        url: 'save_inspector_payment',
        data: {
            id: payment_id,
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


function cancel_payment(id) {
    bootbox.confirm("Are you sure to cancel?", function (result) {
        if (result) {
            delete_payment(id);
        }
    });
}

function delete_payment(id) {
    showLoading();

    $.ajax({
        type: "POST",
        url: 'cancel_inspector_payment',
        data: {
            id: id,
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();

            if (data.code == 0) {
                showAlert("Successfully Cancelled!");
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

function export_file(format) {
    $.fileDownload($("#basePath").val()+"api/export/payable/payroll?file_format="+format+"&period="+$("#pay_period").val()+"&inspector="+$("#inspector").val()+"&start_date="+$("#start_date").val()+"&end_date="+$("#end_date").val() );
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
            "url": "load_inspector_payment",
            "type": "POST",
            data: function(d) {
                d.inspector = $("#inspector").val();
                d.period = $("#pay_period").val();
                d.start_date = $("#start_date").val();
                d.end_date = $("#end_date").val();
            }
        },
//        'searching' : false,
        pageLength: 25,
        "order": [[7, "desc"]],
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
                "data": "inspector_name",
            },
            {
                "data": "inspector_email",
            },
            {
                "data": "inspector_phone",
            },
            {
                "data": "inspector_address",
            },
            {
                "data": "additional",
                "render": function (data, type, row, meta) {
                    var d = "";
                    d += row.start_date + " ~ " + row.end_date;
                    return d;
                }
            },
            {
                "data": "check_amount",
                "render": function (data, type, row, meta) {
                    var d = "";

                    var p = parseFloat(data);
                    d = '<span class="label label-info"><i class="fa fa-dollar"></i> '+p.toFixed(2)+'</span>';
                    
                    return d;
                }
            },
            {
                "data": "check_number",
            },
            {
                "data": "transaction_date",
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
                        data += '<li><a href="javascript:cancel_payment(\'' + row.id + '\')"><i class="fa fa-ban"></i> Cancel</a></li>';

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
        $('#table_content tr td:nth-child(2)').addClass('center');
        $('#table_content tr td:nth-child(3)').addClass('center');
        $('#table_content tr td:nth-child(5)').addClass('center');
        $('#table_content tr td:nth-child(6)').addClass('center');
        $('#table_content tr td:nth-child(7)').addClass('center');
        $('#table_content tr td:nth-child(8)').addClass('center');
        $('#table_content tr td:nth-child(9)').addClass('center');
    });


    $(".table-filter select, .table-filter input").on('change keypress', function(e) {
        $('#table_content').dataTable().api().ajax.reload();
    });
    
    $("#btn_search").on('click', function(e) {
        e.preventDefault();
        $('#table_content').dataTable().api().ajax.reload();
    });

    $("#btn_submit").on('click', function(e) {
        e.preventDefault();
        proceed_all();
    });
    
    $("#payment_confirm_dialog").on('click', '.btn-primary', function(e) {
        e.preventDefault();
        
        var date = $("#transaction_date").val();
        if (date=="") {
            App.showMessage("Please Select Transaction Date");
            return false;
        }
        
        $("#payment_confirm_dialog").modal('hide');
        process_payment(date);
    });
    
    $("#payment_edit_dialog").on('click', '.btn-primary', function(e) {
        e.preventDefault();
        
        var amount = $("#check_amount").val();
        if (amount=="") {
            App.showMessage("Please Input Check Amount");
            return false;
        }

//        var number = $("#check_number").val();
//        if (number=="") {
//            App.showMessage("Please Input Check Number");
//            return false;
//        }
        
        var date = $("#transaction_date_edit").val();
        if (date=="") {
            App.showMessage("Please Select Transaction Date");
            return false;
        }
        
        $("#payment_edit_dialog").modal('hide');
        save_payment(amount, number, date);
    });

    $("#btn_export").on('click', function(e) {
        e.preventDefault();
        export_file('pdf');
    });
    $("#btn_export_csv").on('click', function(e) {
        e.preventDefault();
        export_file('csv');
    });
    
});
