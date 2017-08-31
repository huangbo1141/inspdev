function init() {
  if ($("#msg_alert").html() != '') {
    setTimeout(hideAlert, 2000);
  }
}

function scheduling() {
  location.href = $("#basePath").val() + "scheduling/energy";
}

function submit_data() {

  //var p = $("input[name='field_manager']").val();
  var field_manager = $("#field_manager option:selected").text();
  var field_manager_id = $("#field_manager option:selected").val();
  var data = {
    id: $("#requested_id").val(),
    manager_id: field_manager_id,

    date_requested: $("#date_requested").val(),
    job_number: $("#job_number").val(),
    lot: $("#lot").val(),

    community: $("#community").val(),
    address: $("#address").val(),
    city: $("#city").val(),
    area: $("#area").val(),
    volume: $("#volume").val(),
    wall_area: $("#wall_area").val(),
    ceiling_area: $("#ceiling_area").val(),

    design_location: $("#design_location").val(),
    field_manager: field_manager,
    qn: $("#qn").val(),

    document_person: $("#document_person").val(),
  }
  var req_id = $("#requested_id").val();
  var fname = "update_duct_leakage_inspection_requested";
  if (req_id.length > 0) {
    fname = "update_duct_leakage_inspection_requested2";
  } else {
    fname = "update_duct_leakage_inspection_requested";
  }

  // return;

  showLoading();
  $.ajax({
    type: "POST",
    url: fname,
    data: data,
    dataType: 'json',
    success: function(data) {
      hideLoading();
      showAlert(data.err_msg);

      if (data.err_code == 0) {
        setTimeout(scheduling, 700);
      } else {
        $('form').bootstrapValidator('resetForm', false);
      }
    },
    error: function() {
      hideLoading();
      showAlert(Message.SERVER_ERROR);
      $('form').bootstrapValidator('resetForm', false);
    }
  });
}

jQuery(document).ready(function() {
  $('.date-picker').datepicker({
    autoclose: true,
    format: 'yyyy-mm-dd'
  });

  $('form').bootstrapValidator({
      feedbackIcons: {
        valid: 'has-success',
        invalid: 'has-error',
        validating: ''
      },
      fields: {
        date_requested: {
          validators: {
            notEmpty: {
              message: 'Select the date'
            },
          }
        },
        job_number: {
          validators: {
            notEmpty: {
              message: 'Enter the Job Number'
            },
            integer: {
              message: 'Enter the Number',
            },
            greaterThan: {
              value: 1,
              message: 'Enter the Number greater than 1',
            }
          }
        },
        lot: {
          validators: {
            notEmpty: {
              message: 'Enter the Lot'
            },
            numeric: {
              message: 'Enter the Number',
            },
            greaterThan: {
              value: 1,
              message: 'Enter the Number greater than 1',
            }
          }
        },
        community: {
          validators: {
            notEmpty: {
              message: 'Enter the Community'
            },
          }
        },
        address: {
          validators: {
            notEmpty: {
              message: 'Enter the Address'
            },
          }
        },
        city: {
          validators: {
            notEmpty: {
              message: 'Enter the City'
            },
          }
        },
        area: {
          validators: {
            notEmpty: {
              message: 'Enter the Area'
            },
            integer: {
              message: 'Enter the Number',
            },
            greaterThan: {
              value: 1,
              message: 'Enter the Number greater than 1',
            }
          }
        },
        volume: {
          validators: {
            notEmpty: {
              message: 'Enter the Volume'
            },
            integer: {
              message: 'Enter the Number',
            },
            greaterThan: {
              value: 1,
              message: 'Enter the Number greater than 1',
            }
          }
        },
        wall_area: {
          validators: {
            notEmpty: {
              message: 'Enter the Wall Area'
            },
            integer: {
              message: 'Enter the Number',
            },
            greaterThan: {
              value: 1,
              message: 'Enter the Number greater than 1',
            }
          }
        },
        ceiling_area: {
          validators: {
            notEmpty: {
              message: 'Enter the Ceiling Area'
            },
            integer: {
              message: 'Enter the Number',
            },
            greaterThan: {
              value: 1,
              message: 'Enter the Number greater than 1',
            }
          }
        },
        design_location: {
          validators: {
            notEmpty: {
              message: 'Enter the Design Location'
            },
          }
        },
        // field_manager: {
        //   validators: {
        //     notEmpty: {
        //       message: 'Enter the Field Manager Email Address'
        //     },
        //     emailAddress: {
        //       message: 'Enter the Valid Email Address'
        //     },
        //   }
        // },
        // browser: {
        //   validators: {
        //     notEmpty: {
        //       message: 'Enter the Field Manager Email Address'
        //     },
        //     emailAddress: {
        //       message: 'Enter the Valid Email Address'
        //     },
        //   }
        // },
        qn: {
          validators: {
            notEmpty: {
              message: 'Enter the Volume'
            },
            numeric: {
              message: 'Enter the Number',
            },
            greaterThan: {
              value: 0.01,
              message: 'Enter the Number greater than 0.01',
            },
            lessThan: {
              value: 0.99,
              message: 'Enter the Number less than 0.99',
            }
          }
        },
      }
    })
    .on('success.field.bv', function(e, data) {
      if (data.bv.isValid()) {
        data.bv.disableSubmitButtons(false);
      }
    });

  $('form').on('submit', function(e) {
    if (e.isDefaultPrevented()) {} else {
      e.preventDefault();

      var v = $("#job_number").val();
      var addr = $("#address").val();
      var lot = $("#lot").val();

      bootbox.confirm({
        title: 'Are you sure?',
        message: 'An Inspection for this lot (' + lot + ') for this address (' + addr + ') for this job number(' + v + ') will be requested.<br>Please confirm:',
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
            submit_data();
          } else {
            $('form').bootstrapValidator('resetForm', false);
          }
        }
      });
    }
  });

  init();
});
