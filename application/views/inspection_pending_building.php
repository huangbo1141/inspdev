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
                                Pending Building Inspection List
                            </h3>
                        </div>
                        <div class="col-md-4 col-sm-5 col-xs-6 inspection-logo">
                            <img src="<?php echo LOGO_PATH; ?>" class="" alt="">
                        </div>
                    </div>
                    <hr>
                    <!-- END PAGE HEADER-->

                    <!-- BEGIN PAGE CONTENT -->
                    <div class="row page_content">
                        <div class="col-md-12">

                            <div class="row margin-bottom-10">
                                <h4 style="color: red;" id="msg_alert"></h4>
                            </div>

                            <div class="row margin-bottom-10">
                                <div class="col-md-6">
                                    <label class="control-label col-md-4" for="region">Region : </label>
                                    <div class="col-md-8">
                                        <select class="form-control select-picker" id="region" multiple>
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

                                <div class="col-md-6">
                                    <label class="control-label col-md-4" for="community" >Community : </label>
                                    <div class="col-md-8">
                                        <select class="form-control select-picker" id="community" multiple>
                                        </select>
                                    </div>
                                </div>

                            </div>

                            <div class="row margin-bottom-10">
                                <div class="col-md-6">
                                    <label class="control-label col-md-4">Start Date : </label>
                                    <div class="col-md-8">
                                        <input type="text" placeholder="" id="start_date" name="start_date" readonly class="form-control date-picker no-readonly"  maxlength="10" required value="">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="control-label col-md-4">End Date : </label>
                                    <div class="col-md-8">
                                        <input type="text" placeholder="" id="end_date" name="end_date" readonly class="form-control date-picker no-readonly"  maxlength="10" required value="">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="control-label col-md-4" for="region">Drainage Plane Inspection Status : </label>
                                    <div class="col-md-8">
                                        <select class="form-control select-picker" id="status1">
                                            <option value="">NONE</option>                                 
                                            <option value="1">YES</option>
                                            <option value="0">NO</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="control-label col-md-4" for="region">Lath Inspection Status : </label>
                                    <div class="col-md-8">
                                        <select class="form-control select-picker" id="status2">
                                            <option value="">NONE</option>                                 
                                            <option value="1">YES</option>
                                            <option value="0">NO</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row margin-bottom-10">
                                <div class="col-md-12 text-right">
                                    <a href="#" class="btn green" id="btn_view">View</a>
                                </div>
                            </div>

                            <div class="row table-responsive">
                                <table id="table_content" class="display" cellspacing="0" cellpadding="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Job Number</th>
                                            <th>Community</th>
                                            <th>Address</th>
                                            <th>Drainage Plane Inspection Status</th>
                                            <th>Lath Inspection Status</th>
                                            <th>Field Mananger</th>
                                            <th>Day Passed</th>
                                        </tr>
                                    </thead>
                                </table>
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

        <script src="<?php echo $resPath; ?>assets/scripts/inspection_pending_building.js" type="text/javascript"></script>

    </body>

    <!-- END BODY -->
</html>
