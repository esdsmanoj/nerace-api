<?php
defined('BASEPATH') or exit('No direct script access allowed');

error_reporting(E_ERROR | E_PARSE);
//error_reporting(E_ERROR | E_PARSE);

//error_reporting(E_ALL);

require APPPATH. 'libraries/RestController.php';

use chriskacerguis\RestServer\RestController;

class Chat extends RestController
{

    public function __construct()
    {
		header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        parent::__construct();
        $headers_data = $this->input->request_headers();

		//print_r($headers_data);
		$headers_data['domain'] = $headers_data['Domain'];
		$headers_data['client-type'] = $headers_data['Client_type'];
		$headers_data['client-type'] = $headers_data['Client_type'];
        // Start: Required headers and there value check
        $require_headers    = array('Domain', 'Appname');
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

			// print_r($require_header_arr);
			// echo '------------------';
			// print_r($require_headers);
            // echo $msg      = "Invalid Requrest " . $require_header_str;
            // $msg      = "Invalid Request 33";
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

        // Start: Create upload file name and as per database name : Akash
        $this->connected_domain = '';
		$root_folder            = $_SERVER['HOME'] .'/'. UPLOAD_ROOT_FOLDER . '/';

        // We commented this code as we are sendiing Domain in header of API START
       /* if (strpos($_SERVER['DOCUMENT_ROOT'], 'agri_ecosystem') !== false) {
            $root_folder = $_SERVER['DOCUMENT_ROOT'] . '/'; // Set root folder
        }

        if (!isset($headers_data['domain']) || empty($headers_data['domain'])) {
            // Check domain name inside headers if its not found domain name inside headers then get domain name from host url
            $server_host            = $_SERVER['HTTP_HOST'];
            $headers_data['domain'] = 'agri_solution';
            if (strpos($server_host, 'icar.rmgtec.in') !== false) {
                $headers_data['domain'] = 'famrut_dev';
            } else if (strpos($server_host, 'dndccb.rmgtec.in') !== false) {
                $headers_data['domain'] = 'dndccb_dev';
            }
        }
       // We commented this code as we are sendiing Domain in header of API END
        */

		$headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $this->connected_domain = strtolower($headers_data['domain']); // globaly set connected domain name
        $db_folder              = $root_folder . 'uploads/' . $this->connected_domain;
        if (!file_exists($db_folder)) {
            mkdir($db_folder, 0777, true);
        }

        if (!file_exists($db_folder . '/user_data')) {
            mkdir($db_folder . '/user_data', 0777, true);
        }

        $this->upload_file_folder = $root_folder . 'uploads/' . $this->connected_domain . '/' . 'user_data/'; // globaly set upload file folder root
        // End: Create upload file name and as per db name : Akash

        $this->load->library('Token');

        $this->load->library('upload');       
        $this->load->helper('log_helper');     
        $this->load->model('Masters_model');
		$this->load->model('Notification_model');
		
        $lang_folder = "english";

        if ($this->session->userdata('user_site_language') && $this->session->userdata('user_site_language') == "MR") {
            $lang_folder = "marathi";
        } else {
            $this->session->set_userdata('user_site_language', 'EN');
            $lang_folder = "english";
        }

        // $base_path = $this->base_path;
        //$base_path = 'https://dev.famrut.co.in/agri_ecosystem/';
        $this->base_path = $base_path = BASE_PATH_PORTAL;        

       // $base_path = 'https://dev.famrut.co.in/agroemandi/';
        // $headers_data  = $this->input->request_headers();
        $this->selected_lang = $selected_lang = $headers_data['lang'];

        if ($selected_lang == 'mr') {
            $lang_folder = "marathi";
        } elseif ($selected_lang == 'hi') {
            $lang_folder = "hindi";
        } else {
            $lang_folder = "english";
        }

        $this->lang->load(array('site'), $lang_folder);
        // $group_id     = $headers_data['group_id']; (replace this line with below 2 lines)
        $group_id_arr = explode(',', $headers_data['group_id']);
        $group_id     = $group_id_arr[0];
        
        // Below array is in used
        // $crop_product_img =  $this->upload_file_folder.'farm/' . $crop_prod_image2;

        // $this->config_url = array(
        //     'crop_prod_new'           => $base_path . 'uploads/' . $this->connected_domain . '/user_data/farm/',
        //     'crop_product_img'        => $base_path . 'uploads/' . $this->connected_domain . 'uploads/farm/',
        //     'category_img_url'        => $base_path . 'uploads/category/',
        //     'partner_img_url'         => $base_path . 'uploads/profile/',
        //     'aadhar_no_doc_url'       => $base_path . 'uploads/aadhar_no/',
        //     'pan_no_doc_url'          => $base_path . 'uploads/pan_no/',
        //     'farm_image_url'          => $base_path . 'uploads/farm/',
        //     'Product_image_url'       => $base_path . 'uploads/productcategory/',
        //     'market_cat_image_url'    => $base_path . 'uploads//logo/',
        //     'service_image_url'       => $base_path . 'uploads/product_service/',
        //     'blogs_types_url'         => $base_path . 'uploads/blogs_types/',
        //     'blogs_tags_url'          => $base_path . 'uploads/blogs_tags/',
        //     'created_blogs_url'       => $base_path . 'uploads/created_blogs/',
        //     'farmer_documents_url'    => $base_path . 'uploads/verification_documents/',
        //     'advertise_image_url'     => $base_path . 'uploads/advertise_master/',
        //     'whitelabel_image_url'    => $base_path . 'uploads/client_group_master/',
        //     'terms_sheet'             => $base_path . 'uploads/terms_sheet/',
        //     'farm_doc'                => $base_path . 'uploads/farm_doc/',
        //     'insurance_company'       => $base_path . 'uploads/insurance_company/',
        //     'crop_image_url'          => $base_path . 'uploads/crops/',
        //     'crop_type_url'           => $base_path . 'uploads/crop_type_icon/',
        //     'crop_invoice_url'        => $base_path . 'uploads/crop_invoice/',
        //     'crop_health_predict_api' => 'http://115.124.96.136:8443/predict',
        //     'privacy_policy'          => 'https://gfreshagrotech.com/privacy-policy/',
        //     'terms_and_conditions'    => 'https://gfreshagrotech.com/terms-and-conditions/',
        // );


		// Below array is in used
        $this->config_url = array(
            'category_img_url'        => $base_path . 'uploads/category/',
            'partner_img_url'         => $base_path . 'uploads/' . $this->connected_domain . '/user_data/profile/',
            'aadhar_no_doc_url'       => $base_path . 'uploads/' . $this->connected_domain . '/user_data/aadhar_no/',
            'pan_no_doc_url'          => $base_path . 'uploads/' . $this->connected_domain . '/user_data/pan_no/',
            'farm_image_url'          => $base_path . 'uploads/' . $this->connected_domain . '/user_data/farm/',
            'Product_image_url'       => $base_path . 'uploads/productcategory/',
            'market_cat_image_url'    => $base_path . 'uploads/logo/',
            'service_image_url'       => $base_path . 'uploads/product_service/',
            'blogs_types_url'         => $base_path . 'uploads/blogs_types/',
            'media_types'             => $base_path . 'uploads/media_types/',
            'blogs_tags_url'          => $base_path . 'uploads/blogs_tags/',
            'created_blogs_url'       => $base_path . 'uploads/created_blogs/',
            'farmer_documents_url'    => $base_path . 'uploads/' . $this->connected_domain . '/user_data/verification_documents/',
            'advertise_image_url'     => $base_path . 'uploads/advertise_master/',
            'whitelabel_image_url'    => $base_path . 'uploads/client_group_master/',
            'terms_sheet'             => $base_path . 'uploads/terms_sheet/',
            'farm_doc'                => $base_path . 'uploads/' . $this->connected_domain . '/user_data/farm_doc/',
            'insurance_company'       => $base_path . 'uploads/insurance_company/',
            'crop_image_url'          => $base_path . 'uploads/crops/',
            'crop_type_url'           => $base_path . 'uploads/crop_type_icon/',
            'notice'                  => $base_path . 'uploads/notice/',
            'announcement'            => $base_path . 'uploads/announcement/',
            'crop_health_predict_api' => 'http://115.124.96.136:8443/predict',
            'dss_module_imageurl'     => $base_path . 'uploads/dss_module/',
            'bottom_menu_icon'        => $base_path . 'uploads/app_menu/',
            'crop_verity_img_url'     => $base_path . 'uploads/crop_variety_icon/',
            'crop_ferti_img_url'      => $base_path . 'uploads/crops_ferti_image/',
            'soil_health_image'       => $base_path . 'uploads/soil_health_image/',
            'media_thumbnails'        => $base_path . 'uploads/media_thumbnails/',
            'loan_type_url'           => $base_path . 'uploads/loan_type/',
            'loan_image_url'          => $base_path . 'uploads/' . $this->connected_domain . '/user_data/loan/',
            'crop_image'              => $base_path . 'uploads/' . $this->connected_domain . '/user_data/crop_image/',
			'trade_products'          => $base_path . 'uploads/config_master/trade_products',
			'seller_invoice_path'		=> $base_path . 'uploads/config_master/seller_invoice',
			'prod_master_image_path'	=> $base_path . 'uploads/config_master/prod_master',
        );

       
    }
    /***********************Working APIs: Start***********************/

