var export_type = "";

function init() {
  get_total_count();
  $("#region").trigger('change');
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

  today = yyyy + '-' + mm + '-' + dd;
  return today;
}

function get_community() {
  //    showLoading();

  $("#community").html('');
  $("#community").append('<option value="">All</option>');

  $.ajax({
    type: "POST",
    url: 'get_community',
    data: {
      region: $("#region").val(),
    },
    dataType: 'json',
    success: function(data) {
      //            hideLoading();

      if (data.err_code == 0) {

        var html = "";
        $.each(data.community, function(index, row) {
          html += '<option value="' + row.community_id + '">' + row.community_id + ' - ' + row.community_name + '</option>';
        });

        $("#community").append(html);
      } else {
        showAlert("Failed to load community!");
      }

      $("#community").selectpicker('refresh');
    },
    error: function() {
      //            hideLoading();
      showAlert(Message.SERVER_ERROR);
    }
  });
}

function get_total_count() {
  $("#statistics_result").html('');

  $.ajax({
    type: "POST",
    url: 'get_count',
    data: {
      kind: 're_inspection',
      region: $("#region").val(),
      community: $("#community").val(),
      start_date: $("#start_date").val(),
      end_date: $("#end_date").val(),
      status: $("#status").val(),
      type: $("#inspection_type").val(),
    },
    dataType: 'json',
    success: function(data) {
      if (data.code == 0) {
        $("#statistics_result").html(data.result);
      } else {}
    },
    error: function() {}
  });
}

function export_file(format) {
  export_type = format;
  $("#export_confirm_dialog").modal('show');
}


function send_report() {
  $("#recipients").val("");
  $("#email_confirm_dialog").modal('show');
}

function email_report(recipients) {
  showLoading();

  $.ajax({
    type: "POST",
    url: $("#basePath").val() + 'api/email/statistics/re_inspection',
    data: {
      region: $("#region").val(),
      community: $("#community").val(),
      start_date: $("#start_date").val(),
      end_date: $("#end_date").val(),
      status: $("#status").val(),
      type: $("#inspection_type").val(),
      recipient: recipients,
    },
    dataType: 'json',
    success: function(data) {
      hideLoading();
      showAlert(data.message);

      if (data.code == 0) {

      } else {}
    },
    error: function() {
      hideLoading();
      showAlert(Message.SERVER_ERROR);
    }
  });
}

