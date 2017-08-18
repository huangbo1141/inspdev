<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Payable extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        
        $this->load->model('utility_model');
        $this->load->model('datatable_model');
        
        $this->load->library('excel');
    }
    
    public function index() {
        if (!$this->session->userdata('user_id')) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }
        
        redirect(base_url() . "payable/inspector_payroll.html");
    }


    public function inspector_payroll() {
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission')!='1') {
            redirect(base_url() . "user/login.html");
            exit(1);
        }
        
        $page_data = array();
        
        $page_data['page_name'] = 'inspector_payroll';
        
        $start_time = "";
        $end_time = "";
        
        $current_time = time();
//        $current_time = strtotime("2017-03-11");

        $ranges = array();
        
        $day = intval(date('j', $current_time));
        if ($day<=15) {
            $end_time = date('Y-m-d', strtotime(date('Y-m', $current_time) . "-01") - 1);
            $start_time = date('Y-m', strtotime("-1 month", $current_time)) . "-16";
            array_push($ranges, array('start'=>$start_time, 'end'=>$end_time));

            $end_time = date('Y-m', strtotime($start_time)) . "-15";
            $start_time = date('Y-m', strtotime($start_time)) . "-01";
            array_push($ranges, array('start'=>$start_time, 'end'=>$end_time));
        } else {
            $start_time = date('Y-m', $current_time) . "-01";
            $end_time = date('Y-m', $current_time) . "-15";
            array_push($ranges, array('start'=>$start_time, 'end'=>$end_time));
        }
        
        for ($i=1; $i<=6; $i++) {
            $end_time = date('Y-m', strtotime("-1 month", strtotime($start_time))) . "-15";
            $start_time = date('Y-m-d', strtotime("-1 month", strtotime($start_time)));
            $temp = array('start'=>$start_time, 'end'=>$end_time);
            
            $end_time = date('Y-m-d', strtotime("-1 day", strtotime("+1 month", strtotime($start_time))));
            $start_time = date('Y-m', strtotime($start_time)) . "-16";

            array_push($ranges, array('start'=>$start_time, 'end'=>$end_time));
            array_push($ranges, $temp);
            
            $start_time = $temp['start'];
        }
        
        $page_data['range'] = $ranges;
        
        $this->load->view('inspector_payroll', $page_data);
    }

    public function load_inspector_payroll(){
        $response = array('code'=>1, 'message'=>'No Permission');
        
        if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
            $range_start = $this->input->get_post('start');
            $range_end = $this->input->get_post('end');

            $table = "ins_user a "
                    . " left join ins_inspector_payroll p on p.inspector_id=a.id and p.start_date='$range_start' and p.end_date='$range_end' ";
            
            $sql = " select  a.id, concat(a.first_name, ' ', a.last_name) as inspector_name, a.email, a.phone_number, a.address, a.fee,"
                    . " '' as check_number, "
                    . " ( select count(*) from ins_inspection where user_id=a.id and start_date>='$range_start' and end_date<='$range_end' ) as inspection_count, "
                    . " p.id as payroll_id, p.check_number as payroll_number, p.check_amount as payroll_amount, p.status as payroll_status "
                    . " from " . $table . "  ";

            $sql .= " order by a.first_name asc ";
            $data = $this->datatable_model->get_content($sql);
            if ($data) {
                $result = array();
                
                foreach ($data as $row) {
                    $row['check_amount'] = intval($row['inspection_count']) * floatval($row['fee']);
                    if (isset($row['payroll_id']) && $row['payroll_id']!="") {
                        $row['check_number'] = $row['payroll_number'];
                        $row['check_amount'] = $row['payroll_amount'];
                    }
                    
                    array_push($result, $row);
                }
                
                $response['result'] = $result;
                
                $response['message'] = "Success";
                $response['code'] = 0;
            } else {
                $response['message'] = "No Inspector";
            }
        }

        print_r(json_encode($response));
    }
    
    public function submit_inspector_payroll() {
        $response = array('code'=>1, 'message'=>'No Permission');
        
        if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
            $start_date = $this->input->get_post('start');
            $end_date = $this->input->get_post('end');
            $data = $this->input->get_post('data');
            
            if ($start_date=="" || $end_date=="" || $data=="") {
                $response['message'] = "Bad Request";
            } else {
                $inspectors = json_decode($data, true);
                if ($inspectors===false) {
                    $response['message'] = "Bad Request";
                    
                } else {
                    $t = mdate('%Y%m%d%H%i%s', time());
                    $is_validate = true;
                    $validate_message = "";
                    
                    foreach ($inspectors as $row) {
                        $name = $row['name'];
                        $number = $row['number'];
                        $amount = $row['amount'];
                        $count = $row['count'];
                        
                        if ($amount!="" && doubleval($amount)<0) {
                            $is_validate = false;
                            $validate_message = $name . "'s Check Amount cannot be negative number!";
                            break;
                        }
                        
//                        if ($number!="" && intval($number)<0) {
//                            $is_validate = false;
//                            $validate_message = $name . "'s Check Number cannot be negative number!";
//                            break;
//                        }
                        
//                        if ($amount!="") {
//                            if (doubleval($amount)>0) {
//                                if ($number=="" || intval($number)==0) {
//                                    $is_validate = false;
//                                    $validate_message = $name . "'s Check Number cannot be zero or empty!";
//                                    break;
//                                }
//                            }
//                        }
                    }
                    
                    if ($is_validate) {
                        $has_payment = false;
                        foreach ($inspectors as $row) {
                            $number = $row['number'];
                            $amount = $row['amount'];
                            $count = $row['count'];
                            
                            if ($amount!="" && doubleval($amount)>0) {
                                $has_payment = true;
                                $payroll_id = strtotime($start_date) . "_" . strtotime($end_date) . "_" . $row['id'];
                                
                                $record = array(
                                    'inspector_id'=>$row['id'],
                                    'start_date'=>$start_date,
                                    'end_date'=>$end_date,
                                    'inspector_name'=>$row['name'],
                                    'inspector_email'=>$row['email'],
                                    'inspector_phone'=>$row['phone'],
                                    'inspector_address'=>$row['address'],
                                    'check_amount'=> doubleval( number_format(doubleval($amount), 2) ),
                                    'check_number'=>$number,
                                    'inspection_count'=>intval($count),
                                    'transaction_date'=>date('Y-m-d', time()),
                                    'updated_at'=>$t
                                );

                                $payroll = $this->utility_model->get('ins_inspector_payroll', array('id'=>$payroll_id));
                                if ($payroll)  {
                                    $this->utility_model->update('ins_inspector_payroll', $record, array('id'=>$payroll_id));
                                } else {
                                    $record['id'] = $payroll_id;
                                    $record['created_at'] = $t;
                                    $this->utility_model->insert('ins_inspector_payroll', $record);
                                }
                                
                            }
                        }
                        
                        if ($has_payment) {
                            $response['message'] = "Success";
                            $response['code'] = 0;
                        } else {
                            $response['message'] = "Inspectors have not fee or not submitted any inspections!";
                        }
                    } 
                    else {
                        $response['message'] = $validate_message;
                    }
                }
            }
        }

        print_r(json_encode($response));
    }

    public function get_payroll(){
        $response = array('code'=>1, 'message'=>'No Permission');
        
        if ($this->session->userdata('user_id')) {
            $id = $this->input->get_post('id');
            if ($id=="") {
                $response['message'] = "Bad Request";
            } else {
                $payroll = $this->utility_model->get('ins_inspector_payroll', array('id'=>$id));
                if ($payroll) {
                    $response['result'] = $payroll;

                    $response['message'] = "Success";
                    $response['code'] = 0;
                } else {
                    $response['message'] = "Invalid Check";
                }
            }
        }

        print_r(json_encode($response));
    }


    public function inspector_payment() {
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission')!='1') {
            redirect(base_url() . "user/login.html");
            exit(1);
        }
        
        $page_data = array();
        
        $page_data['page_name'] = 'inspector_payment';
        
        $current_time = time();
        
        $start_time = date('Y-m-d', strtotime("-6 month", $current_time));
        $end_time = date('Y-m-d', $current_time);
        
        $page_data['start_date'] = $start_time;
        $page_data['end_date'] = $end_time;
        
        $page_data['inspector'] = $this->utility_model->get_list('ins_user', array());
        $page_data['period'] = $this->utility_model->get_list__by_sql(" select start_date, end_date from ins_inspector_payroll group by start_date, end_date order by start_date desc ");
        
        $this->load->view('inspector_payment', $page_data);
    }

    public function load_inspector_payment(){
        $cols = array("a.inspector_name", "a.inspector_email", "a.inspector_phone", "a.inspector_address", "a.start_date", "a.check_amount", "a.check_number", "a.transaction_date");
        $table = "ins_inspector_payroll a"; 
        
        $result = array();
        
        $amount = 10;
        $start = 0;
        $col = 7;
	 
	$dir = "asc";
        
        $inspector = $this->input->get_post('inspector');
        $period = $this->input->get_post('period');
        $start_date = $this->input->get_post('start_date');
        $end_date = $this->input->get_post('end_date');
//        $status = $this->input->get_post('status');
        
        $filter_sql = "";
        if ($inspector!="") {
            if ($filter_sql!="") {
                $filter_sql .= " and ";
            }
            
            $filter_sql .= " a.inspector_id='$inspector' ";
        }
        
        if ($period!="") {
            if ($filter_sql!="") {
                $filter_sql .= " and ";
            }
            
            $filter_sql .= " a.start_date='$period' ";
        }
        
        if ($start_date!="" || $end_date!="") {
            if ($filter_sql!="") {
                $filter_sql .= " and ";
            }
            
            $date_sql = " ( a.transaction_date is null or a.transaction_date='' or ";
            if ($start_date!="" && $end_date!="") {
                $date_sql .= " ( a.transaction_date>='" . $start_date . "' and a.transaction_date<='" . $end_date . "' ) ";
            } else if ($start_date!="") {
                $date_sql .= " a.transaction_date>='" . $start_date . "' ";
            } else {
                $date_sql .= " a.transaction_date<='" . $end_date . "' ";
            }
            $date_sql .= " ) ";
            
            $filter_sql .= $date_sql;
        }
        
//        if ($status!="") {
//            if ($filter_sql!="") {
//                $filter_sql .= " and ";
//            }
//            
//            $filter_sql .= " a.status='$status' ";
//        }
        
        if ($filter_sql!="") {
            $table .= " where " . $filter_sql;
        }
        
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
            if ($col<0 || $col>7){
                $col=7;
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
        
        $sql = " select  a.*, '' as additional from " . $table . "  ";
        
        $searchSQL = "";
        $globalSearch = " ( "
                . " a.inspector_name like '%" . $searchTerm . "%' or "
                . " a.inspector_email like '%" . $searchTerm . "%' or  "
                . " a.inspector_phone like '%" . $searchTerm . "%' or  "
                . " a.inspector_address like '%" . $searchTerm . "%' or  "
                . " a.start_date like '%" . $searchTerm . "%' or  "
                . " a.end_date like '%" . $searchTerm . "%' or  "
                . " a.transaction_date like '%" . $searchTerm . "%'  "
                . " ) ";
        
        if ($searchTerm && strlen($searchTerm)>0){
            $searchSQL .= ( $filter_sql!="" ? "and" : "where") . $globalSearch;
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
        
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission')!='1') {
            
        } else {
            $result["recordsTotal"] = $total;
            $result["recordsFiltered"] = $totalAfterFilter;
            $result["data"] = $data;
        }
        
        print_r(json_encode($result));
    }
    
    public function process_inspector_payment() {
        $response = array('code'=>1, 'message'=>'No Permission');
        
        if ($this->session->userdata('user_id')) {
            $id = $this->input->get_post('id');
            $date = $this->input->get_post('date');
            
            if ($id=="" || $date=="") {
                $response['message'] = "Bad Request";
            } else {
                $t = mdate('%Y%m%d%H%i%s', time());
                if ($id=="all") {
                    $inspector = $this->input->get_post('inspector');
                    $period = $this->input->get_post('period');
                    $start_date = $this->input->get_post('start_date');
                    $end_date = $this->input->get_post('end_date');
                    $status = $this->input->get_post('status');

                    $table = "ins_inspector_payroll a"; 

                    $filter_sql = "";
                    if ($inspector!="") {
                        if ($filter_sql!="") {
                            $filter_sql .= " and ";
                        }

                        $filter_sql .= " a.inspector_id='$inspector' ";
                    }

                    if ($period!="") {
                        if ($filter_sql!="") {
                            $filter_sql .= " and ";
                        }

                        $filter_sql .= " a.start_date='$period' ";
                    }

                    if ($start_date!="" || $end_date!="") {
                        if ($filter_sql!="") {
                            $filter_sql .= " and ";
                        }

                        $date_sql = " ( a.transaction_date is null or a.transaction_date='' or ";
                        if ($start_date!="" && $end_date!="") {
                            $date_sql .= " ( a.transaction_date>='" . $start_date . "' and a.transaction_date<='" . $end_date . "' ) ";
                        } else if ($start_date!="") {
                            $date_sql .= " a.transaction_date>='" . $start_date . "' ";
                        } else {
                            $date_sql .= " a.transaction_date<='" . $end_date . "' ";
                        }
                        $date_sql .= " ) ";

                        $filter_sql .= $date_sql;
                    }

                    if ($status!="") {
                        if ($filter_sql!="") {
                            $filter_sql .= " and ";
                        }

                        $filter_sql .= " a.status='$status' ";
                    }

                    if ($filter_sql!="") {
                        $table .= " where " . $filter_sql;
                    }

                    $sql = " select  a.* from " . $table . "  ";
                    
                    $payrolls = $this->utility_model->get_list__by_sql($sql);
                    if ($payrolls) {
                        $is_proceed = false;
                        foreach ($payrolls as $row) {
                            if ($row['status']==0) {
                                if ($this->utility_model->update('ins_inspector_payroll', array('transaction_date'=>$date, 'status'=>1, 'updated_at'=>$t), array('id'=>$row['id']))) {
                                    $is_proceed = true;
                                }
                            }
                        }
                        
                        if ($is_proceed) {
                            $response['message'] = "Success";
                            $response['code'] = 0;
                        } else {
                            $response['message'] = "Already PAID";
                        }
                    } else {
                        $response['message'] = "Invalid Check";
                    }
                } else {
                    $payroll = $this->utility_model->get('ins_inspector_payroll', array('id'=>$id));
                    if ($payroll) {
                        if ($payroll['status']==0) {
                            if ($this->utility_model->update('ins_inspector_payroll', array('transaction_date'=>$date, 'status'=>1, 'updated_at'=>$t), array('id'=>$id))) {
                                $response['message'] = "Success";
                                $response['code'] = 0;
                            } else {
                                $response['message'] = "Failed to proceed";
                            }
                        } else {
                            $response['message'] = "Already PAID";
                        }
                    } else {
                        $response['message'] = "Invalid Check";
                    }
                }
            }
        }

        print_r(json_encode($response));
    }

    public function save_inspector_payment() {
        $response = array('code'=>1, 'message'=>'No Permission');
        
        if ($this->session->userdata('user_id')) {
            $id = $this->input->get_post('id');
            $amount = $this->input->get_post('amount');
            $number = $this->input->get_post('number');
            $date = $this->input->get_post('date');
            
            if ($id=="" || $date=="" || $amount=="" || $number=="") {
                $response['message'] = "Bad Request";
            } else {
                $payroll = $this->utility_model->get('ins_inspector_payroll', array('id'=>$id));
                if ($payroll) {
                    $t = mdate('%Y%m%d%H%i%s', time());
                    
//                    if ($payroll['status']==1) {
                        if ($this->utility_model->update('ins_inspector_payroll', array('transaction_date'=>$date, 'check_amount'=>$amount, 'check_number'=>$number, 'updated_at'=>$t), array('id'=>$id))) {
                            $response['message'] = "Success";
                            $response['code'] = 0;
                        } else {
                            $response['message'] = "Failed to proceed";
                        }
//                    } else {
//                        $response['message'] = "Not Paid Yet";
//                    }
                } else {
                    $response['message'] = "Invalid Check";
                }
            }
        }

        print_r(json_encode($response));
    }
    
    public function cancel_inspector_payment() {
        $response = array('code'=>1, 'message'=>'No Permission');
        
        if ($this->session->userdata('user_id')) {
            $id = $this->input->get_post('id');
            
            if ($id=="") {
                $response['message'] = "Bad Request";
            } else {
                $payroll = $this->utility_model->get('ins_inspector_payroll', array('id'=>$id));
                if ($payroll) {
                    $t = mdate('%Y%m%d%H%i%s', time());
                    
//                    if ($payroll['status']==1) {
                        if ($this->utility_model->delete('ins_inspector_payroll', array('id'=>$id))) {
                            $response['message'] = "Success";
                            $response['code'] = 0;
                        } else {
                            $response['message'] = "Failed to cancel";
                        }
//                    } else {
//                        $response['message'] = "Not Paid Yet";
//                    }
                } else {
                    $response['message'] = "Invalid Check";
                }
            }
        }

        print_r(json_encode($response));
    }
    
    
    public function received_check() {
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission')!='1') {
            redirect(base_url() . "user/login.html");
            exit(1);
        }
        
        $page_data = array();
        $page_data['page_name'] = 'received_check';
        
        $current_time = time();
        
        $start_time = date('Y-m-d', strtotime("-3 month", $current_time));
        $end_time = date('Y-m-d', $current_time);
        
        $page_data['start_date'] = $start_time;
        $page_data['end_date'] = $end_time;
        
        $page_data['builder'] = $this->utility_model->get_list('ins_builder', array());
        
        $this->load->view('received_check', $page_data);
    }

    public function get_check(){
        $response = array('code'=>1, 'message'=>'No Permission');
        
        if ($this->session->userdata('user_id')) {
            $id = $this->input->get_post('id');
            if ($id=="") {
                $response['message'] = "Bad Request";
            } else {
                $check = $this->utility_model->get('ins_builder_check', array('id'=>$id));
                if ($check) {
                    $response['result'] = $check;

                    $response['message'] = "Success";
                    $response['code'] = 0;
                } else {
                    $response['message'] = "Invalid Check";
                }
            }
        }

        print_r(json_encode($response));
    }

    public function save_check() {
        $response = array('code'=>1, 'message'=>'No Permission');
        
        if ($this->session->userdata('user_id')) {
            $id = $this->input->get_post('id');
            $amount = $this->input->get_post('amount');
            $number = $this->input->get_post('number');
            $date = $this->input->get_post('date');
            $builder = $this->input->get_post('builder');
            
            if ($date=="" || $amount=="" || $number=="" || $builder=="") {
                $response['message'] = "Bad Request";
            } else {
                $t = mdate('%Y%m%d%H%i%s', time());
                
                if (doubleval($amount)<=0) {
                    $response['message'] = "Check Amount cannot be Zero or negative!";
                } else {
//                    if (intval($number)<=0) {
//                        $response['message'] = "Check Number cannot be Zero or negative!";
//                    } else {
                        if ($id=="") {
                            if ($this->utility_model->insert('ins_builder_check', array('check_date'=>$date, 'check_amount'=>$amount, 'check_number'=>$number, 'builder'=>$builder, 'created_at'=>$t, 'updated_at'=>$t))) {
                                $response['message'] = "Success";
                                $response['code'] = 0;
                            } else {
                                $response['message'] = "Failed to Add";
                            }
                        } else {
                            $check = $this->utility_model->get('ins_builder_check', array('id'=>$id));
                            if ($check) {
                                if ($this->utility_model->update('ins_builder_check', array('check_date'=>$date, 'check_amount'=>$amount, 'check_number'=>$number, 'builder'=>$builder, 'updated_at'=>$t), array('id'=>$id))) {
                                    $response['message'] = "Success";
                                    $response['code'] = 0;
                                } else {
                                    $response['message'] = "Failed to Update";
                                }
                            } else {
                                $response['message'] = "Invalid Check";
                            }
                        }
//                    }
                }
            }
        }

        print_r(json_encode($response));
    }

    public function load_received_check(){
        $cols = array("a.check_date", "a.builder", "a.check_number", "a.check_amount");
        $table = " ins_builder_check a "
                . " left join ins_builder b on b.id=a.builder "
                . " where a.check_amount>=0 "; 
        
        $result = array();
        
        $amount = 10;
        $start = 0;
        $col = 0;
	 
	$dir = "desc";
        
        $builder = $this->input->get_post('builder');
        $start_date = $this->input->get_post('start_date');
        $end_date = $this->input->get_post('end_date');
        
        $filter_sql = "";
        if ($builder!="") {
            if ($filter_sql!="") {
                $filter_sql .= " and ";
            }
            
            $filter_sql .= " a.builder='$builder' ";
        }
        
        if ($start_date!="" || $end_date!="") {
            if ($filter_sql!="") {
                $filter_sql .= " and ";
            }
            
            $date_sql = "";
            if ($start_date!="" && $end_date!="") {
                $date_sql .= " ( a.check_date>='" . $start_date . "' and a.check_date<='" . $end_date . "' ) ";
            } else if ($start_date!="") {
                $date_sql .= " a.check_date>='" . $start_date . "' ";
            } else {
                $date_sql .= " a.check_date<='" . $end_date . "' ";
            }
            
            $filter_sql .= $date_sql;
        }
        
        if ($filter_sql!="") {
            $table .= " and " . $filter_sql;
        }
        
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
            if ($sdir!="desc"){
                $dir="asc";
            }
        }
        
        $colName = $cols[$col];
        $total = 0;
        $totalAfterFilter = 0;
        
        $sql = " select count(*) from " . $table ;
        $total = $this->datatable_model->get_count($sql);
        $totalAfterFilter = $total;
        
        $sql = " select  a.*, b.name as builder_name, '' as additional from " . $table . "  ";
        
        $searchSQL = "";
        $globalSearch = " ( "
                . " a.check_date like '%" . $searchTerm . "%' or "
                . " b.name like '%" . $searchTerm . "%' "
                . " ) ";
        
        if ($searchTerm && strlen($searchTerm)>0){
            $searchSQL .= " and " . $globalSearch;
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
        
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission')!='1') {
            
        } else {
            $result["recordsTotal"] = $total;
            $result["recordsFiltered"] = $totalAfterFilter;
            $result["data"] = $data;
        }
        
        print_r(json_encode($result));
    }

    public function upload_check() {
        $response = array('code' => 1, 'message' => 'No Permission!');

        if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
            $dir_name = "resource/upload/";

            $this->load->library('uuid');        
            $this->load->helper('csv');
            
            if (isset($_FILES['file']) && isset($_FILES['file']['name'])) {
                $uu_id = $this->uuid->v4();
                $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                
                $fname = mdate('%Y%m%d%H%i%s', time()) . "_" . $uu_id . "." . $ext;
                $new_name = $dir_name . $fname;
                
                if (move_uploaded_file($_FILES['file']['tmp_name'], $new_name)) {
                    $checks = csv_to_array($new_name);
                    
                    $f_date = "";
                    $f_number = "";
                    $f_amount = "";
                    $f_builder = "";
                    
                    $t = mdate('%Y%m%d%H%i%s', time());
                    
                    foreach ($checks as $row) {
                        if ($f_date=="" && isset($row['Check Date'])) {
                            $f_date = "Check Date";
                        }
                        if ($f_date=="" && isset($row['check date'])) {
                            $f_date = "check date";
                        }
                        if ($f_date=="" && isset($row['Check date'])) {
                            $f_date = "Check date";
                        }
                        if ($f_date=="" && isset($row['check Date'])) {
                            $f_date = "check Date";
                        }
                        if ($f_date=="" && isset($row['CheckDate'])) {
                            $f_date = "CheckDate";
                        }
                        
                        if ($f_builder=="" && isset($row['builder'])) {
                            $f_builder = "builder";
                        }
                        if ($f_builder=="" && isset($row['Builder'])) {
                            $f_builder = "Builder";
                        }
                        
                        if ($f_number=="" && isset($row['Check Number'])) {
                            $f_number = "Check Number";
                        }
                        if ($f_number=="" && isset($row['Check number'])) {
                            $f_number = "Check number";
                        }
                        if ($f_number=="" && isset($row['check Number'])) {
                            $f_number = "check Number";
                        }
                        if ($f_number=="" && isset($row['check number'])) {
                            $f_number = "check number";
                        }
                        if ($f_number=="" && isset($row['CheckNumber'])) {
                            $f_number = "CheckNumber";
                        }
                        
                        if ($f_amount=="" && isset($row['Check Amount'])) {
                            $f_amount = "Check Amount";
                        }
                        if ($f_amount=="" && isset($row['Check amount'])) {
                            $f_amount = "Check amount";
                        }
                        if ($f_amount=="" && isset($row['check Amount'])) {
                            $f_amount = "check Amount";
                        }
                        if ($f_amount=="" && isset($row['check amount'])) {
                            $f_amount = "check amount";
                        }
                        if ($f_amount=="" && isset($row['CheckAmount'])) {
                            $f_amount = "CheckAmount";
                        }
                        
                        $data = array(
                            'check_date'=>date('Y-m-d', time()),
                            'builder'=>0,
                            'check_number'=>$row[$f_number],
                            'check_amount'=>$row[$f_amount],
                            'created_at'=>$t,
                            'updated_at'=>$t
                        );
                        
                        if ($f_date!="" && $row[$f_date]!="") {
                             $data['check_date'] = date('Y-m-d', strtotime($row[$f_date]));
                        }
                        
                        $builder = $this->utility_model->get("ins_builder", array('name'=>$row[$f_builder]));
                        if ($builder) {
                            $data['builder'] = $builder['id'];
                        }
                        
                        $this->utility_model->insert("ins_builder_check", $data);
                    }
                    
                    $response['message'] = "Success";
                    $response['code'] = 0;
                    
                    unlink($new_name);
                } else {
                    $response['message'] = "Failed to upload";
                }
            }
        }
        
        print_r(json_encode($response));
    }
    
    public function delete_check() {
        $response = array('code'=>1, 'message'=>'No Permission');
        
        if ($this->session->userdata('user_id')) {
            $id = $this->input->get_post('id');
            
            if ($id=="") {
                $response['message'] = "Bad Request";
            } else {
                $check = $this->utility_model->get('ins_builder_check', array('id'=>$id));
                if ($check) {
                    if ($this->utility_model->delete('ins_builder_check', array('id'=>$id))) {
                        $response['message'] = "Success";
                        $response['code'] = 0;
                    } else {
                        $response['message'] = "Failed to Delete";
                    }
                } else {
                    $response['message'] = "Invalid Check";
                }
            }
        }

        print_r(json_encode($response));
    }

    
    public function record_payment_received() {
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission')!='1') {
            redirect(base_url() . "user/login.html");
            exit(1);
        }
        
        $page_data = array();
        $page_data['page_name'] = 'record_payment_received';
        
        $current_time = time();
        
        $start_time = date('Y-m-d', strtotime("-2 month", $current_time));
        $end_time = date('Y-m-d', $current_time);
        
        $page_data['start_date'] = $start_time;
        $page_data['end_date'] = $end_time;

        $page_data['builder'] = $this->utility_model->get_list('ins_builder', array());
        
        $this->load->view('record_payment_received', $page_data);
    }
    
    public function upload_record_payment_received() {
        $response = array('code' => 1, 'message' => 'No Permission!');

        if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
            $dir_name = "resource/upload/";
            $this->load->library('uuid');        
            
            if (isset($_FILES['file']) && isset($_FILES['file']['name'])) {
                $uu_id = $this->uuid->v4();
                $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                
                $fname = mdate('%Y%m%d%H%i%s', time()) . "_" . $uu_id . "." . $ext;
                $new_name = $dir_name . $fname;
                
                if (move_uploaded_file($_FILES['file']['tmp_name'], $new_name)) {
                    $t = mdate('%Y%m%d%H%i%s', time());
                    
                    $objPHPExcel = PHPExcel_IOFactory::load($new_name);
                    $sheetData = $objPHPExcel->getActiveSheet()->toArray("",true,true,true);
                    
                    $is_payment = true;
                    $fields = array();
                    
                    $record_payment = array();
                    $invoices = array();
                    
                    foreach ($sheetData as $row) {
                        if ($is_payment && ($row['Q']!="" && $row['P']!="" && $row['O']!="" && $row['N']!="" && $row['M']!="" && $row['L']!="" && $row['K']!="" && $row['J']!="" && $row['I']!="" && $row['H']!="" && $row['G']!="" && $row['F']!="" && $row['E']!="" && $row['D']!="" && $row['C']!="" && $row['B']!="" && $row['A']!="")) {
                            $is_payment = false;
                                
                            $xls_header = array(
                                array('field'=>'A', 'value'=>$row['A']),
                                array('field'=>'B', 'value'=>$row['B']),
                                array('field'=>'C', 'value'=>$row['C']),
                                array('field'=>'D', 'value'=>$row['D']),
                                array('field'=>'E', 'value'=>$row['E']),
                                array('field'=>'F', 'value'=>$row['F']),
                                array('field'=>'G', 'value'=>$row['G']),
                                array('field'=>'H', 'value'=>$row['H']),
                                array('field'=>'I', 'value'=>$row['I']),
                                array('field'=>'J', 'value'=>$row['J']),
                                array('field'=>'K', 'value'=>$row['K']),
                                array('field'=>'L', 'value'=>$row['L']),
                                array('field'=>'M', 'value'=>$row['M']),
                                array('field'=>'N', 'value'=>$row['N']),
                                array('field'=>'O', 'value'=>$row['O']),
                                array('field'=>'P', 'value'=>$row['P']),
                                array('field'=>'Q', 'value'=>$row['Q']),
                            );
                            
                            $cell = $this->excel_find_cell(array('invoice', "#"), $xls_header);
                            if ($cell!==false) {
                                $fields['invoice_number'] = $cell;
                            } 
                            
                            $cell = $this->excel_find_cell(array('invoice', "description"), $xls_header);
                            if ($cell!==false) {
                                $fields['invoice_description'] = $cell;
                            } 
                            
                            $cell = $this->excel_find_cell(array('discount', "amount"), $xls_header);
                            if ($cell!==false) {
                                $fields['discount_amount'] = $cell;
                            } 
                            
                            $cell = $this->excel_find_cell(array('invoice', "amount"), $xls_header);
                            if ($cell!==false) {
                                $fields['invoice_amount'] = $cell;
                            } 
                            
                            $cell = $this->excel_find_cell(array('invoice', "date"), $xls_header);
                            if ($cell!==false) {
                                $fields['invoice_date'] = $cell;
                            } 
                            
                            $cell = $this->excel_find_cell(array('community'), $xls_header);
                            if ($cell!==false) {
                                $fields['community'] = $cell;
                            } 
                            
                            $cell = $this->excel_find_cell(array('job', "number"), $xls_header);
                            if ($cell!==false) {
                                $fields['job_number'] = $cell;
                            } 
                            
                            $cell = $this->excel_find_cell(array('item', "description", "address"), $xls_header);
                            if ($cell!==false) {
                                $fields['address'] = $cell;
                            } 
                            
                            $cell = $this->excel_find_cell(array('option', "number"), $xls_header);
                            if ($cell!==false) {
                                $fields['option_number'] = $cell;
                            } 
                            
                            $cell = $this->excel_find_cell(array('invoice', "line", "amount"), $xls_header);
                            if ($cell!==false) {
                                $fields['line_amount'] = $cell;
                            } 
                            
                            $cell = $this->excel_find_cell(array('account', "category"), $xls_header);
                            if ($cell!==false) {
                                $fields['account_category'] = $cell;
                            } 
                            
                            $cell = $this->excel_find_cell(array('account', "category", "description"), $xls_header);
                            if ($cell!==false) {
                                $fields['category_description'] = $cell;
                            } 
                            
                            $cell = $this->excel_find_cell(array('plan', "name"), $xls_header);
                            if ($cell!==false) {
                                $fields['plan_name'] = $cell;
                            } 
                            
                            $cell = $this->excel_find_cell(array('plan', "number"), $xls_header);
                            if ($cell!==false) {
                                $fields['plan_number'] = $cell;
                            } 
                            
                            $cell = $this->excel_find_cell(array('task', "description"), $xls_header);
                            if ($cell!==false) {
                                $fields['task_description'] = $cell;
                            } 
                            
                            $cell = $this->excel_find_cell(array('start', "date"), $xls_header);
                            if ($cell!==false) {
                                $fields['start_date'] = $cell;
                            } 
                            
                            $cell = $this->excel_find_cell(array('complete', "completed"), $xls_header);
                            if ($cell!==false) {
                                $fields['complete_date'] = $cell;
                            } 
                            
                        } 
                        else if ($is_payment && ($row['A']!="" && $row['B']!="")) {
                            
                            if ($this->excel_find_cell(array("check", "detail"), $row['A'])) {
                                $record_payment['check_details'] = $row['B'];
                            }
                            
                            if ($this->excel_find_cell(array("export", "on"), $row['A'])) {
                                if ($row['B']!="") {
                                    $record_payment['exported_on'] = date('Y-m-d H:i:s', strtotime($row['B']));
                                }
                            }
                            
                            if ($this->excel_find_cell(array("check", "#"), $row['A'])) {
                                $record_payment['check_number'] = preg_replace('/[^0-9]+/', "", $row['B']);
                            }
                            
                            if ($this->excel_find_cell(array("check", "cut"), $row['A'])) {
                                if ($row['B']!="") {
                                    $record_payment['check_cut'] = date('Y-m-d', strtotime($row['B']));
                                }
                            }
                            
                            if ($this->excel_find_cell(array("pay", "to"), $row['A'])) {
                                $record_payment['pay_to'] = $row['B'];
                            }
                            
                            if ($this->excel_find_cell(array("check", "amount"), $row['A'])) {
                                if ($row['B']!="") {
                                    $record_payment['check_amount'] = $this->get_decimal($row['B']);
                                }
                            }
                            
                        } 
                        else if ($is_payment===false) {
                            $data = array();
                            foreach ($fields as $key => $value) {
                                $data[$key] = $row[$value];
                            }
                            
                            if (isset($data['invoice_number']) && $data['invoice_number']!="" && isset($data['job_number']) && $data['job_number']!="") {
                                array_push($invoices, $data);
                            }
                        }
                    }
                    
                    if (isset($record_payment['check_number']) && $record_payment['check_number']!="" && isset($record_payment['check_amount']) && $record_payment['check_amount']!="" && isset($record_payment['check_cut']) && $record_payment['check_cut']!="") {
                        $payment_id = "";
                        
                        $this->utility_model->start();
                        
                        $old_record_payment = $this->utility_model->get('ins_record_payment', array('check_number'=>$record_payment['check_number'], 'check_cut'=>$record_payment['check_cut']));
                        if ($old_record_payment) {
                            $record_payment['updated_at'] = $t;
                            
                            if ($this->utility_model->update('ins_record_payment', $record_payment, array('id'=>$old_record_payment['id']))) {
                                $payment_id = $old_record_payment['id'];
                            }
                        } 
                        else {
                            $record_payment['created_at'] = $t;
                            $record_payment['updated_at'] = $t;
                            
                            if ($this->utility_model->insert('ins_record_payment', $record_payment)) {
                                $payment_id = $this->utility_model->new_id();
                            }
                        }
                        
                        if ($payment_id!="") {
                            $invoice_list = array();
                            
                            foreach ($invoices as $row) {
                                if (isset($row['discount_amount']) && $row['discount_amount']!="") {
                                    $row['discount_amount'] = $this->get_decimal($row['discount_amount']);
                                }
                                if (isset($row['invoice_amount']) && $row['invoice_amount']!="") {
                                    $row['invoice_amount'] = $this->get_decimal($row['invoice_amount']);
                                }
                                if (isset($row['line_amount']) && $row['line_amount']!="") {
                                    $row['line_amount'] = $this->get_decimal($row['line_amount']);
                                }
                                
                                if (isset($row['invoice_date']) && $row['invoice_date']!="") {
                                    $row['invoice_date'] = date('Y-m-d', strtotime($row['invoice_date']));
                                }
                                if (isset($row['start_date']) && $row['start_date']!="") {
                                    $row['start_date'] = date('Y-m-d', strtotime($row['start_date']));
                                }
                                if (isset($row['complete_date']) && $row['complete_date']!="") {
                                    $row['complete_date'] = date('Y-m-d', strtotime($row['complete_date']));
                                }
                                
                                array_push($invoice_list, $row);
                                
                                $repeated_count = $this->find_invoice_number_in_list($invoices, $row['invoice_number']);
                                if ($repeated_count>1) {
                                    $repeated_count = $this->find_invoice_number_in_list($invoice_list, $row['invoice_number']);
                                    $row['invoice_number'] = $row['invoice_number'] . "-" . $repeated_count;
                                } else {
                                }
                                
                                if (isset($row['job_number']) && $row['job_number']!="" && isset($row['account_category']) && $row['account_category']!="") {
                                    $is_building_unit = false;
                                    if (isset($row['address']) && $row['address']!="") {
                                        if (strpos(strtolower($row['address']), "shell bld")!==false) {
                                            $is_building_unit = true;
                                        }
                                    }
                                    
                                    if (isset($row['plan_name']) && $row['plan_name']!="") {
                                        if (strpos(strtolower($row['plan_name']), "shell")!==false) {
                                            $is_building_unit = true;
                                        }
                                    }
                                    
                                    $inspection_type = $this->utility_model->get('ins_code', array('kind'=>'ins', 'account_category'=>$row['account_category']));

                                    if ($is_building_unit) {
                                        $building_unit = $this->utility_model->get_list('ins_building_unit', array('job_number'=>$row['job_number']));
                                        if ($building_unit && count($building_unit)>0) {
                                            $invoice_count = count($building_unit);
                                            $invoice_number = $row['invoice_number'];
                                            $line_amount = doubleval($row['line_amount']);
                                            $unit_no = 1;
                                            
                                            foreach ($building_unit as $unit) {
                                                $row['invoice_number'] = $invoice_number . "-" . $unit_no;
                                                $row['line_amount'] = $line_amount*1.0 / $invoice_count;
                                                $this->add_or_update_invoice($payment_id, $row);
                                                
                                                if ($inspection_type) {
                                                    $this->link_invoice_to_inspection($inspection_type['code'], $row['job_number'], $row['invoice_number'], true, $unit['address']);
                                                }                                                
                                                
                                                $unit_no++;
                                            }
                                        }
                                    } else {
                                        $this->add_or_update_invoice($payment_id, $row);
                                        
                                        if ($inspection_type) {
                                            $this->link_invoice_to_inspection($inspection_type['code'], $row['job_number'], $row['invoice_number']);
                                        }
                                    }
                                }
                            }

                            $this->utility_model->complete();
                            
                            $response['message'] = "Success";
                            $response['code'] = 0;
                        } 
                        else {
                            $response['message'] = "Failed to add record payment";
                        }
                    } 
                    else {
                        $response['message'] = "Invalid Format...   Check Number and Amount cannot find in your header! ";
                    }
                    
                    unlink($new_name);
                } 
                else {
                    $response['message'] = "Failed to upload";
                }
            }
        }
        
        print_r(json_encode($response));
    }
    
    public function load_record_payment_received(){
        $cols = array("p.check_number", "p.check_cut", "a.invoice_number", "a.invoice_date", "a.line_amount", "t.code", "a.job_number", "a.address");
        $table = " ins_record_payment p "
                . " left join ins_builder_check b on b.check_number=p.check_number "
                
                . " , ins_record_payment_invoice a "
                . " left join ins_code t on t.kind='ins' and t.code<>0 and t.account_category=a.account_category "
                
                . " left join ( "
                . "     SELECT v1.inspection_id, v2.type, v2.job_number, v2.address "
                . "     FROM  ( SELECT w.type, w.job_number, MAX(w.id) AS inspection_id FROM ins_inspection w GROUP BY w.job_number, w.type ) v1 "
                . "         , ins_inspection v2 WHERE v1.inspection_id=v2.id "
                . " ) ins on replace(ins.job_number,'-','')=replace(a.job_number,'-','') and ins.type=t.code "
                
                . " left join ins_inspection ins2 on a.invoice_number=ins2.invoice_number "
                . " left join ins_code ty on ty.kind='ins' and ty.code<>0 and ty.code=ins2.type "

                . " where a.payment_id=p.id "; 
        
        $result = array();
        
        $amount = 10;
        $start = 0;
        $col = 3;
	 
	$dir = "desc";
        
        $start_date = $this->input->get_post('start_date');
        $end_date = $this->input->get_post('end_date');
        $builder = $this->input->get_post('builder');
        $status = $this->input->get_post('status');
        
        $filter_sql = "";
        if ($start_date!="" || $end_date!="") {
            if ($filter_sql!="") {
                $filter_sql .= " and ";
            }
            
            $date_sql = "";
            if ($start_date!="" && $end_date!="") {
                $date_sql .= " ( a.invoice_date>='" . $start_date . "' and a.invoice_date<='" . $end_date . "' ) ";
            } else if ($start_date!="") {
                $date_sql .= " a.invoice_date>='" . $start_date . "' ";
            } else {
                $date_sql .= " a.invoice_date<='" . $end_date . "' ";
            }
            
            $filter_sql .= $date_sql;
        }
        
        if ($builder!="") {
            if ($filter_sql!="") {
                $filter_sql .= " and ";
            }
            $filter_sql .= " b.builder=$builder ";
        }
        
        if ($status!="") {
            $status_sql = "";
            if ($status=="1") {
                $status_sql .= " ( a.status=1 or ( b.id is not null and ins2.id is not null ) ) ";
            }
            else if ($status=="0") {
                $status_sql .= " ( b.id is null or ins2.id is null ) ";
            } 
            
            if ($status_sql!="") {
                if ($filter_sql!="") {
                    $filter_sql .= " and ";
                }
                $filter_sql .= $status_sql;
            }
        }
        
        if ($filter_sql!="") {
            $table .= " and " . $filter_sql;
        }
        
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
                $amount = 100;
            }
        }
        
        if ($sCol!==false && strlen($sCol)>0){
            $col = intval($sCol);
            if ($col<0 || $col>7){
                $col=3;
            }
        }
        
        if ($sdir && strlen($sdir)>0){
            if ($sdir!="desc"){
                $dir="asc";
            }
        }
        
        $colName = $cols[$col];
        $total = 0;
        $totalAfterFilter = 0;
        
        $sql = " select count(*) from " . $table ;
        $total = $this->datatable_model->get_count($sql);
        $totalAfterFilter = $total;
        
        $sql = " select  a.*, "
                . " p.check_number, p.check_cut, p.pay_to, p.check_details, p.exported_on, "
                . " t.code as inspection_type_code, t.name as inspection_type_name, "
                
                . " ins.inspection_id, ins.job_number as inspection_job_number, "
                . " ins2.id as inspection_id_2, ins2.job_number as inspection_job_number_2, "
                . " ty.code as inspection_type_code_2, ty.name as inspection_type_name_2, "
                
                . " b.id as check_id, "
                . " '' as additional from " . $table . "  ";
        
        $searchSQL = "";
        $globalSearch = " ( "
                . " a.invoice_number like '%" . $searchTerm . "%' or "
                . " a.invoice_date like '%" . $searchTerm . "%' or "
                . " a.invoice_description like '%" . $searchTerm . "%' or "
                . " a.community like '%" . $searchTerm . "%' or "
                . " a.plan_number like '%" . $searchTerm . "%' or "
                . " a.plan_name like '%" . $searchTerm . "%' or "
                . " a.address like '%" . $searchTerm . "%' or "
                . " replace(a.job_number,'-','') like '%" . str_replace("-", "", $searchTerm) . "%' or "
                . " a.option_number like '%" . $searchTerm . "%' or "
                . " a.account_category like '%" . $searchTerm . "%' or "
                . " a.category_description like '%" . $searchTerm . "%' or "
                . " a.task_description like '%" . $searchTerm . "%' or "
                . " a.start_date like '%" . $searchTerm . "%' or "
                . " a.complete_date like '%" . $searchTerm . "%' or "
                
                . " p.check_cut like '%" . $searchTerm . "%' or "
                . " p.pay_to like '%" . $searchTerm . "%' or "
                . " p.check_details like '%" . $searchTerm . "%' or "
                . " p.exported_on like '%" . $searchTerm . "%' or "
                . " p.check_number like '%" . $searchTerm . "%' "
                . " ) ";
        
        if ($searchTerm && strlen($searchTerm)>0){
            $searchSQL .= " and " . $globalSearch;
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
        
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission')!='1') {
            
        } else {
            $result["recordsTotal"] = $total;
            $result["recordsFiltered"] = $totalAfterFilter;
            $result["data"] = $data;
        }
        
        print_r(json_encode($result));
    }
    
    public function get_invoice(){
        $response = array('code'=>1, 'message'=>'No Permission');
        
        if ($this->session->userdata('user_id')) {
            $id = $this->input->get_post('id');
            if ($id=="") {
                $response['message'] = "Bad Request";
            } else {
                $invoice = $this->utility_model->get('ins_record_payment_invoice', array('id'=>$id));
                if ($invoice) {
                    $payment = $this->utility_model->get('ins_record_payment', array('id'=>$invoice['payment_id']));
                    if ($payment) {
                        $response['invoice'] = $invoice;
                        $response['payment'] = $payment;

                        $response['message'] = "Success";
                        $response['code'] = 0;
                    } else {
                        $response['message'] = "Invalid Invoice";
                    }
                } else {
                    $response['message'] = "Invalid Invoice";
                }
            }
        }

        print_r(json_encode($response));
    }

    public function match_invoice(){
        $response = array('code'=>1, 'message'=>'No Permission');
        
        if ($this->session->userdata('user_id')) {
            $id = $this->input->get_post('id');
            if ($id=="") {
                $response['message'] = "Bad Request";
            } else {
                $invoice = $this->utility_model->get('ins_record_payment_invoice', array('id'=>$id));
                if ($invoice) {
                    if (isset($invoice['job_number']) && $invoice['job_number']!="" && isset($invoice['account_category']) && $invoice['account_category']!="") {
                        $inspection_type = $this->utility_model->get('ins_code', array('kind'=>'ins', 'account_category'=>$invoice['account_category']));
                        if ($inspection_type) {
                            $inspection = $this->utility_model->get__by_sql(" select a.* from ins_inspection a "
                                    . " where a.type=" . $inspection_type['code']. " and replace(a.job_number,'-','')='" . str_replace("-", "", $invoice['job_number']) . "' and a.first_submitted=1 "
                                    . " order by a.invoice_linked desc, a.start_date asc, a.end_date asc, a.id asc "
                                    . " limit 1 ");

                            if ($inspection) {
//                                if ($this->utility_model->update('ins_inspection', array('invoice_number'=>$invoice['invoice_number'], 'invoice_linked'=>1), array('id'=>$inspection['id']))) {
                                    $response['message'] = "Success";
                                    $response['code'] = 0;
//                                } else {
//                                    $response['message'] = "Failed to link";
//                                }
                            } else {
                                $response['message'] = "No Inspection to link";
                            }
                        } else {
                            $response['message'] = "No Account Category";
                        }
                    } else {
                        $response['message'] = "Invalid Invoice";
                    }
                } else {
                    $response['message'] = "Invalid Invoice";
                }
            }
        }

        print_r(json_encode($response));
    }

    public function get_paid_invoice(){
        $response = array('code'=>1, 'message'=>'No Permission');
        
        if ($this->session->userdata('user_id')) {
            $id = $this->input->get_post('id');
            if ($id=="") {
                $response['message'] = "Bad Request";
            } else {
                $invoice = $this->utility_model->get('ins_record_payment_invoice', array('id'=>$id));
                if ($invoice) {
                    $status = 0;
                    if ($invoice['status']==0) {
                        $status = 1;
                    }
                    
                    if ($this->utility_model->update('ins_record_payment_invoice', array('status'=>$status), array('id'=>$id))) {
                        $response['message'] = "Success";
                        $response['code'] = 0;
                    } else {
                        $response['message'] = "Failed to process";
                    }
                } else {
                    $response['message'] = "Invalid Invoice";
                }
            }
        }

        print_r(json_encode($response));
    }

    public function get_autocomplete_invoice_number() {
        $response = array('code'=>1, 'message'=>'No Permission');
        
        if ($this->session->userdata('user_id')) {
            $input = $this->input->get_post('input');
            if ($input=="") {
                $response['message'] = "Bad Request";
            } else {
                $invoices = $this->utility_model->get_list__by_sql(" select a.invoice_number from ins_record_payment_invoice a "
                        . " where a.invoice_number not in "
                        . " ( select ins.invoice_number from ins_inspection ins where ins.invoice_linked=1 ) "
                        . " and a.invoice_number like '%" . $input . "%' ");
                
                if ($invoices) {
                    $result = array();
                    foreach ($invoices as $row) {
                        array_push($result, $row['invoice_number']);
                    }
                    
                    $response['result'] = $result;
                    $response['message'] = "Success";
                    $response['code'] = 0;
                } else {
                    $response['message'] = "Invalid Invoice";
                }
            }
        }

        print_r(json_encode($response));
    }

    
    
    public function re_inspection() {
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission')!='1') {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $page_data['page_name'] = 'payable_re_inspection';
        $page_data['region'] = $this->utility_model->get_list('ins_region', array());

        $current_time = time();
        
        $start_time = date('Y-m-d', strtotime("-6 month", $current_time));
        $end_time = date('Y-m-d', $current_time);
        
        $page_data['start_date'] = $start_time;
        $page_data['end_date'] = $end_time;
        
        $this->load->view('payable_re_inspection', $page_data);
    }
    
    public function get_count() {
        $res = array('code'=>1);
        
        $kind = $this->input->get_post('kind');

        if ($kind == 're_inspection') {
            $region = $this->input->get_post('region');
            $community = $this->input->get_post('community');
            $start_date = $this->input->get_post('start_date');
            $end_date = $this->input->get_post('end_date');
            $status = $this->input->get_post('status');
            $type = $this->input->get_post('type');
            $epo_status = $this->input->get_post('epo_status');

            $common_sql = "";

            if ($start_date!==false && $start_date!="") {
                if ($common_sql!="") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.start_date>='$start_date' ";
            }

            if ($end_date!==false && $end_date!="") {
                if ($common_sql!="") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.end_date<='$end_date' ";
            }

            if ($region!==false && $region!="") {
                if ($common_sql!="") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.region='$region' ";
            }

            if ($community!==false && $community!="") {
                if ($common_sql!="") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.community='$community' ";
            }

            if ($status!==false && $status!="") {
                if ($common_sql!="") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.result_code='$status' ";
            }
            
            if ($type!==false && $type!="") {
                if ($common_sql!="") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.type='$type' ";
            }

            if ($epo_status!==false && $epo_status!="") {
                if ($common_sql!="") {
                    $common_sql .= " and ";
                }

                if ($epo_status=="0_1") {
                    $common_sql .= " ( a.epo_status=0 or a.epo_status=1 ) ";
                } else {
                    $common_sql .= " a.epo_status='$epo_status' ";
                }
            }
            
            $table = " ins_region r, ins_code c1, ins_code c2,  "
                    . " ins_inspection a "

                    . " left join "
                    . " ( SELECT t.type, t.job_number, bbb.address, COUNT(*) AS inspection_count "
                    . " FROM ins_inspection t "
                    . " LEFT JOIN ins_building_unit bbb ON REPLACE(t.job_number,'-','')=REPLACE(bbb.job_number, '-', '') AND bbb.address=t.address and t.is_building_unit=1 "
                    . " GROUP BY t.job_number, bbb.address, t.type ) p "
                    . " ON p.type=a.type AND a.job_number=p.job_number AND (a.address=p.address OR p.address IS NULL) "

                    . " LEFT JOIN ins_inspection_requested q ON a.requested_id=q.id "
                    . " LEFT JOIN ins_admin u ON a.field_manager=u.id AND u.kind=2 "
                    . " LEFT JOIN ins_community tt ON tt.community_id=a.community "

                    . " WHERE a.region=r.id AND c1.kind='ins' AND c1.code=a.type AND c2.kind='rst' "
                    . " AND c2.code=a.result_code "
                    . " ";
            
            $sql = " select  a.*, "
                    . " c1.name as inspection_type, c2.name as result_name, "
                    . " r.region as region_name, tt.community_name, "

                    . " (p.inspection_count-1) as inspection_count, q.epo_number as requested_epo_number, '' as pay_invoice_number, "
                    . " a.epo_number as inspection_epo_number, a.epo_status as inspection_epo_status, a.invoice_number as inspection_invoice_number, "

                    . " u.first_name, u.last_name, '' as additional "
                    . " from " . $table . " ";
            
            if ($common_sql!="") {
                $sql .= " and " . $common_sql;
            }
            
            $count_sql = " select count(*) from ( " . $sql . " ) ttt ";
            $total = $this->datatable_model->get_count($count_sql);
            
            $count_text = "<h4 class='total-inspection'>Total: " . $total . "";
            
            $count_sql = " SELECT c.name AS result_name, t.result_code, t.tnt "
                    . " FROM ins_code c, ( select a.result_code, count(*) as tnt from ( $sql ) a group by a.result_code ) t "
                    . " WHERE c.kind='rst' AND c.code=t.result_code ORDER BY c.code ";

            $tnt = $this->utility_model->get_list__by_sql($count_sql);
            if ($tnt && is_array($tnt)) {
                foreach ($tnt as $row) {
                    if ($count_text!="") {
                        $count_text .= ", ";
                    }

                    $count_text .= '<span class="total-' . $row['result_code'] . '">';
                    $count_text .= $row['result_name'] . ": " . $row['tnt'];
                    if ($total!=0) {
                        $tnt = intval($row['tnt']);
                        $count_text .= "(" . round($tnt*1.0/$total * 100, 2) . "%)";
                    }
                    $count_text .= "</span>";
                }
            }
            
            $count_text .= "</h4>";    

            $res['result'] = $count_text;
            $res['code'] = 0;
        }
        
        if ($kind == 'pending_inspection') {
            $ins_re_inspection = " ( "
                    . " SELECT t.type, t.job_number, bbb.address, COUNT(*) AS inspection_count "
                    . " FROM ins_inspection t "
                    . " LEFT JOIN ins_building_unit bbb ON REPLACE(t.job_number,'-','')=REPLACE(bbb.job_number, '-', '') AND bbb.address=t.address and t.is_building_unit=1 "
                    . " GROUP BY t.job_number, bbb.address, t.type "
                    . " ) ";

            $table = " ins_region r, ins_code c1, ins_code c2,  "
                    . " ins_inspection a "

                    . " left join " . $ins_re_inspection . " p ON p.type=a.type AND a.job_number=p.job_number AND (a.address=p.address OR p.address IS NULL) "

                    . " LEFT JOIN ins_inspection_requested q ON a.requested_id=q.id "
                    . " LEFT JOIN ins_admin u ON a.field_manager=u.id AND u.kind=2 "
                    . " LEFT JOIN ins_community tt ON tt.community_id=a.community "

                    . " left join ins_inspection_paid pay on pay.inspection_id=a.id "

                    . " WHERE a.region=r.id AND c1.kind='ins' AND c1.code=a.type AND c2.kind='rst' "
                    . " AND c2.code=a.result_code "
                    . " ";


            $region = $this->input->get_post('region');
            $community = $this->input->get_post('community');
            $start_date = $this->input->get_post('start_date');
            $end_date = $this->input->get_post('end_date');
            $status = $this->input->get_post('status');
            $type = $this->input->get_post('type');
            $epo_status = $this->input->get_post('epo_status');
            $payment_status = $this->input->get_post('payment_status');
            $re_inspection = $this->input->get_post('re_inspection');

            $common_sql = "";

            if ($start_date!==false && $start_date!="") {
                if ($common_sql!="") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.start_date>='$start_date' ";
            }

            if ($end_date!==false && $end_date!="") {
                if ($common_sql!="") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.end_date<='$end_date' ";
            }

            if ($region!==false && $region!="") {
                if ($common_sql!="") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.region='$region' ";
            }

            if ($community!==false && $community!="") {
                if ($common_sql!="") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.community='$community' ";
            }

            if ($status!==false && $status!="") {
                if ($common_sql!="") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.result_code='$status' ";
            }

            if ($type!==false && $type!="") {
                if ($common_sql!="") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.type='$type' ";
            }

            if ($epo_status!==false && $epo_status!="") {
                if ($common_sql!="") {
                    $common_sql .= " and ";
                }

                if ($epo_status=="0_1") {
                    $common_sql .= " ( a.epo_status=0 or a.epo_status=1 ) ";
                } else {
                    $common_sql .= " a.epo_status='$epo_status' ";
                }
            }

            if ($re_inspection!==false && $re_inspection!="") {
                if ($common_sql!="") {
                    $common_sql .= " and ";
                }

                if ($re_inspection=="1") {
                    $common_sql .= " ( p.inspection_count>1 and a.first_submitted=0 ) ";
                } 
                if ($re_inspection=="0") {
                    $common_sql .= " ( p.inspection_count<=1 and a.first_submitted=1 ) ";
                }
            }

            if ($payment_status!==false && $payment_status!="") {
                if ($common_sql!="") {
                    $common_sql .= " and ";
                }

                if ($payment_status=="1") {
                    $common_sql .= " pay.invoice_id is not null ";
                } else {
                    $common_sql .= " pay.invoice_id is null ";
                }
            }

            $sql = " select  a.*, "
                    . " c1.name as inspection_type, c2.name as result_name, "
                    . " r.region as region_name, tt.community_name, "

                    . " (p.inspection_count-1) as inspection_count, q.epo_number as requested_epo_number, "
                    . " pay.invoice_id, pay.invoice_amount, pay.check_number, pay.invoice_number as payment_invoice_number, "

                    . " u.first_name, u.last_name, '' as additional "
                    . " from " . $table . " ";

            if ($common_sql!="") {
                $sql .= " and " . $common_sql;
            }
            
            $res['sql'] = $sql;
            
            $count_sql = " select count(*) from ( " . $sql . " ) ttt ";
            $total = $this->datatable_model->get_count($count_sql);
            
            $count_text = "<h4 class='total-inspection'>Total Inspections: " . $total . "";

            $count_sql = " select count(*) from ( " . $sql . " and pay.invoice_id is not null " . " ) ttt ";
            $total_paid = $this->datatable_model->get_count($count_sql);
            $count_text .= '<span class="total-1">, Total Inspection Paid: ' . $total_paid . '</span>';
            
            $count_sql = " select count(*) from ( " . $sql . " and pay.invoice_id is null " . " ) ttt ";
            $total_pending = $this->datatable_model->get_count($count_sql);
            $count_text .= '<span class="total-2">, Total Inspection Pending: ' . $total_pending . '</span>';

            $count_sql = " select sum(ttt.invoice_amount) as invoice_amount from ( " . $sql . " and pay.invoice_id is not null " . " ) ttt ";
            $amount_received = $this->utility_model->get__by_sql($count_sql);
            $count_text .= '<span class="total-1">, Total $ Received : ' . ( isset($amount_received) && isset($amount_received['invoice_amount']) ? number_format($amount_received['invoice_amount'], 2) : "0.00" ) . '</span>';
            
            $count_text .= '<span class="total-2">, Total $ Pending : ';

            $count_sql = " select vvvvv.type, "
                    . " sum(case when vvvvv.first_submitted=1 and vvvvv.invoice_id is null then 1 else 0 end) as inspection_count, "
                    . " sum(case when vvvvv.first_submitted=0 and vvvvv.invoice_id is null and vvvvv.epo_number is not null and vvvvv.epo_number<>'' then 1 else 0 end) as re_inspection_count "
                    . " from ( " . $sql . " and (a.type=1 or a.type=2) " . " ) vvvvv "
                    . " group by vvvvv.type ";
            
            $pending_amount = 0.0;
            $query_sql = " select (fee.inspection_fee*qwert.inspection_count) as inspection_fee, "
                    . " (fee.re_inspection_fee*qwert.re_inspection_count) as re_inspection_fee "
                    . " from ( " . $count_sql . " ) qwert, ins_builder_fee fee"
                    . " where fee.builder_id=1 and fee.inspection_type=qwert.type ";

            $amounts = $this->utility_model->get_list__by_sql($query_sql);
            if ($amounts) {
                foreach ($amounts as $row) {
                    if (isset($row['inspection_fee'])) {
                        $pending_amount += $row['inspection_fee'];
                    }

                    if (isset($row['re_inspection_fee'])) {
                        $pending_amount += $row['re_inspection_fee'];
                    }
                }
            }
            
            $count_sql = " select vvvvv.type, "
                    . " sum(case when vvvvv.first_submitted=1 and vvvvv.invoice_id is null then 1 else 0 end) as inspection_count, "
                    . " sum(case when vvvvv.first_submitted=0 and vvvvv.invoice_id is null and vvvvv.epo_number is not null and vvvvv.epo_number<>'' then 1 else 0 end) as re_inspection_count "
                    . " from ( " . $sql . " and (a.type=3) " . " ) vvvvv "
                    . " group by vvvvv.type ";
            
            $query_sql = " select (fee.inspection_fee*qwert.inspection_count) as inspection_fee, "
                    . " (fee.re_inspection_fee*qwert.re_inspection_count) as re_inspection_fee "
                    . " from ( " . $count_sql . " ) qwert, ins_builder_fee fee"
                    . " where fee.builder_id=2 and fee.inspection_type=qwert.type ";

            $amounts = $this->utility_model->get_list__by_sql($query_sql);
            if ($amounts) {
                foreach ($amounts as $row) {
                    if (isset($row['inspection_fee'])) {
                        $pending_amount += $row['inspection_fee'];
                    }

                    if (isset($row['re_inspection_fee'])) {
                        $pending_amount += $row['re_inspection_fee'];
                    }
                }
            }
            
            $count_text .= number_format($pending_amount, 2);
            $count_text .= '</span>';
            
            $count_text .= "</h4>";    
            
            $res['result'] = $count_text;
            $res['code'] = 0;
        }
        
        print_r(json_encode($res));
    }

    public function load_re_inspection(){
        $cols = array("a.type", "a.region", "a.community", "a.job_number", "a.address", "u.first_name", "a.start_date", "a.result_code", "a.house_ready", "a.epo_number", "a.epo_status", "a.invoice_number");
        
        $table = " ins_region r, ins_code c1, ins_code c2,  "
                . " ins_inspection a "
                
                . " left join "
                . " ( SELECT t.type, t.job_number, bbb.address, COUNT(*) AS inspection_count "
                . " FROM ins_inspection t "
                . " LEFT JOIN ins_building_unit bbb ON REPLACE(t.job_number,'-','')=REPLACE(bbb.job_number, '-', '') AND bbb.address=t.address and t.is_building_unit=1 "
                . " GROUP BY t.job_number, bbb.address, t.type ) p "
                . " ON p.type=a.type AND a.job_number=p.job_number AND (a.address=p.address OR p.address IS NULL) "
                
                . " LEFT JOIN ins_inspection_requested q ON a.requested_id=q.id "
                . " LEFT JOIN ins_admin u ON a.field_manager=u.id AND u.kind=2 "
                . " LEFT JOIN ins_community tt ON tt.community_id=a.community "
                
                . " WHERE a.region=r.id AND c1.kind='ins' AND c1.code=a.type AND c2.kind='rst' "
                . " AND c2.code=a.result_code "
                . " ";
        
        $result = array();
        
        $amount = 10;
        $start = 0;
        $col = 6;
	 
	$dir = "desc";
        
        $region = $this->input->get_post('region');
        $community = $this->input->get_post('community');
        $start_date = $this->input->get_post('start_date');
        $end_date = $this->input->get_post('end_date');
        $status = $this->input->get_post('status');
        $type = $this->input->get_post('type');
        $epo_status = $this->input->get_post('epo_status');
        
        $common_sql = "";
        
        if ($start_date!==false && $start_date!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }
            
            $common_sql .= " a.start_date>='$start_date' ";
        }
        
        if ($end_date!==false && $end_date!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }
            
            $common_sql .= " a.end_date<='$end_date' ";
        }
        
        if ($region!==false && $region!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }
            
            $common_sql .= " a.region='$region' ";
        }

        if ($community!==false && $community!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }
            
            $common_sql .= " a.community='$community' ";
        }
        
        if ($status!==false && $status!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }
            
            $common_sql .= " a.result_code='$status' ";
        }
        
        if ($type!==false && $type!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }
            
            $common_sql .= " a.type='$type' ";
        }

        if ($epo_status!==false && $epo_status!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            if ($epo_status=="0_1") {
                $common_sql .= " ( a.epo_status=0 or a.epo_status=1 ) ";
            } else {
                $common_sql .= " a.epo_status='$epo_status' ";
            }
        }
        
        $sStart = $this->input->get_post('start');
        $sAmount = $this->input->get_post('length');
