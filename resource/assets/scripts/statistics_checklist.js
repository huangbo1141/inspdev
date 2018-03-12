function init() {
    get_total_count();
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
    //$("#community").append('<option value="">All</option>');
    
    $.ajax({
        type: "POST",
        url: 'get_community',
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

function get_total_count() {
    $("#statistics_result").html('');
    
    $.ajax({
        type: "POST",
        url: 'get_count',
        data: {
            kind: 'checklist',
            region: $("#region").val(),
            community : $("#community").val(),
            start_date : $("#start_date").val(),
            end_date : $("#end_date").val(),
            status: $("#status").val(),
            type: $("#inspection_type").val(),
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

function send_report() {
    $("#recipients").val("");
    $("#email_confirm_dialog").modal('show');
}

function email_report(recipients) {
    showLoading();
    
    $.ajax({
        type: "POST",
        url: $("#basePath").val() + 'api/email/statistics/checklist',
        data: {
            region: $("#region").val(),
            community : $("#community").val(),
            start_date : $("#start_date").val(),
            end_date : $("#end_date").val(),
            status: $("#status").val(),
            type: $("#inspection_type").val(),
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

jQuery(document).ready(function () {
    showAlert($("#msg_alert").html());
    
    $('.date-picker').val(get_formatted_date());
    
    $('.date-picker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
//        endDate: get_formatted_date(),
        todayBtn: true,
        todayHighlight: true,
    });
    
    $('.select-picker').selectpicker({    
        liveSearch: true,
        actionsBox:true
    });
    
    $("#region").change(function(e) {
        get_community();
    });

//    $('#table_content').dataTable({
//        "processing": true,
//        "serverSide": true,
//        "responsive": true,
//        "ajax": {
//            "url": "load_checklist",
//            "type": "POST",
//            "data": function (d){
//                d.region = $("#region").val();
//                d.community = $("#community").val();
//                d.start_date = $("#start_date").val();
//                d.end_date = $("#end_date").val();
//                d.status = $("#status").val();
//                d.type=$("#inspection_type").val();
//            }
//        },
//        "order": [[3, "asc"]],
//        "pageLength": 100,
//        "columnDefs": [
////            {
////                "targets": [-1],
////                "orderable": false
////            },
////            {
////                "targets": "_all",
////                "searchable": false
////            }
//        ],
//        "columns": [
//            {
//                "data": "inspection_type",
//            },
//            {
//                "data": "region_name",
//            },
//            {
//                "data": "community",
//            },
//            {
//                "data": "start_date",
//            },
//            {
//                "data": "location_name",
//            },
//            {
//                "data": "item_name",
//                "render": function (data, type, row, meta) {
//                    var d = row.item_no + ". " + row.item_name;
//                    return d;
//                }
//            },
//            {
//                "data": "status_name",
//                "render": function (data, type, row, meta) {
//                    var data = "";
//                    
//                    if (row.status_code=='1') {
//                        data += '<span class="label label-success">';
//                    }
//                    if (row.status_code=='2') {
//                        data += '<span class="label label-danger">';
//                    }
//                    if (row.status_code=='3') {
//                        data += '<span class="label label-warning">';
//                    }
//                    if (row.status_code=='4' || row.status_code=='5') {
//                        data += '<span class="label label-info">';
//                    }
//                    
//                    data += row.status_name;
//                    data += "</span>";
//                    
//                    return data;
//                }
//            }
//        ]
//    });
//
//    $('#table_content').on('draw.dt', function () {
//        $('#table_content').removeClass('display').addClass('table table-striped table-bordered');
//        $('#table_content tr td:nth-child(1)').addClass('center');
//        $('#table_content tr td:nth-child(2)').addClass('center');
//        $('#table_content tr td:nth-child(3)').addClass('center');
//        $('#table_content tr td:nth-child(4)').addClass('center');
//        $('#table_content tr td:nth-child(5)').addClass('center');
//        $('#table_content tr td:nth-child(7)').addClass('center');
//    });
    
    $("#btn_view").on('click', function(e) {
        e.preventDefault();
//        $('#table_content').dataTable().api().ajax.reload();
        get_total_count();
    });
    
    $("#btn_export").on('click', function(e) {
        e.preventDefault();
        $.fileDownload($("#basePath").val()+"api/export/statistics/checklist?file_format=pdf&region="+$("#region").val()+"&community="+$("#community").val()+"&start_date="+$("#start_date").val()+"&end_date="+$("#end_date").val()+"&status="+$("#status").val()+"&type="+$("#inspection_type").val());
    });
    $("#btn_export_csv").on('click', function(e) {
        e.preventDefault();
        $.fileDownload($("#basePath").val()+"api/export/statistics/checklist?file_format=csv&region="+$("#region").val()+"&community="+$("#community").val()+"&start_date="+$("#start_date").val()+"&end_date="+$("#end_date").val()+"&status="+$("#status").val()+"&type="+$("#inspection_type").val());
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

    init();
});
