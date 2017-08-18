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
                        Inspector Payroll
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

                            <div class="row margin-bottom-20 table-filter">
                                <div class="col-md-6">
                                    <label class="control-label col-md-4" for="inspector">Pay Period : </label>
                                    <div class="col-md-8">
                                        <select class="form-control select-picker" id="pay_period">
        <?php
            foreach ($range as $row) {
        ?>                          
                                                <option value="<?php echo $row['start']; ?>" data-start="<?php echo $row['start']; ?>" data-end="<?php echo $row['end']; ?>"><?php echo $row['start']; ?> ~ <?php echo $row['end']; ?></option>
        <?php
            }
        ?>                                        
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="btn-group">
                                        <button id="btn_search" class="btn green"><i class="fa fa-search"></i> Search</button>
                                    </div>                                
                                </div>
                            </div>
                            
                            <div class="row table-responsive margin-bottom-20">
                                <table id="table_content" class="table table-bordered table-condensed table-striped" cellspacing="0" cellpadding="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Inspector Name</th>
                                            <th>Email</th>
                                            <th>Phone Number</th>
                                            <th>Address</th>
                                            <th>Check Amount</th>
                                            <th>Check Number</th>                                            
                                            <th>Total Inspections</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            
<?php if ($user_permission=='1') { ?>                            
                            <div class="row margin-bottom-10 text-right" style="padding-right: 45px;">
                                <div class="btn-group">
                                    <button id="btn_submit" class="btn btn-primary">SUBMIT</button>
                                </div>                    
                            </div>
<?php } ?>                            
                            
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

        <script src="<?php echo $resPath;?>assets/scripts/inspector_payroll.js" type="text/javascript"></script>
        
    </body>

    <!-- END BODY -->
</html>
