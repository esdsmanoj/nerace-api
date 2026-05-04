<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_db extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $headers_data = $this->input->request_headers();
        
        // $this->load->model('Masters_model');
    }
    /***********************Working APIs: Start***********************/

    // public function api_response($data, $status = null, $token = null)
    // {
    //     // echo $this->base_path;exit;
    //     if (!empty($token)) {
    //         header('Authorization: ' . $token);
    //     }
    //     if (empty($status)) {
    //         $status = 200;
    //     }
    //     // $this->save_logs($data); // Save logs
    //     echo $this->response($data, $status);exit;
    // }

    public function dynamic_connections()
    {
        $master_db = $this->load->database('master_db', TRUE);
        // echo'asdsadsadasdasdasdsada';exit;
        $query2 = $master_db->get("setup_config_master");
		print_r($query2->result());
		exit;
    }
}
