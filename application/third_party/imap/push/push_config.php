<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of config
 *
 * @author hgc
 */
$g_documentrootname = $_SERVER['DOCUMENT_ROOT'] . "/adminuser/";
$g_relative_upload_path = "assets/uploads/";
$g_real_upload_path = $g_documentrootname . $g_relative_upload_path;

$g_env = 0;     //  0   local       1   server
$g_pushmode = 0;    //  0   dev     1   product
$g_config = array();
if ($g_env == 0) {
    $db = array(
        'host' => 'localhost',
        'dbname' => 'inspdev',
        'username' => 'root',
        'password' => '111',
    );

    $g_config = array(
        "host" => $db['host'],
        "dbname" => $db['dbname'],
        "username" => $db['username'],
        "password" => $db['password'],
        "upload" => array("realuploadpath" => $g_real_upload_path,
            "documentrootname" => $g_documentrootname,
            "relativeuploadpath" => $g_relative_upload_path));

    if ($g_pushmode == 0) {
        $g_config["pushconfig"] = array(
            // The APNS server that we will use
            'server' => 'gateway.sandbox.push.apple.com:2195',
            // The SSL certificate that allows us to connect to the APNS servers
            'certificate' => 'C:\xampp\htdocs\ayd23ams\rest\apnsv1\push\ckDev.pem',
            'passphrase' => 'twinklestar',
            // Configuration of the MySQL database
            'db' => $db,
            // Name and path of our log file
            'logfile' => 'd:\push_development.log',
        );
    } else {
        $g_config["pushconfig"] = array(
            // The APNS server that we will use
            'server' => 'gateway.push.apple.com:2195',
            // The SSL certificate that allows us to connect to the APNS servers
            'certificate' => 'C:\xampp\htdocs\ayd23ams\rest\apnsv1\push\ckPro.pem',
            'passphrase' => 'twinklestar',
            // Configuration of the MySQL database
            'db' => $db,
            // Name and path of our log file
            'logfile' => 'd:\push_production.log',
        );
    }
} else {
    //  travpholer 8VjdF#xQ1pB4w   root bohuang29@mysql     139.162.42.92
    $db = array(
        'host' => 'localhost',
        'dbname' => 'inspdev',
        'username' => 'development@insp',
        'password' => 'o8vSF396[Fzl',
    );

    $g_config = array(
        "host" => $db['host'],
        "dbname" => $db['dbname'],
        "username" => $db['username'],
        "password" => $db['password'],
        "upload" => array("realuploadpath" => $g_real_upload_path,
            "documentrootname" => $g_documentrootname,
            "relativeuploadpath" => $g_relative_upload_path));

    if ($g_pushmode == 0) {
        $g_config["pushconfig"] = array(
            // The APNS server that we will use
            'server' => 'gateway.sandbox.push.apple.com:2195',
            // The SSL certificate that allows us to connect to the APNS servers
            'certificate' => '/var/www/html/ayd23ams/rest/apnsv1/push/ckDev.pem',
            'passphrase' => 'twinklestar',
            // Configuration of the MySQL database
            'db' => $db,
            // Name and path of our log file
            'logfile' => '/root/log/push_development.log',
        );
    } else {
        $g_config["pushconfig"] = array(
            // The APNS server that we will use
            'server' => 'gateway.push.apple.com:2195',
            // The SSL certificate that allows us to connect to the APNS servers
            'certificate' => '/var/www/html/ayd23ams/rest/apnsv1/push/ckPro.pem',
            'passphrase' => 'twinklestar',
            // Configuration of the MySQL database
            'db' => $db,
            // Name and path of our log file
            'logfile' => '/root/log/push_production.log',
        );
    }
}


if (isset($_REQUEST['printmode']) && $_REQUEST['printmode'] == 1) {

} else {
    //header('Content-Type: application/json');
}

