<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

include_once APPPATH . '/third_party/imap/push/push_config.php';

class Inspection extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        //        $this->load->library('user_agent');
        $this->load->library('holiday');
        $this->load->library('m_checkwci');
        $this->load->helper('directory');

        $this->load->model('user_model');
        $this->load->model('utility_model');
        $this->load->model('datatable_model');
    }

    private function send_mail($subject, $body, $sender, $isHTML = false)
    {
        $this->load->library('mailer/phpmailerex');
        $mail = new PHPMailer;

        $mail->SMTPDebug = 2;                               // Enable verbose debug output
        $mail->Debugoutput = 'error_log';

        $mail->Timeout = 60;
        $mail->Timelimit = 60;

        //        if (strpos(base_url(), "https://")===false) {
//            $mail->isSMTP();                                      // Set mailer to use SMTP
//        } else {
//            $mail->isMail();                                      // Set mailer to use SMTP
//        }
        $mail->isSMTP();                                      // Set mailer to use SMTP

        $mail->Host = SMTP_HOST;  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = SMTP_USER;                // SMTP username
        $mail->Password = SMTP_PASSWORD;                         // SMTP password
        $mail->SMTPSecure = '';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = SMTP_PORT;                       // TCP port to connect to

        $mail->From = SMTP_FROM;
        $mail->FromName = SMTP_NAME;

        $recipients = array_map("unserialize", array_unique(array_map("serialize", $sender)));
        foreach ($recipients as $row) {
            $mail->addAddress($row['email']);     // Add a recipient
        }

        $mail->isHTML($isHTML);                                  // Set email format to HTML

        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = "";

        if ($mail->send()) {
        } else {
            return $mail->ErrorInfo;
        }

        return "";
    }

    public function energy()
    {
        if (!$this->session->userdata('user_id')) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $page_data['page_name'] = 'inspection_energy';
        $this->load->view('inspection_energy', $page_data);
    }

    public function water_intrusion()
    {
        if (!$this->session->userdata('user_id')) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $page_data['page_name'] = 'inspection_water';
        $this->load->view('inspection_water', $page_data);
    }

    public function load_list()
    {
        $table = " ins_code c1, ins_code c2, ins_code c3, ins_code c4, ins_inspection a "
                . " left join ins_region r on r.id=a.region "
                . " left join ins_user u on a.user_id=u.id "
                . " where c1.kind='ins' and c1.code=a.type and c2.kind='rst' and c2.code=a.result_code and c3.kind='rst_duct' and c3.code=a.result_duct_leakage and c4.kind='rst_envelop' and c4.code=a.result_envelop_leakage ";

        if ($this->session->userdata('permission') == 1) {
        } elseif ($this->session->userdata('permission') == 0) {
            $table .= " and a.user_id='" . $this->session->userdata('user_id') . "' ";
        } elseif ($this->session->userdata('permission') == 4) {
            $table .= " and a.region in ( select region from ins_admin_region where manager_id='" . $this->session->userdata('user_id') . "' ) ";
        } else {
            $table .= " and a.region in ( select region from ins_admin_region where manager_id='" . $this->session->userdata('user_id') . "' ) ";
            //            $region = $this->session->userdata('user_region');
//            if ($region=='0') {
//                $region = "";
//            } else {
//                $table .= " and a.region='$region' ";
//            }
        }

        $type = $this->input->get_post('type');
        if ($type === false) {
            $type = "";
        }

        if ($type == "1_2") {
            $table .= " and ( a.type=1 or a.type=2 )";
            $cols = array("a.type", "a.job_number", "a.address", "a.start_date", "u.first_name", "u.email", "r.region", "a.result_code", "a.house_ready");
        } elseif ($type == '3') {
            $table .= " and ( a.type=3 )";
            $cols = array("a.type", "a.job_number", "a.address", "a.start_date", "u.first_name", "u.email", "a.result_code", "a.qn", "a.ach50", "a.house_ready");
        } else {
            $cols = array("a.type", "a.job_number", "a.address", "a.start_date", "u.first_name", "u.email", "r.region", "a.result_code", "a.house_ready");
        }

        $result = array();

        $amount = 10;
        $start = 0;
        $col = 3;

        $dir = "desc";

        $sStart = $this->input->get_post('start');
        $sAmount = $this->input->get_post('length');
        //	$sCol = $this->input->get_post('iSortCol_0');
        //      $sdir = $this->input->get_post('sSortDir_0');
        $sCol = "";
        $sdir = "";

        $sCol = $this->input->get_post("order");
        foreach ($sCol as $row) {
            foreach ($row as $key => $value) {
                if ($key == 'column') {
                    $sCol = $value;
                }
                if ($key == 'dir') {
                    $sdir = $value;
                }
            }
        }

        $searchTerm = "";
        $search = $this->input->get_post("search");
        foreach ($search as $key => $value) {
            if ($key == 'value') {
                $searchTerm = $value;
            }
        }

        if ($sStart !== false && strlen($sStart) > 0) {
            $start = intval($sStart);
            if ($start < 0) {
                $start = 0;
            }
        }

        if ($sAmount !== false && strlen($sAmount) > 0) {
            $amount = intval($sAmount);
            if ($amount < 10 || $amount > 100) {
                $amount = 10;
            }
        }

        if ($sCol !== false && strlen($sCol) > 0) {
            $col = intval($sCol);
            if ($col < 0 || $col >= count($cols)) {
                $col = 3;
            }
        }

        if ($sdir && strlen($sdir) > 0) {
            if ($sdir != "desc") {
                $dir = "asc";
            }
        }

        $colName = $cols[$col];
        $total = 0;
        $totalAfterFilter = 0;

        $sql = " select count(*) from " . $table . "  ";
        $total = $this->datatable_model->get_count($sql);
        $totalAfterFilter = $total;

        $sql = " select  a.*, c1.name as inspection_type, c2.name as result_name, u.email, u.first_name, u.last_name, r.region as region_name, "
                . " c3.name as result_duct_leakage_name, c4.name as result_envelop_leakage_name, "
                . " '' as additional from " . $table . " ";
        $searchSQL = "";
        $globalSearch = " ( "
                . " replace(a.job_number,'-','') like '%" . str_replace('-', '', $searchTerm) . "%' or "
                . " u.email like '%" . $searchTerm . "%' or  "
                . " a.start_date like '%" . $searchTerm . "%' or  "
                . " u.first_name like '%" . $searchTerm . "%' or  "
                . " u.last_name like '%" . $searchTerm . "%' or  "
                . " r.region like '%" . $searchTerm . "%' or  "
                . " c1.name like '%" . $searchTerm . "%' or  "
                . " c3.name like '%" . $searchTerm . "%' or  "
                . " c4.name like '%" . $searchTerm . "%' or  "
                . " c2.name like '%" . $searchTerm . "%' "
                . " ) ";

        if ($searchTerm && strlen($searchTerm) > 0) {
            $searchSQL .= " and " . $globalSearch;
        }

        $sql .= $searchSQL;
        $sql .= " order by " . $colName . " " . $dir . " ";
        $sql .= " limit " . $start . ", " . $amount . " ";
        $data = $this->datatable_model->get_content($sql);

        $sql = " select count(*) from " . $table . " ";
        if (strlen($searchSQL) > 0) {
            $sql .= $searchSQL;
            $totalAfterFilter = $this->datatable_model->get_count($sql);
        }

        if (!$this->session->userdata('user_id')) {
        } else {
            $result_data = array();
            $index = 1;
            foreach ($data as $row) {
                $row['index'] = $index;
                array_push($result_data, $row);
                $index++;
            }

            $result["recordsTotal"] = $total;
            $result["recordsFiltered"] = $totalAfterFilter;
            $result["data"] = $result_data;
        }

        print_r(json_encode($result));
    }

    public function delete_inspection()
    {
        $res = array('err_code' => 1);
        if ($this->session->userdata('user_id') && $this->session->userdata('permission') == 1) {
            $id = $this->input->get_post('id');

            if ($id !== false) {
                $inspection = $this->utility_model->get('ins_inspection', array('id' => $id));
                if ($inspection) {
                    if ($this->utility_model->delete('ins_inspection', array('id' => $id))) {
                        $this->utility_model->delete('ins_inspection_comment', array('inspection_id' => $id));
                        $this->utility_model->delete('ins_exception_image', array('inspection_id' => $id));
                        $this->utility_model->delete('ins_location', array('inspection_id' => $id));
                        $this->utility_model->delete('ins_checklist', array('inspection_id' => $id));
                        $this->utility_model->delete('ins_unit', array('inspection_id' => $id));
                        $this->utility_model->delete('ins_inspection_comment', array('inspection_id' => $id));

                        $this->utility_model->delete('ins_inspection_requested', array('id' => $inspection['requested_id']));

                        $res['err_code'] = 0;
                    }
                } else {
                }
            } else {
            }
        }

        print_r(json_encode($res));
    }

    public function requested_lists()
    {
        if (!$this->session->userdata('user_id')) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        //        if ($this->session->userdata('permission')!='1' && $this->session->userdata('permission')!='2') {
        //            redirect(base_url() . "user/login.html");
        //            exit(1);
        //        }

        $time = time();

        $page_data = array();

        $page_data['page_name'] = 'requested_inspection';
        $page_data['start_date'] = date('Y-m-d', $time - 7 * 24 * 60 * 60);
        $page_data['end_date'] = date('Y-m-d', $time + 30 * 24 * 60 * 60);

        $this->load->view('inspection_list_request', $page_data);
    }

    public function load_list_request()
    {
        $cols = array("a.requested_at", "a.community_name", "a.job_number", "a.address", "c.city", "m.first_name", "a.category", "a.time_stamp", "u.first_name",);
        $table = " ins_code c1, ins_inspection_requested a "
                . " left join ins_community c on c.community_id=substr(a.job_number,1,4)"
                . " left join ins_region r on c.region=r.id "
                . " left join ins_admin m on a.manager_id=m.id "
                . " left join ins_user u on a.inspector_id=u.id "
                . " where c1.kind='ins' and c1.code=a.category ";  // and ( a.status=0 or a.status=1 )

        if ($this->session->userdata('permission') == 2) {
            $table .= " and a.manager_id='" . $this->session->userdata('user_id') . "' ";
        } elseif ($this->session->userdata('permission') == 0) {
            $table .= " and a.inspector_id='" . $this->session->userdata('user_id') . "' ";
        } elseif ($this->session->userdata('permission') == 1) {
        } else {
            $table .= " and c.region in ( select region from ins_admin_region where manager_id='" . $this->session->userdata('user_id') . "' ) ";
        }

        $result = array();

        $amount = 10;
        $start = 0;
        $col = 0;

        $dir = "desc";

        $start_date = $this->input->get_post('start_date');
        $end_date = $this->input->get_post('end_date');
        $type = $this->input->get_post('type');
        $status = $this->input->get_post('status');

        $common_sql = "";

        if ($start_date !== false && $start_date != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.requested_at>='$start_date' ";
        }

        if ($end_date !== false && $end_date != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.requested_at<='$end_date' ";
        }

        if ($type !== false && $type != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.category='$type' ";
        }

        if ($status !== false && $status != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.status='$status' ";
        }

        if ($common_sql != "") {
            $table .= " and " . $common_sql;
        }

        $sStart = $this->input->get_post('start');
        $sAmount = $this->input->get_post('length');

        $order_sql = "";
        $order = $this->input->get_post("order");
        if ($order !== false && is_array($order)) {
            foreach ($order as $row) {
                $col = intval($row['column']);
                $dir = $row['dir'];

                if ($col < 0 || $col > 8) {
                    $col = 0;
                }

                if ($order_sql != "") {
                    $order_sql .= ", ";
                }
                $order_sql .= $cols[$col] . " " . $dir . " ";
            }

            if ($order_sql != "") {
                $order_sql = " order by " . $order_sql;
            }
        }

        $searchTerm = "";
        $search = $this->input->get_post("search");
        foreach ($search as $key => $value) {
            if ($key == 'value') {
                $searchTerm = $value;
            }
        }

        if ($sStart !== false && strlen($sStart) > 0) {
            $start = intval($sStart);
            if ($start < 0) {
                $start = 0;
            }
        }

        if ($sAmount !== false && strlen($sAmount) > 0) {
            $amount = intval($sAmount);
            if ($amount < 10 || $amount > 100) {
                $amount = 10;
            }
        }

        $total = 0;
        $totalAfterFilter = 0;

        $sql = " select count(*) from " . $table . "  ";

        $total = $this->datatable_model->get_count($sql);
        $totalAfterFilter = $total;

        $sql = " select  a.id, a.category, a.reinspection, a.epo_number, a.job_number, a.requested_at, a.assigned_at, a.completed_at, a.manager_id, a.inspector_id, "
                . " a.time_stamp, a.ip_address, a.community_name, a.lot, a.address, a.status, a.area, a.volume, a.qn, a.city as city_duct, "
                . " '' as additional, "
                . " m.first_name, m.last_name,"
                . " concat(u.first_name, ' ', u.last_name) as inspector_name, "
                . " c1.name as category_name, c.community_id, c.region, r.region as region_name, c.city "
                . " from " . $table . " ";

        $searchSQL = "";
        $globalSearch = " ( "
                . " replace(a.job_number,'-','') like '%" . str_replace('-', '', $searchTerm) . "%' or "
                . " u.first_name like '%" . $searchTerm . "%' or  "
                . " u.last_name like '%" . $searchTerm . "%' or  "
                . " m.first_name like '%" . $searchTerm . "%' or  "
                . " m.last_name like '%" . $searchTerm . "%' or  "
                . " a.requested_at like '%" . $searchTerm . "%' or  "
                . " a.community_name like '%" . $searchTerm . "%' or  "
                . " a.address like '%" . $searchTerm . "%' or  "
                . " r.region like '%" . $searchTerm . "%' or  "
                . " c1.name like '%" . $searchTerm . "%' "
                . " ) ";

        if ($searchTerm && strlen($searchTerm) > 0) {
            $searchSQL .= " and " . $globalSearch;
        }

        $sql .= $searchSQL;
        $sql .= $order_sql;

        $sql .= " limit " . $start . ", " . $amount . " ";
        // var_dump($start_date);
        // var_dump($end_date);
        // var_dump( $type);
        // var_dump( $status);
        // echo $sql;
        $data = $this->datatable_model->get_content($sql);

        $sql = " select count(*) from " . $table . " ";
        if (strlen($searchSQL) > 0) {
            $sql .= $searchSQL;
            $totalAfterFilter = $this->datatable_model->get_count($sql);
        }

        if (!$this->session->userdata('user_id')) {
        } else {
            $result["recordsTotal"] = $total;
            $result["recordsFiltered"] = $totalAfterFilter;
            $result["data"] = $data;
        }
        print_r(json_encode($result));
    }

    public function check_wci()
    {
    }

    public function edit_inspection_requested()
    {
        if (!$this->session->userdata('user_id')) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        if (!$this->utility_model->has_permission($this->session->userdata('permission'), 3)) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $date = "";
        if ($this->session->userdata('permission') == 1) {
            $date = date('Y-m-d', time());
        } else {
            $date = $this->get_valid_requested_date();
        }

        $id = $this->input->get_post('id');

        $page_data = array();

        $community = null;
        $region = $this->session->userdata('user_region');
        if ($region == '0') {
            $community = $this->utility_model->get_list('ins_community', array());
        } else {
            $community = $this->utility_model->get_list('ins_community', array('region' => $region));
        }
        $page_data['community'] = $community;

        $page_data['page_name'] = 'inspection_request';
        $page_data['category'] = $this->utility_model->get_list__by_sql(" select a.* from ins_code a where a.kind='ins' and a.code<>0 and a.value<>1 order by a.code ");

        if ($id === false || $id == "") {
            $page_data['page_title'] = "Add Inspection Request";
            $page_data['inspection'] = array('id' => '', 'requested_at' => $date, 'job_number' => '', 'category' => '', 'reinspection' => '0', 'epo_number' => '', 'community_name' => '', 'lot' => '', 'address' => '', 'is_building_unit' => 0);
        } else {
            $inspection = $this->utility_model->get('ins_inspection_requested', array('id' => $id));
            if ($inspection) {
                if ($inspection['category'] == 3) {
                } else {
                    $page_data['page_title'] = "Edit Inspection Request";

                    if ($this->session->userdata('permission') == 1) {
                    } else {
                        $inspection['requested_at'] = $date;
                    }

                    $page_data['inspection'] = $inspection;
                }
            } else {
                $page_data['page_title'] = "Add Inspection Request";
                $page_data['inspection'] = array('id' => '', 'requested_at' => $date, 'job_number' => '', 'category' => '', 'reinspection' => '0', 'epo_number' => '', 'community_name' => '', 'lot' => '', 'address' => '', 'is_building_unit' => 0);
            }
        }

        $page_data['region'] = $this->utility_model->get_list('ins_region', array());

        $this->load->view('inspection_request_edit', $page_data);
    }

    public function delete_requested_inspection()
    {
        $res = array('err_code' => 1, 'err_msg' => 'No Permission');

        if ($this->session->userdata('user_id')) {
            if ($this->utility_model->has_permission($this->session->userdata('permission'), 2)) {
                $id = $this->input->get_post('id');

                if ($id !== false) {
                    $requested = $this->utility_model->get('ins_inspection_requested', array('id' => $id));
                    if ($requested) {
                        if ($requested['status'] == 2) {
                            $res['err_msg'] = "This requested inspection cannot be deleted. Already completed inspection.";
                        } else {
                            if ($this->session->userdata('permission') == 1 || ($this->session->userdata('permission') == 2 && $requested['status'] == 0)) {
                                if ($this->utility_model->delete('ins_inspection_requested', array('id' => $id))) {
                                    $res['err_code'] = 0;
                                }
                            }
                        }
                    } else {
                        $res['err_msg'] = "Invalid Request";
                    }
                } else {
                    $res['err_msg'] = "Invalid Request";
                }
            }
        }

        print_r(json_encode($res));
    }

    private function get_valid_requested_date()
    {
        //        ini_set('date.timezone', 'America/New_York');
        date_default_timezone_set("America/New_York");
        $holidays = new Holiday();

        $business_day = 0;
        $tm = time();

        if (intval(date('H', $tm)) >= 16) {
            $business_day = 2;
        } else {
            $business_day = 1;
        }

        $tm = strtotime(date('Y-m-d', $tm) . " 00:00:00");
        if ($holidays->is_holiday($tm)) {
            $business_day = 2;
        }

        while ($business_day > 0) {
            $tm += 86400;

            while ($holidays->is_holiday($tm)) {
                $tm += 86400;
            }

            $business_day = $business_day - 1;
        }

        $date = date('Y-m-d', $tm);
        return $date;
    }

    public function update_inspection_requested()
    {
        $res = array('err_code' => 1);
        $res['err_msg'] = "Failed!";

        if ($this->session->userdata('user_id')) {
            $id = $this->input->get_post('id');

            $model_home = $this->input->get_post('model_home');
            $detail = $this->input->get_post('detail');
            $community_id = $this->input->get_post('community_id');

            $date_requested = $this->input->get_post('date_requested');
            $job_number = $this->input->get_post('job_number');
            $category = $this->input->get_post('category');
            $reinspection = $this->input->get_post('reinspection');
            $epo_number = $this->input->get_post('epo_number');
            $community_name = $this->input->get_post('community_name');
            $lot = $this->input->get_post('lot');
            $address = $this->input->get_post('address');

            $edit_inspection_id = $this->input->get_post('inspection_id');

            $kind = $this->input->get_post('kind');

            $unit_address = $this->input->get_post('unit_address');

            $region = $this->input->get_post('region');

            //$inspector_id = $this->input->get_post('inspector_id');

            $field_manager = $this->input->get_post('field_manager');
            if ($field_manager === false) {
                $field_manager = "";
            }

            if ($model_home == "1") {
                $lot = "000";
                $cmm = $this->utility_model->get('ins_community', array('community_id' => $community_id));
                if ($cmm) {
                    $community_name = $cmm['community_name'];
                }

                $address = $detail;
            }

            if ($epo_number == "") {
                $epo_number = "0";
            }

            $data['created_at'] = date('Y-m-d', time());
            $data['requested_at'] = $date_requested;
            $data['job_number'] = $job_number;
            $data['category'] = $category;
            $data['reinspection'] = $reinspection;
            $data['epo_number'] = $epo_number;
            //$data['inspector_id'] = $inspector_id;

            $data['community_name'] = $community_name;
            $data['lot'] = $lot;
            $data['address'] = $address;

            if ($edit_inspection_id !== false && $edit_inspection_id != "") {
                $data['inspection_id'] = $edit_inspection_id;
            }

            $is_valid_date = false;
            $ttt = time();

            if ($this->session->userdata('permission') == 1) {
                $is_valid_date = true;

                if ($field_manager != "") {
                    $data['manager_id'] = $field_manager;
                } else {
                    $data['manager_id'] = $this->session->userdata('user_id');
                }
            } else {
                //                ini_set('date.timezone', 'America/New_York');
                date_default_timezone_set("America/New_York");

                $data['manager_id'] = $this->session->userdata('user_id');

                $holidays = new Holiday();
                $business_day = 0;

                if (intval(date('H', $ttt)) >= 16) {
                    $business_day = 2;
                } else {
                    $business_day = 1;
                }

                $ttt = strtotime(date('Y-m-d', $ttt) . " 00:00:00");
                if ($holidays->is_holiday($ttt)) {
                    $business_day = 2;
                }

                //                $testing_value = date('Y-m-d', $ttt);
                //                $res['business_day'] = $business_day;

                while ($business_day > 0) {
                    $ttt += 86400;

                    while ($holidays->is_holiday($ttt)) {
                        $ttt += 86400;
                    }

                    $business_day = $business_day - 1;
                }

                $valid_date = $ttt;
                $input_date = strtotime($date_requested . " 00:00:00");

                //                $res['input_date'] = date('Y-m-d H:i:s', $input_date);
                //                $res['valid_date'] = date('Y-m-d H:i:s', $valid_date);

                if ($holidays->is_holiday($input_date)) {
                    $res['err_msg'] = "The Date Selected is a Holiday. Select a different Date.";
                } else {
                    if (strtotime($date_requested . " 23:59:59") >= $valid_date) {
                        $is_valid_date = true;
                    } else {
                        $res['err_msg'] = "The Selected Date is Invalid. Please select a Different Date.";
                    }
                }
            }

            if ($is_valid_date) {
                $t = mdate('%Y%m%d%H%i%s', $ttt);
                $data['time_stamp'] = mdate('%Y%m%d%H%i%s', time());
                $data['ip_address'] = $this->get_client_ip();

                $inspection_requsted_id = "";
                $is_already_exist = false;

                $sql = " select a.* from ins_inspection_requested a where a.category='$category' and a.job_number='$job_number' ";
                $sql .= " and (a.status=0 or a.status=1) ";
                if ($unit_address == "1") {
                    $sql .= " and a.address='$address' and a.is_building_unit=1 ";
                }
                if ($id !== false && $id != "") {
                    $sql .= " and a.id<>'$id' ";
                }
                $old_requested_inspection = $this->utility_model->get__by_sql($sql);

                if ($id !== false && $id != "") {
                    $rrr = $this->utility_model->get('ins_inspection_requested', array('id' => $id));
                    if ($rrr) {
                        if ($old_requested_inspection && $rrr['id'] != $old_requested_inspection['id']) {
                            $is_already_exist = true;
                        }
                    } else {
                        if ($old_requested_inspection) {
                            $is_already_exist = true;
                        }
                    }
                } else {
                    if ($old_requested_inspection) {
                        $is_already_exist = true;
                    }
                }

                if ($unit_address == "1") {
                    $data['is_building_unit'] = 1;
                }

                if (!$is_already_exist) {
                    if ($id !== false && $id != "") {
                        $rrr = $this->utility_model->get('ins_inspection_requested', array('id' => $id));
                        if ($rrr) {
                            if ($this->utility_model->update('ins_inspection_requested', $data, array('id' => $id))) {
                                $inspection_requsted_id = $id;
                            }
                        } else {
                            if ($this->utility_model->insert('ins_inspection_requested', $data)) {
                                $inspection_requsted_id = $this->utility_model->new_id();
                            }
                        }
                    } else {
                        //                            $building_job_number = substr($job_number, 0, 5) . str_replace("-", "", substr($job_number, 5));
                        //                            $unit_addresses = $this->utility_model->get_list('ins_building_unit', array('job_number'=>$building_job_number));
                        //                            if (is_array($unit_addresses) && count($unit_addresses)>0) {
                        //                                foreach ($unit_addresses as $row) {
                        //                                    $data['address'] = $row['address'];
                        //                                    $data['is_building_unit'] = 1;
                        //                                    if ($this->utility_model->insert('ins_inspection_requested', $data)) {
                        //                                        if ($inspection_requsted_id!="") {
                        //                                            $inspection_requsted_id .= ", ";
                        //                                        }
                        //                                        $inspection_requsted_id .= $this->utility_model->new_id();
                        //                                    }
                        //                                }
                        //                            } else {
                        //                                if ($this->utility_model->insert('ins_inspection_requested', $data)) {
                        //                                    $inspection_requsted_id = $this->utility_model->new_id();
                        //                                }
                        //                            }
                        //                        } else {
                        if ($this->utility_model->insert('ins_inspection_requested', $data)) {
                            $inspection_requsted_id = $this->utility_model->new_id();
                        }
                        //                        }
                    }

                    if ($inspection_requsted_id != "") {
                        if ($model_home == "1") {
                        } else {
                            //                            $manager = $this->utility_model->get('ins_admin', array('id'=>isset($data['manager_id']) ? $data['manager_id'] : $this->session->userdata('user_id')));
                            $manager = $this->utility_model->get('ins_admin', array('id' => $this->session->userdata('user_id')));
                            $today = mdate('%Y/%n/%j', strtotime($date_requested));
                            $r_today = mdate('%Y/%n/%j', time());
                            //                $today = mdate('%Y/%n/%j', $ttt);

                            $new_jn = substr($job_number, 0, 8) . substr($job_number, 9, 2);
                            $c = $this->utility_model->get_count('ins_building', array('job_number' => $new_jn));

                            if ($c == 0) {
                                $mail_subject = "";
                                if ($category == "1") {
                                    $mail_subject .= "Drainage Plane ";
                                }
                                if ($category == "2") {
                                    $mail_subject .= "Lath ";
                                }

                                $mail_subject .= "Inspection Request Submitted for " . $today . " with Job Number " . $job_number . " Not in Database";
                                $mail_body = "Inspection requesdt with ID(" . $inspection_requsted_id . ") was submitted by " . $manager['first_name'] . " " . $manager['last_name'] . " (" . $manager['email'] . ") on " . $r_today . ".\n";
                                $mail_body .= "The inspection was requested for this day: " . $today . "\n";
                                $mail_body .= "The Job number entered " . $job_number . " was not found in the Database.";

                                if ($community_name != "" || $lot != "" || $address != "") {
                                    $mail_body .= "These were the details entered: \n";
                                    if ($community_name != "") {
                                        $mail_body .= "     Community Name: " . $community_name . "\n";
                                    }

                                    if ($lot != "") {
                                        $mail_body .= "     LOT: " . $lot . "\n";
                                    }

                                    if ($address != "") {
                                        $mail_body .= "     Address: " . $address . "\n";
                                    }
                                }

                                $mail_body .= "\n"
                                        . " Best Regards," . "\n"
                                        . " The Inspections Team" . "\n";


                                $sender = array();
                                $emails = $this->utility_model->get_list('ins_admin', array('kind' => 1, 'allow_email' => 1));
                                if ($emails) {
                                    foreach ($emails as $row) {
                                        array_push($sender, $row);
                                    }
                                }

                                $emails = $this->utility_model->get_list('sys_recipient_email', array('status' => '1'));
                                if ($emails) {
                                    foreach ($emails as $row) {
                                        array_push($sender, $row);
                                    }
                                }

                                if (count($sender) > 0) {
                                    $this->send_mail($mail_subject, $mail_body, $sender, false);
                                }

                                unset($sender);
                                $sender = array();
                                if ($manager['kind'] == 2 && $manager['allow_email'] == 1) {
                                    array_push($sender, $manager);
                                }

                                $mail_subject = "";
                                if ($category == "1") {
                                    $mail_subject .= "Drainage Plane ";
                                }
                                if ($category == "2") {
                                    $mail_subject .= "Lath ";
                                }

                                $mail_subject .= "Inspection Request Successfully Submitted";
                                $mail_body = "Inspection request successfully submitted on " . $r_today . "\n";
                                $mail_body .= "The inspection was requested for this day: " . $today . "\n";

                                $mail_body .= "\n";
                                $mail_body .= "  Job number entered: " . $job_number . "\n";

                                if ($community_name != "" || $lot != "" || $address != "") {
                                    if ($community_name != "") {
                                        $mail_body .= "  Community Name: " . $community_name . "\n";
                                    }

                                    if ($lot != "") {
                                        $mail_body .= "  LOT: " . $lot . "\n";
                                    }

                                    if ($address != "") {
                                        $mail_body .= "  Address: " . $address . "\n";
                                    }
                                }

                                $mail_body .= "\n"
                                        . " Best Regards," . "\n"
                                        . " The Inspections Team" . "\n";

                                if (count($sender) > 0) {
                                    $this->send_mail($mail_subject, $mail_body, $sender, false);
                                }

                                if ($community_name != "" && $address != "") {
                                    $this->utility_model->insert('ins_building', array(
                                        'job_number' => $new_jn,
                                        'community' => $community_name,
                                        'address' => $address,
                                        'builder' => 1, // Pulte
                                        'created_at' => $t,
                                        'updated_at' => $t
                                    ));

                                    $ppp = $this->utility_model->get('ins_community', array('community_id' => substr($new_jn, 0, 4)));
                                    if ($ppp) {
                                        $this->utility_model->update('ins_community', array(
                                            'community_name' => $community_name,
                                            'region' => $region != "" ? $region : 0,
                                            'builder' => 1,
                                            'updated_at' => $t
                                                ), array('id' => $ppp['id']));
                                    } else {
                                        $this->utility_model->insert('ins_community', array(
                                            'community_id' => substr($new_jn, 0, 4),
                                            'community_name' => $community_name,
                                            'region' => $region != "" ? $region : 0,
                                            'builder' => 1,
                                            'created_at' => $t,
                                            'updated_at' => $t
                                        ));
                                    }
                                }
                            } else {
                                if ($kind == "edit") {
                                    $sender = array();
                                    $emails = $this->utility_model->get_list('ins_admin', array('kind' => 1, 'allow_email' => 1));
                                    if ($emails) {
                                        foreach ($emails as $row) {
                                            array_push($sender, $row);
                                        }
                                    }

                                    $emails = $this->utility_model->get_list('sys_recipient_email', array('status' => '1'));
                                    if ($emails) {
                                        foreach ($emails as $row) {
                                            array_push($sender, $row);
                                        }
                                    }

                                    $mail_subject = "";
                                    if ($category == "1") {
                                        $mail_subject .= "Drainage Plane ";
                                    }
                                    if ($category == "2") {
                                        $mail_subject .= "Lath ";
                                    }

                                    $mail_subject .= "Inspection Request Submitted with updated Job Number " . $job_number . " information";
                                    $mail_body = "Inspection request with ID(" . $inspection_requsted_id . ") was submitted by " . $manager['first_name'] . " " . $manager['last_name'] . " (" . $manager['email'] . ") on " . $r_today . ".\n";
                                    $mail_body .= "The inspection was requested for this day: " . $today . "\n";
                                    $mail_body .= "The Job Number entered: " . $job_number . "\n";

                                    if ($community_name != "" || $lot != "" || $address != "") {
                                        $mail_body .= "These were the UPDATED details entered: \n";
                                        if ($community_name != "") {
                                            $mail_body .= "     Community Name: " . $community_name . "\n";
                                        }

                                        if ($lot != "") {
                                            $mail_body .= "     LOT: " . $lot . "\n";
                                        }

                                        if ($address != "") {
                                            $mail_body .= "     Address: " . $address . "\n";
                                        }
                                    }

                                    $mail_body .= "\n"
                                            . " Best Regards," . "\n"
                                            . " The Inspections Team" . "\n";

                                    if (count($sender) > 0) {
                                        $this->send_mail($mail_subject, $mail_body, $sender, false);
                                    }


                                    unset($sender);
                                    $sender = array();
                                    if ($manager['kind'] == 2 && $manager['allow_email'] == 1) {
                                        array_push($sender, $manager);
                                    }

                                    $mail_subject = "";
                                    if ($category == "1") {
                                        $mail_subject .= "Drainage Plane ";
                                    }
                                    if ($category == "2") {
                                        $mail_subject .= "Lath ";
                                    }

                                    $mail_subject .= "Inspection Request Successfully submitted";
                                    $mail_body = "Inspection request successfully submitted on " . $r_today . "\n";
                                    $mail_body .= "The inspection was requested for this day: " . $today . "\n";

                                    $mail_body .= "\n";
                                    $mail_body .= "  Job number entered: " . $job_number . "\n";

                                    if ($community_name != "" || $lot != "" || $address != "") {
                                        if ($community_name != "") {
                                            $mail_body .= "  Community Name: " . $community_name . "\n";
                                        }

                                        if ($lot != "") {
                                            $mail_body .= "  LOT: " . $lot . "\n";
                                        }

                                        if ($address != "") {
                                            $mail_body .= "  Address: " . $address . "\n";
                                        }
                                    }

                                    $mail_body .= "\n"
                                            . " Best Regards," . "\n"
                                            . " The Inspections Team" . "\n";

                                    if (count($sender) > 0) {
                                        $this->send_mail($mail_subject, $mail_body, $sender, false);
                                    }
                                } else {
                                    $sender = array();
                                    $emails = $this->utility_model->get_list('ins_admin', array('kind' => 1, 'allow_email' => 1));
                                    if ($emails) {
                                        foreach ($emails as $row) {
                                            array_push($sender, $row);
                                        }
                                    }

                                    $emails = $this->utility_model->get_list('sys_recipient_email', array('status' => '1'));
                                    if ($emails) {
                                        foreach ($emails as $row) {
                                            array_push($sender, $row);
                                        }
                                    }

                                    $mail_subject = "";
                                    if ($category == "1") {
                                        $mail_subject .= "Drainage Plane ";
                                    }
                                    if ($category == "2") {
                                        $mail_subject .= "Lath ";
                                    }

                                    $mail_subject .= "Inspection Request Submitted for Job Number " . $job_number . "";
                                    $mail_body = "Inspection request with ID(" . $inspection_requsted_id . ") was submitted by " . $manager['first_name'] . " " . $manager['last_name'] . " (" . $manager['email'] . ") on " . $r_today . ".\n";
                                    $mail_body .= "The inspection was requested for this day: " . $today . "\n";
                                    $mail_body .= "The Job Number entered: " . $job_number . "\n";

                                    if ($community_name != "" || $lot != "" || $address != "") {
                                        $mail_body .= "These were the UPDATED details entered: \n";
                                        if ($community_name != "") {
                                            $mail_body .= "     Community Name: " . $community_name . "\n";
                                        }

                                        if ($lot != "") {
                                            $mail_body .= "     LOT: " . $lot . "\n";
                                        }

                                        if ($address != "") {
                                            $mail_body .= "     Address: " . $address . "\n";
                                        }
                                    }

                                    $mail_body .= "\n"
                                            . " Best Regards," . "\n"
                                            . " The Inspections Team" . "\n";

                                    if (count($sender) > 0) {
                                        $this->send_mail($mail_subject, $mail_body, $sender, false);
                                    }

                                    unset($sender);
                                    $sender = array();
                                    if ($manager['kind'] == 2 && $manager['allow_email'] == 1) {
                                        array_push($sender, $manager);
                                    }

                                    $mail_subject = "";
                                    if ($category == "1") {
                                        $mail_subject .= "Drainage Plane ";
                                    }
                                    if ($category == "2") {
                                        $mail_subject .= "Lath ";
                                    }

                                    $mail_subject .= "Inspection Request Successfully submitted";
                                    $mail_body = "Inspection request successfully submitted on " . $r_today . "\n";
                                    $mail_body .= "The inspection was requested for this day: " . $today . "\n";

                                    $mail_body .= "\n";
                                    $mail_body .= "  Job number entered: " . $job_number . "\n";

                                    if ($community_name != "" || $lot != "" || $address != "") {
                                        if ($community_name != "") {
                                            $mail_body .= "  Community Name: " . $community_name . "\n";
                                        }

                                        if ($lot != "") {
                                            $mail_body .= "  LOT: " . $lot . "\n";
                                        }

                                        if ($address != "") {
                                            $mail_body .= "  Address: " . $address . "\n";
                                        }
                                    }

                                    $mail_body .= "\n"
                                            . " Best Regards," . "\n"
                                            . " The Inspections Team" . "\n";

                                    if (count($sender) > 0) {
                                        $this->send_mail($mail_subject, $mail_body, $sender, false);
                                    }
                                }
                            }
                        }

                        $res['err_code'] = 0;
                        $res['err_msg'] = "Successfully Requested!";
                    } else {
                        $res['err_msg'] = "Failed to request!";
                    }
                } else {
                    $res['err_msg'] = "Already Existed in Requested Inspections!";
                }
            }
        }

        print_r(json_encode($res));
    }

    public function check_jobnumber()
    {
        $res = array('err_code' => -1);
        $res['err_msg'] = "Failed!";

        if ($this->session->userdata('user_id')) {
            $id = $this->input->get_post('id');
            $is_first = $this->input->get_post('is_first');
            $job_number = $this->input->get_post('job_number');
            $category = $this->input->get_post('category');
            $address = $this->input->get_post('address');
            if ($address === false) {
                $address = "";
            }

            if ($job_number !== false && $category !== false) {
                $is_building_unit = false;

                $new_jn = substr($job_number, 0, 8) . substr($job_number, 9, 2);
                $c = $this->utility_model->get_count('ins_building', array('job_number' => $new_jn));
                if ($c > 0) {
                    $building = $this->utility_model->get('ins_building', array('job_number' => $new_jn));
                    if (isset($building['unit_count']) && $building['unit_count'] > 0) {
                        $building['unit_address'] = $this->utility_model->get_list('ins_building_unit', array('job_number' => $new_jn));
                        if ($building['unit_address'] && is_array($building['unit_address']) && count($building['unit_address']) > 0) {
                            $is_building_unit = true;
                        } else {
                            $building['unit_count'] = 0;
                        }
                    }

                    if ($is_building_unit) {
                        if ($address == "") {
                            $address = $building['unit_address'][0]['address'];
                        } else {
                        }

                        $uuu = $this->utility_model->get_count('ins_building_unit', array('job_number' => $new_jn, 'address' => $address));
                        if ($uuu > 0) {
                            $building['address'] = $address;
                        } else {
                            $is_building_unit = false;
                        }

                        if ($id != "" && $is_first == "1") {
                            $requested_inspection = $this->utility_model->get('ins_inspection_requested', array('id' => $id));
                            if ($requested_inspection && $requested_inspection['is_building_unit'] == 1) {
                                $building['address'] = $requested_inspection['address'];
                            }
                        }
                    }

                    $community = $this->utility_model->get('ins_community', array('community_id' => substr($job_number, 0, 4)));
                    if ($community) {
                        $building['community'] = $community['community_name'];
                        $building['from_community'] = 1;
                    } else {
                        $building['from_community'] = 0;
                    }

                    $res['building'] = $building;
                }

                $res['building_unit'] = 0;
                if ($is_building_unit) {
                    $res['building_unit'] = 1;
                }

                $sql = " select a.* from ins_inspection a where a.job_number='$job_number' and ( a.result_code=1 or a.result_code=2 ) and a.type='$category' ";
                if ($is_building_unit) {
                    $sql .= " and a.address='$address' and a.is_building_unit=1 ";
                }
                $t = $this->utility_model->get_count__by_sql($sql);
                if ($t > 0) {
                    $res['pass'] = $t;
                }

                $sql = " select a.* from ins_inspection a where a.job_number='$job_number' and ( a.result_code=3 ) and a.type='$category' ";
                if ($is_building_unit) {
                    $sql .= " and a.address='$address' and a.is_building_unit=1 ";
                }
                $t = $this->utility_model->get_count__by_sql($sql);
                if ($t > 0) {
                    $res['fail'] = $t;
                }

                $fm_result = array('has' => 0);

                $community = $this->utility_model->get('ins_community', array('community_id' => substr($job_number, 0, 4)));
                if ($community) {
                    $res['community'] = $community;

                    if ($this->session->userdata('permission') == 1) {
                        $sql = " select a.* from ins_admin a where a.kind=2 and a.id in ( select manager_id from ins_admin_region where region='" . $community['region'] . "' ) ";
                        $fms = $this->utility_model->get_list__by_sql($sql);
                        if ($fms) {
                            $fm_result['list'] = $fms;
                        } else {
                            $fm_result['list'] = array();
                        }

                        $fm_result['manager_id'] = 0;
                        $fm_result['has'] = 1;

                        if ($id != "") {
                            $requested_inspection = $this->utility_model->get('ins_inspection_requested', array('id' => $id));
                            if ($requested_inspection) {
                                $fm_result['manager_id'] = $requested_inspection['manager_id'];
                            }
                        }
                    }
                }

                $res['fm'] = $fm_result;
                $res['err_code'] = 0;
            }
        }

        print_r(json_encode($res));
    }

    public function check_inspection_requested()
    {
        $res = array('err_code' => -1, 'inspection_id' => "");
        $res['err_msg'] = "Failed!";

        if ($this->session->userdata('user_id')) {
            $job_number = $this->input->get_post('job_number');
            $type = $this->input->get_post('type');
            $address = $this->input->get_post('address');
            if ($address === false) {
                $address = "";
            }

            if ($job_number !== false) {
                $ret = false;
                if ($this->session->userdata('permission') == 2) {
                    $sql = " select a.* from ins_inspection a where a.job_number='$job_number' and ( a.result_code=1 or a.result_code=2 ) ";
                    if ($address != "") {
                        $sql .= " and a.address='$address' and a.is_building_unit=1 ";
                    }

                    $t1 = $this->utility_model->get_count__by_sql($sql . " and a.type=1 ");
                    $t2 = $this->utility_model->get_count__by_sql($sql . " and a.type=2 ");
                    if ($t1 > 0 && $t2 > 0) {
                        $res['err_code'] = 1;
                        $ret = true;
                    }
                }

                if ($ret === false) {
                    if ($type == "2") {
                        $sql = " select a.* from ins_inspection a where a.job_number='$job_number' and ( a.result_code=1 or a.result_code=2 ) and a.type=1 ";
                        if ($address != "") {
                            $sql .= " and a.address='$address' and a.is_building_unit=1  ";
                        }
                        $c = $this->utility_model->get_count__by_sql($sql);
                        if ($c == 0) {
                            $res['err_code'] = 0;
                        }
                    } else {
                    }
                }

                if ($this->session->userdata('permission') == 1) {
                    $sql = " select a.* from ins_inspection a where a.job_number='$job_number' and a.type='$type' ";
                    if ($address != "") {
                        $sql .= " and a.address='$address' and a.is_building_unit=1 ";
                    }

                    $sql .= " order by a.start_date desc, a.id desc "
                            . " limit 1 ";

                    $inspection = $this->utility_model->get__by_sql($sql);
                    if ($inspection) {
                        $res['inspection_id'] = $inspection['id'];
                        $res['inspection'] = $inspection;
                    }
                }
            }
        }

        print_r(json_encode($res));
    }

    public function check_requested_date()
    {
        $res = array('err_code' => 1);
        $res['err_msg'] = "Failed!";

        if ($this->session->userdata('user_id')) {
            $date = $this->input->get_post('date');
            if ($date !== false) {
                $date = strtotime($date);


                $new_jn = substr($job_number, 0, 8) . substr($job_number, 9, 2);
                $c = $this->utility_model->get_count('ins_building', array('job_number' => $new_jn));
                if ($c > 0) {
                    $building = $this->utility_model->get('ins_building', array('job_number' => $new_jn));
                    $res['building'] = $building;
                }

                $res['err_code'] = 0;
                $res['err_msg'] = "Success!";
            }
        }

        print_r(json_encode($res));
    }

    public function detail()
    {
        if (!$this->session->userdata('user_id')) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $inspection_id = $this->input->get_post('inspection_id');
        if ($inspection_id === false) {
            redirect(base_url() . "inspection/lists.html");
            exit(1);
        }

        $page_view = "inspection_detail";
        $page_data['page_name'] = 'inspection';

        $sql = " select a.*, u.email, c2.name as result_name "
                . " , c3.name as result_duct_leakage_name, c4.name as result_envelop_leakage_name "
                . " from ins_code c2, ins_code c3, ins_code c4, ins_inspection a "
                . " left join ins_user u on a.user_id=u.id "
                . " where a.id='" . $inspection_id . "' and c2.kind='rst' and c2.code=a.result_code and c3.kind='rst_duct' and c3.code=a.result_duct_leakage and c4.kind='rst_envelop' and c4.code=a.result_envelop_leakage ";

        $inspection = $this->utility_model->get__by_sql($sql);
        $page_data['inspection'] = $inspection;

        $inspection_type = intval($inspection['type']);
        if ($inspection_type == 3) {
            $unit = $this->utility_model->get_list__by_order('ins_unit', array('inspection_id' => $inspection_id), array(array('name' => 'no', 'order' => 'asc')));
            if ($unit) {
                $page_data['unit'] = $unit;
            }

            $page_view = 'inspection_detail_wci';
        } else {
            $fm = $this->utility_model->get('ins_admin', array('id' => $inspection['field_manager']));
            if ($fm) {
                $page_data['field_manager'] = $fm;
            }

            $region = $this->utility_model->get('ins_region', array('id' => $inspection['region']));
            if ($region) {
                $page_data['region'] = $region['region'];
            }

            $page_data['emails'] = $this->utility_model->get_list('ins_recipient_email', array('inspection_id' => $inspection_id));
            $page_data['images'] = $this->utility_model->get_list('ins_exception_image', array('inspection_id' => $inspection_id));

            $inspection_comment_list_code = "";
            if ($inspection_type == 1) {
                $inspection_comment_list_code = "drg_comment";
            } else {
                $inspection_comment_list_code = "lth_comment";
            }
            $page_data['comments'] = $this->utility_model->get_list__by_sql(" select a.*, c.name as comment_name from ins_inspection_comment a left join ins_code c on c.kind='$inspection_comment_list_code' and c.code=a.no where a.inspection_id='$inspection_id' order by a.no asc ");

            $locations = array();
            $location = $this->utility_model->get_list('ins_location', array('inspection_id' => $inspection_id));
            foreach ($location as $row) {
                $k = $page_data['inspection']['type'] == 1 ? 'drg' : 'lth';
                $checklist = $this->utility_model->get_list__by_sql("SELECT a.*, c.name as status_name, b.name FROM ins_code c, ins_checklist a JOIN ins_code b ON a.no=b.code WHERE a.status=c.code and c.kind='sts' and b.kind='$k' and a.inspection_id='" . $inspection_id . "' and a.location_id='" . $row['id'] . "'  ORDER BY a.no ");
                $row['checklist'] = $checklist;
                array_push($locations, $row);
            }

            $page_data['locations'] = $locations;
        }

        $this->load->view($page_view, $page_data);
    }

    public function edit()
    {
        if (!$this->session->userdata('user_id')) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $inspection_id = $this->input->get_post('inspection_id');
        if ($inspection_id === false) {
            redirect(base_url() . "inspection/water_intrusion.html");
            exit(1);
        }

        if ($this->session->userdata('permission') != '1' && $this->session->userdata('permission') != '2') {
            redirect(base_url() . "inspection/water_intrusion.html");
            exit(1);
        }


        $page_view = "inspection_edit";
        $page_data['page_name'] = 'inspection';

        $sql = " select a.*, u.email, c2.name as result_name "
                . " , c3.name as result_duct_leakage_name, c4.name as result_envelop_leakage_name "
                . " from ins_code c2, ins_code c3, ins_code c4, ins_inspection a "
                . " left join ins_user u on a.user_id=u.id "
                . " where a.id='" . $inspection_id . "' and c2.kind='rst' and c2.code=a.result_code and c3.kind='rst_duct' and c3.code=a.result_duct_leakage and c4.kind='rst_envelop' and c4.code=a.result_envelop_leakage ";

        $inspection = $this->utility_model->get__by_sql($sql);
        $inspection_type = intval($inspection['type']);

        $page_data['inspection'] = $inspection;
        if ($inspection_type == 3) {
            $unit = array();

            array_push($unit, $this->get_unit($inspection_id, 1));
            array_push($unit, $this->get_unit($inspection_id, 2));
            array_push($unit, $this->get_unit($inspection_id, 3));
            array_push($unit, $this->get_unit($inspection_id, 4));

            $page_data['unit'] = $unit;

            $page_view = 'inspection_edit_wci';
        } else {
            if ($inspection) {
                $fm = $this->utility_model->get('ins_admin', array('id' => $inspection['field_manager']));
                if ($fm) {
                    $page_data['field_manager'] = $fm;
                }
            }

            $region = $this->utility_model->get('ins_region', array('id' => $inspection['region']));
            if ($region) {
                $page_data['region'] = $region['region'];
            }

            $page_data['emails'] = $this->utility_model->get_list('ins_recipient_email', array('inspection_id' => $inspection_id));
            $page_data['images'] = $this->utility_model->get_list('ins_exception_image', array('inspection_id' => $inspection_id));

            $inspection_comment_list_code = "";
            if ($inspection_type == 1) {
                $inspection_comment_list_code = "drg_comment";
            } else {
                $inspection_comment_list_code = "lth_comment";
            }
            $page_data['comments'] = $this->utility_model->get_list__by_sql(" select a.*, c.name as comment_name from ins_inspection_comment a left join ins_code c on c.kind='$inspection_comment_list_code' and c.code=a.no where a.inspection_id='$inspection_id' order by a.no asc ");

            $locations = array();
            $location = $this->utility_model->get_list('ins_location', array('inspection_id' => $inspection_id));
            foreach ($location as $row) {
                $k = $page_data['inspection']['type'] == 1 ? 'drg' : 'lth';
                $checklist = $this->utility_model->get_list__by_sql("SELECT a.*, c.name as status_name, b.name FROM ins_code c, ins_checklist a JOIN ins_code b ON a.no=b.code WHERE a.status=c.code and c.kind='sts' and b.kind='$k' and a.inspection_id='" . $inspection_id . "' and a.location_id='" . $row['id'] . "'  ORDER BY a.no ");
                $row['checklist'] = $checklist;
                array_push($locations, $row);
            }

            $page_data['locations'] = $locations;

            $page_data['fm'] = $this->utility_model->get_list__by_sql(" select a.* from ins_admin a where a.kind=2 and a.id in (select manager_id from ins_admin_region where region='" . $inspection['region'] . "') ");
        }

        $page_data['inspection_id'] = $inspection_id;

        $this->load->view($page_view, $page_data);
    }

    private function get_unit($inspection_id, $no)
    {
        $unit = $this->utility_model->get('ins_unit', array('inspection_id' => $inspection_id, 'no' => $no));
        if ($unit) {
            return $unit;
        } else {
            return array('no' => $no, 'supply' => '', 'return' => '');
        }
    }

    public function update()
    {
        $res = array('err_code' => 1);
        if ($this->session->userdata('user_id')) {
            if ($this->session->userdata('permission') == 1 || $this->session->userdata('permission') == 2) {
                $inspection_id = $this->input->get_post('inspection_id');
                $field_manager = $this->input->get_post('field_manager');

                //            $community = $this->input->get_post('community');
                $address = $this->input->get_post('address');
                $start_date = $this->input->get_post('start_date');
                $end_date = $this->input->get_post('end_date');
                $initials = $this->input->get_post('initials');
                //            $field_manager = $this->input->get_post('field_manager');

                $front_picture = $this->input->get_post('front_picture');
                $comment = $this->input->get_post('comment');
                $result_code = $this->input->get_post('result_code');

                $job_number = $this->input->get_post('job_number');

                if ($inspection_id !== false) {
                    $t = mdate('%Y%m%d%H%i%s', time());

                    if ($this->session->userdata('permission') == 2) {
                        $this->utility_model->delete('ins_exception_image', array('inspection_id' => $inspection_id));
                        if ($result_code == '2') {
                            $exceptions = $this->input->get_post('exception');
                            if ($exceptions !== false && is_array($exceptions)) {
                                foreach ($exceptions as $row) {
                                    $this->utility_model->insert('ins_exception_image', array('inspection_id' => $inspection_id, 'image' => $row));
                                }
                            }
                        }

                        $res['err_code'] = 0;
                    } else {
                        if ($address === false) {
                            $address = "";
                        }
                        if ($initials === false) {
                            $initials = "";
                        }
                        if ($field_manager === false) {
                            $field_manager = "";
                        }
                        if ($front_picture === false) {
                            $front_picture = "";
                        }

                        $data['community'] = substr($job_number, 0, 4);
                        $data['lot'] = substr($job_number, 5, 3);

                        $data['address'] = $address;
                        $data['start_date'] = $start_date;
                        $data['end_date'] = $end_date;
                        $data['initials'] = $initials;
                        //                $data['field_manager'] = $field_manager;
                        $data['image_front_building'] = $front_picture;
                        $data['overall_comments'] = $comment;
                        $data['result_code'] = $result_code;

                        $data['job_number'] = $job_number;

                        $data['created_at'] = $t;

                        if ($field_manager !== false && $field_manager != "" && is_numeric($field_manager)) {
                            $data['field_manager'] = $field_manager;
                        }

                        if ($this->utility_model->update('ins_inspection', $data, array('id' => $inspection_id))) {
                            $this->utility_model->delete('ins_exception_image', array('inspection_id' => $inspection_id));
                            if ($result_code == '2') {
                                $exceptions = $this->input->get_post('exception');
                                if ($exceptions !== false && is_array($exceptions)) {
                                    foreach ($exceptions as $row) {
                                        $this->utility_model->insert('ins_exception_image', array('inspection_id' => $inspection_id, 'image' => $row));
                                    }
                                }
                            }

                            $this->utility_model->delete('ins_recipient_email', array('inspection_id' => $inspection_id));
                            $emails = $this->input->get_post('email');
                            if ($emails !== false && is_array($emails)) {
                                foreach ($emails as $row) {
                                    $this->utility_model->insert('ins_recipient_email', array('inspection_id' => $inspection_id, 'email' => $row));
                                }
                            }

                            $inspection = $this->utility_model->get("ins_inspection", array('id' => $inspection_id));
                            if ($inspection && isset($inspection['requested_id']) && $inspection['requested_id'] != "") {
                                $requested = $this->utility_model->get("ins_inspection_requested", array('id' => $inspection['requested_id']));
                                if ($requested) {
                                    $this->utility_model->update("ins_inspection_requested", array('job_number' => $job_number, 'lot' => $data['lot']), array('id' => $inspection['requested_id']));
                                }
                            }

                            $res['err_code'] = 0;
                        }
                    }
                } else {
                }
            }
        }

        print_r(json_encode($res));
    }

    public function update_wci()
    {
        $res = array('err_code' => 1);
        if ($this->session->userdata('user_id') && $this->session->userdata('permission') == 1) {
            $inspection_id = $this->input->get_post('inspection_id');
            //            $community = $this->input->get_post('community');
            $address = $this->input->get_post('address');
            $house_ready = $this->input->get_post('house_ready');

            $front_picture = $this->input->get_post('front_picture');
            $testing_setup = $this->input->get_post('testing_setup');
            $manometer = $this->input->get_post('manometer');

            $comment = $this->input->get_post('comment');

            $unit1_supply = $this->input->get_post('unit1_supply');
            $unit1_return = $this->input->get_post('unit1_return');
            $unit2_supply = $this->input->get_post('unit2_supply');
            $unit2_return = $this->input->get_post('unit2_return');
            $unit3_supply = $this->input->get_post('unit3_supply');
            $unit3_return = $this->input->get_post('unit3_return');
            $unit4_supply = $this->input->get_post('unit4_supply');
            $unit4_return = $this->input->get_post('unit4_return');

            $house_pressure = $this->input->get_post('house_pressure');
            $flow = $this->input->get_post('flow');

            $qn_out = $this->input->get_post('qn_out');
            $ach50 = $this->input->get_post('ach_50');

            $result_duct_leakage = $this->input->get_post('result_duct_leakage');
            $result_envelop_leakage = $this->input->get_post('result_envelop_leakage');

            if ($inspection_id !== false) {
                $t = mdate('%Y%m%d%H%i%s', time());

                $data['address'] = $address;
                $data['house_ready'] = $house_ready;

                $data['image_front_building'] = $front_picture;
                $data['image_testing_setup'] = $testing_setup;
                $data['image_manometer'] = $manometer;

                $data['overall_comments'] = $comment;

                $data['house_pressure'] = $house_pressure;
                $data['flow'] = $flow;

                $data['qn_out'] = $qn_out;
                $data['ach50'] = $ach50;

                $data['result_duct_leakage'] = $result_duct_leakage;
                $data['result_envelop_leakage'] = $result_envelop_leakage;

                $data['created_at'] = $t;

                if ($this->utility_model->update('ins_inspection', $data, array('id' => $inspection_id))) {
                    $this->utility_model->delete('ins_unit', array('inspection_id' => $inspection_id));

                    if ($unit1_supply != "" && $unit1_return != "") {
                        $this->utility_model->insert('ins_unit', array('inspection_id' => $inspection_id, 'no' => 1, 'supply' => $unit1_supply, 'return' => $unit1_return));
                    }
                    if ($unit2_supply != "" && $unit2_return != "") {
                        $this->utility_model->insert('ins_unit', array('inspection_id' => $inspection_id, 'no' => 2, 'supply' => $unit2_supply, 'return' => $unit2_return));
                    }
                    if ($unit3_supply != "" && $unit3_return != "") {
                        $this->utility_model->insert('ins_unit', array('inspection_id' => $inspection_id, 'no' => 3, 'supply' => $unit3_supply, 'return' => $unit3_return));
                    }
                    if ($unit4_supply != "" && $unit4_return != "") {
                        $this->utility_model->insert('ins_unit', array('inspection_id' => $inspection_id, 'no' => 4, 'supply' => $unit4_supply, 'return' => $unit4_return));
                    }

                    $res['err_code'] = 0;
                }
            } else {
            }
        }

        print_r(json_encode($res));
    }

    public function load_inspector()
    {
        $res = array('err_code' => 1);
        $res['err_msg'] = "Failed!";

        if ($this->session->userdata('user_id')) {
            $res['inspector'] = $this->utility_model->get_list('ins_user', array());
            $res['err_code'] = 0;
            $res['err_msg'] = "Success!";
        }

        print_r(json_encode($res));
    }

    public function get_client_ip()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }
        return $ipaddress;
    }

    public function pending_building()
    {
        if (!$this->session->userdata('user_id')) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $page_data['page_name'] = 'inspection_pending_building';
        $this->load->view('inspection_pending_building', $page_data);
    }

    public function load_pending_building()
    {
        $cols = array("a.job_number", "a.community", "a.address", "", "", "u.first_name", "a.created_at");
        $table = " ins_building a "
                . " left join ins_building_unit m on m.job_number=a.job_number "
                . " left join ins_admin u on u.kind=2 and concat(u.first_name, ' ', u.last_name)=a.field_manager ";

        $result = array();

        $amount = 10;
        $start = 0;
        $col = 0;

        $dir = "asc";

        $sStart = $this->input->get_post('start');
        $sAmount = $this->input->get_post('length');
        //	$sCol = $this->input->get_post('iSortCol_0');
        //      $sdir = $this->input->get_post('sSortDir_0');
        $sCol = "";
        $sdir = "";

        $sCol = $this->input->get_post("order");
        if ($sCol !== false && is_array($sCol)) {
            foreach ($sCol as $row) {
                foreach ($row as $key => $value) {
                    if ($key == 'column') {
                        $sCol = $value;
                    }
                    if ($key == 'dir') {
                        $sdir = $value;
                    }
                }
            }
        }

        $searchTerm = "";
        $search = $this->input->get_post("search");
        if ($search !== false && is_array($search)) {
            foreach ($search as $key => $value) {
                if ($key == 'value') {
                    $searchTerm = $value;
                }
            }
        }

        if ($sStart !== false && strlen($sStart) > 0) {
            $start = intval($sStart);
            if ($start < 0) {
                $start = 0;
            }
        }

        if ($sAmount !== false && strlen($sAmount) > 0) {
            $amount = intval($sAmount);
            if ($amount < 10 || $amount > 100) {
                $amount = 10;
            }
        }

        if ($sCol !== false && strlen($sCol) > 0) {
            $col = intval($sCol);
            if ($col < 0 || $col > 6) {
                $col = 0;
            }
        }

        if ($sdir && strlen($sdir) > 0) {
            if ($sdir != "asc") {
                $dir = "desc";
            }
        }

        $colName = $cols[$col];
        $total = 0;
        $totalAfterFilter = 0;

        $sql = " select count(*) from " . $table;
        $total = $this->datatable_model->get_count($sql);
        $totalAfterFilter = $total;

        $sql = " select  a.*, u.first_name, u.last_name, m.address as unit_address, '' as additional from " . $table . "  ";
        $searchSQL = "";
        $globalSearch = " ( "
                . " replace(a.job_number,'-','') like '%" . str_replace('-', '', $searchTerm) . "%' or "
                . " a.community like '%" . $searchTerm . "%' or "
                . " a.address like '%" . $searchTerm . "%' or  "
//                . " a.plan like '%" . $searchTerm . "%' or "
                . " u.first_name like '%" . $searchTerm . "%' or "
                . " u.last_name like '%" . $searchTerm . "%' "
//                . " a.neighborhood like '%" . $searchTerm . "%'  "
                . " ) ";

        if ($searchTerm && strlen($searchTerm) > 0) {
            $searchSQL .= " where " . $globalSearch;
        }

        $sql .= $searchSQL;
        $sql .= " order by " . $colName . " " . $dir . " ";
        $sql .= " limit " . $start . ", " . $amount . " ";
        $data = $this->datatable_model->get_content($sql);

        $sql = " select count(*) from " . $table;
        if (strlen($searchSQL) > 0) {
            $sql .= $searchSQL;
            $totalAfterFilter = $this->datatable_model->get_count($sql);
        }

        if (!$this->session->userdata('user_id')) {
        } else {
            $new_data = array();
            foreach ($data as $row) {
                $job_number = $row['job_number'];
                $job_number = str_replace("-", "", $job_number);

                $dp_pass = $this->utility_model->get_count__by_sql(" select a.* from ins_inspection a where a.type=1 and replace(a.job_number,'-','')='$job_number' and ( a.result_code=1 or a.result_code=2 ) ");
                $row['dp_status'] = $dp_pass > 0 ? "1" : "0";

                $lath_pass = $this->utility_model->get_count__by_sql(" select a.* from ins_inspection a where a.type=2 and replace(a.job_number,'-','')='$job_number' and ( a.result_code=1 or a.result_code=2 ) ");
                $row['lath_status'] = $lath_pass > 0 ? "1" : "0";

                $first = $row['created_at'];
                if (isset($first) && $first != "") {
                } else {
                    $first = $row['updated_at'];
                }

                $last = mdate('%Y%m%d%H%i%s', time());

                $first = substr($first, 0, 8);
                $last = substr($last, 0, 8);

                $date_diff = strtotime($last) - strtotime($first);

                $row['days'] = floor($date_diff / (60 * 60 * 24));

                array_push($new_data, $row);
            }

            $result["recordsTotal"] = $total;
            $result["recordsFiltered"] = $totalAfterFilter;
            $result["data"] = $new_data;
        }

        print_r(json_encode($result));
    }

    public function duct_leakage_inspection()
    {
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission') != '1') {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $date = date('Y-m-d', time());
        $id = $this->input->get_post('id');

        $page_data = array();
        $page_data['page_name'] = 'duct_leakage_inspection';
        $page_data['page_title'] = "WCI Duct Leakage Inspection";

        $field_managers = $this->utility_model->get_list('ins_admin', array('builder' => '2'));
        if (is_array($field_managers)) {
            $emails = array();
            foreach ($field_managers as $key => $value) {
                //$emails[] = $value['first_name']." ".$value['last_name'];
                $value['field_manager_name'] = $value['first_name'] . " " . $value['last_name'];
                $field_managers[$key] = $value;
            }
            //$emails = array_unique($emails);
            $page_data['field_managers'] = $field_managers;
        } else {
            $page_data['field_managers'] = array();
        }

        if ($id === false || $id == "") {
            $page_data['inspection'] = array('id' => '', 'requested_at' => $date, 'job_number' => '', 'lot' => '', 'community_name' => '',
            'address' => '', 'city' => '', 'area' => '', 'volume' => '', 'qn' => '', 'wall_area' => '', 'ceiling_area' => '', 'design_location' => '',
             'manager_id' => '', 'manager_email' => '', 'document_person' => '');
        } else {
            $inspection = $this->utility_model->get('ins_inspection_requested', array('id' => $id));
            if ($inspection) {
                $fm = $this->utility_model->get('ins_admin', array('id' => $inspection['manager_id'], 'kind' => 2));
                if ($fm) {
                    $inspection['manager_email'] = $fm['email'];
                } else {
                    $inspection['manager_id'] = "";
                    $inspection['manager_email'] = "";
                }

                $page_data['inspection'] = $inspection;
            } else {
                $page_data['inspection'] = array('id' => '', 'requested_at' => $date, 'job_number' => '', 'lot' => '', 'community_name' => '',
                 'address' => '', 'city' => '', 'area' => '', 'volume' => '', 'qn' => '', 'wall_area' => '', 'ceiling_area' => '', 'design_location' => '',
                  'manager_id' => '', 'manager_email' => '', 'document_person' => '');
            }
        }

        $this->load->view('duct_leakage_inspection', $page_data);
    }

    public function update_duct_leakage_inspection_requested()
    {
        $res = array('err_code' => 1);
        $res['err_msg'] = "Failed to request!";

        if ($this->session->userdata('user_id') && $this->session->userdata('permission') == 1) {
            $id = $this->input->get_post('id');
            $manager_id = $this->input->get_post('manager_id');

            $date_requested = $this->input->get_post('date_requested');
            $job_number = $this->input->get_post('job_number');
            $lot = $this->input->get_post('lot');

            $community = $this->input->get_post('community');
            $address = $this->input->get_post('address');
            $city = $this->input->get_post('city');
            $area = $this->input->get_post('area');
            $volume = $this->input->get_post('volume');
            $wall_area = $this->input->get_post('wall_area');
            $ceiling_area = $this->input->get_post('ceiling_area');
            $design_location = $this->input->get_post('design_location');

            $field_manager_name = $this->input->get_post('field_manager');
            $first_name = "";
            $last_name = "";
            $pieces = explode(" ", $field_manager_name);
            if (is_array($pieces)) {
                if (count($pieces) >= 2) {
                    $first_name = $pieces[0];
                    $last_name = $pieces[1];
                } elseif (count($pieces) == 1) {
                    $first_name = $pieces[0];
                }
            }
            $field_manager = "";
            $qn = $this->input->get_post('qn');

            $document_person = $this->input->get_post('document_person');
            if ($document_person === false) {
                $document_person = "";
            }

            $need_check_fm = true;
            if ($manager_id != "") {
                $fm = $this->utility_model->get('ins_admin', array('id' => $manager_id, 'kind' => 2));
                if ($fm && isset($fm['email']) && $fm['email'] == $field_manager) {
                    $need_check_fm = false;
                } else {
                    $manager_id = "";
                }
            }
            $user = null;
            if (strlen($field_manager) == 0) {
                $user = null;
            } else {
                $user = $this->utility_model->get('ins_admin', array('email' => $field_manager));
            }

            if ($need_check_fm && $user) {
                $res['err_msg'] = "Already Exist Email Address!";
            } else {
                $is_already_exist = false;
               $rrr = $this->utility_model->get('ins_inspection_requested', array('category' => 3, 'job_number' => $job_number, 'status' => 0));
               if ($rrr) {
                   $is_already_exist = true;
               }

                if (!$is_already_exist) {
                    $t = mdate('%Y%m%d%H%i%s', time());

                    $field_manager_id = "";
                    //$field_manager_name = "";
                    $this->utility_model->start();

                    $fm = array('kind' => 2, 'email' => $field_manager, 'address' => $address, 'password' => 'wci', 'builder' => 2, 'updated_at' => $t);
                    if ($manager_id == "") {
                        $fm['created_at'] = $t;
                        $fm['first_name'] = $first_name;
                        $fm['last_name'] = $last_name;

                        if ($this->utility_model->insert('ins_admin', $fm)) {
                            $field_manager_id = $this->utility_model->new_id();
                            $this->utility_model->update('ins_admin', array('first_name' => $first_name), array('last_name' => $last_name), array('id' => $field_manager_id));

                            //$field_manager_name = "" . $field_manager_id . " WCI";
                        }
                    } else {
                        if ($this->utility_model->update('ins_admin', $fm, array('id' => $manager_id))) {
                            $field_manager_id = $manager_id;
                            //$field_manager_name = "" . $field_manager_id . " WCI";
                        }
                    }

                    if ($field_manager_id != "" && $field_manager_name != "") {
                        $building = $this->utility_model->get('ins_building', array('job_number' => $job_number));
                        if ($building) {
                            $this->utility_model->update('ins_building', array('community' => $community, 'address' => $address, 'field_manager' => $field_manager_name, 'builder' => 2, 'updated_at' => $t), array('job_number' => $job_number));
                        } else {
                            $this->utility_model->insert('ins_building', array('job_number' => $job_number, 'community' => $community, 'address' => $address, 'field_manager' => $field_manager_name, 'builder' => 2, 'created_at' => $t, 'updated_at' => $t));
                        }

                        $data = array(
                            'category' => 3,
                            'manager_id' => $field_manager_id, //$this->session->userdata('user_id'),
                            'job_number' => $job_number,
                            'lot' => $lot,
                            'requested_at' => $date_requested,
                            'time_stamp' => $t,
                            'ip_address' => $this->get_client_ip(),
                            'community_name' => $community,
                            'address' => $address,
                            'city' => $city,
                            'area' => $area,
                            'volume' => $volume,
                            'qn' => $qn,
                            'wall_area' => $wall_area,
                            'ceiling_area' => $ceiling_area,
                            'design_location' => $design_location,
                            'document_person' => $document_person
                        );

                        if ($id == "") {
                            if ($this->utility_model->insert('ins_inspection_requested', $data)) {
                                $this->utility_model->complete();

                                $res['err_msg'] = "Successfully Requested!";
                                $res['err_code'] = 0;
                            }
                        } else {
                            if ($this->utility_model->update('ins_inspection_requested', $data, array('id' => $id))) {
                                $this->utility_model->complete();

                                $res['err_msg'] = "Successfully Requested!";
                                $res['err_code'] = 0;
                            }
                        }

                        if ($res['err_code'] == 0) {
                            $recipients = array();

                            $fm = $this->utility_model->get('ins_admin', array('id' => $field_manager_id, 'allow_email' => 1));
                            if ($fm) {
                                array_push($recipients, array('email' => $fm['email']));
                            }

                            if ($document_person != "") {
                                $emails = explode(",", $document_person);
                                if (is_array($emails)) {
                                    foreach ($emails as $row) {
                                        $addr = trim($row);
                                        if (filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                                            array_push($recipients, array('email' => $addr));
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $res['err_msg'] = "Failed to request!";
                    }
                } else {
                    $res['err_msg'] = "Already Existed in Requested Inspections!";
                }
            }
        } else {
            $res['err_msg'] = "You have no permission!";
        }

        print_r(json_encode($res));
    }
    public function update_duct_leakage_inspection_requested2()
    {
        $res = array('err_code' => 1);
        $res['err_msg'] = "Failed to request!";
        $t = mdate('%Y%m%d%H%i%s', time());

        if ($this->session->userdata('user_id') && $this->session->userdata('permission') == 1) {
            $id = $this->input->get_post('id');
            $manager_id = $this->input->get_post('manager_id');

            $date_requested = $this->input->get_post('date_requested');
            $job_number = $this->input->get_post('job_number');
            $lot = $this->input->get_post('lot');

            $community = $this->input->get_post('community');
            $address = $this->input->get_post('address');
            $city = $this->input->get_post('city');
            $area = $this->input->get_post('area');
            $volume = $this->input->get_post('volume');
            $wall_area = $this->input->get_post('wall_area');
            $ceiling_area = $this->input->get_post('ceiling_area');
            $design_location = $this->input->get_post('design_location');

            $field_manager_name = $this->input->get_post('field_manager');
            $qn = $this->input->get_post('qn');

            $document_person = $this->input->get_post('document_person');
            if ($document_person === false) {
                $document_person = "";
            }
            if(is_string($id)&& strlen($id)>0){
              $this->utility_model->start();
              $data = array(
                  'category' => 3,
                  'manager_id' => $manager_id, //$this->session->userdata('user_id'),
                  'job_number' => $job_number,
                  'lot' => $lot,
                  'requested_at' => $date_requested,
                  'time_stamp' => $t,
                  'ip_address' => $this->get_client_ip(),
                  'community_name' => $community,
                  'address' => $address,
                  'city' => $city,
                  'area' => $area,
                  'volume' => $volume,
                  'qn' => $qn,
                  'wall_area' => $wall_area,
                  'ceiling_area' => $ceiling_area,
                  'design_location' => $design_location,
                  'document_person' => $document_person
              );

              if ($this->utility_model->update('ins_inspection_requested', $data, array('id' => $id))) {
                  $this->utility_model->complete();

                  $res['err_msg'] = "Successfully Requested!";
                  $res['err_code'] = 0;
              }else{
                $res['err_msg'] = "Failed to request!";
              }
            }
        } else {
            $res['err_msg'] = "You have no permission!";
        }

        print_r(json_encode($res));
    }

    public function test()
    {
        echo $this->get_valid_requested_date();
        echo "<br>";
        echo "<br>";
    }

    public function testajax()
    {
        $ret = array();
        $ret['response'] = 400;
        header('Content-Type: application/json');
        echo json_encode($ret);
    }



    public function update_community()
    {
        $res = array('err_code'=>1, 'err_msg'=>'Failed!');
        if ($this->session->userdata('user_id')) {
            if ($this->utility_model->has_permission($this->session->userdata('permission'), 1)) {
                $units = $this->input->get_post('units');
                if ($units!==false && $units!="") {
                    $t = mdate('%Y%m%d%H%i%s', time());

                    $unit_list = json_decode($units, true);
                    if ($unit_list===false) {
                        $res['err_msg'] = "Bad Request";
                    } else {
                        $this->utility_model->start();
                        $retarr = array();
                        foreach ($unit_list as $unit) {
                            $id = $unit['id'];
                            $community_id = $unit['community_id'];

                            $building = $this->utility_model->get('ins_community', array('id'=>$id));
                            if ($building) {
                                if ($this->utility_model->update('ins_community', array('community_id'=>$community_id, 'updated_at'=>$t), array('id'=>$id))) {
                                    // succ
                                    $unit['response'] = 200;
                                    $retarr[] = $unit;
                                } else {
                                    $unit['response'] = 400;
                                    $retarr[] = $unit;
                                }
                            }
                        }

                        $this->utility_model->complete();

                        $res['err_code'] = 0;
                        $ret['my_ret'] = $retarr;
                        $res['err_msg'] = "Successfully Updated!";
                    }
                } else {
                    $res['err_msg'] = "Bad Request";
                }
            }
        } else {
            $res['err_msg'] = "You haven't permission";
        }

        print_r(json_encode($res));
    }
    public function testme()
    {
        if (function_exists("set_time_limit") == true and @ ini_get("safe_mode") == 0) {
            @set_time_limit(0);
        }
        $ret = array();
        switch (0) {
          case 3:{
            $ip = $this->get_client_ip();
            $ret['printmode'] = 1;
            $ret['ip'] = $ip;
            echo "<pre>";
            print_r($ret);
            echo "</pre>";
            break;
          }
          case 2:{
            $str = '{"0":{"response":200,"duplicate_building":"16020150175","community_rowid":"245","community":{"community_id":"16020","community_name":"ARTESIA VILLA SF34 AA","city":"NAPLES","builder":"2","created_at":"20170829042056","updated_at":"20170829042056","id":"245"},"duplicate_ins_req_job_number":"16020150175","action":"add"},"1":{"response":200,"duplicate_building":"26500251109","community_rowid":"246","community":{"community_id":"26500","community_name":"PELICAN PRES - PRATO -GR VILLA","city":"FORT MYERS","builder":"2","created_at":"20170829042059","updated_at":"20170829042059","id":"246"},"duplicate_ins_req_job_number":"26500251109","action":"add"},"array_community":[{"community_id":"16020","community_name":"ARTESIA VILLA SF34 AA","city":"NAPLES","builder":"2","created_at":"20170829042056","updated_at":"20170829042056","id":"245"},{"community_id":"26500","community_name":"PELICAN PRES - PRATO -GR VILLA","city":"FORT MYERS","builder":"2","created_at":"20170829042059","updated_at":"20170829042059","id":"246"}],"response":200,"ipaddr":"::1"}';
            header('Content-Type: application/json');
            echo $str;
            break;
          }
            case 1: {
                    $ret['response'] = 200;
                    header('Content-Type: application/json');
                    // echo json_encode( $ret );
                    // echo json_encode( $ret );
                    $this->m_checkwci->setDbInfo(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);
                    $this->m_checkwci->initialize();
                    echo json_encode($ret);
                    return;
                    $ret = $this->m_checkwci->wci->start(1);
                    $ret['response'] = 200;
                    header('Content-Type: application/json');
                    echo json_encode($ret);
                    //print_r(json_encode($ret));
                    break;
                }


            default: {
                    $ip = $this->get_client_ip();
                    $this->m_checkwci->setDbInfo(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);
                    $this->m_checkwci->setMailInfo(SMTP_HOST, SMTP_USER, SMTP_PASSWORD);
                    $this->m_checkwci->initialize();
                    $this->m_checkwci->ipaddr = $ip;
                    $ret = $this->m_checkwci->wci->start(0,$ip);  //16
                    if (is_array($ret)) {
                        $array = array();
                        $array_community_name = array();
                        for ($i=0;$i<count($ret);$i++) {
                            $idata = $ret[$i];
                            if (isset($idata['community'])) {
                              $community = $idata['community'];
                              if (in_array($community['community_name'], $array_community_name)) {
                                // continue
                              }else{
                                $array[] = $community;
                                $array_community_name[] = $community['community_name'];
                              }
                            }
                        }
                        $ret['array_community'] = $array;
                    }
                    $ret['response'] = 200;
                    $ret['ipaddr'] = $ip;
                    $printmode = $this->input->get("printmode");
                    if(is_string($printmode)&& $printmode == "1"){
                      //header('Content-Type: application/json');
                      $ret['printmode'] = 1;
                      echo "<pre>";
                      print_r($ret);
                      echo "</pre>";
                    }else{
                      header('Content-Type: application/json');
                      $ret['printmode'] = 0;
                      echo json_encode($ret);
                    }
                    //print_r(json_encode($ret));
                    break;
                }
        }
    }
}
