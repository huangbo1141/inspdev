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
                                <a href="<?php echo $basePath; ?>inspection/energy.html">Energy</a>
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
                        <div class="col-md-12">

                            <div class="row">
                                <h4 style="color: red;" id="msg_alert" ></h4>
                            </div>

                            <div class="row margin-bottom-10">
                                <h3 style="margin-top: 5px;">
                                    <?php
                                        if ($inspection['type'] == 3){
                                            echo "WCI Duct Leakage Inspection";    
                                        }else{
                                            echo "PULTE Duct Leakage Inspection";    
                                        }
                                        
                                    ?>

                                    <a href="" class="btn btn-danger" style="margin-left: 32px;" id="btn_report_duct" data-id="<?php echo $inspection['id']; ?>"><i class="fa fa-file-pdf-o"></i> Generate Report(Duct Leakage)</a>
                                    <a href="" class="btn btn-danger" style="margin-left: 32px;" id="btn_report_envelop" data-id="<?php echo $inspection['id']; ?>"><i class="fa fa-file-pdf-o"></i> Generate Report(Envelope Leakage)</a>
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
                                                  <label class="control-label text-right col-md-4">Permit Number :</label>
                                                  <div class="col-md-6">
                                                      <label class="control-label label-value"><?php echo $inspection['permit_number']; ?></label>
                                                  </div>
                                              </div>
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
                                                            <img class="img-responsive for-preview google-map" data-src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo $inspection['latitude'];?>+<?php echo $inspection['longitude'];?>&zoom=15&scale=false&size=750x750&maptype=roadmap&format=jpg&visual_refresh=true"  src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo $inspection['latitude'];?>+<?php echo $inspection['longitude'];?>&zoom=16&scale=false&size=300x300&maptype=roadmap&format=jpg&visual_refresh=true" alt="Google Map">
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
                                                    <label class="control-label text-right col-md-4">Duct Testing Setup Photo :</label>
                                                    <div class="col-md-6" <?php echo $inspection['image_testing_setup'] == "" ? "style='padding-top: 3px;'" : ""; ?>>
                                                        <?php
                                                        if ($inspection['image_testing_setup'] == '') {
                                                            echo "<label class='label label-warning'>No Image</label>";
                                                        } else {
                                                            echo "<img src='" . $inspection['image_testing_setup'] . "' class='for-preview' style='max-width: 250px; max-height: 250px;'>";
                                                        }
                                                        ?>
                                                    </div>
                                                </div>

                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-4">Manometer Photo :</label>
                                                    <div class="col-md-6" <?php echo $inspection['image_manometer'] == "" ? "style='padding-top: 3px;'" : ""; ?>>
                                                        <?php
                                                        if ($inspection['image_manometer'] == '') {
                                                            echo "<label class='label label-warning'>No Image</label>";
                                                        } else {
                                                            echo "<img src='" . $inspection['image_manometer'] . "' class='for-preview' style='max-width: 250px; max-height: 250px;'>";
                                                        }
                                                        ?>
                                                    </div>
                                                </div>

                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="portlet box blue-chambray">
                                        <div class="portlet-title">
                                            <div class="caption">
                                                &nbsp; Unit Information
                                            </div>
                                            <div class="tools">
                                                <a href="javascript:;" class="collapse">
                                                </a>
                                            </div>
                                        </div>
                                        <div class="portlet-body">
                                            <ul style="font-size: 15px; line-height: 32px; margin-bottom: 0;">
                                                <?php foreach ($unit as $row) { ?>
                                                <li>Unit <?php echo $row['no']; ?> Supply :  <?php echo $row['supply']; ?></li>
                                                <li>Unit <?php echo $row['no']; ?> Return :  <?php echo $row['return']; ?></li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="portlet box blue-hoki">
                                        <div class="portlet-title">
                                            <div class="caption">
                                                &nbsp; Result Information
                                            </div>
                                            <div class="tools">
                                                <a href="javascript:;" class="collapse">
                                                </a>
                                            </div>
                                        </div>
                                        <div class="portlet-body">
                                            <form class="form-inline" action="#" method="post">
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">Overall Commments :</label>
                                                    <div class="col-md-6">
                                                        <p style="padding-top: 3px; font-weight: bold;"><?php echo $inspection['overall_comments']; ?></label>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">Signature :</label>
                                                    <div class="col-md-6">
                                                        <?php
                                                            echo "<img src='" . $inspection['image_signature'] . "' class='for-preview signature' style='max-width: 150px;'>";
                                                        ?>
                                                    </div>
                                                </div>

                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">Cond. Floor Area(ft<sup>2</sup>) :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value"><?php echo $inspection['area']; ?></label>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">Cond. Volume(ft<sup>3</sup>) :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value"><?php echo $inspection['volume']; ?></label>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">Wall Area(ft<sup>2</sup>) :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value"><?php echo $inspection['wall_area']; ?></label>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">Ceiling Area(ft<sup>2</sup>) :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value"><?php echo $inspection['ceiling_area']; ?></label>
                                                    </div>
                                                </div>

                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">House Pressure :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value"><?php echo $inspection['house_pressure']; ?></label>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">Flow :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value"><?php echo $inspection['flow']; ?></label>
                                                    </div>
                                                </div>

                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">Duct Leakage Result :</label>
                                                    <div class="col-md-6" style="padding-top: 3px;">
                                                        <?php
                                                        $cls = "label-default";
                                                        if ($inspection['result_duct_leakage']=='1')
                                                            $cls = "label-success";
                                                        if ($inspection['result_duct_leakage']=='2')
                                                            $cls = "label-warning";
                                                        if ($inspection['result_duct_leakage']=='3')
                                                            $cls = "label-danger";
                                                        ?>
                                                        <label class="label <?php echo $cls; ?>"><?php echo $inspection['result_duct_leakage_name']; ?></label>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">Qn :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value"><?php echo $inspection['qn']; ?></label>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">Qn.Out :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value"><?php echo $inspection['qn_out']; ?></label>
                                                    </div>
                                                </div>

                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">Envelope Leakage Result :</label>
                                                    <div class="col-md-6" style="padding-top: 3px;">
                                                        <?php
                                                        $cls = "label-default";
                                                        if ($inspection['result_envelop_leakage']=='1')
                                                            $cls = "label-success";
                                                        if ($inspection['result_envelop_leakage']=='2')
                                                            $cls = "label-warning";
                                                        if ($inspection['result_envelop_leakage']=='3')
                                                            $cls = "label-danger";
                                                        ?>
                                                        <label class="label <?php echo $cls; ?>"><?php echo $inspection['result_envelop_leakage_name']; ?></label>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">ACH50 :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value"><?php echo $inspection['ach50']; ?></label>
                                                    </div>
                                                </div>

                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="row">

                            </div>

                        </div>
                    </div>
                    <!-- END PAGE CONTENT -->

                </div>
            </div>
            <!-- END CONTENT -->

        </div>
        <!-- END CONTAINER -->

        <form id="form_move_list" action="<?php echo $basePath; ?>inspection/water_intrusion.html" method="post">
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

        <script src="<?php echo $resPath; ?>assets/scripts/inspection_detail_wci.js" type="text/javascript"></script>

    </body>

    <!-- END BODY -->
</html>
