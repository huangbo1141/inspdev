function activate(k, s) {
    showLoading();
    
    $.ajax({
        type: "POST",
        url: 'activate',
        data: {
            user_id: k,
            status: s
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
    $("#edit_detail_kind").val(k);
    $("#edit_detail_id").val(id);

    if (k != 'add' && id == '') {
        return;
    }

    if (k == 'add' || k == 'profile' || k == 'password') {
        $("#form_move_edit").submit();
    }

    if (k == 'delete') {
        bootbox.confirm("Are you sure to delete?", function (result) {
            if (result) {
                showLoading();
                
                $.ajax({
                    type: "POST",
                    url: 'delete_user.html',
                    data: {
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

    $('#table_content').dataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "ajax": {
            "url": "load_inspector.html",
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
                "data": "first_name",
            },
            {
                "data": "last_name",
            },
            {
                "data": "phone_number",
            },
            {
                "data": "address",
            },
            {
                "data": "fee",
                "render": function (data, type, row, meta) {
                    var d = "";
                    
                    if (data!=null && !isNaN(data)) {
                        var p = parseFloat(data);
                        if (p>0) {
                            d = '<span class="label label-success"><i class="fa fa-dollar"></i> '+p.toFixed(2)+'</span>';
                        } else {
                            d = '<span class="label label-default"><i class="fa fa-dollar"></i> '+p.toFixed(2)+'</span>';
                        }
                    } else {
                        d = '<span class="label label-default"><i class="fa fa-dollar"></i> 0.00</span>';
                    }
                    
                    return d;
                }
            },
            {
                "data": "ip_address",
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
                    
                    var dropdown_direction = "";
                    if (row.index>3) {
                        dropdown_direction = "bottom-up";
                    }
                    
                    data += '<div class="btn-group"> ' +
                            '<button type="button" class="btn default dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true"> Action<i class="fa fa-angle-down"></i> </button>' +
                            '<ul class="dropdown-menu '+ dropdown_direction +' pull-right" role="menu">' +
                            '<li class="divider"></li>';
                    
                    if (row.status=='1') {
                        data += '<li><a href="javascript:activate(\'' + row.id + '\', \'0\')"><i class="fa fa-ban"></i> Deactivate</a></li>';
                    } else  {
                        data += '<li><a href="javascript:activate(\'' + row.id + '\', \'1\')"><i class="fa fa-check-circle-o"></i> Activate</a></li>';
                    }
                    
                    if ($("#user_permission").val()=='1') {
                        data += '<li class="divider"></li>';
                        data += '<li><a href="javascript:update(\'profile\', \'' + row.id + '\')"><i class="fa fa-edit"></i> Edit Profile</a></li>';
                        data += '<li><a href="javascript:update(\'password\', \'' + row.id + '\')"><i class="fa fa-lock"></i> Change Password</a></li>';
                        
                        data += '<li class="divider"></li>';
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
        $('#table_content tr td:nth-child(4)').addClass('center');
        $('#table_content tr td:nth-child(6)').addClass('center');
        $('#table_content tr td:nth-child(7)').addClass('center');
        $('#table_content tr td:nth-child(8)').addClass('center');
        $('#table_content tr td:nth-child(9)').addClass('center');
    });


});
