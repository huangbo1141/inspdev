<?php

$basePath = base_url();
$resPath = $basePath . "resource/";
$message = '';
$user_permission=0;

$user_name='';

if ($this->session->userdata('message')){
    $message = $this->session->userdata('message');
    $this->session->set_userdata('message', '');
}

if ($this->session->userdata('permission')){
    $user_permission=$this->session->userdata('permission');
}

if ($this->session->userdata('user_name')){
    $user_name=$this->session->userdata('user_name');
}
