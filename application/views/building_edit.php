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
                                <label class="control-label col-md-3" for="job_number">Job Number :</label>
                                <div class="col-md-5">
                                <input type="text" placeholder="" data-mask="" id="job_number" name="job_number" class="form-control" maxlength="11" value="<?php echo $building['job_number'];?>">
                                </div>
                            </div>
<!--                            
                            <div class="row margin-bottom-10 form-group">
                                <label class="control-label col-md-3" for="region">Region : </label>
                                <div class="col-md-5">
                                    <select class="form-control select-picker" id="region">
                                        <option value="">&nbsp;</option>
    <?php
        foreach ($region as $row) {
    ?>                          
                                            <option value="<?php echo $row['id']; ?>"><?php echo $row['region']; ?></option>
    <?php
        }
    ?>                                        
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row margin-bottom-10 form-group">
                                <label class="control-label col-md-3" for="community">Community : </label>
                                <div class="col-md-5">
                                    <select class="form-control select-picker" id="community">
                                        <option value="">&nbsp;</option>
                                    </select>
                                </div>
                            </div>
                            -->
                            <div class="row margin-bottom-10 form-group">
                                <label class="control-label col-md-3" for="address">Address :</label>
                                <div class="col-md-5">
                                    <input type="text" placeholder="" id="address" name="address" class="form-control" required maxlength="255" value="<?php echo $building['address'];?>">
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
                                        <option <?php echo $building['builder']==$row['id'] ? 'selected' : ''; ?> value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
<?php
    }
?>                                        
                                    </select>
                                </div>
                            </div>

                            <?php if ($user_permission == '1') { ?>
                            <div class="row margin-bottom-10 form-group">
                                <label class="control-label col-md-3" for="field_manager">Field Manager :</label>
                                <div class="col-md-5">
                                    <input type="text" placeholder="" id="field_manager" name="field_manager" class="form-control" required maxlength="255" value="<?php echo $building['field_manager'];?>">
                                </div>
                            </div>
                            <?php } ?>
                            
                            <div class="row margin-bottom-20 form-group">
                                <label class="control-label col-md-3"></label>
                                <div class="col-md-5">
                                <?php if ($user_permission == '1') { ?>
                                <button type="submit" class="btn btn-warning"><?php echo $page_title; ?></button>
                                <?php } ?>
                                </div>
                                
                                <input type="hidden" name="kind" id="kind" value="<?php echo $kind; ?>">
                                <input type="hidden" name="kind" id="unit_id" value="<?php echo $unit_id; ?>">
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
        <input type="hidden" id="community_id" value="<?php echo $building['community']; ?>">
        
        <script src="<?php echo $resPath;?>assets/plugins/inputmask/inputmask.min.js" type="text/javascript"></script>
        <script src="<?php echo $resPath;?>assets/plugins/inputmask/jquery.inputmask.min.js" type="text/javascript"></script>
        <script src="<?php echo $resPath;?>assets/plugins/inputmask/inputmask.numeric.extensions.min.js" type="text/javascript"></script>
        
        <script src="<?php echo $resPath; ?>assets/plugins/typeahead/bootstrap3-typeahead.min.js" type="text/javascript"></script>
        
        <script>
            jQuery(document).ready(function () {
                Metronic.init(); // init metronic core componets
                Layout.init(); // init layout
            });
        </script>
        <!-- END JAVASCRIPTS -->
        
        <script src="<?php echo $resPath;?>assets/scripts/building_edit.js" type="text/javascript"></script>
        
    </body>

    <!-- END BODY -->
</html>