    function api_response($data,$status=null,$token=null)
    {
        // echo $this->base_path;exit;
        if(!empty($token)){
            header('Authorization: '.$token);
        }
        if(empty($status)){
            $status = 200;
        }
        // $this->save_logs($data); // Save logs
        echo $this->response($data,$status);exit;
    }

    public function index_get()
    {

        $response = array("status" => 0, "message" => "");
        $row      = $this->db->get('users');
        //$row = $this->db->get('users')->where('U.is_deleted = false and U.email_verify = true');
        $result = $row->result_array();
        if (count($result)) {
            $response = array("status" => 1, "data" => $result, "message" => "User data");
        }
        $this->api_response($response);
    }

	public function manage_chat_post()
    {
		$headers_data	= $this->input->request_headers();
		$client_type	= $headers_data['client_type'];

		$send_from_id 	= $this->input->post('send_from_id')??'';
		$search			= $this->input->post('search')??'';
        $response = array("status" => 0, "message" => lang('Missing_Parameter'));
        if (!empty($send_from_id)) {
			$select = " tc.id, tc.send_from_id, tc.send_to_id, tc.trade_product_bidding_id, tc.msg_text, (SELECT CONCAT(client.first_name, ' ', client.last_name) as username FROM client WHERE client.id = tc.send_to_id) AS send_to_username, (SELECT client.profile_image as image FROM client WHERE client.id = tc.send_to_id) AS send_to_userprofile, (SELECT MAX(trade_chat.created_on) FROM trade_chat WHERE trade_chat.id = tc.id) AS last_message_timestamp, tp.id as trade_product_id, tp.prod_cat_id, pt.title as product_type_title, tp.prod_id, pm.title as product_title, pv.title as product_variety_title, tp.active_till_date, tp.surplus, tp.surplus_unit, tp.sell_qty, tp.sell_qty_unit, tp.price, tp.price_unit, tp.added_date, tp.status, tpb.buyer_id, tpb.seller_action_date, tpb.bid_date, tpb.seller_action, tpb.buyer_action ";

			// $select = " tc.id, tc.send_from_id, tc.send_to_id, tc.trade_product_bidding_id, tc.msg_text, tp.id as trade_product_id, tp.prod_cat_id, pt.title as product_type_title, tp.prod_id, pm.title as product_title, pv.title as product_variety_title, tp.active_till_date, tp.surplus, tp.surplus_unit, tp.sell_qty, tp.sell_qty_unit, tp.price, tp.price_unit, tp.added_date, tp.status, tpb.buyer_id, tpb.seller_action_date ";


			$query = " SELECT ". $select ." FROM trade_chat as tc ";
			$query .= " JOIN client as c ON c.id = tc.send_from_id ";
			
			$query .= " JOIN trade_product_bidding as tpb ON tpb.id = tc.trade_product_bidding_id ";
			$query .= " JOIN trade_product as tp ON tp.id = tpb.trade_product_id ";
			$query .= " LEFT JOIN prod_type as pt ON pt.id = tp.prod_type_id ";
			$query .= " LEFT JOIN prod_variety as pv ON pv.id = tp.prod_variety_id ";
			$query .= " LEFT JOIN prod_master as pm ON pm.id = tp.prod_id ";

			// $query .= " WHERE (tc.send_from_id = ".$send_from_id." OR tc.send_to_id = ".$send_from_id.") AND tp.status <= '5' ";
			$query .= " WHERE (tc.send_from_id = ".$send_from_id." OR tc.send_to_id = ".$send_from_id." ) AND tp.status <= '5' ";
			//$query .= " WHERE tc.send_from_id = ".$send_from_id." AND tp.status <= '5' ";

			if(!empty($search)){
				$query .= " AND  (LOWER(pm.title) LIKE LOWER('%".$search."%')  ";

				$query .= " OR  LOWER(pt.title) LIKE LOWER('%".$search."%') ";
				$query .= " OR  LOWER(pv.title) LIKE LOWER('%".$search."%') ";

				$query .= " ) ";


			}

			$query .= " ORDER BY last_message_timestamp DESC ";
			// $query .= " ORDER BY tc.id DESC ";

			// echo $query;exit;

			$res	= $this->db->query($query);
            $result	= $res->result_array();


			// $groupByChatData = array_column($result, 'trade_product_bidding_id');
			$msgData = [];
			$sold_to = [4,5];
			foreach ($result as $key => $value) {
				//$value['bid_place_date'] = date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($value['bid_date'])));
				$value['bid_place_date'] = date('Y-m-d H:i:s', strtotime($value['bid_date']));
				
				$value['added_date'] = date('Y-m-d H:i:s', strtotime($value['added_date']));

				//$value['added_date'] = date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($value['added_date'])));


				/* Start Get product category details : Akash */ 
                $prod_cat_id = $value['prod_cat_id'];
                $product_category   = array_filter(PROD_CAT, function ($product_category_data) use ($prod_cat_id) {
                    return $product_category_data['id'] == $prod_cat_id;
                });
                $product_category = array_values($product_category);
                $value['product_category_title'] = $product_category[0]['title'];
                /* End Get product category details */

                /* Start Get unit details : Akash */ 
                $get_unit_list_ids = array('surplus_unit', 'sell_qty_unit', 'price_unit');
                // $prod_cat_id = $value['prod_cat_id'];
                foreach ($get_unit_list_ids as $unit_id) {
                    $unitId = $value[$unit_id];
                    $product_unit   = array_filter(PROD_UNIT, function ($product_unit_data) use ($unitId) {
                        return $product_unit_data['id'] == $unitId;
                    });
                    $product_unit = array_values($product_unit);
                    $value[$unit_id.'_title'] = $product_unit[0]['title'];
                }
				$statusId   = $value['status'];
                /* End Get unit details */


				/* Start Get product category details : Akash */ 
				// if($client_type == 'seller'){
				// 	$statusFilter = TRADE_STATUS_LIST;
				// } else {
				// 	$statusFilter = BUYER_TRADE_STATUS_FILTER;
				// }
				// $statusList = array_filter($statusFilter, function ($statusList_data) use ($statusId) {
				// 	return $statusList_data['id'] == $statusId;
				// });
				// $statusList = array_values($statusList);
				// $value['status_title'] = $statusList[0]['title'];
				// $value['status_class'] = $statusList[0]['statusClass'];






				$sellerAction	= (int)$value['seller_action'];
				$buyerAction	= (int)$value['buyer_action'];
				$buyerStatus	= [1,2];
				$sellerStatus	= [2,3];
				if((in_array($buyerAction, $buyerStatus)) && (int)$value['status'] === 3){
					if(in_array($sellerAction, $sellerStatus)){
						$manageProductStatus = $sellerAction;
						$manageProductStatusListing	= SELLER_TRADE_STATUS;
					} else {
						$manageProductStatus		= $buyerAction;
						$manageProductStatusListing	= BUYER_TRADE_STATUS;
					}
				} else {
					$manageProductStatus		= $value['status'];
					$manageProductStatusListing	= TRADE_STATUS_LIST;
				}

				$value['status_id'] = $manageProductStatus;

				/* Start Get buyer_action_title details : Akash */ 
							
				$manageProdAction	= array_filter($manageProductStatusListing, function ($manageProdAction_data) use ($manageProductStatus) {
					return $manageProdAction_data['id'] == $manageProductStatus;
				});
				$manageProdAction		= array_values($manageProdAction);
				$value['status_title']	= $manageProdAction[0]['title'];
				$value['status_class']	= $manageProdAction[0]['statusClass'];
				/* End Get buyer_action_title details */



                /* End Get product category details */

				if(in_array($statusId, $sold_to)){
					$buyer_detail	= get_client_detail($value['buyer_id']);
					$buyer_name		= $buyer_detail['first_name'].' '.$buyer_detail['last_name'];

					//$seller_action_date = date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($value['seller_action_date'])));
					$seller_action_date = date('Y-m-d H:i:s', strtotime($value['seller_action_date']));


					$value['sold_to_buyer_id']	= $value['buyer_id'];
					$value['sold_to']			= ucwords($buyer_name);
					$value['sold_on']			= $seller_action_date;
				} else {
					$value['sold_to_buyer_id']	= null;
					$value['sold_to']			= null;
					$value['sold_on']			= null;
				}
				
				$msgData[$value['trade_product_bidding_id']] = $value;
			}

			$response = array("success" => 1, "error" => 0, "status" => 1, "data" => array_values($msgData), "message" => lang('Listed_Successfully'), 'trade_sql' => $query);

            $this->api_response($response);
        }
    }

