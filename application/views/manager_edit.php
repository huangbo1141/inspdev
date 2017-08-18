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
                        <?php echo $page_title; ?>
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
                        <form action="#" method="post" data-toggle="validator" role="form">
                            <div class="row margin-bottom-10" >
                                <h4 style="color: red;" id="msg_alert"><?php echo $message;?></h4>
                            </div>

<?php if ($kind=='add' || $kind=='profile') { ?>
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
                            
<?php if ($page_type=='2' || $page_type=='3' || $page_type=='4') { ?>                            
                            <div class="row margin-bottom-10 form-group">
                                <label class="control-label col-md-3" for="region">Region :</label>
                                <div class="col-md-5">
                                    <select class="form-control select-picker" <?php echo $page_type=="1" ? "" : "multiple"; ?> id="region" name="region">
                                        <?php if ($page_type=='4') { ?>   
                                        <?php } else { ?>
                                        <option value="0">None</option>
                                        <?php } ?>
<?php
    foreach ($region as $row) {
        $selected = "";
        if ($page_type=="1") {
            $selected = $account['region']==$row['id'] ? "selected" : "";
        } else {
            if (isset($row['region_id']) && $row['region_id']!="") {
                $selected = "selected";
            }
        }
?>                          
                                        <option <?php echo $selected; ?> value="<?php echo $row['id']; ?>"><?php echo $row['region']; ?></option>
<?php
    }
?>                                        
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row margin-bottom-10 form-group">
                                <label class="control-label col-md-3" for="builder">Builder :</label>
                                <div class="col-md-5">
                                    <select class="form-control" id="builder" name="builder">
                                        <option value="0">None</option>
<?php
    foreach ($builder as $row) {
?>                          
                                        <option <?php echo $account['builder']==$row['id'] ? 'selected' : ''; ?> value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
<?php
    }
?>                                        
                                    </select>
                                </div>
                            </div>
                            
<?php } ?>      


<?php } ?>      

                            
<?php if ($kind=='add' || $kind=='password') { ?>                            
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
<?php } ?>                            
                            
                            <div class="row margin-bottom-20 form-group">
                                <label class="control-label col-md-3"></label>
                                <div class="col-md-5">
                                <button type="submit" class="btn btn-warning">SUBMIT</button>
                                </div>
                                
                                <input type="hidden" name="user_id" id="user_id" value="<?php echo $user_id; ?>">
                                <input type="hidden" name="kind" id="kind" value="<?php echo $kind; ?>">
                                <input type="hidden" name="type" id="type" value="<?php echo $page_type; ?>">
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
            });
        </script>
        <!-- END JAVASCRIPTS -->
        
        <script src="<?php echo $resPath;?>assets/scripts/manager_edit.js" type="text/javascript"></script>
        
    </body>

    <!-- END BODY -->
</html>
