<?php
defined('BASEPATH') or exit('No direct script access allowed');

error_reporting(E_ERROR | E_PARSE);
//error_reporting(E_ERROR | E_PARSE);
//error_reporting(E_ALL);

require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

// Razorpay libraries
require APPPATH . 'libraries/paytm-checksum-master/PaytmChecksum.php';

class Paytm_payment extends RestController
{
    public function __construct()
    {
        parent::__construct();
        
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

    /***********************Payment Gateway:Start***********************/
    function generate_order_post(){
        $mid                    = $this->input->post('mid');
        $receipt                = $this->input->post('receipt');
        $orderId                = $this->input->post('orderId');
        $pay_amount             = $this->input->post('amount');
        $custId                 = $this->input->post('custId');
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

        if((float)$og_amount === (float)$pay_amount){
            $merchant_key   = PAYTM_MERCHANT_KEY_TEST;

            $paytmParams = array();

            $paytmParams["body"] = array(
                "requestType"   => "Payment",
                "mid"           => $mid,
                "websiteName"   => PAYTM_MERCHANT_WEBSITE_TEST,
                "orderId"       => $orderId, // "ORDERID_98765"
                // "callbackUrl"   => "https://<callback URL to be used by merchant>",
                "txnAmount"     => array(
                    "value"     => $pay_amount,
                    "currency"  => "INR",
                ),
                "userInfo"      => array(
                    "custId"    => $custId,
                ),
            );

            /*
            * Generate checksum by parameters we have in body
            * Find your Merchant Key in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys 
            */
            $checksum = PaytmChecksum::generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), $merchant_key);

            $paytmParams["head"] = array(
                "signature"    => $checksum
            );

            $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

            /* for Staging */
            $url = "https://securegw-stage.paytm.in/theia/api/v1/initiateTransaction?mid={$mid}&orderId={$orderId}";

            /* for Production */
            // $url = "https://securegw.paytm.in/theia/api/v1/initiateTransaction?mid={$mid}&orderId={$orderId}";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json")); 
            $res = curl_exec($ch);
            // print_r($response);
            $result = json_decode($res, true);

            // sucess response 
            /*
            {
                "head": {
                    "responseTimestamp": "1526969112101",
                    "version": "v1",
                    "clientId": "C11",
                    "signature": "TXBw50YPUKIgJd8gR8RpZuOMZ+csvCT7i0/YXmG//J8+BpFdY5goPBiLAkCzKlCkOvAQip/Op5aD6Vs+cNUTjFmC55JBxvp7WunZ45Ke2q0="
                },
                "body": {
                    "resultInfo": {
                        "resultStatus": "S",
                        "resultCode": "0000",
                        "resultMsg": "Success"
                    },
                    "txnToken": "fe795335ed3049c78a57271075f2199e1526969112097",
                    "isPromoCodeValid": false,
                    "authenticated": false
                }
            }
            */

            // fail response 
            /*
            {
                "head": {
                    "responseTimestamp": "1555581762193",
                    "version": "v1"
                },
                "body": {
                    "resultInfo": {
                        "resultStatus": "F",
                        "resultCode": "2004",
                        "resultMsg": "SSO Token is invalid",
                        "bankRetry": null,
                        "retry": null
                    },
                    "extraParamsMap": null
                }
            }       
            */
            
            $resultStatus = $result['body']['resultInfo']['resultStatus'];
            if($resultStatus == "S"){
                $response = array("success" => 1, "data" => $result, "msg" => 'Generate Signature Successfuly', "error" => 0, "status" => 1);
            } else {
                $response = array("success" => 0, "data" => $result, "msg" => 'Signature not Generate', "error" => 1, "status" => 1);
            }

        } else {
            $response = array("success" => 0, "data" => [], "msg" => 'Amount not match', "error" => 1, "status" => 1);
        }

        $this->api_response($response);
        exit;
    }

    /***********************Payment Gateway:End***********************/

    

}
