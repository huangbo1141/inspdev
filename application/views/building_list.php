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
                        Building List
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

                            <?php if ($user_permission == 1) { ?>
                            <div class="row margin-bottom-20">
                                <div class="btn-group">
                                    <button id="btn_add" class="btn blue-madison">Add New <i class="fa fa-plus"></i></button>
                                    &nbsp;
                                    <button id="btn_import" class="btn green">Import <i class="fa fa-upload"></i></button>
                                </div>
                            </div>
                            <?php } ?>
                            
                            <div class="row table-responsive">
                                <table id="table_content" class="display" cellspacing="0" cellpadding="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Job Number</th>
                                            <th>Community</th>
                                            <th>Address</th>
                                            <th>Field Mananger</th>
                                            <th width="10%" style="text-align: center;"></th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            
                            <input type="file" name="files[]" id="takeFileUpload" style="display: none;" accept=".xml" multiple data-url="<?php echo $basePath; ?>building/upload">
                        </div>
                    </div>    
                    <!-- END PAGE CONTENT -->

                </div>
            </div>
            <!-- END CONTENT -->

        </div>
        <!-- END CONTAINER -->
        
        <?php require 'common/footer.php'; ?>

        <form id="form_move_edit" action="<?php echo $basePath;?>building/edit.html" method="post">
            <input type="hidden" name="kind" id="edit_detail_kind" value="">
            <input type="hidden" name="job_number" id="edit_detail_id" value="">
            <input type="hidden" name="unit_id" id="edit_detail_id2" value="">
        </form>

        <div id="unit_dialog" class="bootbox modal fade modal-overflow" tabindex="-1" role="dialog" aria-hidden="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Enter Units</h4>
                    </div>
                    <div class="modal-body">
                        <h4 class="margin-bottom-10">Job Number: 3333-333-33</h4>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-6">Number of Unit : </label>
                            <div class="col-md-9 col-sm-6 input-group">
                                <input type="number" class="form-control" min="1" max="99" value="" id="number_of_units" placeholder="" aria-describedby="btn_unit">
                                <span class="input-group-btn">
                                    <button class="btn btn-info" id="btn_unit">Apply</span>
                                </span>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-3 col-sm-6">
                                <label class="control-label">Address : </label>
                            </div>
                            <div class="col-md-9 col-sm-6 address-area">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary">OK</button>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            jQuery(document).ready(function () {
                Metronic.init(); // init metronic core componets
                Layout.init(); // init layout
            });
        </script>
        <!-- END JAVASCRIPTS -->

        <script src="<?php echo $resPath;?>assets/scripts/building_list.js" type="text/javascript"></script>
        
    </body>

    <!-- END BODY -->
</html>
