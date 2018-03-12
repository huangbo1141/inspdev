<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Statistics extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('utility_model');
        $this->load->model('datatable_model');
    }

    public function inspection() {
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission') != '1') {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $page_data['page_name'] = 'statistics_inspection';
        $page_data['region'] = $this->utility_model->get_list('ins_region', array());
        $this->load->view('statistics_inspection', $page_data);
    }

    public function get_community() {
        $res = array('err_code' => 1);
        if ($this->session->userdata('user_id') && $this->session->userdata('permission') == 1) {
            $region = $this->input->get_post('region');

            if ($region === false || $region == "") {
                $res['community'] = $this->utility_model->get_list('ins_community', array());
                $res['err_code'] = 0;
            } else {
                if (gettype($region) == 'string') {
                    $res['community'] = $this->utility_model->get_list('ins_community', array('region' => $region));
                    $res['err_code'] = 0;
                } else if (gettype($region) == 'array') {
                    $list_community = array();
                    foreach ($region as $id) {
                        $list_temp = $this->utility_model->get_list('ins_community', array('region' => $id));
                        $list_community = array_merge($list_community, $list_temp);
                    }
                    $res['community'] = $list_community;
                    $res['err_code'] = 0;
                }
            }
        }

        print_r(json_encode($res));
    }

    public function load_inspection() {
        $cols = array("a.type", "a.region", "a.community", "a.job_number", "a.address", "u.first_name", "a.overall_comments", "a.start_date", "a.result_code", "a.house_ready");
        $table = " ins_region r, ins_code c1, ins_code c2, ins_inspection a "
                . " left join ins_admin u on a.field_manager=u.id and u.kind=2 "
                . " left join ins_community tt on tt.community_id=a.community "
                . " where a.region=r.id and c1.kind='ins' and c1.code=a.type and c2.kind='rst' and c2.code=a.result_code  ";

        $result = array();

        $amount = 10;
        $start = 0;
        $col = 7;

        $dir = "asc";

        $region = $this->input->get_post('region');
        $community = $this->input->get_post('community');
        $start_date = $this->input->get_post('start_date');
        $end_date = $this->input->get_post('end_date');
        $status = $this->input->get_post('status');
        $type = $this->input->get_post('type');

        $common_sql = "";

        if ($start_date !== false && $start_date != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.start_date>='$start_date' ";
        }

        if ($end_date !== false && $end_date != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.end_date<='$end_date' ";
        }

        if ($region !== false && $region != "") {
            $param = $region;
            if (gettype($param) == 'string') {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }
                $common_sql .= " a.region='$param' ";
            } else if (gettype($param) == 'array') {
                $ids_str = "";
                foreach ($param as $id) {
                    $ids_str = $ids_str . $id . ",";
                }
                if (strlen($ids_str) > 0) {
                    $ids_str = substr($ids_str, 0, strlen($ids_str) - 1);
                    $ids_str = "(" . $ids_str . ")";
                }
                if (strlen($ids_str) > 0) {
                    if ($common_sql != "") {
                        $common_sql .= " and ";
                    }
                    $common_sql .= " a.region in $ids_str ";
                }
            }
        }

        if ($community !== false && $community != "") {
            $param = $community;
            if (gettype($param) == 'string') {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }
                $common_sql .= " a.community='$param' ";
            } else if (gettype($param) == 'array') {
                $ids_str = "";
                foreach ($param as $id) {
                    $ids_str = $ids_str . $id . ",";
                }
                if (strlen($ids_str) > 0) {
                    $ids_str = substr($ids_str, 0, strlen($ids_str) - 1);
                    $ids_str = "(" . $ids_str . ")";
                }
                if (strlen($ids_str) > 0) {
                    if ($common_sql != "") {
                        $common_sql .= " and ";
                    }
                    $common_sql .= " a.community in $ids_str ";
                }
            }
        }

        if ($status !== false && $status != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.result_code='$status' ";
        }

        if ($type !== false && $type != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.type='$type' ";
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
                if ($key == 'column')
                    $sCol = $value;
                if ($key == 'dir')
                    $sdir = $value;
            }
        }

        $searchTerm = "";
        $search = $this->input->get_post("search");
        foreach ($search as $key => $value) {
            if ($key == 'value')
                $searchTerm = $value;
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
            if ($col < 0 || $col > 8) {
                $col = 7;
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

        $sql = " select count(*) from " . $table . " ";
        if ($common_sql != "") {
            $sql .= " and " . $common_sql;
        }

        $total = $this->datatable_model->get_count($sql);
        $totalAfterFilter = $total;

        $sql = " select  a.*, "
                . " c1.name as inspection_type, c2.name as result_name, "
                . " r.region as region_name, tt.community_name, "
                . " u.first_name, u.last_name, '' as additional "
                . " from " . $table . " ";

        $searchSQL = "";

        $globalSearch = " ( "
                . " replace(a.job_number,'-','') like '%" . str_replace('-', '', $searchTerm) . "%' or "
                . " a.start_date like '%" . $searchTerm . "%' or  "
                . " a.community like '%" . $searchTerm . "%' or  "
                . " a.address like '%" . $searchTerm . "%' or  "
                . " r.region like '%" . $searchTerm . "%' or  "
                . " u.first_name like '%" . $searchTerm . "%' or  "
                . " u.last_name like '%" . $searchTerm . "%' or  "
                . " c1.name like '%" . $searchTerm . "%' or  "
                . " c2.name like '%" . $searchTerm . "%' "
                . " ) ";

        if ($searchTerm && strlen($searchTerm) > 0) {
            $searchSQL .= " and " . $globalSearch;
        }

        $sql .= $searchSQL;

        if ($common_sql != "") {
            $sql .= " and " . $common_sql;
        }

        $sql .= " order by " . $colName . " " . $dir . " ";
        $sql .= " limit " . $start . ", " . $amount . " ";

        $data = $this->datatable_model->get_content($sql);

        $sql = " select count(*) from " . $table . " ";

        if (strlen($searchSQL) > 0) {
            $sql .= $searchSQL;

            if ($common_sql != "") {
                $sql .= " and " . $common_sql;
            }

            $totalAfterFilter = $this->datatable_model->get_count($sql);
        }

        if (!$this->session->userdata('user_id') || $this->session->userdata('permission') != '1') {
            
        } else {
            $result["recordsTotal"] = $total;
            $result["recordsFiltered"] = $totalAfterFilter;
            $result["data"] = $data;
        }

        print_r(json_encode($result));
    }

    public function re_inspection() {
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission') != '1') {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $page_data['page_name'] = 'statistics_re_inspection';
        $page_data['region'] = $this->utility_model->get_list('ins_region', array());
        $this->load->view('statistics_re_inspection', $page_data);
    }

    public function load_re_inspection() {
        $cols = array("a.type", "a.region", "a.community", "a.job_number", "a.address", "u.first_name", "a.overall_comments", "a.start_date", "a.epo_number", "g.inspection_count", "a.result_code");
        $table = " ins_region r, ins_code c1, ins_code c2,  "
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
                . " AND c2.code=a.result_code  AND g.inspection_count>1 "
                . " ";

        $result = array();

        $amount = 10;
        $start = 0;
        $col = 9;

        $dir = "desc";

        $region = $this->input->get_post('region');
        $community = $this->input->get_post('community');
        $start_date = $this->input->get_post('start_date');
        $end_date = $this->input->get_post('end_date');
        $status = $this->input->get_post('status');
        $type = $this->input->get_post('type');

        $common_sql = "";

        if ($start_date !== false && $start_date != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.start_date>='$start_date' ";
        }

        if ($end_date !== false && $end_date != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.end_date<='$end_date' ";
        }

        if ($region !== false && $region != "") {
            $param = $region;
            if (gettype($param) == 'string') {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }
                $common_sql .= " a.region='$param' ";
            } else if (gettype($param) == 'array') {
                $ids_str = "";
                foreach ($param as $id) {
                    $ids_str = $ids_str . $id . ",";
                }
                if (strlen($ids_str) > 0) {
                    $ids_str = substr($ids_str, 0, strlen($ids_str) - 1);
                    $ids_str = "(" . $ids_str . ")";
                }
                if (strlen($ids_str) > 0) {
                    if ($common_sql != "") {
                        $common_sql .= " and ";
                    }
                    $common_sql .= " a.region in $ids_str ";
                }
            }
        }

        if ($community !== false && $community != "") {
            $param = $community;
            if (gettype($param) == 'string') {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }
                $common_sql .= " a.community='$param' ";
            } else if (gettype($param) == 'array') {
                $ids_str = "";
                foreach ($param as $id) {
                    $ids_str = $ids_str . $id . ",";
                }
                if (strlen($ids_str) > 0) {
                    $ids_str = substr($ids_str, 0, strlen($ids_str) - 1);
                    $ids_str = "(" . $ids_str . ")";
                }
                if (strlen($ids_str) > 0) {
                    if ($common_sql != "") {
                        $common_sql .= " and ";
                    }
                    $common_sql .= " a.community in $ids_str ";
                }
            }
        }

        if ($status !== false && $status != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.result_code='$status' ";
        }

        if ($type !== false && $type != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.type='$type' ";
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
                if ($key == 'column')
                    $sCol = $value;
                if ($key == 'dir')
                    $sdir = $value;
            }
        }

        $searchTerm = "";
        $search = $this->input->get_post("search");
        foreach ($search as $key => $value) {
            if ($key == 'value')
                $searchTerm = $value;
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
            if ($col < 0 || $col > 10) {
                $col = 9;
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

        $sql = " select count(*) from " . $table . " ";
        if ($common_sql != "") {
            $sql .= " and " . $common_sql;
        }

        $total = $this->datatable_model->get_count($sql);
        $totalAfterFilter = $total;

        $sql = " select  a.*, "
                . " (g.inspection_count-1) as inspection_count, q.epo_number as requested_epo_number, "
                . " c1.name as inspection_type, c2.name as result_name, "
                . " r.region as region_name, tt.community_name, "
                . " u.first_name, u.last_name, '' as additional "
                . " from " . $table . " ";

        $searchSQL = "";

        $globalSearch = " ( "
                . " replace(a.job_number,'-','') like '%" . str_replace('-', '', $searchTerm) . "%' or "
                . " a.start_date like '%" . $searchTerm . "%' or  "
                . " a.community like '%" . $searchTerm . "%' or  "
                . " a.address like '%" . $searchTerm . "%' or  "
                . " r.region like '%" . $searchTerm . "%' or  "
                . " u.first_name like '%" . $searchTerm . "%' or  "
                . " u.last_name like '%" . $searchTerm . "%' or  "
                . " c1.name like '%" . $searchTerm . "%' or  "
                . " c2.name like '%" . $searchTerm . "%' "
                . " ) ";

        if ($searchTerm && strlen($searchTerm) > 0) {
            $searchSQL .= " and " . $globalSearch;
        }

        $sql .= $searchSQL;

        if ($common_sql != "") {
            $sql .= " and " . $common_sql;
        }

        $sql .= " order by " . $colName . " " . $dir . " ";
        $sql .= " limit " . $start . ", " . $amount . " ";

        $data = $this->datatable_model->get_content($sql);
        $result["sql"] = $sql;
        $sql = " select count(*) from " . $table . " ";
        if (strlen($searchSQL) > 0) {
            $sql .= $searchSQL;

            if ($common_sql != "") {
                $sql .= " and " . $common_sql;
            }

            $totalAfterFilter = $this->datatable_model->get_count($sql);
        }

        if (!$this->session->userdata('user_id') || $this->session->userdata('permission') != '1') {
            
        } else {
            $result["recordsTotal"] = $total;
            $result["recordsFiltered"] = $totalAfterFilter;
            $result["data"] = $data;
        }

        print_r(json_encode($result));
    }

    public function checklist() {
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission') != '1') {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $page_data['page_name'] = 'statistics_checklist';
        $page_data['region'] = $this->utility_model->get_list('ins_region', array());
        $this->load->view('statistics_checklist', $page_data);
    }

    public function load_checklist() {
        $cols = array("a.type", "a.region", "a.community", "a.start_date", "loc.name", "ch.no", "ch.status");
        $table = ""
                . " ins_region r, ins_code c1, ins_code c2, ins_code c3, ins_location loc, ins_checklist ch, ins_inspection a"
                . " left join ins_admin u on a.field_manager=u.id and u.kind=2 "
                . " where a.region=r.id and c1.kind='ins' and c1.code=a.type and c2.kind='sts' and c2.code=ch.status "
                . " and loc.inspection_id=a.id and ch.inspection_id=a.id and ch.location_id=loc.id and (ch.status=1 or ch.status=2 or ch.status=3) and c3.value=a.type and (c3.kind='drg' or c3.kind='lth') and c3.code=ch.no ";

        $result = array();

        $amount = 10;
        $start = 0;
        $col = 3;

        $dir = "asc";

        $region = $this->input->get_post('region');
        $community = $this->input->get_post('community');
        $start_date = $this->input->get_post('start_date');
        $end_date = $this->input->get_post('end_date');
        $status = $this->input->get_post('status');
        $type = $this->input->get_post('type');

        $common_sql = "";

        if ($start_date !== false && $start_date != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.start_date>='$start_date' ";
        }

        if ($end_date !== false && $end_date != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.end_date<='$end_date' ";
        }

        if ($region !== false && $region != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.region='$region' ";
        }

        if ($community !== false && $community != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.community='$community' ";
        }

        if ($status !== false && $status != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " ch.status='$status' ";
        }

        if ($type !== false && $type != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.type='$type' ";
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
                if ($key == 'column')
                    $sCol = $value;
                if ($key == 'dir')
                    $sdir = $value;
            }
        }

        $searchTerm = "";
        $search = $this->input->get_post("search");
        foreach ($search as $key => $value) {
            if ($key == 'value')
                $searchTerm = $value;
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
                $col = 3;
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

        $sql = " select count(*) from " . $table . " ";
        if ($common_sql != "") {
            $sql .= " and " . $common_sql;
        }

        $total = $this->datatable_model->get_count($sql);
        $totalAfterFilter = $total;

        $sql = " select  a.*, "
                . " c1.name as inspection_type, c2.name as status_name, c3.name as item_name, ch.no as item_no, ch.status as status_code, "
                . " r.region as region_name, loc.name as location_name, "
                . " u.first_name, u.last_name, '' as additional "
                . " from " . $table . " "
                . "";

        $searchSQL = "";

        $globalSearch = " ( "
                . " replace(a.job_number,'-','') like '%" . str_replace('-', '', $searchTerm) . "%' or "
                . " a.start_date like '%" . $searchTerm . "%' or  "
                . " a.community like '%" . $searchTerm . "%' or  "
                . " a.address like '%" . $searchTerm . "%' or  "
                . " r.region like '%" . $searchTerm . "%' or  "
                . " u.first_name like '%" . $searchTerm . "%' or  "
                . " u.last_name like '%" . $searchTerm . "%' or  "
                . " c1.name like '%" . $searchTerm . "%' or  "
                . " c2.name like '%" . $searchTerm . "%' "
                . " ) ";

        if ($searchTerm && strlen($searchTerm) > 0) {
            $searchSQL .= " and " . $globalSearch;
        }

        $sql .= $searchSQL;

        if ($common_sql != "") {
            $sql .= " and " . $common_sql;
        }

        $sql .= " order by " . $colName . " " . $dir . " ";
        $sql .= " limit " . $start . ", " . $amount . " ";
        $data = $this->datatable_model->get_content($sql);

        $sql = " select count(*) "
                . " from " . $table . " "
                . "";

        if (strlen($searchSQL) > 0) {
            $sql .= $searchSQL;

            if ($common_sql != "") {
                $sql .= " and " . $common_sql;
            }

            $totalAfterFilter = $this->datatable_model->get_count($sql);
        }

        if (!$this->session->userdata('user_id') || $this->session->userdata('permission') != '1') {
            
        } else {
            $result["recordsTotal"] = $total;
            $result["recordsFiltered"] = $totalAfterFilter;
            $result["data"] = $data;
        }

        print_r(json_encode($result));
    }

    public function get_count() {
        $res = array('code' => 1);

        $kind = $this->input->get_post('kind');

        if ($kind == 'checklist') {
            $region = $this->input->get_post('region');
            $community = $this->input->get_post('community');
            $start_date = $this->input->get_post('start_date');
            $end_date = $this->input->get_post('end_date');
            $status = $this->input->get_post('status');
            $type = $this->input->get_post('type');

            $common_sql = "";

            if ($start_date !== false && $start_date != "") {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.start_date>='$start_date' ";
            }

            if ($end_date !== false && $end_date != "") {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.end_date<='$end_date' ";
            }

            if ($region !== false && $region != "") {
                $param = $region;
                if (gettype($param) == 'string') {
                    if ($common_sql != "") {
                        $common_sql .= " and ";
                    }
                    $common_sql .= " a.region='$param' ";
                } else if (gettype($param) == 'array') {
                    $ids_str = "";
                    foreach ($param as $id) {
                        $ids_str = $ids_str . $id . ",";
                    }
                    if (strlen($ids_str) > 0) {
                        $ids_str = substr($ids_str, 0, strlen($ids_str) - 1);
                        $ids_str = "(" . $ids_str . ")";
                    }
                    if (strlen($ids_str) > 0) {
                        if ($common_sql != "") {
                            $common_sql .= " and ";
                        }
                        $common_sql .= " a.region in $ids_str ";
                    }
                }
            }

            if ($community !== false && $community != "") {
                $param = $community;
                if (gettype($param) == 'string') {
                    if ($common_sql != "") {
                        $common_sql .= " and ";
                    }
                    $common_sql .= " a.community='$param' ";
                } else if (gettype($param) == 'array') {
                    $ids_str = "";
                    foreach ($param as $id) {
                        $ids_str = $ids_str . $id . ",";
                    }
                    if (strlen($ids_str) > 0) {
                        $ids_str = substr($ids_str, 0, strlen($ids_str) - 1);
                        $ids_str = "(" . $ids_str . ")";
                    }
                    if (strlen($ids_str) > 0) {
                        if ($common_sql != "") {
                            $common_sql .= " and ";
                        }
                        $common_sql .= " a.community in $ids_str ";
                    }
                }
            }

            if ($status !== false && $status != "") {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }

                $common_sql .= " ch.status='$status' ";
            }

            if ($type !== false && $type != "") {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.type='$type' ";
            }


            $sql = " select  a.*, "
                    . " c1.name as inspection_type, c2.name as status_name, c3.name as item_name, ch.no as item_no, ch.status as status_code, "
                    . " r.region as region_name, loc.name as location_name, "
                    . " u.first_name, u.last_name, '' as additional "
                    . " from ins_region r, ins_code c1, ins_code c2, ins_code c3, ins_location loc, ins_checklist ch, ins_inspection a "
                    . " left join ins_admin u on a.field_manager=u.id and u.kind=2 "
                    . " where a.region=r.id and c1.kind='ins' and c1.code=a.type and c2.kind='sts' and c2.code=ch.status "
                    . " and loc.inspection_id=a.id and ch.inspection_id=a.id and ch.location_id=loc.id and c3.value=a.type and (c3.kind='drg' or c3.kind='lth') and c3.code=ch.no ";

            if ($common_sql != "") {
                $sql .= " and " . $common_sql;
            }

            $count_sql = " select count(*) from ( " . $sql . " ) t ";
            $total = $this->datatable_model->get_count($count_sql);

            $sql .= " and (ch.status=1 or ch.status=2 or ch.status=3) ";

            $count_text = "<h4 class='total-checklist'>Total: " . $total . "";

            //        if ($status=="") {
            $count_sql = " SELECT c.name AS status_name, t.status_code, t.tnt "
                    . " FROM ins_code c, ( select a.status_code, count(*) as tnt from ( $sql ) a group by a.status_code ) t "
                    . " WHERE c.kind='sts' AND c.code=t.status_code ORDER BY c.code ";

            $tnt = $this->utility_model->get_list__by_sql($count_sql);
            if ($tnt && is_array($tnt)) {
                foreach ($tnt as $row) {
                    if ($count_text != "") {
                        $count_text .= ", ";
                    }

                    $count_text .= '<span class="total-' . $row['status_code'] . '">';
                    $count_text .= $row['status_name'] . ": " . $row['tnt'];
                    if ($total != 0) {
                        $tnt = intval($row['tnt']);
                        $count_text .= "(" . round($tnt * 1.0 / $total * 100, 2) . "%)";
                    }
                    $count_text .= '</span>';
                }
            }
            //        }
            $count_text .= "</h4>";
            $html_body = "";

            $top_sql = " select  a.*, "
                    . " c1.name as inspection_type, c2.name as status_name, c3.name as item_name, ch.no as item_no, ch.status as status_code, "
                    . " r.region as region_name, loc.name as location_name, "
                    . " u.first_name, u.last_name, '' as additional "
                    . " from ins_region r, ins_code c1, ins_code c2, ins_code c3, ins_location loc, ins_checklist ch, ins_inspection a left join ins_user u on a.field_manager=u.id "
                    . " where a.region=r.id and c1.kind='ins' and c1.code=a.type and c2.kind='sts' and c2.code=ch.status "
                    . " and loc.inspection_id=a.id and ch.inspection_id=a.id and ch.location_id=loc.id and c3.value=a.type and c3.code=ch.no and (ch.status=1 or ch.status=2 or ch.status=3) ";

            if ($common_sql != "") {
                $top_sql .= " and " . $common_sql;
            }

            $top_content = $this->get_top_item($top_sql, 'drg', 1);
            if ($top_content != "") {
                $html_body .= '<div class="row margin-bottom-10">';

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

            $top_content = $this->get_top_item($top_sql, 'drg', 2);
            if ($top_content != "") {
                $html_body .= '<div class="row margin-bottom-10">';

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

            $top_content = $this->get_top_item($top_sql, 'drg', 3);
            if ($top_content != "") {
                $html_body .= '<div class="row margin-bottom-10">';

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


            $top_content = $this->get_top_item($top_sql, 'lth', 1);
            if ($top_content != "") {
                $html_body .= '<div class="row margin-bottom-10">';

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

            $top_content = $this->get_top_item($top_sql, 'lth', 2);
            if ($top_content != "") {
                $html_body .= '<div class="row margin-bottom-10">';

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

            $top_content = $this->get_top_item($top_sql, 'lth', 3);
            if ($top_content != "") {
                $html_body .= '<div class="row margin-bottom-10">';

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



            $res['result'] = $count_text . $html_body;
            $res['code'] = 0;
        }

        if ($kind == 'inspection') {
            $region = $this->input->get_post('region');
            $community = $this->input->get_post('community');
            $start_date = $this->input->get_post('start_date');
            $end_date = $this->input->get_post('end_date');
            $status = $this->input->get_post('status');
            $type = $this->input->get_post('type');

            $common_sql = "";

            if ($start_date !== false && $start_date != "") {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.start_date>='$start_date' ";
            }

            if ($end_date !== false && $end_date != "") {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.end_date<='$end_date' ";
            }

            if ($region !== false && $region != "") {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.region='$region' ";
            }

            if ($community !== false && $community != "") {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.community='$community' ";
            }

            if ($status !== false && $status != "") {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.result_code='$status' ";
            }

            if ($type !== false && $type != "") {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.type='$type' ";
            }

            $sql = " select  a.*, "
                    . " c1.name as inspection_type, c2.name as result_name, "
                    . " r.region as region_name, "
                    . " u.first_name, u.last_name, '' as additional "
                    . " from ins_region r, ins_code c1, ins_code c2, ins_inspection a left join ins_admin u on a.field_manager=u.id and u.kind=2 "
                    . " where a.region=r.id and c1.kind='ins' and c1.code=a.type and c2.kind='rst' and c2.code=a.result_code  ";

            if ($common_sql != "") {
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
                    if ($count_text != "") {
                        $count_text .= ", ";
                    }

                    $count_text .= '<span class="total-' . $row['result_code'] . '">';
                    $count_text .= $row['result_name'] . ": " . $row['tnt'];
                    if ($total != 0) {
                        $tnt = intval($row['tnt']);
                        $count_text .= "(" . round($tnt * 1.0 / $total * 100, 2) . "%)";
                    }
                    $count_text .= "</span>";
                }
            }

            $count_sql = " select count(*) from ( " . $sql . " and a.house_ready=0 ) t ";
            $house_not_ready = $this->datatable_model->get_count($count_sql);
            if ($count_text != "") {
                $count_text .= ", ";
            }
            $count_text .= '<span class="lbl-house-not-ready">';
            $count_text .= "House Not Ready: " . $house_not_ready;
            $count_text .= "(" . round($house_not_ready * 1.0 / $total * 100, 2) . "%)";

            $count_text .= "</h4>";

            $res['result'] = $count_text;
            $res['code'] = 0;
        }

        if ($kind == 're_inspection') {
            $region = $this->input->get_post('region');
            $community = $this->input->get_post('community');
            $start_date = $this->input->get_post('start_date');
            $end_date = $this->input->get_post('end_date');
            $status = $this->input->get_post('status');
            $type = $this->input->get_post('type');

            $common_sql = "";

            if ($start_date !== false && $start_date != "") {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.start_date>='$start_date' ";
            }

            if ($end_date !== false && $end_date != "") {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.end_date<='$end_date' ";
            }

            if ($region !== false && $region != "") {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.region='$region' ";
            }

            if ($community !== false && $community != "") {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.community='$community' ";
            }

            if ($status !== false && $status != "") {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.result_code='$status' ";
            }

            if ($type !== false && $type != "") {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }

                $common_sql .= " a.type='$type' ";
            }

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

            $sql = " select  a.*, "
                    . " (g.inspection_count-1) as inspection_count, q.epo_number as requested_epo_number, "
                    . " c1.name as inspection_type, c2.name as result_name, "
                    . " r.region as region_name, tt.community_name, "
                    . " u.first_name, u.last_name, '' as additional "
                    . " from " . $table . " ";

            if ($common_sql != "") {
                $sql .= " and " . $common_sql;
            }

            $count_sql = " select count(*) from ( " . $sql . " ) t ";
            $total = $this->datatable_model->get_count($count_sql);
            $res['total_sql'] = $total;

            $count_text = "<h4 class='total-inspection'>Total: " . $total . "";

            $count_sql = " SELECT c.name AS result_name, t.result_code, t.tnt "
                    . " FROM ins_code c, ( select a.result_code, count(*) as tnt from ( $sql ) a group by a.result_code ) t "
                    . " WHERE c.kind='rst' AND c.code=t.result_code ORDER BY c.code ";

            $tnt = $this->utility_model->get_list__by_sql($count_sql);
            $res['tnt_sql'] = $count_sql;
            if ($tnt && is_array($tnt)) {
                foreach ($tnt as $row) {
                    if ($count_text != "") {
                        $count_text .= ", ";
                    }

                    $count_text .= '<span class="total-' . $row['result_code'] . '">';
                    $count_text .= $row['result_name'] . ": " . $row['tnt'];
                    if ($total != 0) {
                        $tnt = intval($row['tnt']);
                        $count_text .= "(" . round($tnt * 1.0 / $total * 100, 2) . "%)";
                    }
                    $count_text .= "</span>";
                }
            }

            $count_sql = " select count(*) from ( " . $sql . " and a.house_ready=0 ) t ";
            $house_not_ready = $this->datatable_model->get_count($count_sql);
            if ($count_text != "") {
                $count_text .= ", ";
            }
            $count_text .= '<span class="lbl-house-not-ready">';
            $count_text .= "House Not Ready: " . $house_not_ready;
            $count_text .= "(" . round($house_not_ready * 1.0 / $total * 100, 2) . "%)";

            $count_text .= "</h4>";

            $res['house_not_ready_sql'] = $count_sql;
            $res['sql'] = $sql;
            $res['result'] = $count_text;
            $res['code'] = 0;
        }

        print_r(json_encode($res));
    }

    private function get_top_item($sql, $inspection_type, $status) {
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
            }
        }

        return $result;
    }

    public function fieldmanager() {
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission') != '1') {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $page_data['page_name'] = 'statistics_fieldmanager';
        $page_data['region'] = $this->utility_model->get_list('ins_region', array());
        $this->load->view('statistics_fieldmanager', $page_data);
    }

    public function load_fieldmanager() {
        $cols = array("a.first_name");
        $table = " ins_admin a "
                . " left join ins_builder b on a.builder=b.id"
                . " where a.kind=2 "
                . " and a.builder = 1"  // only field manager
                . " and a.status = 1"   //  only activated user
                . " and a.testflag = 0" //  only non test user
                . " ";

        $result = array();

        $amount = 10;
        $start = 0;
        $col = 0;

        $dir = "asc";

        $region = $this->input->get_post('region');
        $community = $this->input->get_post('community');
        $start_date = $this->input->get_post('start_date');
        $end_date = $this->input->get_post('end_date');
        $type = $this->input->get_post('type');

        $common_sql = "";

        if ($start_date !== false && $start_date != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.start_date>='$start_date' ";
        }

        if ($end_date !== false && $end_date != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.end_date<='$end_date' ";
        }

        if ($region !== false && $region != "") {
            $param = $region;
            if (gettype($param) == 'string') {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }
                $common_sql .= " a.region='$param' ";
                $table .= " and a.id in ( select manager_id from ins_admin_region where region='$param' ) ";
            } else if (gettype($param) == 'array') {
                $ids_str = "";
                foreach ($param as $id) {
                    $ids_str = $ids_str . $id . ",";
                }
                if (strlen($ids_str) > 0) {
                    $ids_str = substr($ids_str, 0, strlen($ids_str) - 1);
                    $ids_str = "(" . $ids_str . ")";
                }
                if (strlen($ids_str) > 0) {
                    if ($common_sql != "") {
                        $common_sql .= " and ";
                    }
                    $common_sql .= " a.region in $ids_str ";
                    $table .= " and a.id in ( select manager_id from ins_admin_region where region in $ids_str ) ";
                }
            }
        }
        if ($community !== false && $community != "") {
            $param = $community;
            if (gettype($param) == 'string') {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }
                $common_sql .= " a.community='$param' ";
            } else if (gettype($param) == 'array') {
                $ids_str = "";
                foreach ($param as $id) {
                    $ids_str = $ids_str . $id . ",";
                }
                if (strlen($ids_str) > 0) {
                    $ids_str = substr($ids_str, 0, strlen($ids_str) - 1);
                    $ids_str = "(" . $ids_str . ")";
                }
                if (strlen($ids_str) > 0) {
                    if ($common_sql != "") {
                        $common_sql .= " and ";
                    }
                    $common_sql .= " a.community in $ids_str ";
                }
            }
        }

        if ($type !== false && $type != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.type='$type' ";
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
                if ($key == 'column')
                    $sCol = $value;
                if ($key == 'dir')
                    $sdir = $value;
            }
        }

        $searchTerm = "";
        $search = $this->input->get_post("search");
        foreach ($search as $key => $value) {
            if ($key == 'value')
                $searchTerm = $value;
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
            if ($col < 0 || $col > 1) {
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

        $sql = " select count(*) from " . $table . " ";
//        if ($common_sql!="") {
//            $sql .= " where " . $common_sql;
//        }

        $total = $this->datatable_model->get_count($sql);
        $totalAfterFilter = $total;

        $sql = " select  a.*, "
//                . " c1.name as inspection_type, c2.name as result_name, "
                . " '' as additional  "
                . " from " . $table . " "
                . " ";

        $searchSQL = "";

        $globalSearch = " ( "
//                . " replace(a.job_number,'-','') like '%" . str_replace('-','',$searchTerm) . "%' or "
//                . " a.start_date like '%" . $searchTerm . "%' or  "
//                . " a.community like '%" . $searchTerm . "%' or  "
//                . " a.address like '%" . $searchTerm . "%' or  "
//                . " r.region like '%" . $searchTerm . "%' or  "
                . " a.first_name like '%" . $searchTerm . "%' or  "
                . " a.last_name like '%" . $searchTerm . "%'  "
//                . " c1.name like '%" . $searchTerm . "%' or  "
//                . " c2.name like '%" . $searchTerm . "%' "
                . " ) ";

        if ($searchTerm && strlen($searchTerm) > 0) {
            $searchSQL .= " and " . $globalSearch;
        }

        $sql .= $searchSQL;

        $sql .= " order by " . $colName . " " . $dir . " ";
        $sql .= " limit " . $start . ", " . $amount . " ";
        $data = $this->datatable_model->get_content($sql);

        $sql = " select count(*) "
                . " from " . $table . " "
                . " ";

        if (strlen($searchSQL) > 0) {
            $sql .= $searchSQL;

//            if ($common_sql!="") {
//                $sql .= " and " . $common_sql;
//            }

            $totalAfterFilter = $this->datatable_model->get_count($sql);
        }

        if (!$this->session->userdata('user_id') || $this->session->userdata('permission') != '1') {
            
        } else {
            $table_data = array();

            foreach ($data as $row) {
                $region_name = "";
                $sql = " select r.region from ins_admin_region a, ins_region r where a.manager_id='" . $row['id'] . "' and a.region=r.id ";
                $regions = $this->utility_model->get_list__by_sql($sql);
                if ($regions) {
                    foreach ($regions as $rrr) {
                        if ($region_name != "") {
                            $region_name .= ", ";
                        }
                        $region_name .= $rrr['region'];
                    }
                }
                $row['region_name'] = $region_name;

                $sql = " select count(*) from ins_inspection a where a.field_manager='" . $row['id'] . "' ";
                if ($common_sql != "") {
                    $sql .= " and " . $common_sql;
                }

                $inspections = $this->datatable_model->get_count($sql);
                $row['inspections'] = $inspections;

                if ($inspections == 0) {
                    $row['not_ready'] = 0;
                    $row['pass'] = 0;
                    $row['pass_with_exception'] = 0;
                    $row['fail'] = 0;
                    $row['reinspection'] = 0;
                } else {
                    $sql = " select count(*) from ins_inspection a where a.field_manager='" . $row['id'] . "' and a.house_ready='0' ";
                    if ($common_sql != "") {
                        $sql .= " and " . $common_sql;
                    }

                    $not_ready = $this->datatable_model->get_count($sql);
                    $row['not_ready'] = round($not_ready * 1.0 / $inspections * 100.0, 2);


                    $sql = " select count(*) from ins_inspection a where a.field_manager='" . $row['id'] . "' and a.result_code=1 ";
                    if ($common_sql != "") {
                        $sql .= " and " . $common_sql;
                    }

                    $pass = $this->datatable_model->get_count($sql);
                    $row['pass'] = round($pass * 1.0 / $inspections * 100.0, 2);


                    $sql = " select count(*) from ins_inspection a where a.field_manager='" . $row['id'] . "' and a.result_code=2 ";
                    if ($common_sql != "") {
                        $sql .= " and " . $common_sql;
                    }

                    $pass_with_exception = $this->datatable_model->get_count($sql);
                    $row['pass_with_exception'] = round($pass_with_exception * 1.0 / $inspections * 100.0, 2);


                    $sql = " select count(*) from ins_inspection a where a.field_manager='" . $row['id'] . "' and a.result_code=3 ";
                    if ($common_sql != "") {
                        $sql .= " and " . $common_sql;
                    }

                    $fail = $this->datatable_model->get_count($sql);
                    $row['fail'] = round($fail * 1.0 / $inspections * 100.0, 2);


                    $sql = " select count(*) from ins_inspection a left join ins_inspection_requested r on a.requested_id=r.id where a.field_manager='" . $row['id'] . "' and r.reinspection=1 ";
                    if ($common_sql != "") {
                        $sql .= " and " . $common_sql;
                    }

                    $reinspection = $this->datatable_model->get_count($sql);
                    $row['reinspection'] = round($reinspection * 1.0 / $inspections * 100.0, 2);
                }

                array_push($table_data, $row);
            }

            $result["recordsTotal"] = $total;
            $result["recordsFiltered"] = $totalAfterFilter;
            $result["data"] = $table_data;
        }

        print_r(json_encode($result));
    }

    public function inspector() {
        if (!$this->session->userdata('user_id') || $this->session->userdata('permission') != '1') {
            redirect(base_url() . "user/login.html");
            exit(1);
        }

        $page_data['page_name'] = 'statistics_inspector';
        $page_data['region'] = $this->utility_model->get_list('ins_region', array());
        $this->load->view('statistics_inspector', $page_data);
    }

    public function load_inspector() {
        $cols = array("a.first_name");
        $table = " ins_user a ";

        $result = array();

        $amount = 10;
        $start = 0;
        $col = 0;

        $dir = "asc";

        $region = $this->input->get_post('region');
        $community = $this->input->get_post('community');
        $start_date = $this->input->get_post('start_date');
        $end_date = $this->input->get_post('end_date');
        $type = $this->input->get_post('type');

        $common_sql = "";

        if ($start_date !== false && $start_date != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.start_date>='$start_date' ";
        }

        if ($end_date !== false && $end_date != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.end_date<='$end_date' ";
        }

        if ($region !== false && $region != "") {
            $param = $region;
            if (gettype($param) == 'string') {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }
                $common_sql .= " a.region='$param' ";
            } else if (gettype($param) == 'array') {
                $ids_str = "";
                foreach ($param as $id) {
                    $ids_str = $ids_str . $id . ",";
                }
                if (strlen($ids_str) > 0) {
                    $ids_str = substr($ids_str, 0, strlen($ids_str) - 1);
                    $ids_str = "(" . $ids_str . ")";
                }
                if (strlen($ids_str) > 0) {
                    if ($common_sql != "") {
                        $common_sql .= " and ";
                    }
                    $common_sql .= " a.region in $ids_str ";
                }
            }
        }
        
        if ($community !== false && $community != "") {
            $param = $community;
            if (gettype($param) == 'string') {
                if ($common_sql != "") {
                    $common_sql .= " and ";
                }
                $common_sql .= " a.community='$param' ";
            } else if (gettype($param) == 'array') {
                $ids_str = "";
                foreach ($param as $id) {
                    $ids_str = $ids_str . $id . ",";
                }
                if (strlen($ids_str) > 0) {
                    $ids_str = substr($ids_str, 0, strlen($ids_str) - 1);
                    $ids_str = "(" . $ids_str . ")";
                }
                if (strlen($ids_str) > 0) {
                    if ($common_sql != "") {
                        $common_sql .= " and ";
                    }
                    $common_sql .= " a.community in $ids_str ";
                }
            }
        }

        if ($type !== false && $type != "") {
            if ($common_sql != "") {
                $common_sql .= " and ";
            }

            $common_sql .= " a.type='$type' ";
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
                if ($key == 'column')
                    $sCol = $value;
                if ($key == 'dir')
                    $sdir = $value;
            }
        }

        $searchTerm = "";
        $search = $this->input->get_post("search");
        foreach ($search as $key => $value) {
            if ($key == 'value')
                $searchTerm = $value;
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
            if ($col < 0 || $col > 0) {
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

        $sql = " select count(*) from " . $table . " ";

        $total = $this->datatable_model->get_count($sql);
        $totalAfterFilter = $total;

        $sql = " select  a.*, "
                . " '' as additional  "
                . " from " . $table . " "
                . " ";

        $searchSQL = "";

        $globalSearch = " ( "
//                . " replace(a.job_number,'-','') like '%" . str_replace('-','',$searchTerm) . "%' or "
//                . " a.start_date like '%" . $searchTerm . "%' or  "
//                . " a.community like '%" . $searchTerm . "%' or  "
//                . " a.address like '%" . $searchTerm . "%' or  "
                . " a.first_name like '%" . $searchTerm . "%' or  "
                . " a.last_name like '%" . $searchTerm . "%'  "
//                . " c1.name like '%" . $searchTerm . "%' or  "
//                . " c2.name like '%" . $searchTerm . "%' "
                . " ) ";

        if ($searchTerm && strlen($searchTerm) > 0) {
            $searchSQL .= " where " . $globalSearch;
        }

        $sql .= $searchSQL;

        $sql .= " order by " . $colName . " " . $dir . " ";
        $sql .= " limit " . $start . ", " . $amount . " ";
        $data = $this->datatable_model->get_content($sql);

        $sql = " select count(*) "
                . " from " . $table . " "
                . " ";

        if (strlen($searchSQL) > 0) {
            $sql .= $searchSQL;

            $totalAfterFilter = $this->datatable_model->get_count($sql);
        }

        if (!$this->session->userdata('user_id') || $this->session->userdata('permission') != '1') {
            
        } else {
            $table_data = array();

            foreach ($data as $row) {
                $sql = " select count(*) from ins_inspection a where a.user_id='" . $row['id'] . "' ";
                if ($common_sql != "") {
                    $sql .= " and " . $common_sql;
                }
                $inspections = $this->datatable_model->get_count($sql);
                $row['inspections'] = $inspections;
                $row['fee'] = number_format($row['fee'] * $inspections, 2);

                if ($inspections == 0) {
                    $row['not_ready'] = 0;
                    $row['pass'] = 0;
                    $row['pass_with_exception'] = 0;
                    $row['fail'] = 0;
                    $row['reinspection'] = 0;
                } else {
                    $sql = " select count(*) from ins_inspection a where a.user_id='" . $row['id'] . "' and a.house_ready='0' ";
                    if ($common_sql != "") {
                        $sql .= " and " . $common_sql;
                    }

                    $not_ready = $this->datatable_model->get_count($sql);
                    $row['not_ready'] = round($not_ready * 1.0 / $inspections * 100.0, 2);


                    $sql = " select count(*) from ins_inspection a where a.user_id='" . $row['id'] . "' and a.result_code=1 ";
                    if ($common_sql != "") {
                        $sql .= " and " . $common_sql;
                    }

                    $pass = $this->datatable_model->get_count($sql);
                    $row['pass'] = round($pass * 1.0 / $inspections * 100.0, 2);


                    $sql = " select count(*) from ins_inspection a where a.user_id='" . $row['id'] . "' and a.result_code=2 ";
                    if ($common_sql != "") {
                        $sql .= " and " . $common_sql;
                    }

                    $pass_with_exception = $this->datatable_model->get_count($sql);
                    $row['pass_with_exception'] = round($pass_with_exception * 1.0 / $inspections * 100.0, 2);


                    $sql = " select count(*) from ins_inspection a where a.user_id='" . $row['id'] . "' and a.result_code=3 ";
                    if ($common_sql != "") {
                        $sql .= " and " . $common_sql;
                    }

                    $fail = $this->datatable_model->get_count($sql);
                    $row['fail'] = round($fail * 1.0 / $inspections * 100.0, 2);


                    $sql = " select count(*) from ins_inspection a left join ins_inspection_requested r on a.requested_id=r.id where a.user_id='" . $row['id'] . "' and r.reinspection=1 ";
                    if ($common_sql != "") {
                        $sql .= " and " . $common_sql;
                    }

                    $reinspection = $this->datatable_model->get_count($sql);
                    $row['reinspection'] = round($reinspection * 1.0 / $inspections * 100.0, 2);
                }

                array_push($table_data, $row);
            }

            $result["recordsTotal"] = $total;
            $result["recordsFiltered"] = $totalAfterFilter;
            $result["data"] = $table_data;
        }

        print_r(json_encode($result));
    }

}
