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

class Payment extends RestController
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

        // $base_path = base_url();
        //$base_path = 'https://dev.famrut.co.in/agri_ecosystem/';
       // $base_path = 'https://dev.famrut.co.in/agroemandi/';
        $this->base_path = $base_path = BASE_PATH_PORTAL;      
        

       // $base_path = 'https://dev.famrut.co.in/agroemandi/';
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

    /***********************Payment Gateway:Start***********************/
    function generate_order_post(){
        // Check payment gateway is active or not
        $payment_gateway = $this->Masters_model->get_data(array('*'), 'payment_settings', array('is_active' => 'true'), NULL, NULL, 0, 1);

        $order_data = array('title' => 'COD');

        if(!empty($payment_gateway)){
            $payment_data = json_decode($payment_gateway[0]['payment_data'], true);
            $test_mode = array('sandbox');

            if($payment_data['title'] == 'Razorpay'){
                if(in_array($payment_data['mode'], $test_mode)){ // test mode
                    $keyId      = $payment_data['sandbox_merchant_key'];
                    $keySecret  = $payment_data['sandbox_secret_key'];
                } else {
                    $keyId      = $payment_data['production_merchant_key'];
                    $keySecret  = $payment_data['production_secret_key'];
                }
                

                // echo 'mode:'.$payment_data['mode'].', keyId:'.$keyId.', keySecret:'.$keySecret; exit;
                // Actual generate order code
                $receipt                = $this->input->post('receipt');
                $pay_amount             = $this->input->post('amount');
                $cart_prod_ids          = $this->input->post('cart_prod_ids');
                $cart_prod_quantity     = $this->input->post('cart_prod_quantity');
    
                $cart_prod_ids_arr      = explode(',', $cart_prod_ids);
                $cart_prod_quantity_arr = explode(',', $cart_prod_quantity);
    
                // combine product id with its quantity
                $cart_data = array_combine($cart_prod_ids_arr,$cart_prod_quantity_arr);
    
                $sql_prod  = "SELECT id, price from products WHERE id IN (". $cart_prod_ids .")";
                $res_val   = $this->db->query($sql_prod);
                $res_array = $res_val->result_array();
    
                $og_amount = 0;
                foreach($res_array as $val){
                    $og_amount += $val['price'] * $cart_data[$val['id']];
                }
                $razorpayOrder = [];
                // echo $og_amount.'---'.$pay_amount;exit;
    
                if((float)$og_amount === (float)$pay_amount){
                    // $keyId      = KEYID_TEST;
                    // $keySecret  = KEYSECRET_TEST;
                    $api        = new Api($keyId, $keySecret);
                    //
                    // We create an razorpay order using orders api
                    // Docs: https://docs.razorpay.com/docs/orders
                    //
                    $orderData = [
                        'receipt'           => $receipt, // 3456
                        'amount'            => (float)$pay_amount * 100, // 2000 rupees in paise
                        'currency'          => 'INR',
                        'payment_capture'   => 1 // auto capture
                    ];
                
                    $razorpayOrder      = $api->order->create($orderData);
                    $order_data         = array(
                                            'id'            => $razorpayOrder['id'],
                                            'status'        => $razorpayOrder['status'],
                                            'created_at'    => $razorpayOrder['created_at'],
                                            'order_id'      => $razorpayOrder['receipt'],
                                            'amount'        => $razorpayOrder['amount'],        
                                            'status'        => $razorpayOrder['status'],        
                                            'attempts'      => $razorpayOrder['attempts'],
                                        );
    
                    // Save in activity logs
                    $ganerate_order = [];
                    $ganerate_order['orderData']        = $orderData;
                    $ganerate_order['razorpayOrder']    = $order_data;
                    user_activity_logs("User: Order Generated ", json_encode($ganerate_order));
    
                    if(!empty($razorpayOrder)){
                        $response = array("success" => 1, "data" => $order_data, "msg" => 'Order id created', "error" => 0, "status" => 1);
                    } else {
                        $response = array("success" => 0, "data" => $order_data, "msg" => 'Order id not created', "error" => 1, "status" => 1);
                    }
                } else {
                    $response = array("success" => 0, "data" => $order_data, "msg" => 'Amount not match', "error" => 1, "status" => 1);
                }
            } else {
                $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $order_data, "message" => "Selected payment gateway is different!");
            }

        } else {
            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $order_data, "message" => "No payment gateway is active!");
        }
        
        $this->api_response($response);
        exit;
    }

    function payment_status_post(){

        $payment_status     = $this->input->post('status');
        $order_id           = $this->input->post('order_id');

        $payment_status_arr['order_id']         = $order_id;
        $payment_status_arr['payment_status']   = $payment_status;

        if(!empty($payment_status) && $payment_status == 'success'){
            $response = array("success" => 1, "data" => $payment_status_arr, "msg" => 'Payment Success', "error" => 0, "status" => 1);

        } else {
            $response = array("success" => 0, "data" => $payment_status_arr, "msg" => 'Payment Failed', "error" => 1, "status" => 1);
        }
        user_activity_logs("User: Order Payment Status: ".ucwords($payment_status), json_encode($payment_status_arr));

        $this->api_response($response);
        exit;
    }

    
    function verify_order_post(){
        $success    = true;
        $error      = "Payment Failed";

        if (empty($this->input->post('payment_id')) === false)
        {
            $keyId      = KEYID_TEST;
            $keySecret  = KEYSECRET_TEST;
            $api = new Api($keyId, $keySecret);
            try
            {
                // Please note that the razorpay order ID must
                // come from a trusted source (session here, but
                // could be database or something else)
                $attributes = array(
                    'razorpay_order_id'     => $this->input->post('order_id'),
                    'razorpay_payment_id'   => $this->input->post('payment_id'),
                    'razorpay_signature'    => $this->input->post('signature')
                );

                $api->utility->verifyPaymentSignature($attributes);
            }
            catch(SignatureVerificationError $e)
            {
                $success = false;
                $error = 'Razorpay Error : ' . $e->getMessage();
            }
        }

        if ($success === true)
        {
            $response = array("success" => 1, "data" => $attributes, "msg" => 'Your payment was successful. Payment ID: '.$this->input->post('payment_id'), "error" => 0, "status" => 1);
        }
        else
        {
            $response = array("success" => 0, "data" => [], "msg" => 'Your payment was failed.', "error" => 1, "status" => 1);
        }

        $this->api_response($response);
        exit;
        
    }

    /***********************Payment Gateway:End***********************/

    

}
