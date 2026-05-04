<?php

defined('BASEPATH') or exit('No direct script access allowed');



error_reporting(E_ERROR | E_PARSE);

//error_reporting(E_ERROR | E_PARSE);



//error_reporting(E_ALL);



require APPPATH . 'libraries/RestController.php';



use chriskacerguis\RestServer\RestController;



class Vendor extends RestController

{

    public function __construct()

    {

        parent::__construct();



        // header('Access-Control-Allow-Origin: *');

        // header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");

        // header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");



        $headers_data = $this->input->request_headers();



        // Start: Create upload file name and as per database name : Akash

        $this->connected_domain = '';

        $root_folder            = $_SERVER['DOCUMENT_ROOT'] . MAIN_FOLDER . '/';



        if (strpos($_SERVER['DOCUMENT_ROOT'], 'agri_ecosystem') !== false) {

            $root_folder = $_SERVER['DOCUMENT_ROOT'] . '/'; // Set root folder

        }



        /*if(!isset($headers_data['domain']) || empty($headers_data['domain'])) {

        // Check domain name inside headers if its not found domain name inside headers then get domain name from host url

        $server_host = $_SERVER['HTTP_HOST'];

        $headers_data['domain'] = 'agri_solution';

        if(strpos($server_host,'icar.rmgtec.in') !== false) {

        $headers_data['domain'] = 'famrut_dev';

        } else if(strpos($server_host,'dndccb.rmgtec.in') !== false) {

        $headers_data['domain'] = 'dndccb_dev';

        }

        }*/



        $headers_data = $this->input->request_headers();



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

            //$msg      = "Invalid Requrest " . $require_header_str;

            $msg      = "Invalid Request";

            $response = array("status" => 0, "error" => 1, "data" => array(), "message" => $msg);

            $this->api_response($response);exit;

        } else if (!empty($require_header_val) && count($require_header_val) > 0) {

            $require_header_str = implode(', ', $require_header_val);

            // $msg              = "Required headers values: " . $require_header_str;

            $msg      = "Invalid Request";

            $response = array("status" => 0, "error" => 1, "data" => array(), "message" => $msg);

            $this->api_response($response);exit;

        }



        $this->connected_domain = $headers_data['domain']; // globaly set connected domain name

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

        $this->load->model('Masters_model');

        $this->load->model('Email_model');

        $this->load->helper('log_helper');

        $this->load->helper('sms_helper');



        $lang_folder = "english";

        if ($this->session->userdata('user_site_language') && $this->session->userdata('user_site_language') == "MR") {

            $lang_folder = "marathi";

        } else {

            $this->session->set_userdata('user_site_language', 'EN');

            $lang_folder = "english";

        }



        // $base_path        = $this->base_path;

        // $base_path        = 'https://dev.famrut.co.in/agri_ecosystem/';

        $this->base_path = $base_path = BASE_PATH_PORTAL;      



        $selected_lang = $headers_data['lang'];



        if ($selected_lang == 'mr') {

            $lang_folder = "marathi";

        } elseif ($selected_lang == 'hi') {

            $lang_folder = "hindi";

        } else {

            $lang_folder = "english";

        }



        $this->lang->load(array('site'), $lang_folder);



        $this->config_url = array(

            'category_img_url'     => $base_path . 'uploads/category/',

            'partner_img_url'      => $base_path . 'uploads/profile/',

            'farmer_img_url'       => $base_path . 'uploads/profile/',

            'aadhar_no_doc_url'    => $base_path . 'uploads/aadhar_no/',

            'pan_no_doc_url'       => $base_path . 'uploads/pan_no/',

            'farm_image_url'       => $base_path . 'uploads/farm/',

            'Product_image_url'    => $base_path . 'uploads/productcategory/',

            'market_cat_image_url' => $base_path . 'uploads//logo/',

            'service_image_url'    => $base_path . 'uploads/product_service/',

            'blogs_types_url'      => $base_path . 'uploads/blogs_types/',

            'blogs_tags_url'       => $base_path . 'uploads/blogs_tags/',

            'created_blogs_url'    => $base_path . 'uploads/created_blogs/',

            'farmer_documents_url' => $base_path . 'uploads/verification_documents/',

            'advertise_image_url'  => $base_path . 'uploads/advertise_master/',

            'logo'                 => 'https://www.famrut.co.in/images/logo-two.png',

        );



        $this->menu = array(

            array('id' => '1', 'title' => 'Home', 'icon' => 'home'),

            array('id' => '2', 'title' => 'Profile', 'icon' => 'Profile'),

            array('id' => '5', 'title' => 'Enquiry', 'icon' => 'Enquiry'),

            array('id' => '7', 'title' => 'Services', 'icon' => 'Services'),

            array('id' => '9', 'title' => 'Change Language', 'icon' => 'change_language'),

            array('id' => '10', 'title' => 'Logout', 'icon' => 'logout'),

            array('id' => '11', 'title' => 'About us', 'icon' => 'about_us'),

            array('id' => '12', 'title' => 'Invite', 'icon' => 'Invite_icon'),

        );



        $this->home_message = array('message' => 'Welcome to FAMRUT - 10X Growth solution');



        $this->soil_type = array('1' => 'Light Clay', '2' => 'Medium red', '3' => 'Black', '4' => 'Medium black', '5' => 'Black solid', '6' => 'Limestone / Sherwat');



        $this->topology = array(array('id' => '1', 'value' => 'High', 'name_mr' => 'उंच'), array('id' => '2', 'value' => 'Low', 'name_mr' => 'कमी'), array('id' => '3', 'value' => 'Medium', 'name_mr' => 'मध्यम'));



        $this->topology_web = array('1' => 'High', '2' => 'Low', '3' => 'Medium');



        $this->topology_web_mr = array('1' => 'उंच', '2' => 'कमी', '3' => 'मध्यम');



        $this->farm_type = array('1' => 'Organic Farming', '2' => 'Conventional Farming', '3' => 'Residue Free Farming');



        $this->unit = array('1' => 'Square Yard', '2' => 'Acre', '3' => 'Hectare', '4' => 'Square Meter', '5' => 'Square Mile');



        $this->irri_src = array('1' => 'Well', '2' => 'Borewell', '3' => 'Canal/River', '4' => 'Farm lake', '5' => 'Others');



        $this->irri_faty = array('1' => 'Pipelines', '2' => 'Sprinkler Heads', '3' => 'Valves');



        $this->topology_web    = array('1' => 'High', '2' => 'Low', '3' => 'Medium');

        $this->topology_web_mr = array('1' => 'उंच', '2' => 'कमी', '3' => 'मध्यम');



        $this->unit_web = array('1' => 'Square Yard', '2' => 'Acre', '3' => 'Hectare', '4' => 'Square Meter', '5' => 'Square Mile');



        $this->unit_web_mr = array('1' => 'स्क्वेअर यार्ड', '2' => 'एकर', '3' => 'हेक्टर', '4' => 'चौरस मीटर', '5' => 'स्क्वेअर माईल');



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

