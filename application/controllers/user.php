<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class User extends CI_Controller {

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

    public function login() {
        $page_data = array();
        $page_data['page_name'] = 'login';
        $page_data['login_email'] = '';
        $page_data['login_type'] = isset($_COOKIE['inspection_portal_user_type']) && $_COOKIE['inspection_portal_user_type']!="" ? $_COOKIE['inspection_portal_user_type'] : "1";
//        $page_data['facebook_url'] = $this->facebook->login_url();
        $this->load->view('login', $page_data);
    }

    public function logout() {
        $this->session->unset_userdata('user_id');
        $this->session->unset_userdata('email');

        redirect(base_url() . "user/login.html");
    }

    public function signin() {
        $email = $this->input->get_post('email');
        $password = $this->input->get_post('password');
        $user_type = $this->input->get_post('user_type');
        if ($user_type===false || $user_type!="1") {
            $user_type = "0";
        }

        if ($user_type=="1") {
            $result = $this->user_model->get_user__by_email('admin', $email);
            $key_hash = sha1($password . $this->hash_key);
        } else {
            $result = $this->user_model->get_user__by_email('user', $email);
            $key_hash = sha1($password . $this->hash_key__front);
        }

        $is_redirected = false;

        if ($result) {
            if ($result['password'] != $key_hash ) {
                $this->session->set_userdata('message',  $this->errMsg[5]);
            } else if ($result['status']=='0') {
                $this->session->set_userdata('message',  $this->errMsg[9]);

            } else {
                if ($user_type=="1") {
                    setcookie("inspection_portal_user_type", "1");
                } else {
                    setcookie("inspection_portal_user_type", "0");
                }

                $this->session->set_userdata('user_id', $result['id']);
                $this->session->set_userdata('user_name', $result['first_name'] . " " . $result['last_name']);
                $this->session->set_userdata('email', $result['email']);
                $this->session->set_userdata('user_builder', $result['builder']);

                $permission = 0;
                if ($user_type=="1") {
                    $permission = $result['kind'];
                    $this->session->set_userdata('permission', $result['kind']);
                    $this->session->set_userdata('user_region', $result['region']);
                } else {
                    $this->session->set_userdata('permission', 0);
                    $this->session->set_userdata('user_region', 0);
                }

                if ($permission==1 || $permission==0  || $permission==4) {
                    redirect(base_url() . "user/profile.html");
                } else {
                    redirect(base_url() . "inspection/edit_inspection_requested.html");
                }

                $is_redirected = true;
            }
        } else {
            $this->session->set_userdata('message', $this->errMsg[3]);
        }

        if ($is_redirected===false) {
            $page_data = array();
            $page_data['page_name'] = 'login';
            $page_data['login_email'] = $email;
            $page_data['login_type'] = $user_type;
    //        $page_data['facebook_url'] = $this->facebook->login_url();
            $this->load->view('login', $page_data);
        }
    }

    public function profile() {
        if (!$this->session->userdata('user_id')) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $user_id = $this->session->userdata('user_id');

        $page_data = array();
        $page_data['page_name'] = 'profile';

        if ($this->session->userdata('permission')==0) {
            $page_data['account'] = $this->user_model->get_user__by_id('user', $user_id);
        } else {
            $page_data['account'] = $this->user_model->get_user__by_id('admin', $user_id);
        }

        $this->load->view('profile', $page_data);
    }

    public function update_profile() {
        if (!$this->session->userdata('user_id')) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $email = $this->input->get_post('email');
        $password = $this->input->get_post('password');

        $first_name = $this->input->get_post('first_name');
        $last_name = $this->input->get_post('last_name');
        $address = $this->input->get_post('address');
        $cell_phone = $this->input->get_post('cell_phone');
        $other_phone = $this->input->get_post('other_phone');
        if ($other_phone===false) {
            $other_phone = "";
        }
        $fee = $this->input->get_post('inspector_fee');
        if ($fee===false || $fee=="") {
            $fee = "0.0";
        }

        $allow_email = $this->input->get_post('allow_email');
        if ($allow_email===false) {
            $allow_email = "";
        }

        $ret = false;
        $user_id = $this->session->userdata('user_id');
        if ($user_id){
            if ($this->session->userdata('permission')==0) {
                $user = $this->user_model->get_user__by_email('user', $email);
            } else {
                $user = $this->user_model->get_user__by_email('admin', $email);
            }

            if ($user){
                if ($user_id == $user['id']){
                    $ret = true;
                } else {
                    $this->session->set_userdata('message', $this->errMsg[2]);
                }
            } else {
                $ret = true;
            }
        } else {
            $this->session->set_userdata('message', $this->errMsg[3]);
        }

        if ($ret){
            $t = mdate('%Y%m%d%H%i%s', time());
            $data = array('email' => $email, 'first_name'=>$first_name, 'last_name'=>$last_name, 'address'=>$address, 'updated_at' => $t);

            if ($this->session->userdata('permission')==0) {
                $data['phone_number'] = $cell_phone;
                $data['fee'] = $fee;
                $data['password'] = sha1($password . $this->hash_key__front);
            } else {
                $data['cell_phone'] = $cell_phone;
                $data['other_phone'] = $other_phone;
                $data['password'] = sha1($password . $this->hash_key);

                if ($allow_email=="1") {
                    $data['allow_email'] = 1;
                } else {
                    $data['allow_email'] = 0;
                }
            }

            if ($this->session->userdata('permission')==0) {
                if ($this->user_model->update_user__by_id('user', $user_id, $data)) {
                    $this->session->set_userdata('email', $email);
                    $this->session->set_userdata('message', $this->errMsg[0]);
                } else {
                    $this->session->set_userdata('message', $this->errMsg[1]);
                }
            } else {
                if ($this->user_model->update_user__by_id('admin', $user_id, $data)) {
                    $this->session->set_userdata('email', $email);
                    $this->session->set_userdata('message', $this->errMsg[0]);
                } else {
                    $this->session->set_userdata('message', $this->errMsg[1]);
                }
            }
        }

        redirect(base_url() . "user/profile.html");
    }

    public function inspectors() {
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission')!='1') {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $page_data = array();
        $page_data['page_name'] = 'user';
        $this->load->view('user_list', $page_data);
    }

    public function load_inspector(){
        $cols = array("a.email", "a.first_name", "a.last_name", "a.phone_number", "a.ip_address", "a.status");
        $table = "ins_user a";

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
            if ($col<0 || $col>5){
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

        $sql = " select  a.*, '' as additional from " . $table . "  ";
        $searchSQL = "";
        $globalSearch = " ( "
                . " a.first_name like '%" . $searchTerm . "%' or "
                . " a.email like '%" . $searchTerm . "%' or  "
                . " a.phone_number like '%" . $searchTerm . "%' or  "
                . " a.last_name like '%" . $searchTerm . "%' or  "
                . " a.ip_address like '%" . $searchTerm . "%'  "
                . " ) ";

        if ($searchTerm && strlen($searchTerm)>0){
            $searchSQL .= " where " . $globalSearch;
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


    public function activate() {
        $res = array('err_code'=>1);
        if ($this->session->userdata('user_id')) {
            $user_id = $this->input->get_post('user_id');
            $status= $this->input->get_post('status');

            if ($user_id!==false && $status!==false && $this->session->userdata('permission')==1){
                $t = mdate('%Y%m%d%H%i%s', time());
                if ($this->utility_model->update('ins_user', array('status'=>$status, 'updated_at'=>$t), array('id'=>$user_id))) {
                    $res['err_code'] = 0;
                }
            } else {
            }
        }

        print_r(json_encode($res));
    }

    public function edit() {
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission')!='1') {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $user_id = $this->input->get_post('user_id');
        $kind = $this->input->get_post('kind');

        $page_data['page_name'] = 'user';
        if ($kind=='add') {
            $page_data['page_title'] = "New Inspector";
            $page_data['account'] = array('first_name'=>'', 'last_name'=>'', 'email'=>'', 'phone_number'=>'');
        } else {
            $page_data['page_title'] = "Edit Inspector";
            $page_data['account'] = $this->user_model->get_user__by_id('user', $user_id);
        }

        $page_data['user_id'] = $user_id;
        $page_data['kind'] = $kind;

        $this->load->view('user_edit', $page_data);
    }


    public function update() {
        $res = array('err_code'=>1, 'err_msg'=>'Failed!');
        if ($this->session->userdata('user_id')) {
            $user_id = $this->input->get_post('user_id');
            $kind = $this->input->get_post('kind');

            $permission = $this->session->userdata('permission');

            if ($kind=='add' && $user_id===false) {
                $user_id = "";
            }

            if ($user_id!==false && $kind!==false){
                if ($this->session->userdata('permission')==1) {
                    $t = mdate('%Y%m%d%H%i%s', time());
                    $ip = $this->get_client_ip();
                    $data = array('updated_at'=>$t, 'ip_address'=>$ip);

                    $ret = 0;
                    if ($kind=='add' || $kind=='profile') {
                        $email = $this->input->get_post('email');
                        $first_name = $this->input->get_post('first_name');
                        $last_name = $this->input->get_post('last_name');
                        $cell_phone = $this->input->get_post('phone_number');
                        $address = $this->input->get_post('address');
                        $fee = $this->input->get_post('fee');

                        if ($email!==false && $first_name!==false && $last_name!==false) {
                            $ret = 1;

                            $data['email'] = $email;
                            $data['first_name'] = $first_name;
                            $data['last_name'] = $last_name;
                            $data['phone_number'] = $cell_phone;
                            $data['address'] = $address;
                            $data['fee'] = $fee;

                            $user = $this->user_model->get_user__by_email('user', $email);
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
                            $ret = 1;

                            $data['password'] = sha1($password . $this->hash_key__front);
                        }
                    }

                    if ($ret==1) {
                        if ($kind=='add') {
                            $res['err_msg'] = "Failed to Add!";
                        } else {
                            $res['err_msg'] = "Failed to Update!";
                        }

                        if ($kind=='add') {
                            $data['created_at'] = $t;

                            if ($this->user_model->insert_user('user', $data)) {
                                $res['err_code'] = 0;
                                $res['err_msg'] = "Successfully Added!";
                            }

                        } else {
                            if ($this->user_model->update_user__by_id('user', $user_id, $data)) {
                                $res['err_code'] = 0;
                                $res['err_msg'] = "Successfully Updated!";
                            }
                        }
                    } else {
                        if ($res==0) {
                            $res['err_msg'] = "You haven't permission";
                        }
                    }
                } else {
                    $res['err_msg'] = "You haven't permission";
                }
            }
        }

        print_r(json_encode($res));
    }

    function get_client_ip() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }


    public function delete_user() {
        $res = array('err_code'=>1);
        if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
            $user_id = $this->input->get_post('user_id');

            if ($user_id!==false){
                if ($this->user_model->delete_user('user', array('id'=>$user_id))) {
                    $res['err_code'] = 0;
                }
            } else {
            }
        }

        print_r(json_encode($res));
    }


    public function forgot_password() {
        $res = array('err_code'=>1, 'err_msg'=>'Failed to send!');
        $email = $this->input->get_post('email');
        $type = $this->input->get_post('type');

        if ($email!==false && $type!==false){
            if ($type=="1") {
                $user = $this->user_model->get_user__by_email('admin', $email);
            } else {
                $user = $this->user_model->get_user__by_email('user', $email);
            }

            if ($user) {
                if ($this->send_password_email($type, $email)) {
                    $res['err_msg'] = "Success";
                    $res['err_code'] = 0;
                } else {

                }
            } else {
                $res['err_msg'] = "Email Address does not exist";
            }
        } else {
            $res['err_msg'] = "Invalid Request";
        }

        print_r(json_encode($res));
    }

    private function send_password_email($type, $email) {
        $this->load->library('uuid');

        $token = $this->uuid->v4();
        $secret = $this->uuid->v4();

        if ( $this->utility_model->insert( 'ins_token', array( 'type'=>$type, 'token'=>$token, 'secret'=>$secret, 'email'=>$email, 'created_at'=>time() ) ) ) {

            $body = "Click this link to reset password." . "\n"
                    . base_url() . "user/reset_password?secret=" . $secret . "&token=" . $token . "\n";

            $this->load->library('mailer/phpmailerex');
            $mail = new PHPMailer;

            $mail->SMTPDebug = 0;                               // Enable verbose debug output
            $mail->Debugoutput = 'error_log';

            $mail->Timeout = 60;
            $mail->Timelimit = 60;

            $mail->isSMTP();                                      // Set mailer to use SMTP

            $mail->Host = SMTP_HOST;  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = SMTP_USER;                // SMTP username
            $mail->Password = SMTP_PASSWORD;                         // SMTP password
            $mail->SMTPSecure = '';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = SMTP_PORT;                       // TCP port to connect to

            $mail->From = SMTP_FROM;
            $mail->FromName = SMTP_NAME;

            $mail->addAddress($email);     // Add a recipient

            $mail->isHTML(false);                                  // Set email format to HTML

            $mail->Subject = "Reset Password";
            $mail->Body = $body;
            $mail->AltBody = "";

            if ($mail->send()) {
                return true;
            }
        }

        return false;
    }

    public function reset_password() {
        $page_data = array();
        $ret = false;

        $token = $this->input->get_post('token');
        $secret = $this->input->get_post('secret');

        if ($token!==false && $secret!==false) {
            $t = $this->utility_model->get__by_sql(" select * from ins_token where token='" . $token . "' and secret='" . $secret . "' and created_at>=" . (time()-60*10) . " ");
            if ($t) {
                $ret = true;

                $page_data['token'] = $token;
                $page_data['secret'] = $secret;
            } else {

            }
        }

        if ($ret) {
            $page_data['page_name'] = 'password';
            $this->load->view('password', $page_data);
        } else {
            $this->session->set_userdata('message', 'Invalid Secret and Token');
            redirect(base_url() . "welcome/index.html");
        }
    }

    public function change_password() {
        $token = $this->input->get_post('token');
        $secret = $this->input->get_post('secret');
        $password = $this->input->get_post('password');

        $t = $this->utility_model->get__by_sql(" select * from ins_token where token='" . $token . "' and secret='" . $secret . "' and created_at>=" . (time()-60*10) . " ");
        if ($t) {
            $email = $t['email'];
            if ($t['type']==1) {
                $result = $this->user_model->get_user__by_email('admin', $email);
            } else {
                $result = $this->user_model->get_user__by_email('user', $email);
            }

            if ($result) {
                if ($t['type']==1) {
                    if ($this->user_model->update_user__by_id('admin', $result['id'], array('password'=>sha1($password . $this->hash_key )))) {
                        $this->session->set_userdata('message', "Successfully Changed!");
                        redirect(base_url() . "user/login.html");
                        exit(1);
                    } else {
                        $this->session->set_userdata('message', "Failed to reset password!");
                    }
                } else {
                    if ($this->user_model->update_user__by_id('user', $result['id'], array('password'=>sha1($password . $this->hash_key__front )))) {
                        $this->session->set_userdata('message', "Successfully Changed!");
                        redirect(base_url() . "user/login.html");
                        exit(1);
                    } else {
                        $this->session->set_userdata('message', "Failed to reset password!");
                    }
                }
            } else {
                $this->session->set_userdata('message', $this->errMsg[3]);
            }

            $page_data = array();
            $page_data['page_name'] = 'password';
            $page_data['token'] = $token;
            $page_data['secret'] = $secret;
            $this->load->view('password', $page_data);
        } else {
            $this->session->set_userdata('message', 'Invalid Secret and Token');
            redirect(base_url() . "welcome/index.html");
        }
    }


}
