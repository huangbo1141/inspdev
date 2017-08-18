<div class="page-header navbar navbar-fixed-top">
    <!-- BEGIN HEADER INNER -->
    <div class="page-header-inner">
        <!-- BEGIN LOGO -->
        <div class="page-logo">
<!--            <a href="<?php echo $basePath;?>dashboard/index.html">
                <img src="<?php echo $resPath; ?>assets/images/logo-1.png" alt="logo" class="logo-default"/>
                <span style="font-size: 25px; color: red;">Modern Education</span>
            </a>-->
            <span class="logo-title">Inspection Portal</span>
            <div class="menu-toggler sidebar-toggler hide">
            </div>
        </div>
        <!-- END LOGO -->

        <a href="javascript:;" class="menu-toggler responsive-toggler collapsed" data-toggle="collapse" data-target=".navbar-collapse"></a>        
        
        <!-- BEGIN TOP NAVIGATION MENU -->
        <div class="top-menu">
            <a href="<?php echo $basePath;?>user/profile.html">
                <i class="fa fa-user"></i> <?php echo $user_name; ?> - 
                <?php 
                if ($user_permission=='1') {
                    echo "Administrator";
                }
                if ($user_permission=='2') {
                    echo "Field Manager";
                }
                if ($user_permission=='3') {
                    echo "Construction Manager";
                }
                ?>
            </a>
            <a href="<?php echo $basePath;?>user/logout.html"><i class="fa fa-sign-out"></i></a>
        </div>
        <!-- END TOP NAVIGATION MENU -->
    </div>
    <!-- END HEADER INNER -->
</div>
