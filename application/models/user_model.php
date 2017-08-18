<?php

class User_model extends CI_Model    {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function get_user__by_email($table, $email, $password=''){
        $cond = null;
        if ($password==''){
            $cond = array('email'=>$email);
        }else{
            $cond = array('email'=>$email, 'password'=>$password);
        }
        
        $query = $this->db->get_where(DB_PREFIX . $table, $cond);
        return $query->row_array();
    }
    
    public function get_user__by_id($table, $id){
        $query = $this->db->get_where(DB_PREFIX . $table, array('id'=>$id));
        return $query->row_array();
    }

    public function insert_user($table, $data){
        return $this->db->insert(DB_PREFIX . $table, $data);
    }
    
    public function update_user__by_id($table, $id, $data){
        $this->db->where('id', $id);
        return $this->db->update(DB_PREFIX . $table, $data);
    }

    public function update_user__by_email($table, $email, $data){
        $this->db->where('email', $email);
        return $this->db->update(DB_PREFIX . $table, $data);
    }

    public function delete_user($table, $data){
        return $this->db->delete(DB_PREFIX . $table, $data);
    }
    
}
