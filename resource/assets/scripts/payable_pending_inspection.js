var requested_id = "";
var export_type = "";

function init() {
    $("#region").trigger('change');
}

function get_formatted_date() {

    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth() + 1; //January is 0!
    var yyyy = today.getFullYear();

    if (dd < 10) {
        dd = '0' + dd;
    }

    if (mm < 10) {
        mm = '0' + mm;
    }

    today = yyyy + '-' + mm + '-' + dd;
    return today;
}

function get_community() {
//    showLoading();

    $("#community").html('');
    $("#community").append('<option value="">All</option>');
    
    $.ajax({
        type: "POST",
        url: $("#basePath").val() + 'statistics/get_community',
        data: {
            region: $("#region").val(),
        },
        dataType: 'json',
        success: function (data) {
//            hideLoading();
            
            if (data.err_code==0) {
                
                var html = "";
                $.each(data.community, function(index, row) {
                   html += '<option value="'+row.community_id+'">'+row.community_id + ' - ' + row.community_name+'</option>'; 
                });
                
                $("#community").append(html);
                
                get_total_count();
            }else {
                showAlert("Failed to load community!");
            }            
            
            $("#community").selectpicker('refresh');
        },
        error: function () {
//            hideLoading();
            showAlert(Message.SERVER_ERROR);
        }
    });
}

function export_file(format) {
    export_type = format;
    $("#export_confirm_dialog").modal('show');
}


function send_report() {
    $("#recipients").val("");
    $("#email_confirm_dialog").modal('show');
}

function email_report(recipients) {
    showLoading();
    
    $.ajax({
        type: "POST",
        url: $("#basePath").val() + 'api/email/payable/pending_inspection',
        data: {
            region: $("#region").val(),
            community : $("#community").val(),
            start_date : $("#start_date").val(),
            end_date : $("#end_date").val(),
            status: $("#status").val(),
            type: $("#inspection_type").val(),
            epo_status: $("#epo_status").val(),
            payment_status: $("#payment_status").val(),
            re_inspection: $("#re_inspection").val(),
            recipient: recipients,
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();
            showAlert(data.message);
            
            if (data.code==0) {
                
            }else {
            }            
        },
        error: function () {
            hideLoading();
            showAlert(Message.SERVER_ERROR);
        }
    });
}


function get_total_count() {
    $("#statistics_result").html('');
    
    $.ajax({
        type: "POST",
        url: 'get_count',
        data: {
            kind: 'pending_inspection',
            region: $("#region").val(),
            community : $("#community").val(),
            start_date : $("#start_date").val(),
            end_date : $("#end_date").val(),
            status: $("#status").val(),
            epo_status: $("#epo_status").val(),
            type: $("#inspection_type").val(),
            payment_status: $("#payment_status").val(),
            re_inspection: $("#re_inspection").val(),
        },
        dataType: 'json',
        success: function (data) {
            if (data.code==0) {
                $("#statistics_result").html(data.result);
            }else {
            }            
        },
        error: function () {
        }
    });
}

