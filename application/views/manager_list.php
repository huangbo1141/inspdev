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
                    <div class="row page_content">
                        <div class="col-md-12">
                            
                            <div class="row margin-bottom-10">
                                <h4 style="color: red;" id="msg_alert"><?php echo $message; ?></h4>
                            </div>

<?php if ($user_permission==1) { ?>                            
                            <div class="row margin-bottom-20">
                                <div class="btn-group">
                                    <button id="btn_add" class="btn green">Add New <i class="fa fa-plus"></i></button>
                                </div>                                
                            </div>
<?php } ?>                            
                            
                            <div class="row table-responsive">
                                <table id="table_content" class="display" cellspacing="0" cellpadding="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Email</th>
                                            <th>First Name</th>
                                            <th>Last Name</th>
                                            <th>Address</th>
                                            <th>Phone</th>
<?php if ($page_type==2 || $page_type==3 || $page_type==4) { ?>                                            
                                            <th>Region</th>
                                            <th>Test Flag</th>
                                            <th>Builder</th>
<?php } ?>                                            
                                            <th>Status</th>
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

        <input type="hidden" id="edit_detail_id" value="">
        <input type="hidden" id="edit_detail_type" value="<?php echo $page_type; ?>">
        
        <form id="form_move_add" action="<?php echo $basePath;?>manager/add.html" method="post">
            <input type="hidden" name="kind" value="add">
            <input type="hidden" name="user_id" value="">
            <input type="hidden" name="type" value="<?php echo $page_type; ?>">
        </form>

        <form id="form_move_profile" action="<?php echo $basePath;?>manager/edit.html" method="post">
            <input type="hidden" name="kind" value="profile">
            <input type="hidden" name="user_id" id="edit_detail_id2" value="">
            <input type="hidden" name="type" value="<?php echo $page_type; ?>">
        </form>
            
        <form id="form_move_password" action="<?php echo $basePath;?>manager/edit.html" method="post">
            <input type="hidden" name="kind" value="password">
            <input type="hidden" name="user_id" id="edit_detail_id3" value="">
            <input type="hidden" name="type" value="<?php echo $page_type; ?>">
        </form>
        
        <script>
            jQuery(document).ready(function () {
                Metronic.init(); // init metronic core componets
                Layout.init(); // init layout
            });
        </script>
        <!-- END JAVASCRIPTS -->

        <script src="<?php echo $resPath;?>assets/scripts/manager_list.js" type="text/javascript"></script>
        
    </body>

    <!-- END BODY -->
</html>