//	$sCol = $this->input->get_post('iSortCol_0'); 
//      $sdir = $this->input->get_post('sSortDir_0');  
        $sCol = "";
        $sdir = "";
        
//        $sCol = $this->input->get_post("order");
//        foreach ($sCol as $row) {
//            foreach ($row as $key => $value) {
//                if ($key=='column')
//                    $sCol = $value;
//                if ($key=='dir')
//                    $sdir = $value;
//            }
//        }
        
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
            if ($col<0 || $col>=count($cols)){
                $col=6;
            }
        }
        
        if ($sdir && strlen($sdir)>0){
            if ($sdir!="desc"){
                $dir="asc";
            }
        }
        
        $colName = $cols[$col];
        $total = 0;
        $totalAfterFilter = 0;
        
        $sql = " select count(*) from " . $table . " ";
        if ($common_sql!="") {
            $sql .= " and " . $common_sql;
        }
        
        $total = $this->datatable_model->get_count($sql);
        $totalAfterFilter = $total;
        
        $sql = " select  a.*, "
                . " c1.name as inspection_type, c2.name as result_name, "
                . " r.region as region_name, tt.community_name, "
                
                . " (p.inspection_count-1) as inspection_count, q.epo_number as requested_epo_number, '' as pay_invoice_number, "
                . " a.epo_number as inspection_epo_number, a.epo_status as inspection_epo_status, a.invoice_number as inspection_invoice_number, "
                
                . " u.first_name, u.last_name, '' as additional "
                . " from " . $table . " ";
        
        $searchSQL = "";
        
        $globalSearch = " ( "
                . " replace(a.job_number,'-','') like '%" . str_replace('-','',$searchTerm) . "%' or "
                . " a.start_date like '%" . $searchTerm . "%' or  "
                . " a.community like '%" . $searchTerm . "%' or  "
                . " a.address like '%" . $searchTerm . "%' or  "
                . " r.region like '%" . $searchTerm . "%' or  "
                . " u.first_name like '%" . $searchTerm . "%' or  "
                . " u.last_name like '%" . $searchTerm . "%' or  "
                
                . " a.epo_number like '%" . $searchTerm . "%' or  "
                . " a.invoice_number like '%" . $searchTerm . "%' or  "
                
                . " r.region like '%" . $searchTerm . "%' or  "
                . " c1.name like '%" . $searchTerm . "%' or  "
                . " c2.name like '%" . $searchTerm . "%' "
                . " ) ";
        
        if ($searchTerm && strlen($searchTerm)>0){
            $searchSQL .= " and " . $globalSearch;
        }

        $sql .= $searchSQL;
        
        if ($common_sql!="") {
            $sql .= " and " . $common_sql;
        }
        
