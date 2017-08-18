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
        
        <style>
            .control-field {
                font-weight: bold;
                font-size: 15px;
            }
        </style>
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
                        Record Payments Received
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
                                    <label class="control-label col-md-4" for="inspector">Builder : </label>
                                    <div class="col-md-8">
                                        <select class="form-control select-picker" id="builder">
        <?php
            foreach ($builder as $row) {
        ?>                          
                                                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
        <?php
            }
        ?>                                        
                                            <option value="">All</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="control-label col-md-4" for="inspector">Status : </label>
                                    <div class="col-md-8">
                                        <select class="form-control select-picker" id="status">
                                            <option value="0">Pending</option>
                                            <option value="1">Paid</option>
                                            <option value="">All</option>
                                        </select>
                                    </div>
                                </div>
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
                                </div>
                                
                                <div class="col-md-6 text-right">
                                    <div class="btn-group">
                                        <button id="btn_search" class="btn green"><i class="fa fa-search"></i> Search</button>
                                        <a href="#" class="btn yellow" id="btn_import"><i class="fa fa-file-excel-o"></i> Import</a>
                                    </div>                                
                                </div>
                            </div>
                            
                            
                            <div class="row table-responsive margin-bottom-20">
                                <table id="table_content" class="display" cellspacing="0" cellpadding="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Check Number</th>
                                            <th>Check Cut</th>
                                            <th>Invoice Number</th>
                                            <th>Invoice Date</th>
                                            <th>Invoice Amount</th>
                                            <th>Inspection Type</th>
                                            <th>Job Number</th>
                                            <th>Address</th>
                                            <th>Status</th>
                                            <th>Description</th>
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
        
        <input type="file" name="file" id="takeFileUpload" style="display: none;" accept=".xls,.xlsx" data-url="<?php echo $basePath; ?>payable/upload_record_payment_received">

<div id="detail_dialog" class="bootbox modal fade bootbox-confirm modal-overflow" tabindex="-1" role="dialog" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="bootbox-close-button close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Record Payment Received Details</h4>
            </div>
            <div class="modal-body">
                <div class="row check_details">
                    <label class="control-label col-md-4">Check Details : </label>
                    <div class="col-md-8">
                        <label class="control-field"></label>
                    </div>
                </div>
                <div class="row margin-bottom-10 exported_on">
                    <label class="control-label col-md-4">Exported On : </label>
                    <div class="col-md-8">
                        <label class="control-field"></label>
                    </div>
                </div>
                
                <div class="row check_number">
                    <label class="control-label col-md-4">Check # : </label>
                    <div class="col-md-8">
                        <label class="control-field label label-danger"></label>
                    </div>
                </div>
                <div class="row check_cut">
                    <label class="control-label col-md-4">Check Cut : </label>
                    <div class="col-md-8">
                        <label class="control-field"></label>
                    </div>
                </div>
                <div class="row margin-bottom-10 pay_to">
                    <label class="control-label col-md-4">Pay To : </label>
                    <div class="col-md-8">
                        <label class="control-field"></label>
                    </div>
                </div>
                
                <div class="row margin-bottom-20 check_amount">
                    <label class="control-label col-md-4">Check Amount : </label>
                    <div class="col-md-8">
                        <label class="control-field label label-success"></label>
                    </div>
                </div>
                
                <div class="row invoice_number">
                    <label class="control-label col-md-4">Invoice Number : </label>
                    <div class="col-md-8">
                        <label class="control-field"></label>
                    </div>
                </div>
                <div class="row invoice_description">
                    <label class="control-label col-md-4">Invoice Description : </label>
                    <div class="col-md-8">
                        <label class="control-field"></label>
                    </div>
                </div>
                <div class="row discount_amount">
                    <label class="control-label col-md-4">Discount Amount : </label>
                    <div class="col-md-8">
                        <label class="control-field label label-warning"></label>
                    </div>
                </div>
                <div class="row invoice_amount">
                    <label class="control-label col-md-4">Invoice Amount : </label>
                    <div class="col-md-8">
                        <label class="control-field label label-primary"></label>
                    </div>
                </div>
                <div class="row margin-bottom-10 invoice_date">
                    <label class="control-label col-md-4">Invoice Date : </label>
                    <div class="col-md-8">
                        <label class="control-field"></label>
                    </div>
                </div>
                <div class="row community">
                    <label class="control-label col-md-4">Community : </label>
                    <div class="col-md-8">
                        <label class="control-field"></label>
                    </div>
                </div>
                <div class="row job_number">
                    <label class="control-label col-md-4">Job Number : </label>
                    <div class="col-md-8">
                        <label class="control-field"></label>
                    </div>
                </div>
                <div class="row address">
                    <label class="control-label col-md-4">Address : </label>
                    <div class="col-md-8">
                        <label class="control-field"></label>
                    </div>
                </div>
                <div class="row option_number">
                    <label class="control-label col-md-4">Option Number : </label>
                    <div class="col-md-8">
                        <label class="control-field"></label>
                    </div>
                </div>
                <div class="row line_amount">
                    <label class="control-label col-md-4">Line Amount : </label>
                    <div class="col-md-8">
                        <label class="control-field label label-info"></label>
                    </div>
                </div>
                <div class="row account_category">
                    <label class="control-label col-md-4">Account Category : </label>
                    <div class="col-md-8">
                        <label class="control-field"></label>
                    </div>
                </div>
                <div class="row margin-bottom-10 category_description">
                    <label class="control-label col-md-4">Category Description : </label>
                    <div class="col-md-8">
                        <label class="control-field"></label>
                    </div>
                </div>
                <div class="row plan_name">
                    <label class="control-label col-md-4">Plan Name : </label>
                    <div class="col-md-8">
                        <label class="control-field"></label>
                    </div>
                </div>
                <div class="row plan_number">
                    <label class="control-label col-md-4">Plan Number : </label>
                    <div class="col-md-8">
                        <label class="control-field"></label>
                    </div>
                </div>
                <div class="row margin-bottom-10 task_description">
                    <label class="control-label col-md-4">Task Description : </label>
                    <div class="col-md-8">
                        <label class="control-field"></label>
                    </div>
                </div>
                <div class="row start_date">
                    <label class="control-label col-md-4">Start Date : </label>
                    <div class="col-md-8">
                        <label class="control-field"></label>
                    </div>
                </div>
                <div class="row complete_date">
                    <label class="control-label col-md-4">Completed Date : </label>
                    <div class="col-md-8">
                        <label class="control-field"></label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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

        <script src="<?php echo $resPath;?>assets/scripts/record_payment_received.js" type="text/javascript"></script>
        
    </body>

    <!-- END BODY -->
</html>
