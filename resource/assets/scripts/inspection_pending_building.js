function get_formatted_date(weeks = 0) {

    var today = new Date();
    today.setDate(today.getDate() + weeks);
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

            if (data.err_code == 0) {

                var html = "";
                $.each(data.community, function (index, row) {
                    html += '<option value="' + row.community_id + '">' + row.community_id + ' - ' + row.community_name + '</option>';
                });

                $("#community").append(html);
            } else {
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
jQuery(document).ready(function () {
    showAlert($("#msg_alert").html());

    $('#start_date').val(get_formatted_date(-3 * 7));
    $('#end_date').val(get_formatted_date());

    $('.date-picker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
//        endDate: get_formatted_date(),
        todayBtn: true,
        todayHighlight: true,
    });

    $('.select-picker').selectpicker({
        liveSearch: true,
        actionsBox: true
    });

    $("#region").change(function (e) {
        get_community();
    });

    $("#btn_view").on('click', function (e) {
        e.preventDefault();
        $('#table_content').dataTable().api().ajax.reload();
        //get_total_count();
    });

    $('#table_content').dataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "ajax": {
            "url": "load_pending_building",
            "type": "POST",
            "data": function (d) {
                d.region = $("#region").val();
                d.community = $("#community").val();
                d.start_date = $("#start_date").val();
                d.end_date = $("#end_date").val();
                d.status1 = $("#status1").val();
                d.status2 = $("#status2").val();
                if (d.start_date != "") {
                    d.start_date = d.start_date.replace(/-/g, "") + "000000";
                }
                if (d.end_date != "") {
                    d.end_date = d.end_date.replace(/-/g, "") + "235959";
                }

            }
        },
//        'searching' : false,
        "order": [[0, "asc"]],
        "columnDefs": [
//            {
//                "targets": [-3, -4],
//                "orderable": false
//            },
            {
                "targets": "_all",
                "searchable": false
            }
        ],
        "columns": [
            {
                "data": "job_number",
            },
            {
                "data": "community",
            },
            {
                "data": "address",
                "render": function (data, type, row, meta) {
                    var d = "";

                    if (row.unit_address != null && row.unit_address != "") {
                        d = row.unit_address;
                    } else {
                        d = data;
                    }

                    return d;
                }
            },
            {
                "data": "additional",
                "render": function (data, type, row, meta) {
                    var d = "";

                    if (row.dp_status == "1") {
                        d += '<span class="label label-success">Yes</span>';
                    } else {
                        d += '<span class="label label-default">No</span>';
                    }

                    return d;
                }
            },
            {
                "data": "additional",
                "render": function (data, type, row, meta) {
                    var d = "";

                    if (row.lath_status == "1") {
                        d += '<span class="label label-success">Yes</span>';
                    } else {
                        d += '<span class="label label-default">No</span>';
                    }

                    return d;
                }
            },
            {
                "data": "field_manager",
                "render": function (data, type, row, meta) {
                    var d = "";

                    if (row.first_name != null && row.first_name != "" && row.last_name != null && row.last_name != "") {
                        d += row.first_name + " " + row.last_name;

                    } else if (row.field_manager != null && row.field_manager != "") {
                        d += '<span class="label label-warning">Unknown</span>';

                    } else {
                        d += '<span class="label label-danger">Unassigned</span>';
                    }

                    return d;
                }
            },
            {
                "data": "additional",
                "render": function (data, type, row, meta) {
                    var d = "";

                    if (parseInt(row.days) > 0) {
                        d += '<span class="label label-warning">' + row.days + ' Days</span>';
                    } else {
                        d += '<span class="label label-info">0 Days</span>';
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
        $('#table_content tr td:nth-child(5)').addClass('center');
        $('#table_content tr td:nth-child(6)').addClass('center');
        $('#table_content tr td:nth-child(7)').addClass('center');
    });


});
