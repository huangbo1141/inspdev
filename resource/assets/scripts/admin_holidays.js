function submit_data() {
  var input = $("div input:checkbox");
  var tags = document.getElementsByName('holidays');
  var list = [];
  for (var i = 0; i < tags.length; ++i) {
    if (tags[i].checked) {
      var value = tags[i].value;
      var row = {};
      row.id = value;
      row.valid = 1;
      list.push(row);
    } else {
      var value = tags[i].value;
      var row = {};
      row.id = value;
      row.valid = 0;
      list.push(row);
    }
  }
  var jsondata = JSON.stringify(list);

  // var report_keep_day = $('#report_keep_day').val();
  // if (report_keep_day == '') {
  //   showAlert("Please Enter Keep Days for PDF Report!");
  //   return false;
  // }
  //
  // if (isNaN(report_keep_day)) {
  //   showAlert("Please Enter Correct Keep Days for PDF Report!");
  //   return false;
  // }
  showLoading();

  $.ajax({
    type: "POST",
    url: 'update_holidays',
    data: {
      jsondata: jsondata
    },
    dataType: 'json',
    success: function(data) {
      hideLoading();

      if (data.code == 0) {
        showAlert("Successfully Updated!");
      } else {
        showAlert(data.message);
      }
    },
    error: function() {
      hideLoading();
      showAlert(Message.SERVER_ERROR);
    }
  });
}

jQuery(document).ready(function() {
  if ($("#msg_alert").html() != '') {
    setTimeout(hideAlert, 2000);
  }

  $(".btn-submit").on('click', function(e) {
    e.preventDefault();

    submit_data();
  });

});
