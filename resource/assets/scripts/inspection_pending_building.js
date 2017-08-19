
jQuery(document).ready(function () {
    showAlert($("#msg_alert").html());

    $('#table_content').dataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "ajax": {
            "url": "load_pending_building",
            "type": "POST"
        },
//        'searching' : false,
        "order": [[0, "asc"]],
        "columnDefs": [
            {
                "targets": [ -3, -4],
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
                "data": "community",
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
                "data": "additional",
                "render": function (data, type, row, meta) {
                    var d = "";

                    if (row.dp_status=="1") {
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

                    if (row.lath_status=="1") {
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
                    
                    if (row.first_name!=null && row.first_name!="" && row.last_name!=null && row.last_name!="") {
                        d += row.first_name + " " + row.last_name;
                        
                    } else if (row.field_manager!=null && row.field_manager!="") {
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
                    
                    if (parseInt(row.days)>0) {
                        d += '<span class="label label-warning">'+row.days+' Days</span>';
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
