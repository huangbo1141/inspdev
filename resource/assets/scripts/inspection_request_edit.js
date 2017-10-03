var last_job_number = "";
var last_inspection_type = "";
var last_address = "";
var requested_addr = "";

var need_appendix = "";
var need_updated = "";
var need_region = "";
var building;
var community;

var not_completed = false;
var is_model_home = false;
var is_first = true;

function request() {
  location.href = "edit_inspection_requested.html";
}

function logout() {
  location.href = $("#basePath").val() + "user/logout";
  //    $("#frm_logout").submit();
}

function hide_appendix() {
  $("#div_community_name").hide();
  $("#div_lot").hide();
  $("#div_address").hide();
  $("#div_region").hide();
}

function show_appendix() {
  $("#div_community_name").show();
  $("#div_lot").show();
  $("#div_address").show();
}

function show_appendix_address() {
  $("#div_address").show();
}

function refresh_appendix() {
  $("#address").show();
  $("#address_list").hide();
  $("#div_region").hide();

  if (need_appendix == "1") {
    show_appendix();
  } else {
    hide_appendix();
  }

  if (need_appendix == "2") {
    show_appendix_address();

    if (building != null && building.unit_count > 0 && building.unit_address != null && building.unit_address.length > 0) {
      $("#address").hide();

      $("#address_list").attr('disabled', false);
      $("#address_list").html("");

      var html = "";
      $.each(building.unit_address, function(index, row) {
        html += '<option value="' + row.address + '">' + row.address + '</option>';
      });
      $("#address_list").html(html);
      if (requested_addr != "" && $("#address_list option[value='" + requested_addr + "']").length > 0) {
        $("#address_list").val(requested_addr);
      }

      $("#address_list").show();
    }
  }

  if (need_updated == "readonly") {
    $("#community_name").attr('readonly', true);
    //        $("#lot").attr('readonly', true);
    $("#address").attr('readonly', true);
    $("#address_list").attr('disabled', true);
    //        $("#address_list").attr('disabled', false);

    if (building != null && building.unit_count > 0 && building.unit_address != null && building.unit_address.length > 0) {
      $("#address").hide();

      $("#address_list").html("");

      var html = "";
      $.each(building.unit_address, function(index, row) {
        html += '<option value="' + row.address + '">' + row.address + '</option>';
      });
      $("#address_list").html(html);
      if (requested_addr != "" && $("#address_list option[value='" + requested_addr + "']").length > 0) {
        $("#address_list").val(requested_addr);
      }

      $("#address_list").show();
      $("#address_list").attr('disabled', false);
    }
  } else if (need_updated == "edit") {
    $("#community_name").attr('readonly', false);
    //        $("#lot").attr('readonly', true);
    $("#address").attr('readonly', false);
    $("#address_list").attr('disabled', false);

    if (building != null && building.unit_count > 0 && building.unit_address != null && building.unit_address.length > 0) {
      $("#address").hide();

      $("#address_list").html("");

      var html = "";
      $.each(building.unit_address, function(index, row) {
        html += '<option value="' + row.address + '">' + row.address + '</option>';
      });
      $("#address_list").html(html);
      if (requested_addr != "" && $("#address_list option[value='" + requested_addr + "']").length > 0) {
        $("#address_list").val(requested_addr);
      }

      $("#address_list").show();
    }
  } else {
    //        $("#lot").attr('readonly', false);
  }

  if (need_region == "1") {
    $("#div_region").show();
  }
}

function refresh_model_home_without_change() {
  if (is_model_home) {
    $(".for-model-home-hidden").hide();
    $(".for-model-home-visible").show();
  } else {
    $(".for-model-home-visible").hide();
    $(".for-model-home-hidden").show();
  }
}

function refresh_model_home() {
  if (is_model_home) {
    $(".for-model-home-hidden").hide();
    $(".for-model-home-visible").show();
  } else {
    $(".for-model-home-visible").hide();
    $(".for-model-home-hidden").show();
  }

  $("#community_id").trigger('change');
}

