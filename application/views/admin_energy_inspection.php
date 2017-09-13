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

        <link rel="stylesheet" type="text/css" href="<?php echo $resPath; ?>assets/plugins/bootstrap-summernote/summernote.css"/>
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
                                Configuration
                                &nbsp;&nbsp;&nbsp;
                                <a class="btn btn-primary btn-submit">SUBMIT</a>
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
                        <div class="col-md-11">
                            <div class="row margin-bottom-10" >
                                <h4 style="color: red;" id="msg_alert"><?php echo $message;?></h4>
                            </div>

                            <div class="row margin-bottom-10 form-group">
                                <label class="control-label col-md-3" for="report_keep_day">House Pressure Value :</label>
                                <div class="col-md-5">
                                <input type="number" placeholder="" id="house_pressure" name="house_pressure" class="form-control" maxlength="5" value="<?php echo $house_pressure;?>">
                                </div>
                            </div>

                            <div class="row margin-bottom-10 form-group">
                                <label class="control-label col-md-3" for="report_keep_day">Message for Android Home Page :</label>
                                <div class="col-md-5">
                                <input type="text" placeholder="" id="app_home_message1" name="app_home_message1" class="form-control" value="<?php echo $app_home_message1;?>">
                                </div>
                            </div>

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

        <script src="<?php echo $resPath;?>assets/scripts/admin_energy_inspection.js" type="text/javascript"></script>

    </body>

    <!-- END BODY -->
</html>
