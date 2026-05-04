<?php


defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH. 'libraries/RestController.php';

use chriskacerguis\RestServer\RestController;

class Crops extends RestController {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('common_model');
        $this->load->library('Token');

        $headers_data  = $this->input->request_headers();
        $token = explode(' ',$headers_data['Authorization']);
        $authorization = $token[1];

        $fields = array('authorization');
        $cond = array(
            'authorization'     =>  $authorization,
        );
        
        $userdata = $this->common_model->get_data('users', $cond);
        if(empty($userdata)){
            return $this->response(array('message'=>'Unautherized User!'),401);
        }
    }

    function api_response($data,$status,$token=null){
        return $this->response($data,$status);
    }

    function index_get(){
        if($this->check_authorization()){
            $userdata = $this->common_model->get_data('users',[],'',1);
            return $this->api_response($userdata,200);
        }
    }

    function check_authorization(){
        $headers_data  = $this->input->request_headers();
        $token = explode(' ',$headers_data['Authorization']);
        $authorization = $token[1];

        $fields = array('authorization');
        $cond = array(
            'authorization'     =>  $authorization,
        );
        
        $userdata = $this->common_model->get_data('users', $cond);
        if(empty($userdata)){
            return $this->response(array('message'=>'Unautherized User!'),401);
        }
        else{
            return TRUE;
        }
    }

}
