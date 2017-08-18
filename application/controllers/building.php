<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Building extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        
        $this->load->model('utility_model');
        $this->load->model('datatable_model');
        $this->load->model('user_model');
    }
    
    public function index() {
        if (!$this->session->userdata('user_id')) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }
        
        redirect(base_url() . "building/home.html");
    }


    public function home() {
        if (!$this->session->userdata('user_id')) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $page_data['page_name'] = 'building_list';
        $this->load->view('building_list', $page_data);
    }

    public function load(){
        header('Content-Type: application/json');
        
        $cols = array("a.job_number", "c.community_name", "a.address", "a.field_manager");
        $table = " ins_building a "
                . " left join ins_community c on c.community_id=substr(a.job_number, 1, 4) "
                . " left join ins_building_unit t on a.job_number=t.job_number "; 
        
        $region = "";
        if ($this->session->userdata('permission')==1) {
        
        } else if ($this->session->userdata('permission')==0) {
            
        } else if ($this->session->userdata('permission')==4) {
            $region = "1";
            $table .= " where c.region in ( select region from ins_admin_region where manager_id='" . $this->session->userdata('user_id') . "' ) ";
            
        } else {
            $region = "1";
            $table .= " where c.region in ( select region from ins_admin_region where manager_id='" . $this->session->userdata('user_id') . "' ) ";
            
//            $region = $this->session->userdata('user_region');
//            if ($region=='0') {
//                $region = "";
//            } else {
//                $table .= " where c.region='$region' ";
//            }
        }
        
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
        if ($sCol!==false && is_array($sCol)) {
            foreach ($sCol as $row) {
                foreach ($row as $key => $value) {
                    if ($key=='column')
                        $sCol = $value;
                    if ($key=='dir')
                        $sdir = $value;
                }
            }
        }
        
        $searchTerm = "";
        $search = $this->input->get_post("search");
        if ($search!==false && is_array($search)) {
            foreach ($search as $key => $value) {
                if ($key=='value') {
                    $searchTerm = $value;
                }
            }
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
        
        if ($sCol!==false && strlen($sCol)>0){
            $col = intval($sCol);
            if ($col<0 || $col>3){
                $col=0;
            }
        }
        
        if ($sdir && strlen($sdir)>0){
            if ($sdir!="asc"){
                $dir="desc";
            }
        }
        
        $colName = $cols[$col];
        $total = 0;
        $totalAfterFilter = 0;
        
        $sql = " select count(*) from " . $table ;
        $total = $this->datatable_model->get_count($sql);
        $totalAfterFilter = $total;
        
        $sql = " select  a.*, c.community_name, t.id as unit_id, t.address as unit_address, '' as additional from " . $table . "  ";
        $searchSQL = "";
        $globalSearch = " ( "
                . " replace(a.job_number,'-','') like '%" . str_replace('-','',$searchTerm) . "%' or "
                . " a.community like '%" . $searchTerm . "%' or "
                . " t.address like '%" . $searchTerm . "%' or "
                . " a.address like '%" . $searchTerm . "%'  "
                . " ) ";
        
        if ($searchTerm && strlen($searchTerm)>0){
            if ($region=="") {
                $searchSQL .= " where " . $globalSearch;
            } else {
                $searchSQL .= " and " . $globalSearch;
            }
        }

        $sql .= $searchSQL;
        $sql .= " order by " . $colName . " " . $dir . " ";
        $sql .= " limit " . $start . ", " . $amount . " ";
        $data = $this->datatable_model->get_content($sql);
        
        $sql = " select count(*) from " . $table ;
        if (strlen($searchSQL)>0){
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


    public function upload() {
        $msg = array('code' => 1, 'message' => 'Failed!');
        $invalid_files = array();

        $all_fm = array();

        if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
            $dir_name = "resource/upload/building/";

            $this->load->library('uuid');        

            $unassigned_data = array();
            $unit_data = array();
            
            $community_building_match_data = array();
            
            if (is_array($_FILES['files']['name'])) {
                foreach ($_FILES['files']['name'] as $row => $row2) {
                    $uu_id = $this->uuid->v4();
                    $ext = pathinfo($_FILES['files']['name'][$row], PATHINFO_EXTENSION);
                    
                    $fname = mdate('%Y%m%d%H%i%s', time()) . "_" . $uu_id . "." . $ext;
                    $new_name = $dir_name . $fname;
                    $original_filename = $_FILES['files']['name'][$row];
                    
                    if (move_uploaded_file($_FILES['files']['tmp_name'][$row], $new_name)) {
                        $unassigned = array();
                        $community_unassigned = array();
                        
                        libxml_use_internal_errors(true);
                        $xml = simplexml_load_file($new_name);
                        libxml_clear_errors();

                        if ($xml!==false && isset($xml->vendor)) {
                            foreach ($xml->vendor->children() as $row) {
                                $at = mdate('%Y%m%d%H%i%s', time());

                                $job_number = isset($row->jobnum) ? strval($row->jobnum) : "";
                                $div = isset($row->div) && is_numeric($row->div) ? $row->div : 0;
                                $desc = isset($row->taskdesc) ? strval($row->taskdesc) : "";
                                $neighborhood = isset($row->neighborhood) ? strval($row->neighborhood) : "";
                                $address = isset($row->jobaddr) ? strval($row->jobaddr) : "";
                                $plan = isset($row->jobplan) ? strval($row->jobplan) : "";
                                $fm = isset($row->fm) ? strval($row->fm) : "";

                                $jobdel = isset($row->jobdel) && is_numeric($row->jobdel) ? $row->jobdel : 0;
                                $taskdel = isset($row->taskdel) && is_numeric($row->taskdel) ? $row->taskdel : 0;

                                $taskupdate = isset($row->taskupdate) ? strval($row->taskupdate) : "";
                                $tstartorig = isset($row->tstartorig) ? strval($row->tstartorig) : "";
                                $tstartschd = isset($row->tstartschd) ? strval($row->tstartschd) : "";

                                $duration = isset($row->duration) && is_numeric($row->duration) ? $row->duration : 0;
                                $recent_flag = isset($row->recent_flag) && is_numeric($row->recent_flag) ? $row->recent_flag : 0;

                                $building = $this->utility_model->get('ins_building', array('job_number'=>$job_number));

                                $data = array();
                                $data['job_number'] = $job_number;
                                
                                $community_id = substr($job_number, 0, 4);
                                $community = $this->utility_model->get('ins_community', array('community_id'=>$community_id));
                                $community_name = "";
                                if ($community && isset($community['community_name'])) {
                                    $community_name = $community['community_name'];
                                    $data['builder'] = $community['builder'];
                                } else {
                                    if ($this->search_unassigned_job($community_unassigned, $job_number)===false) {
                                        array_push($community_unassigned, $data);
                                    }
                                }
                                
                                $fm = trim(preg_replace("/[0-9\-\(\)]/", "", $fm));
                                $fm = str_replace("  ", " ", $fm);
                                $fm = str_replace("..", "", $fm);
                                $fm = trim($fm);
                                
//                                array_push($all_fm, $fm);
                                
                                $data['community'] = $community_name;
                                $data['updated_at'] = $at;
                                
                                if ($fm!="") {
                                    $data['field_manager'] = $fm;
                                }
                                
                                if ($fm=="") {
                                    array_push($unassigned, $data);
                                } else {
                                    $user = $this->utility_model->get__by_sql(" select u.* from ins_admin u where u.kind=2 and concat(trim(u.first_name), ' ', trim(u.last_name))=" . $this->utility_model->escape($fm) . " ");
                                    if ($user && isset($user['id'])) {
                                        if ($community && isset($community['region'])) {
                                            $region = $this->utility_model->get("ins_region", array('id'=>$community['region']));
                                            if ($region) {
                                                $user_region = $this->utility_model->get__by_sql(" select u.* from ins_admin u where u.kind=2 and u.id='" . $user['id'] . "' and u.id in ( select manager_id from ins_admin_region where region='" . $community['region'] . "' ) ");
                                                if ($user_region) {

                                                } else {
                                                    if ($this->search_unassigned_job_fm_region($community_building_match_data, $job_number, $fm, $region['id'])===false) {
                                                        array_push($community_building_match_data, array(
                                                            'job_number'=>$job_number,
                                                            'field_manager'=>$fm,
                                                            'region_id'=>$region['id'],
                                                            'region'=>$region['region'],
                                                        ));
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        if ($this->search_unassigned_fm($unassigned, $fm)===false) {
                                            array_push($unassigned, $data);
                                        }
                                    }
                                }

                                if ($building && isset($building['job_number'])) {
                                    $this->utility_model->update('ins_building', $data, array('job_number'=>$job_number));
                                } else {
                                    $data['address'] = $address;
                                    $data['created_at'] = $at;
//                                    $data['builder'] = 1;

                                    $this->utility_model->insert('ins_building', $data);
                                }
                                
                                if (strpos(strtolower($address), "shell bld")!==false || strpos(strtolower($plan), "shell")!==false) {
                                    $building_unit_count = $this->utility_model->get_count('ins_building_unit', array('job_number'=>$job_number));
                                    if ($building_unit_count==0) {
                                        if ($this->search_unassigned_job($unit_data, $job_number)===false) {
                                            array_push($unit_data, array(
                                                'job_number'=>$job_number,
                                                'address'=>$address,
                                                'plan'=>$plan,
                                                'field_manager'=>$fm,
                                            ));
                                        }
                                    } else {
                                        
                                    }
                                }
                            }
                        } else {
                            array_push($invalid_files, $original_filename);
                        }

                        unlink($new_name);
                
                        if (count($unassigned)>0) {
                            array_push($unassigned_data, array('filename'=>$original_filename, 'fm'=>$unassigned, 'community'=>$community_unassigned));
                        }
                    }
                }
                
                if (count($unassigned_data)>0) {
                    $this->send_unassigned_fm($unassigned_data);
                }
                
                $msg['units'] = $unit_data;

                $msg['code'] = 0;
                $msg['message'] = "Upload Finished!";
                
//                $msg['fm'] = $all_fm;
//                $msg['unassigned'] = $unassigned;
                
                if (count($invalid_files)>0) {
                    $messages = "";
                    foreach ($invalid_files as $filename) {
                        if ($messages!="") {
                            $messages .= ", ";
                        }

                        $messages .= $filename;
                    }
                    
                    $msg['message'] .= "<br>Invalid File :  " . $messages;
                }
                
                if (count($community_building_match_data)>0) {
                    $user = $this->utility_model->get('ins_admin', array('id'=>$this->session->userdata('user_id')));
                    if ($user && $user['allow_email']==1) {
                        $subject = "No Matching Region and Field Manager";
                        $body = "";
                        
                        foreach ($community_building_match_data as $row) {
                            $body .= " For Job Number " . $row['job_number'] . " Field Manager " . $row['field_manager'] . " is not in region " . $row['region'] . "\n";
                        }
                        
                        $this->send_mail($subject, $body, array( array('email'=>$user['email']) ));
                    }
                }
            } else {
                $msg['message'] = "No Files!";
            }
        } else {
            $msg['message'] = "No Permissions!";
        }
        
        print_r(json_encode($msg));
    }
    
    public function edit() {
        if (!$this->session->userdata('user_id')) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $job_number = $this->input->get_post('job_number');
        $kind = $this->input->get_post('kind');
        $unit_id = $this->input->get_post('unit_id');

        $page_data['page_name'] = 'building_list';
        if ($kind=='add') {
            $page_data['page_title'] = "Add Building";
            $page_data['building'] = array('job_number'=>'', 'community'=>'', 'address'=>'', 'builder'=>'', 'unit_count'=>0, 'field_manager'=>'');
        } else {
            $page_data['page_title'] = "Update Building";
            $building = $this->utility_model->get('ins_building', array('job_number'=>$job_number) );
            if ($building) {
                $ttt = $this->utility_model->get('ins_community', array('community_name'=>$building['community']));
                if ($ttt) {
                    $building['community'] = $ttt['community_id'];
                }
            }
            
            if ($unit_id!="") {
                $unit = $this->utility_model->get('ins_building_unit', array('id'=>$unit_id) );
                if ($unit) {
                    $building['address'] = $unit['address'];
                }
            }
            
            $page_data['building'] = $building;
        }
        
        if ($this->session->userdata('permission')!=1) {
            $page_data['page_title'] = "View Building";
        }

        $page_data['kind'] = $kind;
        $page_data['job_number'] = $job_number;
        $page_data['unit_id'] = $unit_id;
        $page_data['builder'] = $this->utility_model->get_list('ins_builder', array());
        $page_data['region'] = $this->utility_model->get_list('ins_region', array());

        $this->load->view('building_edit', $page_data);
    }
    
    public function get_community() {
        $res = array('err_code'=>1);
        if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
            $region = $this->input->get_post('region');

            if ($region===false || $region=="") {
                $res['community'] = $this->utility_model->get_list('ins_community', array());
                $res['err_code'] = 0;
            } else {
                $res['community'] = $this->utility_model->get_list('ins_community', array('region'=>$region));
                $res['err_code'] = 0;
            }
        }

        print_r(json_encode($res));
    }
    
    public function get_field_manager() {
        $response = array('code'=>1, 'message'=>'No Permission');
        
        if ($this->session->userdata('user_id')) {
            $input = $this->input->get_post('input');
            if ($input=="") {
                $response['message'] = "Bad Request";
            } else {
                $fms = $this->utility_model->get_list__by_sql(" select concat(a.first_name, ' ', a.last_name) as field_manager from ins_admin a where a.kind=2 "
                        . " and concat(a.first_name, ' ', a.last_name) like '%" . $input . "%' ");
                
                if ($fms) {
                    $result = array();
                    foreach ($fms as $row) {
                        array_push($result, $row['field_manager']);
                    }
                    
                    $response['result'] = $result;
                    $response['message'] = "Success";
                    $response['code'] = 0;
                } else {
                    $response['message'] = "Failed to get Field Manager";
                }
            }
        }

        print_r(json_encode($response));
    }
    
    
    public function update() {
        $res = array('err_code'=>1, 'err_msg'=>'Failed!');
        if ($this->session->userdata('user_id')) {
            if ($this->utility_model->has_permission($this->session->userdata('permission'), 3)) {
                $kind = $this->input->get_post('kind');
                $job_number = $this->input->get_post('job_number');
                $unit_id = $this->input->get_post('unit_id');

                if ($kind!==false && $job_number!==false){
                    $t = mdate('%Y%m%d%H%i%s', time());
                    $data = array('updated_at'=>$t);

                    $ret = 0;
                    if ($kind=='add' || $kind=='edit') {
                        $community = $this->input->get_post('community');
                        $address = $this->input->get_post('address');
                        $builder = $this->input->get_post('builder');
                        $field_manager = $this->input->get_post('field_manager');
                        if ($field_manager===false) {
                            $field_manager = "";
                        }
                        $field_manager = trim($field_manager);

                        if ($address!==false && $builder!==false) {
                            $ret = 1;

                            $ttt = $this->utility_model->get('ins_community', array('community_id'=>$community));
                            if ($ttt) {
                                $data['community'] = $ttt['community_name'];
                            }

                            $data['builder'] = $builder;
                        }
                    }

                    if ($ret==1) { 
                        if ($kind=='add') {
                            $res['err_msg'] = "Failed to Add!";
                        } else {
                            $res['err_msg'] = "Failed to Update!";
                        }

                        $ret = true;
                        $ttt = $this->utility_model->get('ins_building', array('job_number'=>$job_number));
                        if ($kind=='add' && $ttt) {
                            $ret = false;
                        }

                        if ($ret) {
                            if ($field_manager!="") {
                                $data['field_manager'] = $field_manager;
                            }

                            if ($kind=='add') {
                                $data['address'] = $address;
                                $data['job_number'] = $job_number;
                                $data['created_at'] = $t;

                                if ($this->utility_model->insert('ins_building', $data)) {
                                    $res['err_code'] = 0;
                                    $res['err_msg'] = "Successfully Added!";
                                }
                            } else {
                                if ($unit_id!='') {
                                    $this->utility_model->update('ins_building_unit', array('address'=>$address), array('id'=>$unit_id));
                                } else {
                                    $data['address'] = $address;
                                }

                                if ($this->utility_model->update('ins_building', $data, array('job_number'=>$job_number))) {
                                    $res['err_code'] = 0;
                                    $res['err_msg'] = "Successfully Updated!";
                                }
                            }
                        } else {
                            $res['err_msg'] = "Already Exist Job Number!";
                        }
                    } else {
                        $res['err_msg'] = "You haven't permission";
                    }
                }
            }
        } else {
            $res['err_msg'] = "You haven't permission";
        }
        
        print_r(json_encode($res));
    }    
    
    public function delete() {
        $res = array('err_code'=>1);
        if ($this->session->userdata('user_id')) {
            if ($this->utility_model->has_permission($this->session->userdata('permission'), 3)) {
                $job_number = $this->input->get_post('job_number');

                if ($job_number!==false){
                    if ($this->utility_model->delete('ins_building', array('job_number'=>$job_number))) {
                        $res['err_code'] = 0;

                        $this->utility_model->delete('ins_building_unit', array('job_number'=>$job_number));
                    }
                } else {
                }
            }
        }

        print_r(json_encode($res));
    }

    public function delete_unit() {
        $res = array('err_code'=>1);
        if ($this->session->userdata('user_id')) {
            if ($this->utility_model->has_permission($this->session->userdata('permission'), 3)) {
                $job_number = $this->input->get_post('job_number');

                if ($job_number!==false){
                    if ($this->utility_model->delete('ins_building_unit', array('job_number'=>$job_number))) {
                        $this->utility_model->update('ins_building', array('unit_count'=>0), array('job_number'=>$job_number));
                        $res['err_code'] = 0;
                    }
                } else {
                }
            }
        }

        print_r(json_encode($res));
    }

    public function update_unit() {
        $res = array('err_code'=>1, 'err_msg'=>'Failed!');
        if ($this->session->userdata('user_id')) {
            if ($this->utility_model->has_permission($this->session->userdata('permission'), 1)) {
                $units = $this->input->get_post('units');
                if ($units!==false && $units!=""){
                    $t = mdate('%Y%m%d%H%i%s', time());

                    $unit_list = json_decode($units, true);
                    if ($unit_list===false) {
                        $res['err_msg'] = "Bad Request";
                    } else {
                        $this->utility_model->start();

                        foreach ($unit_list as $unit) {
                            $job_number = $unit['job_number'];
                            $address = $unit['units'];

                            $building = $this->utility_model->get('ins_building', array('job_number'=>$job_number));
                            if ($building && is_array($address)) {
                                if ($this->utility_model->update('ins_building', array('unit_count'=>count($address), 'updated_at'=>$t), array('job_number'=>$job_number))) {
                                    $this->utility_model->delete('ins_building_unit', array('job_number'=>$job_number));

                                    foreach ($address as $addr) {
                                        $this->utility_model->insert('ins_building_unit', array(
                                            'job_number'=>$job_number,
                                            'address'=>$addr,
                                            'created_at'=>$t
                                        ));
                                    }
                                }
                            }
                        }

                        $this->utility_model->complete();

                        $res['err_code'] = 0;
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
    
    
    private function search_unassigned_fm($unassigned, $field_manager) {
        foreach ($unassigned as $row) {
            if ($row['field_manager']===$field_manager) {
                return true;
            }
        }
        
        return false;
    }

    private function search_unassigned_job($unassigned, $job_number) {
        foreach ($unassigned as $row) {
            if ($row['job_number']===$job_number) {
                return true;
            }
        }
        
        return false;
    }
    
    private function search_unassigned_job_fm_region($unassigned, $job_number, $field_manager, $region) {
        foreach ($unassigned as $row) {
            if ($row['job_number']===$job_number && $row['field_manager']==$field_manager && $row['region_id']==$region) {
                return true;
            }
        }
        
        return false;
    }

    private function send_unassigned_fm($data) {
        $subject = "Unassigned Field Manager";
        
        $body = "There are unassigned field managers while importing buildings. " . "\n"
                . " Below are details: " . "\n";
        
        foreach ($data as $rec) {
            $body .= "\n";
            $body .= " File Selected: " . $rec['filename'] . "\n";

            $i = 1;
            foreach ($rec['fm'] as $row) {
                $body .=  $i . ".  Job Number: " . $row['job_number'] . "\n";

                if (isset($row['field_manager']) && $row['field_manager']!="") {
                    $body .=  "       Field Manager: " . $row['field_manager'] . "\n";
                }
                if (isset($row['address']) && $row['address']!="") {
                    $body .=  "       Address: " . $row['address'] . "\n";
                }

                $i++;
            }
            
            if (isset($rec['community']) && count($rec['community'])>0) {
                $body .= "\n";
                $body .= " Below Job Numbers belong to an unknown Community, please create the community in the Community tab and update the Job Number with the necessary details. " . "\n";
                foreach ($rec['community'] as $row) {
                    $body .=  "              " . $row['job_number'] . "\n";
                }
            }
        }
        
        $user_id = $this->session->userdata('user_id');
        $user = $this->user_model->get_user__by_id('admin', $user_id);
        if ($user && $user['allow_email']==1) {
            $this->send_mail($subject, $body, array( array('email'=>$user['email']) ) );
        }
    }

    private function send_mail($subject, $body, $sender, $isHTML=false) {
        $this->load->library('mailer/phpmailerex');
        $mail = new PHPMailer;

        $mail->SMTPDebug = 0;                               // Enable verbose debug output
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
    
    
    public function test() {
        $fm = "Sean Crittenden ..";
        $fm = str_replace("..", "", $fm);
        echo $fm;
    }
}
