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
        
        $reinspection_allowed = $this->utility_model->get('sys_config', array('code'=>'reinspection_allowed'));
        if ($reinspection_allowed) {
            $page_data['reinspection_allowed'] = $reinspection_allowed['value'];
        } else {
            $page_data['reinspection_allowed'] = 5;
        }
        
        $twilio_term = "twilio";
        $checklist_term = "checklist";
        
        $other_rows = array();
        $checklist_rows = array();
        $config_rows = $this->utility_model->get_list__by_sql("select * from sys_config");
        foreach($config_rows as $row){
            $code = $row['code'];
            $value = $row['value'];
            if(substr($code,0, strlen($twilio_term)) === $twilio_term){
                $other_rows[] = $row;
            }else  if(substr($code,0, strlen($checklist_term)) === $checklist_term){
                $checklist_rows[] = $row;
            }
            
        }
        $page_data['other_rows'] = $other_rows;
        $page_data['checklist_rows'] = $checklist_rows;

        $this->load->view('admin_configuration', $page_data);
    }
    public function holidays() {
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission')!='1') {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $page_data['page_name'] = 'admin_holidays';
        $list_temp = $this->utility_model->get_list__by_sql("select * from sys_config_holiday");
        $page_data['holidays'] = $list_temp;

        $this->load->view('admin_holidays', $page_data);
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
            
            $reinspection_allowed = $this->input->get_post('reinspection_allowed');
            if ($reinspection_allowed=="" || !is_numeric($reinspection_allowed)) {
                $res['message'] = "Please Enter Re-Inspections Allowed!";
            } else {
                if ($this->utility_model->get('sys_config', array('code'=>'reinspection_allowed'))) {
                    if ($this->utility_model->update('sys_config', array('value'=>$reinspection_allowed), array('code'=>'reinspection_allowed'))) {
                        $res['message'] = "Success";
                        $res['code'] = 0;
                    } else {
                        $res['message'] = "Failed to Update";
                    }
                } else {
                    if ($this->utility_model->insert('sys_config', array('code'=>'reinspection_allowed', 'value'=>$reinspection_allowed))) {
                        $res['message'] = "Success";
                        $res['code'] = 0;
                    } else {
                        $res['message'] = "Failed to Update";
                    }
                }
            }
            
            $twilio_sid = $this->input->get_post('twilio_sid');
            if ($twilio_sid=="") {
                $res['message'] = "Please Enter Re-Inspections Allowed!";
            } else {
                if ($this->utility_model->get('sys_config', array('code'=>'twilio_sid'))) {
                    if ($this->utility_model->update('sys_config', array('value'=>$twilio_sid), array('code'=>'twilio_sid'))) {
                        $res['message'] = "Success";
                        $res['code'] = 0;
                    } else {
                        $res['message'] = "Failed to Update";
                    }
                } else {
                    if ($this->utility_model->insert('sys_config', array('code'=>'twilio_sid', 'value'=>$twilio_sid))) {
                        $res['message'] = "Success";
                        $res['code'] = 0;
                    } else {
                        $res['message'] = "Failed to Update";
                    }
                }
            }
            
            $twilio_token = $this->input->get_post('twilio_token');
            if ($twilio_token=="" ) {
                $res['message'] = "Please Enter Re-Inspections Allowed!";
            } else {
                if ($this->utility_model->get('sys_config', array('code'=>'twilio_token'))) {
                    if ($this->utility_model->update('sys_config', array('value'=>$twilio_token), array('code'=>'twilio_token'))) {
                        $res['message'] = "Success";
                        $res['code'] = 0;
                    } else {
                        $res['message'] = "Failed to Update";
                    }
                } else {
                    if ($this->utility_model->insert('sys_config', array('code'=>'twilio_token', 'value'=>$twilio_token))) {
                        $res['message'] = "Success";
                        $res['code'] = 0;
                    } else {
                        $res['message'] = "Failed to Update";
                    }
                }
            }
            
            $twilio_phone1 = $this->input->get_post('twilio_phone1');
            if ($twilio_phone1=="" ) {
                $res['message'] = "Please Enter Re-Inspections Allowed!";
            } else {
                if ($this->utility_model->get('sys_config', array('code'=>'twilio_phone1'))) {
                    if ($this->utility_model->update('sys_config', array('value'=>$twilio_phone1), array('code'=>'twilio_phone1'))) {
                        $res['message'] = "Success";
                        $res['code'] = 0;
                    } else {
                        $res['message'] = "Failed to Update";
                    }
                } else {
                    if ($this->utility_model->insert('sys_config', array('code'=>'twilio_phone1', 'value'=>$twilio_phone1))) {
                        $res['message'] = "Success";
                        $res['code'] = 0;
                    } else {
                        $res['message'] = "Failed to Update";
                    }
                }
            }
            
            $twilio_reply_text = $this->input->get_post('twilio_reply_text');
            if ($twilio_reply_text=="" ) {
                $res['message'] = "Please Enter Re-Inspections Allowed!";
            } else {
                if ($this->utility_model->get('sys_config', array('code'=>'twilio_reply_text'))) {
                    if ($this->utility_model->update('sys_config', array('value'=>$twilio_reply_text), array('code'=>'twilio_reply_text'))) {
                        $res['message'] = "Success";
                        $res['code'] = 0;
                    } else {
                        $res['message'] = "Failed to Update";
                    }
                } else {
                    if ($this->utility_model->insert('sys_config', array('code'=>'twilio_reply_text', 'value'=>$twilio_reply_text))) {
                        $res['message'] = "Success";
                        $res['code'] = 0;
                    } else {
                        $res['message'] = "Failed to Update";
                    }
                }
            }
            
            $twilio_send_text = $this->input->get_post('twilio_send_text');
            if ($twilio_send_text=="" ) {
                $res['message'] = "Please Enter Re-Inspections Allowed!";
            } else {
                if ($this->utility_model->get('sys_config', array('code'=>'twilio_send_text'))) {
                    if ($this->utility_model->update('sys_config', array('value'=>$twilio_send_text), array('code'=>'twilio_send_text'))) {
                        $res['message'] = "Success";
                        $res['code'] = 0;
                    } else {
                        $res['message'] = "Failed to Update";
                    }
                } else {
                    if ($this->utility_model->insert('sys_config', array('code'=>'twilio_send_text', 'value'=>$twilio_send_text))) {
                        $res['message'] = "Success";
                        $res['code'] = 0;
                    } else {
                        $res['message'] = "Failed to Update";
                    }
                }
            }
            
            $checklist_online_link = $this->input->get_post('checklist_online_link');
            if ($checklist_online_link=="" ) {
                $res['message'] = "Please Enter Re-Inspections Allowed!";
            } else {
                if ($this->utility_model->get('sys_config', array('code'=>'checklist_online_link'))) {
                    if ($this->utility_model->update('sys_config', array('value'=>$checklist_online_link), array('code'=>'checklist_online_link'))) {
                        $res['message'] = "Success";
                        $res['code'] = 0;
                    } else {
                        $res['message'] = "Failed to Update";
                    }
                } else {
                    if ($this->utility_model->insert('sys_config', array('code'=>'checklist_online_link', 'value'=>$checklist_online_link))) {
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
    public function update_holidays() {
        $res = array('code'=>-1, 'message'=>'Failed');

        if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
            $jsondata = $this->input->get_post('jsondata');
            if ($jsondata=="") {
                $res['message'] = "Fail to Update";
            } else {
                $myArray = json_decode($jsondata, true);
                $res['message'] = "Success";
                $res['code'] = 0;
                $res['updates'] = array();
                for ($i=0; $i < count($myArray); $i++) {
                  $row = $myArray[$i];
                  $id = $row['id'];
                  $valid = $row['valid'];
                  if ($this->utility_model->update('sys_config_holiday', array('valid'=>$valid), array('id'=>$id))) {
                      $row['success'] = 1;
                      $res['updates'][] = $row;
                  } else {
                    $row['success'] = 0;
                      $res['updates'][] = $row;
                  }
                }
            }
        }

        print_r(json_encode($res));
    }

    public function update_energy_inspection() {
        $res = array('code'=>-1, 'message'=>'Failed');

        if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
            $house_pressure = $this->input->get_post('house_pressure');
            $app_home_message1 = $this->input->get_post('app_home_message1');
            if ($house_pressure=="" || !is_numeric($house_pressure)) {
                $res['message'] = "Please Enter House Pressure";
            }else if ($app_home_message1=="" ) {
                $res['message'] = "Please Enter Message for Android Home Page";
            } else {
                $passed_cnt = 0;

                $this->utility_model->start();
                if ($this->utility_model->get('sys_energy_inspection', array('code'=>'house_pressure'))) {
                    if ($this->utility_model->update('sys_energy_inspection', array('value'=>$house_pressure), array('code'=>'house_pressure'))) {
                        $passed_cnt++;
                    }
                } else {
                    if ($this->utility_model->insert('sys_energy_inspection', array('code'=>'house_pressure', 'value'=>$house_pressure))) {
                        $passed_cnt++;
                    }
                }

                if ($this->utility_model->get('sys_energy_inspection', array('code'=>'app_home_message1'))) {
                    if ($this->utility_model->update('sys_energy_inspection', array('value'=>$app_home_message1), array('code'=>'app_home_message1'))) {
                        $passed_cnt++;
                    }
                } else {
                    if ($this->utility_model->insert('sys_energy_inspection', array('code'=>'app_home_message1', 'value'=>$app_home_message1))) {
                        $passed_cnt++;
                    }
                }
                if($passed_cnt == 2){
                  $this->utility_model->complete();
                  $res['message'] = "Success";
                  $res['code'] = 0;
                }else{
                  $res['message'] = "Failed to Update";
                }
            }
        }

        print_r(json_encode($res));
    }

    public function energy_inspection() {
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission')!='1') {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $page_data['page_name'] = 'admin_energy_inspection';

        $row = $this->utility_model->get('sys_energy_inspection', array('code'=>'house_pressure'));
        if ($row) {
            $page_data['house_pressure'] = $row['value'];
        } else {
            $page_data['house_pressure'] = 50.0;
        }
        $row = $this->utility_model->get('sys_energy_inspection', array('code'=>'app_home_message1'));
        if ($row) {
            $page_data['app_home_message1'] = $row['value'];
        } else {
            $page_data['app_home_message1'] = "call .blablabla for assistance";
        }
// print_r(json_encode($page_data));

        $this->load->view('admin_energy_inspection', $page_data);
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
