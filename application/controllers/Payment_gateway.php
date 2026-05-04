<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(E_ERROR | E_PARSE);

class Payment_gateway extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Email_model');
        $this->load->helper('log_helper');;
        $this->load->model('Masters_model');
        $this->base_path = $base_path = BASE_PATH_PORTAL;
    }


    public function checkout()
	{
        if (!empty($_GET['order_id'])) {
            
            /***** Get order num and get all order details *****/
            $order_data = $this->Masters_model->get_data(array('*'), 'client_orders', array('order_num'=>$_GET['order_id']), NULL, NULL, 0, 1);
            $data['client_orders'] = $client_orders = $order_data[0];
            
            $_SESSION['order_id'] = $client_orders['id'];
            $client_id = $_SESSION['client_id'] = $client_orders['client_id'];
            
            /***** Get client details *****/
            $userdetalis_arr = $this->Masters_model->get_data(array('*'), 'client', array('is_active' => 'true', 'id'=>$client_id), NULL, NULL, 0, 1);
            $data['client_detalis'] = $userdetalis = $userdetalis_arr[0];


            /***** Get payment gateway *****/
            $payment_gateway = $this->Masters_model->get_data(array('*'), 'payment_settings', array('is_active' => 'true'), NULL, NULL, 0, 1);
            
            if(!empty($payment_gateway) && $payment_gateway[0]['title'] == 'Payphi'){
                $data['payment_data'] = $payment_data = json_decode($payment_gateway[0]['payment_data'], true);

                // echo'<pre>';print_r($payment_data);echo'</pre>';

              
                if($payment_data['mode']=='production'){
                    $data['url']            = $payment_data['production_other_key'];
                    $data['merchantID']     = $payment_data['production_merchant_id'];
                    $data['aggregatorID']   = '';
                    $data['key']            = $payment_data['production_secret_key'];
                    // $amount = 51;
                    $amount = $client_orders['amount'];
                }
                else
                {
                    $data['url']            = $payment_data['sandbox_other_key'];
                    $data['merchantID']     = $payment_data['sandbox_merchant_id'];
                    $data['aggregatorID']   = $payment_data['sandbox_merchant_key'];
                    $data['key']            = $payment_data['sandbox_secret_key'];
                    $amount = 1;
                }
              
                $data['merchantTxnNo']  = date("YmdHis");
                $data['amount']         = $amount;
                $data['currencyCode']   = "356";
                $data['payType']        = "0";
                $data['transactionType']= "SALE";
                $data['returnURL']      = base_url().'payment_gateway/payreturn?order_id='.$_GET['order_id'].'&appname='.$_GET['appname'];
                $data['txnDate']        = date("YmdHis");
                $data['customerEmailId']= $userdetalis['email'];
                $data['customerMobileNo']= $userdetalis['phone'];
                $data['addlParam1']     = "Ref1^Ref2^Ref3^Ref4";
                $hashString = $data['addlParam1']."".$data['aggregatorID']."".$data['amount']."".$data['currencyCode']."".$data['customerEmailId']."".$data['customerMobileNo']."".$data['merchantID']."".$data['merchantTxnNo']."".$data['payType']."".$data['returnURL']."".$data['transactionType']."".$data['txnDate'];
                $data['secureHash'] = hash_hmac('SHA256', $hashString, $data['key']);
            } else {
                return false;
            }
        } else {
            return false;
        }
        
        // echo'<pre>';print_r($data);echo'</pre>';exit;
        $this->load->view('payment_gateway/checkout_submit', $data);
	}

	public function payreturn()
	{
        // echo'<pre>';print_r($_REQUEST);echo'</pre>';exit;
        user_activity_logs("Payment: Pay Return:", json_encode($_REQUEST));
	    
	    if ($_GET['order_id'] != '' ) {
	        $query      = $this->db->query("SELECT * FROM client_orders WHERE order_num= '" . $_GET['order_id']."'");
            $order_data = $query->result_array();

            if ($this->input->post('respDescription') == 'Transaction rejected') {
                $transactions_flag = 0;
                $title = 'Transaction rejected';
                $data = array("success" => 0, "data" => array(), "msg" => 'Transactions not added', "error" => 1, "status" => 0, 'transactions_flag' => $transactions_flag,'title' => $title);
            } elseif ($this->input->post('respDescription') == 'Transaction successful') {
                $transactions_flag = 1;

                $invoice_num = 'F00' . $_GET['order_id'];
                foreach ($order_data as $key => $value) {

                    $invoice_query      = $this->db->query("SELECT id FROM client_invoices WHERE order_id= '" . $value['id']."'");
                    $invoice_data = $invoice_query->row_array();

                    $invoice_id = $invoice_data['id'];
        
                    if (empty($invoice_id)) {
                        $insert = array(
                            'sub_total'     => $value['amount'],
                            'total'         => $value['amount'],
                            'client_id'     => $value['client_id'],
                            'order_id'      => $value['id'],
                            'invoice_num'   => $invoice_num,
                            'cust_type'     => 'client',
                            'created_on'    => current_date(),
                            'paid_date'     => date('Y-m-d'),
                            'invoice_date'  => date('Y-m-d'),
                        );
                        $result                 = $this->db->insert('client_invoices', $insert);
                        $_SESSION['invoice_id'] = $invoice_id = $this->db->insert_id();
                    }

                    $insert_trans_array = array(
                        'client_id'        => $value['client_id'],
                        'invoice_id'       => $invoice_id,
                        'transaction_id'   => $this->input->post('txnID'),
                        'amount_in'        => $value['amount'],
                        'gateway'          => 'PayPhi',
                        'bank_name'        => $this->input->post('paymentMode'),
                        'status'           => $this->input->post('respDescription'),
                        'gateway_txn_id'   => $this->input->post('merchantTxnNo'),
                        'transaction_date' => current_date(),
                        'created_on'       => current_date(),
                    );
                    
                    $results        = $this->db->insert('transactions', $insert_trans_array);
                    $transaction_id = $this->db->insert_id();
                    user_activity_logs("Payment: Transaction:", json_encode($insert_trans_array));
                }

                $payment_method   = $this->input->post('paymentMode');
                $payment_status   = 'Paid';
                // update client_invoice
                $this->db->where('id', $invoice_id);
                $this->db->update('client_invoices', array('payment_method' => $payment_method, 'status' => $payment_status));

                
                $where  = array('order_num' => $_GET['order_id']);
                $update_payments    = [];
                $update_payments    = array(
                    'payment_method'    => $payment_method,
                    'payment_status'    => $payment_status,
                    'paid_amount'       => number_format($value['amount'], 2),
                    'order_completion_date' => date('Y-m-d H:i:s'),
                );

                $client_orders_result   = $this->Masters_model->update_data('client_orders', $where, $update_payments);

                $title = 'Transaction successful';
        
                $data = array("success" => 1, "msg" => 'Transactions details added successfully', "error" => 0, "status" => 1, 'transactions_flag' => $transactions_flag,'title' => $title, 'callback' => array('payment_status' => 'success'));

            } else {
                $transactions_flag = 2;
                $title = 'Transaction Not Found';
                
                
                $data = array("success" => 0, "data" => array(), "msg" => 'Transactions not added', "error" => 1, "status" => 0, 'transactions_flag' => $transactions_flag,'title' => $title, 'callback' => array('payment_status' => 'failed'));
            }
        }

        $this->load->view('payment_gateway/payreturn', $data);
        // $this->load->view('front/include/main', $data);

	}

    function my_urldecode($string){
        $array = explode("%",$string);
        if (is_array($array)){
            while (list($k,$v) = each($array)){
                $ascii  = base_convert($v,16,10);
                $ret    .= chr($ascii);
            }
        }
        return ("$ret");
    }
}