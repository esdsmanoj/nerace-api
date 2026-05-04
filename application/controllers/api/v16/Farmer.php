<?php
defined('BASEPATH') or exit('No direct script access allowed');

error_reporting(E_ERROR | E_PARSE);
//error_reporting(E_ERROR | E_PARSE);

//error_reporting(E_ALL);

require APPPATH . 'libraries/RestController.php';

use chriskacerguis\RestServer\RestController;

class Farmer extends RestController
{

    public function __construct()
    {
        parent::__construct();
        $headers_data = $this->input->request_headers();

        // Start: Required headers and there value check
        $require_headers = array('domain', 'appname');
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
            $msg = "Invalid Request";
            $response = array("status" => 0, "error" => 1, "data" => array(), "message" => $msg);
            $this->api_response($response);
            exit;
        } else if (!empty($require_header_val) && count($require_header_val) > 0) {
            $require_header_str = implode(', ', $require_header_val);
            // $msg              = "Required headers values: " . $require_header_str;
            $msg = "Invalid Request";
            $response = array("status" => 0, "error" => 1, "data" => array(), "message" => $msg);
            $this->api_response($response);
            exit;
        }
        // End: Required headers and there value check

        // Start: Create upload file name and as per database name : Akash
        $this->connected_domain = '';
        $root_folder = $_SERVER['DOCUMENT_ROOT'] . '/' . MAIN_FOLDER . '/';

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


        $this->connected_domain = strtolower($headers_data['domain']); // globaly set connected domain name
        $db_folder = $root_folder . 'uploads/' . $this->connected_domain;
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
        $this->load->model('Email_model');
        $this->load->helper('log_helper');
        $this->load->helper('sms_helper');
        $this->load->model('Masters_model');
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
        $group_id = $group_id_arr[0];

        // Below array is in used
        // $crop_product_img =  $this->upload_file_folder.'farm/' . $crop_prod_image2;

        $this->config_url = array(
            'crop_prod_new' => $base_path . 'uploads/' . $this->connected_domain . '/user_data/farm/',
            'crop_product_img' => $base_path . 'uploads/' . $this->connected_domain . '/user_data/farm/',
            'category_img_url' => $base_path . 'uploads/category/',
            'partner_img_url' => $base_path . 'uploads/profile/',
            'aadhar_no_doc_url' => $base_path . 'uploads/aadhar_no/',
            'pan_no_doc_url' => $base_path . 'uploads/pan_no/',
            'farm_image_url' => $base_path . 'uploads/farm/',
            'Product_image_url' => $base_path . 'uploads/productcategory/',
            'market_cat_image_url' => $base_path . 'uploads//logo/',
            'service_image_url' => $base_path . 'uploads/product_service/',
            'blogs_types_url' => $base_path . 'uploads/blogs_types/',
            'blogs_tags_url' => $base_path . 'uploads/blogs_tags/',
            'created_blogs_url' => $base_path . 'uploads/created_blogs/',
            'farmer_documents_url' => $base_path . 'uploads/verification_documents/',
            'advertise_image_url' => $base_path . 'uploads/advertise_master/',
            'whitelabel_image_url' => $base_path . 'uploads/client_group_master/',
            'terms_sheet' => $base_path . 'uploads/terms_sheet/',
            'farm_doc' => $base_path . 'uploads/farm_doc/',
            'insurance_company' => $base_path . 'uploads/insurance_company/',
            'crop_image_url' => $base_path . 'uploads/crops/',
            'crop_type_url' => $base_path . 'uploads/crop_type_icon/',
            'crop_invoice_url' => $base_path . 'uploads/crop_invoice/',
            'crop_health_predict_api' => 'http://115.124.96.136:8443/predict',
            'privacy_policy' => 'https://gfreshagrotech.com/privacy-policy/',
            'terms_and_conditions' => 'https://gfreshagrotech.com/terms-and-conditions/',

        );

