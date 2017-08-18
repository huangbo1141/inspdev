<?php

/**
 * Database Model for DataTable Plugin.
 * 
 * @author CJH
 */

class Datatable_model extends CI_Model    {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get Count of Result.
     * 
     * @param string $sql SQL
     * @return long
     */
    public function get_count($sql){
        $query = $this->db->query($sql);
        $result = $query->row_array();
        return  $result["count(*)"];
    }
    
    /**
     * Get Result List.
     * 
     * @param string $sql SQL
     * @return array
     */
    public function get_content($sql){
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return $result;
    }
}
