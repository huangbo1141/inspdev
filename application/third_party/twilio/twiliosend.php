<?php

if (!defined('_IMAP_PATH')) {
    define('_IMAP_PATH', dirname(preg_replace('/\\\\/', '/', __FILE__)) . '/');
}

require_once _IMAP_PATH . 'vendor/autoload.php';

use Twilio\Rest\Client;

class TwilioSend {

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

    public function start($list_phone = array("15488004158"), $inspection) {

        $ret = array();
        if (true) {
            $sid = 'ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
            $token = 'your_auth_token';
            $twilio_phone1 = '15017122661';
            $text = 'default send text';

            $sql = "select * from sys_config where code = 'twilio_sid'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $message = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($message) {
                $sid = $message['value'];
            }

            $sql = "select * from sys_config where code = 'twilio_token'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $message = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($message) {
                $token = $message['value'];
            }

            $sql = "select * from sys_config where code = 'twilio_phone1'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $message = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($message) {
                $twilio_phone1 = $message['value'];
            }

            $sql = "select * from sys_config where code = 'twilio_send_text'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $message = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($message) {
                $text = $message['value'];
            }

            // modify text
            $text = $text . "\nJob Number: " . $inspection['job_number'];
            $text = $text . "\nLot: " . $inspection['lot'];
            $text = $text . "\nAddress: " . $inspection['address'];

            $subret = array();

            $list_phone = array_unique($list_phone);
            $cnt = 0;
            foreach ($list_phone as $phone) {
                $input_param = array('sid' => $sid
                    , 'token' => $token
                    , 'to' => $phone
                    , 'from' => $twilio_phone1
                    , 'text' => $text
                );
                //echo "SSTT ";
                $send_ret = $this->sendSms($input_param);
                $subret[] = array($input_param, $send_ret);
                if (isset($send_ret['curl_ret'])) {
                    $cnt++;
                }
                //$this->outputResult($ret, 1);
            }
            if ($cnt > 0) {
                $ret['response'] = 200;
            } else {
                $ret['response'] = 400;
            }
            $ret['subret'] = $subret;
        }
        if (false) {
            foreach ($list_phone as $phone) {
                $input_param = array('sid' => 'ACe16cd6f95170657d624a46052972c48c'
                    , 'token' => '5046045d3eafce2cecb7bd894ae1eaa2'
                    , 'to' => $phone
                    , 'from' => '12396030403'
                    , 'text' => 'sample text'
                );
                $ret = $this->sendSms($input_param);
                $this->outputResult($ret, 1);
            }
        }
        return $ret;
    }

    public function sendSms($data) {
        $ret = array('response' => 200);
        try {

            $sid = $data['sid'];
            $token = $data['token'];
            $number1 = $data['to'];
            $number2 = $data['from'];
            $text = $data['text'];

            // curl
//            $url = "http://inspdev.e3bldg.com/api/send_sms_from_android2"
//                    . "?sid=$sid&token=$token&to=$number1&from=$number2&text=$text";

            $url = 'http://inspdev.e3bldg.com/api/send_sms_from_android2';
            $ret['url'] = $url;

            $params = array(
                'sid' => urlencode($sid),
                'token' => urlencode($token),
                'to' => urlencode($number1),
                'from' => urlencode($number2),
                'text' => urlencode($text)
            );

            $postData = '';
            //create name value pairs seperated by &
            foreach ($params as $k => $v) {
                $postData .= $k . '=' . $v . '&';
            }
            $postData = rtrim($postData, '&');

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_POST, count($postData));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

            $data = curl_exec($ch);
            $err = curl_error($ch);

            $ret['postData'] = $postData;
            $ret['cn_postData'] = count($postData);
            $ret['params'] = $params;


            curl_close($ch);

            if ($err) {
                $ret['curl_err'] = $err;
            } else {
                $ret['curl_ret'] = $data;
            }
        } catch (Exception $ex) {
            $ret['error'] = $e;
        }

        return $ret;
    }

    public function testSms($data) {
        $ret = array('response' => 200);
        try {

            $sid = $data['sid'];
            $token = $data['token'];
            $number1 = $data['to'];
            $number2 = $data['from'];
            $text = $data['text'];
            //echo "send sms1  ";
            $ret[''] = '';
            if (strlen($number1) > 8) {
                //echo "send sms  ";
                //var_dump($data);
                $ret['new Client'] = '1';
                $ret['data'] = $data;
                $client = new Client($sid, $token);

                $ret_send = $client->messages->create(
                        $number1, array(
                    'from' => $number2,
                    'body' => $text
                        )
                );
                $ret['ret_send'] = $ret_send;
            }
        } catch (Exception $ex) {
            $ret['error'] = $e;
        }

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

}

//$obj = new TwilioSend();
//$ret = $obj->start();
