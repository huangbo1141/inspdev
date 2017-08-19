
function update(k, id) {
    $("#edit_detail_kind").val(k);
    $("#edit_detail_id").val(id);

    if (k != 'add' && id == '') {
        return;
    }

    if (k == 'add' || k == 'edit') {
        $("#form_move_edit").submit();
    }

    if (k == 'delete') {
        bootbox.confirm("Are you sure to delete?", function (result) {
            if (result) {
                showLoading();

                $.ajax({
                    type: "POST",
                    url: 'delete',
                    data: {
                        builder_id: id
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
}

jQuery(document).ready(function () {
    showAlert($("#msg_alert").html());

    $("#btn_add").on('click', function (e) {
        e.preventDefault();

        update('add', '');
    });

    $('#table_content').dataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "ajax": {
            "url": "load",
            "type": "POST"
        },
//        'searching' : false,
        "order": [[0, "asc"]],
        "columnDefs": [
            {
                "targets": [-1, -2],
                "orderable": false
            },
            {
                "targets": "_all",
                "searchable": false
            }
        ],
        "columns": [
            {
                "data": "name",
            },
            {
                "data": "contact",
            },
            {
                "data": "address",
            },
            {
                "data": "city",
            },
            {
                "data": "state",
            },
            {
                "data": "zip",
            },
            {
                "data": "phone",
            },
            {
                "data": "email",
            },
            {
                "data": "fees",
            },
            {
                "data": "additional",
                "render": function (data, type, row, meta) {
                    var data = "";

                    data += '<div class="btn-group"> ' +
                        '<button type="button" class="btn default dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true"> Action <i class="fa fa-angle-down pull-right"></i> </button>' +
                        '<ul class="dropdown-menu bottom-up pull-right" role="menu">' +
                        '<li class="divider"></li>';

                    if ($("#user_permission").val()=='1') {
                        data += '<li><a href="javascript:update(\'edit\', \'' + row.id + '\')"><i class="fa fa-edit"></i> Edit</a></li>';
                        data += '<li><a href="javascript:update(\'delete\', \'' + row.id + '\')"><i class="fa fa-trash-o"></i> Delete</a></li>';
                    }

                    data += '<li class="divider"></li>';
                    data += '</ul>' +
                        '</div>' +
                        '';

                    return data;
                }
            }
        ]
    });

    $('#table_content').on('draw.dt', function () {
        $('#table_content').removeClass('display').addClass('table table-striped table-bordered');
        $('#table_content tr td:nth-child(7)').addClass('center');
        $('#table_content tr td:nth-child(8)').addClass('center');
        $('#table_content tr td:nth-child(9)').addClass('text-right');
        $('#table_content tr td:nth-child(10)').addClass('center');
    });


});
