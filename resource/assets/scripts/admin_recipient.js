function activate(k, s) {
    showLoading();

    $.ajax({
        type: "POST",
        url: 'activate_recipient',
        data: {
            id: k,
            status: s
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();

            if (data.err_code == 0) {
                if (s == '1')
                    showAlert("Successfully Activated!");
                else
                    showAlert("Successfully Deactivated!");

                $('#table_content').dataTable().api().ajax.reload();
            } else {
                showAlert("Failed to update!");
            }
        },
        error: function () {
            hideLoading();
            showAlert(Message.SERVER_ERROR);
        }
    });
}

function remove(s) {
    bootbox.confirm("Are you sure to delete?", function (result) {
        if (result) {
            showLoading();

            $.ajax({
                type: "POST",
                url: 'delete_recipient.html',
                data: {
                    id: s
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


function show_dialog(kind, id, n) {
    $("#detail_kind").val(kind);
    $("#detail_id").val(id);

    if (kind == 'add') {
        $("#detail_dialog h2.modal-title").html("Add Recipient Email");
        $("#detail_dialog .btn-primary").html('Add');
        $("#recipient_email").val('');
    } else {
        $("#detail_dialog h2.modal-title").html("Edit Recipient Email");
        $("#detail_dialog .btn-primary").html('Update');
        $("#recipient_email").val(n);
    }

    $("#detail_dialog").modal('show');
}

jQuery(document).ready(function () {
    showAlert($("#msg_alert").html());

    $("#btn_add").on('click', function(e) {
        e.preventDefault();
        
        show_dialog('add', '', '');
    });


    $("#detail_dialog button.btn-primary").on('click', function () {
        if ($("#recipient_email").val() == '') {
            alert('Please input email address!');
            return false;
        }

        $("#detail_dialog").modal('hide');
        showLoading();

        $.ajax({
            type: "POST",
            url: 'update_recipient.html',
            data: { kind: $("#detail_kind").val(), id: $("#detail_id").val(),  email: $("#recipient_email").val()    },
            dataType: 'json',
            success: function (data) {
                hideLoading();
                
                showAlert(data.err_msg);
                if (data.err_code == 0) {
                    $('#table_content').dataTable().api().ajax.reload();
                } else {
                }
            },
            error: function () {
                hideLoading();
                showAlert(Message.SERVER_ERROR);
            }
        });
    });

    $('#table_content').dataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "ajax": {
            "url": "load_recipient.html",
            "type": "POST"
        },
//        'searching' : false,
        "order": [[0, "asc"]],
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
                "data": "email",
            },
            {
                "data": "status",
                "render": function (data, type, row, meta) {
                    var data = "";
                    if (row.status == '1')
                        data = '<span class="label label-primary">Activated</span>';
                    else
                        data = '<span class="label label-default">Deactivated</span>';

                    return data;
                }
            },
            {
                "data": "additional",
                "render": function (data, type, row, meta) {
                    var data = "";
                    var has = false;

                    var dropdown_direction = "";
                    if (row.index>1) {
                        dropdown_direction = "bottom-up";
                    }

                    data += '<div class="btn-group"> ' +
                            '<button type="button" class="btn default dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true"> Action<i class="fa fa-angle-down"></i> </button>' +
                            '<ul class="dropdown-menu '+ dropdown_direction +' pull-right" role="menu">' +
                            '<li class="divider"></li>';

                    if ($("#user_permission").val() == '1') {
                        if (row.status == '1') {
                            data += '<li><a href="javascript:activate(\'' + row.id + '\', \'0\')"><i class="fa fa-ban"></i> Deactivate</a></li>';
                        } else {
                            data += '<li><a href="javascript:activate(\'' + row.id + '\', \'1\')"><i class="fa fa-check-circle-o"></i> Activate</a></li>';
                        }

                        data += '<li class="divider"></li>';

                        data += '<li><a href="javascript:show_dialog(\'edit\', \'' + row.id + '\', \'' + row.email + '\')"><i class="fa fa-pencil-square-o"></i> Edit</a></li>';
                        data += '<li><a href="javascript:remove(\'' + row.id + '\')"><i class="fa fa-trash-o"></i> Delete</a></li>';
                        has = true;
                    }

                    data += '<li class="divider"></li>';
                    data += '</ul>' +
                            '</div>' +
                            '';

                    return has ? data : "";
                }
            }
        ]
    });

    $('#table_content').on('draw.dt', function () {
        $('#table_content').removeClass('display').addClass('table table-striped table-bordered');
        $('#table_content tr td:nth-child(2)').addClass('center');
        $('#table_content tr td:nth-child(3)').addClass('center');
    });


});