$g_ins_admin = array("id","kind","email","first_name","last_name","address","password","cell_phone","other_phone","status","region","builder","allow_email","created_at","updated_at");
$g_ins_admin_region = array("id","manager_id","region");
$g_ins_builder = array("id","user_id","name","contact","address","city","state","zip","phone","email","status","created_at","updated_at");
$g_ins_builder_check = array("id","check_date","builder","check_amount","check_number","created_at","updated_at");
$g_ins_builder_fee = array("id","builder_id","inspection_type","inspection_fee","re_inspection_fee","created_at");
$g_ins_building = array("job_number","community","address","field_manager","builder","created_at","updated_at","unit_count");
$g_ins_building_unit = array("id","job_number","address","created_at");
$g_ins_checklist = array("id","inspection_id","location_id","no","status","primary_photo","secondary_photo","description");
$g_ins_code = array("kind","code","name","value","account_category");
$g_ins_community = array("id","community_id","community_name","city","region","builder","created_at","updated_at");
$g_ins_exception_image = array("id","inspection_id","image");
$g_ins_inspection = array("id","user_id","type","job_number","community","lot","address","start_date","end_date","initials","region","field_manager","latitude","longitude","accuracy","image_front_building","house_ready","overall_comments","result_code","image_signature","ip_address","is_first","is_initials","created_at","requested_id","city","area","volume","qn","wall_area","ceiling_area","design_location","image_testing_setup","image_manometer","house_pressure","flow","result_duct_leakage","result_envelop_leakage","qn_out","ach50","app_version","is_building_unit","epo_number","epo_status","invoice_number","invoice_linked","first_submitted");
$g_ins_inspection_comment = array("id","inspection_id","no","status","primary_photo","secondary_photo","description");
$g_ins_inspection_requested = array("id","reinspection","epo_number","category","job_number","created_at","requested_at","assigned_at","completed_at","manager_id","inspector_id","time_stamp","ip_address","community_name","lot","address","status","city","area","volume","qn","wall_area","ceiling_area","design_location","is_building_unit","inspection_id","document_person");
$g_ins_inspector_payroll = array("id","inspector_id","start_date","end_date","inspector_name","inspector_email","inspector_phone","inspector_address","check_amount","check_number","inspection_count","transaction_date","status","created_at","updated_at");
$g_ins_location = array("id","inspection_id","name");
$g_ins_recipient_email = array("id","inspection_id","email","status");
$g_ins_record_payment = array("id","check_number","check_cut","pay_to","check_amount","check_details","exported_on","created_at","updated_at");
$g_ins_record_payment_invoice = array("id","payment_id","invoice_number","invoice_description","discount_amount","invoice_amount","invoice_date","community","job_number","address","option_number","line_amount","account_category","category_description","plan_name","plan_number","task_description","start_date","complete_date","status","created_at","updated_at");
$g_ins_region = array("id","region");
$g_ins_token = array("id","type","token","secret","email","created_at");
$g_ins_unit = array("id","inspection_id","no","supply","return");
$g_ins_user = array("id","email","first_name","last_name","phone_number","password","ip_address","status","address","fee","created_at","updated_at");
$g_sys_config = array("code","value");
$g_sys_recipient_email = array("id","email","status");

$g_additional_fields = array("likes", "commentcount", "likescount", "ilikethis", "bucketcount");

$g_table_status = array("initial" => 2, "published" => 0, "deleted" => 3, "trip_completed" => 4);
$g_bucket_type = array("personal" => 0, "shared" => 1);
$g_trip_status = array("initial" => 2, "published" => 0, "deleted" => 3, "trip_completed" => 4);
$g_trip_type = array("photo" => 0, "video" => 1, "trip" => 2);
$g_reserve_type = array("trip" => 0, "reserve" => 1);
$g_tablenames = array(
    "tbl_itin_day" => "tbl_itin_day",
    "tbl_itin_transport" => "tbl_itin_transport",
    "tbl_itin_rest" => "tbl_itin_rest",
    "tbl_photos" => "tbl_photos",
    "tbl_trip_day" => "tbl_trip_day",
    "tbl_trip_transport" => "tbl_trip_transport",
    "tbl_trip_rest" => "tbl_trip_rest",
    "tbl_trip_photos" => "tbl_trip_photos"
);
//$g_searchterm = array("tu_id", "tp_countryid", "tp_id_viewtop", "tp_id_viewbottom", "tp_steps", "tp_fetcharrow", "tp_location", "create_datetime", "tp_category", //"tp_key","ti_type");

$g_searchterm = array("tu_id", "tp_id_viewtop", "tp_id_viewbottom", "tp_steps", "tp_fetcharrow", "tp_location", "create_datetime", "tp_countryid", "visitor_id",
    "tp_category", "tp_key", "bucket_id", "view_mode", "ti_type", "tp_ids", "tu_last_noti", "action", "map_zoom", "map_distance","age_start", "age_end");
$g_trip_joinstatus = array("accept" => 2, "reject" => 1, "initial" => 0);
$g_tbl_make_itin = array("tp_id", "tp_status", "itin_id", "itin_days", "itin_stories", "tu_id", "action");
$g_tbl_nature = array("itin" => "1", "trip" => "2");
$g_tbl_role = array("user" => "0", "admin" => "1", "premium" => "2");
$g_poly_type = array('dots'=>0,'polygon'=>1);
