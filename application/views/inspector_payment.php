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
                        Processed Inspector's Payments
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

                            <div class="row table-filter">
                                <div class="col-md-6">
                                    <label class="control-label col-md-4" for="inspector">Inspector : </label>
                                    <div class="col-md-8">
                                        <select class="form-control select-picker" id="inspector">
                                            <option value="">All</option>
        <?php
            foreach ($inspector as $row) {
        ?>                          
                                                <option value="<?php echo $row['id']; ?>"><?php echo $row['first_name'] . " " . $row['last_name']; ?></option>
        <?php
            }
        ?>                                        
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="control-label col-md-4" for="pay_period">Pay Period : </label>
                                    <div class="col-md-8">
                                        <select class="form-control select-picker" id="pay_period">
                                            <option value="">All</option>
        <?php
            foreach ($period as $row) {
        ?>                          
                                                <option value="<?php echo $row['start_date']; ?>"><?php echo $row['start_date'] . " ~ " . $row['end_date']; ?></option>
        <?php
            }
        ?>                                        
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row margin-bottom-10 table-filter">
                                <div class="col-md-6">
                                    <label class="control-label col-md-4">Start Date(Transaction) : </label>
                                    <div class="col-md-8">
                                        <input type="text" placeholder="" id="start_date" name="start_date" readonly class="form-control date-picker no-readonly"  maxlength="10" required value="<?php echo $start_date; ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="control-label col-md-4">End Date(Transaction) : </label>
                                    <div class="col-md-8">
                                        <input type="text" placeholder="" id="end_date" name="end_date" readonly class="form-control date-picker no-readonly"  maxlength="10" required value="<?php echo $end_date; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row margin-bottom-20 table-filter">
                                <div class="col-md-6">
                                </div>
                                
                                <div class="col-md-6 text-right">
                                    <div class="btn-group">
                                        <button id="btn_search" class="btn green"><i class="fa fa-search"></i> Search</button>
                                        <a href="#" class="btn blue" id="btn_export"><i class="fa fa-file-pdf-o"></i> Export</a>
                                        <a href="#" class="btn blue" id="btn_export_csv"><i class="fa fa-file-excel-o"></i> Export</a>
                                    </div>                                
                                </div>
                            </div>
                            
                            
                            <div class="row table-responsive margin-bottom-20">
                                <table id="table_content" class="display" cellspacing="0" cellpadding="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Inspector Name</th>
                                            <th>Email</th>
                                            <th>Phone Number</th>
                                            <th>Address</th>
                                            <th>Pay Period</th>
                                            <th>Check Amount</th>
                                            <th>Check Number</th>
                                            <th>Transaction Date</th>
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

<div id="payment_confirm_dialog" class="bootbox modal fade bootbox-confirm modal-overflow" tabindex="-1" role="dialog" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="bootbox-close-button close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Confirm Proceed Payment</h4>
            </div>
            <div class="modal-body">
                <p class="description" style="font-size: 14px; color: #E02222;"></p>
                <div class="row">
                    <label class="control-label col-md-4">Transaction Date : </label>
                    <div class="col-md-8">
                        <input type="text" placeholder="" id="transaction_date" readonly class="form-control date-picker no-readonly"  maxlength="10" value="<?php echo $end_date; ?>">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Proceed</button>
            </div>
        </div>
    </div>
</div>
        
<div id="payment_edit_dialog" class="bootbox modal fade bootbox-confirm modal-overflow" tabindex="-1" role="dialog" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="bootbox-close-button close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Edit Proceed Payment</h4>
            </div>
            <div class="modal-body">
                <div class="row margin-bottom-10">
                    <label class="control-label col-md-4">Check Amount : </label>
                    <div class="col-md-8">
                        <input type="number" placeholder="" id="check_amount" class="form-control"  step=".01" maxlength="10" value="">
                    </div>
                </div>
                <div class="row margin-bottom-10">
                    <label class="control-label col-md-4">Check Number : </label>
                    <div class="col-md-8">
                        <input type="text" placeholder="" id="check_number" class="form-control"  maxlength="10" value="">
                    </div>
                </div>
                <div class="row">
                    <label class="control-label col-md-4">Transaction Date : </label>
                    <div class="col-md-8">
                        <input type="text" placeholder="" id="transaction_date_edit" readonly class="form-control date-picker no-readonly"  maxlength="10" value="">
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
        
        <script>
            jQuery(document).ready(function () {
                Metronic.init(); // init metronic core componets
                Layout.init(); // init layout
            });
        </script>
        <!-- END JAVASCRIPTS -->

        <script src="<?php echo $resPath;?>assets/scripts/inspector_payment.js" type="text/javascript"></script>
        
    </body>

    <!-- END BODY -->
</html>