	public function user_chat_post()
    {
        $send_from_id  = $this->input->post('send_from_id');
        $send_to_id = $this->input->post('send_to_id');
		$trade_product_bidding_id = $this->input->post('trade_product_bidding_id');
		$client_profile_path = $this->config_url['partner_img_url'];
		//$trade_product_bidding_id 
        $output     = "";
        // $is_custom  = 0;
        $response = array();
		$newarray = array();

        $response = array("status" => 0, "message" => lang('Missing_Parameter'));
        if ($send_from_id != '' && $send_to_id != '' && $trade_product_bidding_id != '') {
			$send_to_cilent_details		= $this->getClientData($send_to_id);
			$send_from_cilent_details	= $this->getClientData($send_from_id);
            $select		= "SELECT tp.id as trade_product_id, tp.user_id, tp.prod_id, pm.title as product_title, pm.logo as product_logo, tp.price, tp.price_unit, tp.status, tpb.bid_price FROM
			trade_product_bidding as tpb
			LEFT JOIN trade_product as tp ON tp.id = tpb.trade_product_id
			LEFT JOIN prod_master as pm ON pm.id = tp.prod_id
			WHERE tpb.id = ".$trade_product_bidding_id." ";
			$sql_query	= $this->db->query($select);
			$trade_product_details= $sql_query->row_array();

			$row_val = $this->db->query("SELECT * FROM trade_chat WHERE trade_product_bidding_id = ".$trade_product_bidding_id." ORDER BY id ASC");


            $result = $row_val->result_array();
            if (count($result) > 0) {
                foreach ($result as $key => $row) {

                    if ($row['send_from_id'] === $send_to_id) {
                        $output .= '<div class="chat outgoing">
                                <div class="details">
                                    <p>' . $row['msg_text'] . '</p>
                                </div>
                                </div>';
                    } else {

                        /* <img src="php/images/'.$row['img'].'" alt="">*/
                        $output .= '<div class="chat incoming">
                                <div class="details">
                                    <p>' . $row['msg_text'] . '</p>
                                </div>
                                </div>';
                    }


					//$row['added_date'] = 
					//$row['created_on'] = date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($row['created_on'])));
					//$row['added_date'] = 
					$row['created_on'] = date('Y-m-d H:i:s', strtotime($row['created_on']));
					// if ( $value['logo'] === null) {						
						$newarray[] = $row;
					// }else{						
					// 	$newarray[] = $value;
					// }



                }
            } else {
                $output .= '<div class="text">No messages are available. Once you send message they will appear here.</div>';
            }

            $chat_str = $output;

            if (count($result)) {

                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $newarray, "chat_str" => $chat_str, "message" => lang('Listed_Successfully'), 'trade_product_details'=>$trade_product_details, 'send_to_cilent_details' => $send_to_cilent_details, 'send_from_cilent_details' => $send_from_cilent_details, 'client_profile_path'=>$client_profile_path);
                $this->api_response($response);
                exit;

            } else {

                $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "chat_str" => '<div class="text">No messages are available. Once you send message they will appear here.</div>', "message" => lang('Data_Not_Found'), 'trade_product_details'=>$trade_product_details, 'send_to_cilent_details' => $send_to_cilent_details, 'send_from_cilent_details' => $send_from_cilent_details, 'client_profile_path'=>$client_profile_path);
                $this->api_response($response);
                exit;

            }


