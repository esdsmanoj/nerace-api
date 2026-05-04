<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Common_model extends CI_Model
{
    function get_data($table, $cond=null, $fields=null, $type=null){
        if(!empty($fields)){
            $fields = implode(', ', $fields);
        }
        else{
            $fields = '*';
        }
        $this->db->select($fields);
        $this->db->from($table);
        if(!empty($cond)){
            foreach($cond as $key => $val){
                $this->db->where($key,$val);
            }
        }
        
		$query =$this->db->get();
             
		if ($query->num_rows()) {
            if(!empty($type))
			    return $query->result();
            else
                return $query->row();
		} else {
			return FALSE;
		}
    }
    
    function update_data($table, $data, $cond){
        foreach($cond as $key=>$val){
            $this->db->where($key,$val);
        }
        $this->db->update($table, $data);
    }

    function insert_data($table, $data){
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }
}