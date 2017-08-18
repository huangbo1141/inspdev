<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of phpmailerex
 *
 * @author Cheng
 */
class Phpmailerex {
    
    public $mailer;
   
    public function __construct() {
        //nothing
        require_once('PHPMailerAutoload.php');
    }    
}
