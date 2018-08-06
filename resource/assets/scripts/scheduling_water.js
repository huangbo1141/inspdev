var inspectors = null;

function init() {
    $("#region").trigger('change');

    get_inspectors();
}
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

    today = yyyy + '-' + mm + "-" + dd;
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

function get_inspectors() {
    showLoading();
    $.ajax({
        type: "POST",
        url: 'load_inspector',
        data: {},
        dataType: 'json',
        success: function (data) {
            hideLoading();

            if (data.err_code == 0) {
                inspectors = data.inspector;
            } else {
                showAlert("Failed to load inspectors!");
            }

            init_table();
        },
        error: function () {
            hideLoading();
            showAlert(Message.SERVER_ERROR);
            //            $('form').bootstrapValidator('resetForm', false);

            init_table();
        }
    });
}

function init_table() {
    $('#table_content').dataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "ajax": {
            "url": "load",
            "type": "POST",
            "data": function (d) {
                d.category = "1_2";
                d.region = $("#region").val();
                d.community = $("#community").val();
                d.start_date = $("#start_date").val();
                d.end_date = $("#end_date").val();
                d.status = $("#status").val();
            }
        },
        //        'searching' : false,1
        pageLength: 25,
        "order": [
            [0, "desc"]
        ],
        "columnDefs": [{
                "targets": [-1],
                "orderable": false
            },
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
                "data": "re_inspection",
                // "render": function(data, type, row, meta) {
                //   var d = "";
                //
                //   if (row.category == '3') {
                //     d += row.city_duct;
                //   }
                //
                //   return d;
                // }
            },
            {
                "data": "epo_number",
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
                "render": function (data, type, row, meta) {
                    var d = "";

                    if (row.category_name == 'Drainage Plane') {
                        d = 'DP';
                    } else if (row.category_name == 'Lath') {
                        d = 'L';
                    } else {
                        d = row.category_name;
                    }

                    return d;
                }
            },

            {
                "data": "inspector_id",
                "render": function (data, type, row, meta) {
                    var d = "";

                    d += '<select class="form-control select-picker" id="inspector_' + row.id + '" data-id="' + row.id + '" data-inspector="' + data + '">';
                    d += '<option value="0">No Inspector</option>';

                    $.each(inspectors, function (index, inspector) {
                        d += '<option ' + (inspector.id == row.inspector_id ? "selected" : "") + ' value="' + inspector.id + '">' + inspector.first_name + ' ' + inspector.last_name + '</option>';
                    });

                    d += '</select>';

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
                    var d = "";



                    var p = $("#user_permission").val();
                    //  2 = Field Manager
                    //                      Pulte kind 2 builder 1
                    //                      wci   kind 2 builder 2
                    //  1  = ADMIN kind 1 builder 0
                    if (row.status == '2') {
                        // status completed
                    } else {

                        if (inspectors != null && inspectors.length > 0) {
                            var cls = "not-assigned";
                            //                        if (row.inspector_name!=null && row.inspector_name!="" && row.inspector_name!="0") {
                            //                            cls = "assigned";
                            //                        }
                            d += '<a class="btn btn-link ' + cls + '" id="link_' + row.id + '" href="javascript:assign(\'' + row.id + '\')" title="Assign"><i class="fa fa-flag"></i></a>';
                        }
                        // 1 =  assigned
                        // 0 =  unassigned
                        if (p == '1' || (row.status == 0 && p == '2') || (row.status == 1 && p == '2')) {
                            d += '<a href="javascript:edit(\'' + row.id + '\', \'' + row.category + '\')" title="Edit" class="btn"><i class="fa fa-pencil"></i></a>';
                        }
                        if (p == '1' || (row.status == 0 && p == '2')) {
                            d += '<a href="javascript:cancel(\'' + row.id + '\')" title="Cancel" class="btn"><i class="fa fa-trash-o"></i></a>';
                        }
                    }

                    return d;
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

        $("#table_content tbody .select-picker").selectpicker({
            container: 'body',
            liveSearch: true,
            mobile: true,
        });
    });
}

function assign(inspection_id) {
    var inspector_id = $("#inspector_" + inspection_id).val();
    var old_inspector_id = $("#inspector_" + inspection_id).attr('data-inspector');

    if (inspector_id != old_inspector_id) {
        showLoading();
        $.ajax({
            type: "POST",
            url: 'assign_inspector',
            data: {
                inspection_id: inspection_id,
                inspector_id: inspector_id,
            },
            dataType: 'json',
            success: function (data) {
                hideLoading();

                if (data.err_code == 0) {
                    App.showSuccessMessage(data.err_msg);
                } else {
                    App.showFailedMessage(data.err_msg);
                }

                $('#table_content').dataTable().api().ajax.reload(null, false);
            },
            error: function () {
                hideLoading();
                App.showFailedMessage(Message.SERVER_ERROR);

                $('#table_content').dataTable().api().ajax.reload(null, false);
            }
        });
    }
}

function generate(t, k) {
    $.fileDownload($("#basePath").val() + "api/export/inspection?id=" + k + "&type=" + t);
}

function export_all() {
    showLoading();

    var ordering = "";
    var orders = $('#table_content').DataTable().order();
    $.each(orders, function (index, order) {
        if (ordering != "") {
            ordering += ",,";
        }
        ordering += order[0] + "," + order[1];
    });

    $.each(inspectors, function (index, inspector) {
        setTimeout(function () {
            $.fileDownload($("#basePath").val() + "api/export/scheduling?inspector_id=" + inspector.id + "&ordering=" + ordering + "&region=" + $("#region").val() + "&community=" + $("#community").val() + "&start_date=" + $("#start_date").val() + "&end_date=" + $("#end_date").val() + "&status=" + $("#status").val());
        }, 1200);

        if (inspectors.length == index + 1) {
            hideLoading();
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

    //    $('.date-picker').val(get_formatted_date());

    $('.date-picker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        //        endDate: get_formatted_date(),
        todayBtn: true,
        todayHighlight: true,
    });

    $("#region").change(function (e) {
        get_community();
    });

    $("#btn_view").on('click', function (e) {
        e.preventDefault();
        $('#table_content').dataTable().api().ajax.reload();
        // $.ajax({
        //   type: "POST",
        //   url: "load",
        //   data: {
        //     category: '1_2',
        //     region: $("#region").val(),
        //     community: $("#community").val(),
        //     start_date: $("#start_date").val(),
        //     end_date: $("#end_date").val()
        //   },
        //   dataType: 'json',
        //   success: function(data) {
        //     console.log(data);
        //   },
        //   error: function() {
        //
        //   }
        // });
    });

    $("#btn_export").on('click', function (e) {
        e.preventDefault();

        export_all();
    });

    $('#table_content').on('change', 'tbody tr td select.form-control.select-picker', function (e) {
        var inspector = $(this).val();
        var inspection = $(this).attr('data-id');
        var old_inspector = $(this).attr('data-inspector');

        if (inspector != old_inspector) {
            $("#link_" + inspection).removeClass('not-assigned').addClass('assigned');
        } else {
            $("#link_" + inspection).removeClass('assigned').addClass('not-assigned');
        }
    });

    init();
});
