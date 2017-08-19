var invoice_id = "";

function detail(id) {
    invoice_id = id;
    
    get_invoice();
}

function get_invoice() {
    showLoading();

    $.ajax({
        type: "POST",
        url: 'get_invoice',
        data: {
            id: invoice_id,
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();

            if (data.code == 0) {
                var invoice = data.invoice;
                var payment = data.payment;
                
                $("#detail_dialog .modal-body .check_details label.control-field").html(payment.check_details);
                $("#detail_dialog .modal-body .check_cut label.control-field").html(payment.check_cut);
                $("#detail_dialog .modal-body .pay_to label.control-field").html(payment.pay_to);
                $("#detail_dialog .modal-body .exported_on label.control-field").html(payment.exported_on);
                $("#detail_dialog .modal-body .check_number label.control-field").html(payment.check_number);
                $("#detail_dialog .modal-body .check_amount label.control-field").html('<i class="fa fa-dollar"></i> ' + parseFloat(payment.check_amount).toFixed(2));
                
                $("#detail_dialog .modal-body .invoice_number label.control-field").html(invoice.invoice_number);
                $("#detail_dialog .modal-body .invoice_description label.control-field").html(invoice.invoice_description);
                $("#detail_dialog .modal-body .discount_amount label.control-field").html('<i class="fa fa-dollar"></i> ' + parseFloat(invoice.discount_amount).toFixed(2));
                $("#detail_dialog .modal-body .invoice_amount label.control-field").html('<i class="fa fa-dollar"></i> ' + parseFloat(invoice.invoice_amount).toFixed(2));
                $("#detail_dialog .modal-body .invoice_date label.control-field").html(invoice.invoice_date);
                $("#detail_dialog .modal-body .community label.control-field").html(invoice.community);
                $("#detail_dialog .modal-body .job_number label.control-field").html(invoice.job_number);
                $("#detail_dialog .modal-body .address label.control-field").html(invoice.address);
                $("#detail_dialog .modal-body .option_number label.control-field").html(invoice.option_number);
                $("#detail_dialog .modal-body .line_amount label.control-field").html('<i class="fa fa-dollar"></i> ' + parseFloat(invoice.line_amount).toFixed(2));
                $("#detail_dialog .modal-body .account_category label.control-field").html(invoice.account_category);
                $("#detail_dialog .modal-body .category_description label.control-field").html(invoice.category_description);
                $("#detail_dialog .modal-body .plan_name label.control-field").html(invoice.plan_name);
                $("#detail_dialog .modal-body .plan_number label.control-field").html(invoice.plan_number);
                $("#detail_dialog .modal-body .task_description label.control-field").html(invoice.task_description);
                $("#detail_dialog .modal-body .start_date label.control-field").html(invoice.start_date);
                $("#detail_dialog .modal-body .complete_date label.control-field").html(invoice.complete_date);

                $("#detail_dialog").modal('show');
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

function match(id) {
    showLoading();

    $.ajax({
        type: "POST",
        url: 'match_invoice',
        data: {
            id: id,
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();

            if (data.code == 0) {
                $('#table_content').dataTable().api().ajax.reload(null, false);
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

function get_paid(id) {
    showLoading();

    $.ajax({
        type: "POST",
        url: 'get_paid_invoice',
        data: {
            id: id,
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();

            if (data.code == 0) {
                $('#table_content').dataTable().api().ajax.reload(null, false);
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
            "url": "load_record_payment_received",
            "type": "POST",
            data: function(d) {
                d.start_date = $("#start_date").val();
                d.end_date = $("#end_date").val();
                d.builder = $("#builder").val();
                d.status = $("#status").val();
            }
        },
//        'searching' : false,
        pageLength: 100,
        "order": [[3, "desc"]],
        "columnDefs": [
            {
                "targets": [-1, -2, -3],
                "orderable": false
            },
            {
                "targets": "_all",
                "searchable": false
            }
        ],
        "columns": [
            {
                "data": "check_number",
            },
            {
                "data": "check_cut",
            },
            {
                "data": "invoice_number",
            },
            {
                "data": "invoice_date",
            },
            {
                "data": "line_amount",
                "render": function (data, type, row, meta) {
                    var d = "";

                    var p = parseFloat(data);
                    d = '<span class="label label-info"><i class="fa fa-dollar"></i> '+p.toFixed(2)+'</span>';
                    
                    return d;
                }
            },
            {
                "data": "inspection_type_name",
                "render": function (data, type, row, meta) {
                    var d = "";
                    d = data;

                    if (row.inspection_type_name_2!=null) {
                        d = row.inspection_type_name_2;
                    }
                    
                    return d;
                }
            },
            {
                "data": "job_number",
                "render": function (data, type, row, meta) {
                    var d = "";
                    d = data;

                    if (row.inspection_job_number_2!=null) {
                        d = row.inspection_job_number_2;
                    } else if (row.inspection_job_number!=null) {
                        d = row.inspection_job_number;
                    }
                    
                    return d;
                }
            },
            {
                "data": "address", 
                "render": function (data, type, row, meta) {
                    var d = "";
                    
                    d = data;

                    return d;
                }
            },
            {
                "data": "additional",
                "render": function (data, type, row, meta) {
                    var data = "";
                    
                    if (row.status==1) {
                        data = '<span class="label label-success">PAID</span>';
                    } else {
                        if (row.check_id!=null) {
                            if (row.inspection_id_2!=null && row.inspection_id_2!="") {
                                data = '<span class="label label-success">PAID</span>';
                            } else {
                                data = '<span class="label label-warning">PENDING</span>';
                            }
                        } else {
                            data = '<span class="label label-warning">PENDING</span>';
                        }
                    }
                    
                    return data;
                }
            },
            {
                "data": "additional",
                "render": function (data, type, row, meta) {
                    var data = "";

                    if (row.check_id!=null) {
                    } else {
                        data += "No Received Check";
                    }
                    
                    if (row.inspection_id_2!=null && row.inspection_id_2!="") {
                        
                    } else {
                        if (row.inspection_type_code!=null)  {
                            if (row.inspection_id!=null && row.inspection_id!="") {
//                                if (data!="") {
//                                    data += ", ";
//                                }
//                                data += "Need Match to link with inspection";
                            } else {
                                if (data!="") {
                                    data += ", ";
                                }
                                data += "No Inspection Submitted";
                            }
                        } else {
                            if (data!="") {
                                data += ", ";
                            }

                            if (row.account_category!=null && row.account_category!="") {
                                data += "Account Category is not Match(" + row.account_category + ")";
                            } else {
                                data += "Account Category is not Match";
                            }
                        }
                    }
                    
                    return data;
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

                        data += '<li><a href="javascript:detail(\'' + row.id + '\')"><i class="fa fa-search"></i> View Detail</a></li>';

                        if (row.check_id!=null) {
                            if (row.inspection_id_2!=null && row.inspection_id_2!="") {
//                                data = '<span class="label label-success">PAID</span>';
                            } else {
                                if (row.status==0) {
                                    data += '<li><a href="javascript:get_paid(\'' + row.id + '\')"><i class="fa fa-dollar"></i> Get Paid</a></li>';
                                } else if (row.status==1) {
                                    data += '<li><a href="javascript:get_paid(\'' + row.id + '\')"><i class="fa fa-dollar"></i> Pending</a></li>';
                                }
                            }
                        } else {
                        }
                        
                        if (row.inspection_id_2!=null && row.inspection_id_2!="") {

                        } else {
                            if (row.inspection_type_code!=null)  {
//                                if (row.inspection_id!=null && row.inspection_id!="") {
//                                    data += '<li><a href="javascript:match(\'' + row.id + '\')"><i class="fa fa-link"></i> Match</a></li>';
//                                } else {
//                                }
                            } else {
                            }
                        }

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
        $('#table_content tr td:nth-child(6)').addClass('center');
        $('#table_content tr td:nth-child(7)').addClass('center');
        $('#table_content tr td:nth-child(9)').addClass('center');
        $('#table_content tr td:nth-child(11)').addClass('center');
    });


    $(".table-filter select, .table-filter input").on('change keypress', function(e) {
        $('#table_content').dataTable().api().ajax.reload();
    });
    
    $("#btn_search").on('click', function(e) {
        e.preventDefault();
        $('#table_content').dataTable().api().ajax.reload();
    });


    $("#btn_import").on('click', function(e) {
        e.preventDefault();
        $('#takeFileUpload').trigger('click');
    });

    $('#takeFileUpload').fileupload({
        singleFileUploads: true,

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
            showAlert("Invalid Format or Corrupted file");
        }
    });
    
});
