<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH.'/third_party/mpdf/mpdf.php';

class M_pdf {

    public $pdf;

    public function initialize($page_size="A4-L", $orientation="L"){
        $this->pdf = new mPDF("UTF-8", $page_size, 0, '', 10, 10, 10, 10, 5, 5, $orientation);
    }
}
