<?php

if (!defined('_IMAP_PATH')) {
    define('_IMAP_PATH', dirname(preg_replace('/\\\\/', '/', __FILE__)) . '/');
}

require_once _IMAP_PATH . 'vendor/autoload.php';
require_once _IMAP_PATH . 'push/function.php';

use Ddeboer\Imap\Server;
use Ddeboer\Imap\SearchExpression;
use Ddeboer\Imap\Search\Email\To;
use Ddeboer\Imap\Search\Text\Body;
use Ddeboer\Imap\Search\Email\FromAddress;
use PHPHtmlParser\Dom;

class CheckWCi {

    public $db_host = "";
    public $db_name = "";
    public $db_username = "";
    public $db_password = "";
    public $last_req_date = "";
    public $ipaddr = "";
    public $mail_host = "";
    public $mail_user = "";
    public $mail_password = "";
    private $printmode = 0;
    private $printdetail = 0;
    private $fakeinsert = 0;
    private $index_jobinfo = "JOB INFORMATION";
    private $index_jobinfo_found = 0;
    private $index_coninfo = "CONTACT INFORMATION:";
    private $index_coninfo_found = 0;
    private $pdo = null;

    public function __construct() {
        
    }

    public function initdb() {
        $this->pdo = new PDO(
                'mysql:host=' . $this->db_host . ';dbname=' . $this->db_name, $this->db_username, $this->db_password, array()
        );

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->query('SET NAMES utf8mb4');
    }

