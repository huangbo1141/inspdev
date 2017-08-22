<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH.'/third_party/imap/imap.php';

class M_checkwci {

    public $wci;

    public function initialize(){

        $this->wci = new CheckWci();
    }
}