function generate_job_number() {
  var jn = $("#community_id").val() + "-" + "000-";

  var c1 = String.fromCharCode(97 + Math.floor(Math.random() * 26));
  var c2 = String.fromCharCode(97 + Math.floor(Math.random() * 26));
  jn += c1 + c2;

  $("#g_job_number").val(jn);
}


function confirm_submit(lot, addr, job_number, detail) {
  var type = $("#category").val();

  showLoading();

  $.ajax({
    type: "POST",
    url: 'check_inspection_requested',
    data: {
      job_number: job_number,
      type: type,
      address: $("#address_list").css('display') != "none" ? $("#address_list").val() : "",
    },
    dataType: 'json',
    success: function(data) {
      hideLoading();

      var inspection_id = "";
      var inspection = null;
      if (data.inspection_id != null) {
        inspection_id = data.inspection_id;
        if (inspection_id != "" && data.inspection != null) {
          inspection = data.inspection;
        }
      }

      if (data.err_code == 1) {
        bootbox.alert("Both Drainage Plane and Lath Inspection have already passed for this lot. Please select a different Job Number");
        $('form').bootstrapValidator('resetForm', false);

      } else if (data.err_code == 0) {
        bootbox.confirm({
          title: '<span style="color:#e00;">Warning!!</span>',
          message: 'This Job number has not Passed a Drainage Plane Inspection.<br>Do you wish to request a Lath Inspection anyway?<br>This action will be noted on the report.',
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
              show_confirm_submit(lot, addr, job_number, detail, inspection_id, inspection);
            } else {
              $('form').bootstrapValidator('resetForm', false);
            }
          }
        });
      } else {
        show_confirm_submit(lot, addr, job_number, detail, inspection_id, inspection);
      }
    },
    error: function() {
      hideLoading();
      show_confirm_submit(lot, addr, job_number, detail, "", null);
    }
  });


}

function show_confirm_submit(lot, addr, job_number, detail, inspection_id, inspection) {
  if (detail != "") {
    bootbox.confirm({
      title: 'Are you sure?',
      message: 'An Inspection for this detail ' + detail + ' for this job number(' + job_number + ') will be requested.<br>Please confirm:',
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
          before_submit(inspection_id, inspection);
        } else {
          $('form').bootstrapValidator('resetForm', false);
        }
      }
    });
  } else {
    bootbox.confirm({
      title: 'Are you sure?',
      message: 'An Inspection for this lot (' + lot + ') and address (' + addr + ') for this job number(' + job_number + ') will be requested.<br>Please confirm:',
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
          before_submit(inspection_id, inspection);
        } else {
          $('form').bootstrapValidator('resetForm', false);
        }
      }
    });
  }
}

function before_submit(inspection_id, inspection) {
  if ($("#reinspection").val() == "1" && inspection_id != "") {
    bootbox.confirm({
      title: 'Please confirm:',
      message: 'An Inspection was completed on ' + inspection.start_date + '. <br>Do you want to <b>EDIT</b> the <b>LAST</b> inspection completed on this lot?',
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
          submit_data(inspection_id);
        } else {
          submit_data("");
        }
      }
    });
  } else {
    submit_data("");
  }
}