    public function getLastRequestTime() {
        $sql = "select requested_at from  ins_inspection_requested order by requested_at desc limit 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $message = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($message) {
            $this->last_req_date = $message['requested_at'];
        }
    }

    public function addTables($input, $action = "add", $reschedule_notice = false) {
        $ret = array();
        $g_ins_admin = array("id", "kind", "email", "first_name", "last_name", "address", "password", "cell_phone", "other_phone", "status", "region", "builder", "allow_email", "created_at", "updated_at");
        $g_ins_building = array("job_number", "community", "address", "field_manager", "builder", "created_at", "updated_at", "unit_count");
        $g_ins_inspection_requested = array("id", "reinspection", "epo_number", "category", "job_number", "created_at", "requested_at", "assigned_at", "completed_at", "manager_id", "inspector_id", "time_stamp", "ip_address", "community_name", "lot", "address", "status", "city", "area", "volume", "qn", "wall_area", "ceiling_area", "design_location", "is_building_unit", "inspection_id", "document_person");
        $g_ins_community = array("id", "community_id", "community_name", "city", "region", "builder", "created_at", "updated_at");

        $sql = "";
        $ret['response'] = 400;

        try {
            $this->pdo->beginTransaction();
            $array_community = array();
            if ($action == "add") {
                if (isset($input['ins_admin'])) {
                    $params = $g_ins_admin;
                    $tdata = $input['ins_admin'];
                    $email = $tdata['email'];
                    $sql = "select * from ins_admin where email = '$email'";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute();

                    $message = $stmt->fetch(PDO::FETCH_ASSOC);
                    $builderid = 0;
                    if ($message) {
                        $builderid = $message['id'];
                    } else {
                        $insertData = extractAsArray($tdata, $params);
                        $sql = makeInsertDataSql($insertData, "ins_admin");
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute();

                        $id = $this->pdo->lastInsertId();
                        $builderid = $id;
                    }


                    // $updatedata = array();
                    // $updatedata['first_name'] = $builderid;
                    // $sql = makeUpdateSql($updatedata, "ins_admin", array("id" => $builderid));
                    // $stmt = $this->pdo->prepare($sql);
                    // $stmt->execute();

                    if (isset($input['ins_building'])) {
                        $params = $g_ins_building;
                        $tdata = $input['ins_building'];
                        $job_number = $tdata['job_number'];
                        $sql = "select * from ins_building where job_number = $job_number";
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute();

                        $message = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($message) {
                            $ret['duplicate_building'] = $job_number;
                        } else {
                            $insertData = extractAsArray($tdata, $params);
                            $sql = makeInsertDataSql($insertData, "ins_building");
                            $stmt = $this->pdo->prepare($sql);
                            $stmt->execute();

                            $id = $this->pdo->lastInsertId();
                            $buildingid = $id;
                            $ret['building_inserted'] = $job_number;
                        }
                        //$tdata['field_manager'] = $builderid . " WCI";
                    }

                    if (isset($input['ins_community'])) {
                        $params = $g_ins_community;
                        $tdata = $input['ins_community'];
                        $insertData = extractAsArray($tdata, $params);
                        // check if community is inserted or Not
                        $community_id = $insertData['community_id'];
                        $community_name = $insertData['community_name'];
                        $sql = "select * from ins_community where community_name = '" . $community_name . "'";
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute();
                        $message = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($message) {
                            //
                            // already inserted
                            $ret['duplicate_community_rowid'] = $message['id'];
                        } else {
                            $sql = makeInsertDataSql($insertData, "ins_community");
                            $stmt = $this->pdo->prepare($sql);
                            $stmt->execute();

                            $id = $this->pdo->lastInsertId();
                            $community_insertid = $id;
                            $ret['community_rowid'] = $community_insertid;
                            $insertData['id'] = $community_insertid;
                            $ret['community'] = $insertData;
                        }
                    }

                    if (isset($input['ins_req'])) {
                        $tdata = $input['ins_req'];
                        $job_number = $tdata['job_number'];
                        $sql = "select * from ins_inspection_requested where job_number = $job_number";
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute();

                        $message = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($message) {
                            $ret['duplicate_ins_req_job_number'] = $job_number;
                            if ($reschedule_notice) {
                                // update
                                $params = array("requested_at");
                                $insertData = extractAsArray($tdata, $params);
                                $sql = makeUpdateSql($insertData, "ins_inspection_requested", array("job_number" => $job_number));
                                $stmt = $this->pdo->prepare($sql);
                                $stmt->execute();

                                $ret['reschedule_notice_ins_req_job_number'] = $job_number;
                            }
                        } else {
                            $params = $g_ins_inspection_requested;
                            $tdata = $input['ins_req'];
                            $tdata['manager_id'] = $builderid;

                            $insertData = extractAsArray($tdata, $params);
                            $sql = makeInsertDataSql($insertData, "ins_inspection_requested");
                            $stmt = $this->pdo->prepare($sql);
                            $stmt->execute();

                            $id = $this->pdo->lastInsertId();
                            $reqid = $id;

                            $ret['job_number'] = $tdata['job_number'];
                        }
                    }
                }
                $ret['response'] = 200;
            }
            $this->pdo->commit();
        } catch (Exception $ex) {
            $this->pdo->rollBack();
            $ret['response'] = 102;
            $ret['sql'] = $sql;
            $ret['error'] = $ex;
            unset($ret['community']);
        }
        $ret['action'] = $action;
        return $ret;
    }

    public function resetValues() {
        $this->index_jobinfo_found = 0;
        $this->index_coninfo_found = 0;
    }

    public function start($count = 0, $ip = "") {
        $this->ipaddr = $ip;
        $ret = array();
        if (true) {
            $ret = $this->fetchMessages($count);
            $this->outputResult($ret, 1);
        }
        if (false) {
            $str = "Beach&nbsp;";
            $ret[] = $str;
            $ret = str_replace("&nbsp;", "", $ret, $i);
            $this->outputResult($ret, 1);
        }
        if (false) {
            $this->getLastRequestTime();
            echo $this->last_req_date;
        }
        if (false) {
            $data = $this->parseHtml($this->test_bodytext3);
            $ret = $this->addTables($data);
            // var_dump($data);
        }
        if (false) {
            $data = $this->parseHtml($this->test_bodytext4);
            $ret = $this->addTables($data);
            // var_dump($data);
        }
        if (false) {
            $outerHtml = '<table width="660" border="0" cellpadding="1" cellspacing="0">
        <tbody><tr><td><font face="arial" size="1">&nbsp;</font></td></tr>
        <tr><td><font face="arial" style="font-size:14px;"><b>JOB INFORMATION:</b></font></td></tr>
        </tbody></table>';
            $this->checkHead($outerHtml, "JOB INFORMATION", 'b', false, true);
        }
        if (false) {
            $outerHtml = '<table width="660" border="0" cellpadding="1" cellspacing="0"> <tbody><tr><td><font face="arial" size="1">&nbsp;</font></td></tr> <tr><td align="center"><font face="arial" style="font-size:12px;">Please direct questions regarding the following tasks to: Jeff Beach&nbsp;</font></td> </tr></tbody></table>';
            $this->checkHead($outerHtml, "Please direct questions regarding the following task", 'font', false, true);
        }
        return $ret;
    }

    public function filterFontTag($input, $printmode = false) {
        $ret = array();
        foreach ($input as $key => $value) {
            $dom = new Dom;
            $dom->load($value);
            $bigtable = ($dom->find('font')[0]);
            if (is_null($bigtable)) {
                $value_p = $value;
            } else {
                $value_p = $bigtable->innerHtml();
                if ($printmode) {
                    var_dump($key);
                    var_dump($value_p);
                    echo "<br/>";
                }
            }

            $ret[$key] = $value_p;
        }
        return $ret;
    }

    public function filterArrayContent($input) {
        $datas = array();
        $pattern = array('/\'/i', '<br>', '/\&nbsp;/i', '/lt;/i', '/\rt;/i');
        $replace = array('\\\'', '', '', '', '');
        $pattern = array('/\'/i', '<br>', '/\&nbsp;/i');
        $replace = array('\\\'', '', '');

        $pattern = array('<br>', '/\&nbsp;/i', '/\&nbsp/i');
        $replace = array('', ' ', ' ');
        foreach ($input as $param => $value) {
            $tmp = trim($value);
            if (strlen($tmp) > 0) {
                //$ret = mysql_real_escape_string($tmp);
                $ret = false;

                if (is_bool($ret) && !$ret) {
                    //$datas[$param] = $tmp;
                    $datas[$param] = trim(preg_replace($pattern, $replace, $tmp));
                } else {
                    $datas[$param] = trim($ret);
                }
            }
        }
        $i = 0;
        $datas = str_replace("<>", "", $datas, $i);
        $datas = str_replace("< />", "", $datas, $i);
        $datas = str_replace("\\'", "\'", $datas, $i);
        $datas = str_replace("< /", "", $datas, $i);
        $datas = str_replace("<", "", $datas, $i);
        $datas = str_replace("/>", "", $datas, $i);
        $datas = str_replace(">", "", $datas, $i);
        $datas = str_replace("&nbsp;", "", $datas, $i);

        return $datas;
    }

    public function getInsertObjects($input) {
        $ins_building = array();
        // $params = array("tp_id", "tu_id");
        // $datas = extractAsArray($input, $params);
        $timestr = date("YmdHis", time());
        $ins_building['job_number'] = $input['jnum'];
        $ins_building['community'] = $input['community_name'];
        $ins_building['address'] = $input['jaddress'];
        $ins_building['created_at'] = $timestr;
        $ins_building['updated_at'] = $timestr;
        $ins_building['field_manager'] = $input['fname'] . " " . $input['lname'];
        $ins_building['builder'] = '2';


        $ins_community = array();
        //$ins_community['community_id'] = '';
        $ins_community['community_name'] = $input['community_name'];
        $ins_community['city'] = $input['jcity'];
        $ins_community['region'] = '0';
        // $ins_community['builder'] = '';;
        $ins_community['community_id'] = $input['community_id'];
        $ins_community['builder'] = '2';
        $ins_community['created_at'] = $timestr;
        $ins_community['updated_at'] = $timestr;

        $ins_req = array();
        $ins_req['category'] = '3';
        $ins_req['job_number'] = $input['jnum'];
        $ins_req['created_at'] = $timestr;
        $ins_req['requested_at'] = $input['dneed'];
        $ins_req['time_stamp'] = $timestr;
        $ins_req['community_name'] = $input['community_name'];
        $ins_req['lot'] = $input['lot'];
        $ins_req['address'] = $input['jaddress'];
        $ins_req['city'] = $input['jcity'];
        $ins_req['status'] = '0';
        $ins_req['design_location'] = '';
        $ins_req['ip_address'] = $this->ipaddr;

        $ins_admin = array();
        $ins_admin['kind'] = '2';
        $ins_admin['email'] = $input['email'];
        $ins_admin['first_name'] = $input['fname'];
        $ins_admin['last_name'] = $input['lname'];
        $ins_admin['address'] = '';
        $ins_admin['password'] = '';
        $ins_admin['status'] = '0';
        $ins_admin['builder'] = '2';
        $ins_admin['allow_email'] = '1';
        $ins_admin['status'] = '0';
        $ins_admin['updated_at'] = $timestr;
        $ins_admin['created_at'] = $timestr;


        $ret = array();
        $ret['ins_admin'] = $this->filterArrayContent($ins_admin);
        $ret['ins_building'] = $this->filterArrayContent($ins_building);
        $ret['ins_community'] = $this->filterArrayContent($ins_community);
        $ret['ins_req'] = $this->filterArrayContent($ins_req);
        return $ret;
    }

    public function extractParams($input, $fields1, $fields2) {
        $ret = array();
        for ($i = 0; $i < count($fields1); $i++) {
            $key = $fields1[$i];
            $key_out = $fields2[$i];
            if (isset($input[$key])) {
                $ret[$key_out] = trim($input[$key]);
            }
        }
        return $ret;
    }

    public function generateData($input, $printmode = false) {
        $ret = array();
        $input = $this->filterFontTag($input);

        $fields1 = array("DATE NEEDED", "JOB NAME", "Job Address", "LOT", "Email", "ADDRESS", "FROM", 'manager');
        $fields2 = array("dneed", "jname", "jaddress", "lot", "email", "caddress", "cfrom", 'manager');
        $ret = $this->extractParams($input, $fields1, $fields2);

        if (isset($ret['caddress'])) {
            $ret['caddress'] = $ret['caddress'];
        }
        if (isset($ret['dneed'])) {
            $pieces = explode("/", $ret['dneed']);
            if (is_array($pieces) && count($pieces) >= 3) {
                $ret['dneed'] = $pieces[2] . '-' . $pieces[0] . '-' . $pieces[1];
            }
        }
        if (isset($ret['manager'])) {
            $pieces = explode(" ", $ret['manager']);
            if (is_array($pieces) && count($pieces) >= 2) {
                $ret['fname'] = trim($pieces[0]);
                $ret['lname'] = trim($pieces[1]);
            }
        }
        if (isset($ret['lot'])) {
            $pieces = explode("/", $ret['lot']);
            $ret['lot'] = trim($pieces[0]);
        }
        if (isset($ret['cfrom'])) {
            if ($printmode) {
                echo 'cfrom';
                echo $ret['cfrom'];
            }

            $pieces = explode("-", $ret['cfrom']);
            $ret['community'] = trim($pieces[0]);

            $pos1 = stripos($ret['cfrom'], "-");
            if ($pos1) {
                $ret['community_name'] = substr($ret['cfrom'], $pos1 + 1, -1);
            }
        }
        if (isset($ret['jname'])) {
            $pieces = explode(" ", $ret['jname']);
            if ($printmode) {
                var_dump($input);
                var_dump($jobname);
                var_dump($pieces);
            }
            $ret['jnum'] = $pieces[0];
            $ret['community_id'] = substr($pieces[0], 0, 5);
        }
        if (isset($ret['jaddress'])) {
            // $pieces = explode("<br>", $ret['jaddress']);
            // if (is_array($pieces) && count($pieces) >= 2) {
            //     $ret['jaddress'] = $pieces[0];
            //     $istr = trim($pieces[1]);
            //     $ipieces = explode(",", $istr);
            //     //var_dump($ipieces);
            //     $ret['jcity'] = $ipieces[0];
            // }
            $pattern = array('<br>');
            $replace = array('AAAA');

            $tmp = preg_replace($pattern, $replace, $ret['jaddress']);
            if ($pos1) {
                $pieces = explode("AAAA", $tmp);
                $pieces = $this->filterArrayContent($pieces);
                $ret['jaddress'] = $pieces[0];

                $subpieces = explode(",", $pieces[1]);
                $ret['jcity'] = $subpieces[0];
            }
        }
        if (isset($ret['caddress'])) {
            $pieces = explode(",", $ret['caddress']);
            if (is_array($pieces) && count($pieces) >= 1) {
                $istr = trim($pieces[count($pieces) - 1]);
                $ipieces = explode("&nbsp;", $istr);
                //var_dump($ipieces);
                $ret['ccity'] = $ipieces[0];
            }

            $pieces = explode("-", $ret['caddress']);
            if (is_array($pieces) && count($pieces) >= 2) {
                $ret['caddress'] = $pieces[1];
            }
        }
        return $ret;
    }

    public function parseHtml($bodytext = "") {
        $this->resetValues();
        if ($this->printdetail) {
            echo $bodytext;
        }
        $endP = "<br/>parseHtml Pagraph<br/>";
        $dom = new Dom;
        $dom->load($bodytext);
        $bigtable = ($dom->find('table')[0]);
        $cnt = 0;

        $data = array();
        $jobinfo_index = -1;
        while (true) {
            $ptable1 = $bigtable->find('table')[$cnt];
            if (is_null($ptable1)) {
                break;
            }
            $outerHtml = $ptable1->outerHtml();
            if ($this->printdetail) {
                echo $outerHtml;
                echo $endP;
            }

            if ($this->index_jobinfo_found == 0) {
                if ($this->checkHead($outerHtml, $this->index_jobinfo, 'b')) {
                    //echo "sss ". $outerHtml;
                    // job information table
                    $jobinfo_index = $cnt;

                    $itable = $ptable1 = $bigtable->find('table')[$cnt + 1];
                    $ihtml = $itable->outerHtml();
                    if ($this->printdetail) {
                        echo $ihtml;
                    }
                    $iterms = $this->parseTable($ihtml, ["DATE NEEDED", "JOB NAME", "LOT", "Job Address"], 'TD', false, false);
                    $data = array_merge($data, $iterms);


                    // email part table
                    $itable = $ptable1 = $bigtable->find('table')[$cnt - 1];
                    $ihtml = $itable->outerHtml();
                    if ($this->printdetail) {
                        echo $ihtml;
                    }
                    $iterms = $this->parseTable($ihtml, ["Email"], 'TD', false, false);
                    $data = array_merge($data, $iterms);

                    // field manager
                    $itable = $ptable1 = $bigtable->find('table')[$cnt - 2];
                    $td_param1 = $itable->find('TD')[1];
                    $ihtml = $td_param1->innerHtml();
                    $idom = new Dom();
                    $idom->load($ihtml);
                    $a = $idom->find('font')[0];
                    $text = $a->text;
                    if (stripos($text, "to:") !== false) {
                        $pos = stripos($text, "to:");
                        $istr = substr($text, $pos + 3, -1);
                        $data['manager'] = $istr;
                        // $data['$pos'] = $pos;
                        // $data['$text'] = $text;
                        // $data['$this->index_coninfo'] = $this->index_coninfo;
                    } else {
                        
                    }
                }
            }

            if ($this->index_coninfo_found == 0) {
                if ($this->checkHead($outerHtml, $this->index_coninfo, 'b')) {
                    //echo "sss ". $outerHtml;
                    $itable = $ptable1 = $bigtable->find('table')[$cnt + 1];
                    $ihtml = $itable->outerHtml();
                    if ($this->printdetail) {
                        echo $ihtml;
                    }
                    $iterms = $this->parseTable($ihtml, ["FROM", "ADDRESS"], 'TD', false, false);
                    $data = array_merge($data, $iterms);
                    $this->index_coninfo_found = 1;
                }
            }

            $cnt++;
        }

        if ($this->index_coninfo_found == 0) {
            // contact INFORMATION
            $itable = $ptable1 = $bigtable->find('table')[$jobinfo_index - 3];
            if (!is_null($itable)) {
                $isubtable = $itable->find('table')[0];
                // echo $isubtable;
                //$iterms = $this->parseTable($ihtml, ["FROM","ADDRESS"], 'TD', false, false);
                $td_from = $isubtable->find('td')[0];
                $td_addr = $isubtable->find('td')[2];
                $iterms = array();
                $iterms['FROM'] = $td_from->innerHtml();
                $iterms['ADDRESS'] = $td_addr->innerHtml();
                // echo $td_from;
                // echo $td_addr;
                $data = array_merge($data, $iterms);

                $this->index_jobinfo_found = 1;
            }
        }
        if (count($data) > 0) {
            $this->outputResult($data, 1);
            $data = $this->generateData($data);
            $data = $this->getInsertObjects($data);
            return $data;
        }else{
            return null;
        }

        
    }

    public function outputResult($content, $mode = 0) {
        switch ($mode) {

            case 2: {
                    echo "<pre>";
                    print_r($content);
                    echo "</pre>";
                    break;
                }
            case 1: {

                    if ($this->printmode == 1) {
                        echo "<pre>";
                        print_r($content);
                        echo "</pre>";
                    }

                    break;
                }


            default: {
                    break;
                }
        }
    }

    public function parseTable($html, $content_ary, $search, $testmode = false, $printmode = false) {
        $endP = "<br/>parseTable Pagraph<br/>";
        if ($printmode) {
            echo $endP;
        }
        if ($testmode) {
            $html = '<table width="660" border="0" cellpadding="1" cellspacing="0"> <tbody><tr><td><font face="arial" size="1">&nbsp;</font></td></tr> <tr><td><font face="arial" style="font-size:14px;"><b>JOB INFORMATION:</b></font></td></tr> </tbody></table>';
        }

        $dom = new Dom;
        $dom->load($html);

        $cnt = 0;
        $data = [];
        while (true) {
            $ptable1 = $dom->find($search)[$cnt];
            if (is_null($ptable1)) {
                break;
            }
            if ($printmode) {
                echo $ptable1->innerHtml();
                echo $endP;
            }
            $innerhtml = $ptable1->innerHtml();
            foreach ($content_ary as $content) {
                if (stripos($innerhtml, $content) !== false) {
                    $inext = $dom->find($search)[$cnt + 1];
                    if (!is_null($inext)) {
                        if ($printmode) {
                            echo "FFOONN";
                            echo $inext;
                        }
                        $data[$content] = $inext->innerHtml();
                    }
                    break;
                }
            }

            $cnt++;
        }
        return $data;
    }

    public function checkHead($html, $content, $search, $testmode = false, $printmode = false) {
        $endP = "<br/>checkHead Pagraph<br/>";
        if ($printmode) {
            echo $endP;
            echo $search;
            echo $html;
            echo $endP;
        }
        if ($testmode) {
            $html = '<table width="660" border="0" cellpadding="1" cellspacing="0"> <tbody><tr><td><font face="arial" size="1">&nbsp;</font></td></tr> <tr><td><font face="arial" style="font-size:14px;"><b>JOB INFORMATION:</b></font></td></tr> </tbody></table>';
        }

        $dom = new Dom;
        $dom->load($html);

        $cnt = 0;
        while (true) {
            $ptable1 = $dom->find($search)[$cnt];
            if (is_null($ptable1)) {
                break;
            }
            if ($printmode) {
                echo $ptable1->outerHtml();
                echo $endP;
            }
            $innerhtml = $ptable1->innerHtml();
            if (stripos($innerhtml, $content) !== false) {
                if ($printmode) {
                    echo "contains";
                }
                return true;
            } else {
                if ($printmode) {
                    echo "not contains";
                }
                return false;
            }
            $cnt++;
        }
    }

    public function fetchMessages($limit = 2) {
        set_time_limit(0);
        $host = "smtp.emailsrvr.com";
        $host = "secure.emailsrvr.com";
        $user = "inspect@e3bldg.com";
        $password = "sN>8KM)=";
        $port = 110;

        $server = new Ddeboer\Imap\Server($host);

        // $connection is instance of \Ddeboer\Imap\Connection
        $connection = $server->authenticate($user, $password);
        $mailbox = $connection->getMailbox('INBOX');
        //$messages = $mailbox->getMessages();

        $search = new SearchExpression();
        $search->addCondition(new FromAddress('postmaster@hyphensolutions.net'));

        $messages = $mailbox->getMessages($search);
        $cnt = 0;
        $ret = array();
//        $limit = 26;
        foreach ($messages as $message) {
            $bodytext = $message->getBodyText();
            if (false) {
                echo "getNumber   ";
                echo $message->getNumber();
                echo "<br/>";
                //echo "getSubject   ";echo $message->getSubject(); echo "<br/>";
                //echo "getFrom   ";echo $message->getFrom(); echo "<br/>";
                //echo "isSeen   "; echo $message->isSeen();      echo "<br/>";

                echo "getBodyText   ";
                echo $bodytext;
                echo "<br/>";


                // $dom = new Dom;
                // $dom->load($bodytext);
                // $a = $dom->find('font')[0];
                // echo $a->text; // "click here"

                echo "<br/>";
                echo "<br/>";
            }
            $subject = $message->getSubject();
            $reschedule_notice = true;
            try {
                if (stripos($subject, "Reschedule Notice") !== false) {
                    $reschedule_notice = true;
                } else {
                    $reschedule_notice = false;
                }
                $input = $this->parseHtml($bodytext);
                if (is_array($input) && isset($input['ins_req'])) {
                     if ($this->fakeinsert == 1) {
                         $ret[] = $input;
                     } else {
                         $r1 = $this->addTables($input,"add",$reschedule_notice);
                         $ret[] = $r1;
                     }
                }
            } catch (Exception $ex) {
                
            }
            $ret[] = $subject;

            $cnt++;
            if ($limit > 0 && $cnt >= $limit) {
                break;
            }
        }
        return $ret;
    }

    private $test_bodytext4 = '<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tbody><tr>
      <td valign="bottom">
        <table cellspacing="0" cellpadding="0" width="100%" border="0">
          <tbody><tr>
            <td valign="top" align="middle">
              <table cellspacing="0" cellpadding="0" width="600" border="0">
                <tbody><tr>
                  <td valign="top" align="middle">
                    <font face="arial" color="black" size="2">
                    <table cellspacing="1" cellpadding="1" width="500" border="0">
                      <tbody><tr>
                        <td>
                          <table cellspacing="0" cellpadding="0" width="100%" border="0">
                            <tbody><tr>
                              <td valign="top" align="middle">
                                <table cellspacing="1" cellpadding="3" width="500" border="0">
                                  <tbody><tr>
                                    <td valign="top" align="middle"><font size="5"><b>Delivery Receipt Notification</b></font></td>
                                  </tr>
                                  <tr>
                                    <td valign="top" align="middle"><font size="3"><b>BuildPro Order Received Complete</b></font></td>
                                  </tr>
                                  <tr>
                                    <td valign="top" align="middle"><b><font size="2" face="Arial">Billing Address:<br>Lennar Homes LLC</font><br></b>
                                      <font face="arial" size="2"><b>10481 Ben C Pratt/Six Mile Cyp<br>Fort Myers, FL&nbsp;&nbsp;33966<br></b><br></font>
                                    </td>
                                  </tr>
                                </tbody></table>
                                <table cellspacing="1" cellpadding="3" width="500" border="0">
                                  <tbody><tr>
                                    <td width="250"><font face="arial" size="2">from:</font></td>
                                    <td width="250"><font face="arial" size="2">to:</font></td>
                                  </tr>
                                  <tr>
                                    <td valign="top" width="250"><font face="arial" size="2">
                                      <table cellspacing="0" cellpadding="1" width="240" border="1">
                                        <tbody><tr>
                                          <td colspan="2"><font size="2">Lennar Homes LLC</font></td>
                                        </tr>
                                        <tr>
                                          <td valign="top" align="right" colspan="2"><p align="left"><font size="2">Jack Turner</font></p></td>
                                        </tr>
                                        <tr>
                                          <td align="right"><font face="arial" size="2">phone:</font></td>
                                          <td><font face="arial" size="2">&nbsp;239-872-1127&nbsp;</font></td>
                                        </tr>
                                        <tr>
                                          <td align="right"><font face="arial" size="2">fax:</font></td>
                                          <td><font face="arial" size="2">&nbsp; &nbsp;</font></td>
                                        </tr>
                                        <tr>
                                          <td align="right"><font face="arial" size="2">email:</font></td>
                                          <td><font face="arial" size="2">&nbsp;Jack.Turner@Lennar.com &nbsp;</font></td>
                                        </tr>
                                      </tbody></table></font>
                                    </td>
                                    <td valign="top" width="250"><font face="arial" size="2">
                                      <table cellspacing="0" cellpadding="3" width="240" border="1"><tbody>
                                        <tr>
                                          <td colspan="2"><font face="arial" size="2">E3 Design Group Inc</font></td>
                                        </tr>
                                        <tr>
                                          <td align="right"><font face="arial" size="2">attn:</font></td>
                                          <td><font face="arial" size="2">&nbsp;Order Processing&nbsp;</font></td>
                                        </tr>
                                        <tr>
                                          <td align="right"><font size="2">phone</font><font face="arial" size="2">:</font></td>
                                          <td><font face="arial" size="2">&nbsp;2399492405&nbsp;</font></td>
                                        </tr>
                                        <tr>
                                          <td align="right"><font face="arial" size="2">fax:</font></td>
                                          <td><font face="arial" size="2">&nbsp;5555555555&nbsp;</font></td>
                                        </tr>
                                        <tr>
                                          <td align="right"><font face="arial" size="2">email:</font></td>
                                          <td><font face="arial" size="2">&nbsp;MH2SQL@Lennar.com&nbsp;</font></td>
                                        </tr>
                                      </tbody></table></font>
                                    </td>
                                  </tr>
                                </tbody></table>
                                <table cellspacing="1" cellpadding="0" width="500" border="0">
                                  <tbody><tr>
                                    <td align="middle" width="496" colspan="1"><p align="left">
                                      <font face="Arial" size="2" color="black">* Please direct questions regarding the following order to the above contact.</font>
                                      <br></p><hr>
                                    </td>
                                  </tr>
                                </tbody></table>
                                <table cellspacing="1" cellpadding="2" width="500" border="0">
                                  <tbody><tr>
                                    <td align="right" width="106"><font face="arial" size="2"><b>Order Number : </b></font></td>
                                    <td width="152"><font size="2" face="Arial">32373812-000</font></td>
                                    <td align="right" width="63"><font face="arial" size="2"><b>Plan : </b></font></td>
                                    <td width="208"><font face="arial" size="2">4163</font></td>
                                  </tr>
                                  <tr>
                                    <td valign="top" align="right" width="96"><font face="arial" size="2"><b>Subdivision : </b></font></td>
                                    <td width="152"><font face="Arial" size="2">PELICAN PRES - PRATO -GR VILLA - 966651</font></td>
                                    <td valign="top" align="right" width="63"><font face="arial" size="2"><b>Elevation : </b></font></td>
                                    <td valign="top" width="208"><font face="arial" size="2"></font></td>
                                  </tr>
                                  <tr>
                                    <td valign="top" align="right" width="96" rowspan="2"><font face="arial" size="2"><b>&nbsp;Job Name : </b></font></td>
                                    <td width="152" valign="top" rowspan="2"><font size="2" face="Arial">9666510604 - 10213 LIVORNO DR</font></td>
                                    <td valign="top" align="right" width="63"><b><font face="arial" size="2">Lot : </font></b></td>
                                      <td valign="top" width="208">
                                      <font face="arial" size="2">0604</font></td>
                                  </tr>
                                  <tr>
                                    <td valign="top" align="right" width="63"><b><font face="arial" size="2">Block : </font></b></td>
                                      <td valign="top" width="208">
                                      <font face="arial" size="2">ALERT WCI WARRANTY</font></td>
                                  </tr>
                                  <tr>
                                    <td align="right" width="96" valign="top"><font face="arial" size="2"><b>&nbsp;Task : </b></font></td>
                                    <td valign="top" colspan="3"><font face="Arial" size="2">Duct Testing [9700185 - 32373812-000] [OS] [A]</font></td>
                                  </tr>
                                  <tr>
                                    <td align="right"><font face="arial" size="2">&nbsp;<b>Address : </b></font></td>
                                    <td align="left" colspan="3"><font face="arial" size="2">10213 LIVORNO DR</font></td>
                                  </tr>
                                  <tr>
                                    <td colspan="4" height="5"></td>
                                  </tr>
                                </tbody></table>
                                <table cellspacing="0" cellpadding="2" width="500" border="1">
                                  <tbody><tr>
                                    <td colspan="4" align="center"><b><font face="arial" size="2">Item NO</font></b></td>
                                    <td colspan="4" align="center"><b><font face="arial" size="2">Item</font></b></td>
                                    <td colspan="4" align="center" nowrap=""><b><font face="arial" size="2">Qty Ordered</font></b></td>
                                    <td colspan="4" align="center" nowrap=""><b><font face="arial" size="2">Qty Received</font></b></td>
                                    <td colspan="4" align="center" nowrap=""><b><font face="arial" size="2">Unit Cost</font></b></td>
                                    <td colspan="4" align="center"><b><font face="arial" size="2">Total</font></b></td>
                                  </tr>
                                  <tr>
                                    <td colspan="4" align="left"><font face="Arial" size="2"></font></td>
                                    <td colspan="4" align="left"><font face="Arial" size="2">T-HVAC-Start Up/Test</font></td>
                                    <td colspan="4" align="right"><font face="Arial" size="2">1.00</font></td>
                                    <td colspan="4" align="right"><font face="Arial" size="2">1.00</font></td>
                                    <td colspan="4" align="right"><font face="Arial" size="2">175.0000</font></td>
                                    <td colspan="4" align="right"><font face="Arial" size="2">175.00</font></td>
                                  </tr>
                                </tbody></table>
                                <table cellspacing="0" cellpadding="0" width="500" border="0">
                                  <tbody><tr>
                                    <td colspan="3" height="10"></td>
                                  </tr>
                                  <tr>
                                    <td width="400" align="right"><b><font face="arial" size="2">&nbsp;SUBTOTAL :</font></b></td>
                                    <td align="right"><font face="Arial" size="2">$ 175.00</font></td>
                                  </tr>
                                  <tr>
                                    <td width="400" align="right"><b><font face="arial" size="2">&nbsp;ESTIMATED SALES TAX :</font></b></td>
                                    <td align="right"><font face="Arial" size="2">$ 0.00</font></td>
                                  </tr>
                                  <tr>
                                    <td width="400" align="right"><b><font face="arial" size="2">&nbsp;TOTAL DUE :</font></b></td>
                                    <td align="right"><font face="Arial" size="2">$ 175.00</font></td>
                                  </tr>
                                </tbody></table>
                                <table cellspacing="4" cellpadding="0" width="500" border="0">
                                  <tbody><tr>
                                    <td valign="top" align="right" colspan="4"><p align="left"><b><font face="Arial" size="2">Task Start and End Dates : </font></b><font face="Arial" color="Black" size="2"> 09/05/2017 through 09/05/2017</font>
                                    </p></td>
                                  </tr>
                                  <tr>
                                    <td valign="top" align="left" colspan="4"><font face="Arial" size="2"><b>Time Generated : </b> Sep  6 2017  5:26PM</font>
                                    </td>
                                  </tr>
                                </tbody></table>
                                <table cellspacing="4" cellpadding="0" width="500" border="0">
                                  <tbody><tr>
                                    <td width="10" valign="top" align="left">
                                       <font face="Arial" size="2"><b>Note :</b></font>
                                    </td>
                                    <td width="400" valign="top" align="left"></td>
                                  </tr>
                                  <tr>
                                    <td colspan="2"><hr></td>
                                  </tr>
                                  <tr>
                                    <td colspan="2" valign="top" align="left"><font face="Arial" size="1">If you have technical questions about this transmission, please call Customer Support at 1-877-508-2547 or email us at support@hyphensolutions.com. Powered by Hyphen Solutions - www.hyphensolutions.com</font>
                                    </td>
                                  </tr>
                                </tbody></table>
                              </td>
                            </tr>
                          </tbody></table>
                        </td>
                      </tr>
                    </tbody></table>
                    </font>
                  </td>
                </tr>
              </tbody></table>
            </td>
          </tr>
        </tbody></table>
      </td>
    </tr>
  </tbody></table>';
    private $test_bodytext3 = '<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tbody><tr><td valign="top" align="center">
