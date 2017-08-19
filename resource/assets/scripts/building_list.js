var units = null;
var bulk_index = 0;

function edit_unit(jn, u) {
    $("#edit_detail_kind").val('edit');
    $("#edit_detail_id").val(jn);
    $("#edit_detail_id2").val(u);
    
    $("#form_move_edit").submit();
}

function update(k, id) {
    $("#edit_detail_kind").val(k);
    $("#edit_detail_id").val(id);
    $("#edit_detail_id2").val("");

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
                        job_number: id
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

function ask_unit() {
    $('body').css('cursor','default');
    
    if (units==null || units.length<1) {
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
    $("#unit_dialog .modal-header .modal-title").html("Enter Units for Job Number <b>" + units[bulk_index].job_number+'</b>');
    var subject = " " // Job Number : " + units[bulk_index].job_number + "<br>"
                + 'Job Address : ' + units[bulk_index].address + '<br>'
                + '   Job Plan : ' + units[bulk_index].plan + '<br>';
    
    $("#unit_dialog .modal-body h4").html(subject);
    $("#unit_dialog .modal-body .address-area").html('');
    $("#number_of_units").val('');

    hideLoading();
    $("#unit_dialog").modal('show');
}

var next_unit = function() {
    bulk_index++;
    if (bulk_index>=units.length) {
        update_unit();
    } else {
        enter_unit();
    }
};

function update_unit() {
    var data = [];
    $.each(units, function(index, row) {
        data[index] = {
            job_number: row.job_number,
            units: row.units,
        };
    });
    
    $.ajax({
        type: "POST",
        url: 'update_unit',
        data: {
            units: JSON.stringify(data),
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();
            if (data.err_code == 0) {
                showAlert("Successfully Updated!");
                $('#table_content').dataTable().api().ajax.reload();
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

jQuery(document).ready(function () {
    showAlert($("#msg_alert").html());

    $("#btn_add").on('click', function (e) {
        e.preventDefault();

        update('add', '');
    });
    
    $("#btn_import").on('click', function(e) {
        e.preventDefault();

        $('#takeFileUpload').trigger('click');
    });

    $('#takeFileUpload').fileupload({
        singleFileUploads: false,

        dataType: 'json',
        formData: {
        },
        beforeSend: function () {
            showLoading();
        },
        done: function (e, data) {
            hideLoading();
            units = null;
            
            if (data.result.code == 0) {
                App.showMessage(data.result.message);
                
                $('#table_content').dataTable().api().ajax.reload();
                units = data.result.units;

                ask_unit();
            } else {
                showAlert(data.result.message);
            }
        },
        progressall: function (e, data) {
        }, 
        fail: function(e) {
            units = null;
            hideLoading();
            showAlert(Message.SERVER_ERROR);
        }
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
        pageLength: 25,
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
                "data": "job_number",
            },
            {
                "data": "community_name",
                "render": function (data, type, row, meta) {
                    var d = data;
                    if (d==null || d=='') {
                        d = row.community;
                    }
                    return d;
                }
            },
            {
                "data": "address",
                "render": function (data, type, row, meta) {
                    var d = "";
                    if (row.unit_address!=null && row.unit_address!="") {
                        d = row.unit_address;
                    } else {
                        d = data;
                    }
                    return d;
                }
            },
            {
                "data": "field_manager",
            },
            {
                "data": "additional",
                "render": function (data, type, row, meta) {
                    var data = "";
                    
                    if ($("#user_permission").val()=='0' || $("#user_permission").val()=='4') {
                        
                    } else {
                        data += '<div class="btn-group"> ' +
                            '<button type="button" class="btn default dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true"> Action <i class="fa fa-angle-down pull-right"></i> </button>' +
                            '<ul class="dropdown-menu bottom-up pull-right" role="menu">' +
                            '<li class="divider"></li>';

                        if ($("#user_permission").val()=='1') {
                            if (row.unit_address!=null && row.unit_address!="") {
                                data += '<li><a href="javascript:edit_unit(\'' + row.job_number + '\', \''+row.unit_id+'\')"><i class="fa fa-edit"></i> Edit</a></li>';
                                data += '<li><a href="javascript:delete_unit(\'' + row.job_number + '\')"><i class="fa fa-trash-o"></i> Delete Unit</a></li>';
                            } else {
                                data += '<li><a href="javascript:update(\'edit\', \'' + row.job_number + '\')"><i class="fa fa-edit"></i> Edit</a></li>';
                            }

                            data += '<li><a href="javascript:update(\'delete\', \'' + row.job_number + '\')"><i class="fa fa-trash-o"></i> Delete</a></li>';
                        } else {
                            if (row.unit_address!=null && row.unit_address!="") {
                                data += '<li><a href="javascript:edit_unit(\'' + row.job_number + '\', \''+row.unit_id+'\')"><i class="fa fa-edit"></i> Edit</a></li>';
                            } else {
                                data += '<li><a href="javascript:update(\'edit\', \'' + row.job_number + '\')"><i class="fa fa-edit"></i> Edit</a></li>';
                            }
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
//        $('#table_content tr td:nth-child(2)').addClass('center');
//        $('#table_content tr td:nth-child(3)').addClass('center');
        $('#table_content tr td:nth-child(4)').addClass('center');
        $('#table_content tr td:nth-child(5)').addClass('center');
    });

    $("#btn_unit").on('click', function(e) {
        e.preventDefault();
        
        if ($("#number_of_units").val()=="") {
            App.showMessage("Please Enter Number of Unit!");
        } else {
            $("#unit_dialog .modal-body .address-area").html('');
            var n = parseInt($("#number_of_units").val());
            var html = "";

            for (var i=1; i<=n; i++) {
                html += '<input type="text" class="form-control" placeholder="Address-'+i+'">';
            }

            $("#unit_dialog .modal-body .address-area").html(html);
        }
    });
    
    $("#unit_dialog").on('click', '.btn-primary', function(e) {
        e.preventDefault();
        if ($("#number_of_units").val()=="") {
            App.showMessage("Please Enter Number of Unit!");
        } else if ($("#unit_dialog .modal-body .address-area input").length==0) {
            App.showMessage("Please Enter Number of Unit and Click Apply!");
        } else {
            var n = parseInt($("#number_of_units").val());
            var ret = true;
            
            $("#unit_dialog .modal-body .address-area input").each(function(index, row) {
                var addr = $(row).val();
                if (addr=="") {
                    ret =false;
                }
            });
            
            if (ret==false) {
                App.showMessage("Please Enter Address!");
            } else {
                var address = [];
                
                $("#unit_dialog .modal-body .address-area input").each(function(index, row) {
                    var addr = $(row).val();
                    address[index] = addr;
                });
                
                units[bulk_index].units = address;
                $("#unit_dialog").modal('hide');
                
                showLoading();
                setTimeout(next_unit, 500);
            }
        }
    });

});
