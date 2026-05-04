<?php

defined('BASEPATH') or exit('No direct script access allowed');



error_reporting(E_ERROR | E_PARSE);

//error_reporting(E_ERROR | E_PARSE);



//error_reporting(E_ALL);



require APPPATH. 'libraries/RestController.php';



use chriskacerguis\RestServer\RestController;



class Notification extends RestController

{



    public function __construct()

    {

		header("Access-Control-Allow-Origin: *");

        header("Access-Control-Allow-Headers: *");

        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

        parent::__construct();

        $headers_data = $this->input->request_headers();

        $headers_data['domain'] = $headers_data['Domain'];
        $headers_data['appname'] = $headers_data['Appname'];
		$headers_data['client-type'] = $headers_data['Client_type'];
		$headers_data['client-type'] = $headers_data['Client_type'];

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



        // Start: Create upload file name and as per database name : Akash

        $this->connected_domain = '';

        $root_folder            = $_SERVER['DOCUMENT_ROOT'] . MAIN_FOLDER . '/';



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



    public function userwise_notification_count_post(){
        $user_id  = $this->input->post('user_id');

        if($user_id){

          $sql1        = "SELECT count(unt.id) As count FROM user_notifications_table AS unt INNER JOIN notifications_table as nt ON nt.id = unt.notification_id WHERE nt.map_key!='chat_notification' AND nt.reference_id='client' AND unt.is_read =0 AND unt.is_notify =0 AND unt.user_id=".$user_id;

            $row1            = $this->db->query($sql1);

            $notification_cnt_data = $row1->result_array();



            if(empty($notification_cnt_data)){

                $response = array("success" => 0, "unread_count" => [], "message" => 'No Record Found!');

            }

            else{

                $response = array("success" => 1, "unread_count" => $notification_cnt_data, "message" => 'Listed Successfully!');

            }



        }else{

            $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        }

		$this->api_response($response);

        exit;

    }

    public function userwise_notification_data_post(){

        $user_id  = $this->input->post('user_id');

        if($user_id){

           $sql1        = "SELECT unt.user_id,unt.notification_id,unt.is_read,nt.title,nt.message,nt.created_on,nt.other_details FROM user_notifications_table AS unt INNER JOIN notifications_table as nt ON nt.id = unt.notification_id WHERE nt.map_key!='chat_notification' AND nt.reference_id='client'  AND unt.user_id=".$user_id." ORDER BY nt.created_on DESC";

            $row1            = $this->db->query($sql1);

            $notification_data = $row1->result_array();

            if(!empty($notification_data)){

                foreach($notification_data as $k => $v){

                    if($v['other_details']!=''){

                        $notification_data[$k]['other_details']  = json_decode($v['other_details'], true);

                    }

                }



            }

           

            if(empty($notification_data)){

                $response = array("success" => 0, "notification_data" => [], "message" => 'No Record Found!');

            }

            else{

                $response = array("success" => 1, "notification_data" => $notification_data, "message" => 'Listed Successfully!');

            }



        }else{

            $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        }

		$this->api_response($response);

        exit;

    }

    // read notification by user

    public function read_notification_post()

    {

        $user_id				 = $this->input->post('user_id');

        $notification_id		 = $this->input->post('notification_id');



        $response   = array();



        if ($user_id != '' && $notification_id !='') {

            $this->db->where('user_id', $user_id);

            $this->db->where('notification_id', $notification_id);

            $update_res = $this->db->update('user_notifications_table', array('is_read' => 1));

            $response = array('success'=>1, 'data'=>  $update_res, 'message' => 'Updated Successfully');



        } else {

            $response = array("status" => 0, "data" =>[] , "message" => lang('Missing_Parameter'));

        }

        $this->api_response($response);

        exit;

    }

     // notify notification by user

     public function notifyuser_post()

     {

         $user_id				 = $this->input->post('user_id');

         //$notification_id		 = $this->input->post('notification_id');

 

         $response   = array();

 

         if ($user_id != '') {

            $conditions = array(

                'user_id' => $user_id,

                'is_notify' => 0

            );

            

            $update_data = array('is_notify' => 1);

            $update_res = $this->db->update('user_notifications_table', $update_data, $conditions);

            $updated_row = $this->db->affected_rows();

            $response = array('success'=>1, 'data'=>  $update_res,'update_count' => $updated_row , 'message' => 'Updated Successfully');

 

         } else {

             $response = array("status" => 0, "data" =>[] , "message" => lang('Missing_Parameter'));

         }

         $this->api_response($response);

         exit;

     }

	



}

