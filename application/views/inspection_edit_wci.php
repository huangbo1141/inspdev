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
                        Edit Inspection
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
                                Edit Inspection
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
                                <h3 style="margin-top: 5px;" class="inspection-type">
                                    WCI Duct Leakage Inspection
                                    <span class="sub-title"></span>
                                </h3>
                            </div>

                            <div class="row margin-bottom-10 wci-inspection-form">
                                <div class="col-md-12">
                                    <form id="frm" action="#" method="post" >

                                        <div class="row">

                                            <div class="col-md-6">
                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5">Job Number :</label>
                                                    <div class="col-md-6">
                                                        <input type="text" placeholder="" id="job_number" name="job_number" class="form-control" maxlength="100" readonly value="<?php echo $inspection['job_number']; ?>">
                                                    </div>
                                                </div>

                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5">LOT # :</label>
                                                    <div class="col-md-6">
                                                        <input type="text" placeholder="" id="lot" name="lot" class="form-control"  maxlength="100" readonly value="<?php echo $inspection['lot']; ?>">
                                                    </div>
                                                </div>

                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5">Community :</label>
                                                    <div class="col-md-6">
                                                        <input type="text" placeholder="" id="community" name="community" class="form-control"  maxlength="100" readonly value="<?php echo $inspection['community']; ?>">
                                                    </div>
                                                </div>

                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5">Address :</label>
                                                    <div class="col-md-6">
                                                        <input type="text" placeholder="" id="address" name="address" class="form-control"  maxlength="100" value="<?php echo $inspection['address']; ?>">
                                                    </div>
                                                </div>

                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5">Is the house ready? :</label>
                                                    <div class="col-md-6">
                                                        <select id="house_ready" name="house_ready" class="form-control">
                                                            <option <?php echo $inspection['house_ready'] == '0' ? "selected" : ""; ?> value="0">No</option>
                                                            <option <?php echo $inspection['house_ready'] == '1' ? "selected" : ""; ?> value="1">Yes</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5">GPS Location :</label>
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
                                                    <label class="control-label text-right col-md-5"></label>
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
                                                
                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5">Picture of Front of Building :</label>
                                                    <div class="col-md-6">
                                                        <a href="#" class="btn btn-info" id="front_btn_add">Select</a>  <a href="#" class="btn btn-warning" id="front_btn_delete">Delete</a>
                                                        <input type="file" name="file" id="takeFileUpload_front" style="display: none;" accept=".jpg,.jpeg,.png,.gif" data-url="<?php echo $basePath; ?>api/upload/wci/front">
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5"></label>
                                                    <div class="col-md-6">
                                                        <img src="<?php $inspection['image_front_building']; ?>" id="front_image" class="for-preview" style="max-width: 250px; max-height: 250px;">
                                                    </div>
                                                </div>

                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5">Duct Testing Setup Photo :</label>
                                                    <div class="col-md-6">
                                                        <a href="#" class="btn btn-info" id="testing_btn_add">Select</a>  <a href="#" class="btn btn-warning" id="testing_btn_delete">Delete</a>
                                                        <input type="file" name="file" id="takeFileUpload_testing" style="display: none;" accept=".jpg,.jpeg,.png,.gif" data-url="<?php echo $basePath; ?>api/upload/wci/testing">
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5"></label>
                                                    <div class="col-md-6">
                                                        <img src="<?php $inspection['image_testing_setup']; ?>" id="testing_image" class="for-preview" style="max-width: 250px; max-height: 250px;">
                                                    </div>
                                                </div>

                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5">Manometer Photo :</label>
                                                    <div class="col-md-6">
                                                        <a href="#" class="btn btn-info" id="manometer_btn_add">Select</a>  <a href="#" class="btn btn-warning" id="manometer_btn_delete">Delete</a>
                                                        <input type="file" name="file" id="takeFileUpload_manometer" style="display: none;" accept=".jpg,.jpeg,.png,.gif" data-url="<?php echo $basePath; ?>api/upload/wci/manometer">
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5"></label>
                                                    <div class="col-md-6">
                                                        <img src="<?php $inspection['image_manometer']; ?>" id="manometer_image" class="for-preview" style="max-width: 250px; max-height: 250px;">
                                                    </div>
                                                </div>
                                                
                                            </div>


                                            <div class="col-md-6">
                                                <?php foreach ($unit as $row) { ?>
                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5">Unit <?php echo $row['no'] ?> Supply :</label>
                                                    <div class="col-md-6">
                                                        <input type="text" placeholder="" id="unit<?php echo $row['no'] ?>_supply" name="unit<?php echo $row['no'] ?>_supply" class="form-control wci-result-input"  maxlength="3" value="<?php echo $row['supply'] ?>">
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5">Unit <?php echo $row['no'] ?> Return :</label>
                                                    <div class="col-md-6">
                                                        <input type="text" placeholder="" id="unit<?php echo $row['no'] ?>_return" name="unit<?php echo $row['no'] ?>_return" class="form-control wci-result-input"  maxlength="3" value="<?php echo $row['return'] ?>">
                                                    </div>
                                                </div>
                                                <?php } ?>
                                                
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">Overall Commments :</label>
                                                    <div class="col-md-6">
                                                        <textarea id="overall_comment" class="form-control" style="height: 200px;"><?php echo $inspection['overall_comments']; ?></textarea>
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
                                                        <label class="control-label label-value" id="area"><?php echo $inspection['area']; ?></label>
                                                    </div>                                
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">Cond. Volume(ft<sup>3</sup>) :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value" id="volume"><?php echo $inspection['volume']; ?></label>
                                                    </div>                                
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">Wall Area(ft<sup>2</sup>) :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value" id="wall_area"><?php echo $inspection['wall_area']; ?></label>
                                                    </div>                                
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">Ceiling Area(ft<sup>2</sup>) :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value" id="ceiling_area"><?php echo $inspection['ceiling_area']; ?></label>
                                                    </div>                                
                                                </div>

                                                <div class="row form-group margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">House Pressure :</label>
                                                    <div class="col-md-6">
                                                        <input type="text" placeholder="" id="house_pressure" name="house_pressure" class="form-control wci-result-input"  maxlength="4" value="<?php echo $inspection['house_pressure']; ?>">
                                                    </div>
                                                </div>
                                                <div class="row form-group margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">Flow :</label>
                                                    <div class="col-md-6">
                                                        <input type="text" placeholder="" id="flow" name="flow" class="form-control wci-result-input"  maxlength="6" value="<?php echo $inspection['flow']; ?>">
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
                                                        <label class="label <?php echo $cls; ?>" id="result_duct_leakage"><?php echo $inspection['result_duct_leakage_name']; ?></label>
                                                    </div>                                
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">Qn :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value" id="qn"><?php echo $inspection['qn']; ?></label>
                                                    </div>                                
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">Qn.Out :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value" id="qn_out"><?php echo $inspection['qn_out']; ?></label>
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
                                                        <label class="label <?php echo $cls; ?>" id="result_envelop_leakage"><?php echo $inspection['result_envelop_leakage_name']; ?></label>
                                                    </div>                                
                                                </div>
                                                <div class="row margin-bottom-10">
                                                    <label class="control-label text-right col-md-5">ACH50 :</label>
                                                    <div class="col-md-6">
                                                        <label class="control-label label-value" id="ach50"><?php echo $inspection['ach50']; ?></label>
                                                    </div>                                
                                                </div>
                                                
                                            </div>

                                        </div>

                                        <div class="row text-right">
                                            <button type="submit" class="btn btn-primary" id="btn_submit">Submit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>



                        </div>
                    </div>    
                    <!-- END PAGE CONTENT -->

                </div>
            </div>
            <!-- END CONTENT -->

        </div>
        <!-- END CONTAINER -->

        <input type="hidden" id="inspection_id" value="<?php echo $inspection_id; ?>">

        <?php require 'common/footer.php'; ?>
        <script src="<?php echo $resPath; ?>assets/plugins/jquery-crop/script/jquery.mousewheel.min.js" type="text/javascript"></script>

        <script>
            jQuery(document).ready(function () {
                Metronic.init(); // init metronic core componets
                Layout.init(); // init layout
            });
        </script>
        <!-- END JAVASCRIPTS -->

        <script src="<?php echo $resPath; ?>assets/scripts/inspection_edit_wci.js" type="text/javascript"></script>

    </body>

    <!-- END BODY -->
</html>
