<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Welcome extends CI_Controller {

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     * 	- or -  
     * 		http://example.com/index.php/welcome/index
     * 	- or -
     * Since this controller is set as the default controller in 
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see http://codeigniter.com/user_guide/general/urls.html
     */
    public function index() {
//        $this->load->view('welcome_message');
//        $this->output->set_header("HTTP/1.0 200 OK");
//        $this->output->set_header("HTTP/1.1 200 OK");
//        $this->output->set_header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
//        $this->output->set_header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()).' GMT');
//        $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate");
//        $this->output->set_header("Cache-Control: post-check=0, pre-check=0");
//        $this->output->set_header("Pragma: no-cache");        
        
        redirect(base_url() . "user/login.html");
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
