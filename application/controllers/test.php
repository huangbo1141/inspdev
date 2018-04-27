<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

include_once APPPATH . '/third_party/imap/push/push_config.php';

class Test extends CI_Controller {

    public function __construct() {
        parent::__construct();
        //        $this->load->library('user_agent');
        $this->load->library('holiday');
        $this->load->library('m_checkwci');
        $this->load->helper('directory');

        $this->load->model('user_model');
        $this->load->model('utility_model');
        $this->load->model('datatable_model');
    }
    
    public function link1(){
        echo "Hello";
    }
}