            $this->api_response($response);
        }

    }

    public function add_user_chat_post()
    {
        $send_from_id  = $this->input->post('send_from_id');
        $send_to_id = $this->input->post('send_to_id');
        $msg        = $this->input->post('msg_text');
		$trade_product_bidding_id        = $this->input->post('trade_product_bidding_id');
        // $is_custom  = 0;
        $response = array();

        $response = array("status" => 0, "message" => lang('Missing_Parameter'));
        if ($send_from_id != '' && $send_to_id != '' &&  $trade_product_bidding_id   != '') {
			//  'user_type'       => 'client',
            $insert = array(
                'msg_text'             => $this->input->post('msg_text'),
                'send_to_id' => $send_to_id,
                'send_from_id' => $send_from_id, 
				'trade_product_bidding_id' => $trade_product_bidding_id,             
                'created_on'      => current_date(),

            );

            $result = $this->db->insert('trade_chat', $insert);

            if ($result) {

                if (count($insert)) {

                    $sql_user  = "SELECT first_name, last_name,device_id,profile_image from client where id=" . $send_to_id . "  LIMIT 1";
                    $row_user  = $this->db->query($sql_user);
                    $user_data = $row_user->result_array();

                    $partner_name = $user_data[0]['first_name'] . ' ' . $user_data[0]['last_name'];

                    $sql_farmer  = "SELECT first_name, last_name,device_id,profile_image from client where id=" . $send_from_id . "  LIMIT 1";
                    $row_farmer  = $this->db->query($sql_farmer);
                    $farmer_data = $row_farmer->result_array();

                    $farmer_name = $farmer_data[0]['first_name'] . ' ' . $farmer_data[0]['last_name'];

                    $data['title'] = 'Chat';
                    $title         = 'Chat';
                    $message       = $farmer_name . ':' . truncate_string($msg);
                    // $message       = 'Dear ' . $partner_name . ' you have a New Message';
                    $admno = $send_from_id;
                    $type  = 1;

                    $test_array[] = $partner_name;
                    $token[]      = $user_data[0]['device_id'];

                    $test_array[] = $token;
                    $test_array[] = $sql_user;
                    $send_from_id    = $send_from_id;
                    $farmer_image = $farmer_data[0]['profile_image'];

                    //$jsonString = self::sendPushNotificationToFCMSeverdev_chat($token, $title, $message, $admno, $type, $partner_name, $send_from_id, $farmer_image, 'chat');
					//MMM

                    $test_array[] = $jsonString;
					// CHAT notifcation START code /////
					$notification_enable = get_config_data('notification_enable');
                        if($notification_enable == 1){
                            $headers_data             = $this->input->request_headers();
                            $selected_lang = ($headers_data['lang'])?$headers_data['lang']:'en';
                           // $map_key = ($status==2)?'bid_revoked_by_buyer':'bid_cancelled';
						   $map_key = "chat_notification";
                            $notification_data = get_notification_detail($map_key,'seller',$selected_lang);
							$custom_array = $userid = [];
                            if(!empty($notification_data)){
                                $userid[] = $send_to_id;
                                $buyer_name = ucwords($farmer_name);
                                $title   = $notification_data['title'];
                                $arr = array(
                                    'body'    => array("{PRODUCT_ID}" => '#'.$trade_product_bidding_id , "{USER_NAME}" => $buyer_name),
                                );
                                $sms_template = get_sms_template($notification_data['notification_text'], $arr);
                                $message = $sms_template;
                                if ($message != '' && !empty($token)) {

									//Stay connected! A {USER_NAME} |  {PRODUCT_ID} has sent you a message. Check your chat inbox to address their inquiries and ensure a positive interaction.
									//print_r($token);
									//echo ' :: title '.$title.''.$message
									$custom_array = array("from_id"=>$send_from_id , "to_id" =>$send_to_id  , "trade_id" =>$trade_product_bidding_id ,"user_id" => $userid ,"map_key" => $map_key,"reference_id" => "client");

                                    $notifiy = $this->Notification_model->sendPushNotifications_request_dynamic($token, $title, $message, '','',$custom_array, $type = 'Chat', $trade_product_bidding_id,$send_from_id);

									//($token = array(), $title, $message,$is_whitelable,$group_ids = 0,$custom_array ,$type='blog',$blog_id = '',$img='')
                                    $dd      = json_decode($notifiy);
                                }
                                if ($dd->success == 1) {
                                    $results_notify = true;
                                } else {
                                    $results_notify = false;
                                }
                            }
                        }
						// CHAT notifcation END code /////
                    // $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $insert, "message" => "Chat Added Successfully", 'test_data' => $test_array);
                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $insert, "message" => lang('Added_Successfully'));
                }

                $this->api_response($response);
                exit;

            } else {

                $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $insert, "message" => "Chat Add failed, please try again some time.");

                $this->api_response($response);
                exit;

            }
            $this->api_response($response);
        }

    }

	public function sendPushNotificationToFCMSeverdev_chat($token, $title, $message, $arr_user, $type, $partner_name, $send_from_id, $farmer_image, $route)
    {
        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';

        $fields = array(
            'registration_ids' => $token,
            'priority'         => 10,
            'data'             => array("title" => $title, "body" => $message, "sound" => 'Default', 'image' => 'Notification Image', 'admno' => $arr_user, 'type' => $type, 'meeting_link' => '', 'partner_name' => $partner_name, "route" => $route, "click_action" => "FLUTTER_NOTIFICATION_CLICK", 'send_from_id' => $send_from_id, 'farmer_image' => $farmer_image),

            /*'notification'  => array("title" => $title, "body" => $message, "sound" => 'Default', 'image' => 'Notification Image', 'admno' => $arr_user, 'type' => $type, 'meeting_link' => '', 'partner_name' => $partner_name,"route"=>$route,"click_action"=> "FLUTTER_NOTIFICATION_CLICK",'send_from_id'=>$send_from_id,'farmer_image'=>$farmer_image),*/
        );

        /* // this api key for famrut farmer
        $API_SERVER_KEY = 'AAAAmhCfp8k:APA91bHXSHb1Ihie4m3y0v_0e1tAf7JCPMnubM751PMMVkC9oAi54O1AUGipDZ6ZZGCD9ajyxAFLsxjQ0cuLOqSLGjKp9Z0vBttKkIFeX7_xrZnbrAamAex-HMYK3z4SEz2_mHdwWYdu';*/
        // api key for Vendor or partner app

        $where       = array('is_deleted' => 'false', 'is_active' => 'true', 'key_fields' => 'API_SERVER_KEY');
        $app_key_res = $this->Masters_model->get_data("description", 'config_master', $where);

        $crop_ids_query = '';
        if ($app_key_res[0]['description']) {
            $API_SERVER_KEY = $app_key_res[0]['description'];
        } else {
            $API_SERVER_KEY = 'AAAAZP52chY:APA91bHn09jHHewFEixuQ87yO4QuYql8_bWBtRYtjx27mMIz-VWhMw6FRtbOoAHfm_xgBoZGqC0NJJiNlfObiNsqE-MNjRvNLaFtfysM6_JTzfZMFyRnjDOuzw5oCj-Ly6_Xw1GUXBX4';
        }

        $headers = array(
            'Authorization:key=' . $API_SERVER_KEY,
            'Content-Type:application/json',
        );

        // Open connection
        $ch = curl_init();
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $path_to_firebase_cm);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        // Execute post
        $result       = curl_exec($ch);
        $current_date = date("Y-m-d h:i:s");

        if (curl_errno($ch)) {
            $is_send = 0;
        } else {
            $is_send = 1;
        }
        // Close connection
        curl_close($ch);
        return $result;

    }


	function chat_bot_post(){
		$id		= $this->input->post('id');
		$chat	= trim(strtolower($this->input->post('chat')));

		if(empty($chat) && empty($id)){
			$sql	= "SELECT id, cb_title, cb_keywords, cb_responsedata FROM chat_bot WHERE  is_active = true AND is_deleted = false LIMIT 3";
		}

		if(!empty($chat) && empty($id)){
			$sql	= "SELECT id, cb_title, cb_keywords, cb_responsedata FROM chat_bot WHERE  is_active = true AND is_deleted = false AND ( LOWER(cb_title) = '".$chat."') LIMIT 1";
			$res_val2= $this->db->query($sql);
			$res2	= $res_val2->result_array();

			if(empty($res2)){
				$sql	= "SELECT id, cb_title, cb_keywords, cb_responsedata FROM chat_bot WHERE  is_active = true AND is_deleted = false AND ( LOWER(cb_title) ILIKE '%".$chat."%' OR  LOWER(cb_keywords) ILIKE '%".$chat."%' ) LIMIT 6";	
			}
			//$sql	= "SELECT id, cb_title, cb_keywords, cb_responsedata FROM chat_bot WHERE ( LOWER(cb_title) ILIKE '%".$chat."%' OR  LOWER(cb_keywords) ILIKE '%".$chat."%' ) LIMIT 1";
		}

		if((empty($chat) && !empty($id)) || (!empty($chat) && !empty($id))){
			$sql	= "SELECT id, cb_title, cb_keywords, cb_responsedata FROM chat_bot WHERE  is_active = true AND is_deleted = false AND id=".$id." LIMIT 8";
		}

		$res_val= $this->db->query($sql);
		$res	= $res_val->result_array();
		// print_r($res);exit;

		$replay = ['chat_bot_response' => null, 'chat_bot_replay' => null, 'chat_bot_error' => null];
		if(!empty($res)){
			if(count($res) > 1){
				foreach ($res as $key => $value) {
					$replay['chat_bot_response'][$value['id']] = $value['cb_title'];
				}
			} else {
				$replay['chat_bot_replay'] = $res;

				
				$sql2	= "SELECT id, cb_title, cb_keywords, cb_responsedata FROM chat_bot WHERE  is_active = true AND is_deleted = false AND LOWER(cb_title) ILIKE '%".strtolower($res[0]['cb_title'])."%' AND id!=".$res[0]['id']."  LIMIT 1 ";
				$res_val2= $this->db->query($sql2);
				$res2	= $res_val2->result_array();
				if(!empty($res2) && count($res2) > 0){
					foreach ($res2 as $key2 => $value2) {
						$replay['chat_bot_response'][$value2['id']] = $value2['cb_responsedata'];
					}
				}
				$chat	= $res[0]['cb_title'];
			}
		} else {
			$replay['chat_bot_error'] = 'Please ask relevent question!';
		}

		$response = array("success" => 1, "error" => 0, "status" => 1, "data" => $replay, "chat" => $chat, "message" => lang('Listed_Successfully'), 'sqldebug'=>$sql);
        
        $this->api_response($response);exit;
    }


    public function add_user_rating_post()
    {
        $user_id  = $this->input->post('user_id');
        $rating_value = $this->input->post('rating_value');
        $comment        = $this->input->post('comment');	
        $response = array();
        $response = array("status" => 0,"error" => 0,"message" => lang('Missing_Parameter'));
		

        if ($user_id != '' && $rating_value != '') {
			$insert = array(
                'user_id'   => $user_id, 
                'comment' => $comment,
                'rating_value' => $rating_value, 			
                'created_on'      => current_date(),
            );

            $result = $this->db->insert('ratings', $insert);

            if ($result) {

                    $response = array("success" => 0, "error" => 0, "status" => 0, "data" => $insert, "message" => lang('Added_Successfully'), "show_form" => 0);
               
                $this->api_response($response);
                exit;
            }         
        }elseif ($user_id != ''){
			$comment        = $this->input->post('comment');	
			$chk_rating = "SELECT rating_value,comment,created_on from ratings WHERE user_id = ".$user_id." and is_deleted = 'false'";
				$row    = $this->db->query($chk_rating);
				$result_rating = $row->result_array();
				if(count($result_rating)> 0){
	
					$response = array("success" => 0, "error" => 0, "status" => 0, "data" => $result_rating, "message" => "Rating already added.", "show_form" => 0);
					$this->api_response($response);
					exit;
	
				}else{
	
					$response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result_rating, "message" => "Please add rating.", "show_form" => 1);
					$this->api_response($response);
	
				}
			}else{
				$response = array("success" => 1, "error" => 1, "status" => 1, "data" => $result_rating, "message" => "Params missiing" , "show_form" => 1);			

			}
		$this->api_response($response);
		exit;

    }


	public function show_commodity_rates()
    {
        $lat  = $this->input->post('lat');
        $long = $this->input->post('long');
		$user_location = $this->input->post('user_location');
        $comment  = $this->input->post('comment');

        $response = array();
        $response = array("status" => 0,"error" => 0,"message" => lang('Missing_Parameter'));
		if ($user_id != ''){      
			
		$chk_rating = "SELECT id,market,product,variety,date,min_price,max_price,model_price,unit,arrival,country,state,city,is_active from commodity_price WHERE is_deleted = 'false'";

			$row    = $this->db->query($chk_rating);
            $result_rating = $row->result_array();
			if(count($result_rating)> 0){

				$response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result_rating, "message" => "Rating already added.");
                $this->api_response($response);
                exit;

			}else{

				$response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result_rating, "message" => "Please add rating.");
                $this->api_response($response);

			}
		}

        if ($user_id != '' && $rating_value != '' ) {	
            $insert = array(
                'user_id'   => $user_id, 
                'comment' => $comment,
                'rating_value' => $rating_value, 			
                'created_on'      => current_date(),
            );

            $result = $this->db->insert('ratings', $insert);

            if ($result) {

                if (count($insert)) {
                    
                    // $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $insert, "message" => "Chat Added Successfully", 'test_data' => $test_array);
                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $insert, "message" => lang('Added_Successfully'));
                }
                $this->api_response($response);
                exit;
            } else {
                $response = array("success" => 0, "error" => 1, "status" => 1, "data" => [], "message" => "Rating Add failed, please try again some time.");

                $this->api_response($response);
                exit;
            }
            $this->api_response($response);
        }
		$this->api_response($response);
		exit;

    }




	//19.97631/73.768565
	public function apmc_market_post()
	{
		
		$lat  = $this->input->post('lat');
		$long = $this->input->post('long');
		$user_location = $this->input->post('user_location')??'';
		$apmc_market = $this->input->post('apmc_market')??'';
		
		$apmc_market_data = '';
		
		$longitude = (float) $long;
		$latitude  = (float) $lat;

		//satara
		// $longitude = (float) 74.29827808;
		// $latitude = (float) 17.63612885;
		// $radius = 16; // in miles

		$limit = 10;

		if ($start != 0) {
			$start_sql = $limit * ($start);
		} else {
			$start_sql = 0;
		}

		if (empty($apmc_market)) {
			$select_data = " id, COALESCE( ( 6371 * acos( cos( radians($latitude) ) * cos( radians(latitude) ) * cos( radians( longitude ) - radians($longitude) ) + sin( radians($latitude) ) * sin( radians( latitude ) ) ) ) , 0) AS distance, apmc_market, latitude, longitude ";
			$where_condition	= " ";
			$order_by			= " ORDER BY distance ASC "; 
		} else {
			$select_data = " id, apmc_market, latitude, longitude ";
			$where_condition = " AND id = ". $apmc_market ." ";
			$order_by			= " "; 
		}

		$sql_location = "SELECT ". $select_data ." FROM apmc_location_master WHERE is_active = true AND is_deleted = false ". $where_condition ."  " . $order_by . " LIMIT 1 ";

		$res_val	= $this->db->query($sql_location);
		$res_array	= $res_val->row_array();

		// print_r($res_array);exit;

		if (!empty($res_array)) {
			$apmc_market_data = strtolower($res_array['apmc_market']);
		}

		// $apmc_market    = strtolower($res_array[0]['apmc_market']);
		// $latitude       = $res_array[0]['latitude'];
		// $longitude      = $res_array[0]['longitude'];
		// if ($apmc_market_data != '') {

		// 	$lcoations_data[] = array('apmc_market' => ucfirst(strtolower($apmc_market_data)), 'latitude' => $latitude, 'longitude' => $longitude);
		// 	//$result
		// 	$today     = date('Y-m-d');
		// 	$sql_limit = " LIMIT " . $limit . " OFFSET " . $start_sql;
		// 	$tbl_name   = "commodity_price";

		// 	$headers_data  = $this->input->request_headers();
		// 	$selected_lang = $headers_data['lang'];
		// 	$domain        = $headers_data['domain'];
		// 	$lang_label    = " commodityname as commodity ";

	
		// 	$data   = array();	   
		// 	$start  = $this->input->post('start');	   
		// 	$no     = isset($start) ? $start  : 1 ;
		// 	$limit  = 30;
		// 	$sql_limit = " LIMIT " . $limit . " OFFSET " . $start_sql;

		// 	$sql_query = "SELECT cp.id, m.name AS market_name, pm.title AS product_name, pv.title AS variety_name, cp.date, cp.min_price, cp.max_price, cp.model_price,cp.unit, cp.arrival, cn.name AS country_name, sn.name AS state_name, cp.city, cp.is_active,cp.created_on	   
		// 	FROM commodity_price cp	   
		// 	LEFT JOIN market_master m ON cp.market = m.market_id	   
		// 	LEFT JOIN prod_master pm ON cp.product = pm.id	   
		// 	LEFT JOIN prod_variety pv ON cp.variety = pv.id	   
		// 	LEFT JOIN states_new sn ON cp.state = sn.id	   
		// 	LEFT JOIN countries_new cn ON cp.country = cn.id	   
		// 	WHERE cp.is_deleted = 'false' AND cp.market = '".$apmc_market_data."'  ORDER BY cp.created_on DESC " . $sql_limit; 
			
		// 	$res_val   = $this->db->query($sql_query);
		// 	$res_commodity_array = $res_val->result_array();

		// 	if (count($res_commodity_array) > 0) {

		// 		foreach($res_commodity_array as $k=> $v){
		// 			$unitType_id = $res_commodity_array[$k]['unit'];
		// 			$unitTypeName = ''; 
		// 			foreach (PROD_UNIT as $unitType) {
		// 				if ($unitType['id'] == $unitType_id) {
		// 					$unitTypeName = $unitType['title'];
		// 					break; // Exit the loop once a match is found
		// 				}
		// 			}

		// 			$res_commodity_array[$k]['unit'] = $unitTypeName;

		// 		}


		// 		$response = array("success" => 1, "lcoations_data" => $lcoations_data, "data" => $res_commodity_array, "error" => 0, "status" => 1);
		// 		$this->api_response($response);

		// 	}else{
		// 		$response = array("success" => 0, "lcoations_data" => $lcoations_data, "data" => [], "error" => 0, "status" => 0);
		// 		$this->api_response($response);
		// 	}
		// 	//$result = $query->result_array();
		// }

		// $response = array("success" => 1, "lcoations_data" => $lcoations_data, "data" => $res_commodity_array, "error" => 0, "status" => 1, 'apmc_market' => $apmc_market);

		$response = array("success" => 1, "lcoations_data" => [], "data" => [], "error" => 0, "status" => 1, 'apmc_market' => []);
		$this->api_response($response);

	}




	public function commodity_price_post()
	{
		// Step 1: Get posted data
		$id		= $this->input->post('id')??'';
		$state	= $this->input->post('state')??'';
		$apmc_sql = '';

		//$latitude	= ($this->input->post('lat')) ? (float) $this->input->post('lat') : 17.63612885;
		//$longitude	= ($this->input->post('long')) ? (float) $this->input->post('long') : 74.29827808;

		$latitude	= ($this->input->post('lat')) ? (float) $this->input->post('lat') : '';
		$longitude	= ($this->input->post('long')) ? (float) $this->input->post('long') : '';
		// $location	= $this->input->post('location')??'';
		$apmc_market= $this->input->post('apmc_market')??'';
		$product_name	= $this->input->post('product_name')??'';
		$variety	= $this->input->post('variety')??'';
		$start		= $this->input->post('start')??'';
		$market_date = $this->input->post('market_date')??'';


		$radius		= 50; // in miles
		$res_details = array();
		$newarray = array();

		$limit = 10;
		if ($start != 0) {
			$start_sql = $limit * ($start);
		} else {
			$start_sql = 0;
		}


		// Step 1: Get apmc market ids distance from apmc market table
		$apmc_condition = $condition = " ";
		if(empty($apmc_market) && $latitude != '' && $longitude != '' ){
			$apmc_sql = "WITH DistanceCTE AS (
				SELECT am.id, am.apmc_market, am.latitude, am.longitude, COALESCE( ( 6371 * acos( cos( radians($latitude) ) * cos( radians(am.latitude) ) * cos( radians( am.longitude ) - radians($longitude) ) + sin( radians($latitude) ) * sin( radians( am.latitude ) ) ) ) , 0) AS distance
				FROM apmc_location_master am
			)
			SELECT *
			FROM DistanceCTE
			WHERE distance <= $radius
			ORDER BY distance;
			";
			$ampc_res_val	= $this->db->query($apmc_sql);
			$apmc_res		= $ampc_res_val->result_array();
			$apmc_id		= [];

			if(count($apmc_res) > 0){
				$apmc_id	= array_column($apmc_res, 'id');
				if($id == ''){
					$apmc_condition	.= " AND am.id IN (".implode(',', $apmc_id).") ";
				}
			}
		}
		
		// Common sql query use all over
		$sql = "SELECT
			tc.marketwiseapmcprice_id AS id,
			am.id AS market_id,
			am.apmc_market AS market_name,
			tc.commodityname AS product_name,
			tc.variety,
			tc.marketwiseapmcpricedate,
			tc.minimumprices,
			tc.maximumprices,
			tc.modalprices,
			tc.unitofprice,
			tc.arrivals,
			tc.is_active,
			am.latitude,
			am.longitude,
			tc.state_id ,
			pm.logo
		FROM tbl_commodity tc
		LEFT JOIN apmc_location_master am ON tc.market_id = am.id 	
		LEFT JOIN prod_master pm ON tc.pg_crop_master_id = pm.id
		WHERE 1=1 AND tc.is_active = 'true' ";
		if(!empty($apmc_market)){			
			$apmc_condition .= " AND am.id = $apmc_market";		
		}
		if(!empty($product_name)){
			$condition .= " AND LOWER(tc.commodityname) LIKE '%".strtolower($product_name)."'";	
		}
		if(!empty($variety)){
			$condition .= " AND LOWER(tc.variety) LIKE '%".strtolower($variety)."'";
		}
		if(!empty($id)){
			$condition .= " AND tc.marketwiseapmcprice_id = $id";	
		}

		if(!empty($state)){			
			$apmc_condition .= " AND tc.state_id  = '".$state."'";		
		}

		if($market_date != ''){
			$date_chk = date('d-m-Y', strtotime($market_date));
			$condition .= "  AND tc.marketwiseapmcpricedate  = '".$date_chk."' ";	
		}

		$order_by	= " ORDER BY marketwiseapmcpricedate desc, market_name asc ";
		$sql_limit	= " LIMIT " . $limit . " OFFSET " . $start_sql;

		$res_val	= $this->db->query($sql.$apmc_condition.$condition.$order_by.$sql_limit);
		$res		= $res_val->result_array();

		
		if(count($res)> 0 &&   $id != ''){

			$market_id = $res[0]['market_id'];
			$product_name = $res[0]['product_name'];
			$variety = $res[0]['variety'];
			$condition_details = '';
			$condition_details = " AND tc.market_id = $market_id";	
			$condition_details .= " AND LOWER(tc.commodityname) LIKE '%".strtolower($product_name)."'";		
			$condition_details .= " AND LOWER(tc.variety) LIKE '%".strtolower($variety)."'";
			$res_val_details   = $this->db->query($sql.$apmc_condition.$condition_details.$order_by);
			$res_details	= $res_val_details->result_array();	
			foreach($res_details as $v){
				$v['unitofprice'] =  $this->getTitleById($v['unitofprice']) ;
				if ( $v['logo'] === null) {
					$v['logo'] = $this->base_path."uploads/config_master/prod_master/mix_fruit.png"; 
				}else{
					$v['logo'] = $this->base_path."uploads/config_master/prod_master/".$v['logo'];
				}
				$more_details[] = $v;
			}
			
			


		}

		if(empty($res)){

			$common_res	= $this->db->query($sql.$condition.$order_by.$sql_limit);
			$res		= $common_res->result_array();
			
		}

		

		foreach ($res as $value) {
			//$newarray[] = $value;
			//PROD_UNIT
			
			$value['unitofprice'] =  $this->getTitleById($value['unitofprice']) ;
			if ( $value['logo'] === null) {
				$value['logo'] = $this->base_path."uploads/config_master/prod_master/mix_fruit.png"; // Set default value for empty key				
				$newarray[] = $value;				
			}else{
				$value['logo'] = $this->base_path."uploads/config_master/prod_master/".$value['logo'];
				$newarray[] = $value;
			}
		}

		//print_r($newarray);
		$last_query = $this->db->last_query();
		$data = $newarray;
		$response = array("success" => 1, "data" => $data, "error" => 0, "status" => 1, "limit" => $limit, "total" => count($res), 'last_query'=>$last_query,'more_details'=>$more_details,"apmcres"=>$apmc_sql,'uint_master'=>PROD_UNIT);
		$this->api_response($response);exit;
	}


	public function getTitleById($id, $prod_unit = PROD_UNIT) {
		foreach ($prod_unit as $units) {
		  if ($units['id'] == $id) {
			return $units['short_title'];
		  }
		}
		return $id; // If no matching ID is found, return null.
	  }


	public function commodity_state_get()
	{
		$table  = 'apmc_location_master';
		$query  = "SELECT state_name FROM $table WHERE is_deleted = 'false' AND is_active = 'true' GROUP BY state_name"; 
		$row    = $this->db->query($query);
		$result = $row->result_array();

		// $state_data['state'] = array_column($result, 'state_name');

	
		if (!empty($result)) {
			$response = array("success" => 1, "data" => $result, "message" => lang('Listed_Successfully'));
		} else {
			$response = array("success" => 0, "data" => [], "message" => lang('Data_Not_Found'));
		}

		$this->api_response($response);exit;
	}


	public function market_list_post()
	{
		// Step 1: Get posted data
		//$id			= $this->input->post('id')??'';
		$state = $this->input->post('state');
		$latitude	= ($this->input->post('lat')) ? (float) $this->input->post('lat') : 17.63612885;
		$longitude	= ($this->input->post('long')) ? (float) $this->input->post('long') : 74.29827808;
		// $location	= $this->input->post('location')??'';
		//$apmc_market= $this->input->post('apmc_market')??'';

		$select_data = " id, apmc_market, latitude, longitude , state_name";

		if($latitude != '' && $longitude != '' && $state == '' ) {

			$select_data = " id,  COALESCE( ( 6371 * acos( cos( radians($latitude) ) * cos( radians(latitude) ) * cos( radians( longitude ) - radians($longitude) ) + sin( radians($latitude) ) * sin( radians( latitude ) ) ) ) ,0) AS distance , apmc_market, latitude, longitude , state_name";
			$where_condition	= " ";
			$order_by			= " ORDER BY distance ASC ";

			$sql_location = "SELECT ". $select_data ." FROM apmc_location_master WHERE is_active = true AND is_deleted = false ". $where_condition ."  " . $order_by . " ";

			$ampc_res_val	= $this->db->query($sql_location);
			$apmc_res		= $ampc_res_val->result_array();	
			if(count($apmc_res)){
				$response = array("success" => 1, "data" => $apmc_res, "error" => 0, "status" => 1);
				
			}else{
				$response = array("success" => 1, "data" => [], "error" => 0, "status" => 1);			
			}

		}else{

			if($state != ''){

				$where_condition	= " AND state_name = '".$state."' "; 
				$sql_location = "SELECT ". $select_data ." FROM apmc_location_master WHERE is_active = true AND is_deleted = false ". $where_condition ."  " . $order_by . " ";

				$ampc_res_val	= $this->db->query($sql_location);

				$apmc_res		= $ampc_res_val->result_array();	
				if(count($apmc_res)){
					$response = array("success" => 1, "data" => $apmc_res, "error" => 0, "status" => 1);
				} else {
					$response = array("success" => 1, "data" => [], "error" => 0, "status" => 1);
				}


				$this->api_response($response);exit;

			}
			
			$response = array("success" => 1, "data" => [], "error" => 0, "status" => 1);
		}

		$this->api_response($response);exit;
	}


	public function commodity_list_post()
	{
		// Step 1: Get posted data
		//$id			= $this->input->post('id')??'';

		$latitude	= ($this->input->post('lat')) ? (float) $this->input->post('lat') : 17.63612885;
		$longitude	= ($this->input->post('long')) ? (float) $this->input->post('long') : 74.29827808;
		// $location	= $this->input->post('location')??'';
		$apmc_market= $this->input->post('apmc_market')??'';

		if($latitude != '' && $longitude != '' ) {

			$select_data = " id,  COALESCE( ( 6371 * acos( cos( radians($latitude) ) * cos( radians(latitude) ) * cos( radians( longitude ) - radians($longitude) ) + sin( radians($latitude) ) * sin( radians( latitude ) ) ) ) ,0) AS distance , apmc_market, latitude, longitude ";
			$where_condition	= " ";
			$order_by			= " ORDER BY distance ASC ";

			$sql_location = "SELECT ". $select_data ." FROM apmc_location_master WHERE is_active = true AND is_deleted = false ". $where_condition ."  " . $order_by . " ";

			$ampc_res_val	= $this->db->query($sql_location);
			$apmc_res		= $ampc_res_val->result_array();	
			if(count($apmc_res)){
				$response = array("success" => 1, "data" => $apmc_res, "error" => 0, "status" => 1);
				
			}else{
				$response = array("success" => 1, "data" => [], "error" => 0, "status" => 1);			
			}

		}else{

			$response = array("success" => 1, "data" => [], "error" => 0, "status" => 1);
			
	}

		$this->api_response($response);exit;
	}


	public function commodity_details_post()
	{
		// Step 1: Get posted data
		//$id			= $this->input->post('id')??'';
		$id = $this->input->post('id')??'';
		

		if($id != '') {

			

			$ampc_res_val	= $this->db->query($sql_location);
			$apmc_res		= $ampc_res_val->result_array();	
			if(count($apmc_res)){
				$response = array("success" => 1, "data" => $apmc_res, "error" => 0, "status" => 1);
				
			}else{
				$response = array("success" => 1, "data" => [], "error" => 0, "status" => 1);			
			}

		}else{

				$response = array("success" => 1, "data" => [], "error" => 0, "status" => 1);
				
		}

		$this->api_response($response);exit;
	}

	function getClientData($clinet_id)
    {
		if(!empty($clinet_id)){
			$sql = "SELECT id, first_name, last_name, profile_image from client 
            WHERE  is_active = true  AND  is_deleted = false AND id = '".$clinet_id."'";
			$rest = $this->db->query($sql);
			$data = $rest->row_array(); 
		} else {
			$data = [];
		}

        return $data;
    }


	

}
