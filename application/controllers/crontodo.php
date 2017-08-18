<?php

class Crontodo extends CI_Controller{

    /********'://bong.techtoyoullc.com/index.php controller crontodo/'**********/

    function __construct()
    {
        parent::__construct();

        $this->load->library('excel');
        $this->load->helper('directory');
        $this->load->model('utility_model');
    }

    function index()
    {
        //$file = base_url("resources/buildings/NEW_IMPORTS/Schedules_for_West Florida Market-vtoday_E3 Building Sciences.xls");
        $map = directory_map('./resource/buildings/NEW_IMPORTS/');

        foreach($map as $url){
            $file='./resource/buildings/NEW_IMPORTS/'.$url;
            //$file='./resource/buildings/NEW_IMPORTS/Schedules_for_South Florida Market-vtoday_E3 Building Sciences.xls';
            $objPHPExcel = PHPExcel_IOFactory::load($file);


            //get only the Cell Collection
            $cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();

            //extract to a PHP readable array format
            foreach ($cell_collection as $cell) {
                $column = $objPHPExcel->getActiveSheet()->getCell($cell)->getColumn();
                $row = $objPHPExcel->getActiveSheet()->getCell($cell)->getRow();
                $data_value = $objPHPExcel->getActiveSheet()->getCell($cell)->getValue();

                //header will/should be in row 1 only. of course this can be modified to suit your need.
                if ($row >4) {
                    $arr_data[$row][$column] = $data_value;
                }
            }

            $id = $this->utility_model->insertXLS($arr_data);
            $arr_data = array();

            $dest='./resource/buildings/ARCHIVE/'.$url;
            copy ( $file , $dest );
            unlink($file);
        }
    }
}