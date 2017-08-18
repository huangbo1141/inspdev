<?php require 'common/variable.php'; ?>

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
    <!--<![endif]-->

    <!-- Head BEGIN -->
    <head>
        <meta charset="utf-8">
        <title><?php echo APP_TITLE; ?></title>

        <meta content="width=device-width, initial-scale=1.0" name="viewport">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

        <meta content="Modern Education" name="description">
        <meta content="keywords" name="keywords">
        <meta content="cjh124" name="author">

        <meta property="og:site_name" content="-CUSTOMER VALUE-">
        <meta property="og:title" content="-CUSTOMER VALUE-">
        <meta property="og:description" content="-CUSTOMER VALUE-">
        <meta property="og:type" content="website">
        <meta property="og:image" content="-CUSTOMER VALUE-"><!-- link to image for socio -->
        <meta property="og:url" content="-CUSTOMER VALUE-">

        <!-- Global styles START -->          
        <!--<link href="<?php echo $resPath; ?>assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet">-->
        <link href="<?php echo $resPath; ?>assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <!-- Global styles END --> 

        <link href="<?php echo $resPath; ?>assets/plugins/bootstrap-validator/css/bootstrapValidator.css" rel="stylesheet">
        <link href="<?php echo $resPath; ?>assets/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet">
        <link href="<?php echo $resPath; ?>assets/plugins/pgwdialog/pgwmodal.css" rel="stylesheet">
        <link href="<?php echo $resPath; ?>assets/plugins/bootstrap-dialog/css/bootstrap-dialog.css" rel="stylesheet">
        
        <!-- Theme styles START -->
        <link href="<?php echo $resPath; ?>assets/layout/css/components.css" rel="stylesheet">
        <link href="<?php echo $resPath; ?>assets/layout/css/themes/default.css" rel="stylesheet" id="style-color">

        <link href="<?php echo $resPath; ?>assets/layout/css/custom.css" rel="stylesheet">        

    </head>
    <!-- Head END -->

    <!-- Body BEGIN -->
    <body class="corporate" style="background: #f2f2f2;">
        <div class="main">
            <div class="container">
                <div class="row register-box">
                    <form action="<?php echo $basePath; ?>user/change_password" method="post" role="form">
                        
                        <div class="row margin-bottom-10" style="text-align: center;">
                            <img src="<?php echo $resPath; ?>assets/images/logo.png" class="img-responsive">
                        </div>

                        <div class="row margin-bottom-10" style="text-align: center;">
                            <h1 style="color: #02d;">Inspections</h1>
                        </div>
                        
                        <div class="row margin-bottom-10" style='text-align: center;'>
                            <h4 style="color: red;" id="msg_alert"><?php echo $message; ?></h4>
                        </div>

                        <div class="row margin-bottom-10 form-group">
                            <input type="password" placeholder="Password" id="password" name="password" class="form-control" required maxlength="100">
                        </div>
                        <div class="row margin-bottom-30 form-group">
                            <input type="password" placeholder="Confirm Password" id="confirm_password" name="confirm_password" class="form-control" required maxlength="100">
                        </div>
                        
                        <div class="row margin-bottom-15">
                            <button type="submit" class="btn btn-primary">Reset Password</button>

                            <input type="hidden" name="token" value="<?php echo $token; ?>">
                            <input type="hidden" name="secret" value="<?php echo $secret; ?>">
                        </div>
                        
                    </form>
                </div>

            </div>
        </div>

        <input type="hidden" id="basePath" value="<?php echo $basePath; ?>">
        <input type="hidden" id="resPath" value="<?php echo $resPath; ?>">

        <script src="<?php echo $resPath; ?>assets/plugins/jquery-1.11.0.min.js" type="text/javascript"></script>
        <script src="<?php echo $resPath; ?>assets/plugins/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>

        <script src="<?php echo $resPath; ?>assets/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>      

        <script src="<?php echo $resPath; ?>assets/plugins/bootstrap-validator/js/bootstrapValidator.min.js" type="text/javascript"></script>      

        <script src="<?php echo $resPath;?>assets/plugins/jquery.blockui.min.js" type="text/javascript"></script>

        <script src="<?php echo $resPath; ?>assets/plugins/pgwdialog/pgwmodal.min.js" type="text/javascript"></script>
        <script src="<?php echo $resPath; ?>assets/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>      
        <script src="<?php echo $resPath; ?>assets/plugins/bootstrap-modal/js/bootstrap-modal.js" type="text/javascript"></script>      
        <script src="<?php echo $resPath; ?>assets/plugins/bootstrap-modal/js/bootstrap-modalmanager.js" type="text/javascript"></script>
        <script src="<?php echo $resPath; ?>assets/plugins/bootstrap-dialog/js/bootstrap-dialog.min.js" type="text/javascript"></script>      

        <script src="<?php echo $resPath;?>assets/scripts/metronic.js" type="text/javascript"></script>
        <script src="<?php echo $resPath;?>assets/scripts/layout.js" type="text/javascript"></script>
        <script src="<?php echo $resPath; ?>assets/scripts/message.js" type="text/javascript"></script>
        <script src="<?php echo $resPath; ?>assets/scripts/common.js" type="text/javascript"></script>

        <script type="text/javascript">
            function forgot_password() {
                bootbox.prompt({
                    title: 'Enter your email address',
                    callback: function(result) {
                        if (result!=null && result!="") {
                            forget_password(result);
                        }
                    }
                });
            }
            
            function forget_password(email) {
                showLoading();
                
                $.ajax({
                    type: "POST",
                    url: 'forgot_password',
                    data: { 
                        email: email
                    },
                    dataType: 'json',
                    success: function (data) {
                        hideLoading();

                        if (data.err_code == 0) {
                            showAlert("Successfully Sent!");
                        } else {
                            showAlert(data.err_msg);
                        }
                    },
                    error: function () {
                        hideLoading();
                        showAlert(Message.SERVER_ERROR);
                    }
                });
            }
            
            jQuery(document).ready(function () {
                Metronic.init(); // init metronic core componets
                Layout.init(); // init layout

                $('form').bootstrapValidator({
                    feedbackIcons: {
                        valid: 'has-success',
                        invalid: 'has-error',
                        validating: ''
                    },
                    fields: {
                        password: {
                            validators: {
                                notEmpty: {
                                    message: 'Enter the password'
                                },
                            }
                        },
                        confirm_password: {
                            validators: {
                                notEmpty: {
                                    message: 'Confirm the password'
                                },
                                identical : {
                                    field: 'password',
                                    message: 'Enter correct password'
                                }
                            }
                        }
                    }
                })
                        .on('success.field.bv', function (e, data) {
                            if (data.bv.isValid()) {
                                data.bv.disableSubmitButtons(false);
                            }
                        });

                if ($("#msg_alert").html() != '') {
                    showAlert($("#msg_alert").html());
                }
                
                $("#btn_forgot").on('click', function(e) {
                    e.preventDefault();

                    forgot_password();
                });
            });
        </script>
        <!-- END PAGE LEVEL JAVASCRIPTS -->
    </body>

    <!-- END BODY -->
</html>
