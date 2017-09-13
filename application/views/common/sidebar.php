<ul class="page-sidebar-menu" data-auto-scroll="true" data-slide-speed="200">
    <!-- DOC: To remove the sidebar toggler from the sidebar you just need to completely remove the below "sidebar-toggler-wrapper" LI element -->
    <li class="sidebar-toggler-wrapper" style="margin-bottom: 15px !important;">
        <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
        <div class="sidebar-toggler">
        </div>
        <!-- END SIDEBAR TOGGLER BUTTON -->
    </li>

    <?php if ($user_permission == 1) { ?>

    <?php if ($page_name == 'builder') { ?>
        <li class="active open">
            <a href="<?php echo $basePath; ?>builder/home.html">
                <i class="fa fa-user-md"></i>
                <span class="title">Builder</span>
                <span class="selected"></span>
            </a>
        </li>
    <?php } else { ?>
        <li class="">
            <a href="<?php echo $basePath; ?>builder/home.html">
                <i class="fa fa-user-md"></i>
                <span class="title">Builder</span>
            </a>
        </li>
    <?php } ?>

    <?php } ?>


    <?php if ($page_name == 'building' || $page_name == 'building_list') { ?>
        <li class="active open">
            <a href="<?php echo $basePath; ?>building/home.html">
                <i class="fa fa-building-o"></i>
                <span class="title">Buildings</span>
                <span class="selected"></span>
            </a>
        </li>
    <?php } else { ?>
        <li class="">
            <a href="<?php echo $basePath; ?>building/home.html">
                <i class="fa fa-building-o"></i>
                <span class="title">Buildings</span>
            </a>
        </li>
    <?php } ?>


    <?php if ($page_name == 'community') { ?>
        <li class="active open">
            <a href="<?php echo $basePath; ?>community/home.html">
                <i class="fa fa-bank"></i>
                <span class="title">Community</span>
                <span class="selected"></span>
            </a>
        </li>
    <?php } else { ?>
        <li class="">
            <a href="<?php echo $basePath; ?>community/home.html">
                <i class="fa fa-bank"></i>
                <span class="title">Community</span>
            </a>
        </li>
    <?php } ?>


    <?php if ($page_name == 'inspection' || $page_name == 'inspection_energy' || $page_name == 'inspection_water') { ?>
        <li class="active open">
            <a href="#">
                <i class="fa fa-database"></i>
                <span class="title">Inspections</span>
                <span class="selected"></span>
                <span class="arrow open"></span>
            </a>
            <ul class="sub-menu">

            <?php if ($page_name == 'inspection_energy') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>inspection/energy.html"> Energy</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>inspection/energy.html"> Energy</a></li>
            <?php } ?>

            <?php if ($page_name == 'inspection_water') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>inspection/water_intrusion.html"> Water Intrusion</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>inspection/water_intrusion.html"> Water Intrusion</a></li>
            <?php } ?>

            </ul>
        </li>
    <?php } else { ?>
        <li class="">
            <a href="#">
                <i class="fa fa-database"></i>
                <span class="title">Inspections</span>
                <span class="selected"></span>
                <span class="arrow"></span>
            </a>
            <ul class="sub-menu">
                <li><a href="<?php echo $basePath; ?>inspection/energy.html"> Energy</a></li>
                <li><a href="<?php echo $basePath; ?>inspection/water_intrusion.html"> Water Intrusion</a></li>
            </ul>
        </li>
    <?php } ?>


    <?php if ($page_name == 'requested_inspection') { ?>
        <li class="active open">
            <a href="<?php echo $basePath; ?>inspection/requested_lists.html">
                <i class="fa fa-database"></i>
                <span class="title">Requested Inspections</span>
                <span class="selected"></span>
            </a>
        </li>
    <?php } else { ?>
        <li class="">
            <a href="<?php echo $basePath; ?>inspection/requested_lists.html">
                <i class="fa fa-database"></i>
                <span class="title">Requested Inspections</span>
            </a>
        </li>
    <?php } ?>


    <?php if ($user_permission == 1) { ?>

    <?php if ($page_name == 'scheduling' || $page_name == 'scheduling_energy' || $page_name == 'scheduling_water') { ?>
        <li class="active open">
            <a href="#">
                <i class="fa fa-database"></i>
                <span class="title">Scheduling</span>
                <span class="selected"></span>
                <span class="arrow open"></span>
            </a>
            <ul class="sub-menu">

            <?php if ($page_name == 'scheduling_energy') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>scheduling/energy.html"> Energy</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>scheduling/energy.html"> Energy</a></li>
            <?php } ?>

            <?php if ($page_name == 'scheduling_water') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>scheduling/water_intrusion.html"> Water Intrusion</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>scheduling/water_intrusion.html"> Water Intrusion</a></li>
            <?php } ?>

            </ul>
        </li>
    <?php } else { ?>
        <li class="">
            <a href="#">
                <i class="fa fa-database"></i>
                <span class="title">Scheduling</span>
                <span class="selected"></span>
                <span class="arrow"></span>
            </a>
            <ul class="sub-menu">
                <li><a href="<?php echo $basePath; ?>scheduling/energy.html"> Energy</a></li>
                <li><a href="<?php echo $basePath; ?>scheduling/water_intrusion.html"> Water Intrusion</a></li>
            </ul>
        </li>
    <?php } ?>

    <?php } ?>


    <?php if ($user_permission==1 || $user_permission==2 || $user_permission==3) { ?>

    <?php if ($page_name == 'inspection_request') { ?>
        <li class="active open">
            <a href="<?php echo $basePath; ?>inspection/edit_inspection_requested.html">
                <i class="fa fa-database"></i>
                <span class="title">Pulte Inspection Request</span>
                <span class="selected"></span>
            </a>
        </li>
    <?php } else { ?>
        <li class="">
            <a href="<?php echo $basePath; ?>inspection/edit_inspection_requested.html">
                <i class="fa fa-database"></i>
                <span class="title">Pulte Inspection Request</span>
            </a>
        </li>
    <?php } ?>

    <?php } ?>


    <?php if ($user_permission == 1) { ?>

    <?php if ($page_name == 'duct_leakage_inspection') { ?>
        <li class="active open">
            <a href="<?php echo $basePath; ?>inspection/duct_leakage_inspection.html">
                <i class="fa fa-database"></i>
                <span class="title">Duct Leakage Inspection</span>
                <span class="selected"></span>
            </a>
        </li>
    <?php } else { ?>
        <li class="">
            <a href="<?php echo $basePath; ?>inspection/duct_leakage_inspection.html">
                <i class="fa fa-database"></i>
                <span class="title">Duct Leakage Inspection</span>
            </a>
        </li>
    <?php } ?>

    <?php } ?>



    <?php if ($user_permission == 1) { ?>

    <?php if ($page_name == 'admin' || $page_name == 'user' || $page_name == 'field_manager'  || $page_name == 'construction_manager'
            || $page_name == 'scheduler' ) { ?>
        <li class="active open">
            <a href="#">
                <i class="fa fa-user-md"></i>
                <span class="title">Users and Admins</span>
                <span class="selected"></span>
                <span class="arrow open"></span>
            </a>
            <ul class="sub-menu">

            <?php if ($page_name == 'user') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>user/inspectors.html"> Inspectors</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>user/inspectors.html"> Inspectors</a></li>
            <?php } ?>

            <?php if ($page_name == 'admin') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>manager/admin.html"> Admin</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>manager/admin.html"> Admin</a></li>
            <?php } ?>

            <?php if ($page_name == 'field_manager') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>manager/field.html"> Field Manager</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>manager/field.html"> Field Manager</a></li>
            <?php } ?>

            <?php if ($page_name == 'construction_manager') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>manager/construction.html"> Construction Manager</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>manager/construction.html"> Construction Manager</a></li>
            <?php } ?>

            <?php if ($page_name == 'scheduler') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>manager/scheduler.html"> Scheduler</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>manager/scheduler.html"> Scheduler</a></li>
            <?php } ?>

            </ul>
        </li>
    <?php } else { ?>
        <li class="">
            <a href="#">
                <i class="fa fa-user-md"></i>
                <span class="title">Users and Admins</span>
                <span class="selected"></span>
                <span class="arrow"></span>
            </a>
            <ul class="sub-menu">
                <li><a href="<?php echo $basePath; ?>user/inspectors.html"> Inspectors</a></li>
                <li><a href="<?php echo $basePath; ?>manager/admin.html"> Admin</a></li>
                <li><a href="<?php echo $basePath; ?>manager/field.html"> Field Manager</a></li>
                <li><a href="<?php echo $basePath; ?>manager/construction.html"> Construction Manager</a></li>
                <li><a href="<?php echo $basePath; ?>manager/scheduler.html"> Scheduler</a></li>
            </ul>
        </li>
    <?php } ?>

    <?php } ?>


    <?php if ($user_permission == 1) { ?>

    <?php if ($page_name == 'statistics' || $page_name == 'statistics_inspection' || $page_name == 'statistics_re_inspection'
            || $page_name == 'statistics_checklist' || $page_name == 'statistics_fieldmanager' || $page_name == 'statistics_inspector'
            || $page_name == 'inspection_pending_building') { ?>
        <li class="active open">
            <a href="#">
                <i class="fa fa-th"></i>
                <span class="title">Statistics</span>
                <span class="selected"></span>
                <span class="arrow open"></span>
            </a>
            <ul class="sub-menu">

            <?php if ($page_name == 'statistics_inspection') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>statistics/inspection.html"> Inspection</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>statistics/inspection.html"> Inspection</a></li>
            <?php } ?>

            <?php if ($page_name == 'statistics_re_inspection') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>statistics/re_inspection.html"> Re-Inspection</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>statistics/re_inspection.html"> Re-Inspection</a></li>
            <?php } ?>

            <?php if ($page_name == 'statistics_checklist') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>statistics/checklist.html"> CheckList</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>statistics/checklist.html"> CheckList</a></li>
            <?php } ?>

            <?php if ($page_name == 'statistics_fieldmanager') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>statistics/fieldmanager.html"> Field Manager</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>statistics/fieldmanager.html"> Field Manager</a></li>
            <?php } ?>

            <?php if ($page_name == 'statistics_inspector') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>statistics/inspector.html"> Inspector</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>statistics/inspector.html"> Inspector</a></li>
            <?php } ?>

            <?php if ($page_name == 'inspection_pending_building') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>inspection/pending_building.html"> Pending Building Inspection</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>inspection/pending_building.html"> Pending Building Inspection</a></li>
            <?php } ?>

            </ul>
        </li>
    <?php } else { ?>
        <li class="">
            <a href="#">
                <i class="fa fa-th"></i>
                <span class="title">Statistics</span>
                <span class="selected"></span>
                <span class="arrow"></span>
            </a>
            <ul class="sub-menu">
                <li><a href="<?php echo $basePath; ?>statistics/inspection.html"> Inspection</a></li>
                <li><a href="<?php echo $basePath; ?>statistics/re_inspection.html"> Re-Inspection</a></li>
                <li><a href="<?php echo $basePath; ?>statistics/checklist.html"> CheckList</a></li>
                <li><a href="<?php echo $basePath; ?>statistics/fieldmanager.html"> Field Manager</a></li>
                <li><a href="<?php echo $basePath; ?>statistics/inspector.html"> Inspector</a></li>
                <li><a href="<?php echo $basePath; ?>inspection/pending_building.html"> Pending Building Inspection</a></li>
            </ul>
        </li>
    <?php } ?>

    <?php } ?>



    <?php if ($user_permission == 1) { ?>

    <?php if ($page_name == 'payable' || $page_name == 'inspector_payroll' || $page_name == 'record_payment_received'
            || $page_name == 'received_check' || $page_name == 'inspector_payment'
            || $page_name == 'payable_re_inspection' || $page_name == 'payable_pending_inspection' ) { ?>
        <li class="active open">
            <a href="#">
                <i class="fa fa-credit-card"></i>
                <span class="title">Payable and Receivable</span>
                <span class="selected"></span>
                <span class="arrow open"></span>
            </a>
            <ul class="sub-menu">

            <?php if ($page_name == 'inspector_payroll') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>payable/inspector_payroll.html"> Inspector Payroll</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>payable/inspector_payroll.html"> Inspector Payroll</a></li>
            <?php } ?>

            <?php if ($page_name == 'inspector_payment') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>payable/inspector_payment.html"> Processed Inspector's Payments</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>payable/inspector_payment.html"> Processed Inspector's Payments</a></li>
            <?php } ?>

            <?php if ($page_name == 'received_check') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>payable/received_check.html"> Received Check</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>payable/received_check.html"> Received Check</a></li>
            <?php } ?>

            <?php if ($page_name == 'record_payment_received') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>payable/record_payment_received.html"> Record Payments Received</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>payable/record_payment_received.html"> Record Payments Received</a></li>
            <?php } ?>

            <?php if ($page_name == 'payable_re_inspection') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>payable/re_inspection.html"> EPO and Inspections</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>payable/re_inspection.html"> EPO and Inspections</a></li>
            <?php } ?>

            <?php if ($page_name == 'payable_pending_inspection') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>payable/pending_inspection.html"> Inspections Pending Payment</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>payable/pending_inspection.html"> Inspections Pending Payment</a></li>
            <?php } ?>

            </ul>
        </li>
    <?php } else { ?>
        <li class="">
            <a href="#">
                <i class="fa fa-credit-card"></i>
                <span class="title">Payable and Receivable</span>
                <span class="selected"></span>
                <span class="arrow"></span>
            </a>
            <ul class="sub-menu">
                <li><a href="<?php echo $basePath; ?>payable/inspector_payroll.html"> Inspector Payroll</a></li>
                <li><a href="<?php echo $basePath; ?>payable/inspector_payment.html"> Processed Inspector's Payments</a></li>
                <li><a href="<?php echo $basePath; ?>payable/received_check.html"> Received Check</a></li>
                <li><a href="<?php echo $basePath; ?>payable/record_payment_received.html"> Record Payments Received</a></li>
                <li><a href="<?php echo $basePath; ?>payable/re_inspection.html"> EPO and Inspections</a></li>
                <li><a href="<?php echo $basePath; ?>payable/pending_inspection.html"> Inspections Pending Payment</a></li>
            </ul>
        </li>
    <?php } ?>

    <?php } ?>



    <?php if ($page_name == 'profile') { ?>
        <li class="active open">
            <a href="<?php echo $basePath; ?>user/profile.html">
                <i class="icon-user"></i>
                <span class="title">Profile</span>
                <span class="selected"></span>
            </a>
        </li>
    <?php } else { ?>
        <li class="">
            <a href="<?php echo $basePath; ?>user/profile.html">
                <i class="icon-user"></i>
                <span class="title">Profile</span>
            </a>
        </li>
    <?php } ?>



    <?php if ($user_permission == 1) { ?>

    <?php if ($page_name == 'setting' || $page_name == 'recipient_email' || $page_name == 'report_template'
    || $page_name == 'admin_configuration' || $page_name == 'admin_energy_inspection') { ?>
        <li class="active open">
            <a href="#">
                <i class="fa fa-cogs"></i>
                <span class="title">Settings</span>
                <span class="selected"></span>
                <span class="arrow open"></span>
            </a>
            <ul class="sub-menu">

            <?php if ($page_name == 'admin_configuration') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>admin/configuration.html"> Configuration</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>admin/configuration.html"> Configuration</a></li>
            <?php } ?>

            <?php if ($page_name == 'recipient_email') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>admin/recipient.html"> Recipient Email</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>admin/recipient.html"> Recipient Email</a></li>
            <?php } ?>

            <?php if ($page_name == 'report_template') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>admin/template.html"> Report Template</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>admin/template.html"> Report Template</a></li>
            <?php } ?>

            <?php if ($page_name == 'admin_energy_inspection') { ?>
                <li class="active"><a href="<?php echo $basePath; ?>admin/energy_inspection.html"> Energy Inspection</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $basePath; ?>admin/energy_inspection.html"> Energy Inspection</a></li>
            <?php } ?>

            </ul>
        </li>
    <?php } else { ?>
        <li class="">
            <a href="#">
                <i class="fa fa-cogs"></i>
                <span class="title">Settings</span>
                <span class="selected"></span>
                <span class="arrow"></span>
            </a>
            <ul class="sub-menu">
                <li><a href="<?php echo $basePath; ?>admin/configuration.html"> Configuration</a></li>
                <li><a href="<?php echo $basePath; ?>admin/recipient.html"> Recipient Email</a></li>
                <li><a href="<?php echo $basePath; ?>admin/template.html"> Report Template</a></li>
                <li><a href="<?php echo $basePath; ?>admin/energy_inspection.html"> Energy Inspection</a></li>
            </ul>
        </li>
    <?php } ?>

        <li class="menu-3rd">
            <a href="<?php echo $basePath; ?>user/logout.html">
                <i class="fa fa-sign-out"></i>
                <span class="title">Log Out</span>
            </a>
        </li>

    <?php } ?>


</ul>