<center><table width="330" border="0" cellpadding="1" cellspacing="0" bgcolor="black">
<tbody><tr><td bgcolor="white">
<table align="center" width="330" border="0" cellpadding="0" cellspacing="0">
<tbody><tr><td valign="top" align="center"><font face="arial" style="font-size:15px;" nowrap=""><b>WCI Communities Inc</b></font></td></tr>
<tr><td valign="top" align="center"><font face="arial" style="font-size:13px;" nowrap=""><b><b>Purchase Order Request</b></b></font></td></tr>
<tr><td valign="top" align="center"><font face="arial" style="font-size:18px;" nowrap="">BuildPro Completion Notification</font></td></tr>
<tr><td valign="top" align="center"><font face="arial" style="font-size:12px;"></font></td></tr>
<tr><td valign="top" align="center"><font face="arial" style="font-size:12px;">PELICAN PRES - PRATO -GR VILLA</font></td></tr>
<tr><td valign="top" align="center"><font face="arial" style="font-size:12px;">Cost Code: 3225120</font></td></tr>
</tbody></table>
</td></tr>
</tbody></table></center><br>
<table width="660" border="0" cellpadding="0" cellspacing="0">
<tbody><tr><td width="50%"><font face="arial" style="font-size:14px;"><b>FROM:</b></font></td>
<td width="50%"><font face="arial" style="font-size:14px;"><b>TO:</b></font></td></tr>
<tr><td valign="top">
	<table width="320" border="1" cellpadding="1" cellspacing="0" bgcolor="black">
		<tbody><tr>
			<td width="320" colspan="2" bgcolor="white" valign="top">
				<font face="arial" style="font-size:12px;">WCI Communities Inc - PELICAN PRES - PRATO -GR VILLA&nbsp;
			</font></td>
		</tr><tr>
			<td width="85" bgcolor="#f7f3f7" valign="center">
				<font face="arial" style="font-size:12px;"><b>Address:</b>
			</font></td>
			<td width="245" bgcolor="white" valign="top">
				<font face="arial" style="font-size:12px;">PELICAN PRES - PRATO - GR VILL<br>FORT MYERS, FL&nbsp;&nbsp;33913&nbsp;
			</font></td>
		</tr><tr>
			<td width="85" bgcolor="#f7f3f7" valign="top">
				<font face="arial" style="font-size:12px;"><b>Main:</b>
			</font></td>
			<td width="245" bgcolor="white" valign="top">
				<font face="arial" style="font-size:12px;">2394988200&nbsp;
			</font></td>
		</tr><tr>
			<td width="85" bgcolor="#f7f3f7" valign="top">
				<font face="arial" style="font-size:12px;"><b>Fax:</b>
			</font></td>
			<td width="245" bgcolor="white" valign="top">
				<font face="arial" style="font-size:12px;">2394988200&nbsp;
			</font></td>
		</tr>
	</tbody></table>
