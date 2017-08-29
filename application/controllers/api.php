<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Api extends CI_Controller {

    private $hash_key = "inspection_front_user";

    const FILESIZE = 26214400; // 10MB

    private $status = array(
        array('code' => 0, 'message' => 'Success'), // 0
        array('code' => 1, 'message' => 'Failed'), // 1
        array('code' => -1, 'message' => 'Bad Credential'), // 2
        array('code' => -2, 'message' => 'Bad Request'), // 3
        array('code' => 2, 'message' => 'Non Exist User'), // 4
        array('code' => 3, 'message' => 'Wrong Password'), // 5
        array('code' => 4, 'message' => 'You haven\'t permission'), // 6
        array('code' => 5, 'message' => 'Can\'t open file'), // 7
        array('code' => 6, 'message' => 'Unknown Device'), // 8
        array('code' => 7, 'message' => 'Already exist'), // 9
        array('code' => 8, 'message' => 'Please wait until activated!'), // 10
    );

    public function __construct() {
        parent::__construct();

        $this->load->library('uuid');
        $this->load->library('m_pdf');

        $this->load->model('user_model');
        $this->load->model('utility_model');
        $this->load->model('datatable_model');

        $this->load->library('mailer/phpmailerex');
        $this->load->helper('csv');
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

    // version 1.0
    public function v1($method = '', $param = '', $kind = '') {
        $response = array(
            'status' => $this->status[1],
            'request' => array(
                'method' => $method,
                'param' => $param,
                'kind' => $kind,
                'data' => array()
            ),
            'response' => array(
            )
        );

        $request_data = array();
        $result_data = array();

        if ($method == 'user') {
            $t = mdate('%Y%m%d%H%i%s', time());

            if ($param == 'register') {
                $first_name = $this->input->get_post('first_name');
                $last_name = $this->input->get_post('last_name');
                $email = $this->input->get_post('email');
                $phone_number = $this->input->get_post('phone_number');
                $password = $this->input->get_post('password');

                if ($email === false || $password === false) {
                    $response['status'] = $this->status[3];
                } else {

                    $user = $this->utility_model->get('ins_user', array('email' => $email));
                    if ($user) {
                        $response['status'] = $this->status[9];
                    } else {
                        $ip = $this->get_client_ip();

                        if ($this->utility_model->insert('ins_user', array('email' => $email, 'ip_address' => $ip, 'phone_number'=>$phone_number, 'first_name' => $first_name, 'last_name' => $last_name, 'password' => sha1($password . $this->hash_key), 'created_at' => $t, 'updated_at' => $t))) {
                            $response['status'] = $this->status[0];
                            $result_data = $this->utility_model->get('ins_user', array('email' => $email));

                            $mail_subject = "New Inspector is registered";
                            $mail_body =  " First Name: " . $result_data['first_name'] . "\n"
                                        . " Last Name: " . $result_data['last_name'] . "\n"
                                        . " Email Address: " . $result_data['email'] . "\n"
                                        . " Phone Number: " . $result_data['phone_number'] . "\n"
                                        . "\n"
                                        . " Please login admin panel and check this user. \n"
                                        . " " . base_url() . " \n\n"
                                        . " Regards."
                                        . "\n";

                            $sender = $this->utility_model->get_list('ins_admin', array('kind'=>1, 'allow_email'=>1));
                            $this->send_mail($mail_subject, $mail_body, $sender, false);
                        } else {
                            $response['status'] = $this->status[1];
                        }
                    }
                }
            }
            else if ($param == 'update') {
                $first_name = $this->input->get_post('first_name');
                $last_name = $this->input->get_post('last_name');
                $email = $this->input->get_post('email');
                $phone_number = $this->input->get_post('phone_number');
                $old_password = $this->input->get_post('old_password');
                $password = $this->input->get_post('new_password');
                $address = $this->input->get_post('address');

                if ($email === false || $password === false || $old_password === false) {
                    $response['status'] = $this->status[3];
                } else {

                    $user = $this->utility_model->get('ins_user', array('email' => $email));
                    if ($user) {
                        if (sha1($old_password . $this->hash_key) == $user['password']) {
                            $ip = $this->get_client_ip();

                            if ($this->utility_model->update('ins_user', array('address'=>$address, 'email' => $email, 'ip_address' => $ip, 'phone_number'=>$phone_number, 'first_name' => $first_name, 'last_name' => $last_name, 'password' => sha1($password . $this->hash_key), 'updated_at' => $t), array('email' => $email))) {
                                $response['status'] = $this->status[0];
                                $result_data = $this->utility_model->get('ins_user', array('email' => $email));
                            } else {
                                $response['status'] = $this->status[1];
                            }
                        } else {
                            $response['status'] = $this->status[5];
                        }
                    } else {
                        $response['status'] = $this->status[4];
                    }
                }
            }
            else if ($param == 'login') {
                $email = $this->input->get_post('email');
                $password = $this->input->get_post('password');

                if ($email === false || $password === false) {
                    $response['status'] = $this->status[3];
                } else {

                    $user = $this->utility_model->get('ins_user', array('email' => $email));
                    if ($user) {
                        if (sha1($password . $this->hash_key) == $user['password']) {
                            if ($user['status'] == '0') {
                                $response['status'] = $this->status[10];
                            } else {
                                $response['status'] = $this->status[0];
                                $result_data = $user;
                            }
                        } else {
                            $response['status'] = $this->status[5];
                        }
                    } else {
                        $response['status'] = $this->status[4];
                    }
                }
            }
            else if ($param == 'sign') {
                $email = $this->input->get_post('email');

                if ($email === false) {
                    $response['status'] = $this->status[3];
                } else {

                    $user = $this->utility_model->get('ins_user', array('email' => $email));
                    if ($user) {
                        if ($user['status'] == '0') {
                            $response['status'] = $this->status[10];
                        } else {
                            $response['status'] = $this->status[0];
                            $result_data = $user;
                        }
                    } else {
                        $response['status'] = $this->status[4];
                    }
                }
            }
            else if ($param == 'field_manager') {
                $region = $this->input->get_post('region');

                if ($region === false) {
                    $response['status'] = $this->status[3];
                } else {
                    $region = $this->utility_model->decode($region);

                    $user = $this->utility_model->get_list('ins_admin', array('kind'=>2, 'status'=>'1', 'region'=>$region));
                    if ($user) {
                        $result_data['user'] = $user;
                    } else {
                        $result_data['user'] = array();
                    }

                    $response['status'] = $this->status[0];
                }
            }
            else {
                $response['status'] = $this->status[2];
            }
        }
        else if ($method == 'inspection') {
            if ($kind == 'drainage' || $kind == 'lath') {
                $type = $kind == 'drainage' ? 1 : 2;
                $user_id = $this->input->get_post('user_id');

                if ($user_id === false) {
                    $response['status'] = $this->status[3];
                }
                else {
                    $user_id = $this->utility_model->decode($user_id);
                    $user = $this->utility_model->get('ins_user', array('id' => $user_id));
                    if ($user) {

                        if ($param == 'check') {
                            $job = $this->input->get_post('job');
                            $is_building_unit = $this->input->get_post('is_building_unit');
                            if ($is_building_unit===false || $is_building_unit=="") {
                                $is_building_unit = "0";
                            }

                            $address = $this->input->get_post('address');
                            if ($address===false)  {
                                $address = "";
                            }

                            $edit_inspection_id = $this->input->get_post('inspection_id');
                            if ($edit_inspection_id===false || $edit_inspection_id=="0")  {
                                $edit_inspection_id = "";
                            }

                            if ($job === false) {
                                $response['status'] = $this->status[3];
                            } else {

                                $schedule = $this->utility_model->get__by_sql("select a.*, u.id as manager_id from ins_building a "
                                        . " left join ins_admin u on concat(u.first_name, ' ', u.last_name)=a.field_manager and u.kind=2 "
                                        . " where replace(a.job_number,'-','')=replace('$job','-','') "
                                        . " order by a.updated_at desc limit 1");
                                if ($schedule) {
                                    $result_data['is_schedule'] = 1;
                                    $result_data['schedule'] = $schedule;
                                } else {
                                    $result_data['is_schedule'] = 0;
                                }

                                if ($is_building_unit=="1" && $address!="") {
                                    $result_data['is_bu'] = 1;
                                } else {
                                    $result_data['is_bu'] = 0;
                                }

                                $result_data['is_initials'] = 0;
                                if ($kind=='lath') {
                                    $pass_drainage = $this->utility_model->get_count__by_sql("select a.* from ins_inspection a where replace(a.job_number,'-','')=replace('$job','-','') and ( a.result_code=1 or a.result_code=2 ) and a.type=1");     // drainage inspection with pass or pass with exception
                                    if ($pass_drainage>0) {
//                                        $result_data['is_initials'] = 0;
                                    } else {
                                        $result_data['is_initials'] = 1;
                                    }
                                }

                                $result_data['is_exist'] = 0;
                                $sql = " select "
                                        . " a.id, a.user_id, a.type, a.job_number, a.community, a.lot, a.address as addr, a.start_date as date, a.end_date as date_l, a.initials as init, a.region, a.field_manager as fm, "
                                        . " a.latitude, a.longitude, a.accuracy, a.house_ready as ready, a.overall_comments as overall, a.image_front_building, a.image_signature, "
                                        . " a.is_first, a.is_initials, "
                                        . " a.result_code as result, "
                                        . " a.city, a.area, a.volume, a.qn, a.wall_area as w_area, a.ceiling_area as c_area, a.design_location as des_loc, "
                                        . " a.image_testing_setup as setup, a.image_manometer as mano, "
                                        . " a.house_pressure  as pressure, a.flow, "
                                        . " a.qn_out, a.ach50, a.result_duct_leakage as duct_leakage, a.result_envelop_leakage as envelop_leakage "
                                        . " from ins_inspection a "
                                        . " where ";

                                if ($edit_inspection_id!="") {
                                    $sql .= " a.id='$edit_inspection_id' ";
                                } else {
                                    $sql .= " replace(a.job_number,'-','')=replace('$job','-','') and a.type='$type' ";

                                    if ($result_data['is_bu']==1) {
                                        $sql .= " and a.address='$address' and a.is_building_unit=1 ";
                                    }

                                    $sql .= " order by a.start_date desc, a.id desc "
                                        . " limit 1 ";
                                }

                                $inspection = $this->utility_model->get__by_sql($sql);

                                if ($inspection) {
                                    $result_data['is_exist'] = 1;

                                    $inspection['loc'] = array(
                                        'lat'=>$inspection['latitude'],
                                        'lon'=>$inspection['longitude'],
                                        'acc'=>$inspection['accuracy'],
                                    );

                                    $inspection['is_exist'] = $inspection['is_first']=='1' ? "0" : "1";

                                    if (isset($inspection['image_front_building']) && $inspection['image_front_building']!="") {
                                        $inspection['front'] = array(
                                            'mode'=>2,
                                            'img'=>$inspection['image_front_building'],
                                        );
                                    } else {
                                        $inspection['front'] = "";
                                    }

                                    if (isset($inspection['image_signature']) && $inspection['image_signature']!="") {
                                        $inspection['sign'] = array(
                                            'mode'=>2,
                                            'img'=>$inspection['image_signature'],
                                        );
                                    } else {
                                        $inspection['sign'] = "";
                                    }

                                    $inspection['ex1'] = "";
                                    $inspection['ex2'] = "";
                                    $inspection['ex3'] = "";
                                    $inspection['ex4'] = "";

                                    $exception_images = $this->utility_model->get_list('ins_exception_image', array('inspection_id'=>$inspection['id']));
                                    if ($exception_images) {
                                        $i = 1;
                                        foreach ($exception_images as $row) {
                                            if (isset($row['image']) && $row['image']!="") {
                                                $inspection['ex' . $i] = array(
                                                    'mode'=>2,
                                                    'img'=>$row['image'],
                                                );
                                            } else {
//                                                $inspection['ex' . $i] = "";
                                            }

                                            $i++;
                                        }
                                    }

                                    $result_email = array();
                                    $emails = $this->utility_model->get_list('ins_recipient_email', array('inspection_id'=>$inspection['id'], 'status'=>'0'));
                                    if ($emails) {
                                        foreach ($emails as $row) {
                                            array_push($result_email, $row['email']);
                                        }
                                    }

                                    $location = array(
                                        'left'=>$this->get_location($inspection['id'], 'Left', $type),
                                        'right'=>$this->get_location($inspection['id'], 'Right', $type),
                                        'front'=>$this->get_location($inspection['id'], 'Front', $type),
                                        'back'=>$this->get_location($inspection['id'], 'Back', $type),
                                    );

                                    $result_data['inspection'] = $inspection;
                                    $result_data['email'] = $result_email;
                                    $result_data['location'] = $location;

                                    $result_data['comment'] = $this->get_comment($inspection['id']);
                                }
                                else {
                                    // $inspection = $this->utility_model->get__by_sql("select a.id, a.user_id, a.type, a.job_number, a.community, a.lot, b.address as addr, a.start_date as date, a.end_date as date_l, a.initials as init, a.region, a.field_manager as fm, a.latitude, a.longitude, a.accuracy, a.house_ready as ready, a.overall_comments as overall, a.image_front_building, a.image_signature, a.is_first, a.is_initials, a.result_code as result
                                    //                                                 from ins_schedule b
                                    //                                                 left join ins_inspection a
                                    //                                                 on replace(b.job_number,'-','')=replace(a.job_number,'-','')
                                    //                                                 where replace(b.job_number,'-','')=replace('$job', '-','') order by a.created_at desc limit 1");
                                    // $result_data['inspection'] = $inspection;
                                }

                                $response['status'] = $this->status[0];
                            }
                        }
                        else if ($param == 'submit') {
                            $req = $this->input->get_post('request');
                            $app_version = $this->input->get_post('version');
                            if ($app_version===false || $app_version=="") {
                                $app_version = "1.0";
                            }

                            if ($req === false) {
                                $response['status'] = $this->status[3];
                            } else {

                                $ip = $this->get_client_ip();
                                $t = mdate('%Y%m%d%H%i%s', time());

                                $obj = json_decode($req);

                                $requested_inspection_id = $obj->requested_id;
                                $edit_inspection_id = isset($obj->inspection_id) ? $obj->inspection_id : "";
                                if ($edit_inspection_id===0 || $edit_inspection_id==="0") {
                                    $edit_inspection_id = "";
                                }

                                $data = array(
                                    'user_id' => $user_id,
                                    'type' => $type,
                                    'job_number' => $obj->job_number,
                                    'lot' => $obj->lot,
                                    'community' => $obj->community,
                                    'address' => $obj->address,
                                    'initials' => $obj->initials,
                                    'region' => $obj->region,
                                    'field_manager' => $obj->field_manager,
                                    'latitude' => $obj->latitude,
                                    'longitude' => $obj->longitude,
                                    'accuracy' => $obj->accuracy,
                                    'image_front_building' => $obj->front_building,
                                    'house_ready' => $obj->house_ready,
                                    'overall_comments' => $obj->overall_comments,
                                    'result_code' => $obj->result_code,
                                    'image_signature' => $obj->signature,
                                    'is_first' => $obj->is_first,
                                    'is_initials' => $obj->is_initials,
                                    'ip_address' => $ip,
                                    'created_at' => $t,
                                    'requested_id' =>$requested_inspection_id,

                                    'app_version'=>$app_version,
                                );

                                if ($edit_inspection_id!="") {

                                } else {
                                    $data['start_date'] = date('Y-m-d', time()); // $obj->start_date,
                                    $data['end_date'] = date('Y-m-d', time()); // $obj->start_date,
                                }

                                if ($edit_inspection_id!="") {

                                } else {
                                    if (isset($obj->is_building_unit))  {
                                        $data['is_building_unit'] = $obj->is_building_unit;

                                        $old_inspection = $this->utility_model->get('ins_inspection', array('type'=>$type, 'job_number'=>$obj->job_number, 'address'=>$obj->address, 'is_building_unit'=>1));
                                        if ($old_inspection) {
                                        } else {
                                            $data['first_submitted'] = 1;
                                        }
                                    } else {
                                        $old_inspection = $this->utility_model->get('ins_inspection', array('type'=>$type, 'job_number'=>$obj->job_number));
                                        if ($old_inspection) {
                                        } else {
                                            $data['first_submitted'] = 1;
                                        }
                                    }
                                }

                                $inspection_id = false;
                                $this->utility_model->start();

                                if ($edit_inspection_id!="") {
                                    if ($this->utility_model->update('ins_inspection', $data, array('id'=>$edit_inspection_id))) {
                                        $inspection_id = $edit_inspection_id;
                                    }
                                } else {
                                    if ($this->utility_model->insert('ins_inspection', $data)) {
                                        $inspection_id = $this->utility_model->new_id();
                                    }
                                }

                                if ($inspection_id!==false) {
                                    $this->utility_model->delete('ins_exception_image', array('inspection_id' => $inspection_id));
                                    if (isset($obj->exception_1) && $obj->exception_1!="") {
                                        $this->utility_model->insert('ins_exception_image', array('inspection_id' => $inspection_id, 'image'=>$obj->exception_1));
                                    }
                                    if (isset($obj->exception_2) && $obj->exception_2!="") {
                                        $this->utility_model->insert('ins_exception_image', array('inspection_id' => $inspection_id, 'image'=>$obj->exception_2));
                                    }
                                    if (isset($obj->exception_3) && $obj->exception_3!="") {
                                        $this->utility_model->insert('ins_exception_image', array('inspection_id' => $inspection_id, 'image'=>$obj->exception_3));
                                    }
                                    if (isset($obj->exception_4) && $obj->exception_4!="") {
                                        $this->utility_model->insert('ins_exception_image', array('inspection_id' => $inspection_id, 'image'=>$obj->exception_4));
                                    }

                                    $this->utility_model->delete('ins_recipient_email', array('inspection_id' => $inspection_id));
                                    if (is_array($obj->emails)) {
                                        foreach ($obj->emails as $row) {
                                            $this->utility_model->insert('ins_recipient_email', array('inspection_id' => $inspection_id, 'email' => $row));
                                        }
                                    }

                                    $this->utility_model->delete('ins_location', array('inspection_id' => $inspection_id));
                                    $this->utility_model->delete('ins_checklist', array('inspection_id' => $inspection_id));
                                    if (is_array($obj->locations)) {
                                        foreach ($obj->locations as $row) {
                                            if ($this->utility_model->insert('ins_location', array('inspection_id' => $inspection_id, 'name' => $row->name))) {
                                                $location_id = $this->utility_model->new_id();

                                                if (is_array($row->checklist)) {

                                                    foreach ($row->checklist as $row1) {
                                                        $this->utility_model->insert('ins_checklist', array('inspection_id' => $inspection_id, 'location_id' => $location_id, 'no' => $row1->no, 'status' => $row1->status, 'primary_photo' => $row1->primary, 'secondary_photo' => $row1->secondary, 'description' => $row1->description));
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    $this->utility_model->delete('ins_inspection_comment', array('inspection_id' => $inspection_id));
                                    if (is_array($obj->comments)) {
                                        foreach ($obj->comments as $row1) {
                                            $this->utility_model->insert('ins_inspection_comment', array('inspection_id' => $inspection_id, 'no' => $row1->no, 'status' => $row1->status, 'primary_photo' => $row1->primary, 'secondary_photo' => $row1->secondary, 'description' => $row1->description));
                                        }
                                    }

                                    $today = mdate('%Y-%m-%d', time());
                                    $this->utility_model->update('ins_inspection_requested', array('status'=>2, 'completed_at'=>$today), array('id'=>$requested_inspection_id));

                                    $this->utility_model->complete();

                                    $result_data['inspection_id'] = $inspection_id;
                                    $response['status'] = $this->status[0];
                                }
                                else {
                                    $response['status'] = $this->status[1];
                                }
                            }
                        }
                        else {
                            $response['status'] = $this->status[2];
                        }
                    } else {
                        $response['status'] = $this->status[4];
                    }
                }
            }
            else if ($kind == 'wci') {
                $type = 3;
                $user_id = $this->input->get_post('user_id');

                if ($user_id === false) {
                    $response['status'] = $this->status[3];
                }
                else {
                    $user_id = $this->utility_model->decode($user_id);
                    $user = $this->utility_model->get('ins_user', array('id' => $user_id));
                    if ($user) {
                        if ($param=='check') {

                        }
                        else if ($param == 'submit') {
                            $req = $this->input->get_post('request');
                            $app_version = $this->input->get_post('version');
                            if ($app_version===false || $app_version=="") {
                                $app_version = "1.0";
                            }

                            if ($req === false) {
                                $response['status'] = $this->status[3];
                            } else {

                                $ip = $this->get_client_ip();
                                $t = mdate('%Y%m%d%H%i%s', time());

                                $obj = json_decode($req);
                                $requested_inspection_id = $obj->requested_id;

                                $data = array(
                                    'user_id' => $user_id,
                                    'type' => $type,
                                    'job_number' => $obj->job_number,
                                    'lot' => $obj->lot,
                                    'community' => $obj->community,
                                    'address' => $obj->address,
                                    'start_date' => date('Y-m-d', time()), // $obj->start_date, // date("m/d/Y", strtotime($obj->start_date)),
                                    'end_date' => date('Y-m-d', time()), //$obj->end_date, // date("m/d/Y", strtotime($obj->end_date)),
//                                    'initials' => $obj->initials,
                                    'region' => $obj->region,
                                    'field_manager' => $obj->field_manager,
                                    'latitude' => $obj->latitude,
                                    'longitude' => $obj->longitude,
                                    'accuracy' => $obj->accuracy,
                                    'image_front_building' => $obj->front_building,
                                    'house_ready' => $obj->house_ready,
                                    'overall_comments' => $obj->overall_comments,
//                                    'result_code' => $obj->result_code,
                                    'image_signature' => $obj->signature,
//                                    'is_first' => $obj->is_first,
//                                    'is_initials' => $obj->is_initials,
                                    'ip_address' => $ip,
                                    'created_at' => $t,
                                    'requested_id' =>$requested_inspection_id,

                                    'city'=>$obj->city,
                                    'area'=>$obj->area,
                                    'volume'=>$obj->volume,
                                    'qn'=>$obj->qn,

                                    'wall_area'=>$obj->wall_area,
                                    'ceiling_area'=>$obj->ceiling_area,
                                    'design_location'=>$obj->design_location,

                                    'image_testing_setup'=>$obj->testing_setup,
                                    'image_manometer'=>$obj->manometer,

                                    'house_pressure'=>$obj->house_pressure,
                                    'flow'=>$obj->flow,

                                    'result_duct_leakage'=>$obj->result_duct_leakage,
                                    'result_envelop_leakage'=>$obj->result_envelop_leakage,

                                    'qn_out'=>$obj->qn_out,
                                    'ach50'=>$obj->ach50,

                                    'app_version'=>$app_version,
                                );

                                if (isset($obj->is_building_unit))  {
                                    $data['is_building_unit'] = $obj->is_building_unit;

                                    $old_inspection = $this->utility_model->get('ins_inspection', array('type'=>$type, 'job_number'=>$obj->job_number, 'address'=>$obj->address, 'is_building_unit'=>1));
                                    if ($old_inspection) {
                                    } else {
                                        $data['first_submitted'] = 1;
                                    }
                                } else {
                                    $old_inspection = $this->utility_model->get('ins_inspection', array('type'=>$type, 'job_number'=>$obj->job_number));
                                    if ($old_inspection) {
                                    } else {
                                        $data['first_submitted'] = 1;
                                    }
                                }

                                if ($this->utility_model->insert('ins_inspection', $data)) {
                                    $inspection_id = $this->utility_model->new_id();

                                    if (is_array($obj->unit)) {
                                        foreach ($obj->unit as $row) {
                                            $this->utility_model->insert('ins_unit', array('inspection_id'=>$inspection_id, 'no'=>$row->no, 'supply'=>$row->supply, 'return'=>$row->return));
                                        }
                                    }

                                    $today = mdate('%Y-%m-%d', time());
                                    $this->utility_model->update('ins_inspection_requested', array('status'=>2, 'completed_at'=>$today), array('id'=>$requested_inspection_id));

                                    $result_data['inspection_id'] = $inspection_id;
                                    $response['status'] = $this->status[0];
                                } else {
                                    $response['status'] = $this->status[1];
                                }
                            }
                        }
                        else {
                            $response['status'] = $this->status[2];
                        }
                    } else {
                        $response['status'] = $this->status[4];
                    }
                }
            }
            else if ($param=='requested') {
                $user_id = $this->input->get_post('user_id');
                $requested_date = $this->input->get_post('date');


                $response['requested_date'] = $requested_date;

                if ($requested_date===false) {
                    $requested_date = "";
                }

                if ($user_id === false) {
                    $response['status'] = $this->status[3];
                } else {
                    $user_id = $this->utility_model->decode($user_id);
                    $response['user_id'] = $user_id;

                    $user = $this->utility_model->get('ins_user', array('id' => $user_id));
                    if ($user) {
                        $table = " ins_inspection_requested a "
                               . " left join ins_community c on c.community_id=substr(a.job_number,1,4) "
//                               . " left join ins_region r on c.region=r.id "
                               . " left join ins_admin m on a.manager_id=m.id "
                               . " ";

                        $sql = " select a.id, a.category, a.reinspection, a.epo_number, a.job_number, a.lot, a.requested_at, "
                                . " a.assigned_at, a.completed_at, a.manager_id, a.inspector_id, "
                                . " a.time_stamp, a.ip_address, a.community_name, a.lot, a.address, a.status, a.area, a.volume, a.qn, a.is_building_unit, "
                                . " a.city as city_duct, a.wall_area, a.ceiling_area, a.design_location, "
                                . " a.inspection_id as edit_inspection_id, "
//                                . " concat(m.first_name, ' ', m.last_name) as field_managenoasr_name, "
//                                . " c1.name as category_name, "
                                . " c.community_id, c.city, m.region "
//                                . " r.region as region_name, "
//                                . " u.first_name, u.last_name "
                                . " from ins_user u, " . $table . " where u.id=a.inspector_id and a.status=1 and a.inspector_id='" . $user_id . "' ";

                        if ($requested_date!="") {
                            $sql .= " and a.requested_at >= '$requested_date' ";
                        }

                        $sql .= " order by a.requested_at asc, a.job_number asc ";
                        $response['sql'] = $sql;
                        $requested_list = $this->utility_model->get_list__by_sql($sql);
                        $result_data = $requested_list;

                        $response['status'] = $this->status[0];
                    } else {
                        $response['status'] = $this->status[4];
                    }
                }
            }
            else {
                $response['status'] = $this->status[2];
            }
        }
        else if ($method == 'send') {
            $user_id = $this->input->get_post('user_id');
            $inspection_id = $this->input->get_post('inspection_id');

            if ($user_id !== false && $inspection_id !== false) {
                $user_id = $this->utility_model->decode($user_id);
                $inspection_id = $this->utility_model->decode($inspection_id);

                $inspection = $this->utility_model->get('ins_inspection', array('user_id' => $user_id, 'id' => $inspection_id));
                if ($inspection) {
                    $report = $this->send_report($user_id, $inspection_id);
                    if ($report===false) {
                        $response['status'] = $this->status[1];
                    } else {
//                        $result_data['email'] = $report;
                        $response['status'] = $this->status[0];
                    }
                }
                else {
                    $response['status'] = $this->status[3];
                }
            } else {
                $response['status'] = $this->status[3];
            }
        }
        else if ($method == 'community') {

            if ($param=='check') {
                $community_id = $this->input->get_post('community_id');
                if ($community_id!==false) {
                    $community = $this->utility_model->get__by_sql(" select a.* from ins_community a, ins_region r where a.community_id='$community_id' and r.id=a.region ");
                    if ($community) {
                        $result_data['region'] = $community['region'];
                        $result_data['community_name'] = $community["community_name"];
                    } else {
                        $result_data['region'] = 0;
                        $result_data['community_name'] = "";
                        $result_data['regions'] = $this->utility_model->get_list('ins_region', array());
                    }

                    $t = mdate('%m/%d/%Y', time());
                    $result_data['date'] = $t;

                    $response['status'] = $this->status[0];
                } else {
                    $response['status'] = $this->status[3];
                }
            }
            else {
                $response['status'] = $this->status[3];
            }
        }
        else if ($method == 'sync') {
            if ($param=='region') {
                $ids = $this->input->get_post('ids');
                if ($ids===false || !is_array($ids)) {
                    $result_data['region'] = $this->utility_model->get_list('ins_region', array());
                    $result_data['delete'] = array();
                } else {
                    $result_data['region'] = $this->utility_model->get_list('ins_region', array());
                    $result_data['delete'] = array();

                    foreach ($ids as $row) {
                        $region = $this->utility_model->get('ins_region', array('id'=>$row));
                        if ($region) {

                        } else {
                            array_push($result_data['delete'], $row);
                        }
                    }
                }

                $response['status'] = $this->status[0];
            }
            else if ($param=='field_manager') {
                $ids = $this->input->get_post('ids');

                if ($ids===false || !is_array($ids)) {
                    $result_data['fm'] = $this->utility_model->get_list('ins_admin', array('kind'=>2));
                    $result_data['delete'] = array();
                } else {
                    $result_data['fm'] = $this->utility_model->get_list('ins_admin', array('kind'=>2));
                    $result_data['delete'] = array();

                    foreach ($ids as $row) {
                        $fm = $this->utility_model->get('ins_admin', array('id'=>$row));
                        if ($fm) {
                        } else {
                            array_push($result_data['delete'], $row);
                        }
                    }
                }

                $response['status'] = $this->status[0];
            }
            else {
                $response['status'] = $this->status[3];
            }
        }
        else {
            $response['status'] = $this->status[2];
        }

        $response['request']['data'] = $request_data;
        $response['response'] = $result_data;

        print_r(json_encode($response, JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG));
    }

    // version 2.0
    public function v2($method = '', $param = '', $kind = '') {
        $response = array(
            'status' => $this->status[1],
            'request' => array(
                'method' => $method,
                'param' => $param,
                'kind' => $kind,
                'data' => array()
            ),
            'response' => array(
            )
        );

        $request_data = array();
        $result_data = array();

        if ($method == 'user') {
            if ($param == 'field_manager') {
                $region = $this->input->get_post('region');

                if ($region === false) {
                    $response['status'] = $this->status[3];
                } else {
                    $region = $this->utility_model->decode($region);

                    $user = $this->utility_model->get_list__by_sql(" select a.* from ins_admin a where a.kind=2 and a.status=1 and a.id in ( select manager_id from ins_admin_region where region='$region' ) ");
                    if ($user) {
                        $result_data['user'] = $user;
                    } else {
                        $result_data['user'] = array();
                    }

                    $response['status'] = $this->status[0];
                }
            }
            else {
                $response['status'] = $this->status[2];
            }
        }
        else if ($method == 'sync') {
            if ($param=='region') {
                $ids = $this->input->get_post('ids');
                if ($ids===false || !is_array($ids)) {
                    $result_data['region'] = $this->utility_model->get_list('ins_region', array());
                    $result_data['delete'] = array();
                } else {
                    $result_data['region'] = $this->utility_model->get_list('ins_region', array());
                    $result_data['delete'] = array();

                    foreach ($ids as $row) {
                        $region = $this->utility_model->get('ins_region', array('id'=>$row));
                        if ($region) {

                        } else {
                            array_push($result_data['delete'], $row);
                        }
                    }
                }

                $response['status'] = $this->status[0];
            }
            else if ($param=='field_manager') {
                $ids = $this->input->get_post('ids');

                if ($ids===false || !is_array($ids)) {
                    $fm = $this->utility_model->get_list('ins_admin', array('kind'=>2));
                    $result_data['delete'] = array();
                } else {
                    $fm = $this->utility_model->get_list('ins_admin', array('kind'=>2));
                    $result_data['delete'] = array();

                    foreach ($ids as $row) {
                        if ($this->utility_model->get('ins_admin', array('id'=>$row))) {
                        } else {
                            array_push($result_data['delete'], $row);
                        }
                    }
                }

                $fms = array();
                if (isset($fm) && is_array($fm)) {
                    foreach ($fm as $row) {
                        $region = "";

                        $ffff = $this->utility_model->get_list__by_sql(" select a.region from ins_admin_region a where a.manager_id='" . $row['id'] . "' ");
                        if ($ffff) {
                            foreach ($ffff as $rrr) {
                                $region .= "r" . $rrr['region'] . "r";
                            }
                        }

                        $row['region'] = $region;
                        array_push($fms, $row);
                    }
                }
                $result_data['fm'] = $fms;

                $response['status'] = $this->status[0];
            }
            else {
                $response['status'] = $this->status[3];
            }
        } else if ($method == 'optimize') {
            $report_keep_day = 30;
            $configuration = $this->utility_model->get('sys_config', array('code'=>'report_keep_day'));
            if ($configuration) {
                $report_keep_day = intval($configuration['value']);
            }

            $current_time = time();

            $path = "resource/upload/report/";
            $files = scandir($path);
            foreach ($files as $file) {
                $full_path = $path . $file;
                if (is_file($full_path)) {
                    $ext = pathinfo($full_path, PATHINFO_EXTENSION);
                    if (strtolower($ext)=="pdf") {
                        if ($current_time - filemtime($full_path)>=30 * 24 * 60 * 60) {
                            unlink($full_path);
                            array_push($result_data,  $file);
                        }
                    }
                }
            }

            $response['status'] = $this->status[0];
        }

        $response['request']['data'] = $request_data;
        $response['response'] = $result_data;

        print_r(json_encode($response, JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG));
    }

    public function upload($kind = '', $type = '') {
        $msg = array('code' => 1, 'message' => 'Failed!', 'url' => '', 'path' => '');
        $dir_name = "";

        if ($kind != "") {
            if ($type != "") {
                $dir_name = "resource/upload/$kind/$type/";
            } else {
                $dir_name = "resource/upload/$kind/";
            }

            $uu_id = $this->uuid->v4();
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

            $fname = mdate('%Y%m%d%H%i%s', time()) . "_" . $uu_id . "." . $ext;
            $new_name = $dir_name . $fname;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $new_name)) {
                $msg['url'] = base_url() . $new_name;
                $msg['path'] = $fname;
                $msg['code'] = 0;
                $msg['message'] = "Success!";
            }
        }

        print_r(json_encode($msg));
    }

    public function export($kind = '', $method = '') {
        if ($kind == 'inspection') {
            ini_set('memory_limit', '512M');

            $inspection_id = $this->input->get_post('id');
//            $inspection_id = $this->utility_model->decode($inspection_id);

            $type = $this->input->get_post('type');
            if ($type===false) {
                $type = "full";
            }

            if ($type=='duct' || $type=='envelop') {
                $this->m_pdf->initialize("B4-C", "P");
            } else {
                $this->m_pdf->initialize();
            }


            $html = "";
            if ($type=='duct') {
                $html = $this->get_report_html__for_duct_leakage($inspection_id);
            } else if ($type=='envelop') {
                $html = $this->get_report_html__for_envelop_leakage($inspection_id);
            } else {
                $html = $this->get_report_html($inspection_id, $type);
            }

            $this->m_pdf->pdf->WriteHTML($html);
            $this->m_pdf->pdf->Output("report.pdf", "D");
        }

        if ($kind == 'statistics') {
            if ($method == 'inspection') {
                if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
                    ini_set('memory_limit', '512M');

                    $file_format = $this->input->get_post('file_format');
                    if ($file_format===false || $file_format=="") {
                        $file_format = "pdf";
                    }

                    $description = $this->input->get_post('desc');
                    if ($description===false || $description=="") {
                        $description = "1";
                    }

                    $region = $this->input->get_post('region');
                    $community = $this->input->get_post('community');
                    $start_date = $this->input->get_post('start_date');
                    $end_date = $this->input->get_post('end_date');
                    $status = $this->input->get_post('status');
                    $type = $this->input->get_post('type');

                    if ($region===false) {
                        $region = "";
                    }
                    if ($community===false) {
                        $community = "";
                    }
                    if ($start_date===false) {
                        $start_date = "";
                    }
                    if ($end_date===false) {
                        $end_date = "";
                    }
                    if ($status===false) {
                        $status = "";
                    }
                    if ($type===false) {
                        $type = "";
                    }

                    if ($file_format == "pdf") {
                        $this->m_pdf->initialize();
                        $html = $this->get_report_data__for_statistics_inspection($region, $community, $start_date, $end_date, $status, $type, false, intval($description)===1);

                        $this->m_pdf->pdf->WriteHTML($html);
                        $this->m_pdf->pdf->Output("report.pdf", "D");
                    }

                    if ($file_format == "csv") {
                        $data = $this->get_report_data__for_statistics_inspection($region, $community, $start_date, $end_date, $status, $type, true, intval($description)===1);
                        array_to_csv($data, "report.csv");
                    }
                }
            }

            if ($method == 're_inspection') {
                if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
                    ini_set('memory_limit', '512M');

                    $file_format = $this->input->get_post('file_format');
                    if ($file_format===false || $file_format=="") {
                        $file_format = "pdf";
                    }

                    $description = $this->input->get_post('desc');
                    if ($description===false || $description=="") {
                        $description = "1";
                    }

                    $region = $this->input->get_post('region');
                    $community = $this->input->get_post('community');
                    $start_date = $this->input->get_post('start_date');
                    $end_date = $this->input->get_post('end_date');
                    $status = $this->input->get_post('status');
                    $type = $this->input->get_post('type');

                    if ($region===false) {
                        $region = "";
                    }
                    if ($community===false) {
                        $community = "";
                    }
                    if ($start_date===false) {
                        $start_date = "";
                    }
                    if ($end_date===false) {
                        $end_date = "";
                    }
                    if ($status===false) {
                        $status = "";
                    }
                    if ($type===false) {
                        $type = "";
                    }

                    if ($file_format == "pdf") {
                        $this->m_pdf->initialize();

                        $html = $this->get_report_data__for_statistics_re_inspection($region, $community, $start_date, $end_date, $status, $type, false, intval($description)===1);

                        $this->m_pdf->pdf->WriteHTML($html);
                        $this->m_pdf->pdf->Output("report.pdf", "D");
                    }

                    if ($file_format == "csv") {
                        $data = $this->get_report_data__for_statistics_re_inspection($region, $community, $start_date, $end_date, $status, $type, true, intval($description)===1);
                        array_to_csv($data, "report.csv");
                    }
                }
            }

            if ($method == 'checklist') {
                if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
                    ini_set('memory_limit', '512M');

                    $file_format = $this->input->get_post('file_format');
                    if ($file_format===false || $file_format=="") {
                        $file_format = "pdf";
                    }

                    $region = $this->input->get_post('region');
                    $community = $this->input->get_post('community');
                    $start_date = $this->input->get_post('start_date');
                    $end_date = $this->input->get_post('end_date');
                    $status = $this->input->get_post('status');
                    $type = $this->input->get_post('type');

                    if ($region===false) {
                        $region = "";
                    }
                    if ($community===false) {
                        $community = "";
                    }
                    if ($start_date===false) {
                        $start_date = "";
                    }
                    if ($end_date===false) {
                        $end_date = "";
                    }
                    if ($status===false) {
                        $status = "";
                    }
                    if ($type===false) {
                        $type = "";
                    }

                    if ($file_format == "pdf") {
                        $this->m_pdf->initialize();
                        $html = $this->get_report_data__for_statistics_checklist($region, $community, $start_date, $end_date, $status, $type);

                        $this->m_pdf->pdf->WriteHTML($html);
                        $this->m_pdf->pdf->Output("report.pdf", "D");
                    }

                    if ($file_format == "csv") {
                        $data = $this->get_report_data__for_statistics_checklist($region, $community, $start_date, $end_date, $status, $type, true);
                        array_to_csv($data, "report.csv");
                    }
//                    echo $html;
                }
            }

            if ($method == 'fieldmanager') {
                if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
                    ini_set('memory_limit', '512M');

                    $file_format = $this->input->get_post('file_format');
                    if ($file_format===false || $file_format=="") {
                        $file_format = "pdf";
                    }

                    $region = $this->input->get_post('region');
                    $start_date = $this->input->get_post('start_date');
                    $end_date = $this->input->get_post('end_date');
                    $type = $this->input->get_post('type');

                    if ($region===false) {
                        $region = "";
                    }
                    if ($start_date===false) {
                        $start_date = "";
                    }
                    if ($end_date===false) {
                        $end_date = "";
                    }
                    if ($type===false) {
                        $type = "";
                    }

                    if ($file_format == "pdf") {
                        $this->m_pdf->initialize();
                        $html = $this->get_report_data__for_statistics_fieldmanager($region, $start_date, $end_date, $type);

                        $this->m_pdf->pdf->WriteHTML($html);
                        $this->m_pdf->pdf->Output("report.pdf", "D");
                    }

                    if ($file_format == "csv") {
                        $data = $this->get_report_data__for_statistics_fieldmanager($region, $start_date, $end_date, $type, true);
                        array_to_csv($data, "report.csv");
                    }

//                    echo $html;
                }
            }

            if ($method == 'inspector') {
                if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
                    ini_set('memory_limit', '512M');

                    $file_format = $this->input->get_post('file_format');
                    if ($file_format===false || $file_format=="") {
                        $file_format = "pdf";
                    }

                    $region = $this->input->get_post('region');
                    $start_date = $this->input->get_post('start_date');
                    $end_date = $this->input->get_post('end_date');
                    $type = $this->input->get_post('type');

                    if ($region===false) {
                        $region = "";
                    }
                    if ($start_date===false) {
                        $start_date = "";
                    }
                    if ($end_date===false) {
                        $end_date = "";
                    }
                    if ($type===false) {
                        $type = "";
                    }

                    if ($file_format == "pdf") {
                        $this->m_pdf->initialize();
                        $html = $this->get_report_data__for_statistics_inspector($region, $start_date, $end_date, $type);

                        $this->m_pdf->pdf->WriteHTML($html);
                        $this->m_pdf->pdf->Output("report.pdf", "D");
                    }

                    if ($file_format == "csv") {
                        $data = $this->get_report_data__for_statistics_inspector($region, $start_date, $end_date, $type, true);
                        array_to_csv($data, "report.csv");
                    }
//                    echo $html;
                }
            }
        }

        if ($kind == 'scheduling') {
            $this->load->helper('csv');

            $region = $this->input->get_post('region');
            $community = $this->input->get_post('community');
            $start_date = $this->input->get_post('start_date');
            $end_date = $this->input->get_post('end_date');
            $inspector_id = $this->input->get_post('inspector_id');
            $ordering = $this->input->get_post('ordering');

            $data = $this->get_scheduling_data($inspector_id, $region, $community, $start_date, $end_date, $ordering);

            if (count($data)>0) {
                $filename = "schedule_" . $start_date . "_" . $end_date ;

                $user = $this->utility_model->get('ins_user', array('id'=>$inspector_id));
                if ($user) {
                    $filename .= "_" . $user['first_name'] . " " . $user['last_name'];
                }
                array_to_csv($data, $filename . ".csv");
            }
        }

        if ($kind == 'payable') {
            if ($method == 'payroll') {
                if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
                    ini_set('memory_limit', '512M');

                    $file_format = $this->input->get_post('file_format');
                    if ($file_format===false || $file_format=="") {
                        $file_format = "pdf";
                    }

                    $inspector = $this->input->get_post('inspector');
                    $period = $this->input->get_post('period');
                    $start_date = $this->input->get_post('start_date');
                    $end_date = $this->input->get_post('end_date');

                    if ($inspector===false) {
                        $inspector = "";
                    }
                    if ($period===false) {
                        $period = "";
                    }
                    if ($start_date===false) {
                        $start_date = "";
                    }
                    if ($end_date===false) {
                        $end_date = "";
                    }
                    if ($type===false) {
                        $type = "";
                    }

                    if ($file_format == "pdf") {
                        $this->m_pdf->initialize();
                        $html = $this->get_report_data__for_payable_payroll($inspector, $period, $start_date, $end_date);

                        $this->m_pdf->pdf->WriteHTML($html);
                        $this->m_pdf->pdf->Output("report.pdf", "D");
                    }

                    if ($file_format == "csv") {
                        $data = $this->get_report_data__for_payable_payroll($inspector, $period, $start_date, $end_date, true);
                        array_to_csv($data, "report.csv");
                    }
                }
            }

            if ($method == 're_inspection') {
                if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
                    ini_set('memory_limit', '512M');

                    $file_format = $this->input->get_post('file_format');
                    if ($file_format===false || $file_format=="") {
                        $file_format = "pdf";
                    }

                    $region = $this->input->get_post('region');
                    $community = $this->input->get_post('community');
                    $start_date = $this->input->get_post('start_date');
                    $end_date = $this->input->get_post('end_date');
                    $status = $this->input->get_post('status');
                    $type = $this->input->get_post('type');
                    $epo_status = $this->input->get_post('epo_status');

                    if ($region===false) {
                        $region = "";
                    }
                    if ($community===false) {
                        $community = "";
                    }
                    if ($start_date===false) {
                        $start_date = "";
                    }
                    if ($end_date===false) {
                        $end_date = "";
                    }
                    if ($status===false) {
                        $status = "";
                    }
                    if ($type===false) {
                        $type = "";
                    }
                    if ($epo_status===false) {
                        $epo_status = "";
                    }

                    if ($file_format == "pdf") {
                        $this->m_pdf->initialize();

                        $html = $this->get_report_data__for_payable_re_inspection($region, $community, $start_date, $end_date, $status, $type, $epo_status, false);

                        $this->m_pdf->pdf->WriteHTML($html);
                        $this->m_pdf->pdf->Output("report.pdf", "D");
                    }

                    if ($file_format == "csv") {
                        $data = $this->get_report_data__for_payable_re_inspection($region, $community, $start_date, $end_date, $status, $type, $epo_status, true);
                        array_to_csv($data, "report.csv");
                    }
                }
            }

            if ($method == 'pending_inspection') {
                if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
                    ini_set('memory_limit', '512M');

                    $file_format = $this->input->get_post('file_format');
                    if ($file_format===false || $file_format=="") {
                        $file_format = "pdf";
                    }

                    $region = $this->input->get_post('region');
                    $community = $this->input->get_post('community');
                    $start_date = $this->input->get_post('start_date');
                    $end_date = $this->input->get_post('end_date');
                    $status = $this->input->get_post('status');
                    $type = $this->input->get_post('type');
                    $epo_status = $this->input->get_post('epo_status');
                    $payment_status = $this->input->get_post('payment_status');
                    $re_inspection = $this->input->get_post('re_inspection');

                    if ($region===false) {
                        $region = "";
                    }
                    if ($community===false) {
                        $community = "";
                    }
                    if ($start_date===false) {
                        $start_date = "";
                    }
                    if ($end_date===false) {
                        $end_date = "";
                    }
                    if ($status===false) {
                        $status = "";
                    }
                    if ($type===false) {
                        $type = "";
                    }
                    if ($epo_status===false) {
                        $epo_status = "";
                    }
                    if ($payment_status===false) {
                        $payment_status = "";
                    }
                    if ($re_inspection===false) {
                        $re_inspection = "";
                    }

                    if ($file_format == "pdf") {
                        $this->m_pdf->initialize();

                        $html = $this->get_report_data__for_payable_pending_inspection($region, $community, $start_date, $end_date, $status, $type, $epo_status, $payment_status, $re_inspection, false);

                        $this->m_pdf->pdf->WriteHTML($html);
                        $this->m_pdf->pdf->Output("report.pdf", "D");
                    }

                    if ($file_format == "csv") {
                        $data = $this->get_report_data__for_payable_pending_inspection($region, $community, $start_date, $end_date, $status, $type, $epo_status, $payment_status, $re_inspection, true);
                        array_to_csv($data, "report.csv");
                    }
                }
            }

        }

        if ($kind == 'requested_inspection') {
            if ($this->session->userdata('user_id')) {
                ini_set('memory_limit', '512M');

                $this->load->helper('csv');

                $start_date = $this->input->get_post('start_date');
                $end_date = $this->input->get_post('end_date');
                $type = $this->input->get_post('type');
                $status = $this->input->get_post('status');

                if ($start_date===false) {
                    $start_date = "";
                }
                if ($end_date===false) {
                    $end_date = "";
                }
                if ($status===false) {
                    $status = "";
                }
                if ($type===false) {
                    $type = "";
                }

                $data = $this->get_report_data__for_requested_inspection($start_date, $end_date, $status, $type, true);
                array_to_csv($data, "report.csv");
            }

        }
    }

    public function email($kind, $method="") {
        $response = array('code'=>-1, 'message'=>'Failed to send email');

        if ($kind == 'statistics') {
            if ($method == 'inspection') {
                if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
                    ini_set('memory_limit', '512M');

                    $this->m_pdf->initialize();

                    $region = $this->input->get_post('region');
                    $community = $this->input->get_post('community');
                    $start_date = $this->input->get_post('start_date');
                    $end_date = $this->input->get_post('end_date');
                    $status = $this->input->get_post('status');
                    $type = $this->input->get_post('type');

                    if ($region===false) {
                        $region = "";
                    }
                    if ($community===false) {
                        $community = "";
                    }
                    if ($start_date===false) {
                        $start_date = "";
                    }
                    if ($end_date===false) {
                        $end_date = "";
                    }
                    if ($status===false) {
                        $status = "";
                    }
                    if ($type===false) {
                        $type = "";
                    }


                    $user_id = $this->session->userdata('user_id');
                    $user = $this->user_model->get_user__by_id('admin', $user_id);
                    if ($user) {
                        $uu_id = $this->uuid->v4();

                        $recipients = array();
                        array_push($recipients, array('email'=>$user['email']));

                        $recipient = $this->input->get_post('recipient');
                        if ($recipient!==false && $recipient!="") {
                            $emails = explode(",", $recipient);
                            if (is_array($emails)) {
                                foreach ($emails as $row) {
                                    $addr = trim($row);
                                    if (filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                                        array_push($recipients, array('email'=>$addr));
                                    }
                                }
                            }
                        }

                        $html = $this->get_report_data__for_statistics_inspection($region, $community, $start_date, $end_date, $status, $type);
                        $this->m_pdf->pdf->WriteHTML($html);

                        $filename = "resource/upload/report/report_" . $uu_id . ".pdf";
                        $this->m_pdf->pdf->Output($filename, "F");

                        $email_template = $this->get_report_html__for_mail($filename);

                        $result = $this->send_mail("Inspection Report", $email_template, $recipients, true);
                        if ($result=="") {
                            $response['code'] = 0;
                            $response['message'] = "Successfully Sent!";
                        } else {

                        }

                        sleep(1);
//                        unlink($filename);
                    }
                }
            }

            if ($method == 're_inspection') {
                if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
                    ini_set('memory_limit', '512M');

                    $this->m_pdf->initialize();

                    $region = $this->input->get_post('region');
                    $community = $this->input->get_post('community');
                    $start_date = $this->input->get_post('start_date');
                    $end_date = $this->input->get_post('end_date');
                    $status = $this->input->get_post('status');
                    $type = $this->input->get_post('type');

                    if ($region===false) {
                        $region = "";
                    }
                    if ($community===false) {
                        $community = "";
                    }
                    if ($start_date===false) {
                        $start_date = "";
                    }
                    if ($end_date===false) {
                        $end_date = "";
                    }
                    if ($status===false) {
                        $status = "";
                    }
                    if ($type===false) {
                        $type = "";
                    }


                    $user_id = $this->session->userdata('user_id');
                    $user = $this->user_model->get_user__by_id('admin', $user_id);
                    if ($user) {
                        $uu_id = $this->uuid->v4();

                        $recipients = array();
                        array_push($recipients, array('email'=>$user['email']));

                        $recipient = $this->input->get_post('recipient');
                        if ($recipient!==false && $recipient!="") {
                            $emails = explode(",", $recipient);
                            if (is_array($emails)) {
                                foreach ($emails as $row) {
                                    $addr = trim($row);
                                    if (filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                                        array_push($recipients, array('email'=>$addr));
                                    }
                                }
                            }
                        }

                        $html = $this->get_report_data__for_statistics_re_inspection($region, $community, $start_date, $end_date, $status, $type);
                        $this->m_pdf->pdf->WriteHTML($html);

                        $filename = "resource/upload/report/report_" . $uu_id . ".pdf";
                        $this->m_pdf->pdf->Output($filename, "F");

                        $email_template = $this->get_report_html__for_mail($filename);

                        $result = $this->send_mail("Inspection Report", $email_template, $recipients, true);
                        if ($result=="") {
                            $response['code'] = 0;
                            $response['message'] = "Successfully Sent!";
                        } else {

                        }

                        sleep(1);
//                        unlink($filename);
                    }
                }
            }

            if ($method == 'checklist') {
                if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
                    ini_set('memory_limit', '512M');
                    $this->m_pdf->initialize();

                    $region = $this->input->get_post('region');
                    $community = $this->input->get_post('community');
                    $start_date = $this->input->get_post('start_date');
                    $end_date = $this->input->get_post('end_date');
                    $status = $this->input->get_post('status');
                    $type = $this->input->get_post('type');

                    if ($region===false) {
                        $region = "";
                    }
                    if ($community===false) {
                        $community = "";
                    }
                    if ($start_date===false) {
                        $start_date = "";
                    }
                    if ($end_date===false) {
                        $end_date = "";
                    }
                    if ($status===false) {
                        $status = "";
                    }
                    if ($type===false) {
                        $type = "";
                    }

                    $user_id = $this->session->userdata('user_id');
                    $user = $this->user_model->get_user__by_id('admin', $user_id);
                    if ($user) {
                        $uu_id = $this->uuid->v4();

                        $recipients = array();
                        array_push($recipients, array('email'=>$user['email']));

                        $recipient = $this->input->get_post('recipient');
                        if ($recipient!==false && $recipient!="") {
                            $emails = explode(",", $recipient);
                            if (is_array($emails)) {
                                foreach ($emails as $row) {
                                    $addr = trim($row);
                                    if (filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                                        array_push($recipients, array('email'=>$addr));
                                    }
                                }
                            }
                        }

                        $html = $this->get_report_data__for_statistics_checklist($region, $community, $start_date, $end_date, $status, $type);
                        $this->m_pdf->pdf->WriteHTML($html);

                        $filename = "resource/upload/report/report_" . $uu_id . ".pdf";
                        $this->m_pdf->pdf->Output($filename, "F");

                        $email_template = $this->get_report_html__for_mail($filename);

                        $result = $this->send_mail("Inspection Report", $email_template, $recipients, true);
                        if ($result=="") {
                            $response['code'] = 0;
                            $response['message'] = "Successfully Sent!";
                        } else {

                        }

                        sleep(1);
//                        unlink($filename);
                    }
                }
            }

            if ($method == 'fieldmanager') {
                if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
                    ini_set('memory_limit', '512M');
                    $this->m_pdf->initialize();

                    $region = $this->input->get_post('region');
                    $start_date = $this->input->get_post('start_date');
                    $end_date = $this->input->get_post('end_date');
                    $type = $this->input->get_post('type');

                    if ($region===false) {
                        $region = "";
                    }
                    if ($start_date===false) {
                        $start_date = "";
                    }
                    if ($end_date===false) {
                        $end_date = "";
                    }
                    if ($type===false) {
                        $type = "";
                    }

                    $user_id = $this->session->userdata('user_id');
                    $user = $this->user_model->get_user__by_id('admin', $user_id);
                    if ($user) {
                        $uu_id = $this->uuid->v4();

                        $recipients = array();
                        array_push($recipients, array('email'=>$user['email']));

                        $recipient = $this->input->get_post('recipient');
                        if ($recipient!==false && $recipient!="") {
                            $emails = explode(",", $recipient);
                            if (is_array($emails)) {
                                foreach ($emails as $row) {
                                    $addr = trim($row);
                                    if (filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                                        array_push($recipients, array('email'=>$addr));
                                    }
                                }
                            }
                        }

                        $html = $this->get_report_data__for_statistics_fieldmanager($region, $start_date, $end_date, $type);
                        $this->m_pdf->pdf->WriteHTML($html);

                        $filename = "resource/upload/report/report_" . $uu_id . ".pdf";
                        $this->m_pdf->pdf->Output($filename, "F");

                        $email_template = $this->get_report_html__for_mail($filename);

                        $result = $this->send_mail("Inspection Report", $email_template, $recipients, true);
                        if ($result=="") {
                            $response['code'] = 0;
                            $response['message'] = "Successfully Sent!";
                        } else {

                        }

                        sleep(1);
//                        unlink($filename);
                    }
                }
            }

            if ($method == 'inspector') {
                if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
                    ini_set('memory_limit', '512M');
                    $this->m_pdf->initialize();

                    $region = $this->input->get_post('region');
                    $start_date = $this->input->get_post('start_date');
                    $end_date = $this->input->get_post('end_date');
                    $type = $this->input->get_post('type');

                    if ($region===false) {
                        $region = "";
                    }
                    if ($start_date===false) {
                        $start_date = "";
                    }
                    if ($end_date===false) {
                        $end_date = "";
                    }
                    if ($type===false) {
                        $type = "";
                    }

                    $user_id = $this->session->userdata('user_id');
                    $user = $this->user_model->get_user__by_id('admin', $user_id);
                    if ($user) {
                        $uu_id = $this->uuid->v4();

                        $recipients = array();
                        array_push($recipients, array('email'=>$user['email']));

                        $recipient = $this->input->get_post('recipient');
                        if ($recipient!==false && $recipient!="") {
                            $emails = explode(",", $recipient);
                            if (is_array($emails)) {
                                foreach ($emails as $row) {
                                    $addr = trim($row);
                                    if (filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                                        array_push($recipients, array('email'=>$addr));
                                    }
                                }
                            }
                        }

                        $html = $this->get_report_data__for_statistics_inspector($region, $start_date, $end_date, $type);
                        $this->m_pdf->pdf->WriteHTML($html);

                        $filename = "resource/upload/report/report_" . $uu_id . ".pdf";
                        $this->m_pdf->pdf->Output($filename, "F");

                        $email_template = $this->get_report_html__for_mail($filename);

                        $result = $this->send_mail("Inspection Report", $email_template, $recipients, true);
                        if ($result=="") {
                            $response['code'] = 0;
                            $response['message'] = "Successfully Sent!";
                        } else {

                        }

                        sleep(1);
//                        unlink($filename);
                    }
                }
            }
        }

        else if ($kind == 'inspection') {
            if ($this->session->userdata('user_id')) {
                ini_set('memory_limit', '512M');

                $inspection_id = $this->input->get_post('id');
                if ($inspection_id===false || $inspection_id=="") {
                    $response['message'] = "Invalid Inspection";
                } else {
                    $inspection = $this->utility_model->get('ins_inspection', array('id'=>$inspection_id));
                    if ($inspection) {
                        $recipients = array();
                        $recipient = $this->input->get_post('recipient');
                        if ($recipient!==false && $recipient!="") {
                            $emails = explode(",", $recipient);
                            if (is_array($emails)) {
                                foreach ($emails as $row) {
                                    $addr = trim($row);
                                    if (filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                                        array_push($recipients, $addr);
                                    }
                                }
                            }
                        }

                        $report = $this->send_report($this->session->userdata('user_id'), $inspection_id, true, $recipients);
                        if ($report===false) {
                            $response = $this->status[1];
                        } else {
    //                        $result_data['email'] = $report;
                            $response = $this->status[0];
                        }
                    } else {
                        $response['message'] = "Invalid Inspection";
                    }
                }
            }
        }

        else if ($kind == 'payable') {

            if ($method == 're_inspection') {
                if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
                    ini_set('memory_limit', '512M');

                    $this->m_pdf->initialize();

                    $region = $this->input->get_post('region');
                    $community = $this->input->get_post('community');
                    $start_date = $this->input->get_post('start_date');
                    $end_date = $this->input->get_post('end_date');
                    $status = $this->input->get_post('status');
                    $type = $this->input->get_post('type');
                    $epo_status = $this->input->get_post('epo_status');

                    if ($region===false) {
                        $region = "";
                    }
                    if ($community===false) {
                        $community = "";
                    }
                    if ($start_date===false) {
                        $start_date = "";
                    }
                    if ($end_date===false) {
                        $end_date = "";
                    }
                    if ($status===false) {
                        $status = "";
                    }
                    if ($type===false) {
                        $type = "";
                    }
                    if ($epo_status===false) {
                        $epo_status = "";
                    }

                    $user_id = $this->session->userdata('user_id');
                    $user = $this->user_model->get_user__by_id('admin', $user_id);
                    if ($user) {
                        $uu_id = $this->uuid->v4();

                        $recipients = array();
                        array_push($recipients, array('email'=>$user['email']));

                        $recipient = $this->input->get_post('recipient');
                        if ($recipient!==false && $recipient!="") {
                            $emails = explode(",", $recipient);
                            if (is_array($emails)) {
                                foreach ($emails as $row) {
                                    $addr = trim($row);
                                    if (filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                                        array_push($recipients, array('email'=>$addr));
                                    }
                                }
                            }
                        }

                        $html = $this->get_report_data__for_payable_re_inspection($region, $community, $start_date, $end_date, $status, $type, $epo_status);
                        $this->m_pdf->pdf->WriteHTML($html);

                        $filename = "resource/upload/report/report_" . $uu_id . ".pdf";
                        $this->m_pdf->pdf->Output($filename, "F");

                        $email_template = $this->get_report_html__for_mail($filename);

                        $result = $this->send_mail("Re-Inspections EPO", $email_template, $recipients, true);
                        if ($result=="") {
                            $response['code'] = 0;
                            $response['message'] = "Successfully Sent!";
                        } else {

                        }

                        sleep(1);
//                        unlink($filename);
                    }
                }
            }

            if ($method == 'pending_inspection') {
                if ($this->session->userdata('user_id') && $this->session->userdata('permission')==1) {
                    ini_set('memory_limit', '512M');

                    $this->m_pdf->initialize();

                    $region = $this->input->get_post('region');
                    $community = $this->input->get_post('community');
                    $start_date = $this->input->get_post('start_date');
                    $end_date = $this->input->get_post('end_date');
                    $status = $this->input->get_post('status');
                    $type = $this->input->get_post('type');
                    $epo_status = $this->input->get_post('epo_status');
                    $payment_status = $this->input->get_post('payment_status');
                    $re_inspection = $this->input->get_post('re_inspection');

                    if ($region===false) {
                        $region = "";
                    }
                    if ($community===false) {
                        $community = "";
                    }
                    if ($start_date===false) {
                        $start_date = "";
                    }
                    if ($end_date===false) {
                        $end_date = "";
                    }
                    if ($status===false) {
                        $status = "";
                    }
                    if ($type===false) {
                        $type = "";
                    }
                    if ($epo_status===false) {
                        $epo_status = "";
                    }
                    if ($payment_status===false) {
                        $payment_status = "";
                    }
                    if ($re_inspection===false) {
                        $re_inspection = "";
                    }

                    $user_id = $this->session->userdata('user_id');
                    $user = $this->user_model->get_user__by_id('admin', $user_id);
                    if ($user) {
                        $uu_id = $this->uuid->v4();

                        $recipients = array();
                        array_push($recipients, array('email'=>$user['email']));

                        $recipient = $this->input->get_post('recipient');
                        if ($recipient!==false && $recipient!="") {
                            $emails = explode(",", $recipient);
                            if (is_array($emails)) {
                                foreach ($emails as $row) {
                                    $addr = trim($row);
                                    if (filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                                        array_push($recipients, array('email'=>$addr));
                                    }
                                }
                            }
                        }

                        $html = $this->get_report_data__for_payable_pending_inspection($region, $community, $start_date, $end_date, $status, $type, $epo_status, $payment_status, $re_inspection);
                        $this->m_pdf->pdf->WriteHTML($html);

                        $filename = "resource/upload/report/report_" . $uu_id . ".pdf";
                        $this->m_pdf->pdf->Output($filename, "F");

                        $email_template = $this->get_report_html__for_mail($filename);

                        $result = $this->send_mail("Inspections Pending Payment Report", $email_template, $recipients, true);
                        if ($result=="") {
                            $response['code'] = 0;
                            $response['message'] = "Successfully Sent!";
                        } else {

                        }

                        sleep(1);
//                        unlink($filename);
                    }
                }
            }

        }

        print_r(json_encode($response, JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG));
    }



    private function send_report($user_id, $inspection_id, $manual_report=false, $recipients=array()) {
        $ret = false;
        if ($manual_report) {
            $inspection = $this->utility_model->get('ins_inspection', array('id' => $inspection_id));
        } else {
            $inspection = $this->utility_model->get('ins_inspection', array('user_id' => $user_id, 'id' => $inspection_id));
        }

        if ($inspection['type']==3) {
            $sender = array();
            $user = $this->utility_model->get('ins_user', array('id'=>$inspection['user_id']));

            if ($manual_report) {
                $fm = $this->utility_model->get('ins_admin', array('id'=>$user_id, 'allow_email'=>1));
                if ($fm) {
                    array_push($sender, array('email'=>$fm['email']));
                }

                foreach ($recipients as $row) {
                    array_push($sender, array('email'=>$row));
                }
            } else {
                $fm = $this->utility_model->get('ins_admin', array('id'=>$inspection['field_manager'], 'allow_email'=>1));
                if ($fm) {
                    array_push($sender, array('email'=>$fm['email']));
                }

                // add inspector. 6/3
                if ($user) {
                    array_push($sender, array('email'=>$user['email']));
                }
            }


            $inspection_requested = $this->utility_model->get('ins_inspection_requested', array('id'=>$inspection['requested_id']));
            $complete_date = $inspection['end_date'];
            if ($inspection_requested) {
                $complete_date = $inspection_requested['completed_at'];

                if (isset($inspection_requested['document_person']) && $inspection_requested['document_person']!="") {
                    $emails = explode(",", $inspection_requested['document_person']);
                    if (is_array($emails)) {
                        foreach ($emails as $row) {
                            $addr = trim($row);
                            if (filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                                array_push($sender, array('email'=>$addr));
                            }
                        }
                    }
                }
            }

            $result_duct_leakage = $this->utility_model->get('ins_code', array('kind'=>'rst_duct', 'code'=>$inspection['result_duct_leakage']));
            $result_envelop_leakage = $this->utility_model->get('ins_code', array('kind'=>'rst_envelop', 'code'=>$inspection['result_envelop_leakage']));

            $subject = "Community " . $inspection['community'] . ", Lot " . $inspection['lot'] . " Duct and Envelope Leakage Inspection Results";

            $sys_emails = $this->utility_model->get_list('sys_recipient_email', array('status'=>'1'));
            if ($sys_emails) {
                foreach ($sys_emails as $row) {
                    array_push($sender, $row);
                }
            }

            $file1 = $this->make_pdf_for_duct_leakage($inspection_id);
            $file2 = $this->make_pdf_for_envelop_leakage($inspection_id);

            if ($manual_report) {
                $body = "<div>"
                        . "Duct and Envelope Leakage Inspection was completed by " . $user['first_name'] . " " . $user['last_name'] . ", on " . $complete_date . "<br>" . "<br>"
                        . "     Lot Number :  " . $inspection['lot'] . "<br>"
                        . "     Community  :  " . $inspection['community'] . "<br>"
                        . "     Address    :  " . $inspection['address'] . "<br>" . "<br>"
                        . "     Duct Leakage Test     :  " . $result_duct_leakage['name'] . "<br>"
                        . "     Envelope Leakage Test  :  " . $result_envelop_leakage['name'] . "<br>" . "<br>"
                        . "Duct and Envelope Leakage Inspection was completed by " . $user['first_name'] . " " . $user['last_name'] . ", on " . $complete_date . "<br>" . "<br>"
                        . "<br>"
                        . '<a href="' . base_url() . $file1 . '">' . "Duct Leakage Report" . '</a>' . "<br>"
                        . '<a href="' . base_url() . $file2 . '">' . "Envelope Leakage Report" . '</a>' . "<br>"
                        . "<br>"
                        . "Best Regards," . "<br>"
                        . "The Inspections Team" . "<br>"
                        . "</div>";


                if ($this->send_mail($subject, $body, $sender, true)==="") {
                    $ret = true;
                }
            } else {
                $body = "<div>"
                        . "Duct and Envelope Leakage Inspection was completed by " . $user['first_name'] . " " . $user['last_name'] . ", on " . $complete_date . "<br>" . "<br>"
                        . "     Lot Number :  " . $inspection['lot'] . "<br>"
                        . "     Community  :  " . $inspection['community'] . "<br>"
                        . "     Address    :  " . $inspection['address'] . "<br>" . "<br>"
                        . "     Duct Leakage Test     :  " . $result_duct_leakage['name'] . "<br>"
                        . "     Envelope Leakage Test  :  " . $result_envelop_leakage['name'] . "<br>" . "<br>"
                        . "Duct and Envelope Leakage Inspection was completed by " . $user['first_name'] . " " . $user['last_name'] . ", on " . $complete_date . "<br>" . "<br>"
                        . "<br>"
                        . '<a href="' . base_url() . $file1 . '">' . "Duct Leakage Report" . '</a>' . "<br>"
                        . '<a href="' . base_url() . $file2 . '">' . "Envelope Leakage Report" . '</a>' . "<br>"
                        . "<br>"
                        . "Best Regards," . "<br>"
                        . "The Inspections Team" . "<br>"
                        . "</div>";

                if ($this->send_mail($subject, $body, $sender, true)==="") {
                    $ret = true;
                }
            }

            sleep(30);
//                unlink($file1);
//                unlink($file2);
        }
        else {
            $html_subject = "";

            switch ($inspection['result_code']){
                case 1:
                    $html_subject = "Inspection - PASS";
                    break;
                case 2:
                    $html_subject = "Inspection - PASS WITH EXCEPTION";
                    break;
                default:
                    $html_subject = "Inspection - FAIL";
            }

            switch ($inspection['type']) {
                case 1:
                    $html_subject = "Drainage Plane " . $html_subject;
                    break;

                case 2:
                    $html_subject = "Lath " . $html_subject;
                    break;
            }

            $html_subject .= " with Job Number " . $inspection['job_number'] . "";

            $sender = array();

            if ($manual_report) {
                $fm = $this->utility_model->get('ins_admin', array('id'=>$user_id));
                if ($fm) {
                    array_push($sender, array('email'=>$fm['email']));
                }

                foreach ($recipients as $row) {
                    array_push($sender, array('email'=>$row));
                }
            } else {
                $emails = $this->utility_model->get_list('ins_recipient_email', array('inspection_id' => $inspection_id));
                if ($emails) {
                    foreach ($emails as $row) {
                        array_push($sender, $row);
                    }
                }

                $emails = $this->utility_model->get_list('sys_recipient_email', array('status'=>'1'));
                if ($emails) {
                    foreach ($emails as $row) {
                        array_push($sender, $row);
                    }
                }

                $fm = $this->utility_model->get('ins_admin', array('id'=>$inspection['field_manager'], 'allow_email'=>1));
                if ($fm) {
                    array_push($sender, array('email'=>$fm['email']));
                }

                // add inspector. 6/3
                $user = $this->utility_model->get('ins_user', array('id'=>$user_id));
                if ($user) {
                    array_push($sender, array('email'=>$user['email']));
                }
                // ------------------

                // add requested inspection's fm. 6/7/17.
                $requested_inspection = $this->utility_model->get('ins_inspection_requested', array('id'=>$inspection['requested_id']));
                if ($requested_inspection) {
                    $fm = $this->utility_model->get('ins_admin', array('kind'=>2, 'id'=>$requested_inspection['manager_id'], 'allow_email'=>1));
                    if ($fm) {
                        array_push($sender, array('email'=>$fm['email']));
                    }
                }
                // ----------------------------------------
            }

            $file = $this->make_pdf($inspection_id);
            $html = $this->get_report_html__for_mail($file);

            if ($manual_report) {
                if ($this->send_mail($html_subject, $html, $sender, true)==="") {
                    $ret = true;
                }
            } else {
                if ($this->send_mail($html_subject, $html, $sender, true)==="") {
                    $ret = true;
                }
            }

            sleep(1);
//            unlink($file);
        }

//        return $ret ? $sender : false;
        return $ret;
    }

    private function make_pdf_for_duct_leakage($inspection_id) {
        $this->m_pdf->initialize("B4-C", "P");

        $fname = mdate('%Y-%m-%d %H%i%s', time());
        $fname = $this->utility_model->escape_filename($fname);

        $inspection = $this->utility_model->get('ins_inspection', array('id'=>$inspection_id));
        if ($inspection) {
            $fname = $inspection['community'] . "_" . $inspection['job_number'] . "_duct_leakage" . "__" . $fname;
        }

        $html = $this->get_report_html__for_duct_leakage($inspection_id);
        $this->m_pdf->pdf->WriteHTML($html);

        $filename = "resource/upload/report/" . $fname . ".pdf";
        $this->m_pdf->pdf->Output($filename, "F");

        return $filename;
    }

    private function make_pdf_for_envelop_leakage($inspection_id) {
        $this->m_pdf->initialize("B4-C", "P");

        $fname = mdate('%Y-%m-%d %H%i%s', time());
        $fname = $this->utility_model->escape_filename($fname);

        $inspection = $this->utility_model->get('ins_inspection', array('id'=>$inspection_id));
        if ($inspection) {
            $fname = $inspection['community'] . "_" . $inspection['job_number'] . "_envelope_leakage" . "__" . $fname;
        }

        $html = $this->get_report_html__for_envelop_leakage($inspection_id);
        $this->m_pdf->pdf->WriteHTML($html);

        $filename = "resource/upload/report/" . $fname . ".pdf";
        $this->m_pdf->pdf->Output($filename, "F");

        return $filename;
    }

    private function make_pdf($inspection_id) {
        $this->m_pdf->initialize();

        $fname = "";

        $inspection = $this->utility_model->get('ins_inspection', array('id'=>$inspection_id));
        if ($inspection) {
            if ($inspection['type']==1) {
                $fname = "Drainage Plane Inspection";
            }
            else if ($inspection['type']==2) {
                $fname = "Lath Inspection";
            }

            $result_code = $this->utility_model->get('ins_code', array('kind'=>'rst', 'code'=>$inspection['result_code']));
            if ($result_code) {
                $fname .= " - " . $result_code['name'];
            }

            $fname .= " with Job Number " . $inspection['job_number'];

            $community = $this->utility_model->get('ins_community', array('community_id'=>$inspection['community']));
            if ($community) {
                $fname .= " " . $community['community_name'];
            }
        }

        $fname .= "__" . mdate('%Y-%m-%d %H%i%s', time());
        $fname = $this->utility_model->escape_filename($fname);

        $html = $this->get_report_html($inspection_id, 'pass');
        $this->m_pdf->pdf->WriteHTML($html);

        $filename = "resource/upload/report/" . $fname . ".pdf";
        $this->m_pdf->pdf->Output($filename, "F");

        return $filename;
    }

    public function get_report_html($inspection_id, $type='full') {
        //$sql = " select a.*, u.email, c2.name as result_name as result_code from ins_code c2, ins_inspection a left join ins_user u on a.user_id=u.id where a.id='" . $inspection_id . "' and c2.kind='rst' and c2.code=a.result_code ";
        //modified by bongbong 2016/04/08
        $sql = "select a.*, u.email, c2.name as result_name,
                (select count(*) from ins_inspection d where replace(d.job_number,'-','')=replace(a.job_number,'-','') and type=1 and (d.result_code=1 or d.result_code=2)) as pass_drg_cnt
                from ins_code c2, ins_inspection a
                left join ins_user u on a.user_id=u.id where a.id='" . $inspection_id . "' and c2.kind='rst' and c2.code=a.result_code ";
        $inspection = $this->utility_model->get__by_sql($sql);

        $html_styles = "<style type='text/css'>.text-center{text-align:center}.row{float:left;width:100%;margin-bottom:20px}.col-50-percent{float:left;width:50%}span{color:#111;padding:2px 7px;font-weight:bold}.label-danger{background:#d9534f;color:#fff;font-size:30px;font-weight:bold;padding:5px 2px;text-align:center}.label-success{background:#5cb85c;color:#fff;font-size:30px;font-weight:bold;padding:5px 2px;text-align:center}.label-warning{background:#f0ad4e;color:#fff;font-size:30px;font-weight:bold;padding:5px 2px;text-align:center}.checklist{border:1px solid #000;width:100%; border-collapse: collapse;}.location{width:100px;text-align:center}.checklist .status{width:100px;text-align:center}.checklist .item{padding:4px 8px}</style>";

        $html_header = "<html><head><meta charset='utf-8'/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/><title>Report</title>" . $html_styles . "</head><body>";

        $html_body = "";

        $title = "";
        if ($inspection['type'] == '1')
            $title = "DRAINAGE PLANE INSPECTION REPORT";
        if ($inspection['type'] == '2')
            $title = "LATH INSPECTION REPORT";

        $html_body .= "<h1 style='text-align: center; color: #00e;'>" . $title . "</h1>";

        // added logo
        $html_body .= '<div class="row text-center"><img src="' . $this->image_url_change(LOGO_PATH) . '" style="max-width: 400px; margin: auto;"></div>';

        // added by bongbong 2016/04/08
        if ($inspection["pass_drg_cnt"] == 0 && $inspection["type"] == '2') { // if there is no a Drainage Plane with pass or pass exception for this lath check
            $warning_message = "No Pass or Pass with Exception Drainage Inspection was completed for this lot";
            $html_body .= "<h4 style='text-align: center; color: #f00;'>" . $warning_message . "</h4>";
        }

        $title = $inspection['community'] . ", " . $inspection['lot'] . ", " . $inspection['start_date'];
        $html_body .= "<h3 style='text-align: right; color: #006;'>" . $title . "</h3>";

        if ($inspection['image_signature'] != "") {
            $html_body .= "<div class='row' style='text-align: right;'><img style='float: right; max-width: 150px;' src='" . $this->image_url_change($inspection['image_signature']) . "'></div>";
        }

        $html_body .= "<div class='row'><div class='col-50-percent'><table class='data-table'>";

//        $html_body .= "<tr><td class='field-name'>Community :</td><td class='field-value'>" . $inspection['community'] . "</td></tr>";
//        $html_body .= "<tr><td class='field-name'>LOT# :</td><td class='field-value'>" . $inspection['lot'] . "</td></tr>";
        $html_body .= "<tr><td class='field-name'>Job Number :</td><td class='field-value'>" . $inspection['job_number'] . "</td></tr>";
        $html_body .= "<tr><td class='field-name'>Address :</td><td class='field-value'>" . $inspection['address'] . "</td></tr>";
        $html_body .= "<tr><td colspan='2'>Is This House Ready For Inspection? <span>" . ($inspection['house_ready'] == '1' ? "Yes" : "No" ) . "</span></td></tr>";

        if ($inspection['image_front_building'] != "") {
            $html_body .= "<tr><td colspan='2' style='text-align: center;'><img style='max-height: 300px;' src='" . $this->image_url_change($inspection['image_front_building']) . "'></td></tr>";
        }

        $html_body .="</table></div><div class='col-50-percent'><table class='data-table'> ";

//        $html_body .= "<tr><td class='field-name'>Date :</td><td class='field-value'>" . $inspection['start_date'] . "</td></tr>";
        $html_body .= "<tr><td class='field-name'>Inspector :</td><td class='field-value'>" . $inspection['initials'] . "</td></tr>";
        $fm = $this->utility_model->get('ins_admin', array('id'=>$inspection['field_manager']));
        if ($fm) {
            $html_body .= "<tr><td class='field-name'>Field Manager :</td><td class='field-value'>" . $fm['first_name'] . " " . $fm['last_name'] . "</td></tr>";
        }

        if ($inspection['latitude'] == '-1' && $inspection['longitude'] == '-1' && $inspection['accuracy'] == '-1') {

        } else {
            $google_map = "<img width='300' src='http://maps.googleapis.com/maps/api/staticmap?center=" . $inspection['latitude'] . "+" . $inspection['longitude'] . "&zoom=16&scale=false&size=300x300&maptype=roadmap&format=jpg&visual_refresh=true' alt='Google Map'>";
            $html_body .="<tr><td colspan='2'>GPS Location : <span>Lat: " . $inspection['latitude'] . ", Lon: " . $inspection['longitude'] . ", Acc: " . $inspection['accuracy'] . "m</span></td></tr>";
            $html_body .= "<tr><td colspan='2' style='text-align: center;'>" . $google_map . "</td></tr>";
        }

        $html_body .= "</table></div></div>";

        $html_body .= "<div class='row text-center'>";

        $cls = "";
        if ($inspection['result_code'] == 1)
            $cls = "label-success";
        if ($inspection['result_code'] == 2)
            $cls = "label-warning";
        if ($inspection['result_code'] == 3)
            $cls = "label-danger";

        $html_body .= "<h4 class='" . $cls . "'>" . $inspection['result_name'] . "</h4>";
        $html_body .= "</div>";

        $failed_image = $this->utility_model->get_list('ins_exception_image', array('inspection_id'=>$inspection_id));
        $failed_image_count = count($failed_image);

        $html_body .= '<p style="font-size: 18px;">Overall Comments: ' . $inspection['overall_comments']  . '</p>';
        if ($failed_image_count>0) {
            $html_body .= "<div class='row'><table class='checklist'>";

            $image_percent = intval(100/$failed_image_count * 3 / 4) ;

            $html_body .= "<tr><td class='text-center'>";

            foreach ($failed_image as $row) {
                $img = "<img style='max-width: " . $image_percent . "%; padding:5px; ' src='" . $this->image_url_change($row['image']) . "'>";
                $html_body .= $img;
            }

            $html_body .= "</td></tr>";

            $html_body .= "</table></div>";
        }

        $inspection_comment_list_code = "";
        $inspection_type = intval($inspection['type']);
        if ($inspection_type==1) {
            $inspection_comment_list_code = "drg_comment";
        } else {
            $inspection_comment_list_code = "lth_comment";
        }

        $comment_list = $this->utility_model->get_list__by_sql(" select a.*, c.name as comment_name from ins_inspection_comment a left join ins_code c on c.kind='$inspection_comment_list_code' and c.code=a.no where a.inspection_id='$inspection_id' order by a.no asc ");
        if (count($comment_list)>0) {
            $html_body .= "<div class='row'><table class='checklist' border='1'>";
            $html_body .= "<thead>";
            $html_body .= "<tr><th class='text-center'>Comments</th></tr>";
            $html_body .= "</thead>";

            $html_body .= "<tbody>";
            foreach ($comment_list as $row) {
                $html_body .= "<tr><td>";
                $html_body .= $row['comment_name'];
                $html_body .= "</td></tr>";
            }
            $html_body .= "</tbody>";
            $html_body .= "</table></div>";
        }

        $header_style = "background: #ddd; font-size:18px; padding: 10px 2px;";
        $body_style = "background: #f8f8f8;";

        if ($inspection['house_ready'] == '1') {
            $html_body .= "<div class='row'><table class='checklist'><tr><td class='location' style='" . $header_style . "'>Location</td><td class='item' style='" . $header_style . " text-align:center;'>Item</td><td class='status' style='" . $header_style . "'>Status</td></tr>";

            $k = $inspection['type'] == 1 ? 'drg' : 'lth';
            $locations = $this->utility_model->get_list('ins_location', array('inspection_id' => $inspection_id));
            foreach ($locations as $row) {
                $location = $row['name'];
                $checklist = $this->utility_model->get_list__by_sql("SELECT a.*, c.name as status_name, b.name as check_name FROM ins_code c, ins_checklist a JOIN ins_code b ON a.no=b.code WHERE a.status=c.code and c.kind='sts' and b.kind='" . $k . "' and a.inspection_id='" . $inspection_id . "' and a.location_id='" . $row['id'] . "'  ORDER BY a.no ");
                foreach ($checklist as $point) {

                    if ($type=='full' || ($type=='pass' && $point['status']!='0' && $point['status']!='1' && $point['status']!='4')) {
                        $html_body .= "<tr><td class='location' style='" . $body_style . "'>" . $location . "</td><td class='item' style='" . $body_style . "'>" . $point['check_name'] . "</td><td class='status' style='" . $body_style . "'>" . $point['status_name'] . "</td></tr>";
                        if ($point['status'] == '2' || $point['status'] == '3') {
                            $html_body .= "<tr><td class='location' style='" . $body_style . "'></td><td class='item' colspan='2' style='" . $body_style . "'>Comments: " . $point['description'] . "</td></tr>";
                        }

                        if ($point['status'] == '2') {
                            $img = "";
                            if ($point['primary_photo'] != "") {
                                $img .= "<img style='max-width: 36%; padding:5px; ' src='" . $this->image_url_change($point['primary_photo']) . "'>";
                            }
                            if ($point['secondary_photo'] != "") {
                                $img .= "<img style='max-width: 36%; padding:5px; ' src='" . $this->image_url_change($point['secondary_photo']) . "'>";
                            }

                            $html_body .= "<tr><td colspan='3' class='text-center'>" . $img . "</td></tr>";
                        }
                    }
                }
            }

            $html_body .= "</table></div>";
        }

        $html_body .= "<div class='row'></div><div class='row'></div>";

        $html_footer = "</body></html>";

        $html = $html_header . $html_body . $html_footer;

        return $html;
    }

    public function get_report_html__for_mail($file) {
        $html_styles = "<style type='text/css'>.text-center{text-align:center}.row{float:left;width:100%;margin-bottom:20px}.col-50-percent{float:left;width:50%}span{color:#111;padding:2px 7px;font-weight:bold}.label-danger{background:#d9534f;color:#fff;font-size:30px;font-weight:bold;padding:5px 2px;text-align:center}.label-success{background:#5cb85c;color:#fff;font-size:30px;font-weight:bold;padding:5px 2px;text-align:center}.label-warning{background:#f0ad4e;color:#fff;font-size:30px;font-weight:bold;padding:5px 2px;text-align:center}.checklist{border:1px solid #000;width:100%}.checklist .location{width:100px;text-align:center}.checklist .status{width:100px;text-align:center}.checklist .item{padding:4px 8px}</style>";
        $html_header = "<html><head><meta charset='utf-8'/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/><title>Report</title>" . $html_styles . "</head><body>";

        $html_body = "";

        $template = $this->utility_model->get('sys_config', array('code'=>'report_template'));
        if ($template) {
            $html_body .= "<div class='row'>" . $template['value'] . "</div>";
        }

        $html_body .= "<div class='row'></div>";

        $url = base_url() . $file;
        $html_body .= "<div class='row'>"
                . '<a href="' . $url . '">' . "Attached File" . '</a>'
                . "</div>";

        $html_body .= "<div class='row'></div>";

        $html_footer = "</body></html>";

        $html = $html_header . $html_body . $html_footer;

        return $html;
    }

    private function send_mail($subject, $body, $sender, $isHTML=false) {
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

    private function send_mail_with_file($subject, $body, $sender, $file, $isHTML=false) {
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

//        $mail->addAttachment($file);

        if ($mail->send()) {

        } else {
            return $mail->ErrorInfo;
        }

        return "";
    }

    private function send_mail_with_files($subject, $body, $sender, $files, $isHTML=false) {
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

//        foreach ($files as $file) {
//            $mail->addAttachment($file);
//        }

        if ($mail->send()) {

        } else {
            return $mail->ErrorInfo;
        }

        return "";
    }

    private function get_location($inspection_id, $location_name, $type) {
        $result = array('omit'=>1);

        $location = $this->utility_model->get('ins_location', array('inspection_id'=>$inspection_id, 'name'=>$location_name));
        if ($location) {
            $location_id = $location['id'];

            $result['omit'] = 0;
            $result['front'] = 0;

            if ($location_name=='Front')  {
                $result['front'] = 1;
            }

            $c = $this->utility_model->get_count('ins_checklist', array('inspection_id'=>$inspection_id, 'location_id'=>$location_id));
            if ($type == 1) {
                if ($c != 21) {
                    $result['omit'] = 1;
                }
            }
            else {
                if ($c == 15 || $c == 13) {

                } else {
                    $result['omit'] = 1;
                }
            }

            $result['list'] = array();
            $list = $this->utility_model->get_list__by_sql(" select "
                    . " a.no as kind, a.status as stat, a.description as cmt, a.primary_photo, a.secondary_photo "
                    . " from ins_checklist a "
                    . " where a.inspection_id='$inspection_id' and a.location_id='$location_id' order by a.no ");
            if ($list) {
                foreach ($list as $row) {
                    $row['prm'] = '';
                    if (isset($row['primary_photo']) && $row['primary_photo']!="") {
                        $row['prm'] = array(
                            'mode'=>2,
                            'img'=>$row['primary_photo'],
                        );
                    }

                    $row['snd'] = '';
                    if (isset($row['secondary_photo']) && $row['secondary_photo']!="") {
                        $row['snd'] = array(
                            'mode'=>2,
                            'img'=>$row['secondary_photo'],
                        );
                    }

                    array_push($result['list'], $row);
                }
            }

            return $result;
        }

        return "";
    }

    private function get_comment($inspection_id) {
        $list = $this->utility_model->get_list__by_sql(" select "
                . " a.no as kind, a.status as stat, a.description as cmt, a.primary_photo, a.secondary_photo "
                . " from ins_inspection_comment a "
                . " where a.inspection_id='$inspection_id' order by a.no ");

        if ($list) {
            $result = array();
            $result['list'] = array();

            foreach ($list as $row) {
                $row['submit'] = '1';

                $row['prm'] = '';
                if (isset($row['primary_photo']) && $row['primary_photo']!="") {
                    $row['prm'] = array(
                        'mode'=>2,
                        'img'=>$row['primary_photo'],
                    );
                }

                $row['snd'] = '';
                if (isset($row['secondary_photo']) && $row['secondary_photo']!="") {
                    $row['snd'] = array(
                        'mode'=>2,
                        'img'=>$row['secondary_photo'],
                    );
                }

                array_push($result['list'], $row);
            }

            return $result;
        }

        return "";
    }


    private function get_report_data__for_statistics_inspection($region, $community, $start_date, $end_date, $status, $type, $is_array=false, $include_description=true) {
        $reports = array();

        $table = " select  a.*, "
                . " c1.name as inspection_type, c2.name as result_name, "
                . " r.region as region_name, tt.community_name, "
                . " u.first_name, u.last_name, '' as additional "
                . " from ins_region r, ins_code c1, ins_code c2, ins_inspection a "
                . " left join ins_admin u on a.field_manager=u.id and u.kind=2 "
                . " left join ins_community tt on tt.community_id=a.community "
                . " where a.region=r.id and c1.kind='ins' and c1.code=a.type and c2.kind='rst' and c2.code=a.result_code  ";

        $common_sql = "";

        if ($start_date!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.start_date>='$start_date' ";
        }

        if ($end_date!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.end_date<='$end_date' ";
        }

        if ($region!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.region='$region' ";
        }

        if ($community!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.community='$community' ";
        }

        if ($status!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.result_code='$status' ";
        }

        if ($type!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.type='$type' ";
        }


        $sql = $table;

        if ($common_sql!="") {
            $sql .= " and " . $common_sql;
        }

        $count_sql = " select count(*) from ( " . $sql . " ) t ";
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

        $count_sql = " select count(*) from ( " . $sql . " and a.house_ready=0 ) t ";
        $house_not_ready = $this->datatable_model->get_count($count_sql);
        if ($count_text!="") {
            $count_text .= ", ";
        }
        $count_text .= '<span class="lbl-house-not-ready">';
        $count_text .= "House Not Ready: " . $house_not_ready;
        $count_text .= "(" . round($house_not_ready*1.0/$total * 100, 2) . "%)";

        $count_text .= "</h4>";

        $table_styles = " .data-table {width: 100%; border: 1px solid #000; } "
                . " .data-table thead th { padding: 7px 5px; } "
                . " .table-bordered { border-collapse: collapse; }"
                . " .table-bordered thead th, .table-bordered tbody td { border: 1px solid #000; }  "
                . " .table-bordered tbody td { font-size: 85%; padding: 4px 4px; }  ";

        $html_styles = "<style type='text/css'> " . $table_styles . " "
                . " .text-right{text-align:right;} "
                . " .text-center{text-align:center;} "
                . " .row{float:left;width:100%;margin-bottom:20px;} "
                . " span.label{} .label-danger{color:#d9534f;} .label-success{color:#5cb85c;} .label-warning{color:#f0ad4e;} "
                . " .col-50-percent{float:left;width:50%;} "
                . ".total-inspection span , .total-checklist span { font-size: 84%; } .total-checklist span.total-1 { color: #02B302; } .total-checklist span.total-2 { color: #e33737; } .total-checklist span.total-3 { color: #11B4CE; }  .total-inspection span.total-1 { color: #02B302; } .total-inspection span.total-2 { color: #e89701; } .total-inspection span.total-3 { color: #e33737; }"
                . "</style>";

        $html_header = "<html><head><meta charset='utf-8'/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/><title>Report</title>" . $html_styles . "</head><body>";
        $html_body = "";

        $html_body .= "<div class='row text-center'>" . '<img alt="" src="' . $this->image_url_change(LOGO_PATH) . '" style="margin: auto; max-width: 400px;">' . "</div>";

        $cls = "text-center";
        $title = "Inspection Report";
        if ($type == '1') {
            $title = "Drainage Plane " . $title;
        }
        if ($type == "2") {
            $title = "Lath " . $title;
        }

        $html_body .= "<h1 class='" . $cls . "'>" . $title . "</h1>";

        $sub_title = "";
        if ($region!="") {
            $r = $this->utility_model->get('ins_region', array('id'=>$region));
            if ($r) {
                $sub_title .= $r['region'];
            }
        }

        if ($community!="") {
            $c = $this->utility_model->get('ins_community', array('community_id'=>$community));
            if ($c) {
                if ($sub_title!="") {
                    $sub_title .= ", ";
                }

                $sub_title .= $c['community_name'];
            }
        }

        $cls = "text-right";

        if ($sub_title!="") {
            $html_body .= "<h5 class='" . $cls . "'>" . $sub_title . "</h5>";
        }

        if ($start_date!="" && $end_date!="") {
            $html_body .= "<h6 class='" . $cls . "'>" . $start_date . " ~ " . $end_date . "</h6>";
        }

        if ($count_text!="") {
            $html_body .=  $count_text ;
        }

        $html_body .= '<div class="row">';

        $html_body .= '<table class="data-table table-bordered">';
        $html_body .= '' .
                '<thead>' .
                    '<tr>' .
                        '<th>Type</th>' .
                        '<th>Region</th>' .
                        '<th>Community</th>' .
                        '<th>Job Number</th>' .
                        '<th>Address</th>' .
                        '<th>Field Manager</th>' .
                        ( $include_description ? '<th>Description</th>' : '' ) .
                        '<th>Date</th>' .
                        '<th>Result</th>' .
                        '<th>House Ready</th>' .
                    '</tr>' .
                '</thead>' .
                '';

        $html_body .= '<tbody>';

        $sql = $table;
        if ($common_sql!="") {
            $sql .= " and " . $common_sql;
        }

        $sql .= " order by a.start_date ";

        if ($include_description) {
            array_push($reports, array(
                'inspection_type'=>"Inspection Type",
                'region'=>'Region',
                'community'=>'Community',
                'job_number'=>'Job Number',
                'address'=>'Address',
                'field_manager'=>'Field Manager',
                'description'=>'Description',
                'date'=>'Date',
                'result'=>'Result',
                'house_ready'=>'House Ready',
            ));
        } else {
            array_push($reports, array(
                'inspection_type'=>"Inspection Type",
                'region'=>'Region',
                'community'=>'Community',
                'job_number'=>'Job Number',
                'address'=>'Address',
                'field_manager'=>'Field Manager',
                'date'=>'Date',
                'result'=>'Result',
                'house_ready'=>'House Ready',
            ));
        }

        $data = $this->datatable_model->get_content($sql);
        if ($data && is_array($data)) {
            foreach ($data as $row) {
                $html_body .= '<tr>';

                $field_manager = "";
                if (isset($row['first_name']) && isset($row['last_name']) && $row['first_name']!="" && $row['last_name']!="") {
                    $field_manager = $row['first_name'] . " " . $row['last_name'];
                }

                // replace community name.  2016/11/3
                $community_name = ""; // $row['community'];
                if (isset($row['community_name']) && $row['community_name']!="") {
                    $community_name = $row['community_name'];
                }

                $html_body .= '<td class="text-center">' . $row['inspection_type']  . '</td>';
                $html_body .= '<td class="text-center">' . $row['region_name']  . '</td>';
                $html_body .= '<td class="text-center">' . $community_name  . '</td>';
                $html_body .= '<td class="text-center">' . $row['job_number']  . '</td>';
                $html_body .= '<td>' . $row['address']  . '</td>';
                $html_body .= '<td class="text-center">' . $field_manager  . '</td>';

                if ($include_description) {
                    $html_body .= '<td>' . $row['overall_comments']  . '</td>';
                }

                $html_body .= '<td class="text-center">' . $row['start_date']  . '</td>';

                $cls = "";
                if ($row['result_code'] == '1')
                    $cls = "label-success";
                if ($row['result_code'] == '2')
                    $cls = "label-warning";
                if ($row['result_code'] == '3')
                    $cls = "label-danger";

                $html_body .= '<td class="text-center"><span class="label '. $cls  . '">' . $row['result_name']  . '</span></td>';
                $html_body .= '<td class="text-center"><span class="">' . ($row['house_ready']==1 ? "House Ready" : "House Not Ready") . '</span></td>';

                $html_body .= '</tr>';

                if ($include_description) {
                    array_push($reports, array(
                        'inspection_type'=>$row['inspection_type'],
                        'region'=>$row['region_name'],
                        'community'=>$community_name,
                        'job_number'=>$row['job_number'],
                        'address'=>$row['address'],
                        'field_manager'=>$field_manager,
                        'description'=>$row['overall_comments'],
                        'date'=>$row['start_date'],
                        'result'=>$row['result_name'],
                        'house_ready'=>$row['house_ready']==1 ? "House Ready" : "House Not Ready"
                    ));
                } else {
                    array_push($reports, array(
                        'inspection_type'=>$row['inspection_type'],
                        'region'=>$row['region_name'],
                        'community'=>$community_name,
                        'job_number'=>$row['job_number'],
                        'address'=>$row['address'],
                        'field_manager'=>$field_manager,
                        'date'=>$row['start_date'],
                        'result'=>$row['result_name'],
                        'house_ready'=>$row['house_ready']==1 ? "House Ready" : "House Not Ready"
                    ));
                }
            }
        }


        $html_body .= '</tbody>';
        $html_body .= '</table>';

        $html_body .= '</div>';


        $html_footer = "</body></html>";

        $html = $html_header . $html_body . $html_footer;

        if ($is_array) {
            return $reports;
        } else {
            return $html;
        }
    }

    private function get_report_data__for_statistics_re_inspection($region, $community, $start_date, $end_date, $status, $type, $is_array=false, $include_description=true) {
        $reports = array();

        $table = " ins_region r, ins_code c1, ins_code c2,  "
                . " ( SELECT p1.inspection_id, p2.* "
                . "   FROM "
                . "    ( SELECT MAX(t.id) AS inspection_id, t.job_number, bbb.address, t.type FROM ins_inspection t LEFT JOIN ins_building_unit bbb ON REPLACE(t.job_number,'-','')=REPLACE(bbb.job_number, '-', '') AND bbb.address=t.address and t.is_building_unit=1 GROUP BY t.job_number, bbb.address, t.type ) p1, "
                . "    ( SELECT t.type, t.job_number, bbb.address, MAX(t.start_date) AS inspection_date, COUNT(*) AS inspection_count  FROM ins_inspection t  LEFT JOIN ins_building_unit bbb ON REPLACE(t.job_number,'-','')=REPLACE(bbb.job_number, '-', '') AND bbb.address=t.address and t.is_building_unit=1 GROUP BY t.job_number, bbb.address, t.type ) p2 "
                . "   WHERE p1.type=p2.type AND p1.job_number=p2.job_number AND ((p1.address IS NULL AND p2.address IS NULL) OR p1.address=p2.address) "
                . " ) g "
                . " LEFT JOIN ins_inspection a ON g.inspection_id=a.id "
                . " LEFT JOIN ins_inspection_requested q ON a.requested_id=q.id "
                . " LEFT JOIN ins_admin u ON a.field_manager=u.id AND u.kind=2 "
                . " LEFT JOIN ins_community tt ON tt.community_id=a.community "
                . " WHERE a.region=r.id AND c1.kind='ins' AND c1.code=a.type AND c2.kind='rst' "
                . " AND c2.code=a.result_code  AND g.inspection_count>1 "
                . " ";

        $common_sql = "";

        if ($start_date!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.start_date>='$start_date' ";
        }

        if ($end_date!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.end_date<='$end_date' ";
        }

        if ($region!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.region='$region' ";
        }

        if ($community!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.community='$community' ";
        }

        if ($status!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.result_code='$status' ";
        }

        if ($type!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.type='$type' ";
        }


        $sql = " select  a.*, "
                . " (g.inspection_count-1) as inspection_count, q.epo_number as requested_epo_number, "
                . " c1.name as inspection_type, c2.name as result_name, "
                . " r.region as region_name, tt.community_name, "
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

        $table_styles = " .data-table {width: 100%; border: 1px solid #000; } "
                . " .data-table thead th { padding: 7px 5px; } "
                . " .table-bordered { border-collapse: collapse; }"
                . " .table-bordered thead th, .table-bordered tbody td { border: 1px solid #000; }  "
                . " .table-bordered tbody td { font-size: 85%; padding: 4px 4px; }  ";

        $html_styles = "<style type='text/css'> " . $table_styles . " "
                . " .text-right{text-align:right;} "
                . " .text-center{text-align:center;} "
                . " .row{float:left;width:100%;margin-bottom:20px;} "
                . " span.label{} .label-danger{color:#d9534f;} .label-success{color:#5cb85c;} .label-warning{color:#f0ad4e;} "
                . " .col-50-percent{float:left;width:50%;} "
                . ".total-inspection span , .total-checklist span { font-size: 84%; } .total-checklist span.total-1 { color: #02B302; } .total-checklist span.total-2 { color: #e33737; } .total-checklist span.total-3 { color: #11B4CE; }  .total-inspection span.total-1 { color: #02B302; } .total-inspection span.total-2 { color: #e89701; } .total-inspection span.total-3 { color: #e33737; }"
                . "</style>";

        $html_header = "<html><head><meta charset='utf-8'/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/><title>Report</title>" . $html_styles . "</head><body>";
        $html_body = "";

        $html_body .= "<div class='row text-center'>" . '<img alt="" src="' . $this->image_url_change(LOGO_PATH) . '" style="margin: auto; max-width: 400px;">' . "</div>";

        $cls = "text-center";
        $title = "Inspection Report";
        if ($type == '1') {
            $title = "Drainage Plane " . $title;
        }
        if ($type == "2") {
            $title = "Lath " . $title;
        }

        $html_body .= "<h1 class='" . $cls . "'>" . $title . "</h1>";

        $sub_title = "";
        if ($region!="") {
            $r = $this->utility_model->get('ins_region', array('id'=>$region));
            if ($r) {
                $sub_title .= $r['region'];
            }
        }

        if ($community!="") {
            $c = $this->utility_model->get('ins_community', array('community_id'=>$community));
            if ($c) {
                if ($sub_title!="") {
                    $sub_title .= ", ";
                }

                $sub_title .= $c['community_name'];
            }
        }

        $cls = "text-right";

        if ($sub_title!="") {
            $html_body .= "<h5 class='" . $cls . "'>" . $sub_title . "</h5>";
        }

        if ($start_date!="" && $end_date!="") {
            $html_body .= "<h6 class='" . $cls . "'>" . $start_date . " ~ " . $end_date . "</h6>";
        }

        if ($count_text!="") {
            $html_body .=  $count_text ;
        }

        $html_body .= '<div class="row">';

        $html_body .= '<table class="data-table table-bordered">';
        $html_body .= '' .
                '<thead>' .
                    '<tr>' .
                        '<th>Type</th>' .
                        '<th>Region</th>' .
                        '<th>Community</th>' .
                        '<th>Job Number</th>' .
                        '<th>Address</th>' .
                        '<th>Field Manager</th>' .
                        ( $include_description ? '<th>Description</th>' : '' ) .
                        '<th>Date</th>' .
                        '<th>EPO Number</th>' .
                        '<th>Re-Inspections</th>' .
                        '<th>Result</th>' .
                    '</tr>' .
                '</thead>' .
                '';

        $html_body .= '<tbody>';

        $sql = " select  a.*, "
                . " (g.inspection_count-1) as inspection_count, q.epo_number, "
                . " c1.name as inspection_type, c2.name as result_name, "
                . " r.region as region_name, tt.community_name, "
                . " u.first_name, u.last_name, '' as additional "
                . " from " . $table . " ";
        if ($common_sql!="") {
            $sql .= " and " . $common_sql;
        }

        $sql .= " order by g.inspection_count desc ";

        if ($include_description) {
            array_push($reports, array(
                'inspection_type'=>"Inspection Type",
                'region'=>'Region',
                'community'=>'Community',
                'job_number'=>'Job Number',
                'address'=>'Address',
                'field_manager'=>'Field Manager',
                'description'=>'Description',
                'date'=>'Date',
                'epo_number'=>'EPO Number',
                're_inspections'=>'Re-Inspections',
                'result'=>'Result',
            ));
        } else {
            array_push($reports, array(
                'inspection_type'=>"Inspection Type",
                'region'=>'Region',
                'community'=>'Community',
                'job_number'=>'Job Number',
                'address'=>'Address',
                'field_manager'=>'Field Manager',
                'date'=>'Date',
                'epo_number'=>'EPO Number',
                're_inspections'=>'Re-Inspections',
                'result'=>'Result',
            ));
        }

        $data = $this->datatable_model->get_content($sql);
        if ($data && is_array($data)) {
            foreach ($data as $row) {
                $html_body .= '<tr>';

                $field_manager = "";
                if (isset($row['first_name']) && isset($row['last_name']) && $row['first_name']!="" && $row['last_name']!="") {
                    $field_manager = $row['first_name'] . $row['last_name'];
                }

                // replace community name.  2016/11/3
                $community_name = ""; // $row['community'];
                if (isset($row['community_name']) && $row['community_name']!="") {
                    $community_name = $row['community_name'];
                }

                $html_body .= '<td class="text-center">' . $row['inspection_type']  . '</td>';
                $html_body .= '<td class="text-center">' . $row['region_name']  . '</td>';
                $html_body .= '<td class="text-center">' . $community_name  . '</td>';
                $html_body .= '<td class="text-center">' . $row['job_number']  . '</td>';
                $html_body .= '<td>' . $row['address']  . '</td>';
                $html_body .= '<td class="text-center">' . $field_manager  . '</td>';

                if ($include_description) {
                    $html_body .= '<td>' . $row['overall_comments']  . '</td>';
                }

                $html_body .= '<td class="text-center">' . $row['start_date']  . '</td>';

                $epo_number = "";
                if (isset($row['epo_number']) && $row['epo_number']!="") {
                    $epo_number = $row['epo_number'];
                } else {
                    $epo_number = isset($row['requested_epo_number']) && $row['requested_epo_number']!=0 ? $row['requested_epo_number'] : "";
                }

                $html_body .= '<td class="text-center">' . $epo_number . '</td>';
                $html_body .= '<td class="text-center">' . $row['inspection_count']  . '</td>';

                $cls = "";
                if ($row['result_code'] == '1')
                    $cls = "label-success";
                if ($row['result_code'] == '2')
                    $cls = "label-warning";
                if ($row['result_code'] == '3')
                    $cls = "label-danger";

                $html_body .= '<td class="text-center"><span class="label '. $cls  . '">' . $row['result_name']  . '</span></td>';

                $html_body .= '</tr>';

                if ($include_description) {
                    array_push($reports, array(
                        'inspection_type'=>$row['inspection_type'],
                        'region'=>$row['region_name'],
                        'community'=>$community_name,
                        'job_number'=>$row['job_number'],
                        'address'=>$row['address'],
                        'field_manager'=>$field_manager,
                        'description'=>$row['overall_comments'],
                        'date'=>$row['start_date'],
                        'epo_number'=>$epo_number,
                        're_inspections'=>$row['inspection_count'],
                        'result'=>$row['result_name']
                    ));
                } else {
                    array_push($reports, array(
                        'inspection_type'=>$row['inspection_type'],
                        'region'=>$row['region_name'],
                        'community'=>$community_name,
                        'job_number'=>$row['job_number'],
                        'address'=>$row['address'],
                        'field_manager'=>$field_manager,
                        'date'=>$row['start_date'],
                        'epo_number'=>$epo_number,
                        're_inspections'=>$row['inspection_count'],
                        'result'=>$row['result_name']
                    ));
                }
            }
        }


        $html_body .= '</tbody>';
        $html_body .= '</table>';

        $html_body .= '</div>';


        $html_footer = "</body></html>";

        $html = $html_header . $html_body . $html_footer;

        if ($is_array) {
            return $reports;
        } else {
            return $html;
        }
    }

    private function get_report_data__for_statistics_checklist($region, $community, $start_date, $end_date, $status, $type, $is_array=false) {
        $reports = array();

        $table = " select  a.*, "
                . " c1.name as inspection_type, c2.name as status_name, c3.name as item_name, ch.no as item_no, ch.status as status_code, "
                . " r.region as region_name, loc.name as location_name, "
                . " u.first_name, u.last_name, '' as additional "
                . " from ins_region r, ins_code c1, ins_code c2, ins_code c3, ins_location loc, ins_checklist ch, ins_inspection a "
                . " left join ins_admin u on a.field_manager=u.id and u.kind=2 "
                . " where a.region=r.id and c1.kind='ins' and c1.code=a.type and c2.kind='sts' and c2.code=ch.status "
                . " and loc.inspection_id=a.id and ch.inspection_id=a.id and ch.location_id=loc.id and c3.value=a.type and (c3.kind='drg' or c3.kind='lth') and c3.code=ch.no ";

        $common_sql = "";

        if ($start_date!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.start_date>='$start_date' ";
        }

        if ($end_date!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.end_date<='$end_date' ";
        }

        if ($region!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.region='$region' ";
        }

        if ($community!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.community='$community' ";
        }

        if ($status!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " ch.status='$status' ";
        }

        if ($type!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.type='$type' ";
        }

        $sql = $table;
        if ($common_sql!="") {
            $sql .= " and " . $common_sql;
        }

        $count_sql = " select count(*) from ( " . $sql . " ) t ";
        $total = $this->datatable_model->get_count($count_sql);

        $sql .= " and (ch.status=1 or ch.status=2 or ch.status=3) ";

        $count_text = "Total: " . $total . "";

//        if ($status=="") {
            $count_sql = " SELECT c.name AS status_name, t.status_code, t.tnt "
                    . " FROM ins_code c, ( select a.status_code, count(*) as tnt from ( $sql ) a group by a.status_code ) t "
                    . " WHERE c.kind='sts' AND c.code=t.status_code ORDER BY c.code ";

            $tnt = $this->utility_model->get_list__by_sql($count_sql);
            if ($tnt && is_array($tnt)) {
                foreach ($tnt as $row) {
                    if ($count_text!="") {
                        $count_text .= ", ";
                    }

                    if ($is_array) {
                    } else {
                        $count_text .= '<span class="total-' . $row['status_code'] . '">';
                    }

                    $count_text .= $row['status_name'] . ": " . $row['tnt'];
                    if ($total!=0) {
                        $tnt = intval($row['tnt']);
                        $count_text .= "(" . round($tnt*1.0/$total * 100, 2) . "%)";
                    }

                    if ($is_array) {

                    } else {
                        $count_text .= '</span>';
                    }
                }
            }

                array_push($reports, array(
                   'title'=>$count_text,
                    'count'=>'',
                ));
                array_push($reports, array(
                   'title'=>'',
                    'count'=>'',
                ));

//        }

        $sql .= " order by a.start_date ";

        $table_styles = " .data-table {width: 100%; border: 1px solid #000; } "
                . " .data-table thead th { padding: 7px 5px; } "
                . " .table-bordered { border-collapse: collapse; }"
                . " .table-bordered thead th, .table-bordered tbody td { border: 1px solid #000; }  "
                . " .table-bordered tbody td { font-size: 85%; padding: 4px 4px; }  ";

        $html_styles = "<style type='text/css'> " . $table_styles . " "
                . " .text-right{text-align:right;} "
                . " .text-center{text-align:center;} "
                . " .row{float:left;width:100%;margin-bottom:20px;} "
                . " span.label{} .label-danger{color:#d9534f;} .label-success{color:#5cb85c;} .label-warning{color:#f0ad4e;} "
                . " .col-50-percent{float:left;width:50%;} "
                . ".total-inspection span , .total-checklist span { font-size: 84%; } .total-checklist span.total-1 { color: #02B302; } .total-checklist span.total-2 { color: #e33737; } .total-checklist span.total-3 { color: #11B4CE; }  .total-inspection span.total-1 { color: #02B302; } .total-inspection span.total-2 { color: #e89701; } .total-inspection span.total-3 { color: #e33737; }"
                . "</style>";

        $html_header = "<html><head><meta charset='utf-8'/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/><title>Report</title>" . $html_styles . "</head><body>";
        $html_body = "";

        $html_body .= "<div class='row text-center'>" . '<img alt="" src="' . $this->image_url_change(LOGO_PATH) . '" style="margin: auto; max-width: 400px;">' . "</div>";

        $cls = "text-center";
        $title = "Inspection Report";
        if ($type == '1') {
            $title = "Drainage Plane " . $title;
        }
        if ($type == "2") {
            $title = "Lath " . $title;
        }

        $html_body .= "<h1 class='" . $cls . "'>" . $title . "</h1>";

        $sub_title = "";
        if ($region!="") {
            $r = $this->utility_model->get('ins_region', array('id'=>$region));
            if ($r) {
                $sub_title .= $r['region'];
            }
        }

        if ($community!="") {
            $c = $this->utility_model->get('ins_community', array('community_id'=>$community));
            if ($c) {
                if ($sub_title!="") {
                    $sub_title .= ", ";
                }

                $sub_title .= $c['community_name'];
            }
        }

        $cls = "text-right";

        if ($sub_title!="") {
            $html_body .= "<h5 class='" . $cls . "'>" . $sub_title . "</h5>";
        }

        if ($start_date!="" && $end_date!="") {
            $html_body .= "<h6 class='" . $cls . "'>" . $start_date . " ~ " . $end_date . "</h6>";
        }

        if ($count_text!="") {
            $html_body .= "<h4 class='total-checklist'>" . $count_text . "</h4>";
        }

        $top_sql = " select  a.*, "
                . " c1.name as inspection_type, c2.name as status_name, c3.name as item_name, ch.no as item_no, ch.status as status_code, "
                . " r.region as region_name, loc.name as location_name, "
                . " u.first_name, u.last_name, '' as additional "
                . " from ins_region r, ins_code c1, ins_code c2, ins_code c3, ins_location loc, ins_checklist ch, ins_inspection a left join ins_user u on a.field_manager=u.id "
                . " where a.region=r.id and c1.kind='ins' and c1.code=a.type and c2.kind='sts' and c2.code=ch.status "
                . " and loc.inspection_id=a.id and ch.inspection_id=a.id and ch.location_id=loc.id and c3.value=a.type and c3.code=ch.no and (ch.status=1 or ch.status=2 or ch.status=3) ";

        if ($common_sql!="") {
            $top_sql .= " and " . $common_sql;
        }

        $top_content = $this->get_top_item($top_sql, 'drg', 1, $is_array);
        if ($is_array) {
            if (count($top_content)>0) {
                array_push($reports, array(
                   'title'=>'Most Passed in Drainage Plane Inspection',
                    'count'=>'',
                ));
                foreach ($top_content as $row) {
                    array_push($reports, $row);
                }
            }
        } else {
            if ($top_content!="") {
                $html_body .= '<div class="row">';

                $html_body .= '<table class="data-table table-bordered">';
                $html_body .= '' .
                            '<thead>' .
                                '<tr>' .
                                    '<th colspan="2">Most Passed in Drainage Plane Inspection</th>' .
                                '</tr>' .
                            '</thead>' .
                            '';
                $html_body .= '<tbody>';
                $html_body .= $top_content;
                $html_body .= '</tbody>';
                $html_body .= '</table>';

                $html_body .= '</div>';
            }
        }

        $top_content = $this->get_top_item($top_sql, 'drg', 2, $is_array);
        if ($is_array) {
            if (count($top_content)>0) {
                array_push($reports, array(
                   'title'=>'',
                    'count'=>'',
                ));
                array_push($reports, array(
                   'title'=>'Most Failed in Drainage Plane Inspection',
                    'count'=>'',
                ));
                foreach ($top_content as $row) {
                    array_push($reports, $row);
                }
            }
        } else {
            if ($top_content!="") {
                $html_body .= '<div class="row">';

                $html_body .= '<table class="data-table table-bordered">';
                $html_body .= '' .
                            '<thead>' .
                                '<tr>' .
                                    '<th colspan="2">Most Failed in Drainage Plane Inspection</th>' .
                                '</tr>' .
                            '</thead>' .
                            '';
                $html_body .= '<tbody>';
                $html_body .= $top_content;
                $html_body .= '</tbody>';
                $html_body .= '</table>';

                $html_body .= '</div>';
            }
        }

        $top_content = $this->get_top_item($top_sql, 'drg', 3, $is_array);
        if ($is_array) {
            if (count($top_content)>0) {
                array_push($reports, array(
                   'title'=>'',
                    'count'=>'',
                ));
                array_push($reports, array(
                   'title'=>'Most Not Ready in Drainage Plane Inspection',
                    'count'=>'',
                ));
                foreach ($top_content as $row) {
                    array_push($reports, $row);
                }
            }
        } else {
            if ($top_content!="") {
                $html_body .= '<div class="row">';

                $html_body .= '<table class="data-table table-bordered">';
                $html_body .= '' .
                            '<thead>' .
                                '<tr>' .
                                    '<th colspan="2">Most Not Ready in Drainage Plane Inspection</th>' .
                                '</tr>' .
                            '</thead>' .
                            '';
                $html_body .= '<tbody>';
                $html_body .= $top_content;
                $html_body .= '</tbody>';
                $html_body .= '</table>';

                $html_body .= '</div>';
            }
        }

        $top_content = $this->get_top_item($top_sql, 'lth', 1, $is_array);
        if ($is_array) {
            if (count($top_content)>0) {
                array_push($reports, array(
                   'title'=>'',
                    'count'=>'',
                ));
                array_push($reports, array(
                   'title'=>'Most Passed in Lath Inspection',
                    'count'=>'',
                ));
                foreach ($top_content as $row) {
                    array_push($reports, $row);
                }
            }
        } else {
            if ($top_content!="") {
                $html_body .= '<div class="row">';

                $html_body .= '<table class="data-table table-bordered">';
                $html_body .= '' .
                            '<thead>' .
                                '<tr>' .
                                    '<th colspan="2">Most Passed in Lath Inspection</th>' .
                                '</tr>' .
                            '</thead>' .
                            '';
                $html_body .= '<tbody>';
                $html_body .= $top_content;
                $html_body .= '</tbody>';
                $html_body .= '</table>';

                $html_body .= '</div>';
            }
        }

        $top_content = $this->get_top_item($top_sql, 'lth', 2, $is_array);
        if ($is_array) {
            if (count($top_content)>0) {
                array_push($reports, array(
                   'title'=>'',
                    'count'=>'',
                ));
                array_push($reports, array(
                   'title'=>'Most Failed in Lath Inspection',
                    'count'=>'',
                ));

                foreach ($top_content as $row) {
                    array_push($reports, $row);
                }
            }
        } else {
            if ($top_content!="") {
                $html_body .= '<div class="row">';

                $html_body .= '<table class="data-table table-bordered">';
                $html_body .= '' .
                            '<thead>' .
                                '<tr>' .
                                    '<th colspan="2">Most Failed in Lath Inspection</th>' .
                                '</tr>' .
                            '</thead>' .
                            '';
                $html_body .= '<tbody>';
                $html_body .= $top_content;
                $html_body .= '</tbody>';
                $html_body .= '</table>';

                $html_body .= '</div>';
            }
        }

        $top_content = $this->get_top_item($top_sql, 'lth', 3, $is_array);
        if ($is_array) {
            if (count($top_content)>0) {
                array_push($reports, array(
                   'title'=>'',
                    'count'=>'',
                ));
                array_push($reports, array(
                   'title'=>'Most Not Ready in Lath Inspection',
                    'count'=>'',
                ));
                foreach ($top_content as $row) {
                    array_push($reports, $row);
                }
            }
        } else {
            if ($top_content!="") {
                $html_body .= '<div class="row">';

                $html_body .= '<table class="data-table table-bordered">';
                $html_body .= '' .
                            '<thead>' .
                                '<tr>' .
                                    '<th colspan="2">Most Not Ready in Lath Inspection</th>' .
                                '</tr>' .
                            '</thead>' .
                            '';
                $html_body .= '<tbody>';
                $html_body .= $top_content;
                $html_body .= '</tbody>';
                $html_body .= '</table>';

                $html_body .= '</div>';
            }
        }

//        $html_body .= '<div class="row" style="margin-top: 25px;">';
//
//        $html_body .= '<table class="data-table table-bordered">';
//        $html_body .= '' .
//                '<thead>' .
//                    '<tr>' .
//                        '<th>Type</th>' .
//                        '<th>Region</th>' .
//                        '<th>Community</th>' .
//                        '<th>Date</th>' .
//                        '<th>Location</th>' .
//                        '<th style="width:50%;">CheckItem</th>' .
//                        '<th>Result</th>' .
//                    '</tr>' .
//                '</thead>' .
//                '';
//
//        $html_body .= '<tbody>';
//
//
//        $data = $this->datatable_model->get_content($sql);
//        if ($data && is_array($data)) {
//            foreach ($data as $row) {
//                $html_body .= '<tr>';
//
//                $field_manager = "";
//                if (isset($row['first_name']) && isset($row['last_name']) && $row['first_name']!="" && $row['last_name']!="") {
//                    $field_manager = $row['first_name'] . $row['last_name'];
//                }
//
//                $html_body .= '<td class="text-center">' . $row['inspection_type']  . '</td>';
//                $html_body .= '<td class="text-center">' . $row['region_name']  . '</td>';
//                $html_body .= '<td class="text-center">' . $row['community']  . '</td>';
//                $html_body .= '<td class="text-center">' . $row['start_date']  . '</td>';
//                $html_body .= '<td class="text-center">' . $row['location_name']  . '</td>';
//                $html_body .= '<td class="">' . $row['item_name']  . '</td>';
//
//                $cls = "";
//                if ($row['status_code'] == '1')
//                    $cls = "label-success";
//                if ($row['status_code'] == '3')
//                    $cls = "label-warning";
//                if ($row['status_code'] == '2')
//                    $cls = "label-danger";
//
//                $html_body .= '<td class="text-center"><span class="label '. $cls  . '">' . $row['status_name']  . '</span></td>';
//
//
//                $html_body .= '</tr>';
//            }
//        }
//
//
//        $html_body .= '</tbody>';
//        $html_body .= '</table>';
//
//        $html_body .= '</div>';


        $html_footer = "</body></html>";

        $html = $html_header . $html_body . $html_footer;

        if ($is_array) {
            return $reports;
        } else {
            return $html;
        }
    }

    private function get_top_item($sql, $inspection_type, $status, $is_array=false) {
        $reports = array();
        $result = "";
        $sql .= " and (c3.kind='$inspection_type') and ch.status='$status' ";

        $count_sql = " SELECT c.name AS item_name, t.item_no, t.tnt "
                . " FROM ins_code c, ( select a.status_code, a.item_no, count(*) as tnt from ( $sql ) a group by a.status_code, a.item_no ) t "
                . " WHERE c.kind='$inspection_type' and t.status_code='$status' and c.code=t.item_no "
                . " ORDER BY t.tnt desc "
                . " LIMIT 10 ";

        $top = $this->utility_model->get_list__by_sql($count_sql);
        if ($top && is_array($top)) {
            foreach ($top as $row) {
                $result .= '<tr>';
                $result .= '<td>' . $row['item_no'] . ". " . $row['item_name'] . '</td>';
                $result .= '<td class="text-center">' . $row['tnt'] . '</td>';
                $result .= '</tr>';

                array_push($reports, array(
                   'title'=>$row['item_no'] . ". " . $row['item_name'],
                    'count'=>$row['tnt'],
                ));
            }
        }

        if ($is_array) {
            return $reports;
        } else {
            return $result;
        }
    }


    private function get_scheduling_data($inspector_id, $region, $community, $start_date, $end_date, $ordering) {
        $result = array();

        $cols = array( "a.requested_at", "a.community_name", "a.job_number", "a.address", "c.city", "m.first_name", "a.category", "a.time_stamp" );

        $common_sql = "";

        if ($inspector_id!==false && $inspector_id!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.inspector_id='$inspector_id' ";
        }

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

        $order_sql = "";
        if ($ordering!==false && $ordering!="") {
            $order_item = explode(",,", $ordering);
            if (is_array($order_item)) {
                foreach ($order_item as $row) {
                    $order_cell = explode(",", $row);
                    if (is_array($order_cell) && count($order_cell)==2) {
                        $col = intval($order_cell[0]);
                        $dir = $order_cell[1];

                        if ($col<0 || $col>7){
                            $col=0;
                        }

                        if ($order_sql!="") {
                            $order_sql .= ", ";
                        }

                        $order_sql .= $cols[$col] . " " . $dir . " ";
                    }
                }

                if ($order_sql!="") {
                    $order_sql = " order by " . $order_sql;
                }
            }
        }

        $table = " ins_inspection_requested a "
               . " left join ins_community c on c.community_id=substr(a.job_number,1,4)"
               . " left join ins_region r on c.region=r.id "
               . " left join ins_admin m on a.manager_id=m.id "
               . " ";

        $sql = " select  a.id, a.category, a.reinspection, a.epo_number, a.job_number, a.requested_at, a.assigned_at, a.completed_at, a.manager_id, a.inspector_id, "
                . " a.time_stamp, a.ip_address, a.community_name, a.lot, a.address, a.status, a.area, a.volume, a.qn, a.city as city_duct, "
                . " concat(m.first_name, ' ', m.last_name) as field_manager_name, "
                . " c1.name as category_name, c.community_id, c.region, r.region as region_name, c.city, "
                . " u.first_name, u.last_name "
                . " from ins_user u, ins_code c1, " . $table . " where u.id=a.inspector_id and c1.kind='ins' and c1.code=a.category and a.status=1 ";

        if ($common_sql!="") {
            $sql .= " and " . $common_sql;
        }

        $sql .= $order_sql;
        $data = $this->datatable_model->get_content($sql);

        if ($data && is_array($data)) {
            $header = array();
            if ($inspector_id=="") {
                $header['inspector'] = "Inspector";
            }
            $header['requested_at'] = "Inspection Date";
            $header['community'] = "Community";
            $header['job_number'] = "Job Number";
            $header['address'] = "Address";
            $header['city'] = "City";
            $header['field_manager'] = "Field Manager";
            $header['inspection_type'] = "Inspection Type";
            $header['time_stamp'] = "Requested Time";

            array_push($result, $header);

            $last_inspector_id = "";
            foreach ($data as $row) {
                $item = array();

                if ($inspector_id=="") {
                    if ($last_inspector_id=="" || $last_inspector_id!=$row['inspector_id']) {
                        $last_inspector_id = $row['inspector_id'];

                        $item['inspector'] = $row['first_name'] . " " . $row['last_name'];
                        $item['requested_at'] = "";
                        $item['community'] = "";
                        $item['job_number'] = "";
                        $item['address'] = "";
                        $item['city'] = "";
                        $item['field_manager'] = "";
                        $item['inspection_type'] = "";
                        $item['time_stamp'] = "";

                        array_push($result, $item);
                    }
                }

                if ($inspector_id=="") {
                    $item['inspector'] = "";
                }

                $item['requested_at'] = $row['requested_at'];
                $item['community'] = $row['community_name'];
                $item['job_number'] = $row['job_number'];
                $item['address'] = $row['address'];
                $item['city'] = $row['city'];
                if ($row['category']==3) {
                    $item['city'] = $row['city_duct'];
                }

                $item['field_manager'] = $row['field_manager_name'];
                $item['inspection_type'] = $row['category_name'];
                $item['time_stamp'] = mdate('%Y-%m-%d %H:%i:%s', strtotime($row['time_stamp']));

                array_push($result, $item);
            }
        }

        return $result;
    }


    private function get_report_data__for_statistics_fieldmanager($region, $start_date, $end_date, $type, $is_array=false) {
        $reports = array();

        $table = " ins_admin a where a.kind=2 ";
//        $table = " ( select field_manager from ins_building group by field_manager ) b "
//                . " left join ins_admin a on a.kind=2 and concat(a.first_name, ' ', a.last_name)=b.field_manager"
//                . " left join ins_region r on r.id=a.region where b.field_manager is not null and b.field_manager<>'' ";

        $common_sql = "";

        if ($start_date!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.start_date>='$start_date' ";
        }

        if ($end_date!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.end_date<='$end_date' ";
        }

        if ($region!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.region='$region' ";
            $table .= " and a.id in ( select manager_id from ins_admin_region where region='$region' ) ";
        }

        if ($type!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.type='$type' ";
        }

        $sql = " select  a.* "
                . " from " . $table . " "
                . " order by a.first_name ";

        $data = $this->datatable_model->get_content($sql);
        $table_data = array();

        foreach ($data as $row) {
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

            $inspections = 0;
            if (isset($row['id']) && $row['id']!='') {
                $sql = " select count(*) from ins_inspection a where a.field_manager='" . $row['id'] . "' ";
                if ($common_sql!="") {
                    $sql .= " and " . $common_sql;
                }
                $inspections = $this->datatable_model->get_count($sql);
                $row['inspections'] = $inspections;
            } else {

            }

            if ($inspections==0) {
                $row['not_ready'] = 0;
                $row['pass'] = 0;
                $row['pass_with_exception'] = 0;
                $row['fail'] = 0;
                $row['reinspection'] = 0;

            } else {
                $sql = " select count(*) from ins_inspection a where a.field_manager='" . $row['id'] . "' and a.house_ready='0' ";
                if ($common_sql!="") {
                    $sql .= " and " . $common_sql;
                }

                $not_ready = $this->datatable_model->get_count($sql);
                $row['not_ready'] = round($not_ready*1.0 / $inspections * 100.0, 2);


                $sql = " select count(*) from ins_inspection a where a.field_manager='" . $row['id'] . "' and a.result_code=1 ";
                if ($common_sql!="") {
                    $sql .= " and " . $common_sql;
                }

                $pass = $this->datatable_model->get_count($sql);
                $row['pass'] = round($pass*1.0 / $inspections * 100.0, 2);


                $sql = " select count(*) from ins_inspection a where a.field_manager='" . $row['id'] . "' and a.result_code=2 ";
                if ($common_sql!="") {
                    $sql .= " and " . $common_sql;
                }

                $pass_with_exception = $this->datatable_model->get_count($sql);
                $row['pass_with_exception'] = round($pass_with_exception*1.0 / $inspections * 100.0, 2);


                $sql = " select count(*) from ins_inspection a where a.field_manager='" . $row['id'] . "' and a.result_code=3 ";
                if ($common_sql!="") {
                    $sql .= " and " . $common_sql;
                }

                $fail = $this->datatable_model->get_count($sql);
                $row['fail'] = round($fail*1.0 / $inspections * 100.0, 2);


                $sql = " select count(*) from ins_inspection a left join ins_inspection_requested r on a.requested_id=r.id where a.field_manager='" . $row['id'] . "' and r.reinspection=1 ";
                if ($common_sql!="") {
                    $sql .= " and " . $common_sql;
                }

                $reinspection = $this->datatable_model->get_count($sql);
                $row['reinspection'] = round($reinspection*1.0 / $inspections * 100.0, 2);
            }

            array_push($table_data, $row);
        }


        $table_styles = " .data-table {width: 100%; border: 1px solid #000; } "
                . " .data-table thead th { padding: 7px 5px; } "
                . " .table-bordered { border-collapse: collapse; }"
                . " .table-bordered thead th, .table-bordered tbody td { border: 1px solid #000; }  "
                . " .table-bordered tbody td { font-size: 85%; padding: 4px 4px; }  ";

        $html_styles = "<style type='text/css'> " . $table_styles . " "
                . " .text-right{text-align:right;} "
                . " .text-center{text-align:center;} "
                . " .row{float:left;width:100%;margin-bottom:20px;} "
                . " span.label{} .label-danger{color:#d9534f;} .label-success{color:#5cb85c;} .label-warning{color:#f0ad4e;} "
                . " .col-50-percent{float:left;width:50%;} "
                . ".total-inspection span , .total-checklist span { font-size: 84%; } .total-checklist span.total-1 { color: #02B302; } .total-checklist span.total-2 { color: #e33737; } .total-checklist span.total-3 { color: #11B4CE; }  .total-inspection span.total-1 { color: #02B302; } .total-inspection span.total-2 { color: #e89701; } .total-inspection span.total-3 { color: #e33737; }"
                . "</style>";

        $html_header = "<html><head><meta charset='utf-8'/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/><title>Report</title>" . $html_styles . "</head><body>";
        $html_body = "";

        $html_body .= "<div class='row text-center'>" . '<img alt="" src="' . $this->image_url_change(LOGO_PATH) . '" style="margin: auto; max-width: 400px;">' . "</div>";

        $cls = "text-center";
        $title = "Inspection Report";
        if ($type == '1') {
            $title = "Drainage Plane " . $title;
        }
        if ($type == "2") {
            $title = "Lath " . $title;
        }

        $html_body .= "<h1 class='" . $cls . "'>" . $title . "</h1>";

        $sub_title = "";
        if ($region!="") {
            $r = $this->utility_model->get('ins_region', array('id'=>$region));
            if ($r) {
                $sub_title .= $r['region'];
            }
        }

        $cls = "text-right";
        if ($sub_title!="") {
            $html_body .= "<h5 class='" . $cls . "'>" . $sub_title . "</h5>";
        }

        if ($start_date!="" && $end_date!="") {
            $html_body .= "<h6 class='" . $cls . "'>" . $start_date . " ~ " . $end_date . "</h6>";
        }

        $html_body .= '<div class="row" style="margin-top: 10px;">';

        $html_body .= '<table class="data-table table-bordered">';
        $html_body .= '' .
                '<thead>' .
                    '<tr>' .
                        '<th>Field Manager</th>' .
                        '<th>Region</th>' .
                        '<th>Total Inspections</th>' .
                        '<th>Not Ready(%)</th>' .
                        '<th>Pass(%)</th>' .
                        '<th>Pass with Exception(%)</th>' .
                        '<th>Fail(%)</th>' .
                        '<th>Reinspections(%)</th>' .
                    '</tr>' .
                '</thead>' .
                '';

        $html_body .= '<tbody>';

        array_push($reports, array(
            'field_manager'=>'Field Manager',
            'region'=>'Region',
            'inspections'=>'Total Inspections',
            'not_ready'=>'Not Ready(%)',
            'pass'=>'Pass(%)',
            'pass_with_exception'=>'Pass With Exception(%)',
            'fail'=>'Fail(%)',
            'reinspections'=>'Reinspections(%)',
        ));

        if ($table_data && is_array($table_data)) {
            foreach ($table_data as $row) {
                $html_body .= '<tr>';

                $field_manager = "";
//                $field_manager = $row['field_manager'];
                if (isset($row['first_name']) && isset($row['last_name']) && $row['first_name']!="" && $row['last_name']!="") {
                    $field_manager = $row['first_name'] . $row['last_name'];
                }

                $html_body .= '<td class="text-center">' . $field_manager  . '</td>';
                $html_body .= '<td class="text-center">' . $row['region_name']  . '</td>';
                $html_body .= '<td class="text-center">' . $row['inspections']  . '</td>';
                $html_body .= '<td class="text-center">' . $row['not_ready']  . '</td>';
                $html_body .= '<td class="text-center">' . $row['pass']  . '</td>';
                $html_body .= '<td class="text-center">' . $row['pass_with_exception']  . '</td>';
                $html_body .= '<td class="text-center">' . $row['fail']  . '</td>';
                $html_body .= '<td class="text-center">' . $row['reinspection']  . '</td>';

                $html_body .= '</tr>';

                array_push($reports, array(
                    'field_manager'=>$field_manager,
                    'region'=>$row['region_name'],
                    'inspections'=>$row['inspections'],
                    'not_ready'=>$row['not_ready'],
                    'pass'=>$row['pass'],
                    'pass_with_exception'=>$row['pass_with_exception'],
                    'fail'=>$row['fail'],
                    'reinspections'=>$row['reinspection'],
                ));
            }
        }

        $html_body .= '</tbody>';
        $html_body .= '</table>';

        $html_body .= '</div>';

        $html_footer = "</body></html>";

        $html = $html_header . $html_body . $html_footer;

        if ($is_array) {
            return $reports;
        } else {
            return $html;
        }
    }

    private function get_report_data__for_statistics_inspector($region, $start_date, $end_date, $type, $is_array=false) {
        $reports = array();
        $table = " ins_user a ";

        $common_sql = "";

        if ($start_date!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.start_date>='$start_date' ";
        }

        if ($end_date!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.end_date<='$end_date' ";
        }

        if ($region!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.region='$region' ";
        }

        if ($type!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.type='$type' ";
        }

        $sql = " select  a.* "
                . " from " . $table . " "
                . " order by a.first_name ";

        $data = $this->datatable_model->get_content($sql);
        $table_data = array();

        foreach ($data as $row) {
            $sql = " select count(*) from ins_inspection a where a.user_id='" . $row['id'] . "' ";
            if ($common_sql!="") {
                $sql .= " and " . $common_sql;
            }
            $inspections = $this->datatable_model->get_count($sql);
            $row['inspections'] = $inspections;
            $row['fee'] = number_format($row['fee'] * $inspections, 2);

            if ($inspections==0) {
                $row['not_ready'] = 0;
                $row['pass'] = 0;
                $row['pass_with_exception'] = 0;
                $row['fail'] = 0;
                $row['reinspection'] = 0;

            } else {
                $sql = " select count(*) from ins_inspection a where a.user_id='" . $row['id'] . "' and a.house_ready='0' ";
                if ($common_sql!="") {
                    $sql .= " and " . $common_sql;
                }

                $not_ready = $this->datatable_model->get_count($sql);
                $row['not_ready'] = round($not_ready*1.0 / $inspections * 100.0, 2);


                $sql = " select count(*) from ins_inspection a where a.user_id='" . $row['id'] . "' and a.result_code=1 ";
                if ($common_sql!="") {
                    $sql .= " and " . $common_sql;
                }

                $pass = $this->datatable_model->get_count($sql);
                $row['pass'] = round($pass*1.0 / $inspections * 100.0, 2);


                $sql = " select count(*) from ins_inspection a where a.user_id='" . $row['id'] . "' and a.result_code=2 ";
                if ($common_sql!="") {
                    $sql .= " and " . $common_sql;
                }

                $pass_with_exception = $this->datatable_model->get_count($sql);
                $row['pass_with_exception'] = round($pass_with_exception*1.0 / $inspections * 100.0, 2);


                $sql = " select count(*) from ins_inspection a where a.user_id='" . $row['id'] . "' and a.result_code=3 ";
                if ($common_sql!="") {
                    $sql .= " and " . $common_sql;
                }

                $fail = $this->datatable_model->get_count($sql);
                $row['fail'] = round($fail*1.0 / $inspections * 100.0, 2);


                $sql = " select count(*) from ins_inspection a left join ins_inspection_requested r on a.requested_id=r.id where a.user_id='" . $row['id'] . "' and r.reinspection=1 ";
                if ($common_sql!="") {
                    $sql .= " and " . $common_sql;
                }

                $reinspection = $this->datatable_model->get_count($sql);
                $row['reinspection'] = round($reinspection*1.0 / $inspections * 100.0, 2);
            }

            array_push($table_data, $row);
        }


        $table_styles = " .data-table {width: 100%; border: 1px solid #000; } "
                . " .data-table thead th { padding: 7px 5px; } "
                . " .table-bordered { border-collapse: collapse; }"
                . " .table-bordered thead th, .table-bordered tbody td { border: 1px solid #000; }  "
                . " .table-bordered tbody td { font-size: 85%; padding: 4px 4px; }  ";

        $html_styles = "<style type='text/css'> " . $table_styles . " "
                . " .text-right{text-align:right;} "
                . " .text-center{text-align:center;} "
                . " .row{float:left;width:100%;margin-bottom:20px;} "
                . " span.label{} .label-danger{color:#d9534f;} .label-success{color:#5cb85c;} .label-warning{color:#f0ad4e;} "
                . " .col-50-percent{float:left;width:50%;} "
                . ".total-inspection span , .total-checklist span { font-size: 84%; } .total-checklist span.total-1 { color: #02B302; } .total-checklist span.total-2 { color: #e33737; } .total-checklist span.total-3 { color: #11B4CE; }  .total-inspection span.total-1 { color: #02B302; } .total-inspection span.total-2 { color: #e89701; } .total-inspection span.total-3 { color: #e33737; }"
                . "</style>";

        $html_header = "<html><head><meta charset='utf-8'/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/><title>Report</title>" . $html_styles . "</head><body>";
        $html_body = "";

        $html_body .= "<div class='row text-center'>" . '<img alt="" src="' . $this->image_url_change(LOGO_PATH) . '" style="margin: auto; max-width: 400px;">' . "</div>";

        $cls = "text-center";
        $title = "Inspection Report";
        if ($type == '1') {
            $title = "Drainage Plane " . $title;
        }
        if ($type == "2") {
            $title = "Lath " . $title;
        }

        $html_body .= "<h1 class='" . $cls . "'>" . $title . "</h1>";

        $sub_title = "";
        if ($region!="") {
            $r = $this->utility_model->get('ins_region', array('id'=>$region));
            if ($r) {
                $sub_title .= $r['region'];
            }
        }

        $cls = "text-right";
        if ($sub_title!="") {
            $html_body .= "<h5 class='" . $cls . "'>" . $sub_title . "</h5>";
        }

        if ($start_date!="" && $end_date!="") {
            $html_body .= "<h6 class='" . $cls . "'>" . $start_date . " ~ " . $end_date . "</h6>";
        }

        $html_body .= '<div class="row" style="margin-top: 10px;">';

        $html_body .= '<table class="data-table table-bordered">';
        $html_body .= '' .
                '<thead>' .
                    '<tr>' .
                        '<th>Inspector</th>' .
                        '<th>Total Inspections</th>' .
                        '<th>Not Ready(%)</th>' .
                        '<th>Pass(%)</th>' .
                        '<th>Pass with Exception(%)</th>' .
                        '<th>Fail(%)</th>' .
                        '<th>Reinspections(%)</th>' .
                        '<th>Total Fee($)</th>' .
                    '</tr>' .
                '</thead>' .
                '';

        $html_body .= '<tbody>';

        array_push($reports, array(
            'inspector' => 'Inspector',
            'inspections' => 'Total Inspections',
            'not_ready' => 'Not Ready(%)',
            'pass' => 'Pass(%)',
            'pass_with_exception' => 'Pass With Exception(%)',
            'fail' => 'Fail(%)',
            'reinspections' => 'Reinspections(%)',
            'fee' => 'Total Fee($)',
        ));

        if ($table_data && is_array($table_data)) {
            foreach ($table_data as $row) {
                $html_body .= '<tr>';

                $field_manager = "";
                if (isset($row['first_name']) && isset($row['last_name']) && $row['first_name']!="" && $row['last_name']!="") {
                    $field_manager = $row['first_name'] . $row['last_name'];
                }

                $html_body .= '<td class="text-center">' . $field_manager  . '</td>';
                $html_body .= '<td class="text-center">' . $row['inspections']  . '</td>';
                $html_body .= '<td class="text-center">' . $row['not_ready']  . '</td>';
                $html_body .= '<td class="text-center">' . $row['pass']  . '</td>';
                $html_body .= '<td class="text-center">' . $row['pass_with_exception']  . '</td>';
                $html_body .= '<td class="text-center">' . $row['fail']  . '</td>';
                $html_body .= '<td class="text-center">' . $row['reinspection']  . '</td>';
                $html_body .= '<td class="text-center">' . $row['fee']  . '</td>';

                $html_body .= '</tr>';

                array_push($reports, array(
                    'inspector'=>$field_manager,
                    'inspections'=>$row['inspections'],
                    'not_ready'=>$row['not_ready'],
                    'pass'=>$row['pass'],
                    'pass_with_exception'=>$row['pass_with_exception'],
                    'fail'=>$row['fail'],
                    'reinspections'=>$row['reinspection'],
                    'fee'=>$row['fee'],
                ));
            }
        }


        $html_body .= '</tbody>';
        $html_body .= '</table>';

        $html_body .= '</div>';


        $html_footer = "</body></html>";

        $html = $html_header . $html_body . $html_footer;

        if ($is_array) {
            return $reports;
        } else {
            return $html;
        }
    }

    public function testFunc($param1,$param2){
      switch ($param1) {
        case '1':
          echo $this->get_report_html__for_duct_leakage($param2);
          break;
          case '2':
            echo $this->get_report_html__for_envelop_leakage($param2);
            break;
        default:
          # code...
          break;
      }
    }
    private function get_report_html__for_duct_leakage($inspection_id) {
        $sql = " select a.*, u.email, c2.name as result_name "
                . " , c3.name as result_duct_leakage_name, c4.name as result_envelop_leakage_name "
                . " from ins_code c2, ins_code c3, ins_code c4, ins_inspection a "
                . " left join ins_user u on a.user_id=u.id "
                . " where a.id='" . $inspection_id . "' and c2.kind='rst' and c2.code=a.result_code and c3.kind='rst_duct' and c3.code=a.result_duct_leakage and c4.kind='rst_envelop' and c4.code=a.result_envelop_leakage ";

        $inspection = $this->utility_model->get__by_sql($sql);

        $html = "";
        $html_head = "";
        $html_body = "";

        $html .= '<html>';
        $html_head .= '<head>';
        $html_head .= '<style type="text/css">'
                        . ' body { font-family: Arial, sans-serif; padding: 0; margin: 0; } '
                        . '.title {    font-size: 23.92px; padding: 0 140px; line-height: 23px;  margin-bottom: 4px; }'
                        . '.sub-title { font-size: 21.12px; margin-bottom: 0px;  }'
                        . 'h2.sub-title { font-size: 19.24px; margin-top: 28px; font-weight: 600; margin-bottom: 3px; line-height: 16px; }'
                        . '.text-center { text-align: center; }'
                        . '.text-underline { padding-bottom: 3px; border-bottom: 1px solid #333; }'
                        . '.font-light { font-weight: 100; }'
                        . '.font-bold { font-weight: bold; }'
                        . '.width-full { width: 100%; }'
                        . '.performance-method {  font-size: 10.92px; padding: 5px 12px 1px;  border: 3px solid #000; }'
                        . '.performance-method td { padding: 1px 2px; vertical-align: top; }'
                        . '.row {  display: block; width: 100%;  }      '
                        . '.test-result { font-size: 16.42px; border: 1px solid #000; border-collapse: collapse; }'
                        . '.test-result td { border: 1px solid #000; vertical-align: top; padding: 16px 8px 8px; }'
                        . '.test-result td span.text-underline { padding: 0 4px; }'
                        . '.test-result td.result-line { text-align: center; width: 9%; }'
                        . '.test-result td.result-system { text-align: left; width: 31%; padding-left: 16px; }'
                        . '.test-result td.result-leakage { text-align: center; width: 60%; }'
                        . '.width-25-percent { width: 32%; }'
                        . '.width-40-percent { width: 42%; }'
                        . '.width-50-percent { width: 36%; }'
                        . '.width-60-percent { width: 58%; }'
                        . '.inline-container>div { display: inline-block; }'
                        . '.img-responsive { max-width: 100%; }'
                        . '.footer-description { font-size: 12.92px; font-weight: 100; margin-top: 8px; margin-bottom: 32px; }'
                        . 'td.footer-padding { padding: 4px 24px 32px; vertical-align: top; }'
                        . 'td.footer-small-padding { padding: 8px 12px 8px 8px; vertical-align: top; }'
                        . '.footer-value { font-size: 13.72px; font-weight: bold; padding: 10px 0; }'
                        . '.footer .width-60-percent { border: 1px solid #000; }'
                    . '</style>';
        $html_head .= '</head>';

        $html_body .= '<body>';

        if ($inspection) {
          $builder = "WCI";
          if($inspection['type'] == 3){
            $builder = "WCI";
          }else{
            $builder = "Pulte";
          }
            $html_body .= '<h2 class="font-light" style="font-size: 13px;">FORM R405-2014 Duct Leakage Test Report Performance Method</h2>';
            $html_body .= '<h1 class="title text-center">FLORIDA ENERGY EFFICIENCY CODE FOR BUILDING CONSTRUCTION</h1>';
            $html_body .= '<h1 class="sub-title text-center font-light" style="margin-top: 3px; padding:0 140px;">Form R405 Duct Leakage Test Report Performance Method</h1>';


            $html_body .= '<div class="row" style="padding: 0 2px;">';
            $html_body .= '<table class="performance-method width-full" style="">';
            $html_body .= '<tr><td style="width: 55%;">Project Name: <span class="text-value">' . $inspection['community'] . '</span></td><td style="width: 45%;">Builder Name: <span class="text-value">' . $builder.'</td></tr>';
            $html_body .= '<tr><td>Street: <span class="text-value">' . $inspection['address'] . '</span></td><td>Permit Office: </td></tr>';
            $html_body .= '<tr><td>City, State, Zip: <span class="text-value">' . $inspection['city'] . '</span></td><td>Permit Number: </td></tr>';
            $html_body .= '<tr><td>Design Location: <span class="text-value">' . $inspection['design_location'] . '</span></td><td>Jurisidiction: </td></tr>';
            $html_body .= '<tr><td>&nbsp;</td><td>Duct Test Time: Post Construction</td></tr>';
            $html_body .= '</table>';
            $html_body .= '</div>';

            $cfm25_system_1 = $this->cfm25($inspection_id, 1);
            $cfm25_system_2 = $this->cfm25($inspection_id, 2);
            $cfm25_system_3 = $this->cfm25($inspection_id, 3);
            $cfm25_system_4 = $this->cfm25($inspection_id, 4);
            $cfm25_system = $cfm25_system_1 + $cfm25_system_2 + $cfm25_system_3 + $cfm25_system_4;

            $html_body .= '<h2 class="sub-title text-center">Duct Leakage Test Results</h2>';
            $html_body .= '<div class="row">';
            $html_body .= '<table class="width-full" style="border-collapse: collapse;"><tr><td class="width-25-percent">&nbsp;</td>';
            $html_body .= '<td class="width-50-percent">'
                            . '<table class="test-result width-full">'
                                . '<tr><td colspan="3" style="padding-top: 20px; font-size: 18px;">CFM25 Duct Leakage Test Values</td></tr>'
                                . '<tr><td class="result-line">Line</td><td class="result-system">System</td><td class="result-leakage">Outside Duct Leakage</td></tr>'
                                . '<tr><td class="result-line">1</td><td class="result-system">System 1</td><td class="result-leakage"><span class="text-underline">&nbsp;&nbsp;&nbsp; ' . $this->show_decimal($cfm25_system_1, 1) . ' &nbsp;&nbsp;&nbsp;</span> cfm25(Out)</td></tr>'
                                . '<tr><td class="result-line">2</td><td class="result-system">System 2</td><td class="result-leakage"><span class="text-underline">&nbsp;&nbsp;&nbsp; ' . $this->show_decimal($cfm25_system_2, 1) . ' &nbsp;&nbsp;&nbsp;</span> cfm25(Out)</td></tr>'
                                . '<tr><td class="result-line">3</td><td class="result-system">System 3</td><td class="result-leakage"><span class="text-underline">&nbsp;&nbsp;&nbsp; ' . $this->show_decimal($cfm25_system_3, 1) . ' &nbsp;&nbsp;&nbsp;</span> cfm25(Out)</td></tr>'
                                . '<tr><td class="result-line">4</td><td class="result-system">System 4</td><td class="result-leakage"><span class="text-underline">&nbsp;&nbsp;&nbsp; ' . $this->show_decimal($cfm25_system_4, 1) . ' &nbsp;&nbsp;&nbsp;</span> cfm25(Out)</td></tr>'
                                . '<tr>'
                                    . '<td class="result-line">5</td><td class="result-system font-bold">Total House Duct System Leakage</td>'
                                    . '<td class="result-leakage-total" style="padding-bottom: 96px; line-height: 28px;">'
                                        . 'Sum lines 1-4 <span class="text-underline">&nbsp;&nbsp;&nbsp; ' . $this->show_decimal($cfm25_system, 1) . ' &nbsp;&nbsp;&nbsp;</span> <br>'
                                        . 'Divide by &nbsp;&nbsp;&nbsp; <span class="text-underline">&nbsp;&nbsp;&nbsp; ' . $this->show_decimal($inspection['area'], 0) . ' &nbsp;&nbsp;&nbsp;</span> <br>'
                                        . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; (Total Conditioned Floor Area) <br>'
                                        . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; = &nbsp; <span class="text-underline">&nbsp; ' . $this->show_decimal($inspection['qn_out'], 3) . ' &nbsp;</span> <span class="font-bold">(Qn,Out)</span>'
                                    . '</td>'
                                . '</tr>'
                            . '</table>'
                        . '</td>';
            $html_body .= '<td class="width-25-percent">&nbsp;</td>';
            $html_body .= '</tr></table>';
            $html_body .= '</div>';

            $html_body .= '<div class="row" style="height: 16px;"></div>';

            $html_body .= '<div class="row"><table class="width-full footer"><tr>';

            $html_body .= '<td class="width-40-percent footer-padding">';
            $html_body .= '<br><br>';
            $html_body .= '<h2 class="footer-description">I certify the tested duct leakage to outside, Qn, is not greater than the proposed duct leakage Qn specified on Form R405-2014.</h2>';

            $html_body .= '<table class="">'
                        . '<tr>'
                            . '<td style="vertical-align:bottom;"><span class="footer-value">SIGNATURE: </span></td>'
                            . '<td style="padding-left: 8px; vertical-align:bottom; border-bottom: 1px solid #000;"><img class="img-responsive" src="' . $this->image_url_change(base_url()) . 'resource/upload/signature.png" alt="" style="height: 42px;"></td>'
                        . '</tr>'
                        . '<tr>'
                            . '<td style="vertical-align:bottom;"><span class="footer-value">RESNET ID: 9377172</span></td>'
                        . '</tr>'
                        . '</table>';
            $html_body .= '<br>';
            $html_body .= '<h3 class="footer-value">PRINTED NAME: <span class="text-underline">&nbsp;&nbsp;&nbsp;' . 'Tom Karras - Rater ID 791' . '&nbsp;&nbsp;&nbsp;</span></h3>';
            $html_body .= '<br>';
            $html_body .= '<h3 class="footer-value">DATE: <span class="text-underline">&nbsp;&nbsp;&nbsp;' . $inspection['end_date'] . '&nbsp;&nbsp;&nbsp;</span></h3>';
            $html_body .= '</td>';

            $html_body .= '<td class="width-60-percent footer-small-padding">';
            $html_body .= '<table class="width-full">'
                        . '<tr>'
                            . '<td style="vertical-align: middle;"><h2 class="footer-description" style="padding-right: 16px; padding-top: 8px;">Duct tightness shall be verified by testing to Section 803 of the RESNET Standards by an energy rater certified in accordance with Section 553.99, Florida Statutes.</h2></td>'
                            . '<td style="padding-left:12px;"><img src="' . $this->image_url_change(base_url()) . 'resource/upload/wci.png" alt="" style="width: 164px; margin-top: 4px;"></td>'
                        . '</tr>'
                        . '</table>';
            $html_body .= '<h3 class="footer-value">BUILDING OFFICIAL: _______________</h3>';
            $html_body .= '<h3 class="footer-value">DATE: ____________________________</h3>';
            $html_body .= '</td>';

            $html_body .= '</tr></table></div>';

            $html_body .= '<div class="row" style="height: 16px;"></div>';
        }

        $html_body .= '</body>';

        $html .= $html_head . $html_body;
        $html .= '</html>';

        return $html;
    }

    private function get_report_html__for_envelop_leakage($inspection_id) {
        $sql = " select a.*, u.email, c2.name as result_name "
                . " , c3.name as result_duct_leakage_name, c4.name as result_envelop_leakage_name "
                . " from ins_code c2, ins_code c3, ins_code c4, ins_inspection a "
                . " left join ins_user u on a.user_id=u.id "
                . " where a.id='" . $inspection_id . "' and c2.kind='rst' and c2.code=a.result_code and c3.kind='rst_duct' and c3.code=a.result_duct_leakage and c4.kind='rst_envelop' and c4.code=a.result_envelop_leakage ";

        $inspection = $this->utility_model->get__by_sql($sql);

        $html = "";
        $html_head = "";
        $html_body = "";

        $html .= '<html>';
        $html_head .= '<head>';
        $html_head .= '<style type="text/css">'
                        . ' body { font-family: Arial, sans-serif; padding: 0; margin: 0; } '
                        . '.title {    font-size: 23.92px; padding: 16px 140px 0; line-height: 23px; margin-bottom: 4px; }'
                        . '.sub-title { font-size: 21.12px; margin-bottom: 4px; line-height: 22px; }'
                        . 'h2.sub-title { font-size: 19.24px; margin-top: 28px; font-weight: 600; margin-bottom: 3px; line-height: 16px; }'
                        . '.text-center { text-align: center; }'
                        . '.text-underline { padding-bottom: 3px; border-bottom: 1px solid #333; }'
                        . '.font-light { font-weight: 100; }'
                        . '.font-bold { font-weight: bold; }'
                        . '.width-full { width: 100%; }'
                        . '.performance-method {  font-size: 10.92px; padding: 5px 12px 3px;  border: 3px solid #000; }'
                        . '.performance-method td { padding: 1px 2px; vertical-align: top; }'
                        . '.row {  display: block; width: 100%; margin-left: -10px; margin-right: -10px;  }      '
                        . '.test-result { font-size: 13.92px; border: 1px solid #000; border-collapse: collapse; }'
                        . '.test-result td { border: 1px solid #000; vertical-align: top; padding: 16px 8px 8px; }'
                        . '.test-result td span.text-underline { padding: 0 4px; }'
                        . '.test-result td.result-line { text-align: center; width: 9%; }'
                        . '.test-result td.result-system { text-align: left; width: 31%; padding-left: 16px; }'
                        . '.test-result td.result-leakage { text-align: center; width: 60%; }'
                        . '.width-25-percent { width: 32%; }'
                        . '.width-40-percent { width: 42%; }'
                        . '.width-50-percent { width: 36%; }'
                        . '.width-60-percent { width: 58%; }'
                        . '.width-30-percent { width: 40%; }'
                        . '.width-70-percent { width: 60%; }'
                        . '.inline-container>div { display: inline-block; }'
                        . '.img-responsive { max-width: 100%; }'
                        . '.footer-description { font-size: 12.92px; font-weight: 100; margin-top: 8px; margin-bottom: 32px; }'
                        . 'td.footer-padding { padding: 4px 24px 32px; vertical-align: top; }'
                        . 'td.footer-small-padding { padding: 8px 12px 8px 8px; vertical-align: top; }'
                        . '.footer-value { font-size: 13.72px; font-weight: bold; padding: 10px 0; }'
                        . '.footer .width-60-percent { border: 1px solid #000; }'
                        . '.border-bottom { border-bottom: 1px solid #000; } '
                        . '.part-title { font-size: 18.56px; margin-bottom: 4px; font-weight: 100; line-height: 22px; }'
                        . '.test-result td.house-pressure { text-align: center; width: 200px; }'
                        . '.test-result td.flow { text-align: center; width: 150px; }'
                        . '.leakage-characteristics td { padding: 4px 4px; } '
                        . 'li { padding: 3px 0; } '
                    . '</style>';
        $html_head .= '</head>';

        $html_body .= '<body>';

        if ($inspection) {
          $builder = "WCI";
          if($inspection['type'] == 3){
            $builder = "WCI";
          }else{
            $builder = "Pulte";
          }

            $html_body .= '<h1 class="title text-center">FLORIDA ENERGY EFFICIENCY CODE FOR BUILDING CONSTRUCTION</h1>';
            $html_body .= '<h1 class="sub-title text-center font-light" style="margin-top: 3px; padding:0 100px;">Envelope Leakage Test Report<br>Prescriptive and Performance Method</h1>';

            $html_body .= '<div class="row" style="padding: 0 2px;">';
            $html_body .= '<table class="performance-method width-full" style="">';
            $html_body .= '<tr><td style="width: 55%;">Project Name: <span class="text-value">' . $inspection['community'] . '</span></td><td style="width: 45%;">Builder Name: <span class="text-value">' . $builder.'</td></tr>';
            $html_body .= '<tr><td>Street: <span class="text-value">' . $inspection['address'] . '</span></td><td>Permit Office: </td></tr>';
            $html_body .= '<tr><td>City, State, Zip: <span class="text-value">' . $inspection['city'] . '</span></td><td>Permit Number: </td></tr>';
            $html_body .= '<tr><td>Design Location: <span class="text-value">' . $inspection['design_location'] . '</span></td><td>Jurisidiction: </td></tr>';
            $html_body .= '<tr><td>Cond. Floor Area: <span class="text-value">' . $this->show_decimal($inspection['area'], 0) . ' sq.ft</span></td><td>Cond. Volume: <span class="text-value">' . $this->show_decimal($inspection['volume'], 0) . ' cu.ft</span></td></tr>';
            $html_body .= '</table>';
            $html_body .= '</div>';

            $cfm25_system_1 = $this->cfm25($inspection_id, 1);
            $cfm25_system_2 = $this->cfm25($inspection_id, 2);
            $cfm25_system_3 = $this->cfm25($inspection_id, 3);
            $cfm25_system_4 = $this->cfm25($inspection_id, 4);

            $html_body .= '<div class="row" style="height: 12px;"></div>';
            $html_body .= '<div class="row" style="padding: 0 16px;">';
            $html_body .= '<table class="width-full"><tr>';

            $c = floatval($inspection['flow']) / pow(floatval($inspection['house_pressure']), 0.65);
            $cfm50 = $c * 12.7154;
            $ela = $cfm50 * 0.055;
            $eqla = $cfm50 * 0.1032;
            $ach = floatval($inspection['ach50']) / 25.36;
            $sla = $ela * 0.00694 / floatval($inspection['area']);

            $html_body .= '<td class="width-70-percent" style="vertical-align:top; line-height: 14px;">';
            $html_body .= '<div class="row" style="padding: 0 16px;">';
            $html_body .= '<h2 class="part-title" style="margin-bottom: 20px;">Envelope Leakage Test Results</h2>';
            $html_body .= '<br>';
            $html_body .= '<h3 style="font-size: 13.92px; font-weight: 100; margin: 4px 0px 4px 0px;">Regression Data: </h3>';
            $html_body .= '<h3 style="font-size: 13.92px; font-weight: 100; margin: 4px 0px 4px 0px; padding-left: 8px;">'
                            . '&nbsp; C: <span class="text-underline">&nbsp;&nbsp;&nbsp; ' . $this->show_decimal($c, 5) . ' &nbsp;&nbsp;&nbsp;</span> '
                            . '&nbsp; n: <span class="text-underline">&nbsp;&nbsp;&nbsp; 0.65 &nbsp;&nbsp;&nbsp;</span> '
                            . '&nbsp; R: <span class="text-underline">&nbsp;&nbsp;&nbsp; N/A &nbsp;&nbsp;&nbsp;</span>'
                        . '</h3>';
            $html_body .= '<br>';
            $html_body .= '<h3 style="font-size: 13.92px; font-weight: 100; margin: 4px 0 8px;">Single or Multi Point Test Data</h3>';
            $html_body .= '<table class="test-result" style="width: 200px;">';
            $html_body .= '<tr><td class="result-line">&nbsp;</td><td class="house-pressure">HOUSE PRESSURE</td><td class="flow">FLOW</td></tr>';
            $html_body .= '<tr><td class="result-line">&nbsp;</td><td class="house-pressure">' . $this->show_decimal($inspection['house_pressure'], 1) . '</td><td class="flow">' . $this->show_decimal($inspection['flow'], 1) . '</td></tr>';
            $html_body .= '</table>';
            $html_body .= '</div>';
            $html_body .= '</td>';

            $html_body .= '<td class="width-30-percent" style="vertical-align:top;">';
            $html_body .= '<h2 class="part-title">Leakage Characteristics</h2>';
            $html_body .= '<table class="leakage-characteristics" style="padding-left: 20px;">';
            $html_body .= '<tr><td>&nbsp;</td><td style="width:100px;">&nbsp;</td></tr>';
            $html_body .= '<tr><td>CFM(50): &nbsp;</td><td class="border-bottom text-center"> ' . $this->show_decimal($cfm50, 0) . '  </td></tr>';
            $html_body .= '<tr><td>ELA: &nbsp;</td><td class="border-bottom text-center"> ' . $this->show_decimal($ela, 4) . '  </td></tr>';
            $html_body .= '<tr><td>EqLA: &nbsp;</td><td class="border-bottom text-center"> ' . $this->show_decimal($eqla, 4) . '  </td></tr>';
            $html_body .= '<tr><td>ACH: &nbsp;</td><td class="border-bottom text-center"> ' . $this->show_decimal($ach, 4) . '  </td></tr>';
            $html_body .= '<tr><td>ACH(50): &nbsp;</td><td class="border-bottom text-center">' . $this->show_decimal($inspection['ach50'], 2) . '</td></tr>';
            $html_body .= '<tr><td>SLA: &nbsp;</td><td class="border-bottom text-center"> ' . $this->show_decimal($sla, 4) . '  </td></tr>';
            $html_body .= '</table>';
            $html_body .= '</td>';

            $html_body .= '</tr></table>';
            $html_body .= '</div>';


            $html_body .= '<div class="row" style="height: 24px;"></div>';
            $html_body .= '<div class="row" style="padding: 0 12px;">';
            $html_body .= '<h3 style="font-size: 12.24px; font-weight: 100; margin: 4px 0 2px; line-height: 14px;">';
            $html_body .= '<span class="font-bold">R402.4.1.2.Testing.</span>&nbsp;';
            $html_body .= 'The building or dwelling unit shall be tested and verified as having an air leakage rate of not exceeding 5 air changes per hour in Climate Zones 1 and 2, 3 air changes per hour in Climate Zones 3 through 8. Testing shall be conducted with a blower door at a pressure of 0.2 inches w.g. (50 Pascals). Where required by the code official, testing shall be conducted by an approved third party. A written report of the results of the test shall be signed by the party conducting the test and provided to the code official. Testing shall be performed at any time after creation of all penetrations of the building thermal envelope.';
            $html_body .= '</h3>';
            $html_body .= '<div style="padding: 0 24px;">';
            $html_body .= '<h3 style="font-size: 12.0px; font-weight: 100; margin: 2px 0;">During testing:</h3>';
            $html_body .= '<ul style="font-size: 12.0px; line-height: 13px; margin: 4px 0; list-style-type:decimal; ">';
            $html_body .= '<li>Exterior windows and doors, fireplace and stove doors shall be closed, but not sealed, beyond the intended weatherstripping or other infiltration control measures;</li>';
            $html_body .= '<li>Dampers including exhaust, intake, makeup air, backdraft and flue dampers shall be closed, but not sealed beyond intended infiltration control measures;</li>';
            $html_body .= '<li>Interior doors, if installed at the time of the test, shall be open;</li>';
            $html_body .= '<li>Exterior doors for continuous ventilation systems and heat recovery ventilators shall be closed and sealed;</li>';
            $html_body .= '<li>Heating and cooling systems, if installed at the time of the test, shall be turned off; and</li>';
            $html_body .= '<li>Supply and return registers, if installed at the time of the test, shall be fully open.</li>';
            $html_body .= '</ul>';
            $html_body .= '</div>';
            $html_body .= '</div>';


            $html_body .= '<div class="row" style="height: 16px;"></div>';
            $html_body .= '<div class="row"><table class="width-full footer"><tr>';

            $html_body .= '<td class="width-40-percent footer-padding">';
            $html_body .= '<br><br>';
            $html_body .= '<h2 class="footer-description">I hereby certify that the above envelope leakage performance results demonstrate compliance with Florida Energy Code requirements in accordance with Section R402.4.1.2.</h2>';

            $html_body .= '<table class="">'
                        . '<tr>'
                            . '<td style="vertical-align:bottom;"><span class="footer-value">SIGNATURE: </span></td>'
                            . '<td style="padding-left: 8px; vertical-align:bottom; border-bottom: 1px solid #000;"><img class="img-responsive" src="' . $this->image_url_change(base_url()) . 'resource/upload/signature.png" alt="" style="height: 42px;"></td>'
                        . '</tr>'
                        . '<tr>'
                            . '<td style="vertical-align:bottom;"><span class="footer-value">RESNET ID: 9377172</span></td>'
                        . '</tr>'
                        . '</table>';
            $html_body .= '<br>';
            $html_body .= '<h3 class="footer-value">PRINTED NAME: <span class="text-underline">&nbsp;&nbsp;&nbsp;' . 'Tom Karras - Rater ID 791' . '&nbsp;&nbsp;&nbsp;</span></h3>';
            $html_body .= '<br>';
            $html_body .= '<h3 class="footer-value">DATE: <span class="text-underline">&nbsp;&nbsp;&nbsp;' . $inspection['end_date'] . '&nbsp;&nbsp;&nbsp;</span></h3>';
            $html_body .= '</td>';

            $html_body .= '<td class="width-60-percent footer-small-padding">';
            $html_body .= '<table class="width-full">'
                        . '<tr>'
                            . '<td style="vertical-align: middle;"><h2 class="footer-description" style="padding-right: 16px; padding-top: 8px;">Where required by the code official, testing shall be conducted by an approved third party. A written report of the results of the test shall be signed by the third party conducting the test and provided to the code official.</h2></td>'
                            . '<td style="padding-left:12px;"><img src="' . $this->image_url_change(base_url()) . 'resource/upload/wci.png" alt="" style="width: 164px; margin-top: 4px;"></td>'
                        . '</tr>'
                        . '</table>';
            $html_body .= '<br>';
            $html_body .= '<h3 class="footer-value">BUILDING OFFICIAL: _______________</h3>';
            $html_body .= '<h3 class="footer-value">DATE: ____________________________</h3>';
            $html_body .= '</td>';

            $html_body .= '</tr></table></div>';

            $html_body .= '<div class="row" style="height: 16px;"></div>';
        }

        $html_body .= '</body>';

        $html .= $html_head . $html_body;
        $html .= '</html>';

        return $html;
    }

    private function cfm25($inspection_id, $no) {
        $unit = $this->utility_model->get('ins_unit', array('inspection_id'=>$inspection_id, 'no'=>$no));
        if ($unit) {
            $result = 0;
            if ($unit['supply']!="") {
                $result += floatval($unit['supply']);
            }
            if ($unit['return']!="") {
                $result += floatval($unit['return']);
            }
            return $result/2;
        } else {
            return 0;
        }
    }

    private function show_decimal($value, $decimal, $is_integer=false) {
        return number_format(floatval($value), intval($decimal), ".", "‚");
    }

    private function image_url_change($url) {
        $url = str_replace("https://", "http://", $url);
        return $url;
    }


    private function get_report_data__for_payable_payroll($inspector, $period, $start_date, $end_date, $is_array=false) {
        $reports = array();

        $table = " select * from ins_inspector_payroll a ";

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

        $sql = $table;
        if ($filter_sql!="") {
            $sql .= " where " . $filter_sql;
        }

        $table_styles = " .data-table {width: 100%; border: 1px solid #000; } "
                . " .data-table thead th { padding: 7px 5px; } "
                . " .table-bordered { border-collapse: collapse; }"
                . " .table-bordered thead th, .table-bordered tbody td { border: 1px solid #000; }  "
                . " .table-bordered tbody td { font-size: 85%; padding: 4px 4px; }  ";

        $html_styles = "<style type='text/css'> " . $table_styles . " "
                . " .text-right{text-align:right;} "
                . " .text-center{text-align:center;} "
                . " .row{float:left;width:100%;margin-bottom:20px;} "
                . " span.label{} .label-danger{color:#d9534f;} .label-success{color:#5cb85c;} .label-warning{color:#f0ad4e;} "
                . " .col-50-percent{float:left;width:50%;} "
                . ".total-inspection span , .total-checklist span { font-size: 84%; } .total-checklist span.total-1 { color: #02B302; } .total-checklist span.total-2 { color: #e33737; } .total-checklist span.total-3 { color: #11B4CE; }  .total-inspection span.total-1 { color: #02B302; } .total-inspection span.total-2 { color: #e89701; } .total-inspection span.total-3 { color: #e33737; }"
                . "</style>";

        $html_header = "<html><head><meta charset='utf-8'/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/><title>Report</title>" . $html_styles . "</head><body>";
        $html_body = "";

        $html_body .= "<div class='row text-center'>" . '<img alt="" src="' . $this->image_url_change(LOGO_PATH) . '" style="margin: auto; max-width: 400px;">' . "</div>";

        $cls = "text-center";
        $title = "Processed Inspector's Payments Report";

        $html_body .= "<h1 class='" . $cls . "'>" . $title . "</h1>";

        $sub_title = "";
        $cls = "text-right";
        if ($inspector!="") {
            $u = $this->utility_model->get("ins_user", array('id'=>$inspector));
            if ($u) {
                $sub_title = "Inspector: " . $u['first_name'] . " " . $u['last_name'];
            }
        }

        if ($sub_title!="") {
            $html_body .= "<h5 class='" . $cls . "'>" . $sub_title . "</h5>";
        }

        if ($start_date!="" && $end_date!="") {
            $html_body .= "<h6 class='" . $cls . "'>" . $start_date . " ~ " . $end_date . "</h6>";
        }

        $html_body .= '<div class="row">';

        $html_body .= '<table class="data-table table-bordered">';
        $html_body .= '' .
                '<thead>' .
                    '<tr>' .
                        '<th>Inspector Name</th>' .
                        '<th>Email</th>' .
                        '<th>Phone Number</th>' .
                        '<th>Address</th>' .
                        '<th>Pay Period</th>' .
                        '<th>Check Amount</th>' .
                        '<th>Check Number</th>' .
                        '<th>Transaction Date</th>' .
                    '</tr>' .
                '</thead>' .
                '';

        $html_body .= '<tbody>';

        $sql .= " order by a.updated_at asc ";

        if (true) {
            array_push($reports, array(
                'name'=>"Inspection Name",
                'email'=>'Email',
                'phone'=>'Phone Number',
                'address'=>'Address',
                'period'=>'Pay Period',
                'amount'=>'Check Amount',
                'number'=>'Check Number',
                'transaction_date'=>'Transaction Date',
            ));
        }

        $data = $this->datatable_model->get_content($sql);
        if ($data && is_array($data)) {
            foreach ($data as $row) {
                $html_body .= '<tr>';

                $html_body .= '<td class="">' . $row['inspector_name']  . '</td>';
                $html_body .= '<td class="text-center">' . $row['inspector_email']  . '</td>';
                $html_body .= '<td class="text-center">' . $row['inspector_phone'] . '</td>';
                $html_body .= '<td class="">' . $row['inspector_address']  . '</td>';
                $html_body .= '<td class="text-center">' . $row['start_date'] . " ~ " . $row['end_date'] . '</td>';
                $html_body .= '<td class="text-center">$' . number_format($row['check_amount'], 2)  . '</td>';
                $html_body .= '<td class="text-center">' . $row['check_number']  . '</td>';
                $html_body .= '<td class="text-center">$' . $row['transaction_date']  . '</td>';

//                $cls = "";
//                $field_value = "";
//                if ($row['status'] == 1) {
//                    $cls = "label-success";
//                    $field_value = "PAID";
//                } else {
//                    $cls = "label-warning";
//                    $field_value = "PENDING";
//                }
//
//                $html_body .= '<td class="text-center"><span class="label '. $cls  . '">' . $field_value  . '</span></td>';
                $html_body .= '</tr>';

                array_push($reports, array(
                    'name'=>$row['inspector_name'],
                    'email'=>$row['inspector_email'],
                    'phone'=>$row['inspector_phone'],
                    'address'=>$row['inspector_address'],
                    'period'=> $row['start_date'] . " ~ " . $row['end_date'],
                    'amount'=> number_format($row['check_amount'], 2),
                    'number'=>$row['check_number'],
                    'transaction_date'=>$row['transaction_date'],
//                    'status'=>$row['status']==1 ? "PAID" : "PENDING",
                ));
            }
        }

        $html_body .= '</tbody>';
        $html_body .= '</table>';

        $html_body .= '</div>';


        $html_footer = "</body></html>";

        $html = $html_header . $html_body . $html_footer;

        if ($is_array) {
            return $reports;
        } else {
            return $html;
        }
    }

    private function get_report_data__for_payable_re_inspection($region, $community, $start_date, $end_date, $status, $type, $epo_status, $is_array=false) {
        $reports = array();

        $table = " ins_region r, ins_code c1, ins_code c2, "
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
                . "       AND c2.code=a.result_code AND p.inspection_count>1 "
                . " ";

        $common_sql = "";

        if ($start_date!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.start_date>='$start_date' ";
        }

        if ($end_date!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.end_date<='$end_date' ";
        }

        if ($region!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.region='$region' ";
        }

        if ($community!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.community='$community' ";
        }

        if ($status!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.result_code='$status' ";
        }

        if ($type!="") {
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

        $table_styles = " .data-table {width: 100%; border: 1px solid #000; } "
                . " .data-table thead th { padding: 7px 5px; } "
                . " .table-bordered { border-collapse: collapse; }"
                . " .table-bordered thead th, .table-bordered tbody td { border: 1px solid #000; }  "
                . " .table-bordered tbody td { font-size: 85%; padding: 4px 4px; }  ";

        $html_styles = "<style type='text/css'> " . $table_styles . " "
                . " .text-right{text-align:right;} "
                . " .text-center{text-align:center;} "
                . " .row{float:left;width:100%;margin-bottom:20px;} "
                . " span.label{} .label-danger{color:#d9534f;} .label-success{color:#5cb85c;} .label-warning{color:#f0ad4e;} "
                . " .col-50-percent{float:left;width:50%;} "
                . ".total-inspection span , .total-checklist span { font-size: 84%; } .total-checklist span.total-1 { color: #02B302; } .total-checklist span.total-2 { color: #e33737; } .total-checklist span.total-3 { color: #11B4CE; }  .total-inspection span.total-1 { color: #02B302; } .total-inspection span.total-2 { color: #e89701; } .total-inspection span.total-3 { color: #e33737; }"
                . "</style>";

        $html_header = "<html><head><meta charset='utf-8'/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/><title>Report</title>" . $html_styles . "</head><body>";
        $html_body = "";

        $html_body .= "<div class='row text-center'>" . '<img alt="" src="' . $this->image_url_change(LOGO_PATH) . '" style="margin: auto; max-width: 400px;">' . "</div>";

        $cls = "text-center";
        $title = "Re-Inspections EPO Report";
        if ($type == '1') {
            $title = "Drainage Plane " . $title;
        }
        if ($type == "2") {
            $title = "Lath " . $title;
        }

        $html_body .= "<h1 class='" . $cls . "'>" . $title . "</h1>";

        $sub_title = "";
        if ($region!="") {
            $r = $this->utility_model->get('ins_region', array('id'=>$region));
            if ($r) {
                $sub_title .= $r['region'];
            }
        }

        if ($community!="") {
            $c = $this->utility_model->get('ins_community', array('community_id'=>$community));
            if ($c) {
                if ($sub_title!="") {
                    $sub_title .= ", ";
                }

                $sub_title .= $c['community_name'];
            }
        }

        $cls = "text-right";

        if ($sub_title!="") {
            $html_body .= "<h5 class='" . $cls . "'>" . $sub_title . "</h5>";
        }

        if ($start_date!="" && $end_date!="") {
            $html_body .= "<h6 class='" . $cls . "'>" . $start_date . " ~ " . $end_date . "</h6>";
        }

        $html_body .= '<div class="row">';

        $html_body .= '<table class="data-table table-bordered">';
        $html_body .= '' .
                '<thead>' .
                    '<tr>' .
                        '<th>Type</th>' .
                        '<th>Region</th>' .
                        '<th>Community</th>' .
                        '<th>Job Number</th>' .
                        '<th>Address</th>' .
                        '<th>Field Manager</th>' .
                        '<th>Date</th>' .
                        '<th>Result</th>' .
                        '<th>Status</th>' .
                        '<th>EPO Number</th>' .
                        '<th>EPO Status</th>' .
                        '<th>Invoice Number</th>' .
                    '</tr>' .
                '</thead>' .
                '';

        $html_body .= '<tbody>';

        $sql = " select  a.*, "
                . " c1.name as inspection_type, c2.name as result_name, "
                . " r.region as region_name, tt.community_name, "

                . " p.inspection_count, q.epo_number as requested_epo_number, pay.invoice_number as pay_invoice_number, "
                . " a.epo_number as inspection_epo_number, a.epo_status as inspection_epo_status, a.invoice_number as inspection_invoice_number, "

                . " u.first_name, u.last_name, '' as additional "
                . " from " . $table . " ";
        if ($common_sql!="") {
            $sql .= " and " . $common_sql;
        }

        $sql .= " order by a.start_date desc ";

        array_push($reports, array(
            'inspection_type'=>"Inspection Type",
            'region'=>'Region',
            'community'=>'Community',
            'job_number'=>'Job Number',
            'address'=>'Address',
            'field_manager'=>'Field Manager',
            'date'=>'Date',
            'result'=>'Result',
            'status'=>'Status',
            'epo_number'=>'EPO Number',
            'epo_status'=>'EPO Status',
            'invoice_number'=>'Invoice Number',
        ));

        $data = $this->datatable_model->get_content($sql);
        if ($data && is_array($data)) {
            foreach ($data as $row) {
                $html_body .= '<tr>';

                $field_manager = "";
                if (isset($row['first_name']) && isset($row['last_name']) && $row['first_name']!="" && $row['last_name']!="") {
                    $field_manager = $row['first_name'] . $row['last_name'];
                }

                // replace community name.  2016/11/3
                $community_name = ""; // $row['community'];
                if (isset($row['community_name']) && $row['community_name']!="") {
                    $community_name = $row['community_name'];
                }

                $html_body .= '<td class="text-center">' . $row['inspection_type']  . '</td>';
                $html_body .= '<td class="text-center">' . $row['region_name']  . '</td>';
                $html_body .= '<td class="text-center">' . $community_name  . '</td>';
                $html_body .= '<td class="text-center">' . $row['job_number']  . '</td>';
                $html_body .= '<td>' . $row['address']  . '</td>';
                $html_body .= '<td class="text-center">' . $field_manager  . '</td>';

                $html_body .= '<td class="text-center">' . $row['start_date']  . '</td>';

                $cls = "";
                if ($row['result_code'] == '1')
                    $cls = "label-success";
                if ($row['result_code'] == '2')
                    $cls = "label-warning";
                if ($row['result_code'] == '3')
                    $cls = "label-danger";

                $html_body .= '<td class="text-center"><span class="label '. $cls  . '">' . $row['result_name']  . '</span></td>';
                $html_body .= '<td class="text-center"><span class="label '. ($row['house_ready']=="1" ? "label-success" : "label-warning") . '">' . ($row['house_ready']=="1" ? "House Ready" : "House Not Ready")  . '</span></td>';

                $epo_number = " ";
                $epo_status = $row['inspection_epo_status'];
                $invoice_number = " ";

                if (isset($row['inspection_epo_number']) && $row['inspection_epo_number']!="") {
                    $epo_number = $row['inspection_epo_number'];
                } else if (isset($row['requested_epo_number']) && $row['requested_epo_number']!=0) {
                    $epo_number = $row['requested_epo_number'];
                }

                if (isset($row['pay_invoice_number']) && $row['pay_invoice_number']!="") {
                    $invoice_number = $row['pay_invoice_number'];
                } else {
                    $invoice_number = $row['inspection_invoice_number'];
                }


                $html_body .= '<td class="text-center">' . $epo_number . '</td>';
                $html_body .= '<td class="text-center">' . $this->get_epo_status_title($epo_status) . '</td>';
                $html_body .= '<td class="text-center">' . $invoice_number . '</td>';

                $html_body .= '</tr>';

                array_push($reports, array(
                    'inspection_type'=>$row['inspection_type'],
                    'region'=>$row['region_name'],
                    'community'=>$community_name,
                    'job_number'=>$row['job_number'],
                    'address'=>$row['address'],
                    'field_manager'=>$field_manager,
                    'date'=>$row['start_date'],
                    'result'=>$row['result_name'],
                    'status'=>$row['house_ready']=="1" ? "House Ready" : "House Not Ready",
                    'epo_number'=>$epo_number,
                    'epo_status'=>$this->get_epo_status_title($epo_status),
                    'invoice_number'=>$invoice_number,
                ));
            }
        }

        $html_body .= '</tbody>';
        $html_body .= '</table>';

        $html_body .= '</div>';


        $html_footer = "</body></html>";

        $html = $html_header . $html_body . $html_footer;

        if ($is_array) {
            return $reports;
        } else {
            return $html;
        }
    }

    private function get_report_data__for_payable_pending_inspection($region, $community, $start_date, $end_date, $status, $type, $epo_status, $payment_status, $re_inspection, $is_array=false) {
        $reports = array();

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

        $table_styles = " .data-table {width: 100%; border: 1px solid #000; } "
                . " .data-table thead th { padding: 7px 5px; } "
                . " .table-bordered { border-collapse: collapse; }"
                . " .table-bordered thead th, .table-bordered tbody td { border: 1px solid #000; }  "
                . " .table-bordered tbody td { font-size: 85%; padding: 4px 4px; }  ";

        $html_styles = "<style type='text/css'> " . $table_styles . " "
                . " .text-right{text-align:right;} "
                . " .text-center{text-align:center;} "
                . " .row{float:left;width:100%;margin-bottom:20px;} "
                . " span.label{} .label-danger{color:#d9534f;} .label-success{color:#5cb85c;} .label-warning{color:#f0ad4e;} "
                . " .col-50-percent{float:left;width:50%;} "
                . ".total-inspection span , .total-checklist span { font-size: 84%; } .total-checklist span.total-1 { color: #02B302; } .total-checklist span.total-2 { color: #e33737; } .total-checklist span.total-3 { color: #11B4CE; }  .total-inspection span.total-1 { color: #02B302; } .total-inspection span.total-2 { color: #e89701; } .total-inspection span.total-3 { color: #e33737; }"
                . "</style>";

        $html_header = "<html><head><meta charset='utf-8'/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/><title>Report</title>" . $html_styles . "</head><body>";
        $html_body = "";

        $html_body .= "<div class='row text-center'>" . '<img alt="" src="' . $this->image_url_change(LOGO_PATH) . '" style="margin: auto; max-width: 400px;">' . "</div>";

        $cls = "text-center";
        $title = "Inspections Pending Payment Report";
        if ($type == '1') {
            $title = "Drainage Plane " . $title;
        }
        if ($type == "2") {
            $title = "Lath " . $title;
        }

        $html_body .= "<h1 class='" . $cls . "'>" . $title . "</h1>";

        $sub_title = "";
        if ($region!="") {
            $r = $this->utility_model->get('ins_region', array('id'=>$region));
            if ($r) {
                $sub_title .= $r['region'];
            }
        }

        if ($community!="") {
            $c = $this->utility_model->get('ins_community', array('community_id'=>$community));
            if ($c) {
                if ($sub_title!="") {
                    $sub_title .= ", ";
                }

                $sub_title .= $c['community_name'];
            }
        }

        $cls = "text-right";

        if ($sub_title!="") {
            $html_body .= "<h5 class='" . $cls . "'>" . $sub_title . "</h5>";
        }

        if ($start_date!="" && $end_date!="") {
            $html_body .= "<h6 class='" . $cls . "'>" . $start_date . " ~ " . $end_date . "</h6>";
        }

        $html_body .= '<div class="row">';

        $html_body .= '<table class="data-table table-bordered">';
        $html_body .= '' .
                '<thead>' .
                    '<tr>' .
                        '<th>Type</th>' .
                        '<th>Region</th>' .
                        '<th>Community</th>' .
                        '<th>Job Number</th>' .
                        '<th>Address</th>' .
                        '<th>Field Manager</th>' .
                        '<th>Date</th>' .
                        '<th>Result</th>' .
                        '<th>EPO Number</th>' .
                        '<th>EPO Status</th>' .
                        '<th>Payment Status</th>' .
                    '</tr>' .
                '</thead>' .
                '';

        $html_body .= '<tbody>';

        $sql .= " order by a.start_date desc ";

        array_push($reports, array(
            'inspection_type'=>"Inspection Type",
            'region'=>'Region',
            'community'=>'Community',
            'job_number'=>'Job Number',
            'address'=>'Address',
            'field_manager'=>'Field Manager',
            'date'=>'Date',
            'result'=>'Result',
            'epo_number'=>'EPO Number',
            'epo_status'=>'EPO Status',
            'payment_status'=>'Paymenet Status',
        ));

        $data = $this->datatable_model->get_content($sql);
        if ($data && is_array($data)) {
            foreach ($data as $row) {
                $html_body .= '<tr>';

                $field_manager = "";
                if (isset($row['first_name']) && isset($row['last_name']) && $row['first_name']!="" && $row['last_name']!="") {
                    $field_manager = $row['first_name'] . $row['last_name'];
                }

                // replace community name.  2016/11/3
                $community_name = ""; // $row['community'];
                if (isset($row['community_name']) && $row['community_name']!="") {
                    $community_name = $row['community_name'];
                }

                $html_body .= '<td class="text-center">' . $row['inspection_type']  . '</td>';
                $html_body .= '<td class="text-center">' . $row['region_name']  . '</td>';
                $html_body .= '<td class="text-center">' . $community_name  . '</td>';
                $html_body .= '<td class="text-center">' . $row['job_number']  . '</td>';
                $html_body .= '<td>' . $row['address']  . '</td>';
                $html_body .= '<td class="text-center">' . $field_manager  . '</td>';

                $html_body .= '<td class="text-center">' . $row['start_date']  . '</td>';

                $cls = "";
                if ($row['result_code'] == '1')
                    $cls = "label-success";
                if ($row['result_code'] == '2')
                    $cls = "label-warning";
                if ($row['result_code'] == '3')
                    $cls = "label-danger";

                $html_body .= '<td class="text-center"><span class="label '. $cls  . '">' . $row['result_name']  . '</span></td>';

                $epo_number = " ";
                $epo_status = $row['epo_status'];
                $payment_status = false;

//                if (isset($row['inspection_epo_number']) && $row['inspection_epo_number']!="") {
//                    $epo_number = $row['inspection_epo_number'];
//                }

                if (isset($row['invoice_id']) && $row['invoice_id']!="") {
                    $payment_status = true;
                }



                $html_body .= '<td class="text-center">' . $epo_number . '</td>';
                $html_body .= '<td class="text-center">' . $this->get_epo_status_title($epo_status) . '</td>';
                $html_body .= '<td class="text-center"><span class="label ' . ( $payment_status===true ? "label-success" : "label-warning" ) . '">' . ( $payment_status===true ? "PAID" : "PENDING" ) . '</span></td>';

                $html_body .= '</tr>';

                array_push($reports, array(
                    'inspection_type'=>$row['inspection_type'],
                    'region'=>$row['region_name'],
                    'community'=>$community_name,
                    'job_number'=>$row['job_number'],
                    'address'=>$row['address'],
                    'field_manager'=>$field_manager,
                    'date'=>$row['start_date'],
                    'result'=>$row['result_name'],
                    'epo_number'=>$epo_number,
                    'epo_status'=>$this->get_epo_status_title($epo_status),
                    'payment_status'=>$payment_status===true ? "PAID" : "PENDING",
                ));
            }
        }

        $html_body .= '</tbody>';
        $html_body .= '</table>';

        $html_body .= '</div>';


        $html_footer = "</body></html>";

        $html = $html_header . $html_body . $html_footer;

        if ($is_array) {
            return $reports;
        }
        else {
            return $html;
        }
    }



    private function get_report_data__for_requested_inspection($start_date, $end_date, $status, $type, $is_array=false) {
        $reports = array();

        $table = " ins_code c1, ins_inspection_requested a "
               . " left join ins_community c on c.community_id=substr(a.job_number,1,4)"
               . " left join ins_region r on c.region=r.id "
               . " left join ins_admin m on a.manager_id=m.id "
               . " left join ins_user u on a.inspector_id=u.id "
               . " where c1.kind='ins' and c1.code=a.category ";  // and ( a.status=0 or a.status=1 )

        if ($this->session->userdata('permission')==2) {
            $table .= " and a.manager_id='" . $this->session->userdata('user_id') . "' ";
        } else if ($this->session->userdata('permission')==0) {
            $table .= " and a.inspector_id='" . $this->session->userdata('user_id') . "' ";
        }

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

        if ($type!==false && $type!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.category='$type' ";
        }

        if ($status!==false && $status!="") {
            if ($common_sql!="") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.status='$status' ";
        }

        $sql = " select  a.id, a.category, a.reinspection, a.epo_number, a.job_number, a.requested_at, a.assigned_at, a.completed_at, a.manager_id, a.inspector_id, "
                . " a.time_stamp, a.ip_address, a.community_name, a.lot, a.address, a.status, a.area, a.volume, a.qn, a.city as city_duct, "
                . " '' as additional, "
                . " m.first_name, m.last_name,"
                . " concat(u.first_name, ' ', u.last_name) as inspector_name, "
                . " c1.name as category_name, c.community_id, c.region, r.region as region_name, c.city "
                . " from " . $table . " ";

        if ($common_sql!="") {
            $sql .= " and " . $common_sql;
        }

//        $count_sql = " select count(*) from ( " . $sql . " ) ttt ";
//        $total = $this->datatable_model->get_count($count_sql);
//
//        $count_text = "<h4 class='total-inspection'>Total: " . $total . "";
//
//        $count_sql = " SELECT c.name AS result_name, t.result_code, t.tnt "
//                . " FROM ins_code c, ( select a.result_code, count(*) as tnt from ( $sql ) a group by a.result_code ) t "
//                . " WHERE c.kind='rst' AND c.code=t.result_code ORDER BY c.code ";
//
//        $tnt = $this->utility_model->get_list__by_sql($count_sql);
//        if ($tnt && is_array($tnt)) {
//            foreach ($tnt as $row) {
//                if ($count_text!="") {
//                    $count_text .= ", ";
//                }
//
//                $count_text .= '<span class="total-' . $row['result_code'] . '">';
//                $count_text .= $row['result_name'] . ": " . $row['tnt'];
//                if ($total!=0) {
//                    $tnt = intval($row['tnt']);
//                    $count_text .= "(" . round($tnt*1.0/$total * 100, 2) . "%)";
//                }
//                $count_text .= "</span>";
//            }
//        }
//
//        $count_text .= "</h4>";

        $table_styles = " .data-table {width: 100%; border: 1px solid #000; } "
                . " .data-table thead th { padding: 7px 5px; } "
                . " .table-bordered { border-collapse: collapse; }"
                . " .table-bordered thead th, .table-bordered tbody td { border: 1px solid #000; }  "
                . " .table-bordered tbody td { font-size: 85%; padding: 4px 4px; }  ";

        $html_styles = "<style type='text/css'> " . $table_styles . " "
                . " .text-right{text-align:right;} "
                . " .text-center{text-align:center;} "
                . " .row{float:left;width:100%;margin-bottom:20px;} "
                . " span.label{} .label-danger{color:#d9534f;} .label-success{color:#5cb85c;} .label-warning{color:#f0ad4e;} "
                . " .col-50-percent{float:left;width:50%;} "
                . ".total-inspection span , .total-checklist span { font-size: 84%; } .total-checklist span.total-1 { color: #02B302; } .total-checklist span.total-2 { color: #e33737; } .total-checklist span.total-3 { color: #11B4CE; }  .total-inspection span.total-1 { color: #02B302; } .total-inspection span.total-2 { color: #e89701; } .total-inspection span.total-3 { color: #e33737; }"
                . "</style>";

        $html_header = "<html><head><meta charset='utf-8'/><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/><title>Report</title>" . $html_styles . "</head><body>";
        $html_body = "";

        $html_body .= "<div class='row text-center'>" . '<img alt="" src="' . $this->image_url_change(LOGO_PATH) . '" style="margin: auto; max-width: 400px;">' . "</div>";

        $cls = "text-center";
        $title = "Requested Inspection Report";
        if ($type == '1') {
            $title = "Drainage Plane " . $title;
        }
        if ($type == "2") {
            $title = "Lath " . $title;
        }
        if ($type == "3") {
            $title = "WCI Duct Leakage " . $title;
        }

        $html_body .= "<h1 class='" . $cls . "'>" . $title . "</h1>";

        $cls = "text-right";
        if ($start_date!="" && $end_date!="") {
            $html_body .= "<h6 class='" . $cls . "'>" . $start_date . " ~ " . $end_date . "</h6>";
        }

//        if ($count_text!="") {
//            $html_body .=  $count_text ;
//        }

        $html_body .= '<div class="row">';

        $html_body .= '<table class="data-table table-bordered">';
        $html_body .= '' .
                '<thead>' .
                    '<tr>' .
                        '<th>Inspection Date</th>' .
                        '<th>Community</th>' .
                        '<th>Job Number</th>' .
                        '<th>Address</th>' .
                        '<th>City</th>' .
                        '<th>Field Manager</th>' .
                        '<th>Inspection Type</th>' .
                        '<th>Requested Time</th>' .
                        '<th>Inspector</th>' .
                        '<th>Status</th>' .
                    '</tr>' .
                '</thead>' .
                '';

        $html_body .= '<tbody>';

        $sql .= " order by a.requested_at desc ";

        array_push($reports, array(
            'inspection_date'=>'Inspection Date',
            'community'=>'Community',
            'job_number'=>'Job Number',
            'address'=>'Address',
            'city'=>'City',
            'field_manager'=>'Field Manager',
            'inspection_type'=>"Inspection Type",
            'requested_time'=>'Requested Time',
            'inspector'=>'Inspector',
            'status'=>'Status',
        ));

        $data = $this->datatable_model->get_content($sql);
        if ($data && is_array($data)) {
            foreach ($data as $row) {
                $html_body .= '<tr>';

                $html_body .= '<td>' . $row['requested_at']  . '</td>';
                $html_body .= '<td>' . $row['community_name']  . '</td>';
                $html_body .= '<td>' . $row['job_number']  . '</td>';
                $html_body .= '<td>' . $row['address']  . '</td>';

                $city = "";
                if ($row['category']==3) {
                    if (isset($row['city_duct']) && $row['city_duct']!="") {
                        $city = $row['city_duct'];
                    }
                } else {
                    if (isset($row['city']) && $row['city']!="") {
                        $city = $row['city'];
                    }
                }

                $html_body .= '<td>' . $city  . '</td>';

                $field_manager = "";
                if (isset($row['first_name']) && isset($row['last_name']) && $row['first_name']!="" && $row['last_name']!="") {
                    $field_manager = $row['first_name'] . $row['last_name'];
                }

                $html_body .= '<td class="text-center">' . $field_manager  . '</td>';
                $html_body .= '<td>' . $row['category_name']  . '</td>';

                $requested_time = date('Y-m-d H:i:s', strtotime($row['time_stamp']));
                $html_body .= '<td>' . $requested_time . '</td>';

                $html_body .= '<td>' . $row['inspector_name']  . '</td>';


//                // replace community name.  2016/11/3
//                $community_name = ""; // $row['community'];
//                if (isset($row['community_name']) && $row['community_name']!="") {
//                    $community_name = $row['community_name'];
//                }
//                $html_body .= '<td class="text-center">' . $community_name  . '</td>';

                $cls = "";
                $status_name = "";
                if ($row['status'] == 2) {
                    $cls = "label-success";
                    $status_name = "Completed";
                } else if ($row['status'] == 1) {
                    $cls = "label-warning";
                    $status_name = "Assigned";
                } else {
                    $cls = "label-default";
                    $status_name = "Unassigned";
                }

                $html_body .= '<td class="text-center"><span class="label '. $cls  . '">' . $status_name . '</span></td>';

                $html_body .= '</tr>';

                array_push($reports, array(
                    'inspection_date'=>$row['requested_at'],
                    'community'=>$row['community_name'],
                    'job_number'=>$row['job_number'],
                    'address'=>$row['address'],
                    'city'=>$city,
                    'field_manager'=>$field_manager,
                    'inspection_type'=>$row['category_name'],
                    'requested_time'=>$requested_time,
                    'inspector'=>$row['inspector_name'],
                    'status'=>$status_name
                ));
            }
        }


        $html_body .= '</tbody>';
        $html_body .= '</table>';

        $html_body .= '</div>';


        $html_footer = "</body></html>";

        $html = $html_header . $html_body . $html_footer;

        if ($is_array) {
            return $reports;
        } else {
            return $html;
        }
    }


    private function get_epo_status_title($status) {
        if ($status==0) {
            return "To Request";
        }
        if ($status==1) {
            return "Requested";
        }
        if ($status==2) {
            return "Received";
        }
        if ($status==3) {
            return "Not Needed";
        }

        return "";
    }


    public function test($id) {
        echo $this->get_report_html__for_envelop_leakage($id);

        echo "<br>";
        echo "End";
        echo "<br>";
    }

}