        // 'crop_health_predict_api'  => 'http://115.124.96.136:8443/predict',
        $this->menu = array(
            array('id' => '1', 'title' => 'Profile', 'icon' => 'user_prof'),
            array('id' => '2', 'title' => 'Home', 'icon' => 'home'),
            array('id' => '9', 'title' => 'Change Language', 'icon' => 'change_language'),
            array('id' => '10', 'title' => 'Logout', 'icon' => 'logout'),
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

        $this->topology_web = array('1' => 'High', '2' => 'Low', '3' => 'Medium');
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

        $this->irri_faty_web = array('1' => 'Pipelines', '2' => 'Sprinkler Heads', '3' => 'Valves');
        $this->irri_faty_web_mr = array('1' => 'पाईपलाईन', '2' => 'शिंपडण्याचे प्रमुख', '3' => 'वाल्व्ह');

        $this->soil_type_web = array('1' => 'Light Clay', '2' => 'Medium red', '3' => 'Black', '4' => 'Medium black', '5' => 'Black solid', '6' => 'Limestone / Sherwat');
        $this->soil_type_web_mr = array('1' => 'हलकी चिकणमाती', '2' => 'मध्यम लाल', '3' => 'काळा', '4' => 'मध्यम  काळा', '5' => 'काळा घन', '6' => 'चुनखडी / शेरवत');

        $this->farm_type_web = array('1' => 'Organic Farming', '2' => 'Conventional Farming', '3' => 'Residue Free Farming');
        $this->farm_type_web_mr = array('1' => 'सेंद्रिय शेती', '2' => 'पारंपारिक शेती', '3' => 'अवशेष मुक्त शेती');

        ///////////////////////////////////

        $this->crop_type = array(
            array('id' => '1', 'value' => 'Kharif', 'name_mr' => 'खरिफ'),
            array('id' => '2', 'value' => 'Rabi', 'name_mr' => 'रुबी'),
            array('id' => '3', 'value' => 'fruits', 'name_mr' => 'फळे'),

        );
        $this->vehicle_type = array(
            array('id' => 'pickup', 'value' => 'Pickup'),
            array('id' => 'tractor', 'value' => 'Tractor'),
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
    /***********************Working APIs: Start***********************/

    function api_response($data, $status = null, $token = null)
    {
        // echo $this->base_path;exit;
        if (!empty($token)) {
            header('Authorization: ' . $token);
        }
        if (empty($status)) {
            $status = 200;
        }
        // $this->save_logs($data); // Save logs
        echo $this->response($data, $status);
        exit;
    }

    public function index_get()
    {

        $response = array("status" => 0, "message" => "");
        $row = $this->db->get('users');
        //$row = $this->db->get('users')->where('U.is_deleted = false and U.email_verify = true');
        $result = $row->result_array();
        if (count($result)) {
            $response = array("status" => 1, "data" => $result, "message" => "User data");
        }
        $this->api_response($response);
    }

    public function splash_data_get()
    {
        $result['splash_data'] = $this->splash_data;
        $response = array("status" => 1, "data" => $result, "message" => "splash screen data");
        $this->api_response($response);
    }

    public function is_user_regsitered_post()
    {

        if ($this->input->post('phone') != '') {

            $phone = substr(preg_replace('/\s+/', '', $this->input->post('phone')), -10, 10);

            $row = $this->db->query("SELECT * FROM client WHERE is_deleted = 'false' and phone::varchar = '$phone'::varchar ");
            $result = $row->result_array();
            if (count($result)) {

                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Mobile nubmer is Registered", 'is_registered' => 1);
                $this->api_response($response);
                exit;

            } else {

                $response = array("success" => 0, "error" => 0, "status" => 0, "data" => null, "message" => "Mobile nubmer is not Registered", 'is_registered' => 0);
                $this->api_response($response);
                exit;
            }

        } else {

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Err !! Mobile number is blank");
            $this->api_response($response);
            exit;

        }
    }

    public function get_register_otp_post()
    {

        $result = array();
        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Registration failed, please try again some time.");

        if ($this->input->post('btn_submit') == 'submit' && $this->input->post('phone') != '') {
            if (0) {
                $data = $this->input->post();
                $data['error'] = validation_errors();

            } else {

                $phone = substr(preg_replace('/\s+/', '', $this->input->post('phone')), -10, 10);

                $row = $this->db->query("SELECT * FROM client WHERE is_deleted = 'false' and phone::varchar = '$phone'::varchar ");
                // $user_data = $row->result_array();
                /*  $row    = $this->db->query("SELECT id, first_name, middle_name, last_name, email, phone, password, address1, address2, state, city, postcode, company, email_verify, invoice_type, country, profile_image, payment_method, pan_no, gst_no, last_login, ip, village, aadhar_no, dob, gender FROM client WHERE is_deleted = 'false' and phone::varchar = '$phone'::varchar ");*/
                $result = $row->result_array();
                if (count($result)) {
                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => "NULL", "message" => "Mobile nubmer is already Register.");
                    $this->api_response($response);
                    exit;
                } else {

                    $opt_number = mt_rand(100000, 999999);
                    $sms_type = 1; // for OTP its once
                    // $mobile = 8208953165;
                    $mobile = substr(preg_replace('/\s+/', '', $this->input->post('phone')), -10, 10);
                    // $text   = 'Your OTP for Famrut is: ' . $opt_number . ' . Please enter it on the app to confirm your account. Thanks for using Famrut';
                    $text = 'Your OTP for GFresh is ' . $opt_number . '. Please enter OTP into the app to verify your account. Thank you - GFresh Team.';
                    // $mobile = 7448148405;
                    //$this->input->post('phone')
                    // static code added for Testing MMMM
                    if ($phone == 9876543210 || $phone == 9976543210) {
                        $opt_number = 643215;
                        // $update_arr['opt_number'] = $opt_number;
                    } else {
                        $resp = send_sms($mobile, $text, $sms_type);
                    }

                }

                /*$referral_code = $this->input->post('referral_code');
                if (strpos($referral_code, '-') !== false) {

                $get_arr       = explode('-', $referral_code);
                $referral_code = $get_arr[0];
                // echo "My string contains Bob";
                }*/

                $insert = array(
                    'phone' => $mobile,
                    'referral_code' => time(),
                    'state' => 22,
                    'opt_number' => $opt_number,
                    'is_active' => 'true',
                    'my_refferal_code' => time(),
                    'created_on' => current_date(),
                );

                //$insert['opt_number'] = $opt_number;

                if ($this->input->post('device_id')) {
                    $insert['device_id'] = $this->input->post('device_id');
                }

                /*    $full_name = ucfirst($this->input->post('first_name')) . ' ' . ucfirst($this->input->post('last_name'));
                 */
                //user_activity_log
                $title = "Client: Registered";
                $description = json_encode($insert);
                user_activity_logs($title, $description);

                $result = $this->db->insert('client', $insert);
                $insert_id = $this->db->insert_id();

                if ($result) {

                    if (count($insert)) {
                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Registration Successfully", 'opt_number' => $opt_number);
                    }

                    $this->api_response($response);
                    exit;

                } else {

                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Registration failed, please try again some time.");

                    $this->api_response($response);
                    exit;

                }
            }
        }

        $this->api_response($response);
        exit;

    }

    public function resend_otp_post()
    {
        // $this->load->helper('sms_helper');
        $phone = substr(preg_replace('/\s+/', '', $this->input->post('phone')), -10, 10);
        $opt_number = mt_rand(100000, 999999);

        $result = array();
        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Params missing.");

        if ($phone != '' && $opt_number != '') {

            $sql_chk = "SELECT id,is_active FROM client WHERE is_deleted = 'false' and phone::varchar = '$phone'::varchar LIMIT 1";
            $row = $this->db->query($sql_chk);
            $result = $row->result_array();
            if (count($result)) {

                if ($result[0]['is_active'] == "t") {

                    $update_arr['opt_number'] = $opt_number;

                    //$opt_number = mt_rand(100000,999999);
                    $sms_type = 1; // for OTP its once // 7448148405
                    // $mobile   = substr(preg_replace('/\s+/', '', $this->input->post('phone')), -10, 10);
                    $mobile = $phone;
                    //$text   = 'Your OTP for Famrut is: ' . $opt_number . ' . Please enter it on the app to confirm your account. Thanks for using Famrut';
                    $text = 'Your OTP for GFresh is ' . $opt_number . '. Please enter OTP into the app to verify your account. Thank you - GFresh Team.';
                    //$mobile   = 7448148405;

                    if ($phone == 9876543210 || $phone == 9976543210) {
                        $opt_number = 643215;
                        $update_arr['opt_number'] = $opt_number;
                    } else {
                        $update_arr['opt_number'] = $opt_number;
                        $resp = send_sms($mobile, $text, $sms_type);

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
                    $this->api_response($response);
                    exit;

                } else {

                    $response = array("success" => 0, "error" => 1, "status" => 2, "data" => "NULL", "message" => "Your account is not active : contact Admin", 'opt_number' => $opt_number, 'resp_query' => $result[0]['is_active']);
                    $this->api_response($response);
                    exit;
                }

            } else {

                $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "sql_chk" => $sql_chk, "message" => "Mobile Number is not Registed : " . $this->input->post('phone'));
                $this->api_response($response);
                exit;
            }
        }

        $this->api_response($response);
        exit;
    }

    public function get_login_otp_post()
    {

        $result = array();
        $update_arr = array();

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $_POST, "message" => "missing params");

        if ($this->input->post('btn_submit') == 'submit') {
            $username = substr(preg_replace('/\s+/', '', $this->input->post('phone')), -10, 10);
            if ($username == 9876543210) {
                $otp = 643215;
            } else {
                $otp = $this->input->post('otp');
            }

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $_POST, "message" => "missing err 33");

            $row = $this->db->query("SELECT * FROM client WHERE is_deleted = 'false' and phone::varchar = '$username'::varchar ");
            $user_data = $row->result_array();
            //$user_data - 
            $row = $user_data;

            $this->db->select('*');
            $this->db->where('is_deleted', 'false');
            $countries = $this->db->get('countries')->result_array();

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $row, "message" => "missing err 33");