</td><td valign="top">
	<table width="320" border="1" cellpadding="1" cellspacing="0" bgcolor="black">
		<tbody><tr>
			<td width="320" colspan="2" bgcolor="white" valign="top">
				<font face="arial" style="font-size:12px;">E3 DESIGN GROUP INC&nbsp;
			</font></td>
		</tr><tr>
			<td width="85" bgcolor="#f7f3f7" valign="top">
				<font face="arial" style="font-size:12px;"><b>Attn:</b>
			</font></td>
			<td width="245" bgcolor="white" valign="top">
				<font face="arial" style="font-size:12px;">Stephanie Gray&nbsp;
			</font></td>
		</tr><tr>
			<td width="85" bgcolor="#f7f3f7" valign="top">
				<font face="arial" style="font-size:12px;"><b>Office:</b>
			</font></td>
			<td width="245" bgcolor="white" valign="top">
				<font face="arial" style="font-size:12px;">(239) 949-2405&nbsp;
			</font></td>
		</tr><tr>
			<td width="85" bgcolor="#f7f3f7" valign="top">
				<font face="arial" style="font-size:12px;"><b>Fax:</b>
			</font></td>
			<td width="245" bgcolor="white" valign="top">
				<font face="arial" style="font-size:12px;">(239) 949-3702&nbsp;
			</font></td>
		</tr><tr>
			<td width="85" bgcolor="#f7f3f7" valign="top">
				<font face="arial" style="font-size:12px;"><b>Vendor #:</b>
			</font></td>
			<td width="245" bgcolor="white" valign="top">
				<font face="arial" style="font-size:12px;">1139451&nbsp;
			</font></td>
		</tr>
	</tbody></table>
