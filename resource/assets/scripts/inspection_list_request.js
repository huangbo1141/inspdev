var units = null;
var bulk_index = 0;

function edit(k, c) {
    if (c == "3") {
        $("#detail_id2").val(k);
        $("#form_move_wci").submit();
    } else if (c == "4") {
        $("#detail_id4").val(k);
        $("#form_move_wci_pulte").submit();
    } else {
        $("#detail_id1").val(k);
        $("#form_move_pulte").submit();
    }
}

function cancel(k) {
    bootbox.confirm("Are you sure to cancel?", function (result) {
        if (result) {
            showLoading();

            $.ajax({
                type: "POST",
                url: 'delete_requested_inspection',
                data: {
                    id: k,
                },
                dataType: 'json',
                success: function (data) {
                    hideLoading();
                    if (data.err_code == 0) {
                        showAlert("Successfully Cancelled!");
                        $('#table_content').dataTable().api().ajax.reload();
                    } else {
                        showAlert("Failed to cancel!");
                    }
                },
                error: function () {
                    hideLoading();
                    showAlert(Message.SERVER_ERROR);
                }
            });
        }
    });
}

function ask_unit() {
    $('body').css('cursor', 'default');

    if (units == null || units.length < 1) {
        return;
    }

    start_unit();
}

function start_unit() {
    showLoading();
    bulk_index = 0;
    enter_unit();
}

function enter_unit() {
    $("#unit_dialog .modal-header .modal-title").html("New Community Found <b>" + units[bulk_index].community_name + '</b>');
    var subject = " " // Job Number : " + units[bulk_index].job_number + "<br>"
            +
            'Please Enter Community ID (6 digits) : ' + '' + '<br>';

    $("#unit_dialog .modal-body h4").html(subject);
    $("#unit_dialog .modal-body .address-area").html('');
    var community_id = units[bulk_index].community_id;
    $("#number_of_units").val(community_id);

    hideLoading();
    $("#unit_dialog").modal('show');
}

var next_unit = function () {
    bulk_index++;
    if (bulk_index >= units.length) {
        update_unit();
    } else {
        enter_unit();
    }
};

function update_unit() {
    var data = [];
    $.each(units, function (index, row) {
        data[index] = {
            id: row.id,
            community_id: row.community_id,
        };
    });

    $.ajax({
        type: "POST",
        url: 'update_community',
        data: {
            units: JSON.stringify(data),
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();
            if (data.err_code == 0) {
                //showAlert("Successfully Updated!");
                //$('#table_content').dataTable().api().ajax.reload();
                location.href = "";
            } else {
                showAlert("Failed to Update!");
            }
        },
        error: function () {
            hideLoading();
            showAlert(Message.SERVER_ERROR);
        }
    });
}

function delete_unit(job_number) {
    bootbox.confirm("Are you sure to delete unit?", function (result) {
        if (result) {
            showLoading();

            $.ajax({
                type: "POST",
                url: 'delete_unit',
                data: {
                    job_number: job_number
                },
                dataType: 'json',
                success: function (data) {
                    hideLoading();
                    if (data.err_code == 0) {
                        showAlert("Successfully deleted!");
                        $('#table_content').dataTable().api().ajax.reload();
                    } else {
                        showAlert("Failed to delete!");
                    }
                },
                error: function () {
                    hideLoading();
                    showAlert(Message.SERVER_ERROR);
                }
            });
        }
    });
}
$("#number_of_units").attr('maxlength', '6');
// $("#number_of_units").inputmask("999999", {
//   placeholder: 'x'
// });

