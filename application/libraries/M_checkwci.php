<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH.'/third_party/imap/imap.php';

class M_checkwci {

    public $wci;
    public $host;
    public $dbname;
    public $dbpass;
    public $dbuser;

    public function setDbInfo($host, $dbname, $dbuser, $dbpass)
    {
      $this->host = $host;
      $this->dbname = $dbname;
      $this->dbuser = $dbuser;
      $this->dbpass = $dbpass;
    }

    public function initialize(){

        $this->wci = new CheckWci($this->host,$this->dbname,$this->dbuser,$this->dbpass);
    }
}
