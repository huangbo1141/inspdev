function activate(k, s) {
    showLoading();
    
    $.ajax({
        type: "POST",
        url: $("#basePath").val() + 'manager/activate',
        data: {
            user_id: k,
            status: s,
            type: $("#edit_detail_type").val()
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();
            
            if (data.err_code==0) {
                if (s=='1')
                    showAlert("Successfully Activated!");
                else
                    showAlert("Successfully Deactivated!");
                
                $('#table_content').dataTable().api().ajax.reload();
            }else {
                showAlert("Failed to update!");
            }            
        },
        error: function () {
            hideLoading();
            showAlert(Message.SERVER_ERROR);
        }
    });
}

function update(k, id) {
    if (k != 'add' && id == '') {
        return;
    }

    if (k == 'add') {
        $("#form_move_add").submit();
    }
    
    if (k == 'profile') {
        $("#edit_detail_id2").val(id);
        $("#form_move_profile").submit();
    } 
    
    if (k == 'password') {
        $("#edit_detail_id3").val(id);
        $("#form_move_password").submit();
    } 

    if (k == 'delete') {
        bootbox.confirm("Are you sure to delete?", function (result) {
            if (result) {
                showLoading();
                
                $.ajax({
                    type: "POST",
                    url: 'delete_user',
                    data: {
                        type: $("#edit_detail_type").val(),
                        user_id: id
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

    var datatable_options = {
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "ajax": {
            "url": "load",
            "data": {
                "type": $("#edit_detail_type").val(),
            },
            "type": "POST"
        },
//        'searching' : false,
        "order": [[0, "asc"]],
    };

    var page_type = $("#edit_detail_type").val();
    if(page_type=='2' || page_type=='3' || page_type=='4') {
        datatable_options.columnDefs = [
            {
                "targets": [-1, -2, -4, -5],
                "orderable": false
            },
            {
                "targets": "_all",
                "searchable": false
            }
        ];
    } else {
        datatable_options.columnDefs = [
            {
                "targets": [-1, -2, -3],
                "orderable": false
            },
            {
                "targets": "_all",
                "searchable": false
            }
        ];
    }
    
    if(page_type=='2' || page_type=='3' || page_type=='4') {
        datatable_options.columns = [
            {
                "data": "email",
            },
            {
                "data": "first_name",
            },
            {
                "data": "last_name",
            },
            {
                "data": "address",
            },
            {
                "data": "cell_phone",
                "render": function (data, type, row, meta) {
                    var data = "";
                    if (row.cell_phone!=null && row.cell_phone != '') {
                        data += '<span class="label label-primary">Cell: ' + row.cell_phone + '</span>';
                    }

                    if (row.other_phone!=null && row.other_phone != '') {
                        data += '<br><br>';
                        data += '<span class="label label-default">Other: ' + row.other_phone + '</span>';
                    }
                    return data;
                }
            },
            {
                "data": "region_name",
            },
            {
                "data": "builder_name",
            },
            {
                "data": "status",
                "render": function (data, type, row, meta) {
                    var data = "";
                    if (row.status=='1') 
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
                    var h = false;                    

                    var dropdown_direction = "";
                    if (row.index>3) {
                        dropdown_direction = "bottom-up";
                    }

                    data += '<div class="btn-group"> ' +
                            '<button type="button" class="btn default dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true"> Action<i class="fa fa-angle-down"></i> </button>' +
                            '<ul class="dropdown-menu '+ dropdown_direction +' pull-right" role="menu">' +
                            '<li class="divider"></li>';

                    if ($("#user_permission").val()=='1' || ($("#user_permission").val()=='0' || $("#edit_detail_type").val()!='1')) {
                        if (row.status=='1') {
                            data += '<li><a href="javascript:activate(\'' + row.id + '\', \'0\')"><i class="fa fa-ban"></i> Deactivate</a></li>';
                            data += '<li class="divider"></li>';
                        } else  {
                            data += '<li><a href="javascript:activate(\'' + row.id + '\', \'1\')"><i class="fa fa-check-circle-o"></i> Activate</a></li>';
                            data += '<li class="divider"></li>';
                        }

                        h = true;
                    }

                    if ($("#user_permission").val()=='1' || ($("#user_permission").val()=='0' || $("#edit_detail_type").val()!='1')) {
                        data += '<li><a href="javascript:update(\'profile\', \'' + row.id + '\')"><i class="fa fa-edit"></i> Edit Profile</a></li>';
                        data += '<li><a href="javascript:update(\'password\', \'' + row.id + '\')"><i class="fa fa-lock"></i> Change Password</a></li>';
                        data += '<li><a href="javascript:update(\'delete\', \'' + row.id + '\')"><i class="fa fa-trash-o"></i> Delete</a></li>';

                        h = true;
                    }

                    data += '<li class="divider"></li>';
                    data += '</ul>' +
                            '</div>' +
                            '';

                    return h ? data : "";
                }
            }
        ];
    } else {        
        datatable_options.columns = [
            {
                "data": "email",
            },
            {
                "data": "first_name",
            },
            {
                "data": "last_name",
            },
            {
                "data": "address",
            },
            {
                "data": "cell_phone",
                "render": function (data, type, row, meta) {
                    var data = "";
                    if (row.cell_phone!=null && row.cell_phone != '') {
                        data += '<span class="label label-primary">Cell: ' + row.cell_phone + '</span>';
                    }

                    if (row.other_phone!=null && row.other_phone != '') {
                        data += '<br><br>';
                        data += '<span class="label label-default">Other: ' + row.other_phone + '</span>';
                    }
                    return data;
                }
            },
            {
                "data": "status",
                "render": function (data, type, row, meta) {
                    var data = "";
                    if (row.status=='1') 
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
                    var h = false;                    

                    data += '<div class="btn-group"> ' +
                            '<button type="button" class="btn default dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true"> Action<i class="fa fa-angle-down"></i> </button>' +
                            '<ul class="dropdown-menu bottom-up pull-right" role="menu">' +
                            '<li class="divider"></li>';

                    if ($("#user_permission").val()=='1' || ($("#user_permission").val()=='0' || $("#edit_detail_type").val()!='1')) {
                        if (row.status=='1') {
                            data += '<li><a href="javascript:activate(\'' + row.id + '\', \'0\')"><i class="fa fa-ban"></i> Deactivate</a></li>';
                            data += '<li class="divider"></li>';
                        } else  {
                            data += '<li><a href="javascript:activate(\'' + row.id + '\', \'1\')"><i class="fa fa-check-circle-o"></i> Activate</a></li>';
                            data += '<li class="divider"></li>';
                        }

                        h = true;
                    }

                    if ($("#user_permission").val()=='1' || ($("#user_permission").val()=='0' || $("#edit_detail_type").val()!='1')) {
                        data += '<li><a href="javascript:update(\'profile\', \'' + row.id + '\')"><i class="fa fa-edit"></i> Edit Profile</a></li>';
                        data += '<li><a href="javascript:update(\'password\', \'' + row.id + '\')"><i class="fa fa-lock"></i> Change Password</a></li>';
                        data += '<li><a href="javascript:update(\'delete\', \'' + row.id + '\')"><i class="fa fa-trash-o"></i> Delete</a></li>';

                        h = true;
                    }

                    data += '<li class="divider"></li>';
                    data += '</ul>' +
                            '</div>' +
                            '';

                    return h ? data : "";
                }
            }
        ];
    }

    
    $('#table_content').dataTable(datatable_options);

    $('#table_content').on('draw.dt', function () {
        $('#table_content').removeClass('display').addClass('table table-striped table-bordered');
        $('#table_content tr td:nth-child(5)').addClass('center');
        $('#table_content tr td:nth-child(6)').addClass('center');
        $('#table_content tr td:nth-child(7)').addClass('center');
        $('#table_content tr td:nth-child(8)').addClass('center');
        $('#table_content tr td:nth-child(9)').addClass('center');
    });


});
