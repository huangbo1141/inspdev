<?php

class Utility_model extends CI_Model    {

    private $encrypt_key = "_inspection_e3_sciences_";
    
    public function __construct() {
        parent::__construct();
    }
    
    public function start() {
        $this->db->trans_start();
    }
    public function complete() {
        $this->db->trans_complete();
    }
    
    public function begin() {
        $this->db->trans_begin();
    }
    public function commit() {
        $this->db->trans_commit();
    }
    public function rollback() {
        $this->db->trans_rollback();
    }
    
    public function escape($str) {
        return $this->db->escape($str);
    }
    
    public function insert($table, $data){
        return $this->db->insert($table, $data);
    }
    public function new_id() {
        return $this->db->insert_id();
    }
    
    public function update($table, $data, $cond){
        return $this->db->update($table, $data, $cond);
    }
    public function get($table, $cond){
        $query = $this->db->get_where($table, $cond);
        return $query->row_array();
    }
    public function get__by_sql($sql){
        $query = $this->db->query($sql);
        return $query->row_array();
    }
    public function get_list($table, $cond=''){
        $query=null;
        if (is_array($cond)){
            $query = $this->db->get_where($table, $cond);
        } else {
            $query = $this->db->get($table);
        }
        return $query->result_array();
    }
    public function get_count($table, $cond){
        $query = $this->db->get_where($table, $cond);
        return $query->num_rows();
    }
    public function get_count__by_sql($sql){
        $query = $this->db->query($sql);
        return $query->num_rows();
    }
    
    public function get_list__by_order($table, $cond, $order){
        foreach ($order as $row) {
            $this->db->order_by($row['name'], $row['order']);
        }
        
        $query = $this->db->get_where($table, $cond);
        return $query->result_array();
    }
    
    public function get_list__by_sql($sql){
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    
    public function delete($table, $cond){
       return $this->db->delete($table , $cond);
    }
 
    public function get_field__by_sql($sql, $field){
        $query = $this->db->query($sql);
        $row = $query->row_array();
        if ($row){
            return isset($row[$field]) ? $row[$field] : '';
        }
        
        return '';
    }
    
    public function encode($key) {
        return base64_encode($key . $this->encrypt_key);
    }
    
    public function decode($key) {
        $key = base64_decode($key);
        $enc_position = strpos($key, $this->encrypt_key);
        $key = substr($key, 0, $enc_position-2);
        
        return $key;
    }
    
    public function escape_filename($filename) {
        $filename = str_replace("/", "-", $filename);
        return $filename;
    }
    
    public function has_permission($permission, $kind=1) {
        switch ($kind) {
            case 1:
                if ($permission==1) {
                    return true;
                }
                break;
                
            case 2:
                if ($permission==1) {
                    return true;
                }
                if ($permission==2) {
                    return true;
                }
                break;
                
            case 3:
                if ($permission==1) {
                    return true;
                }
                if ($permission==2) {
                    return true;
                }
                if ($permission==3) {
                    return true;
                }
                break;
                
            case 4:
                if ($permission==1) {
                    return true;
                }
                if ($permission==2) {
                    return true;
                }
                if ($permission==3) {
                    return true;
                }
                if ($permission==4) {
                    return true;
                }
                break;
                
            case 0:
                if ($permission==0) {
                    return true;
                }
                break;
        }
        
        return false;
    }

}
