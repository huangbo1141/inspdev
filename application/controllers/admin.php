<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Admin extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
//        $this->load->library('user_agent');

        $this->load->model('user_model');
        $this->load->model('utility_model');
        $this->load->model('datatable_model');
    }
    


    public function configuration() {
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission')!='1') {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $page_data['page_name'] = 'admin_configuration';
        
        $report_keep_day = $this->utility_model->get('sys_config', array('code'=>'report_keep_day'));
        if ($report_keep_day) {
            $page_data['report_keep_day'] = $report_keep_day['value'];
        } else {
            $page_data['report_keep_day'] = 30;
        }
        
        $this->load->view('admin_configuration', $page_data);
    }

    public function update_configuration() {
        $res = array('code'=>-1, 'message'=>'Failed');
        
        if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) { 
            $report_keep_day = $this->input->get_post('report_keep_day');
            if ($report_keep_day=="" || !is_numeric($report_keep_day)) {
                $res['message'] = "Please Enter Keep Days for PDF Report!";
            } else {
                if ($this->utility_model->get('sys_config', array('code'=>'report_keep_day'))) {
                    if ($this->utility_model->update('sys_config', array('value'=>$report_keep_day), array('code'=>'report_keep_day'))) {
                        $res['message'] = "Success";
                        $res['code'] = 0;
                    } else {
                        $res['message'] = "Failed to Update";
                    }
                } else {
                    if ($this->utility_model->insert('sys_config', array('code'=>'report_keep_day', 'value'=>$report_keep_day))) {
                        $res['message'] = "Success";
                        $res['code'] = 0;
                    } else {
                        $res['message'] = "Failed to Update";
                    }
                }
            }
        }
        
        print_r(json_encode($res));
    }
    
    
    
    
    public function recipient() {
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission')!='1') {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $page_data['page_name'] = 'recipient_email';
        
        $this->load->view('admin_recipient', $page_data);
    }

    public function load_recipient(){
        $cols = array("a.email", "a.status");
        $table = "sys_recipient_email a"; 
        
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
            if ($col<0 || $col>4){
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
        
        $sql = " select  a.*, '' as additional, '' as action from " . $table . " " ;
        $searchSQL = "";
        $globalSearch = " ( "
                . " a.email like '%" . $searchTerm . "%' or "
                . " a.status like '%" . $searchTerm . "%'  "
                . " ) ";
        
        if ($searchTerm && strlen($searchTerm)>0){
            $searchSQL .= " where " . $globalSearch;
        }

        $sql .= $searchSQL;
        $sql .= " order by " . $colName . " " . $dir . " ";
        $sql .= " limit " . $start . ", " . $amount . " ";
        $data = $this->datatable_model->get_content($sql);
        
        $sql = " select count(*) from " . $table . " ";
        if (strlen($searchSQL)>0){
            $sql .= $searchSQL;
            $totalAfterFilter = $this->datatable_model->get_count($sql);
        }
        
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission')!='1') {
            
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
    

    public function update_recipient() {
        $res = array('err_code'=>1, 'err_msg'=>'Failed!');
        
        if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) { 
            $kind = $this->input->get_post('kind');
            $id = $this->input->get_post('id');
            $email = $this->input->get_post('email');
            
            if ($kind!=false && $id!==false && $email!==false) {
                if ($kind=='add') {
                    $res['err_msg'] = "Failed to Add!";
                } else {
                    $res['err_msg'] = "Failed to Update!";
                }
                
                $ret = true;
                $d = $this->utility_model->get('sys_recipient_email', array('email'=>$email));
                if ($d) {
                    if ($d['id']!=$id) {
                        $res['err_msg'] = $this->errMsg[2];
                        $ret = false;
                    } 
                }
                
                if ($ret) {
                    if ($kind=='add') {
                        if ($this->utility_model->insert('sys_recipient_email', array('email'=>$email))) {
                            $res['err_code'] = 0;
                            $res['err_msg'] = "Successfully Added!";
                        }
                    } 
                    
                    if ($kind=='edit') {
                        if ($this->utility_model->update('sys_recipient_email', array('email'=>$email), array('id'=>$id))) {
                            $res['err_code'] = 0;
                            $res['err_msg'] = "Successfully Updated!";
                        }
                    }
                }
            } 
        }
        
        print_r(json_encode($res));
    }

    public function delete_recipient() {
        $res = array('err_code'=>1);
        
        if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) { 
            $id = $this->input->get_post('id');
            
            if ($id!==false) {
                if ($this->utility_model->delete('sys_recipient_email', array('id'=>$id))) {
                    $res['err_code'] = 0;
                }
            } 
        }
        
        print_r(json_encode($res));
    }
    
    public function activate_recipient() {
        $res = array('err_code'=>1);
        
        if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) { 
            $id = $this->input->get_post('id');
            $status = $this->input->get_post('status');
            
            if ($id!==false && $status!==false) {
                if ($this->utility_model->update('sys_recipient_email', array('status'=>$status), array('id'=>$id))) {
                    $res['err_code'] = 0;
                }
            } 
        }
        
        print_r(json_encode($res));
    }    
    
    
    public function template() {
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission')!='1') {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $page_data['page_name'] = 'report_template';
        $page_data['template'] = $this->utility_model->get('sys_config', array('code'=>'report_template'));
        
        $this->load->view('admin_template', $page_data);
    }

    public function update_template() {
        $res = array('err_code'=>1);
        
        if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) { 
            $template = $this->input->get_post('template');
            
            if ($template!==false) {
                if ($this->utility_model->update('sys_config', array('value'=>$template), array('code'=>'report_template'))) {
                    $res['err_code'] = 0;
                }
            } 
        }
        
        print_r(json_encode($res));
    }
    
}
