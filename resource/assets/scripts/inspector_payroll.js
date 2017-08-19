function load() {
    $("#table_content tbody").html("");
    showLoading();
    
    var pay_period = $("#pay_period").val();

    $.ajax({
        type: "POST",
        url: 'load_inspector_payroll',
        data: {
            start: $("#pay_period option[value='"+pay_period+"']").attr('data-start'),
            end: $("#pay_period option[value='"+pay_period+"']").attr('data-end'),
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();

            if (data.code == 0) {
                var html = "";
                $.each(data.result, function(index, row) {
                   html += ''
                            + '<tr data-id="' + row.id + '">'
                                + '<td class="inspector_name">' + row.inspector_name + '</td>'
                                + '<td class="inspector_email text-center">' + row.email + '</td>'
                                + '<td class="inspector_phone text-center">' + (row.phone_number!=null ? row.phone_number : "") + '</td>'
                                + '<td class="inspector_address">' + (row.address!=null ? row.address : "") + '</td>'
                                + '<td class="check_amount text-center"><input type="number" class="form-control text-center" step="0.01" value="' + parseFloat(row.check_amount).toFixed(2) + '"></td>'
                                + '<td class="check_number text-center"><input type="text" class="form-control text-center" value="' + row.check_number + '"></td>'
                                + '<td class="inspection_count text-center">'+row.inspection_count+'</td>'
                            + '</tr>'
                            + '';
                });
                
                $("#table_content tbody").html(html);
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

function submit_data() {
    showLoading();
    
    var inspectors = Array();
    var pay_period = $("#pay_period").val();
    
    $("#table_content tbody tr").each(function(index, row) {
        var amount = $(this).find('td.check_amount input').val();
        var number = $(this).find('td.check_number input').val();
        var count = $(this).find('td.inspection_count').html();
        
        inspectors[index] = {
            id: $(this).attr('data-id'),
            name: $(this).find('td.inspector_name').html(),
            email: $(this).find('td.inspector_email').html(),
            phone: $(this).find('td.inspector_phone').html(),
            address: $(this).find('td.inspector_address').html(),
            amount: amount,
            number: number,
            count: count,
        };
    });

    $.ajax({
        type: "POST",
        url: 'submit_inspector_payroll',
        data: {
            start: $("#pay_period option[value='"+pay_period+"']").attr('data-start'),
            end: $("#pay_period option[value='"+pay_period+"']").attr('data-end'),
            data: JSON.stringify(inspectors),
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();

            if (data.code == 0) {
                showAlert("Successfully Submitted!");
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

    $(".select-picker").selectpicker({
        container: 'body',
        liveSearch: true,
        mobile: true,
    });

//    $(".table-filter select, .table-filter input").on('change keypress', function(e) {
//        $('#table_content').dataTable().api().ajax.reload();
//    });

    $("#btn_search").on('click', function(e) {
        e.preventDefault();
        load();
    });

    $("#btn_submit").on('click', function (e) {
        e.preventDefault();
        submit_data();
    });
    
    load();
});