</td></tr></tbody></table>
<table width="660" border="0" cellpadding="1" cellspacing="0">
<tbody><tr><td><font face="arial" size="1">&nbsp;</font></td></tr>
<tr><td align="center"><font face="arial" style="font-size:12px;">Please direct questions regarding the following tasks to: Melvin Jordan&nbsp;</font></td>
</tr></tbody></table>
<table width="660" border="1" cellpadding="1" cellspacing="0" bgcolor="black">
<tbody><tr>
<td width="85" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>Phone:</b></font></td>
<td width="245" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">2392810201&nbsp;</font></td>
<td width="85" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>Fax:</b></font></td>
<td width="245" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td width="85" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>Email:</b></font></td>
<td width="245" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">MelvinJordan@wcicommunities.com&nbsp;</font></td>
</tr>
</tbody></table>
<table width="660" border="0" cellpadding="1" cellspacing="0">
	<tbody><tr><td><font face="arial" size="1">&nbsp;</font></td></tr>
	<tr><td><font face="arial" style="font-size:14px;"><b>JOB INFORMATION:</b></font></td></tr>
</tbody></table>
<table width="660" border="1" cellpadding="1" cellspacing="0" bgcolor="black">
	<tbody><tr>
		<td width="80" bgcolor="#f7f3f7" align="left" valign="middle"><font face="arial" style="font-size:12px;"><b>Order #:</b></font></td>
		<td width="140" bgcolor="white" align="left" valign="middle"><font face="arial" style="font-size:12px;">2436809-000</font></td>
		<td width="80" bgcolor="#f7f3f7" valign="middle"><font face="arial" style="font-size:12px;"><b>Job Name:</b></font></td>
		<td width="140" bgcolor="white" valign="middle"><font face="arial" style="font-size:12px;">26500250519 - 10342 FONTANELLA DR - 4163&nbsp;</font></td>
		<td width="80" bgcolor="#f7f3f7" valign="middle"><font face="arial" style="font-size:12px;"><b>Plan:</b></font></td>
		<td width="140" bgcolor="white" valign="middlep"><font face="arial" style="font-size:12px;">4163&nbsp;</font></td>
	</tr>
	<tr>
		<td width="80" bgcolor="#f7f3f7" valign="middle"><font face="arial" style="font-size:12px;"><b>Date Needed:</b></font></td>
		<td width="140" bgcolor="white" valign="middle"><font face="arial" style="font-size:12px;">08/14/2017</font></td>
		<td width="80" bgcolor="#f7f3f7" valign="middle" rowspan="2"><font face="arial" style="font-size:12px;"><b>Job Address:</b></font></td>
		<td width="140" bgcolor="white" valign="middle" rowspan="2"><font face="arial" style="font-size:12px;">10342 FONTANELLA DR<br>FORT MYERS, FL&nbsp;&nbsp;33913<br></font></td>
		<td width="80" bgcolor="#f7f3f7" valign="middle"><font face="arial" style="font-size:12px;"><b>Elev/Swing:</b></font></td>
		<td width="140" bgcolor="white" valign="middle"><font face="arial" style="font-size:12px;">R/R&nbsp;</font></td>
	</tr>
	<tr>
		<td width="80" bgcolor="#f7f3f7" valign="middle"><font face="arial" style="font-size:12px;"><b>Date&nbsp;Completed:</b></font></td>
		<td width="140" bgcolor="white" valign="middle"><font face="arial" style="font-size:12px;">
08/15/2017
		&nbsp;</font></td>
		<td width="80" bgcolor="#f7f3f7" valign="middle"><font face="arial" style="font-size:12px;"><b>Lot/Block:</b></font></td>
		<td width="140" bgcolor="white" valign="middler"><font face="arial" style="font-size:12px;">0519/&nbsp;</font></td>
	</tr>
	<tr>
		<td width="80" bgcolor="#f7f3f7" valign="middle"><font face="arial" style="font-size:12px;"><b>PO Date:</b></font></td>
		<td width="140" colspan="5" bgcolor="white" valign="middle"><font face="arial" style="font-size:12px;">07/17/2017</font></td>
	</tr>