function submit_data(inspection_id) {
  showLoading();

  var is_unit_address = false;
  var address = "";
  if (need_appendix == "1") {
    if ($("#address").css('display') != "none") {
      address = $("#address").val();
    } else if ($("#address_list").css('display') != "none") {
      address = $("#address_list").val();
      is_unit_address = true;
    }
  }

  $.ajax({
    type: "POST",
    url: 'update_inspection_requested',
    data: {
      id: $("#requested_id").val(),
      date_requested: $("#date_requested").val(),

      job_number: is_model_home ? $("#g_job_number").val() : $("#job_number").val(),
      category: $("#category").val(),
      reinspection: $("#reinspection").val(),
      epo_number: $("#reinspection").val() == "1" ? $("#epo_number").val() : "",

      community_name: need_appendix == "1" ? $("#community_name").val() : "",
      lot: need_appendix == "1" ? $("#lot").val() : "",
      address: address,

      kind: need_updated,

      community_id: is_model_home ? $("#community_id").val() : "",
      model_home: is_model_home ? "1" : "0",
      detail: is_model_home ? $("#details").val() : "",

      unit_address: is_unit_address ? "1" : "",

      region: need_region == "1" ? $("#region").val() : "",

      inspection_id: inspection_id,

      field_manager: $("#field_manager").val(),
    },
    dataType: 'json',
    success: function(data) {
      hideLoading();
      showAlert(data.err_msg);

      if (data.err_code == 0) {
        bootbox.confirm({
          title: 'Submit another request or Logout',
          message: 'Please confirm:',
          buttons: {
            'cancel': {
              label: 'Request',
              className: 'btn-default'
            },
            'confirm': {
              label: 'LogOut',
              className: 'btn-danger'
            }
          },
          callback: function(result) {
            if (result) {
              logout();
            } else {
              request();
            }
          }
        });

      } else {
        $('form').bootstrapValidator('resetForm', false);
      }
    },
    error: function() {
      hideLoading();
      showAlert(Message.SERVER_ERROR);
      //            $('form').bootstrapValidator('resetForm', false);
    }
  });
}

function init() {
  hide_appendix();

  if ($("#msg_alert").html() != '') {
    setTimeout(hideAlert, 2000);
  }

  $('#reinspection').trigger('change');

  if ($("#requested_id").val() != "") {
    var v = $("#job_number").val();
    v = v.replace(/_/g, "");
    if (v != "" && v.length != 11) {
      $("#community_id").val(v.substr(0, 4));
      $("#g_job_number").val($("#job_number").val());
      is_model_home = true;
      $("#model_home").prop('checked', true);
      refresh_model_home_without_change();
    } else {
      $("#details").val("");

      if ($("#job_number").val() != "") {
        $("#job_number").trigger('change');
      }
    }
  } else {
    $("#details").val("");
  }
}

