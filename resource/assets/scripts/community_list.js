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
                        community_id: id
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
                "data": "community_id",
            },
            {
                "data": "community_name",
            },
            {
                "data": "city",
            },
            {
                "data": "region_name",
            },
            {
                "data": "builder_name",
            },
            {
                "data": "additional",
                "render": function (data, type, row, meta) {
                    var data = "";
                    
                    if ($("#user_permission").val()=='0' || $("#user_permission").val()=='4') {
                        
                    } else {
                        data += '<div class="btn-group"> ' +
                                '<button type="button" class="btn default dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true"> Action<i class="fa fa-angle-down"></i> </button>' +
                                '<ul class="dropdown-menu bottom-up pull-right" role="menu">' +
                                '<li class="divider"></li>';

                        if ($("#user_permission").val()=='1') {
                            data += '<li><a href="javascript:update(\'edit\', \'' + row.id + '\')"><i class="fa fa-edit"></i> Edit</a></li>';
                            data += '<li><a href="javascript:update(\'delete\', \'' + row.id + '\')"><i class="fa fa-trash-o"></i> Delete</a></li>';
                        } else {
                            data += '<li><a href="javascript:update(\'edit\', \'' + row.id + '\')"><i class="fa fa-search"></i> View</a></li>';
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
        $('#table_content tr td:nth-child(4)').addClass('center');
        $('#table_content tr td:nth-child(5)').addClass('center');
        $('#table_content tr td:nth-child(6)').addClass('center');
    });


});
