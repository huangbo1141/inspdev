<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH.'/third_party/twilio/twiliosend.php';

class M_twilio {

    public $wci;
    public $dbhost;
    public $dbname;
    public $dbpass;
    public $dbuser;

    public $mail_host ;
    public $mail_user ;
    public $mail_password ;

    public function setDbInfo($dbhost, $dbname, $dbuser, $dbpass)
    {
      $this->dbhost = $dbhost;
      $this->dbname = $dbname;
      $this->dbuser = $dbuser;
      $this->dbpass = $dbpass;
    }
    public function setMailInfo($host, $user, $pass)
    {
      $this->mail_host = $host;
      $this->mail_user = $user;
      $this->mail_password = $pass;
    }

    public function initialize(){

        $this->wci = new TwilioSend();
        $this->wci->db_host = $this->dbhost;
        $this->wci->db_name = $this->dbname;
        $this->wci->db_username = $this->dbuser;
        $this->wci->db_password = $this->dbpass;
        $this->wci->mail_host = $this->mail_host;
        $this->wci->mail_user = $this->mail_user;
        $this->wci->mail_password = $this->mail_password;
        $this->wci->initdb();
    }
}