jQuery(document).ready(function() {
  showAlert($("#msg_alert").html());

  $('.date-picker').val(get_formatted_date());

  $('.date-picker').datepicker({
    format: 'yyyy-mm-dd',
    autoclose: true,
    //        endDate: get_formatted_date(),
    todayBtn: true,
    todayHighlight: true,
  });

  $('.select-picker').selectpicker({
    liveSearch: true,
  });

  $("#region").change(function(e) {
    get_community();
  });

  $('#table_content').dataTable({
    "processing": true,
    "serverSide": true,
    "responsive": true,
    "ajax": {
      "url": "load_re_inspection",
      "type": "POST",
      "data": function(d) {
        d.region = $("#region").val();
        d.community = $("#community").val();
        d.start_date = $("#start_date").val();
        d.end_date = $("#end_date").val();
        d.status = $("#status").val();
        d.type = $("#inspection_type").val();
      }
    },
    "order": [
      [9, "desc"]
    ],
    "pageLength": 100,
    "columnDefs": [
      //            {
      //                "targets": [-1],
      //                "orderable": false
      //            },
      //            {
      //                "targets": "_all",
      //                "searchable": false
      //            }
    ],
    "columns": [{
        "data": "inspection_type",
      },
      {
        "data": "region_name",
      },
      {
        "data": "community",
        "render": function(data, type, row, meta) {
          var d = "";

          if (row.community_name != null && row.community_name != "") {
            d = row.community_name;
          } else {
            //                        d = data;
          }

          return d;
        }
      },
      {
        "data": "job_number",
      },
      {
        "data": "address",
      },
      {
        "data": "first_name",
        "render": function(data, type, row, meta) {
          var d = "";

          if (row.first_name != null) {
            d += row.first_name + " ";
          }

          if (row.last_name != null) {
            d += row.last_name;
          }

          return d;
        }
      },
      {
        "data": "overall_comments",
      },
      {
        "data": "start_date",
      },
      {
        "data": "requested_epo_number",
        // "render": function(data, type, row, meta) {
        //   var d = "";
        //
        //   if (data != "" && data != "") {
        //     d = data;
        //   } else {
        //     if (row.requested_epo_number != null && row.requested_epo_number != "" && row.requested_epo_number != 0) {
        //       d = row.requested_epo_number;
        //     }
        //   }
        //
        //   return d;
        // }
      },
      {
        "data": "inspection_count",
      },
      {
        "data": "result_code",
        "render": function(data, type, row, meta) {
          var data = "";

          if (row.result_code == '1') {
            data += '<span class="label label-success">';
          }
          if (row.result_code == '2') {
            data += '<span class="label label-warning">';
          }
          if (row.result_code == '3') {
            data += '<span class="label label-danger">';
          }

          data += row.result_name;
          data += "</span>";

          return data;
        }
      },
    ]
  });

  $('#table_content').on('draw.dt', function() {
    $('#table_content').removeClass('display').addClass('table table-striped table-bordered');
    $('#table_content tr td:nth-child(1)').addClass('center');
    $('#table_content tr td:nth-child(2)').addClass('center');
    $('#table_content tr td:nth-child(3)').addClass('center');
    $('#table_content tr td:nth-child(4)').addClass('center');
    $('#table_content tr td:nth-child(6)').addClass('center');
    $('#table_content tr td:nth-child(8)').addClass('center');
    $('#table_content tr td:nth-child(10)').addClass('center');
    $('#table_content tr td:nth-child(11)').addClass('center');
  });

  $("#btn_view").on('click', function(e) {
    e.preventDefault();
    $('#table_content').dataTable().api().ajax.reload();
    get_total_count();
    // $.ajax({
    //   type: "POST",
    //   url: 'load_re_inspection',
    //   data: {
    //     region: $("#region").val(),
    //     community: $("#community").val(),
    //     start_date: $("#start_date").val(),
    //     end_date: $("#end_date").val(),
    //     status: $("#status").val(),
    //     type: $("#inspection_type").val(),
    //   },
    //   dataType: 'json',
    //   success: function(data) {
    //     if (data.code == 0) {
    //       console.log(data);
    //     } else {}
    //   },
    //   error: function() {}
    // });
  });

  $("#btn_export").on('click', function(e) {
    e.preventDefault();
    export_file('pdf');
  });
  $("#btn_export_csv").on('click', function(e) {
    e.preventDefault();
    export_file('csv');
  });

  $("#export_confirm_dialog").on('click', '.modal-footer .btn', function(e) {
    e.preventDefault();

    $("#export_confirm_dialog").modal('hide');
    if ($(this).hasClass('btn-default')) {}

    if ($(this).hasClass('btn-primary')) {
      $.fileDownload($("#basePath").val() + "api/export/statistics/re_inspection?desc=0&file_format=" + export_type + "&region=" + $("#region").val() + "&community=" + $("#community").val() + "&start_date=" + $("#start_date").val() + "&end_date=" + $("#end_date").val() + "&status=" + $("#status").val() + "&type=" + $("#inspection_type").val());
    }

    if ($(this).hasClass('btn-danger')) {
      $.fileDownload($("#basePath").val() + "api/export/statistics/re_inspection?desc=1&file_format=" + export_type + "&region=" + $("#region").val() + "&community=" + $("#community").val() + "&start_date=" + $("#start_date").val() + "&end_date=" + $("#end_date").val() + "&status=" + $("#status").val() + "&type=" + $("#inspection_type").val());
    }
  });


  $("#btn_email").on('click', function(e) {
    e.preventDefault();
    send_report();
  });
  $("#email_confirm_dialog").on('click', '.modal-footer .btn-primary', function(e) {
    e.preventDefault();
    email_report($("#recipients").val());
    $("#email_confirm_dialog").modal('hide');
  });

  init();
});
