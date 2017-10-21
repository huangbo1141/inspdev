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
            <?php
              $check_flag1 = false;   //  when user is field manager of drain or lath and inspection requested is assigned status
              $readonly_str1 = "";
              if (isset($inspection['status']) && $user_permission == 2 && $user_builder == 1) {
                $status = $inspection['status'];
                if ($status == 1) { //  assigned one
                  $check_flag1 = true;
                }
              }
              if ($check_flag1) {
                $readonly_str1 = 'readonly';
              }
             ?>
            <div class="row page_content profile-page">
                <div class="col-md-8 col-sm-8">
                    <form action="#" method="post" data-toggle="validator" role="form" id="frm_inspection">
                        <div class="row margin-bottom-10" >
                            <h4 style="color: red;" id="msg_alert"><?php echo $message;?></h4>
                        </div>

                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="model_home">Model Home :</label>
                            <div class="col-md-5">
                                <input type="checkbox" id="model_home" name="model_home" class="form-control" style="width: 20px; margin-top: 0px;">
                            </div>
                        </div>
                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="date_requested">Inspection Requested for :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="date_requested" name="date_requested" readonly class="form-control date-picker no-readonly"  maxlength="10" required value="<?php echo $inspection['requested_at']; ?>">
                            </div>
                        </div>
                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="category">Inspection Category :</label>
                            <div class="col-md-5">
                                <select class="form-control" id="category" name="category" <?php echo $check_flag1?'readonly':'' ?>>
                                    <?php foreach ($category as $row) { ?>
                                            <option <?php echo $inspection['category'] == $row['code'] ? 'selected' : ''; ?> value="<?php echo $row['code']; ?>"  <?php echo $check_flag1?'disabled':'' ?> ><?php echo $row['name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="row margin-bottom-10 form-group for-model-home-hidden">
                            <label class="control-label col-md-3" for="job_number">Job Number :</label>
                            <div class="col-md-5">
                                <input <?php echo $check_flag1?'readonly':'' ?> type="text" placeholder="" data-mask="" id="job_number" name="job_number" class="form-control" maxlength="11" value="<?php echo $inspection['job_number'];?>">
                            </div>
                        </div>
                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="reinspection">Re-Inspection:</label>
                            <div class="col-md-5">
                                <select class="form-control" id="reinspection" name="reinspection" disabled>
                                    <option <?php echo $inspection['reinspection']=='1' ? 'selected' : '';  ?> value="1">Yes</option>
                                    <option <?php echo $inspection['reinspection']=='0' ? 'selected' : '';  ?> value="0">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="epo_number">EPO Number :</label>
                            <div class="col-md-5">
                                <input type="number" placeholder="" id="epo_number" name="epo_number" class="form-control" maxlength="10" value="<?php echo $inspection['epo_number'];?>">
                            </div>
                        </div>

                        <div class="row margin-bottom-10 form-group" id="div_community_name">
                            <label class="control-label col-md-3" for="community_name">Community Name :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="community_name" name="community_name" class="form-control" value="<?php echo $inspection['community_name'];?>">
                            </div>
                        </div>
                        <div class="row margin-bottom-10 form-group" id="div_lot">
                            <label class="control-label col-md-3" for="lot">Lot :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="lot" name="lot" class="form-control" value="<?php echo $inspection['lot'];?>" readonly maxlength="3">
                            </div>
                        </div>
                        <div class="row margin-bottom-10 form-group" id="div_address">
                            <label class="control-label col-md-3" for="address">Address :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="address" name="address" class="form-control" value="<?php echo $inspection['address'];?>">
                                <select id="address_list" name="address_list" class="form-control" style="display: none;">
                                </select>
                            </div>
                        </div>
                        <div class="row margin-bottom-10 form-group" id="div_region">
                            <label class="control-label col-md-3" for="region">Region :</label>
                            <div class="col-md-5">
                                <select id="region" name="region" class="form-control">
                                <?php foreach ($region as $row) { ?>
                                    <option value="<?php echo $row['id']; ?>"><?php echo $row['region']; ?></option>
                                <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="row margin-bottom-10 form-group for-model-home-visible">
                            <label class="control-label col-md-3" for="community_id">Community :</label>
                            <div class="col-md-5">
                                <select class="form-control" id="community_id">
                                <?php foreach ($community as $row) { ?>
                                        <option value="<?php echo $row['community_id']; ?>"><?php echo $row['community_id'] . " - " . $row['community_name']; ?></option>
                                <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="row margin-bottom-10 form-group for-model-home-visible">
                            <label class="control-label col-md-3" for="g_job_number">Job Number :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="g_job_number" name="g_job_number" class="form-control" readonly value="">
                            </div>
                        </div>
                        <div class="row margin-bottom-10 form-group for-model-home-visible">
                            <label class="control-label col-md-3" for="details">Details :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="details" name="details" class="form-control" value="<?php echo $inspection['address']; ?>">
                            </div>
                        </div>

                        <div class="row margin-bottom-10 form-group for-edit-inspection-requested" style="display: none;">
                            <label class="control-label col-md-3" for="field_manager">Field Manager :</label>
                            <div class="col-md-5">
                                <select class="form-control" id="field_manager" <?php echo $check_flag1?'readonly':'' ?>>
                                </select>
                            </div>
                        </div>

                        <div class="row margin-bottom-20 form-group">
                            <label class="control-label col-md-3"></label>
                            <div class="col-md-5">
                                <button type="submit" class="btn btn-warning">Submit</button>
                            </div>

                            <input type="hidden" id="inspector_id" name="inspector_id" value="">
                            <input type="hidden" id="requested_id" name="requested_id" value="<?php echo $inspection['id']; ?>">
                        </div>
                    </form>
                </div>
            </div>
            <!-- END PAGE CONTENT -->

        </div>
    </div>
    <!-- END CONTENT -->

</div>
<!-- END CONTAINER -->

<?php require 'common/footer.php'; ?>

<div id="job_confirm_dialog" class="bootbox modal fade bootbox-confirm modal-overflow" tabindex="-1" role="dialog" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="bootbox-close-button close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Confirm</h4>
            </div>
            <div class="modal-body">
                <div class="bootbox-body"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default yellow">Change Job Number</button>
                <button type="button" class="btn btn-primary">Edit</button>
                <button type="button" class="btn btn-danger green">Continue</button>
            </div>
        </div>
    </div>
</div>

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

<script src="<?php echo $resPath;?>assets/scripts/inspection_request_edit.js" type="text/javascript"></script>

</body>

<!-- END BODY -->
</html>