</tbody></table>
<table width="660" border="0" cellpadding="1" cellspacing="0">
<tbody><tr><td><font face="arial" size="1">&nbsp;</font></td></tr>
<tr><td><font face="arial" style="font-size:14px;"><b>REQUEST INFORMATION:</b></font></td></tr>
</tbody></table>
<table width="660" border="1" cellpadding="1" cellspacing="0" bgcolor="black">
<tbody><tr><td bgcolor="white">
<table width="660" border="0" cellpadding="0" cellspacing="0" bgcolor="black">
<tbody><tr>
<th style="border-bottom: 1px solid black; border-right: 1px solid black;" width="409" align="left" bgcolor="#f7f3f7"><font face="arial" style="font-size:12px;">&nbsp;&nbsp;ITEM DESCRIPTION</font></th>
<th style="border-bottom: 1px solid black; border-right: 1px solid black;" width="49" align="center" bgcolor="#f7f3f7"><font face="arial" style="font-size:12px;">&nbsp;&nbsp;&nbsp;&nbsp;QTY<br>&nbsp;ORDERED&nbsp;</font></th>
<th style="border-bottom: 1px solid black; border-right: 1px solid black;" width="49" align="center" bgcolor="#f7f3f7"><font face="arial" style="font-size:12px;">&nbsp;&nbsp;&nbsp;&nbsp;QTY<br>&nbsp;RECEIVED&nbsp;</font></th>
<th style="border-bottom: 1px solid black; border-right: 1px solid black;" width="99" align="center" bgcolor="#f7f3f7"><font face="arial" style="font-size:12px;">UNIT PRICE</font></th>
<th style="border-bottom: 1px solid black;" width="100" align="center" bgcolor="#f7f3f7"><font face="arial" style="font-size:12px;">TOTAL</font></th>
</tr>
<tr>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;Duct Testing [1139451 - 2436809-000 - 3225120][UB]</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
</tr>
<tr>
<td style="border-right: 1px solid black;" valign="top" align="left" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;HVAC - DUCT TESTING - 0.001</font></td>
<td style="border-right: 1px solid black;" valign="top" align="center" bgcolor="white"><font face="arial" style="font-size:12px;">1</font></td>
<td style="border-right: 1px solid black;" valign="top" align="center" bgcolor="white"><font face="arial" style="font-size:12px;">1</font></td>
<td style="border-right: 1px solid black;" valign="top" align="right" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;$
175.00
&nbsp;</font></td>
<td valign="top" align="right" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;$
175.00
&nbsp;</font></td>
</tr>
<tr>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
</tr>
<tr>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
</tr>
<tr>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" align="right" bgcolor="white"><font face="arial" style="font-size:12px;">Subtotal: &nbsp;</font></td>
<td align="right" bgcolor="white"><font face="arial" style="font-size:12px;">$
175.00
&nbsp;</font></td>
</tr>
<tr>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" align="right" bgcolor="white"><font face="arial" style="font-size:12px;">Tax Total: &nbsp;</font></td>
<td align="right" bgcolor="white"><font face="arial" style="font-size:12px;">$
0.00
&nbsp;</font></td>
</tr>
<tr>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" align="right" bgcolor="white"><font face="arial" style="font-size:12px;"><b>Total: &nbsp;</b></font></td>
<td align="right" bgcolor="white"><font face="arial" style="font-size:12px;">$
175.00
&nbsp;</font></td>
</tr>
</tbody></table>
</td></tr>
</tbody></table>
<table width="660" border="0" cellpadding="1" cellspacing="0">
<tbody><tr><td><font face="arial" size="1">&nbsp;</font></td></tr>
<tr>
<td align="left"><font face="arial" style="font-size:12px;">Submitted by:&nbsp;Melvin Jordan</font></td>
<td align="right"><font face="arial" style="font-size:12px;">08/15/2017  4:13:33 AM</font></td>
</tr>
</tbody></table>
<table width="660" border="0" cellpadding="1" cellspacing="0">
<tbody><tr><td align="center"><font face="arial" size="1">Powered by Hyphen Solutions - http://www.hyphensolutions.com </font></td></tr>
</tbody></table>
</td></tr>
</tbody></table>';
    private $test_bodytext2 = '<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tbody><tr><td valign="top" align="center">
<center><table width="330" border="0" cellpadding="1" cellspacing="0" bgcolor="black">
<tbody><tr><td bgcolor="white">
<table align="center" width="330" border="0" cellpadding="0" cellspacing="0">
<tbody><tr><td valign="top" align="center"><font face="arial" style="font-size:15px;" nowrap=""><b>WCI Communities Inc</b></font></td></tr>
<tr><td valign="top" align="center"><font face="arial" style="font-size:13px;" nowrap=""><b><b>Purchase Order Request</b></b></font></td></tr>
<tr><td valign="top" align="center"><font face="arial" style="font-size:18px;" nowrap="">BuildPro Completion Notification</font></td></tr>
<tr><td valign="top" align="center"><font face="arial" style="font-size:12px;"></font></td></tr>
<tr><td valign="top" align="center"><font face="arial" style="font-size:12px;">PELICAN PRES - PRATO -GR VILLA</font></td></tr>
<tr><td valign="top" align="center"><font face="arial" style="font-size:12px;">Cost Code: 3225120</font></td></tr>
</tbody></table>
</td></tr>
</tbody></table></center><br>
<table width="660" border="0" cellpadding="0" cellspacing="0">
<tbody><tr><td width="50%"><font face="arial" style="font-size:14px;"><b>FROM:</b></font></td>
<td width="50%"><font face="arial" style="font-size:14px;"><b>TO:</b></font></td></tr>
<tr><td valign="top">
	<table width="320" border="1" cellpadding="1" cellspacing="0" bgcolor="black">
		<tbody><tr>
			<td width="320" colspan="2" bgcolor="white" valign="top">
				<font face="arial" style="font-size:12px;">WCI Communities Inc - PELICAN PRES - PRATO -GR VILLA&nbsp;
			</font></td>
		</tr><tr>
			<td width="85" bgcolor="#f7f3f7" valign="center">
				<font face="arial" style="font-size:12px;"><b>Address:</b>
			</font></td>
			<td width="245" bgcolor="white" valign="top">
				<font face="arial" style="font-size:12px;">PELICAN PRES - PRATO - GR VILL<br>FORT MYERS, FL&nbsp;&nbsp;33913&nbsp;
			</font></td>
		</tr><tr>
			<td width="85" bgcolor="#f7f3f7" valign="top">
				<font face="arial" style="font-size:12px;"><b>Main:</b>
			</font></td>
			<td width="245" bgcolor="white" valign="top">
				<font face="arial" style="font-size:12px;">2394988200&nbsp;
			</font></td>
		</tr><tr>
			<td width="85" bgcolor="#f7f3f7" valign="top">
				<font face="arial" style="font-size:12px;"><b>Fax:</b>
			</font></td>
			<td width="245" bgcolor="white" valign="top">
				<font face="arial" style="font-size:12px;">2394988200&nbsp;
			</font></td>
		</tr>
	</tbody></table>
</td><td valign="top">
	<table width="320" border="1" cellpadding="1" cellspacing="0" bgcolor="black">
		<tbody><tr>
			<td width="320" colspan="2" bgcolor="white" valign="top">
				<font face="arial" style="font-size:12px;">E3 DESIGN GROUP INC&nbsp;
			</font></td>
		</tr><tr>
			<td width="85" bgcolor="#f7f3f7" valign="top">
				<font face="arial" style="font-size:12px;"><b>Attn:</b>
			</font></td>
			<td width="245" bgcolor="white" valign="top">
				<font face="arial" style="font-size:12px;">Stephanie Gray&nbsp;
			</font></td>
		</tr><tr>
			<td width="85" bgcolor="#f7f3f7" valign="top">
				<font face="arial" style="font-size:12px;"><b>Office:</b>
			</font></td>
			<td width="245" bgcolor="white" valign="top">
				<font face="arial" style="font-size:12px;">(239) 949-2405&nbsp;
			</font></td>
		</tr><tr>
			<td width="85" bgcolor="#f7f3f7" valign="top">
				<font face="arial" style="font-size:12px;"><b>Fax:</b>
			</font></td>
			<td width="245" bgcolor="white" valign="top">
				<font face="arial" style="font-size:12px;">(239) 949-3702&nbsp;
			</font></td>
		</tr><tr>
			<td width="85" bgcolor="#f7f3f7" valign="top">
				<font face="arial" style="font-size:12px;"><b>Vendor #:</b>
			</font></td>
			<td width="245" bgcolor="white" valign="top">
				<font face="arial" style="font-size:12px;">1139451&nbsp;
			</font></td>
		</tr>
	</tbody></table>
</td></tr></tbody></table>
<table width="660" border="0" cellpadding="1" cellspacing="0">
<tbody><tr><td><font face="arial" size="1">&nbsp;</font></td></tr>
<tr><td align="center"><font face="arial" style="font-size:12px;">Please direct questions regarding the following tasks to: Melvin Jordan&nbsp;</font></td>
</tr></tbody></table>
<table width="660" border="1" cellpadding="1" cellspacing="0" bgcolor="black">
<tbody><tr>
<td width="85" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>Phone:</b></font></td>
<td width="245" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">2392810201&nbsp;</font></td>
<td width="85" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>Fax:</b></font></td>
<td width="245" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td width="85" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>Email:</b></font></td>
<td width="245" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">MelvinJordan@wcicommunities.com&nbsp;</font></td>
</tr>
</tbody></table>
<table width="660" border="0" cellpadding="1" cellspacing="0">
	<tbody><tr><td><font face="arial" size="1">&nbsp;</font></td></tr>
	<tr><td><font face="arial" style="font-size:14px;"><b>JOB INFORMATION:</b></font></td></tr>