function check_job_number() {
  var v = $("#job_number").val();
  v = v.replace(/_/g, ""); //.replace(/X/g, "");
  if (v == "" || v.length != 11) {

  } else {
    var cat = $("#category").val();
    var addr = $("#address_list").css('display') != "none" ? $("#address_list").val() : "";

    if (last_job_number == "" || last_job_number != v || last_inspection_type != cat || (addr != "" && last_address != addr)) {
      last_job_number = v;
      last_inspection_type = cat;
      last_addr = addr;

      $("#lot").val(v.substr(5, 3));

      not_completed = false;

      $.ajax({
        type: "POST",
        url: 'check_jobnumber',
        data: {
          id: $("#requested_id").val(),
          is_first: is_first ? "1" : "0",
          job_number: v,
          category: cat,
          address: addr,
        },
        dataType: 'json',
        success: function(data) {
          hideLoading();

          need_region = "";
          if (need_appendix != "2") {
            need_appendix = "";
          }

          if (is_first) {
            is_first = false;
          }

          $(".for-edit-inspection-requested").hide();

          if (data.err_code == 0) {
            $("#field_manager").html("");
            $("#field_manager").append('<option value="">No Changes</option>');

            if (data.fm.has == 1) {
              $.each(data.fm.list, function(index, row) {
                $("#field_manager").append('<option ' + (data.fm.manager_id == row.id ? "selected" : "") + ' value="' + row.id + '">' + row.first_name + ' ' + row.last_name + '</option>');
              });
            }

            $(".for-edit-inspection-requested").show();

            if (data.building_unit == 1) {
              need_appendix = "2";
              need_updated = "";

              building = data.building;
              requested_addr = building.address;

              refresh_appendix();
            } else {
              need_appendix = "";
              need_updated = "";

              building = null;
              requested_addr = "";

              refresh_appendix();
            }

            $("#reinspection").val('0');
            $("#epo_number").val('');

            if (data.pass != null && data.pass > 0) {
              showAlert("It have already Passed Inspection!");

              need_updated = "";
              not_completed = true;

              return false;
            } else if (data.fail != null && data.fail > 0) {
              $("#reinspection").val('1');
              if (data.fail_epo != null && data.fail_epo > 0) {
                $("#epo_number").val(data.fail_epo);
              }
            }

            $("#reinspection").trigger('change');

            if (data.community != null) {
              community = data.community;
            } else {
              community = null;
            }

            if (data.building != null) {
              building = data.building;

              if ($("#user_permission").val() == "1") {
                $("#job_confirm_dialog .modal-body .bootbox-body").html("The Job Number <b>" + v + "</b> refers to this Community <b>" + data.building.community +
                  "</b>, Lot <b>" + v.substr(5, 3) + "</b> and Address <b>" + data.building.address +
                  "</b>.<br>If the details are correct, press <b>Continue</b>.<br>If you need to update the details fill all the fields after you click <b>Edit</b>.<br>If you want to enter a different job number click <b>Change</b>.");
                $("#job_confirm_dialog .modal-footer .btn-primary").show();
              } else {
                $("#job_confirm_dialog .modal-body .bootbox-body").html("Please Confirm: <br>" +
                  "Job Number : <b>" + v + "</b><br>" +
                  " Community : <b>" + data.building.community + "</b><br>" +
                  "   Address : <b>" + data.building.address + "</b><br>");
                $("#job_confirm_dialog .modal-footer .btn-primary").hide();
              }
              $("#job_confirm_dialog").modal('show');
            } else {
              if ($("#user_permission").val() == "1") {
                bootbox.confirm({
                  title: 'Confirm',
                  message: 'The Job Number <b>' + v + '</b> is not in the database.<br>If you want to enter a different job number click on the <b>Change</b> button.<br>If you want to enter the address manually click <b>Continue</b> and fill in all the fields.',
                  buttons: {
                    'cancel': {
                      label: 'Change',
                      className: 'btn-default pull-left'
                    },
                    'confirm': {
                      label: 'Continue',
                      className: 'btn-danger pull-right'
                    }
                  },
                  callback: function(result) {
                    need_updated = "";
                    if (result) {
                      need_appendix = "1";
                      need_region = "1";

                      if (community != null) {
                        $("#community_name").val(community.community_name);
                      }
                    } else {
                      if (need_appendix != "2")
                        need_appendix = "";
                      not_completed = true;
                    }

                    refresh_appendix();
                  }
                });
              } else {
                bootbox.alert({
                  size: 'small',
                  title: 'Warning',
                  message: 'The Job Number <b>' + v + '</b> is not in the database.<br>Please contact the E3 Office for assistance.<br>',
                  callback: function() {
                    need_updated = "";
                    not_completed = true;

                    $('form').bootstrapValidator('resetForm', false);
                  }
                });
              }
            }
          } else {
            showAlert(Message.SERVER_ERROR);
          }
        },
        error: function() {
          $(".for-edit-inspection-requested").hide();

          hideLoading();
          showAlert(Message.SERVER_ERROR);
          //                $('form').bootstrapValidator('resetForm', false);
        }
      });

    }
  }
}