//        $sql .= " order by " . $colName . " " . $dir . " ";
        $sql .= " order by a.type asc, a.job_number asc, a.is_building_unit asc, p.address asc, a.first_submitted desc ";
        $sql .= " limit " . $start . ", " . $amount . " ";
        
        $data = $this->datatable_model->get_content($sql);
        
        $sql = " select count(*) from " . $table . " ";
        if (strlen($searchSQL)>0){
            $sql .= $searchSQL;
            
            if ($common_sql!="") {
                $sql .= " and " . $common_sql;
            }
            
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
    
    public function get_payable_re_inspection(){
        $response = array('code'=>1, 'message'=>'No Permission');
        
        if ($this->session->userdata('user_id')) {
            $requested_id = $this->input->get_post('requested_id');
            if ($requested_id=="") {
                $response['message'] = "Bad Request";
            } else {
                $requested_inspection = $this->utility_model->get('ins_inspection_requested', array('id'=>$requested_id));
                if ($requested_inspection) {
                    $response['requested_inspection'] = $requested_inspection;

                    $response['message'] = "Success";
                    $response['code'] = 0;
                } else {
                    $response['message'] = "Invalid Requested ID";
                }
            }
        }

        print_r(json_encode($response));
    }

    public function update_payable_re_inspection(){
        $response = array('code'=>1, 'message'=>'No Permission');
        
        if ($this->session->userdata('user_id')) {
            $id = $this->input->get_post('id');
            $epo_number = $this->input->get_post('epo_number');
            $epo_status = $this->input->get_post('epo_status');
            $invoice_number = $this->input->get_post('invoice_number');
            
            if ($id=="") {
                $response['message'] = "Bad Request";
            } else {
                $inspection = $this->utility_model->get('ins_inspection', array('id'=>$id));
                if ($inspection) {
                    $data = array(
                        'epo_number'=>$epo_number,
                        'epo_status'=>$epo_status,
                        'invoice_number'=>$invoice_number
                    );
                    
                    if ($this->utility_model->update('ins_inspection', $data, array('id'=>$id))) {
                        $response['message'] = "Success";
                        $response['code'] = 0;
                    } else {
                        $response['message'] = "Failed to Update!";
                    }
                } else {
                    $response['message'] = "Invalid Inspection";
                }
            }
        }

        print_r(json_encode($response));
    }


    
    public function pending_inspection() {
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission')!='1') {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $page_data['page_name'] = 'payable_pending_inspection';
        $page_data['region'] = $this->utility_model->get_list('ins_region', array());

        $current_time = time();
        
        $start_time = date('Y-m-d', strtotime("-6 month", $current_time));
        $end_time = date('Y-m-d', $current_time);
        
        $page_data['start_date'] = $start_time;
        $page_data['end_date'] = $end_time;
        
        $this->load->view('payable_pending_inspection', $page_data);
    }
    
    public function load_pending_inspection(){
        $cols = array("a.type", "a.region", "a.community", "a.job_number", "a.address", "u.first_name", "a.start_date", "a.result_code", "a.epo_number", "a.epo_status", "pay.invoice_id" );
        
        $ins_re_inspection = " ( "
                . " SELECT t.type, t.job_number, bbb.address, COUNT(*) AS inspection_count "
                . " FROM ins_inspection t "
                . " LEFT JOIN ins_building_unit bbb ON REPLACE(t.job_number,'-','')=REPLACE(bbb.job_number, '-', '') AND bbb.address=t.address and t.is_building_unit=1 "
                . " GROUP BY t.job_number, bbb.address, t.type "
                . " ) ";
                    
        $table = " ins_region r, ins_code c1, ins_code c2,  "
                . " ins_inspection a "
                
                . " left join " . $ins_re_inspection . " p ON p.type=a.type AND a.job_number=p.job_number AND (a.address=p.address OR p.address IS NULL) "
                
                . " LEFT JOIN ins_inspection_requested q ON a.requested_id=q.id "
                . " LEFT JOIN ins_admin u ON a.field_manager=u.id AND u.kind=2 "
                . " LEFT JOIN ins_community tt ON tt.community_id=a.community "
                
                . " left join ins_inspection_paid pay on pay.inspection_id=a.id "
                
                . " WHERE a.region=r.id AND c1.kind='ins' AND c1.code=a.type AND c2.kind='rst' "
                . " AND c2.code=a.result_code "
                . " ";
        
        $result = array();
        
        $amount = 10;
        $start = 0;
        $col = 6;
	 
	$dir = "desc";
        
        $region = $this->input->get_post('region');
        $community = $this->input->get_post('community');
        $start_date = $this->input->get_post('start_date');
        $end_date = $this->input->get_post('end_date');
        $status = $this->input->get_post('status');
        $type = $this->input->get_post('type');
        $epo_status = $this->input->get_post('epo_status');
        $payment_status = $this->input->get_post('payment_status');
        $re_inspection = $this->input->get_post('re_inspection');
        
        $common_sql = "";
        
        if ($start_date!==false && $start_date!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }
            
            $common_sql .= " a.start_date>='$start_date' ";
        }
        
        if ($end_date!==false && $end_date!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }
            
            $common_sql .= " a.end_date<='$end_date' ";
        }
        
        if ($region!==false && $region!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }
            
            $common_sql .= " a.region='$region' ";
        }

        if ($community!==false && $community!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }
            
            $common_sql .= " a.community='$community' ";
        }
        
        if ($status!==false && $status!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }
            
            $common_sql .= " a.result_code='$status' ";
        }
        
        if ($type!==false && $type!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }
            
            $common_sql .= " a.type='$type' ";
        }

        if ($epo_status!==false && $epo_status!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            if ($epo_status=="0_1") {
                $common_sql .= " ( a.epo_status=0 or a.epo_status=1 ) ";
            } else {
                $common_sql .= " a.epo_status='$epo_status' ";
            }
        }
        
        if ($re_inspection!==false && $re_inspection!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }
            
            if ($re_inspection=="1") {
                $common_sql .= " ( p.inspection_count>1 and a.first_submitted=0 ) ";
            } 
            if ($re_inspection=="0") {
                $common_sql .= " ( p.inspection_count<=1 and a.first_submitted=1 ) ";
            }
        }
        
        if ($payment_status!==false && $payment_status!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            if ($payment_status=="1") {
                $common_sql .= " pay.invoice_id is not null ";
            } else {
                $common_sql .= " pay.invoice_id is null ";
            }
        }
        
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
            if ($col<0 || $col>=count($cols)){
                $col=6;
            }
        }
        
        if ($sdir && strlen($sdir)>0){
            if ($sdir!="desc"){
                $dir="asc";
            }
        }
        
        $colName = $cols[$col];
        $total = 0;
        $totalAfterFilter = 0;
        
        $sql = " select count(*) from " . $table . " ";
        if ($common_sql!="") {
            $sql .= " and " . $common_sql;
        }
        
        $total = $this->datatable_model->get_count($sql);
        $totalAfterFilter = $total;
        
        $sql = " select  a.*, "
                . " c1.name as inspection_type, c2.name as result_name, "
                . " r.region as region_name, tt.community_name, "
                
                . " (p.inspection_count-1) as inspection_count, q.epo_number as requested_epo_number, "
                . " pay.invoice_id, pay.invoice_amount, pay.check_number, pay.invoice_number as payment_invoice_number, "
                
                . " u.first_name, u.last_name, '' as additional "
                . " from " . $table . " ";
        
        $searchSQL = "";
        
        $globalSearch = " ( "
                . " replace(a.job_number,'-','') like '%" . str_replace('-','',$searchTerm) . "%' or "
                . " a.start_date like '%" . $searchTerm . "%' or  "
                . " a.community like '%" . $searchTerm . "%' or  "
                . " a.address like '%" . $searchTerm . "%' or  "
                . " r.region like '%" . $searchTerm . "%' or  "
                . " u.first_name like '%" . $searchTerm . "%' or  "
                . " u.last_name like '%" . $searchTerm . "%' or  "
                
                . " a.epo_number like '%" . $searchTerm . "%' or  "
                . " a.invoice_number like '%" . $searchTerm . "%' or  "
                
                . " pay.invoice_number like '%" . $searchTerm . "%' or  "
                . " pay.check_number like '%" . $searchTerm . "%' or  "
                
                . " r.region like '%" . $searchTerm . "%' or  "
                . " c1.name like '%" . $searchTerm . "%' or  "
                . " c2.name like '%" . $searchTerm . "%' "
                . " ) ";
        
        if ($searchTerm && strlen($searchTerm)>0){
            $searchSQL .= " and " . $globalSearch;
        }

        $sql .= $searchSQL;
        
        if ($common_sql!="") {
            $sql .= " and " . $common_sql;
        }
        
        $sql .= " order by " . $colName . " " . $dir . " ";
        $sql .= " limit " . $start . ", " . $amount . " ";
        
        $data = $this->datatable_model->get_content($sql);
        
        $sql = " select count(*) from " . $table . " ";
        if (strlen($searchSQL)>0){
            $sql .= $searchSQL;
            
            if ($common_sql!="") {
                $sql .= " and " . $common_sql;
            }
            
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
    
    
    private function excel_find_cell($values, $headers) {
        if (is_array($headers)) {
            foreach ($headers as $header) {
                $value = strtolower($header['value']);

                $is_search = true;
                foreach ($values as $v) {
                    if (strpos($value, $v)===false) {
                        $is_search = false;
                    } else {

                    }
                }

                if ($is_search) {
                    return $header['field'];
                }
            }
        } else {
            $value = strtolower($headers);

            $is_search = true;
            foreach ($values as $v) {
                if (strpos($value, $v)===false) {
                    $is_search = false;
                } else {

                }
            }

            if ($is_search) {
                return true;
            }
        }
        
        return false;
    }
    
    private function get_decimal($value) {
        $value = str_replace("$", "", $value);
        $value = str_replace(",", "", $value);
        $value = str_replace(" ", "", $value);

        return doubleval($value);
    }
    
    private function find_invoice_number_in_list($invoices, $invoice_number) {
        $count = 0;
        
        foreach ($invoices as $row) {
            if (isset($row['invoice_number']) && $row['invoice_number']!="") {
                if ($row['invoice_number'] == $invoice_number) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
    
    private function add_or_update_invoice($payment_id, $row) {
        $t = mdate('%Y%m%d%H%i%s', time());
        $invoice = $this->utility_model->get('ins_record_payment_invoice', array('invoice_number'=>$row['invoice_number']));
        if ($invoice) {
            $row['updated_at'] = $t;
            return $this->utility_model->update('ins_record_payment_invoice', $row, array('id'=>$invoice['id']));
        } 
        else {
            $row['payment_id'] = $payment_id;
            $row['created_at'] = $t;
            $row['updated_at'] = $t;

            return $this->utility_model->insert('ins_record_payment_invoice', $row);
        }
        
        return false;
    }
    
    private function link_invoice_to_inspection($inspection_type, $job_number, $invoice_number, $is_unit=false, $address=false) {
        $inspection = $this->utility_model->get__by_sql(" select a.* from ins_inspection a "
                . " where a.type=" . $inspection_type . " and replace(a.job_number,'-','')='" . str_replace("-", "", $job_number) . "' and a.first_submitted=1 "
                . ( $is_unit ? " and a.is_building_unit=1 " :  " and a.is_building_unit=0 " )
                . ( $address!==false ? " and a.address='" . $address . "'  " :  "" )
                . " order by a.invoice_linked desc, a.start_date asc, a.end_date asc, a.id asc "
                . " limit 1 ");

        if ($inspection) {
            return $this->utility_model->update('ins_inspection', array('invoice_number'=>$invoice_number, 'invoice_linked'=>1), array('id'=>$inspection['id']));
        }     
        
        return false;
    }
    
    public function test() {
        $response = array('code'=>1, 'message'=>'No Permission');

        $this->utility_model->update('ins_inspection', array('first_submitted'=>0, 'invoice_number'=>'', 'invoice_linked'=>0), array());

        $this->utility_model->start();
        
//        $response['ddd'] = $this->get_decimal("$ 343,343.34");
        $inspections = $this->utility_model->get_list__by_sql(" select a.* from ins_inspection a "
                . " order by a.type asc, a.job_number asc, a.is_building_unit asc, a.start_date asc, a.id asc, a.address asc ");
        
        $previous = "";
        $address = "";
        $result = array();
        
        if ($inspections) {
            foreach ($inspections as $row) {
                $new_one = $row['type'] . "_" . $row['job_number'];
                if ($previous!=$new_one) {
                    $address = "";
                    
                    if ($row['is_building_unit']==1) {
                        if ($address!=$row['address']) {
                            $this->utility_model->update('ins_inspection', array('first_submitted'=>1), array('id'=>$row['id']));
                            $address = $row['address'];
                            
//                            if ($row['job_number']=="3115-004-98") {
                            array_push($result, array('id'=>$row['id'], 'type'=>$row['type'], 'job_number'=>$row['job_number'], 'address'=>$address));
//                            }
                        } 
                    } else {
                        $this->utility_model->update('ins_inspection', array('first_submitted'=>1), array('id'=>$row['id']));
//                        if ($row['job_number']=="3115-004-98") {
                        array_push($result, array('id'=>$row['id'], 'type'=>$row['type'], 'job_number'=>$row['job_number'], 'address'=>'No Unit'));
//                        }
                    }
                    
                    $previous = $new_one;
                } else {
                    if ($row['is_building_unit']==1) {
                        if ($address!=$row['address']) {
                            $this->utility_model->update('ins_inspection', array('first_submitted'=>1), array('id'=>$row['id']));
                            $address = $row['address'];
                            
//                            if ($row['job_number']=="3115-004-98") {
                            array_push($result, array('id'=>$row['id'], 'type'=>$row['type'], 'job_number'=>$row['job_number'], 'address'=>$address));
//                            }
                        } 
                    }
                }
            }
            
            $response['code'] = 0;
        }
        
        $this->utility_model->complete();
        
        $response['result'] = $result;
        print_r(json_encode($response));
        

//CREATE
//    VIEW `ins_inspection_paid` 
//    AS
//(
//SELECT 
//t.id as inspection_id, t.type AS inspection_type, t.job_number,
//b.id AS check_id, b.check_date, b.builder, b.check_amount, b.check_number, 
//a.id AS invoice_id, a.invoice_number, a.invoice_date, a.line_amount AS invoice_amount,
//p.id AS payment_id, p.pay_to, p.check_cut, p.check_amount AS payment_amount
//
//FROM ins_record_payment p
//LEFT JOIN ins_builder_check b ON b.check_number=p.check_number
//, ins_record_payment_invoice a
//LEFT JOIN ins_inspection t ON a.invoice_number=t.invoice_number
//LEFT JOIN ins_code c ON c.kind='ins' AND c.code<>0 AND c.code=t.type
//
//WHERE p.id=a.payment_id AND t.type IS NOT NULL AND b.id IS NOT NULL
//);


//CREATE
//    VIEW `ins_re_inspection` 
//    AS
//(
//SELECT t.type, t.job_number, bbb.address, COUNT(*) AS inspection_count
//FROM ins_inspection t
//LEFT JOIN ins_building_unit bbb ON REPLACE(t.job_number,'-','')=REPLACE(bbb.job_number, '-', '') AND bbb.address=t.address
//GROUP BY t.job_number, bbb.address, t.type 
//);
        
        
    }

    
}
