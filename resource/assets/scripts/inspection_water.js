var inspection_id = "";

function detail(k) {
    $("#detail_id").val(k);
    $("#form_move_detail").submit();
}

function edit(k) {
    $("#detail_id2").val(k);
    $("#form_move_edit").submit();
}

function generate(t, k) {
    $.fileDownload($("#basePath").val()+"api/export/inspection?id="+k+"&type="+t);
}

function remove(id) {
    bootbox.confirm({
        title: '<span style="color:#e00;">Warning!!</span>',
        message: 'Are you sure to delete this inspection?',
        buttons: {
            'cancel': {
                label: 'No',
                className: 'btn-default'
            },
            'confirm': {
                label: 'Yes',
                className: 'btn-danger'
            }
        },
        callback: function(result) {
            if (result) {
                delete_inspection(id);
            } else {
            }
        }
    });
}

function delete_inspection(id) {
    showLoading();

    $.ajax({
        type: "POST",
        url: 'delete_inspection',
        data: {
            id: id,
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();
            if (data.err_code == 0) {
                showAlert("Successfully Deleted!");
                $('#table_content').dataTable().api().ajax.reload();
            } else {
                showAlert("Failed to Delete!");
            }
        },
        error: function () {
            hideLoading();
            showAlert(Message.SERVER_ERROR);
        }
    });    
}

function report(id) {
    inspection_id = id; 
    $("#recipients").val("");
    $("#export_confirm_dialog").modal('show');
}

function email_report(recipients) {
    showLoading();
    
    $.ajax({
        type: "POST",
        url: $("#basePath").val() + 'api/email/inspection',
        data: {
            id: inspection_id,
            recipient: recipients,
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();
            if (data.code == 0) {
                showAlert("Successfully Sent! Check your Inbox");
            } else {
                showAlert(data.message);
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

    $('#table_content').dataTable({
        "processing": true,
        "serverSide": true,
        "responsive": false,
        "ajax": {
            "url": "load_list",
            "type": "POST",
            data: {
                type: '1_2',
            }
        },
//        'searching' : false,
        "order": [[3, "desc"]],
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
                "data": "inspection_type",
            },
            {
                "data": "job_number",
            },
            {
                "data": "address",
            },
            {
                "data": "start_date",
            },
            {
                "data": "first_name",
                "render": function (data, type, row, meta) {
                    var d = "";
                    
                    if (row.first_name!=null && row.first_name!="") {
                        d += row.first_name + " ";
                    }
                    
                    if (row.last_name!=null && row.last_name!="") {
                        d += row.last_name;
                    }
                    
                    return d;
                }
            },
            {
                "data": "email",
            },
            {
                "data": "region_name",
            },
            {
                "data": "result_name",
                "render": function (data, type, row, meta) {
                    var d = "";
                    
                    if (row.type=='3') {
                        if (row.result_duct_leakage=='1') {
                            d += '<span class="label label-success">';
                        }
                        if (row.result_duct_leakage=='3') {
                            d += '<span class="label label-danger">';
                        }
                        
                        d += "Duct: ";
                        d += row.result_duct_leakage_name;
                        
                        d += "</span>";
                        
                        d += "<br>";
                        d += "<br>";
                        
                        if (row.result_envelop_leakage=='1') {
                            d += '<span class="label label-success">';
                        }
                        if (row.result_envelop_leakage=='2') {
                            d += '<span class="label label-warning">';
                        }
                        if (row.result_envelop_leakage=='3') {
                            d += '<span class="label label-danger">';
                        }
                        
                        d += "Envelope: ";
                        
                        if (row.result_envelop_leakage=='2') {
                            d += "Pass";
                        } else {
                            d += row.result_envelop_leakage_name;
                        }
                        
                        d += "</span>";
                    } else {
                        if (row.result_code=='1') {
                            d += '<span class="label label-success">';
                        }
                        if (row.result_code=='2') {
                            d += '<span class="label label-warning">';
                        }
                        if (row.result_code=='3') {
                            d += '<span class="label label-danger">';
                        }

                        if (row.result_code=='2') {
                            d += "Pass";
                        } else {
                            d += row.result_name;
                        }
                        
                        d += "</span>";
                    }
                    
                    return d;
                }
            },
            {
                "data": "is_first",
                "render": function (data, type, row, meta) {
                    var data = "";
                    if (row.house_ready=='1') {
                        data += '<span class="label label-primary">House Ready</span>';
                    } else {
                        data += '<span class="label label-info">House Not Ready</span>';
                    }
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
                            '<ul class="dropdown-menu '+dropdown_direction + ' pull-right" role="menu">' +
                            '<li class="divider"></li>';
                    
                    data += '<li><a href="javascript:detail(\'' + row.id + '\')"><i class="fa fa-search"></i> View Details</a></li>';
                    
                    if ($("#user_permission").val()=='1') {
                        data += '<li><a href="javascript:edit(\'' + row.id + '\')"><i class="fa fa-edit"></i> Edit</a></li>';
                    }
                    if ($("#user_permission").val()=='2' && row.result_code=='2') {
                        data += '<li><a href="javascript:edit(\'' + row.id + '\')"><i class="fa fa-edit"></i> Edit</a></li>';
                    }
                    
                    if (row.type=='3') {
                        data += '<li><a href="javascript:generate(\'duct\', \'' + row.id + '\')"><i class="fa fa-file-pdf-o"></i> Generate Report(Duct Leakage)</a></li>';
                        data += '<li><a href="javascript:generate(\'envelop\', \'' + row.id + '\')"><i class="fa fa-file-pdf-o"></i> Generate Report(Envelope Leakage)</a></li>';
                    } else {
                        data += '<li><a href="javascript:generate(\'full\', \'' + row.id + '\')"><i class="fa fa-file-pdf-o"></i> Generate Report(Full)</a></li>';
                        data += '<li><a href="javascript:generate(\'pass\', \'' + row.id + '\')"><i class="fa fa-file-pdf-o"></i> Generate Report(Without PASS)</a></li>';
                    }
                    
                    data += '<li><a href="javascript:report(\'' + row.id + '\')"><i class="fa fa-inbox"></i> Report</a></li>';
                    
                    if ($("#user_permission").val()=='1') {
                        data += '<li><a href="javascript:remove(\'' + row.id + '\')"><i class="fa fa-trash-o"></i> Delete</a></li>';
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
        $('#table_content tr td:nth-child(1)').addClass('center');
        $('#table_content tr td:nth-child(2)').addClass('center');
        $('#table_content tr td:nth-child(4)').addClass('center');
        $('#table_content tr td:nth-child(6)').addClass('center');
        $('#table_content tr td:nth-child(7)').addClass('center');
        $('#table_content tr td:nth-child(8)').addClass('center');
        $('#table_content tr td:nth-child(9)').addClass('center');
        $('#table_content tr td:nth-child(10)').addClass('center');
    });

    $("#export_confirm_dialog").on('click', '.modal-footer .btn-primary', function(e) {
        e.preventDefault();
        email_report($("#recipients").val());
        $("#export_confirm_dialog").modal('hide');
    });

});
