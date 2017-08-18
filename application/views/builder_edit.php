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
                    <form action="#" method="post" data-toggle="validator" role="form">
                        <div class="row margin-bottom-10" >
                            <h4 style="color: red;" id="msg_alert"><?php echo $message;?></h4>
                        </div>
                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="builder_name">Builder Name :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="builder_name" name="builder_name" class="form-control" required maxlength="255" value="<?php echo $builder['name'];?>">
                            </div>
                        </div>
                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="contact">Contact :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="contact" name="contact" class="form-control" required maxlength="255" value="<?php echo $builder['contact'];?>">
                            </div>
                        </div>
                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="address">Address :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="address" name="address" class="form-control" required maxlength="255" value="<?php echo $builder['address'];?>">
                            </div>
                        </div>
                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="city">City :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="city" name="city" class="form-control" required maxlength="255" value="<?php echo $builder['city'];?>">
                            </div>
                        </div>
                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="state">State :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="state" name="state" class="form-control" required maxlength="255" value="<?php echo $builder['state'];?>">
                            </div>
                        </div>
                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="zip">Zip Code :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="zip" name="zip" class="form-control" required maxlength="255" value="<?php echo $builder['zip'];?>">
                            </div>
                        </div>
                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="phone">Phone Number :</label>
                            <div class="col-md-5">
                                <input type="text" placeholder="" id="phone" name="phone" class="form-control" required maxlength="255" value="<?php echo $builder['phone'];?>">
                            </div>
                        </div>
                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3" for="email">Email :</label>
                            <div class="col-md-5">
                                <input type="email" placeholder="" id="email" name="email" class="form-control" required maxlength="255" value="<?php echo $builder['email'];?>">
                            </div>
                        </div>
                        
                        <div class="row margin-bottom-10 form-group">
                            <label class="control-label col-md-3">Fee :</label>
                            <div class="col-md-8">
                                <table class="dataTable table-condensed table-bordered width-100" id="fees">
                                    <thead>
                                        <tr>
                                            <th class="text-right">Inspection Type</th>
                                            <th class="text-right">Inspection Fee</th>
                                            <th class="text-right">Re-Inspection Fee</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($builder['fee'] as $row) { ?> 
                                        <tr data-type="<?php echo $row['inspection_type']; ?>">
                                            <td class="text-right"><?php echo $row['inspection_name']; ?></td>
                                            <td class="text-center"><input type="number" class="form-control text-right inspection_fee" step="0.01" value="<?php echo $row['inspection_fee']; ?>"></td>
                                            <td class="text-center"><input type="number" class="form-control text-right re_inspection_fee" step="0.01" value="<?php echo $row['re_inspection_fee']; ?>"></td>
                                        </tr>
                                    <?php } ?> 
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row margin-bottom-20 form-group">
                            <label class="control-label col-md-3"></label>
                            <div class="col-md-5">
                                <button type="submit" class="btn btn-warning"><?php echo $page_title; ?></button>
                            </div>

                            <input type="hidden" name="builder_id" id="builder_id" value="<?php echo $builder_id; ?>">
                            <input type="hidden" name="kind" id="kind" value="<?php echo $kind; ?>">
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

<script src="<?php echo $resPath;?>assets/scripts/builder_edit.js" type="text/javascript"></script>

</body>

<!-- END BODY -->
</html>
