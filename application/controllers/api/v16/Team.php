<?php

defined('BASEPATH') or exit('No direct script access allowed');



error_reporting(E_ERROR | E_PARSE);

//error_reporting(E_ERROR | E_PARSE);



//error_reporting(E_ALL);



class Team extends CI_Controller

{



    public function __construct()

    {

        parent::__construct();

        // $this->load->model('Product_model');



        header('Access-Control-Allow-Origin: *');

        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");

        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");



        $this->load->library('upload');

        $this->load->model('Email_model');

        $this->load->helper('log_helper');

        $this->load->helper('sms_helper');

        $this->load->model('Masters_model');

        $lang_folder = "english";

        //echo 'LANG :'.$this->session->userdata('user_site_language');

        //$lang_folder = "marathi";

        $headers_data = $this->input->request_headers();

        // $headers = $this->input->request_headers();

        // $headers_data = array_change_key_case($headers, CASE_LOWER);

        // print_r($headers_data)

        // Start: Required headers and there value check

        // if ((!strpos($_SERVER['REQUEST_URI'], 'partner_login')) || (!strpos($_SERVER['REQUEST_URI'], 'dynamic_domain_db_connection'))) {

        if (!strpos($_SERVER['REQUEST_URI'], 'dynamic_domain_db_connection')) {

        	$require_headers    = array('Domain', 'Appname');

        }else{

            $require_headers    = array('Domain');

        }

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

            $msg                = "Invalid Request";

            $response           = array("status" => 0, "error" => 1, "data" => array(), "message" => $msg);

            $this->api_response($response);exit;

        } else if (!empty($require_header_val) && count($require_header_val) > 0) {

            $require_header_str = implode(', ', $require_header_val);

            $msg                = "Invalid Request";

            $response           = array("status" => 0, "error" => 1, "data" => array(), "message" => $msg);

            $this->api_response($response);exit;

        }

        // End: Required headers and there value check



        // Start: Create upload file name and as per database name : Akash

        $this->connected_appname = '';

        $this->connected_domain = '';

        $root_folder            = $_SERVER['DOCUMENT_ROOT'] .'/'. MAIN_FOLDER . '/';



        $this->connected_appname = strtolower($headers_data['appname']); // globaly set connected appname name

        $this->connected_domain = strtolower($headers_data['domain']); // globaly set connected domain name

        $db_folder              = $root_folder . 'uploads/' . $this->connected_domain;



        $this->load->library('upload');

        $this->load->model('Email_model');

        $this->load->helper('log_helper');

        $this->load->helper('sms_helper');

        $this->load->model('Masters_model');

        $lang_folder = "english";

        //echo 'LANG :'.$this->session->userdata('user_site_language');

        //$lang_folder = "marathi";

        if ($this->session->userdata('user_site_language') && $this->session->userdata('user_site_language') == "MR") {

            $lang_folder = "marathi";

        } else {

            $this->session->set_userdata('user_site_language', 'EN');

            $lang_folder = "english";

        }



        //$base_path = $this->base_path;

        $this->base_path = $base_path = BASE_PATH_PORTAL;



        /*    $this->config_url = array(

        'category_img_url' => $base_path.'uploads/category/',

        'partner_img_url'  => $base_path.'uploads/profile/',

        'pan_no_doc_url'  => $base_path.'uploads/aadhar_no/',

        'aadhar_no_doc_url'  => $base_path.'uploads/pan_no/',

        'farm_image_url'  => $base_path.'uploads/farm/',

        );*/



        //http://115.124.120.147/marketplace/uploads/advertise_master



        $this->config_url = array(

            'crop_product_img'        => $base_path . 'uploads/farm/',

            'category_img_url'        => $base_path . 'uploads/category/',

            'partner_img_url'         => $base_path . 'uploads/profile/',

            'aadhar_no_doc_url'       => $base_path . 'uploads/aadhar_no/',

            'pan_no_doc_url'          => $base_path . 'uploads/pan_no/',

            'farm_image_url'          => $base_path . 'uploads/farm/',

            'Product_image_url'       => $base_path . 'uploads/productcategory/',

            'market_cat_image_url'    => $base_path . 'uploads//logo/',

            'service_image_url'       => $base_path . 'uploads/product_service/',

            'blogs_types_url'         => $base_path . 'uploads/blogs_types/',

            'blogs_tags_url'          => $base_path . 'uploads/blogs_tags/',

            'created_blogs_url'       => $base_path . 'uploads/created_blogs/',

            'farmer_documents_url'    => $base_path . 'uploads/verification_documents/',

            'advertise_image_url'     => $base_path . 'uploads/advertise_master/',

            'whitelabel_image_url'    => $base_path . 'uploads/client_group_master/',

            'terms_sheet'             => $base_path . 'uploads/terms_sheet/',

            'farm_doc'                => $base_path . 'uploads/farm_doc/',

            'insurance_company'       => $base_path . 'uploads/insurance_company/',

            'crop_image_url'          => $base_path . 'uploads/crops/',

            'crop_type_url'           => $base_path . 'uploads/crop_type_icon/',

            'crop_invoice_url'        => $base_path . 'uploads/crop_invoice/',

            'crop_health_predict_api' => 'http://115.124.96.136:8443/predict',

            'privacy_policy'          => 'https://gfreshagrotech.com/privacy-policy/',

            'terms_and_conditions'    => 'https://gfreshagrotech.com/terms-and-conditions/',



        );



        // 'crop_health_predict_api'  => 'http://115.124.96.136:8443/predict',

        $this->menu = array(

            array('id' => '1', 'title' => 'Profile', 'icon' => 'user_prof'),

            array('id' => '2', 'title' => 'Home', 'icon' => 'home'),

            array('id' => '3', 'title' => 'My Farms', 'icon' => 'my_farm'),

            array('id' => '6', 'title' => 'My Orders', 'icon' => 'my_orders'),

            array('id' => '9', 'title' => 'Change Language', 'icon' => 'change_language'),

            array('id' => '10', 'title' => 'Logout', 'icon' => 'logout'),

            array('id' => '14', 'title' => 'Invite', 'icon' => 'Invite_icon'),

            array('id' => '15', 'title' => 'About us', 'icon' => 'about_us'),

            array('id' => '16', 'title' => 'Privacy_Policy', 'icon' => 'about_us'),

            array('id' => '16', 'title' => 'terms_and_conditions', 'icon' => 'about_us'),

        );



        // array('id' => '13', 'title' => 'Disease Detection', 'icon' => 'Disease_icon'),



        // array('id' => '13', 'title' => 'Disease Detection', 'icon' => 'Disease_icon'),



        $this->home_message = array('message' => 'Welcome to GFresh');



        $this->soil_type = array('1' => 'Light Clay', '2' => 'Medium red', '3' => 'Black', '4' => 'Medium black', '5' => 'Black solid', '6' => 'Limestone / Sherwat');



        $this->topology = array(array('id' => '1', 'value' => 'High', 'name_mr' => 'उंच'), array('id' => '2', 'value' => 'Low', 'name_mr' => 'कमी'), array('id' => '3', 'value' => 'Medium', 'name_mr' => 'मध्यम'));



        $this->topology_web = array('1' => 'High', '2' => 'Low', '3' => 'Medium');



        $this->topology_web_mr = array('1' => 'उंच', '2' => 'कमी', '3' => 'मध्यम');



        $this->farm_type = array('1' => 'Organic Farming', '2' => 'Conventional Farming', '3' => 'Residue Free Farming');



        /* $this->unit = array('1' => 'Square Yard', '2' => 'Acre', '3' => 'Hectare', '4' => 'Square Meter', '5' => 'Square Mile');*/

        $this->unit = array('1' => 'Hectare', '2' => 'Acre');



        $this->irri_src = array('1' => 'Well', '2' => 'Borewell', '3' => 'Canal/River', '4' => 'Farm lake', '5' => 'Others');



        $this->irri_faty = array('1' => 'Pipelines', '2' => 'Sprinkler Heads', '3' => 'Valves');



        // $this->crop_type = array('1' => 'Kharif', '2' => 'Rabi', '3' => 'fruits');



/////////////////////////array  set for web and app both MMM////////////////////



        $this->topology_web    = array('1' => 'High', '2' => 'Low', '3' => 'Medium');

        $this->topology_web_mr = array('1' => 'उंच', '2' => 'कमी', '3' => 'मध्यम');



        /*$this->unit_web = array('1' => 'Square Yard', '2' => 'Acre', '3' => 'Hectare', '4' => 'Square Meter', '5' => 'Square Mile');

        $this->unit_web_mr = array('1' => 'स्क्वेअर यार्ड', '2' => 'एकर', '3' => 'हेक्टर', '4' => 'चौरस मीटर', '5' => 'स्क्वेअर माईल');*/



        $this->unit_web_mr = array('1' => 'हेक्टर', '2' => 'एकर');



        $this->unit_web = array('1' => 'Hectare', '2' => 'Acre');



        $this->crop_type_web = array('1' => 'Kharif', '2' => 'Rabi', '3' => 'fruits');



        $this->crop_type_web_mr = array('1' => 'खरिफ', '2' => 'रुबी', '3' => 'फळे');



        $this->crop_web_mr = array('1' => 'डाळींब', '2' => 'द्राक्षे', '3' => 'शिमला', '4' => 'इतरपिके', '5' => 'फ्लोरिकल्चर', '6' => 'संत्री', '7' => 'आंबा', '8' => 'मोसंबी');



        $this->crop_web = array('1' => 'Pomegranate', '2' => 'Grapes', '3' => 'Capsicum', '4' => 'Othercrops', '5' => 'Floriculture', '6' => 'Orange', '7' => 'Mango', '8' => 'Citrus');



        $this->irri_src_web = array('1' => 'Well', '2' => 'Borewell', '3' => 'Canal/River', '4' => 'Farm lake', '5' => 'Others');



        $this->irri_src_web_mr = array('1' => 'विहीर', '2' => 'बोअरवेल', '3' => 'कालवा / नदी', '4' => 'शेत तलाव', '5' => 'इतर');



        $this->irri_faty_web    = array('1' => 'Pipelines', '2' => 'Sprinkler Heads', '3' => 'Valves');

        $this->irri_faty_web_mr = array('1' => 'पाईपलाईन', '2' => 'शिंपडण्याचे प्रमुख', '3' => 'वाल्व्ह');



        $this->soil_type_web    = array('1' => 'Light Clay', '2' => 'Medium red', '3' => 'Black', '4' => 'Medium black', '5' => 'Black solid', '6' => 'Limestone / Sherwat');

        $this->soil_type_web_mr = array('1' => 'हलकी चिकणमाती', '2' => 'मध्यम लाल', '3' => 'काळा', '4' => 'मध्यम  काळा', '5' => 'काळा घन', '6' => 'चुनखडी / शेरवत');



        $this->farm_type_web    = array('1' => 'Organic Farming', '2' => 'Conventional Farming', '3' => 'Residue Free Farming');

        $this->farm_type_web_mr = array('1' => 'सेंद्रिय शेती', '2' => 'पारंपारिक शेती', '3' => 'अवशेष मुक्त शेती');



        ///////////////////////////////////



        $this->crop_type = array(

            array('id' => '1', 'value' => 'Kharif', 'name_mr' => 'खरिफ'),

            array('id' => '2', 'value' => 'Rabi', 'name_mr' => 'रुबी'),

            array('id' => '3', 'value' => 'fruits', 'name_mr' => 'फळे'),



        );



