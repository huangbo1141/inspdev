<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Community extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('utility_model');
        $this->load->model('datatable_model');
    }

    public function index() {
        if (!$this->session->userdata('user_id')) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        redirect(base_url() . "community/home.html");
    }


    public function home() {
        if (!$this->session->userdata('user_id')) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $page_data['page_name'] = 'community';
        $this->load->view('community_list', $page_data);
    }

    public function load(){
        $cols = array("a.community_id", "a.community_name", "a.city", "r.region","b.name");
        $table = " ins_community a "
                . " left join ins_region r on r.id=a.region left join ins_builder b on b.id=a.builder "
                . " ";

        $region = "";
        if ($this->session->userdata('permission')==1) {

        } else if ($this->session->userdata('permission')==0) {

        } else if ($this->session->userdata('permission')==4) {
            $region = "1";
            $table .= " where a.region in ( select region from ins_admin_region where manager_id='" . $this->session->userdata('user_id') . "' ) ";

        } else {
            $region = "1";
            $table .= " where a.region in ( select region from ins_admin_region where manager_id='" . $this->session->userdata('user_id') . "' ) ";

//            $region = $this->session->userdata('user_region');
//            if ($region=='0') {
//                $region = "";
//            } else {
//                $table .= " where a.region='$region' ";
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

        $sql = " select  a.*, r.region as region_name, b.name as builder_name, '' as additional from " . $table . "  ";
        $searchSQL = "";
        $globalSearch = " ( "
                . " a.community_id like '%" . $searchTerm . "%' or "
                . " a.community_name like '%" . $searchTerm . "%' or "
                . " a.city like '%" . $searchTerm . "%' or  "
                . " r.region like '%" . $searchTerm . "%'  "
                . " ) ";

        if ($searchTerm && strlen($searchTerm)>0){
            $searchSQL .= ( $region=="" ? " where " : " and " ) . $globalSearch;
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

        if (!$this->session->userdata('user_id')) {

        } else {
            $result["recordsTotal"] = $total;
            $result["recordsFiltered"] = $totalAfterFilter;
            $result["data"] = $data;
        }

        print_r(json_encode($result));
    }

    public function edit() {
        if (!$this->session->userdata('user_id')) {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $community_id = $this->input->get_post('community_id');
        $kind = $this->input->get_post('kind');

        $page_data['page_name'] = 'community';
        if ($kind=='add') {
            $page_data['page_title'] = "Add Community";
            $page_data['community'] = array('community_id'=>'', 'community_name'=>'', 'city'=>'', 'region'=>'');
        } else {
            $page_data['page_title'] = "Update Community";
            $page_data['community'] = $this->utility_model->get('ins_community', array('id'=>$community_id) );
        }

        if ($this->session->userdata('permission')!='1') {
            $page_data['page_title'] = "View Community";
        }

        $page_data['community_id'] = $community_id;
        $page_data['kind'] = $kind;
        $page_data['region'] = $this->utility_model->get_list('ins_region', array());
        $page_data['builder'] = $this->utility_model->get_list('ins_builder', array());

        $this->load->view('community_edit', $page_data);
    }

    public function update() {
        $res = array('err_code'=>1, 'err_msg'=>'Failed!');
        if ($this->session->userdata('user_id')) {
            if ($this->utility_model->has_permission($this->session->userdata('permission'), 3)) {
                $community_id = $this->input->get_post('community_id');
                $kind = $this->input->get_post('kind');

                if ($kind=='add' && $community_id===false) {
                    $community_id = "";
                }

                if ($community_id!==false && $kind!==false){
                    $t = mdate('%Y%m%d%H%i%s', time());
                    $data = array('updated_at'=>$t);

                    $ret = 0;
                    if ($kind=='add' || $kind=='edit') {
                        $id = $this->input->get_post('community_idv');
                        $name = $this->input->get_post('community_name');
                        $city = $this->input->get_post('city');
                        $region = $this->input->get_post('region');
                        $builder = $this->input->get_post('builder');

                        if ($id!==false && $name!==false && $city!==false && $region!==false && $builder!==false) {
                            $ret = 1;

                            $data['community_id'] = $id;
                            $data['community_name'] = $name;
                            $data['city'] = $city;
                            $data['region'] = $region;
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
                        $ttt = $this->utility_model->get('ins_community', array('community_id'=>$id));
                        if ($ttt) {
                            if ($ttt['id']!=$community_id) {
                                $ret = false;
                            }
                        }

                        if ($ret) {
                            if ($kind=='add') {
                                $data['created_at'] = $t;

                                if ($this->utility_model->insert('ins_community', $data)) {
                                    $res['err_code'] = 0;
                                    $res['err_msg'] = "Successfully Added!";
                                }

                            } else {
                                if ($this->utility_model->update('ins_community', $data, array('id'=>$community_id))) {
                                    $res['err_code'] = 0;
                                    $res['err_msg'] = "Successfully Updated!";
                                }
                            }
                        } else {
                            $res['err_msg'] = "Already Exist Community ID!";
                        }
                    } else {
                        $res['err_msg'] = "You haven't permission";
                    }
                }
            }
        }

        print_r(json_encode($res));
    }

    public function delete() {
        $res = array('err_code'=>1);
        if ($this->session->userdata('user_id')) {
            if ($this->utility_model->has_permission($this->session->userdata('permission'), 3)) {
                $community_id = $this->input->get_post('community_id');

                if ($community_id!==false){
                    if ($this->utility_model->delete('ins_community', array('id'=>$community_id))) {
                        $res['err_code'] = 0;
                    }
                } else {
                }
            }
        }

        print_r(json_encode($res));
    }


}
