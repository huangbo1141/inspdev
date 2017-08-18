<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Manager extends CI_Controller {
    
    private $hash_key = "inspection_A";
    private $hash_key__front = "inspection_front_user";

    private $errMsg = array(
        "Success!",
        "Failed!",
        "Already exist!",
        "Non Exist User!",
        "Please Active User. <br> Click to \"Reset it\"",
        "Wrong Password!",
        "Bad Crediential!",
        "Expired!",
        "Your Account has been activated!",
        "Your Account has been deactivated!"
    );
    
    
    public function __construct() {
        parent::__construct();
//        $this->load->library('user_agent');

        $this->load->model('user_model');
        $this->load->model('utility_model');
        $this->load->model('datatable_model');
    }

    public function lists($type='') {
        if ($type=='1') {
            redirect(base_url() . "manager/admin.html");
        } else if ($type=='2') {
            redirect(base_url() . "manager/field.html");
        } else if ($type=='3') {
            redirect(base_url() . "manager/construction.html");
        } else {
            redirect(base_url() . "user/profile.html");
        }
    }
    
    public function admin() {
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission')!=1) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }
        
        $page_data['page_name'] = 'admin';
        $page_data['page_type'] = 1;
        $page_data['page_title'] = 'Admin List';
        $this->load->view('manager_list', $page_data);
    }

    public function field() {
        if (!$this->session->userdata('user_id') || !$this->is_admin($this->session->userdata('permission'))) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }
        
        $page_data['page_name'] = 'field_manager';
        $page_data['page_type'] = 2;
        $page_data['page_title'] = 'Field Manager List';
        $this->load->view('manager_list', $page_data);
    }

    public function construction() {
        if (!$this->session->userdata('user_id') || !$this->is_admin($this->session->userdata('permission'))) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }
        
        $page_data['page_name'] = 'construction_manager';
        $page_data['page_type'] = 3;
        $page_data['page_title'] = 'Construction Manager List';
        $this->load->view('manager_list', $page_data);
    }
    
    public function scheduler() {
        if (!$this->session->userdata('user_id') || !$this->is_admin($this->session->userdata('permission'))) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }
        
        $page_data['page_name'] = 'scheduler';
        $page_data['page_type'] = 4;
        $page_data['page_title'] = 'Scheduler List';
        $this->load->view('manager_list', $page_data);
    }
    
    
    public function load(){
        $cols = array("a.email", "a.first_name", "a.last_name", "a.address" );
        $table = "ins_admin a"; 
        
        $result = array();

        if (!$this->session->userdata('user_id')) {
            print_r(json_encode($result));
            exit(1);
        } 
        
        $user_id = $this->session->userdata('user_id');
        
        $amount = 10;
        $start = 0;
        $col = 0;
	 
	$dir = "asc";
        
        $type = $this->input->get_post('type');
        
        $sStart = $this->input->get_post('start');
        $sAmount = $this->input->get_post('length');
//	$sCol = $this->input->get_post('iSortCol_0'); 
//      $sdir = $this->input->get_post('sSortDir_0');  
        $sCol = "";
        $sdir = "";
        
        $sCol = $this->input->get_post("order");
        foreach ($sCol as $row) {
            foreach ($row as $key => $value) {
                if ($key=='column')
                    $sCol = $value;
                if ($key=='dir')
                    $sdir = $value;
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
        
        $condition = "";
        if ($type=="1") {
            $condition = " ( a.kind='0' or a.kind='1' ) ";
        } else {
            $condition = " a.kind='" . $type . "' ";
        }
        $condition .= " and a.id<>'" . $user_id . "' ";
        
        $sql = " select count(*) from " . $table . " where " . $condition ;
        $total = $this->datatable_model->get_count($sql);
        $totalAfterFilter = $total;
        
        $sql = " select  a.*, b.name as builder_name, '' as additional from " . $table . " "
                . " left join ins_builder b on a.builder=b.id " . " "
                . " where " . $condition ;
        $searchSQL = "";
        $globalSearch = " ( "
                . " a.first_name like '%" . $searchTerm . "%' or "
                . " a.email like '%" . $searchTerm . "%' or  "
                . " a.last_name like '%" . $searchTerm . "%' or  "
                . " a.address like '%" . $searchTerm . "%' "
//                . " r.region like '%" . $searchTerm . "%' "
                . " ) ";
        
        if ($searchTerm && strlen($searchTerm)>0){
            $searchSQL .= " and " . $globalSearch;
        }

        $sql .= $searchSQL;
        $sql .= " order by " . $colName . " " . $dir . " ";
        $sql .= " limit " . $start . ", " . $amount . " ";
        $data = $this->datatable_model->get_content($sql);
        
        $result_data = array();
        $index = 1;
        foreach ($data as $row) {
            $row['index'] = $index;
            
            $region_name = "";
            $sql = " select r.region from ins_admin_region a, ins_region r where a.manager_id='" . $row['id'] . "' and a.region=r.id ";
            $regions = $this->utility_model->get_list__by_sql($sql);
            if ($regions) {
                foreach ($regions as $rrr) {
                    if ($region_name!="") {
                        $region_name .= ", ";
                    }
                    $region_name .= $rrr['region'];
                }
            }
            $row['region_name'] = $region_name;
            
            array_push($result_data, $row);
            $index++;
        }
        
        $sql = " select count(*) from " . $table . " " . " where " . $condition ;
        if (strlen($searchSQL)>0){
            $sql .= $searchSQL;
            $totalAfterFilter = $this->datatable_model->get_count($sql);
        }
        
        $result["recordsTotal"] = $total;
        $result["recordsFiltered"] = $totalAfterFilter;
        $result["data"] = $result_data;
        
        print_r(json_encode($result));
    }
    
    public function add() {
        if (!$this->session->userdata('user_id')) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }
        
        $permission = $this->session->userdata('permission');
        $type = $this->input->get_post('type');

        $user_id = $this->input->get_post('user_id');
        
        if (!$this->check_permission($type, $permission))  {
            $this->session->set_userdata('message', $this->errMsg[6]);
            redirect(base_url() . "manager/lists/" . $type);
            exit(1);
        }
        
        if ($type=='1') {
            $page_data['page_name'] = 'admin';
            $page_data['page_title'] = "New Admin";
        }
        
        if ($type=='2') {
            $page_data['page_name'] = 'field_manager';
            $page_data['page_title'] = "New Field Manager";
        }
        
        if ($type=='3') {
            $page_data['page_name'] = 'construction_manager';
            $page_data['page_title'] = "New Construction Manager";
        }
        
        if ($type=='4') {
            $page_data['page_name'] = 'scheduler';
            $page_data['page_title'] = "New Scheduler";
        }

        $page_data['page_type'] = $type;
        
        $page_data['account'] = array('first_name'=>'', 'last_name'=>'', 'email'=>'', 'address'=>'', 'cell_phone'=>'', 'other_phone'=>'', 'region'=>'0', 'builder'=>'0');
        $page_data['region'] = $this->utility_model->get_list('ins_region', array());
        $page_data['builder'] = $this->utility_model->get_list('ins_builder', array());
        $page_data['user_id'] = $user_id;
        $page_data['kind'] = 'add';
        
        $this->load->view('manager_edit', $page_data);
    }  
    
    public function edit() {
        if (!$this->session->userdata('user_id')) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }
        
        $permission = $this->session->userdata('permission');
        $type = $this->input->get_post('type');

        $user_id = $this->input->get_post('user_id');
        $kind = $this->input->get_post('kind');

        if ($user_id==false) {
            redirect(base_url() . "manager/lists/" . $type);
            exit(1);
        }

        $page_data['account'] = $this->user_model->get_user__by_id('admin', $user_id);
        if ($page_data['account']===false) {
            redirect(base_url() . "manager/lists/" . $type);
            exit(1);
        }

        if (!$this->check_permission($type, $permission))  {
            $this->session->set_userdata('message', $this->errMsg[6]);
            redirect(base_url() . "manager/lists/" . $type);
            exit(1);
        }
        
        if ($type=='1') {
            $page_data['page_name'] = 'admin';
            $page_data['page_title'] = "Edit Admin";
        }
        
        if ($type=='2') {
            $page_data['page_name'] = 'field_manager';
            $page_data['page_title'] = "Edit Field Manager";
        }
        
        if ($type=='3') {
            $page_data['page_name'] = 'construction_manager';
            $page_data['page_title'] = "Edit Construction Manager";
        }
        
        if ($type=='4') {
            $page_data['page_name'] = 'scheduler';
            $page_data['page_title'] = "Edit Scheduler";
        }

        $page_data['page_type'] = $type;

        $regions = $this->utility_model->get_list__by_sql(" select a.*, r.id as region_id from ins_region a left join ins_admin_region r on r.manager_id='$user_id' and r.region=a.id ");
        $page_data['region'] = $regions;
        
        $page_data['builder'] = $this->utility_model->get_list('ins_builder', array());
        $page_data['user_id'] = $user_id;
        $page_data['kind'] = $kind;
        
        $this->load->view('manager_edit', $page_data);
    }        
    
    
    public function delete_user() {
        $res = array('err_code'=>1);
        if ($this->session->userdata('user_id')) {
            $user_id = $this->input->get_post('user_id');
            $type = $this->input->get_post('type');
            $permission = $this->session->userdata('permission');

            if ($this->check_permission($type, $permission, 1)) {
                if ($user_id!==false){
                    if ($this->user_model->delete_user('admin', array('id'=>$user_id))) {
                        $this->utility_model->delete('ins_admin_region', array('manager_id'=>$user_id));
                        $res['err_code'] = 0;
                    }
                } else {
                }
            }
        }
        
        print_r(json_encode($res));
    }    
    
    public function update_user() {
        $res = array('err_code'=>1, 'err_msg'=>'Failed!');
        if ($this->session->userdata('user_id')) {
            $user_id = $this->input->get_post('user_id');
            $kind = $this->input->get_post('kind');
            
            $type = $this->input->get_post('type');
            $permission = $this->session->userdata('permission');

            if ($kind=='add' && $user_id===false) {
                $user_id = "";
            }
            
            if ($user_id!==false && $kind!==false){
                if ($this->check_permission($type, $permission, 1)) {
                    $this->utility_model->start();
                    
                    $t = mdate('%Y%m%d%H%i%s', time());
                    $data = array('updated_at'=>$t);
                    
                    $ret = 1;
                    if ($kind=='add' || $kind=='profile') {
                        $email = $this->input->get_post('email');
                        $first_name = $this->input->get_post('first_name');
                        $last_name = $this->input->get_post('last_name');
                        $address = $this->input->get_post('address');
                        $cell_phone = $this->input->get_post('cell_phone');
                        $other_phone = $this->input->get_post('other_phone');
                        $region = $this->input->get_post('region');
                        $builder = $this->input->get_post('builder');

                        if ($other_phone===false) {
                            $other_phone = "";
                        }
                        
                        if ($email!==false && $first_name!==false && $last_name!==false) {
                            if ($type!='1' && ($region===false || $builder===false)) {
                                $ret = 0;
                            } else {
                                $data['email'] = $email;
                                $data['first_name'] = $first_name;
                                $data['last_name'] = $last_name;
                                $data['address'] = $address;
                                $data['cell_phone'] = $cell_phone;
                                $data['other_phone'] = $other_phone;
                                
                                if ($type=='2' || $type=='3' || $type=='4') {
                                    $data['region'] = 0;
                                } else {
                                    $data['region'] = $region;
                                }
                                
                                $data['builder'] = $builder;
                            }
                            
                            $user = $this->user_model->get_user__by_email('admin', $email);
                            if ($user) {
                                if ($user_id == $user['id']){
                                } else {
                                    $res['err_msg'] = $this->errMsg[2];
                                    $ret = 2;
                                }
                            }
                        }
                    }
                    
                    if ($kind=='add' || $kind=='password') {
                        $password = $this->input->get_post('password');
                        
                        if ($password!==false) {
                            $data['password'] = sha1($password . $this->hash_key);
                        } else {
                            $ret = 0;
                        }
                    }
                    
                    if ($ret==1) { 
                        if ($kind=='add') {
                            $res['err_msg'] = "Failed to Add!";
                        } else {
                            $res['err_msg'] = "Failed to Update!";
                        }
                        
                        if ($kind=='add') {
                            $data['kind'] = $type;
                            $data['created_at'] = $t;
                            
                            if ($this->user_model->insert_user('admin', $data)) {
                                $user_id = $this->utility_model->new_id();
                                
                                $res['err_code'] = 0;
                                $res['err_msg'] = "Successfully Added!";
                            }
                            
                        } else {
                            if ($this->user_model->update_user__by_id('admin', $user_id, $data)) {
                                $res['err_code'] = 0;
                                $res['err_msg'] = "Successfully Updated!";
                            }
                        }
                        
                        if ($res['err_code']==0) {
                            if ($kind!="password" && ($type=='2' || $type=='3' || $type=='4')) {
                                $this->utility_model->delete('ins_admin_region', array('manager_id'=>$user_id));
                                if (is_array($region)) {
                                    foreach ($region as $row) {
                                        $this->utility_model->insert('ins_admin_region', array('manager_id'=>$user_id, 'region'=>$row));
                                    }
                                }
                            }
                            
                            $this->utility_model->complete();
                        }
                    } else {
                        if ($ret==0) {
                            $res['err_msg'] = "You don't have permission";
                        }
                    }
                } else {
                    $res['err_msg'] = "You don't have permission";
                }
            }
        }
        
        print_r(json_encode($res));
    }    

    public function activate() {
        $res = array('err_code'=>1);
        if ($this->session->userdata('user_id')) {
            $user_id = $this->input->get_post('user_id');
            $status= $this->input->get_post('status');

            $type = $this->input->get_post('type');
            $permission = $this->session->userdata('permission');
            
            if ($this->check_permission($type, $permission, 1)) {
                if ($user_id!==false && $status!==false){
                    $t = mdate('%Y%m%d%H%i%s', time());
                    if ($this->utility_model->update('ins_admin', array('status'=>$status, 'updated_at'=>$t), array('id'=>$user_id))) {
                        $res['err_code'] = 0;
                    }
                } else {
                }
            }
        }
        
        print_r(json_encode($res));
    }    
    
    
    
    
    private function is_admin($permission) {
        return $permission==1 ? true : false;
    }
    
    private function check_permission($type, $permission, $kind=0) {
        if ($kind==0)  {
            if ($type=='1') {
                if ($permission==1) {
                    return true;
                }
            }
            
            if ($type=='2') {
                if ($this->is_admin($permission)) {
                    return true;
                }
            }
            
            if ($type=='3') {
                if ($this->is_admin($permission)) {
                    return true;
                }
            }
            
            if ($type=='4') {
                if ($this->is_admin($permission)) {
                    return true;
                }
            }
        }
        
        if ($kind==1)  {
            if ($permission==1) {
                return true;
            }
            
            if ($type!='1') {
                return true;
            }
        }
        
        return false;
    }
    
}