jQuery(document).ready(function () {
    showAlert($("#msg_alert").html());
    
//    $('.date-picker').val(get_formatted_date());
    
    $('.date-picker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
//        endDate: get_formatted_date(),
        todayBtn: true,
        todayHighlight: true,
    });
    
    $('.select-picker').selectpicker({    
        liveSearch: true,
    });
    
    $("#region").change(function(e) {
        get_community();
    });

    $('#table_content').dataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "ajax": {
            "url": "load_pending_inspection",
            "type": "POST",
            "data": function (d){
                d.region = $("#region").val();
                d.community = $("#community").val();
                d.start_date = $("#start_date").val();
                d.end_date = $("#end_date").val();
                d.status = $("#status").val();
                d.type = $("#inspection_type").val();
                d.epo_status = $("#epo_status").val();
                d.payment_status = $("#payment_status").val();
                d.re_inspection = $("#re_inspection").val();
            }
        },
        "order": [[6, "desc"]],
        "pageLength": 25,
        "columnDefs": [
//            {
//                "targets": [-1],
//                "orderable": false
//            },
//            {
//                "targets": "_all",
//                "searchable": false
//            }
        ],
        "columns": [
            {
                "data": "inspection_type",
            },
            {
                "data": "region_name",
            },
            {
                "data": "community",
                "render": function (data, type, row, meta) {
                    var d = "";
                    
                    if (row.community_name!=null && row.community_name!="") {
                        d = row.community_name;
                    } else {
//                        d = data;
                    }
                    
                    return d;
                }
            },
            {
                "data": "job_number",
            },
            {
                "data": "address",
            },
            {
                "data": "first_name",
                "render": function (data, type, row, meta) {
                    var d = "";
                    
                    if (row.first_name!=null) {
                        d += row.first_name + " ";
                    }
                    
                    if (row.last_name!=null) {
                        d += row.last_name;
                    }
                    
                    return d;
                }
            },
            {
                "data": "start_date",
            },
            {
                "data": "result_code",
                "render": function (data, type, row, meta) {
                    var data = "";
                    
                    if (row.result_code=='1') {
                        data += '<span class="label label-success">';
                        data += row.result_name;
                    }
                    if (row.result_code=='2') {
                        data += '<span class="label label-warning">';
                        data += "Pass";
                    }
                    if (row.result_code=='3') {
                        data += '<span class="label label-danger">';
                        data += row.result_name;
                    }
                    
                    data += "</span>";
                    
                    return data;
                }
            },
            {
                "data": "epo_number",
                "render": function (data, type, row, meta) {
                    var d = data;
                    return d;
                }
            },
            {
                "data": "epo_status",
                "render": function (data, type, row, meta) {
                    var d = "";
                    var v = row.epo_status;
                    
                    if (v==0)  {
                        d += '<span class="label label-info">To Request</span>';
                    }
                    if (v==1)  {
                        d += '<span class="label label-warning">Requested</span>';
                    }
                    if (v==2)  {
                        d += '<span class="label label-success">Received</span>';
                    }
                    if (v==3)  {
                        d += '<span class="label label-default">Not Needed</span>';
                    }
                    
                    return d;
                }
            },
            {
                "data": "additional",
                "render": function (data, type, row, meta) {
                    var d = "";
                    
                    if (row.invoice_id!=null && row.invoice_id!="") {
                        d = '<span class="label label-success">PAID</span>';  
                    } else {
                        d = '<span class="label label-warning">PENDING</span>';
                    }
                    
                    return d;
                }
            },
        ]
    });

    $('#table_content').on('draw.dt', function () {
        $('#table_content').removeClass('display').addClass('table table-striped table-bordered');
        $('#table_content tr td:nth-child(1)').addClass('center');
        $('#table_content tr td:nth-child(4)').addClass('center');
        $('#table_content tr td:nth-child(6)').addClass('center');
        $('#table_content tr td:nth-child(7)').addClass('center');
        $('#table_content tr td:nth-child(8)').addClass('center');
        $('#table_content tr td:nth-child(10)').addClass('center');
        $('#table_content tr td:nth-child(11)').addClass('center');
    });
    
    $("#btn_view").on('click', function(e) {
        e.preventDefault();
        $('#table_content').dataTable().api().ajax.reload();
        get_total_count();
    });
    
    $("#btn_export").on('click', function(e) {
        e.preventDefault();
        $.fileDownload($("#basePath").val()+"api/export/payable/pending_inspection?file_format=pdf&region="+$("#region").val()+"&community="+$("#community").val()+"&start_date="+$("#start_date").val()+"&end_date="+$("#end_date").val()+"&status=" + $("#status").val() + "&type="+$("#inspection_type").val()+ "&epo_status="+$("#epo_status").val() + "&payment_status="+$("#payment_status").val() + "&re_inspection="+$("#re_inspection").val() );
    });
    $("#btn_export_csv").on('click', function(e) {
        e.preventDefault();
        $.fileDownload($("#basePath").val()+"api/export/payable/pending_inspection?file_format=csv&region="+$("#region").val()+"&community="+$("#community").val()+"&start_date="+$("#start_date").val()+"&end_date="+$("#end_date").val()+"&status=" + $("#status").val() + "&type="+$("#inspection_type").val()+ "&epo_status="+$("#epo_status").val() + "&payment_status="+$("#payment_status").val() + "&re_inspection="+$("#re_inspection").val() );
    });

    $("#btn_email").on('click', function(e) {
        e.preventDefault();
        send_report();
    });
    $("#email_confirm_dialog").on('click', '.modal-footer .btn-primary', function(e) {
        e.preventDefault();
        email_report($("#recipients").val());
        $("#email_confirm_dialog").modal('hide');
    });

    $('#table_content').on('change', 'tbody tr td .form-control', function(e) {
        var id = $(this).attr('data-id');
        var old = $(this).attr('data-value');
        var current  = $(this).val();
        
        if (old!=current) {
            $("#link_"+id).removeClass('not-assigned').addClass('assigned');
        } else {
            $("#link_"+id).removeClass('assigned').addClass('not-assigned');
        }
    });
    
    init();
});
