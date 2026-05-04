<?php
defined('BASEPATH') or exit('No direct script access allowed');

error_reporting(E_ERROR | E_PARSE);
//error_reporting(E_ERROR | E_PARSE);
//error_reporting(E_ALL);

require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

// Razorpay libraries
require APPPATH . 'libraries/razorpay-php/Razorpay.php';
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class Payphi_payment extends RestController
{
    public function __construct()
    {
        parent::__construct();
        // $this->load->model('Product_model');
        // header('Access-Control-Allow-Origin: *');
        // header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
        // header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

        $headers_data = $this->input->request_headers();

        // Start: Required headers and there value check
        $require_headers    = array('domain', 'appname');
        $require_header_arr = [];
        $require_header_val = [];
        foreach ($require_headers as $rh_val) {
            if (!array_key_exists($rh_val, $headers_data)) {
                $require_header_arr[] = $rh_val;
            } else if (empty($headers_data[$rh_val])) {
                $require_header_val[] = $rh_val;
            }
        }

        if (!empty($require_header_arr) && count($require_header_arr) > 0) {
            $require_header_str = implode(', ', $require_header_arr);
            // echo $require_header_str;exit;
            //$msg      = "Invalid Requrest " . $require_header_str;
            $msg      = "Invalid Request";
            $response = array("status" => 0, "error" => 1, "data" => array(), "message" => $msg);
            $this->api_response($response);exit;
        } else if (!empty($require_header_val) && count($require_header_val) > 0) {
            $require_header_str = implode(', ', $require_header_val);
           // $msg              = "Required headers values: " . $require_header_str;
            $msg                = "Invalid Request";
            $response           = array("status" => 0, "error" => 1, "data" => array(), "message" => $msg);
            $this->api_response($response);exit;
        }
        // End: Required headers and there value check


        $this->load->library('Token');

        // $this->load->library('upload');
        $this->load->model('Email_model');
        $this->load->helper('log_helper');
        // $this->load->helper('sms_helper');
        $this->load->model('Masters_model');

        // $base_bath = base_url();
        //$base_bath = 'https://dev.famrut.co.in/agri_ecosystem/';
        //$base_bath = 'https://dev.famrut.co.in/agroemandi/';
        $this->base_path = $base_path = BASE_PATH_PORTAL;      
        

       // $base_bath = 'https://dev.famrut.co.in/agroemandi/';
        // $headers_data  = $this->input->request_headers();
        $selected_lang = $headers_data['lang'];

        if ($selected_lang == 'mr') {
            $lang_folder = "marathi";
        } elseif ($selected_lang == 'hi') {
            $lang_folder = "hindi";
        } else {
            $lang_folder = "english";
        }

        $this->lang->load(array('site'), $lang_folder);
    }

    public function api_response($data, $status = null, $token = null)
    {
        // echo base_url();exit;
        if (!empty($token)) {
            header('Authorization: ' . $token);
        }
        if (empty($status)) {
            $status = 200;
        }
        // $this->save_logs($data); // Save logs
        echo $this->response($data, $status);exit;
    }

    /***********************Payphi Gateway:Start***********************/
    function generate_securehashcode_post()
    {
        $empty_param     = [];
        $required_posted = array('securehash','aggregatorID','amount','currencyCode','customerEmailId','customerMobileNo','merchantID','returnURL','key');
        foreach ($_POST as $key => $val) {
            if (in_array($key, $required_posted) && empty($val)) {
                $empty_param[] = $key;
            }
        }


        if (!empty($empty_param)) {
            $msg      = implode(', ', $empty_param);
            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => [], "message" => "Posted values required: " . $msg);
        } else {
            if($this->input->post('securehash') == true){
                $addlParam1         = $this->input->post('addlParam1');
                $aggregatorID       = $this->input->post('aggregatorID');
                $amount             = $this->input->post('amount');
                $currencyCode       = $this->input->post('currencyCode');
                $customerEmailId    = $this->input->post('customerEmailId');
                $customerMobileNo   = $this->input->post('customerMobileNo');
                $merchantID         = $this->input->post('merchantID');
                $merchantTxnNo      = date("YmdHis");
                $payType            = (!empty($this->input->post('payType'))) ? $this->input->post('payType') : 0;
                $returnURL          = $this->input->post('returnURL');
                $transactionType    = 'SALE';
                // $transactionType    = $this->input->post('transactionType');
                $txnDate            = date("YmdHis");
                $key                = $this->input->post('key');
    
                $hashString = $addlParam1 ."".$aggregatorID ."".$amount ."".$currencyCode ."".$customerEmailId ."".$customerMobileNo ."".$merchantID ."".$merchantTxnNo ."".$payType ."".$returnURL ."".$transactionType ."".$txnDate;
                
                $secureHash  = hash_hmac('SHA256', $hashString, $key);
    
                if(!empty($secureHash)){
                    $response = array("success" => 1, "data" => $_POST, "msg" => 'Secure hash code ganerated successfully!', "securehash" => $secureHash, "error" => 0, "status" => 1);
                } else {
                    $response = array("success" => 0, "data" => [], "msg" => 'Something went worng!', "error" => 1, "status" => 1);
                }
    
            } else {
                $response = array("success" => 0, "data" => [], "msg" => 'Add requird fields!', "error" => 1, "status" => 1);
            }

        }

        
        $this->api_response($response);
        exit;
    }

    // https://dev.famrut.co.in/agri-ecosystem-api/api/v11/Payphi_payment/pay_response


    function pay_response_post()
    {
        if ($this->input->post('respDescription') == 'Transaction rejected') {
            $transactions_flag = 0;
        } elseif ($this->input->post('respDescription') == 'Transaction successful') {
            
            // update client_invoice
            if(!empty($this->input->post('order_id'))){
                $this->db->where('id', $this->input->post('order_id'));
                $this->db->update('client_orders', array('status' => 'Paid'));
            }
            
            $transactions_flag = 1;
        } else {
            $transactions_flag = 2;
        }

        
        $response = array("success" => 1, "data" => $_POST, "msg" => 'Transactions details added successfully!', "error" => 0, "status" => 1, 'transactions_flag' => $transactions_flag);

        $this->api_response($response);
        exit;
    }

    /***********************Payphi Gateway:End***********************/

    

}