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

function send_report() {
    $("#recipients").val("");
    $("#email_confirm_dialog").modal('show');
}

function email_report(recipients) {
    showLoading();
    
    $.ajax({
        type: "POST",
        url: $("#basePath").val() + 'api/email/statistics/inspector',
        data: {
            region: $("#region").val(),
            start_date : $("#start_date").val(),
            end_date : $("#end_date").val(),
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
    });
    
    $('#table_content').dataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "ajax": {
            "url": "load_inspector",
            "type": "POST",
            "data": function (d){
                d.region = $("#region").val();
                d.start_date = $("#start_date").val();
                d.end_date = $("#end_date").val();
                d.type = $("#inspection_type").val();
            }
        },
        "order": [[0, "asc"]],
        "pageLength": 100,
        "columnDefs": [
            {
                "targets": [2,3,4,5,6, 1, -1],
                "orderable": false
            },
            {
                "targets": "_all",
                "searchable": false
            }
        ],
        "columns": [
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
                "data": "inspections",
            },
            {
                "data": "not_ready",
            },
            {
                "data": "pass",
            },
            {
                "data": "pass_with_exception",
            },
            {
                "data": "fail",
            },
            {
                "data": "reinspection",
            },
            {
                "data": "fee",
            },
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
        $('#table_content tr td:nth-child(8)').addClass('center');
        $('#table_content tr td:nth-child(9)').addClass('center');
    });
    
    $("#btn_view").on('click', function(e) {
        e.preventDefault();
        $('#table_content').dataTable().api().ajax.reload();
    });
    
    $("#btn_export").on('click', function(e) {
        e.preventDefault();
        $.fileDownload($("#basePath").val()+"api/export/statistics/inspector?file_format=pdf&region="+$("#region").val()+"&start_date="+$("#start_date").val()+"&end_date="+$("#end_date").val()+ "&type="+$("#inspection_type").val());
    });
    $("#btn_export_csv").on('click', function(e) {
        e.preventDefault();
        $.fileDownload($("#basePath").val()+"api/export/statistics/inspector?file_format=csv&region="+$("#region").val()+"&start_date="+$("#start_date").val()+"&end_date="+$("#end_date").val()+ "&type="+$("#inspection_type").val());
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