jQuery(document).ready(function () {
    showAlert($("#msg_alert").html());



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
            "url": "load_list_request",
            "type": "POST",
            "data": function (d) {
                d.start_date = $("#start_date").val();
                d.end_date = $("#end_date").val();
                d.type = $("#inspection_type").val();
                d.status = $("#status").val();
            }
        },
        //        'searching' : false,
        "order": [
            [0, "desc"]
        ],
        "columnDefs": [
            //            {
            //                "targets": [-1],
            //                "orderable": false
            //            },
            {
                "targets": "_all",
                "searchable": false
            }
        ],
        "columns": [{
                "data": "requested_at",
            },
            {
                "data": "community_name",
            },
            {
                "data": "job_number",
            },
            {
                "data": "address",
            },
            {
                "data": "city",
                "render": function (data, type, row, meta) {
                    var d = "";

                    if (row.category == '3') {
                        if (row.city_duct != null && row.city_duct != "") {
                            d += row.city_duct;
                        }
                    } else {
                        if (row.city != null && row.city != "") {
                            d += row.city;
                        }
                    }

                    return d;
                }
            },
            {
                "data": "additional",
                "render": function (data, type, row, meta) {
                    var d = "";

                    if (row.first_name != null && row.last_name != null) {
                        d += row.first_name;
                        d += " ";
                        d += row.last_name;
                    }

                    return d;
                }
            },
            {
                "data": "category_name",
            },
            {
                "data": "time_stamp",
                "render": function (data, type, row, meta) {
                    var d = "";
                    if (data != null)
                        d = data.substring(0, 4) + "-" + data.substring(4, 6) + "-" + data.substring(6, 8) + " " + data.substring(8, 10) + ":" + data.substring(10, 12) + ":" + data.substring(12, 14);
                    return d;
                }
            },
            {
                "data": "inspector_name",
                "render": function (data, type, row, meta) {
                    var d = data;


                    return d;
                }
            },
            {
                "data": "status",
                "render": function (data, type, row, meta) {
                    var d = '';

                    if (data == 2) {
                        d += '<span class="label label-success">Completed</span>';
                    } else if (data == 1) {
                        d += '<span class="label label-primary">Assigned</span>';
                    } else {
                        d += '<span class="label label-default">Unassigned</span>';
                    }

                    return d;
                }
            },
            {
                "data": "additional",
                "render": function (data, type, row, meta) {
                    var data = "";

                    var p = $("#user_permission").val();
                    //  2 = Field Manager
                    //                      Pulte kind 2 builder 1
                    //                      wci   kind 2 builder 2
                    //  1  = ADMIN kind 1 builder 0
                    if (row.status == '2') {
                        // status completed
                    } else {
                        // 1 =  assigned
                        // 0 =  unassigned
                        if (p == '1' || (row.status == 0 && p == '2') || (row.status == 1 && p == '2')) {
                            data += '<a href="javascript:edit(\'' + row.id + '\', \'' + row.category + '\')" title="Edit" class="btn"><i class="fa fa-pencil"></i></a>';
                        }
                        if (p == '1' || (row.status == 0 && p == '2')) {
                            data += '<a href="javascript:cancel(\'' + row.id + '\')" title="Cancel" class="btn"><i class="fa fa-trash-o"></i></a>';
                        }
                    }

                    return data;
                }
            },
        ]
    });

    $('#table_content').on('draw.dt', function () {
        $('#table_content').removeClass('display').addClass('table table-striped table-bordered');
        $('#table_content tr td:nth-child(1)').addClass('center');
        //        $('#table_content tr td:nth-child(2)').addClass('center');
        $('#table_content tr td:nth-child(3)').addClass('center');
        //        $('#table_content tr td:nth-child(4)').addClass('center');
        //        $('#table_content tr td:nth-child(5)').addClass('center');
        $('#table_content tr td:nth-child(6)').addClass('center');
        $('#table_content tr td:nth-child(7)').addClass('center');
        $('#table_content tr td:nth-child(8)').addClass('center');
        $('#table_content tr td:nth-child(9)').addClass('center');
        $('#table_content tr td:nth-child(10)').addClass('center');
        $('#table_content tr td:nth-child(11)').addClass('center');
    });

    $("#btn_check").on('click', function (e) {
        var modalform = $("#entirepage");
        $.ajax({
            type: "POST",
            url: 'testme',
            data: null,
            success: function (resp, status, xhr) {
                if (resp != null) {
                    var ret = resp.response;
                    if (ret == 200) {
                        //location.href = "";
                        if (resp.array_community == null) {
                            location.href = "";
                        } else {
                            units = resp.array_community;
                            ask_unit();
                        }
                    }
                }
                Metronic.unblockUI(modalform);

            },
            error: function (request, status, error) {
                alert("There's network error");
                Metronic.unblockUI(modalform);
            },
            dataType: 'json'
        });
        Metronic.blockUI({
            message: 'Processing...',
            target: modalform,
            overlayColor: 'none',
            cenrerY: true,
            centerX: true,
            boxed: true
        });
        e.preventDefault();
    });

    $("#btn_view").on('click', function (e) {
        e.preventDefault();
        $('#table_content').dataTable().api().ajax.reload();
    });

    $("#btn_export").on('click', function (e) {
        e.preventDefault();

        $.fileDownload($("#basePath").val() + "api/export/requested_inspection?file_format=csv&status=" + $("#status").val() + "&start_date=" + $("#start_date").val() + "&end_date=" + $("#end_date").val() + "&type=" + $("#inspection_type").val());
    });

    $("#unit_dialog").on('click', '.btn-primary', function (e) {
        e.preventDefault();
        if ($("#number_of_units").val() == "") {
            App.showMessage("Please Enter Community ID!");
        } else if ($("#number_of_units").val().length != 6) {
            App.showMessage("Please Enter Community ID (6 digits)");
        } else {
            var n = $("#number_of_units").val();
            var ret = true;

            var address = [];

            units[bulk_index].community_id = n;
            $("#unit_dialog").modal('hide');

            showLoading();
            setTimeout(next_unit, 500);
        }
    });
});
