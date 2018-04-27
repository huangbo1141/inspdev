<?php require 'common/variable.php'; ?>

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
    <!--<![endif]-->
    <!-- BEGIN HEAD -->
    <head>
        <?php require 'common/header.php'; ?>
    </head>
    
    <body class="page-header-fixed page-quick-sidebar-over-content">
        <!-- BEGIN HEADER -->
        <?php require 'common/topbar.php'; ?>
        <!-- END HEADER -->
        
        <div class="clearfix">
        </div>
        
        <!-- BEGIN CONTAINER -->
        <div class="page-container">
            
            <!-- BEGIN SIDEBAR -->
            <div class="page-sidebar-wrapper">
                <div class="page-sidebar navbar-collapse collapse">
                    <!-- BEGIN SIDEBAR MENU -->
                    <?php require 'common/sidebar.php'; ?>
                    <!-- END SIDEBAR MENU -->
                </div>
            </div>
            <!-- END SIDEBAR -->
            
            <!-- BEGIN CONTENT -->
            <div class="page-content-wrapper">
                <div class="page-content">
                    
                    <!-- BEGIN PAGE HEADER-->
                    <div class="row inspection-page-header">
                        <div class="col-md-8 col-sm-7 col-xs-6 inspection-title">
                            <h3 class="page-title">
                                Profile <small>account information test</small>
                            </h3>
                        </div>
                        <div class="col-md-4 col-sm-5 col-xs-6 inspection-logo">
                            <img src="<?php echo LOGO_PATH; ?>" class="" alt="">
                        </div>
                    </div>
                    <hr>
                    <!-- END PAGE HEADER-->
                    
                    <!-- BEGIN PAGE CONTENT -->
                    <div class="row page_content profile-page">
                        <div class="col-md-8 col-sm-8">
                        <form action="<?php echo $basePath;?>user/update_profile.html" method="post" data-toggle="validator" role="form">
                            <div class="row margin-bottom-10" >
                                <h4 style="color: red;" id="msg_alert"><?php echo $message;?></h4>
                            </div>

                            <div class="row margin-bottom-10 form-group">
                                <label class="control-label col-md-3" for="first_name">First Name :</label>
                                <div class="col-md-5">
                                <input type="text" placeholder="" id="first_name" name="first_name" class="form-control" required maxlength="100" value="<?php echo $account['first_name'];?>">
                                </div>
                            </div>
                            <div class="row margin-bottom-10 form-group">
                                <label class="control-label col-md-3" for="last_name">Last Name :</label>
                                <div class="col-md-5">
                                <input type="text" placeholder="" id="last_name" name="last_name" class="form-control" required maxlength="100" value="<?php echo $account['last_name'];?>">
                                </div>
                            </div>
                            
                            <div class="row margin-bottom-10 form-group">
                                <label class="control-label col-md-3" for="address">Address :</label>
                                <div class="col-md-5">
                                <input type="text" placeholder="" id="address" name="address" class="form-control" required maxlength="100" value="<?php echo $account['address'];?>">
                                </div>
                            </div>
                            <div class="row margin-bottom-10 form-group">
                                <label class="control-label col-md-3" for="email">E-mail :</label>
                                <div class="col-md-5">
                                    <input type="email" placeholder="" id="email" name="email" class="form-control" required maxlength="100" value="<?php echo $account['email'];?>">
                                </div>
                            </div>
                            
                            <?php if ($user_permission==0) { ?>
                            <div class="row margin-bottom-10 form-group">
                                <label class="control-label col-md-3" for="cell_phone">Phone Number :</label>
                                <div class="col-md-5">
                                    <input type="tel" placeholder="" id="cell_phone" name="cell_phone" class="form-control" required maxlength="50" value="<?php echo $account['phone_number'];?>">
                                </div>
                            </div>
                            
                            <div class="row margin-bottom-10 form-group">
                                <label class="control-label col-md-3" for="inspector_fee">Inspector Fee(xxx.xx) :</label>
                                <div class="col-md-5">
                                    <input type="number" placeholder="" id="inspector_fee" name="inspector_fee" step=".01" class="form-control" required maxlength="10" value="<?php echo $account['fee'];?>">
                                </div>
                            </div>
                            <?php } else { ?>
                            <div class="row margin-bottom-10 form-group">
                                <label class="control-label col-md-3" for="cell_phone">Cell Phone Number :</label>
                                <div class="col-md-5">
                                    <input type="tel" placeholder="" id="cell_phone" name="cell_phone" class="form-control" required maxlength="50" value="<?php echo $account['cell_phone'];?>">
                                </div>
                            </div>
                            
                            <div class="row margin-bottom-10 form-group">
                                <label class="control-label col-md-3" for="other_phone">Other Phone Number :</label>
                                <div class="col-md-5">
                                    <input type="tel" placeholder="" id="other_phone" name="other_phone" class="form-control" maxlength="50" value="<?php echo $account['other_phone'];?>">
                                </div>
                            </div>
                            <?php } ?>
                            
                            <div class="row margin-bottom-10 form-group">
                                <label class="control-label col-md-3" for="password">Password :</label>
                                <div class="col-md-5">
                                <input type="password" placeholder="" id="password" name="password"  class="form-control" required maxlength="50" value="">
                                </div>
                            </div>
                            <div class="row margin-bottom-20 form-group">
                                <label class="control-label col-md-3" for="confirm_password">Confirm Password :</label>
                                <div class="col-md-5">
                                <input type="password" placeholder="" id="confirm_password" name="confirm_password" class="form-control" required maxlength="50" value="">
                                </div>
                            </div>

                            <?php if ($user_permission==0) { ?>
                            <?php } else { ?>
                            <div class="row margin-bottom-20 form-group">
                                <label class="control-label col-md-3" for="allow_email">Allow Receive Email :</label>
                                <div class="col-md-5">
                                    <input type="checkbox" placeholder="" id="allow_email" name="allow_email" <?php echo $account['allow_email']==1 ? "checked" : "";?> value="1">
                                </div>
                            </div>
                            <?php } ?>

                            
                            <div class="row margin-bottom-20 form-group">
                                <label class="control-label col-md-3"></label>
                                <div class="col-md-5">
                                <button type="submit" class="btn btn-warning">Update Profile</button>
                                </div>
                            </div>
                        </form>
                        </div>
                    </div>
                    <!-- END PAGE CONTENT -->
                    
                </div>
            </div>
            <!-- END CONTENT -->
            
        </div>
        <!-- END CONTAINER -->
       
        <?php require 'common/footer.php'; ?>
        
        <script>
            jQuery(document).ready(function () {
                Metronic.init(); // init metronic core componets
                Layout.init(); // init layout
                
                $('form').bootstrapValidator({
                    feedbackIcons: {
                        valid: 'has-success',
                        invalid: 'has-error',
                        validating: ''
                    },
                    fields : {
                        first_name: {
                            validators: {
                                notEmpty: {
                                    message: 'Enter the first name'
                                },
                            }
                        },
                        last_name: {
                            validators: {
                                notEmpty: {
                                    message: 'Enter the last name'
                                },
                            }
                        },
                        email: {
                            validators: {
                                notEmpty: {
                                    message: 'Enter the email'
                                },
                                emailAddress : {
                                    message: 'Enter valid email'
                                }
                            }
                        },
                        address: {
                            validators: {
                                notEmpty: {
                                    message: 'Enter the address'
                                },
                            }
                        },
                        cell_phone: {
                            validators: {
                                notEmpty: {
                                    message: 'Enter the phone number'
                                },
                            }
                        },
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
                .on('success.field.bv', function(e, data) {
                    if (data.bv.isValid()) {
                        data.bv.disableSubmitButtons(false);
                    }
                });
                
                if ($("#msg_alert").html()!=''){
                    setTimeout(hideAlert, 2000);
                }
            });
        </script>
        <!-- END JAVASCRIPTS -->
        
    </body>

    <!-- END BODY -->
</html>