</tbody></table>
<table width="660" border="1" cellpadding="1" cellspacing="0" bgcolor="black">
	<tbody><tr>
		<td width="80" bgcolor="#f7f3f7" align="left" valign="middle"><font face="arial" style="font-size:12px;"><b>Order #:</b></font></td>
		<td width="140" bgcolor="white" align="left" valign="middle"><font face="arial" style="font-size:12px;">2436809-000</font></td>
		<td width="80" bgcolor="#f7f3f7" valign="middle"><font face="arial" style="font-size:12px;"><b>Job Name:</b></font></td>
		<td width="140" bgcolor="white" valign="middle"><font face="arial" style="font-size:12px;">26500250519 - 10342 FONTANELLA DR - 4163&nbsp;</font></td>
		<td width="80" bgcolor="#f7f3f7" valign="middle"><font face="arial" style="font-size:12px;"><b>Plan:</b></font></td>
		<td width="140" bgcolor="white" valign="middlep"><font face="arial" style="font-size:12px;">4163&nbsp;</font></td>
	</tr>
	<tr>
		<td width="80" bgcolor="#f7f3f7" valign="middle"><font face="arial" style="font-size:12px;"><b>Date Needed:</b></font></td>
		<td width="140" bgcolor="white" valign="middle"><font face="arial" style="font-size:12px;">08/14/2017</font></td>
		<td width="80" bgcolor="#f7f3f7" valign="middle" rowspan="2"><font face="arial" style="font-size:12px;"><b>Job Address:</b></font></td>
		<td width="140" bgcolor="white" valign="middle" rowspan="2"><font face="arial" style="font-size:12px;">10342 FONTANELLA DR<br>FORT MYERS, FL&nbsp;&nbsp;33913<br></font></td>
		<td width="80" bgcolor="#f7f3f7" valign="middle"><font face="arial" style="font-size:12px;"><b>Elev/Swing:</b></font></td>
		<td width="140" bgcolor="white" valign="middle"><font face="arial" style="font-size:12px;">R/R&nbsp;</font></td>
	</tr>
	<tr>
		<td width="80" bgcolor="#f7f3f7" valign="middle"><font face="arial" style="font-size:12px;"><b>Date&nbsp;Completed:</b></font></td>
		<td width="140" bgcolor="white" valign="middle"><font face="arial" style="font-size:12px;">
08/15/2017
		&nbsp;</font></td>
		<td width="80" bgcolor="#f7f3f7" valign="middle"><font face="arial" style="font-size:12px;"><b>Lot/Block:</b></font></td>
		<td width="140" bgcolor="white" valign="middler"><font face="arial" style="font-size:12px;">0519/&nbsp;</font></td>
	</tr>
	<tr>
		<td width="80" bgcolor="#f7f3f7" valign="middle"><font face="arial" style="font-size:12px;"><b>PO Date:</b></font></td>
		<td width="140" colspan="5" bgcolor="white" valign="middle"><font face="arial" style="font-size:12px;">07/17/2017</font></td>
	</tr>
</tbody></table>
<table width="660" border="0" cellpadding="1" cellspacing="0">
<tbody><tr><td><font face="arial" size="1">&nbsp;</font></td></tr>
<tr><td><font face="arial" style="font-size:14px;"><b>REQUEST INFORMATION:</b></font></td></tr>
</tbody></table>
<table width="660" border="1" cellpadding="1" cellspacing="0" bgcolor="black">
<tbody><tr><td bgcolor="white">
<table width="660" border="0" cellpadding="0" cellspacing="0" bgcolor="black">
<tbody><tr>
<th style="border-bottom: 1px solid black; border-right: 1px solid black;" width="409" align="left" bgcolor="#f7f3f7"><font face="arial" style="font-size:12px;">&nbsp;&nbsp;ITEM DESCRIPTION</font></th>
<th style="border-bottom: 1px solid black; border-right: 1px solid black;" width="49" align="center" bgcolor="#f7f3f7"><font face="arial" style="font-size:12px;">&nbsp;&nbsp;&nbsp;&nbsp;QTY<br>&nbsp;ORDERED&nbsp;</font></th>
<th style="border-bottom: 1px solid black; border-right: 1px solid black;" width="49" align="center" bgcolor="#f7f3f7"><font face="arial" style="font-size:12px;">&nbsp;&nbsp;&nbsp;&nbsp;QTY<br>&nbsp;RECEIVED&nbsp;</font></th>
<th style="border-bottom: 1px solid black; border-right: 1px solid black;" width="99" align="center" bgcolor="#f7f3f7"><font face="arial" style="font-size:12px;">UNIT PRICE</font></th>
<th style="border-bottom: 1px solid black;" width="100" align="center" bgcolor="#f7f3f7"><font face="arial" style="font-size:12px;">TOTAL</font></th>
</tr>
<tr>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;Duct Testing [1139451 - 2436809-000 - 3225120][UB]</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
</tr>
<tr>
<td style="border-right: 1px solid black;" valign="top" align="left" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;HVAC - DUCT TESTING - 0.001</font></td>
<td style="border-right: 1px solid black;" valign="top" align="center" bgcolor="white"><font face="arial" style="font-size:12px;">1</font></td>
<td style="border-right: 1px solid black;" valign="top" align="center" bgcolor="white"><font face="arial" style="font-size:12px;">1</font></td>
<td style="border-right: 1px solid black;" valign="top" align="right" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;$
175.00
&nbsp;</font></td>
<td valign="top" align="right" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;$
175.00
&nbsp;</font></td>
</tr>
<tr>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
</tr>
<tr>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
</tr>
<tr>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" align="right" bgcolor="white"><font face="arial" style="font-size:12px;">Subtotal: &nbsp;</font></td>
<td align="right" bgcolor="white"><font face="arial" style="font-size:12px;">$
175.00
&nbsp;</font></td>
</tr>
<tr>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" align="right" bgcolor="white"><font face="arial" style="font-size:12px;">Tax Total: &nbsp;</font></td>
<td align="right" bgcolor="white"><font face="arial" style="font-size:12px;">$
0.00
&nbsp;</font></td>
</tr>
<tr>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td style="border-right: 1px solid black;" align="right" bgcolor="white"><font face="arial" style="font-size:12px;"><b>Total: &nbsp;</b></font></td>
<td align="right" bgcolor="white"><font face="arial" style="font-size:12px;">$
175.00
&nbsp;</font></td>
</tr>
</tbody></table>
</td></tr>
</tbody></table>
<table width="660" border="0" cellpadding="1" cellspacing="0">
<tbody><tr><td><font face="arial" size="1">&nbsp;</font></td></tr>
<tr>
<td align="left"><font face="arial" style="font-size:12px;">Submitted by:&nbsp;Melvin Jordan</font></td>
<td align="right"><font face="arial" style="font-size:12px;">08/15/2017  4:13:33 AM</font></td>
</tr>
</tbody></table>
<table width="660" border="0" cellpadding="1" cellspacing="0">
<tbody><tr><td align="center"><font face="arial" size="1">Powered by Hyphen Solutions - http://www.hyphensolutions.com </font></td></tr>
</tbody></table>
</td></tr>
</tbody></table>';
    private $test_bodytext1 = '<meta http-equiv="Content-Type" content="text/html; charset=windows-1252"><title>BuildPro:  Job Management</title><table width="100%" border="0" cellpadding="0" cellspacing="0">
