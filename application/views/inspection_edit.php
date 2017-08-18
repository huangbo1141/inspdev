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
                                <a href="<?php echo $basePath; ?>inspection/water_intrusion.html">Water Intrusion</a>
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
                                    <?php
                                    if ($inspection['type'] == '1')
                                        echo "Drainage Plane Inspection";
                                    if ($inspection['type'] == '2')
                                        echo "Lath Inspection";
                                    ?>
                                    <span class="sub-title"></span>
                                </h3>
                            </div>

                            <div class="row margin-bottom-10 inspection-form step-1">
                                <div class="col-md-12">
                                    <form id="frm_step1" action="#" method="post" >

                                        <div class="row">

                                            <div class="col-md-6">
                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5">Job Number :</label>
                                                    <div class="col-md-6">
                                                        <input type="text" placeholder="" id="job_number" name="job_number" class="form-control" maxlength="100" <?php echo $user_permission!=1 ? "readonly" : ""; ?> value="<?php echo $inspection['job_number']; ?>">
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
                                                        <input type="text" placeholder="" id="address" name="address" class="form-control"  maxlength="100" <?php echo $user_permission!=1 ? "readonly" : ""; ?> value="<?php echo $inspection['address']; ?>">
                                                    </div>
                                                </div>

                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5">Picture of Front of Building :</label>
                                                    <div class="col-md-6">
                                                        <?php if ($user_permission==1) { ?>
                                                        <a href="#" class="btn btn-info" id="front_btn_add">Select</a>  <a href="#" class="btn btn-warning" id="front_btn_delete">Delete</a>
                                                        <?php } ?>
                                                        <input type="file" name="file" id="takeFileUpload_front" style="display: none;" accept=".jpg,.jpeg,.png,.gif" data-url="<?php echo $basePath; ?>api/upload/<?php echo $inspection['type'] == '1' ? "drainage" : "lath"; ?>/front">
                                                    </div>
                                                </div>

                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5"></label>
                                                    <div class="col-md-6">
                                                        <img src="<?php $inspection['image_front_building']; ?>" id="front_image" class="for-preview" style="max-width: 250px;">
                                                    </div>
                                                </div>

                                            </div>


                                            <div class="col-md-6">
                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5">Date of Inspection :</label>
                                                    <div class="col-md-6">
                                                        <input type="text" placeholder="" id="start_date" name="start_date" readonly class="form-control <?php echo $user_permission==1 ? "date-picker" : ""; ?> no-readonly"  maxlength="10" required value="<?php echo $inspection['start_date']; ?>">
                                                    </div>
                                                </div>

                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5">Is the house ready? :</label>
                                                    <div class="col-md-6">
                                                        <select id="house_ready" name="house_ready" class="form-control" disabled>
                                                            <option <?php echo $inspection['house_ready'] == '0' ? "selected" : ""; ?> value="0">No</option>
                                                            <option <?php echo $inspection['house_ready'] == '1' ? "selected" : ""; ?> value="1">Yes</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5">Inspector Initials :</label>
                                                    <div class="col-md-6">
                                                        <input type="text" placeholder="" id="initials" name="initials" class="form-control"  maxlength="100" <?php echo $user_permission!=1 ? "readonly" : ""; ?> value="<?php echo $inspection['initials']; ?>">
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
                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5">Field Manager :</label>
                                                    <div class="col-md-6">
                                                        <select id="field_manager" name="field_manager" <?php echo $user_permission!=1 ? "disabled" : ""; ?> class="form-control select-picker">
                                                        <?php foreach ($fm as $row) { ?>
                                                            <option <?php echo $inspection['field_manager']==$row['id'] ? "selected" : ""; ?> value="<?php echo $row['id']; ?>"><?php echo $row['first_name'] . " " . $row['last_name']; ?></option>
                                                        <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <?php } ?>

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
                                                
                                            </div>

                                        </div>

                                        <div class="row">
                                            <a href="#" class="btn btn-primary btn-next button-for-submit" data-step="1">Next</a>
                                        </div>

                                    </form>
                                </div>
                            </div>

                            <div class="row margin-bottom-10 inspection-form step-2">
                                <div class="col-md-12">
                                    <form id="frm_step2" action="#" method="post" >

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
                                                                                if ($checklist['status'] == '1')
                                                                                    $cls = "label-primary";
                                                                                if ($checklist['status'] == '2')
                                                                                    $cls = "label-danger";
                                                                                if ($checklist['status'] == '3')
                                                                                    $cls = "label-warning";
                                                                                if ($checklist['status'] == '4')
                                                                                    $cls = "label-info";
                                                                                if ($checklist['status'] == '5')
                                                                                    $cls = "label-info";
                                                                                ?>
                                                                                <label class="label <?php echo $cls; ?>" style="font-size: 20px;"><?php echo $checklist['status_name'] ?></label> <br>

                                                                                <?php if ($checklist['status'] == 2 && $checklist['primary_photo'] != "") { ?>
                                                                                    <br>
                                                                                    <img class="for-preview" src="<?php echo $checklist['primary_photo']; ?>" alt="" style="max-width: 200px;">
                                                                                <?php } ?>

                                                                                <?php if ($checklist['status'] == 2 && $checklist['secondary_photo'] != "") { ?>
                                                                                    <br>
                                                                                    <img class="for-preview" src="<?php echo $checklist['secondary_photo']; ?>" alt="" style="max-width: 200px;">
                                                                                <?php } ?>

                                                                                <?php if ($checklist['status'] == 2 || $checklist['status'] == 3) { ?>
                                                                                    <p style="padding-top: 10px; "><?php echo $checklist['description']; ?></p>
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

                                        <div class="row">
                                            <a href="#" class="btn btn-primary btn-next button-for-submit" data-step="2">Next</a>
                                            &nbsp;
                                            <a href="#" class="btn btn-default btn-prev button-for-submit" data-step="2">Prev</a>
                                        </div>

                                    </form>
                                </div>
                            </div>


                            <div class="row margin-bottom-10 inspection-form step-4">
                                <div class="col-md-12">
                                    <form id="frm_step4" action="#" method="post" >

                                        <div class="row">

                                            <div class="col-md-6">
                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-3">Result :</label>
                                                    <div class="col-md-6">
                                                        <select id="result_code" name="result_code" <?php echo $user_permission!=1 ? "disabled" : ""; ?> class="form-control">
                                                            <option <?php echo $inspection['result_code'] == '1' ? "selected" : ""; ?> value="1">Pass</option>
                                                            <option <?php echo $inspection['result_code'] == '2' ? "selected" : ""; ?> value="2">Pass with Exception</option>
                                                            <option <?php echo $inspection['result_code'] == '3' ? "selected" : ""; ?> value="3">Fail</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row margin-bottom-10 form-group for-pass-exception">
                                                    <label class="control-label text-right col-md-3">Images :</label>
                                                    <div class="col-md-6">
                                                        <a href="#" class="btn btn-warning" id="exception_btn_add">Add</a>
                                                        <input type="file" name="file" id="takeFileUpload_exception" style="display: none;" accept=".jpg,.jpeg,.png,.gif" data-url="<?php echo $basePath; ?>api/upload/<?php echo $inspection['type'] == '1' ? "drainage" : "lath"; ?>/exception">
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10 form-group for-pass-exception">
                                                    <label class="control-label text-right col-md-3"></label>
                                                    <div class="col-md-8 image-list" id="exception_images">

                                                        <?php if ($inspection['result_code'] == '2' && isset($images) && is_array($images)) {
                                                            foreach ($images as $row) {
                                                                ?>
                                                                <div class="col-md-4 item">
                                                                    <img data-src="<?php echo $row['image']; ?>" src="<?php echo $resPath; ?>blank.png" style="background: url('<?php echo $row['image']; ?>') center center no-repeat; background-size: cover;" class="img-responsive for-preview thumb-image">
                                                                    <a href="#" class="item-remove"><i class="fa fa-times"></i></a>
                                                                </div>
                                                            <?php }
                                                        }
                                                        ?>

                                                    </div>
                                                </div>

                                                <div class="row">
                                                <?php if (isset($comments) && is_array($comments) && count($comments)>0) { ?>
                                                <div class="portlet box blue-hoki">
                                                    <div class="portlet-title">
                                                        <div class="caption">
                                                            &nbsp; Comments
                                                        </div>
                                                        <div class="tools">
                                                            <a href="javascript:;" class="collapse">
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="portlet-body">
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

                                            <div class="col-md-6">
                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5">Date of Inspection :</label>
                                                    <div class="col-md-6">
                                                        <input type="text" placeholder="" id="end_date" name="end_date" readonly class="form-control no-readonly"  maxlength="10" readonly value="<?php echo $inspection['end_date']; ?>">
                                                    </div>
                                                </div>

                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5">Signature :</label>
                                                    <div class="col-md-6">
                                                        <?php
                                                        echo "<img src='" . $inspection['image_signature'] . "' class='for-preview signature' style='width: 100%;'>";
                                                        ?>
                                                    </div>
                                                </div>

                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-5">Overall Comments :</label>
                                                    <div class="col-md-6">
                                                        <textarea class="form-control" required <?php echo $user_permission!=1 ? "readonly" : ""; ?> id="txt_overall_comment" name="txt_overall_comment" style="min-height: 150px;"><?php echo $inspection['overall_comments']; ?></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="row">
                                            <a href="#" class="btn btn-primary btn-next button-for-submit" data-step="4">Next</a>
                                            &nbsp;
                                            <a href="#" class="btn btn-default btn-prev button-for-submit" data-step="4">Prev</a>
                                        </div>

                                    </form>
                                </div>
                            </div>


                            <div class="row margin-bottom-10 inspection-form step-5">
                                <div class="col-md-12">
                                    <form id="frm_step5" action="#" method="post" >

                                        <div class="row">

                                            <div class="col-md-10">
                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-3">Email :</label>
                                                    <div class="col-md-8">
<?php if ($user_permission==1) { ?>
                                                        <input type="email" placeholder="" id="recipient_email" name="recipient_email" class="form-control"  maxlength="100" value="" style="float: left; width: 250px;">
                                                        <a href="#" class="btn btn-info" id="email_btn_add" style="float: left;">Add</a>
<?php } ?>                                                        
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-10 form-group">
                                                    <label class="control-label text-right col-md-3"></label>
                                                    <div class="col-md-8">
                                                        <ul class="data-list" id="recipient_emails">
                                                            <?php foreach ($emails as $row) { ?>
                                                                <li data-email="<?php echo $row['email']; ?>">
                                                                    <?php echo $row['email']; ?> 
                                                                <?php if ($user_permission==1) { ?>
                                                                    <a href="#" class="btn"><i class="fa fa-times"></i></a>
                                                                <?php } ?>
                                                                </li>
<?php } ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="row">
                                            <a href="#" class="btn btn-primary btn-next button-for-submit" data-step="5">Next</a>
                                            &nbsp;
                                            <a href="#" class="btn btn-default btn-prev button-for-submit" data-step="5">Prev</a>
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

<script src="<?php echo $resPath;?>assets/plugins/inputmask/inputmask.min.js" type="text/javascript"></script>
<script src="<?php echo $resPath;?>assets/plugins/inputmask/jquery.inputmask.min.js" type="text/javascript"></script>
<script src="<?php echo $resPath;?>assets/plugins/inputmask/inputmask.extensions.min.js" type="text/javascript"></script>
<script src="<?php echo $resPath;?>assets/plugins/inputmask/inputmask.numeric.extensions.min.js" type="text/javascript"></script>
        
        <script>
            jQuery(document).ready(function () {
                Metronic.init(); // init metronic core componets
                Layout.init(); // init layout
            });
        </script>
        <!-- END JAVASCRIPTS -->

        <script src="<?php echo $resPath; ?>assets/scripts/inspection_edit.js" type="text/javascript"></script>

    </body>

    <!-- END BODY -->
</html>