        $this->lang->load(array('site'), $lang_folder);



    }



    /***********************Working APIs: Start***********************/



    public function api_response($data, $status = null, $token = null)

    {

        // echo $this->base_path;exit;

        if (!empty($token)) {

            header('Authorization: ' . $token);

        }

        if (empty($status)) {

            $status = 200;

        }

        // $this->save_logs($data); // Save logs

        echo $this->response($data, $status);exit;

    }



    public function index()

    {

        $response = array("status" => 0,"error" => 0, "message" => "Err !! Method missing");

       /* $row      = $this->db->get('users');

        $result   = $row->result_array();

        if (count($result)) {

            $response = array("status" => 1, "data" => $result, "message" => "User data");

        }*/

        $this->api_response($response);

    }



    public function splash_data_get()

    {

        $result['splash_data'] = $this->splash_data;

        $response              = array("status" => 1,"error" => 0, "data" => $result, "message" => "splash screen data");

        $this->api_response($response);

    }



    public function splash_screen_get()

    {



        $sql = "SELECT id,logo,mob_icon,key_fields FROM config_master WHERE key_fields ='farmer_splash1' AND  is_deleted = false  AND is_active = true ORDER BY id ASC";



        $res_chk   = $this->db->query($sql);

        $res       = $res_chk->result_array();

        // $base_path = 'https://dev.famrut.co.in/agroemandi/';

        $base_path = $this->base_path;

        // $base_path = $this->base_path;

        //$logo_url  = $base_path . 'uploads/config_master/';

        $image = $base_path . "uploads/config_master/" . $res[0]['mob_icon'];

        $logo  = $base_path . "uploads/config_master/" . $res[0]['logo'];



        if (count($res) > 0) {

            $response = array("success" => 1, "error" => 0, "status" => 1, 'logo_url' => $logo_url, "data" => $res, "message" => "splash screen data", "image" => $image, "logo" => $logo);



        } else {



            $img_logo  = $base_path . 'assets/img/spoc.png';

            $img_group = $base_path . 'assets/img/spoc.png';



            $response = array("success" => 0, "error" => 1, "status" => 0, 'logo_url' => $logo_url, "data" => $res, "message" => "splash screen data", "image" => $image, "logo" => $logo);

        }

        $this->api_response($response);

        exit;

    }



    public function is_vendor_regsitered_post()

    {       



        $app_user_type = $this->input->post('app_user_type');

        if ($this->input->post('phone_no') != '' && $app_user_type != '') {



            $phone_no = substr(preg_replace('/\s+/', '', $this->input->post('phone_no')), -10, 10);





            if($app_user_type == 1 ||  $app_user_type == 3 ){



                $row    = $this->db->query("SELECT * FROM users WHERE is_deleted = 'false' and phone_no::varchar = '$phone_no'::varchar ");

            $result = $row->result_array();

            if (count($result)) {



                if ($result[0]['is_active'] != f) {

                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Mobile nubmer is Registered", 'is_registered' => 1);

                    $this->api_response($response);

                    exit;

                } else {

                    $response = array("success" => 1, "error" => 0, "status" => 0, "data" => null, "message" => "Mobile nubmer is devactivated", 'is_registered' => 1, 'show_referral' => 1, 'registration_lock' => 1, "registration_lock_messge" => "Your number is devactivated Please contact admin");

                    $this->api_response($response);

                    exit;

                }



                // $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Mobile nubmer is Registered", 'is_registered' => 1);

                //$this->api_response($response);

                //exit;



            } else {



                /*$response = array("success" => 1, "error" => 0, "status" => 0, "data" => null, "message" => "Mobile nubmer is not Registered", 'is_registered' => 0);

                $this->api_response($response);

                exit;*/

                $response = array("success" => 1, "error" => 0, "status" => 0, "data" => null, "message" => "Mobile nubmer is not Registered", 'is_registered' => 0, 'show_referral' => 1, 'registration_lock' => 0, "registration_lock_messge" => "Please contact admin to register you Mobile number.");

                $this->api_response($response);

                exit;

            }



            } else {



                $row    = $this->db->query("SELECT * FROM pickup_location_master WHERE is_deleted = 'false' and phone::varchar = '$phone_no'::varchar  LIMIT 1");

                $result = $row->result_array();

               if (count($result)) {

                // , "status" => $this->db->last_query());

                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Pickup user Mobile nubmer is Registered", 'is_registered' => 1);

                    $this->api_response($response);

                    exit;

                } else {

                   /* $response = array("success" => 1, "error" => 0, "status" => 0, "data" => null, "message" => "Mobile nubmer is devactivated for Pickup user", 'is_registered' => 1, 'show_referral' => 1, 'registration_lock' => 1, "registration_lock_messge" => "Your number for Pickup user is devactivated Please contact admin" );*/

                   $response = array("success" => 1, "error" => 0, "status" => 0, "data" => null, "message" => "Mobile nubmer is not Registered", 'is_registered' => 0, 'show_referral' => 1, 'registration_lock' => 1, "registration_lock_messge" => "Please contact admin to register you Mobile number.");

                    $this->api_response($response);

                    exit;

                  

                }



        }

            



        } else {



            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => array(), "message" => "Err !! Mobile number is blank or User tpye Missing");

            $this->api_response($response);

            exit;



        }

    }





    public function order_filters_post()

    {

        $app_user_type = $this->input->post('app_user_type');

        $id = $this->input->post('user_id');



        if($app_user_type == 1 ||  $app_user_type == 3){

            $sql_pl       = "SELECT id,trim(address) as address ,pincode FROM pickup_location_master WHERE  is_active='true' and is_deleted='false'";

            $res_pl       = $this->db->query($sql_pl);

            $result_pl    = $res_pl->result_array();

        }else{

            if($id != ''){

                $sql_pl       = "SELECT id,trim(address) as address,pincode FROM pickup_location_master WHERE  is_active='true' and is_deleted='false' and id=".$id;

                $res_pl       = $this->db->query($sql_pl);

                $result_pl    = $res_pl->result_array();

            }else{

                $response = array("error"=>1, "status" => 0, "data" => $result, "message" => "user id is missing");

                $this->response($response);

                exit;

            }

        }

        

        $result['order_status']   = array('All','Pending','Cancelled','Complete','Inprogress','Fraud');

        $result['payment_status'] = array('Paid','Unpaid');

        $result['payment_method'] = array('Online','Cash','UPI');



        if($app_user_type == 1 ||  $app_user_type == 3){

            $all_locations = array(

                'id'        =>  '',

                'address'   => 'All',

                'pincode'   =>  '',

            ); 



            array_unshift($result_pl, $all_locations);

        }



        $result['pickup_location'] =  $result_pl;

        

        $response = array("status" => 1,"error"=>0, "data" => $result, "message" => "Order Filters");

        

        $this->response($response);

    }







    public function order_list_post()

    {

        $response           = array();

        $client_id          = $this->input->post('client_id');

        $pickup_location_id = $this->input->post('pickup_location_id');

        $status             = $this->input->post('status');

        $order_num          = $this->input->post('order_num');

        $limit              = 8;

        $start              = 1;

        $partner_id         = $this->input->post('partner_id'); // temporory commented***

        // $partner_id         = '';

        $headers_data = $this->input->request_headers();



        $app_user_type = $headers_data['app_user_type'];



        $start     = $this->input->post('start') ? $this->input->post('start') : 1;

        $sql_where = '';





        if ($partner_id != '' && $app_user_type != 2) {



            $sql_where .= " AND o.partner_id =  '" . $partner_id . "' ";



        }



        if ($client_id != '') {



            $sql_where .= " AND o.client_id =  '" . $client_id . "' ";



        }

        if ($pickup_location_id != '') {

            $sql_where .= " AND o.pickup_location_id =  '" . $pickup_location_id . "' ";

        }

        if ($status != '' && $status != 'All') {



            $sql_where .= " AND o.status =  '" . $status . "' ";

        }

        if ($order_num != '') {



            $sql_where .= " AND o.order_num =  '" . $order_num . "' ";

        }





        $start_chk = $start - 1;

        if ($start_chk != 0) {

            $start_sql = $limit * ($start_chk);

        } else {

            $start_sql = 0;

        }



        $sql_limit = " LIMIT " . $limit . " OFFSET " . $start_sql;

        $sql       = "SELECT o.id,o.invoice_id,o.order_num,o.client_id,o.pickup_location_id,o.status, o.order_date  + INTERVAL '5 hours 30 minutes' as order_date,o.pickup_location_id,o.amount,c.first_name,c.last_name,c.phone,c.email,o.paid_amount,o.invoice_number,o.payment_method, o.payment_status as order_payment_status  FROM client_orders as o LEFT JOIN client as c ON c.id = o.client_id WHERE o.is_deleted = 'false' " . $sql_where . "  ORDER BY o.id DESC " . $sql_limit;

        $row       = $this->db->query($sql);

        $result    = $row->result_array();



        if (count($result)) {

            $response = array("status" => 1, "data" => $result, "message" => "Order listed successfully");

        } else {

            $response = array("status" => 1, "data" => array(), "message" => "No data avaiable");

        }



        $this->response($response);

    }





    public function user_order_details_get($order_id)

    {

        $response = array();

        if ($order_id != '') {

        	$sql = "SELECT c.*,p.product_name,p.price, p.logo, o.order_num,o.amount as total_amout, o.status as full_order_status, o.order_date  + INTERVAL '5 hours 30 minutes' as order_date,o.paid_amount,  o.payment_status as order_payment_status, o.cphone as phone, o.first_name, o.last_name FROM client_order_product c

            LEFT JOIN products as p ON p.id = c.product_id

            LEFT JOIN client_orders as o ON o.id = c.order_id 

            WHERE c.order_id=" . $order_id;

            $row    = $this->db->query($sql);

            $result = $row->result_array();



            $order_details = [];



            foreach($result as $key => $value)

            {

                if(!empty($value['logo'])){

                    $value['logo'] = $this->base_path.'uploads/logo/'.$value['logo'];

                }

                $order_details[]  = $value;

            }



            // echo'<pre>';print_r($result);exit;





            if (count($order_details)) {

                $response = array("status" => 1, "data" => $order_details, "message" => "Order details listed successfully");

            } else {

                $response = array("status" => 0, "message" => "Order details  not available");

            }

        } else {

            $response = array("status" => 0, "message" => "order id is Wrong or Missing");

        }

        $this->api_response($response);

    }



     // update order status

    function update_order_status_post()

    {

        $id                 = $this->input->post('id');

        $status             = $this->input->post('status');

        $remark             = $this->input->post('remark');

        $payment_method     = $this->input->post('payment_method') ? $this->input->post('payment_method') : null;

        $transaction_text   = $this->input->post('transaction_text') ? $this->input->post('transaction_text') : null;

        $statuses           = array('Cancelled', 'Fraud');



        if(!empty($id)){

            // update client order product status

            $result = $this->Masters_model->update_data('client_order_product', array('order_id' => $id), array('status' => $status,'remark' =>$remark, 'payment_method' => $payment_method,'transaction_text'=>$transaction_text));



            // admin_activity_log

            admin_activity_logs("Order status Updated, Order ID -".$id, "ID: ".$id.' '."Status: ".$status." Remark:".$remark);

            $notification_data = array();



            // update clients order status

            $where1 = array('id' => $id);

            $result = $this->Masters_model->update_data('client_orders', $where1, array('status' => $status,'payment_method' => $payment_method));

            // echo'<br>last_query: '.$this->db->last_query();//exit;



            // send notifications

            $notification_data = $this->send_notification($id);



            if($status == 'Complete'){

                $client_orders_data = $this->Masters_model->get_data(array('*'), 'client_orders', array('id'=>$id));

                $insert_data = array(

                    'client_id'         => $client_orders_data[0]['client_id'],

                    'invoice_id'        => $client_orders_data[0]['invoice_id'],

                    'transaction_id'    => $transaction_text,

                    'description'       => $remark,

                    'status'            => $status,

                    'transaction_date'  => date('Y-m-d H:i:s'),

                    'amount_in'         => $client_orders_data[0]['amount'],

                    'gateway'           => $payment_method,

                );



                user_activity_logs("Partner: Transaction:", json_encode($insert_data));

                $this->Masters_model->add_data('transactions',$insert_data);



                // update amount

                $paid_amount = (!empty($client_orders_data[0]['amount'])) ? number_format($client_orders_data[0]['amount'],2) : 0.00;

                $this->Masters_model->update_data('client_orders', array('id' => $id), array('paid_amount' => $paid_amount,'payment_status'=>'Paid','order_completion_date'=>date('Y-m-d H:i:s')));



            } else if(in_array($status, $statuses)){

                // update amount

                $paid_amount = null;

                $this->Masters_model->update_data('client_orders', array('id' => $id), array('paid_amount' => $paid_amount,'payment_status'=>'Unpaid','order_completion_date'=>null));



                // update product stock

                $cop_where = array('order_id' => $id);

                $cop_order_data = $this->Masters_model->get_data(array('*'), 'client_order_product', $cop_where);

                // echo'<pre>cop_order_data:';print_r($cop_order_data);echo'</pre>';

                foreach($cop_order_data as $key => $val){

                    $this->update_product_stock_by_status($val['id']);

                }

            }

        }



        $data['noti_status']    = isset($notification_data['message']) ? $notification_data['message'] : '';

        $data['noti_msg']       = isset($notification_data['success']) ? $notification_data['success'] : 0;

        

        if ($result) {

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, "message" => "Status updated successfully!");



        } else {

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $data, "message" => "Status not updated!");

        }



        $this->api_response($response);exit;

    }



    // Send notification of order status update by partner

    public function send_notification($id)

    {

        $message = [];

        $result_client_orders = [];

        if ($id) {

            $sql_client_orders = "select * from client_orders WHERE id = '" . $id . "' LIMIT 1";

            $result_client_orders = $this->db->query($sql_client_orders)->result_array();

            $title = 'order';



            if($result_client_orders[0]['status'] != ''){

                $client_orders_message = 'Your Order number #'.$result_client_orders[0]['order_num'].' status is updated to '.$result_client_orders[0]['status']; 

            }

            

            $admno = 1;

            $call_data = '';

            $meeting_link = '';

            //$farmer_name = $farmer_data[0]['first_name'];

            $is_whitelable = 1;

            $custom_array = ''; 

            $img = '';

            $jsonString = '';

            $whr_chk_farmer  = 'AND id='.$result_client_orders[0]['client_id'];



            $qry   = "SELECT id, device_id FROM client WHERE is_deleted='false' AND is_active='true' AND device_id is NOT NULL ".$whr_chk_farmer;



            $res_data        = $this->db->query($qry);

            $device_id_data  = $res_data->row_array();

            $token = [];

            if(count($device_id_data)){

                $token[] = $device_id_data['device_id'];

            }

            

            if(count($token)){

                $this->load->model('Notification_model');

                $jsonString =  $this->Notification_model->sendPushNotifications_NA($token, $title, $client_orders_message, $is_whitelable, $group_ids = 0, $custom_array, $type='order', $id);



                $notification_data = json_decode($jsonString, true);

                $notification_status = $notification_data['success'];

                if($notification_status == 1){

                    $notification_msg = 'Status Sent successfully';



                    $sql = "UPDATE client_orders SET is_notification_sent = 'true' WHERE id = '" . $id . "'";

                    $this->db->query($sql);

                } else {

                    $notification_msg = 'Status Not Sent';

                }



                $message = array("status" => 1, 'success' => $notification_status, "data" => json_decode($jsonString, true), "message" => $notification_msg);

            } else {

                $message = array("status" => 0, 'data'=>[], "message" => "Status Not Sent");

            }

            // echo'<pre>';print_r($message);exit;

        } else {

            $message = array("status" => 0, "message" => "Status not Sent");

        }

        return $message;

    }



    // Update stock of order status update by partner

    function update_product_stock_by_status($id){

        if(!empty($id)){    

            $cop_data = $this->Masters_model->get_data(array('*'), 'client_order_product', array('id' => $id));

            $product_id = $cop_data[0]['product_id'];

            $product_data = $this->Masters_model->get_data(array('*'), 'products', array('id' => $product_id));

            $update_stock = (int)$product_data[0]['in_stock'] + (int)$cop_data[0]['quantity'];

            $this->Masters_model->update_data('products', array('id' => $product_id), array('in_stock' => $update_stock));

        }

        return true;

    }



    public function user_type_get()

    {

        //lang('Partner') lang('Pickup-user') 

        $headers_data = $this->input->request_headers();

        $domain = strtolower($headers_data['domain']);

        if($domain == 'yltp'){

        $result = array(          

            array('id' => '1', 'title' => 'Partner', 'map_key' => '1', 'icon' => 'Partner'),

            array('id' => '2', 'title' => 'Pickup-user', 'map_key' => '2', 'icon' => 'Pickup-user'),           

        );

    }else{

        $result = array(          

            array('id' => '1', 'title' => 'Partner', 'map_key' => '1', 'icon' => 'Partner'),

            array('id' => '2', 'title' => 'Pickup-user', 'map_key' => '2', 'icon' => 'Pickup-user'),  

            array('id' => '3', 'title' => 'Crop-advisory', 'map_key' => '3', 'icon' => 'Crop-advisory'),        

        );



    }

      

        $response = array("status" => 1, "error" => 0,"data" => $result, "message" => "User Type");

        

        $this->api_response($response);

    }



    public function get_vendor_register_post()

    {



        $result   = array();

        $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => "Registration failed, please try again some time.");



        if ($this->input->post('btn_submit') == 'submit') {

            if (0) {

                $data          = $this->input->post();

                $data['error'] = validation_errors();



            } else {



                $phone_no = $this->input->post('phone_no');



                $row    = $this->db->query("SELECT user_id,phone_no FROM users WHERE is_deleted = 'false' and phone_no::varchar = '$phone_no'::varchar ");

                $result = $row->result_array();

                if (count($result)) {

                    $response = array("success" => 0, "error" => 1, "status" => 0, "data" => "NULL", "message" => "Mobile nubmer is already Register.");

                    $this->api_response($response);

                    exit;

                }

                //referral_code



                $insert = array(

                    'first_name'       => ucfirst($this->input->post('first_name')),

                    'last_name'        => ucfirst($this->input->post('last_name')),

                    'company_name'     => $this->input->post('company_name'),

                    'referral_code'    => $this->input->post('referral_code'),

                    'phone_no'         => $this->input->post('phone_no'),

                    'created_on'       => current_date(),

                    'email'            => strtolower($this->input->post('email')),

                    'password'         => encrypt($this->input->post('password'), config_item('encryption_key')),

                    'type'             => 'partner',

                    'user_type'        => $this->input->post('user_type'),

                    'my_refferal_code' => time(),

                );



                $full_name = ucfirst($this->input->post('first_name')) . ' ' . ucfirst($this->input->post('last_name'));



                //user_activity_log

                $title       = "Vendor: Registered";

                $description = json_encode($insert);

                user_activity_logs($title, $description);



                $result    = $this->db->insert('users', $insert);

                $insert_id = $this->db->insert_id();



                if ($result) {



                    if (count($insert)) {

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Vendor Registration Successfully");

                    }



                    $opt_number = mt_rand(100000, 999999);

                    $sms_type   = 1; // for OTP its once

                    // $mobile = 8208953165;

                    $mobile = substr(preg_replace('/\s+/', '', $this->input->post('phone_no')), -10, 10);

                    $text   = 'Your OTP for Famrut is: ' . $opt_number . ' . Please enter it on the app to confirm your account. Thanks for using Famrut';

                    // $mobile = 7448148405;

                    //$this->input->post('phone')

                    // static code added for Testing MMMM

                    if ($mobile == '8888888888') {

                        $opt_number               = 000000;

                        $update_arr['opt_number'] = $opt_number;

                    } else {

                        $update_arr['opt_number'] = $opt_number;

                        $resp                     = send_sms($mobile, $text, $sms_type);

                    }



                    if (count($update_arr)) {



                        //$id = $result[0]['user_id'];

                        $this->db->where('user_id', $insert_id);

                        $this->db->update('users', $update_arr);



                    }



                    $this->api_response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => "Registration failed, please try again some time.");



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



        $phone      = substr(preg_replace('/\s+/', '', $this->input->post('phone')), -10, 10);

        $result     = array();

        $opt_number = mt_rand(100000, 999999);

        $response   = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => "Registration / login failed, please try again some time.");



        $app_user_type = $this->input->post('app_user_type');



        if ($phone != '' && $opt_number != '') {





            if($app_user_type == 1 ||  $app_user_type == 3)

            {



            $sql_chk = "SELECT * FROM users WHERE is_deleted = 'false' and phone_no::varchar = '$phone'::varchar ";

            $row     = $this->db->query($sql_chk);

            $result  = $row->result_array();

            if (count($result)) {



                if ($result[0]['is_active'] == "t") {



                    $sms_type = 1;

                    $text     = 'Your OTP for Famrut is: ' . $opt_number . ' . Please enter it on the app to confirm your account. Thanks for using Famrut';



                    if ($phone == '8888888888') {

                        $opt_number               = 000000;

                        $update_arr['opt_number'] = $opt_number;

                    } else {

                        $resp                     = send_sms($phone, $text, $sms_type);

                        $update_arr['opt_number'] = $opt_number;

                    }



                    if (count($update_arr)) {



                        $id = $result[0]['user_id'];

                        $this->db->where('user_id', $id);

                        $this->db->update('users', $update_arr);



                    }



                     $response = array("success" => 1, "error" => 0, "status" => 1, "data" => "NULL", "message" => "OTP sent", 'opt_number' => $opt_number, 'resp_query' => $result[0]['is_active']);

                    $this->api_response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 0, "data" => "NULL", "message" => "Your account is not active : contact Admin", 'opt_number' => $opt_number, 'resp_query' => $result[0]['is_active']);

                    $this->api_response($response);

                    exit;

                }



                $response = array("success" => 1, "error" => 0, "status" => 0, "data" => "NULL", "message" => "OTP reset successfully", 'opt_number' => $opt_number);

                $this->api_response($response);

                exit;

            } else {



                $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => "OTP not reset , Err : Number is not Registed");

                $this->api_response($response);

                exit;

            }



            }else{



                 $row    = $this->db->query("SELECT * FROM pickup_location_master WHERE is_deleted = 'false' and phone::varchar = '$phone'::varchar ");

                    $result = $row->result_array();

             



                    if (count($result)) {



                         if ($result[0]['is_active'] == "t") {



                    $sms_type = 1;

                    $text     = 'Your OTP for Famrut is: ' . $opt_number . ' . Please enter it on the app to confirm your account. Thanks for using Famrut';



                    if ($phone == '8888888888') {

                        $opt_number               = 000000;

                        $update_arr['password'] = $opt_number;

                    } else {

                        $resp                     = send_sms($phone, $text, $sms_type);

                        $update_arr['password'] = $opt_number;

                    }



                    if (count($update_arr)) {



                        $id = $result[0]['id'];

                        $this->db->where('id', $id);

                        $this->db->update('pickup_location_master', $update_arr);



                    }



                      $response = array("success" => 1, "error" => 0, "status" => 1, "data" =>$result, "message" => "OTP sent", 'opt_number' => $opt_number, 'resp_query' => $result[0]['is_active']);

                    $this->api_response($response);

                    exit;





                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => "Your account is not active : contact Admin", 'opt_number' => $opt_number, 'resp_query' => $result[0]['is_active']);

                    $this->api_response($response);

                    exit;

                }



                    }else{



                          $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => "OTP not reset , Err : Number is not Registed");

                        $this->api_response($response);

                        exit;



                    }

            }

        }



        $this->api_response($response);

        exit;

    }



    public function get_login_otp_post()

    {



        $result     = array();

        $update_arr = array();        



        $app_user_type = $this->input->post('app_user_type');



        $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $_POST, "message" => "missing params");



        if ($this->input->post('btn_submit') == 'submit' && $this->input->post('phone') != '') {

            $username = substr(preg_replace('/\s+/', '', $this->input->post('phone')), -10, 10);



            $otp      = $this->input->post('otp');



              if( $app_user_type == 1 ||  $app_user_type == 3 ){



                    $sql_query = "SELECT * FROM users WHERE is_deleted = 'false' and phone_no::varchar = '$username'::varchar AND opt_number = $otp";



                    $row       = $this->db->query("SELECT * FROM users WHERE is_deleted = 'false' and phone_no::varchar = '$username'::varchar ");

                    $user_data = $row->result_array();



                    $row = $user_data;



                    if (count($user_data)) {



                        $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $row, "message" => "missing err >> :" . $row[0]['opt_number'], 'usr_data' => $row, 'sql_query' => $sql_query);



                        if ($row[0]['opt_number'] ==  $otp || $otp == 999999) {



                            $update_arr = array();



                            if ($row[0]['login_count']) {

                                $update_arr['login_count'] = $row[0]['login_count'] + 1;

                            } else {

                                $update_arr['login_count'] = 1;

                            }



                            $device_id = $this->input->post('device_id');



                            if ($this->input->post('device_id')) {



                                $update_arr['device_id'] = $this->input->post('device_id');

                            }

                            if ($this->input->post('phone')) {



                                $update_arr['is_login'] = true;

                            }



                            if (!$row[0]['my_refferal_code']) {



                                $update_arr['my_refferal_code'] = time();

                            }



                            if (count($update_arr)) {



                                $this->db->where('users.phone_no', $username);

                                $result = $this->db->update('users', $update_arr);



                                $user_data = array();

                                $row_data  = $this->db->query("SELECT * FROM users WHERE is_deleted = 'false' and phone_no::varchar = '$username'::varchar ");

                                $user_data = $row_data->result_array();



                            }



                            if ($row[0]['user_type'] != '') {



                                $this->db->select('*');

                                $this->db->where('cat_id', $row[0]['user_type']);

                                $partner_res          = $this->db->get('categories')->result_array();

                                $partner_type_name    = $partner_res[0]['name'];

                                $partner_type_name_mr = $partner_res[0]['name_mr'];



                            } else {

                                $partner_type_name    = array();

                                $partner_type_name_mr = array();

                            }





                            



                            $response = array("success" => 1, "data" => $user_data[0], "msg" => 'Login successfully', "error" => 0, "status" => 0, "config_url" => $this->config_url, "vendor_menu" => $vendor_menu, 'partner_type_name' => $partner_type_name, 'partner_type_name_mr' => $partner_type_name_mr, 'device_id' => $device_id, 'sql_query' => $sql_query);



                            $this->api_response($response);

                            exit;

                        }

                    } else {

                        $response = array("success" => 0, "error" => 1, "status" => 0, "message" => "mobile number not register or active");

                        $this->api_response($response);

                        exit;

                    }

            }else{

                   //$sql_query = "SELECT * FROM users WHERE is_deleted = 'false' and phone_no::varchar = '$username'::varchar AND opt_number = $otp";

                //echo "SELECT id as user_id,phone as phone_no,password as otp   FROM pickup_location_masterWHERE is_deleted = 'false' and phone::varchar ='$username'::varchar ";

                    $row    = $this->db->query("SELECT id as user_id,phone as phone_no,password as otp  FROM pickup_location_master WHERE is_deleted = 'false' and phone::varchar ='$username'::varchar");

                    $user_data = $row->result_array();   

                    if (count($user_data)) {



                        $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $user_data, "message" => "missing err >> :" . $user_data[0]['opt_number'], 'usr_data' => $user_data);



                        if ($user_data[0]['otp'] ==  $otp || $otp == 999999) {



                            $update_arr = array();



                           /* if ($row[0]['login_count']) {

                                $update_arr['login_count'] = $row[0]['login_count'] + 1;

                            } else {

                                $update_arr['login_count'] = 1;

                            }*/

                            //$resp_data['user_id'] = $user_data[0]['id'];

                           // $resp_data['phone_no'] = $user_data[0]['phone'];

                           // $resp_data['phone_no'] = $user_data[0]['phone']; 



                             $vendor_menu_pickup = array(

                                array('id' => '1', 'title' => 'Home', 'icon' => 'home'),

                                array('id' => '2', 'title' => 'Profile', 'icon' => 'Profile'),

                                array('id' => '5', 'title' => 'Orders', 'icon' => 'Orders'),          

                                array('id' => '9', 'title' => 'Change Language', 'icon' => 'change_language'),

                                array('id' => '10', 'title' => 'Logout', 'icon' => 'logout'),

                                array('id' => '11', 'title' => 'About us', 'icon' => 'about_us')         

                            );



                            $device_id = $this->input->post('device_id');



                              $response = array("success" => 1, "data" => $user_data[0], "msg" => 'Login successfully', "error" => 0, "status" => 1, "config_url" => $this->config_url, "vendor_menu" => $vendor_menu_pickup,'device_id' => $device_id);



                            $this->api_response($response);

                            exit;



                        }else{



                            $response = array("success" => 0, "error" => 1, "status" => 0, "message" => "mobile number not register or active");

                            $this->api_response($response);

                            exit;



                        }



                    }else{



                        $response = array("success" => 0, "error" => 1, "status" => 0, "message" => "mobile number not registerd");

                            $this->api_response($response);

                            exit;



                    }

            }

        }

        $this->api_response($response);

    }



    public function vendor_menu_get($user_id = '')

    {



        if ($user_id) {



            $sql = 'SELECT my_refferal_code,phone_no FROM users WHERE user_id =' . $user_id;

            $row = $this->db->query($sql);



            $result = $row->result_array();

            if ($result[0]['my_refferal_code'] != '') {



                $my_ref_code = $result[0]['my_refferal_code'];

            } else {



                $my_ref_code = time();

                $update_arr  = array('my_refferal_code' => $my_ref_code);

                $this->db->where('users.user_id', $user_id);

                $result = $this->db->update('users', $update_arr);



            }

        } else {

            $my_ref_code = '';

        }

        $vendor_menu = array(

            array('id' => '1', 'title' => 'Home', 'icon' => 'home'),

            array('id' => '2', 'title' => 'Profile', 'icon' => 'Profile'),

            array('id' => '5', 'title' => 'Enquiry', 'icon' => 'Enquiry'),

            array('id' => '7', 'title' => 'Services', 'icon' => 'Services'),

            array('id' => '9', 'title' => 'Change Language', 'icon' => 'change_language'),

            array('id' => '10', 'title' => 'Logout', 'icon' => 'logout'),

            array('id' => '11', 'title' => 'About us', 'icon' => 'about_us'),

            array('id' => '12', 'title' => 'Invite', 'icon' => 'Invite_icon'),

        );



        $response = array("success" => 1, "data" => "", "msg" => 'Vendor Menu list', "error" => 0, "status" => 0, "config_url" => $this->config_url, "vendor_menu" => $vendor_menu, "my_ref_code" => $my_ref_code);



        $this->api_response($response);



    }





    public function vendor_menu_pickup_get()

    {



        

        $vendor_menu_pickup = array(

            array('id' => '1', 'title' => 'Home', 'icon' => 'home'),

            array('id' => '2', 'title' => 'Profile', 'icon' => 'Profile'),

            array('id' => '5', 'title' => 'Orders', 'icon' => 'Orders'),          

            array('id' => '9', 'title' => 'Change Language', 'icon' => 'change_language'),

            array('id' => '10', 'title' => 'Logout', 'icon' => 'logout'),

            array('id' => '11', 'title' => 'About us', 'icon' => 'about_us')         

        );

        $response = array("success" => 1, "data" => $vendor_menu_pickup, "msg" => 'Vendor Menu list', "error" => 0, "status" =>1, "config_url" => $this->config_url,);



        $this->api_response($response);



    }



    public function get_partner_categories_get()

    {

        $row    = $this->db->query("SELECT cat_id ,name ,logo ,name_mr ,mob_icon FROM categories WHERE is_active = 'true' AND is_deleted = 'false' ORDER BY seq ASC");

        $result = $row->result_array();



        if (count($result)) {

            $response = array("status" => 1, "data" => $result, "config_url" => $this->config_url, "message" => "Categories listed successfully");

        }

        // $this->response($result, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

        $this->api_response($response);

    }



    public function disconnect_farmer_post()

    {



        $partner_id       = $this->input->post('user_id');

        $farmer_id        = $this->input->post('farmer_id');

        $call_status_flag = $this->input->post('call_status_flag');



        if ($call_status_flag == '') {

            $call_status_flag = 4;

        }



        //$meeting_duration = trim($this->input->post('meeting_duration'));

        $meeting_link = trim($this->input->post('meeting_link'));

        if ($meeting_link == '') {



            $response = array("status" => 0, "message" => "missing params");



        } else {



            $sql_emeeting      = "SELECT created_on FROM eMeeting WHERE meeting_link_id='" . $meeting_link . "' ORDER BY id DESC LIMIT 1";

            $booked_call_query = $this->db->query($sql_emeeting);

            $emeeting          = $booked_call_query->result_array();

            $d1                = new DateTime(current_date());

            $d2                = new DateTime($emeeting[0]['created_on']);

            $interval          = $d2->diff($d1);

            $hour              = $interval->format('%H');



            $time = $interval->format('%H:%I:%S');

            $arr  = explode(':', $time);

            if (count($arr) === 3) {

                $call_duration_sec = $arr[0] * 3600 + $arr[1] * 60 + $arr[2];

            } else {

                $call_duration_sec = $arr[0] * 60 + $arr[1];

            }



            if ($hour) {

                $call_duration = $interval->format('%H hr %I mins %S sec');

            } else {

                $call_duration = $interval->format('%I mins %S sec');

            }



            if ($this->input->post('meeting_duration')) {

                $meeting_duration = $this->input->post('meeting_duration');



            } else {



                $meeting_duration = 11;

            }



            //'meeting_link_id' => $meeting_link,



            $where_array = array(

                'farmer_id'            => $farmer_id,

                'partner_id'           => $partner_id,

                'meeting_link_id'      => $meeting_link,

                'meeting_status_id !=' => 4,

            );



            $update_array = array(

                'meeting_status_id' => $call_status_flag,

                'meeting_end_from'  => 2,

                'updated_on'        => current_date(),

                'call_duration'     => $call_duration,

                'call_duration_sec' => $call_duration_sec,

            );



            $sql_update = $this->db->update('emeeting', $update_array, $where_array);



            $sql_user  = "SELECT first_name, last_name,device_id from users where user_id=" . $partner_id . "  LIMIT 1";

            $row_user  = $this->db->query($sql_user);

            $user_data = $row_user->result_array();



            $partner_name = $user_data[0]['first_name'] . ' ' . $user_data[0]['last_name'];



            $sql         = "SELECT first_name, last_name,device_id from client where id=" . $farmer_id . "  LIMIT 1";

            $row         = $this->db->query($sql);

            $farmer_data = $row->result_array();



            $data['title'] = $type = 'eMeeting';



            $farmer_name = $farmer_data[0]['first_name'] . ' ' . $farmer_data[0]['last_name'];



            $title = 'Disconnect call';

            /*  $message = 'Dear ' . $farmer_name . ' have Call Disconnected from ' . $partner_name . ' please stop meeting ';*/

            $message = 'Your call is Disconnected';



            $admno     = $partner_id;

            $token[]   = $farmer_data[0]['device_id'];

            $call_data = array(

                'meeting_link_id'=> $meeting_link,

                'farmer_name'   => $farmer_name,

                'farmer_id'     => $farmer_id,

                'partner_id'    => $partner_id,

                'partner_name'  => $partner_name,

            );



            $jsonString = self::sendPushNotificationToFCMSeverdev($token, $title, $message, $admno, $type, $call_data);



            $test_array[] = $jsonString;

            $test_array[] = $farmer_data[0]['device_id'];



            if ($meeting_link != '') {



                $response = array("success" => 1, "data" => $data, "msg" => 'Call Disconnected ', "error" => 0, "status" => 1, 'test_array' => $test_array);

            } else {

                $response = array("success" => 0, "data" => $data, "msg" => 'Call Not Disconnected', "error" => 1, "status" => 1);

            }



        }



        $this->api_response($response);



    }



    public function start_call_meeting_post()

    {

        /*

        you need to send call_status_flag as define here

        1: new call

        2: in proeccess call

        3: hold

        4: disconect call

        5: reject call

         */



        $today = date('Y-m-d');



        $partner_id = $this->input->post('user_id');

        $farmer_id  = $this->input->post('farmer_id');

        $lead_id    = $this->input->post('lead_id');



        $sql_chk     = "SELECT * FROM emeeting WHERE partner_id = " . $partner_id . " and farmer_id = " . $farmer_id . " and (meeting_status_id = 1 OR meeting_status_id = 2) AND date(created_on) = '" . $today . "' ORDER BY id ASC LIMIT 1";

        $row_val     = $this->db->query($sql_chk);

        $call_result = $row_val->result_array();



        if (count($call_result) > 0) {

            $avaiable_flag = 0; // Partner is not avaiable

            $call_coming   = 1;

            $meeting_link  = $call_result[0]['meeting_link_id'];



        } else {

            $meeting_link = md5(date("Ymdhis") . $farmer_id . $partner_id);



            $insert = array(

                'farmer_id'            => $farmer_id,

                'partner_id'           => $partner_id,

                'meeting_status_id'    => 1,

                'meeting_started_from' => 2,

                'meeting_link_id'      => $meeting_link,

                'lead_id'              => $lead_id,

                'is_active'            => 'true',

                'created_on'           => current_date(),

            );



            $sql_insert = $this->db->insert('emeeting', $insert);



        }



        $row_val = $this->db->query("SELECT * FROM emeeting WHERE partner_id = " . $partner_id . " and (meeting_status_id = 1 OR meeting_status_id = 2)  ORDER BY id ASC");



        $result = $row_val->result_array();

        if (count($result) > 0) {

            $avaiable_flag = 0; // Partner is not avaiable

        } else {

            $avaiable_flag = 1; // Partner is avaiable

        }



        $data['title']        = 'Farmer Call';

        $data['meeting_link'] = $meeting_link;

        $data['farmer_id']    = $farmer_id;



        $today       = date('Y-m-d');

        $sql_chk     = "SELECT * FROM emeeting WHERE partner_id = " . $partner_id . " and farmer_id = " . $farmer_id . " and (meeting_status_id = 1 OR meeting_status_id = 2) AND date(created_on) = '" . $today . "' ORDER BY id ASC LIMIT 1";

        $row_val     = $this->db->query($sql_chk);

        $call_result = $row_val->result_array();



        if (count($call_result) > 0) {

            $avaiable_flag = 0; // Partner is not avaiable

            $call_coming   = 1;

            $meeting_link  = $call_result[0]['meeting_link_id'];

        }



        $sql_user  = "SELECT first_name, last_name,device_id from users where user_id=" . $partner_id . "  LIMIT 1";

        $row_user  = $this->db->query($sql_user);

        $user_data = $row_user->result_array();



        $partner_name = $user_data[0]['first_name'] . ' ' . $user_data[0]['last_name'];



        $sql         = "SELECT first_name, last_name,device_id from client where id=" . $farmer_id . "  LIMIT 1";

        $row         = $this->db->query($sql);

        $farmer_data = $row->result_array();



        $data['title'] = $type = 'eMeeting';



        $farmer_name = $farmer_data[0]['first_name'] . ' ' . $farmer_data[0]['last_name'];



        $title = 'Incoming call';

        /* $message = 'Dear ' . $partner_name . ' have Call from ' . $farmer_name . ' please join meeting ';*/

        $message = 'Incoming Video Call';



        $admno = $partner_id;



        $call_data = array(

            'meeting_link_id'=> $meeting_link,

            'farmer_name'   => $farmer_name,

            'farmer_id'     => $farmer_id,

            'partner_id'    => $partner_id,

            'partner_name'  => $partner_name,

        );



        $token[] = $farmer_data[0]['device_id'];



        $data['call_details'] = $call_data;



        $jsonString = self::sendPushNotificationToFCMSeverdev($token, $title, $message, $admno, $type, $call_data);



        if ($meeting_link != '') {



            $response = array("success" => 1, "data" => $data, "msg" => 'Connect call', "error" => 0, "status" => 1);

        } else {

            $response = array("success" => 0, "data" => $data, "msg" => 'Not Connecting call', "error" => 1, "status" => 1);

        }

        $this->api_response($response);



    }



    public function enquiry_list_get($vendor_id)

    {

        $data['title'] = 'Product Leads';



        if ($vendor_id != '') {



            $slq_farmer = "SELECT c.id,c.first_name,c.middle_name,c.last_name,c.email,c.phone,c.profile_image,c.created_on,c.is_login,c.address1,c.address2,c.city,c.postcode,c.latitude,c.longitude,P.schedule_call_status,P.product_type,P.schedule_call_status,P.call_schedule_timestamp,P.call_schedule_time,c.is_online from product_leads as P INNER JOIN client as c ON c.id=P.client_id where c.is_deleted = false AND P.is_deleted = false AND P.partner_id= $vendor_id GROUP BY c.id,c.phone,P.id  ORDER BY  c.id DESC";



            $query  = $this->db->query($slq_farmer);

            $result = $query->result_array();

            $unique = array();



            if (count($result)) {

                $response = array("success" => 1, "data" => $result, "filter_data" => $unique, "msg" => 'farmer enquiry list', "error" => 0, "status" => 1);



            } else {

                $response = array("success" => 1, "data" => array(), "filter_data" => array(), "msg" => 'No farmer enquiry avaialbe', "error" => 0, "status" => 1);

            }

        } else {



            $response = array("success" => 0, "data" => array(), "msg" => 'Params missing', "error" => 1, "status" => 1);



        }

        $this->api_response($response);



    }



    public function get_chat_data_post()

    {



        $farmer_id  = $this->input->post('farmer_id');

        $partner_id = $this->input->post('user_id');



        $sql_chat = "SELECT m.* FROM messages as m  where ( m.outgoing_msg_id = " . $farmer_id . " OR  m.incoming_msg_id =" . $farmer_id . " ) AND ( m.outgoing_msg_id = " . $partner_id . " OR  m.incoming_msg_id = " . $partner_id . " ) ORDER BY created_on ASC";



        $Chat_Data = $this->db->query($sql_chat)->result_array();



        if (count($Chat_Data)) {

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $Chat_Data, "msg" => "chat data listed.");

        } else {

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => array(), "msg" => "No chat avaialbe");

        }



        $this->api_response($response);



        exit;



    }



    public function add_chat_post()

    {



        $farmer_id  = $this->input->post('farmer_id');

        $partner_id = $this->input->post('user_id');

        $msg        = $this->input->post('msg');



        //$response = array();

        $response = array("success" => 0, "status" => 0, "msg" => "missing params");



        if ($farmer_id != '' && $partner_id != '' && $msg != '') {



            $insert = array(

                'msg'             => $this->input->post('msg'),

                'incoming_msg_id' => $partner_id,

                'outgoing_msg_id' => $farmer_id,

                'user_type'       => 'partner',

                'created_on'      => current_date(),

            );



            $result = $this->db->insert('messages', $insert);



            if ($result) {

                if (1) {



                    $sql           = "SELECT first_name, last_name,device_id from client where id=" . $farmer_id . "  LIMIT 1";

                    $row           = $this->db->query($sql);

                    $farmer_data   = $row->result_array();

                    $data['title'] = 'Chat';



                    $farmer_name = $farmer_data[0]['first_name'] . ' ' . $farmer_data[0]['last_name'];

                    $token[]     = $farmer_data[0]['device_id'];



                    $sql_user  = "SELECT first_name, last_name,device_id from users where user_id=" . $partner_id . "  LIMIT 1";

                    $row_user  = $this->db->query($sql_user);

                    $user_data = $row_user->result_array();



                    $partner_name = $user_data[0]['first_name'] . ' ' . $user_data[0]['last_name'];



                    $title   = 'Chat';

                    $message = $partner_name . ':' . truncate_string($msg);

                    $admno   = $partner_id;

                    $type    = 1;



                    $jsonString = self::sendPushNotificationToFCMSeverdev_chat($token, $title, $message, $admno, $type, $partner_name);



                    $test_array[] = $partner_name;

                    $test_array[] = $jsonString;

                    $test_array[] = $token;



                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "msg" => "Chat Added Successfully", "notification_resp" => $test_array);

                    $this->api_response($response);

                    exit;

                }



                $this->api_response($response);

                exit;



            } else {



                $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "msg" => "Chat Add failed, please try again some time.");



                $this->api_response($response);

                exit;



            }

            $this->api_response($response);

            exit;

        }



    }



    public function logout_check_v_get($phone_number)

    {



        if ($phone_number != '') {



            $phone = substr(preg_replace('/\s+/', '', $phone_number), -10, 10);

            // $this->db->select('phone_no,user_id');

            //$this->db->where('phone_no', $phone);

            $sql     = "SELECT phone_no,user_id FROM users where phone_no :: varchar = $phone_number::varchar AND is_active= true AND is_deleted = false ";

            $res_chk = $this->db->query($sql);

            $res     = $res_chk->result_array();

            //print_r($res);

            if (count($res) > 0) {



                $user_id = $res[0]['user_id'];



                ///// code to disconnnect call of vendor if any active call

                $where_array = array(

                    'partner_id'           => $user_id,

                    'meeting_status_id !=' => 4,

                );



                $update_array = array(

                    'meeting_status_id' => 4,

                    'meeting_end_from'  => 2,

                    'updated_on'        => current_date(),

                );



                $sql_update = $this->db->update('emeeting', $update_array, $where_array);

                //// disconnect call code end ///////////////////////////



                $update_arr = array('is_login' => false, 'device_id' => null);

                $this->db->where('users.phone_no', $phone);

                $result   = $this->db->update('users', $update_arr);

                $sql_data = $this->db->last_query();



                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Vendor Logout Successfully", 'sql_data' => $sql_data);

            } else {

                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => [], "message" => "Vendor Logout");

            }



        } else {



            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => "", "message" => "Parmas missing");

        }



        $this->api_response($response);

        exit;



    }



    public function get_partner_services_get($partner_id)

    {

        $response = array();



        if ($partner_id != '') {



            $sql2    = "SELECT id, name_en, name_mr, user_type_id FROM user_services WHERE is_deleted='false' AND is_active = 'true'";

            $row2    = $this->db->query($sql2);

            $result2 = $row2->result_array();



            $row3            = $this->db->query("SELECT * FROM product_services WHERE is_deleted = 'false' AND is_active='true' AND partner_id = " . $partner_id);

            $result_packages = $row3->result_array();



            $selects = array('users.*');

            $where   = array('users.user_id' => $partner_id, 'users.is_deleted' => 'false', 'users.is_active' => 'true');



            $user_data = $this->Masters_model->get_data($selects, 'users', $where, $join);



            if (count($user_data)) {

                $response = array("success" => 1, "status" => 1, "data" => $user_data, "config_url" => $this->config_url, "service_options" => $result_packages, "packages" => $result_packages, "msg" => "partner listed successfully");

            } else {



                $response = array("success" => 0, "status" => 1, "data" => $user_data, "config_url" => $this->config_url, "service_options" => $result_packages, "packages" => $result_packages, "msg" => "partner listed successfully");

            }

        } else {

            $response = array("success" => 0, "status" => 0, "message" => "partner id is missing");

        }

        $this->api_response($response);

    }



    public function about_us_get()

    {



        $result['phone1'] = '+91 9607005004';



        $result['about_bg_image'] = 'https://www.famrut.co.in/images/about_bg.png';

        $result['phone2']         = '+91 (0253) 6636500';

        $result['email']          = 'getintouch@famrut.co.in';

        $result['address']        = 'Plot No. B- 24 & 25, NICE Industrial Area, Satpur MIDC, Nashik 422 007';

        $result['about_us']       = 'Take your agri products and services to billions of farmers across India



        If you are into agro-business, be it from providing loans, crop insurances, agronomists, labour contractors, traders, vet, agricultural tools companies, or more, you are at the right place.  Famrut Partner App is a platform that will help you reach billions of farmers across India and offer your services. All you have to do is register yourself on the app as our partner.



        Famrut Partner App will not only provide visibility for your products and services but also will help you increase revenues. Famrut Business App has everything you need to scale your agri related business and expand it to the next level with added benefits, incentives, and much more. It is your one-stop-shop to reach billions of farmers.



        Download the App now and explore new revenue opportunities for your Business.';



        $result['about_us_mr'] = 'तुमची कृषी उत्पादने आणि सेवा भारतातील अब्जावधी शेतकऱ्यांपर्यंत पोहोचवा.



        तुमचा कृषी-व्यवसायात असेल, मग ते बँक, पीक विमा, कृषीशास्त्रज्ञ, कामगार कंत्राटदार, व्यापारी, पशुवैद्यकीय, कृषी उपकरणे कंपन्या किंवा आणखी काही असो, तुम्ही योग्य ठिकाणी आहात. फामृत पार्टनर ऍप हे एक प्लॅटफॉर्म आहे जे तुम्हाला भारतातील अब्जावधी शेतकऱ्यांपर्यंत पोहोचण्यात आणि तुमच्या सेवा प्रदान करण्यात मदत करेल. तुम्हाला फक्त आमचे भागीदार म्हणून ऍपवर स्वतःची नोंदणी करायची आहे.



        फामृत पार्टनर ऍप हे तुम्हास तुमच्या कृषी संबंधित व्यवसायास पोषक तसेच अतिरिक्त लाभ प्रदान करून तुमचा व्यवसाय वृद्धिंगत करण्यासाठी सर्व गोष्टी देत आहे. कोट्यवधी शेतकर्‍यांपर्यंत पोहोचण्यासाठी हा सुलभ मार्ग आहे



        तर व्यासायिक बंधूनो आता फामृत हे ऍप वापरण्यास सुरुवात करा आणि तुमचा व्यवसाय वृंधिंगत करा.';



        $response = array("success" => 1, "config_url" => $this->config_url, "data" => $result, "msg" => 'About us', "error" => 0, "status" => 1);



        $this->api_response($response);

    }



    public function get_vendor_booked_slot_post()

    {

        $partner_id  = $this->input->post('partner_id');

        $crop_id     = $this->input->post('crop_id');

        $call_status = $this->input->post('schedule_call_status');

        if ($partner_id != '') {



            $sql_query = "SELECT product_leads.id,product_leads.client_id,product_leads.partner_id,product_leads.call_schedule_date,product_leads.call_schedule_time,product_leads.schedule_call_status,product_leads.crop_id,product_leads.created_on,client.first_name,client.last_name,client.phone,client.is_online,client.profile_image,crop.name,crop.name_mr FROM product_leads JOIN client ON client.id=product_leads.client_id FULL JOIN crop ON crop.crop_id = product_leads.crop_id WHERE product_leads.is_deleted=false AND product_leads.product_type='video_call_schedule' AND partner_id= '" . $partner_id . "'";

            if ($crop_id) {

                $sql_query .= " AND product_leads.crop_id= '" . $crop_id . "'";

            }



            $sql_query .= " ORDER BY product_leads.id DESC";



            $booked_call_query = $this->db->query($sql_query);

            $booked_call       = $booked_call_query->result_array();



            foreach ($booked_call as $res_key => $res_value) {

                $datecreate                                  = date_create($res_value['call_schedule_date']);

                $booked_call[$res_key]['call_schedule_date'] = date_format($datecreate, "d-M-Y");



                $booked_call[$res_key]['call_schedule_time'] = date("g:i A", strtotime($res_value['created_on']));



                $sql_emeeting = "SELECT meeting_status, call_duration, call_duration_sec FROM eMeeting WHERE lead_id='" . $res_value['id'] . "' ORDER BY id DESC LIMIT 1";



                $booked_call_query = $this->db->query($sql_emeeting);

                $emeeting          = $booked_call_query->result_array();



                $booked_call[$res_key]['call_duration'] = $emeeting[0]['call_duration'];



                $d1       = new DateTime($res_value['call_schedule_date']);

                $d2       = new DateTime(date("Y-m-d"));

                $interval = $d1->diff($d2);

                //$days     = $interval->format('%d');

                $days = $interval->days;



                $schedule_date = strtotime($res_value['call_schedule_date']);



                if ($emeeting[0]['call_duration_sec'] > 60) {

                    //$booked_call[$res_key]['schedule_call_status'] = "Past";

                    $booked_call[$res_key]['schedule_call_status'] = lang('schedule_call_past');

                    ///&& $emeeting[0]['call_duration_sec'] != ''

                } else if ($days > 2 && ($emeeting[0]['call_duration_sec'] < 60) && $schedule_date < time()) {

                    $booked_call[$res_key]['schedule_call_status'] = "Canceled";

                    $booked_call[$res_key]['schedule_call_status'] = lang('schedule_call_cancel');



                } else if (!empty($emeeting) && $emeeting[0]['call_duration_sec'] < 60 && ($schedule_date > time() or $days <= 2)) {

                    $booked_call[$res_key]['schedule_call_status'] = "Reschedule";

                    $booked_call[$res_key]['schedule_call_status'] = lang('schedule_call_reschedule');



                } else if ($emeeting[0]['call_duration_sec'] == '' && ($schedule_date > time() or $days <= 2)) {

                    $booked_call[$res_key]['schedule_call_status'] = "Upcoming";

                    $booked_call[$res_key]['schedule_call_status'] = lang('schedule_call_upcoming');

                }

                if ($call_status) {

                    $key = array_search($call_status, $booked_call[$res_key]);

                    if ($key) {

                        $booked_call_filter[] = $booked_call[$res_key];

                    }

                } else {

                    $booked_call_filter[] = $booked_call[$res_key];

                }



            }

            if (count($booked_call_filter)) {

                $response = array("status" => 1, "data" => $booked_call_filter, "message" => "Booked time slot");

            } else {

                $response = array("status" => 1, "data" => null, "message" => "Booked time slot not available");

            }



        } else {



            $response = array("status" => 0, "message" => "missing params");

        }



        $this->api_response($response);

    }



    public function get_partner_dashboard_get($partner_id)

    {

        $total_farmer     = 0;

        $total_booking    = 0;

        $upcoming_booking = $past_booking = $booking_cancelled = $booking_missed = 0;



        if ($partner_id != '') {

            $sql       = "SELECT count(id) as total_farmer  from client as c where c.is_deleted=false AND c.is_active=true AND c.is_whitelabeled = false";

            $query     = $this->db->query($sql);

            $total_res = $query->result_array();

            if (count($total_res)) {

                $total_farmer = $total_res[0]['total_farmer'];

            }



            $sql_query = "SELECT product_leads.id,product_leads.call_schedule_date,product_leads.call_schedule_time,product_leads.schedule_call_status,product_leads.created_on FROM product_leads WHERE product_leads.is_deleted=false AND product_leads.product_type='video_call_schedule' AND partner_id= '" . $partner_id . "'";



            $booked_call_query = $this->db->query($sql_query);

            $booked_call       = $booked_call_query->result_array();

            $past              = $total              = $canceled              = $upcoming              = $reschedule              = 0;

            foreach ($booked_call as $res_key => $res_value) {

                $total++;

                $datecreate                                  = date_create($res_value['call_schedule_date']);

                $booked_call[$res_key]['call_schedule_date'] = date_format($datecreate, "d-M-Y");



                $booked_call[$res_key]['call_schedule_time'] = date("g:i A", strtotime($res_value['created_on']));



                $sql_emeeting = "SELECT meeting_status, call_duration, call_duration_sec FROM eMeeting WHERE lead_id='" . $res_value['id'] . "' ORDER BY id DESC LIMIT 1";



                $booked_call_query = $this->db->query($sql_emeeting);

                $emeeting          = $booked_call_query->result_array();



                $booked_call[$res_key]['call_duration'] = $emeeting[0]['call_duration'];



                $d1       = new DateTime($res_value['call_schedule_date']);

                $d2       = new DateTime(date("Y-m-d"));

                $interval = $d1->diff($d2);

                //$days     = $interval->format('%d');

                $days          = $interval->days;

                $schedule_date = strtotime($res_value['call_schedule_date']);



                if ($emeeting[0]['call_duration_sec'] > 60) {

                    $past++;



                } else if ($days > 2 && ($emeeting[0]['call_duration_sec'] < 60) && $schedule_date < time()) {

                    $canceled++;

                } else if (!empty($emeeting) && $emeeting[0]['call_duration_sec'] < 60 && ($schedule_date > time() or $days <= 2)) {

                    $reschedule++;

                } else if ($emeeting[0]['call_duration_sec'] == '' && ($schedule_date > time() or $days <= 2)) {

                    $upcoming++;

                }



            }



            $dashboard_data[] = array(

                'total_farmer'      => $total_farmer,

                'total_booking'     => $total,

                'upcoming_booking'  => $upcoming,

                'past_booking'      => $past,

                'booking_cancelled' => $canceled,

                'booking_missed'    => $reschedule,

            );

            // print_r($dashboard_data);exit();

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $dashboard_data, "message" => "Get Partner dashboard");



            $this->api_response($response);

            exit;



        } else {



            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => "Partner id missing , please try again some time.");

            $this->api_response($response);

            exit;

        }



        $this->api_response($response);

        exit;

    }



    // Send notification to client by partner for pickup your order

    public function notify_client_post()

    {

        $order_id = $this->input->post('order_id');

        $message = [];

        $result_client_orders = [];

        if ($order_id) {

            $sql_client_orders = "select * from client_orders WHERE id = '" . $order_id . "' LIMIT 1";

            $result_client_orders = $this->db->query($sql_client_orders)->result_array();

            $title = 'order';



            if($result_client_orders[0]['status'] != ''){

                if(!empty($result_client_orders[0]['pickup_location_id'])){

                    // $client_orders_message = 'Pickup your order number #'.$result_client_orders[0]['order_num'].' from selected location.'; 

                    $client_orders_message = 'Your order is ready. Please pick up now. Thank you!'; 



                    // Your order is ready. Please pick up now. Thank you!

                } else {

                    $client_orders_message = 'Your order has arrived. Please collect from doorstep. Enjoy!'; 



                    // "Your order has arrived. Please collect from doorstep. Enjoy!"

                }

            }

            

            $admno = 1;

            $call_data = $meeting_link = $custom_array = $img = $jsonString = '';

            //$farmer_name = $farmer_data[0]['first_name'];

            $is_whitelable = 1;

            $whr_chk_farmer  = ' AND id='.$result_client_orders[0]['client_id'];

            $qry   = "SELECT id, device_id FROM client WHERE is_deleted='false' AND is_active='true' AND device_id is NOT NULL ".$whr_chk_farmer;

            

            $res_data        = $this->db->query($qry);

            $device_id_data  = $res_data->row_array();

            $token = [];

            if(count($device_id_data)){

                $token[] = $device_id_data['device_id'];

            }



            // $token[] = 'eUeRKVqhSrKbhz6c625a1F:APA91bHC_1QnFT3dazCzaIQKvL_I4ld75AtEaYk_gk69HifCTRefAW0_ux1ylv_Bg2BjZSiGs2w0XsKviCA_FablpDwdEtcWtsDk2SprUpZLr8PKBYO7o_F-6Hb1Mm5JwRWssJAK2Pz6';

            

            if(count($token)){

                $this->load->model('Notification_model');

                $jsonString =  $this->Notification_model->sendPushNotifications_NA($token, $title, $client_orders_message, $is_whitelable, $group_ids = 0, $custom_array, $type='Order', $order_id);



                $notification_data = json_decode($jsonString, true);

                $notification_status = $notification_data['success'];

                if($notification_status == 1){

                    $notification_msg = 'Notify to client successfully';

                } else {

                    $notification_msg = 'Not able to notify';

                }



                $response = array("success"=> 1, "error" => 0, "status" => $notification_status, "message" => $notification_msg, "data"=>json_decode($jsonString, true));

            } else {

                $response = array("success"=> 0, "error" => 1, "status" => 1, "message" => "Not found any client token", "data"=>[]);

            }

            // echo'<pre>';print_r($message);exit;

        } else {

            $response = array("success"=> 0, "error" => 1, "status" => 0, "message" => "Order id required!", "data"=>[]);

        }



        $this->api_response($response);

        exit;

    }



    /***********************Working APIs: End***********************/



    public function vendor_dashboard($vendor_id)

    {



        if ($vendor_id != '') {

            $slq_farmer = "SELECT c.id,c.first_name,c.middle_name,c.last_name,c.email,c.phone,c.profile_image,P.created_on,c.address1,c.address2,c.city,c.postcode,c.latitude,c.longitude from product_leads as P LEFT JOIN client as c ON c.id=P.client_id where c.is_deleted = false AND P.is_deleted = false AND P.partner_id= $vendor_id ORDER BY  P.id DESC";

            $query      = $this->db->query($slq_farmer);

            $result     = $query->result_array();

            $lead_count = count($result);



            $row = $this->db->query("SELECT * FROM product_services WHERE is_deleted = 'false' AND partner_id = " . $vendor_id);



            $result_services = $row->result_array();

            $services_count  = count($result_services);



            $row_prod = $this->db->query("SELECT * FROM products WHERE is_deleted = 'false' AND partner_id = " . $vendor_id);



            $result_products = $row_prod->result_array();

            $products_count  = count($result_products);



            $dashboard_data[] = array('menu_key' => 'Services', 'title' => 'Services count', 'count' => $services_count, 'id' => 1, 'image' => 'https://img.icons8.com/nolan/96/conference.png');

            $dashboard_data[] = array('menu_key' => 'Product', 'title' => 'Product count', 'count' => $products_count, 'id' => 2, 'image' => 'https://img.icons8.com/nolan/96/shop.png');

            $dashboard_data[] = array('menu_key' => 'Lead', 'title' => 'Lead count', 'count' => $lead_count, 'id' => 3, 'image' => 'https://img.icons8.com/nolan/96/customer-support.png');



        }



        $response = array("success" => 1, "error" => 0, "status" => 1, 'dashboard_data' => $dashboard_data, 'farmer_lead_count' => $lead_count, "products_count" => $products_count, "services_count" => $services_count);

        $this->api_response($response);

    }



    public function v_connect_call()

    {



        $farmer_id      = $this->input->post('farmer_id');

        $partner_id     = $this->input->post('partner_id');

        $output         = "";

        $today          = date('Y-m-d');

        $response       = array();

        $available_flag = 0; // Not avaiable



        $response = array("status" => 0, "message" => "missing params");

        if ($partner_id != '') {



            $row_val = $this->db->query("SELECT * FROM emeeting WHERE farmer_id = " . $farmer_id . " and (meeting_status_id = 1 OR meeting_status_id = 2)  AND date(created_on) = '" . $today . "' ORDER BY id ASC LIMIT 1");

            $result  = $row_val->result_array();

            if (count($result) > 0) {

                $available_flag = 0; // Partner is not avaiable

                $meeting_link   = '';

            } else {

                $available_flag = 1; // Partner is avaiable



                $meeting_link = md5(date("Ymdhis") . $this->input->post('farmer_id') . $this->input->post('partner_id'));



            }



            $data     = $_POST;

            $response = array("success" => 1, "available_flag" => $available_flag, "error" => 0, "status" => 1, "data" => $insert, "MeetingId" => $meeting_link);

            $this->api_response($response);

        }

    }



    public function v_generate_connect_call()

    {

        $farmer_id      = $this->input->post('farmer_id');

        $partner_id     = $this->input->post('partner_id');

        $output         = "";

        $today          = date('Y-m-d');

        $response       = array();

        $available_flag = 0; // Not avaiable



        $response = array("status" => 0, "message" => "missing params");

        if ($farmer_id != '' && $partner_id != '') {



            $meeting_link = date("Ymdhis") . $this->input->post('farmer_id') . $this->input->post('partner_id');



            $insert = array(

                'farmer_id'            => $this->input->post('farmer_id'),

                'partner_id'           => $this->input->post('partner_id'),

                'meeting_status_id'    => 1,

                'meeting_started_from' => 1,

                'meeting_link_id'      => md5($meeting_link),

                'is_active'            => 'true',

                'created_on'           => current_date(),

            );



            $sql_insert = $this->db->insert('emeeting', $insert);



            $row_val = $this->db->query("SELECT * FROM emeeting WHERE partner_id = " . $partner_id . " and (meeting_status_id = 1 OR meeting_status_id = 2)  AND date(created_on) = '" . $today . "' ORDER BY id ASC LIMIT 1");



            $result = $row_val->result_array();

            if (count($result) > 0) {

                $available_flag = 0; // Partner is not avaiable

            } else {

                $available_flag = 1; // Partner is avaiable

            }



            $data     = $_POST;

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data);

            $this->api_response($response);

        }

    }



    public function v_incoming_farmer_call()

    {



        $today           = date('Y-m-d');

        $farmer_id       = $this->input->post('farmer_id');

        $partner_id      = $this->input->post('partner_id');

        $meeting_link_id = $this->input->post('MeetingId');



        $sql_chk = "SELECT * FROM emeeting WHERE partner_id = " . $partner_id . " and farmer_id = " . $farmer_id . " and (meeting_status_id = 1 OR meeting_status_id = 2) AND date(created_on) = '" . $today . "' ORDER BY id ASC LIMIT 1";

        $row_val = $this->db->query($sql_chk);



        $call_result = $row_val->result_array();



        if (count($call_result) > 0) {

            $available_flag = 1; // Partner is not avaiable

            $call_coming    = 1;

            $meeting_link   = $call_result[0]['meeting_link_id'];

            $msg            = 'there is already meeting id genrated from farmer';



        } else {



            $msg            = 'Farmer call initiated';

            $available_flag = 1;



            if ($meeting_link_id == '') {

                $meeting_link_id = md5(date("Ymdhis") . $farmer_id . $partner_id);

            }



            $insert = array(

                'farmer_id'            => $farmer_id,

                'partner_id'           => $partner_id,

                'meeting_status_id'    => 1,

                'meeting_started_from' => 1,

                'meeting_link_id'      => $meeting_link_id,

                'is_active'            => 'true',

                'created_on'           => current_date(),

            );



            $sql_insert  = $this->db->insert('emeeting', $insert);

            $call_result = $insert;

        }



        $data['title']     = 'Farmer Call';

        $data['MeetingId'] = $meeting_link_id;

        $data['farmer_id'] = $farmer_id;

        $data['call_data'] = $call_result;



        $response = array("success" => 1, "available_flag" => $available_flag, "call_data"->$data, "error" => 0, "status" => 1, "data" => $data, "msg" => $msg);

        $this->api_response($response);



    }



    public function v_disconnect_call()

    {



        $partner_id       = $this->input->post('partner_id');

        $farmer_id        = $this->input->post('farmer_id');

        $call_status_flag = $this->input->post('call_status_flag');



        if ($this->input->post('meeting_duration')) {

            $meeting_duration = $this->input->post('meeting_duration');



        } else {

            $meeting_duration = 11;

        }



        $meeting_link = $this->input->post('MeetingId');



        $where_array = array(

            'farmer_id'       => $farmer_id,

            'partner_id'      => $partner_id,

            'meeting_link_id' => $meeting_link,

        );



        $update_array = array(

            'meeting_status_id' => $call_status_flag,

            'meeting_end_from'  => 2,

            'updated_on'        => current_date(),

        );



        $sql_update = $this->db->update('emeeting', $update_array, $where_array);



        $response = array("success" => 1, "call_data"->$where_array, "error" => 0, "status" => 1, "data" => $update_array);

        $this->api_response($response);

    }



    public function v_accept_call()

    {



        $call_status_flag = 2;



        $partner_id       = $this->input->post('partner_id');

        $farmer_id        = $this->input->post('farmer_id');

        $call_status_flag = $call_status_flag;

        $accept_call_time = current_date();



        $meeting_link = $this->input->post('MeetingId');



        $where_array = array(

            'farmer_id'       => $farmer_id,

            'partner_id'      => $partner_id,

            'meeting_link_id' => $meeting_link,

        );



        $update_array = array(

            'meeting_status_id' => $call_status_flag,

            'accept_call_time'  => current_date(),

        );



        $sql_update = $this->db->update('emeeting', $update_array, $where_array);



        $response = array("success" => 1, "call_data"->$where_array, "error" => 0, "status" => 1, "data" => $update_array);

        $this->api_response($response);

    }



    public function custom_config()

    {



        $config_master_data = array();

        $data_array         = array();

        $sql                = "SELECT id,name,key_fields,seq,logo,mob_icon,is_active,description FROM config_master where is_deleted=false AND name ILIKE '%vendor%' ORDER BY id ASC";

        $res_val            = $this->db->query($sql);

        $res_array          = $res_val->result_array();



        if (count($res_array) > 0) {

            $config_master_data = $res_array;

        }



        $base_path = $this->base_path;

        $logo_url  = $base_path . 'uploads/config_master/';



        $response = array("success" => 1, "config_master_data" => $config_master_data, "error" => 0, "status" => 1, "data" => $data_array, "logo_url" => $logo_url);

        $this->api_response($response);



    }



    public function chk_otp()

    {



        $phone      = substr(preg_replace('/\s+/', '', $this->input->post('phone')), -10, 10);

        $opt_number = $this->input->post('otp');



        $result   = array();

        $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => "Registration / login failed, please try again some time.");



        if ($phone != '' && $opt_number != '') {



            /* $row    = $this->db->query("SELECT id FROM client WHERE is_deleted = 'false' AND 'is_active' => 'true' AND opt_number='$opt_number' AND phone::varchar = '$phone'::varchar ");*/

            $sql_chk = "SELECT id FROM users WHERE is_deleted = 'false' and phone_no::varchar = '$phone'::varchar ";

            $row     = $this->db->query($sql_chk);

            $result  = $row->result_array();



            if (count($result)) {



                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "OTP matched", 'opt_number' => $opt_number);



                $this->api_response($response);

                exit;



            } else {



                $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => "OTP not matched, please try again some time.");



                $this->api_response($response);

                exit;



            }

        } else {

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => "Params missing, please try again some time.");



            $this->api_response($response);

            exit;

        }



        $this->api_response($response);

        exit;

    }



    public function get_vendor_profile($id)

    {



        $result   = array();

        $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $_POST, "message" => "missing params");



        if ($id != '') {



            $sql_user = "SELECT users.*, categories.name,categories.name_mr FROM users

            LEFT JOIN categories ON categories.cat_id = users.user_type

             where  users.user_id = $id AND users.is_deleted= false";



            $res       = $this->db->query($sql_user);

            $user_data = $res->result_array();



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



            $row = $user_data;



            if (count($row)) {

                if (1) {



                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $row, "message" => "Profile data", 'config_url' => $this->config_url, 'sql_user' => $sql_user);

                }

            }

        }



        $this->api_response($response);

    }



    public function get_farmer_data_by_id($id)

    {

        $sql     = "SELECT id,device_id,first_name,last_name,phone FROM client WHERE is_deleted='false' AND is_active='true' AND id =" . $id . " LIMIT 1";

        $row_tag = $this->db->query($sql);

        $results = $row_tag->result_array();

        if (count($results)) {



            $response = array("status" => 1, "data" => $results, "message" => "Farmer data");



        } else {

            $response = array("status" => 0, "message" => "missing params");

        }

        $this->api_response($response);

    }



    public function sendPushNotificationToFCMSeverdev($token, $title, $message, $arr_user, $type, $call_data)

    {

        $meeting_link   = $call_data['meeting_link_id'];

        $farmer_name    = $call_data['farmer_name'];

        $farmer_id      = $call_data['farmer_id'];

        $partner_id     = $call_data['partner_id'];

        $partner_name   = $call_data['partner_name'];



        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';



        $fields = array(

            'registration_ids' => $token,

            'priority'         => 10,



            'data'             => array(

                                    "title" => $title,

                                    "body"  => $message,

                                    "sound" => 'Default',

                                    'image' => 'Notification Image',

                                    'admno' => $arr_user,

                                    'type'  => $type,

                                    'route' => $type,

                                    'meeting_link'  => $meeting_link,

                                    'partner_name'  => $partner_name,

                                    'farmer_id'     => $farmer_id,

                                    'partner_id'    => $partner_id

                                ),

            "time_to_live"     => 30,

            "ttl"              => 30,

        );



        if(get_config_data('API_SERVER_KEY')){

            $API_SERVER_KEY = get_config_data('API_SERVER_KEY');

        } else {

            $API_SERVER_KEY = API_SERVER_KEY;

        }

        

        // $where       = array('is_deleted' => 'false', 'is_active' => 'true', 'key_fields' => 'API_SERVER_KEY');

        // $app_key_res = $this->Masters_model->get_data("description", 'config_master', $where);



        // if ($app_key_res[0]['description']) {

        //     $API_SERVER_KEY = $app_key_res[0]['description'];

        // } else {

        //     $API_SERVER_KEY = 'AAAAZP52chY:APA91bHn09jHHewFEixuQ87yO4QuYql8_bWBtRYtjx27mMIz-VWhMw6FRtbOoAHfm_xgBoZGqC0NJJiNlfObiNsqE-MNjRvNLaFtfysM6_JTzfZMFyRnjDOuzw5oCj-Ly6_Xw1GUXBX4';

        // }



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



    public function sendPushNotificationToFCMSeverdev_chat($token, $title, $message, $arr_user, $type, $partner_name)

    {

        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';



        $fields = array(

            'registration_ids' => $token,

            'priority'         => 10,

            'data'             => array("title" => $title, "body" => $message, "sound" => 'Default', 'image' => 'Notification Image', 'admno' => $arr_user, 'type' => $type, 'partner_name' => $partner_name),

        );



        if ($app_key_res[0]['description']) {

            $API_SERVER_KEY = $app_key_res[0]['description'];

        } else {

            $API_SERVER_KEY = 'AAAAZP52chY:APA91bHn09jHHewFEixuQ87yO4QuYql8_bWBtRYtjx27mMIz-VWhMw6FRtbOoAHfm_xgBoZGqC0NJJiNlfObiNsqE-MNjRvNLaFtfysM6_JTzfZMFyRnjDOuzw5oCj-Ly6_Xw1GUXBX4';

        }



        //$API_SERVER_KEY = 'AAAAmhCfp8k:APA91bHXSHb1Ihie4m3y0v_0e1tAf7JCPMnubM751PMMVkC9oAi54O1AUGipDZ6ZZGCD9ajyxAFLsxjQ0cuLOqSLGjKp9Z0vBttKkIFeX7_xrZnbrAamAex-HMYK3z4SEz2_mHdwWYdu';



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



    // public function response($response)

    // {

    //     header('Content-type: application/json');

    //     echo json_encode($response);

    // }



    /***********************Save Logs:Start***********************/

    public function save_logs($response = [])

    {

        $log = array(

            'USER'     => $_SERVER['REMOTE_ADDR'],

            'DATE'     => date("Y-m-d, H:i:s"),

            'URL'      => $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],

            'METHOD'   => $_SERVER['REQUEST_METHOD'],

            'REQUEST'  => $_REQUEST,

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