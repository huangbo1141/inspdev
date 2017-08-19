var preventRunDefault = false;

function gotoStep(step) {
    var title = "";
    if (step==1) title = "Basic Information";
    if (step==2) title = "CheckList";
    if (step==3) title = "Basic Information";
    if (step==4) title = "Additional Information";
    if (step==5) title = "Recipient Emails";
    
    $("h3.inspection-type span.sub-title").html(">>> "+title);
    $(".inspection-form").hide();
    $(".inspection-form.step-"+step).show();
    
}

function refresh_exception_image() {
    if ($("#exception_images .item").length<4) {
        $("#exception_btn_add").show();
        
    } else {
        $("#exception_btn_add").hide();
    }
}


function update_inspection() {
    showLoading();
    
    var job_number = $("#job_number").val();
    var lot = $("#lot").val();

    // step 1
//    var house_ready = $("#house_ready").val();
//    var community = $("#community").val();
    var start_date = $("#start_date").val();
    var address = $("#address").val();
    var initials = $("#initials").val();
//    var field_manager = $("#field_manager").val();
    var front_picture = $("#front_image").attr('src');

    // step4
    var end_date = $("#end_date").val();
    var overall_comment = $("#txt_overall_comment").val();
    var result_code = $("#result_code").val();
    var exception_images = Array();
    if (result_code=='2') {
        $("#exception_images .item").each(function(index ,row){
            exception_images[index] = $(row).find('img').attr('data-src');
        });
    }

    // step5
    var recipient_email = Array();
    $("#recipient_emails li").each(function(index, row) {
        recipient_email[index] = $(row).attr('data-email');
    });
    
    
    $.ajax({
        type: "POST",
        url: 'update',
        data: { 
            inspection_id: $("#inspection_id").val() ,
            
            field_manager: $("#field_manager").val(),
//            community: community,
            start_date: start_date,
            address: address,
            initials: initials,
//            field_manager: field_manager,
            front_picture: front_picture,
            end_date: start_date,
            comment: overall_comment,
            result_code: result_code,
            exception: exception_images,
            email: recipient_email,
            job_number: job_number,
        },
        dataType: 'json',
        success: function (data) {
            hideLoading();
            
            if (data.err_code == 0) {
                showAlert("Successfully updated!");
                setTimeout(go_list, 1000);
            } else {
                showAlert("Failed to update!");
            }
        },
        error: function () {
            hideLoading();
            showAlert(Message.SERVER_ERROR);
        }
    });
}

function go_list() {
    location.href = "water_intrusion.html";
}