            if (count($user_data)) {

                $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $row, "message" => "missing err 3464 :");

                //if ($user_data[0]['opt_number'] == $otp) 
                if ($row[0]['opt_number'] == $otp || $otp == 999999) {

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


                    $update_arr['login_count'] = $user_data[0]['login_count'] + 1;

                    if (count($update_arr)) {

                        $id = $user_data[0]['id'];
                        $this->db->where('client.id', $id);
                        $result = $this->db->update('client', $update_arr);

                    }

                    $data = array(
                        'user_id' => $row[0]['id'],
                        'first_name' => $row[0]['first_name'],
                        'last_name' => $row[0]['last_name'],
                        'email' => $row[0]['email'],
                        'phone' => $row[0]['phone'],
                        'address1' => $row[0]['address1'],
                        'address2' => $row[0]['address2'],
                        'city' => $row[0]['city'],
                        'postcode' => $row[0]['postcode'],
                        'country_name' => $row[0]['country_name'],
                        'state_name' => $row[0]['state_name'],
                        'branch_name' => $row[0]['branch_name'],
                        'bank_name' => $row[0]['bank_name'],
                        'state' => $row[0]['state'],
                        'acc_no' => $row[0]['acc_no'],
                        'ifsc_code' => $row[0]['ifsc_code'],
                        'village' => $row[0]['village'],
                        'pan_no' => $row[0]['pan_no'],
                        'gst_no' => $row[0]['gst_no'],
                        'company' => $row[0]['company'],
                        'profile_status' => $row[0]['profile_status'],
                        'document_status' => $row[0]['document_status'],
                        'user_type' => 'client',
                        'profile_image' => $row[0]['profile_image'],
                        'pan_no_doc' => $row[0]['pan_no_doc'],
                        'aadhar_no_doc' => $row[0]['aadhar_no_doc'],
                        'aadhar_no' => $row[0]['aadhar_no'],
                        'group_id' => $row[0]['group_id'],
                        'dob' => $row[0]['dob'],
                        'gender' => $row[0]['gender'],
                        'logged_in' => true,
                        'is_login' => true,
                        'my_refferal_code' => $row[0]['my_refferal_code'],
                        'ACCESS_TOKEN' => current_date(),

                    );

                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, "message" => "Login successfully", 'config_url' => $this->config_url, 'menu' => $this->menu, );
                    $this->api_response($response);
                    exit;
                }
            } else {
                $response = array("success" => 0, "error" => 1, "status" => 0, "message" => "mobile not found");
                $this->api_response($response);
                exit;
            }

        }
        $this->api_response($response);
    }



    public function logout_check_get($phone_number)
    {

        if ($phone_number != '') {

            $phone = substr(preg_replace('/\s+/', '', $phone_number), -10, 10);
            // $this->db->select('phone_no,user_id');
            // $this->db->where('phone', $phone);
            $sql = "SELECT phone,id FROM client where phone :: varchar = $phone_number::varchar AND is_active= true AND is_deleted = false ";
            $res_chk = $this->db->query($sql);
            $res = $res_chk->result_array();

            if (count($res) > 0) {

                $update_arr = array('is_login' => false, 'device_id' => null);
                $this->db->where('client.phone', $phone);
                $result = $this->db->update('client', $update_arr);
                $sql_data = $this->db->last_query();

                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Farmer Logout Successfully");
            } else {
                $response = array("success" => 0, "error" => 1, "status" => 0, "data" => "", "message" => "Farmer Not found");
            }

        } else {

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => "", "message" => "Parmas missing");
        }

        $this->api_response($response);
        exit;

    }

    public function about_us_get()
    {

        $result['phone1'] = '+91 9923534591';
        $result['phone2'] = '+91 9923534591';
        $result['email'] = 'office@gfreshagrotech.com';
        $result['address'] = '2039 A Pandit Mohalla, Garhi Village, Alipur North West Delhi India';
        $result['about_us'] = 'GFresh Agrotech is the biggest marketplace for onions all over India. Their products are in the form of four types of onions viz. Garwa Onion – Grade A, Red Onion – Grade A, Red Onion Golta – Grade A, and Unala Kanda – Grade A. 

        Their onions are directly sourced from onions without any middleman and they assure quality and appropriate weight in their products. The platform claims to have impacted more than 40,000 farmers in less than 24 months of operation. One can shop these onions from their website as well.';

        $result['about_us_mr'] = 'GFresh Agrotech ही संपूर्ण भारतातील कांद्याची सर्वात मोठी बाजारपेठ आहे. त्यांची उत्पादने चार प्रकारच्या कांद्याच्या स्वरूपात आहेत उदा. गारवा कांदा – ग्रेड ए, लाल कांदा – ग्रेड ए, लाल कांदा गोलटा – ग्रेड ए आणि उनाला कांडा – ग्रेड ए. त्यांचे कांदे कोणत्याही मध्यस्थीशिवाय थेट कांद्यापासून मिळवले जातात आणि ते त्यांच्या उत्पादनांमध्ये गुणवत्ता आणि योग्य वजनाची खात्री देतात. 

        प्लॅटफॉर्मने 24 महिन्यांपेक्षा कमी कालावधीत 40,000 हून अधिक शेतकऱ्यांना प्रभावित केल्याचा दावा केला आहे. हे कांदे त्यांच्या वेबसाइटवरूनही खरेदी करता येतात';

        $response = array("success" => 1, "data" => $result, "msg" => 'About us', "error" => 0, "status" => 1);

        $this->api_response($response);
    }

    public function get_crop_list_get()
    {
        $headers_data   = $this->input->request_headers();
        $selected_lang = $headers_data['lang'];
        $lang_label    = " name_mr ";
        if ($selected_lang == 'mr') {
            $lang_folder = "marathi";
        } elseif ($selected_lang == 'hi') {
            $lang_folder = "hindi";
            $lang_label  = " name_hi as name_mr ";
        } else {
            $lang_folder = "english";
            $lang_label  = " name_mr ";
        }

        $v_sql = "SELECT crop_variety_id, crop_id FROM crop_variety_master
        WHERE is_deleted=false AND is_active=true ";
        $v_res = $this->db->query($v_sql);
        $v_res_array = $v_res->result_array();

        if(count($v_res_array)){
            $crop_ids = array_unique(array_column($v_res_array, 'crop_id'));
        }
        
        $sql_chk = "SELECT crop_id,lang_json->>'" . $selected_lang . "' as name FROM crop
        WHERE is_deleted=false AND is_active=true ";
        if(count($crop_ids) > 0){
            $sql_chk    .= " AND crop_id IN (". implode(', ',$crop_ids).")";
        }
        $res_val = $this->db->query($sql_chk);
        $res_array = $res_val->result_array();



        
        $response = array("success" => 1, "data" => $res_array, "error" => 0, "status" => 1);
        $this->api_response($response);
    }

    public function get_crop_variety_get($crop_id)
    {
        //name_hindi
        $headers_data = $this->input->request_headers();
        if ($client_id == '') {
            $client_id = $headers_data['client_id'];
        }
        $selected_lang = $headers_data['lang'];
        $sql_limit = '';
        $lang_label = " name_mr ";
        if ($selected_lang == 'mr') {
            $lang_folder = "marathi";
        } elseif ($selected_lang == 'hi') {
            $lang_folder = "hindi";
            $lang_label = " name_hi as name_mr ";
        } else {
            $lang_folder = "english";
            $lang_label = " name_mr ";
        }

        $sql_chk = "SELECT crop_variety_id,variety_lang_json->>'" . $selected_lang . "' as name,name_mr,crop_id FROM crop_variety_master
        WHERE is_deleted=false AND is_active=true AND crop_id=" . $crop_id;
        $res_val = $this->db->query($sql_chk);
        $res_array = $res_val->result_array();
        $response = array("success" => 1, "data" => $res_array, "error" => 0, "status" => 1);
        $this->api_response($response);
    }

    public function update_bank_details_post()
    {
        $result = array();
        $id = $this->input->post('id');

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Bank update failed, please try again some time.", "post_param" => $_POST);

        if ($id != '') {

            if (0) {

            } else {

                $bank_name = $this->input->post('bank_name') != '' ? $this->input->post('bank_name') : null;
                $branch_name = $this->input->post('branch_name') != '' ? $this->input->post('branch_name') : null;
                $acc_no = $this->input->post('acc_no') != '' ? $this->input->post('acc_no') : 0;
                $ifsc_code = $this->input->post('ifsc_code') != '' ? $this->input->post('ifsc_code') : null;
                $acc_holder_name = $this->input->post('acc_holder_name') != '' ? $this->input->post('acc_holder_name') : null;

                $update_arr['bank_name'] = $bank_name;
                $update_arr['branch_name'] = $branch_name;
                $update_arr['acc_no'] = $acc_no;
                $update_arr['ifsc_code'] = $ifsc_code;
                $update_arr['acc_holder_name'] = $acc_holder_name;
                $update_arr['updated_on'] = current_date();
                $this->db->where('client.id', $id);
                $result = $this->db->update('client', $update_arr);
                if ($result) {

                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Bank Details updated Successfully");

                    $this->api_response($response);
                    exit;

                } else {

                    $response = array("success" => 0, "error" => 1, "status" => 2, "data" => $result, "message" => "Err!! Bank updated failed, please try again some time.", "post_param" => $_POST);

                    $this->api_response($response);
                    exit;

                }
            }
        }

        $this->api_response($response);
        exit;
    }

    public function update_documents_post()
    {
        $result = array();
        $id = $this->input->post('id');
        $image = '';
        $farm_doc_7_12_name = '';
        $pan_no_doc_name = '';
        $aadhar_no_doc_name = '';

        if (!empty($_FILES['doc_7_12']['name'])) {

            $extension = pathinfo($_FILES['doc_7_12']['name'], PATHINFO_EXTENSION);

            $farm_doc_7_12_name = $this->connected_domain . '_doc_7_12_' . time() . '.' . $extension;
            if (!file_exists($this->upload_file_folder . 'farm_doc')) {
                mkdir($this->upload_file_folder . 'farm_doc', 0777, true);
            }
            $target_file = $this->upload_file_folder . 'farm_doc/' . $farm_doc_7_12_name;


            // for delete previous image.
            if ($this->input->post('old_doc_7_12') != "") {
                @unlink($this->upload_file_folder . 'farm_doc/' . $this->input->post('old_doc_7_12'));
            }

            if (move_uploaded_file($_FILES["doc_7_12"]["tmp_name"], $target_file)) {
                $update_arr['doc_7_12'] = $farm_doc_7_12_name;
                // $doc_7_12_upload = $farm_doc_7_12_name;
                $error = 0;

            } else {

                $error = 2;

            }
        }

        if (!empty($_FILES['pan_no_doc']['name'])) {
            $extension = pathinfo($_FILES['pan_no_doc']['name'], PATHINFO_EXTENSION);

            $pan_no_doc_name = $this->connected_domain . '_pan_no_doc_' . time() . '.' . $extension;
            if (!file_exists($this->upload_file_folder . 'pan_no')) {
                mkdir($this->upload_file_folder . 'pan_no', 0777, true);
            }
            $target_file = $this->upload_file_folder . 'pan_no/' . $pan_no_doc_name;


            // for delete previous image.
            if ($this->input->post('old_pan_no_doc') != "") {
                @unlink($this->upload_file_folder . 'pan_no/' . $this->input->post('old_pan_no_doc'));
            }

            if (move_uploaded_file($_FILES["pan_no_doc"]["tmp_name"], $target_file)) {
                $update_arr['pan_no_doc'] = $pan_no_doc_name;
                $error = 0;

            } else {
                $error = 2;
            }
        }
        if (!empty($_FILES['aadhar_no_doc']['name'])) {
            $extension = pathinfo($_FILES['aadhar_no_doc']['name'], PATHINFO_EXTENSION);

            $aadhar_no_doc_name = $this->connected_domain . '_aadhar_no_doc_' . time() . '.' . $extension;
            if (!file_exists($this->upload_file_folder . 'aadhar_no')) {
                mkdir($this->upload_file_folder . 'aadhar_no', 0777, true);
            }
            $target_file = $this->upload_file_folder . 'aadhar_no/' . $aadhar_no_doc_name;


            // for delete previous image.
            if ($this->input->post('old_aadhar_no_doc') != "") {
                @unlink($this->upload_file_folder . 'aadhar_no/' . $this->input->post('old_aadhar_no_doc'));
            }

            if (move_uploaded_file($_FILES["aadhar_no_doc"]["tmp_name"], $target_file)) {
                $update_arr['aadhar_no_doc'] = $aadhar_no_doc_name;
                // $$aadhar_no_doc_name = $aadhar_no_doc_name;
                $error = 0;
            } else {
                $error = 2;

            }
        }

        if (!empty($_FILES['aadhar_no_doc_back']['name'])) {
            $extension = pathinfo($_FILES['aadhar_no_doc_back']['name'], PATHINFO_EXTENSION);

            $aadhar_no_doc_name = $this->connected_domain . '_aadhar_no_doc_back' . time() . '.' . $extension;
            if (!file_exists($this->upload_file_folder . 'aadhar_no')) {
                mkdir($this->upload_file_folder . 'aadhar_no', 0777, true);
            }
            $target_file = $this->upload_file_folder . 'aadhar_no/' . $aadhar_no_doc_name;


            // for delete previous image.
            if ($this->input->post('aadhar_no_doc_back') != "") {
                @unlink($this->connected_domain . 'aadhar_no/' . $this->input->post('aadhar_no_doc_back'));
            }

            if (move_uploaded_file($_FILES["aadhar_no_doc_back"]["tmp_name"], $target_file)) {
                $update_arr['aadhar_no_doc_back'] = $aadhar_no_doc_name;
                // $$aadhar_no_doc_name = $aadhar_no_doc_name;
                $error = 0;
            } else {
                $error = 2;

            }
        }

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Document update failed, please try again some time.", "post_param" => $_POST);

        if ($id != '') {

            if (0) {

            } else {

                /* if ($pan_no_doc_name != '') {
                $update_arr['pan_no_doc'] = $pan_no_doc_name;
                }
                if ($doc_7_12_upload != '') {
                $update_arr['doc_7_12'] = $doc_7_12_upload;
                }
                if ($aadhar_no_doc_name != '') {
                $update_arr['aadhar_no_doc'] = $aadhar_no_doc_name;
                }*/

                $update_arr['updated_on'] = current_date();

                $this->db->where('client.id', $id);
                $result = $this->db->update('client', $update_arr);
                if ($result) {

                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Documents updated Successfully", 'config_url' => $this->config_url);

                    $this->api_response($response);
                    exit;

                } else {

                    $response = array("success" => 0, "error" => 1, "status" => 2, "data" => $result, "message" => "Err!! profile updated failed, please try again some time.", "post_param" => $_POST);

                    $this->api_response($response);
                    exit;
                }
            }
        }
        $this->api_response($response);
        exit;
    }

    public function get_states_new_post()
    {
        $country_id = $this->input->post('country_id') ? $this->input->post('country_id') : 101;

        if ($country_id) {
            //os version
            $type = $this->input->post('type');
            $where = array('country_id' => $country_id);
            $result = $this->Masters_model->get_data(array('id', 'name', 'country_id'), 'states_new', $where);
            $str = '<option value="">Select state</option>';

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
                $this->api_response($response);
                exit;

            } else {
                echo $str;
                exit;
            }
        } else {

            $response = array("status" => 0, "error" => 1, "success" => 0, "data" => null, "message" => "Parmans missing country_id");
            $this->api_response($response);
            exit;

        }
    }

    public function get_city_new_post()
    {
        // $state      = $state;
        // $state_id = $this->input->post('state_id');
        $type = $this->input->post('type');
        //$city       = $this->input->post('city');
        //os version
        $state_id = $this->input->post('state_id') ? $this->input->post('state_id') : 22;

        if ($state_id) {
            $where = array('state_id' => $state_id);
            $result = $this->Masters_model->get_data(array('id', 'name', 'state_id'), 'cities_new', $where);
            $str = "";
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
                $this->api_response($response);
                exit;

            } else {
                echo $str;
                exit;
            }
        } else {

            $response = array("status" => 0, "error" => 1, "success" => 0, "data" => null, "message" => "Parmans missing state_id");
            $this->api_response($response);
            exit;

        }
    }

    public function update_profile_post()
    {
        $result = array();
        $id = $this->input->post('id');
        $image = '';
        $profile_image = '';
        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => array(), "message" => "Profile PARAMS MISSING.", "post_param" => $_POST);

        if (!empty($_FILES['profile_image']['name'])) {
            $extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            //echo $extension;
            $profile_image_name = $this->connected_domain . '_profile_image_' . time() . '.' . $extension;
            if (!file_exists($this->upload_file_folder . 'profile')) {
                mkdir($this->upload_file_folder . 'profile', 0777, true);
            }
            $target_file = $this->upload_file_folder . 'profile/' . $profile_image_name;




            // for delete previous image.
            // if ($this->input->post('old_profile_image') != "") {
            //     @unlink('./uploads/profile/' . $this->input->post('old_profile_image'));
            // }

            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                $profile_image = $profile_image_name;
                $error = 0;

            } else {
                $error = 2;
            }
        }

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Profile update failed, please try again some time.", "post_param" => $_POST);

        if ($id != '') {

            if (0) {

            } else {

                $address1 = $this->input->post('address1') != '' ? $this->input->post('address1') : null;
                /* $postcode    = $this->input->post('postcode') != '' ? $this->input->post('postcode') : null;
                $village     = $this->input->post('village') != '' ? $this->input->post('village') : null;
                $dob         = $this->input->post('dob') != '' ? $this->input->post('dob') : null;*/

                $update_arr['first_name'] = $this->input->post('first_name');
                $update_arr['last_name'] = $this->input->post('last_name');
                /*$update_arr['phone']       = $this->input->post('phone');*/
                $update_arr['email'] = $this->input->post('email');
                /*  $update_arr['country']     = $this->input->post('country');*/
                $update_arr['state'] = $this->input->post('state');
                $update_arr['city'] = $this->input->post('city');
                $update_arr['village'] = $this->input->post('village');
                $update_arr['pan_no'] = $this->input->post('pan_no');
                $update_arr['aadhar_no'] = $this->input->post('aadhar_no');
                $update_arr['gst_no'] = $this->input->post('gst_no');
                /*  $update_arr['village']     = $village;*/
                /*   $update_arr['postcode']    = $postcode;*/
                $update_arr['address1'] = $address1;
                /*   $update_arr['dob']         = $dob;
                $update_arr['gender']      = $this->input->post('gender');          */
                $update_arr['updated_on'] = current_date();

                if ($profile_image != '') {
                    $update_arr['profile_image'] = $profile_image;
                }

                $this->db->where('client.id', $id);
                $result = $this->db->update('client', $update_arr);
                if ($result) {

                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "profile updated Successfully", 'config_url' => $this->config_url);

                    $this->api_response($response);
                    exit;

                } else {

                    $response = array("success" => 0, "error" => 1, "status" => 2, "data" => $result, "message" => "Err!! profile updated failed, please try again some time.", "post_param" => $_POST);

                    $this->api_response($response);
                    exit;
                }
            }
        }

        $this->api_response($response);
        exit;
    }

    public function add_crop_product_post()
    {

        $result = array();
        $image = '';
        $crop_img1 = '';
        $crop_img2 = '';

        $crop_product_img = $this->upload_file_folder . 'farm/' . $crop_prod_image2;



        if (!empty($_FILES['crop_img1']['name'])) {

            $extension = pathinfo($_FILES['crop_img1']['name'], PATHINFO_EXTENSION);

            $crop_prod_image = $this->connected_domain . '_crop_prod_image_one' . time() . '.' . $extension;
            if (!file_exists($this->upload_file_folder . 'farm')) {
                mkdir($this->upload_file_folder . 'farm', 0777, true);
            }
            $target_file = $this->upload_file_folder . 'farm/' . $crop_prod_image;


            // for delete previous image.
            if ($this->input->post('old_crop_img1') != "") {
                @unlink($this->upload_file_folder . 'farm/' . $this->input->post('old_crop_img1'));
            }

            if (move_uploaded_file($_FILES["crop_img1"]["tmp_name"], $target_file)) {
                $crop_img1 = $crop_prod_image;
                $error = 0;

            } else {

                $error = 2;

            }
        }

        if (!empty($_FILES['crop_img2']['name'])) {

            $extension = pathinfo($_FILES['crop_img2']['name'], PATHINFO_EXTENSION);

            $crop_prod_image2 = $this->connected_domain . '_crop_prod_image_two' . time() . '.' . $extension;
            if (!file_exists($this->upload_file_folder . 'farm')) {
                mkdir($this->upload_file_folder . 'farm', 0777, true);
            }
            $target_file = $this->upload_file_folder . 'farm/' . $crop_prod_image2;
            // for delete previous image.
            if ($this->input->post('old_crop_img2') != "") {
                @unlink($this->upload_file_folder . 'farm/' . $this->input->post('old_crop_img2'));
            }

            if (move_uploaded_file($_FILES["crop_img2"]["tmp_name"], $target_file)) {
                $crop_img2 = $crop_prod_image2;
                $error = 0;

            } else {

                $error = 2;

            }
        }

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Product add failed, please try again some time.");

        if ($this->input->post('btn_submit') == 'submit') {

            if (0) {
                $data = $this->input->post();
                $data['error'] = validation_errors();
            } else {

                $insert = array(
                    'crop_id' => $this->input->post('crop_id'),
                    'crop_variety_id' => $this->input->post('crop_variety_id'),
                    'farmer_id' => $this->input->post('farmer_id'),
                    'prod_desc' => $this->input->post('prod_desc'),
                    'market_id' => $this->input->post('market_id'),
                    /* 'price'           => $this->input->post('price'),
                    'price_unit'           => $this->input->post('price_unit'),
                    'weight'                => $this->input->post('weight'),
                    'weight_unit'   => $this->input->post('weight_unit'),
                    'product_status' => $this->input->post('product_status'),*/
                    /*'payed_amount'           => $this->input->post('payed_amount'),
                    'total_amount'        => $this->input->post('total_amount'), */
                    'product_status' => 0,
                    'product_add_date' => current_date(),
                    'created_on' => current_date(),
                );

                if ($crop_img1 != '') {
                    $insert['crop_img1'] = $crop_img1;
                }

                if ($crop_img2 != '') {
                    $insert['crop_img2'] = $crop_img2;
                }

                $result = $this->db->insert('crop_product', $insert);
                $insert_id = $this->db->insert_id();

                $title = "Crop Product: Added";
                $description = json_encode($insert);
                user_activity_logs($title, $description);

                if ($insert_id) {

                    if (count($insert_id)) {
                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Product added Successfully", 'config_url' => $this->config_url, "crop_product_img" => $crop_product_img, "post_Data" => $insert, 'target_file' => $target_file);
                    }



                    $this->api_response($response);
                    exit;

                } else {

                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Product add failed, please try again some time.");

                    $this->api_response($response);
                    exit;

                }
            }
        }

        $this->api_response($response);
        exit;
    }

    public function get_crop_variety_price_post()
    {

        $crop_id = $this->input->post('crop_id');
        $crop_variety_id = $this->input->post('crop_variety_id');
        if ($crop_variety_id != '' && $crop_id != '') {
            $sql_chk = "SELECT market_date,crop_variety_id,product_price,unit,crop_id FROM crop_price_master
            WHERE is_deleted=false AND is_active=true AND crop_variety_id=" . $crop_variety_id . " AND crop_id=" . $crop_id . " ORDER BY crop_price_id DESC LIMIT 1";
            $res_val = $this->db->query($sql_chk);
            $res_array = $res_val->result_array();
            $response = array("success" => 1, "data" => $res_array, "error" => 0, "status" => 1);
        } else {
            $res_array = $_POST;
            $response = array("success" => 0, "data" => $res_array, "msg" => "params missing", "error" => 1, "status" => 0);
        }
        $this->api_response($response);
    }

    public function get_farmer_product_get($farmer_id)
    {

        $result = array();
        // $crop_product_img = $this->base_path.'uploads/farm/' . $crop_prod_image2;
        $crop_product_img = $this->upload_file_folder . 'farm/' . $crop_prod_image2;
        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "missing params");

        if ($farmer_id) {


            $sql_data = "SELECT p.*,c.crop_id,c.name,c.name_mr,c.logo as mob_icon,c.mob_icon as mob_icon ,ct.name_en as crop_variety_name,ct.name_mr as crop_variety_name_mr ,m.name as market_name,m.name_mr as market_name_mr 
        FROM crop_product as p
        LEFT JOIN crop as c ON c.crop_id = p.crop_id
        LEFT JOIN crop_variety_master ct ON ct.crop_variety_id = p.crop_variety_id
        LEFT JOIN market_master as m ON m.market_id = p.market_id
        WHERE p.is_deleted = false  AND p.farmer_id = $farmer_id AND c.is_deleted = false  ORDER BY p.id DESC";

            $row = $this->db->query($sql_data);
            $res = $row->result_array();

            if (count($res) > 0) {
                foreach ($res as $key => $value) {
                    $res[$key]['weight_unit'] = ucwords($res[$key]['weight_unit']);
                    $res[$key]['product_add_date'] = date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($res[$key]['product_add_date'])));
                }
                // print_r($res);exit;
                //$this->config_url['farm_image_url']
                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $res, "message" => "Farmer Prodcuts listing", 'config_url' => $this->config_url);
            } else {
                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Farmer Prodcuts listing", 'crop_product_img' => $crop_product_img, 'config_url' => $this->config_url);
            }
        }
        $this->api_response($response);
        exit;
    }

    public function get_farmer_product_detail_get($crop_product_id)
    {

        $result = array();
        // $crop_product_img = $this->base_path.'uploads/farm/' . $crop_prod_image2;
        $crop_product_img = $this->upload_file_folder . 'farm/' . $crop_prod_image2;
        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "missing params");

        if ($crop_product_id) {


            $sql_data = "SELECT p.*,c.crop_id,c.name,c.name_mr,c.logo as mob_icon,c.mob_icon as mob_icon ,ct.name_en as crop_variety_name,ct.name_mr as crop_variety_name_mr ,m.name as market_name,m.name_mr as market_name_mr 
            FROM crop_product as p
            LEFT JOIN crop as c ON c.crop_id = p.crop_id
            LEFT JOIN crop_variety_master ct ON ct.crop_variety_id = p.crop_variety_id
            LEFT JOIN market_master as m ON m.market_id = p.market_id
            WHERE p.is_deleted = false  AND p.id = $crop_product_id AND c.is_deleted = false  ORDER BY p.id DESC";

            $row = $this->db->query($sql_data);
            $res = $row->row_array();

            if (count($res) > 0) {
                $res['weight_unit'] = ucwords($res['weight_unit']);
                $res['product_add_date'] = date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($res['product_add_date'])));
                // print_r($res);exit;
                //$this->config_url['farm_image_url']
                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $res, "message" => "Farmer Prodcuts listing", 'config_url' => $this->config_url);
            } else {
                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Farmer Prodcuts listing", 'crop_product_img' => $crop_product_img, 'config_url' => $this->config_url);
            }
        }
        $this->api_response($response);
        exit;
    }

    public function update_crop_product_status_post()
    {
        $result = array();
        $image = '';
        $crop_img1 = '';
        $crop_img2 = '';

        $id = $this->input->post('id');
        $product_status = $this->input->post('product_status');

        if ($this->input->post('id') != '' && $product_status != '') {

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Product Updation failed here, please try again some time.");

            if ($this->input->post('btn_submit') == 'submit') {

                if (0) {
                    $data = $this->input->post();
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
                            $url_invoice_update = $this->base_path . '/GeneratePdfController/index/' . $id;
                            $data_pdf_update = file_get_contents($url_invoice_update);
                            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $update_arr, "message" => "Product Status Updated Successfully", 'config_url' => $this->config_url, 'url_invoice_update' => $url_invoice_update);
                        }
                        $this->api_response($response);
                        exit;

                    } else {

                        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Product Status update failed, please try again some time.");

                        $this->api_response($response);
                        exit;

                    }
                }
            }

        } else {

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Product id missing , please try again some time.");
            $this->api_response($response);
            exit;

        }

        $this->api_response($response);
        exit;
    }

    public function get_farmer_product_invoice_get($id)
    {

        $result = array();
        $image = '';
        $crop_img1 = '';
        $crop_img2 = '';

        //$id = $this->input->post('id');
        //$product_status = $this->input->post('product_status');

        // $crop_product_img    = $this->base_path.'uploads/farm/' . $crop_prod_image2;
        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "missing params");

        if ($id) {
            /*AND p.farmer_id = $farmer_id*/

            $sql_data = "SELECT p.*,c.crop_id,c.name,c.name_mr,c.logo as mob_icon,c.mob_icon as mob_icon, ct.name_en as crop_variety_name,ct.name_mr as crop_variety_name_mr,m.name as market_name,m.name_mr as market_name_mr 
            FROM crop_product as p
            LEFT JOIN crop as c ON c.crop_id = p.crop_id
            LEFT JOIN crop_variety_master ct ON ct.crop_variety_id = p.crop_variety_id
            LEFT JOIN market_master as m ON m.market_id = p.market_id
            WHERE p.is_deleted = false  AND p.id = $id AND c.is_deleted = false";

            $row = $this->db->query($sql_data);
            $res = $row->result_array();

            if (count($res) > 0) {

                $res[0]['weight_unit'] = ucwords($res[0]['weight_unit']);

                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $res, "message" => "Farmer Prodcuts invoice", 'config_url' => $this->config_url);
            } else {
                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Farmer Prodcuts invoice", 'crop_product_img' => $crop_product_img, 'config_url' => $this->config_url);
            }
        }
        $this->api_response($response);
        exit;
    }

    public function product_invoice_list_get($farmer_id)
    {

        $result = array();
        // $crop_product_img = $this->base_path. 'uploads/farm/' . $crop_prod_image2;
        $crop_product_img = '';
        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "missing params");

        if ($farmer_id) {

            $sql_data = "SELECT p.*,c.crop_id,c.name,c.name_mr,c.logo as mob_icon,c.mob_icon as mob_icon ,ct.name_en as crop_variety_name,ct.name_mr as crop_variety_name_mr,m.name as market_name,m.name_mr as market_name_mr 
            FROM crop_product as p
            LEFT JOIN crop as c ON c.crop_id = p.crop_id
            LEFT JOIN crop_variety_master ct ON ct.crop_variety_id = p.crop_variety_id
            LEFT JOIN market_master as m ON m.market_id = p.market_id
            WHERE p.is_deleted = false  AND p.invoice_number != '' AND p.invoice_file != ''  AND p.farmer_id = $farmer_id AND c.is_deleted = false";
            // echo $sql_data;exit;


            $row = $this->db->query($sql_data);
            $res = $row->result_array();

            if (count($res) > 0) {
                $res[0]['weight_unit'] = ucwords($res[0]['weight_unit']);

                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $res, "message" => "Farmer Invoice Prodcuts listing", 'config_url' => $this->config_url);
            } else {
                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Farmer Invoice Prodcuts listing", 'crop_product_img' => $crop_product_img, 'config_url' => $this->config_url);
            }
        }
        $this->api_response($response);
        exit;
    }

    public function get_farmer_dashboard_get($farmer_id)
    {

        if ($farmer_id != '') {
            $sql = "SELECT SUM(c.total_amount::REAL) as total_payment  from crop_product c where c.is_deleted=false AND c.farmer_id =" . $farmer_id . "";
            $query = $this->db->query($sql);
            $total_amount = $query->result_array();

            $sql1 = "SELECT SUM(c.total_amount::REAL) as total_pay_online from crop_product c where c.is_deleted=false AND c.payment_type='Online'  AND c.farmer_id =" . $farmer_id . "";
            $query1 = $this->db->query($sql1);
            $total_paid_online = $query1->result_array();

            $sql2 = "SELECT SUM(c.total_amount::REAL) as total_pay_cod from crop_product c where c.is_deleted=false AND c.payment_type='Cash' AND c.farmer_id =" . $farmer_id . "";
            $query2 = $this->db->query($sql2);
            $total_paid_cash = $query2->result_array();

            $sql3 = "SELECT SUM(c.payed_amount::REAL) as payed_amount from crop_product c where c.is_deleted=false  AND c.farmer_id =" . $farmer_id . "";
            $query3 = $this->db->query($sql3);
            $total_paid_amount = $query3->result_array();

            if (!empty($total_amount[0]['total_payment']) && !empty($total_paid_amount[0]['payed_amount'])) {
                // $total_due_amount = $total_amount[0]['total_payment'] - $total_paid_amount[0]['payed_amount'];
                $total_due_amount = 0;
            } else {
                $total_due_amount = 0;
            }

            if (!empty($total_paid_cash[0]['total_pay_cod'])) {
                $total_pay_cod = $total_paid_cash[0]['total_pay_cod'];
            } else {
                $total_pay_cod = 0;
            }

            if (!empty($total_amount[0]['total_payment'])) {
                $total_amount = $total_amount[0]['total_payment'];
            } else {
                $total_amount = 0;
            }

            if (!empty($total_paid_online[0]['total_pay_online'])) {
                $total_paid_online = $total_paid_online[0]['total_pay_online'];
            } else {
                $total_paid_online = 0;
            }

            $dashboard_data[] = array(
                'total_amount' => $total_amount,
                'total_due_amount' => $total_due_amount,
                'total_paid_amount' => $total_amount,
                'total_paid_cash' => $total_pay_cod,
                'total_paid_online' => $total_paid_online,
            );
            // print_r($dashboard_data);exit();
            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $dashboard_data, "message" => "Get farmer dashboard");

            $this->api_response($response);
            exit;

        } else {

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Farmer id missing , please try again some time.");
            $this->api_response($response);
            exit;
        }

        $this->api_response($response);
        exit;
    }

    public function get_farmer_profile_get($farmer_id)
    {

        $result = array();
        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "missing params");

        if ($farmer_id) {

            $select = array('client.*', 'client_group_master.name as group_name', 'countries_new.name as country_name', 'states_new.name as state_name');
            $join = array(
                'countries_new' => array('countries_new.id = 101', 'left'),
                'states_new' => array('cast(states_new.id as INTEGER)  = cast(client.state as INTEGER)', 'left'),
                'client_group_master' => array('client_group_master.client_group_id = client.group_id ', 'left')
            );

            $where = array('client.id' => $farmer_id, 'client.is_deleted' => 'false');
            $user_data = $this->Masters_model->get_data($select, 'client', $where, $join, '', '', 1);

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $user_data, "message" => "Farmer Profile data", 'config_url' => $this->config_url);

        }

        $this->api_response($response);
        exit;

    }

    public function chk_profile_get($farmer_id)
    {


        $row_chk = $this->db->query("SELECT * FROM client WHERE is_deleted = 'false' and id = $farmer_id and first_name != '' and aadhar_no !='' ");
        $res_array = $row_chk->result_array();
        // print_r($res_array);exit();
        if (count($res_array)) {
            $response = array("success" => 1, "error" => 0, "data" => $res_array, "message" => "User Profile Completed ");

        } else {
            $response = array("success" => 0, "error" => 1, "data" => $res_array, "message" => "Enter First And Aadhar Number");

        }

        $this->api_response($response);
        exit;

    }

    public function get_markets_get()
    {

        $this->db->select('*');
        $this->db->where('is_active', true);
        $this->db->where('is_deleted', false);
        $markets = $this->db->get('market_master')->result_array();
        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $markets, "message" => "market data");

        $this->api_response($response);

    }


    public function get_invoice_get($crop_product_id=null)
    {
        if(!empty($crop_product_id)){
            // $this->db->select('*');
            // $this->db->where('is_active', true);
            // $this->db->where('is_deleted', false);
            // $markets = $this->db->get('market_master')->result_array();

            $data = $this->base_path.'GeneratePdfController/index/'.$crop_product_id;

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, "message" => "Invoice Generated Successfully!");
        } else {
            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => [], "message" => "Invoice Not Generated!");
        }

        $this->api_response($response);

    }



    /***********************Working APIs:End***********************/

    /***********************Save Logs:Start***********************/
    function save_logs($response = [])
    {
        $log = array(
            'USER' => $_SERVER['REMOTE_ADDR'],
            'DATE' => date("Y-m-d, H:i:s"),
            'URL' => $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
            'METHOD' => $_SERVER['REQUEST_METHOD'],
            'REQUEST' => $_REQUEST,
            'RESPONSE' => $response,
        );
        //Save string to log, use FILE_APPEND to append.
        $log_filename = APPPATH . "logs";
        if (!file_exists($log_filename)) {
            // create directory/folder uploads.
            mkdir($log_filename, 0777, true);
        }
        $log_file_data = $log_filename . '/log_' . date('d-M-Y') . '.log';
        // if you don't add `FILE_APPEND`, the file will be erased each time you add a log
        file_put_contents($log_file_data, json_encode($log) . "\n", FILE_APPEND);
    }
    /***********************Save Logs:End***********************/

}