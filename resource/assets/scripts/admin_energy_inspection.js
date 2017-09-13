function submit_data() {
  var house_pressure = $('#house_pressure').val();
  if (house_pressure == '') {
    showAlert("Please Enter House Pressure!");
    return false;
  }

  if (isNaN(house_pressure)) {
    showAlert("Please Enter Correct House Pressure!");
    return false;
  }

  var app_home_message1 = $('#app_home_message1').val();
  if (app_home_message1 == '') {
    showAlert("Please Enter Message for Android Home Page!");
    return false;
  }

  if (isNaN(house_pressure)) {
    showAlert("Please Enter Correct Message for Android Home Page!");
    return false;
  }

  showLoading();

  $.ajax({
    type: "POST",
    url: 'update_energy_inspection',
    data: {
      house_pressure: house_pressure,
      app_home_message1: app_home_message1
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