jQuery(document).ready(function () {
    if ($("#user_permission").val()=="1") {
        $("#job_number").inputmask("9999-999-**", {
            placeholder: '_'
        });
    }

    $(".inspection-form").hide();
    showAlert($("#msg_alert").html());

    $('.date-picker').datepicker({
        format: 'yyyy-mm-dd'
    });
    
    $(".select-picker").selectpicker({
        container: 'body',
        liveSearch: true,
    });    

    $(".page_content").on('click', 'img.for-preview', function(e) {
        e.preventDefault();
        
        if ($(this).hasClass('signature')) {
            $.swipebox( [ {
                    href: $(this).attr('src')   ,
                    title : '',
            }], {
                afterOpen: function() {
                    $("#swipebox-container .slide.current").addClass('white-background');
                },
                afterClose: function() {
                    $("#swipebox-container .slide.current").removeClass('white-background');
                }
            });  
        } else if ($(this).hasClass('google-map')) {
            $.swipebox( [ {
                    href: $(this).attr('data-src')   ,
                    title : '',
            }]);         
        } else {
            if ($(this).hasClass('thumb-image')) {
                $.swipebox( [ {
                        href: $(this).attr('data-src')   ,
                        title : '',
                }]);         
            } else {
                $.swipebox( [ {
                        href: $(this).attr('src')   ,
                        title : '',
                }]);         
            }
        }
    });
    


    // Step 1
    $('#takeFileUpload_front').fileupload({
        dataType: 'json',
        formData: {
        },
        beforeSend: function () {
            showLoading();
        },
        done: function (e, data) {
            hideLoading();
            if (data.result.code == 0) {
                $("#front_image").attr('src', data.result.url);
            } else {
                showAlert("Failed to upload!");
            }
        },
        progressall: function (e, data) {
        }, 
        fail: function(e) {
            hideLoading();
            showAlert(Message.SERVER_ERROR);
        }
    });
    
    $("#front_btn_add").on('click', function(e) {
        e.preventDefault();
        $('#takeFileUpload_front').trigger('click');
    });
    
    $("#front_btn_delete").on('click', function(e) {
        e.preventDefault();
        $("#front_image").attr('src', '');
    });


    
    // step 4
    $('#takeFileUpload_exception').fileupload({
        dataType: 'json',
        autoUpload: false,
        formData: {
        },
        beforeSend: function () {
            showLoading();
        },
        add: function(e, data) {
            var uploadErrors = [];
            var acceptFileTypes = /^image\/(gif|jpe?g|png)$/i;
            
            if(data.originalFiles[0]['type'].length && !acceptFileTypes.test(data.originalFiles[0]['type'])) {
                uploadErrors.push('Not an accepted file type');
            }
            if(data.originalFiles[0]['size']!=null && data.originalFiles[0]['size'] > 2000000) {
                uploadErrors.push('File cannot be larger than 2MB!');
            }

            if(uploadErrors.length > 0) {
                App.showFailedMessage(uploadErrors.join("<br>"));
            } else {
                data.submit();
            }
        },        
        done: function (e, data) {
            hideLoading();
            
            if (data.result.code == 0) {
                var html = "";
                
                html += '<div class="col-md-4 item">'
                    + '<img data-src="'+data.result.url+'" src="'+$("#resPath").val()+"blank.png"+'" style="background: url(\''+data.result.url+'\') center center no-repeat; background-size: cover; " class="img-responsive for-preview thumb-image">'
                    + '<a href="#" class="item-remove"><i class="fa fa-times"></i></a>'
                    + '</div>'
                    + '';
                
                $("#exception_images").append(html);
                $("#front_image").attr('src', data.result.url);
                
                refresh_exception_image();
            } else {
                showAlert("Failed to upload!");
            }
        },
        progressall: function (e, data) {
        }, 
        fail: function(e) {
            hideLoading();
            showAlert(Message.SERVER_ERROR);
        }
    });

    $("#exception_btn_add").on('click', function(e) {
        e.preventDefault();
        $('#takeFileUpload_exception').trigger('click');
    });
    
    $("#result_code").change(function(){
        var result_code = $("#result_code").val();
        if (result_code == '2') {
            $(".for-pass-exception").show();
            refresh_exception_image();
        } else {
            $(".for-pass-exception").hide();
        }
    });
    
    $("#exception_images").on('click', 'a.item-remove', function(e){
        e.preventDefault();
        $(this).parent(".item").remove();
        refresh_exception_image();
    }); 
    
    
    refresh_exception_image();
    $("#result_code").trigger('change');
    
    
    
    // step5 
    $("#email_btn_add").on('click', function(e){
        e.preventDefault();
        var email = $("#recipient_email").val();
        if (email!="") {
            if ($("#recipient_emails li[data-email='"+email+"']").length>0) {
                showAlert("Already Exist!");
            } else {
                $("#recipient_emails").append(''
                        + '<li data-email="'+email+'">' + email + ' <a href="#" class="btn"><i class="fa fa-times"></i></a></li>'
                        + '');
            }
            
            $("#recipient_email").val('');
        }
    });
    $("#recipient_emails").on('click', 'a.btn', function(e){
        e.preventDefault();
        $(this).parent('li').remove();
    });



    // prev, next
    $(".btn-prev").on('click', function(e) {
        e.preventDefault();
        
        var step = $(this).attr('data-step');
        var house_ready = $("#house_ready").val();
        
        if (step=='2') {
            gotoStep(1);
        }
        
        if (step=='4') {
            if (house_ready=='1') {
                gotoStep(2); 
            } else {
                gotoStep(1); 
            }
        }
        
        if (step=='5') {
            gotoStep(4); 
        }
    });

    $(".btn-next").on('click', function(e) {
        e.preventDefault();
        
        var step = $(this).attr('data-step');
    
        var house_ready = $("#house_ready").val();
        var community = $("#community").val();
        var start_date = $("#start_date").val();

        var end_date = $("#end_date").val();
        var overall_comment = $("#txt_overall_comment").val();
        
        var result_code = $("#result_code").val();

        if (step=='1') {
            if ($("#user_permission").val()=='1') {
                if (community=='') {
                    showAlert("Please input community!");
                    return;
                }
                if (start_date=='') {
                    showAlert("Please input date of inspection!");
                    return;
                }

                if ($("#user_permission").val()=="1") {
                    var v = $("#job_number").val();
                    v = v.replace(/_/g, "");//.replace(/X/g, "");
                    if (v=="" || v.length!=11) {
                        showAlert("Enter the Job Number");
                        return;
                    }
                }

                $("#end_date").val(start_date);
            }
            
            if (house_ready=='1') {
                gotoStep(2); 
            } else {
                gotoStep(4); 
            }
        }
        
        if (step=='2') {
            gotoStep(4);
        }
        
        if (step=='4') {
            if ($("#user_permission").val()=='1') {
                if (end_date=='') {
                    showAlert("Please input date of inspection!");
                    return;
                }
                if (overall_comment=='') {
                    showAlert("Please input overall comments!");
                    return;
                }
            }
            
            if (result_code=='2' && $("#exception_images .item").length==0) {
                showAlert("Please Add exception image!");
                return;
            }

            gotoStep(5);
        }
        
        if (step=='5') {
//            if ($("#recipient_emails li").length==0) {
//                showAlert("Please add recipient email!");
//                return;
//            }
            
            update_inspection();
        }
    });
    


    gotoStep(1);
});
