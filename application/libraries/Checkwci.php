<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once APPPATH . '/third_party/imap/vendor/autoload.php';
include_once APPPATH . '/third_party/imap/push/function.php';
require_once APPPATH . '/third_party/imap/push/push_config.php';

use Ddeboer\Imap\Server;
use Ddeboer\Imap\SearchExpression;
use Ddeboer\Imap\Search\Email\To;
use Ddeboer\Imap\Search\Text\Body;
use Ddeboer\Imap\Search\Email\FromAddress;
use PHPHtmlParser\Dom;

class CheckWCi {

    private $db_host = "localhost";
    private $db_name = "reward";
    private $db_username = "root";
    private $db_password = "";
    private $config;
    private $last_req_date = "";

    function __construct() {
        global $g_config;
        $config = $g_config;
        $this->db_host = $config['host'];
        $this->db_name = $config['dbname'];
        $this->db_username = $config['username'];
        $this->db_password = $config['password'];
        // Create a connection to the database.
        $this->pdo = new PDO(
                'mysql:host=' . $this->db_host . ';dbname=' . $this->db_name, $this->db_username, $this->db_password, array());

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->query('SET NAMES utf8mb4');

        $this->config = $config;
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

    public function addTables($input, $action = "add") {
        $ret = array();
        global $g_ins_admin;
        global $g_ins_building;
        global $g_ins_inspection_requested;

        $sql = "";
        $ret['response'] = 400;
        if (isset($input['ins_req'])) {
            $tdata = $input['ins_req'];
            $job_number = $tdata['job_number'];
            $sql = "select * from ins_inspection_requested where job_number = $job_number";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            $message = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($message) {
                $ret['job_number'] = $job_number;
                return $ret;
            }
        }


        try {
            $this->pdo->beginTransaction();
            if ($action == "add") {
                if (isset($input['ins_admin'])) {
                    $params = $g_ins_admin;
                    $tdata = $input['ins_admin'];
                    $insertData = extractAsArray($tdata, $params);
                    $sql = makeInsertDataSql($insertData, "ins_admin");
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute();

                    $id = $this->pdo->lastInsertId();
                    $builderid = $id;

                    $updatedata = array();
                    $updatedata['first_name'] = $builderid;
                    $sql = makeUpdateSql($updatedata, "ins_admin", array("id" => $builderid));
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute();

                    if (isset($input['ins_building'])) {
                        $params = $g_ins_building;
                        $tdata = $input['ins_building'];
                        $tdata['field_manager'] = $builderid . " WCI";
                        $insertData = extractAsArray($tdata, $params);
                        $sql = makeInsertDataSql($insertData, "ins_building");
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute();

                        $id = $this->pdo->lastInsertId();
                        $buildingid = $id;
                    }

                    if (isset($input['ins_req'])) {
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
                $ret['response'] = 200;
            }
            $this->pdo->commit();
        } catch (Exception $ex) {
            $this->pdo->rollBack();
            $ret['response'] = 102;
            $ret['sql'] = $sql;
            $ret['error'] = $ex;
        }
        $ret['action'] = $action;
        return $ret;
    }

    private $host = "secure.emailsrvr.com";
    private $user = "inspect@e3bldg.com";
    private $password = "sN>8KM)=";
    public $printmode = 0;
    public $printdetail = 0;
    private $testdata = 0;
    private $index_jobinfo = "JOB INFORMATION";
    private $index_jobinfo_found = 0;
    private $index_coninfo = "CONTACT INFORMATION:";
    private $index_coninfo_found = 0;

    public function resetValues() {
        $this->index_jobinfo_found = 0;
        $this->index_coninfo_found = 0;
    }

    public function start() {
        $ret = array();
        $ret['response'] = 400;
        if (true) {
            $ret = $this->fetchMessages();
//            $this->outputResult($ret, 2);
        }
        if (false) {
            $this->getLastRequestTime();
            echo $this->last_req_date;
        }
        if (false) {
            $data = $this->parseHtml($this->test_bodytext3);
            $ret = $this->addTables($data);
//            $this->outputResult($data, 2);
            $this->outputResult($ret, 2);
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
            $dom = new PHPHtmlParser\Dom;
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

        $pattern = array('<br>', '/\&nbsp;/i');
        $replace = array('', '');
        foreach ($input as $param => $value) {
            $tmp = trim($value);
            if (strlen($tmp) > 0) {
                //$ret = mysql_real_escape_string($tmp);
                $ret = false;

                if (is_bool($ret) && !$ret) {
                    //$datas[$param] = $tmp;
                    $datas[$param] = preg_replace($pattern, $replace, $tmp);
                } else {
                    $datas[$param] = $ret;
                }
            }
        }
        $i = 0;
        $datas = str_replace("<>", "", $datas, $i);
        $datas = str_replace("< />", "", $datas, $i);
        $datas = str_replace("\\'", "\'", $datas, $i);

        return $datas;
    }

    public function getInsertObjects($input) {
        $ins_building = array();
        // $params = array("tp_id", "tu_id");
        // $datas = extractAsArray($input, $params);
        $timestr = date("YmdHis", time());
        $ins_building['job_number'] = $input['jnum'];
        $ins_building['community'] = $input['community'];
        $ins_building['address'] = $input['caddress'];
        $ins_building['created_at'] = $timestr;
        $ins_building['updated_at'] = $timestr;
        //$ins_building['field_manager'] = '264 WCI';
        $ins_building['builder'] = '2';


        // $ins_community = array();
        // //$ins_community['community_id'] = '';
        // $ins_community['community_name'] = $input['community'];
        // $ins_community['city'] = $input['ccity'];
        // // $ins_community['region'] = '';
        // // $ins_community['builder'] = '';;
        // $ins_community['created_at'] = $timestr;
        // $ins_community['updated_at'] = $timestr;

        $ins_req = array();
        $ins_req['category'] = '3';
        $ins_req['job_number'] = $input['jnum'];
        $ins_req['created_at'] = $timestr;
        $ins_req['requested_at'] = $input['dneed'];
        $ins_req['time_stamp'] = $timestr;
        $ins_req['community_name'] = $input['community'];
        $ins_req['lot'] = $input['lot'];
        $ins_req['address'] = $input['jaddress'];
        $ins_req['city'] = $input['jcity'];
        $ins_req['status'] = '0';
        $ins_req['design_location'] = '';


        $ins_admin = array();
        $ins_admin['kind'] = '2';
        $ins_admin['email'] = $input['email'];
        //$ins_admin['firstname'] = ' ';
        $ins_admin['last_name'] = 'WCI';
        $ins_admin['address'] = $input['caddress'];
        $ins_admin['password'] = 'wci';
        $ins_admin['status'] = '0';
        $ins_admin['builder'] = '2';
        $ins_admin['allow_email'] = '1';
        $ins_admin['status'] = '0';
        $ins_admin['updated_at'] = $timestr;
        $ins_admin['created_at'] = $timestr;


        $ret = array();
        $ret['ins_admin'] = $this->filterArrayContent($ins_admin);
        $ret['ins_building'] = $this->filterArrayContent($ins_building);
        // $ret[] = $this->filterArrayContent($ins_community);
        $ret['ins_req'] = $this->filterArrayContent($ins_req);
        return $ret;
    }

    public function extractParams($input, $fields1, $fields2) {
        $ret = array();
        for ($i = 0; $i < count($fields1); $i++) {
            $key = $fields1[$i];
            $key_out = $fields2[$i];
            if (isset($input[$key])) {
                $ret[$key_out] = $input[$key];
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
        if (isset($ret['cfrom'])) {
            if ($printmode) {
                echo 'cfrom';
                echo $ret['cfrom'];
            }

            $pieces = explode("-", $ret['cfrom']);
            $ret['community'] = trim($pieces[0]);
        }
        if (isset($ret['jname'])) {
            $pieces = explode(" ", $ret['jname']);
            if ($printmode) {
                var_dump($input);
                var_dump($jobname);
                var_dump($pieces);
            }
            $ret['jnum'] = $pieces[0];
        }
        if (isset($ret['jaddress'])) {
            $pieces = explode(",", $ret['jaddress']);
            if (is_array($pieces) && count($pieces) > 0) {
                $istr = trim($pieces[count($pieces) - 1]);
                $ipieces = explode("&nbsp;", $istr);
                //var_dump($ipieces);
                $ret['jcity'] = $ipieces[0];
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
        $endP = "<br/>parseHtml Pagraph<br/>";
        $data = array();
        try {
            $dom = new PHPHtmlParser\Dom;
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
                if ($this->printmode) {
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
                        $this->outputResult($iterms, 1);


                        // email part table
                        $itable = $ptable1 = $bigtable->find('table')[$cnt - 1];
                        $ihtml = $itable->outerHtml();
                        if ($this->printdetail) {
                            echo $ihtml;
                        }
                        $iterms = $this->parseTable($ihtml, ["Email"], 'TD', false, false);
                        $data = array_merge($data, $iterms);
                        $this->outputResult($iterms, 1);

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
                        $this->outputResult($iterms, 1);
                        //$itable->find('font')
                        $this->index_coninfo_found = 1;
                    }
                }

                $cnt++;
            }

            if ($this->index_coninfo_found == 0) {
                // contact INFORMATION
                $itable = $ptable1 = $bigtable->find('table')[$jobinfo_index - 3];
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
                $this->outputResult($iterms, 1);

                $this->index_jobinfo_found = 1;
            }

            $data = $this->generateData($data);
            $data = $this->getInsertObjects($data);
        } catch (Exception $ex) {
            $data = array();
        }

        return $data;
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

        $server = new Server($host);

        // $connection is instance of \Ddeboer\Imap\Connection
        $connection = $server->authenticate($user, $password);
        $mailbox = $connection->getMailbox('INBOX');
        //$messages = $mailbox->getMessages();

        $search = new SearchExpression();
        $search->addCondition(new FromAddress('postmaster@hyphensolutions.net'));

        $messages = $mailbox->getMessages($search);
        $cnt = 0;
        $ret = array();
        foreach ($messages as $message) {
            $bodytext = $message->getBodyText();
            if ($this->printmode) {
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
            $input = $this->parseHtml($bodytext);
            if (is_array($input) && isset($input['ins_req'])) {
                $r1 = $this->addTables($input);
                $ret[] = $r1;
            }




            $cnt++;
            if ($cnt >= $limit) {
                break;
            }
        }
        return $ret;
    }

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