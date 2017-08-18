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
                        Received Check
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

                            <div class="row margin-bottom-10 table-filter">
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
                            
                            <div class="row margin-bottom-20 table-filter">
                                <div class="col-md-6">
                                    <label class="control-label col-md-4" for="inspector">Builder : </label>
                                    <div class="col-md-8">
                                        <select class="form-control select-picker" id="builder">
                                            <option value="">All</option>
        <?php
            foreach ($builder as $row) {
        ?>                          
                                                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
        <?php
            }
        ?>                                        
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 text-right">
                                    <div class="btn-group">
                                        <button id="btn_search" class="btn green"><i class="fa fa-search"></i> Search</button>
                                        <button id="btn_add" class="btn btn-danger"><i class="fa fa-plus"></i> Add</button>
                                        <a href="#" class="btn yellow" id="btn_import"><i class="fa fa-file-excel-o"></i> Import</a>
                                    </div>                                
                                </div>
                            </div>
                            
                            
                            <div class="row table-responsive margin-bottom-20">
                                <table id="table_content" class="display" cellspacing="0" cellpadding="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Check Date</th>
                                            <th>Builder</th>
                                            <th>Check Number</th>
                                            <th>Check Amount</th>
                                            <th width="10%" style="text-align: center;"></th>
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
        
        <input type="file" name="file" id="takeFileUpload" style="display: none;" accept=".csv" data-url="<?php echo $basePath; ?>payable/upload_check">

        
<div id="edit_dialog" class="bootbox modal fade bootbox-confirm modal-overflow" tabindex="-1" role="dialog" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="bootbox-close-button close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <div class="row margin-bottom-10">
                    <label class="control-label col-md-4">Check Date : </label>
                    <div class="col-md-8">
                        <input type="text" placeholder="" id="check_date" readonly class="form-control date-picker no-readonly"  maxlength="10" value="">
                    </div>
                </div>
                <div class="row margin-bottom-10">
                    <label class="control-label col-md-4">Builder : </label>
                    <div class="col-md-8">
                        <select class="form-control" id="builder_edit">
<?php
foreach ($builder as $row) {
?>                          
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
<?php
}
?>                                        
                        </select>
                    </div>
                </div>
                <div class="row margin-bottom-10">
                    <label class="control-label col-md-4">Check Number : </label>
                    <div class="col-md-8">
                        <input type="text" placeholder="" id="check_number" class="form-control"  maxlength="50" value="">
                    </div>
                </div>
                <div class="row">
                    <label class="control-label col-md-4">Check Amount : </label>
                    <div class="col-md-8">
                        <input type="number" placeholder="" id="check_amount" class="form-control"  step=".01" maxlength="10" value="">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>
        
<div id="upload_dialog" class="bootbox modal fade bootbox-confirm modal-overflow" tabindex="-1" role="dialog" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="bootbox-close-button close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Import Received Checks</h4>
            </div>
            <div class="modal-body">
                <p class="description">Imported CSV file should be below format: (it should have to include header)</p>
                <div class="col-xs-12">
                    <table class="table table-bordered table-condensed table-striped">
                        <thead>
                            <tr>
                                <th>Check Date</th>
                                <th>Builder</th>
                                <th>Check Number</th>
                                <th>Check Amount</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Upload</button>
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

        <script src="<?php echo $resPath;?>assets/scripts/received_check.js" type="text/javascript"></script>
        
    </body>

    <!-- END BODY -->
</html>
