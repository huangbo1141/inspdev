<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 *  =======================================
 *  Author     : Muhammad Surya Ikhsanudin
 *  License    : Protected
 *  Email      : mutofiyah@gmail.com
 *
 *  Dilarang merubah, mengganti dan mendistribusikan
 *  ulang tanpa sepengetahuan Author
 *  =======================================
 */
require_once APPPATH."/third_party/US_Federal_Holidays.php";

class Holiday extends US_Federal_Holidays {
    public function __construct() {
        parent::__construct();
    }
    
}
?>
