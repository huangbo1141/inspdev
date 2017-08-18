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

                            <div class="row margin-bottom-10 form-group">
                                <label class="control-label col-md-3" for="community_idv">Community ID :</label>
                                <div class="col-md-5">
                                <input type="text" placeholder="" data-mask="" id="community_idv" name="community_idv" class="form-control" maxlength="4" value="<?php echo $community['community_id'];?>">
                                </div>
                            </div>
                            <div class="row margin-bottom-10 form-group">
                                <label class="control-label col-md-3" for="community_name">Community Name :</label>
                                <div class="col-md-5">
                                <input type="text" placeholder="" id="community_name" name="community_name" class="form-control" required maxlength="255" value="<?php echo $community['community_name'];?>">
                                </div>
                            </div>
                            <div class="row margin-bottom-10 form-group">
                                <label class="control-label col-md-3" for="city">City :</label>
                                <div class="col-md-5">
                                    <input type="text" placeholder="" id="city" name="city" class="form-control" required maxlength="255" value="<?php echo $community['city'];?>">
                                </div>
                            </div>
                            <div class="row margin-bottom-10 form-group">
                                <label class="control-label col-md-3" for="region">Region :</label>
                                <div class="col-md-5">
                                    <select class="form-control" id="region" name="region">
                                        <option value="0">None</option>
<?php
    foreach ($region as $row) {
?>                          
                                        <option <?php echo $community['region']==$row['id'] ? 'selected' : ''; ?> value="<?php echo $row['id']; ?>"><?php echo $row['region']; ?></option>
<?php
    }
?>                                        
                                    </select>
                                </div>
                            </div>
                            <div class="row margin-bottom-20 form-group">
                                <label class="control-label col-md-3" for="builder">Builder :</label>
                                <div class="col-md-5">
                                    <select class="form-control" id="builder" name="builder">
                                        <option value="0">None</option>
<?php
    foreach ($builder as $row) {
?>                          
                                        <option <?php echo $community['builder']==$row['id'] ? 'selected' : ''; ?> value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
<?php
    }
?>                                        
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row margin-bottom-20 form-group">
                                <label class="control-label col-md-3"></label>
                                <div class="col-md-5">
                                <?php if ($user_permission == '1') { ?>
                                <button type="submit" class="btn btn-warning"><?php echo $page_title; ?></button>
                                <?php } ?>
                                </div>
                                
                                <input type="hidden" name="community_id" id="community_id" value="<?php echo $community_id; ?>">
                                <input type="hidden" name="kind" id="kind" value="<?php echo $kind; ?>">
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
        
        <script src="<?php echo $resPath;?>assets/plugins/inputmask/inputmask.min.js" type="text/javascript"></script>
        <script src="<?php echo $resPath;?>assets/plugins/inputmask/inputmask.min.js" type="text/javascript"></script>
        <script src="<?php echo $resPath;?>assets/plugins/inputmask/jquery.inputmask.min.js" type="text/javascript"></script>
        <script src="<?php echo $resPath;?>assets/plugins/inputmask/inputmask.numeric.extensions.min.js" type="text/javascript"></script>
        
        <script>
            jQuery(document).ready(function () {
                Metronic.init(); // init metronic core componets
                Layout.init(); // init layout
            });
        </script>
        <!-- END JAVASCRIPTS -->
        
        <script src="<?php echo $resPath;?>assets/scripts/community_edit.js" type="text/javascript"></script>
        
    </body>

    <!-- END BODY -->
</html>