        $this->soil_type = array(

            array('id' => '1', 'value' => 'Light Clay', 'name_mr' => 'हलकी चिकणमाती'),

            array('id' => '2', 'value' => 'Medium red', 'name_mr' => 'मध्यम लाल'),

            array('id' => '3', 'value' => 'Black', 'name_mr' => 'काळा'),

            array('id' => '4', 'value' => 'Medium black', 'name_mr' => 'मध्यम  काळा'),

            array('id' => '5', 'value' => 'Black solid', 'name_mr' => 'काळा घन'),

            array('id' => '6', 'value' => 'Limestone / Sherwat', 'name_mr' => 'चुनखडी / शेरवत'),

        );



        $this->farm_type = array(

            array('id' => '1', 'value' => 'Organic Farming', 'name_mr' => 'सेंद्रिय शेती'),

            array('id' => '2', 'value' => 'Conventional Farming', 'name_mr' => 'पारंपारिक शेती'),

            array('id' => '3', 'value' => 'Residue Free Farming', 'name_mr' => 'अवशेष मुक्त शेती'),



        );



        $this->unit = array(

            array('id' => '1', 'value' => 'Square Yard', 'name_mr' => 'स्क्वेअर यार्ड'),

            array('id' => '2', 'value' => 'Acre', 'name_mr' => 'एकर'),

            array('id' => '3', 'value' => 'Hectare', 'name_mr' => 'हेक्टर'),

            array('id' => '4', 'value' => 'Square Meter', 'name_mr' => 'चौरस मीटर'),

            array('id' => '5', 'value' => 'Square Mile', 'name_mr' => 'स्क्वेअर माईल'),



        );



        $this->irri_src = array(

            array('id' => '1', 'value' => 'Well', 'name_mr' => 'विहीर'),

            array('id' => '2', 'value' => 'Borewell', 'name_mr' => 'बोअरवेल'),

            array('id' => '3', 'value' => 'Canal/River', 'name_mr' => 'कालवा / नदी'),

            array('id' => '4', 'value' => 'Farm lake', 'name_mr' => 'शेत तलाव'),

            array('id' => '5', 'value' => 'Others', 'name_mr' => 'इतर'),



        );

        $this->irri_faty = array(

            array('id' => '1', 'value' => 'Pipelines', 'name_mr' => 'पाईपलाईन'),

            array('id' => '2', 'value' => 'Sprinkler Heads', 'name_mr' => 'शिंपडण्याचे प्रमुख'),

            array('id' => '3', 'value' => 'Valves', 'name_mr' => 'वाल्व्ह'),



        );

        $this->crop = array(

            array('id' => '1', 'value' => 'Pomegranate', 'name_mr' => 'डाळींब'),

            array('id' => '2', 'value' => 'Grapes', 'name_mr' => 'द्राक्षे'),

            array('id' => '3', 'value' => 'Capsicum', 'name_mr' => 'शिमला'),

            array('id' => '4', 'value' => 'Othercrops', 'name_mr' => 'इतरपिके'),

            array('id' => '5', 'value' => 'Floriculture', 'name_mr' => 'फ्लोरिकल्चर'),

            array('id' => '6', 'value' => 'Orange', 'name_mr' => 'संत्री'),

            array('id' => '7', 'value' => 'Mango', 'name_mr' => 'आंबा'),

            array('id' => '8', 'value' => 'Citrus', 'name_mr' => 'मोसंबी'),



        );



        $this->topology = array(

            array('id' => '1', 'value' => 'High', 'name_mr' => 'उंच'),

            array('id' => '2', 'value' => 'Low', 'name_mr' => 'कमी'),

            array('id' => '3', 'value' => 'Medium', 'name_mr' => 'मध्यम'),

        );



        $this->vehicle_type = array(

            array('id' => 'pickup', 'value' => 'Pickup'),

            array('id' => 'tractor', 'value' => 'Tractor'),

        );



        $this->poi = array(

            array('id' => '1', 'value' => 'Aadhaar_card', 'name_mr' => 'Aadhaar_card_m'),

            array('id' => '2', 'value' => 'PAN_card', 'name_mr' => 'PAN_card_m'),

            array('id' => '3', 'value' => 'Lite_bill', 'name_mr' => 'Lite_bill_m'),

            array('id' => '4', 'value' => 'Voter_id_card', 'name_mr' => 'Voter_id_card_m'),

            array('id' => '5', 'value' => 'Driving_License', 'name_mr' => 'Driving_License_m'),

        );



        $this->poa = array(

            array('id' => '1', 'value' => 'Aadhaar_card', 'name_mr' => 'Aadhaar_card_m'),

            array('id' => '3', 'value' => 'Lite_bill', 'name_mr' => 'Lite_bill_m'),

            array('id' => '5', 'value' => 'Driving_License', 'name_mr' => 'Driving_License_m'),

        );



        $this->splash_data[] = array('title' => 'Why do we use it?', 'description' => 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using Content here content here making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for lorem ipsum will uncover many web sites still in their infancy. Various versions have evolved over the years');

        $this->splash_data[] = array('title' => 'What is Lorem Ipsum?', 'description' => 'The standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested. Sections');

        $this->splash_data[] = array('title' => 'Where does it come from?', 'description' => 'Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock a Latin professor at Hampden-Sydney Colleges');

        //$this->lang->load(array('loan'),$lang_folder);

        $this->lang->load(array('site'), $lang_folder);



    }



    /**

     *

     */

    public function index()

    {



        $response = array("status" => 0, "message" => "");

        $row      = $this->db->get('users');

        //$row = $this->db->get('users')->where('U.is_deleted = false and U.email_verify = true');

        $result = $row->result_array();

        if (count($result)) {

            $response = array("status" => 1, "data" => $result, "message" => "User data");

        }

        $this->response($response);

    }



    public function splash_data()

    {

        $result['splash_data'] = $this->splash_data;

        $response              = array("status" => 1, "data" => $result, "message" => "splash screen data");

        $this->response($response);

    }



    public function is_user_regsitered()

