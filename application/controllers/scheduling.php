<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Scheduling extends CI_Controller {


    public function __construct() {
        parent::__construct();
//        $this->load->library('user_agent');
        $this->load->library('holiday');
        $this->load->helper('directory');

        $this->load->model('user_model');
        $this->load->model('utility_model');
        $this->load->model('datatable_model');
    }

    private function send_mail($subject, $body, $sender, $isHTML=false) {
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

    public function energy() {
        if (!$this->session->userdata('user_id')) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $time = time();

        $page_data['start_date'] = date('Y-m-d', $time-7*24*60*60);
        $page_data['end_date'] = date('Y-m-d', $time+30*24*60*60);
        $page_data['region'] = $this->utility_model->get_list('ins_region', array());

        $page_data['page_name'] = 'scheduling_energy';
        $this->load->view('scheduling_energy', $page_data);
    }

    public function water_intrusion() {
        if (!$this->session->userdata('user_id')) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $time = time();

        $page_data['start_date'] = date('Y-m-d', $time-7*24*60*60);
        $page_data['end_date'] = date('Y-m-d', $time+30*24*60*60);
        $page_data['region'] = $this->utility_model->get_list('ins_region', array());

        $page_data['page_name'] = 'scheduling_water';
        $this->load->view('scheduling_water', $page_data);
    }

    public function load_inspector() {
        $res = array('err_code'=>1);
        $res['err_msg'] = "Failed!";

        if ($this->session->userdata('user_id')) {
            $res['inspector'] = $this->utility_model->get_list('ins_user', array());
            $res['err_code'] = 0;
            $res['err_msg'] = "Success!";
        }

        print_r(json_encode($res));
    }

    public function assign_inspector() {
        $res = array('err_code'=>1);
        $res['err_msg'] = "Failed to assign!";

        if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
            $inspection_id = $this->input->get_post('inspection_id');
            $inspector_id = $this->input->get_post('inspector_id');

            if ($inspection_id!==false && $inspector_id!==false && $inspection_id!="" && $inspector_id!="") {
                $today = mdate('%Y-%m-%d', time());

                $data = array(
                    'inspector_id'=>$inspector_id,
                    'assigned_at'=>$today,
                    'status'=>1,
                    'time_stamp'=>mdate('%Y%m%d%H%i%s', time()),
                );

                if ($inspector_id=="0") {
                    $data['assigned_at'] = null;
                    $data['status'] = 0;
                }

                if ($this->utility_model->update('ins_inspection_requested', $data, array('id'=>$inspection_id))) {
                    $res['err_msg'] = "Successfully Assigned!";
                    $res['err_code'] = 0;
                }
            } else {
                $res['err_msg'] = "Missing Parameters!";
            }
        } else {
            $res['err_msg'] = "You have no permission!";
        }

        print_r(json_encode($res));
    }

    public function load(){

        $cols = array( "a.requested_at", "a.community_name", "a.job_number", "a.address", "c.city", "m.first_name", "a.category", "a.time_stamp", "u.first_name", );

        $table = " ins_inspection_requested a "
               . " left join ins_community c on c.community_id=substr(a.job_number,1,4)"
               . " left join ins_region r on c.region=r.id "
               . " left join ins_admin m on a.manager_id=m.id "
               . " left join ins_user u on a.inspector_id=u.id "
               . " where ( a.status=0 or a.status=1 ) ";

        $category = $this->input->get_post('category');
        if ($category===false) {
            $category = "";
        }

        if ($category=="1_2") {
            $table .= " and ( a.category=1 or a.category=2 )";
        } else if ($category=='3') {
            $table .= " and ( a.category=3 )";
        } else if ($category=='3_4') {
            $table .= " and ( a.category=3 or a.category=4 )";
        }else {
        }

        $result = array();

        $amount = 10;
        $start = 0;
        $col = 0;

        $dir = "desc";

        $region = $this->input->get_post('region');
        $community = $this->input->get_post('community');
        $start_date = $this->input->get_post('start_date');
        $end_date = $this->input->get_post('end_date');

        $common_sql = "";

        if ($start_date!==false && $start_date!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.requested_at>='$start_date' ";
        }

        if ($end_date!==false && $end_date!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.requested_at<='$end_date' ";
        }

        if ($region!==false && $region!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " c.region='$region' ";
        }

        if ($community!==false && $community!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " substr(a.job_number,1,4)='$community' ";
        }

        $sStart = $this->input->get_post('start');
        $sAmount = $this->input->get_post('length');

        $order_sql = "";
        $order = $this->input->get_post("order");
        if ($order!==false && is_array($order)) {
            foreach ($order as $row) {
                $col = intval($row['column']);
                $dir = $row['dir'];

                if ($col<0 || $col>8){
                    $col=0;
                }

                if ($order_sql!="") {
                    $order_sql .= ", ";
                }
                $order_sql .= $cols[$col] . " " . $dir . " ";
            }

            if ($order_sql!="") {
                $order_sql = " order by " . $order_sql;
            }
        }

        $searchTerm = "";
        $search = $this->input->get_post("search");
        foreach ($search as $key => $value) {
            if ($key=='value')
                $searchTerm = $value;
        }

        if ($sStart!==false && strlen($sStart)>0){
            $start = intval($sStart);
            if ($start<0){
                $start=0;
            }
        }

        if ($sAmount!==false && strlen($sAmount)>0){
            $amount = intval($sAmount);
            if ($amount<10 || $amount>100){
                $amount = 10;
            }
        }

        $total = 0;
        $totalAfterFilter = 0;

        $sql = " select count(*) from " . $table . " ";
        if ($common_sql!="") {
            $sql .= " and " . $common_sql;
        }

        $total = $this->datatable_model->get_count($sql);
        $totalAfterFilter = $total;

        $sql = " select  a.id, a.category, a.reinspection, a.epo_number, a.job_number, a.requested_at, a.assigned_at, a.completed_at, a.manager_id, a.inspector_id, "
                . " a.time_stamp, a.ip_address, a.community_name, a.lot, a.address, a.status, a.area, a.volume, a.qn, a.city as city_duct, "
                . " '' as additional, "
                . " m.first_name, m.last_name, concat(u.first_name, ' ', u.last_name) as inspector_name, "
                . " c1.name as category_name, c.community_id, c.region, r.region as region_name, c.city "
                . " from ins_code c1, " . $table . " and c1.kind='ins' and c1.code=a.category ";

        if ($common_sql!="") {
            $sql .= " and " . $common_sql;
        }

        $searchSQL = "";
        $globalSearch = " ( "
            . " replace(a.job_number,'-','') like '%" . str_replace('-','',$searchTerm) . "%' or "
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

        if ($searchTerm && strlen($searchTerm)>0){
            $searchSQL .= " and " . $globalSearch;
        }

        $sql .= $searchSQL;
        $sql .= $order_sql;

        $sql .= " limit " . $start . ", " . $amount . " ";
        $data = $this->datatable_model->get_content($sql);
        $result["sql"] = $sql;
        if (is_array($data)) {
            $mtable = " ins_region r, ins_code c1, ins_code c2,  "
                    . " ( SELECT p1.inspection_id, p2.* "
                    . "   FROM "
                    . "    ( SELECT MAX(t.id) AS inspection_id, t.job_number, bbb.address, t.type FROM ins_inspection t LEFT JOIN ins_building_unit bbb ON REPLACE(t.job_number,'-','')=REPLACE(bbb.job_number, '-', '') AND bbb.address=t.address AND bbb.address=t.address and t.is_building_unit=1 GROUP BY t.job_number, bbb.address, t.type ) p1, "
                    . "    ( SELECT t.type, t.job_number, bbb.address, MAX(t.start_date) AS inspection_date, COUNT(*) AS inspection_count  FROM ins_inspection t  LEFT JOIN ins_building_unit bbb ON REPLACE(t.job_number,'-','')=REPLACE(bbb.job_number, '-', '') AND bbb.address=t.address and t.is_building_unit=1 GROUP BY t.job_number, bbb.address, t.type ) p2 "
                    . "   WHERE p1.type=p2.type AND p1.job_number=p2.job_number AND ((p1.address IS NULL AND p2.address IS NULL) OR p1.address=p2.address) "
                    . " ) g "
                    . " LEFT JOIN ins_inspection a ON g.inspection_id=a.id "
                    . " LEFT JOIN ins_inspection_requested q ON a.requested_id=q.id "
                    . " LEFT JOIN ins_admin u ON a.field_manager=u.id AND u.kind=2 "
                    . " LEFT JOIN ins_community tt ON tt.community_id=a.community "
                    . " WHERE a.region=r.id AND c1.kind='ins' AND c1.code=a.type AND c2.kind='rst' "
                    . " AND c2.code=a.result_code "
                    . " ";
            $base_sql = " select  a.*, "
                    . " (g.inspection_count-1) as inspection_count, q.epo_number as requested_epo_number, "
                    . " c1.name as inspection_type, c2.name as result_name, "
                    . " r.region as region_name, tt.community_name, "
                    . " u.first_name, u.last_name, '' as additional "
                    . " from " . $mtable . " ";
            foreach ($data as $key => $value) {

              $id = $value['id'];
              $job_number = $value['job_number'];
              $category = $value['category'];
              $msql = $base_sql." and q.job_number = '$job_number' and q.category = $category";
              $mdata = $this->datatable_model->get_content($msql);
              if (is_array($mdata)) {
                $value['re_inspection'] = $mdata[0]['inspection_count'];
              }else{
                $value['re_inspection'] = '0';
              }
//              $result["sql".$key] = $msql;
              $data[$key] = $value;
            }
        }

        $sql = " select count(*) from ins_code c1 , " . $table . " and c1.kind='ins' and c1.code=a.category  ";
        if (strlen($searchSQL)>0){
            if ($common_sql!="") {
                $sql .= " and " . $common_sql;
            }

            $sql .= $searchSQL;
            $totalAfterFilter = $this->datatable_model->get_count($sql);
        }

        if (!$this->session->userdata('user_id') || $this->session->userdata('permission')!='1') {

        } else {
            $result["recordsTotal"] = $total;
            $result["recordsFiltered"] = $totalAfterFilter;
            $result["data"] = $data;
        }

        print_r(json_encode($result));
    }

}
