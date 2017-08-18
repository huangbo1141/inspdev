<?php
require 'common/variable.php';

foreach ($urls as $url) {
    $msg .= $url . '<br/>';
}

if (empty($msg)) {
    $msg = 'no data imported';
} else {
    $msg .=' was imported successfully.';
}
?>

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
                    <h3 class="page-title">
                        Imported Data List
                    </h3>
                    <hr>
                    <!-- END PAGE HEADER-->

                    <div class="row page_content">
                        <div class="col-md-12">

                            <div class="row margin-bottom-10">
                                <h4 style="color: black;" id="msg_alert"><?php echo $msg; ?></h4>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
            <!-- END CONTENT -->

        </div>
        <!-- END CONTAINER -->

        <?php require 'common/footer.php'; ?>

    </body>

    <!-- END BODY -->
</html>
