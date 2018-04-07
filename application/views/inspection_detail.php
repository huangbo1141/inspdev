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
                        Inspection Details
                    </h3>
                </div>
                <div class="col-md-4 col-sm-5 col-xs-6 inspection-logo">
                    <img src="<?php echo LOGO_PATH; ?>" class="" alt="">
                </div>
            </div>
                    <!--<hr>-->
                    <div class="page-bar">
                        <ul class="page-breadcrumb">
                            <li>
                                Inspections
                                <i class="fa fa-angle-right"></i>
                            </li>
                            <li>
                                <a href="<?php echo $basePath; ?>inspection/water_intrusion.html">Water Intrusion</a>
                                <i class="fa fa-angle-right"></i>
                            </li>
                            <li>
                                Details
                            </li>
                            <li>
                            </li>
                        </ul>
                    </div>
                    <!-- END PAGE HEADER-->

                    <!-- BEGIN PAGE CONTENT -->
                    <div class="row page_content">
                        <div class="col-md-10">

                            <div class="row">
                                <h4 style="color: red;" id="msg_alert" ></h4>
                            </div>

                            <div class="row margin-bottom-10">
                                <h3 style="margin-top: 5px;">
                                    <?php
                                    if ($inspection['type'] == '1')
                                        echo "Drainage Plane Inspection";
                                    if ($inspection['type'] == '2')
                                        echo "Lath Inspection";
                                    ?>

                                    <a href="" class="btn btn-danger" style="margin-left: 32px;" id="btn_report" data-id="<?php echo $inspection['id']; ?>"><i class="fa fa-file-pdf-o"></i> Generate Report(Full)</a>
                                    <a href="" class="btn btn-danger" style="margin-left: 32px;" id="btn_report_pass" data-id="<?php echo $inspection['id']; ?>"><i class="fa fa-file-pdf-o"></i> Generate Report(Without Pass)</a>
                                </h3>
                            </div>

                            <div class="row margin-bottom-10">

                                <div class="col-md-6">
                                    <div class="portlet box blue-steel">
                                        <div class="portlet-title">
                                            <div class="caption">
                                                &nbsp; Basic Information
                                            </div>
                                            <div class="tools">
                                                <a href="javascript:;" class="collapse">
                                                </a>
                                            </div>
                                        </div>
                                        <div class="portlet-body">
                                            <form class="form-inline" action="#" method="post">
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4">Job Number :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value"><?php echo $inspection['job_number']; ?></label>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4">Community :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value"><?php echo $inspection['community']; ?></label>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4">LOT# :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value"><?php echo $inspection['lot']; ?></label>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4">Address :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value"><?php echo $inspection['address']; ?></label>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4">Date of Inspection :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value"><?php echo $inspection['start_date']; ?></label>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4">Inspector Initials :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value"><?php echo $inspection['initials']; ?></label>
                                                    </div>
                                                </div>

                                                <?php if (isset($region)) { ?>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4">Region :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value"><?php echo $region; ?></label>
                                                    </div>
                                                </div>
                                                <?php } ?>

                                                <?php if (isset($field_manager)) { ?>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4">Field Manager :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value"><?php echo $field_manager['first_name'] . " " . $field_manager['last_name']; ?></label>
                                                    </div>
                                                </div>
                                                <?php } ?>

                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4">House Ready :</label>
                                                    <div class="col-md-6" style="padding-top: 3px;">
                                                        <?php
                                                        if ($inspection['house_ready'] == '1') {
                                                            echo "<label class='label label-danger'>Yes</label>";
                                                        } else {
                                                            echo "<label class='label label-default'>No</label>";
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4">Location :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value">
                                                            <?php
                                                            if ($inspection['latitude'] == '-1' && $inspection['longitude'] == '-1' && $inspection['accuracy'] == '-1') {
                                                                echo "Not Captured";
                                                            } else {
                                                                echo $inspection['latitude'] . ", " . $inspection['longitude'] . ", Accuracy: " . $inspection['accuracy'] . "m";
                                                            }
                                                            ?>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4"></label>
                                                    <div class="col-md-6">
                                                            <?php
                                                            if ($inspection['latitude'] == '-1' && $inspection['longitude'] == '-1' && $inspection['accuracy'] == '-1') {
                                                            } else {
                                                            ?>
                                                            <img class="img-responsive for-preview google-map" data-src="http://maps.googleapis.com/maps/api/staticmap?center=<?php echo $inspection['latitude'];?>+<?php echo $inspection['longitude'];?>&zoom=15&scale=false&size=750x750&maptype=roadmap&format=jpg&visual_refresh=true"  src="http://maps.googleapis.com/maps/api/staticmap?center=<?php echo $inspection['latitude'];?>+<?php echo $inspection['longitude'];?>&zoom=16&scale=false&size=300x300&maptype=roadmap&format=jpg&visual_refresh=true" alt="Google Map">
                                                            <?php
                                                            }
                                                            ?>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4">Front Building :</label>
                                                    <div class="col-md-6" <?php echo $inspection['image_front_building'] == "" ? "style='padding-top: 3px;'" : ""; ?>>
                                                        <?php
                                                        if ($inspection['image_front_building'] == '') {
                                                            echo "<label class='label label-warning'>No Image</label>";
                                                        } else {
                                                            echo "<img src='" . $inspection['image_front_building'] . "' class='for-preview' style='max-width: 250px;'>";
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4">Front Side :</label>
                                                    <div class="col-md-6" <?php echo trim($inspection['image_front_building_2']) == "" ? "style='padding-top: 3px;'" : ""; ?>>
                                                        <?php
                                                        if (trim($inspection['image_front_building_2']) == '') {
                                                            echo "<label class='label label-warning'>No Image</label>";
                                                        } else {
                                                            echo "<img src='" . trim($inspection['image_front_building_2']) . "' class='for-preview' style='max-width: 250px;'>";
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4">Right Side :</label>
                                                    <div class="col-md-6" <?php echo trim($inspection['image_right_building']) == "" ? "style='padding-top: 3px;'" : ""; ?>>
                                                        <?php
                                                        if (trim($inspection['image_right_building']) == '') {
                                                            echo "<label class='label label-warning'>No Image</label>";
                                                        } else {
                                                            echo "<img src='" . trim($inspection['image_right_building']) . "' class='for-preview' style='max-width: 250px;'>";
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4">Left Side :</label>
                                                    <div class="col-md-6" <?php echo trim($inspection['image_left_building']) == "" ? "style='padding-top: 3px;'" : ""; ?>>
                                                        <?php
                                                        if (trim($inspection['image_left_building']) == '') {
                                                            echo "<label class='label label-warning'>No Image</label>";
                                                        } else {
                                                            echo "<img src='" . trim($inspection['image_left_building']) . "' class='for-preview' style='max-width: 250px;'>";
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4">Back Side :</label>
                                                    <div class="col-md-6" <?php echo trim($inspection['image_back_building']) == "" ? "style='padding-top: 3px;'" : ""; ?>>
                                                        <?php
                                                        if (trim($inspection['image_back_building']) == '') {
                                                            echo "<label class='label label-warning'>No Image</label>";
                                                        } else {
                                                            echo "<img src='" . trim($inspection['image_back_building']) . "' class='for-preview' style='max-width: 250px;'>";
                                                        }
                                                        ?>
                                                    </div>
                                                </div>

                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="portlet box blue-hoki">
                                        <div class="portlet-title">
                                            <div class="caption">
                                                &nbsp; Additional Information
                                            </div>
                                            <div class="tools">
                                                <a href="javascript:;" class="collapse">
                                                </a>
                                            </div>
                                        </div>
                                        <div class="portlet-body">
                                            <form class="form-inline" action="#" method="post">
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4">Overall Commments :</label>
                                                    <div class="col-md-6">
                                                        <p style="padding-top: 3px; font-weight: bold;"><?php echo $inspection['overall_comments']; ?></label>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4">Result :</label>
                                                    <div class="col-md-6" style="padding-top: 3px;">
                                                        <?php
                                                        $cls = "label-default";
                                                        if ($inspection['result_code']=='1')
                                                            $cls = "label-primary";
                                                        if ($inspection['result_code']=='2')
                                                            $cls = "label-warning";
                                                        if ($inspection['result_code']=='3')
                                                            $cls = "label-danger";
                                                        ?>
                                                        <label class="label <?php echo $cls; ?>"><?php echo $inspection['result_name']; ?></label>
                                                    </div>
                                                </div>

                                                <?php if (isset($images) && is_array($images)) {
                                                    foreach ($images as $row) {     ?>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4"></label>
                                                    <div class="col-md-6">
                                                        <img src="<?php echo $row['image']; ?>" class="img-responsive for-preview">
                                                    </div>
                                                </div>
                                                <?php   }
                                                } ?>

                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4">Date of Inspection :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value"><?php echo $inspection['end_date']; ?></label>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4">Signature :</label>
                                                    <div class="col-md-6">
                                                        <?php
                                                            echo "<img src='" . $inspection['image_signature'] . "' class='for-preview signature' style='max-width: 150px;'>";
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4">Recipient Emails :</label>
                                                    <div class="col-md-6">
                                                        <?php foreach ($emails as $row) { ?>
                                                        <label class="control-label label-value"><?php echo $row['email']; ?></label><br>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <?php if (isset($comments) && is_array($comments) && count($comments)>0) { ?>
                                    <div class="portlet box blue-hoki">
                                        <div class="portlet-title">
                                            <div class="caption">
                                                &nbsp; Comments
                                            </div>
                                            <div class="tools">
                                                <a href="javascript:;" class="expand">
                                                </a>
                                            </div>
                                        </div>
                                        <div class="portlet-body" style="display: none;">
                                            <ul style="font-size: 15px; line-height: 32px; margin-bottom: 0;">
                                                <?php foreach ($comments as $row) { ?>
                                                <li><?php echo $row['comment_name']; ?></li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <?php } ?>

                                </div>

                            </div>

                            <div class="row">

                                <?php
                                    foreach ($locations as $location) {
                                ?>

                                <div class="col-md-12">
                                    <div class="portlet box green-jungle">
                                        <div class="portlet-title">
                                            <div class="caption">
                                                &nbsp; Checklist - <?php echo $location['name']; ?>
                                            </div>
                                            <div class="tools">
                                                <a href="javascript:;" class="expand">
                                                </a>
                                            </div>
                                        </div>
                                        <div class="portlet-body" style="display: none;">
                                            <table class="checklist" style="width: 100%; " border="1" >
                                                <thead>
                                                    <tr>
                                                        <th style="width: 75%;">CheckPoint</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                            <?php
                                                foreach ($location['checklist'] as $checklist) {
                                            ?>

                                                <tr>
                                                    <td class="title"><?php echo $checklist['name'] ?></td>
                                                    <td class="status text-center">
                                                        <?php
                                                        $cls = "label-default";
                                                        if ($checklist['status']=='1')
                                                            $cls = "label-primary";
                                                        if ($checklist['status']=='2')
                                                            $cls = "label-danger";
                                                        if ($checklist['status']=='3')
                                                            $cls = "label-warning";
                                                        if ($checklist['status']=='4')
                                                            $cls = "label-info";
                                                        if ($checklist['status']=='5')
                                                            $cls = "label-info";

                                                        ?>
                                                        <label class="label <?php echo $cls; ?>" style="font-size: 20px;"><?php echo $checklist['status_name'] ?></label> <br>

                                                        <?php if ($checklist['status']==2 && $checklist['primary_photo']!="") { ?>
                                                        <br>
                                                        <img class="for-preview" src="<?php echo $checklist['primary_photo']; ?>" alt="" style="max-width: 200px;">
                                                        <?php } ?>

                                                        <?php if ($checklist['status']==2 && $checklist['secondary_photo']!="") { ?>
                                                        <br>
                                                        <img class="for-preview" src="<?php echo $checklist['secondary_photo']; ?>" alt="" style="max-width: 200px;">
                                                        <?php } ?>

                                                        <?php if ($checklist['status']==2 || $checklist['status']==3) { ?>
                                                        <p style="padding-top: 10px; "><?php echo $checklist['description']; ?></label>
                                                        <?php } ?>
                                                    </td>
                                                </tr>

                                            <?php
                                                }
                                            ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <?php
                                    }
                                ?>

                            </div>

                        </div>
                    </div>
                    <!-- END PAGE CONTENT -->

                </div>
            </div>
            <!-- END CONTENT -->

        </div>
        <!-- END CONTAINER -->

        <form id="form_move_list" action="<?php echo $basePath; ?>inspection/energy.html" method="post">
        </form>


        <?php require 'common/footer.php'; ?>
        <script src="<?php echo $resPath; ?>assets/plugins/jquery-crop/script/jquery.mousewheel.min.js" type="text/javascript"></script>

        <script>
            jQuery(document).ready(function () {
                Metronic.init(); // init metronic core componets
                Layout.init(); // init layout
            });
        </script>
        <!-- END JAVASCRIPTS -->

        <script src="<?php echo $resPath; ?>assets/scripts/inspection_detail.js" type="text/javascript"></script>

    </body>

    <!-- END BODY -->
</html>