<tbody><tr><td valign="top" align="center">
<table width="660" border="1" cellpadding="1" cellspacing="0" bgcolor="black">
<tbody><tr><td bgcolor="#f7f3f7">
<table width="660" border="0" cellpadding="0" cellspacing="0">
<tbody><tr>
<td width="200" valign="top" align="left"><font face="arial" style="font-size:12px;"><b>&nbsp;<br>ARTESIA VILLA SF34 AA&nbsp;</b></font></td>
<td width="260" valign="top" align="center"><font face="arial" style="font-size:15px;" nowrap=""><b>WCI Communities Inc</b><br><b>Purchase Order Request</b>&nbsp;</font></td>
<td width="200" valign="top" align="right"><font face="arial" style="font-size:12px;">
<table border="0" cellpadding="1" cellspacing="0">
<tbody><tr><td align="left" valign="top"><font face="arial" style="font-size:12px;"><b>ORDER #:</b></font></td><td align="right"><font face="arial" style="font-size:12px;">&nbsp;2437015-000&nbsp;</font></td></tr>
<tr><td align="left" valign="top"><font face="arial" style="font-size:12px;"><b>COST CODE:</b></font></td><td align="right"><font face="arial" style="font-size:12px;">&nbsp;3225120&nbsp;</font></td></tr>
</tbody></table>
</font></td>
</tr>
</tbody></table>
</td></tr>
</tbody></table>
<table width="660" border="0" cellpadding="1" cellspacing="0">
<tbody><tr><td><font face="arial" size="1">&nbsp;</font></td></tr>
<tr><td><font face="arial" style="font-size:14px;"><b>CONTACT INFORMATION:</b></font></td></tr>
</tbody></table>
<table width="660" border="1" cellpadding="1" cellspacing="0" bgcolor="black">
<tbody><tr>
<td width="85" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>FROM:</b></font></td>
<td width="245" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">WCI Communities Inc - ARTESIA VILLA SF34 AA&nbsp;<br></font></td>
<td width="85" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>TO:</b></font></td>
<td width="245" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">E3 DESIGN GROUP INC&nbsp;</font></td>
</tr>
<tr>
<td width="85" bgcolor="#f7f3f7" valign="top" rowspan="2"><font face="arial" style="font-size:12px;"><b>ADDRESS:</b></font></td>
<td width="245" bgcolor="white" valign="top" rowspan="2"><font face="arial" style="font-size:12px;">ARTESIA VILLA - SF34\' AA<br>NAPLES, FL&nbsp;&nbsp;34114&nbsp;</font></td>
<td width="85" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>ATTN:</b></font></td>
<td width="245" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">Stephanie Gray&nbsp;</font></td>
</tr>
<tr>
<td width="85" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>OFFICE:</b></font></td>
<td width="245" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">(239) 949-2405&nbsp;</font></td>
</tr>
<tr>
<td width="85" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>MAIN:</b></font></td>
<td width="245" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">2394988200&nbsp;</font></td>
<td width="85" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>FAX:</b></font></td>
<td width="245" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">(239) 949-3702&nbsp;</font></td>
</tr>
<tr>
<td width="85" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>FAX:</b></font></td>
<td width="245" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">2394988200&nbsp;</font></td>
<td width="85" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>VENDOR #:</b></font></td>
<td width="245" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">1139451&nbsp;</font></td>
</tr>
</tbody></table>
<table width="660" border="0" cellpadding="1" cellspacing="0">
<tbody><tr><td><font face="arial" size="1">&nbsp;</font></td></tr>
<tr><td align="center"><font face="arial" style="font-size:12px;">Please direct questions regarding the following tasks to: Jeff Beach&nbsp;</font></td>
</tr></tbody></table>
<table width="660" border="1" cellpadding="1" cellspacing="0" bgcolor="black">
<tbody><tr>
<td width="85" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>PHONE:</b></font></td>
<td width="245" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">239-498-8200&nbsp;</font></td>
<td width="85" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>FAX:</b></font></td>
<td width="245" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td width="85" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>EMAIL:</b></font></td>
<td width="245" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">Jeffbeach@wcicommunities.com&nbsp;</font></td>
</tr>
</tbody></table>
<table width="660" border="0" cellpadding="1" cellspacing="0">
<tbody><tr><td><font face="arial" size="1">&nbsp;</font></td></tr>
<tr><td><font face="arial" style="font-size:14px;"><b>JOB INFORMATION:</b></font></td></tr>
</tbody></table>
<table width="660" border="1" cellpadding="1" cellspacing="0" bgcolor="black">
<tbody><tr>
<td width="80" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>DATE NEEDED:</b></font></td>
<td width="140" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">08/18/2017</font></td>
<td width="80" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>JOB NAME:</b></font></td>
<td width="140" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">16020150175 - 1615 PARNELL COURT - 3103&nbsp;</font></td>
<td width="80" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>PLAN:</b></font></td>
<td width="140" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">3103&nbsp;</font></td>
</tr>
<tr>
<td width="80" bgcolor="#f7f3f7" valign="top" rowspan="2"><font face="arial" style="font-size:12px;"><b>PO DATE:</b></font></td>
<td width="140" bgcolor="white" valign="top" rowspan="2"><font face="arial" style="font-size:12px;">08/11/2017</font></td>
<td width="80" bgcolor="#f7f3f7" valign="top" rowspan="2"><font face="arial" style="font-size:12px;"><b>JOB ADDRESS:</b></font></td>
<td width="140" bgcolor="white" valign="top" rowspan="2"><font face="arial" style="font-size:12px;">1615 PARNELL COURT<br>NAPLES, FL&nbsp;&nbsp;34113<br>&nbsp;</font></td>
<td width="80" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>ELEV/SWING:</b></font></td>
<td width="140" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">R/R&nbsp;</font></td>
</tr>
<tr>
<td width="80" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>LOT/BLOCK:</b></font></td>
<td width="140" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">0175/&nbsp;</font></td>
</tr>
<tr>
<td width="80" bgcolor="#f7f3f7" valign="top"><font face="arial" style="font-size:12px;"><b>MAP LOCATION:</b></font></td>
<td width="140" bgcolor="white" valign="top"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td width="220" bgcolor="#f7f3f7" valign="top" align="right" colspan="2"><font face="arial" style="font-size:12px;"><b>BACKORDER POLICY:</b></font></td>
<td width="140" bgcolor="white" valign="top" colspan="2"><font face="arial" style="font-size:12px;">Ship and Back Order&nbsp;</font></td>
</tr>
</tbody></table>
<table width="660" border="0" cellpadding="1" cellspacing="0">
<tbody><tr><td><font face="arial" size="1">&nbsp;</font></td></tr>
<tr><td><font face="arial" style="font-size:14px;"><b>REQUEST INFORMATION:</b></font></td></tr>
</tbody></table>
<table width="660" border="1" cellpadding="1" cellspacing="0" bgcolor="black">
<tbody><tr><td bgcolor="white">
<table width="660" border="0" cellpadding="0" cellspacing="0" bgcolor="black">
<tbody><tr>
<th width="49" bgcolor="#f7f3f7"><font face="arial" style="font-size:12px;"><u>QTY</u></font></th>
<th width="1" bgcolor="black"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></th>
<th width="409" bgcolor="#f7f3f7"><font face="arial" style="font-size:12px;"><u>DESCRIPTION</u></font></th>
<th width="1" bgcolor="black"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></th>
<th width="99" bgcolor="#f7f3f7"><font face="arial" style="font-size:12px;"><u>UNIT PRICE</u></font></th>
<th width="1" bgcolor="black"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></th>
<th width="100" bgcolor="#f7f3f7"><font face="arial" style="font-size:12px;"><u>TOTAL</u></font></th>
</tr>
<tr>
<td colspan="7" bgcolor="black"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
</tr>
<tr>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td bgcolor="black" width="1"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;Duct Testing [1139451 - 2437015-000 - 3225120][UB]</font></td>
<td bgcolor="black" width="1"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td bgcolor="black" width="1"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
</tr>
<tr>
<td valign="top" align="center" bgcolor="white"><font face="arial" style="font-size:12px;">1</font></td>
<td bgcolor="black" width="1"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
<td valign="top" align="left" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;DUCT TESTING - 0.001</font></td>
<td bgcolor="black" width="1"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
<td valign="top" align="right" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;$175.00&nbsp;</font></td>
<td bgcolor="black" width="1"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
<td valign="top" align="right" bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;$175.00&nbsp;</font></td>
</tr>
<tr>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td bgcolor="black" width="1"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td bgcolor="black" width="1"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td bgcolor="black" width="1"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
</tr>
<tr>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td bgcolor="black" width="1"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td bgcolor="black" width="1"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td bgcolor="black" width="1"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
</tr>
<tr>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td bgcolor="black" width="1"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td bgcolor="black" width="1"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
<td align="right" bgcolor="white"><font face="arial" style="font-size:12px;">Subtotal: &nbsp;</font></td>
<td bgcolor="black" width="1"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
<td align="right" bgcolor="white"><font face="arial" style="font-size:12px;">$175.00&nbsp;</font></td>
</tr>
<tr>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td bgcolor="black" width="1"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td bgcolor="black" width="1"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
<td align="right" bgcolor="white"><font face="arial" style="font-size:12px;">Tax Total: &nbsp;</font></td>
<td bgcolor="black" width="1"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
<td align="right" bgcolor="white"><font face="arial" style="font-size:12px;">$0.00&nbsp;</font></td>
</tr>
<tr>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td bgcolor="black" width="1"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
<td bgcolor="white"><font face="arial" style="font-size:12px;">&nbsp;</font></td>
<td bgcolor="black" width="1"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
<td align="right" bgcolor="white"><font face="arial" style="font-size:12px;"><b>Total: &nbsp;</b></font></td>
<td bgcolor="black" width="1"><font face="arial" style="font-size:1px;"><img src="https://www.hyphensolutions.com/build/images/spacer.gif" width="1" height="1" vspace="0"></font></td>
<td align="right" bgcolor="white"><font face="arial" style="font-size:12px;"><b>$175.00&nbsp;</b></font></td>
</tr>
</tbody></table>
</td></tr>
</tbody></table>
<table width="660" border="0" cellpadding="1" cellspacing="0">
<tbody><tr><td><font face="arial" size="1">&nbsp;</font></td></tr>
<tr>
<td align="left"><font face="arial" style="font-size:12px;">Submitted by:&nbsp;Matt Mahoney</font></td>
<td align="right"><font face="arial" style="font-size:12px;">08/11/2017 10:51:08 AM</font></td>
</tr>
</tbody></table>
<table width="660" border="0" cellpadding="1" cellspacing="0">
<tbody><tr><td align="center"><font face="arial" size="1">Powered by Hyphen Solutions - http://www.hyphensolutions.com </font></td></tr>
</tbody></table>
</td></tr>
</tbody></table>';

}
