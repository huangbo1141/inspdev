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
                                Water Intrusion Inspection Scheduling
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

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="control-label col-md-4" for="region">Region : </label>
                                    <div class="col-md-8">
                                        <select class="form-control select-picker" id="region">
                                            <option value="">All</option>
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
                                    <label class="control-label col-md-4" for="community">Community : </label>
                                    <div class="col-md-8">
                                        <select class="form-control select-picker" id="community">
                                            <option value="">All</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row margin-bottom-10">
                                <div class="col-md-6">
                                    <label class="control-label col-md-4">Start Date : </label>
                                    <div class="col-md-8">
                                        <input type="text" placeholder="" id="start_date" name="start_date" readonly class="form-control date-picker no-readonly"  maxlength="10" required value="<?php echo $start_date; ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="control-label col-md-4">End Date : </label>
                                    <div class="col-md-8">
                                        <input type="text" placeholder="" id="end_date" name="end_date" readonly class="form-control date-picker no-readonly"  maxlength="10" required value="<?php echo $end_date; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row margin-bottom-10">
                                <div class="col-md-6">
                                    <label class="control-label col-md-4" for="status">Status : </label>
                                    <div class="col-md-8">
                                        <select class="form-control" id="status">
                                            <option value="">All</option>
                                            <?php if ($user_permission == 0) { ?>
                                            <?php } else { ?>
                                                <option value="0">Unassigned</option>
                                            <?php } ?>
                                            <option value="1">Assigned</option>
                                            <option value="2">Completed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row margin-bottom-10">
                                <div class="col-md-12 text-right">
                                    <a href="#" class="btn green" id="btn_view">View</a>
                                    <a href="#" class="btn blue" id="btn_export">Export</a>
                                </div>
                            </div>

                            <div class="row table-responsive">
                                <table id="table_content" class="display" cellspacing="0" cellpadding="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Inspection Date</th>
                                            <th>Community</th>
                                            <th>Job Number</th>
                                            <th>Address</th>
                                            <th>Re-Insp</th>
                                            <th>EPO</th>
                                            <th>Field Manager</th>
                                            <th>Type</th>
                                            <th>Inspector</th>
                                            <th>Status</th>
                                            <th>Action</th>
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

        <form id="form_check_wci" action="<?php echo $basePath; ?>inspection/check_wci.html" method="post">
            <input type="hidden" name="id" id="detail_id3" value="">
        </form>

        <form id="form_move_pulte" action="<?php echo $basePath; ?>inspection/edit_inspection_requested.html" method="post">
            <input type="hidden" name="id" id="detail_id1" value="">
        </form>

        <form id="form_move_wci" action="<?php echo $basePath; ?>inspection/duct_leakage_inspection.html" method="post">
            <input type="hidden" name="id" id="detail_id2" value="">
        </form>
        <form id="form_move_wci_pulte" action="<?php echo $basePath; ?>inspection/duct_leakage_inspection_pulte.html" method="post">
            <input type="hidden" name="id" id="detail_id4" value="">
        </form>
        <script>
            jQuery(document).ready(function () {
                Metronic.init(); // init metronic core componets
                Layout.init(); // init layout
            });
        </script>
        <!-- END JAVASCRIPTS -->

        <script src="<?php echo $resPath; ?>assets/scripts/scheduling_water.js" type="text/javascript"></script>

    </body>

    <!-- END BODY -->
</html>
