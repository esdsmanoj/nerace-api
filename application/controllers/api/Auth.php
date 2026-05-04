<?php


defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH. 'libraries/RestController.php';

use chriskacerguis\RestServer\RestController;

class Auth extends RestController {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('common_model');
        $this->load->library('Token');
    }

    function api_response($data,$status,$token=null){
        if(!empty($token)){
            header('Authorization: '.$token);
        }
        return $this->response($data,$status);
    }

    function index_get(){
        $data = $this->common_model->get_data('client');
        $status = 200;
        echo $this->api_response($data, $status);
    }

    function signin_post(){
        $fields = array('id, name, email');
        $cond = array(
            'email'     =>  $this->input->post('email'),
            'password'  =>  $this->input->post('password')
        );
        
        $userdata = $this->common_model->get_data('client', $cond, $fields);
        $token = '';
        $status = 400;
        if(!empty($userdata)){
            $token  = $this->token->ganerate_key();
            $update_data['auth_token']=$token;
            $cond = array('id'=>$userdata->id);
            // save authorization
            $this->common_model->update_data('client', $update_data, $cond);
            $status = 201;
        }

        echo $this->api_response($userdata, $status, $token);
    }

    function signup_post(){
        $fields = array('*');
        $insert_data = array(
            'first_name'      =>  $this->input->post('first_name'),
            'last_name'      =>  $this->input->post('last_name'),
            'email'     =>  $this->input->post('email'),
            'password'  =>  $this->input->post('password')
        );
        $userdata = $this->common_model->get_data('client', array('email' => $this->input->post('email')));
        $data['message'] = 'Email already registered!';
        $status = 400;
        if(empty($userdata)){
            $last_insert_id = $this->common_model->insert_data('client', $insert_data);
            $data['message'] = 'User Registraion Sucessfull!';
            $status = 200;
        }
        echo $this->api_response($data, $status);
    }

}