jQuery(document).ready(function() {

  $("#job_number").inputmask("9999-999-**", {
    placeholder: '_'
  });

  $('.date-picker').datepicker({
    autoclose: true,
    format: 'yyyy-mm-dd'
  });

  $("#community_id").change(function(e) {
    generate_job_number();
  });

  $("#model_home").change(function(e) {
    is_model_home = $(this).prop('checked');
    refresh_model_home();
  });

  $('#reinspection').change(function(e) {
    if ($(this).val() == "1") {
      $('#epo_number').attr('readonly', false);
    } else {
      //            $("#epo_number").parent('div').parent('.form-group').removeClass('has-error');
      $('#epo_number').attr('readonly', true);
      //            $('form').bootstrapValidator('resetForm', false);
    }
  });

  $("#category").change(function(e) {
    check_job_number();
  });

  $('#job_number').change(function(e) {
    check_job_number();
  });

  $('#address_list').on('change', function(e) {
    check_job_number();
  });

  $("#frm_inspection").on('keyup keypress', function(e) {
    var keyCode = e.keyCode || e.which;
    if (keyCode === 13) {
      e.preventDefault();
      return false;
    }
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
          }
        },
      }
    })
    .on('success.field.bv', function(e, data) {
      if (data.bv.isValid()) {
        data.bv.disableSubmitButtons(false);
      }
    });

  $("#job_confirm_dialog").on('click', '.modal-footer .btn', function(e) {
    e.preventDefault();

    need_updated = "";
    if ($(this).hasClass('btn-default')) {
      if (need_appendix != "2") {
        need_appendix = "";
      }
      not_completed = true;
    }

    if ($(this).hasClass('btn-primary')) {
      need_updated = "edit";
      need_appendix = "1";

      if (building != null) {
        $("#community_name").val(building.community);
        $("#address").val(building.address);
      } else if (community != null) {
        $("#community_name").val(community.community_name);
      }
    }

    if ($(this).hasClass('btn-danger')) {
      need_appendix = "1";
      need_updated = "readonly";

      if (building != null) {
        $("#community_name").val(building.community);
        $("#address").val(building.address);
      } else if (community != null) {
        $("#community_name").val(community.community_name);
      }
    }

    refresh_appendix();
    $("#job_confirm_dialog").modal('hide');
  });


  $('form').on('submit', function(e) {
    if (e.isDefaultPrevented()) {

    } else {
      e.preventDefault();

      var epo = $("#epo_number").val();
      if (is_model_home) {
        var details = $("#details").val();
        //                if ($("#epo_number").attr('readonly')==null && epo=="") {
        //                    showAlert("Enter the EPO Number");
        //                }
        if (details == "") {
          showAlert("Enter the Details");
        } else {
          if ($("#reinspection").val() == "1") {
            var permission = $("#user_permission").val();
            if (permission == "2") {
              // when field manager
              if (epo == "") {
                showAlert("Enter the EPO Number");
                return;
              }
            }
          }
          confirm_submit('', '', $("#g_job_number").val(), details);
        }
      } else {

        var v = $("#job_number").val();
        v = v.replace(/_/g, ""); //.replace(/X/g, "");

        if (v == "" || v.length != 11) {
          showAlert("Enter the Job Number");

          //                } else if ($("#epo_number").attr('readonly')==null && epo=="") {
          //                    showAlert("Enter the EPO Number");

        } else {
          if (need_appendix == "1") {
            var cn = $("#community_name").val();
            var lot = $("#lot").val();

            var addr = "";
            if ($("#address").css('display') != "none") {
              addr = $("#address").val();
            } else if ($("#address_list").css('display') != "none") {
              addr = $("#address_list").val();
            }

            if (cn == "") {
              showAlert("Enter the Community Name");
              $('form').bootstrapValidator('resetForm', false);
            } else if (lot == "") {
              showAlert("Enter the LOT#");
              $('form').bootstrapValidator('resetForm', false);
            } else if (addr == "") {
              if ($("#address").css('display') != "none") {
                showAlert("Enter the Address");
              } else if ($("#address_list").css('display') != "none") {
                showAlert("Select the Address");
              }

              $('form').bootstrapValidator('resetForm', false);
            } else {
              if ($("#reinspection").val() == "1") {
                var permission = $("#user_permission").val();
                if (permission == "2") {
                  // when field manager
                  if (epo == "") {
                    showAlert("Enter the EPO Number");
                    return;
                  }
                }
              }
              confirm_submit(lot, addr, v, '');
            }

          } else {
            //                    submit_data();
            if (not_completed) {
              if ($("#address_list").css('display') != "none") {
                showAlert("Enter the Another Job Number or Select Another Address");
              } else {
                showAlert("Enter the Another Job Number");
              }
            }

            $('form').bootstrapValidator('resetForm', false);
          }
        }
      }
    }
  });

  init();

});
