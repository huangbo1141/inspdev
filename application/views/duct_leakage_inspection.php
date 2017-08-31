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
            <div class="row page_content profile-page">
                <div class="col-md-8 col-sm-8">
                    <form action="#" method="post" data-toggle="validator" role="form" id="frm_inspection">
                        <div class="row margin-bottom-10" >
                            <h4 style="color: red;" id="msg_alert"><?php echo $message;?></h4>
                        </div>

                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="date_requested">Requested Date :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="date_requested" name="date_requested" readonly class="form-control date-picker no-readonly"  maxlength="10" required value="<?php echo $inspection['requested_at']; ?>">
                            </div>
                        </div>

                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="job_number">Job Number :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="job_number" name="job_number" class="form-control" value="<?php echo $inspection['job_number']; ?>">
                            </div>
                        </div>

                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="lot">Lot # :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="lot" name="lot" class="form-control"  value="<?php echo $inspection['lot']; ?>">
                            </div>
                        </div>

                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="community">Community :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="community" name="community" class="form-control"  value="<?php echo $inspection['community_name']; ?>">
                            </div>
                        </div>

                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="address">Address :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="address" name="address" class="form-control"  value="<?php echo $inspection['address']; ?>">
                            </div>
                        </div>

                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="city">City :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="city" name="city" class="form-control"  value="<?php echo $inspection['city']; ?>">
                            </div>
                        </div>

                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="area">Cond. Floor Area(ft<sup>2</sup>) :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="area" name="area" class="form-control"  value="<?php echo $inspection['area']; ?>">
                            </div>
                        </div>

                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="volume">Cond. Volume(ft<sup>3</sup>) :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="volume" name="volume" class="form-control"  value="<?php echo $inspection['volume']; ?>">
                            </div>
                        </div>

                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="wall_area">Wall Area(ft<sup>2</sup>) :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="wall_area" name="wall_area" class="form-control"  value="<?php echo $inspection['wall_area']; ?>">
                            </div>
                        </div>

                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="ceiling_area">Ceiling Area(ft<sup>2</sup>) :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="ceiling_area" name="ceiling_area" class="form-control"  value="<?php echo $inspection['ceiling_area']; ?>">
                            </div>
                        </div>

                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="design_location">Design Location :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="design_location" name="design_location" class="form-control"  value="<?php echo $inspection['design_location']; ?>">
                            </div>
                        </div>

                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="field_manager">Field Manager</label>
                            <div class="col-md-5">
                              <select class="form-control" id="field_manager" name="field_manager">
                                  <option value="0">None</option>
<?php
if (isset($field_managers)&&is_array($field_managers)) {
    if (count($field_managers)>0) {
      foreach ($field_managers as $row) {
          ?>
                                        <option <?php echo $inspection['manager_id']==$row['id'] ? 'selected' : ''; ?> value="<?php echo $row['id']; ?>"><?php echo $row['field_manager_name']; ?></option>
      <?php
      }
    }
  }

?>
                              </select>
                                <!-- <input type="email" placeholder="" id="field_manager" name="field_manager" class="form-control"  value="<?php echo $inspection['manager_email']; ?>"> -->
                            </div>
                        </div>

                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="qn">Qn :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="qn" name="qn" minlength="4" maxlength="4" class="form-control"  value="<?php echo $inspection['qn']; ?>">
                            </div>
                        </div>

                        <div class="row margin-bottom-20 form-group">
                            <label class="control-label col-md-3" for="document_person">Document Persons :</label>
                            <div class="col-md-5">
                                <small>Comma separated list of extra email recipients:</small>
                                <input type="text" placeholder="" id="document_person" name="document_person" class="form-control"  value="<?php echo $inspection['document_person']; ?>">
                            </div>
                        </div>

                        <div class="row margin-bottom-20 form-group">
                            <label class="control-label col-md-3"></label>
                            <div class="col-md-5">
                                <button type="submit" class="btn btn-warning">Submit</button>
                            </div>

                            <input type="hidden" id="requested_id" name="requested_id" value="<?php echo $inspection['id']; ?>">
                            <input type="hidden" id="manager_id" name="manager_id" value="<?php echo $inspection['manager_id']; ?>">
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

<script>
    jQuery(document).ready(function () {
        Metronic.init(); // init metronic core componets
        Layout.init(); // init layout
    });
</script>
<!-- END JAVASCRIPTS -->

<script src="<?php echo $resPath;?>assets/scripts/duct_leakage_inspection.js" type="text/javascript"></script>

</body>

<!-- END BODY -->
</html>