    {



        if ($this->input->post('phone') != '') {



            $phone = substr(preg_replace('/\s+/', '', $this->input->post('phone')), -10, 10);



            $row    = $this->db->query("SELECT * FROM admins WHERE is_deleted = 'false' and username::varchar = '$phone'::varchar ");

            $result = $row->result_array();

            if (count($result)) {



                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "User is Registered", 'is_registered' => 1);

                $this->response($response);

                exit;



            } else {



                $response = array("success" => 0, "error" => 0, "status" => 0, "data" => null, "message" => "User is not Registered", 'is_registered' => 0);

                $this->response($response);

                exit;

            }



        } else {



            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Err !! Username is blank");

            $this->response($response);

            exit;



        }

    }



    public function delete_number($phone_number)

    {



        $phone      = substr(preg_replace('/\s+/', '', $phone_number), -10, 10);

        $update_arr = array('is_deleted' => true);

        $this->db->where('client.phone', $phone);

        $result = $this->db->update('client', $update_arr);



        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "User removed");



        $this->response($response);

        exit;



    }



    public function resend_otp()

    {

        // $this->load->helper('sms_helper');

        $phone      = substr(preg_replace('/\s+/', '', $this->input->post('phone')), -10, 10);

        $opt_number = mt_rand(100000, 999999);



        $result   = array();

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Params missing.");



        if ($phone != '' && $opt_number != '') {



            $sql_chk = "SELECT id,is_active FROM client WHERE is_deleted = 'false' and phone::varchar = '$phone'::varchar LIMIT 1";

            $row     = $this->db->query($sql_chk);

            $result  = $row->result_array();

            if (count($result)) {



                if ($result[0]['is_active'] == "t") {



                    $update_arr['opt_number'] = $opt_number;



                    //$opt_number = mt_rand(100000,999999);

                    $sms_type = 1; // for OTP its once // 7448148405

                    // $mobile   = substr(preg_replace('/\s+/', '', $this->input->post('phone')), -10, 10);

                    $mobile = $phone;

                    $text   = 'Your OTP for GFresh is ' . $opt_number . '. Please enter OTP into the app to verify your account. Thank you - GFresh Team.';

                    //$mobile   = 7448148405;



                    if ($phone == 9876543210 || $phone == 9976543210) {

                        $opt_number               = 643215;

                        $update_arr['opt_number'] = $opt_number;

                    } else {

                        $update_arr['opt_number'] = $opt_number;

                        $resp                     = send_sms($mobile, $text, $sms_type);



                    }



                    if (count($update_arr)) {



                        $id = $result[0]['id'];

                        $this->db->where('client.id', $id);

                        $this->db->update('client', $update_arr);

                        /*$id = $result[0]['id'];

                    $this->db->where( phone::varchar = '$phone'::varchar);

                    $result = $this->db->update('client', $update_arr);*/



                    }



                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => "NULL", "message" => "OTP reset successfully", 'opt_number' => $opt_number);

                    $this->response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 2, "data" => "NULL", "message" => "Your account is not active : contact Admin", 'opt_number' => $opt_number, 'resp_query' => $result[0]['is_active']);

                    $this->response($response);

                    exit;

                }



            } else {



                $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "sql_chk" => $sql_chk, "message" => "Mobile Number is not Registed : " . $this->input->post('phone'));

                $this->response($response);

                exit;

            }

        }



        $this->response($response);

        exit;

    }



    public function get_login_otp()

    {



        $result     = array();

        $update_arr = array();



        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $_POST, "message" => "missing params");



        if ($this->input->post('btn_submit') == 'submit') {

            $username = substr(preg_replace('/\s+/', '', $this->input->post('phone')), -10, 10);

            //$password = $this->input->post('password');

            if ($username == 9876543210) {

                $otp = 643215;

            } else {

                $otp = $this->input->post('otp');

            }



            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $_POST, "message" => "missing err 33");



            $row       = $this->db->query("SELECT * FROM client WHERE is_deleted = 'false' and phone::varchar = '$username'::varchar ");

            $user_data = $row->result_array();



            $this->db->select('*');

            $this->db->where('is_deleted', 'false');

            $countries = $this->db->get('countries')->result_array();



            $where_cat = array('is_deleted' => 'false', 'is_active' => 'true');

            $this->db->select('*');

            $this->db->where($where_cat);

            $this->db->order_by("seq", "asc");

            $categories = $this->db->get('categories')->result_array();



            $where_cat_p = array('is_deleted' => 'false', 'is_active' => 'true');

            $this->db->select('*');

            $this->db->where($where_cat_p);

            $pcategories = $this->db->get('pcategories')->result_array();



            $row_blog     = $this->db->query("SELECT blogs_types_id ,name ,logo ,name_mr ,mob_icon FROM blogs_types_master WHERE is_active = 'true' AND is_deleted = 'false' AND is_home =1  ORDER BY seq ASC");

            $result_blogs = $row_blog->result_array();



            $row = $user_data;



            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $row, "message" => "missing err 33");



            if (count($row)) {



                $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $row, "message" => "missing err 3464 :" . $row[0]['opt_number']);



                if ($user_data[0]['opt_number'] == $otp) {



                    if ($this->input->post('latitude')) {

                        $update_arr['latitude'] = $this->input->post('latitude');

                    }



                    if ($this->input->post('longitude')) {

                        $update_arr['longitude'] = $this->input->post('longitude');

                    }



                    if ($this->input->post('city_name')) {

                        $update_arr['city_name'] = $this->input->post('city_name');

                    }



                    if ($this->input->post('device_id')) {

                        $update_arr['device_id'] = $this->input->post('device_id');

                    }



                    if ($this->input->post('loc_addresss')) {

                        $update_arr['loc_addresss'] = $this->input->post('loc_addresss');

                    }



                    if ($this->input->post('phone')) {

                        $update_arr['is_login'] = true;

                    }



                    if (!$row[0]['my_refferal_code']) {

                        //$t=time();

                        $update_arr['my_refferal_code'] = time();

                    }



                    $update_arr['login_count'] = $row[0]['login_count'] + 1;



                    if (count($update_arr)) {



                        $id = $row[0]['id'];

                        $this->db->where('client.id', $id);

                        $result = $this->db->update('client', $update_arr);



                    }



                    $data = array(

                        'user_id'          => $row[0]['id'],

                        'first_name'       => $row[0]['first_name'],

                        'last_name'        => $row[0]['last_name'],

                        'email'            => $row[0]['email'],

                        'phone'            => $row[0]['phone'],

                        'address1'         => $row[0]['address1'],

                        'address2'         => $row[0]['address2'],

                        'city'             => $row[0]['city'],

                        'postcode'         => $row[0]['postcode'],

                        'country_name'     => $row[0]['country_name'],

                        'state_name'       => $row[0]['state_name'],

                        'branch_name'      => $row[0]['branch_name'],

                        'bank_name'        => $row[0]['bank_name'],

                        'state'            => $row[0]['state'],

                        'acc_no'           => $row[0]['acc_no'],

                        'ifsc_code'        => $row[0]['ifsc_code'],

                        'village'          => $row[0]['village'],

                        'pan_no'           => $row[0]['pan_no'],

                        'gst_no'           => $row[0]['gst_no'],

                        'company'          => $row[0]['company'],

                        'profile_status'   => $row[0]['profile_status'],

                        'document_status'  => $row[0]['document_status'],

                        'user_type'        => 'client',

                        'profile_image'    => $row[0]['profile_image'],

                        'pan_no_doc'       => $row[0]['pan_no_doc'],

                        'aadhar_no_doc'    => $row[0]['aadhar_no_doc'],

                        'aadhar_no'        => $row[0]['aadhar_no'],

                        'group_id'         => $row[0]['group_id'],

                        'dob'              => $row[0]['dob'],

                        'gender'           => $row[0]['gender'],

                        'logged_in'        => true,

                        'is_login'         => true,

                        'my_refferal_code' => $row[0]['my_refferal_code'],

                        'ACCESS_TOKEN'     => current_date(),

                        'categories'       => $categories,

                        'pcategories'      => $pcategories,

                        'countries'        => $countries,

                        'is_whitelabeled'  => $row[0]['is_whitelabeled'],

                        'is_video_enable'  => $row[0]['is_video_enable'],

                        'is_chat_enable'   => $row[0]['is_chat_enable'],



                    );



                    if ($row[0]['is_whitelabeled'] === 't') {



                        $bank_master_id = $row[0]['bank_master_id'];

                        $row_bank       = $this->db->query("SELECT  gm.logo,gm.mob_icon,bm.*

                            FROM bank_master as bm

                            LEFT JOIN client_group_master as gm ON gm.client_group_id = bm.group_id

                            WHERE bm.is_active = 'true' AND bm.is_deleted = 'false' AND bm.bank_master_id = $bank_master_id

                            LIMIT 1");

                        $whitelabel_data = $row_bank->result_array();



                    } else {

                        $whitelabel_data = array();



                    }



                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, 'farm_type' => $this->farm_type, 'topology' => $this->topology, 'soil_type' => $this->soil_type, 'unit' => $this->unit, "message" => "Login successfully", 'config_url' => $this->config_url, 'menu' => $this->menu, 'whitelabel_data' => $whitelabel_data);



                    $this->response($response);

                    exit;

                }

            } else {

                $response = array("success" => 0, "error" => 1, "status" => 0, "message" => "mobile not found");

                $this->response($response);

                exit;

            }



        }

        $this->response($response);

    }





    public function get_login_chkk()

    {



        $result     = array();

        $update_arr = array();

       // print_r($_POST);



        $response = array("success" => 0, "error" => 1, "status" => 1, "message" => "missing params");



        if ($this->input->post('btn_submit') == 'submit') {



            echo 'hererere';

            $username = $this->input->post('username');

            $password = $this->input->post('password');



            //$row = $this->db->query("SELECT * FROM admins WHERE is_deleted = 'false' and username::varchar = '$phone'::varchar ");



            $select    = array('admins.*');

           // $where     = array('admins.username' => $username, 'admins.is_deleted' => 'false');

            $where     = array('username' =>$username, 'admins.is_deleted' => 'false');

            $user_data = $this->Masters_model->get_data($select, 'admins', $where);



            print_r($user_data);



            $row = $user_data;



            if (count($row)) {

                if (decrypt($row[0]['password'], config_item('encryption_key')) === $password) {

                    //if($row[0]['email_verify'] == 't')



                    /*if ($this->input->post('latitude')) {

                    $update_arr['latitude'] = $this->input->post('latitude');

                    }



                    if ($this->input->post('longitude')) {

                    $update_arr['longitude'] = $this->input->post('longitude');

                    }



                    if ($this->input->post('city_name')) {

                    $update_arr['city_name'] = $this->input->post('city_name');

                    }



                    if ($this->input->post('device_id')) {

                    $update_arr['device_id'] = $this->input->post('device_id');

                    }



                    if ($this->input->post('loc_addresss')) {

                    $update_arr['loc_addresss'] = $this->input->post('loc_addresss');

                    }*/



                    if ($this->input->post('device_id')) {

                        $update_arr['device_id'] = $this->input->post('device_id');

                    }



                    if ($this->input->post('login_count')) {

                        $update_arr['login_count'] = $row[0]['login_count'] + 1;

                    }

                    // loc_addresss



                    if (count($update_arr)) {



                        $id = $row[0]['id'];

                        $this->db->where('admins.id', $id);

                        $result = $this->db->update('admins', $update_arr);



                    }



                    $data = array(

                        'id'           => $row[0]['id'],

                        'first_name'   => $row[0]['first_name'],

                        'last_name'    => $row[0]['last_name'],

                        'email'        => $row[0]['email'],

                        'village_name' => $row[0]['village_name'],

                        'device_id'    => $row[0]['device_id'],

                        'country'      => $row[0]['country'],

                        'state'        => $row[0]['state'],

                        'city_name'    => $row[0]['city_name'],

                        'market_id'    => $row[0]['market_id'],

                        'phone'        => $row[0]['phone'],

                    );



                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, "message" => "Login successfully", 'config_url' => $this->config_url, 'menu' => $this->menu);



                }

            }



        }

        $this->response($response);

    }





    public function get_login()

    {



        $result     = array();

        $update_arr = array();



        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $_POST, "message" => "missing params");



        if ($this->input->post('btn_submit') == 'submit') {

            $username = $this->input->post('username');

            $password = $this->input->post('password');



           // $row = $this->db->query("SELECT * FROM admins WHERE is_deleted = 'false' and username::varchar = '$phone'::varchar ");



            $select    = array('admins.*');

            $where     = array('admins.username' => $username, 'admins.is_deleted' => 'false');

            $user_data = $this->Masters_model->get_data($select, 'admins', $where);



           // print_r($user_data);



            $row = $user_data;



            if (count($row)) {

               // echo decrypt($row[0]['password'], config_item('encryption_key'));



                if (1) {

                    //if($row[0]['email_verify'] == 't')



                    // if ($this->input->post('latitude')) {

                    // $update_arr['latitude'] = $this->input->post('latitude');

                    // }



                    // if ($this->input->post('longitude')) {

                    // $update_arr['longitude'] = $this->input->post('longitude');

                    // }



                    // if ($this->input->post('city_name')) {

                    // $update_arr['city_name'] = $this->input->post('city_name');

                    // }



                    if ($this->input->post('device_id')) {

                    $update_arr['device_id'] = $this->input->post('device_id');

                    }



                    // if ($this->input->post('loc_addresss')) {

                    // $update_arr['loc_addresss'] = $this->input->post('loc_addresss');

                    // }



                    if ($this->input->post('device_id')) {

                        $update_arr['device_id'] = $this->input->post('device_id');

                    }



                     if ($this->input->post('login_count')) {

                         $update_arr['login_count'] = $row[0]['login_count'] + 1;

                     }

                    // loc_addresss



                    if (count($update_arr)) {



                        $id = $row[0]['id'];

                        $this->db->where('admins.id', $id);

                        $result = $this->db->update('admins', $update_arr);



                    }



                    // 'country'      => $row[0]['country'],

                    // 'state'        => $row[0]['state'],

                    // 'city_name'    => $row[0]['city_name'],

                    // 'village_name' => $row[0]['village_name'],

                    $data = array(

                        'id'           => $row[0]['id'],

                        'first_name'   => $row[0]['first_name'],

                        'last_name'    => $row[0]['last_name'],

                        'email'        => $row[0]['email'],                       

                        'device_id'    => $row[0]['device_id'],                       

                        'market_id'    => $row[0]['market_id'],

                        'phone'        => $row[0]['phone'],

                    );



                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, "message" => "Login successfully", 'config_url' => $this->config_url, 'menu' => $this->menu);



                }

            }



        }

        $this->response($response);

    }



    public function get_login_old()

    {



        $result     = array();

        $update_arr = array();



        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => "", "message" => "missing params");



        if ($this->input->post('btn_submit') == 'submit') {



            //echo 'herer';

            $username = $this->input->post('username');

            $password = $this->input->post('password');



            // $row = $this->db->query("SELECT * FROM admins WHERE is_deleted = 'false' and username::varchar = '$phone'::varchar ");



            $select = array('admins.*');

            $where  = array('admins.username' => $username, 'admins.is_deleted' => 'false');



            // $where     = array('admins.username' => $username, 'admins.is_deleted' => 'false');

            $user_data = $this->Masters_model->get_data($select, 'admins', $where);



            //echo ''->$this->db->last_query();



            $row = $user_data;



            if (count($row)) {

                if (decrypt($row[0]['password'], config_item('encryption_key')) === $password) {

                    //if($row[0]['email_verify'] == 't')



                    /*if ($this->input->post('latitude')) {

                    $update_arr['latitude'] = $this->input->post('latitude');

                    }



                    if ($this->input->post('longitude')) {

                    $update_arr['longitude'] = $this->input->post('longitude');

                    }



                    if ($this->input->post('city_name')) {

                    $update_arr['city_name'] = $this->input->post('city_name');

                    }



                    if ($this->input->post('device_id')) {

                    $update_arr['device_id'] = $this->input->post('device_id');

                    }



                    if ($this->input->post('loc_addresss')) {

                    $update_arr['loc_addresss'] = $this->input->post('loc_addresss');

                    }*/



                    if ($this->input->post('device_id')) {

                        $update_arr['device_id'] = $this->input->post('device_id');

                    }



                   // if ($this->input->post('login_count')) {

                        $update_arr['login_count'] = $row[0]['login_count'] + 1;

                   // }

                    // loc_addresss



                    if (count($update_arr)) {



                        $id = $row[0]['id'];



                        $this->db->where('admins.id', $id);

                        $result = $this->db->update('admins', $update_arr);



                    }



                    $market_location  = $row[0]['market_location'];

                    $location_name    = '';

                    $location_name_mr = '';

                    if ($market_location != '') {



                        $row_loc          = $this->db->query("SELECT name,name_mr,market_id FROM market_master WHERE is_deleted = 'false' AND market_id=".$market_location);

                        $result_loc       = $row_loc->result_array();

                        $location_name    = $result_loc[0]['name'];

                        $location_name_mr = $result_loc[0]['name_mr'];



                    }



                    $data = array(

                        'id'              => $row[0]['id'],

                        'first_name'      => $row[0]['first_name'],

                        'last_name'       => $row[0]['last_name'],

                        'email'           => $row[0]['email'],

                        'village_name'    => $row[0]['village_name'],

                        'device_id'       => $row[0]['device_id'],

                        'country'         => $row[0]['country'],

                        'state'           => $row[0]['state'],

                        'city_name'       => $row[0]['city_name'],

                        'market_id'       => $row[0]['market_location'],

                        'phone'           => $row[0]['phone'],

                        'market_location' => $row[0]['market_location'],

                        'location'        => $location_name,

                        'location_mr'     => $location_name_mr,

                    );



                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, "message" => "Login successfully", 'config_url' => $this->config_url, 'menu' => $this->menu);



                }

            }



        }

        $this->response($response);

    }



    public function get_menu()

    {

        $response = array("success" => 1, "error" => 0, "status" => 1, 'home_message' => $this->home_message, "menu" => $this->menu);

        $this->response($response);

    }



    public function get_profile_data($id)

    {



        $result   = array();

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $_POST, "message" => "missing params");



        if ($id != '') {



            /*  $select = array('admins.*','states.name as state_name');

            $join   = array('states' => array('states.code = admins.state', 'left'));

            $where     = array('admins.id' => $id, 'admins.is_deleted' => 'false');

            $user_data = $this->Masters_model->get_data($select, 'admins', $where, $join);*/



            $sql       = "SELECT a.*,s.name as  state_name FROM admins as a LEFT JOIN states_new as s ON cast(s.id as INTEGER) = cast(a.state as INTEGER) WHERE a.id=" . $id . " LIMIT 1";

            $user_data = $this->db->query($sql)->result_array();



            $this->db->select('*');

            $this->db->where('id', 101);

            $countries = $this->db->get('countries_new')->result_array();



            $row = $user_data;



            if (count($row)) {



                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $user_data, "message" => "Profile data here", 'config_url' => $this->config_url);



            } else {

                $user_data = array();



                $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $user_data, "message" => "Profile Display error / id missing", 'config_url' => $this->config_url);

            }



        }



        $this->response($response);



    }



    public function get_categories()

    {

        $row    = $this->db->query("SELECT cat_id ,name ,logo ,name_mr ,mob_icon FROM categories WHERE is_active = 'true' AND is_deleted = 'false' ORDER BY seq ASC");

        $result = $row->result_array();



        $row_blog     = $this->db->query("SELECT blogs_types_id ,name ,logo ,name_mr ,mob_icon FROM blogs_types_master WHERE is_active = 'true' AND is_deleted = 'false' AND is_home =1  ORDER BY seq ASC");

        $result_blogs = $row_blog->result_array();



        if (count($result)) {

            $response = array("status" => 1, "data" => $result, "blog_type" => $result_blogs, "config_url" => $this->config_url, 'home_message' => $this->home_message, "message" => "categories listed successfully");

        }

        // $this->response($result, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

        $this->response($response);

    }



    public function get_farmers($admin_id, $start = 1 )

    {

        $data_res = array();



        //$start = $this->input->post('start');



        $limit    = 4;

        //$start    = 1;



        if ($start != '') {

            $start_chk = $start - 1;

            if ($start_chk != 0) {

                $start_sql = $limit * ($start_chk);

            } else {

                $start_sql = 0;

            }



            $sql_limit = " LIMIT " . $limit . " OFFSET " . $start_sql;



        } else {



            $sql_limit = "";



        }



        if ($admin_id != '') {



            $this->db->select('market_location');

            $this->db->where('id', $admin_id);

            $admin_location = $this->db->get('admins')->result_array();

            // echo 'Location ID :'

            $location_id = $admin_location[0]['market_location'];



            if ($location_id != '') {



                /*$sql = "SELECT c.id,c.first_name,c.last_name,c.phone,c.city,cp.farmer_id,c.profile_image FROM client as c LEFT JOIN crop_product as cp ON c.id = cp.farmer_id WHERE cp.is_deleted = false AND c.is_deleted = false AND cp.market_id = " . $location_id . "  GROUP BY c.id,cp.farmer_id ".$sql_limit;

*/

                //LEFT JOIN crop_product as cp ON c.id = cp.farmer_id 

                $sql = "SELECT c.id,c.first_name,c.last_name,c.phone,c.city,c.profile_image FROM client as c WHERE c.is_deleted = false  ORDER BY c.id DESC ".$sql_limit;



                $data_res = $this->db->query($sql)->result_array();



                // echo '<pre><br> ============== :<br>';

                // print_r($data_res);

                $this->db->select('*');

                $this->db->where('is_deleted', 'false');

                $countries = $this->db->get('client')->result_array();

                $response  = array("success" => 1, "error" => 0, "status" => 1, "data" => $data_res, "message" => "Farmer data","farmer_profile_image"=>base_url('uploads/profile/'));

            } else {

                $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $data_res, "message" => "Location is not assign","farmer_profile_image"=>base_url('uploads/profile/'));

            }



        } else {

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $data_res, "message" => "Admin id Missing","farmer_profile_image"=>base_url('uploads/profile/'));

        }



        $this->response($response);



    }



    public function get_farmer_details($farmer_id)

    {



        $this->db->select('*');

        $this->db->where('id', $farmer_id);

        $client = $this->db->get('client')->result_array();



      /*  $this->db->select('*');

        $this->db->where('farmer_id', $farmer_id);

        $crop_product = $this->db->get('crop_product')->result_array();*/



        $sql_data = "SELECT p.*,f.first_name,f.last_name,f.phone,c.crop_id,c.name,c.name_mr,c.logo as mob_icon,c.mob_icon as mob_icon ,ct.name_en as crop_variety_name,ct.name_mr as crop_variety_name_mr,m.name as market_name,m.name_mr as market_name_mr 

        FROM crop_product as p

        LEFT JOIN crop as c ON c.crop_id = p.crop_id

        LEFT JOIN client as f ON f.id = p.farmer_id        

        LEFT JOIN market_master as m ON m.market_id = p.market_id

        LEFT JOIN crop_variety_master ct ON ct.crop_variety_id = p.crop_variety_id

        WHERE p.is_deleted = false  AND c.is_deleted = false AND p.farmer_id = $farmer_id  ORDER BY id DESC";



        $row = $this->db->query($sql_data);

        $crop_product = $row->result_array();



       // $crop_product = $this->db->query($sql_data)->result_array();



        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $client, "crop_product" => $crop_product, "message" => "Farmer Detail data");



        $this->response($response);



    }



    public function get_countries_new()

    {



        $this->db->select('*');

        $this->db->where('id', 101);

        $countries = $this->db->get('countries_new')->result_array();

        $response  = array("success" => 1, "error" => 0, "status" => 1, "data" => $countries, "message" => "countries data");



        $this->response($response);



    }



    public function get_states_new()

    {



        $country_id = $this->input->post('country_id') ? $this->input->post('country_id') : 101;



        if ($country_id) {

            //os version

            $type   = $this->input->post('type');

            $where  = array('country_id' => $country_id);

            $result = $this->Masters_model->get_data(array('id', 'name', 'country_id'), 'states_new', $where);

            $str    = '<option value="">Select state</option>';



            //echo $result['code'];

            if (count($result) > 0) {



                foreach ($result as $res) {

                    $t = '';

                    if (!empty($country_id) && !empty($country_id)) {



                        $test = ($res['id'] == $country_id && $res['name'] == $country_id) ? ' selected ' : '';

                        //$t = "<option value='".$res['code']."' data-country='".$country_id."' data-state='".$country_id."' ".$test." >".$res['name'].'</option>';

                        $str .= '<option value="' . $res['id'] . '" ' . $test . ' >' . $res['name'] . '</option>';

                    } else {

                        //$str .= "<option value='".$res['code']."' ".$t.">".$res['name']."</option>";

                        $str .= '<option value="' . $res['id'] . '"  ' . $t . '  > ' . $res['name'] . ' </option>';

                    }



                }

            }

            if ($type == 1) {



                $response = array("status" => 1, "error" => 0, "success" => 1, "data" => $result, "message" => "State list");

                $this->response($response);

                exit;



            } else {

                echo $str;

                exit;

            }

        } else {



            $response = array("status" => 0, "error" => 1, "success" => 0, "data" => null, "message" => "Parmans missing country_id");

            $this->response($response);

            exit;



        }

    }



    public function get_city_new()

    {

        // $state      = $state;

        // $state_id = $this->input->post('state_id');

        $type = $this->input->post('type');

        //$city       = $this->input->post('city');

        //os version

        $state_id = $this->input->post('state_id') ? $this->input->post('state_id') : 22;



        if ($state_id) {

            $where  = array('state_id' => $state_id);

            $result = $this->Masters_model->get_data(array('id', 'name', 'state_id'), 'cities_new', $where);

            $str    = "";

            if (count($result) > 0) {

                $test = '';

                foreach ($result as $res) {



                    if (!empty($city) && !empty($city)) {

                        $test = ($res['name'] == $city) ? ' selected ' : '';

                    }

                    //$str .= "<option value='".$res['name']."' ".$test.">".$res['name']."</option>";

                    $str .= '<option value="' . $res['name'] . '" ' . $t . ' >' . $res['name'] . '</option>';

                }

            }



            if ($type == 1) {



                $response = array("status" => 1, "error" => 0, "success" => 1, "data" => $result, "message" => "City list");

                $this->response($response);

                exit;



            } else {

                echo $str;

                exit;

            }

        } else {



            $response = array("status" => 0, "error" => 1, "success" => 0, "data" => null, "message" => "Parmans missing state_id");

            $this->response($response);

            exit;



        }

    }



    public function add_basic_details()

    {



        $result   = array();

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Registration failed, please try again some time.");



        if ($this->input->post('btn_submit') == 'submit') {

            $this->form_validation->set_rules('profile_image', 'profile image', 'required');

            $this->form_validation->set_rules('first_name', 'first name', 'required');

            $this->form_validation->set_rules('last_name', 'last name', 'required');

            $this->form_validation->set_rules('cphone', 'cphone', 'required');

            $this->form_validation->set_rules('email', 'email', 'required');

            $this->form_validation->set_rules('aadhar_no', 'aadhar no', 'required');

            $this->form_validation->set_rules('pan_no', ' pan no', 'required');

            $this->form_validation->set_rules('country', 'country', 'required');

            $this->form_validation->set_rules('state', 'state', 'required');

            $this->form_validation->set_rules('city', 'city', 'required');

            $this->form_validation->set_rules('postcode', 'postcode', 'required');

            $this->form_validation->set_rules('address', 'address', 'required');

            $this->form_validation->set_rules('bank_name', 'bank name', 'required');

            $this->form_validation->set_rules('branch_name', 'branch name', 'required');

            $this->form_validation->set_rules('acc_no', 'acc no', 'required');

            $this->form_validation->set_rules('ifsc_code', 'ifsc code', 'required');

            $this->form_validation->set_rules('gender', 'gender', 'required');

            $this->form_validation->set_rules('dob', 'date of birth', 'required');

            //  if ($this->form_validation->run() == FALSE) {



            if (0) {

                $data          = $this->input->post();

                $data['error'] = validation_errors();

            } else {



                $insert = array(

                    'profile_image' => $this->input->post('profile_image'),

                    'first_name'    => $this->input->post('first_name'),

                    'last_name'     => $this->input->post('last_name'),

                    'phone'         => $this->input->post('cphone'),

                    'email'         => $this->input->post('email'),

                    'aadhar_no'     => $this->input->post('aadhar_no'),

                    'pan_no'        => $this->input->post('pan_no'),

                    'country'       => $this->input->post('country'),

                    'state'         => $this->input->post('state'),

                    'city'          => $this->input->post('city'),

                    'village'       => $this->input->post('village'),

                    'postcode'      => $this->input->post('postcode'),

                    'address1'      => $this->input->post('address'),

                    'bank_name'     => $this->input->post('bank_name'),

                    'branch_name'   => $this->input->post('branch_name'),

                    'acc_no'        => $this->input->post('acc_no'),

                    'ifsc_code'     => $this->input->post('ifsc_code'),

                    'gender'        => $this->input->post('gender'),

                    'dob'           => $this->input->post('dob'),

                    'created_on'    => current_date(),



                );



                $result    = $this->db->insert('client', $insert);

                $insert_id = $this->db->insert_id();



                $this->session->set_flashdata('success', 'Basic detail Added Successfully');



                // code for email

                if ($result) {



                    if (count($insert)) {

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Basic  detail Added Successfully");

                    }



                    $this->response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Basic  detail Add failed, please try again some time.", 'config_url' => $this->config_url);



                    $this->response($response);

                    exit;



                }

            }

        }



        $this->response($response);

        exit;

    }



    public function update_profile()

    {



        $result        = array();

        $id            = $this->input->post('id');

        $image         = '';

        $profile_image = '';

        $response      = array("success" => 0, "error" => 1, "status" => 1, "data" => array(), "message" => "Profile PARAMS MISSING.", "post_param" => $_POST);



        if (!empty($_FILES['profile_image']['name'])) {

            $extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);

            //echo $extension;

            $profile_image_name = 'profile_image_' . time() . '.' . $extension;

            $target_file        = 'uploads/profile/' . $profile_image_name;

            // for delete previous image.

            if ($this->input->post('old_profile_image') != "") {

                @unlink('./uploads/profile/' . $this->input->post('old_profile_image'));

            }



            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {

                $profile_image = $profile_image_name;

                $error         = 0;



            } else {

                $error = 2;

            }

        }



        $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => "Profile update failed, please try again some time.", "post_param" => $_POST);



        if ($id != '') {



            if (0) {



            } else {



                $address1 = $this->input->post('address1') != '' ? $this->input->post('address1') : null;

                /* $postcode    = $this->input->post('postcode') != '' ? $this->input->post('postcode') : null;

                $village     = $this->input->post('village') != '' ? $this->input->post('village') : null;

                $dob         = $this->input->post('dob') != '' ? $this->input->post('dob') : null;*/



                $update_arr['first_name'] = $this->input->post('first_name');

                $update_arr['last_name']  = $this->input->post('last_name');

                $update_arr['phone']       = $this->input->post('phone');

                $update_arr['email'] = $this->input->post('email');

                /*  $update_arr['country']    = $this->input->post('country');

                $update_arr['state']   = $this->input->post('state');

                $update_arr['city_name']  = $this->input->post('city_name');

                $update_arr['village'] = $this->input->post('village');  */           

                $update_arr['address1'] = $address1;               

                $update_arr['updated_on'] = current_date();



                if ($profile_image != '') {

                    $update_arr['profile_image'] = $profile_image;

                }



                $this->db->where('admins.id', $id);

                $result = $this->db->update('admins', $update_arr);

                if ($result) {



                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "profile updated Successfully", 'config_url' => $this->config_url ,"post_param" => $_POST);



                    $this->response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 2, "data" => $result, "message" => "Err!! profile updated failed, please try again some time.", "post_param" => $_POST);



                    $this->response($response);

                    exit;

                }

            }

        }



        $this->response($response);

        exit;

    }







    public function get_all_products_with_pagination()

    {

        //echo $type_get = $type_get;

        $response = array();

        $limit    = 6;

        $start    = 1;

        $cat_id   = 0;



        $start  = $this->input->post('start');

        $cat_id = number_format($this->input->post('cat_id'));



        if ($cat_id != 0) {

            $sql_where = " AND is_deleted = 'false' AND is_publish='true' AND '" . $cat_id . "' = ANY (string_to_array(category_id,','))";

        } else {

            $sql_where = '';

        }



        $sql_sort = " ";



        $sort_filter = $this->input->post('sort_filter');



        if ($sort_filter != 0) {



            if ($sort_filter == 1) {

                $sql_sort = " ORDER BY id DESC ";

            }



            if ($sort_filter == 2) {

                $sql_sort = " ORDER BY price ASC ";

            }



            if ($sort_filter == 3) {

                $sql_sort = " ORDER BY price DESC ";

            }



        } else {

            $sql_sort = '';

        }



        $sql_count    = "SELECT COUNT(id) FROM products WHERE is_deleted = 'false' AND is_publish='true' ";

        $sql_string   = $sql_count . $sql_where;

        $row_count    = $this->db->query($sql_count . $sql_where);

        $result_count = $row_count->row_array();

        $count_res    = $result_count['count'];



        $sql = "SELECT id, partner_id, category_id, product_name, version, logo, type, product_type, price FROM products WHERE is_deleted = 'false' ";



        // $this->db->limit($limit, $start);

        $sql_limit = " LIMIT " . $limit . " OFFSET " . $start;

        $row       = $this->db->query($sql . $sql_where . $sql_sort . $sql_limit);

        //$result    = $row->result_array();

        $result = $row->result_array(); // MMM comment for live only

        // $result  = array();



        $query_str = $this->db->last_query();



        if (count($result)) {

            $response = array("status" => 1, "data" => $result, 'total_records' => $count_res, "message" => "Product listed successfully", 'query_str' => $query_str, 'sql_srr' => $sql_string);

        } else {

            $response = array("status" => 0, 'query_str' => $query_str, "message" => "Product listing successfully");

        }

        $this->response($response);

    }



    public function sendPushNotificationToFCMSeverdev($token, $title, $message, $arr_user, $type, $meeting_link, $partner_name, $farmer_id = '', $route = '')

    {

        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';



        $fields = array(

            'registration_ids' => $token,

            'priority'         => 10,

            'data'             => array("title" => $title, "body" => $message, "sound" => 'Default', 'image' => 'Notification Image', 'admno' => $arr_user, 'type' => $type, 'meeting_link' => $meeting_link, 'partner_name' => $partner_name, 'route' => $route, "click_action" => "FLUTTER_NOTIFICATION_CLICK", 'farmer_id' => $farmer_id),

            /*'notification'             => array("title" => $title, "body" => $message, "sound" => 'Default', 'image' => 'Notification Image', 'admno' => $arr_user, 'type' => $type, 'meeting_link' => $meeting_link, 'partner_name' => $partner_name,'route'=> $route,"click_action"=> "FLUTTER_NOTIFICATION_CLICK",'farmer_id'=>$farmer_id),*/

            "time_to_live"     => 30,

            "ttl"              => 30,

        );



        /* // this api key for famrut farmer

        $API_SERVER_KEY = 'AAAAmhCfp8k:APA91bHXSHb1Ihie4m3y0v_0e1tAf7JCPMnubM751PMMVkC9oAi54O1AUGipDZ6ZZGCD9ajyxAFLsxjQ0cuLOqSLGjKp9Z0vBttKkIFeX7_xrZnbrAamAex-HMYK3z4SEz2_mHdwWYdu';*/

        // api key for Vendor or partner app

        $API_SERVER_KEY = 'AAAAZP52chY:APA91bHn09jHHewFEixuQ87yO4QuYql8_bWBtRYtjx27mMIz-VWhMw6FRtbOoAHfm_xgBoZGqC0NJJiNlfObiNsqE-MNjRvNLaFtfysM6_JTzfZMFyRnjDOuzw5oCj-Ly6_Xw1GUXBX4';



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



    public function custom_config()

    {



        $config_master_data = array();

        $data_array         = array();

        $sql                = "SELECT id,name,key_fields,seq,logo,mob_icon,is_active,description FROM config_master where is_deleted=false ORDER BY id ASC";

        $res_val            = $this->db->query($sql);

        $res_array          = $res_val->result_array();



        if (count($res_array) > 0) {

            $config_master_data = $res_array;

        }



        //$base_path = $this->base_path;

        

        $logo_url  =  $this->base_path. 'uploads/config_master/';



        $response = array("success" => 1, "config_master_data" => $config_master_data, "error" => 0, "status" => 1, "data" => $data_array, 'phone_number' => '+91 9607005004', "logo_url" => $logo_url);

        $this->response($response);



    }



    public function chk_profile($id)

    {

        $sql = "SELECT * FROM admins WHERE is_deleted = 'false' AND id = $id AND first_name != ''  AND last_name !='' "; // AND city_name !='' AND phone != ''

        $row_chk = $this->db->query($sql);

        $res_array     = $row_chk->result_array();

      

         if (count($res_array)) {

            $response = array("success" => 1, "error" => 0,"data" => $res_array, "message" => "User Profile Completed ",'sql'=>$sql);            

        } else {

            $response = array("success" => 0, "error" => 1, "data" => $res_array, "message" => "Enter First And Last Name,City Name,Phone.",'sql'=>$sql);           

        }

       

        $this->response($response);

        exit;



    }



    public function get_crop_list()

    {

        $sql_chk = "SELECT crop_id,name,name_mr,name_hindi FROM crop

        WHERE is_deleted=false AND is_active=true";

        $res_val   = $this->db->query($sql_chk);

        $res_array = $res_val->result_array();

        $response  = array("success" => 1, "data" => $res_array, "error" => 0, "status" => 1);

        $this->response($response);

    }



    public function get_crop_variety($crop_id)

    {

        $sql_chk = "SELECT crop_variety_id,name,name_mr,name_hindi,crop_id FROM crop_variety_master

        WHERE is_deleted=false AND is_active=true AND crop_id=" . $crop_id;

        $res_val   = $this->db->query($sql_chk);

        $res_array = $res_val->result_array();

        $response  = array("success" => 1, "data" => $res_array, "error" => 0, "status" => 1);

        $this->response($response);

    }



    public function farmer_list($vendor_id)

    {



        if ($vendor_id != '') {

            $sql_farmer = "SELECT id,first_name,middle_name,last_name,email,phone,profile_image from client where is_active=true AND is_deleted = false AND phone !='' ORDER BY  referral_code ASC";

            $query      = $this->db->query($sql_farmer);

            $result     = $query->result_array();

            if (count($result)) {



                $response = array("success" => 1, "data" => $result, "msg" => 'farmer_list', "error" => 0, "status" => 1);



            } else {

                $response = array("success" => 1, "data" => array(), "msg" => 'No farmer avaialbe', "error" => 0, "status" => 1);

            }

        } else {



            $response = array("success" => 0, "data" => array(), "msg" => 'params missing', "error" => 1, "status" => 1);



        }

        $this->response($response);

    }



    public function about_us()

    {



        $result['phone1']   = '+91 9923534591';

        $result['phone2']   = '+91 9923534591';

        $result['email']    = 'office@gfreshagrotech.com';

        $result['address']  = '2039 A Pandit Mohalla, Garhi Village, Alipur North West Delhi India';

        $result['about_us'] = 'GFresh Agrotech is the biggest marketplace for onions all over India. Their products are in the form of four types of onions viz. Garwa Onion – Grade A, Red Onion – Grade A, Red Onion Golta – Grade A, and Unala Kanda – Grade A. Their onions are directly sourced from onions without any middleman and they assure quality and appropriate weight in their products. ';



        $result['about_us_mr'] = 'GFresh Agrotech ही संपूर्ण भारतातील कांद्याची सर्वात मोठी बाजारपेठ आहे. त्यांची उत्पादने चार प्रकारच्या कांद्याच्या स्वरूपात आहेत उदा. गारवा कांदा – ग्रेड ए, लाल कांदा – ग्रेड ए, लाल कांदा गोलटा – ग्रेड ए आणि उनाला कांडा – ग्रेड ए. त्यांचे कांदे कोणत्याही मध्यस्थीशिवाय थेट कांद्यापासून मिळवले जातात आणि ते त्यांच्या उत्पादनांमध्ये गुणवत्ता आणि योग्य वजनाची खात्री देतात.';



        $response = array("success" => 1, "data" => $result, "msg" => 'About us', "error" => 0, "status" => 1);



        $this->response($response);

    }



    public function logout_check($phone_number)

    {



        if ($phone_number != '') {



            $phone = substr(preg_replace('/\s+/', '', $phone_number), -10, 10);

            // $this->db->select('phone_no,user_id');

            // $this->db->where('phone', $phone);

            echo $sql = "SELECT phone,id FROM client where phone :: varchar = $phone_number::varchar AND is_active= true AND is_deleted = false ";

            $res_chk  = $this->db->query($sql);

            $res      = $res_chk->result_array();



            if (count($res) > 0) {



                $id = $res[0]['id'];



                ///// code to disconnnect call of vendor if any active call

                $where_array = array(

                    'farmer_id'            => $id,

                    'meeting_status_id !=' => 4,

                );



                $update_array = array(

                    'meeting_status_id' => 4,

                    'meeting_end_from'  => 1,

                    'updated_on'        => current_date(),

                );



                $sql_update = $this->db->update('emeeting', $update_array, $where_array);

                //// disconnect call code end ///////////////////////////



                $update_arr = array('is_login' => false, 'device_id' => null);

                $this->db->where('client.phone', $phone);

                $result   = $this->db->update('client', $update_arr);

                $sql_data = $this->db->last_query();



                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Farmer Logout Successfully", 'sql_data' => $sql_data);

            } else {

                $response = array("success" => 0, "error" => 1, "status" => 0, "data" => "", "message" => "Farmer Not found");

            }



        } else {



            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => "", "message" => "Parmas missing");

        }



        $this->response($response);

        exit;



    }



    public function splash_screen()

    {



        $sql = "SELECT id,logo,mob_icon,key_fields FROM config_master WHERE key_fields ='farmer_splash1' AND  is_deleted = false  AND is_active = true ORDER BY id ASC";



        $res_chk   = $this->db->query($sql);

        $res       = $res_chk->result_array();

       // $base_path = $this->base_path;

        $logo_url  =  $this->base_path . 'uploads/config_master/';



        if (count($res) > 0) {

            $response = array("success" => 1, "error" => 0, "status" => 1, 'logo_url' => $logo_url, "data" => $res, "message" => "splash screen data");



        } else {

            $response = array("success" => 0, "error" => 1, "status" => 1, 'logo_url' => $logo_url, "data" => $res, "message" => "splash screen data");

        }

        $this->response($response);

        exit;

    }



    public function slider_screen()

    {



        $sql = "SELECT id,logo,mob_icon,key_fields FROM config_master WHERE key_fields LIKE 'farmer_slide%' AND  is_deleted = false  AND is_active = true ORDER BY id ASC";



        $res_chk   = $this->db->query($sql);

        $res       = $res_chk->result_array();

        $base_path = $this->base_path;

        $logo_url  = $this->base_path . 'uploads/config_master/';



        if (count($res) > 0) {

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $res, 'logo_url' => $logo_url, "message" => "Slider screen data");



        } else {

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $res, 'logo_url' => $logo_url, "message" => "Slider screen data");

        }

        $this->response($response);

        exit;

    }



    public function add_crop_product()

    {



        $result    = array();

        $image     = '';

        $crop_img1 = '';

        $crop_img2 = '';



        $crop_product_img =  $this->base_path . 'uploads/farm/' . $crop_prod_image2;



        if (!empty($_FILES['crop_img1']['name'])) {



            $extension = pathinfo($_FILES['crop_img1']['name'], PATHINFO_EXTENSION);



            $crop_prod_image = 'crop_prod_image_' . time() . '.' . $extension;

            $target_file     = 'uploads/farm/' . $crop_prod_image;

            // for delete previous image.

            if ($this->input->post('old_crop_img1') != "") {

                @unlink('./uploads/farm/' . $this->input->post('old_crop_img1'));

            }



            if (move_uploaded_file($_FILES["crop_img1"]["tmp_name"], $target_file)) {

                $crop_img1 = $crop_prod_image;

                $error     = 0;



            } else {



                $error = 2;



            }

        }



        if (!empty($_FILES['crop_img2']['name'])) {



            $extension = pathinfo($_FILES['crop_img2']['name'], PATHINFO_EXTENSION);



            $crop_prod_image2 = 'crop_prod_image_' . time() . '.' . $extension;

            $target_file      = 'uploads/farm/' . $crop_prod_image2;

            // for delete previous image.

            if ($this->input->post('old_crop_img2') != "") {

                @unlink('./uploads/farm/' . $this->input->post('old_crop_img2'));

            }



            if (move_uploaded_file($_FILES["crop_img2"]["tmp_name"], $target_file)) {

                $crop_img2 = $crop_prod_image2;

                $error     = 0;



            } else {



                $error = 2;



            }

        }



        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Product add failed, please try again some time.");



        if ($this->input->post('btn_submit') == 'submit') {



            if (0) {

                $data          = $this->input->post();

                $data['error'] = validation_errors();

            } else {



                $insert = array(

                    'crop_id'          => $this->input->post('crop_id'),

                    'crop_variety_id'  => $this->input->post('crop_variety_id'),

                    'farmer_id'        => $this->input->post('farmer_id'),

                    'prod_desc'        => $this->input->post('prod_desc'),

                    /* 'price'           => $this->input->post('price'),

                    'price_unit'           => $this->input->post('price_unit'),

                    'weight'                => $this->input->post('weight'),

                    'weight_unit'   => $this->input->post('weight_unit'),

                    'product_status' => $this->input->post('product_status'),*/

                    /*'payed_amount'           => $this->input->post('payed_amount'),

                    'total_amount'        => $this->input->post('total_amount'), */

                    'product_status'   => 1,

                    'product_add_date' => current_date(),

                    'created_on'       => current_date(),

                );



                if ($crop_img1 != '') {

                    $insert['crop_img1'] = $crop_img1;

                }



                if ($crop_img2 != '') {

                    $insert['crop_img2'] = $crop_img2;

                }



                $result    = $this->db->insert('crop_product', $insert);

                $insert_id = $this->db->insert_id();



                $title       = "Crop Product: Added";

                $description = json_encode($insert);

                user_activity_logs($title, $description);



                if ($insert_id) {



                    if (count($insert_id)) {

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Product added Successfully", 'config_url' => $this->config_url, "crop_product_img" => $crop_product_img, "post_Data" => $insert);

                    }



                    $this->response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Product add failed, please try again some time.");



                    $this->response($response);

                    exit;



                }

            }

        }



        $this->response($response);

        exit;

    }



    public function update_crop_product_weight_img()

    {

        //weight



        //exit;



        $result             = array();

        $image              = '';

        $crop_weight_image1 = '';

        $crop_weight_image2 = '';

        $data_post          = $this->input->post();



        $id = $this->input->post('id');



        if ($this->input->post('id') != '') 

        {



            if (!empty($_FILES['crop_weight_image1']['name'])) {



                $extension = pathinfo($_FILES['crop_weight_image1']['name'], PATHINFO_EXTENSION);



                $crop_prod_image = 'crop_prod_image1_' . time() . '.' . $extension;

                $target_file     = 'uploads/farm/' . $crop_prod_image;

                // for delete previous image.

                if ($this->input->post('old_crop_weight_image1') != "") {

                    @unlink('./uploads/farm/' . $this->input->post('old_crop_weight_image1'));

                }

                if (move_uploaded_file($_FILES["crop_weight_image1"]["tmp_name"], $target_file)) {

                    $crop_weight_image1 = $crop_prod_image;

                    $error              = 0;



                } else {



                    $error = 2;



                }

            }



            if (!empty($_FILES['crop_weight_image2']['name'])) {



                $extension = pathinfo($_FILES['crop_weight_image2']['name'], PATHINFO_EXTENSION);



                $crop_prod_image2 = 'crop_prod_image2_' . time() . '.' . $extension;

                $target_file      = 'uploads/farm/' . $crop_prod_image2;

                // for delete previous image.

                if ($this->input->post('old_crop_weight_image2') != "") {

                    @unlink('./uploads/farm/' . $this->input->post('old_crop_weight_image2'));

                }



                if (move_uploaded_file($_FILES["crop_weight_image2"]["tmp_name"], $target_file)) {

                    $crop_weight_image2 = $crop_prod_image2;

                    $error              = 0;

                } else {

                    $error = 2;

                }

            }



            if (!empty($_FILES['crop_weight_image3']['name'])) {



                $extension = pathinfo($_FILES['crop_weight_image3']['name'], PATHINFO_EXTENSION);



                $crop_prod_image3 = 'crop_prod_image3_' . time() . '.' . $extension;

                $target_file      = 'uploads/farm/' . $crop_prod_image3;

                // for delete previous image.

                if ($this->input->post('old_crop_weight_image3') != "") {

                    @unlink('./uploads/farm/' . $this->input->post('old_crop_weight_image3'));

                }



                if (move_uploaded_file($_FILES["crop_weight_image3"]["tmp_name"], $target_file)) {

                    $crop_weight_image3 = $crop_prod_image3;

                    $error              = 0;

                } else {

                    $error = 2;

                }

            }



            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Product Updation failed here, please try again some time.");



            if ($this->input->post('btn_submit') == 'submit') {



                if (0) {

                    $data_post     = $this->input->post();

                    $data['error'] = validation_errors();

                } else {



                    /*   $price          = $_REQUEST['price'];

                    $weight_unit    = $_REQUEST['weight_unit']; //$this->input->post('weight_unit');

                    $weight         = $_REQUEST['weight']; //$this->input->post('weight');

                    $product_status = $_REQUEST['product_status']; //$this->input->post('product_status');

                    $total_amount   = $_REQUEST['total_amount'];



                    'price'              => $this->input->post('price'),

                    'weight'             => $this->input->post('weight'),

                    'weight_unit'        => $this->input->post('weight_unit'),

                    'total_amount'       => $this->input->post('total_amount'),

                    'weight_added_by_id' => $this->input->post('weight_added_by_id'),



                     */



                    $update_arr = array(

                        'weight_added_date' => current_date(),



                    );



                    if ($crop_weight_image1 != '') {

                        $update_arr['crop_weight_image1'] = $crop_weight_image1;

                    }



                    if ($crop_weight_image2 != '') {

                        $update_arr['crop_weight_image2'] = $crop_weight_image2;

                    }



                    if ($crop_weight_image3 != '') {

                        $update_arr['crop_weight_image3'] = $crop_weight_image3;

                    }



                    /*  if ( $this->input->post('price') != '' ) {

                    $update_arr['price'] = $this->input->post('price');

                    $update_arr['product_status'] = 1; // conver status 0 to 1 if price added

                    }

                     */

                    if ($this->input->post('weight_unit') != '') {

                        $update_arr['weight_unit'] = $this->input->post('weight_unit');

                    }



                    if ($this->input->post('price_unit') != '') {

                        $update_arr['price_unit'] = $this->input->post('price_unit');

                    }



                    /* if ( $this->input->post('weight') != '' ) {

                    $update_arr['weight'] = $this->input->post('weight');

                    }*/



                    // if ( trim($this->input->post('weight')) != '' &&  trim($this->input->post('price')) != '' )

                    // {

                    $update_arr['price']        = $this->input->post('price');

                    $calculation_price          = $this->input->post('weight') * $this->input->post('price');

                    $update_arr['total_amount'] = round($calculation_price);

                    //$update_arr['product_status'] = 1; (pending)

                    // product status set to accepted as discussed

                    $update_arr['product_status'] = 2;

                    $update_arr['vehicle_type']   = $this->input->post('vehicle_type');

                    $update_arr['weight']         = $this->input->post('weight');

                    // Notification send to accept by farmer....



                   



                    if (fmod($this->input->post('weight'), 1) !== 0.00) {

                       $qut = $this->roundUp($this->input->post('weight'), 0.5);

                   } else {

                       $qut = $this->input->post('weight');

                   }



                   $hamali_total   = $qut * get_config_data("hamali") ;

                   $warai_total   = $qut * get_config_data("warai");

                   $tolai_total   = $qut * get_config_data("tolai");

                   $update_arr['hamali'] = $hamali_total;

                   $update_arr['varai'] = $warai_total;

                   $update_arr['tolai'] = $tolai_total;

                   $total_expense =  $hamali_total + $warai_total + $tolai_total;

                   $update_arr['total_expense'] = $total_expense;

                   $update_arr['payed_amount'] =  round(round($calculation_price) - $total_expense);

                    // }

                     //print_r($update_arr);exit();

                    if (count($update_arr)) {

                        // $id = $row[0]['id'];

                        $this->db->where('crop_product.id', $id);

                        $result = $this->db->update('crop_product', $update_arr);

                        //echo $this->db->last_query();

                    }



                    if ($result) {

                        $title       = "Crop Product: Product Weight Updated Successfully";

                        $description = json_encode($update_arr);

                        user_activity_logs($title, $description);



                        if (count($update_arr)) {

                            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $update_arr, "message" => "Your Product Weight Updated Successfully !!", 'post_params' => $data_post);

                            // 'config_url' => $this->config_url,

                        }



                        $this->response($response);

                        exit;



                    } else {



                        $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => "Product update failed, please try again some time.", 'post_params' => $data_post);



                        $this->response($response);

                        exit;



                    }

                }

            }



        } else {



            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => "Product id missing , please try again some time.", 'post_params' => $data_post);

            $this->response($response);

            exit;



        }



        $this->response($response);

        exit;

    }





    public  function roundUp($number, $nearest)

                    {

                        return $number + ($nearest - fmod($number, $nearest));

                    }



    public function update_crop_product_status()

    {



        $result    = array();

        $image     = '';

        $crop_img1 = '';

        $crop_img2 = '';



        $id             = $this->input->post('id');

        $product_status = $this->input->post('product_status');



        if ($this->input->post('id') != '' && $product_status != '') {



            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Product Updation failed here, please try again some time.");



            if ($this->input->post('btn_submit') == 'submit') {



                if (0) {

                    $data          = $this->input->post();

                    $data['error'] = validation_errors();

                } else {



                    $update_arr = array(

                        'product_status' => $this->input->post('product_status'),

                    );



                    if (count($update_arr)) {

                        $this->db->where('crop_product.id', $id);

                        $result = $this->db->update('crop_product', $update_arr);

                        // echo $this->db->last_query();

                    }

                    if ($result) {



                        if (count($update_arr)) {

                            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $update_arr, "message" => "Product Status Updated Successfully", 'config_url' => $this->config_url);

                        }

                        $this->response($response);

                        exit;



                    } else {



                        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Product Status update failed, please try again some time.");



                        $this->response($response);

                        exit;



                    }

                }

            }



        } else {



            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Product id missing , please try again some time.");

            $this->response($response);

            exit;



        }



        $this->response($response);

        exit;

    }



    public function get_crop_variety_price()

    {



        $crop_id         = $this->input->post('crop_id');

        $crop_variety_id = $this->input->post('crop_variety_id');

        if ($crop_variety_id != '' && $crop_id != '') {

            $sql_chk = "SELECT market_date,crop_variety_id,product_price,unit,crop_id FROM crop_price_master

            WHERE is_deleted=false AND is_active=true AND crop_variety_id=" . $crop_variety_id . " AND crop_id=" . $crop_id . " ORDER BY crop_price_id DESC LIMIT 1";

            $res_val   = $this->db->query($sql_chk);

            $res_array = $res_val->result_array();

            $response  = array("success" => 1, "data" => $res_array, "error" => 0, "status" => 1);

        } else {

            $res_array = $_POST;

            $response  = array("success" => 0, "data" => $res_array, "msg" => "params missing", "error" => 1, "status" => 0);

        }

        $this->response($response);

    }



    public function get_farmer_profile($farmer_id)

    {



        $result   = array();

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "missing params");



        if ($farmer_id) {



            $select = array('client.*', 'client_group_master.name as group_name', 'countries_new.name as country_name', 'states_new.name as state_name');

            $join   = array('countries_new' => array('countries_new.id = 101', 'left'),

                'states_new'                    => array('cast(states_new.id as INTEGER)  = cast(client.state as INTEGER)', 'left'),

                'client_group_master'           => array('client_group_master.client_group_id = client.group_id ', 'left'));



            $where     = array('client.id' => $farmer_id, 'client.is_deleted' => 'false');

            $user_data = $this->Masters_model->get_data($select, 'client', $where, $join, '', '', 1);



            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $user_data, "message" => "Farmer Profile data", 'config_url' => $this->config_url);



        }



        $this->response($response);

        exit;



    }



    // crop product listing

    public function get_crop_product_list($market_id = '')

    {



        $result           = array();

        $crop_product_img =  $this->base_path . 'uploads/farm/' . $crop_prod_image2;

        $response         = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "missing params");



        if ($market_id != '') 

        {



            $sql_data = "SELECT p.*,f.first_name,f.last_name,f.phone,c.crop_id,c.name,c.name_mr,c.logo as mob_icon,c.mob_icon as mob_icon ,ct.name_en as crop_variety_name,ct.name_mr as crop_variety_name_mr,m.name as market_name,m.name_mr as market_name_mr 

        FROM crop_product as p

        LEFT JOIN crop as c ON c.crop_id = p.crop_id

        LEFT JOIN client as f ON f.id = p.farmer_id

        LEFT JOIN market_master as m ON m.market_id = p.market_id

        LEFT JOIN crop_variety_master ct ON ct.crop_variety_id = p.crop_variety_id

        WHERE p.is_deleted = false  AND c.is_deleted = false ORDER BY id DESC ";

        } else {



            $sql_data = "SELECT p.*,f.first_name,f.last_name,f.phone,c.crop_id,c.name,c.name_mr,c.logo as mob_icon,c.mob_icon as mob_icon ,ct.name_en as crop_variety_name,ct.name_mr as crop_variety_name_mr,m.name as market_name,m.name_mr as market_name_mr 

        FROM crop_product as p

        LEFT JOIN crop as c ON c.crop_id = p.crop_id

        LEFT JOIN client as f ON f.id = p.farmer_id

        LEFT JOIN market_master as m ON m.market_id = p.market_id

        LEFT JOIN crop_variety_master ct ON ct.crop_variety_id = p.crop_variety_id

        WHERE p.is_deleted = false  AND c.is_deleted = false ORDER BY id DESC";



        }



        $row = $this->db->query($sql_data);

        $res = $row->result_array();



        if (count($res) > 0) {



            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $res, "message" => "Farmer Prodcuts listing", 'config_url' => $this->config_url, 'vehicle_type' => $this->vehicle_type);

        } else {

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => "Farmer Prodcuts listing", 'crop_product_img' => $crop_product_img, 'config_url' => $this->config_url, 'vehicle_type' => $this->vehicle_type);

        }



        $this->response($response);

        exit;

    }



    // crop product listing

    public function get_crop_product_list_pagination($market_id = '')

    {



        $limit  = 6;

        $start  = 1;

        $cat_id = 0;



        $start = $this->input->post('start');

        $list_filter = $this->input->post('filter');



        if($list_filter != ''){





            if($list_filter == 1){

            $sql_where = " AND p.created_on >= NOW() - INTERVAL '24 HOURS' ";

            }else if($list_filter == 2){

           $sql_where = " AND p.created_on >= NOW() - INTERVAL '7 DAY' ";

            }else if($list_filter == 3){

           $sql_where = " AND p.created_on >= NOW() - INTERVAL '30 DAY' ";

            }else{

            $sql_where = ""; 

            } 

        }else{

             $sql_where = " ";

        }



        if ($start != '') {

            $start_chk = $start - 1;

            if ($start_chk != 0) {

                $start_sql = $limit * ($start_chk);

            } else {

                $start_sql = 0;

            }



            $sql_limit = " LIMIT " . $limit . " OFFSET " . $start_sql;



        } else {



            $sql_limit = "";



        }



        $market_id = $this->input->post('market_id');

        if ($market_id != '') {

            $market_chk = ' AND p.market_id = ' . $market_id;

        } else {

            $market_chk = ' ';

        }



        $product_status = $this->input->post('product_status');

        if ($product_status != '') {



            if ($product_status == 1) {

                $product_status_chk = ' AND (p.product_status::INTEGER = 1 OR p.product_status::INTEGER = 0 ) ';

            } else {

                $product_status_chk = ' AND p.product_status::INTEGER = ' . $product_status;

            }

        } else {

            $product_status_chk = ' ';

        }



        $result           = array();

        $crop_product_img = $this->base_path . 'uploads/farm/' . $crop_prod_image2;

        $response         = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "missing params");



        $sql_data = "SELECT p.*,f.first_name,f.last_name,f.phone,c.crop_id,c.name,c.name_mr,c.logo as mob_icon,c.mob_icon as mob_icon ,ct.name_en as crop_variety_name,ct.name_mr as crop_variety_name_mr,m.name as market_name,m.name_mr as market_name_mr 

        FROM crop_product as p

        LEFT JOIN crop as c ON c.crop_id = p.crop_id

        LEFT JOIN client as f ON f.id = p.farmer_id

        LEFT JOIN market_master as m ON m.market_id = p.market_id

        LEFT JOIN crop_variety_master ct ON ct.crop_variety_id = p.crop_variety_id

        WHERE p.is_deleted = false  AND c.is_deleted = false " .$sql_where. "  " . $market_chk . " " . $product_status_chk . "  ORDER BY id DESC " . $sql_limit;



        $row = $this->db->query($sql_data);

        $res = $row->result_array();



        if (count($res) > 0) {

            $result = [];



            foreach ($res as $key => $value) {

                if(!empty($value['product_add_date'])){

                    $value['product_add_date'] = date('Y-m-d h:i:s', strtotime('+5 hour +30 minutes', strtotime($value['product_add_date'])));

                }

                if(!empty($value['created_on'])){

                    $value['created_on'] = date('Y-m-d h:i:s', strtotime('+5 hour +30 minutes', strtotime($value['created_on'])));

                }

                $result[] = $value;

            }



            $response = array("success" => 1, "error" => 0, "status" => 1, "start" => $start, "data" => $result, "message" => "Farmer Prodcuts listing", 'config_url' => $this->config_url, 'vehicle_type' => $this->vehicle_type);

        } else {

            $response = array("success" => 0, "error" => 1, "status" => 0, "start" => $start, "data" => $result, "message" => "Farmer Prodcuts listing", 'crop_product_img' => $crop_product_img, 'config_url' => $this->config_url, 'vehicle_type' => $this->vehicle_type);

        }



        $this->response($response);

        exit;

    }



    public function get_farmer_product($farmer_id)

    {



        $result           = array();

        $crop_product_img = $this->base_path . 'uploads/farm/' . $crop_prod_image2;

        $response         = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "missing params");



        if ($farmer_id) {



            $sql_data = "SELECT p.*,c.crop_id,c.name,c.name_mr,c.logo as mob_icon,c.mob_icon as mob_icon ,ct.name_en as crop_variety_name,ct.name_mr as crop_variety_name_mr,m.name as market_name,m.name_mr as market_name_mr 

        FROM crop_product as p

        LEFT JOIN crop as c ON c.crop_id = p.crop_id

        LEFT JOIN market_master as m ON m.market_id = p.market_id

        LEFT JOIN crop_variety_master ct ON ct.crop_variety_id = p.crop_variety_id

        WHERE p.is_deleted = false  AND p.farmer_id = $farmer_id AND c.is_deleted = false";



            $row = $this->db->query($sql_data);

            $res = $row->result_array();



            if (count($res) > 0) {



                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $res, "message" => "Farmer Prodcuts listing", 'config_url' => $this->config_url);

            } else {

                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Farmer Prodcuts listing", 'crop_product_img' => $crop_product_img, 'config_url' => $this->config_url);

            }

        }

        $this->response($response);

        exit;

    }



    public function get_farmer_product_invoice($id)

    {



        $result    = array();

        $image     = '';

        $crop_img1 = '';

        $crop_img2 = '';



        //$id = $this->input->post('id');

        //$product_status = $this->input->post('product_status');



        // $crop_product_img    = $this->base_path.'uploads/farm/' . $crop_prod_image2;

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "missing params");



        if ($id) {

            /*AND p.farmer_id = $farmer_id*/



            $sql_data = "SELECT p.*,c.crop_id,c.name,c.name_mr,c.logo as mob_icon,c.mob_icon as mob_icon ,ct.name_en as crop_variety_name,ct.name_mr as crop_variety_name_mr,m.name as market_name,m.name_mr as market_name_mr 

        FROM crop_product as p

        LEFT JOIN crop as c ON c.crop_id = p.crop_id

        LEFT JOIN market_master as m ON m.market_id = p.market_id

        LEFT JOIN crop_variety_master ct ON ct.crop_variety_id = p.crop_variety_id

        WHERE p.is_deleted = false  AND p.id = $id AND c.is_deleted = false";



            $row = $this->db->query($sql_data);

            $res = $row->result_array();



            if (count($res) > 0) {



                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $res, "message" => "Farmer Prodcuts invoice", 'config_url' => $this->config_url);

            } else {

                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Farmer Prodcuts invoice", 'crop_product_img' => $crop_product_img, 'config_url' => $this->config_url);

            }

        }

        $this->response($response);

        exit;

    }



    public function product_invoice_list($farmer_id)

    {



        $result           = array();

        $crop_product_img =  $this->base_path . 'uploads/farm/' . $crop_prod_image2;

        $response         = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "missing params");



        if ($farmer_id) {



            $sql_data = "SELECT p.*,c.crop_id,c.name,c.name_mr,c.logo as mob_icon,c.mob_icon as mob_icon ,ct.name_en as crop_variety_name,ct.name_mr as crop_variety_name_mr,m.name as market_name,m.name_mr as market_name_mr 

        FROM crop_product as p

        LEFT JOIN crop as c ON c.crop_id = p.crop_id

        LEFT JOIN market_master as m ON m.market_id = p.market_id

        LEFT JOIN crop_variety_master ct ON ct.crop_variety_id = p.crop_variety_id

        WHERE p.is_deleted = false  AND p.invoice_number != '' AND p.invoice_file != ''  AND p.farmer_id = $farmer_id AND c.is_deleted = false";



            $row = $this->db->query($sql_data);

            $res = $row->result_array();



            if (count($res) > 0) {



                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $res, "message" => "Farmer Invoice Prodcuts listing", 'config_url' => $this->config_url);

            } else {

                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Farmer Invoice Prodcuts listing", 'crop_product_img' => $crop_product_img, 'config_url' => $this->config_url);

            }

        }

        $this->response($response);

        exit;

    }



    public function update_crop_product_qty()

    {



        $result    = array();

        $image     = '';

        $crop_img1 = '';

        $crop_img2 = '';



        $id             = $_REQUEST['id'];

        $price          = $_REQUEST['price'];

        $weight_unit    = $_REQUEST['weight_unit']; //$this->input->post('weight_unit');

        $weight         = $_REQUEST['weight']; //$this->input->post('weight');

        $product_status = $_REQUEST['product_status']; //$this->input->post('product_status');

        $total_amount   = $_REQUEST['total_amount']; // $this->input->post('total_amount');

        // $farmer_id     = $_REQUEST['farmer_id'] ; // $this->input->post('total_amount');

        $farmer_id = 259; // $this->input->post('total_amount');



        if ($id != '' && $product_status != '') {



            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Product Updation failed here, please try again some time.");



            // id

            /* $sql_data = "SELECT p.*,c.crop_id,c.name,c.name_mr,c.logo as mob_icon,c.mob_icon as mob_icon ,ct.name_en as crop_variety_name,ct.name_mr as crop_variety_name_mr

            FROM crop_product as p

            LEFT JOIN crop as c ON c.crop_id = p.crop_id

            LEFT JOIN crop_variety_master ct ON ct.crop_variety_id = p.crop_variety_id

            WHERE p.is_deleted = false  AND p.invoice_number != '' AND p.invoice_file != ''  AND p.farmer_id = $farmer_id AND c.is_deleted = false";*/



            //  $row = $this->db->query($sql_data);

            //  $res = $row->result_array();



            if ($id != '' && $weight != '') {



                if (0) {

                    $data          = $this->input->post();

                    $data['error'] = validation_errors();

                } else {



                    $update_arr = array(

                        'product_status' => $product_status,

                        'weight_unit'    => $weight_unit,

                        'weight'         => $weight,

                        'price'          => $price,

                        'total_amount'   => $total_amount,

                    );



                    if (count($update_arr)) {

                        $this->db->where('crop_product.id', $id);

                        $result    = $this->db->update('crop_product', $update_arr);

                        $query_sql = $this->db->last_query();



                        ///User Log



                        $title       = "Crop Product: Add Product Quantity";

                        $description = json_encode($update_arr);

                        user_activity_logs($title, $description);



                        //// get farmer details and send notificaiton



                        $sql_farmer  = "SELECT first_name, last_name,device_id from client where id=" . $farmer_id . "  LIMIT 1";

                        $row_farmer  = $this->db->query($sql_farmer);

                        $farmer_data = $row_farmer->result_array();

                        $farmer_name = $farmer_data[0]['first_name'] . ' ' . $farmer_data[0]['last_name'];

                        $token[]     = $farmer_data[0]['device_id'];



                        $message      = 'Dear ' . $farmer_name . ' your product have weight :' . $weight . 'Quintal with Total cost  Rs.' . $total_amount . ' for sale. Do you agree ?';

                        $admno        = 1;

                        $type         = 'Product_update';

                        $product_id   = $id;

                        $title        = 'Product Updates';

                        $partner_name = "Gfresh";

                        $farmer_image = '';



                        $jsonString = self::sendPushNotificationToFCMSeverdev_product($token, $title, $message, $admno, $type, $partner_name, $product_id, $farmer_image, 'product');



                    }



                    if (count($update_arr)) {

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $update_arr, "message" => "Product Status Updated Successfully", 'config_url' => $jsonString);

                    } else {

                        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Product Status update failed, please try again some time.");

                    }

                    $this->response($response);

                    exit;

                }

            }



        } else {



            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Product id or other params missing , please try again some time.");

            $this->response($response);

            exit;



        }



        $this->response($response);

        exit;

    }



    public function update_product_pay_status()

    {



        $result    = array();

        $image     = '';

        $crop_img1 = '';

        $crop_img2 = '';



        $id             = $_REQUEST['id'];

        $product_status = $_REQUEST['product_status']; //$this->input->post('product_status');

        $payment_type   = $_REQUEST['payment_type']; // $this->input->post('total_amount');

        // $total_amount   = $_REQUEST['total_amount']; // $this->input->post('total_amount');

        $user_id   = $_REQUEST['user_id'];

        $user_type = $_REQUEST['user_type'];



        if ($id != '' && $product_status != '') {



            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Product Updation failed here, please try again some time.");



            if ($id != '' && $product_status != '') {



                if (0) {

                    $data          = $this->input->post();

                    $data['error'] = validation_errors();

                } else {



                    $update_arr = array(

                        'product_status' => $product_status,

                        'payment_type'   => $payment_type,

                        'user_type'      => $user_type,

                        'user_id'        => $user_id,

                    );



                    if (count($update_arr)) {

                        $this->db->where('crop_product.id', $id);

                        $result    = $this->db->update('crop_product', $update_arr);

                        $query_sql = $this->db->last_query();

                    }



                    $title       = "Crop Product: Make Payment";

                    $description = json_encode($update_arr);

                    user_activity_logs($title, $description);



                    if (count($update_arr)) {

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $update_arr, "message" => "Product Payment Status Updated Successfully", 'config_url' => $query_sql);

                    } else {

                        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $update_arr, "message" => "Product Payment Status update failed, please try again some time.");

                    }

                    $this->response($response);

                    exit;



                }

            }



        } else {



            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $_REQUEST, "message" => "Product id or other params missing , please try again some time.");

            $this->response($response);

            exit;



        }



        $this->response($response);

        exit;

    }



    public function get_farmer_dashboard($farmer_id)

    {



        if ($farmer_id != '') {

            $sql          = "SELECT SUM(c.total_amount::INTEGER) as total_payment  from crop_product c where c.is_deleted=false AND c.farmer_id =" . $farmer_id . "";

            $query        = $this->db->query($sql);

            $total_amount = $query->result_array();



            $sql1              = "SELECT SUM(c.total_amount::INTEGER) as total_pay_online from crop_product c where c.is_deleted=false AND c.payment_type='Online'  AND c.farmer_id =" . $farmer_id . "";

            $query1            = $this->db->query($sql1);

            $total_paid_online = $query1->result_array();



            $sql2            = "SELECT SUM(c.total_amount::INTEGER) as total_pay_cod from crop_product c where c.is_deleted=false AND c.payment_type='COD' AND c.farmer_id =" . $farmer_id . "";

            $query2          = $this->db->query($sql2);

            $total_paid_cash = $query2->result_array();



            $sql3              = "SELECT SUM(c.payed_amount::INTEGER) as payed_amount from crop_product c where c.is_deleted=false  AND c.farmer_id =" . $farmer_id . "";

            $query3            = $this->db->query($sql3);

            $total_paid_amount = $query3->result_array();



            if (!empty($total_amount[0]['total_payment']) && !empty($total_paid_amount[0]['payed_amount'])) {

                // $total_due_amount = $total_amount[0]['total_payment'] - $total_paid_amount[0]['payed_amount'];

                $total_due_amount = 0;

            } else {

                $total_due_amount = 0;

            }



            $dashboard_data[] = array(

                'total_amount'      => $total_amount[0]['total_payment'],

                'total_due_amount'  => $total_due_amount,

                'total_paid_amount' => $total_amount[0]['total_payment'],

                'total_paid_cash'   => $total_paid_cash[0]['total_pay_cod'],

                'total_paid_online' => $total_paid_online[0]['total_pay_online'],

            );

            // print_r($dashboard_data);exit();

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $dashboard_data, "message" => "Get farmer dashboard");



            $this->response($response);

            exit;



        } else {



            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Farmer id missing , please try again some time.");

            $this->response($response);

            exit;

        }



        $this->response($response);

        exit;

    }



//http://115.124.120.147/gfresh/api/farmer/get_farmer_product_invoice/10





    public function get_markets()

    {



        $this->db->select('*');

        $this->db->where('is_active', true);

        $this->db->where('is_deleted', false);

        $markets = $this->db->get('market_master')->result_array();

        $response  = array("success" => 1, "error" => 0, "status" => 1, "data" => $markets, "message" => "market data");



        $this->response($response);



    }



    public function response($response)

    {

        header('Content-type: application/json');

        echo json_encode($response);

    }



}

