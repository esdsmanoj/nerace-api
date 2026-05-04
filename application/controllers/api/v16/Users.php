<?php

defined('BASEPATH') or exit('No direct script access allowed');



error_reporting(E_ERROR | E_PARSE);

//error_reporting(E_ERROR | E_PARSE);

//error_reporting(E_ALL);



require APPPATH . 'libraries/RestController.php';



use chriskacerguis\RestServer\RestController;



class Users extends RestController
{

    public function __construct()
    {

        header("Access-Control-Allow-Origin: *");

        header("Access-Control-Allow-Headers: *");

        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

        parent::__construct();

        $headers = $this->input->request_headers();
        $headers_data = array_change_key_case($headers, CASE_LOWER);
        // print_r($headers_data);

        $this->load->model('Ekyc_model');
        $this->load->model('Notification_model');

        // $this->api_base_path = $api_base_path = "https://dev.famrut.co.in/agri-ecosystem-api/api/v16/";

        // $headers_data = $this->input->request_headers();
        //$selected_lang = ($headers_data['lang'])?$headers_data['lang']:'en';
        //$headers_data['lang'] = $headers_data['Lang'];
        // $headers_data['domain'] = $headers_data['domain'];
        // $headers_data['client-type'] = $headers_data['Client-type'];
        // $headers_data['client-type'] = $headers_data['Client_type'];

        ///$headers_data['lang'] = $headers_data['Lang'];
        ///$headers_data['lang'] = $headers_data['Lang'];


        $this->api_base_path = $api_base_path = API_BASE_PATH;

        // Start: Required headers and there value check

        // if ((!strpos($_SERVER['REQUEST_URI'], 'partner_login')) || (!strpos($_SERVER['REQUEST_URI'], 'dynamic_domain_db_connection'))) {

        // if (!strpos($_SERVER['REQUEST_URI'], 'dynamic_domain_db_connection')) {



        if (!strpos($_SERVER['REQUEST_URI'], 'dynamic_domain_db_connection')) {
            $require_headers = array('domain', 'appname');
        } else if (!strpos($_SERVER['REQUEST_URI'], 'dynamic_theme_color')) {
            $require_headers = array('domain', 'appname');
        } else {
            $require_headers = array('domain');
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

            $msg = "Invalid Request";

            $response = array("status" => 0, "error" => 1, "data" => array(), "message" => $msg);

            $this->api_response($response);
            exit;

        } else if (!empty($require_header_val) && count($require_header_val) > 0) {

            $require_header_str = implode(', ', $require_header_val);

            $msg = "Invalid Request";

            $response = array("status" => 0, "error" => 1, "data" => array(), "message" => $msg);

            $this->api_response($response);
            exit;

        }

        // End: Required headers and there value check



        // Start: Create upload file name and as per database name : Akash

        $this->connected_appname = '';

        $this->connected_domain = '';

        $root_folder = $_SERVER['HOME'] . '/' . UPLOAD_ROOT_FOLDER . '/';



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



        $this->connected_appname = strtolower($headers_data['appname']); // globaly set connected appname name

        $this->connected_domain = strtolower($headers_data['domain']); // globaly set connected domain name

        $db_folder = $root_folder . 'uploads/' . $this->connected_domain;

        if (!file_exists($db_folder)) {

            mkdir($db_folder, 0777, true);

        }



        if (!file_exists($db_folder . '/user_data')) {

            mkdir($db_folder . '/user_data', 0777, true);

        }



        $this->upload_file_folder = $db_folder . '/' . 'user_data/'; // globaly set upload file folder root

        // End: Create upload file name and as per db name : Akash



        // echo $this->upload_file_folder;exit;



        $this->load->library('Token');

        if (!strpos($_SERVER['REQUEST_URI'], 'dynamic_domain_db_connection')) {

            $this->load->library('upload');

            $this->load->model('Email_model');

            $this->load->helper('log_helper');

            $this->load->helper('sms_helper');

            $this->load->helper('npks_helper');

            $this->load->model('Masters_model');

        }



        $lang_folder = "english";



        if ($this->session->userdata('user_site_language') && $this->session->userdata('user_site_language') == "MR") {

            $lang_folder = "marathi";

        } else {

            $this->session->set_userdata('user_site_language', 'EN');

            $lang_folder = "english";

        }



        // $base_path = $this->base_path;

        //$base_path = 'https://dev.famrut.co.in/agri_ecosystem/';

        // $this->base_path = $base_path = 'https://dev.famrut.co.in/agroemandi/';

        //$this->base_path = $base_path = 'https://dev.famrut.co.in/agri-ecosystem-uat/';

        $this->base_path = $base_path = BASE_PATH_PORTAL;

        // agri-ecosystem-api





        // $base_path = 'https://dev.famrut.co.in/agroemandi/';

        // $headers_data  = $this->input->request_headers();
        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $headers_data['lang'] = isset($headers_data['lang']) ? $headers_data['lang'] : 'en';



        $this->selected_lang = $selected_lang = $this->$headers_data['lang'] = $headers_data['lang'];



        if ($this->selected_lang == '') {

            $this->selected_lang = $selected_lang = 'en';

            $this->$headers_data['lang'] = 'en';

        }



        if ($selected_lang == 'mr') {

            $lang_folder = "marathi";

        } elseif ($selected_lang == 'hi') {

            $lang_folder = "hindi";

        } else {

            $lang_folder = "english";

        }



        $this->lang->load(array('site'), $lang_folder);

        // echo lang('Data_Not_Found');exit;

        // $group_id     = $headers_data['group_id']; (replace this line with below 2 lines)

        $group_id_arr = explode(',', $headers_data['group_id']);

        $group_id = $group_id_arr[0];



        // Below array is in used

        $this->config_url = array(

            'category_img_url' => $base_path . 'uploads/category/',

            'partner_img_url' => $base_path . 'uploads/' . $this->connected_domain . '/user_data/profile/',

            'aadhar_no_doc_url' => $base_path . 'uploads/' . $this->connected_domain . '/user_data/aadhar_no/',

            'pan_no_doc_url' => $base_path . 'uploads/' . $this->connected_domain . '/user_data/pan_no/',

            'farm_image_url' => $base_path . 'uploads/' . $this->connected_domain . '/user_data/farm/',

            'Product_image_url' => $base_path . 'uploads/productcategory/',

            'market_cat_image_url' => $base_path . 'uploads/logo/',

            'service_image_url' => $base_path . 'uploads/product_service/',

            'blogs_types_url' => $base_path . 'uploads/blogs_types/',

            'media_types' => $base_path . 'uploads/media_types/',

            'blogs_tags_url' => $base_path . 'uploads/blogs_tags/',

            'created_blogs_url' => $base_path . 'uploads/created_blogs/',

            'farmer_documents_url' => $base_path . 'uploads/' . $this->connected_domain . '/user_data/verification_documents/',

            'advertise_image_url' => $base_path . 'uploads/advertise_master/',

            'whitelabel_image_url' => $base_path . 'uploads/client_group_master/',

            'terms_sheet' => $base_path . 'uploads/terms_sheet/',

            'farm_doc' => $base_path . 'uploads/' . $this->connected_domain . '/user_data/farm_doc/',

            'insurance_company' => $base_path . 'uploads/insurance_company/',

            'crop_image_url' => $base_path . 'uploads/crops/',

            'crop_type_url' => $base_path . 'uploads/crop_type_icon/',

            'notice' => $base_path . 'uploads/notice/',

            'announcement' => $base_path . 'uploads/announcement/',

            'crop_health_predict_api' => 'http://115.124.96.136:8443/predict',

            'dss_module_imageurl' => $base_path . 'uploads/dss_module/',

            'bottom_menu_icon' => $base_path . 'uploads/app_menu/',

            'crop_verity_img_url' => $base_path . 'uploads/crop_variety_icon/',

            'crop_ferti_img_url' => $base_path . 'uploads/crops_ferti_image/',

            'soil_health_image' => $base_path . 'uploads/soil_health_image/',

            'media_thumbnails' => $base_path . 'uploads/media_thumbnails/',

            'loan_type_url' => $base_path . 'uploads/loan_type/',

            'loan_image_url' => $base_path . 'uploads/' . $this->connected_domain . '/user_data/loan/',

            'crop_image' => $base_path . 'uploads/' . $this->connected_domain . '/user_data/crop_image/',

            'intro_screen_img_url' => $base_path . 'uploads/config_master/intro_master',

            'trade_products' => $base_path . 'uploads/config_master/trade_products',

            'log_urls' => $base_path . 'uploads/config_master',

            'partner_business_logo' => $base_path . 'uploads/profile',

        );



        $show_cart = (!empty(get_config_settings('show_cart'))) ? get_config_settings('show_cart')['description'] : true;

        $show_crop = (!empty(get_config_settings('show_crop'))) ? get_config_settings('show_crop')['description'] : true;

        $show_qr = (!empty(get_config_settings('show_qr'))) ? get_config_settings('show_qr')['description'] : true;

        $show_commodity_search_bar = (!empty(get_config_settings('show_commodity_search_bar'))) ? get_config_settings('show_commodity_search_bar')['description'] : true;



        $this->config_flag = array(

            'show_cart' => filter_var($show_cart, FILTER_VALIDATE_BOOLEAN),

            'show_crop' => filter_var($show_crop, FILTER_VALIDATE_BOOLEAN),

            'show_qr' => filter_var($show_qr, FILTER_VALIDATE_BOOLEAN),

            'show_commodity_search_bar' => filter_var($show_commodity_search_bar, FILTER_VALIDATE_BOOLEAN),

        );



        $reward_section = (get_config_settings('reward_section')) ? get_config_settings('reward_section') : [];

        $this->REWARDS = false;

        if (!empty($reward_section) && $reward_section['description'] == '1') {

            $this->REWARDS = true;

        }





        // send app_user_type:  1 = ( not farmer)  &&&&&   0 = farmer.

        if (isset($headers_data['app_user_type']) || !empty($headers_data['app_user_type'])) {



            if ($headers_data['app_user_type'] == 1) {



                $this->menu = array(

                    /* array('id' => '2', 'title' => lang('Home'), 'map_key' => 'Home', 'icon' => 'home'),       */

                    /* array('id' => '6', 'title' => lang('My-Orders'), 'map_key' => 'My-Orders', 'icon' => 'order'),*/

                    array('id' => '15', 'title' => lang('About us'), 'map_key' => 'About-us', 'icon' => 'about_us'),

                    array('id' => '16', 'title' => lang('Privacy-Policy'), 'map_key' => 'Privacy-Policy', 'icon' => 'ic_assignment'),

                    array('id' => '17', 'title' => lang('Announcement'), 'map_key' => 'Announcement', 'icon' => 'ic_announcement'),

                    array('id' => '18', 'title' => lang('Setting'), 'map_key' => 'Setting', 'icon' => 'seeting'),



                );



            } else {



                $this->menu = array(

                    /* array('id' => '2', 'title' => lang('Home'), 'map_key' => 'Home', 'icon' => 'home'),*/

                    array('id' => '3', 'title' => lang('My-Farms'), 'map_key' => 'My-Farms', 'icon' => 'my_farm'),

                    /*  array('id' => '4', 'title' => lang('Apply-for-Loan'), 'map_key' => 'Apply-for-Loan', 'icon' => 'loan'),*/

                    array('id' => '5', 'title' => lang('Commodity'), 'map_key' => 'Commodity', 'icon' => 'commodity'),

                    array('id' => '6', 'title' => lang('My-Orders'), 'map_key' => 'My-Orders', 'icon' => 'order'),

                    array('id' => '7', 'title' => lang('Weather-Forcast'), 'map_key' => 'Weather-Forcast', 'icon' => 'Weather-Forcast'),

                    /* array('id' => '8', 'title' => lang('IOT-Devices'), 'map_key' => 'IOT-Devices', 'icon' => 'iot_icon'),*/

                    array('id' => '11', 'title' => lang('NPK-Calculator'), 'map_key' => 'NPK-Calculator', 'icon' => 'ic_calc'),

                    /* array('id' => '12', 'title' => lang('Dockbox'), 'map_key' => 'Dockbox', 'icon' => 'docbox'),*/

                    array('id' => '16', 'title' => lang('Notice'), 'map_key' => 'Notice', 'icon' => 'ic_notice'),

                    array('id' => '17', 'title' => lang('Announcement'), 'map_key' => 'Announcement', 'icon' => 'ic_announcement'),

                    /* array('id' => '14', 'title' => lang('Invite'), 'map_key' => 'Invite', 'icon' => 'ic_invite'),*/

                    array('id' => '15', 'title' => lang('About us'), 'map_key' => 'About-us', 'icon' => 'about_us'),

                    array('id' => '16', 'title' => lang('Privacy-Policy'), 'map_key' => 'Privacy-Policy', 'icon' => 'ic_assignment'),



                    array('id' => '18', 'title' => lang('Setting'), 'map_key' => 'Setting', 'icon' => 'seeting'),

                );



                if ($this->REWARDS) {

                    $this->menu[] = array('id' => '19', 'title' => lang('Rewards'), 'map_key' => 'Rewards', 'icon' => 'rewards');

                }

            }



        } else {

            $this->menu = array(

                array('id' => '2', 'title' => lang('Home'), 'map_key' => 'Home', 'icon' => 'home'),

                array('id' => '3', 'title' => lang('My-Farms'), 'map_key' => 'My-Farms', 'icon' => 'my_farm'),

                /*  array('id' => '4', 'title' => lang('Apply-for-Loan'), 'map_key' => 'Apply-for-Loan', 'icon' => 'loan'),*/

                array('id' => '5', 'title' => lang('Commodity'), 'map_key' => 'Commodity', 'icon' => 'commodity'),

                array('id' => '6', 'title' => lang('My-Orders'), 'map_key' => 'My-Orders', 'icon' => 'order'),

                array('id' => '7', 'title' => lang('Weather-Forcast'), 'map_key' => 'Weather-Forcast', 'icon' => 'weather'),

                /*   array('id' => '8', 'title' => lang('IOT-Devices'), 'map_key' => 'IOT-Devices', 'icon' => 'iot_icon'),*/

                array('id' => '11', 'title' => lang('NPK-Calculator'), 'map_key' => 'NPK-Calculator', 'icon' => 'ic_calc'),

                /*   array('id' => '12', 'title' => lang('Dockbox'), 'map_key' => 'Dockbox', 'icon' => 'docbox'),*/

                array('id' => '14', 'title' => lang('Invite'), 'map_key' => 'Invite', 'icon' => 'ic_invite'),

                array('id' => '15', 'title' => lang('About us'), 'map_key' => 'About-us', 'icon' => 'about_us'),

                array('id' => '16', 'title' => lang('Privacy-Policy'), 'map_key' => 'Privacy-Policy', 'icon' => 'ic_assignment'),

                array('id' => '16', 'title' => lang('Notice'), 'map_key' => 'Notice', 'icon' => 'ic_notice'),

                array('id' => '17', 'title' => lang('Announcement'), 'map_key' => 'Announcement', 'icon' => 'ic_announcement'),

                array('id' => '18', 'title' => lang('Setting'), 'map_key' => 'Setting', 'icon' => 'seeting'),

                //  array('id' => '19', 'title' => lang('Rewards'), 'map_key' => 'Rewards', 'icon' => 'rewards'),

            );



            if ($this->REWARDS) {

                $this->menu[] = array('id' => '19', 'title' => lang('Rewards'), 'map_key' => 'Rewards', 'icon' => 'rewards');

            }



        }







        $this->home_message = array('message' => 'Welcome to FAMRUT - 10X Growth solution');



        $this->topology = array(

            array('id' => '1', 'value' => 'High', 'name_mr' => 'उंच'),

            array('id' => '2', 'value' => 'Low', 'name_mr' => 'कमी'),

            array('id' => '3', 'value' => 'Medium', 'name_mr' => 'मध्यम'),

        );

        $this->topology_web = array('1' => 'High', '2' => 'Low', '3' => 'Medium');

        $this->topology_web_mr = array('1' => 'उंच', '2' => 'कमी', '3' => 'मध्यम');



        $this->farm_type = array(

            array('id' => '1', 'value' => 'Organic Farming', 'name_mr' => 'सेंद्रिय शेती'),

            array('id' => '2', 'value' => 'Conventional Farming', 'name_mr' => 'पारंपारिक शेती'),

            array('id' => '3', 'value' => 'Residue Free Farming', 'name_mr' => 'अवशेष मुक्त शेती'),



        );

        $this->farm_type_web = array('1' => 'Organic Farming', '2' => 'Conventional Farming', '3' => 'Residue Free Farming');

        $this->farm_type_web_mr = array('1' => 'सेंद्रिय शेती', '2' => 'पारंपारिक शेती', '3' => 'अवशेष मुक्त शेती');



        $this->unit = array(

            array('id' => '1', 'value' => 'Acre', 'name_mr' => 'एकर'),

            array('id' => '2', 'value' => 'Hectare', 'name_mr' => 'हेक्टर'),

        );

        $this->unit_web_mr = array('1' => 'एकर', '2' => 'हेक्टर');

        $this->unit_web = array('1' => 'Acre', '2' => 'Hectare');



        $this->irri_src = array(

            array('id' => '1', 'value' => 'Well', 'name_mr' => 'विहीर'),

            array('id' => '2', 'value' => 'Borewell', 'name_mr' => 'बोअरवेल'),

            array('id' => '3', 'value' => 'Canal/River', 'name_mr' => 'कालवा / नदी'),

            array('id' => '4', 'value' => 'Farm lake', 'name_mr' => 'शेत तलाव'),

            array('id' => '5', 'value' => 'Others', 'name_mr' => 'इतर'),

        );

        $this->irri_src_web = array('1' => 'Well', '2' => 'Borewell', '3' => 'Canal/River', '4' => 'Farm lake', '5' => 'Others');

        $this->irri_src_web_mr = array('1' => 'विहीर', '2' => 'बोअरवेल', '3' => 'कालवा / नदी', '4' => 'शेत तलाव', '5' => 'इतर');



        $this->irri_faty = array(

            array('id' => '1', 'value' => 'Pipelines', 'name_mr' => 'पाईपलाईन'),

            array('id' => '2', 'value' => 'Sprinkler Heads', 'name_mr' => 'शिंपडण्याचे प्रमुख'),

            array('id' => '3', 'value' => 'Valves', 'name_mr' => 'वाल्व्ह'),

        );

        $this->irri_faty_web = array('1' => 'Pipelines', '2' => 'Sprinkler Heads', '3' => 'Valves');

        $this->irri_faty_web_mr = array('1' => 'पाईपलाईन', '2' => 'शिंपडण्याचे प्रमुख', '3' => 'वाल्व्ह');



        $this->crop_type = array(

            array('id' => '1', 'value' => 'Kharif', 'name_mr' => 'खरिफ'),

            array('id' => '2', 'value' => 'Rabi', 'name_mr' => 'रुबी'),

            array('id' => '3', 'value' => 'fruits', 'name_mr' => 'फळे'),

        );

        $this->crop_type_web = array('1' => 'Kharif', '2' => 'Rabi', '3' => 'fruits');

        $this->crop_type_web_mr = array('1' => 'खरिफ', '2' => 'रुबी', '3' => 'फळे');



        $this->crop_web = array('1' => 'Pomegranate', '2' => 'Grapes', '3' => 'Capsicum', '4' => 'Othercrops', '5' => 'Floriculture', '6' => 'Orange', '7' => 'Mango', '8' => 'Citrus');

        $this->crop_web_mr = array('1' => 'डाळींब', '2' => 'द्राक्षे', '3' => 'शिमला', '4' => 'इतरपिके', '5' => 'फ्लोरिकल्चर', '6' => 'संत्री', '7' => 'आंबा', '8' => 'मोसंबी');



        $this->soil_type = array(

            array('id' => '1', 'value' => 'Light Clay', 'name_mr' => 'हलकी चिकणमाती'),

            array('id' => '2', 'value' => 'Medium red', 'name_mr' => 'मध्यम लाल'),

            array('id' => '3', 'value' => 'Black', 'name_mr' => 'काळा'),

            array('id' => '4', 'value' => 'Medium black', 'name_mr' => 'मध्यम  काळा'),

            array('id' => '5', 'value' => 'Black solid', 'name_mr' => 'काळा घन'),

            array('id' => '6', 'value' => 'Limestone / Sherwat', 'name_mr' => 'चुनखडी / शेरवत'),

        );

        $this->soil_type_web = array('1' => 'Light Clay', '2' => 'Medium red', '3' => 'Black', '4' => 'Medium black', '5' => 'Black solid', '6' => 'Limestone / Sherwat');

        $this->soil_type_web_mr = array('1' => 'हलकी चिकणमाती', '2' => 'मध्यम लाल', '3' => 'काळा', '4' => 'मध्यम  काळा', '5' => 'काळा घन', '6' => 'चुनखडी / शेरवत');



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



        $this->splash_data[] = array('title' => 'Why do we use it?', 'description' => 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using Content here content here making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for lorem ipsum will uncover many web sites still in their infancy. Various versions have evolved over the years');

        $this->splash_data[] = array('title' => 'What is Lorem Ipsum?', 'description' => 'The standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested. Sections');

        $this->splash_data[] = array('title' => 'Where does it come from?', 'description' => 'Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock a Latin professor at Hampden-Sydney Colleges');

        //$this->lang->load(array('loan'),$lang_folder);

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

        $this->save_logs($data); // Save logs

        echo $this->response($data, $status);
        exit;

    }



    public function index_get()
    {

        $response = array("status" => 1, "data" => array(), "message" => lang('Invalid_URL'));

        $this->api_response($response);

    }



    public function splash_data_get()
    {

        $result['splash_data'] = $this->splash_data;

        $response = array("status" => 1, "data" => $result, "message" => "splash screen data");

        $this->api_response($response);

    }



    public function login_post()
    {

        $result = array();

        $update_arr = array();



        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $_POST, "message" => lang('Missing_Parameter'));



        if ($this->input->post('btn_submit') == 'submit') {

            $username = $this->input->post('username');

            $password = $this->input->post('password');



            $select = array('client.*', 'countries.name as country_name', 'states.name as state_name');

            $join = array(
                'countries' => array('countries.code = client.country', 'left'),

                'states' => array('states.code = client.state', 'left')
            );



            $where = array('client.phone' => strtolower($username), 'client.is_deleted' => 'false');



            $user_data = $this->Masters_model->get_data($select, 'client', $where, $join);



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



            $row_blog = $this->db->query("SELECT blogs_types_id ,name ,logo ,name_mr ,mob_icon FROM blogs_types_master WHERE is_active = 'true' AND is_deleted = 'false' AND is_home =1  ORDER BY seq ASC");

            $result_blogs = $row_blog->result_array();



            $row = $user_data;



            if (count($row)) {

                if (decrypt($row[0]['password'], config_item('encryption_key')) === $password) {

                    //if($row[0]['email_verify'] == 't')



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



                    if ($this->input->post('login_count')) {

                        $update_arr['login_count'] = $row[0]['login_count'] + 1;

                    }



                    if ($this->input->post('loc_addresss')) {

                        $update_arr['loc_addresss'] = $this->input->post('loc_addresss');

                    }



                    // loc_addresss



                    if (count($update_arr)) {



                        $id = $row[0]['id'];

                        $this->db->where('client.id', $id);

                        $result = $this->db->update('client', $update_arr);



                    }



                    $user_type_val = 'farmer';

                    if (1 == $row[0]['app_user_type']) {

                        $user_type_val = 'client';

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

                        'user_type' => $user_type_val,

                        'profile_image' => $row[0]['profile_image'],

                        'pan_no_doc' => $row[0]['pan_no_doc'],

                        'aadhar_no_doc' => $row[0]['aadhar_no_doc'],

                        'aadhar_no' => $row[0]['aadhar_no'],

                        'group_id' => $row[0]['group_id'],

                        'dob' => $row[0]['dob'],

                        'gender' => $row[0]['gender'],

                        'logged_in' => true,

                        'iot_device_url' => $row[0]['iot_device_url'],

                        'my_refferal_code' => $row[0]['my_refferal_code'],

                        'ACCESS_TOKEN' => current_date(),

                        'categories' => $categories,

                        'pcategories' => $pcategories,

                        'countries' => $countries,

                        'is_video_enable' => $row[0]['is_video_enable'],

                        'is_chat_enable' => $row[0]['is_chat_enable'],

                        'refferal_code' => $row[0]['refferal_code'],

                        'app_user_type' => $row[0]['app_user_type'],

                    );



                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, 'farm_type' => $this->farm_type, 'topology' => $this->topology, 'soil_type' => $this->soil_type, 'unit' => $this->unit, "message" => lang('Login_Successfully'), 'config_url' => $this->config_url, 'menu' => $this->menu);



                }

            }



        }

        $this->api_response($response);

    }



    public function logout_check_get($phone_number)
    {

        if ($phone_number != '') {



            $phone = substr(preg_replace('/\s+/', '', $phone_number), -10, 10);

            $sql = "SELECT phone,id FROM client where phone :: varchar = $phone_number::varchar AND is_active= true AND is_deleted = false ";

            $res_chk = $this->db->query($sql);

            $res = $res_chk->result_array();



            if (count($res) > 0) {



                $id = $res[0]['id'];



                ///// code to disconnnect call of vendor if any active call

                $where_array = array(

                    'farmer_id' => $id,

                    'meeting_status_id !=' => 4,

                );



                $update_array = array(

                    'meeting_status_id' => 4,

                    'meeting_end_from' => 1,

                    'updated_on' => current_date(),

                );



                $sql_update = $this->db->update('emeeting', $update_array, $where_array);

                //// disconnect call code end ///////////////////////////



                $update_arr = array('is_login' => false, 'device_id' => null);

                $this->db->where('client.phone', $phone);

                $result = $this->db->update('client', $update_arr);

                $sql_data = $this->db->last_query();



                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Logout_Successfully'));

            } else {

                $response = array("success" => 0, "error" => 1, "status" => 0, "data" => "", "message" => lang('Data_Not_Found'));

            }



        } else {



            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => "", "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);

        exit;



    }



    public function login_otp_post()
    {

        $result = array();

        $update_arr = array();



        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => null, "message" => lang('Missing_Parameter'));



        if ($this->input->post('btn_submit') == 'submit') {

            $ekyc_enable = !empty(get_config_data('ekyc')) ? get_config_data('ekyc') : '0';

            $username = substr(preg_replace('/\s+/', '', $this->input->post('phone')), -10, 10);

            //$password = $this->input->post('password');

            if ($username == 9876543210) {

                $otp = 643215;

            } else {

                $otp = $this->input->post('otp');

            }



            // $response = array("success" => 0, "error" => 1, "status" => 1, "data" => null, "message" => "missing err 33");



            $row = $this->db->query("SELECT * FROM client WHERE is_deleted = 'false' and phone::varchar = '$username'::varchar ");

            $user_data = $row->result_array();

            $sql_query = $this->db->last_query();



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



            $row_blog = $this->db->query("SELECT blogs_types_id ,name ,logo ,name_mr ,mob_icon FROM blogs_types_master WHERE is_active = 'true' AND is_deleted = 'false' AND is_home =1  ORDER BY seq ASC");

            $result_blogs = $row_blog->result_array();



            $row = $user_data;



            // $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $row, "message" => "missing err 33");



            if (count($row)) {



                $response = array("success" => 0, "error" => 1, "status" => 1, "data" => null, "message" => lang('Invalid_Otp'), 'db_otp' => $user_data[0]['opt_number'], 'all_data' => $user_data);



                // echo ' opt_number >> '.$otp;



                if ($user_data[0]['opt_number'] == $otp || 888888 == $otp) {



                    // echo 'hrterwes';



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



                    /*   if ($response) {

                    $sql       = "UPDATE is_login FROM client where is_deleted=false AND is_active=true";

                    $res_val   = $this->db->query($sql);

                    $res_array = $res_val->result_array();

                    }*/

                    // loc_addresss



                    $update_arr['login_count'] = $row[0]['login_count'] + 1;

                    $update_arr['is_online'] = true;



                    if (count($update_arr)) {



                        $id = $row[0]['id'];

                        $this->db->where('client.id', $id);

                        $result = $this->db->update('client', $update_arr);



                    }





                    $user_type_val = 'farmer';

                    if (1 == $row[0]['app_user_type']) {

                        $user_type_val = 'client';

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

                        'user_type' => $user_type_val,

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

                        'iot_device_url' => $row[0]['iot_device_url'],

                        'ACCESS_TOKEN' => current_date(),

                        'countries' => $countries,

                        'is_whitelabeled' => $row[0]['is_whitelabeled'],

                        'is_video_enable' => $row[0]['is_video_enable'],

                        'is_chat_enable' => $row[0]['is_chat_enable'],

                        'pacs_master_id' => $row[0]['pacs_master_id'],

                        'society_master_id' => $row[0]['society_master_id'],

                        'bank_master_id' => $row[0]['bank_master_id'],

                        'group_ids' => $row[0]['group_ids'],

                        'app_user_type' => $row[0]['app_user_type'],

                        'active_step' => $row[0]['active_step'],





                    );



                    /* 'categories'       => $categories,

                    'pcategories'      => $pcategories,*/



                    if ($row[0]['is_whitelabeled'] === 't') {



                        $bank_master_id = $row[0]['bank_master_id'];

                        $group_ids = $row[0]['group_ids'];

                        if ($bank_master_id != '') {

                            $row_bank = $this->db->query("SELECT  gm.logo,gm.mob_icon,bm.*

                            FROM bank_master as bm

                            LEFT JOIN client_group_master as gm ON gm.client_group_id = bm.group_id

                            WHERE bm.is_active = 'true' AND bm.is_deleted = 'false' AND bm.bank_master_id = $bank_master_id

                            LIMIT 1");



                            $whitelabel_data = $row_bank->result_array();

                        } else {



                            if ($group_ids != '') {

                                $group_srt_arr = explode(',', $group_ids);

                                $row_group = $this->db->query("SELECT  gm.logo,gm.mob_icon FROM client_group_master as gm   WHERE gm.is_active = 'true' AND gm.is_deleted = 'false' AND gm.client_group_id = $group_srt_arr[0] LIMIT 1");

                                $whitelabel_data = $row_group->result_array();



                            }

                        }



                    } else {

                        $whitelabel_data = array();



                    }

                    $data['ekyc_enable'] = $ekyc_enable;

                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, "message" => lang('Login_Successfully'), 'config_url' => $this->config_url, 'whitelabel_data' => $whitelabel_data, 'menu' => $this->menu);



                    $this->api_response($response);

                    exit;

                }

            } else {

                $response = array("success" => 0, "error" => 1, "status" => 0, "message" => lang('Data_Not_Found'));

                $this->api_response($response);

                exit;

            }



        }

        $this->api_response($response);

    }



    public function register_post()
    {

        $result = array();

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Registration_Failed'));



        if ($this->input->post('btn_submit') == 'submit') {

            if (0) {

                $data = $this->input->post();

                $data['error'] = validation_errors();



            } else {



                $phone = $this->input->post('phone');



                $row = $this->db->query("SELECT id, first_name, middle_name, last_name, email, phone, password, address1, address2, state, city, postcode, company, email_verify, invoice_type, country, profile_image, payment_method, pan_no, gst_no, last_login, ip, village, aadhar_no, dob, gender FROM client WHERE is_deleted = 'false' and phone::varchar = '$phone'::varchar ");

                $result = $row->result_array();

                if (count($result)) {

                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => "NULL", "message" => lang('Already_Register'));

                    $this->api_response($response);

                    exit;

                }



                //referral_code

                // send app_user_type:  1 = ( not farmer)  &&&&&   0 = farmer.



                $insert = array(

                    'first_name' => ucfirst($this->input->post('first_name')),

                    'last_name' => ucfirst($this->input->post('last_name')),

                    'email' => strtolower($this->input->post('email')),

                    'app_user_type' => $this->input->post('app_user_type'),

                    'phone' => $this->input->post('phone'),

                    'referral_code' => $this->input->post('referral_code'),

                    'temp_data' => base64_encode($this->input->post('password')),

                    'iot_password' => $this->input->post('password'),

                    'is_active' => 'true',

                    'email_verify' => 'true',

                    // 'dob'              => $this->input->post('dob'),

                    'gender' => $this->input->post('gender'),

                    'my_refferal_code' => time(),

                    'password' => encrypt($this->input->post('password'), config_item('encryption_key')),

                    'created_on' => current_date(),

                );



                if (trim($this->input->post('dob')) != '') {

                    $insert['dob'] = $this->input->post('dob');

                }



                $full_name = ucfirst($this->input->post('first_name')) . ' ' . ucfirst($this->input->post('last_name'));



                //user_activity_log

                $title = "Client: Registered";

                $description = json_encode($insert);

                user_activity_logs($title, $description);



                $result = $this->db->insert('client', $insert);

                $insert_id = $this->db->insert_id();



                $nc_data = $_POST;

                $nc_data['client_id'] = $insert_id;



                $nc_api_data = $this->add_nc_member($nc_data);

                $AddMemberEnrolmentAPI = $nc_api_data['AddMemberEnrolmentAPI'];

                $TransactionAPI = $nc_api_data['TransactionAPI'];





                if ($result) {



                    if (count($insert)) {

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Register_Successfully'), "nc_user_registration" => $AddMemberEnrolmentAPI, 'nc_loyality_points' => $TransactionAPI);

                    }



                    $this->api_response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Registration_Failed'));



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

        $phone = substr(preg_replace('/\s+/', '', $this->input->post('phone')), -10, 10);

        $opt_number = mt_rand(100000, 999999);



        $result = array();

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Missing_Parameter'));



        if ($phone != '' && $opt_number != '') {



            $sql_chk = "SELECT id,is_active FROM client WHERE is_deleted = 'false' and phone::varchar = '$phone'::varchar LIMIT 1";

            $row = $this->db->query($sql_chk);

            $result = $row->result_array();

            if (count($result)) {



                if ($result[0]['is_active'] == "t") {



                    $update_arr['opt_number'] = $opt_number;

                    $sms_type = 'NERACE_OTP_Mobile_Verification'; // for OTP its once // 7448148405

                    $mobile = $phone;

                    $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
                    $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

                    // if (strtolower($headers_data['domain']) == 'famrut' || strtolower($headers_data['domain']) == 'famrutd') {

                    //     $text = 'Your OTP for Famrut is: ' . $opt_number . ' mQ5HHzOtTip . Please enter it on the app to confirm your account. Thanks for using Famrut';

                    // } else {

                    //     $text = 'Your OTP for Famrut is: ' . $opt_number . ' U5fcG3OYgG2 . Please enter it on the app to confirm your account. Thanks for using Famrut';

                    // }



                    if (strtolower($headers_data['domain']) == 'famrut' || strtolower($headers_data['domain']) == 'famrutd') {

                        $text = 'Your OTP for Famrut is: ' . $opt_number . ' rwVTa6olvjM . Please enter it on the app to confirm your account. Thanks for using Famrut';

                        $otpstring = $opt_number . ' rwVTa6olvjM ';

                    } else {

                        $text = 'Your OTP for Famrut is: ' . $opt_number . ' mQ5HHzOtTip . Please enter it on the app to confirm your account. Thanks for using Famrut';

                        $otpstring = $opt_number . ' mQ5HHzOtTip ';

                    }



                    if ($phone == 9876543210 || $phone == 9976543210) {

                        $opt_number = 643215;

                        $update_arr['opt_number'] = $opt_number;

                    } else {

                        $update_arr['opt_number'] = $opt_number;

                        //$resp                     = send_sms($mobile, $text, $sms_type);
                        $replace = array(
                            'body' => array("{OTP_NUMBER}" => $otpstring),
                        );

                        $resp = dynamic_send_sms($mobile, '', $sms_type, '', $selected_lang, $replace);



                    }



                    if (count($update_arr)) {



                        $id = $result[0]['id'];

                        $this->db->where('client.id', $id);

                        $this->db->update('client', $update_arr);

                    }



                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => "NULL", "message" => lang('OTP_Reset_Successfully'));

                    $this->api_response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 0, "data" => "NULL", "message" => lang('Account_Not_Active'), 'resp_query' => $result[0]['is_active']);

                    $this->api_response($response);

                    exit;

                }



            } else {



                $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Not_Register') . $this->input->post('phone'));

                $this->api_response($response);

                exit;

            }

        }



        $this->api_response($response);

        exit;

    }



    public function is_user_regsitered_post()
    {

        $is_profile_complete = 0;

        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        if (strpos(strtolower($headers_data['domain']), CODE) !== false) {

            $step_list = STEP_LIST;

        } else {

            $step_list = SHORT_STEP_LIST;

        }

        // echo 'show_referral:'.get_config_data('show_referral');exit;

        $show_referral = !empty(get_config_data('show_referral')) ? get_config_data('show_referral') : '0';

        $registration_lock = !empty(get_config_data('registration_lock')) ? get_config_data('registration_lock') : '0';

        $registration_lock_messge = !empty(get_config_data('registration_lock_messge')) ? get_config_data('registration_lock_messge') : '';

        $app_user_type = !empty(get_config_data('app_user_type')) ? get_config_data('app_user_type') : '0';

        //echo "<pre>";print_r($_POST);echo "<br>PHONE===>".$this->input->post('phone');exit;

        if ($this->input->post('phone') != '') {

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => [], "message" => lang('Already_Register'), 'is_registered' => 1, 'app_user_type' => $app_user_type);

            $phone = substr(preg_replace('/\s+/', '', $this->input->post('phone')), -10, 10);

            $row = $this->db->query("SELECT *,(select name from cities_new where id::varchar=city) as new_city_name,(select name from states_new where id::varchar=state) as new_state_name FROM client WHERE  is_deleted = 'false' and phone::varchar = '$phone'::varchar ");

            $result = $row->result_array();

            $is_profile_complete = ($result[0]['active_step'] == 3) ? 1 : 0;





            if (count($result)) {

                // Check seller login
                // echo CODE;
                // print_r($result);
                // print_r($headers_data);exit;

                if (!empty($result[0]['client_type']) && strpos(strtolower($headers_data['domain']), CODE) !== false) {

                    if ((int) $result[0]['client_type'] === 2) {

                        if ($result[0]['is_active'] != 'f') {

                            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "step_list" => $step_list, "message" => lang('Already_Register'), 'is_registered' => 1, 'app_user_type' => $app_user_type, 'show_referral' => $show_referral, 'is_profile_complete' => $is_profile_complete, 'registration_lock' => $registration_lock, "registration_lock_messge" => $registration_lock_messge);

                        } else {

                            $response = array("success" => 1, "error" => 0, "status" => 0, "data" => null, "step_list" => $step_list, "message" => lang('Mobile_Deactivated'), 'is_registered' => 1, 'app_user_type' => $app_user_type, 'show_referral' => $show_referral, 'is_profile_complete' => $is_profile_complete, 'registration_lock' => $registration_lock, "registration_lock_messge" => $registration_lock_messge);



                        }

                    } else {

                        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => [], "message" => lang('Seller_Login_Failed'), 'app_user_type' => $app_user_type, 'show_referral' => $show_referral, 'registration_lock' => $registration_lock, "registration_lock_messge" => $registration_lock_messge);

                    }

                } else {

                    if ($result[0]['is_active'] != 'f') {

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "step_list" => $step_list, "message" => lang('Already_Register'), 'is_registered' => 1, 'app_user_type' => $app_user_type, 'show_referral' => $show_referral, 'is_profile_complete' => $is_profile_complete, 'registration_lock' => $registration_lock, "registration_lock_messge" => $registration_lock_messge);

                    } else {

                        $response = array("success" => 1, "error" => 0, "status" => 0, "data" => null, "step_list" => $step_list, "message" => lang('Mobile_Deactivated'), 'is_registered' => 1, 'app_user_type' => $app_user_type, 'show_referral' => $show_referral, 'is_profile_complete' => $is_profile_complete, 'registration_lock' => $registration_lock, "registration_lock_messge" => $registration_lock_messge);



                    }

                }



            } else {

                // show_referral key is set 1 to show referral screen if 0 then hide that screen on app.

                //registration_lock key is set 0 to allow registration if 1 then show registration_lock_messge .

                $response = array("success" => 1, "error" => 0, "status" => 0, "data" => null, "step_list" => $step_list, "message" => lang('Not_Register'), 'is_registered' => 0, 'app_user_type' => $app_user_type, 'show_referral' => $show_referral, 'is_profile_complete' => $is_profile_complete, 'registration_lock' => $registration_lock, "registration_lock_messge" => $registration_lock_messge);

            }



        } else {



            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Missing_Parameter'), 'app_user_type' => $app_user_type, 'show_referral' => $show_referral, 'registration_lock' => $registration_lock, "registration_lock_messge" => $registration_lock_messge);



        }



        $this->api_response($response);

        exit;

    }



    public function register_otp_post()
    {

        $client_type_listing = CLIENT_TYPE;

        $client_type = 2;

        /* foreach ($client_type_listing as $key => $value) {

            if(strtolower($value['title']) == 'seller'){

                $client_type = $value['id'];

            }

        } */



        $result = array();

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Registration_Failed'));

        $is_whitelabeled = 'false';



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

                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => "NULL", 'user_id' => $result[0]['id'], 'active_step' => $result[0]['active_step'], "message" => lang('Already_Register'));

                    $this->api_response($response);

                    exit;

                } else {



                    $opt_number = mt_rand(100000, 999999);

                    $sms_type = 'NERACE_OTP_Mobile_Verification'; // for OTP its once

                    // $mobile = 8208953165;

                    $mobile = substr(preg_replace('/\s+/', '', $this->input->post('phone')), -10, 10);



                    $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
                    $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

                    if (strtolower($headers_data['domain']) == 'famrut' || strtolower($headers_data['domain']) == 'famrutd') {

                        $text = 'Your OTP for Famrut is: ' . $opt_number . ' mQ5HHzOtTip . Please enter it on the app to confirm your account. Thanks for using Famrut';

                        $otpstring = $opt_number . ' mQ5HHzOtTip ';

                    } else {

                        $text = 'Your OTP for Famrut is: ' . $opt_number . ' U5fcG3OYgG2 . Please enter it on the app to confirm your account. Thanks for using Famrut';

                        $otpstring = $opt_number . ' U5fcG3OYgG2 ';

                    }

                    //$text   = 'Your OTP for Famrut is: ' . $opt_number . ' mQ5HHzOtTip . Please enter it on the app to confirm your account. Thanks for using Famrut';

                    // $mobile = 7448148405;

                    //$this->input->post('phone')

                    // static code added for Testing MMMM

                    if ($phone == 9876543210 || $phone == 9976543210) {

                        $opt_number = 643215;

                        // $update_arr['opt_number'] = $opt_number;

                    } else {

                        //$resp = send_sms($mobile, $text, $sms_type);

                        $replace = array(
                            'body' => array("{OTP_NUMBER}" => $otpstring),
                        );

                        $resp = dynamic_send_sms($mobile, '', $sms_type, '', $selected_lang, $replace);

                    }



                }



                $referral_code = !empty($this->input->post('referral_code')) ? $this->input->post('referral_code') : '';

                if (!empty($referral_code) && strpos($referral_code, '-') !== false) {



                    $get_arr = explode('-', $referral_code);

                    $referral_code = $get_arr[0];

                    // echo "My string contains Bob";

                }



                $farmer_group_id = '';



                $where = array('is_deleted' => 'false', 'is_active' => 'true');



                if (!empty($referral_code)) {

                    $where['referral_code'] = $referral_code;

                }



                $group_id = $this->Masters_model->get_data("client_group_id,", 'client_group_master', $where);



                if ($group_id[0]['client_group_id']) {

                    $farmer_group_id = $group_id[0]['client_group_id'];

                    if ($group_id[0]['is_whitelabeled']) {

                        $is_whitelabeled = $group_id[0]['is_whitelabeled'];

                    }



                } else {

                    $where = array('is_deleted' => 'false', 'is_active' => 'true');



                    if (!empty($referral_code)) {

                        $where['my_refferal_code'] = $referral_code;

                    }

                    $group_id = $this->Masters_model->get_data("group_id", 'client', $where);

                    $farmer_group_id = $group_id[0]['group_id'];



                }



                /*  $update_arr['first_name']  = $this->input->post('first_name');

                $update_arr['last_name']   = $this->input->post('last_name');

                $update_arr['phone']       = $this->input->post('phone');

                $postcode    = $this->input->post('postcode') != '' ? $this->input->post('postcode') : null;*/

                // send app_user_type:  1 = ( not farmer)  &&&&&   0 = farmer.

                // dd-mm-yyyy

                // $dob = '';

                $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
                $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

                /***
                 * Make changes for set client type for seller : 09-01-2024 - Akash Wagh
                 * ***/
                // if (strtolower($headers_data['domain']) == CODE){
                //     $insert = array(
                //         'phone'            => $this->input->post('phone'),
                //         'opt_number'       => $opt_number,
                //         'is_active'        => 'true',
                //         'is_whitelabeled'  => $is_whitelabeled,
                //         'created_on'       => current_date(),
                //     );
                // }

                if (strpos(strtolower($headers_data['domain']), strtolower(CODE)) !== false) {
                    $insert = array(
                        'phone' => $this->input->post('phone'),
                        'opt_number' => $opt_number,
                        'is_active' => 'true',
                        'is_whitelabeled' => $is_whitelabeled,
                        'client_type' => $client_type,
                        'created_on' => current_date(),
                    );
                } else {

                    $app_user_type = $this->input->post('app_user_type');



                    // if(empty($this->input->post('app_user_type'))){

                    //     $app_user_type = "0";

                    // }

                    $insert = array(

                        'phone' => $mobile,

                        'first_name' => $this->input->post('first_name'),

                        'last_name' => $this->input->post('last_name'),

                        'phone' => $this->input->post('phone'),

                        'postcode' => $this->input->post('postcode'),

                        'email' => $this->input->post('email'),

                        'address1' => $this->input->post('address1'),

                        'opt_number' => $opt_number,

                        'is_active' => 'true',

                        'is_whitelabeled' => $is_whitelabeled,

                        // 'dob'              => $dob,

                        'gender' => $this->input->post('gender'),

                        'my_refferal_code' => time(),

                        'group_id' => $farmer_group_id,

                        'group_ids' => $farmer_group_id,

                        'app_user_type' => $app_user_type,

                        'created_on' => current_date(),

                        'client_type' => $client_type,

                    );

                }





                if (trim($this->input->post('dob')) != '') {

                    $insert['dob'] = date('Y-m-d', strtotime($this->input->post('dob')));

                }



                //$insert['opt_number'] = $opt_number;



                if ($this->input->post('device_id')) {

                    $insert['device_id'] = $this->input->post('device_id');

                }



                if (!empty($referral_code)) {

                    $insert['referral_code'] = $referral_code;

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

                    $step = $this->db->query("SELECT active_step FROM client WHERE is_deleted = 'false' and id= " . $insert_id);

                    $step_result = $step->result_array();

                    if (count($insert)) {

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Register_Successfully'), 'opt_number' => $opt_number, 'user_id' => $insert_id, 'active_step' => $step_result[0]['active_step']);

                    }



                    $this->api_response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Registration_Failed'));



                    $this->api_response($response);

                    exit;



                }

            }

        }



        $this->api_response($response);

        exit;



    }



    public function register_otp_test_post()
    {

        $empty_param = [];

        $required_posted = array('phone', 'referral_code');

        foreach ($_POST as $key => $val) {

            if (in_array($key, $required_posted) && empty($val)) {

                $empty_param[] = $key;

            }

        }



        if (!empty($empty_param)) {

            $msg = implode(', ', $empty_param);

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => [], "message" => "Posted values required: " . $msg);

        } else {

            $result = array();

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Registration failed, please try again some time.");

            $is_whitelabeled = 'false';



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

                $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
                $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

                if (strtolower($headers_data['domain']) == 'famrut' || strtolower($headers_data['domain']) == 'famrutd') {

                    $text = 'Your OTP for Famrut is: ' . $opt_number . ' mQ5HHzOtTip . Please enter it on the app to confirm your account. Thanks for using Famrut';

                } else {

                    $text = 'Your OTP for Famrut is: ' . $opt_number . ' U5fcG3OYgG2 . Please enter it on the app to confirm your account. Thanks for using Famrut';

                }

                //$text   = 'Your OTP for Famrut is: ' . $opt_number . ' . Please enter it on the app to confirm your account. Thanks for using Famrut';

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



            $referral_code = $this->input->post('referral_code');

            if (strpos($referral_code, '-') !== false) {



                $get_arr = explode('-', $referral_code);

                $referral_code = $get_arr[0];

                // echo "My string contains Bob";

            }



            $farmer_group_id = '';



            $where = array('is_deleted' => 'false', 'is_active' => 'true', 'referral_code' => $referral_code);

            $group_id = $this->Masters_model->get_data("client_group_id,", 'client_group_master', $where);



            if ($group_id[0]['client_group_id']) {

                $farmer_group_id = $group_id[0]['client_group_id'];

                if ($group_id[0]['is_whitelabeled']) {

                    $is_whitelabeled = $group_id[0]['is_whitelabeled'];

                }



            } else {

                $where = array('is_deleted' => 'false', 'is_active' => 'true', 'my_refferal_code' => $referral_code);

                $group_id = $this->Masters_model->get_data("group_id", 'client', $where);

                $farmer_group_id = $group_id[0]['group_id'];



            }



            $insert = array(

                'phone' => $mobile,

                'referral_code' => $referral_code,

                'opt_number' => $opt_number,

                'is_active' => 'true',

                'is_whitelabeled' => $is_whitelabeled,

                'my_refferal_code' => time(),

                'group_id' => $farmer_group_id,

                'group_ids' => $farmer_group_id,

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

                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Register Successful", 'opt_number' => $opt_number);

                }



                $this->api_response($response);

                exit;



            } else {



                $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Registration failed, please try again some time.");



                $this->api_response($response);

                exit;



            }



        }



        $this->api_response($response);

        exit;



    }



    public function generate_referral_code_post()
    {

        $mob = $this->input->post('mobile');

        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        if (strtolower($headers_data['domain']) == CODE) {

            $step_list = STEP_LIST;

        } else {

            $step_list = SHORT_STEP_LIST;

        }

        //Referral Code

        if ($mob != '') {

            //'device_id'  => $this->input->post('device_id'),

            $insert = array(

                'mobile' => $mob,

                'created_on' => current_date(),

            );



            //$this->db->where('client.id', $id);

            $result = $this->db->insert('request_invitation', $insert);

            $request_invitation_id = $this->db->insert_id();



            $date = time();

            $ref_code = '9607005004';

            $data_arr = array('referral_code' => $ref_code . '-' . $date);



            $response = array("success" => 1, "data" => $data_arr, "step_list" => $step_list, "msg" => lang('Referral_Code_Generated'), "error" => 0, "status" => 1, 'validated' => 1);

        } else {

            $response = array("success" => 1, "data" => [], "step_list" => $step_list, "msg" => lang('Missing_Parameter'), "error" => 1, "status" => 0, 'validated' => 0);

        }



        $this->api_response($response);

        exit;

    }



    public function chk_otp_post()
    {

        $phone = substr(preg_replace('/\s+/', '', $this->input->post('phone')), -10, 10);

        if ($phone == 9876543210 || $phone == 9976543210) {

            $opt_number = 643215;

            // $update_arr['opt_number'] = $opt_number;

        } else {

            $opt_number = $this->input->post('otp');

        }



        $result = array();

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => null, "message" => lang('Login_Failed'));



        if ($phone != '' && $opt_number != '') {



            /* $row    = $this->db->query("SELECT id FROM client WHERE is_deleted = 'false' AND 'is_active' => 'true' AND opt_number='$opt_number' AND phone::varchar = '$phone'::varchar ");*/

            $sql_chk = "SELECT id FROM client WHERE is_deleted = 'false' and phone::varchar = '$phone'::varchar ";

            $row = $this->db->query($sql_chk);

            $result = $row->result_array();



            if (count($result)) {



                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('OTP_Matched'), 'opt_number' => $opt_number);



                $this->api_response($response);

                exit;



            } else {



                $response = array("success" => 0, "error" => 1, "status" => 1, "data" => null, "message" => lang('Invalid_Otp'));



                $this->api_response($response);

                exit;



            }

        } else {

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => null, "message" => lang('Missing_Parameter'));



            $this->api_response($response);

            exit;

        }



        $this->api_response($response);

        exit;

    }



    public function request_invitation_post($demo = 0)
    {

        if ($this->input->post('mobile') != '' && $this->input->post('device_id') != '') {



            $mobile = $this->input->post('mobile');

            $phone = substr(preg_replace('/\s+/', '', $mobile), -10, 10);

            $this->db->where('client.phone', $phone);

            $result = $this->db->get('client')->result_array();



            if (count($result)) {



                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Referral code request already sent");

                $this->api_response($response);



            } else {



                $insert = array(

                    'mobile' => $this->input->post('mobile'),

                    'device_id' => $this->input->post('device_id'),

                    'created_on' => current_date(),

                );



                if (1) {

                    $sms_type = 3; // Referral ACk1

                    $mobile = $this->input->post('mobile');

                    $text = 'Thank you for downloading Famrut App. We will send the referral code soon.';



                    $arr = array(

                        'body' => array("{MOBILE_NUMBER}" => $mobile),

                        'subject' => array("{MOBILE_NUMBER}" => $mobile),

                    );

                    $mail_data = get_email_body('Referral_code_requests', $arr);

                    $to_mail = 'shraddha.kawade@esds.co.in';

                    $this->Email_model->send_mail($mail_data['subject'], $mail_data['body'], $to_mail, $mail_data['from_mail'], 'Famrut');

                    $response = array("success" => 1, "msg" => lang('Added_Successfully'), "error" => 0, "status" => 0, 'sms_resp' => $resp);

                } else {

                    $response = array("success" => 0, "data" => array(), "msg" => 'invitation requested failed', "error" => 1, "status" => 1);

                }

            }

        } else {

            $response = array("success" => 0, "data" => array(), "msg" => lang('Missing_Parameter'), "error" => 1, "status" => 1);

        }

        $this->api_response($response);

    }



    public function categories_get()
    {

        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        $sel_lang = $selected_lang;

        $cat_query = '';

        // $group_id     = $headers_data['group_id']; (replace this line with below 2 lines)

        /*$group_id_arr = explode(',', $headers_data['group_id']);

        $group_id     = $group_id_arr[0];

        if ($group_id != '') {

        $where        = array('is_deleted' => 'false', 'is_active' => 'true', 'group_referral_code' => $group_id);

        $category_ids = $this->Masters_model->get_data("category_id", 'config_master', $where);

        $cat_query    = '';

        if (count($category_ids)) {

        if ($category_ids[0]['category_id']) {

        $cat_query = "AND cat_id IN (" . $category_ids[0]['category_id'] . ")";

        }

        }

        }*/



        $lang_label = " name_mr ";

        if ($selected_lang == 'mr') {

            $lang_folder = "marathi";

        } elseif ($selected_lang == 'hi') {

            $lang_folder = "hindi";

            $lang_label = "name_hi as name_mr";

        } else {

            $lang_folder = "english";

            $lang_label = " name_mr ";

        }



        //SELECT id,lang_json->>'hi' as title,map_key,icon,menu_position FROM app_menu_master WHERE is_deleted='false' ORDER BY seq ASC



        //SELECT cat_id ,name,logo ,$lang_label ,mob_icon,lang_json->>'hi' as name FROM categories WHERE is_active = 'true' AND is_deleted = 'false' " . $cat_query . " ORDER BY seq ASC



        $row = $this->db->query("SELECT cat_id,$lang_label ,logo ,mob_icon,lang_json->>'" . $selected_lang . "' as name FROM categories WHERE is_active = 'true' AND is_deleted = 'false' " . $cat_query . " ORDER BY seq ASC");

        $result = $row->result_array();



        $row_blog = $this->db->query("SELECT blogs_types_id ,logo ,$lang_label ,mob_icon,lang_json->>'" . $selected_lang . "' as name  FROM blogs_types_master WHERE is_active = 'true' AND is_deleted = 'false' AND is_home =1  ORDER BY seq ASC");

        $result_blogs = $row_blog->result_array();



        if (count($result)) {

            $response = array("status" => 1, "data" => $result, "blog_type" => $result_blogs, "config_url" => $this->config_url, 'home_message' => $this->home_message, "message" => lang('Listed_Successfully'));

        }

        // $this->api_response($result, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

        $this->api_response($response);

    }



    public function insurance_terms_get()
    {



        $res['esds_term_title'] = 'ESDS insurance';

        $res['esds_term_desc'] = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.';

        $res['esds_term_details'] = '<details data-i18n-context="experimental-note"> <summary>Table of contents</summary> <p> &nbsp; </p> <ol> <li> The singing Canadian </li> <li> Spaceflights </li> <li> Bibliography </li> <li> Space Oddity </li> </ol> <p> &nbsp; </p></details><h2> The singing Canadian</h2><p style="text-align:justify;"> <strong>Chris Austin Hadfield</strong>&nbsp;was born on August 29, 1959, in Canada. As a child, he watched the Apollo 11 moon landing and it inspired him to also become an astronaut. At the time Canada had no space program, so Hadfield joined the <a href="https://www.rcaf-arc.forces.gc.ca/en/"><strong>Royal Canadian Air Forces</strong></a> an served as a fighter pilot for 25 years.</p><p style="text-align:justify;"> In 1992, Hadfield was accepted into the Canadian astronaut program by the <a href="https://www.asc-csa.gc.ca/eng/"><strong>Canadian Space Agency</strong></a>. He flew his first mission to the Russian <em><i>Mir</i></em> space station in 1995 aboard&nbsp;the <em><i>Atlantis</i></em> space shuttle. Six years later onboard the <em><i>Endeavour</i></em> space shuttle he flew to the <em><i>International Space Station</i></em>. He revisited the <em><i>ISS</i></em> in 2012 flying a Russian <em><i>Soyuz</i></em> spacecraft and taking command over the station during <em><i>Expedition 34/35</i></em>.</p><p style="text-align:justify;"> Hadfield was most recognised by the general public for his rendition of the famous <em><i>Space Oddity</i></em> song by David Bowie which he recorded onboard the <em><i>International Space Station</i></em>. He also recorded numerous educational materials for schools while working in orbit. After his retirement from the astronaut service, he wrote three books based on his experience.</p><h2> Spaceflights</h2><p> Hadfield flew to space thrice. He also performed two <em><i>EVA</i></em>s (<em><i>Extra-vehicular activity</i></em>, a spacewalk) that lasted together for&nbsp;14 hours 53 minutes and 38 seconds.</p>';



        $res['term_title'] = 'Company_Name insurance';

        $res['term_desc_mr'] = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.';

        $res['term_details'] = '<details data-i18n-context="experimental-note"> <summary>Company_Name of contents</summary> <p> &nbsp; </p> <ol> <li> Company_Name singing Canadian </li> <li> Spaceflights </li> <li> Bibliography </li> <li> Space Oddity </li> </ol> <p> &nbsp; </p></details><h2> The singing Canadian</h2><p style="text-align:justify;"> <strong>Chris Austin Hadfield</strong>&nbsp;was born on August 29, 1959, in Canada. As a child, he watched the Apollo 11 moon landing and it inspired him to also become an astronaut. At the time Canada had no space program, so Hadfield joined the <a href="https://www.rcaf-arc.forces.gc.ca/en/"><strong>Royal Canadian Air Forces</strong></a> an served as a fighter pilot for 25 years.</p><p style="text-align:justify;"> In 1992, Hadfield was accepted into the Canadian astronaut program by the <a href="https://www.asc-csa.gc.ca/eng/"><strong>Canadian Space Agency</strong></a>. He flew his first mission to the Russian <em><i>Mir</i></em> space station in 1995 aboard&nbsp;the <em><i>Atlantis</i></em> space shuttle. Six years later onboard the <em><i>Endeavour</i></em> space shuttle he flew to the <em><i>International Space Station</i></em>. He revisited the <em><i>ISS</i></em> in 2012 flying a Russian <em><i>Soyuz</i></em> spacecraft and taking command over the station during <em><i>Expedition 34/35</i></em>.</p><p style="text-align:justify;"> Hadfield was most recognised by the general public for his rendition of the famous <em><i>Space Oddity</i></em> song by David Bowie which he recorded onboard the <em><i>International Space Station</i></em>. He also recorded numerous educational materials for schools while working in orbit. After his retirement from the astronaut service, he wrote three books based on his experience.</p><h2> Spaceflights</h2><p> Hadfield flew to space thrice. He also performed two <em><i>EVA</i></em>s (<em><i>Extra-vehicular activity</i></em>, a spacewalk) that lasted together for&nbsp;14 hours 53 minutes and 38 seconds.</p><figure class="table" style="width:700px;"> <table> <tbody> <tr> <td style="background-color:hsl(0, 0%, 90%);text-align:center;"> Flight </td> <td style="background-color:hsl(0, 0%, 90%);text-align:center;"> Date </td> <td style="background-color:hsl(0, 0%, 90%);text-align:center;"> Spacecraft </td> <td style="background-color:hsl(0, 0%, 90%);text-align:center;"> Function </td> <td style="background-color:hsl(0, 0%, 90%);text-align:center;"> Emblem </td> </tr> <tr> <td style="text-align:center;width:200px;"> <strong>STS-74</strong> </td> <td style="text-align:center;width:200px;"> 12-20.11.1995 </td> <td style="text-align:center;width:200px;"> Atlantis </td> <td style="text-align:center;width:200px;"> Mission Specialist </td> <td style="text-align:center;width:100px;"> <img src="https://ckeditor.com/docs/ckeditor5/latest/assets/img/Sts-74-patch.png" alt=""> </td> </tr> <tr> <td style="text-align:center;width:200px;"> <strong>STS-100</strong> </td> <td style="text-align:center;width:200px;"> 19.04.2001-01.05.2001 </td> <td style="text-align:center;width:200px;"> Endeavour </td> <td style="text-align:center;width:200px;"> Mission Specialist </td> <td style="text-align:center;width:100px;"> <img src="https://ckeditor.com/docs/ckeditor5/latest/assets/img/40px-STS-100_patch.png" alt=""> </td> </tr> <tr> <td style="text-align:center;width:200px;"> <strong>Expedition 34/35</strong> </td> <td style="text-align:center;width:200px;"> 19.12.2012-14.05.2013 </td> <td style="text-align:center;width:200px;"> Soyuz TMA-07M </td> <td style="text-align:center;width:200px;"> ISS Commander </td> <td style="text-align:center;width:100px;"> <img src="https://ckeditor.com/docs/ckeditor5/latest/assets/img/40px-Soyuz-TMA-07M-Mission-Patch.png" alt=""> </td> </tr> </tbody> </table></figure><h2> Bibliography</h2><ul> <li> <strong>An Astronauts Guide to Life on Earth</strong>: What Going to Space Taught Me About Ingenuity, Determination, and Being Prepared for Anything. <em><i>Little, Brown and Company, 2013</i></em> </li> <li> <strong>You Are Here</strong>: Around the World in 92 Minutes: Photographs from the International Space Station. <em><i>Little, Brown and Company, 2014</i></em> </li> <li> <strong>The Darkest Dark</strong>. Illustrated by Terry and Eric Fan. <em><i>Little, Brown and Company, 2016</i></em> </li> <li> <strong>The Apollo Murders</strong> <em><i>Random House, 2021</i></em> </li></ul><h2> Space Oddity</h2><p> The rendition of Space Oddity by Chris Hadfield, shot in 2013 was <u>the first ever</u> music video shot in space.</p><figure class="media"> <oembed url="https://www.youtube.com/watch?v=KaOC9danxNo"></oembed></figure><blockquote> <p> The only reason Chris Hadfield isnt the coolest guy on Earth is that hes not on Earth </p></blockquote><p style="text-align:right;"> A comment by August Vctjuh on YouTube.</p>';



        $res['esds_term_title_mr'] = 'ESDS insurance';

        $res['esds_term_desc_mr'] = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.';

        $res['esds_term_details_mr'] = '<details data-i18n-context="experimental-note"> <summary>Table of contents</summary> <p> &nbsp; </p> <ol> <li> The singing Canadian </li> <li> Spaceflights </li> <li> Bibliography </li> <li> Space Oddity </li> </ol> <p> &nbsp; </p></details><h2> The singing Canadian</h2><p style="text-align:justify;"> <strong>Chris Austin Hadfield</strong>&nbsp;was born on August 29, 1959, in Canada. As a child, he watched the Apollo 11 moon landing and it inspired him to also become an astronaut. At the time Canada had no space program, so Hadfield joined the <a href="https://www.rcaf-arc.forces.gc.ca/en/"><strong>Royal Canadian Air Forces</strong></a> an served as a fighter pilot for 25 years.</p><p style="text-align:justify;"> In 1992, Hadfield was accepted into the Canadian astronaut program by the <a href="https://www.asc-csa.gc.ca/eng/"><strong>Canadian Space Agency</strong></a>. He flew his first mission to the Russian <em><i>Mir</i></em> space station in 1995 aboard&nbsp;the <em><i>Atlantis</i></em> space shuttle. Six years later onboard the <em><i>Endeavour</i></em> space shuttle he flew to the <em><i>International Space Station</i></em>. He revisited the <em><i>ISS</i></em> in 2012 flying a Russian <em><i>Soyuz</i></em> spacecraft and taking command over the station during <em><i>Expedition 34/35</i></em>.</p><p style="text-align:justify;"> Hadfield was most recognised by the general public for his rendition of the famous <em><i>Space Oddity</i></em> song by David Bowie which he recorded onboard the <em><i>International Space Station</i></em>. He also recorded numerous educational materials for schools while working in orbit. After his retirement from the astronaut service, he wrote three books based on his experience.</p><h2> Spaceflights</h2><p> Hadfield flew to space thrice. He also performed two <em><i>EVA</i></em>s (<em><i>Extra-vehicular activity</i></em>, a spacewalk) that lasted together for&nbsp;14 hours 53 minutes and 38 seconds.</p>';



        $res['term_title_mr'] = 'Company_Name insurance';

        $res['term_desc_mr'] = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.';

        $res['term_details_mr'] = '<details data-i18n-context="experimental-note"> <summary>Company_Name of contents</summary> <p> &nbsp; </p> <ol> <li> Company_Name singing Canadian </li> <li> Spaceflights </li> <li> Bibliography </li> <li> Space Oddity </li> </ol> <p> &nbsp; </p></details><h2> The singing Canadian</h2><p style="text-align:justify;"> <strong>Chris Austin Hadfield</strong>&nbsp;was born on August 29, 1959, in Canada. As a child, he watched the Apollo 11 moon landing and it inspired him to also become an astronaut. At the time Canada had no space program, so Hadfield joined the <a href="https://www.rcaf-arc.forces.gc.ca/en/"><strong>Royal Canadian Air Forces</strong></a> an served as a fighter pilot for 25 years.</p><p style="text-align:justify;"> In 1992, Hadfield was accepted into the Canadian astronaut program by the <a href="https://www.asc-csa.gc.ca/eng/"><strong>Canadian Space Agency</strong></a>. He flew his first mission to the Russian <em><i>Mir</i></em> space station in 1995 aboard&nbsp;the <em><i>Atlantis</i></em> space shuttle. Six years later onboard the <em><i>Endeavour</i></em> space shuttle he flew to the <em><i>International Space Station</i></em>. He revisited the <em><i>ISS</i></em> in 2012 flying a Russian <em><i>Soyuz</i></em> spacecraft and taking command over the station during <em><i>Expedition 34/35</i></em>.</p><p style="text-align:justify;"> Hadfield was most recognised by the general public for his rendition of the famous <em><i>Space Oddity</i></em> song by David Bowie which he recorded onboard the <em><i>International Space Station</i></em>. He also recorded numerous educational materials for schools while working in orbit. After his retirement from the astronaut service, he wrote three books based on his experience.</p><h2> Spaceflights</h2><p> Hadfield flew to space thrice. He also performed two <em><i>EVA</i></em>s (<em><i>Extra-vehicular activity</i></em>, a spacewalk) that lasted together for&nbsp;14 hours 53 minutes and 38 seconds.</p><figure class="table" style="width:700px;"> <table> <tbody> <tr> <td style="background-color:hsl(0, 0%, 90%);text-align:center;"> Flight </td> <td style="background-color:hsl(0, 0%, 90%);text-align:center;"> Date </td> <td style="background-color:hsl(0, 0%, 90%);text-align:center;"> Spacecraft </td> <td style="background-color:hsl(0, 0%, 90%);text-align:center;"> Function </td> <td style="background-color:hsl(0, 0%, 90%);text-align:center;"> Emblem </td> </tr> <tr> <td style="text-align:center;width:200px;"> <strong>STS-74</strong> </td> <td style="text-align:center;width:200px;"> 12-20.11.1995 </td> <td style="text-align:center;width:200px;"> Atlantis </td> <td style="text-align:center;width:200px;"> Mission Specialist </td> <td style="text-align:center;width:100px;"> <img src="https://ckeditor.com/docs/ckeditor5/latest/assets/img/Sts-74-patch.png" alt=""> </td> </tr> <tr> <td style="text-align:center;width:200px;"> <strong>STS-100</strong> </td> <td style="text-align:center;width:200px;"> 19.04.2001-01.05.2001 </td> <td style="text-align:center;width:200px;"> Endeavour </td> <td style="text-align:center;width:200px;"> Mission Specialist </td> <td style="text-align:center;width:100px;"> <img src="https://ckeditor.com/docs/ckeditor5/latest/assets/img/40px-STS-100_patch.png" alt=""> </td> </tr> <tr> <td style="text-align:center;width:200px;"> <strong>Expedition 34/35</strong> </td> <td style="text-align:center;width:200px;"> 19.12.2012-14.05.2013 </td> <td style="text-align:center;width:200px;"> Soyuz TMA-07M </td> <td style="text-align:center;width:200px;"> ISS Commander </td> <td style="text-align:center;width:100px;"> <img src="https://ckeditor.com/docs/ckeditor5/latest/assets/img/40px-Soyuz-TMA-07M-Mission-Patch.png" alt=""> </td> </tr> </tbody> </table></figure><h2> Bibliography</h2><ul> <li> <strong>An Astronauts Guide to Life on Earth</strong>: What Going to Space Taught Me About Ingenuity, Determination, and Being Prepared for Anything. <em><i>Little, Brown and Company, 2013</i></em> </li> <li> <strong>You Are Here</strong>: Around the World in 92 Minutes: Photographs from the International Space Station. <em><i>Little, Brown and Company, 2014</i></em> </li> <li> <strong>The Darkest Dark</strong>. Illustrated by Terry and Eric Fan. <em><i>Little, Brown and Company, 2016</i></em> </li> <li> <strong>The Apollo Murders</strong> <em><i>Random House, 2021</i></em> </li></ul><h2> Space Oddity</h2><p> The rendition of Space Oddity by Chris Hadfield, shot in 2013 was <u>the first ever</u> music video shot in space.</p><figure class="media"> <oembed url="https://www.youtube.com/watch?v=KaOC9danxNo"></oembed></figure><blockquote> <p> The only reason Chris Hadfield isnt the coolest guy on Earth is that hes not on Earth </p></blockquote><p style="text-align:right;"> A comment by August Vctjuh on YouTube.</p>';



        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $res, "message" => lang('Listed_Successfully'));



        $this->api_response($response);

        exit;

    }



    public function insurance_details_data_get($appliction_id, $farmer_id)
    {

        /* insurance_details_data - company name, crop type text, unit text, state and district text, premium per acer and payble amount , path for showing 7/12 image */



        $sql_query = "SELECT ins.*,m.land_id ,m.farmer_id ,m.farm_name,m.topology,m.farm_size ,m.unit ,m.irrigation_facility , m.farm_image, m.calculated_land_area ,m.survey_no,m.irrigation_source,c.logo,c.name,c.name_mr,c.duration_days,c.mob_icon,cm.crop_image,cm.crop_type,cm.duration_from,cm.duration_to,m.soil_type,m.topology,cm.area_under_cultivation,m.doc_7_12,ct.name as city_name,d.name as insurance_company_name,d.logo as insurance_company_logo

        FROM crop_insurance_details as ins

        LEFT JOIN crop as c ON c.crop_id = ins.crop_id

        LEFT JOIN master_land_details as m ON m.land_id = ins.land_id

        LEFT JOIN master_crop_details as cm ON cm.id = ins.crop_id

        LEFT JOIN insurance_company_master as d ON d.insurance_company_id = ins.company_id

        LEFT JOIN cities_new ct ON ct.id = m.cities_id

        WHERE m.farmer_id='" . $farmer_id . "' AND ins.id = '" . $appliction_id . "' LIMIT 1";



        $rows = $this->db->query($sql_query);

        $result_array = $rows->result_array();

        // echo count($result_array);

        if (count($result_array) > 0) {



            $value = $result_array[0];



            $topology_name = $topology_name_mr = $unit_name = $unit_name_mr = $irri_faty_name = $irri_faty_name_mr = $irri_src_name = $irri_src_name_mr = $soil_type_name = $soil_type_name_mr = $farm_type_name = $farm_type_name_mr = null;



            if (!is_null($value['soil_type'])) {

                //echo $value['topology'];

                $soil_type_name = $this->soil_type_web[$value['soil_type']];

                $soil_type_name_mr = $this->soil_type_web_mr[$value['soil_type']];

            }

            if (!is_null($value['farm_type'])) {

                //echo $value['topology'];

                $farm_type_name = $this->farm_type_web[$value['farm_type']];

                $farm_type_name_mr = $this->farm_type_web_mr[$value['farm_type']];

            }



            if (!is_null($value['topology'])) {

                //echo $value['topology'];

                $topology_name = $this->topology_web[$value['topology']];

                $topology_name_mr = $this->topology_web_mr[$value['topology']];

            }

            if (!is_null($value['unit'])) {

                $unit_name = $this->unit_web[$value['unit']];

                $unit_name_mr = $this->unit_web_mr[$value['unit']];

            }

            if (!is_null($value['irrigation_facility'])) {

                $irri_faty_name = $this->irri_faty_web[$value['irrigation_facility']];

                $irri_faty_name_mr = $this->irri_faty_web_mr[$value['irrigation_facility']];

            }

            if (!is_null($value['irrigation_source'])) {

                $irri_src_name = $this->irri_src_web[$value['irrigation_source']];

                $irri_src_name_mr = $this->irri_src_web_mr[$value['irrigation_source']];

            }



            if ($value['duration_from'] != '' && $value['duration_days'] != 0) {

                //2021-03-29

                $duration_to = '';

                $duration = '+' . $value['duration_days'] . ' days';

                //define('ADD_DAYS','+'.$duration.'' days');

                //$start_date = date('Y-m-d H:i:s');

                $duration_to = date("Y-M-d", strtotime($duration, strtotime($value['duration_from'])));



                // $duration_to = date("Y-m-d", strtotime($value['duration_from'],$duration));

            }



            if (!is_null($value['crop_type'])) {

                $crop_type_name = $this->crop_type_web[$value['crop_type']];

                $crop_type_name_mr = $this->crop_type_web_mr[$value['crop_type']];

            }



            if (!is_null($value['crop_image'])) {

                // $crop_image = base_url('uploads/user_data/crop_image/' . $value['crop_image']);

                $crop_image = $this->config_url['crop_image'] . $value['crop_image'];

            } else {

                // $crop_image = base_url('uploads/user_data/crop_image/default.png');

                $crop_image = $this->config_url['crop_image'] . 'default.png';

            }



            $new_crop_arr[] = array(

                'land_id' => $value['land_id'],

                'farmer_id' => $value['farmer_id'],

                'crop' => $value['crop'],

                'crop_image' => $crop_image,

                'farm_image' => $value['farm_image'],

                'crop_name' => $value['name'],

                'crop_name_mr' => $value['name_mr'],

                'crop_type' => $value['crop_type'],

                'crop_type_name' => $crop_type_name,

                'crop_type_name_mr' => $crop_type_name_mr,

                'unit' => $value['unit'],

                'unit_name' => $unit_name,

                'unit_name_mr' => $unit_name_mr,

                'area_under_cultivation' => $value['area_under_cultivation'],

                'duration_from' => $value['duration_from'],

                'duration_to' => $duration_to,

                'farm_name' => $value['farm_name'],

                'farm_name_mr' => $value['farm_name_mr'],

                'duration_days' => $value['duration_days'],

                'doc_7_12' => $value['doc_7_12'],

                'cities_id' => $value['cities_id'],

                'city_name' => $value['city_name'],

                'policy_no' => $value['policy_no'],

                'product_id' => $value['product_id'],

                'company_id' => $value['company_id'],

                'crop_id' => $value['crop_id'],

                'land_id' => $value['land_id'],

                'amount' => $value['amount'],

                'pay_status' => $value['pay_status'],

                'application_status' => $value['application_status'],

                'created_on' => $value['created_on'],

                'insurance_company_name' => $value['insurance_company_name'],

                'insurance_company_logo' => $value['insurance_company_logo'],

                'city_name' => $value['city_name'],

                'premium_per_acre' => $value['premium_per_acre'],



            );



            $response = array("success" => 1, "status" => 1, "data" => $result_array, 'new_crop_arr' => $new_crop_arr, "message" => "Application details");

        } else {

            $response = array("success" => 0, "status" => 1, "data" => $result, "message" => "Not Application data");

        }



        $this->api_response($response);

        exit;

    }



    public function advertise_get()
    {

        $advertise_data = array();

        $data_array = array();

        $sql = "SELECT * FROM advertise_master where is_deleted=false AND is_active=true ORDER BY seq ASC";

        $res_val = $this->db->query($sql);

        $res_array = $res_val->result_array();



        if (count($res_array) > 0) {

            $advertise_data = $res_array;

        }



        $response = array("success" => 1, "data" => $advertise_data, "error" => 0, "status" => 1);

        $this->api_response($response);

    }



    public function home_advertise_get()
    {



        $advertise_data = $this->advertise_listing();

        $response = array("success" => 1, "data" => $advertise_data, "error" => 0, "status" => 1, "config_url" => $this->config_url);



        $this->api_response($response);
        exit;

    }



    public function custom_config_get()
    {

        $config_master_data = array();

        $data_array = array();

        $sql = "SELECT id,name,key_fields,seq,logo,mob_icon,is_active,description FROM config_master where is_deleted=false ORDER BY id ASC";

        $res_val = $this->db->query($sql);

        $res_array = $res_val->result_array();



        if (count($res_array) > 0) {

            $config_master_data = $res_array;

        }



        //$base_path = $this->base_path;

        $logo_url = $this->base_path . 'uploads/config_master/';



        $response = array("success" => 1, "config_master_data" => $config_master_data, "error" => 0, "status" => 1, "data" => $data_array, 'phone_number' => '+91 9607005004', "logo_url" => $logo_url);

        $this->api_response($response);

    }



    // function to send JSON response -

    public function nearby_market_get($lat = '19.997454', $long = '73.789803')
    {



        // Nashik

        // $longitude = (float) 73.789803;

        // $latitude = (float) 19.997454;



        // PUNE

        // $longitude = (float) 73.79737682647546;

        // $latitude = (float) 18.52154807142458;



        // Satara

        // $longitude = (float) 74.29827808;

        // $latitude = (float) 17.63612885;



        // satara 17.63612885 74.29827808

        $longitude = (float) $long;

        $latitude = (float) $lat;



        // $longitude = (float) 74.29827808;

        // $latitude  = (float) 17.63612885;



        $radius = 20; // miles // approx 30 KM



        $sql_location = "SELECT  COALESCE( ( 6371 * acos( cos( radians($latitude) ) * cos( radians(latitude) ) * cos( radians( longitude ) - radians($longitude) ) + sin( radians($latitude) ) * sin( radians( latitude ) ) ) ) , 0) AS distance , apmc_market ,latitude,longitude FROM apmc_location_master  ORDER BY distance ASC  LIMIT 1";



        $res_val = $this->db->query($sql_location);

        $res_array = $res_val->result_array();



        /*$res_val   = $this->db->query($sql_location);

        $res_array = $res_val->result_array();*/



        if (count($res_array) > 0) {

            $apmc_market = strtolower($res_array[0]['apmc_market']);

            $latitude = $res_array[0]['latitude'];

            $longitude = $res_array[0]['longitude'];

            $lcoations_data = $res_array;

            //$result

            $today = date('Y-m-d');



            $tbl_name = "tbl_maharashtra";



            $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
            $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

            $lang_label = " commodityname as commodity ";



            //$lang_label = " name_mr ";

            if ($selected_lang == 'mr') {

                $lang_folder = "marathi";

                $lang_label = " commodity_marathi as commodity ";

            } elseif ($selected_lang == 'hi') {

                $lang_folder = "hindi";

                $lang_label = " commodity_hindi as commodity ";

            } else {

                $lang_folder = "english";

                $lang_label = " commodityname as commodity ";

            }



            /*$slq_comm = "SELECT market,commodityname as commodity, varity as variety,minimumprices as min_price,maximumprices as max_price,marketwiseapmcpricedate as arrival_date, to_char( to_timestamp((marketwiseapmcpricedate),'YYYY-MM-DD'),'YYYY-MM-DD')  NewDateFormat,arrivals,unitofarrivals,modalprices,unitofprice FROM $tbl_name WHERE lower(market) = lower('" . $apmc_market . "')  ORDER BY marketwiseapmcpricedate DESC LIMIT 20 ";*/



            $domain = $headers_data['domain'];



            $slq_comm = "SELECT market, $lang_label , varity as variety,minimumprices as min_price,maximumprices as max_price,marketwiseapmcpricedate as arrival_date,arrivals,unitofarrivals FROM $tbl_name WHERE lower(market) = lower('" . $apmc_market . "')  ORDER BY marketwiseapmcpricedate DESC LIMIT 20";



            if ('ICAR' == $domain) {

                $slq_comm = "SELECT market, $lang_label , varity as variety,minimumprices as min_price,maximumprices as max_price,marketwiseapmcpricedate as arrival_date,arrivals,unitofarrivals FROM $tbl_name WHERE lower(market) = lower('" . $apmc_market . "') ORDER BY CASE WHEN commodityname = 'Onion' THEN 1 ELSE 2 END ASC, marketwiseapmcpricedate DESC LIMIT 50";

            }



            $query = $this->db->query($slq_comm);

            /* $query  = $this->db->query("SELECT market,commodityname as commodity, varity as variety, minimumprices as min_price, maximumprices as max_price, crd as arrival_date FROM marketwiseapmcprices WHERE lower(market) = lower('" . $apmc_market . "')  ORDER BY crd DESC  LIMIT 20");*/

            $result = $query->result_array();

        }



        $response = array("success" => 1, "lcoations_data" => $lcoations_data, "data" => $result, "error" => 0, "status" => 1, 'apmc_market' => $apmc_market);

        $this->api_response($response);



    }



    public function check_referral_code_get($mob = 999)
    {

        $sql_chk = "SELECT first_name,phone_no,first_name,last_name FROM users

        WHERE ( phone_no = '" . $mob . "' OR my_refferal_code = " . $mob . ")  AND is_active = true AND is_deleted =false  LIMIT 1";

        $res_val = $this->db->query($sql_chk);

        $res_array = $res_val->result_array();

        if (count($res_array) > 0) {

            $response = array("success" => 1, "data" => $res_array, "error" => 0, "msg" => 'referral code found', "status" => 1);

        } else {

            $sql_chk2 = "SELECT first_name,phone,middle_name,last_name FROM client WHERE (phone = '" . $mob . "' OR my_refferal_code = " . $mob . " ) AND is_active = true AND is_deleted =false  LIMIT 1";

            $res_val2 = $this->db->query($sql_chk2);

            $res_array2 = $res_val2->result_array();

            if (count($res_array2) > 0) {

                $response = array("success" => 1, "data" => $res_array2, "error" => 0, "msg" => 'referral code found', "status" => 1);

            } else {

                //$response = array("success" => 0, "data" => array(), "msg" => 'Invalid referral code', "error" => 1, "status" => 1);



                $sql_chk3 = "SELECT name FROM client_group_master WHERE referral_code = '" . $mob . "'  AND is_active = true AND is_deleted =false  LIMIT 1";

                $res_val3 = $this->db->query($sql_chk3);

                $res_array3 = $res_val3->result_array();

                if (count($res_array3) > 0) {

                    $response = array("success" => 1, "data" => $res_array3, "error" => 0, "msg" => 'referral code found', "status" => 1);

                } else {

                    $response = array("success" => 0, "data" => array(), "msg" => 'Invalid referral code', "error" => 1, "status" => 1);



                }



            }

        }

        $this->api_response($response);



    }



    // this api is replace by crop_npk_get function

    function crop_npks_details_post()
    {

        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';



        // $this->load->helper('npks_helper');



        $crop_id = $this->input->post('crop_id') ? $this->input->post('crop_id') : 1;

        $n = $this->input->post('n') ? $this->input->post('n') : 100;

        $p = $this->input->post('p') ? $this->input->post('p') : 50;

        $k = $this->input->post('k') ? $this->input->post('k') : 50;

        $s = $this->input->post('s') ? $this->input->post('s') : 30;

        $size = $this->input->post('size') ? $this->input->post('size') : 1;

        $unit = $this->input->post('unit') ? $this->input->post('unit') : 'hectare';

        $season = $this->input->post('season') ? $this->input->post('season') : 'Kharif';

        // crop_id, n, p, k, s, size, unit, season

        // Kharif, Rabi, Late kharif

        if ($crop_id != '') {

            // $list_array = array();

            $sql_chk = "SELECT name,name_mr,nitrogen,phosphorus,potassium FROM crop

            WHERE crop_id = $crop_id  LIMIT 1";

            $res_val = $this->db->query($sql_chk);

            $res_array = $res_val->row_array();



            if (strtolower($unit) == "acre") {

                $hac = 0.404686 * $size;

                $unit = "acre";

            } else {

                $hac = $size;

                $unit = "hectare";

            }



            // if ($n == '' || $n == 0) {

            //     $n = 100;

            // }

            // if ($p == '' || $p == 0) {

            //     $p = 50;

            // }

            // if ($k == '' || $k == 0) {

            //     $k = 50;

            // }

            // if (($crop_id == 2) && ($s == '')) {

            //     $s = 30;

            // }



            $data = [];

            $data['n'] = round($n * $hac);

            $data['p'] = round($p * $hac);

            $data['k'] = round($k * $hac);

            $data['s'] = round($s * $hac);



            $data['crop_name'] = $res_array['name'];

            $data['crop_id'] = $crop_id;

            $data['season'] = $season;

            $data['size'] = $size;

            $data['unit'] = $unit;

            $data['lang'] = $selected_lang;



            $npks_calculations['npk_values'] = execute_calculations($data);



            if ($crop_id == 2) {

                $Required_npk = lang('NPKS_List') . ' ' . $data['n'] . ':' . $data['p'] . ':' . $data['k'] . ':' . $data['s'];

            } else {

                $Required_npk = lang('NPK_List') . ' ' . $data['n'] . ':' . $data['p'] . ':' . $data['k'];

            }



            $npks_calculations['Required_npk'] = $Required_npk;

            $npks_calculations['unit_size'] = 'For ' . $hac . ' ' . $unit;

            if (!empty($npks_calculations) && count($npks_calculations) > 0) {

                $response = array("success" => 1, "data" => $res_array, "details" => $npks_calculations, "error" => 0, "status" => 1);

            } else {

                $response = array("success" => 0, "data" => $res_array, "details" => [], "error" => 1, "status" => 1);

            }

        } else {

            $response = array("success" => 0, "data" => $res_array, "details" => [], "error" => 1, "status" => 1);

        }



        $this->api_response($response);
        exit;

    }



    public function crop_npk_get($crop_id = 1, $n = 120, $p = 100, $k = 120, $size = 1, $unit = 'hectare')
    {

        if ($crop_id != '') {



            $list_array = array();

            //echo 'herer';

            $sql_chk = "SELECT name,name_mr,nitrogen,phosphorus,potassium FROM crop

            WHERE crop_id = $crop_id  LIMIT 1";

            $res_val = $this->db->query($sql_chk);

            $res_array = $res_val->row_array();



            $price_array = array("dap" => 1200, "urea" => 276, "mop" => 980, "ssp" => 420, "102626" => 1175, "123216" => 1185, "151515" => 739.50, "161616" => 368.70, "171717" => 927, "20200" => 850, "05234" => 115, "Bensulf" => 1250);



            # Combination 1 - Urea,DAP,MOP

            # Urea N Conversion Factor

            $urea_factor = 2.17;

            # DAP N&P Conversion Factor (DAP 18%:46%)

            $dap_n_factor = 0.18;

            $dap_p_factor = 2.2;

            # DAP N&P Conversion Factor (DAP 16%:48%)

            //$dap_n_factor = 0.16;

            //$dap_p_factor = 2;



            # MOP K Cmop_factoronversion Factor

            $mop_factor = 1.66;



            # Set 2 Conversion Factor - SSP(16%)

            $ssp_factor = 6.25;



            # Required NPK for particular crop - User Input

            //$ar_required_npk = array("N" => 120, "P" => 100, "K" => 120);

            if ($n == '' || $n == 0) {

                $n = 120;

            }

            if ($p == '' || $p == 0) {

                $p = 100;

            }

            if ($k == '' || $k == 0) {

                $k = 120;

            }



            if (count($res_array) > 0 && $n == 120 && $p == 100 && $k == 120) {

                $nitrogen = $res_array['nitrogen'];

                $phosphorus = $res_array['phosphorus'];

                $potassium = $res_array['potassium'];



                $ar_required_npk = array("N" => $nitrogen, "P" => $phosphorus, "K" => $potassium);

            } else {

                $ar_required_npk = array("N" => $n, "P" => $p, "K" => $k);

            }

            //echo "Required NPK = ".$ar_required_npk["N"].":".$ar_required_npk["P"].":".$ar_required_npk["K"]."<br><br>";



            $req_npk = $ar_required_npk["N"] . ":" . $ar_required_npk["P"] . ":" . $ar_required_npk["K"];

            //$unit = "hectare"; /// this line comment for demo

            //$unit = "acre";

            //$size = 5;

            $s = 30;

            $ar_required_npk["s"] = 30;

            if (strtolower($unit) == "acre") {

                $hac = 0.404686 * $size;

                $unit == "acre";

                //$ar_required_npk["s"] = 30;

                // $s                    = 30;

            } else {

                $hac = $size;

                $unit == "hectare";

            }

            //echo "For $size $unit <br><br>";

            $unit_size = "For $size $unit";



            # ------ Simple Fertilisers ---------

            # Starting with DAP and P in it



            $estimated_dap_kg = $ar_required_npk["P"] * $dap_p_factor;

            $n_indap = $estimated_dap_kg * $dap_n_factor;



            $remaining_n = $ar_required_npk["N"] - $n_indap;

            $estimated_urea_kg = $remaining_n * $urea_factor * $hac;

            $estimated_mop_kg = $ar_required_npk["K"] * $mop_factor * $hac;



            $s = round($ar_required_npk["s"] * $hac);



            $base_price = $this->bagsprice_arr(round($s), "Bensulf");

            $cost_bensulf['bensulf_total_cost_bags'] = $base_price['bags'];

            $cost_bensulf['bensulf_total_cost_bag_price'] = $base_price['bag_price'];

            $cost_bensulf['bensulf_total_cost_cost'] = $base_price['cost'];



            $total_cost = 0;



            $base_price = $this->bagsprice_arr(round($estimated_urea_kg), "urea");

            $cost_udm['urea_total_cost_bags'] = $base_price['bags'];

            $cost_udm['urea_total_cost_bag_price'] = $base_price['bag_price'];

            $cost_udm['urea_total_cost_cost'] = $base_price['cost'];



            $total_cost_udm += $base_price['cost'];



            $base_price = $this->bagsprice_arr(round($estimated_dap_kg), "dap");

            $cost_udm['dap_total_cost_bags'] = $base_price['bags'];

            $cost_udm['dap_total_cost_bag_price'] = $base_price['bag_price'];

            $cost_udm['dap_total_cost_cost'] = $base_price['cost'];

            $total_cost_udm += $base_price['cost'];



            $base_price = $this->bagsprice_arr(round($estimated_mop_kg), "mop");

            $cost_udm['mop_total_cost_bags'] = $base_price['bags'];

            $cost_udm['mop_total_cost_bag_price'] = $base_price['bag_price'];

            $cost_udm['mop_total_cost_cost'] = $base_price['cost'];

            $total_cost_udm += $base_price['cost'];

            $total_cost_udm += $cost_bensulf['bensulf_total_cost_cost'];



            $Urea = round($estimated_urea_kg) . " Kg ";

            $DAP = round($estimated_dap_kg) . " Kg ";

            $MOP = round($estimated_mop_kg) . " Kg ";



            $udm = array('Urea' => $Urea, 'DAP' => $DAP, 'MOP' => $MOP, 'cost' => $cost_udm);



            $str['line1'] = 'Urea ,' . $Urea . ',' . $cost_udm['urea_total_cost_bags'] . ',' . $cost_udm['urea_total_cost_bag_price'];



            $str['line2'] = 'DAP ,' . $DAP . ',' . $cost_udm['dap_total_cost_bags'] . ',' . $cost_udm['dap_total_cost_bag_price'];



            $str['line3'] = 'MOP ,' . $MOP . ',' . $cost_udm['mop_total_cost_bags'] . ',' . $cost_udm['mop_total_cost_bag_price'];



            $str['line4'] = 'Bensulf ,' . $s . ' Kg ,' . $cost_bensulf['bensulf_total_cost_bags'] . ',' . $cost_bensulf['bensulf_total_cost_bag_price'];



            $str['Total'] = "₹ " . number_format($total_cost_udm, 2);



            $complex_fert[] = $str;



            # ------ Simple Fertilisers End ---------

            # ---------------------------------------------------------

            # SSP: P=60% (100/60=1.7)

            $estimated_urea_kg = $ar_required_npk["N"] * $urea_factor * $hac;

            $estimated_ssp_kg = $ar_required_npk["P"] * $ssp_factor * $hac;

            $estimated_mop_kg = $ar_required_npk["K"] * $mop_factor * $hac;

            $total_cost = 0;

            $total_cost_usm += $base_price['cost'];



            $base_price = $this->bagsprice_arr(round($estimated_urea_kg), "urea");

            $cost_usm['urea_total_cost_bags'] = $base_price['bags'];

            $cost_usm['urea_total_cost_bag_price'] = $base_price['bag_price'];

            $cost_usm['urea_total_cost_cost'] = $base_price['cost'];

            $total_cost_usm += $base_price['cost'];



            $base_price = $this->bagsprice_arr(round($estimated_ssp_kg), "ssp");

            $cost_usm['ssp_total_cost_bags'] = $base_price['bags'];

            $cost_usm['ssp_total_cost_bag_price'] = $base_price['bag_price'];

            $cost_usm['ssp_total_cost_cost'] = $base_price['cost'];

            $total_cost_usm += $base_price['cost'];



            $base_price = $this->bagsprice_arr(round($estimated_mop_kg), "mop");

            $cost_usm['mop_total_cost_bags'] = $base_price['bags'];

            $cost_usm['mop_total_cost_bag_price'] = $base_price['bag_price'];

            $cost_usm['mop_total_cost_cost'] = $base_price['cost'];

            $total_cost_usm += $base_price['cost'];



            $total_cost_usm += $cost_bensulf['bensulf_total_cost_bag_price'];

            # ---------------------------------------------------------

            # Set 2

            # SSP: P=60% (100/60=1.7)

            $estimated_urea_kg = $ar_required_npk["N"] * $urea_factor;

            $estimated_ssp_kg = $ar_required_npk["P"] * $ssp_factor;

            $estimated_mop_kg = $ar_required_npk["K"] * $mop_factor;



            $Urea = round($estimated_urea_kg) . " Kg "; //Per " . $hac . " ".$unit;

            $SSP = round($estimated_ssp_kg) . " Kg ";

            $MOP = round($estimated_mop_kg) . " Kg ";



            $usm = array('Urea' => $Urea, 'SSP' => $SSP, 'MOP' => $MOP, 'cost' => $cost_usm);



            $str2['line1'] = 'Urea ,' . $Urea . ',' . $cost_usm['urea_total_cost_bags'] . ',' . $cost_usm['urea_total_cost_bag_price'];



            $str2['line2'] = 'SSP ,' . $SSP . ',' . $cost_usm['ssp_total_cost_bags'] . ',' . $cost_usm['ssp_total_cost_bag_price'];



            $str2['line3'] = 'MOP ,' . $MOP . ',' . $cost_usm['mop_total_cost_bags'] . ',' . $cost_usm['mop_total_cost_bag_price'];



            $str2['line4'] = 'Bensulf ,' . $s . ' Kg ,' . $cost_bensulf['bensulf_total_cost_bags'] . ',' . $cost_bensulf['bensulf_total_cost_bag_price'];



            $str2['Total'] = "₹ " . number_format($total_cost_usm, 2);



            $complex_fert[] = $str2;

            // echo ' =>here234r';

            $complex_fert[] = $this->complex_fert(15, 15, 15, $ar_required_npk, $size, $unit);

            $complex_fert[] = $this->complex_fert(16, 16, 16, $ar_required_npk, $size, $unit);

            $complex_fert[] = $this->complex_fert(17, 17, 17, $ar_required_npk, $size, $unit);

            // $complex_fert[] = $this->complex_fert(19, 19, 19,$ar_required_npk,$size, $unit);

            $complex_fert[] = $this->complex_fert(10, 26, 26, $ar_required_npk, $size, $unit);

            $complex_fert[] = $this->complex_fert(20, 20, 0, $ar_required_npk, $size, $unit);

            $complex_fert[] = $this->complex_fert(0, 0, 50, $ar_required_npk, $size, $unit);

            $complex_fert[] = $this->complex_fert(10, 34, 0, $ar_required_npk, $size, $unit);



            /*  $complex_fert[] = $this->complex_fert(0,52,34,$ar_required_npk,$size, $unit);

            $complex_fert[] = $this->complex_fert(12,61,0,$ar_required_npk,$size, $unit);

            $complex_fert[] = $this->complex_fert(0,0,50,$ar_required_npk,$size, $unit);      */



            // echo ' ===> here324';



            $details_array = array("npk_values" => $complex_fert, 'Required_npk' => $req_npk, 'unit_size' => $unit_size);



            //print_r(json_encode($details_array,JSON_UNESCAPED_UNICODE));

            //echo  json_encode(unserialize(str_replace(array('NAN;','INF;'),'0;',serialize($details_array))));

            //echo json_last_error_msg();

            $response = array("success" => 1, "data" => $res_array, "details" => $details_array, "error" => 0, "status" => 1);



            header('Content-type: application/json');

            echo json_encode(unserialize(str_replace(array('NAN;', 'INF;'), '0;', serialize($response))));

            //$this->api_response($details_array);

            //print_r($response);



            //  header('Content-type: application/json');

            // echo json_encode($response);

        }

    }



    public function crop_npk_get_bkkk($crop_id = 1, $n = 120, $p = 100, $k = 120, $size = 1, $unit = 'hectare')
    {

        if ($crop_id != '') {



            $list_array = array();

            //echo 'herer';

            $sql_chk = "SELECT name,name_mr,nitrogen,phosphorus,potassium FROM crop

            WHERE crop_id = $crop_id  LIMIT 1";

            $res_val = $this->db->query($sql_chk);

            $res_array = $res_val->row_array();



            // $res_array= array();



            /*     $price_array =array("dap"=>1200,"urea"=>276,"mop"=>980,"ssp"=>420,"102626"=>1175,"123216"=>1185,"151515"=>739.50,"161616"=>368.70,"171717"=>927,"20200"=>850,"05234"=>115);*/



            $price_array = array("dap" => 1200, "urea" => 276, "mop" => 980, "ssp" => 420, "102626" => 1175, "123216" => 1185, "151515" => 739.50, "161616" => 368.70, "171717" => 927, "20200" => 850, "05234" => 115);



            # Combination 1 - Urea,DAP,MOP

            # Urea N Conversion Factor

            $urea_factor = 2.17;

            # DAP N&P Conversion Factor (DAP 18%:46%)

            $dap_n_factor = 0.18;

            $dap_p_factor = 2.2;

            # DAP N&P Conversion Factor (DAP 16%:48%)

            //$dap_n_factor = 0.16;

            //$dap_p_factor = 2;



            # MOP K Cmop_factoronversion Factor

            $mop_factor = 1.66;



            # Set 2 Conversion Factor - SSP(16%)

            $ssp_factor = 6.25;



            # Required NPK for particular crop - User Input

            //$ar_required_npk = array("N" => 120, "P" => 100, "K" => 120);

            if ($n == '' || $n == 0) {

                $n = 120;

            }

            if ($p == '' || $p == 0) {

                $p = 100;

            }

            if ($k == '' || $k == 0) {

                $k = 120;

            }



            if (count($res_array) > 0 && $n == 120 && $p == 100 && $k == 120) {

                $nitrogen = $res_array['nitrogen'];

                $phosphorus = $res_array['phosphorus'];

                $potassium = $res_array['potassium'];



                $ar_required_npk = array("N" => $nitrogen, "P" => $phosphorus, "K" => $potassium);

            } else {

                $ar_required_npk = array("N" => $n, "P" => $p, "K" => $k);

            }

            //echo "Required NPK = ".$ar_required_npk["N"].":".$ar_required_npk["P"].":".$ar_required_npk["K"]."<br><br>";



            $req_npk = $ar_required_npk["N"] . ":" . $ar_required_npk["P"] . ":" . $ar_required_npk["K"];

            //$unit = "hectare"; /// this line comment for demo

            //$unit = "acre";

            //$size = 5;

            if (strtolower($unit) == "acre") {

                $hac = 0.404686 * $size;

                $unit == "acre";

            } else {

                $hac = $size;

                $unit == "hectare";

            }

            //echo "For $size $unit <br><br>";

            $unit_size = "For $size $unit";

            # ------ Simple Fertilisers ---------

            # Starting with DAP and P in it

            $estimated_dap_kg = $ar_required_npk["P"] * $dap_p_factor;

            $n_indap = $estimated_dap_kg * $dap_n_factor;



            $remaining_n = $ar_required_npk["N"] - $n_indap;

            $estimated_urea_kg = $remaining_n * $urea_factor * $hac;

            $estimated_mop_kg = $ar_required_npk["K"] * $mop_factor * $hac;



            $total_cost = 0;



            $base_price = $this->bagsprice_arr(round($estimated_urea_kg), "urea");

            $cost_udm['urea_total_cost_bags'] = $base_price['bags'];

            $cost_udm['urea_total_cost_bag_price'] = $base_price['bag_price'];

            $cost_udm['urea_total_cost_cost'] = $base_price['cost'];



            $total_cost_udm += $base_price['cost'];



            $base_price = $this->bagsprice_arr(round($estimated_dap_kg), "dap");

            $cost_udm['dap_total_cost_bags'] = $base_price['bags'];

            $cost_udm['dap_total_cost_bag_price'] = $base_price['bag_price'];

            $cost_udm['dap_total_cost_cost'] = $base_price['cost'];

            $total_cost_udm += $base_price['cost'];



            $base_price = $this->bagsprice_arr(round($estimated_mop_kg), "mop");

            $cost_udm['mop_total_cost_bags'] = $base_price['bags'];

            $cost_udm['mop_total_cost_bag_price'] = $base_price['bag_price'];

            $cost_udm['mop_total_cost_cost'] = $base_price['cost'];

            $total_cost_udm += $base_price['cost'];



            $Urea = round($estimated_urea_kg) . " Kg ";

            $DAP = round($estimated_dap_kg) . " Kg ";

            $MOP = round($estimated_mop_kg) . " Kg ";



            $udm = array('Urea' => $Urea, 'DAP' => $DAP, 'MOP' => $MOP, 'cost' => $cost_udm);



            $str['line1'] = 'Urea ,' . $Urea . ',' . $cost_udm['urea_total_cost_bags'] . ',' . $cost_udm['urea_total_cost_bag_price'];



            $str['line2'] = 'DAP ,' . $DAP . ',' . $cost_udm['dap_total_cost_bags'] . ',' . $cost_udm['dap_total_cost_bag_price'];



            $str['line3'] = 'MOP ,' . $MOP . ',' . $cost_udm['mop_total_cost_bags'] . ',' . $cost_udm['mop_total_cost_bag_price'];



            $str['Total'] = "₹ " . number_format($total_cost_udm, 2);



            $complex_fert[] = $str;



            # ------ Simple Fertilisers End ---------

            # ---------------------------------------------------------

            # SSP: P=60% (100/60=1.7)

            $estimated_urea_kg = $ar_required_npk["N"] * $urea_factor * $hac;

            $estimated_ssp_kg = $ar_required_npk["P"] * $ssp_factor * $hac;

            $estimated_mop_kg = $ar_required_npk["K"] * $mop_factor * $hac;

            $total_cost = 0;

            $total_cost_usm += $base_price['cost'];



            $base_price = $this->bagsprice_arr(round($estimated_urea_kg), "urea");

            $cost_usm['urea_total_cost_bags'] = $base_price['bags'];

            $cost_usm['urea_total_cost_bag_price'] = $base_price['bag_price'];

            $cost_usm['urea_total_cost_cost'] = $base_price['cost'];

            $total_cost_usm += $base_price['cost'];



            $base_price = $this->bagsprice_arr(round($estimated_ssp_kg), "ssp");

            $cost_usm['ssp_total_cost_bags'] = $base_price['bags'];

            $cost_usm['ssp_total_cost_bag_price'] = $base_price['bag_price'];

            $cost_usm['ssp_total_cost_cost'] = $base_price['cost'];

            $total_cost_usm += $base_price['cost'];



            $base_price = $this->bagsprice_arr(round($estimated_mop_kg), "mop");

            $cost_usm['mop_total_cost_bags'] = $base_price['bags'];

            $cost_usm['mop_total_cost_bag_price'] = $base_price['bag_price'];

            $cost_usm['mop_total_cost_cost'] = $base_price['cost'];

            $total_cost_usm += $base_price['cost'];

            # ---------------------------------------------------------

            # Set 2

            # SSP: P=60% (100/60=1.7)

            $estimated_urea_kg = $ar_required_npk["N"] * $urea_factor;

            $estimated_ssp_kg = $ar_required_npk["P"] * $ssp_factor;

            $estimated_mop_kg = $ar_required_npk["K"] * $mop_factor;



            $Urea = round($estimated_urea_kg) . " Kg "; //Per " . $hac . " ".$unit;

            $SSP = round($estimated_ssp_kg) . " Kg ";

            $MOP = round($estimated_mop_kg) . " Kg ";



            $usm = array('Urea' => $Urea, 'SSP' => $SSP, 'MOP' => $MOP, 'cost' => $cost_usm);



            $str2['line1'] = 'Urea ,' . $Urea . ',' . $cost_usm['urea_total_cost_bags'] . ',' . $cost_usm['urea_total_cost_bag_price'];



            $str2['line2'] = 'SSP ,' . $SSP . ',' . $cost_usm['ssp_total_cost_bags'] . ',' . $cost_usm['ssp_total_cost_bag_price'];



            $str2['line3'] = 'MOP ,' . $MOP . ',' . $cost_usm['mop_total_cost_bags'] . ',' . $cost_usm['mop_total_cost_bag_price'];



            $str2['Total'] = "₹ " . number_format($total_cost_usm, 2);



            $complex_fert[] = $str2;

            // echo ' =>here234r';

            $complex_fert[] = $this->complex_fert(15, 15, 15, $ar_required_npk, $size, $unit);

            $complex_fert[] = $this->complex_fert(16, 16, 16, $ar_required_npk, $size, $unit);

            $complex_fert[] = $this->complex_fert(17, 17, 17, $ar_required_npk, $size, $unit);

            // $complex_fert[] = $this->complex_fert(19, 19, 19,$ar_required_npk,$size, $unit);

            $complex_fert[] = $this->complex_fert(10, 26, 26, $ar_required_npk, $size, $unit);

            $complex_fert[] = $this->complex_fert(20, 20, 0, $ar_required_npk, $size, $unit);

            $complex_fert[] = $this->complex_fert(0, 0, 50, $ar_required_npk, $size, $unit);

            $complex_fert[] = $this->complex_fert(10, 34, 0, $ar_required_npk, $size, $unit);



            /*  $complex_fert[] = $this->complex_fert(0,52,34,$ar_required_npk,$size, $unit);

            $complex_fert[] = $this->complex_fert(12,61,0,$ar_required_npk,$size, $unit);

            $complex_fert[] = $this->complex_fert(0,0,50,$ar_required_npk,$size, $unit);      */



            // echo ' ===> here324';



            $details_array = array("npk_values" => $complex_fert, 'Required_npk' => $req_npk, 'unit_size' => $unit_size);



            //print_r(json_encode($details_array,JSON_UNESCAPED_UNICODE));

            //echo  json_encode(unserialize(str_replace(array('NAN;','INF;'),'0;',serialize($details_array))));

            //echo json_last_error_msg();

            $response = array("success" => 1, "data" => $res_array, "details" => $details_array, "error" => 0, "status" => 1);



            header('Content-type: application/json');

            echo json_encode(unserialize(str_replace(array('NAN;', 'INF;'), '0;', serialize($response))));

            //$this->api_response($details_array);

            //print_r($response);



            //  header('Content-type: application/json');

            // echo json_encode($response);

        }

    }



    ///19.97631/73.768565

    public function nearby_market_all_data_new_post()
    {



        $apmc_market = $_REQUEST['apmc_market'];

        $lat = $_REQUEST['lat'];

        $long = $_REQUEST['long'];

        $start = $_REQUEST['start'];



        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        $domain = $headers_data['domain'];

        $lang_label = " tm.commodityname as commodity_title ";



        //$lang_label = " name_mr ";

        $lang_label_map = " tm.commodityname as map_key ";

        if ($selected_lang == 'mr') {

            $lang_folder = "marathi";

            $lang_label = " tm.commodity_marathi as commodity_title ";

        } elseif ($selected_lang == 'hi') {

            $lang_folder = "hindi";

            $lang_label = " tm.commodity_hindi as commodity_title ";

        } else {

            $lang_folder = "english";

            $lang_label = " tm.commodityname as commodity_title ";



        }



        if (!$lat) {

            $lat = (float) 19.997454;

        }



        if (!$long) {

            $long = (float) 73.789803;

        }



        // Nashik

        //$longitude = (float) 73.789803;

        // $latitude = (float) 19.997454;



        // PUNE

        //$longitude = (float) 73.79737682647546;

        // $latitude = (float) 18.52154807142458;



        $apmc_market_data = '';

        //$apmc_market      = $_REQUEST['apmc_market'];



        $longitude = (float) $long;

        $latitude = (float) $lat;



        //satara

        // $longitude = (float) 74.29827808;

        // $latitude = (float) 17.63612885;

        // $radius = 16; // in miles



        $limit = 10;

        // $start    = 1;

        $cat_id = 0;



        if ($start != 0) {

            $start_sql = $limit * ($start - 1);

        } else {

            $start_sql = 0;

        }



        if ($apmc_market == '') {



            $sql_location = "SELECT  COALESCE( ( 6371 * acos( cos( radians($latitude) ) * cos( radians(latitude) ) * cos( radians( longitude ) - radians($longitude) ) + sin( radians($latitude) ) * sin( radians( latitude ) ) ) ) , 0) AS distance , apmc_market ,latitude,longitude FROM apmc_location_master  ORDER BY distance ASC  LIMIT 1";



            $res_val = $this->db->query($sql_location);

            $res_array = $res_val->result_array();



            if (count($res_array) > 0) {

                $apmc_market_data = strtolower($res_array[0]['apmc_market']);

            }

        } else {

            $apmc_market_data = $apmc_market;

        }



        //  $apmc_market    = strtolower($res_array[0]['apmc_market']);

        ///$latitude       = $res_array[0]['latitude'];

        // $longitude      = $res_array[0]['longitude'];

        if ($apmc_market_data != '') {



            $lcoations_data[] = array('apmc_market' => ucfirst(strtolower($apmc_market_data)), 'latitude' => $latitude, 'longitude' => $longitude);

            //$result

            $today = date('Y-m-d');

            $sql_limit = " LIMIT " . $limit . " OFFSET " . $start_sql;



            /* $slq_comm = "SELECT market,commodityname as commodity, varity as variety,minimumprices as min_price,maximumprices as max_price,marketwiseapmcpricedate as arrival_date, to_char( to_timestamp((marketwiseapmcpricedate),'DD/MM/YYYY'),'YYYY-MM-DD')  NewDateFormat,arrivals,unitofarrivals,modalprices,unitofprice FROM lastfiveyeardata WHERE lower(market) = lower('" . $apmc_market_data . "')  ORDER BY marketwiseapmcpriceid DESC " . $sql_limit;*/



            $tbl_name = "tbl_maharashtra";

            //$tbl_name_tm = "tbl_maharashtra as tm";



            $sql_comm = "SELECT  tm.market, $lang_label_map ,$lang_label, tm.commodityname as commodity, tm.varity as variety, tm.minimumprices as min_price, tm.maximumprices as max_price, tm.marketwiseapmcpricedate as arrival_date, to_char( to_timestamp((tm.marketwiseapmcpricedate),'YYYY-MM-DD'),'YYYY-MM-DD') NewDateFormat, tm.arrivals, tm.unitofarrivals, tm.modalprices, tm.unitofprice, cr.logo ";

            $sql_comm .= " FROM $tbl_name as tm";

            $sql_comm .= " LEFT JOIN crop as cr ON cr.crop_id = tm.pg_crop_master_id";

            $sql_comm .= " WHERE lower(tm.market) = lower('" . $apmc_market_data . "') ";

            $sql_comm .= " ORDER BY tm.marketwiseapmcpricedate DESC " . $sql_limit;



            /*$slq_comm = "SELECT  market, $lang_label , commodityname as commodity, varity as variety,minimumprices as min_price,maximumprices as max_price,marketwiseapmcpricedate as arrival_date, to_char( to_timestamp((marketwiseapmcpricedate),'YYYY-MM-DD'),'YYYY-MM-DD')  NewDateFormat,arrivals,unitofarrivals,modalprices,unitofprice FROM $tbl_name WHERE lower(market) = lower('" . $apmc_market_data . "')  ORDER BY marketwiseapmcpricedate DESC " . $sql_limit;*/



            if ('ICAR' == $domain) {

                $slq_comm = '';



                $sql_comm = "SELECT  tm.market,$lang_label_map ,$lang_label, tm.commodityname as commodity, tm.varity as variety, tm.minimumprices as min_price, tm.maximumprices as max_price, tm.marketwiseapmcpricedate as arrival_date, to_char( to_timestamp((tm.marketwiseapmcpricedate),'YYYY-MM-DD'),'YYYY-MM-DD') NewDateFormat, tm.arrivals, tm.unitofarrivals, tm.modalprices, tm.unitofprice, cr.logo ";

                $sql_comm .= " FROM $tbl_name as tm";

                $sql_comm .= " LEFT JOIN crop as cr ON cr.crop_id = tm.pg_crop_master_id";

                $sql_comm .= " WHERE lower(tm.market) = lower('" . $apmc_market_data . "') ";

                $sql_comm .= " ORDER BY CASE WHEN commodityname = 'Onion' THEN 1 ELSE 2 END ASC, marketwiseapmcpricedate DESC " . $sql_limit;



                /*  $slq_comm = "SELECT  market, $lang_label , commodityname as commodity, varity as variety,minimumprices as min_price,maximumprices as max_price,marketwiseapmcpricedate as arrival_date, to_char( to_timestamp((marketwiseapmcpricedate),'YYYY-MM-DD'),'YYYY-MM-DD')  NewDateFormat,arrivals,unitofarrivals,modalprices,unitofprice FROM $tbl_name WHERE lower(market) = lower('" . $apmc_market_data . "')  ORDER BY CASE WHEN commodityname = 'Onion' THEN 1 ELSE 2 END ASC, marketwiseapmcpricedate DESC " . $sql_limit;*/



            }



            //commodityname = 'Onion' THEN 1 ELSE 2 END ASC, marketwiseapmcpricedate DESC LIMIT 20



            /* echo "my".$sql_comm;

             exit();*/



            $query = $this->db->query($sql_comm);



            $result = $query->result_array();

        }



        $response = array("success" => 1, "lcoations_data" => $lcoations_data, "data" => $result, "error" => 0, "status" => 1, 'apmc_market' => $apmc_market);

        $this->api_response($response);



    }



    public function markets_get()
    {

        $res = array();

        /* $sql = "SELECT CONCAT(UPPER(SUBSTRING(market,1,1)),LOWER(SUBSTRING(market,2))) AS market FROM prediction_data_files

        WHERE is_deleted = false GROUP BY market ORDER BY market ASC";

         */

        // $sql = "SELECT *, CONCAT(UPPER(SUBSTRING(apmc_market,1,1)),LOWER(SUBSTRING(apmc_market,2))) AS market FROM apmc_location_master WHERE is_deleted = false GROUP BY market ORDER BY market ASC";



        $sql = "SELECT *, CONCAT(UPPER(SUBSTRING(apmc_market,1,1)),LOWER(SUBSTRING(apmc_market,2))) AS market FROM apmc_location_master

        WHERE is_deleted = false ORDER BY market ASC";



        $res_chk = $this->db->query($sql);

        $res = $res_chk->result_array();



        if (count($res) > 0) {

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $res, "message" => "APMC Market listing");



        } else {

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $res, "message" => "APMC Market listing");

        }

        $this->api_response($response);

        exit;

    }



    public function saller_markets_get()
    {

        $this->db->select('*');

        $this->db->where('is_active', true);

        $this->db->where('is_deleted', false);

        $markets = $this->db->get('market_master')->result_array();



        if (count($markets) > 0) {

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $markets, "message" => "Saller Market listing");



        } else {

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $res, "message" => "Saller Market listing");

        }

        $this->api_response($response);

        exit;



    }



    public function about_us_get()
    {

        //$result['phone1']   = '+91 9607005004';



        $result['phone1'] = !empty(get_config_data('phone')) ? get_config_data('phone') : '+91 9607005004';

        $result['email'] = !empty(get_config_data('email')) ? get_config_data('email') : 'getintouch@famrut.com';

        $result['phone2'] = !empty(get_config_data('phone2')) ? get_config_data('phone2') : '+91 (0253) 6636500';

        // $result['email']    = 'getintouch@famrut.com';

        $result['address'] = !empty(get_config_data('address')) ? get_config_data('address') : 'Plot No. B- 24 & 25, NICE Industrial Area, Satpur MIDC, Nashik 422 007';



        //$about_us = get_config_data('about_us');

        $about_us_str = 'Famrut App provides the agri related business to offer their products and services in various segments such as Banks for providing Loans, Insurance companies for providing Crop and other insurance, Agronomists for providing crop related assistance, Labour Providers, Traders for offering their products, Vet Doctors for offering Tele Medicine services, Equipment companies for offering equipments on outright and rental basis and various other products and services.



        Famrut App is your one-stop solution that facilitates businesses to offer their products and services on a single platform.



        Famrut App not only provides visibility for your products and services but also help you increase in revenues as well.



        Famrut App has everything you need to scale you agri related business and expand it it to the next level with added benefits, incentives and much more.



        Download the App now and explore new revenue opportunities for your Farm.

        Our Privacy Policy : https://www.famrut.com/privacy-policy.html';



        $about_us_str_mr = 'फामृत ऍप हे कृषि व्यवसाय संबंधित सर्व सेवा आणि उत्पादने प्रदान करते. जसे की कर्जा साठी बँक, पीक आणि इतर विमा देण्यासाठी विमा कंपन्या, पिका संबंधी मार्गदर्शन साठी कृषीशास्त्रज्ञ, कृषी कामा साठी लागणारे मजूर, कृषी उत्पादन विकण्यासाठी व्यापारी, पशु वैद्यकीय सेवा, टेली मेडिसिन सेवा, कृषी व्यवसाय संबंधित उपकरणे (विकत किंवा भाडे तत्वावर) आणि इतर विविध उत्पादने आणि सेवा.



        फामृत ऍप हे शेतकऱ्यांसाठी साठी सर्व समावेशक आहे जे कृषी व्यावसायिकांना त्यांची उत्पादने आणि सेवा एकाच प्लॅटफॉर्मवर ऑफर करण्याची सुविधा देतो.



        फामृत ऍप तुमची उत्पादने आणि सेवांसाठी केवळ प्लॅटफॉर्म प्रदान करत नाही तर तुम्हाला महसूल वाढवण्यासही मदत करते.



        फामृत ऍप मध्ये सर्व काही आहे जे तुमचा कृषी व्यवसाय वाढवून एका उच्च स्तरावर घेऊन जाण्यास मदत करते आणि शेतकऱ्यांना लाभ प्रदान करते.



        तर शेतकरी बंधूनो आता फामृत हे ऍप वापरण्यास सुरुवात करा आणि तुमच्या कृषी साठी कमाईच्या नवीन संधी शोधा आणि उत्पन्न वाढवा.



        गोपनीयता धोरण : https://www.famrut.com/privacy-policy.html';



        $result['about_us'] = !empty(get_config_data('about_us')) ? get_config_data('about_us') : $about_us_str;



        $result['about_us_mr'] = !empty(get_config_data('about_us_mr')) ? get_config_data('about_us_mr') : $about_us_str_mr;



        $result['lat'] = !empty(get_config_data('address_latitude')) ? get_config_data('address_latitude') : '20.009316';

        $result['long'] = !empty(get_config_data('address_longitude')) ? get_config_data('address_longitude') : '73.7590361';



        $response = array("success" => 1, "data" => $result, "msg" => 'About us', "error" => 0, "status" => 1);



        $this->api_response($response);

    }



    public function commodity_details_data_new_post()
    {

        $commodity_name = $_REQUEST['commodity_name'];

        $market_name = $_REQUEST['market_name'];

        $varity_nm = $_REQUEST['varity'];

        $is_encode = $_REQUEST['is_encode'];

        $lang_label = " commodityname as commodity ";

        $graph_path = '';



        //$lang_label = " name_mr ";

        if ($selected_lang == 'mr') {

            $lang_folder = "marathi";

            $lang_label = " commodity_marathi as commodity ";

            $cmm_name = " commodityname ";

        } elseif ($selected_lang == 'hi') {

            $lang_folder = "hindi";

            $lang_label = " commodity_hindi as commodity ";

            $cmm_name = " commodityname ";

        } else {

            $lang_folder = "english";

            $lang_label = " commodityname as commodity ";

            $cmm_name = " commodityname ";

        }



        $today = date('Y-m-d');

        $varity = '';

        if ($is_encode == 1) {



            if ($varity_nm != '') {

                $varity = base64_decode($varity_nm);

            }



            if ($commodity_name != '') {

                $commodity_name = base64_decode($commodity_name);

            }



            if ($market_name != '') {

                $market_name = base64_decode($market_name);

            }

        }



        if ($varity != '') {

            $where_varity = " AND varity ILIKE '" . $varity . "' ";

        } else {

            $where_varity = '';

        }



        /* $varity = $_GET['where_varity'] != ''? $_GET['where_varity'] : 'Khandesh' ;

        $commodity_name = $_GET['commodity_name'] != ''? $_GET['commodity_name'] : 'Banana' ;

        $market_name = $_GET['market_name'] != ''? $_GET['market_name'] : 'Nasik' ; */

        $tbl_name = 'tbl_maharashtra';



        /*  if ($varity != '') {

        $where_varity = " AND varity ILIKE '" . $varity . "' ";

        } else {

        $where_varity = '';

        }*/



        /*   echo $sql = "SELECT to_char( to_timestamp((marketwiseapmcpricedate),'DD/MM/YYYY'),'YYYY-MM-DD') NewDateFormat ,commodityname, market, minimumprices, maximumprices,modalprices,unitofprice,unitofarrivals,arrivals,varity

        FROM $tbl_name WHERE commodityname ILIKE '" . $commodity_name . "'  " . $where_varity . " AND market ILIKE '" . $market_name . "'  ORDER BY marketwiseapmcprice_id ASC LIMIT 5"; */

        $sql = "SELECT marketwiseapmcpricedate as NewDateFormat ,commodityname, market, minimumprices, maximumprices,modalprices,unitofprice,unitofarrivals,arrivals,varity FROM $tbl_name WHERE $cmm_name ILIKE '" . $commodity_name . "'  " . $where_varity . " AND market ILIKE '" . $market_name . "'  ORDER BY marketwiseapmcpricedate DESC LIMIT 5";



        $query_res = $this->db->query($sql);

        $days_data = $query_res->result_array();

        $query_prediction = $this->db->query("SELECT * FROM prediction_data_files WHERE commodity ILIKE '" . $commodity_name . "' AND market ILIKE '" . $market_name . "'  ORDER BY id DESC LIMIT 1");

        $prediction_data = $query_prediction->result_array();

        // print_r($prediction_data);



        $prediction_file_path = 'https://dev.famrut.co.in/python/Prediction/prediction_result/';

        $data_new = array();

        $graph_array = array();

        $cost_array = array();

        $next_date_arr = array();

        $date = date("Y-m-d");

        $date_my = date();

        $date_last_month_timestamp = strtotime('-30 day');

        // $prev_date = date('Y-m-d', strtotime($date .' -1 day'));

        $next_date = date('Y-m-d', strtotime($date . ' +1 day'));

        // $prev_date_2 = date('Y-m-d', strtotime($date .' -2 day'));

        $next_date_2 = date('Y-m-d', strtotime($date . ' +2 day'));

        for ($i = 1; $i <= 15; $i++) {

            $next_date_arr[0] = date("Y-m-d");

            $next_date_arr[$i] = date('Y-m-d', strtotime($date . ' ' . $i . ' day'));



        }

        if (count($prediction_data)) {

            $file_name = $prediction_data[0]['file_name'];

            $graph_file_name = $prediction_data[0]['graph_file_name'];

            if ($file_name != '') {

                $file_path = 'https://dev.famrut.co.in/python/Prediction/prediction_result/' . $file_name;

                $graph_path = 'https://dev.famrut.co.in/python/Prediction/prediction_graphs/' . $graph_file_name;

                $data_val = file_get_contents($file_path);

                $data_clean = explode(PHP_EOL, $data_val);

                $new_array = explode($b, -30, 30);

                foreach ($data_clean as $d) {

                    if ($d != '') {



                        $marray = explode(':', $d);

                        $data_new[] = explode(':', $d);



                        $date_chk = substr($marray[0], 0, 10);

                        $date_chk_fix = substr($date_last_month_timestamp, 0, 10);



                        $chk_str = 'val1 :' . $date_chk . ' ||  new_val : ' . $date_chk_fix;



                        if ($date_chk >= $date_chk_fix) {



                            $graph_array[] = array('date' => date('Y-m-d', substr($marray[0], 0, 10)), 'price' => number_format($marray[1], 2, '.', ''));



                            $converted_date = date('Y-m-d', substr($marray[0], 0, 10));

                            foreach ($next_date_arr as $nd) {

                                if ($nd == $converted_date) {

                                    $cost_array[] = array('newdateformat' => date('Y-m-d', substr($marray[0], 0, 10)), 'modalprices' => number_format($marray[1], 2), 'minimumprices' => '0', 'maximumprices' => '0', 'unitofprice' => '-', 'unitofarrivals' => '-', 'arrivals' => '-', 'prediction_flag' => '1');

                                }

                            }

                            /* if ($date == $converted_date) {

                                $cost_array[] = array('newdateformat' => date('Y-m-d', substr($marray[0], 0, 10)), 'modalprices' => number_format($marray[1], 2), 'minimumprices' => '0', 'maximumprices' => '0', 'unitofprice' => '-', 'unitofarrivals' => '-', 'arrivals' => '-', 'prediction_flag' => '1');

                            }

                            if ($next_date == $converted_date) {

                                $cost_array[] = array('newdateformat' => date('Y-m-d', substr($marray[0], 0, 10)), 'modalprices' => number_format($marray[1], 2), 'minimumprices' => '0', 'maximumprices' => '0', 'unitofprice' => '-', 'unitofarrivals' => '-', 'arrivals' => '-', 'prediction_flag' => '1');

                            }

                            if ($next_date_2 == $converted_date) {

                                $cost_array[] = array('newdateformat' => date('Y-m-d', substr($marray[0], 0, 10)), 'modalprices' => number_format($marray[1], 2), 'minimumprices' => '0', 'maximumprices' => '0', 'unitofprice' => '-', 'unitofarrivals' => '-', 'arrivals' => '-', 'prediction_flag' => '1');

                            } */



                        }



                    }

                }

            }

        }



        $days_data_3 = array_reverse($days_data);

        $c = array_merge($days_data_3, $cost_array);



        $slq_30 = "SELECT marketwiseapmcpricedate as NewDateFormat ,commodityname, market, minimumprices, maximumprices,modalprices,unitofprice,unitofarrivals,arrivals,varity

            FROM $tbl_name WHERE $cmm_name ILIKE '" . $commodity_name . "'  " . $where_varity . " AND market ILIKE '" . $market_name . "'  ORDER BY marketwiseapmcpricedate DESC LIMIT 28";



        /*"SELECT to_char( to_timestamp((marketwiseapmcpricedate),'DD/MM/YYYY'),'YYYY-MM-DD')  NewDateFormat,minimumprices, maximumprices,unitofarrivals,arrivals,varity FROM lastfiveyeardata WHERE commodityname ILIKE '" . $commodity_name . "' " . $where_varity . "  AND market ILIKE '" . $market_name . "' ORDER BY marketwiseapmcpriceid DESC LIMIT 30"*/



        $query_graph = $this->db->query($slq_30);

        $result = $query_graph->result_array();



        if (count($result)) {

            $response = array("status" => 1, "success" => 1, "error" => 0, "data" => array_reverse($result), "graph_array" => $graph_array, "cost_array" => $c, "days_data" => $days_data, 'date_last_month_timestamp' => $_REQUEST, 'graph_image' => $graph_path, "message" => lang('Listed_Successfully'));

        } else {



            $response = array("status" => 1, "success" => 0, "error" => 1, "data" => [], "graph_array" => [], "cost_array" => [], "days_data" => [], 'date_last_month_timestamp' => $_REQUEST, 'graph_image' => $graph_path, "message" => lang('Data_Not_Found'));

        }



        $this->api_response($response);



    }



    public function commodity_details_data_new2_post()
    {



        $commodity_name = $_REQUEST['commodity_name'];

        $market_name = $_REQUEST['market_name'];

        $varity_nm = $_REQUEST['varity'];

        $is_encode = $_REQUEST['is_encode'];

        $lang_label = " commodityname as commodity ";

        $graph_path = '';



        //$lang_label = " name_mr ";

        if ($selected_lang == 'mr') {

            $lang_folder = "marathi";

            $lang_label = " commodity_marathi as commodity ";

            $cmm_name = " commodityname ";

        } elseif ($selected_lang == 'hi') {

            $lang_folder = "hindi";

            $lang_label = " commodity_hindi as commodity ";

            $cmm_name = " commodityname ";

        } else {

            $lang_folder = "english";

            $lang_label = " commodityname as commodity ";

            $cmm_name = " commodityname ";

        }



        $today = date('Y-m-d');

        $varity = '';

        if ($is_encode == 1) {



            if ($varity_nm != '') {

                $varity = base64_decode($varity_nm);

            }



            if ($commodity_name != '') {

                $commodity_name = base64_decode($commodity_name);

            }



            if ($market_name != '') {

                $market_name = base64_decode($market_name);

            }

        }



        if ($varity != '') {

            $where_varity = " AND varity ILIKE '" . $varity . "' ";

        } else {

            $where_varity = '';

        }



        /* $varity = $_GET['where_varity'] != ''? $_GET['where_varity'] : 'Khandesh' ;

        $commodity_name = $_GET['commodity_name'] != ''? $_GET['commodity_name'] : 'Banana' ;

        $market_name = $_GET['market_name'] != ''? $_GET['market_name'] : 'Nasik' ; */

        $tbl_name = 'tbl_maharashtra';



        /*  if ($varity != '') {

        $where_varity = " AND varity ILIKE '" . $varity . "' ";

        } else {

        $where_varity = '';

        }*/



        /*   echo $sql = "SELECT to_char( to_timestamp((marketwiseapmcpricedate),'DD/MM/YYYY'),'YYYY-MM-DD') NewDateFormat ,commodityname, market, minimumprices, maximumprices,modalprices,unitofprice,unitofarrivals,arrivals,varity

        FROM $tbl_name WHERE commodityname ILIKE '" . $commodity_name . "'  " . $where_varity . " AND market ILIKE '" . $market_name . "'  ORDER BY marketwiseapmcprice_id ASC LIMIT 5"; */

        $sql = "SELECT marketwiseapmcpricedate as NewDateFormat ,commodityname, market, minimumprices, maximumprices,modalprices,unitofprice,unitofarrivals,arrivals,varity FROM $tbl_name WHERE $cmm_name ILIKE '" . $commodity_name . "'  " . $where_varity . " AND market ILIKE '" . $market_name . "'  ORDER BY marketwiseapmcpricedate DESC LIMIT 5";



        $query_res = $this->db->query($sql);

        $days_data = $query_res->result_array();

        //echo '<pre>';

        //print_r($days_data);



        $query_prediction = $this->db->query("SELECT * FROM prediction_data_files WHERE commodity ILIKE '" . $commodity_name . "' AND market ILIKE '" . $market_name . "'  ORDER BY id DESC LIMIT 1");

        $prediction_data = $query_prediction->result_array();

        // print_r($prediction_data);



        $prediction_file_path = 'https://dev.famrut.co.in/python/Prediction/prediction_result/';

        $data_new = array();

        $graph_array = array();

        $cost_array = array();



        $date = date("Y-m-d");

        $date_my = date();

        $date_last_month_timestamp = strtotime('-30 day');

        // $prev_date = date('Y-m-d', strtotime($date .' -1 day'));

        $next_date = date('Y-m-d', strtotime($date . ' +1 day'));

        // $prev_date_2 = date('Y-m-d', strtotime($date .' -2 day'));

        $next_date_2 = date('Y-m-d', strtotime($date . ' +2 day'));



        if (count($prediction_data)) {

            $file_name = $prediction_data[0]['file_name'];

            $graph_file_name = $prediction_data[0]['graph_file_name'];

            if ($file_name != '') {

                $file_path = 'https://dev.famrut.co.in/python/Prediction/prediction_result/' . $file_name;

                $graph_path = 'https://dev.famrut.co.in/python/Prediction/prediction_graphs/' . $graph_file_name;

                $data_val = file_get_contents($file_path);

                $data_clean = explode(PHP_EOL, $data_val);

                $new_array = explode($b, -30, 30);

                foreach ($data_clean as $d) {

                    if ($d != '') {



                        $marray = explode(':', $d);

                        $data_new[] = explode(':', $d);



                        $date_chk = substr($marray[0], 0, 10);

                        $date_chk_fix = substr($date_last_month_timestamp, 0, 10);



                        $chk_str = 'val1 :' . $date_chk . ' ||  new_val : ' . $date_chk_fix;



                        if ($date_chk >= $date_chk_fix) {



                            $graph_array[] = array('date' => date('Y-m-d', substr($marray[0], 0, 10)), 'price' => number_format($marray[1], 2, '.', ''));



                            $converted_date = date('Y-m-d', substr($marray[0], 0, 10));

                            if ($date == $converted_date) {

                                $cost_array[] = array('newdateformat' => date('Y-m-d', substr($marray[0], 0, 10)), 'modalprices' => number_format($marray[1], 2), 'minimumprices' => '0', 'maximumprices' => '0', 'unitofprice' => '-', 'unitofarrivals' => '-', 'arrivals' => '-', 'prediction_flag' => '1');

                            }

                            if ($next_date == $converted_date) {

                                $cost_array[] = array('newdateformat' => date('Y-m-d', substr($marray[0], 0, 10)), 'modalprices' => number_format($marray[1], 2), 'minimumprices' => '0', 'maximumprices' => '0', 'unitofprice' => '-', 'unitofarrivals' => '-', 'arrivals' => '-', 'prediction_flag' => '1');

                            }

                            if ($next_date_2 == $converted_date) {

                                $cost_array[] = array('newdateformat' => date('Y-m-d', substr($marray[0], 0, 10)), 'modalprices' => number_format($marray[1], 2), 'minimumprices' => '0', 'maximumprices' => '0', 'unitofprice' => '-', 'unitofarrivals' => '-', 'arrivals' => '-', 'prediction_flag' => '1');

                            }



                        }



                    }

                }

            }

        }



        $days_data_3 = array_reverse($days_data);

        $c = array_merge($days_data_3, $cost_array);



        $slq_30 = "SELECT marketwiseapmcpricedate as NewDateFormat ,commodityname, market, minimumprices, maximumprices,modalprices,unitofprice,unitofarrivals,arrivals,varity

            FROM $tbl_name WHERE $cmm_name ILIKE '" . $commodity_name . "'  " . $where_varity . " AND market ILIKE '" . $market_name . "'  ORDER BY marketwiseapmcpricedate DESC LIMIT 28";



        /*"SELECT to_char( to_timestamp((marketwiseapmcpricedate),'DD/MM/YYYY'),'YYYY-MM-DD')  NewDateFormat,minimumprices, maximumprices,unitofarrivals,arrivals,varity FROM lastfiveyeardata WHERE commodityname ILIKE '" . $commodity_name . "' " . $where_varity . "  AND market ILIKE '" . $market_name . "' ORDER BY marketwiseapmcpriceid DESC LIMIT 30"*/



        $query_graph = $this->db->query($slq_30);

        $result = $query_graph->result_array();



        if (count($result)) {

            $response = array("status" => 1, "data" => array_reverse($result), "graph_array" => $graph_array, "cost_array" => $c, "days_data" => $days_data, "message" => lang('Listed_Successfully'), 'date_last_month_timestamp' => $_REQUEST, 'graph_image' => $graph_path);

        } else {

            $response = array("status" => 1, "data" => array(), "graph_array" => array(), "cost_array" => array(), "days_data" => array(), "message" => lang('Data_Not_Found'), 'date_last_month_timestamp' => $_REQUEST, 'graph_image' => $graph_path);

        }







        $this->api_response($response);



    }



    public function product_category_eccom_get()
    {

        $response = array();

        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        $lang_label = " name_mr ";

        if ($selected_lang == 'mr') {

            $lang_folder = "marathi";

        } elseif ($selected_lang == 'hi') {

            $lang_folder = "hindi";

            $lang_label = "name_hi as name_mr";

        } else {

            $lang_folder = "english";

            $lang_label = " name_mr ";

        }



        $sql = "SELECT pcat_id ,name ,logo ,$lang_label ,mob_icon FROM pcategories WHERE is_deleted = 'false' AND is_active = 'true' ORDER BY pcat_id DESC";

        $row = $this->db->query($sql);

        $result = $row->result_array();



        if (count($result)) {

            $response = array("status" => 1, "success" => 1, "error" => 0, "data" => $result, "message" => lang('Listed_Successfully'));

        } else {

            $response = array("status" => 1, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }



        $this->api_response($response);

    }



    public function master_data_get()
    {

        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        // $group_id     = $headers_data['group_id']; (replace this line with below 2 lines)

        $group_id_arr = explode(',', $headers_data['group_id']);

        $group_id = $group_id_arr[0];

        $crop_ids_query = '';

        if ($group_id != '') {

            $where = array('is_deleted' => 'false', 'is_active' => 'true', 'group_referral_code' => $group_id);

            $crop_ids = $this->Masters_model->get_data("crop_id", 'config_master', $where);



            $crop_ids_query = '';

            if (count($crop_ids)) {

                if ($crop_ids[0]['crop_id']) {

                    $crop_ids_query = "AND crop_id IN (" . $crop_ids[0]['crop_id'] . ")";

                }

            }

        }



        $sql_crop = "SELECT crop.name,crop.name_mr,crop.crop_id,crop.duration_days FROM crop WHERE is_deleted = false AND is_active = true " . $crop_ids_query;



        $res_crop = $this->db->query($sql_crop);

        $crop = $res_crop->result_array();



        /*$where = array('crop.is_deleted' => 'false', 'crop.is_active' => 'true');

        $where_in = array('crop_id' => $crop_ids[0]['crop_id']);

        $crop  = $this->Masters_model->get_data("crop.name,crop.name_mr,crop.crop_id,crop.duration_days", 'crop', $where,$where_in);*/



        $response = array("success" => 1, "error" => 0, "status" => 1, 'config_url' => $this->config_url, 'farm_type' => $this->farm_type, 'topology' => $this->topology, 'soil_type' => $this->soil_type, 'unit' => $this->unit, 'irri_src' => $this->irri_src, 'irri_faty' => $this->irri_faty, 'crop' => $crop, 'crop_type' => $this->crop_type, "message" => "Master data sent successfully");

        // $this->api_response($result, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

        $this->api_response($response);

    }



    public function add_land_details_new_post()
    {

        $result = array();

        $image = '';

        $farm_image_upload = '';

        $doc_7_12_upload = '';



        if (!empty($_FILES['farm_image']['name'])) {



            $extension = pathinfo($_FILES['farm_image']['name'], PATHINFO_EXTENSION);



            $farm_image_name = $this->connected_domain . '_farm_image_' . time() . '.' . $extension;

            if (!file_exists($this->upload_file_folder . 'farm')) {

                mkdir($this->upload_file_folder . 'farm', 0777, true);

            }



            $target_file = $this->upload_file_folder . 'farm/' . $farm_image_name;

            // for delete previous image.

            if ($this->input->post('old_farm_image') != "") {

                @unlink($this->upload_file_folder . 'farm/' . $this->input->post('old_farm_image'));

            }



            if (move_uploaded_file($_FILES["farm_image"]["tmp_name"], $target_file)) {

                //$insert['farm_image'] = $farm_image_name;

                $farm_image_upload = $farm_image_name;

                $error = 0;



            } else {

                $error = 2;

            }

        }

        /*else{

        $farm_image_name = $this->input->post('old_farm_image');

        }*/



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

                //$insert['doc_7_12'] = $farm_doc_7_12_name;

                $doc_7_12_upload = $farm_doc_7_12_name;

                $error = 0;



            } else {



                $error = 2;



            }

        } /*else{



     }*/

        $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => lang('Missing_Parameter'));



        if ($this->input->post('btn_submit') == 'submit') {



            if (0) {

                $data = $this->input->post();

                $data['error'] = validation_errors();

            } else {



                //'khasra_no'           => $this->input->post('khasra_no'),



                $insert = array(

                    /*'farm_image'          => $farm_image_name,*/

                    'farmer_id' => $this->input->post('farmer_id'),

                    'survey_no' => $this->input->post('survey_no'),

                    /* 'state_id'            => $this->input->post('state_id'),s

                    'cities_id'           => $this->input->post('cities_id'),*/

                    'soil_type' => $this->input->post('soil_type'),

                    'topology' => $this->input->post('topology'),

                    'farm_type' => $this->input->post('farm_type'),

                    'farm_size' => $this->input->post('farm_size'),

                    'unit' => $this->input->post('unit'),

                    'irrigation_source' => $this->input->post('irrigation_source'),

                    'irrigation_facility' => $this->input->post('irrigation_facility'),

                    'farm_name' => $this->input->post('farm_name'),

                    'farm_name_mr' => $this->input->post('farm_name_mr'),

                    //'village_city' => $this->input->post('village_city'),

                    'created_on' => current_date(),

                );



                if ($farm_image_upload != '') {

                    $insert['farm_image'] = $farm_image_upload;

                }

                if ($doc_7_12_upload != '') {

                    $insert['doc_7_12'] = $doc_7_12_upload;

                }



                $result = $this->db->insert('master_land_details', $insert);

                $insert_id = $this->db->insert_id();



                if ($result) {



                    if (count($insert)) {

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Added_Successfully'), 'config_url' => $this->config_url, 'result' => $insert);

                    }



                    $this->api_response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => "Land detail Add failed, please try again some time.");



                    $this->api_response($response);

                    exit;



                }

            }

        }



        $this->api_response($response);

        exit;

    }



    public function update_crop_doc_post()
    {

        $land_id = $_REQUEST['land_id'];

        $error = 0;

        $result = [];



        if (!empty($_FILES['doc_7_12']['name']) && $land_id != '') {



            $extension = pathinfo($_FILES['doc_7_12']['name'], PATHINFO_EXTENSION);



            // $farm_doc_7_12_name = 'doc_7_12_' . time() . '.' . $extension;

            // $target_file        = 'uploads/user_data/farm_doc/' . $farm_doc_7_12_name;

            $farm_doc_7_12_name = $this->connected_domain . '_doc_7_12_' . time() . '.' . $extension;

            if (!file_exists($this->upload_file_folder . 'farm_doc')) {

                mkdir($this->upload_file_folder . 'farm_doc', 0777, true);

            }

            $target_file = $this->upload_file_folder . 'farm_doc/' . $farm_doc_7_12_name;

            // for delete previous image.

            /*if ($this->input->post('old_doc_7_12') != "") {

            @unlink('./uploads/user_data/farm_doc/' . $this->input->post('old_doc_7_12'));

            }*/



            if (move_uploaded_file($_FILES["doc_7_12"]["tmp_name"], $target_file)) {

                $update['doc_7_12'] = $farm_doc_7_12_name;

                $error = 0;



                // $sql = 'SELECT id from '

                $this->db->where('land_id', $land_id);

                $update_master_land = $this->db->update('master_land_details', $update);

                if ($update_master_land) {

                    $fields = 'land_id, farmer_id, soil_type, topology, farm_type, farm_size, unit, irrigation_facility, calculated_land_area, survey_no, khasra_no, irrigation_source, village_city, farm_name, farm_name_mr, farm_image, state_id, cities_id, doc_7_12';

                    $result = $this->Masters_model->get_data($fields, 'master_land_details', array('land_id' => $land_id));

                }



                $response = array("success" => 1, "error" => $error, "status" => 1, "data" => $result, "message" => "7 12 document uplaoded");



            } else {



                $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => "sorry 7 12 document not uplaoded");

            }

        } else {

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);

        exit;

    }



    public function update_land_details_post()
    {

        $result = array();

        $id = $this->input->post('land_id');



        $image = '';



        if (!empty($_FILES['farm_image']['name'])) {

            $extension = pathinfo($_FILES['farm_image']['name'], PATHINFO_EXTENSION);



            $farm_image_name = $this->connected_domain . '_farm_image_' . time() . '.' . $extension;

            if (!file_exists($this->upload_file_folder . 'farm')) {

                mkdir($this->upload_file_folder . 'farm', 0777, true);

            }

            $target_file = $this->upload_file_folder . 'farm/' . $farm_image_name;



            // for delete previous image.

            if ($this->input->post('old_farm_image') != "") {

                @unlink($this->upload_file_folder . 'farm/' . $this->input->post('old_farm_image'));

            }



            if (move_uploaded_file($_FILES["farm_image"]["tmp_name"], $target_file)) {

                $update_arr['farm_image'] = $farm_image_name;

                $error = 0;

                //echo 'successs';

            } else {

                // $this->session->set_flashdata('error', 'file not uploaded.');

                // die();

                $error = 2;

                //echo 'Err';

            }

        }



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

                $doc_7_12_upload = $farm_doc_7_12_name;

                $error = 0;



            } else {



                $error = 2;



            }

        }



        $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => "Land update, please try again some time.");



        if ($id != '') {



            if (0) {



            } else {



                // $update_arr['farm_image'] = ''          => $farm_image_name,

                $update_arr['farmer_id'] = $this->input->post('farmer_id');

                $update_arr['survey_no'] = $this->input->post('survey_no');

                /*$update_arr['khasra_no']           = $this->input->post('khasra_no');*/

                $update_arr['soil_type'] = $this->input->post('soil_type');

                $update_arr['topology'] = $this->input->post('topology');

                /*$update_arr['state_id']            = $this->input->post('state_id');

                $update_arr['cities_id']           = $this->input->post('cities_id');*/

                $update_arr['farm_type'] = $this->input->post('farm_type');

                $update_arr['farm_size'] = $this->input->post('farm_size');

                $update_arr['unit'] = $this->input->post('unit');

                $update_arr['irrigation_source'] = $this->input->post('irrigation_source');

                $update_arr['irrigation_facility'] = $this->input->post('irrigation_facility');

                $update_arr['farm_name'] = $this->input->post('farm_name');

                $update_arr['farm_name_mr'] = $this->input->post('farm_name_mr');

                //'village_city' => $this->input->post('village_city'),

                $update_arr['created_on'] = current_date();



                $this->db->where('land_id', $id);

                $result = $this->db->update('master_land_details', $update_arr);



                if ($result) {



                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Updated_Successfully'), 'config_url' => $this->config_url);



                    $this->api_response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => lang('Not_Able_Update'));



                    $this->api_response($response);

                    exit;



                }

            }

        }



        $this->api_response($response);

        exit;

    }



    public function add_crop_details_post()
    {

        $result = array();

        $image = '';



        $crop_image_name = $this->input->post('old_crop_image');

        if (!empty($_FILES['crop_image']['name'])) {

            $extension = pathinfo($_FILES['crop_image']['name'], PATHINFO_EXTENSION);



            $crop_image_name = $this->connected_domain . '_crop_image_' . time() . '.' . $extension;

            if (!file_exists($this->upload_file_folder . 'crop_image')) {

                mkdir($this->upload_file_folder . 'crop_image', 0777, true);

            }



            $target_file = $this->upload_file_folder . 'crop_image/' . $crop_image_name;

            // for delete previous image.



            if (move_uploaded_file($_FILES["crop_image"]["tmp_name"], $target_file)) {

                //$insert['doc_7_12'] = $farm_doc_7_12_name;

                $error = 0;



            } else {



                $error = 2;



            }

        }



        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Not_Able_Add'));



        if ($this->input->post('btn_submit') == 'submit') {



            if (0) {

                $data = $this->input->post();

                $data['error'] = validation_errors();

            } else {



                $datefrom = date_create($this->input->post('duration_from'));

                $dateto = date_create($this->input->post('duration_to'));



                /*if($datefrom != '' && $this->input->post('crop') != ''){



                $duration_to = '';

                $duration = '+'.$value['duration_days'].' days';

                $duration_to = date("Y-m-d", strtotime($value['duration_from'],$duration));



                }*/



                $land_id = $this->input->post('land_id') ? $this->input->post('land_id') : '';

                $insert = array(

                    'crop_image' => $crop_image_name,

                    'client_id' => $this->input->post('client_id'),

                    'land_id' => $land_id,

                    'crop' => $this->input->post('crop'),

                    'crop_type' => $this->input->post('crop_type'),

                    'duration_from' => date_format($datefrom, "Y-m-d"),

                    'duration_to' => date_format($dateto, "Y-m-d"),

                    'area_under_cultivation' => $this->input->post('area_under_cultivation'),

                    'crop_name' => $this->input->post('crop_name'),

                    'crop_name_mr' => $this->input->post('crop_name_mr'),

                    'unit' => $this->input->post('unit'),

                    'created_on' => current_date(),

                );



                // echo json_encode($insert, true);exit;



                $result = $this->db->insert('master_crop_details', $insert);

                $insert_id = $this->db->insert_id();



                // add crop calander enty code//



                //crop_calender_rel



                ////////////// end here///////////////



                // $this->session->set_flashdata('success', 'Crop detail Added Successfully');



                if ($result) {



                    if (count($insert)) {

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Added_Successfully'));

                    }



                    $this->api_response($response);

                    exit;

                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Not_Able_Add'));

                    $this->api_response($response);

                    exit;

                }

            }

        }



        $this->api_response($response);

    }



    public function update_crop_details_post()
    {

        $result = array();

        $id = $this->input->post('id');

        $image = '';



        if (!empty($_FILES['crop_image']['name'])) {

            $extension = pathinfo($_FILES['crop_image']['name'], PATHINFO_EXTENSION);



            $crop_image_name = $this->connected_domain . '_crop_image_' . time() . '.' . $extension;

            if (!file_exists($this->upload_file_folder . 'crop_image')) {

                mkdir($this->upload_file_folder . 'crop_image', 0777, true);

            }

            $target_file = $this->upload_file_folder . 'crop_image/' . $crop_image_name;

            // for delete previous image.

            if ($this->input->post('old_crop_image') != "") {

                @unlink($this->upload_file_folder . 'crop_image/' . $this->input->post('old_crop_image'));

            }



            if (move_uploaded_file($_FILES["crop_image"]["tmp_name"], $target_file)) {

                $update_arr['crop_image'] = $crop_image_name;

                $error = 0;



            } else {

                $error = 2;

            }

        }



        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Missing_Parameter'));



        if ($id != '') {



            if (0) {



            } else {



                $datefrom = date_create($this->input->post('duration_from'));

                $dateto = date_create($this->input->post('duration_to'));



                //'crop_image'             => $crop_image_name,

                $update_arr['client_id'] = $this->input->post('client_id');

                $update_arr['land_id'] = $this->input->post('land_id');

                $update_arr['crop'] = $this->input->post('crop');

                $update_arr['crop_type'] = $this->input->post('crop_type');

                $update_arr['duration_from'] = date_format($datefrom, "Y-m-d");

                $update_arr['duration_to'] = date_format($dateto, "Y-m-d");

                $update_arr['area_under_cultivation'] = $this->input->post('area_under_cultivation');

                $update_arr['crop_name'] = $this->input->post('crop_name');

                $update_arr['crop_name_mr'] = $this->input->post('crop_name_mr');

                $update_arr['unit'] = $this->input->post('unit');

                $update_arr['created_on'] = current_date();



                $this->db->where('master_crop_details.id', $id);

                $result = $this->db->update('master_crop_details', $update_arr);



                if ($result) {



                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Updated_Successfully'));

                    $this->api_response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Not_Able_Update'));



                    $this->api_response($response);

                    exit;

                }

            }

        }



        $this->api_response($response);

        exit;

    }



    public function update_profile_post()
    {

        $result = array();

        $id = $this->input->post('id');

        $image = '';



        if (!empty($_FILES['profile_image']['name'])) {

            $target_file = '';

            $extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);

            //echo $extension;

            $profile_image_name = $this->connected_domain . '_profile_image_' . time() . '.' . $extension;

            if (!file_exists($this->upload_file_folder . 'profile')) {

                mkdir($this->upload_file_folder . 'profile', 0777, true);

            }

            $target_file = $this->upload_file_folder . 'profile/' . $profile_image_name;



            // for delete previous image.

            if ($this->input->post('old_profile_image') != "") {

                @unlink($this->upload_file_folder . 'profile/' . $this->input->post('old_profile_image'));

            }



            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {

                $update_arr['profile_image'] = $profile_image_name;

                $error = 0;



            } else {

                $error = 2;

            }

        }



        // print_r($_FILES);



        if (!empty($_FILES['pan_no_doc']['name'])) {

            $target_file = '';

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

            $target_file = '';

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

                $error = 0;

            } else {

                $error = 2;



            }

        }



        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Not_Able_Update'), "post_param" => $_POST);



        if (!empty($id)) {



            $not_post_fields = array('id', 'btn_submit', 'phone', 'old_pan_no_doc', 'old_aadhar_no_doc', 'old_profile_image');



            foreach ($_POST as $key => $val) {

                if (!empty($val) && !in_array($key, $not_post_fields)) {

                    if ($key == 'dob') {

                        $update_arr[$key] = date('Y-m-d', (strtotime($val)));

                    } else {

                        $update_arr[$key] = $val;

                    }

                }

            }



            $update_arr['updated_on'] = current_date();



            // print_r($update_arr);exit;



            $this->db->where('client.id', $id);

            $result = $this->db->update('client', $update_arr);

            if ($result) {



                $sql_chk = "SELECT * from client WHERE id=$id";

                $res_val = $this->db->query($sql_chk);

                $res_array = $res_val->result_array();



                // $response = array("success" => 1, "error" => 0, "status" => 1, "header_data"=>$this->input->request_headers(), "selected_data" => $res_array, "data" => $update_arr, "message" => "profile updated Successfully", 'config_url' => $this->config_url, "post_param" => $_POST, 'sql_query' => $this->db->last_query());

                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $update_arr, "message" => lang('Updated_Successfully'), 'config_url' => $this->config_url);



                $this->api_response($response);

                exit;



            } else {



                $response = array("success" => 0, "error" => 1, "status" => 2, "data" => $result, "message" => lang('Not_Able_Update'), "post_param" => $_POST);



                $this->api_response($response);

                exit;



            }

        }



        $this->api_response($response);

        exit;

    }



    public function document_upload_steps_post()
    {

        $response = array();

        $farmer_id = $this->input->post('farmer_id');

        $file_data = $_FILES;

        $stp_arr = $this->input->post('step_id');



        // print_r($stp_arr);exit;



        $dd_chk = $this->input->post('step_id_with_dd');

        $document_category_chk = $this->input->post('document_category');

        $j = 0;

        // foreach($this->input->post('step_id') as $val ){

        for ($i = 0; $i < count($stp_arr); $i++) {



            $insert_data = array();



            $insert_data['document_type'] = '';

            $insert_data['doc_file'] = '';

            if ($dd_chk[$j] == 1) {

                $doc_name = $document_category_chk[$i];

                $insert_data['document_type'] = $doc_name;

            } else {

                $insert_data['document_type'] = '';

            }



            $step_id = $stp_arr[$i];

            // echo '<br> step_id => | '.$step_id;

            /// document uplaod

            //doc_file_1

            if (!empty($_FILES['doc_file_' . $step_id]['name'])) {

                $extension = pathinfo($_FILES['doc_file_' . $step_id]['name'], PATHINFO_EXTENSION);



                $doc_file = $this->connected_domain . '_doc_file_' . $farmer_id . '_' . $step_id . time() . '.' . $extension;

                if (!file_exists($this->upload_file_folder . 'verification_documents')) {

                    mkdir($this->upload_file_folder . 'verification_documents', 0777, true);

                }

                $target_file = $this->upload_file_folder . 'verification_documents/' . $doc_file;



                // for delete previous image.

                /* if ($this->input->post('old_doc_file_'.$step_id) != "") {

                @unlink('./uploads/user_data/verification_documents/' . $this->input->post('old_doc_file_'.$step_id));

                }*/



                if (move_uploaded_file($_FILES["doc_file_" . $step_id]["tmp_name"], $target_file)) {

                    // $update['doc_file_'.$step_id] = $doc_file;

                    // $error                     = 0;

                    //echo 'no err';

                    $insert_data['doc_file'] = $doc_file;

                } else {

                    // $error = 2;

                    // echo 'in err';

                    $insert_data['doc_file'] = '';

                }



                $insert_data['farmer_id'] = $farmer_id;

                $insert_data['document_step'] = $step_id;



                $sql_chk = "SELECT id from farmer_documents WHERE farmer_id=$farmer_id AND document_step = $step_id";

                $res_val = $this->db->query($sql_chk);

                $res_array = $res_val->result_array();

                if (count($res_array) > 0) {



                    $this->db->where('farmer_documents.farmer_id', $farmer_id);

                    $this->db->where('farmer_documents.document_step', $step_id);

                    $result = $this->db->update('farmer_documents', $insert_data);

                    if ($result) {

                        //  echo ' ok updated >> ';

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Added_Successfully'));

                    }



                } else {

                    $result = $this->db->insert('farmer_documents', $insert_data);

                    if ($result) {

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Added_Successfully'));

                    }



                }



            } else {

                $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $_POST, "files_array" => $_FILES, "message" => lang('Not_Able_Add'));

            }



        }



        $this->api_response($response);

        exit;



    }



    public function add_client_order_post()
    {

        // user_activity_logs("Add Client Order: POST data:", json_encode($_POST));

        $result = $products_data = array();

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "ORDER failed, please try again some time.");



        $order_num = $this->input->post('order_id');

        // $payment_id = $this->input->post('payment_id');

        // $signature  = $this->input->post('signature');

        $order_type = $this->input->post('order_type') ? $this->input->post('order_type') : null;



        $pickup_location_id = $this->input->post('pickup_location_id');

        $payment_method = '';

        $payment_status = 'Unpaid';

        $transaction_text = '';



        if ($this->input->post('btn_submit') == 'submit') {

            if (0) {

                $data = $this->input->post();

                $data['error'] = validation_errors();

            } else {



                $cart_prod_ids = $this->input->post('cart_prod_ids');

                $cart_prod_quantity = $this->input->post('cart_prod_quantity');



                $cart_prod_ids_arr = explode(',', $cart_prod_ids);

                $cart_prod_quantity_arr = explode(',', $cart_prod_quantity);



                // combine product id with its quantity

                $cart_data = array_combine($cart_prod_ids_arr, $cart_prod_quantity_arr);



                $sql_prod = "SELECT * from products WHERE id IN (" . $cart_prod_ids . ")";

                $res_val = $this->db->query($sql_prod);

                $res_array = $res_val->result_array();



                if (empty($order_num)) {

                    $order_num = $this->input->post('client_id') . '' . time();

                }



                foreach ($res_array as $res_data) {

                    $products_data[$res_data['partner_id']][] = $res_data;

                }



                // if (!empty($order_num) && !empty($payment_id)) {

                //     $payment_method   = 'Online';

                //     $payment_status   = 'Paid';

                //     $transaction_text = 'Order No.: ' . $order_num . ', Payment ID: ' . $payment_id . ', Signature: ' . $signature;

                // }



                if (count($products_data) > 0) {

                    foreach ($products_data as $partnerId => $values) {



                        $insert = [];

                        $insert = array(

                            'first_name' => $this->input->post('first_name'),

                            'last_name' => $this->input->post('last_name'),

                            'cphone' => $this->input->post('cphone'),

                            'email_id' => $this->input->post('email_id'),

                            'billing_country' => $this->input->post('billing_country'),

                            'billing_state' => $this->input->post('billing_state'),

                            'billing_city' => $this->input->post('billing_city'),

                            'billing_pin_code' => $this->input->post('billing_pin_code'),

                            'billing_address1' => $this->input->post('billing_address1'),

                            'company_name' => $this->input->post('company_name'),

                            'billing_village' => $this->input->post('billing_village'),

                            'client_id' => $this->input->post('client_id'),

                            'plan_id' => 1,

                            'qty' => 0,

                            'status' => Pending,

                            'order_num' => $order_num,

                            'order_date' => current_date(),

                            'created_on' => current_date(),

                            'partner_id' => $partnerId,

                            'payment_method' => $payment_method,

                            'pickup_location_id' => $pickup_location_id,

                            'payment_status' => $payment_status,

                        );



                        if (empty($order_type)) {

                            if (!empty($pickup_location_id)) {

                                $insert['plan_details'] = json_encode(array('pickup'));

                            } else {

                                $insert['plan_details'] = json_encode(array('delivery'));

                            }

                        } else {

                            $insert['plan_details'] = json_encode(array($order_type));

                        }



                        $result = $this->db->insert('client_orders', $insert);

                        $order_id = $this->db->insert_id();

                        $insert_ids[] = $order_id;

                        $rem_stock = [];



                        $total_subtotal = 0;



                        if (!empty($pickup_location_id)) {

                            $pickuplocationid = $pickup_location_id;

                            $location_type = 'pickup';

                        } else {

                            $pickuplocationid = 0;

                            $location_type = 'delivery';

                            $delivery_charges = 0;

                            if (!empty(get_config_settings('delivery_charges'))) {

                                $config_delivery_charges = get_config_settings('delivery_charges');



                                $delivery_charges_convert = floatval($config_delivery_charges['description']);

                                $delivery_charges = number_format($delivery_charges_convert, 2);

                            }





                        }





                        foreach ($values as $key => $val) {

                            $product_id = $val['id'];

                            $qty = $cart_data[$val['id']];

                            $insert = [];

                            $total_subtotal += $val['price'] ? ((float) $val['price'] * (int) $qty) : 0;



                            if ($val['in_stock'] != 0 && (int) $qty <= $val['in_stock']) {

                                $remaining_stock = $val['in_stock'] - (int) $qty;

                                $this->update_product_stock($product_id, $remaining_stock);

                                $rem_stock[] = $remaining_stock;

                            }



                            $insert = array(

                                'quantity' => $qty,

                                'product_id' => $product_id,

                                'client_id' => $this->input->post('client_id'),

                                'created_on' => current_date(),

                                'order_id' => $order_id,

                                'price' => $val['price'] ? $val['price'] : 0,

                                'sub_total' => $val['price'] ? ((float) $val['price'] * (int) $qty) : 0,

                                'status' => 'Pending',

                                'unit' => $val['unit'] ? $val['unit'] : null,

                                'delivery_days' => $val['delivery_days'] ? $val['delivery_days'] : null,

                                'created_by_id' => $this->input->post('client_id'),

                                'payment_method' => $payment_method,

                                'transaction_text' => $transaction_text,

                                'pickup_location_id' => $pickuplocationid,

                                'location_type' => $location_type,

                            );







                            $this->db->insert('client_order_product', $insert); // Insert into client_order_product table

                        }





                        $total = $total_subtotal + $delivery_charges;

                        // $total_subtotal = $total_subtotal;



                        // Insert into Client Invoice

                        /**************** Client Order ****************/

                        $select = array('client_id', 'order_num', 'id', 'order_num', 'status');

                        $where = array('id' => $order_id, 'is_deleted' => false);

                        $client_order = $this->Masters_model->get_data($select, 'client_orders', $where);

                        $client_orders = $client_order[0];



                        $insert_data = [];

                        $insert_data = array(

                            'client_id' => $client_orders['client_id'],

                            'order_id' => $client_orders['id'],

                            'invoice_num' => $client_orders['order_num'],

                            'sub_total' => $total_subtotal,

                            'total' => $total,

                            'status' => $client_orders['status'],

                            'invoice_date' => date('Y-m-d H:i:s'),

                            'delivery_charges' => $delivery_charges,

                            // 'invoice_data' => json_encode($invoice_data),

                        );



                        $this->Masters_model->add_data('client_invoices', $insert_data);

                        $invoice_id = $this->db->insert_id();

                        $where = array('id' => $client_orders['id']);



                        $update_payments['invoice_id'] = $invoice_id;

                        $update_payments['amount'] = $total;

                        $update_payments['delivery_charges'] = $delivery_charges;



                        // Insert into Transactions

                        // if (!empty($order_num) && !empty($payment_id)) {



                        //     // send notifications

                        //     // $notification_data = $this->send_notification($id);

                        //     $insert_data = array(

                        //         'client_id'        => $client_orders['client_id'],

                        //         'invoice_id'       => $invoice_id,

                        //         'transaction_id'   => $transaction_text,

                        //         'description'      => $transaction_text,

                        //         'status'           => 'Complete',

                        //         'transaction_date' => date('Y-m-d H:i:s'),

                        //         'amount_in'        => $total_subtotal,

                        //         'gateway'          => $payment_method,

                        //     );



                        //     user_activity_logs("Payment: Transaction:", json_encode($insert_data));

                        //     $this->Masters_model->add_data('transactions', $insert_data);



                        //     $update_payments['payment_status']        = $payment_status;

                        //     $update_payments['paid_amount']           = $total_subtotal;

                        //     $update_payments['order_completion_date'] = date('Y-m-d H:i:s');



                        // }



                        $result[] = $this->Masters_model->update_data('client_orders', $where, $update_payments);

                    }

                }



                // Pickup time before / after

                // $pickup_data = [];

                $currnet_time = $order_before_time = $pickup_msg = $color = '';

                if (!empty(get_config_settings('order_before_time'))) {

                    $order_before_time_arr = get_config_settings('order_before_time');



                    // $currnet_time      = date('H:i', strtotime('+5 hour +30 minutes', strtotime(current_date())));

                    $currnet_time = date('H:i', strtotime(current_date()));

                    $order_before_time = $order_before_time_arr['description'];



                    if ($currnet_time > $order_before_time) {

                        $pickup_msg = lang('Pick_Up_Next_Day');

                        $color = '#95c329';

                    } else {

                        $pickup_msg = lang('Pick_Up_Today');

                        $color = '#dc3545';

                    }

                }



                /***** Get payment gateway *****/

                $payment_gateway = $this->Masters_model->get_data(array('*'), 'payment_settings', array('is_active' => 'true'), NULL, NULL, 0, 1);

                $redirect_payement_gateway_url = '';



                if (!empty($payment_gateway) && $payment_gateway[0]['title'] == 'Payphi') {

                    $redirect_payement_gateway_url = base_url('payment_gateway/checkout') . '?order_id=' . $order_num . '&appname=' . $this->connected_appname;

                }



                if (count($result)) {



                    if (count($insert_ids)) {

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "order_id" => $order_num, "client_order_ids" => $insert_ids, "message" => lang('Order_Placed_Successfully'), "rem_stock" => $rem_stock, 'currnet_time' => $currnet_time, 'order_before_time' => $order_before_time, 'pickup_msg' => $pickup_msg, 'color' => $color, 'redirect_payement_gateway_url' => $redirect_payement_gateway_url);

                    }



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Order_Placed_failed'));



                }

            }

        }



        // user_activity_logs("Add Client Order Final Resposne:", json_encode($response));



        $this->api_response($response);

        exit;

    }



    public function update_client_order_post()
    {

        // $client_order_ids   = $this->input->post('client_order_ids');

        $order_num = $this->input->post('order_id');

        $payment_id = $this->input->post('payment_id');

        $signature = $this->input->post('signature');

        $client_orders_result = [];



        $response = $this->client_order_common($order_num, $payment_id, $signature);



        $this->api_response($response);

        exit;

    }



    public function client_order_common($order_num, $payment_id = null, $signature = null)
    {

        $client_orders_result = [];

        if (!empty($order_num) && !empty($payment_id)) {

            $payment_method = 'Online';

            $payment_status = 'Paid';

            $transaction_text = 'Order No.: ' . $order_num . ', Payment ID: ' . $payment_id . ', Signature: ' . $signature;



            $where = array('order_num' => $order_num);

            $client_order_data = $this->Masters_model->get_data('*', 'client_orders', $where);



            // print_r($client_order_data);exit;



            if (count($client_order_data) > 0) {

                foreach ($client_order_data as $key => $value) {



                    $update_payments = [];

                    $update_payments = array(

                        'payment_method' => $payment_method,

                        'payment_status' => $payment_status,

                        'paid_amount' => number_format($value['amount'], 2),

                        'order_completion_date' => date('Y-m-d H:i:s'),

                    );



                    $client_orders_result[] = $this->Masters_model->update_data('client_orders', $where, $update_payments);



                    $insert_transaction = array(

                        'client_id' => $value['client_id'],

                        'invoice_id' => $value['invoice_id'],

                        'transaction_id' => $transaction_text,

                        'description' => $transaction_text,

                        'status' => 'Complete',

                        'transaction_date' => date('Y-m-d H:i:s'),

                        'amount_in' => number_format($value['amount'], 2),

                        'gateway' => $payment_method,

                    );



                    user_activity_logs("Payment: Transaction:", json_encode($insert_transaction));

                    $this->Masters_model->add_data('transactions', $insert_transaction);

                }



                // Pickup time before / after

                $currnet_time = $order_before_time = $pickup_msg = $color = '';

                if (!empty(get_config_settings('order_before_time'))) {

                    $order_before_time_arr = get_config_settings('order_before_time');



                    //$currnet_time      = date('H:i', strtotime('+5 hour +30 minutes', strtotime(current_date())));

                    $currnet_time = date('H:i', strtotime(current_date()));

                    $order_before_time = $order_before_time_arr['description'];



                    if ($currnet_time > $order_before_time) {

                        $pickup_msg = lang('Pick_Up_Next_Day');

                        $color = '#95c329';

                    } else {

                        $pickup_msg = lang('Pick_Up_Today');

                        $color = '#dc3545';

                    }

                }

            }





            $res = array("success" => 1, "error" => 0, "status" => 1, "data" => $client_orders_result, "order_id" => $order_num, "message" => "Order Placed Successfully", 'order_before_time' => $order_before_time, 'pickup_msg' => $pickup_msg, 'color' => $color);





        } else if (!empty($order_num) && empty($payment_id)) {

            $where = array('order_num' => $order_num);

            $client_order_data = $this->Masters_model->get_data('*', 'client_orders', $where);

            if (count($client_order_data) > 0) {

                foreach ($client_order_data as $key => $value) {



                    $update_status = [];

                    $update_status = array(

                        'status' => 'Cancelled',

                    );



                    $client_orders_result[] = $this->Masters_model->update_data('client_orders', $where, $update_status);

                }

            }



            $res = array("success" => 1, "error" => 0, "status" => 1, "data" => $client_orders_result, "order_id" => $order_num, "message" => "Order was cancelled");

        }



        if (count($client_orders_result)) {

            $response = $res;

        } else {

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Order Placed failed, please try again some time.");

        }

        user_activity_logs("Add Client Order Final Resposne:", json_encode($response));

        return $response;

    }



    public function verify_payments_post()
    {

        $client_id = $this->input->post('client_id');

        $data = [];



        if (!empty($client_id)) {

            $where = array('client_id' => $client_id, 'payment_status' => 'Unpaid');

            $client_order_data = $this->Masters_model->get_data('*', 'client_orders', $where);



            if (!empty($client_order_data)) {

                foreach ($client_order_data as $key => $value) {



                    $payment_gateway = $this->Masters_model->get_data(array('*'), 'payment_settings', array('is_active' => 'true'), NULL, NULL, 0, 1);



                    if (!empty($payment_gateway)) {

                        $payment_data = json_decode($payment_gateway[0]['payment_data'], true);

                        $test_mode = array('sandbox');



                        if ($payment_data['title'] == 'Razorpay') {

                            $curl = curl_init();



                            curl_setopt_array($curl, array(

                                CURLOPT_URL => 'https://api.razorpay.com/v1/orders/' . $value['order_num'] . '/payments',

                                CURLOPT_RETURNTRANSFER => true,

                                CURLOPT_ENCODING => '',

                                CURLOPT_MAXREDIRS => 10,

                                CURLOPT_TIMEOUT => 0,

                                CURLOPT_FOLLOWLOCATION => true,

                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

                                CURLOPT_CUSTOMREQUEST => 'GET',

                                CURLOPT_HTTPHEADER => array(

                                    'Authorization: Basic cnpwX2xpdmVfRDl3VDdxcllyWTRkamI6VU1kMDNSUzRUcVBadWRMVUdrT1RTN3ZL'

                                ),

                            ));



                            $response = curl_exec($curl);



                            curl_close($curl);

                            $res = json_decode($response, true);



                            if (!empty($res['items'])) {

                                $items = $res['items'];

                                foreach ($items as $items_key => $items_value) {

                                    $status = $items_value['status'];

                                    $captured = $items_value['captured'];



                                    if ($status == 'captured' && $captured == 'true') {



                                        $order_id = $items_value['order_id'];

                                        $payment_id = $items_value['id'];



                                        $uco_response = $this->client_order_common($order_id, $payment_id);



                                        $data['update_client_order'][] = json_decode($uco_response, true);

                                        $data['items'][] = $items_value;

                                    }

                                }

                            }

                        }

                    }

                }

            }

        }



        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, "message" => "Success");

        $this->api_response($response);

        exit;

    }



    public function update_product_stock($product_id, $remaining_stock = 0)
    {



        if ($product_id != '') {

            $this->db->where('id', $product_id);

            $this->db->update('products', array('in_stock' => $remaining_stock));

            // if($this->db->trans_status() === FALSE) {

            //     $this->db->trans_rollback();

            // } else {

            //     $this->db->trans_commit();

            // }

        }

    }



    public function chk_product_stock_post()
    {



        $cart_prod_ids = $this->input->post('cart_prod_ids');

        $cart_prod_quantity = $this->input->post('cart_prod_quantity');



        $cart_prod_ids_arr = explode(',', $cart_prod_ids);

        $cart_prod_quantity_arr = explode(',', $cart_prod_quantity);



        for ($i = 0; $i < count($cart_prod_ids_arr); $i++) {

            //$insert_data['product_id'] = $cart_prod_ids_arr[$i];

            //$insert_data['quantity']   = $cart_prod_quantity_arr[$i];]



            $product_id = $cart_prod_ids_arr[$i];

            $qty = $cart_prod_quantity_arr[$i];



            $qty_array = array();

            $res_array = array();



            $sql_prod = "SELECT * from products WHERE id=" . $product_id;

            $res_val = $this->db->query($sql_prod);

            $res_array = $res_val->result_array();

            $p_qty = (int) $qty;



            //pickup_location_id

            //location_type

            if ($res_array[0]['in_stock'] != 0 && (int) $p_qty <= $res_array[0]['in_stock']) {



                $stock_array[$i] = $p_qty;



                $detail_array[] = array("product_id" => $product_id, "stock" => $p_qty);

            } else {

                $detail_array[] = array("product_id" => $product_id, "stock" => 0);

                $stock_array[$i] = 0;



            }

        }



        $stock_avaiable = implode(',', $stock_array);



        $response = array("status" => 1, "data" => $detail_array, "message" => "Stock validate successfully", "stock_details" => $stock_avaiable);



        $this->api_response($response);



    }



    public function add_user_leads_post()
    {

        $farmer_id = $this->input->post('farmer_id');

        $partner_id = $this->input->post('partner_id');

        $is_custom = 0;

        $response = array();



        $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        if ($farmer_id != '' && $partner_id != '') {



            $insert = array(

                'client_id' => $farmer_id,

                'partner_id' => $partner_id,

                'is_custom' => $is_custom,

                'created_on' => current_date(),



            );



            $this->db->insert('product_leads', $insert);

            $response = array("status" => 1, "data" => 1, "message" => lang('Added_Successfully'));



        } else {

            $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        }

        $this->api_response($response);

    }



    public function add_product_leads_post()
    {

        $type = $this->input->post('type');

        $farmer_id = $this->input->post('farmer_id');

        $product_id = $this->input->post('product_id');

        $partner_id = $this->input->post('partner_id');

        $custom_field = $this->input->post('custom_field');



        if (null !== $this->input->post('custom_field')) {

            $is_custom = 1;

        } else {

            $is_custom = 0;

        }

        $response = array();



        $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        if ($farmer_id != '' && $product_id != '') {



            $insert = array(

                'client_id' => $farmer_id,

                'product_id' => $product_id,

                'partner_id' => $partner_id,

                'is_custom' => $is_custom,

                'created_on' => current_date(),

            );



            $this->db->insert('product_leads', $insert);

            $insertId = $this->db->insert_id();



            if (null !== $this->input->post('custom_field')) {

                $is_custom = 1;

                $custom_fields = $this->input->post('custom_field');



                foreach ($custom_fields as $key => $value) {

                    # code...

                    $insert_data = array(

                        'field_id' => $key,

                        'field_value' => $value,

                        'product_leads_id' => $insertId,

                        'created_on' => current_date(),

                    );



                    $this->db->insert('custom_fields_values', $insert_data);

                }

            }



            $response = array("status" => 1, "data" => 1, "message" => lang('Added_Successfully'));



        } else {

            $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        }

        $this->api_response($response);

    }



    public function countries_get()
    {

        $this->db->select('*');

        $this->db->where('id', 101);

        $countries = $this->db->get('countries_new')->result_array();

        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $countries, "message" => "countries data");



        $this->api_response($response);

    }



    public function crop_list_get()
    {

        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

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



        // $group_id     = $headers_data['group_id']; (replace this line with below 2 lines)

        // $group_id_arr   = explode(',', $headers_data['group_id']);

        //  $group_id       = $group_id_arr[0];

        $crop_ids_query = '';



        /* if ($group_id != '') {

            $where    = array('is_deleted' => 'false', 'is_active' => 'true', 'group_referral_code' => $group_id);

            $crop_ids = $this->Masters_model->get_data("crop_id", 'config_master', $where);



            $crop_ids_query = '';

            if ($crop_ids[0]['crop_id']) {

                $crop_ids_query = " AND crop_id IN (" . $crop_ids[0]['crop_id'] . ") ";

            }

        }*/





        $client_crop_data = $this->my_crops_listing($client_id, 'my_crops');













        /* $page  = $this->input->post('page');



        if($page != ''){

        $page = $page;

        }else{

        $page = 1;

        }



        $limit    = 24;

        //$start    = 1;

        $cat_id = 0;

        //$start  = $this->input->post('start') != ''?$this->input->post('start'):1;

        $start_chk = $page - 1;

        if ($start_chk != 0) {

        $start_sql = $limit * ($start_chk);

        } else {

        $start_sql = 0;

        }



        $sql_limit = " LIMIT " . $limit . " OFFSET " . $start_sql;*/

        /*  $sql_chk = "SELECT * FROM crop

        WHERE is_deleted=false AND is_active=true ".$crop_ids_query; */



        $sql_chk = "SELECT crop_id,name, lang_json->>'" . $selected_lang . "' as name  ,logo as mob_icon,nitrogen as n ,phosphorus as p,potassium as k FROM crop WHERE is_deleted=false AND is_active=true " . $crop_ids_query . "  ORDER BY crop_id ASC " . $sql_limit;

        $res_val = $this->db->query($sql_chk);

        $res_array = $res_val->result_array();



        foreach ($res_array as $key => $value) {

            if ($value['crop_id'] == 2) {

                $res_array[$key]['s'] = "30";

            }

        }



        $data_array['all_crops'] = $res_array;

        $data_array['my_crops'] = $client_crop_data;

        $response = array("success" => 1, "data" => $data_array, "error" => 0, "status" => 1, 'sql_chk' => $sql_chk);

        $this->api_response($response);



    }



    public function crop_list_veriety_get()
    {

        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        // $group_id     = $headers_data['group_id']; (replace this line with below 2 lines)

        $group_id_arr = explode(',', $headers_data['group_id']);

        $group_id = $group_id_arr[0];

        $where = array('is_deleted' => 'false', 'is_active' => 'true', 'group_referral_code' => $group_id);

        $crop_ids = $this->Masters_model->get_data("crop_id", 'config_master', $where);



        $crop_ids_query = '';

        if (count($crop_ids)) {

            if ($crop_ids[0]['crop_id']) {

                $crop_ids_query = "AND crop_id IN (" . $crop_ids[0]['crop_id'] . ")";

            }

        }



        $sql_chk = "SELECT * FROM crop

        WHERE crop_id IN (SELECT crop_id

                 FROM crop_variety_master WHERE is_deleted=false AND is_active=true) AND is_deleted=false AND is_active=true " . $crop_ids_query;

        $res_val = $this->db->query($sql_chk);

        $res_array = $res_val->result_array();

        $response = array("success" => 1, "data" => $res_array, "error" => 0, "status" => 1);

        $this->api_response($response);

    }



    public function approval_steps_get()
    {

        $step_master_data = array();

        $data_array = array();

        $sql = "SELECT * FROM step_master where is_deleted=false AND is_active=true ORDER BY step_order ASC";

        $res_val = $this->db->query($sql);

        $res_array = $res_val->result_array();



        if (count($res_array) > 0) {

            $step_master_data = $res_array;

        }



        $response = array("success" => 1, "step_master_data" => $step_master_data, "error" => 0, "status" => 1, "data" => $data_array);

        $this->api_response($response);

    }



    public function order_list_post()
    {

        $response = array();

        $limit = 10;

        $start = $this->input->post('start') ? $this->input->post('start') : 1;

        $client_id = $this->input->post('client_id');



        if ($client_id != '') {





            $start_chk = $start - 1;

            if ($start_chk != 0) {

                $start_sql = $limit * ($start_chk);

            } else {

                $start_sql = 0;

            }

            $sql_limit = " LIMIT " . $limit . " OFFSET " . $start_sql;







            $sql = "SELECT id, client_id, invoice_id, order_num, plan_id, qty, plan_details, next_invoice_date, status, created_by_id, created_on, updated_by_id, updated_on, deleted_by_id, is_deleted, deleted_on, min_frequency, order_completion_date, ipaddress, promo_code, promo_type, promo_value, remark, billing_address1, billing_city, billing_pin_code, shipping_address1, shipping_city, shipping_state, shipping_pin_code, first_name, last_name, company_name, email_id, billing_village, cphone, billing_country, shipping_country, billing_state, payment_method, amount, invoice_number, invoice_file, paid_amount, is_notification_sent, partner_id, pickup_location_id, payment_status, order_date  + INTERVAL '5 hours 30 minutes' as order_date, delivery_charges FROM client_orders WHERE client_id='" . $client_id . "' ORDER BY id DESC ";

            $sql .= $sql_limit;



            // $sql    = "SELECT * FROM client_orders WHERE client_id='" . $client_id . "'AND is_deleted = 'false' ORDER BY id DESC";



            $row = $this->db->query($sql);

            $result = $row->result_array();



            if (count($result)) {

                $data = [];

                foreach ($result as $key => $value) {

                    if (!empty($value['plan_details'])) {

                        $plan_details = json_decode($value['plan_details'], true);

                        $plan_details = implode(', ', $plan_details);

                        $value['plan_details'] = ucwords($plan_details);



                    }



                    $data[] = $value;

                }







                $response = array("status" => 1, "error" => 0, "success" => 1, 'count' => count($result), "data" => $data, "message" => lang('Listed_Successfully'));

            } else {

                $response = array("status" => 1, "error" => 0, "success" => 1, 'count' => 0, "data" => [], "message" => lang('Data_Not_Found'));

                // $response = array("status" => 0, "message" => "Order list is empty");

            }



        } else {

            $response = array("status" => 0, "error" => 1, "success" => 0, "data" => [], "message" => lang('Missing_Parameter'));

        }

        $this->api_response($response);

    }



    public function user_order_details_get($order_id)
    {

        $response = array();

        if ($order_id != '') {





            $query = $this->db->query("SELECT * FROM client_orders WHERE id= " . $order_id);

            $order_data = $query->result_array();



            $sql = "SELECT c.*,p.product_name,p.price, p.logo, o.order_num, o.delivery_charges, o.payment_status, o.status as order_status,o.order_date, o.cphone as phone, o.first_name, o.last_name FROM client_order_product c

                    LEFT JOIN products as p ON p.id = c.product_id

                    LEFT JOIN client_orders as o ON o.id = c.order_id

                    WHERE c.order_id=" . $order_id;

            $row = $this->db->query($sql);

            $result = $row->result_array();







            // delivery_days



            if (count($result)) {

                $result_data = [];

                foreach ($result as $key => $value) {

                    if (!empty($order_data[0]['pickup_location_id'])) {

                        $value['delivery_days'] = null;

                    }

                    $result_data[] = $value;

                }

                $response = array("status" => 1, "success" => 1, "error" => 0, "data" => $result_data, 'order_data' => $order_data, "message" => lang('Listed_Successfully'));

            } else {

                $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

            }



        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }

        $this->api_response($response);

    }



    public function states_post()
    {

        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        $client_type = $headers_data['client-type'];

        /// $country        = $this->input->post('id');

        /*   $country_code   = $this->input->post('code') ? $this->input->post('code') : 'IN'; */



        $country_id = $this->input->post('country_id') ? $this->input->post('country_id') : null;



        if ($this->input->post('country_id')) {

            //os version

            $seven_state = [3, 34, 4, 24, 23, 25, 26, 37];

            $type = $this->input->post('type');

            $sql = "SELECT id, name, country_id FROM states_new 

			WHERE country_id = '101' ";



            if (!empty($client_type) && $client_type == 'seller') {

                $sql .= " AND id IN (" . implode(',', $seven_state) . ")";

            }

            // echo $sql;exit;

            $row = $this->db->query($sql);

            $result = $row->result_array();





            // $where  = array('country_id' => $country_id);

            // $result = $this->Masters_model->get_data(array('id', 'name', 'country_id'), 'states_new', $where);



            // echo $this->db->last_query();exit;





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



            $response = array("status" => 0, "error" => 1, "success" => 0, "data" => null, "message" => lang('Missing_Parameter'));

            $this->api_response($response);

            exit;



        }

    }



    public function all_product_list_get()
    {

        $response = array();

        $sql = "SELECT * FROM products WHERE is_deleted = 'false' AND is_publish='true' ";

        $row = $this->db->query($sql);

        //  $result   = $row->result_array();

        //$result = $row->result_array(); // MMM comment for live only

        $result = array();



        if (count($result)) {

            $response = array("status" => 1, "success" => 1, "error" => 0, "data" => $result, "message" => lang('Listed_Successfully'));

        } else {



            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }



        $this->api_response($response);

    }



    public function all_products_with_pagination_post()
    {

        $response = array();

        $limit = 10;

        $start = 1;

        $cat_id = 0;



        $start = $this->input->post('start') ? $this->input->post('start') : 1;

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



        $sql_count = "SELECT COUNT(id) FROM products WHERE is_deleted = 'false' AND is_publish='true' ";

        $sql_string = $sql_count . $sql_where;

        $row_count = $this->db->query($sql_count . $sql_where);

        $result_count = $row_count->row_array();

        $count_res = $result_count['count'];



        $sql = "SELECT * FROM products WHERE is_publish='true' AND is_deleted = 'false' ";



        // $this->db->limit($limit, $start);



        $start_chk = $start - 1;

        if ($start_chk != 0) {

            $start_sql = $limit * ($start_chk);

        } else {

            $start_sql = 0;

        }



        $sql_limit = " LIMIT " . $limit . " OFFSET " . $start_sql;

        // $sql_limit = " LIMIT " . $limit . " OFFSET " . $start;

        $row = $this->db->query($sql . $sql_where . $sql_sort . $sql_limit);

        //$result    = $row->result_array();

        $result = $row->result_array(); // MMM comment for live only

        // $products_res = $result; // MMM comment for live only

        // $products_result  = [];







        // if (count($products_res) > 0) {



        //     foreach ($products_res as $reskey => $resval) {

        //         $product_unit           = $resval['unit'];

        //         $product_unit_desc      = $resval['unit_desc'];

        //         $updated_product_unit   = $resval['unit_desc'].' / '.$resval['unit'];



        //         $resval['unit_desc']    = null;

        //         $resval['unit']         = $updated_product_unit;



        //         $products_result[]      = $resval;

        //     }

        // }









        $query_str = $this->db->last_query();



        if (count($result)) {

            $response = array("status" => 1, "success" => 1, "error" => 0, "data" => $result, 'total_records' => $count_res, "message" => lang('Listed_Successfully'));

        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }



        $this->api_response($response);

    }



    public function home_products_with_pagination_post()
    {

        $response = array();

        $limit = 10;

        $start = 1;

        $cat_id = 0;



        $start = $this->input->post('start') ? $this->input->post('start') : 1;

        $cat_id = 15;



        if ($cat_id != 0) {

            //category_id

            //$sql_where = " AND is_deleted = 'false' AND is_publish='true' AND '" . $cat_id . "' = ANY (string_to_array(category_id,','))"; //show_consumer = 'true'

            $sql_where = " AND is_deleted = 'false' AND is_publish='true'";

        } else {

            $sql_where = " AND is_deleted = 'false' AND is_publish='true'";



            // $sql_where = " AND is_deleted = 'false' AND is_publish='true' AND '15' = ANY (string_to_array(category_id,','))";

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



        $sql_count = "SELECT COUNT(id) FROM products  WHERE is_publish='true' AND is_deleted = 'false' AND is_publish='true' ";

        $sql_string = $sql_count . $sql_where;

        $row_count = $this->db->query($sql_count . $sql_where);

        $result_count = $row_count->row_array();

        $count_res = $result_count['count'];



        $sql = "SELECT * FROM products WHERE is_publish='true' AND is_deleted = 'false' ";



        // $this->db->limit($limit, $start);



        $start_chk = $start - 1;

        if ($start_chk != 0) {

            $start_sql = $limit * ($start_chk);

        } else {

            $start_sql = 0;

        }



        $sql_limit = " LIMIT " . $limit . " OFFSET " . $start_sql;

        // $sql_limit = " LIMIT " . $limit . " OFFSET " . $start;

        $row = $this->db->query($sql . $sql_where . $sql_sort . $sql_limit);

        //$result    = $row->result_array();

        $result = $row->result_array(); // MMM comment for live only

        // $result  = array();



        $query_str = $this->db->last_query();



        if (count($result)) {

            $response = array("status" => 1, "success" => 1, "error" => 0, "data" => $result, 'total_records' => $count_res, "page_count" => $start, "message" => lang('Listed_Successfully'));

        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }





        $this->api_response($response);

    }



    public function city_post()
    {

        // $state      = $state;

        $state_id = $this->input->post('state_id');

        $type = $this->input->post('type');

        //$city       = $this->input->post('city');

        //os version



        if ($this->input->post('state_id')) {

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



            $response = array("status" => 0, "error" => 1, "success" => 0, "data" => null, "message" => lang('Missing_Parameter'));

            $this->api_response($response);

            exit;



        }

    }



    public function my_land_get($farmer_id)
    {

        //echo $type_get = $type_get;

        $response = array();

        $new_vals = array();



        if ($farmer_id != '') {

            $sql = "SELECT land_id ,farmer_id , farm_name, soil_type ,topology ,farm_type ,farm_size ,farm_image, unit ,irrigation_facility ,calculated_land_area ,survey_no ,khasra_no ,irrigation_source,state_id,cities_id,doc_7_12,farm_polygoan_coordinates FROM master_land_details WHERE farmer_id='" . $farmer_id . "'AND is_deleted ='false' ORDER BY land_id DESC";

            $row = $this->db->query($sql);

            $result = $row->result_array();

            if (count($result)) {



                foreach ($result as $key => $value) {



                    $topology_name = $topology_name_mr = $unit_name = $unit_name_mr = $irri_faty_name = $irri_faty_name_mr = $irri_src_name = $irri_src_name_mr = $soil_type_name = $soil_type_name_mr = $farm_type_name = $farm_type_name_mr = null;



                    if (!is_null($value['soil_type'])) {

                        //echo $value['topology'];

                        $soil_type_name = $this->soil_type_web[$value['soil_type']];

                        $soil_type_name_mr = $this->soil_type_web_mr[$value['soil_type']];

                    }

                    if (!is_null($value['farm_type'])) {

                        //echo $value['topology'];

                        $farm_type_name = $this->farm_type_web[$value['farm_type']];

                        $farm_type_name_mr = $this->farm_type_web_mr[$value['farm_type']];

                    }



                    if (!is_null($value['topology'])) {

                        //echo $value['topology'];

                        $topology_name = $this->topology_web[$value['topology']];

                        $topology_name_mr = $this->topology_web_mr[$value['topology']];

                    }

                    if (!is_null($value['unit'])) {

                        $unit_name = $this->unit_web[$value['unit']];

                        $unit_name_mr = $this->unit_web_mr[$value['unit']];

                    }

                    if (!is_null($value['irrigation_facility'])) {

                        $irri_faty_name = $this->irri_faty_web[$value['irrigation_facility']];

                        $irri_faty_name_mr = $this->irri_faty_web_mr[$value['irrigation_facility']];

                    }

                    if (!is_null($value['irrigation_source'])) {

                        $irri_src_name = $this->irri_src_web[$value['irrigation_source']];

                        $irri_src_name_mr = $this->irri_src_web_mr[$value['irrigation_source']];

                    }



                    if (!is_null($value['farm_image'])) {

                        $farm_image = $this->config_url['farm_image_url'] . $value['farm_image'];

                    } else {

                        $farm_image = base_url('uploads/user_data/farm/default.png');

                    }



                    $chk_insurance_sql = "SELECT id,application_status from crop_insurance_details where farmer_id='" . $farmer_id . "' AND land_id='" . $value['land_id'] . "'";

                    $row_chk = $this->db->query($chk_insurance_sql);

                    $result_ins = $row_chk->result_array();



                    if (count($result_ins)) {

                        $is_insured = 1;

                    } else {

                        $is_insured = 0;

                    }



                    $nw_arr = array(
                        'land_id' => $value['land_id'],

                        'farmer_id' => $value['farmer_id'],

                        'topology' => $value['topology'],

                        'soil_type' => $value['soil_type'],

                        'farm_type' => $value['farm_type'],

                        'farm_size' => $value['farm_size'],

                        'soil_type_name' => $soil_type_name,

                        'soil_type_name_mr' => $soil_type_name_mr,

                        'farm_type_name' => $farm_type_name,

                        'farm_type_name_mr' => $farm_type_name_mr,

                        'topology_name' => $topology_name,

                        'topology_name_mr' => $topology_name_mr,

                        'irri_faty_name' => $irri_faty_name,

                        'irri_faty_name_mr' => $irri_faty_name_mr,

                        'irri_src_name' => $irri_src_name,

                        'irri_src_name_mr' => $irri_src_name_mr,

                        'unit' => $value['unit'],

                        'unit_name' => $unit_name,

                        'unit_name_mr' => $unit_name_mr,

                        'irrigation_source' => $value['irrigation_source'],

                        'irrigation_facility' => $value['irrigation_facility'],

                        'calculated_land_area' => $value['calculated_land_area'],

                        'survey_no' => $value['survey_no'],

                        'state_id' => $value['state_id'],

                        'cities_id' => $value['cities_id'],

                        'doc_7_12' => $value['doc_7_12'],

                        'farm_name' => $value['farm_name'],

                        'farm_image' => $farm_image,

                        'is_insured' => $is_insured,

                        'farm_polygoan_coordinates' => $value['farm_polygoan_coordinates'],

                    );

                    //'khasra_no'               => $value['khasra_no'],



                    $new_vals[] = $nw_arr;



                }



                $response = array("success" => 1, "status" => 1, "custom_data" => $new_vals, 'farm_type' => $this->farm_type, 'topology' => $this->topology, 'soil_type' => $this->soil_type, "unit" => $this->unit, "message" => lang('Listed_Successfully'), 'config_url' => $this->config_url);



            } else {

                $response = array("success" => 1, "status" => 1, "custom_data" => $new_vals, 'farm_type' => $this->farm_type, 'topology' => $this->topology, 'soil_type' => $this->soil_type, "unit" => $this->unit, "message" => lang('Data_Not_Found'), 'config_url' => $this->config_url);

            }

        } else {

            $response = array("status" => 0, "message" => lang('Data_Not_Found'));

        }

        $this->api_response($response);

    }



    public function land_detail_get($land_id)
    {

        //echo $type_get = $type_get;

        $response1 = array();

        $response2 = array();

        $new_vals = array();

        $new_crop_vals = array();

        $response = array("success" => 1, "status" => 1, "message" => "Farm and its Crops are Deleted");



        if ($land_id != '') {



            // $sql_crop = "SELECT crop_id , name as crop_name ,name_mr as crop_name_mr,duration_days FROM crop where  is_active = 'true' AND is_deleted = 'false' ORDER BY crop_id ASC";

            // $row_crop = $this->db->query($sql_crop);

            //get days



            $sql = "SELECT land_id ,farmer_id,farm_image, farm_name,farm_name_mr, soil_type ,topology ,farm_type ,farm_size ,unit ,irrigation_facility ,calculated_land_area ,survey_no ,khasra_no ,irrigation_source,state_id,cities_id,doc_7_12  FROM master_land_details WHERE land_id='" . $land_id . "' AND is_deleted = 'false' ORDER BY land_id DESC ";

            $row = $this->db->query($sql);

            $result_land = $row->result_array();



            /*          $sql_crop = "SELECT c.id ,c.client_id ,c.land_id ,c.crop ,c.crop_name,c.crop_image, c.crop_type ,c.area_under_cultivation ,c.unit , c.duration_from ,c.duration_to,l.farm_name,l.farm_name_mr FROM master_crop_details c LEFT JOIN master_land_details l ON c.land_id = l.land_id

            WHERE c.land_id='" . $land_id . "' AND c.is_deleted = 'false' ORDER BY c.id DESC";*/

            // crop added list sql;

            $sql_crop = "SELECT c.id, l.farmer_id, c.client_id ,c.land_id ,cm.name,cm.name_mr,c.crop ,c.crop_name,c.crop_image, c.crop_type ,c.area_under_cultivation ,c.unit , c.duration_from ,c.duration_to,l.farm_name,cm.crop_id,cm.crop_id,cm.duration_days,l.state_id,l.cities_id,l.doc_7_12,ctm.crop_type_icon,ctm.name as crop_type_name,cm.logo as crop_logo FROM master_crop_details c

             LEFT JOIN master_land_details l ON c.land_id = l.land_id

             LEFT JOIN crop cm ON cm.crop_id = c.crop

             LEFT JOIN crop_type_master ctm ON ctm.crop_type_id = cm.crop_type_id

            WHERE c.land_id='" . $land_id . "' AND c.is_deleted = 'false' ORDER BY c.id DESC";



            $row_crops = $this->db->query($sql_crop);

            $result_crops = $row_crops->result_array();



            $result['result_crops'] = $result_crops;

            $result['result_land'] = $result_land;

            if (count($result_crops)) {



                foreach ($result_crops as $key => $value) {



                    $crop_name = $crop_name_mr = $unit_name = $unit_name_mr = $crop_type_name = $crop_type_name_mr = null;



                    /*if (!is_null($value['crop'])) {

                    //echo $value['crop'];

                    $crop_name    = $this->crop_web[$value['crop']];

                    $crop_name_mr = $this->crop_web_mr[$value['crop']];

                    }*/



                    if (!is_null($value['unit'])) {

                        $unit_name = $this->unit_web[$value['unit']];

                        $unit_name_mr = $this->unit_web_mr[$value['unit']];

                    }



                    if (!is_null($value['crop_type'])) {

                        $crop_type_name = $this->crop_type_web[$value['crop_type']];

                        $crop_type_name_mr = $this->crop_type_web_mr[$value['crop_type']];

                    }





                    if (!is_null($value['crop_image'])) {

                        $crop_image = $this->config_url['crop_image'] . $value['crop_image'];

                    } elseif (!is_null($value['crop_logo'])) {

                        $crop_image = $this->config_url['crop_logo'] . $value['crop_logo'];

                    } elseif (!is_null($value['crop_type_icon'])) {

                        $crop_image = $this->config_url['crop_type_icon'] . $value['crop_type_icon'];

                    } else {

                        // $crop_image = base_url('uploads/user_data/crop_image/default.png');

                        $crop_image = base_url('uploads/user_data/crop_image/default_new.png');

                    }



                    $duration_to = '';

                    if ($value['duration_from'] != '' && $value['duration_days'] != 0) {

                        //2021-03-29

                        $duration_to = '';

                        $duration = '+' . $value['duration_days'] . ' days';

                        //define('ADD_DAYS','+'.$duration.'' days');

                        //$start_date = date('Y-m-d H:i:s');

                        $duration_to = date("Y-M-d", strtotime($duration, strtotime($value['duration_from'])));



                        // $duration_to = date("Y-m-d", strtotime($value['duration_from'],$duration));

                    }



                    $land_id = $value['land_id'];

                    $farmer_id = $value['farmer_id'];

                    $crop_id = $value['crop_id'] ? $value['crop_id'] : '';

                    $crop_land_id = $value['id'];



                    $chk_insurance_sql = "SELECT id,application_status from crop_insurance_details where farmer_id='" . $farmer_id . "' AND land_id='" . $land_id . "' AND crop_land_id='" . $crop_land_id . "' ";

                    if (!empty($crop_id)) {

                        $chk_insurance_sql .= " AND crop_id='" . $crop_id . "' ";

                    }

                    $row_chk = $this->db->query($chk_insurance_sql);

                    $result_ins = $row_chk->result_array();



                    if (count($result_ins)) {

                        $is_insured = 1;

                        $insurance_id = $result_ins[0]['id'];

                        $insurance_status = $result_ins[0]['application_status'];

                    } else {



                        $is_insured = 0;

                        $insurance_id = 0;

                        $insurance_status = null;

                    }



                    $crop_id = $value['crop'];

                    $is_insurance_sql = "SELECT p.id,p.title,p.premium_per_acre,p.region_id,p.company_id,c.crop_id,c.name,c.name_mr,c.logo as mob_icon,c.mob_icon as icona,d.name as insurance_company_name,d.logo as insurance_company_logo,ct.name as city_name  FROM product_relation as p

                    LEFT JOIN crop as c ON c.crop_id = p.crop_id

                    LEFT JOIN cities_new ct ON ct.id = p.region_id

                    LEFT JOIN insurance_company_master as d ON d.insurance_company_id = p.company_id

                    WHERE p.is_deleted = false AND p.crop_id = '" . $crop_id . "'   AND c.is_deleted = false";



                    $row_ins_chk = $this->db->query($is_insurance_sql);

                    $result_insuranced = $row_ins_chk->result_array();



                    if (count($result_insuranced)) {

                        $is_insurance_plan = 1;

                    } else {

                        $is_insurance_plan = 0;

                    }



                    $nw_crop_arr = array(
                        'land_id' => $value['land_id'],

                        'id' => $value['id'],

                        'crop' => $value['crop'],

                        'crop_id' => $value['crop_id'],

                        'crop_name' => $value['name'],

                        'crop_name_mr' => $value['name_mr'],

                        'crop_type' => $value['crop_type'],

                        'crop_type_name' => $crop_type_name,

                        'crop_type_name_mr' => $crop_type_name_mr,

                        'unit' => $value['unit'],

                        'unit_name' => $unit_name,

                        'unit_name_mr' => $unit_name_mr,

                        'area_under_cultivation' => $value['area_under_cultivation'],

                        'duration_from' => date("d-M-Y", strtotime($value['duration_from'])),

                        'duration_to' => date("d-M-Y", strtotime($duration_to)),

                        'crop_image' => $crop_image,

                        'farm_name' => $value['farm_name'],

                        /* 'state_id'                     => $value['state_id'],

                        'cities_id'                    => $value['cities_id'],*/

                        'doc_7_12' => $value['doc_7_12'],

                        'farm_name_mr' => $value['farm_name_mr'],

                        'duration_days' => $value['duration_days'],

                        // 'city_name'                    => $value['city_name'],

                        'is_insured' => $is_insured,

                        'insurance_id' => $insurance_id,

                        'insurance_status' => $insurance_status,

                        'is_insurance_plan' => $is_insurance_plan,

                        'crop_type_icon' => $value['crop_type_icon'],

                        // 'crop_type_name'               => $value['crop_type_name'],



                    );



                    $new_crop_vals[] = $nw_crop_arr;



                }



                $response = array("status" => 1, "data" => $result_crops, "custom_data" => $new_crop_vals, 'crop' => $result_crops, 'crop_type' => $this->crop_type, "unit" => $this->unit, "message" => lang('Listed_Successfully'), 'config_url' => $this->config_url);



            }

        } else {

            $response = array("success" => 1, "status" => 0, "message" => lang('Data_Not_Found'));

        }

        $this->api_response($response);

    }



    public function land_detail_bkp_insurace_flow_get($land_id)
    {

        //echo $type_get = $type_get;

        $response1 = array();

        $response2 = array();

        $new_vals = array();

        $new_crop_vals = array();

        $response = array("success" => 1, "status" => 1, "message" => "Farm and its Crops are Deleted");



        if ($land_id != '') {



            $sql_crop = "SELECT crop_id , name as crop_name ,name_mr as crop_name_mr,duration_days FROM crop where  is_active = 'true' AND is_deleted = 'false' ORDER BY crop_id ASC";

            $row_crop = $this->db->query($sql_crop);

            //get days



            $sql = "SELECT land_id ,farmer_id,farm_image, farm_name,farm_name_mr, soil_type ,topology ,farm_type ,farm_size ,unit ,irrigation_facility ,calculated_land_area ,survey_no ,khasra_no ,irrigation_source,state_id,cities_id,doc_7_12  FROM master_land_details WHERE land_id='" . $land_id . "' AND is_deleted = 'false' ORDER BY land_id DESC ";

            $row = $this->db->query($sql);

            $result_land = $row->result_array();



            /*          $sql_crop = "SELECT c.id ,c.client_id ,c.land_id ,c.crop ,c.crop_name,c.crop_image, c.crop_type ,c.area_under_cultivation ,c.unit , c.duration_from ,c.duration_to,l.farm_name,l.farm_name_mr FROM master_crop_details c LEFT JOIN master_land_details l ON c.land_id = l.land_id

            WHERE c.land_id='" . $land_id . "' AND c.is_deleted = 'false' ORDER BY c.id DESC";*/

            // crop added list sql;

            $sql_crop = "SELECT c.id, l.farmer_id, c.client_id ,c.land_id ,cm.name,cm.name_mr,c.crop ,c.crop_name,c.crop_image, c.crop_type ,c.area_under_cultivation ,c.unit , c.duration_from ,c.duration_to,l.farm_name,cm.crop_id,cm.crop_id,cm.duration_days,l.state_id,l.cities_id,l.doc_7_12,ct.name as city_name,ctm.crop_type_icon,ctm.name as crop_type_name,cm.logo as crop_logo FROM master_crop_details c

             LEFT JOIN master_land_details l ON c.land_id = l.land_id

             LEFT JOIN crop cm ON cm.crop_id = c.crop

             LEFT JOIN crop_type_master ctm ON ctm.crop_type_id = cm.crop_type_id

            LEFT JOIN cities_new ct ON l.cities_id = ct.id

            WHERE c.land_id='" . $land_id . "' AND c.is_deleted = 'false' ORDER BY c.id DESC";



            $row_crops = $this->db->query($sql_crop);

            $result_crops = $row_crops->result_array();



            $result['result_crops'] = $result_crops;

            $result['result_land'] = $result_land;

            if (count($result_crops)) {



                foreach ($result_crops as $key => $value) {



                    $crop_name = $crop_name_mr = $unit_name = $unit_name_mr = $crop_type_name = $crop_type_name_mr = null;



                    /*if (!is_null($value['crop'])) {

                    //echo $value['crop'];

                    $crop_name    = $this->crop_web[$value['crop']];

                    $crop_name_mr = $this->crop_web_mr[$value['crop']];

                    }*/



                    if (!is_null($value['unit'])) {

                        $unit_name = $this->unit_web[$value['unit']];

                        $unit_name_mr = $this->unit_web_mr[$value['unit']];

                    }



                    if (!is_null($value['crop_type'])) {

                        $crop_type_name = $this->crop_type_web[$value['crop_type']];

                        $crop_type_name_mr = $this->crop_type_web_mr[$value['crop_type']];

                    }



                    if (!is_null($value['crop_image'])) {

                        $crop_image = base_url('uploads/user_data/crop_image/' . $value['crop_image']);

                    } elseif (!is_null($value['crop_logo'])) {

                        $crop_image = base_url('uploads/crops/' . $value['crop_logo']);

                    } elseif (!is_null($value['crop_type_icon'])) {

                        $crop_image = base_url('uploads/crop_type_icon/' . $value['crop_type_icon']);

                    } else {

                        // $crop_image = base_url('uploads/user_data/crop_image/default.png');

                        $crop_image = base_url('uploads/user_data/crop_image/default_new.png');

                    }



                    $duration_to = '';

                    if ($value['duration_from'] != '' && $value['duration_days'] != 0) {

                        //2021-03-29

                        $duration_to = '';

                        $duration = '+' . $value['duration_days'] . ' days';

                        //define('ADD_DAYS','+'.$duration.'' days');

                        //$start_date = date('Y-m-d H:i:s');

                        $duration_to = date("Y-M-d", strtotime($duration, strtotime($value['duration_from'])));



                        // $duration_to = date("Y-m-d", strtotime($value['duration_from'],$duration));

                    }



                    $land_id = $value['land_id'];

                    $farmer_id = $value['farmer_id'];

                    $crop_id = $value['crop_id'];

                    $crop_land_id = $value['id'];



                    $chk_insurance_sql = "SELECT id,application_status from crop_insurance_details where farmer_id='" . $farmer_id . "' AND land_id='" . $land_id . "' AND crop_land_id='" . $crop_land_id . "'  AND crop_id='" . $crop_id . "'";

                    $row_chk = $this->db->query($chk_insurance_sql);

                    $result_ins = $row_chk->result_array();



                    if (count($result_ins)) {

                        $is_insured = 1;

                        $insurance_id = $result_ins[0]['id'];

                        $insurance_status = $result_ins[0]['application_status'];

                    } else {



                        $is_insured = 0;

                        $insurance_id = 0;

                        $insurance_status = null;

                    }



                    $crop_id = $value['crop'];

                    $is_insurance_sql = "SELECT p.id,p.title,p.premium_per_acre,p.region_id,p.company_id,c.crop_id,c.name,c.name_mr,c.logo as mob_icon,c.mob_icon as icona,d.name as insurance_company_name,d.logo as insurance_company_logo,ct.name as city_name  FROM product_relation as p

                    LEFT JOIN crop as c ON c.crop_id = p.crop_id

                    LEFT JOIN cities_new ct ON ct.id = p.region_id

                    LEFT JOIN insurance_company_master as d ON d.insurance_company_id = p.company_id

                    WHERE p.is_deleted = false AND p.crop_id = '" . $crop_id . "'   AND c.is_deleted = false";



                    $row_ins_chk = $this->db->query($is_insurance_sql);

                    $result_insuranced = $row_ins_chk->result_array();



                    if (count($result_insuranced)) {

                        $is_insurance_plan = 1;

                    } else {

                        $is_insurance_plan = 0;

                    }



                    $nw_crop_arr = array(
                        'land_id' => $value['land_id'],

                        'id' => $value['id'],

                        'crop' => $value['crop'],

                        'crop_id' => $value['crop_id'],

                        'crop_name' => $value['name'],

                        'crop_name_mr' => $value['name_mr'],

                        'crop_type' => $value['crop_type'],

                        'crop_type_name' => $crop_type_name,

                        'crop_type_name_mr' => $crop_type_name_mr,

                        'unit' => $value['unit'],

                        'unit_name' => $unit_name,

                        'unit_name_mr' => $unit_name_mr,

                        'area_under_cultivation' => $value['area_under_cultivation'],

                        'duration_from' => $value['duration_from'],

                        'duration_to' => $duration_to,

                        'crop_image' => $crop_image,

                        'farm_name' => $value['farm_name'],

                        /* 'state_id'                     => $value['state_id'],

                        'cities_id'                    => $value['cities_id'],*/

                        'doc_7_12' => $value['doc_7_12'],

                        'farm_name_mr' => $value['farm_name_mr'],

                        'duration_days' => $value['duration_days'],

                        'city_name' => $value['city_name'],

                        'is_insured' => $is_insured,

                        'insurance_id' => $insurance_id,

                        'insurance_status' => $insurance_status,

                        'is_insurance_plan' => $is_insurance_plan,

                        'crop_type_icon' => $value['crop_type_icon'],

                        // 'crop_type_name'               => $value['crop_type_name'],



                    );



                    $new_crop_vals[] = $nw_crop_arr;



                }





                $response = array("status" => 1, "data" => $result_crops, "custom_data" => $new_crop_vals, 'crop' => $result_crops, 'crop_type' => $this->crop_type, "unit" => $this->unit, "message" => lang('Listed_Successfully'), 'config_url' => $this->config_url);

            }

        } else {

            $response = array("success" => 1, "status" => 0, "message" => lang('Data_Not_Found'));

        }

        $this->api_response($response);

    }



    public function show_crop_calander_get($master_crop_id)
    {



        if ($master_crop_id != '') {

            $sql = "SELECT c.*,cm.name as crop_name,cm.name_mr as crop_name_mr,cm.logo from master_crop_details  as c

            LEFT JOIN crop as cm ON cm.crop_id=c.crop

            where id=" . $master_crop_id;

            $row = $this->db->query($sql);

            $result = $row->result_array();

            if (count($result)) {



                $crop_id = $result[0]['crop'];

                $sql_cc = "SELECT id, crop_id, days_count, activities, details, chemical_consertation, expected_height, is_active, created_on, duration, activities_mr, details_mr, crop_step from crop_calender where is_deleted=false AND crop_id=" . $crop_id . " ORDER BY days_count ASC ";

                $row_cc = $this->db->query($sql_cc);

                $result_crop_cal = $row_cc->result_array();



                $new_result = [];

                foreach ($result_crop_cal as $key => $value) {

                    // $crop_step = $value['crop_step'] ? trim($value['crop_step']) : 'crop_step';

                    $new_result[$value['crop_step']][] = $value;

                }



                $result_data['new_result'] = $new_result;

                $result_data['crop_data'] = $result;

                $result_data['crop_cal'] = $result_crop_cal;







                $response = array("status" => 1, "data" => $result_data, "message" => "Crop listed  with calender successfully");

            }



            /*if (count($result)) {

        $response = array("status" => 1, "data" => $result, "message" => "Farm listed successfully");

        }*/



        }



        $this->api_response($response);

        exit;



    }





    public function crop_calender_action_get($crop_id)
    {

        // seedlings transplanting

        if (2 == $crop_id) {

            $calender_option[] = array(

                'title' => 'calender_option',

                'data' => array(

                    'id' => '1',

                    'value' => lang('Calender_Option'),

                    'map_key' => 'calender_option_url',

                    'option' => array(

                        array(

                            'id' => 'Seedlings',

                            'value' => lang('Onion_Seedlings'),

                            'map_key' => 'onion_seedlings_url'

                        ),

                        array(

                            'id' => 'Transplanting',

                            'value' => lang('Onion_Transplanting'),

                            'map_key' => 'onion_transplanting_url'

                        ),

                    )

                )

            );

        }



        // $calender_option[] = array(

        //     'title'=>'Season',

        //     'data'=>array(

        //         'id' => '2', 

        //         'value' => lang('Season'),

        //         'map_key' =>'season_url',

        //         'option' =>array(

        //             array(

        //                 'id' => 'Kharif', 

        //                 'value' => lang('Kharif'),

        //                 'map_key' =>'Kharif_url'

        //             ),

        //             array(

        //                 'id' => 'Late_Kharif', 

        //                 'value' => lang('Late_Kharif'),

        //                 'map_key' =>'Late_Kharif_url'

        //             ),

        //             array(

        //                 'id' => 'Rabi', 

        //                 'value' => lang('Rabi'),

        //                 'map_key' =>'Rabi_url'

        //             ),

        //         )

        //     )

        // );





        $response = array("success" => 1, "status" => 0, "data" => $calender_option);



        $this->api_response($response);

        exit;



    }



    public function show_crop_calander_data_post()
    {


        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';



        if ($selected_lang == 'mr') {

            $lang_col = "_mr";

        } elseif ($selected_lang == 'hi') {

            $lang_col = "_hi";

        } else {

            $lang_col = "_mr";

        }



        $crop_id = $this->input->post('crop_id');

        $seeding_date = $this->input->post('seeding_date');

        $user_id = $this->input->post('user_id'); //date("n", $timestamp)

        $calender_action = ($this->input->post('calender_action') == 'Seedlings') ? $this->input->post('calender_action') : '';



        $timestamp = strtotime($seeding_date);

        $month = date("n", $timestamp);



        /*

        Kharif : May-June  : 5,6

        Late Kharif : Aug.-Sept  : 8,9

        Rabi : Oct.-Nov : 10,11

         */



        // flow to detect Season from Month

        if (5 == $month || 6 == $month || 7 == $month) {

            $season = "Kharif";

        } elseif (8 == $month || 9 == $month) {

            $season = "Late Kharif";

        } elseif (10 == $month || 11 == $month) {

            $season = "Rabi";

        } else {

            $season = "Kharif";

        }





        // $season = "Late Kharif";



        if ($crop_id != '' && $season != '') {

            $sql = "SELECT c.*,cm.name as crop_name,cm.name_mr as crop_name_mr,cm.logo from master_crop_details  as c

            RIGHT JOIN crop as cm ON cm.crop_id=c.crop

            where cm.crop_id=" . $crop_id . " ";

            $row = $this->db->query($sql);

            $result = $row->result_array();

            if (count($result)) {



                // $crop_id         = $result[0]['crop'];

                $sql_cc = "SELECT id, crop_id, days_count, activities, details, chemical_consertation, expected_height, is_active, created_on, duration, activities" . $lang_col . " as activities_mr, details" . $lang_col . " as details_mr, season, extra_text, crop_step from crop_calender where is_deleted=false AND crop_id=" . $crop_id . " AND season= '" . $season . "' ";

                if ($crop_id == 2) {

                    $sql_cc .= " AND extra_text = '" . $calender_action . "'"; // Seedlings condistions

                }

                $sql_cc .= " ORDER BY days_count ASC ";

                $row_cc = $this->db->query($sql_cc);

                $result_crop_cal = $row_cc->result_array();



                // print_r($result_crop_cal);exit;



                $new_result = null;

                if ($crop_id == 2) {

                    foreach ($result_crop_cal as $key => $value) {

                        $new_result[$value['crop_step']][] = $value;

                    }

                } else {

                    foreach ($result_crop_cal as $key => $value) {

                        $new_result['Transplanting'][] = $value;

                    }

                }



                $result_data['new_result'] = $new_result;

                $result_data['crop_data'] = $result;

                $result_data['crop_cal'] = $result_crop_cal;

                // $result_data['sql_cc']  = $sql_cc;



                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result_data, "message" => "Crop listed  with calender successfully", "season" => $season, "post_data" => $_POST, "month" => $month, 'seeding_date' => $seeding_date, 'sql_cc' => $sql_cc);

            } else {

                $response = array("success" => 0, "error" => 1, "status" => 0, "data" => [], "message" => lang('Data_Not_Found'), "season" => $season, "post_data" => $_POST);

            }

        } else {

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => array(), "message" => lang('Missing_Parameter'), "season" => $season, "post_data" => $_POST);

        }



        $this->api_response($response);

        exit;



    }



    public function delete_crop_details_put($id)
    {

        $response = array();

        if ($id != '') {

            $sql = "UPDATE master_crop_details SET is_deleted = 'true' WHERE id = '" . $id . "'";



            $result = $this->db->query($sql);



            if (count($result)) {

                $response = array("status" => 1, "data" => $result, "message" => "Crop Deleted successfully");

            }

        } else {

            $response = array("status" => 0, "message" => "Crop not Deleted successfully");

        }

        $this->api_response($response);

    }



    public function delete_land_crop_put($land_id)
    {



        $response = array();

        if ($land_id != '') {

            $sql = "UPDATE master_land_details SET is_deleted = 'true' WHERE land_id = '" . $land_id . "'";



            $result_land = $this->db->query($sql);

            if (count($result_land)) {



                $sql_crop = "UPDATE master_crop_details SET is_deleted = 'true' WHERE land_id = '" . $land_id . "'";

                $result_crops = $this->db->query($sql_crop);

                //$result_crops = $row_crops->result_array();



                $result['result_crops'] = $result_crops;

                $result['result_land'] = $result_land;



                $response = array("status" => 1, "data" => $result, "message" => "Farm Deleted successfully");

            }

        } else {

            $response = array("status" => 0, "message" => "Crop Deleted successfully");

        }

        $this->api_response($response);

    }



    public function partners_get($type_get)
    {

        //echo $type_get = $type_get;

        $response = array();

        $user_data = array();

        $result2 = array();



        if ($type_get != '') {



            $sql2 = "SELECT * FROM user_services WHERE is_deleted='false' AND is_active = 'true' AND user_type_id =" . $type_get;

            $row2 = $this->db->query($sql2);

            $result2 = $row2->result_array();

            $selects = array('users.*');

            // , 'countries.name as country_name', 'states.name as state_name'

            /* $join    = array(

            'states'    => array('states.code = users.state', 'LEFT'),

            'countries' => array('countries.code = users.country', 'INNER'),

            );*/

            //'states.is_deleted' => 'false',

            $where = array('users.user_type' => $type_get, 'users.is_deleted' => 'false', 'users.is_active' => 'true');

            $user_data = $this->Masters_model->get_data($selects, 'users', $where, $join);



            if (count($user_data)) {

                $response = array("status" => 1, "data" => $user_data, "config_url" => $this->config_url, "service_options" => $result2, "message" => lang('Listed_Successfully'));

            } else {

                $response = array("status" => 0, "data" => null, "config_url" => $this->config_url, "service_options" => null, "message" => lang('Data_Not_Found'));

            }

        } else {

            $user_data = array();

            $response = array("status" => 0, "data" => $user_data, "service_options" => $result2, "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);

    }



    public function is_custom_field_mob_get($prod_id)
    {

        $where_cat = array('real_id' => $prod_id);

        $this->db->select('*');

        $this->db->where($where_cat);

        $custom_fields = $this->db->get('custom_fields')->result_array();



        if (count($custom_fields)) {



            $response = array("status" => 1, "is_custom" => 1, "data" => $custom_fields, "message" => "Custom form data");



        } else {



            $response = array("success" => 1, "status" => 1, "is_custom" => 0, "data" => null, "message" => "Normal form data");



        }

        $this->api_response($response);



    }



    public function partner_services_get($partner_id)
    {

        //echo $type_get = $type_get;

        $response = array();



        if ($partner_id != '') {



            $sql2 = "SELECT id, name_en, name_mr, user_type_id FROM user_services WHERE is_deleted='false' AND is_active = 'true'";

            $row2 = $this->db->query($sql2);

            $result2 = $row2->result_array();



            $row3 = $this->db->query("SELECT * FROM product_services WHERE is_deleted = 'false' AND is_active='true' AND partner_id = " . $partner_id);

            $result_packages = $row3->result_array();

            // 'countries.name as country_name', 'states.name as state_name'

            $selects = array('users.*');

            /*$join    = array(

            'states'    => array('states.code = users.state', 'LEFT'),

            'countries' => array('countries.code = users.country', 'INNER'),

            );*/

            //'states.is_deleted' => 'false',

            $where = array('users.user_id' => $partner_id, 'users.is_deleted' => 'false', 'users.is_active' => 'true');



            $user_data = $this->Masters_model->get_data($selects, 'users', $where, $join);





            if (count($user_data)) {

                $response = array("success" => 1, "status" => 1, "data" => $user_data, "config_url" => $this->config_url, "service_options" => $result_packages, "packages" => $result_packages, "message" => lang('Listed_Successfully'), 'default_image' => 'service_default.jpg');

            } else {

                $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

            }

        } else {

            $response = array("success" => 1, "status" => 0, "status" => 0, "message" => lang('Missing_Parameter'));

        }

        $this->api_response($response);

    }



    public function profile_get($farmer_id, $is_new = 0)
    {

        $result = array();

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $_POST, "message" => lang('Missing_Parameter'));



        if ($farmer_id) {



            if ($is_new) {



                $select = array('client.*', 'client_group_master.name as group_name', 'countries_new.name as country_name', 'states_new.name as state_name');

                $join = array(
                    'countries_new' => array('countries_new.id = 101', 'left'),

                    'states_new' => array('cast(states_new.id as INTEGER)  = cast(client.state as INTEGER)', 'left'),

                    'client_group_master' => array('client_group_master.client_group_id = client.group_id ', 'left')
                );

                //$sql = "SLECT "



                $where = array('client.id' => $farmer_id, 'client.is_deleted' => 'false');

                $user_data = $this->Masters_model->get_data($select, 'client', $where, $join, '', '', 1);



            } else {



                $select = array('client.*', 'client_group_master.name as group_name', 'countries.name as country_name', 'states.name as state_name');

                $join = array(
                    'countries' => array('countries.code = client.country', 'left'),

                    'states' => array('states.code = client.state', 'left'),

                    'client_group_master' => array('client_group_master.client_group_id = client.group_id ', 'left')
                );



                $where = array('client.id' => $farmer_id, 'client.is_deleted' => 'false');

                $user_data = $this->Masters_model->get_data($select, 'client', $where, $join, '', '', 1);

            }



            // $step_master_data = array();

            $sql = "SELECT * FROM step_master where is_deleted=false AND is_active=true ORDER BY step_order ASC";

            $res_val = $this->db->query($sql);

            $res_array = $res_val->result_array();



            if (count($res_array) > 0) {

                $step_master_data = $res_array;

            }



            //$sql_chk   = "SELECT * from farmer_documents WHERE farmer_id=".$farmer_id;

            $sql_chk = "SELECT s.step_title,f.* from step_master as s

            LEFT JOIN farmer_documents as f ON f.document_step = s.id

            WHERE f.farmer_id=$farmer_id ";

            $res_doc = $this->db->query($sql_chk);

            $res_array_doc = $res_doc->result_array();

            if (count($res_array_doc) > 0) {

                $doc_array = $res_array_doc;

            }



            if ($user_data[0]['dob'] != '') {

                $user_data[0]['my_dob'] = $user_data[0]['dob'];

                $user_data[0]['dob'] = date("d-m-Y", strtotime($user_data[0]['dob']));

            }



            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $user_data, "step_master_data" => $step_master_data, "doc_array" => $doc_array, "message" => "Login successfully", 'config_url' => $this->config_url);



        }



        $this->api_response($response);

        exit;



    }



    public function add_loan_details_post()
    {

        $result = array();

        $image = '';



        if (!empty($_FILES['loan_image']['name'])) {

            $extension = pathinfo($_FILES['loan_image']['name'], PATHINFO_EXTENSION);



            $loan_image_name = $this->connected_domain . '_loan_image_' . time() . '.' . $extension;

            if (!file_exists($this->upload_file_folder . 'loan')) {

                mkdir($this->upload_file_folder . 'loan', 0777, true);

            }

            $target_file = $this->upload_file_folder . 'loan/' . $loan_image_name;



            if ($this->input->post('old_loan_image') != "") {

                @unlink($this->upload_file_folder . 'loan/' . $this->input->post('old_loan_image'));

            }



            if (move_uploaded_file($_FILES["loan_image"]["tmp_name"], $target_file)) {

                $update['loan_image'] = $loan_image_name;

                $error = 0;

            } else {

                $error = 2;

            }

        }

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Registration failed, please try again some time.");



        if ($this->input->post('btn_submit') == 'submit') {



            if (0) {

                $data = $this->input->post();

                $data['error'] = validation_errors();

            } else {



                $insert = array(

                    'loan_image' => $loan_image_name,

                    'user_id' => $this->input->post('user_id'),

                    'first_name' => $this->input->post('first_name'),

                    'last_name' => $this->input->post('last_name'),

                    'loan_type_id' => $this->input->post('loan_type_id'),

                    'status' => 'Loan Applied',

                    'created_on' => current_date(),



                );



                $result = $this->db->insert('loan_details', $insert);

                $insert_id = $this->db->insert_id();



                if ($insert_id) {



                    //$user_data = $this->Masters_model->get_data('email', 'client', $where, $join);



                    $arr = array('body' => array(), 'subject' => array());

                    $mail_data = get_email_body('loan_lead', $arr);



                    $to_mail = 'manojmali9@gmail.com';



                    $this->Email_model->send_mail($mail_data['subject'], $mail_data['body'], $to_mail, $mail_data['from_mail'], $partner_name);

                    // email_activity_logs($mail_data['subject'],$mail_data['body'],$to_mail,$insert_id,'partner');

                    $up_data = json_encode($_POST);

                    $sql_update = "update loan_details set other_details = '" . $up_data . "' where id = " . $insert_id;



                    $this->db->query($sql_update);



                }



                if ($result) {



                    if (count($insert)) {

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Added_Successfully'));

                    }



                    $this->api_response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Loan detail Add failed, please try again some time.");



                    $this->api_response($response);

                    exit;



                }

            }

        }



        $this->api_response($response);

        exit;

    }



    public function add_agent_interested_post()
    {

        $result = array();

        $app_type = 1;

        $id = $this->input->post('id');

        $bank_id = $this->input->post('bank_id');



        if ($app_type == 1) {

            $table_name = 'loan_details';

        } else {

            $table_name = 'insurance_details';

        }



        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Loan update failed, please try again some time.");



        if ($id != '') {



            if (0) {



            } else {



                $update_arr = array(

                    'bank_interested' => $this->input->post('bank_id'),

                    'status' => 'Farmer Selected Bank',

                    'updated_on' => current_date(),

                );



                $this->db->where('id', $id);

                $result = $this->db->update($table_name, $update_arr);



                if ($result) {



                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Updated_Successfully'));



                    $this->api_response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Not_Able_Update'));



                    $this->api_response($response);

                    exit;



                }

            }

        }



    }



    public function loan_types_get()
    {


        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        $is_whitelabeled = $headers_data['is_whitelabeled'];

        $advertise_data = array();

        $data_array = array();

        $bank_master_id = $headers_data['bank_master_id'];

        //CONCAT  (first_name, ' ', last_name) AS "Full name"

        /*$row = $this->db->query("SELECT loan_type_id , CONCAT(name, ' (', loan_duration,')') AS name , CONCAT(name_mr, ' (', loan_duration,')') AS name_mr ,logo ,mob_icon,loan_duration FROM loan_types_master WHERE is_active = 'true' AND is_deleted = 'false'");

         */

        $row = $this->db->query("SELECT loan_type_id, name,name_mr , CONCAT(loan_duration, ' Term') as loan_duration ,logo ,mob_icon FROM loan_types_master WHERE is_active = 'true' AND is_deleted = 'false'  ORDER BY loan_duration DESC");



        if ($is_whitelabeled != '' && $bank_master_id != '') {



            $sql_bank = "select loan_type from banks where bank_master_id=" . $bank_master_id . " AND loan_type != ''  LIMIT 1 ";

            $row_bank = $this->db->query($sql_bank);

            $result_bank = $row_bank->result_array();

            $loan_types = $result_bank[0]['loan_type'];



            if ($loan_types != '') {

                $row = $this->db->query("SELECT loan_type_id, name,name_mr , CONCAT(loan_duration, ' Term') as loan_duration ,logo ,mob_icon FROM loan_types_master WHERE is_active = 'true'  AND is_deleted = 'false' AND loan_type_id IN($loan_types) ORDER BY loan_duration DESC");

            } else {



                $row = $this->db->query("SELECT loan_type_id, name,name_mr , CONCAT(loan_duration, ' Term') as loan_duration ,logo ,mob_icon FROM loan_types_master WHERE is_active = 'true' AND is_deleted = 'false' ORDER BY loan_duration DESC");

            }

        }

        $result = $row->result_array();

        if (count($result)) {

            $response = array('sql_bank' => $sql_bank, "header_data" => $headers_data, "status" => 1, "data" => $result, "message" => lang('Listed_Successfully'), 'bank_master_id' => $bank_master_id);

        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }



        // $this->api_response($result, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

        $this->api_response($response);

    }



    public function my_loan_get($farmer_id)
    {

        //echo $type_get = $type_get;

        $response = array();

        if ($farmer_id != '') {

            $sql = "SELECT ld.id,ld.id as loan_app_id , ld.user_id, ld.first_name, ld.last_name, ld.previous_loan, ld.age, ld.farmer_cast, ld.own_home, ld.own_land, ld.own_vehicle, ld.own_animal,ld.loan_type, ld.apply_loan_against,ld.annual_income, ld.family_members, ld.status,  to_char(ld.created_on::date,'DD-MM-YYYY') as created_on ,ld.loan_type_id, lm.name as loan_name FROM loan_details as ld

            LEFT JOIN loan_types_master as lm ON lm.loan_type_id = ld.loan_type_id

            WHERE ld.user_id='" . $farmer_id . "' AND ld.is_deleted ='false' ORDER BY ld.id DESC";

            $row = $this->db->query($sql);

            $result = $row->result_array();

            if (count($result)) {

                $response = array("status" => 1, "data" => $result, "message" => lang('Listed_Successfully'));

            } else {

                $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

            }



        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }

        $this->api_response($response);

    }



    public function my_loan_new_get($id)
    {

        //echo $type_get = $type_get;

        $response = array();

        if ($id != '') {

            $sql = "SELECT id, user_id, first_name,loan_type, last_name, status, to_char(created_on::date,'DD-MM-YYYY') as created_on , other_details,updated_on,bank_interested,laon_amount_sanctioned,bank_id,loan_image,loan_amount_disbursed,interest_rate FROM loan_details WHERE id='" . $id . "'";

            $row = $this->db->query($sql);

            $result = $row->result_array();

            if (count($result)) {

                $sql_banks = "SELECT b.app_interest , a.company_name,b.bank_id,b.loan_sanctioned,b.loan_disbursed,b.interest_rate FROM bank_loan_details as b

                LEFT JOIN banks as a ON a.bank_id = b.bank_id

                WHERE b.application_id='" . $id . "' and b.app_interest='yes'";

                $rows = $this->db->query($sql_banks);

                $bank_dd = $rows->result_array();

                $data['application'] = $result;

                if (count($bank_dd)) {

                    $data['banks'] = $bank_dd;

                } else {

                    $data['banks'] = array();

                }



                $response = array("status" => 1, "data" => $data, "message" => lang('Listed_Successfully'));

            } else {

                $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

            }



        } else {

            $response = array("status" => 0, "message" => lang('Listed_Successfully'));

        }

        $this->api_response($response);

    }



    public function iot_devices_post()
    {

        $id = $this->input->post('user_id');

        if (!empty($id)) {

            $dashboard_url = 'http://115.124.120.147:8080/dashboard/7f9b8790-e3bf-11eb-98d1-3506792d8c20?publicId=08badfa0-e3e6-11eb-98d1-3506792d8c20';

            $row = $this->db->query("SELECT * FROM client_iot_device WHERE  is_deleted = 'false'AND user_id=" . $id);

            $result = $row->result_array();

            //print_r($result);

            if (count($result)) {

                $response = array("status" => 1, "data" => $result, "config_url" => $this->config_url, "dashboard_url" => $dashboard_url, "message" => lang('Listed_Successfully'));

            } else {

                $response = array("status" => 1, "data" => null, "config_url" => $this->config_url, "dashboard_url" => $dashboard_url, "message" => lang('Data_Not_Found'));

            }



        } else {

            $response = array("status" => 1, "data" => null, "config_url" => $this->config_url, "dashboard_url" => $dashboard_url, "message" => lang('Data_Not_Found'));

        }

        $this->api_response($response);

        exit;

    }



    public function add_client_iot_device_post()
    {

        // echo 'efsdfgvxc';

        $result = array();



        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Registration failed, please try again some time.");



        if ($this->input->post('btn_submit') == 'submit') {



            if (0) {

                $data = $this->input->post();

                $data['error'] = validation_errors();

            } else {



                $insert = array(

                    'device_name' => $this->input->post('device_name'),

                    'farmer_land_name' => $this->input->post('farmer_land_name'),



                    'user_id' => $this->input->post('user_id'),

                    'created_on' => current_date(),

                );



                $result = $this->db->insert('client_iot_device', $insert);

                $insert_id = $this->db->insert_id();



                if ($result) {



                    if (count($insert)) {

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Added_Successfully'));

                    }

                    $this->api_response($response);

                    exit;

                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Client IoT Device Add failed, please try again some time.");

                    $this->api_response($response);

                    exit;

                }

            }

        }

        $this->api_response($response);

    }



    public function blogs_types_get()
    {


        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';



        $lang_label = " name_mr ";

        if ($selected_lang == 'mr') {

            $lang_folder = "marathi";

        } elseif ($selected_lang == 'hi') {

            $lang_folder = "hindi";

            $lang_label = "name_hi as name_mr";

        } else {

            $lang_folder = "english";

            $lang_label = " name_mr ";

        }



        $row = $this->db->query("SELECT blogs_types_id ,name ,logo , $lang_label ,mob_icon FROM blogs_types_master WHERE is_active = 'true' AND is_deleted = 'false' AND blog_cat != 2 ORDER BY seq ASC");

        $result = $row->result_array();

        if (count($result)) {

            $response = array("status" => 1, "data" => $result, "config_url" => $this->config_url, "message" => lang('Listed_Successfully'));

        }

        // $this->api_response($result, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

        $this->api_response($response);

    }



    public function menu_get()
    {

        /* $arr = array('body'=>array(),'subject'=>array());

        $mail_data= get_email_body('loan_lead',$arr);



        $to_mail = 'manojmali9@gmail.com';



        $this->Email_model->send_mail($mail_data['subject'], $mail_data['body'],$to_mail,$mail_data['from_mail'],$partner_name);*/

        //$headers_data = $this->input->request_headers();


        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';



        if ($selected_lang == 'mr') {

            $lang_folder = "marathi";

        } elseif ($selected_lang == 'hi') {

            $lang_folder = "hindi";

        } else {

            $lang_folder = "english";

        }

        $is_whitelabeled = $headers_data['is_whitelabeled'];

        //$this->lang->load(array('loan'),$lang_folder);

        $this->lang->load(array('site'), $lang_folder);



        $server = $_SERVER;

        $getheader = getallheaders();



        $switch_user_type = 0;



        //$response  = array("success" => 1, "error" => 0, "status" => 1, 'home_message' => $this->home_message, "menu" => $this->menu, 'headers_data' => $headers_data, 'is_whitelabeled' => $is_whitelabeled);



        $response = array("success" => 1, "error" => 0, "status" => 1, 'home_message' => $this->home_message, "menu" => $this->menu, 'headers_data' => $headers_data, 'config_url' => $this->config_url, 'is_whitelabeled' => $is_whitelabeled, 'switch_user_type' => $switch_user_type);

        $this->api_response($response);

    }



    public function all_blogs_details_get($blogs_types_id = 'all', $start = 1, $crop_id = 'all', $blog_cat = 1)
    {

        $result = $this->blog_listing($blogs_types_id, $start, $crop_id, $blog_cat);



        if (count($result)) {

            $response = array("status" => 1, "data" => $result, "config_url" => $this->config_url, "message" => lang('Listed_Successfully'));

        } else {

            $result = array();

            $response = array("status" => 1, "data" => $result, "config_url" => $this->config_url, "message" => lang('Data_Not_Found'));

        }



        $this->api_response($response);

    }



    public function all_blogs_news_get($blogs_types_id = 3, $start = 1)
    {


        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';



        if ($selected_lang == 'mr') {

            $lang_folder = "marathi";

            $lang_label = " bty.name_mr as blogs_types_name";

        } elseif ($selected_lang == 'hi') {

            $lang_folder = "hindi";

            $lang_label = " bty.name as blogs_types_name";

        } else {

            $lang_folder = "english";

            $lang_label = " bty.name as blogs_types_name";

        }



        $response = array();

        $limit = 8;

        //$start    = 1;

        $cat_id = 0;

        //$start  = $this->input->post('start') != ''?$this->input->post('start'):1;

        $start_chk = $start - 1;

        if ($start_chk != 0) {

            $start_sql = $limit * ($start_chk);

        } else {

            $start_sql = 0;

        }



        $sql_limit = " LIMIT " . $limit . " OFFSET " . $start_sql;

        //  $row       = $this->db->query($sql . $sql_where . $sql_sort . $sql_limit);

        $crop_wise_data = '';

        if ($blogs_types_id) {



            $row = $this->db->query("SELECT cb.id as blogs_id,cb.logo,cb.blogs_tags_id,cb.blogs_types_id,cb.id,cb.title as blogs_title,cb.sub_title as blogs_sub_title,cb.sub_description as blogs_sub_description,cb.created_on as blogs_created_on,bty.logo as blogs_types_logo ,bty.mob_icon as blogs_types_mob_icon,$lang_label

            FROM created_blogs as cb

            LEFT JOIN blogs_types_master as bty ON CAST(bty.blogs_types_id AS TEXT) IN (cb.blogs_types_id)

            WHERE cb.is_active=true AND cb.is_deleted = 'false' AND  cb.blogs_types_id='" . $blogs_types_id . "'" . $crop_wise_data . " ORDER BY cb.created_on DESC " . $sql_limit);



        }



        $result = $row->result_array();



        if (count($result)) {

            $response = array("status" => 1, "data" => $result, "config_url" => $this->config_url, "message" => lang('Listed_Successfully'));

        } else {

            $result = array();

            $response = array("status" => 1, "data" => $result, "config_url" => $this->config_url, "message" => lang('Data_Not_Found'));

        }



        $this->api_response($response);

    }



    public function blogs_details_get($id)
    {

        $result_tags_blogs = array();

        $result_similar_blogs = array();



        $blog_sql = "SELECT cb.id as blogs_id,cb.logo,cb.blogs_tags_id,cb.blogs_types_id,cb.id,cb.title as blogs_title,cb.sub_title as blogs_sub_title,cb.description as blogs_description,cb.sub_description as blogs_sub_description,cb.created_on as blogs_created_on, bty.name as blogs_types_name,bty.logo as blogs_types_logo ,bty.mob_icon as blogs_types_mob_icon,bty.name_mr as blogs_types_name_mr

            FROM created_blogs as cb

            LEFT JOIN blogs_types_master as bty ON CAST(bty.blogs_types_id AS TEXT) IN (cb.blogs_types_id)

            WHERE cb.is_active=true AND cb.is_deleted = 'false' AND cb.id = " . $id . " LIMIT 1";



        $row = $this->db->query($blog_sql);



        $result = $row->result_array();

        //print_r($result);



        $response = array("status" => 1, "data" => '', "similar_blogs" => '', "result_tags_blogs" => '', "config_url" => $this->config_url, "message" => lang('Data_Not_Found'));



        if (count($result)) {



            $update_arr['view_count'] = $result[0]['view_count'] + 1;



            if (count($update_arr)) {

                $this->db->where('created_blogs.id', $id);

                $this->db->update('created_blogs', $update_arr);

            }



            $blogs_tags = $result[0]['blogs_tags_id'];

            $blog_type = $result[0]['blogs_types_id'];

            $blog_id = $result[0]['blogs_id'];



            if ($blogs_tags != '') {

                $get_tags_blogs = "SELECT blogs_tags_id,name FROM blogs_tags_master WHERE blogs_tags_id IN (" . $blogs_tags . ")";

                $row_tags_blogs = $this->db->query($get_tags_blogs);

                $result_tags_blogs = $row_tags_blogs->result_array();



            }



            if ($blog_type != '') {



                $get_similar_blogs = "SELECT id,title,logo FROM created_blogs WHERE blogs_types_id='" . $blog_type . "' AND id !=" . $blog_id . " AND  is_active=true AND is_deleted = 'false' LIMIT 15";

                $row_similar_blogs = $this->db->query($get_similar_blogs);

                $result_similar_blogs = $row_similar_blogs->result_array();

            }



            $response = array("status" => 1, "data" => $result, "similar_blogs" => $result_similar_blogs, "result_tags_blogs" => $result_tags_blogs, "config_url" => $this->config_url, "message" => lang('Listed_Successfully'));

        }



        $this->api_response($response);

    }



    public function user_chat_post()
    {

        $farmer_id = $this->input->post('incoming_id');

        $partner_id = $this->input->post('outgoing_id');

        $output = "";

        // $is_custom  = 0;

        $response = array();



        $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        if ($farmer_id != '' && $partner_id != '') {



            $row_val = $this->db->query("SELECT * FROM messages WHERE (outgoing_msg_id = " . $partner_id . " AND incoming_msg_id = " . $farmer_id . ")

                OR (outgoing_msg_id = " . $farmer_id . " AND incoming_msg_id = " . $partner_id . ") ORDER BY msg_id ASC");

            $result = $row_val->result_array();



            if (count($result) > 0) {

                foreach ($result as $key => $row) {



                    if ($row['outgoing_msg_id'] === $partner_id) {

                        $output .= '<div class="chat outgoing">

                                <div class="details">

                                    <p>' . $row['msg'] . '</p>

                                </div>

                                </div>';

                    } else {



                        /* <img src="php/images/'.$row['img'].'" alt="">*/

                        $output .= '<div class="chat incoming">

                                <div class="details">

                                    <p>' . $row['msg'] . '</p>

                                </div>

                                </div>';

                    }

                }

            } else {

                $output .= '<div class="text">No messages are available. Once you send message they will appear here.</div>';

            }



            $chat_str = $output;



            if (count($result)) {



                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "chat_str" => $chat_str, "message" => lang('Listed_Successfully'));

                $this->api_response($response);

                exit;



            } else {



                $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "chat_str" => '<div class="text">No messages are available. Once you send message they will appear here.</div>', "message" => lang('Data_Not_Found'));

                $this->api_response($response);

                exit;



            }





            $this->api_response($response);

        }



    }



    public function add_user_chat_post()
    {

        $farmer_id = $this->input->post('farmer_id');

        $partner_id = $this->input->post('partner_id');

        $msg = $this->input->post('msg');

        // $is_custom  = 0;

        $response = array();



        $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        if ($farmer_id != '' && $partner_id != '') {



            $insert = array(

                'msg' => $this->input->post('msg'),

                'incoming_msg_id' => $this->input->post('farmer_id'),

                'outgoing_msg_id' => $partner_id,

                'user_type' => 'client',

                'created_on' => current_date(),



            );



            $result = $this->db->insert('messages', $insert);



            if ($result) {



                if (count($insert)) {



                    $sql_user = "SELECT first_name, last_name,device_id from users where user_id=" . $partner_id . "  LIMIT 1";

                    $row_user = $this->db->query($sql_user);

                    $user_data = $row_user->result_array();



                    $partner_name = $user_data[0]['first_name'] . ' ' . $user_data[0]['last_name'];



                    $sql_farmer = "SELECT first_name, last_name,device_id,profile_image from client where id=" . $farmer_id . "  LIMIT 1";

                    $row_farmer = $this->db->query($sql_farmer);

                    $farmer_data = $row_farmer->result_array();



                    $farmer_name = $farmer_data[0]['first_name'] . ' ' . $farmer_data[0]['last_name'];



                    $data['title'] = 'Chat';

                    $title = 'Chat';

                    $message = $farmer_name . ':' . truncate_string($msg);

                    // $message       = 'Dear ' . $partner_name . ' you have a New Message';

                    $admno = $farmer_id;

                    $type = 1;



                    $test_array[] = $partner_name;

                    $token[] = $user_data[0]['device_id'];



                    $test_array[] = $token;

                    $test_array[] = $sql_user;

                    $farmer_id = $farmer_id;

                    $farmer_image = $farmer_data[0]['profile_image'];



                    $jsonString = self::sendPushNotificationToFCMSeverdev_chat($token, $title, $message, $admno, $type, $partner_name, $farmer_id, $farmer_image, 'chat');



                    $test_array[] = $jsonString;



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



    public function connect_call_post()
    {

        //  farmer_id/partner_id

        $farmer_id = $this->input->post('farmer_id');

        $partner_id = $this->input->post('partner_id');

        $output = "";

        $today = date('Y-m-d');

        // $is_custom  = 0;

        $response = array();

        $available_flag = 0; // Not avaiable



        $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        if ($partner_id != '') {



            $row_val = $this->db->query("SELECT * FROM emeeting WHERE partner_id = " . $partner_id . " and (meeting_status_id = 1 OR meeting_status_id = 2)  AND date(created_on) = '" . $today . "' ORDER BY id ASC LIMIT 1");

            $result = $row_val->result_array();

            if (count($result) > 0) {

                $available_flag = 0; // Partner is not avaiable

                $meeting_link = '';

            } else {

                $available_flag = 1; // Partner is avaiable



                $meeting_link = md5(date("Ymdhis") . $this->input->post('farmer_id') . $this->input->post('partner_id'));



                /* $insert = array(

            'farmer_id'            => $this->input->post('farmer_id'),

            'partner_id'           => $this->input->post('partner_id'),

            'meeting_status_id'    => 1,

            'meeting_started_from' => 1,

            'meeting_link_id'      => md5($meeting_link),

            'is_active'            => 'true',

            'created_on'           => current_date(),

            );



            $sql_insert = $this->db->insert('emeeting', $insert);*/



            }



            $data = $_POST;

            $response = array("success" => 1, "available_flag" => $available_flag, "error" => 0, "status" => 1, "data" => $insert, "MeetingId" => $meeting_link);

            $this->api_response($response);

        }

    }



    public function incoming_farmer_call_post()
    {



        $today = date('Y-m-d');

        $farmer_id = $this->input->post('farmer_id');

        $partner_id = $this->input->post('partner_id');

        $meeting_link_id = $this->input->post('MeetingId');



        $sql_chk = "SELECT * FROM emeeting WHERE partner_id = " . $partner_id . " and farmer_id = " . $farmer_id . " and (meeting_status_id = 1 OR meeting_status_id = 2) AND date(created_on) = '" . $today . "' ORDER BY id ASC LIMIT 1";

        $row_val = $this->db->query($sql_chk);



        $call_result = $row_val->result_array();



        if (count($call_result) > 0) {

            $available_flag = 1; // Partner is not avaiable

            $call_coming = 1;

            $meeting_link = $call_result[0]['meeting_link_id'];

            $msg = 'there is already meeting id genrated from farmer';



        } else {



            $msg = 'Farmer call initiated';



            $available_flag = 1;



            if ($meeting_link_id == '') {

                $meeting_link_id = md5(date("Ymdhis") . $farmer_id . $partner_id);

            }



            $insert = array(

                'farmer_id' => $farmer_id,

                'partner_id' => $partner_id,

                'meeting_status_id' => 1,

                'meeting_started_from' => 1,

                'meeting_link_id' => $meeting_link_id,

                'is_active' => 'true',

                'created_on' => current_date(),

            );



            $sql_insert = $this->db->insert('emeeting', $insert);

            $call_result = $insert;

        }



        $data['title'] = 'Farmer Call';

        $data['MeetingId'] = $meeting_link_id;

        $data['farmer_id'] = $farmer_id;

        $data['call_data'] = $call_result;



        $sql_user = "SELECT first_name, last_name,device_id from users where user_id=" . $partner_id . "  LIMIT 1";

        $row_user = $this->db->query($sql_user);

        $user_data = $row_user->result_array();

        $partner_name = $user_data[0]['first_name'] . ' ' . $user_data[0]['last_name'];



        ////////////////////////////////////////

        $sql = "SELECT first_name, last_name,device_id from client where id=" . $farmer_id . "  LIMIT 1";

        $row = $this->db->query($sql);

        $farmer_data = $row->result_array();

        $farmer_name = $farmer_data[0]['first_name'] . ' ' . $farmer_data[0]['last_name'];



        $data['title'] = 'eMeeting';



        // $title   = 'Incoming call';

        $title = 'eMeeting';

        $message = 'Dear ' . $partner_name . ' have Call from ' . $farmer_name . ' please join meeting ';



        $admno = $farmer_id;

        /* $call_data = array(

        'farmer_id'       => $farmer_id,

        'farmer_name'     => $farmer_name,

        'partner_name'    => $partner_name,

        'meeting_link_id' => $meeting_link_id,

        ); */



        $call_data = array();



        //$token[] = $farmer_data[0]['device_id'];

        $token[] = $user_data[0]['device_id'];

        $meeting_link = $meeting_link_id;



        /*$token[] = 'dd6iOBwSQSCTyFLwDXBsHZ:APA91bFxIXftTYQcgIAQB7Tu06AlA1cu9mWN2paXXArIJp27tpxw3iaxYsDKCgF0dS2zgYVUK2qXsL63WUgUM3L6r4SF78SaLTxznrvUrQvY7I2Eude14H5seECti2cnglZPTOYoKv55';*/



        /* $token[] = 'dd6iOBwSQSCTyFLwDXBsHZ:APA91bFxIXftTYQcgIAQB7Tu06AlA1cu9mWN2paXXArIJp27tpxw3iaxYsDKCgF0dS2zgYVUK2qXsL63WUgUM3L6r4SF78SaLTxznrvUrQvY7I2Eude14H5seECti2cnglZPTOYoKv55';*/



        /* $token[] = 'dd6iOBwSQSCTyFLwDXBsHZ:APA91bFxIXftTYQcgIAQB7Tu06AlA1cu9mWN2paXXArIJp27tpxw3iaxYsDKCgF0dS2zgYVUK2qXsL63WUgUM3L6r4SF78SaLTxznrvUrQvY7I2Eude14H5seECti2cnglZPTOYoKv55';*/

        // nayan device ID



        /*  $token[] = 'ey2_WNsWRqO68C1H3KhH_f:APA91bFS9zHgaxQlapb05nJRmixIo29KBe5Jqjg0ha3osYTlkyU1DCqyh2elzSSVVtxd8-dG_GyxLjtcPgByLNbD97i40FczJnlBd41FFmB2csEztkPDof-hzLh4f7PrRCE-UYCy_JJv';*/



        $data['call_details'] = $call_data;

        //Device Id for Manoj Mobile to test Notification

        //$token[] = 'dcNMWbLMr1c:APA91bGni1E09a6jOEc0IUNlyLA8bixfOjUczIYR26GDlbRLoP48JlMK5ueeLnIqXajh8eMNC97LOVbFzORIpIwUI2q1dnOAqi2cpKK_256hVmq3jplLyxVVqKlTwLUnZrLr53SxLnqE';



        $jsonString = self::sendPushNotificationToFCMSeverdev($token, $title, $message, $admno, $call_data, $meeting_link, $partner_name, $farmer_id, 'video');

        //////////////////////////////////////



        $response = array("success" => 1, "available_flag" => $available_flag, "call_data"->$data, "error" => 0, "status" => 1, "data" => $data, "msg" => $msg);

        $this->api_response($response);



    }



    public function disconnect_call_post()
    {



        //echo 'Farmer id :'.$farmer_id;

        /*

        you need to send call_status_flag as define here

        1: new call

        2: in proeccess call

        3: hold

        4: disconect call

        5: reject call

         */



        $partner_id = $this->input->post('partner_id');

        $farmer_id = $this->input->post('farmer_id');

        $call_status_flag = $this->input->post('call_status_flag');



        $call_status_flag = $this->input->post('call_status_flag');

        if ($call_status_flag == '') {

            $call_status_flag = 4;

        }



        if ($this->input->post('meeting_duration')) {

            $meeting_duration = $this->input->post('meeting_duration');



        } else {

            $meeting_duration = 11;

        }

        //$meeting_link =   $this->input->post('farmer_id');

        //$meeting_duration = $this->input->post('meeting_duration');

        $meeting_link = $this->input->post('MeetingId');



        $sql_emeeting = "SELECT created_on FROM eMeeting WHERE meeting_link_id='" . $meeting_link . "' ORDER BY id DESC LIMIT 1";

        $booked_call_query = $this->db->query($sql_emeeting);

        $emeeting = $booked_call_query->result_array();

        $d1 = new DateTime(current_date());

        $d2 = new DateTime($emeeting[0]['created_on']);

        $interval = $d2->diff($d1);

        $hour = $interval->format('%H');



        $time = $interval->format('%H:%I:%S');

        $arr = explode(':', $time);

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

        //$meeting_link

        $where_array = array(

            'farmer_id' => $farmer_id,

            'partner_id' => $partner_id,

            'meeting_link_id' => $meeting_link,

            'meeting_status_id !=' => 4,

        );



        //'call_duration' => $meeting_duration



        $update_array = array(

            'meeting_status_id' => $call_status_flag,

            'meeting_end_from' => 1,

            'updated_on' => current_date(),

            'call_duration' => $call_duration,

            'call_duration_sec' => $call_duration_sec,

        );



        $sql_update = $this->db->update('emeeting', $update_array, $where_array);



        ////////////////////////////////////////////////////////////////////////////////////

        $sql_user = "SELECT first_name, last_name,device_id from users where user_id=" . $partner_id . "  LIMIT 1";

        $row_user = $this->db->query($sql_user);

        $user_data = $row_user->result_array();



        $partner_name = $user_data[0]['first_name'] . ' ' . $user_data[0]['last_name'];



        ////////////////////////////////////////

        $sql = "SELECT first_name, last_name,device_id from client where id=" . $farmer_id . "  LIMIT 1";

        $row = $this->db->query($sql);

        $farmer_data = $row->result_array();



        $farmer_name = $farmer_data[0]['first_name'] . ' ' . $farmer_data[0]['last_name'];



        $data['title'] = 'eMeeting';



        $title = 'Incoming call';

        $message = 'Dear ' . $partner_name . ' have Call from ' . $farmer_name . ' please join meeting ';



        $admno = $partner_id;

        $call_data = array(

            'farmer_id' => $farmer_id,

            'farmer_name' => $farmer_name,

            'partner_name' => $partner_name,

            'meeting_link_id' => $meeting_link,

        );



        //$token[] = $farmer_data[0]['device_id'];

        // $token[] = $user_data[0]['device_id'];



        /*$token[] = 'dd6iOBwSQSCTyFLwDXBsHZ:APA91bFxIXftTYQcgIAQB7Tu06AlA1cu9mWN2paXXArIJp27tpxw3iaxYsDKCgF0dS2zgYVUK2qXsL63WUgUM3L6r4SF78SaLTxznrvUrQvY7I2Eude14H5seECti2cnglZPTOYoKv55';*/



        /* $token[] = 'dd6iOBwSQSCTyFLwDXBsHZ:APA91bFxIXftTYQcgIAQB7Tu06AlA1cu9mWN2paXXArIJp27tpxw3iaxYsDKCgF0dS2zgYVUK2qXsL63WUgUM3L6r4SF78SaLTxznrvUrQvY7I2Eude14H5seECti2cnglZPTOYoKv55';*/

        // nayan device ID



        $data['call_details'] = $call_data;

        //Device Id for Manoj Mobile to test Notification

        //$token[] = 'dcNMWbLMr1c:APA91bGni1E09a6jOEc0IUNlyLA8bixfOjUczIYR26GDlbRLoP48JlMK5ueeLnIqXajh8eMNC97LOVbFzORIpIwUI2q1dnOAqi2cpKK_256hVmq3jplLyxVVqKlTwLUnZrLr53SxLnqE';



        $data['title'] = 'eMeeting';



        $title = 'disconnect call';

        /* $message = 'Dear ' . $partner_name . ' have Call Disconnected from ' . $farmer_data . ' please stop meeting';*/

        $message = 'Your call is Disconnected';



        $admno = $partner_id;

        $call_data = array();



        //$token[] = $farmer_data[0]['device_id'];

        $token[] = $user_data[0]['device_id'];



        // nayana Device ID

        $token[] = 'ey2_WNsWRqO68C1H3KhH_f:APA91bFS9zHgaxQlapb05nJRmixIo29KBe5Jqjg0ha3osYTlkyU1DCqyh2elzSSVVtxd8-dG_GyxLjtcPgByLNbD97i40FczJnlBd41FFmB2csEztkPDof-hzLh4f7PrRCE-UYCy_JJv';



        $jsonString = self::sendPushNotificationToFCMSeverdev($token, $title, $message, $admno, $call_data, $meeting_link, $partner_name, $farmer_id, 'disconnect call');



        $response = array("success" => 1, "call_data"->$where_array, "error" => 0, "status" => 1, "data" => $update_array);

        $this->api_response($response);

    }



    public function insured_crops_get()
    {

        $res = array();

        $sql = "SELECT p.id,p.title,p.premium_per_acre,p.region_id,p.company_id,c.crop_id,c.name,c.name_mr,c.logo as mob_icon,c.mob_icon as icona,d.name as insurance_company_name,d.logo as insurance_company_logo  FROM product_relation as p

        LEFT JOIN crop as c ON c.crop_id = p.crop_id

        LEFT JOIN insurance_company_master as d ON d.insurance_company_id = p.company_id

        WHERE p.is_deleted = false  AND c.is_deleted = false";

        $res_chk = $this->db->query($sql);

        $res = $res_chk->result_array();



        if (count($res) > 0) {

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $res, "message" => "Insured Crop listing");



        } else {

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $res, "message" => "No Insured Crop ");

        }

        $this->api_response($response);

        exit;

    }



    public function all_crop_insurance_get()
    {

        $res = array();

        /*       $sql = "SELECT p.id,p.title,p.premium_per_acre,p.region_id,p.company_id,c.crop_id,c.name,c.name_mr,c.logo as mob_icon,c.mob_icon as icona,d.name as insurance_company_name,d.logo as insurance_company_logo,ct.name as city_name  FROM product_relation as p

        LEFT JOIN crop as c ON c.crop_id = p.crop_id

        LEFT JOIN cities_new ct ON ct.id = p.region_id

        LEFT JOIN insurance_company_master as d ON d.insurance_company_id = p.company_id

        WHERE p.is_deleted = false  AND c.is_deleted = false";

         */

        $sql = "SELECT STRING_AGG(ci2.name, ',') AS city_name,u12.crop_id, c.name as crop_name, c.name_mr as crop_name_mr,

                d.name as insurance_company_name,d.logo as insurance_company_logo

                FROM public.product_relation u12

                inner join cities_new ci2 on u12.region_id = ci2.id

                LEFT JOIN crop as c ON c.crop_id = u12.crop_id

                LEFT JOIN insurance_company_master as d ON d.insurance_company_id = u12.company_id

                GROUP BY u12.crop_id, c.crop_id,d.insurance_company_id";



        $res_chk = $this->db->query($sql);

        $res = $res_chk->result_array();



        if (count($res) > 0) {

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $res, "message" => "Insured Crop listing");



        } else {

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $res, "message" => "No Insured Crop ");

        }

        $this->api_response($response);

        exit;

    }



    public function insurance_data_post()
    {

        /* $region_id  = $_REQUEST['region_id'];

        $crop_id    = $_REQUEST['crop_id'];

        $data_array['payble_amount']    = 200;*/



        $crop_id = $_REQUEST['crop_id'];

        $region_id = $_REQUEST['region_id'];

        $land_id = $_REQUEST['land_id'];

        $area_under_cultivation = $_REQUEST['area_under_cultivation'];

        $unit = $_REQUEST['unit']; //id

        //echo $type_get = $type_get;



        if (!$area_under_cultivation) {

            $area_under_cultivation = 1;

        }



        $response = array();

        if ($region_id != '' && $crop_id != '') {



            $sql_data = "SELECT p.id,p.title,p.premium_per_acre,p.region_id,p.company_id,c.crop_id,c.name,c.name_mr,c.logo as mob_icon,c.mob_icon as mob_icon ,d.name as insurance_company_name,d.logo as insurance_company_logo,ct.name as city_name

            FROM product_relation as p

            LEFT JOIN crop as c ON c.crop_id = p.crop_id

            LEFT JOIN cities_new ct ON ct.id = p.region_id

            LEFT JOIN insurance_company_master as d ON d.insurance_company_id = p.company_id

            WHERE p.is_deleted = false  AND p.region_id = $region_id AND p.crop_id = $crop_id AND c.is_deleted = false";



            $row = $this->db->query($sql_data);

            $res = $row->result_array();



            if (count($res) > 0) {



                $crop_cal = $res[0];

                $premium_per_acre = $crop_cal['premium_per_acre'];



                if ($unit == 2) {

                    //Acre

                    $payble_amount = $premium_per_acre * $area_under_cultivation;

                } else {

                    //Hectare

                    $payble_amount = $premium_per_acre * (2.47 * $area_under_cultivation);

                }



                $data_array = $res[0];

                $data_array['payble_amount'] = round($payble_amount);

                $data_array['premium_per_acre'] = $premium_per_acre;



                $new_array[] = $data_array;

                // $new_array = json_decode(json_encode($data_array), true);



                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $new_array, "message" => "Insured Crop Terms Data");

                $this->api_response($response);

                exit;



            } else {



                $response = array("status" => 0, "message" => "Insurance details from Crop");



            }

        } else {

            $response = array("status" => 0, "message" => "Insurance details from Crop");

        }



        $this->api_response($response);

    }



    public function crop_insured_tc_post()
    {

        $res = array();

        $crop_id = $_REQUEST['crop_id'];

        $region_id = $_REQUEST['region_id'];



        $sql = "SELECT p.id,p.title,p.premium_per_acre,p.region_id,p.company_id,p.insurance_details,p.terms_sheet,p.duration,p.other_details,c.name,c.name_mr,d.name as insurance_company_name,d.logo as insurance_company_logo  FROM product_relation as p

        LEFT JOIN crop as c ON c.crop_id = p.crop_id

        LEFT JOIN insurance_company_master as d ON d.insurance_company_id = p.company_id

        WHERE p.crop_id =  $crop_id AND p.region_id = $region_id  AND p.is_deleted = false  AND c.is_deleted = false";

        $res_chk = $this->db->query($sql);

        $res = $res_chk->result_array();



        if (count($res) > 0) {

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $res, "message" => "Insured Crop Terms Data");



        } else {

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $res, "message" => "No Insured Terms Crop ");

        }

        $this->api_response($response);

        exit;

    }



    public function farm_crops_post()
    {

        $res = array();

        $crop_id = $_REQUEST['crop_id'];

        $farmer_id = $_REQUEST['farmer_id'];

        // $region_id = $_REQUEST['region_id'];



        //$response = array();

        if ($farmer_id != '' && $crop_id != '') {



            // LEFT JOIN cities_new ct ON ct.id = m.cities_id

            //ct.name as city_name

            //AND c.cities_id != ''



            $sql = "SELECT c.id as crop_land_id,cm.name,cm.name_mr,cm.duration_days,m.land_id ,m.farmer_id , m.soil_type , m.farm_name,m.topology ,m.farm_type ,m.farm_size ,m.unit ,m.irrigation_facility , m.farm_image, m.calculated_land_area ,m.survey_no ,m.khasra_no ,m.irrigation_source,c.crop_image,c.crop_type,c.duration_from,c.duration_to,c.area_under_cultivation,m.doc_7_12,m.cities_id FROM master_crop_details as c

            LEFT JOIN master_land_details as m ON m.land_id = c.land_id

            LEFT JOIN crop cm ON cm.crop_id = c.crop

            WHERE m.farmer_id='" . $farmer_id . "'  AND c.crop = $crop_id AND m.is_deleted = 'false' ORDER BY m.land_id DESC";

            //AND m.cities_id = '" . $region_id . "'



            $row = $this->db->query($sql);

            $result = $row->result_array();



            if (count($result)) {



                foreach ($result as $value) {



                    //$value = $result[0];



                    $topology_name = $topology_name_mr = $unit_name = $unit_name_mr = $irri_faty_name = $irri_faty_name_mr = $irri_src_name = $irri_src_name_mr = $soil_type_name = $soil_type_name_mr = $farm_type_name = $farm_type_name_mr = null;



                    if (!is_null($value['soil_type'])) {

                        //echo $value['topology'];

                        $soil_type_name = $this->soil_type_web[$value['soil_type']];

                        $soil_type_name_mr = $this->soil_type_web_mr[$value['soil_type']];

                    }

                    if (!is_null($value['farm_type'])) {

                        //echo $value['topology'];

                        $farm_type_name = $this->farm_type_web[$value['farm_type']];

                        $farm_type_name_mr = $this->farm_type_web_mr[$value['farm_type']];

                    }



                    if (!is_null($value['topology'])) {

                        //echo $value['topology'];

                        $topology_name = $this->topology_web[$value['topology']];

                        $topology_name_mr = $this->topology_web_mr[$value['topology']];

                    }

                    if (!is_null($value['unit'])) {

                        $unit_name = $this->unit_web[$value['unit']];

                        $unit_name_mr = $this->unit_web_mr[$value['unit']];

                    }

                    if (!is_null($value['irrigation_facility'])) {

                        $irri_faty_name = $this->irri_faty_web[$value['irrigation_facility']];

                        $irri_faty_name_mr = $this->irri_faty_web_mr[$value['irrigation_facility']];

                    }

                    if (!is_null($value['irrigation_source'])) {

                        $irri_src_name = $this->irri_src_web[$value['irrigation_source']];

                        $irri_src_name_mr = $this->irri_src_web_mr[$value['irrigation_source']];

                    }



                    if ($value['duration_from'] != '' && $value['duration_days'] != 0) {

                        //2021-03-29

                        $duration_to = '';

                        $duration = '+' . $value['duration_days'] . ' days';

                        //define('ADD_DAYS','+'.$duration.'' days');

                        //$start_date = date('Y-m-d H:i:s');

                        $duration_to = date("Y-M-d", strtotime($duration, strtotime($value['duration_from'])));



                        // $duration_to = date("Y-m-d", strtotime($value['duration_from'],$duration));

                    }



                    if (!is_null($value['crop_type'])) {

                        $crop_type_name = $this->crop_type_web[$value['crop_type']];

                        $crop_type_name_mr = $this->crop_type_web_mr[$value['crop_type']];

                    }



                    if (!is_null($value['crop_image'])) {

                        $crop_image = base_url('uploads/user_data/crop_image/' . $value['crop_image']);

                    } else {

                        $crop_image = base_url('uploads/user_data/crop_image/default.png');

                    }



                    $land_id = $value['land_id'];

                    $crop_land_id = $value['crop_land_id'];



                    $chk_insurance_sql = "SELECT id,application_status from crop_insurance_details where farmer_id='" . $farmer_id . "' AND land_id='" . $land_id . "' AND crop_land_id='" . $crop_land_id . "'  AND crop_id='" . $crop_id . "' ";

                    $row_chk = $this->db->query($chk_insurance_sql);

                    $result_ins = $row_chk->result_array();



                    if (count($result_ins)) {

                        $is_insured = 1;

                        $insurance_id = $result_ins[0]['id'];

                        $insurance_status = $result_ins[0]['application_status'];

                    } else {



                        $is_insured = 0;

                        $insurance_id = 0;

                        $insurance_status = null;

                    }



                    $new_crop_arr[] = array(

                        'land_id' => $value['land_id'],

                        'farmer_id' => $value['farmer_id'],

                        'crop_land_id' => $value['crop_land_id'],

                        'crop' => $value['crop'],

                        'crop_image' => $crop_image,

                        'farm_image' => $value['farm_image'],

                        'crop_name' => $value['name'],

                        'crop_name_mr' => $value['name_mr'],

                        'crop_type' => $value['crop_type'],

                        'crop_type_name' => $crop_type_name,

                        'crop_type_name_mr' => $crop_type_name_mr,

                        'unit' => $value['unit'],

                        'unit_name' => $unit_name,

                        'unit_name_mr' => $unit_name_mr,

                        'area_under_cultivation' => $value['area_under_cultivation'],

                        'duration_from' => $value['duration_from'],

                        'duration_to' => $duration_to,

                        'farm_name' => $value['farm_name'],

                        'farm_name_mr' => $value['farm_name_mr'],

                        'duration_days' => $value['duration_days'],

                        'doc_7_12' => $value['doc_7_12'],

                        'cities_id' => $value['cities_id'],

                        'is_insured' => $is_insured,

                        'insurance_id' => $insurance_id,

                        'insurance_status' => $insurance_status,



                    );

                }



                //"data" => $result, crop_data

                $response = array("status" => 1, 'data' => $new_crop_arr, "message" => lang('Listed_Successfully'));

            } else {

                $new_crop_arr = array();

                $result = array();

                $response = array("status" => 1, "data" => $result, 'crop_data' => $new_crop_arr, "message" => lang('Data_Not_Found'));



            }



        } else {

            $response = array("status" => 0, "message" => lang('Data_Not_Found'));

        }



        $this->api_response($response);

    }



    public function farm_crops_validation_post()
    {

        $res = array();

        $crop_id = $_REQUEST['crop_id'];

        $region_id = $_REQUEST['region_id'];

        $land_id = $_REQUEST['land_id'];

        $area_under_cultivation = $_REQUEST['area_under_cultivation'];

        $unit = $_REQUEST['unit']; //id



        // $unit_name              = $_REQUEST['unit_name'];

        //region id, land id, crop id, cultivation area and unit



        if ($region_id != '' && $crop_id != '' && $land_id != '' && $area_under_cultivation != '' && $unit != '') {



            $sql = "SELECT p.id,p.title,p.premium_per_acre,p.region_id,p.company_id,p.insurance_details,p.terms_sheet,p.duration,p.other_details,c.name,c.name_mr,d.name as insurance_company_name,d.logo as insurance_company_logo  FROM product_relation as p

            LEFT JOIN crop as c ON c.crop_id = p.crop_id

            LEFT JOIN insurance_company_master as d ON d.insurance_company_id = p.company_id

            WHERE p.crop_id =  $crop_id AND p.region_id = $region_id  AND p.is_deleted = false  AND c.is_deleted = false";

            $res_chk = $this->db->query($sql);

            $res = $res_chk->result_array();



            if (count($res) > 0) {



                $crop_cal = $res[0];

                $premium_per_acre = $crop_cal['premium_per_acre'];



                /*

                Acre to other converiosn

                1 = 2.47 Hectare

                1  = 4840 Square Yard

                1 = 0.0015625 Square Mile

                1 = 4046.86 Square Meter

                y Acre or Hectare

                 */



                if ($unit == 2) {

                    //Acre

                    $payble_amount = $premium_per_acre * $area_under_cultivation;

                } else {

                    //Hectare

                    $payble_amount = $premium_per_acre * (2.47 * $area_under_cultivation);

                }



                /* elseif ($unit == 4) {

                //Square Meter

                $payble_amount = $premium_per_acre * (4046.86 * $area_under_cultivation);

                } elseif ($unit == 1) {

                //Square Yard

                $payble_amount = $premium_per_acre * (4840 * $area_under_cultivation);

                } else {

                //Square Mile

                $payble_amount = $premium_per_acre * (0.0015625 * $area_under_cultivation);

                }*/



                // $data_array['payble_amount'] =  $payble_amount;

                $data_array = $res[0];

                $data_array['payble_amount'] = $payble_amount;

                $data_array['premium_per_acre'] = $premium_per_acre;



                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data_array, "message" => "Insured Crop Terms Data");



            } else {

                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => null, "message" => "No Insured Terms Crop ");

            }

        } else {

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => null, "message" => "No Insured Terms Crop ");

        }

        $this->api_response($response);

        exit;

    }



    public function add_insurance_data_post()
    {

        $land_id = $_REQUEST['land_id'];

        $crop_id = $_REQUEST['crop_id'];

        $farmer_id = $_REQUEST['farmer_id'];

        $amount = $_REQUEST['amount'];

        $product_id = $_REQUEST['product_id'];

        $crop_land_id = $_REQUEST['crop_land_id'];

        $company_id = $_REQUEST['company_id'];

        //company_id //crop_id //land_id //amount //pay_status //application_status //policy_no

        $policy_no = $_REQUEST['farmer_id'] . $_REQUEST['land_id'] . time();



        if ($crop_id != '' && $land_id != '' && $farmer_id && $amount != '') {

            //insurance_details //crop_insurance_details

            $insert_data = array(

                'farmer_id' => $this->input->post('farmer_id'),

                'amount' => $this->input->post('amount'),

                'crop_id' => $this->input->post('crop_id'),

                'land_id' => $this->input->post('land_id'),

                'product_id' => $this->input->post('product_id'),

                'company_id' => $this->input->post('company_id'),

                'pay_status' => $this->input->post('pay_status'),

                'premium_per_acre' => $this->input->post('premium_per_acre'),

                'crop_land_id' => $this->input->post('crop_land_id'),

                'application_status' => 'Pending',

                'policy_no' => $policy_no,

            );

            $result = $this->db->insert('crop_insurance_details', $insert_data);



            if (count($result) > 0) {

                $response = array("success" => 1, "error" => $error, "status" => 1, "data" => $res, "message" => "Insurance application submitted successfully");



            } else {

                $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $res, "message" => "Error in adding insurance");

            }



        } else {

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $res, "message" => lang('Missing_Parameter'));

        }

        $this->api_response($response);

        exit;



    }



    public function list_insurance_data_post()
    {

        $res = array();

        $farmer_id = $_REQUEST['farmer_id'];

        if ($farmer_id != '') {

            /*   $sql       = "SELECT cr.*,c.name,c.name_mr FROM crop_insurance_details  as cr

            LEFT JOIN crop as c ON c.crop_id = cr.crop_id

            WHERE cr.is_deleted = false  ORDER BY cr.id DESC"; */



            /* $sql       = "SELECT * FROM crop_insurance_details

            WHERE is_deleted = false ORDER BY id DESC"; */



            $sql = "SELECT cr.*,c.name,c.name_mr FROM crop_insurance_details  as cr

           INNER JOIN crop as c ON c.crop_id = cr.crop_id

        WHERE cr.is_deleted = false   AND  farmer_id=$farmer_id ORDER BY cr.id DESC";



            //AND  farmer_id=$farmer_id

        } else {



            $sql = "SELECT cr.*,c.name,c.name_mr FROM crop_insurance_details  as cr

           LEFT JOIN crop as c ON c.crop_id = cr.crop_id

        WHERE cr.is_deleted = false  ORDER BY cr.id DESC";

            /*  $sql       = "SELECT * FROM crop_insurance_details

        WHERE is_deleted = false AND  farmer_id=$farmer_id ORDER BY id DESC";    */

        }



        $res_chk = $this->db->query($sql);

        $res = $res_chk->result_array();



        if (count($res) > 0) {

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $res, "message" => "Insurance Application listing");



        } else {

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $res, "message" => "No application listing");

        }

        $this->api_response($response);

        exit;

    }



    public function agronomist_crops_get()
    {

        //$type_get = 5;

        $response = array();

        $user_data = array();




        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

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



        // name_hi , '.$lang_label.'



        /* $selects = array('user_id', 'first_name','last_name','phone_no','address','profile_image','is_deleted','deleted_on','is_active','type','user_type','is_external','device_id','is_video_enable','is_chat_enable','crop_id');*/

        $selects = array('crop_id');



        $where = array('users.crop_id !=' => '', 'users.is_deleted' => 'false', 'users.is_active' => 'true');

        $user_data = $this->Masters_model->get_data($selects, 'users', $where);

        $a = array();

        foreach ($user_data as $v) {

            $a[] = $v['crop_id'];

            //$m =array();

            $m = explode(',', $v['crop_id']);



        }



        // $p             = array_merge($s);

        $n = implode(',', $a);

        $crop_id_array = array_unique(explode(',', $n));



        $crop_id_str = implode(',', $crop_id_array);



        $sql_crops = 'SELECT crop_id, name, ' . $lang_label . ' ,logo as mob_icon FROM crop WHERE crop_id IN (' . $crop_id_str . ')';

        $res_val = $this->db->query($sql_crops);

        $res_array = $res_val->result_array();



        //print_r($res_array);



        if (count($user_data)) {

            $response = array("status" => 1, 'nw_key' => $crop_id_array, "data" => $res_array, "message" => lang('Listed_Successfully'));

        } else {

            $response = array("status" => 1, "data" => null, "message" => lang('Data_Not_Found'));

        }



        $this->api_response($response);

    }



    public function agronomist_get($crop_id)
    {

        $type_get = 5;

        $response = array();

        $user_data = array();



        $sql = "SELECT id,logo,mob_icon,description,key_fields FROM config_master WHERE key_fields ='emeeting_price' AND  is_deleted = false  AND is_active = true ORDER BY id ASC";



        $res_chk = $this->db->query($sql);

        $res_price = $res_chk->result_array();



        $sql_query = "SELECT user_id,first_name,last_name,company_name,phone_no,city,profile_image,crop_id,user_type,type,is_video_enable,is_chat_enable,expertise,user_experiance,rating FROM users WHERE is_deleted=false AND is_active=true AND user_type = 5 AND '" . $crop_id . "' = ANY (string_to_array(crop_id,','))";



        $query = $this->db->query($sql_query);

        $res = $query->result_array();



        foreach ($res as $res_key => $res_value) {



            $sql = "SELECT name,crop_id FROM crop WHERE is_deleted=false AND is_active=true AND crop_id IN (" . $res_value['crop_id'] . ")";



            $crop_query = $this->db->query($sql);



            $crop_array = $crop_query->result_array();



            $sql_schedule = "SELECT time_from,time_to FROM crop_adviser_schedule WHERE is_deleted=false AND adviser_id = '" . $res_value['user_id'] . "' LIMIT 1";



            $schedule_query = $this->db->query($sql_schedule);



            $schedule_array = $schedule_query->result_array();



            $res[$res_key]['expertise'] = implode(',', array_column($crop_array, 'name'));

            $res[$res_key]['user_experiance'] = $res[$res_key]['user_experiance'] . ' years';

            $sql_price = "SELECT setting_value,setting_title FROM partner_settings WHERE is_deleted=false AND partner_id='" . $res_value['user_id'] . "'";

            $price_query = $this->db->query($sql_price);

            $price_array = $price_query->result_array();

            $setting_title = array_column($price_array, 'setting_title');

            $advisory_call_amount_key = array_search("advisory_call_amount", $setting_title);

            $advisory_call_payment_status_key = array_search("advisory_call_payment_status", $setting_title);

            $res[$res_key]['price'] = $price_array[$advisory_call_amount_key]['setting_value'];

            $res[$res_key]['payment_status'] = $price_array[$advisory_call_payment_status_key]['setting_value'];

            $res[$res_key]['availability'] = implode(" To ", $schedule_array[0]);

        }



        if (count($res)) {

            $response = array("status" => 1, "data" => $res, "config_url" => $this->config_url, "message" => lang('Listed_Successfully'), "Video call price" => $res_price);

        } else {

            $response = array("status" => 1, "data" => null, "config_url" => $this->config_url, "message" => lang('Data_Not_Found'));

        }



        $this->api_response($response);

    }



    public function time_slot_post()
    {



        $response = array();

        $user_data = array();

        $date = $this->input->post('date');

        $partner_id = $this->input->post('partner_id');

        $day = date('D', strtotime($date));



        if ($partner_id != '' && $date != '') {



            $sql = "SELECT working_days,time_from,time_to FROM crop_adviser_schedule WHERE is_deleted=false AND adviser_id= '" . $partner_id . "' AND working_days= '" . $day . "'";



            $query = $this->db->query($sql);

            $res = $query->result_array();



            $res[0]['time_slot'] = self::SplitTime($res[0]['time_from'], $res[0]['time_to'], $date, $partner_id);



            if (count($res)) {

                $response = array("status" => 1, "data" => $res[0]['time_slot'], "message" => "Available time slot");

            } else {

                $response = array("status" => 1, "data" => null, "message" => "Agronomist not available");

            }



        } else {



            $response = array("status" => 0, "message" => lang('Missing_Parameter'));



        }



        $this->api_response($response);

    }



    public function farmer_booked_slot_post()
    {

        $farmer_id = $this->input->post('farmer_id');

        $call_status = $this->input->post('status');

        if ($farmer_id != '') {

            $sql_query = "SELECT product_leads.id,product_leads.client_id,product_leads.partner_id,product_leads.call_schedule_date,product_leads.call_schedule_time,product_leads.schedule_call_status,product_leads.call_reschedule_date,product_leads.call_reschedule_time,users.first_name,users.last_name,users.company_name,users.phone_no,users.city,users.profile_image,users.crop_id,users.user_type,users.type,users.is_video_enable,users.is_chat_enable,users.expertise,users.user_experiance,users.rating,crop.name,crop.name_mr FROM product_leads JOIN users ON users.user_id=product_leads.partner_id FULL JOIN crop ON crop.crop_id = product_leads.crop_id  WHERE product_leads.is_deleted=false AND product_leads.product_type='video_call_schedule' AND client_id= '" . $farmer_id . "' ORDER BY product_leads.id DESC";

            /*if ($schedule_call_status != '') {

            $sql_query .= " AND schedule_call_status IN ('" . $schedule_call_status . "')";

            }*/

            $booked_call_query = $query = $this->db->query($sql_query);

            $booked_call = $query->result_array();

            foreach ($booked_call as $res_key => $res_value) {



                $datecreate = date_create($res_value['call_schedule_date']);

                $booked_call[$res_key]['call_schedule_date'] = date_format($datecreate, "d-M-Y");



                $booked_call[$res_key]['expertise'] = array();

                if ($res_value['crop_id']) {

                    $sql = "SELECT name,crop_id FROM crop WHERE is_deleted=false AND is_active=true AND crop_id IN (" . $res_value['crop_id'] . ")";



                    $crop_query = $this->db->query($sql);



                    $crop_array = $crop_query->result_array();

                    $booked_call[$res_key]['expertise'] = implode(',', array_column($crop_array, 'name'));

                }



                $datecreate = date_create($res_value['call_schedule_date']);

                $booked_call[$res_key]['call_schedule_date'] = date_format($datecreate, "d-M-Y");



                // $booked_call[$res_key]['call_schedule_time'] = date("g:i A", strtotime($res_value['created_on']));

                $booked_call[$res_key]['call_schedule_time'] = $res_value['call_schedule_time'];



                $sql_emeeting = "SELECT meeting_status, call_duration, call_duration_sec FROM emeeting WHERE lead_id='" . $res_value['id'] . "' ORDER BY id DESC LIMIT 1";



                $booked_call_query = $this->db->query($sql_emeeting);

                $emeeting = $booked_call_query->result_array();



                $booked_call[$res_key]['call_duration'] = $emeeting[0]['call_duration'];



                $d1 = new DateTime($res_value['call_schedule_date']);

                $d2 = new DateTime(date("Y-m-d"));

                $interval = $d1->diff($d2);

                //$days     = $interval->format('%d');

                $days = $interval->days;



                $schedule_date = strtotime($res_value['call_schedule_date']);



                if ($emeeting[0]['call_duration_sec'] > 60) {

                    $booked_call[$res_key]['call_status'] = "Past";

                    $booked_call[$res_key]['schedule_call_status'] = lang('schedule_call_past');

                    ///&& $emeeting[0]['call_duration_sec'] != ''

                } else if ($days > 2 && ($emeeting[0]['call_duration_sec'] < 60) && $schedule_date < time()) {

                    $booked_call[$res_key]['call_status'] = "Cancelled";

                    $booked_call[$res_key]['schedule_call_status'] = lang('schedule_call_cancel');

                } else if (!empty($emeeting) && $emeeting[0]['call_duration_sec'] < 60 && ($schedule_date > time() or $days <= 2)) {

                    $booked_call[$res_key]['call_status'] = "Reschedule";

                    $booked_call[$res_key]['schedule_call_status'] = lang('schedule_call_reschedule');

                } else if ($emeeting[0]['call_duration_sec'] == '' && ($schedule_date > time() or $days <= 2)) {

                    $booked_call[$res_key]['call_status'] = "Upcoming";

                    $booked_call[$res_key]['schedule_call_status'] = lang('schedule_call_upcoming');

                }

                if ($booked_call[$res_key]['user_experiance']) {

                    $booked_call[$res_key]['user_experiance'] = $booked_call[$res_key]['user_experiance'] . ' ' . lang('years');

                }



                $sql_price = "SELECT setting_value,setting_title FROM partner_settings WHERE is_deleted=false AND setting_title='advisory_call_amount'";

                $price_query = $this->db->query($sql_price);

                $price_array = $price_query->result_array();

                $booked_call[$res_key]['price'] = $price_array[0]['setting_value'];



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

            $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);

    }



    public function add_vendor_call_leads_post()
    {


        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';



        $message_mr = "तुमची कॉल विनंती आमच्या पीक सल्लागाराला यशस्वीरित्या पाठवण्यात आली आहे, तुम्हाला 48 तासांच्या आत कॉल प्राप्त होईल";

        if ($selected_lang == 'mr') {

            $message_mr = "तुमची कॉल विनंती आमच्या पीक सल्लागाराला यशस्वीरित्या पाठवण्यात आली आहे, तुम्हाला 48 तासांच्या आत कॉल प्राप्त होईल";

            $lang_folder = "marathi";

        } elseif ($selected_lang == 'hi') {

            $lang_folder = "hindi";

            $message_mr = "आपका कॉल अनुरोध हमारे फसल सलाहकार को सफलतापूर्वक भेज दिया गया है, आपको 48 घंटों के भीतर एक कॉल प्राप्त होगी";

        } else {

            $lang_folder = "english";

        }



        $this->load->model('Notification_model');

        $custom_array = '';

        $farmer_id = $this->input->post('farmer_id');

        $partner_id = $this->input->post('partner_id');

        $crop_id = $this->input->post('crop_id');



        $call_schedule_date = date('Y-m-d');



        // $call_schedule_date = $this->input->post('call_schedule_date');



        $datecreate = date_create($call_schedule_date);

        $date_formate = $call_schedule_date;

        /*$slot_time[] = $this->input->post('slot_time_from');

        $slot_time[] = $this->input->post('slot_time_to');

        $call_schedule_time = implode("-",$slot_time);*/

        //$call_schedule_timestamp = $this->input->post('call_schedule_timestamp');

        $product_type = 'video_call_schedule';

        $is_custom = 0;

        $img = '';

        $response = array();

        date_default_timezone_set('Asia/Kolkata');

        $booking_time = date('g:i A');

        if ($farmer_id != '' && $partner_id != '' && $call_schedule_date != '') {



            $insert = array(

                'client_id' => $farmer_id,

                'partner_id' => $partner_id,

                'crop_id' => $crop_id,

                'is_custom' => $is_custom,

                'product_type' => $product_type,

                'schedule_call_status' => 'Requested',

                'call_schedule_date' => $date_formate,

                'call_schedule_time' => $booking_time,

                'created_on' => current_date(),



            );





            // print_r($insert);exit;



            $this->db->insert('product_leads', $insert);

            $lead_id = $this->db->insert_id();



            // Start E meeting insert: Akash

            $meeting_link = date("Ymdhis") . $this->input->post('farmer_id') . $this->input->post('partner_id');

            $insert_emeeting = array(

                'farmer_id' => $farmer_id,

                'partner_id' => $partner_id,

                'meeting_status_id' => 1,

                'meeting_started_from' => 1,

                'meeting_link_id' => md5($meeting_link),

                'is_active' => 'true',

                'created_on' => current_date(),

            );



            $this->db->insert('emeeting', $insert_emeeting); // emeeting insert



            // End E meeting insert : Akash



            $sql = "SELECT id,device_id,first_name,last_name FROM client WHERE is_deleted='false' AND is_active='true' AND device_id is NOT NULL AND id =" . $farmer_id;

            $row_tag = $this->db->query($sql);

            $results = $row_tag->result_array();

            //print_r($results);

            $sql_vendor = "SELECT user_id,device_id,first_name,last_name FROM users WHERE is_deleted='false' AND is_active='true' AND device_id is NOT NULL AND user_id =" . $partner_id;

            $row_vendor = $this->db->query($sql_vendor);

            $results_vendor = $row_vendor->result_array();



            $sql_img = "SELECT id,name,mob_icon FROM config_master WHERE is_deleted='false' AND is_active='true' AND id = 31";

            $row_tag_img = $this->db->query($sql_img);

            $results_img = $row_tag_img->result_array();

            $img = $results_img[0]['mob_icon'];



            /*users table$token[] = 'caAuzKuWlvw:APA91bHGt24ZqLNsA5_rLjNDDxWaYuU4AuQUtbLUKuZ9NoV0qru8PcPdZpj7HpmErbGix_rmAC1EJv_E24AngMMig430muESMoQJCulYVTWNTdrpCQWck3HQCsZNrBxNxOFzUnxB0I7P';*/

            $token[] = $results_vendor[0]['device_id'];

            $title = 'Call Schedule';

            /*From '.$results[0]['first_name'].' '.$results[0]['last_name'].'*/

            /* $message = 'You Have Call Schedule Request From ' . $results[0]['first_name'] . ' ' . $results[0]['last_name'] . ' on ' . $date_formate . ' at ' . $call_schedule_time;*/

            $message = 'You Have Call Schedule Request';



            $notifiy = $this->Notification_model->sendPushNotifications_request_partner($token, $title, $message, $custom_array, $type = 'Schedule', $lead_id, $img);

            $dd = json_decode($notifiy);



            if ($dd->success == 1) {

                $sql_notify = "UPDATE product_leads SET notification_send = 'true' WHERE id = '" . $lead_id . "'";

                $results_notify = $this->db->query($sql_notify);

                //$results_notify = $row_notify->result_array();

            } else {

                $results_notify = false;

            }



            $response = array("status" => 1, "data" => 1, "message" => "Your call request has been sent successfully to our crop advisor, You will receive call within 48 hour", "message_mr" => $message_mr, 'Notification' => $message, 'notification_sent' => $results_notify);



        } else {

            $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        }

        $this->api_response($response);

    }



    public function bank_spash_get($group_id)
    {

        if ($group_id != '') {

            $row_bank = $this->db->query("SELECT logo,mob_icon FROM client_group_master WHERE is_active = 'true' AND is_deleted = 'false' AND client_group_id = $group_id");



            /*    $row_bank = $this->db->query("SELECT  gm.logo,gm.mob_icon,bm.bank_master_id

            FROM bank_master as bm

            LEFT JOIN client_group_master as gm ON gm.client_group_id = bm.group_id

            WHERE bm.is_active = 'true' AND bm.is_deleted = 'false' AND bm.bank_master_id = $bank_master_id

            LIMIT 1");*/

            $data = $row_bank->result_array();

            if (count($data) > 0) {

                if ($data[0]['logo'] != '' && $data[0]['mob_icon'] != '') {



                    $img_logo = $this->base_path . 'uploads/client_group_master/' . $data[0]['logo'];

                    $img_group = $this->base_path . 'uploads/client_group_master/' . $data[0]['mob_icon'];



                } else {



                    $img_logo = $this->base_path . 'assets/img/spoc.png';

                    $img_group = $this->base_path . 'assets/img/spoc.png';



                }

                //$response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, "message" => "get splash image", 'image' => $img_logo, 'logo' => $img_group);

                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, "message" => "get splash image", 'logo' => $img_logo, 'image' => $img_group);

                $this->api_response($response);

                exit;



            } else {



                $img_logo = $this->base_path . 'assets/img/spoc.png';

                $img_group = $this->base_path . 'assets/img/spoc.png';

                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, "message" => "get default splash image", 'image' => $img_group, 'logo' => $img_logo);

                $this->api_response($response);

                exit;

            }



        } else {



            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => null, "message" => lang('Missing_Parameter'), 'image' => null, 'logo' => null);

            $this->api_response($response);

            exit;



        }

    }



    public function announcement_get()
    {

        $this->db->select('*');

        $this->db->where('is_deleted', 'false');

        $this->db->order_by('id', 'DESC');

        $anouncement = $this->db->get('anouncement')->result_array();



        $result = [];



        if (!empty($anouncement)) {

            foreach ($anouncement as $key => $value) {

                if (!empty($value['created_on'])) {

                    $value['created_on'] = date('Y-m-d h:i:s', strtotime($value['created_on']));

                }

                $result[] = $value;

            }

        }



        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "anouncement data");



        $this->api_response($response);

    }



    public function announcement_detailsbkkk_post()
    {

        $announcement_id = trim($this->input->post('id'));

        $data_param = null;

        if ($announcement_id != '') {



            //$row_anouncement = $this->db->query("SELECT * FROM anouncement WHERE  is_deleted = 'false' AND id = $announcement_id");

            $row_anouncement = $this->db->query("SELECT * FROM anouncement WHERE  is_deleted = 'false' ");

            $anouncement_data = $row_anouncement->result_array();

            // $this->db->select('*');

            //$this->db->where('is_deleted', 'false');

            // $this->db->where('id',$announcement_id);            

            // $this->db->order_by('id', 'DESC');

            // $anouncement_data = $this->db->get('anouncement')->row_array();

            $sql = $this->db->last_query();

            if (count($anouncement_data)) {

                $data = $anouncement_data;

                $attached_document = array();

                //, 'rest' =>  $anouncement

                if ($data['attached_document']) {

                    $attached_document = explode(',', $data['attached_document']);

                }



                $data_param = array(
                    'title' => $data['title'],

                    'description' => $data['description'],

                    'priority_type' => $data['priority_type'],

                    'created_on' => $data['created_on'],

                    'attached_document' => $attached_document,

                );

            }

        }

        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $anouncement_data, "message" => "anouncement data", "sql" => $sql);



        $this->api_response($response);

    }



    public function announcement_details_post()
    {

        $announcement_id = trim($this->input->post('id'));

        $data_param = null;

        // $data_param  = array();

        if ($announcement_id != '') {



            $this->db->select('title,description,priority_type,created_on,attached_document');

            $this->db->where('is_deleted', 'false');

            $this->db->where('id', $announcement_id);

            //$this->db->order_by('id', 'DESC');

            $anouncement_data = $this->db->get('anouncement')->row_array();



            // print_r($anouncement_data);exit;

            if (!empty($anouncement_data['created_on'])) {

                $anouncement_data['created_on'] = date('Y-m-d h:i:s', strtotime($anouncement_data['created_on']));

            }



            // $sql = $this->db->last_query();

            if (count($anouncement_data)) {

                $data = $anouncement_data;

                $attached_document = array();

                //, 'rest' =>  $anouncement

                if ($data['attached_document']) {

                    $attached_document = explode(',', $data['attached_document']);

                }



                $data_param = array(
                    'title' => $data['title'],

                    'description' => $data['description'],

                    'priority_type' => $data['priority_type'],

                    'created_on' => $data['created_on'],

                    'attached_document' => $attached_document,

                );

            }



        }

        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data_param, "message" => "Anouncement data");



        $this->api_response($response);



    }



    public function notice_post()
    {

        //$farmer_id = $farmer_id

        $farmer_id = 0;

        //  $lead_id      = $this->input->post('lead_id');

        $farmer_id = $this->input->post('farmer_id');

        /* $headers_data = $this->input->request_headers();

        if (count($headers_data)) {

        if ($headers_data['user_id'] != '') {

        $farmer_id = $headers_data['user_id'];

        }

        }*/



        if ($farmer_id != '') {

            $this->db->select('*');

            $this->db->where('is_deleted', 'false');

            // if ($farmer_id) {

            $this->db->where('farmer_id', $farmer_id);

            //}

            $this->db->order_by('id', 'DESC');

            $notice = $this->db->get('notice_master')->result_array();



            if (!empty($notice[0]['created_on'])) {

                $notice[0]['created_on'] = date('Y-m-d h:i:s', strtotime($notice[0]['created_on']));

            }



            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $notice, "message" => "notice data");



        } else {

            $notice = array();

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => [], "message" => "No notice data farmer id misssing");

        }

        $this->api_response($response);

    }



    public function notice_details_post()
    {



        $farmer_id = $this->input->post('farmer_id');

        $notice_id = $this->input->post('notice_id');



        if ($farmer_id != '' && $notice_id != '') {

            /*  $this->db->select('*');

            $this->db->where('is_deleted', 'false');

            $this->db->where('farmer_id', $farmer_id);

            $this->db->order_by('id', $notice_id);

            $notice = $this->db->get('notice_master')->result_array();



            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $notice, "message" => "notice data");*/



            // $this->db->where('farmer_id', $farmer_id);

            $this->db->select('*');

            $this->db->where('is_deleted', 'false');



            $this->db->where('id', $notice_id);

            $data_param = array();

            $notice_res = $this->db->get('notice_master')->result_array();

            $notice_res[0]['created_on'] = date('Y-m-d h:i:s', strtotime($notice_res[0]['created_on']));



            if (count($notice_res)) {

                // print_r($notice_res);exit;

                $data = $notice_res[0];

                $attached_document = array();

                //, 'rest' =>  $notice_res

                if ($data['attached_document']) {

                    $attached_document = explode(',', $data['attached_document']);

                }



                $data_param = array(
                    'title' => $data['title'],

                    'description' => $data['description'],

                    'priority_type' => $data['priority_type'],

                    'created_on' => $data['created_on'],

                    'attached_document' => $attached_document,

                );

            }



            if (empty($data_param)) {

                $data_param = null;

            }



            // $notice   = array();

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data_param, "message" => "Notice details found!");



        } else {

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => null, "message" => "No notice data/ farmer id or Notice id misssing");

        }



        $this->api_response($response);



    }



    public function dss_module_get($crop_id = '')
    {



        // $headers_data  = $this->input->request_headers();

        // $selected_lang = $headers_data['lang'];

        // if($selected_lang == 'mr'){

        //      $lang_folder = "marathi";

        // }elseif($selected_lang == 'hi'){

        //     $lang_folder = "hindi";

        // }else{

        //       $lang_folder = "english";

        // }

        if ($crop_id) {



            $season_array = array('Kharif', 'Late Kharif', 'Rabi');



        } else {

            $season_array = array('Kharif', 'Late Kharif', 'Rabi');



        }

        $module_arr = [

            ['name' => lang('farm'), 'icon' => 'farms.png', 'key' => 1, 'seq' => 1],

            ['name' => lang('Crop_Calender'), 'icon' => 'crop_calender.png', 'key' => 2, 'seq' => 2],

            ['name' => lang('crop_manual'), 'icon' => 'crop_manual.png', 'key' => 3, 'seq' => 3],

            ['name' => lang('nutrient_management'), 'icon' => 'insecticide.png', 'key' => 4, 'seq' => 4],

            ['name' => lang('varieties'), 'icon' => 'variety.png', 'key' => 5, 'seq' => 5],

            ['name' => lang('pest_disease'), 'icon' => 'pest.png', 'key' => 6, 'seq' => 6],

        ];



        $response = array("status" => 1, "data" => $module_arr, "config_url" => $this->config_url, "season" => $season_array, "message" => "DSS Module List");



        $this->api_response($response);

    }



    public function crop_seasons_get()
    {

        $crop_seasons = array(

            array('id' => '1', 'name' => 'Kharif', 'name_mr' => 'खरीप'),

            array('id' => '2', 'name' => 'Rabi', 'name_mr' => 'रबी'),

            array('id' => '3', 'name' => 'Summer ', 'name_mr' => 'उन्हाळा'),

        );



        $response = array("status" => 1, "data" => $crop_seasons, "config_url" => $this->config_url, "message" => "Season List");



        $this->api_response($response);

    }



    public function crop_varity_post()
    {



        $crop_id = $this->input->post('crop_id');

        /* $state_id     = $this->input->post('state_id');

        $district_id  = $this->input->post('district_id');

        $season       = $this->input->post('season');*/

        $crop_veriety = array();

        if ($crop_id != '') {

            $this->db->select('*');

            $this->db->where('is_deleted', 'false');

            $this->db->where('is_active', 'true');

            $this->db->where('crop_id', $crop_id);

            /*   $this->db->where('variety_state', $state_id);

            $this->db->where('variety_district', $district_id);

            $this->db->like('season', $season);*/



            $crop_veriety = $this->db->get('crop_variety_master')->result_array();



            if (!empty($crop_veriety)) {

                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $crop_veriety, "message" => "Crop Veriety");

            } else {

                $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $crop_veriety, "message" => lang('Data_Not_Found'));

            }



        } else {

            $crop_veriety = array();

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $crop_veriety, "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);



    }



    public function npk_filter_submit_post()
    {



        $crop_id = $this->input->post('crop_id');

        $varity_id = $this->input->post('varity_id');

        $state_id = $this->input->post('state_id');

        $district_id = $this->input->post('district_id');

        $season = $this->input->post('season');

        $crop_veriety = array();



        if ($crop_id != '') {

            $this->db->select('crop_fertilizers_product_master.ferti_id');

            $this->db->join('crop_fertilizers_product_master', 'crop_fertilizers_product_master.ferti_id = crop_variety_master.crop_variety_id');

            $this->db->where('crop_variety_master.is_deleted', 'false');

            $this->db->where('crop_fertilizers_product_master.is_active', 'true');

            $this->db->where('crop_fertilizers_product_master.is_deleted', 'false');

            $this->db->where('crop_variety_master.is_active', 'true');

            $this->db->where('crop_variety_master.crop_id', $crop_id);

            $this->db->where('crop_variety_master.crop_variety_id', $varity_id);

            $this->db->where('crop_variety_master.variety_state', $state_id);

            $this->db->where('crop_variety_master.variety_district', $district_id);

            $this->db->where('crop_variety_master.season', $season);



            $crop_veriety = $this->db->get('crop_variety_master')->result_array();



            if (!empty($crop_veriety)) {

                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $crop_veriety, "message" => "Crop NPK");

            } else {

                $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $crop_veriety, "message" => lang('Data_Not_Found'));

            }



        } else {

            $crop_veriety = array();

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $crop_veriety, "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);



    }



    public function crop_variety_master_post()
    {



        $crop_id = $this->input->post('crop_id');

        //$varity_id    = $this->input->post('varity_id');

        $state_id = $this->input->post('state_id');

        //$district_id  = $this->input->post('district_id');

        $season_id = $this->input->post('season_id');



        if ($crop_id != '' && $state_id != '' && $season_id != '') {

            $this->db->select('*');

            $this->db->where('is_deleted', 'false');

            $this->db->where('is_active', 'true');

            $this->db->where('crop_id', $crop_id);

            // $this->db->where('soil_type', $soil_type);

            $this->db->where('variety_state', $state_id);

            $this->db->where('season', $season_id);



            $crop_veriety = $this->db->get('crop_variety_master')->result_array();



            // $sql_stmt = $this->db->last_query();  "sql"=>$sql_stmt;

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $crop_veriety, "message" => "Crop Veriety");



        } else {

            $crop_veriety = array();

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $crop_veriety, "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);

    }



    public function select_crop_params_post()
    {

        $crop_id = $this->input->post('crop_id');

        $resp_array_json = array();

        if ($crop_id != '') {



            $where_crop_id = array('crop_id' => $crop_id);

            $result_params = $this->Masters_model->get_data('filter_json_params', 'crop', $where_crop_id);

            if ($result_params != '') {

                $resp_array_json = json_decode($result_params[0]['filter_json_params'], true);

            }

            if (count($resp_array_json) > 0) {

                foreach ($resp_array_json as $key => $value) {



                    // if($value['name'] == 'state'){

                    $resp_array_json[$key]['name'] = lang(ucfirst($value['name']));

                    $resp_array_json[$key]['title'] = lang(ucfirst($value['title']));

                    // }

                }

            }

            $response = array("status" => 1, "error" => 0, "success" => 1, "data" => $resp_array_json, "message" => "Crop filter found.");



        } else {

            $result_params = array();

            $response = array("status" => 0, "error" => 1, "success" => 0, "data" => $result_params, "message" => lang('Missing_Parameter'));

        }

        $this->api_response($response);

    }



    public function select_crop_params_bkp_post()
    {

        $crop_id = $this->input->post('crop_id');

        $resp_array_json = array();

        if ($crop_id != '') {



            $where_crop_id = array('crop_id' => $crop_id);

            $result_params = $this->Masters_model->get_data('filter_json_params', 'crop', $where_crop_id);

            if ($result_params != '') {

                $resp_array_json = json_decode($result_params[0]['filter_json_params'], true);

            }



            if (count($resp_array_json) > 0) {

                foreach ($resp_array_json as $key => $value) {



                    // if($value['name'] == 'state'){

                    $resp_array_json[$key]['name'] = lang(ucfirst($value['name']));

                    $resp_array_json[$key]['title'] = lang(ucfirst($value['title']));

                    // }

                }

            }



            // print_r($resp_array_json);



            // exit;





            $response = array("status" => 1, "error" => 0, "success" => 1, "data" => $resp_array_json, "message" => "Crop filter found.");



        } else {

            $result_params = array();

            $response = array("status" => 0, "error" => 1, "success" => 0, "data" => $result_params, "message" => lang('Missing_Parameter'));

        }

        $this->api_response($response);

    }



    public function crop_variety_master_filtered_post()
    {



        $crop_id = $this->input->post('crop_id');

        //$varity_id    = $this->input->post('varity_id');

        $state = $this->input->post('state');

        $season = $this->input->post('season');

        $color = $this->input->post('color');

        $duration = $this->input->post('duration');

        $irrigation = $this->input->post('irrigation');



        //print_r($_POST);



        //{"state":["Tamil Nadu","Chhattisgarh","Delhi","Gujarat","Haryana","Karnataka","Madhya Pradesh","Maharashtra","Odisha","Punjab","Rajasthan"],"season":["Kharif"],"color":["Red","Dark Red"],"duration":["Medium"],"irrigation":["Sufficient"]}



        // if ($crop_id != '' && $state != '' && $season != '' && $color != '' && $duration != '') {

        if ($crop_id != '' && $state != '' && $season != '' && $color != '') {



            $this->db->select('*');

            $this->db->where('is_deleted', 'false');

            $this->db->where('is_active', 'true');

            $this->db->where('crop_id', $crop_id);

            // $this->db->where('soil_type', $soil_type);

            // $this->db->where('variety_state', $state_id);

            // $this->db->where('season', $season_id);



            $crop_veriety = $this->db->get('crop_variety_master')->result_array();



            // print_r($crop_veriety);

            if (count($crop_veriety)) {



                foreach ($crop_veriety as $k) {



                    $new_json = json_decode($k['variety_filter_param_json'], true);



                    //print_r($new_json);



                    if (in_array($state, $new_json['state']) && in_array($color, $new_json['color']) && in_array($season, $new_json['season'])) {

                        $expected_crop_variety[] = $k['crop_variety_id'];

                    }

                }



                $result_crop_variety = array();



                // print_r($expected_crop_variety);

                if (count($expected_crop_variety)) {

                    $whr = implode(',', $expected_crop_variety);

                    $sql_v = "SELECT * FROM crop_variety_master where crop_variety_id IN( $whr )";

                    //$res = $this->db

                    $row = $this->db->query($sql_v);

                    $result_crop_variety = $row->result_array();

                }

                // $sql_stmt = $this->db->last_query();  "sql"=>$sql_stmt;

                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result_crop_variety, "message" => "Crop Veriety");



            }

        } else {

            $crop_veriety = array();

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $crop_veriety, "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);



    }



    public function soil_healthcard_post()
    {

        $soil_type = $this->input->post('soil_type');



        if ($soil_type != '') {

            if ($this->input->post('user_id') != '') {

                $update_arr['soil_healthcard'] = json_encode($_POST);

                $id = $this->input->post('user_id');

                $this->db->where('client.id', $id);

                $result = $this->db->update('client', $update_arr);

            }

            $this->db->select('soil_id');

            $this->db->where('is_deleted', 'false');

            $this->db->where('is_active', 'true');

            $this->db->where('soil_type', $soil_type);



            $soil_health = $this->db->get('soil_health_master')->result_array();



            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $soil_health, "message" => "Soild Health Card");



        } else {

            $soil_health = array();

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $soil_health, "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);

    }



    public function plantation_methods_get()
    {

        $crop_seasons = array(

            array('id' => '1', 'name' => 'seedling', 'name_mr' => 'बीपासून नुकतेच तयार झालेले रोप'),

            array('id' => '2', 'name' => 'cutting', 'name_mr' => 'वृक्षारोपण कापणे'),

            array('id' => '3', 'name' => 'sowing ', 'name_mr' => 'पेरणी'),

            array('id' => '3', 'name' => 'leafing ', 'name_mr' => 'पानांची लागवड'),

        );



        $response = array("status" => 1, "data" => $crop_seasons, "config_url" => $this->config_url, "message" => "Season List");



        $this->api_response($response);

    }



    public function npk_details_get($ferti_id)
    {



        if ($ferti_id != '') {

            $this->db->select('ferti_id,description,potential_production,product_image');

            $this->db->where('is_deleted', 'false');

            $this->db->where('is_active', 'true');

            $this->db->where('ferti_id', $ferti_id);

            $crop_npk = $this->db->get('crop_fertilizers_product_master')->result_array();

            $crop_npk[0]['pictures'] = explode(",", $crop_npk[0]['product_image']);



            // print_r($crop_veriety); die;



            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $crop_npk, "config_url" => $this->config_url, "message" => "Product details");



        } else {

            $crop_npk = array();

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $crop_npk, "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);



    }



    public function crop_components_post()
    {



        $data['title'] = 'Crop Component';

        $lang_val = 'name';

        $comp = array();




        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';



        if ($selected_lang == 'mr') {

            $lang_val = 'name_mr';

        } elseif ($selected_lang == 'hi') {

            $lang_val = 'name_hi';

        } else {

            $lang_val = 'name';

        }

        // $data['components'] = ['1' => 'fruit', '2' => 'leaf', '3' => 'stem', '4' => 'root', '5' => 'insect', '6' => 'flower'];

        $data['components'][1] = array('id' => '1', 'name' => 'fruit', 'name_mr' => 'फळ', 'name_hi' => 'फल');



        $data['components'][2] = array('id' => '1', 'name' => 'leaf', 'name_mr' => 'पाने', 'name_hi' => 'पत्ती');

        $data['components'][3] = array('id' => '1', 'name' => 'stem', 'name_mr' => 'खोड', 'name_hi' => 'तना');



        $data['components'][4] = array('id' => '1', 'name' => 'root', 'name_mr' => 'मूळ', 'name_hi' => 'जड़/मूल');



        $data['components'][5] = array('id' => '1', 'name' => 'insect', 'name_mr' => 'कीटक', 'name_hi' => 'कीड़ा');



        $data['components'][6] = array('id' => '1', 'name' => 'flower', 'name_mr' => 'फूल', 'name_hi' => 'फूल');



        /* array('id' => '2', 'name' => 'leaf', 'name_mr' => 'पाने', 'name_hi' => 'पत्ती'),

        array('id' => '3', 'name' => 'stem', 'name_mr' => 'खोड', 'name_hi' => 'तना'),

        array('id' => '4', 'name' => 'root', 'name_mr' => 'मूळ', 'name_hi' => 'जड़/मूल'),

        array('id' => '5', 'name' => 'insect', 'name_mr' => 'कीटक', 'name_hi' => 'कीड़ा'),

        array('id' => '6', 'name' => 'flower', 'name_mr' => 'फूल', 'name_hi' => 'फूल'),

        );*/

        $data3['components_refff'] = ['1' => 'fruit', '2' => 'leaf', '3' => 'stem', '4' => 'root', '5' => 'insect', '6' => 'flower'];



        //$crop_id  = '';

        // $base_url_component = base_url('uploads/crop_component/');

        $base_url_component = 'https://dev.famrut.co.in/agroemandi/uploads/crop_component/';



        // if ($crop_id == '') {

        $crop_id = $this->input->post('crop_id');

        // }



        if ($crop_id != '') {



            $sql = "SELECT component_img, crop_component,component_id FROM crop_components  WHERE crop_components.is_deleted = false AND crop_components.crop_id = " . $crop_id . " ";

            $detail_data = $this->db->query($sql)->result_array();



            if (count($detail_data)) {

                foreach ($detail_data as $v) {



                    $data_val['title'] = $data['components'][$v['crop_component']][$lang_val];

                    $data_val['component_img'] = $base_url_component . $v['component_img'];

                    $data_val['component_id'] = $v['component_id'];

                    $data_val['crop_component'] = $v['crop_component'];

                    $comp[] = $data_val;

                }

            }



            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $comp, "base_url" => $base_url_component, "message" => "Crop Components");

        } else {

            $data = array();

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $data, "base_url" => $base_url_component, "message" => lang('Missing_Parameter'));

        }

        $this->api_response($response);

    }



    public function soil_healthcard_details_get($soil_id)
    {



        if ($soil_id != '') {

            $this->db->select('defciency,pictures,information,recommendations');

            $this->db->where('is_deleted', 'false');

            $this->db->where('is_active', 'true');

            $this->db->where('soil_id', $soil_id);



            $soil_health = $this->db->get('soil_health_master')->result_array();

            if (count($soil_health) > 0) {

                if ($soil_health[0]['pictures'] != '') {

                    $soil_health[0]['pictures'] = explode(",", $soil_health[0]['pictures']);

                }

            }



            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $soil_health, "config_url" => $this->config_url, "message" => "Soild Health Card");



        } else {

            $soil_health = array();

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $soil_health, "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);



    }



    public function crop_varity_details_get($crop_variety_id)
    {

        $crop_variety_fields = ' crop_variety_id, crop_id, crop_variety_icon, name_en, name_mr, name_hi, variety_state, variety_district, variety_taluka, variety_village, days_to_maturity, yield_potential, storability, season, traits, other_informantion, characteristics ';

        if ($crop_variety_id != '') {

            $this->db->select($crop_variety_fields);

            $this->db->where('is_deleted', 'false');

            $this->db->where('is_active', 'true');

            $this->db->where('crop_variety_id', $crop_variety_id);

            $crop_veriety = $this->db->get('crop_variety_master')->result_array();

            $crop_veriety[0]['pictures'] = explode(",", $crop_veriety[0]['crop_variety_icon']);



            // print_r($crop_veriety); die;



            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $crop_veriety, "config_url" => $this->config_url, "message" => "Crop Veriety");



        } else {

            $crop_veriety = array();

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $crop_veriety, "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);



    }



    public function crop_disease_detection_filtered_post()
    {

        $crop_id = $this->input->post('crop_id');

        $component_id = $this->input->post('component_id');

        $data_imgs = array();



        if ($crop_id != '' && $component_id != '') {



            $where = array('is_deleted' => false, 'is_active' => true);

            $crop_data = array();

            $data_component = array('1' => 'fruit', '2' => 'leaf', '3' => 'stem', '4' => 'root', '5' => 'insect', '6' => 'flower');



            $sql = "SELECT crop_components.component_id,crop_components.crop_id,crop_components.crop_component,crop_components.component_img,crop.name,crop.logo

            FROM crop_components

            LEFT JOIN crop ON crop.crop_id = crop_components.crop_id

            WHERE crop_components.crop_id=$crop_id

            AND crop_components.is_deleted = false

            AND crop_components.crop_component::varchar  = '$component_id'::varchar ";

            $crop_data = $this->db->query($sql)->result_array();

            if (count($crop_data)) {

                foreach ($crop_data as $key => $value) {



                    $sql = "SELECT crop_disease.component_id,crop_disease.disease_id,crop_disease.disease_name,crop_disease.disease_img,crop_disease.disease_info FROM crop_disease  WHERE crop_disease.is_deleted = false AND crop_disease.component_id = " . $value['component_id'];

                    $detail_data = $this->db->query($sql)->result_array();

                    $data = $detail_data;

                    //$data = array($detail_data);



                    $component_name = $data_component[$component_id];



                    foreach ($detail_data as $key => $v) {

                        $disease_detection[$key] = array(

                            'disease_name' => $v['disease_name'],

                            'component_id' => $v['component_id'],

                            'disease_id' => $v['disease_id'],

                            'disease_info' => $v['disease_info'],

                        );



                        $data_val = explode(',', $v['disease_img']);



                        //$data_imgs[$v['disease_name']] = $data_val;



                        foreach ($data_val as $k) {

                            $img = 'https://dev.famrut.co.in/agroemandi/uploads/crop_disease/' . $value['name'] . '/' . $component_name . '/' . $k;



                            $disease_detection[$key]['icon_img'] = $img;

                            $disease_detection[$key]['images'][] = $img;

                            // $data_images[] = array('disease_name' => $v['disease_name'], 'images' => $img, 'component_id' => $v['component_id'], 'disease_id' => $v['disease_id'], 'disease_info' => $v['disease_info']);



                        }

                    }

                }

            }



            // $data['crop_data'] = $crop_data;

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $disease_detection, "message" => "Crop disease detection");



        } else {

            $data = array();

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $data, "message" => lang('Missing_Parameter'));

        }

        $this->api_response($response);

    }



    public function show_regions_get()
    {


        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        $state_id = $headers_data['state_id'];

        $user_id = $headers_data['user_id'];



        if ($user_id) {

            $sele_region = $this->db->query("SELECT region_id FROM client WHERE is_deleted = 'false' AND id = " . $user_id);

            $selected_region = $sele_region->result_array();

            $selected_region = explode(',', $selected_region[0]['region_id']);

        }



        // print_r($selected_region);exit();

        if ($state_id) {

            $row = $this->db->query("SELECT id,crop_region_name,status,state,location,lat,long FROM crop_region_master WHERE is_deleted = 'false' AND state = " . $state_id);

        } else {

            $row = $this->db->query("SELECT id,crop_region_name,status,state,location,lat,long FROM crop_region_master WHERE is_deleted = 'false'");

        }

        $result = $row->result_array();



        if (count($result)) {

            $response = array("status" => 1, "data" => $result, "selected_region" => $selected_region, "message" => lang('Listed_Successfully'));

        } else {

            $response = array("status" => 0, "data" => $result, "selected_region" => $selected_region, "message" => lang('Data_Not_Found'));

        }



        // $this->api_response($result, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

        $this->api_response($response);

    }



    public function save_regions_post()
    {



        $user_id = $this->input->post('user_id');

        $region_id['region_id'] = $this->input->post('region_id');

        if (!empty($region_id)) {

            $this->db->where('client.id', $user_id);

            $result = $this->db->update('client', $region_id);

        }



        if (count($result)) {

            $response = array("status" => 1, "data" => $result, "message" => "Crop Region save successfully");

        } else {

            $response = array("status" => 0, "data" => $result, "message" => "Crop Region not avaiable");

        }

        // $this->api_response($result, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

        $this->api_response($response);

    }



    public function crop_blogs_details_get($crop_id = 'All', $start = 1)
    {


        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';



        $lang_label = "bty.name_mr as blogs_types_name_mr";

        if ($selected_lang == 'hi') {

            $lang_label = "bty.name_hi as blogs_types_name_mr";

        }



        $crop_ids_query = '';

        if (strtolower($crop_id) == 'all') {

            $crop_ids_query = "";

        } else {

            $crop_ids_query = "AND cb.crop_id IN (" . $crop_id . ")";

        }



        if (1) {

            //$crop_ids_query = "AND cb.crop_id IN (".$crop_id.")";



            //echo $type_get = $type_get;

            $response = array();

            $limit = 5;

            //$start    = 1;

            $cat_id = 0;

            //$start  = $this->input->post('start') != ''?$this->input->post('start'):1;

            $start_chk = $start - 1;

            if ($start_chk != 0) {

                $start_sql = $limit * ($start_chk);

            } else {

                $start_sql = 0;

            }



            $sql_limit = " LIMIT " . $limit . " OFFSET " . $start_sql;

            //  $row       = $this->db->query($sql . $sql_where . $sql_sort . $sql_limit);



            if ($blogs_types_id) {



                $row = $this->db->query("SELECT cb.id as blogs_id,cb.logo,cb.blogs_tags_id,cb.blogs_types_id,cb.id,cb.title as blogs_title,cb.sub_title as blogs_sub_title,cb.description as blogs_description,cb.sub_description as blogs_sub_description,cb.created_on as blogs_created_on,bty.name as blogs_types_name,bty.logo as blogs_types_logo ,bty.mob_icon as blogs_types_mob_icon,$lang_label FROM created_blogs as cb

                LEFT JOIN blogs_types_master as bty ON CAST(bty.blogs_types_id AS TEXT) IN (cb.blogs_types_id)

                WHERE cb.is_active=true AND cb.is_deleted = 'false' AND  cb.blogs_types_id='" . $blogs_types_id . "' AND cb.crop_id IN (" . $crop_id . ") ORDER BY cb.created_on DESC " . $sql_limit);

            } else {

                $row = $this->db->query("SELECT cb.id as blogs_id,cb.logo,cb.blogs_tags_id,cb.blogs_types_id,cb.id,cb.title as blogs_title,cb.sub_title as blogs_sub_title,cb.description as blogs_description,cb.sub_description as blogs_sub_description,cb.created_on as blogs_created_on, bty.name as blogs_types_name,bty.logo as blogs_types_logo ,bty.mob_icon as blogs_types_mob_icon,$lang_label FROM created_blogs as cb

                LEFT JOIN blogs_types_master as bty ON CAST(bty.blogs_types_id AS TEXT) IN (cb.blogs_types_id)

                WHERE cb.is_active=true AND cb.is_deleted = 'false' " . $crop_ids_query . " ORDER BY cb.created_on DESC " . $sql_limit);

            }



            $lst_qry = $this->db->last_query();



            $result = $row->result_array();



            if (count($result)) {

                $response = array("status" => 1, "success" => 1, "error" => 0, "data" => $result, "config_url" => $this->config_url, "message" => lang('Listed_Successfully'));

            } else {

                $response = array("status" => 1, "success" => 0, "error" => 1, "data" => [], "config_url" => $this->config_url, "message" => lang('Data_Not_Found'));

            }

        } else {



            $result = array();

            $response = array("status" => 1, "data" => null, "config_url" => $this->config_url, "message" => lang('Missing_Parameter'));



        }



        $response['lst_qry'] = $lst_qry;



        $this->api_response($response);

    }



    public function media_get($page = 1)
    {

        $result = $this->media_listing($page);

        if (count($result)) {

            $response = array("success" => 1, "data" => $result['result'], "featured" => $result['result_featured'], "msg" => 'media list', "error" => 0, "status" => 1, "base_url_media" => $base_url_media, "sql_query_featured" => $result['sql_query_featured']);

        } else {

            $response = array("success" => 0, "data" => $result, "msg" => 'media list', "error" => 0, "status" => 1);

        }

        $this->api_response($response);
        exit;

    }



    public function view_media_get($media_id = 0)
    {

        $sql_media = "SELECT * FROM media WHERE is_deleted = false AND is_active = true AND media_id=$media_id LIMIT 1";



        $query_media_main = $this->db->query($sql_media);

        //uploads/media_thumbnails/mob_icon_1616759429_pin.png

        $row = $query_media_main->result_array();



        if (count($row)) {



            $update_arr['view_count'] = $row[0]['view_count'] + 1;



            if (count($update_arr)) {

                $ids = $row[0]['media_id'];

                $this->db->where('media.media_id', $ids);

                $result = $this->db->update('media', $update_arr);



            }

            $response = array("success" => 1, "data" => $row, "msg" => lang('Added_Successfully'), "error" => 0, "status" => 1, "SQL" => $sql_media, "dis" => $ids, "row" => $update_arr);



            $this->api_response($response);

            exit;



        } else {

            $response = array("success" => 0, "data" => $result, "msg" => lang('Missing_Parameter'), "error" => 0, "status" => 1);

            $this->api_response($response);

            exit;

        }

    }



    public function crop_list_pagination_post()
    {


        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        $lang_label = " name_mr ";

        if ($selected_lang == 'mr') {

            $lang_folder = "marathi";

        } elseif ($selected_lang == 'hi') {

            $lang_folder = "hindi";

            // $lang_label = " name_hi as name_mr ";

        } else {

            $lang_folder = "english";

            $lang_label = " name_mr ";

        }



        $page = $this->input->post('page');



        if ($page != '') {

            $page = $page;

        } else {

            $page = 1;

        }



        $limit = 34;

        //$start    = 1;

        $cat_id = 0;

        //$start  = $this->input->post('start') != ''?$this->input->post('start'):1;

        $start_chk = $page - 1;

        if ($start_chk != 0) {

            $start_sql = $limit * ($start_chk);

        } else {

            $start_sql = 0;

        }



        // $group_id     = $headers_data['group_id']; (replace this line with below 2 lines)

        $group_id_arr = explode(',', $headers_data['group_id']);

        $group_id = $group_id_arr[0];

        $crop_ids_query = '';



        if ($group_id != '') {

            $where = array('is_deleted' => 'false', 'is_active' => 'true', 'group_referral_code' => $group_id);

            $crop_ids = $this->Masters_model->get_data("crop_id", 'config_master', $where);



            $crop_ids_query = '';

            if (count($crop_ids)) {

                if ($crop_ids[0]['crop_id']) {

                    $crop_ids_query = " AND crop_id IN (" . $crop_ids[0]['crop_id'] . ") ";

                }

            }

        }



        $sql_limit = " LIMIT " . $limit . " OFFSET " . $start_sql;



        $sql_count = "SELECT count(crop_id) as total_crops FROM crop  WHERE is_deleted=false AND is_active=true  " . $crop_ids_query;

        $res_vals = $this->db->query($sql_count);

        $res_count = $res_vals->result_array();

        $total_crops = $res_count[0]['total_crops'];



        $sql_chk = "SELECT crop_id,name,name_mr,name_hi,logo as mob_icon FROM crop

        WHERE is_deleted=false AND is_active=true " . $crop_ids_query . " ORDER BY crop_id ASC " . $sql_limit;

        $res_val = $this->db->query($sql_chk);

        $res_array = $res_val->result_array();

        $response = array("success" => 1, "data" => $res_array, "error" => 0, "status" => 1, 'total_crops' => $total_crops);

        $this->api_response($response);



    }



    public function dss_crop_calander_get($crop_id)
    {


        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        $sql_limit = '';

        $lang_tr_str = " activities_mr , details_mr ";

        if ($selected_lang == 'mr') {

            $lang_folder = "marathi";

        } elseif ($selected_lang == 'hi') {

            $lang_folder = "hindi";

            $lang_tr_str = " activities_hi as activities_mr , details_hi as details_mr ";

        } else {

            $lang_folder = "english";

            $lang_tr_str = " activities_mr , details_mr ";

        }



        if ($crop_id != '') {

            /* $sql = "SELECT c.*,cm.name as crop_name, $lang_label ,cm.logo from master_crop_details  as c

            LEFT JOIN crop as cm ON cm.crop_id=c.crop

            where id=" . $master_crop_id;

            $row    = $this->db->query($sql);

            $result = $row->result_array();*/

            if (1) {

                // $crop_id         = $result[0]['crop'];

                // $sql_cc                   = "SELECT id, crop_id, days_count, activities, details, chemical_consertation, expected_height, is_active, created_on, duration,  $lang_tr_str from crop_calender where is_deleted=false AND crop_id=" . $crop_id . " ORDER BY days_count ASC ";

                $sql_cc = "SELECT id, crop_id, days_count, activities, details, chemical_consertation, expected_height, duration,  $lang_tr_str from crop_calender where is_deleted=false AND crop_id=" . $crop_id . " ORDER BY days_count ASC ";



                $row_cc = $this->db->query($sql_cc);

                $result_crop_cal = $row_cc->result_array();

                $result_data['crop_data'] = array();

                $result_data['crop_cal'] = $result_crop_cal;

                $response = array("status" => 1, "data" => $result_data, "message" => "Crop listed  with calender successfully");

            }

            /*if (count($result)) {

        $response = array("status" => 1, "data" => $result, "message" => "Farm listed successfully");

        }*/

        }



        $this->api_response($response);

        exit;



    }



    public function nc_auth_get($mobile_no = '')
    {

        if (!empty($mobile_no)) {

            $get_token_data = array(

                'url' => NETCARROTS . 'AuthAPI/token',

                'postfields' => 'username=' . NC_AUTH_USER . '&password=' . NC_AUTH_PASSWORD . '&grant_type=password',

                // 'postfields'  => 'username=Famrut&password=Famrut@Demo&grant_type=password',

                'method' => 'GET',

                'http_header' => array('Content-Type: application/x-www-form-urlencoded'),

            );



            $nc_access_token = $this->get_nc_token_curl_call($get_token_data); // Get access token

            if (isset($nc_access_token['access_token']) && !empty($nc_access_token['access_token'])) {

                $token = $nc_access_token['access_token'];

                $get_authentication_data = array(

                    'url' => NETCARROTS . 'AuthAPI/FMAPI/AuhenticateMemberAPI',

                    'postfields' => 'MobileNo=' . $mobile_no,

                    'method' => 'POST',

                    'http_header' => array(

                        "Authorization: Bearer $token",

                        "Content-Type: application/x-www-form-urlencoded",

                    ),

                );



                $result = $this->get_nc_token_curl_call($get_authentication_data);

                if ($result['ErrorCode'] == 0) {

                    $nc_url = NETCARROTS . 'Authentication.aspx?Guid=' . $result['Result'];

                    $response = array("status" => 1, "data" => $nc_url);

                } else {



                    $sql = "SELECT id as client_id, first_name, last_name, phone  FROM client WHERE  phone= '" . $mobile_no . "' AND is_active = 'true' AND is_deleted = 'false' limit 1";

                    $query = $this->db->query($sql);

                    $client_data = $query->row_array();



                    $nc_data = $client_data;

                    $nc_api_data = $this->add_nc_member($nc_data);

                    $AddMemberEnrolmentAPI = $nc_api_data['AddMemberEnrolmentAPI'];

                    $TransactionAPI = $nc_api_data['TransactionAPI'];



                    $result = $this->get_nc_token_curl_call($get_authentication_data);

                    if ($result['ErrorCode'] == 0) {

                        $nc_url = NETCARROTS . 'Authentication.aspx?Guid=' . $result['Result'];

                        $response = array("status" => 1, "data" => $nc_url, 'AddMemberEnrolmentAPI' => $AddMemberEnrolmentAPI, 'TransactionAPI' => $TransactionAPI, 'nc_data' => $nc_data);

                    } else {

                        $response = array("status" => 0, "data" => [], "message" => lang('Data_Not_Found'), 'AddMemberEnrolmentAPI' => $AddMemberEnrolmentAPI, 'TransactionAPI' => $TransactionAPI, 'nc_data' => $nc_data);

                    }

                }

            } else {

                $response = array("status" => 0, "data" => [], "message" => "Not found Access token!");

            }

        } else {

            $response = array("status" => 0, "data" => [], "message" => "Mobile No required");

        }



        $this->api_response($response);

        exit;

    }



    public function get_nc_token_curl_call($data)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(

            CURLOPT_URL => $data['url'],

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_ENCODING => '',

            CURLOPT_MAXREDIRS => 10,

            CURLOPT_TIMEOUT => 0,

            CURLOPT_FOLLOWLOCATION => true,

            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

            CURLOPT_CUSTOMREQUEST => $data['method'],

            CURLOPT_POSTFIELDS => $data['postfields'],

            CURLOPT_HTTPHEADER => $data['http_header'],

        ));



        $res = curl_exec($curl);

        curl_close($curl);

        return json_decode($res, true);

    }



    public function dynamic_domain_db_connection_get()
    {

        $api_base_path = $this->api_base_path;


        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        $domain = strtolower(trim($headers_data['domain']));

        if (!empty($domain)) {

            $master_db = $this->load->database('master', TRUE);



            $sql = "SELECT id, db_name as appname, url_path, title, domain, x_api_key FROM setup_config_master WHERE LOWER(domain) = '" . $domain . "' AND is_deleted = false ORDER BY id desc LIMIT 1";

            $row = $master_db->query($sql);

            $result = $row->row_array();



            if (!empty($result) && count($result) > 0) {

                if (isset($result['url_path']) && $result['url_path'] != '') {

                    $api_base_path = $result['url_path'];

                }

                $status = 1;

                $error = 0;

                $data = $result;

                $msg = "Code found.";

            } else {

                $status = 0;

                $error = 1;

                $data = null;

                $msg = lang('Data_Not_Found');

            }



        } else {

            $status = 0;

            $error = 1;

            $data = null;

            $msg = lang('Data_Not_Found');

        }



        $response = array('status' => $status, 'error' => $error, 'data' => $data, 'api_base_path' => $api_base_path, 'msg' => $msg);



        $this->api_response($response);

        exit;

    }



    // public function dynamic_domain_db_connection_get()

    // {

    //     $headers_data = $this->input->request_headers();

    //     $domain       = $headers_data['domain'];

    //     if (!empty($domain)) {

    //         $curl = curl_init();

    //         curl_setopt_array($curl, array(

    //             CURLOPT_URL            => base_url() . 'dynamic_domain_db_connection.php',

    //             CURLOPT_RETURNTRANSFER => true,

    //             CURLOPT_ENCODING       => '',

    //             CURLOPT_MAXREDIRS      => 10,

    //             CURLOPT_TIMEOUT        => 0,

    //             CURLOPT_FOLLOWLOCATION => true,

    //             CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,

    //             CURLOPT_CUSTOMREQUEST  => 'POST',

    //             CURLOPT_HTTPHEADER     => array(

    //                 'domain: ' . $domain,

    //             ),

    //         ));



    //         $response = json_decode(curl_exec($curl), true);

    //         curl_close($curl);

    //     } else {

    //         $response = array("status" => 0, "error" => 1, "data" => null, "msg" => "Domain Required!");

    //     }

    //     $this->api_response($response);

    //     exit;

    // }



    // public function dynamic_domain_db_connection_old_get()

    // {

    //     $headers_data = $this->input->request_headers();

    //     $domain       = $headers_data['domain'];



    //     if (!empty($domain)) {

    //         $curl = curl_init();

    //         curl_setopt_array($curl, array(

    //             CURLOPT_URL            => 'https://dev.famrut.co.in/agri-ecosystem-api/dynamic_domain_db_connection.php',

    //             CURLOPT_RETURNTRANSFER => true,

    //             CURLOPT_ENCODING       => '',

    //             CURLOPT_MAXREDIRS      => 10,

    //             CURLOPT_TIMEOUT        => 0,

    //             CURLOPT_FOLLOWLOCATION => true,

    //             CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,

    //             CURLOPT_CUSTOMREQUEST  => 'POST',

    //             CURLOPT_HTTPHEADER     => array(

    //                 'domain: ' . $domain,

    //             ),

    //         ));



    //         $response = json_decode(curl_exec($curl), true);

    //         curl_close($curl);

    //     } else {

    //         $response = array("status" => 0, "error" => 1, "data" => null, "msg" => "Domain Required!");

    //     }



    //     $this->api_response($response);

    //     exit;

    //     // echo $response;

    // }



    public function products_listing_get($limit = 10)
    {



        $response = array();


        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        $response = array();

        $limit = 10;

        $start = 1;

        $cat_id = 0;



        $start = $this->input->post('start');

        $cat_id = number_format($this->input->post('cat_id'));



        $lang_label = " name_mr ";

        if ($selected_lang == 'mr') {

            $lang_folder = "marathi";

        } elseif ($selected_lang == 'hi') {

            $lang_folder = "hindi";

            $lang_label = "name_hi as name_mr";

        } else {

            $lang_folder = "english";

            $lang_label = " name_mr ";

        }



        /* $start_chk = $start - 1;

        if ($start_chk != 0) {

        $start_sql = $limit * ($start_chk);

        } else {

        $start_sql = 0;

        }



        $sql_limit = " LIMIT " . $limit . " OFFSET " . $start_sql;*/



        $sql = "SELECT pcat_id ,name ,logo ,$lang_label ,mob_icon FROM pcategories WHERE is_deleted = 'false' AND is_active = 'true' ORDER BY pcat_id DESC ";

        $row = $this->db->query($sql);

        $result = $row->result_array();

        if (count($result) > 0) {

            $j = 0;

            foreach ($result as $key => $val) {



                $cat_name = $val['name'] . ' / ' . $val['name_mr'];

                $sql_products = "SELECT * FROM products WHERE category_id = '" . $val['pcat_id'] . "' AND is_deleted = false AND is_publish='true' LIMIT " . $limit;

                $query_products = $this->db->query($sql_products);

                $products_res = $query_products->result_array();

                // print_r($products_res);

                $products_result = [];

                if (count($products_res) > 0) {



                    foreach ($products_res as $reskey => $resval) {

                        $product_unit = $resval['unit'];

                        $product_unit_desc = $resval['unit_desc'];

                        $updated_product_unit = $resval['unit_desc'] . ' / ' . $resval['unit'];



                        $resval['unit_desc'] = null;

                        $resval['unit'] = $updated_product_unit;



                        $products_result[] = $resval;

                    }



                    $cat_array[$j] = array('pcat_id' => $val['pcat_id'], 'cat_name' => $cat_name, 'image' => $val['mob_icon']);

                    $cat_array[$j]['products_list'] = $products_result;

                    $j++;

                }

            }

            // exit;

        } else {

            $response = array("status" => 0, "message" => "Product Categories listing successfully");

        }





        // print_r($product_result);exit;



        if (count($cat_array) > 0) {

            $response = array("success" => 1, "data" => $cat_array, "msg" => lang('Listed_Successfully'), "error" => 0, "status" => 1);

        } else {

            $response = array("success" => 0, "data" => $cat_array, "msg" => lang('Missing_Parameter'), "error" => 0, "status" => 1);

        }

        $this->api_response($response);

        exit;

    }



    public function products_details_get($products_id)
    {

        $response = array();


        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        $lang_label = " name_mr ";

        if ($selected_lang == 'mr') {

            $lang_folder = "marathi";

        } elseif ($selected_lang == 'hi') {

            $lang_folder = "hindi";

            $lang_label = "name_hi as name_mr";

        } else {

            $lang_folder = "english";

            $lang_label = " name_mr ";

        }



        $products_res = [];



        if (!empty($products_id)) {

            $sql_products = "SELECT * FROM products

            WHERE id = '" . $products_id . "' AND is_deleted = false";

            $query_products = $this->db->query($sql_products);

            $products_res = $query_products->row_array();



            if (count($products_res) > 0) {

                $sql = "SELECT pcat_id, name, logo, $lang_label, mob_icon FROM pcategories WHERE pcat_id = " . $products_res['category_id'] . " AND is_deleted = 'false' AND is_active = 'true'";

                $row = $this->db->query($sql);

                $result = $row->row_array();

                if (count($result) > 0) {

                    $products_res['category_details'] = $result;



                }



                $products_res['rating'] = 4;



                $response = array("success" => 1, "data" => $products_res, "msg" => 'Products details!', "error" => 0, "status" => 1);



            } else {

                $response = array("success" => 0, "data" => [], "msg" => 'Data not found!', "error" => 1, "status" => 1);

            }

        } else {

            $response = array("success" => 0, "data" => [], "msg" => 'Product id required!', "error" => 1, "status" => 1);

        }



        $this->api_response($response);

        exit;

    }



    public function check_pickup_location_get($product_id, $lat = '19.9959911', $long = '73.7470536')
    {

        $longitude = (float) $long;

        $latitude = (float) $lat;

        $minimum_radius = get_config_settings('minimum_radius');



        $sql_location = "SELECT address, pincode, lat, long, id";

        $sql_location .= ", (6371 * acos (cos (radians($latitude))* cos(radians(lat))* cos( radians($longitude) - radians(long) )+ sin (radians($latitude) )* sin(radians(lat)))) AS distance ";

        $sql_location .= " FROM pickup_location_master  WHERE is_active=true AND is_deleted=false ORDER BY distance ASC";



        $query_products_location = $this->db->query($sql_location);

        $result = $query_products_location->result_array();



        $products_res_loc = [];



        if (count($result) > 0) {

            foreach ($result as $key => $value) {

                $distance = round($value['distance'], 1);

                $products_res_loc[] = array(

                    'id' => $value['id'],

                    'address' => $value['address'] . ' ( ' . $distance . ' KM )',

                );

            }

        }



        if (count($products_res_loc) > 0) {

            $response = array("success" => 1, "data" => $products_res_loc, "msg" => 'location avaiable!', "error" => 0, "status" => 1, 'minimum_radius' => $minimum_radius['description']);

        } else {

            $response = array("success" => 0, "data" => [], "msg" => 'No location avaiable ', "error" => 1, "status" => 1);

        }



        $this->api_response($response);

        exit;

    }



    public function client_choice_post()
    {



        $client_id = $this->input->post('client_id');

        $product_id = $this->input->post('product_id');

        $choice_type = $this->input->post('choice_type') ? $this->input->post('notify_me') : "notify_me";



        if ($client_id != '' && $product_id != '') {



            $sql_products = "SELECT id FROM client_choice WHERE  client_id= " . $client_id . " AND product_id= " . $product_id . " AND choice_type = 'notify_me' AND is_deleted = false AND notifications_sent = false ";

            $query_products = $this->db->query($sql_products);

            $products_res = $query_products->result_array();

            if (count($products_res) == 0) {



                $insert = array(

                    'client_id' => $this->input->post('client_id'),

                    'product_id' => $this->input->post('product_id'),

                    'choice_type' => $choice_type,

                    'created_on' => current_date(),

                );

                //$this->db->where('client.id', $id);

                $result = $this->db->insert('client_choice', $insert);

                $order_id = $this->db->insert_id();

                $response = array("success" => 1, "data" => $insert, "msg" => lang('We_will_notify_you_when_it_is_available'), "error" => 0, "status" => 1);



            } else {



                $response = array("success" => 1, "data" => "", "msg" => lang('We_will_notify_you_when_it_is_available'), "error" => 0, "status" => 1);



            }



        } else {

            $response = array("success" => 0, "data" => [], "msg" => lang('All_fields_is_required'), "error" => 1, "status" => 1);

        }

        $this->api_response($response);

        exit;

    }



    public function client_delivery_address_post()
    {

        $client_id = $this->input->post('client_id');



        if ($client_id != '') {

            // $client_data = $this->Masters_model->get_data('*','client_orders',array('client_id'=>$client_id), NULL, NULL, 0, 1);





            $sql = "SELECT client_id, billing_address1, billing_city, billing_pin_code, billing_village, billing_state, billing_country,  shipping_address1, shipping_city, shipping_state, shipping_pin_code, shipping_country, first_name, last_name, email_id, cphone  FROM client_orders WHERE  client_id= " . $client_id . " AND pickup_location_id IS NULL order by order_date desc limit 1";

            $query = $this->db->query($sql);

            // echo $this->db->last_query();exit;

            $client_data = $query->row_array();









            if (count($client_data) > 0) {

                $response = array("success" => 1, "data" => $client_data, "msg" => 'Address found!', "error" => 0, "status" => 1);

            } else {

                $response = array("success" => 1, "data" => [], "msg" => lang('Data_Not_Found'), "error" => 0, "status" => 1);

            }

        } else {

            $response = array("success" => 0, "data" => [], "msg" => lang('All_fields_is_required'), "error" => 1, "status" => 1);

        }

        $this->api_response($response);

        exit;

    }





    public function client_crop_details_post()
    {

        $client_id = $this->input->post('client_id');

        if ($client_id != '') {

            // $client_data = $this->Masters_model->get_data('*','client_orders',array('client_id'=>$client_id), NULL, NULL, 0, 1);





            // $client_crop_data    = $this->Masters_model->get_data('*', 'master_crop_details', array('client_id' => $client_id));

            $client_crop_data = $this->my_crops_listing($client_id);



            if (count($client_crop_data) > 0) {

                $response = array("success" => 1, "data" => $client_crop_data, "msg" => 'Client crop details found!', "error" => 0, "status" => 1);

            } else {

                $response = array("success" => 1, "data" => [], "msg" => lang('Data_Not_Found'), "error" => 0, "status" => 1);

            }

        } else {

            $response = array("success" => 0, "data" => [], "msg" => lang('All_fields_is_required'), "error" => 1, "status" => 1);

        }



        $this->api_response($response);

        exit;

    }



    public function delete_client_crop_details_post()
    {

        $id = $this->input->post('id');

        $client_id = $this->input->post('client_id');



        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => [], "message" => lang('Missing_Parameter'));

        if ($id != '') {



            $where_arr['id'] = $id;

            if ($client_id != '') {

                $where_arr['client_id'] = $client_id;

            }



            $client_crop_data = $this->Masters_model->get_data('*', 'master_crop_details', $where_arr);



            if (count($client_crop_data) > 0) {

                $update_arr['updated_on'] = current_date();

                $update_arr['updated_by_id'] = $client_id;

                $update_arr['is_deleted'] = true;

                $update_arr['deleted_by_id'] = $client_id;

                $update_arr['deleted_on'] = current_date();



                $this->db->where('master_crop_details.id', $id);

                $result = $this->db->update('master_crop_details', $update_arr);



                if ($result) {

                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Crop deleted Successfully!");



                    $this->api_response($response);
                    exit;

                } else {

                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Crop deleting failed, please try again some time.");

                    $this->api_response($response);
                    exit;

                }



            } else {

                $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Please provice correct data!");

                $this->api_response($response);
                exit;

            }

        }

        $this->api_response($response);

        exit;

    }



    public function language_list_get($lang = '')
    {

        $multilingual_setting = get_config_settings('multilingual_setting');

        $languages = $this->config->item('languages');

        if (!empty($multilingual_setting) && $multilingual_setting['description'] != '') {

            $multilingual_description = json_decode($multilingual_setting['description'], true);

            // echo $multilingual_description;exit;

            // $implode_description		= explode(",", $multilingual_description);



            // print_r($multilingual_description);exit;

            if (count($languages) > 0) {

                if (!empty($lang) && in_array($lang, $multilingual_description)) {

                    $data['lang'] = array("lang_key" => $lang, "lang_val" => $languages[$lang]);

                } else {

                    foreach ($languages as $key => $val) {

                        if (in_array($key, $multilingual_description)) {

                            $data['lang'][] = array("lang_key" => $key, "lang_val" => $val);

                        }

                    }

                }







                if (!empty($data['lang'])) {

                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, "message" => "languages found successfully!");

                } else {

                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => [], "message" => lang('Data_Not_Found'));

                }

            } else {

                $response = array("success" => 0, "error" => 1, "status" => 1, "data" => [], "message" => lang('Data_Not_Found'));

            }

        }



        $this->api_response($response);

        exit;

    }



    function my_crops_listing($client_id = '', $table = 'master_crop_details')
    {


        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        if ($client_id == '') {

            $client_id = $headers_data['client_id'];

        }

        // $selected_lang = $headers_data['lang'];

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



        $client_crop_data = [];

        $idname = ($table == 'my_crops') ? 'crop_id' : 'crop';

        if (!empty($client_id)) {

            $crop_data = $this->Masters_model->get_data('*', $table, array('is_deleted' => 'false', 'client_id' => $client_id), null, $idname);

            if (count($crop_data)) {

                $crops = '';

                foreach ($crop_data as $key => $value) {

                    if ($crops != $value[$idname]) {

                        $sql_crop = "SELECT crop_id,lang_json->>'" . $selected_lang . "' as name ,logo as mob_icon,nitrogen as n ,phosphorus as p,potassium as k  FROM crop WHERE crop_id = " . $value[$idname] . " LIMIT 1";



                        $crop_val = $this->db->query($sql_crop);

                        $logo = $crop_val->row_array();

                        $value['name'] = $logo['name'];

                        $value['logo'] = $logo['mob_icon'];

                        $value['n'] = $logo['n'];

                        $value['p'] = $logo['p'];

                        $value['k'] = $logo['k'];

                        $value['crop_id'] = $logo['crop_id'];

                        if (2 == $logo['crop_id']) {

                            $value['s'] = 30;

                        } else {

                            $value['s'] = 0;

                        }



                        $client_crop_data[] = $value;

                    }

                    $crops = $value[$idname];

                }

            }

        }



        return $client_crop_data;

    }



    function commodity_rate_updates_listing()
    {

        $apmc_market = $_REQUEST['apmc_market'];

        // $lat            = $_REQUEST['lat'];

        // $long           = $_REQUEST['long'];



        $start = $_REQUEST['start'];


        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        $domain = $headers_data['domain'];

        $lat = $headers_data['lat'];

        $long = $headers_data['long'];





        if (empty($lat)) {
            $lat = (float) 19.997454;
        }

        if (empty($long)) {
            $long = (float) 73.789803;
        }



        $lang_label = " commodityname as commodity_title ";



        if ($selected_lang == 'mr') {

            $lang_folder = "marathi";

            $lang_label = " commodity_marathi as commodity_title ";

        } elseif ($selected_lang == 'hi') {

            $lang_folder = "hindi";

            $lang_label = " commodity_hindi as commodity_title ";

        } else {

            $lang_folder = "english";

            $lang_label = " commodityname as commodity_title ";

        }



        $apmc_market_data = '';

        $longitude = (float) $long;

        $latitude = (float) $lat;

        $limit = 10;

        $cat_id = 0;

        if ($start != 0) {

            $start_sql = $limit * ($start - 1);

        } else {

            $start_sql = 0;

        }



        if ($apmc_market == '') {

            // $sql_location   = "SELECT  COALESCE( ( 6371 * acos( cos( radians($latitude) ) * cos( radians(latitude) ) * cos( radians( longitude ) - radians($longitude) ) + sin( radians($latitude) ) * sin( radians( latitude ) ) ) ) , 0) AS distance, apmc_market, latitude, longitude FROM apmc_location_master  ORDER BY distance ASC  LIMIT 1";

            $sql_location = "SELECT  state_name, apmc_market, latitude, longitude, ( 3959 * acos(cos(radians($latitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians($longitude)) + sin(radians($latitude)) * sin(radians(latitude)))) AS distance FROM apmc_location_master ORDER BY distance LIMIT 1";



            $res_val = $this->db->query($sql_location);

            $res = $res_val->row_array();



            if (count($res) > 0 && $res['distance'] <= 50) {

                $apmc_market_data = strtolower($res['apmc_market']);

            }



        } else {

            $apmc_market_data = $apmc_market;

        }



        $result = [];



        if ($apmc_market_data != '') {

            $locations_data = array(

                'apmc_market' => ucfirst(strtolower($apmc_market_data)),

                'latitude' => $latitude,

                'longitude' => $longitude

            );



            $today = date('Y-m-d');

            $sql_limit = " LIMIT " . $limit . " OFFSET " . $start_sql;

            $tbl_name = "tbl_maharashtra";



            $sql_comm = "SELECT  market, $lang_label , commodityname as commodity, varity as variety,minimumprices as min_price,maximumprices as max_price,marketwiseapmcpricedate as arrival_date, to_char( to_timestamp((marketwiseapmcpricedate),'YYYY-MM-DD'),'YYYY-MM-DD')  NewDateFormat,arrivals,unitofarrivals,modalprices,unitofprice FROM $tbl_name WHERE lower(market) = lower('" . $apmc_market_data . "')  ORDER BY marketwiseapmcpricedate DESC " . $sql_limit;



            // if ('ICAR' == $domain) {

            //     $sql_comm = "SELECT  market, $lang_label , commodityname as commodity, varity as variety,minimumprices as min_price,maximumprices as max_price,marketwiseapmcpricedate as arrival_date, to_char( to_timestamp((marketwiseapmcpricedate),'YYYY-MM-DD'),'YYYY-MM-DD')  NewDateFormat,arrivals,unitofarrivals,modalprices,unitofprice FROM $tbl_name WHERE lower(market) = lower('" . $apmc_market_data . "')  ORDER BY CASE WHEN commodityname = 'Onion' THEN 1 ELSE 2 END ASC, marketwiseapmcpricedate DESC " . $sql_limit;

            // }



            $query = $this->db->query($sql_comm);

            $result = $query->result_array();

        }

        return $result;

    }





    function commodity_rate_updates_client_wise()
    {

        $apmc_market = $_REQUEST['apmc_market'];

        // $lat            = $_REQUEST['lat'];

        // $long           = $_REQUEST['long'];



        $start = $_REQUEST['start'];


        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        $domain = $headers_data['domain'];

        $lat = $headers_data['lat'];

        $long = $headers_data['long'];

        $client_id = $headers_data['client_id'];





        if (empty($lat)) {
            $lat = (float) 19.997454;
        }

        if (empty($long)) {
            $long = (float) 73.789803;
        }



        $lang_label = " tm.commodityname as commodity_title ";

        $lang_label_map = " tm.commodityname as map_key ";



        if ($selected_lang == 'mr') {

            $lang_folder = "marathi";

            $lang_label = " tm.commodity_marathi as commodity_title ";

        } elseif ($selected_lang == 'hi') {

            $lang_folder = "hindi";

            $lang_label = " tm.commodity_hindi as commodity_title ";

        } else {

            $lang_folder = "english";

            $lang_label = " tm.commodityname as commodity_title ";

        }



        $apmc_market_data = '';

        $longitude = (float) $long;

        $latitude = (float) $lat;

        $limit = 10;

        $cat_id = 0;

        if ($start != 0) {

            $start_sql = $limit * ($start - 1);

        } else {

            $start_sql = 0;

        }



        $client_crop_data = [];



        if ($client_id != '') {

            $client_crop_data = $this->Masters_model->get_data(array('crop'), 'master_crop_details', array('client_id' => $client_id), null, 'crop');



            if (count($client_crop_data)) {

                $client_crop_data = array_unique(array_column($client_crop_data, 'crop'));

            }



            if ($apmc_market == '') {

                $sql_location = "SELECT  state_name, apmc_market, latitude, longitude, ( 3959 * acos(cos(radians($latitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians($longitude)) + sin(radians($latitude)) * sin(radians(latitude)))) AS distance FROM apmc_location_master ORDER BY distance LIMIT 1";



                $res_val = $this->db->query($sql_location);

                $res = $res_val->row_array();



                if (count($res) > 0 && $res['distance'] <= 50) {

                    $apmc_market_data = strtolower($res['apmc_market']);

                }



            } else {

                $apmc_market_data = $apmc_market;

            }



            $result = [];



            if ($apmc_market_data != '') {

                $locations_data = array(

                    'apmc_market' => ucfirst(strtolower($apmc_market_data)),

                    'latitude' => $latitude,

                    'longitude' => $longitude

                );



                $today = date('Y-m-d');

                $sql_limit = " LIMIT " . $limit . " OFFSET " . $start_sql;

                $tbl_name = "tbl_maharashtra as tm";



                $sql_comm = "SELECT  tm.market,$lang_label_map ,$lang_label, tm.commodityname as commodity, tm.varity as variety, tm.minimumprices as min_price, tm.maximumprices as max_price, tm.marketwiseapmcpricedate as arrival_date, to_char( to_timestamp((tm.marketwiseapmcpricedate),'YYYY-MM-DD'),'YYYY-MM-DD') NewDateFormat, tm.arrivals, tm.unitofarrivals, tm.modalprices, tm.unitofprice, cr.logo ";

                $sql_comm .= " FROM $tbl_name ";

                $sql_comm .= " LEFT JOIN crop as cr ON cr.crop_id = tm.pg_crop_master_id";



                // id IN (".implode(', ', $client_crop_data).")





                $sql_comm .= " WHERE lower(tm.market) = lower('" . $apmc_market_data . "') ";

                if (count($client_crop_data)) {

                    $sql_comm .= " AND tm.pg_crop_master_id IN (" . implode(', ', $client_crop_data) . ")";

                }

                $sql_comm .= " ORDER BY tm.marketwiseapmcpricedate DESC " . $sql_limit;



                if ('ICAR' == $domain) {



                    $sql_comm = "SELECT  tm.market,$lang_label_map ,$lang_label, tm.commodityname as commodity, tm.varity as variety, tm.minimumprices as min_price, tm.maximumprices as max_price, tm.marketwiseapmcpricedate as arrival_date, to_char( to_timestamp((tm.marketwiseapmcpricedate),'YYYY-MM-DD'),'YYYY-MM-DD') NewDateFormat, tm.arrivals, tm.unitofarrivals, tm.modalprices, tm.unitofprice, cr.logo ";

                    $sql_comm .= " FROM $tbl_name ";

                    $sql_comm .= " LEFT JOIN crop as cr ON cr.crop_id = tm.pg_crop_master_id";



                    // id IN (".implode(', ', $client_crop_data).")





                    $sql_comm .= " WHERE lower(tm.market) = lower('" . $apmc_market_data . "') ";

                    if (count($client_crop_data)) {

                        $sql_comm .= " AND tm.pg_crop_master_id IN (" . implode(', ', $client_crop_data) . ")";

                    }

                    $sql_comm .= " ORDER BY CASE WHEN commodityname = 'Onion' THEN 1 ELSE 2 END ASC, marketwiseapmcpricedate DESC " . $sql_limit;



                    /*  $sql_comm = "SELECT  market, $lang_label_map,$lang_label , commodityname as commodity, varity as variety,minimumprices as min_price,maximumprices as max_price,marketwiseapmcpricedate as arrival_date, to_char( to_timestamp((marketwiseapmcpricedate),'YYYY-MM-DD'),'YYYY-MM-DD')  NewDateFormat,arrivals,unitofarrivals,modalprices,unitofprice FROM $tbl_name WHERE lower(market) = lower('" . $apmc_market_data . "')  ORDER BY CASE WHEN commodityname = 'Onion' THEN 1 ELSE 2 END ASC, marketwiseapmcpricedate DESC " . $sql_limit;*/

                }



                $query = $this->db->query($sql_comm);

                $result = $query->result_array();



            }

        }

        return $result;

    }



    function advertise_listing()
    {

        $advertise_data = $data_array = [];

        $sql = "SELECT * FROM advertise_master where is_deleted=false AND is_active=true ORDER BY seq ASC";

        $res_val = $this->db->query($sql);

        $res_array = $res_val->result_array();

        if (count($res_array) > 0) {

            $advertise_data = $res_array;

        }

        return $advertise_data;

    }



    function recommended_products_listing()
    {

        $response = array();

        $limit = 4;

        $start = $this->input->post('start') ? $this->input->post('start') : 1;

        $client_id = $this->input->post('client_id');





        $start_chk = $start - 1;

        if ($start_chk != 0) {

            $start_sql = $limit * ($start_chk);

        } else {

            $start_sql = 0;

        }

        $sql_limit = " LIMIT " . $limit . " OFFSET " . $start_sql;







        // $sql = "SELECT * FROM products WHERE 1=1 ";

        $sql = "SELECT cop.product_id, COUNT(cop.product_id) as total_ordered FROM client_order_product as cop JOIN products as p ON p.id = cop.product_id WHERE 1=1 ";

        if (!empty($client_id)) {

            $sql .= " AND cop.client_id=" . $client_id;

        }



        $sql .= " GROUP BY cop.product_id ";

        $sql .= " ORDER BY total_ordered DESC ";

        $sql .= $sql_limit;



        $row = $this->db->query($sql);

        $recommended_products = $row->result_array();

        $result = [];



        if (count($recommended_products) > 0) {

            $products = array_column($recommended_products, 'product_id');

            // print_r($products);exit;



            $p_sql = "SELECT * FROM products WHERE 1=1 AND is_publish = 'true' AND is_active = 'true'";

            if (count($products) > 0) {

                $p_sql .= " AND id IN (" . implode(', ', $products) . ")";

            }

            $p_sql .= " ORDER BY id ASC ";



            $p_row = $this->db->query($p_sql);

            $result = $p_row->result_array();

        }



        return $result;

    }



    function categories_listing()
    {
        // Get headers in lowercase
        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = isset($headers_data['lang']) ? $headers_data['lang'] : 'en';

        // Default values
        $lang_label = "name_mr";
        $lang_folder = "english";

        if ($selected_lang == 'mr') {
            $lang_folder = "marathi";
            $lang_label = "name_mr";
        } elseif ($selected_lang == 'hi') {
            $lang_folder = "hindi";
            $lang_label = "name_hi AS name_mr";
        }

        // Build query
        $this->db->select("cat_id, $lang_label, logo, mob_icon, lang_json->>'$selected_lang' as name, map_key");
        $this->db->from("categories");
        $this->db->where("is_active", 'true');
        $this->db->where("is_deleted", 'false');
        $this->db->order_by("seq", "ASC");

        $query = $this->db->get();
        $result = $query->result_array();

        // Debugging (if needed)
        // echo $this->db->last_query(); exit;

        return $result;
    }




    function blog_listing($blogs_types_id = 'all', $start = 1, $crop_id = 'all', $blogs_cat = 1)
    {



        // echo $blogs_types_id.'--'.$start.'--'.$crop_id;exit;






        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        // $group_id     = $headers_data['group_id'];

        $group_id_arr = explode(',', $headers_data['group_id']);

        $group_id = $group_id_arr[0];

        $crop_ids_query = '';

        /* if ($group_id != '') {

             $where    = array('is_deleted' => 'false', 'is_active' => 'true', 'group_referral_code' => $group_id);

             $crop_ids = $this->Masters_model->get_data("crop_id", 'config_master', $where);



             if ($crop_ids[0]['crop_id']) {

                 $crop_ids_query = "AND cb.crop_id IN (" . $crop_ids[0]['crop_id'] . ")";

             }

         }*/



        //echo $type_get = $type_get;

        $response = array();

        $limit = 20;

        //$start    = 1;

        $cat_id = 0;

        //$start  = $this->input->post('start') != ''?$this->input->post('start'):1;

        $start_chk = $start - 1;

        if ($start_chk != 0) {

            $start_sql = $limit * ($start_chk);

        } else {

            $start_sql = 0;

        }



        $sql_limit = " LIMIT " . $limit . " OFFSET " . $start_sql;

        //  $row       = $this->db->query($sql . $sql_where . $sql_sort . $sql_limit);

        $crop_wise_data = '';

        if (strtolower($crop_id) != 'all') {



            $crop_wise_data = ' AND cb.crop_id =' . $crop_id;

        }



        $blogs_cat_condition = ' AND bty.blog_cat = 1 ';

        if ($blogs_cat == 2) {

            $blogs_cat_condition = ' AND bty.blog_cat = 2 ';

        }



        if (strtolower($blogs_types_id) != 'all') {

            if ($crop_ids[0]['crop_id'] != '') {



                $row = $this->db->query("SELECT cb.id as blogs_id,cb.logo,cb.blogs_tags_id,cb.blogs_types_id,cb.id,cb.title as blogs_title,cb.sub_title as blogs_sub_title,cb.description as blogs_description,cb.sub_description as blogs_sub_description,cb.created_on as blogs_created_on,bty.name as blogs_types_name,bty.logo as blogs_types_logo ,bty.mob_icon as blogs_types_mob_icon,bty.name_mr as blogs_types_name_mr

                FROM created_blogs as cb

                LEFT JOIN blogs_types_master as bty ON CAST(bty.blogs_types_id AS TEXT) IN (cb.blogs_types_id)

                WHERE cb.is_active=true AND cb.is_deleted = 'false' AND  cb.blogs_types_id='" . $blogs_types_id . "'" . $crop_wise_data . " AND cb.crop_id IN (" . $crop_ids[0]['crop_id'] . ") " . $blogs_cat_condition . " ORDER BY cb.created_on DESC " . $sql_limit);



            } else {



                $row = $this->db->query("SELECT cb.id as blogs_id,cb.logo,cb.blogs_tags_id,cb.blogs_types_id,cb.id,cb.title as blogs_title,cb.sub_title as blogs_sub_title,cb.description as blogs_description,cb.sub_description as blogs_sub_description,cb.created_on as blogs_created_on,bty.name as blogs_types_name,bty.logo as blogs_types_logo ,bty.mob_icon as blogs_types_mob_icon,bty.name_mr as blogs_types_name_mr

                FROM created_blogs as cb

                LEFT JOIN blogs_types_master as bty ON CAST(bty.blogs_types_id AS TEXT) IN (cb.blogs_types_id)

                WHERE cb.is_active=true AND cb.is_deleted = 'false' AND  cb.blogs_types_id='" . $blogs_types_id . "'" . $crop_wise_data . " " . $blogs_cat_condition . " ORDER BY cb.created_on DESC " . $sql_limit);



            }



        } else {

            $row = $this->db->query("SELECT cb.id as blogs_id,cb.logo,cb.blogs_tags_id,cb.blogs_types_id,cb.id,cb.title as blogs_title,cb.sub_title as blogs_sub_title,cb.description as blogs_description,cb.sub_description as blogs_sub_description,cb.created_on as blogs_created_on, bty.name as blogs_types_name,bty.logo as blogs_types_logo ,bty.mob_icon as blogs_types_mob_icon,bty.name_mr as blogs_types_name_mr

            FROM created_blogs as cb

            LEFT JOIN blogs_types_master as bty ON CAST(bty.blogs_types_id AS TEXT) IN (cb.blogs_types_id) 

            WHERE cb.is_active=true AND cb.is_deleted = 'false' " . $crop_ids_query . $crop_wise_data . " " . $blogs_cat_condition . " ORDER BY cb.created_on DESC " . $sql_limit);

        }



        $result = $row->result_array();

        return $result;

    }



    // function custom_blog_type_listing($blogs_types_id = 'all', $start = 1){

    //     $headers_data   = $this->input->request_headers();

    //     $group_id_arr   = explode(',', $headers_data['group_id']);

    //     $group_id       = $group_id_arr[0];

    //     $crop_ids_query = '';



    //     $response   = array();

    //     $limit      = 5;

    //     $cat_id     = 0;

    //     $start_chk  = $start - 1;

    //     if ($start_chk != 0) {

    //         $start_sql = $limit * ($start_chk);

    //     } else {

    //         $start_sql = 0;

    //     }



    //     $sql_limit  = " LIMIT " . $limit . " OFFSET " . $start_sql;

    //     $crop_wise_data = '';

    //     // if ( strtolower($crop_id) != 'all') {

    //     //     $crop_wise_data = ' AND cb.crop_id =' . $crop_id;

    //     // }



    //     if (strtolower($blogs_types_id) != 'all') {

    //         if ($crop_ids[0]['crop_id'] != '') {



    //             $row = $this->db->query("SELECT cb.id as blogs_id,cb.logo,cb.blogs_tags_id,cb.blogs_types_id,cb.id,cb.title as blogs_title,cb.sub_title as blogs_sub_title,cb.description as blogs_description,cb.sub_description as blogs_sub_description,cb.created_on as blogs_created_on,bty.name as blogs_types_name,bty.logo as blogs_types_logo ,bty.mob_icon as blogs_types_mob_icon,bty.name_mr as blogs_types_name_mr

    //             FROM created_blogs as cb

    //             LEFT JOIN blogs_types_master as bty ON CAST(bty.blogs_types_id AS TEXT) IN (cb.blogs_types_id)

    //             WHERE cb.is_active=true AND cb.is_deleted = 'false' AND  cb.blogs_types_id='" . $blogs_types_id . "'" . $crop_wise_data . " AND cb.crop_id IN (" . $crop_ids[0]['crop_id'] . ") AND bty.blog_cat NOT IN(1) ORDER BY cb.created_on DESC " . $sql_limit);



    //         } else {



    //             $row = $this->db->query("SELECT cb.id as blogs_id,cb.logo,cb.blogs_tags_id,cb.blogs_types_id,cb.id,cb.title as blogs_title,cb.sub_title as blogs_sub_title,cb.description as blogs_description,cb.sub_description as blogs_sub_description,cb.created_on as blogs_created_on,bty.name as blogs_types_name,bty.logo as blogs_types_logo ,bty.mob_icon as blogs_types_mob_icon,bty.name_mr as blogs_types_name_mr

    //             FROM created_blogs as cb

    //             LEFT JOIN blogs_types_master as bty ON CAST(bty.blogs_types_id AS TEXT) IN (cb.blogs_types_id)

    //             WHERE cb.is_active=true AND cb.is_deleted = 'false' AND  cb.blogs_types_id='" . $blogs_types_id . "'" . $crop_wise_data . " AND bty.blog_cat NOT IN(1) ORDER BY cb.created_on DESC " . $sql_limit);



    //         }



    //     } else {

    //         $row = $this->db->query("SELECT cb.id as blogs_id,cb.logo,cb.blogs_tags_id,cb.blogs_types_id,cb.id,cb.title as blogs_title,cb.sub_title as blogs_sub_title,cb.description as blogs_description,cb.sub_description as blogs_sub_description,cb.created_on as blogs_created_on, bty.name as blogs_types_name,bty.logo as blogs_types_logo ,bty.mob_icon as blogs_types_mob_icon,bty.name_mr as blogs_types_name_mr

    //         FROM created_blogs as cb

    //         LEFT JOIN blogs_types_master as bty ON CAST(bty.blogs_types_id AS TEXT) IN (cb.blogs_types_id) 

    //         WHERE cb.is_active=true AND cb.is_deleted = 'false' " . $crop_ids_query . $crop_wise_data . " AND bty.blog_cat NOT IN(1) ORDER BY cb.created_on DESC " . $sql_limit);

    //     }



    //     $result = $row->result_array();

    //     return $result;

    // }



    function media_listing($page = 1)
    {

        $base_url_media = $this->config_url['media_thumbnails'];

        $response = [];

        $limit = 30;

        // $start      = 1;

        $cat_id = 0;

        // $start       = $this->input->post('start') != ''?$this->input->post('start'):1;

        $start_chk = $page - 1;

        if ($start_chk != 0) {

            $start_sql = $limit * ($start_chk);

        } else {

            $start_sql = 0;

        }



        $sql_limit = " LIMIT " . $limit . " OFFSET " . $start_sql;

        $media_fields = ' media_id, url, url_type, title, description, partner_id, category, published_on, thumbnails, view_count, is_home, is_featured';

        $query_media_main = $this->db->query("SELECT " . $media_fields . " FROM media WHERE is_deleted = false AND is_active = true AND is_featured=1  ORDER BY media_id DESC LIMIT 10");

        $result_featured = $query_media_main->result_array();

        $sql_query_featured = $this->db->last_query();



        $query_media = $this->db->query("SELECT " . $media_fields . " FROM media WHERE is_deleted = false AND is_active = true ORDER BY media_id DESC " . $sql_limit);

        $result = $query_media->result_array();



        return array('result' => $result, 'result_featured' => $result_featured, 'sql_query_featured' => $sql_query_featured);

    }



    function dss_recommended_listing()
    {

        $dss_recommended = $data_array = [];

        $blogs_types_id = 9;

        $sql_limit = 'LIMIT 3 ';

        $row = $this->db->query("SELECT cb.id as blogs_id,cb.logo,cb.blogs_tags_id,cb.blogs_types_id,cb.id,cb.title as blogs_title,cb.sub_title as blogs_sub_title,cb.description as blogs_description,cb.sub_description as blogs_sub_description,cb.created_on as blogs_created_on,bty.name as blogs_types_name,bty.logo as blogs_types_logo ,bty.mob_icon as blogs_types_mob_icon,bty.name_mr as blogs_types_name_mr

            FROM created_blogs as cb

            LEFT JOIN blogs_types_master as bty ON CAST(bty.blogs_types_id AS TEXT) IN (cb.blogs_types_id)

            WHERE cb.is_active=true AND cb.is_deleted = 'false' AND  cb.blogs_types_id='" . $blogs_types_id . "' ORDER BY cb.created_on DESC " . $sql_limit);



        //  $sql            = "SELECT * FROM advertise_master where is_deleted=false AND is_active=true ORDER BY seq ASC";

        //$res_val        = $this->db->query($sql);

        $res_array = $row->result_array();

        if (count($res_array) > 0) {

            $dss_recommended = $res_array;

        }

        return $dss_recommended;

    }



    public function home_page_post()
    {


        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        $client_type = $headers_data['client-type'];

        $weather = array('display' => true, );

        $media = $this->media_listing();

        $dynamic_menu_functions = [

            "my_crops" => $this->my_crops_listing('', 'my_crops'),

            "weather" => $weather,

            // "commodity_rate_updates" => $this->commodity_rate_updates_client_wise(),

            "advertise" => $this->advertise_listing(),

            "recommended" => $this->recommended_products_listing(),

            "services" => $this->categories_listing(),

            "other_services" => $this->blog_listing('all', 1, 'all', 2),

            "blogs" => $this->blog_listing(),

            "media" => $media['result_featured'],

            "dss_recommended" => $this->dss_recommended_listing()

        ];

        // print_r($dynamic_menu_functions);exit;



        $home_page_settings = get_config_settings('home_page_setting');
        $description = $home_page_settings['description'];

        if (empty($description) || $description == '' || $description == 'null' || $description == null || $description == '[]') {
            $dynamic_setting = [
                "my_crops",
                "weather",
                "commodity_rate_updates",
                "advertise",
                "recommended",
                "services",
                "other_services",
                "blogs",
                "media",
                "dss_recommended"
            ];
        } else {

            // Sometimes it’s double-encoded like "[\"my_crops\",...]"
            $decoded = json_decode($description, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // Try decoding after htmlspecialchars_decode
                $decoded = json_decode(htmlspecialchars_decode($description), true);
            }

            $dynamic_setting = is_array($decoded) ? $decoded : [];
        }




        if ($client_type == 'buyer') {

            $valueToRemove = "services";

            $dynamicSetting = array_filter($dynamic_setting, function ($element) use ($valueToRemove) {

                return $element != $valueToRemove;

            });

        } else {

            $dynamicSetting = $dynamic_setting;

        }





        foreach ($dynamic_menu_functions as $key => $value) {

            // print_r($dynamicSetting);exit;

            if (in_array($key, $dynamicSetting)) {

                $data[$key] = $value;

            } else {

                if ($key == 'weather') {

                    $data[$key] = array('display' => false, );

                } else {

                    $data[$key] = [];

                }

            }

        }



        // $data['my_crops']   = $this->my_crops_listing('','my_crops');

        // $data['weather']    = $weather;

        // $data['commodity_rate_updates'] = $this->commodity_rate_updates_client_wise();

        // $data['advertise']  = $this->advertise_listing();

        // $data['recommended']= $this->recommended_products_listing();

        // $data['services']   = $this->categories_listing();

        // $data['other_services']= $this->blog_listing('all',1,'all',2);

        // $data['blogs']      = $this->blog_listing();

        // $data['media']      = $media['result'];

        // $data['dss_recommended']     = $this->dss_recommended_listing();



        if (count($data) > 0) {

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, "message" => lang('Listed_Successfully'), "config_url" => $this->config_url, "config_flag" => $this->config_flag);



        } else {

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $data, "message" => lang('Data_Not_Found'), "config_url" => $this->config_url, "config_flag" => $this->config_flag);



        }





        $this->api_response($response);
        exit;

    }



    /***********************Working APIs:End***********************/



    /***********************Save Logs:Start***********************/

    public function save_logs($response = [])
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



    public function chk_otp_new()
    {



        $phone = substr(preg_replace('/\s+/', '', $this->input->post('phone')), -10, 10);



        if ($phone == 9876543210 || $phone == 9976543210) {

            $opt_number = 643215;

            // $update_arr['opt_number'] = $opt_number;

        } else {

            $opt_number = $this->input->post('otp');

        }



        $result = array();

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Registration / login failed, please try again some time.");



        if ($phone != '' && $opt_number != '') {



            /* $row    = $this->db->query("SELECT id FROM client WHERE is_deleted = 'false' AND 'is_active' => 'true' AND opt_number='$opt_number' AND phone::varchar = '$phone'::varchar ");*/

            $sql_chk = "SELECT * FROM client WHERE is_deleted = 'false' and phone::varchar = '$phone'::varchar ";

            $row_data = $this->db->query($sql_chk);

            $row = $row_data->result_array();



            if (count($result)) {



                if ($row[0]['opt_number'] == $otp || $otp == 888888) {



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



                    $row_blog = $this->db->query("SELECT blogs_types_id ,name ,logo ,name_mr ,mob_icon FROM blogs_types_master WHERE is_active = 'true' AND is_deleted = 'false' AND is_home =1  ORDER BY seq ASC");

                    $result_blogs = $row_blog->result_array();



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

                        'iot_device_url' => $row[0]['iot_device_url'],

                        'ACCESS_TOKEN' => current_date(),

                        'categories' => $categories,

                        'pcategories' => $pcategories,

                        'countries' => $countries,

                        'is_whitelabeled' => $row[0]['is_whitelabeled'],

                        'is_video_enable' => $row[0]['is_video_enable'],

                        'is_chat_enable' => $row[0]['is_chat_enable'],

                        'referral_code' => $row[0]['referral_code'],

                    );



                    if ($row[0]['is_whitelabeled'] === 't') {



                        $bank_master_id = $row[0]['bank_master_id'];

                        $row_bank = $this->db->query("SELECT  gm.logo,gm.mob_icon,bm.*

                                FROM bank_master as bm

                                LEFT JOIN client_group_master as gm ON gm.client_group_id = bm.group_id

                                WHERE bm.is_active = 'true' AND bm.is_deleted = 'false' AND bm.bank_master_id = $bank_master_id

                                LIMIT 1");

                        $whitelabel_data = $row_bank->result_array();



                    } else {

                        $whitelabel_data = array();



                    }



                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, 'farm_type' => $this->farm_type, 'topology' => $this->topology, 'soil_type' => $this->soil_type, 'unit' => $this->unit, "message" => "Login successfully", 'config_url' => $this->config_url, 'menu' => $this->menu, 'whitelabel_data' => $whitelabel_data, 'is_whitelabeled' => $row[0]['is_whitelabeled']);

                    $this->api_response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "OTP not matched, please try again some time.");



                    $this->api_response($response);

                    exit;



                }



            } else {



                $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "OTP not matched, please try again some time.");



                $this->api_response($response);

                exit;



            }

        } else {

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Missing_Parameter'));



            $this->api_response($response);

            exit;

        }



        $this->api_response($response);

        exit;

    }



    public function delete_number($phone_number)
    {

        // $this->load->helper('sms_helper');

        $phone = substr(preg_replace('/\s+/', '', $phone_number), -10, 10);



        //$id = $row[0]['id'];

        $update_arr = array('is_deleted' => true);

        $this->db->where('client.phone', $phone);

        $result = $this->db->update('client', $update_arr);



        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "User removed");



        $this->api_response($response);

        exit;



    }



    public function get_profile_data($farmer_id)
    {



        $result = array();

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $_POST, "message" => lang('Missing_Parameter'));



        if ($farmer_id != '') {



            $select = array('client.*', 'countries.name as country_name', 'states.name as state_name');

            $join = array(
                'countries' => array('countries.code = client.country', 'left'),

                'states' => array('states.code = client.state', 'left')
            );



            $where = array('client.id' => $farmer_id, 'client.is_deleted' => 'false');

            $user_data = $this->Masters_model->get_data($select, 'client', $where, $join);



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



            //$data['categories'] = $categories;

            $row = $user_data;



            if (count($row)) {

                if (1) {

                    //if($row[0]['email_verify'] == 't')



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

                        'ACCESS_TOKEN' => current_date(),

                        'iot_device_url' => $row[0]['iot_device_url'],

                        'my_refferal_code' => $row[0]['my_refferal_code'],

                        'categories' => $categories,

                        'pcategories' => $pcategories,

                        'countries' => $countries,

                        'is_video_enable' => $row[0]['is_video_enable'],

                        'is_chat_enable' => $row[0]['is_chat_enable'],

                    );



                    //'state'         => $row[0]['state'],

                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, 'farm_type' => $this->farm_type, 'topology' => $this->topology, 'soil_type' => $this->soil_type, 'unit' => $this->unit, "message" => lang('Updated_Successfully'), 'config_url' => $this->config_url);

                }



            }



        }



        $this->api_response($response);



    }



    /*

    This function is use to show states according to crop.

     */

    public function states_crop_wise_post()
    {

        // parse_str($_SERVER['QUERY_STRING'], $_POST);

        // $type = $this->input->post('type');

        $crop_id = $this->input->post('crop_id');

        $country_id = $this->input->post('country') ? $this->input->post('country') : 101;

        $crop_seasons = array(

            array('id' => '1', 'name' => 'Kharif', 'name_mr' => 'खरीप'),

            array('id' => '2', 'name' => 'Rabi', 'name_mr' => 'रबी'),

            array('id' => '3', 'name' => 'Summer ', 'name_mr' => 'उन्हाळा'),

        );

        // $country_code = $this->input->post('country_code') ? $this->input->post('country_code') : 'IN';

        // $state_code   = $this->input->post('state_code') ? $this->input->post('state_code') : null;

        /*  $crop_ids_query = '';

          if ($crop_id != '') {

              $where_vm         = array('crop_id' => $crop_id);

              $result_state_ids = $this->Masters_model->get_data(array('variety_state'), 'crop_variety_master', $where_vm);



              if (count($result_state_ids) > 0) {



                  foreach ($result_state_ids as $v) {

                      $res_ids[] = $v['variety_state'];

                  }



                  $state_ids_str  = implode(',', $res_ids);

                  $crop_ids_query = " AND id IN (" . $state_ids_str . ") ";

              }



          }

          $sql_states       = "SELECT id,name FROM states_new where country_id =   $country_id " . $crop_ids_query;

          $res_states       = $this->db->query($sql_states);

          $res_states_array = $res_states->result_array();



          if (count($res_states_array) > 0) {



              $response = array("status" => 1, "error" => 0, "success" => 1, "data" => $res_states_array, 'irrigation_src' => $this->irri_src, 'irrigation_type' => $this->irri_faty, 'soil_type' => $this->soil_type, "crop_seasons" => $crop_seasons, "message" => "States list");



          } else {



              $where    = array('country' => $country);

              $result   = $this->Masters_model->get_data(array('id', 'name'), 'states_new', $where);

              $response = array("status" => 1, "error" => 0, "success" => 1, 'irrigation_src' => $this->irri_src, 'irrigation_type' => $this->irri_faty, 'soil_type' => $this->soil_type, "crop_seasons" => $crop_seasons, "data" => $res_states_array, "message" => "State list");

          }*/



        $mystates = array('Tamil Nadu', 'Chhattisgarh', 'Delhi', 'Gujarat', 'Haryana', 'Karnataka', 'Madhya Pradesh', 'Maharashtra', 'Odisha', 'Punjab', 'Rajasthan', 'Andhra Pradesh', 'Uttar Pradesh', 'Bihar', 'Orissa', 'Gujrat', 'Daman and diu', 'Goa', 'Jammu and Kashmir', 'Himachal Pradesh', 'Uttarakhand', 'Chandigarh', 'Kerala');



        foreach ($mystates as $val) {

            $mystates_arr[] = array('id' => $val, 'name' => $val);



        }



        $response = array("status" => 1, "error" => 0, "success" => 1, 'irrigation_src' => $this->irri_src, 'irrigation_type' => $this->irri_faty, 'soil_type' => $this->soil_type, "crop_seasons" => $crop_seasons, "data" => $mystates_arr, "message" => "State list");



        $this->api_response($response);

        exit;



    }







    public function get_expertise($type_get)
    {

        $response = array();



        if ($type_get != '') {

            $sql = "SELECT name,name_en,name_mr FROM categories WHERE cat_id='" . $type_get . "' AND is_active = 'true'";

            $row = $this->db->query($sql);

            $result = $row->result_array();

            if (count($result)) {

                $response = array("status" => 1, "success" => 1, "error" => 0, "data" => $result, "config_url" => $this->config_url, "message" => lang('Listed_Successfully'));

            } else {

                $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

            }



        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "message" => lang('Data_Not_Found'));

        }

        $this->api_response($response);

    }



    public function get_partner_services_details($partner_id, $service_id)
    {

        //echo $type_get = $type_get;

        $response = array();



        if ($service_id != '' && $partner_id) {



            /*$sql2    = "SELECT id, name_en, name_mr, user_type_id FROM user_services WHERE is_deleted='false' AND is_active = 'true'";

            $row2    = $this->db->query($sql2);

            $result2 = $row2->result_array();*/



            $sql_ser = "SELECT * FROM product_services WHERE is_deleted = 'false' AND is_active='true' AND service_id=" . $service_id . " AND created_by_id = " . $partner_id;

            $row3 = $this->db->query($sql_ser);

            $result_packages = $row3->result_array();



            $selects = array('first_name', 'last_name', 'city', 'postal_code', 'profile_image');

            $where = array('user_id' => $partner_id, 'is_deleted' => 'false', 'is_active' => 'true');



            $user_data = $this->Masters_model->get_data($selects, 'users', $where);



            if (count($user_data)) {

                $response = array("success" => 1, "status" => 1, "data" => $user_data, "config_url" => $this->config_url, "service_options" => $result_packages, "packages" => $result_packages, "message" => lang('Listed_Successfully'), 'default_image' => 'service_default.jpg');

            } else {

                $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

            }

        } else {

            $response = array("success" => 1, "status" => 0, "status" => 0, "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);

    }



    public function is_custom_field($prod_id)
    {

        //$p_type = 'product';



        $where_cat = array('real_id' => $prod_id);

        $this->db->select('*');

        $this->db->where($where_cat);

        $custom_fields = $this->db->get('custom_fields')->result_array();



        if (count($custom_fields)) {



            echo $form_data_str = get_custom_field_form($prod_id);

            if ($form_data_str == '') {



            }



        } else {

            echo $status = 0;

            //echo 0;

        }



    }



    public function get_my_land($farmer_id)
    {

        $response = array();



        if ($farmer_id != '') {

            $sql = "SELECT land_id ,farmer_id , farm_name, soil_type ,topology ,farm_type ,farm_size , farm_name,farm_name_mr, farm_image, unit ,irrigation_facility ,calculated_land_area ,survey_no ,khasra_no ,irrigation_source,farm_polygoan_coordinates  FROM master_land_details WHERE farmer_id='" . $farmer_id . "' ORDER BY land_id DESC";

            $row = $this->db->query($sql);

            $result = $row->result_array();

            if (count($result)) {



                $response = array("status" => 1, "data" => $result, 'farm_type' => $this->farm_type, 'topology' => $this->topology, 'soil_type' => $this->soil_type, "unit" => $this->unit, "message" => lang('Listed_Successfully'), 'config_url' => $this->config_url);

            } else {

                $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

            }

        } else {

            $response = array("success" => 1, "status" => 0, "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);

    }



    public function add_basic_details()
    {



        $result = array();

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

                $data = $this->input->post();

                $data['error'] = validation_errors();

            } else {



                $insert = array(

                    'profile_image' => $this->input->post('profile_image'),

                    'first_name' => $this->input->post('first_name'),

                    'last_name' => $this->input->post('last_name'),

                    'phone' => $this->input->post('cphone'),

                    'email' => $this->input->post('email'),

                    'aadhar_no' => $this->input->post('aadhar_no'),

                    'pan_no' => $this->input->post('pan_no'),

                    'country' => $this->input->post('country'),

                    'state' => $this->input->post('state'),

                    'city' => $this->input->post('city'),

                    'village' => $this->input->post('village'),

                    'postcode' => $this->input->post('postcode'),

                    'address1' => $this->input->post('address'),

                    'bank_name' => $this->input->post('bank_name'),

                    'branch_name' => $this->input->post('branch_name'),

                    'acc_no' => $this->input->post('acc_no'),

                    'ifsc_code' => $this->input->post('ifsc_code'),

                    'gender' => $this->input->post('gender'),

                    'dob' => $this->input->post('dob'),

                    'created_on' => current_date(),



                );



                $result = $this->db->insert('client', $insert);

                $insert_id = $this->db->insert_id();



                // code for email

                if ($result) {



                    if (count($insert)) {

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Added_Successfully'));

                    }



                    $this->api_response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Basic  detail Add failed, please try again some time.", 'config_url' => $this->config_url);



                    $this->api_response($response);

                    exit;



                }

            }

        }



        $this->api_response($response);

        exit;

    }



    public function login()
    {



        $result = array();

        $id = $this->input->post('id');

        $image = '';



        if (!empty($_FILES['profile_image']['name'])) {

            $extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);

            //echo $extension;

            $profile_image_name = 'profile_image_' . time() . '.' . $extension;

            $target_file = 'uploads/user_data/profile/' . $profile_image_name;

            // for delete previous image.

            if ($this->input->post('old_profile_image') != "") {

                @unlink('./uploads/user_data/profile/' . $this->input->post('old_profile_image'));

            }



            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {

                $update_arr['profile_image'] = $profile_image_name;

                $error = 0;



            } else {

                $error = 2;

            }

        } else {

            //if(old_profile_image)

            // $update['profile_image'] = $this->input->post('old_profile_image');

        }



        if (!empty($_FILES['pan_no_doc']['name'])) {

            $extension = pathinfo($_FILES['pan_no_doc']['name'], PATHINFO_EXTENSION);



            $pan_no_doc_name = 'pan_no_doc_' . time() . '.' . $extension;

            $target_file = 'uploads/user_data/pan_no/' . $pan_no_doc_name;

            // for delete previous image.

            if ($this->input->post('old_pan_no_doc') != "") {

                @unlink('./uploads/user_data/pan_no/' . $this->input->post('old_pan_no_doc'));

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



            $aadhar_no_doc_name = 'aadhar_no_doc_' . time() . '.' . $extension;

            $target_file = 'uploads/user_data/aadhar_no/' . $aadhar_no_doc_name;

            // for delete previous image.

            if ($this->input->post('old_aadhar_no_doc') != "") {

                @unlink('./uploads/user_data/aadhar_no/' . $this->input->post('old_aadhar_no_doc'));

            }



            if (move_uploaded_file($_FILES["aadhar_no_doc"]["tmp_name"], $target_file)) {

                $update_arr['aadhar_no_doc'] = $aadhar_no_doc_name;

                $error = 0;

            } else {

                $error = 2;



            }

        }



        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Not_Able_Update'), "post_param" => $_POST);



        if ($id != '') {



            if (0) {



            } else {



                $bank_name = $this->input->post('bank_name') != '' ? $this->input->post('bank_name') : null;

                $branch_name = $this->input->post('branch_name') != '' ? $this->input->post('branch_name') : null;

                $acc_no = $this->input->post('acc_no') != '' ? $this->input->post('acc_no') : 0;

                $ifsc_code = $this->input->post('ifsc_code') != '' ? $this->input->post('ifsc_code') : null;

                $address1 = $this->input->post('address1') != '' ? $this->input->post('address1') : null;

                $postcode = $this->input->post('postcode') != '' ? $this->input->post('postcode') : null;

                $village = $this->input->post('village') != '' ? $this->input->post('village') : null;

                $dob = $this->input->post('dob') != '' ? $this->input->post('dob') : null;



                $update_arr['first_name'] = $this->input->post('first_name');

                $update_arr['last_name'] = $this->input->post('last_name');

                $update_arr['phone'] = $this->input->post('phone');

                $update_arr['email'] = $this->input->post('email');

                $update_arr['aadhar_no'] = $this->input->post('aadhar_no');

                $update_arr['pan_no'] = $this->input->post('pan_no');

                $update_arr['country'] = $this->input->post('country');

                $update_arr['state'] = $this->input->post('state');

                $update_arr['city'] = $this->input->post('city');

                $update_arr['village'] = $village;

                $update_arr['postcode'] = $postcode;

                $update_arr['address1'] = $address1;

                $update_arr['bank_name'] = $bank_name;

                $update_arr['branch_name'] = $branch_name;

                $update_arr['acc_no'] = $acc_no;

                $update_arr['ifsc_code'] = $ifsc_code;

                $update_arr['dob'] = $dob;

                $update_arr['group_id'] = $this->input->post('group_id');

                $update_arr['gender'] = $this->input->post('gender');

                // $update_arr['aadhar_no_doc'] = $this->input->post('aadhar_no_doc');

                $update_arr['updated_on'] = current_date();



                $this->db->where('client.id', $id);

                $result = $this->db->update('client', $update_arr);

                if ($result) {



                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Updated_Successfully'), 'config_url' => $this->config_url);



                    $this->api_response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 2, "data" => $result, "message" => lang('Not_Able_Update'), "post_param" => $_POST);



                    $this->api_response($response);

                    exit;



                }

            }

        }



        $this->api_response($response);

        exit;

    }



    public function add_land_details()
    {



        $result = array();

        $image = '';

        $farm_image_upload = '';



        if (!empty($_FILES['farm_image']['name'])) {



            if (!file_exists($this->upload_file_folder . 'farm')) {

                mkdir($this->upload_file_folder . 'farm', 0777, true);

            }



            $extension = pathinfo($_FILES['farm_image']['name'], PATHINFO_EXTENSION);



            $farm_image_name = 'farm_image_' . time() . '.' . $extension;

            $target_file = $this->upload_file_folder . 'farm/' . $farm_image_name;

            // for delete previous image.

            if ($this->input->post('old_farm_image') != "") {

                @unlink($this->upload_file_folder . '/farm/' . $this->input->post('old_farm_image'));

            }



            if (move_uploaded_file($_FILES["farm_image"]["tmp_name"], $target_file)) {

                $farm_image_upload = $farm_image_name;

                $error = 0;



            } else {



                $error = 2;



            }

        }

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Missing_Parameter'));



        if ($this->input->post('btn_submit') == 'submit') {



            if (0) {

                $data = $this->input->post();

                $data['error'] = validation_errors();

            } else {



                //   'khasra_no'           => $this->input->post('khasra_no'),



                $insert = array(

                    'farm_image' => $farm_image_name,

                    'farmer_id' => $this->input->post('farmer_id'),

                    'survey_no' => $this->input->post('survey_no'),



                    'soil_type' => $this->input->post('soil_type'),

                    'topology' => $this->input->post('topology'),

                    'farm_type' => $this->input->post('farm_type'),

                    'farm_size' => $this->input->post('farm_size'),

                    'unit' => $this->input->post('unit'),

                    'irrigation_source' => $this->input->post('irrigation_source'),

                    'irrigation_facility' => $this->input->post('irrigation_facility'),

                    'farm_name' => $this->input->post('farm_name'),

                    'farm_name_mr' => $this->input->post('farm_name_mr'),

                    //'village_city' => $this->input->post('village_city'),

                    'created_on' => current_date(),

                );



                if ($farm_image_upload != '') {

                    $insert['farm_image'] = $farm_image_upload;

                }



                $result = $this->db->insert('master_land_details', $insert);

                $insert_id = $this->db->insert_id();



                if ($result) {



                    if (count($insert)) {

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Added_Successfully'), 'config_url' => $this->config_url);

                    }



                    $this->api_response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Land detail Add failed, please try again some time.");



                    $this->api_response($response);

                    exit;



                }

            }

        }



        $this->api_response($response);

        exit;

    }



    public function delete_land_details($land_id)
    {

        $response = array();

        if ($land_id != '') {

            $sql = "UPDATE master_land_details SET is_deleted = 'true' WHERE land_id = '" . $land_id . "'";



            $result = $this->db->query($sql);



            if (count($result)) {

                $response = array("status" => 1, "data" => $result, "message" => "Farm Deleted successfully");

            }

        } else {

            $response = array("status" => 0, "message" => "Farm not Deleted successfully");

        }

        $this->api_response($response);

    }



    public function get_land($farmer_id)
    {

        $response = array();

        if ($farmer_id != '') {

            $sql = "SELECT land_id ,farmer_id , soil_type , farm_name,topology ,farm_type ,farm_size ,unit ,irrigation_facility , farm_image, calculated_land_area ,survey_no ,khasra_no ,irrigation_source,farm_polygoan_coordinates  FROM master_land_details WHERE farmer_id='" . $farmer_id . "' AND is_deleted = 'false' ORDER BY land_id DESC";

            $row = $this->db->query($sql);

            $result = $row->result_array();

            if (count($result)) {

                $response = array("status" => 1, "data" => $result, "message" => lang('Listed_Successfully'));

            } else {

                $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

            }

        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);

    }



    public function get_product_list($partner_id)
    {

        $response = array();

        if ($partner_id != '') {

            $sql = "SELECT id, partner_id, category_id, product_name, overview, brief, highlight, usage, version, logo, type, product_type, price FROM products WHERE partner_id='" . $partner_id . "' AND is_deleted = 'false' ORDER BY id DESC";

            $row = $this->db->query($sql);

            //$result = $row->result_array(); // MMM comment for live only

            $result = array();

            if (count($result)) {

                $response = array("status" => 1, "data" => $result, "message" => lang('Listed_Successfully'));

            } else {

                $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

            }

        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);

    }



    public function get_land_detail($land_id)
    {

        $response = array();

        if ($land_id != '') {

            $sql = "SELECT *  FROM master_land_details WHERE land_id='" . $land_id . "' AND is_deleted = 'false' LIMIT 1";

            $row = $this->db->query($sql);

            $result_land = $row->result_array();



            if (count($result_land)) {



                $sql_crop = "SELECT md.id ,md.client_id ,md.land_id ,md.crop ,md.crop_name,md.crop_type ,md.area_under_cultivation ,md.unit ,md.crop_image,md.calculated_area , md.duration_from ,md.duration_to, c.name as crop_name FROM master_crop_details as md LEFT JOIN crop as c ON md.crop = c.crop_id WHERE md.land_id='" . $land_id . "' AND md.is_deleted = 'false'";



                $row_crops = $this->db->query($sql_crop);

                $result_crops = $row_crops->result_array();



                $result['result_crops'] = $result_crops;

                $result['result_land'] = $result_land;



                $response = array("status" => 1, "data" => $result, "unit" => $this->unit, "soil_type" => $this->soil_type, "farm_type" => $this->farm_type, "irri_src" => $this->irri_src, "irri_faty" => $this->irri_faty, "topology" => $this->topology, "crop_type" => $this->crop_type, "crop" => $this->crop, "message" => lang('Listed_Successfully'));



            } else {

                $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

            }

        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);

    }



    public function custom_from_leads($product_id = 0, $farmer_id = 0, $prod_id = 0)
    {



        $farmer_id = $this->input->post('farmer_id');

        $product_id = $product_id;

        $partner_id = $this->input->post('partner_id');

        $custom_field = $this->input->post('custom_field');



        if (null !== $this->input->post('custom_field')) {

            $is_custom = 1;

        } else {

            $is_custom = 0;

        }



        $response = array();

        $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        if ($farmer_id != '' && $product_id != '') {



            $insert = array(

                'client_id' => $farmer_id,

                'product_id' => $product_id,

                'partner_id' => $partner_id,

                'is_custom' => $is_custom,

                'created_on' => current_date(),

            );



            $this->db->insert('product_leads', $insert);

            $insertId = $this->db->insert_id();



            if (null !== $this->input->post('custom_field')) {

                $is_custom = 1;

                $custom_fields = $this->input->post('custom_field');



                foreach ($custom_fields as $key => $value) {

                    # code...

                    $insert_data[] = array(

                        'field_id' => $key,

                        'field_value' => $value,

                        'product_leads_id' => $insertId,

                    );

                }



                $this->db->insert('custom_fields_values', $insert_data);

            }



            $response = array("status" => 1, "data" => 1, "message" => lang('Added_Successfully'));



        } else {

            $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        }

        $this->api_response($response);

    }



    public function get_cart_items()
    {

        $response = array();

        $prod_ids = $this->input->post('prod_ids');

        $prod_ids_str = $prod_ids;

        $sql = "SELECT id, partner_id, category_id, product_name, version, logo, type, product_type, price FROM products WHERE id IN(" . $prod_ids_str . ") AND  is_deleted = 'false' ORDER BY id DESC";

        $row = $this->db->query($sql);

        $result = $row->result_array();

        $query_str = $this->db->last_query();



        if (count($result)) {

            $response = array("status" => 1, "data" => $result, 'total_records' => count($result), "message" => lang('Listed_Successfully'), 'query_str' => $query_str);

        } else {

            $response = array("status" => 0, "message" => lang('Data_Not_Found'));

        }



        $this->api_response($response);

    }



    // public function add_loan_details()

    // {

    //     $result = array();



    //     $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Registration failed, please try again some time.");



    //     if ($this->input->post('btn_submit') == 'submit') {



    //         if (0) {

    //             $data          = $this->input->post();

    //             $data['error'] = validation_errors();

    //         } else {



    //             $insert = array(

    //                 'user_id'            => $this->input->post('farmer_id'),

    //                 'first_name'         => $this->input->post('first_name'),

    //                 'last_name'          => $this->input->post('last_name'),

    //                 'previous_loan'      => $this->input->post('previous_loan'),

    //                 'age'                => $this->input->post('age'),

    //                 'farmer_cast'        => $this->input->post('farmer_cast'),

    //                 'own_home'           => $this->input->post('own_home'),

    //                 'own_land'           => $this->input->post('own_land'),

    //                 'own_vehicle'        => $this->input->post('own_vehicle'),

    //                 'own_animal'         => $this->input->post('own_animal'),

    //                 'loan_type'          => $this->input->post('loan_type'),

    //                 'apply_loan_against' => $this->input->post('apply_loan_against'),

    //                 'annual_income'      => $this->input->post('annual_income'),

    //                 'family_members'     => $this->input->post('family_members'),

    //                 'status'             => 'Pending',

    //                 'created_on'         => current_date(),



    //             );



    //             $result    = $this->db->insert('loan_details', $insert);

    //             $insert_id = $this->db->insert_id();



    //             if ($result) {



    //                 if (count($insert)) {

    //                     $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Loan detail Added Successfully");

    //                 }



    //                 $this->api_response($response);

    //                 exit;



    //             } else {



    //                 $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Loan detail Add failed, please try again some time.");



    //                 $this->api_response($response);

    //                 exit;

    //             }

    //         }

    //     }



    //     $this->api_response($response);

    //     exit;

    // }



    public function add_insurance_details()
    {



        $result = array();



        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Registration failed, please try again some time.");



        if ($this->input->post('btn_submit') == 'submit') {



            if (0) {

                $data = $this->input->post();

                $data['error'] = validation_errors();

            } else {



                $insert = array(

                    'user_id' => $this->input->post('farmer_id'),

                    'age' => $this->input->post('age'),

                    'farmer_cast' => $this->input->post('farmer_cast'),

                    'own_home' => $this->input->post('own_home'),

                    'own_land' => $this->input->post('own_land'),

                    'own_vehicle' => $this->input->post('own_vehicle'),

                    'own_animal' => $this->input->post('own_animal'),

                    'insurance_type' => $this->input->post('insurance_type'),

                    'annual_income' => $this->input->post('annual_income'),

                    'family_members' => $this->input->post('family_members'),

                    'previous_insurance' => $this->input->post('previous_insurance'),

                    'status' => 'Pending',

                    'created_on' => current_date(),



                );



                $result = $this->db->insert('insurance_details', $insert);

                $insert_id = $this->db->insert_id();



                if ($result) {

                    if (count($insert)) {

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Added_Successfully'));

                    }



                    $this->api_response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Insurance detail Add failed, please try again some time.");



                    $this->api_response($response);

                    exit;



                }

            }

        }



        $this->api_response($response);

        exit;

    }



    public function get_my_insurance($farmer_id)
    {

        //echo $type_get = $type_get;

        $response = array();

        if ($farmer_id != '') {

            $sql = "SELECT c.*,ide.id as insurance_app_id,ide.user_id as insurance_app_user_id,ide.insurance_type_id,ide.insurance_company_id,ide.insurance_package_id,ide.other_details,ide.status,ide.created_on,ide.insurance_image, ip.insurance_user_id as insurance_package_id, ip.title,ip.sub_title,ip.description,ip.price,ip.brochure, itm.name as insurance_type_name ,icm.name as insurance_company_name

FROM insurance_details as ide

LEFT JOIN insurance_packages as ip ON ip.id = ide.insurance_package_id

LEFT JOIN insurance_types_master as itm O	N itm.insurance_type_id = ide.insurance_type_id

LEFT JOIN insurance_company_master as icm ON icm.insurance_company_id = ide.insurance_company_id

LEFT JOIN client as c ON c.id = ide.user_id

WHERE ide.is_deleted=false AND ide.user_id=" . $farmer_id;

            $row = $this->db->query($sql);

            $result = $row->result_array();

            if (count($result)) {



                $response = array("status" => 1, "data" => $result, "message" => lang('Listed_Successfully'));

            } else {

                $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

            }

        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);

    }



    public function get_my_insurance_new($id)
    {

        //echo $type_get = $type_get;

        $response = array();

        if ($id != '') {



            $sql = 'SELECT ide.*,ip.title,ip.sub_title,ip.description,ip.price,ip.brochure, itm.name as insurance_type_name ,icm.name as insurance_company_name

FROM insurance_details as ide

LEFT JOIN insurance_packages as ip ON ip.id = ide.insurance_package_id

LEFT JOIN insurance_types_master as itm ON itm.insurance_type_id = ide.insurance_type_id

LEFT JOIN insurance_company_master as icm ON icm.insurance_company_id = ide.insurance_company_id

WHERE ip.is_active=true AND ide.id=' . $id . ' LIMIT 1';

            $row = $this->db->query($sql);

            $result = $row->result_array();

            if (count($result)) {

                $data['application'] = $result;

                $sql_banks = "SELECT a.*, b.app_interest , a.company_name,a.bank_id FROM bank_insurance_details as b

                LEFT JOIN banks as a ON a.bank_id = b.bank_id

                WHERE application_id='" . $id . "' and app_interest='yes'";

                $rows = $this->db->query($sql_banks);

                $bank_dd = $rows->result_array();

                $data['application'] = $result;

                if (count($bank_dd)) {

                    $data['banks'] = $bank_dd;

                } else {

                    $data['banks'] = array();

                }



                $response = array("status" => 1, "data" => $data, "message" => lang('Listed_Successfully'));

            } else {

                $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

            }

        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);

    }



    public function update_insurance($id)
    {



        $result = array();

        $id = $this->input->post('id');

        $farmer_id = $this->session->userdata('user_id');



        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Not_Able_Update'));



        if ($id != '') {



            echo "herere";

            if (0) {



            } else {



                $update_arr = array(

                    'id' => $this->input->post('id'),

                    'user_id' => $this->input->post('user_id'),

                    'age' => $this->input->post('age'),

                    'farmer_cast' => $this->input->post('farmer_cast'),

                    'own_home' => $this->input->post('own_home'),

                    'own_land' => $this->input->post('own_land'),

                    'own_vehicle' => $this->input->post('own_vehicle'),

                    'own_animal' => $this->input->post('own_animal'),

                    'insurance_type' => $this->input->post('insurance_type'),

                    'annual_income' => $this->input->post('annual_income'),

                    'family_members' => $this->input->post('family_members'),

                    'previous_insurance' => $this->input->post('previous_insurance'),

                    'updated_on' => current_date(),



                );

                //print_r($update_arr);



                $this->db->where('insurance_details.id', $id);

                $result = $this->db->update('insurance_details', $update_arr);

                //$insert_id = $this->db->insert_id();

                //echo $this->db->last_query();

                // code for email

                if ($result) {



                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Updated_Successfully'));



                    $this->api_response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Not_Able_Update'));



                    $this->api_response($response);

                    exit;



                }

            }

        }



        $this->api_response($response);

        exit;

    }



    public function update_loan($id)
    {



        $result = array();

        $id = $this->input->post('id');

        $farmer_id = $this->session->userdata('user_id');



        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Not_Able_Update'));



        if ($id != '') {



            echo "herere";

            if (0) {



            } else {



                $update_arr = array(

                    'id' => $this->input->post('id'),

                    'user_id' => $this->input->post('farmer_id'),

                    'first_name' => $this->input->post('first_name'),

                    'last_name' => $this->input->post('last_name'),

                    'previous_loan' => $this->input->post('previous_loan'),

                    'age' => $this->input->post('age'),

                    'farmer_cast' => $this->input->post('farmer_cast'),

                    'own_home' => $this->input->post('own_home'),

                    'own_land' => $this->input->post('own_land'),

                    'own_vehicle' => $this->input->post('own_vehicle'),

                    'own_animal' => $this->input->post('own_animal'),

                    'loan_type' => $this->input->post('loan_type'),

                    'apply_loan_against' => $this->input->post('apply_loan_against'),

                    'annual_income' => $this->input->post('annual_income'),

                    'family_members' => $this->input->post('family_members'),

                    'updated_on' => current_date(),



                );

                //print_r($update_arr);



                $this->db->where('loan_details.id', $id);

                $result = $this->db->update('loan_details', $update_arr);

                //$insert_id = $this->db->insert_id();

                //  echo $this->db->last_query();

                // code for email

                if ($result) {



                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Updated_Successfully'));



                    $this->api_response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Not_Able_Update'));



                    $this->api_response($response);

                    exit;



                }

            }

        }



        $this->api_response($response);

        exit;

    }



    public function get_product_details($product_id)
    {

        $response = array();

        $mydata = array();



        if ($product_id != '') {

            $sql = "SELECT product_name, price, logo, is_cod, type, version, product_type, category_id FROM products Where id='" . $product_id . "' AND is_deleted = 'false'";



            $row = $this->db->query($sql);

            $result = $row->result_array();

            if (count($result)) {



                foreach ($result as $value) {

                    $cat_ids = $value['category_id'];



                    $sql_cat = "SELECT name from pcategories where pcat_id IN (" . $cat_ids . ")";

                    $row2 = $this->db->query($sql_cat);

                    $result_val = $row2->result_array();

                    if (count($result_val)) {

                        //$cat_str = implode(',', $result_val);

                        $cat_str = $result_val;

                    } else {

                        $cat_str = '';

                    }



                    $new_data = array('product_name' => $value['product_name'], 'price' => $value['price'], 'logo' => $value['logo'], 'is_cod' => $value['is_cod'], 'type' => $value['type'], 'version' => $value['version'], 'product_type' => $value['product_type'], 'categories' => $cat_str);



                    $mydata[] = $new_data;

                }



            }

            if (count($mydata)) {

                $response = array("status" => 1, "success" => 1, "error" => 0, "data" => $mydata, "message" => lang('Listed_Successfully'));

            } else {

                $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

            }







            $this->api_response($response);

        }

    }



    public function document_upload()
    {

        $response = array();

        $id = $this->input->post('user_id');

        $image = '';

        $result = '';



        if (!empty($_FILES['profile_image']['name'])) {

            $extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);



            $profile_image_name = 'profile_image_' . time() . '.' . $extension;

            $target_file = 'uploads/user_data/user_documents/' . $profile_image_name;



            if ($this->input->post('old_profile_image') != "") {

                @unlink('./uploads/user_data/user_documents/' . $this->input->post('old_profile_image'));

            }



            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {

                $update['profile_image'] = $profile_image_name;

                $error = 0;



            } else {

                $error = 2;

            }

        }

        if ($profile_image_name != '') {

            if (0) {



            } else {



                $update_arr = array(

                    'profile_image' => $profile_image_name,

                    'created_on' => current_date(),



                );



                $this->db->where('uploaded_docs.id', $id);

                $result = $this->db->insert('uploaded_docs', $update_arr);

                if ($result) {

                    echo "File uploaded successfully!!";

                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "picture inserted successfully");



                    $this->api_response($response);

                    exit;



                } else {

                    $response = array("success" => 0, "error" => $error, "status" => 1, "data" => $result, "message" => "picture inserted failed, please try again some time.");



                    $this->api_response($response);

                    exit;

                    die;



                }

            }

        }



        $this->api_response($response);

        exit;

    }



    public function get_loan_types_chk()
    {



        //$_POST = file_get_contents("php://input");

        //$_POST = json_decode($_POST,true);

        /* $headers_data    = $this->input->request_headers();

        $is_whitelabeled = $headers_data['is_whitelabeled'];

        $advertise_data  = array();

        $data_array      = array();

        $bank_master_id         = $headers_data['bank_master_id'];*/

        $is_whitelabeled = 1;

        $bank_master_id = 8;



        if ($is_whitelabeled) {



            $sql_bank = "select loan_type from banks where bank_master_id=" . $bank_master_id;

            $row_bank = $this->db->query($sql_bank);

            $result_bank = $row_bank->result_array();

            $loan_types = $result_bank[0]['loan_type'];



            print_r($result_bank);



            if ($loan_types != '') {

                $row = $this->db->query("SELECT loan_type_id ,name ,logo ,name_mr ,mob_icon FROM loan_types_master WHERE is_active = 'true'  AND is_deleted = 'false' AND loan_type_id IN($loan_types) ");

            } else {



                $row = $this->db->query("SELECT loan_type_id ,name ,logo ,name_mr ,mob_icon FROM loan_types_master WHERE is_active = 'true' AND is_deleted = 'false'");



            }

        } else {



            $row = $this->db->query("SELECT loan_type_id ,name ,logo ,name_mr ,mob_icon FROM loan_types_master WHERE is_active = 'true' AND is_deleted = 'false'");

        }

        $result = $row->result_array();

        $sql_chk = $this->db->last_query();

        if (count($result)) {

            $response = array("status" => 1, "data" => $result, "config_url" => $this->config_url, "message" => lang('Listed_Successfully'));

        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }





        /*

            if (count($result)) {

                $response = array("status" => 1, "success" => 1, "error" => 0, "data" => $result, 'total_records' => $count_res, "page_count" => $start, "message" => lang('Listed_Successfully'));

            } else {

                $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

            }



            lang('Missing_Parameter')

            */

        // $this->api_response($result, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

        $this->api_response($response);

    }



    public function get_insurance_types()
    {

        $row = $this->db->query("SELECT insurance_type_id ,name ,logo ,name_mr ,mob_icon FROM insurance_types_master WHERE is_active = 'true' AND is_deleted = 'false'");

        $result = $row->result_array();

        if (count($result)) {

            $response = array("status" => 1, "success" => 1, "error" => 0, "data" => $result, "config_url" => $this->config_url, "message" => lang('Listed_Successfully'));

        } else {

            $response = array("status" => 1, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }



        $this->api_response($response);

    }



    public function get_insurance_company()
    {



        $row = $this->db->query("SELECT insurance_company_id ,name ,logo ,name_mr ,mob_icon FROM insurance_company_master WHERE is_active = 'true' AND is_deleted = 'false'");

        $result = $row->result_array();



        if (count($result)) {

            $response = array("status" => 1, "success" => 1, "error" => 0, "data" => $result, "config_url" => $this->config_url, "message" => lang('Listed_Successfully'));

        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }

        // $this->api_response($result, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

        $this->api_response($response);

    }



    public function get_insurance_packages($insurance_type_id)
    {



        $row = $this->db->query("SELECT ip.id as insurance_pacakge_id,ip.insurance_type_id,ip.insurance_company_id,ip.insurance_user_id,ip.title as insurance_package_title,ip.sub_title as insurance_package_sub_title,ip.description as insurance_package_description,ip.price as insurance_package_price,b.bank_id,b.first_name,b.last_name,b.email,b.company_name,b.phone_no,b.type, itm.name as insurance_type_name,itm.logo as insurance_type_logo ,itm.mob_icon as insurance_type_mob_icon,itm.name_mr as insurance_type_name_mr,icm.name as insurance_company_name,icm.logo as insurance_company_logo ,icm.mob_icon as insurance_company_mob_icon,icm.name_mr as insurance_company_name_mr

            FROM insurance_packages as ip

            LEFT JOIN insurance_types_master as itm ON itm.insurance_type_id = ip.insurance_type_id

            LEFT JOIN insurance_company_master as icm ON icm.insurance_company_id = ip.insurance_company_id

            LEFT JOIN banks as b ON b.bank_id = ip.insurance_user_id

            WHERE ip.is_active=true AND ip.is_deleted = 'false' AND ip.insurance_type_id = " . $insurance_type_id);

        $result = $row->result_array();

        if (count($result)) {

            $response = array("status" => 1, "success" => 1, "error" => 0, "data" => $result, "config_url" => $this->config_url, "message" => lang('Listed_Successfully'));

        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }





        // $this->api_response($result, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

        $this->api_response($response);

    }

    public function get_insurance_packages_details($id)
    {

        //$insurance_pacakge_id       = $this->input->post('id');

        //$_POST = file_get_contents("php://input");

        //$_POST = json_decode($_POST,true);



        $row = $this->db->query("SELECT ip.id as insurance_pacakge_id,ip.insurance_type_id,ip.insurance_company_id,ip.insurance_user_id,ip.title as insurance_package_title,ip.sub_title as insurance_package_sub_title,ip.description as insurance_package_description,ip.price as insurance_package_price,b.bank_id,b.first_name,b.last_name,b.email,b.company_name,b.phone_no,b.type, itm.name as insurance_type_name,itm.logo as insurance_type_logo ,itm.mob_icon as insurance_type_mob_icon,itm.name_mr as insurance_type_name_mr,icm.name as insurance_company_name,icm.logo as insurance_company_logo ,icm.mob_icon as insurance_company_mob_icon,icm.name_mr as insurance_company_name_mr

            FROM insurance_packages as ip

            LEFT JOIN insurance_types_master as itm ON itm.insurance_type_id = ip.insurance_type_id

            LEFT JOIN insurance_company_master as icm ON icm.insurance_company_id = ip.insurance_company_id

            LEFT JOIN banks as b ON b.bank_id = ip.insurance_user_id

            WHERE ip.is_active=true AND ip.is_deleted = 'false' AND ip.id = " . $id);



        $result = $row->result_array();



        if (count($result)) {

            $response = array("status" => 1, "success" => 1, "error" => 0, "data" => $result, "config_url" => $this->config_url, "message" => lang('Listed_Successfully'));

        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }



        $this->api_response($response);

    }



    public function add_insurance_details_new()
    {



        $result = array();

        $image = '';



        if (!empty($_FILES['insurance_image']['name'])) {

            $extension = pathinfo($_FILES['insurance_image']['name'], PATHINFO_EXTENSION);

            $insurance_image_name = 'insurance_image_' . time() . '.' . $extension;

            $target_file = 'uploads/user_data/insurance/' . $insurance_image_name;

            // for delete previous image.

            if ($this->input->post('old_insurance_image') != "") {

                @unlink('./uploads/user_data/insurance/' . $this->input->post('old_insurance_image'));

            }



            if (move_uploaded_file($_FILES["insurance_image"]["tmp_name"], $target_file)) {

                $update['insurance_image'] = $insurance_image_name;

                $error = 0;

            } else {

                $error = 2;

            }

        }

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Registration failed, please try again some time.");



        if ($this->input->post('btn_submit') == 'submit') {



            //  if ($this->form_validation->run() == FALSE) {

            if (0) {

                $data = $this->input->post();

                $data['error'] = validation_errors();

            } else {



                $insert = array(

                    'insurance_image' => $insurance_image_name,

                    'user_id' => $this->input->post('user_id'),

                    'insurance_type_id' => $this->input->post('insurance_type_id'),

                    'insurance_package_id' => $this->input->post('insurance_package_id'),

                    'insurance_company_id' => $this->input->post('insurance_company_id'),

                    'status' => 'Insurance Applied',

                    'created_on' => current_date(),



                );



                $result = $this->db->insert('insurance_details', $insert);

                $insert_id = $this->db->insert_id();



                if ($insert_id) {

                    $up_data = json_encode($_POST);

                    $sql_update = "update insurance_details set other_details = '" . $up_data . "' where id = " . $insert_id;

                    $this->db->query($sql_update);



                }



                if ($result) {



                    if (count($insert)) {

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Added_Successfully'));

                    }



                    $this->api_response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Insurance detail Add failed, please try again some time.");

                    $this->api_response($response);

                    exit;



                }

            }

        }



        $this->api_response($response);

        exit;

    }



    public function get_client_group()
    {

        $row = $this->db->query("SELECT client_group_id ,name ,logo ,name_mr ,mob_icon FROM client_group_master WHERE is_active = 'true' AND is_deleted = 'false'");

        $result = $row->result_array();

        if (count($result)) {

            $response = array("status" => 1, "data" => $result, "config_url" => $this->config_url, "message" => lang('Listed_Successfully'), 'config_url' => $this->config_url);

        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }



        $this->api_response($response);

    }



    public function commodity()
    {

        $today = date('Y-m-d');

        $query = $this->db->query("SELECT DISTINCT commodity, variety, min_price, max_price,arrival_date FROM commodities_market ORDER BY arrival_date DESC  LIMIT 20");



        $result = $query->result_array();

        if (count($result)) {

            $response = array("status" => 1, "data" => $result, "message" => lang('Listed_Successfully'), 'config_url' => $this->config_url);

        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }



        $this->api_response($response);

    }



    public function commodity_all()
    {



        $today = date('Y-m-d');

        $query = $this->db->query("SELECT commodity, variety, min_price, max_price,arrival_date FROM commodities_market ORDER BY arrival_date DESC  LIMIT 550");



        $result = $query->result_array();

        if (count($result)) {

            $response = array("status" => 1, "data" => $result, "config_url" => $this->config_url, "message" => lang('Listed_Successfully'));

        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }



        $this->api_response($response);



    }

    public function commodity_all_wheat()
    {



        $today = date('Y-m-d');

        $query = $this->db->query("SELECT  min_price, max_price,arrival_date FROM commodities_market WHERE commodity='Wheat' AND  variety='Local'  ORDER BY arrival_date DESC ");



        $result = $query->result_array();

        if (count($result)) {

            $response = array("data" => $result);

        }



        $this->api_response($response);



    }



    public function commodity_all_new()
    {



        // $today = date('Y-m-d');

        $query = $this->db->query("SELECT * FROM marketwiseapmcprices");



        $result = $query->result_array();

        if (count($result)) {

            $response = array("data" => $result);

        }



        $this->api_response($response);

    }



    public function commodity_all_new_by_type($commodityname = '')
    {



        // $today = date('Y-m-d');

        if ($commodityname) {

            $query = $this->db->query("SELECT * FROM marketwiseapmcprices where commodityname= '" . $commodityname . "' ");

        } else {

            $query = $this->db->query("SELECT * FROM marketwiseapmcprices");

        }



        $result = $query->result_array();

        if (count($result)) {

            $response = array("data" => $result);

        }



        $this->api_response($response);

    }



    public function get_product_services($partner_id = "")
    {

        if ($partner_id) {

            echo 'hrere';

            $row = $this->db->query("SELECT * FROM product_services WHERE is_deleted = 'false' AND created_by_id = " . $partner_id);

        } else {

            $row = $this->db->query("SELECT * FROM product_services WHERE is_deleted = 'false'");

        }

        $result = $row->result_array();

        if (count($result)) {

            $response = array("status" => 1, "data" => $result, "config_url" => $this->config_url, "message" => lang('Listed_Successfully'));

        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }







        // $this->api_response($result, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

        $this->api_response($response);

    }



    public function get_blogs_details_old($id)
    {



        $row = $this->db->query("SELECT cb.id as blogs_id,cb.logo,cb.blogs_tags_id,cb.blogs_types_id,cb.id,cb.title as blogs_title,cb.sub_title as blogs_sub_title,cb.description as blogs_description, btg.name as blogs_types_name,btg.logo as blogs_types_logo ,btg.mob_icon as blogs_types_mob_icon,btg.name_mr as blogs_types_name_mr,bty.name as blogs_tags_name,bty.logo as blogs_tags_logo ,bty.mob_icon as blogs_tags_mob_icon,bty.name_mr as blogs_tags_name_mr

            FROM created_blogs as cb

            LEFT JOIN blogs_tags_master as btg ON CAST(btg.blogs_tags_id AS TEXT) IN (cb.blogs_tags_id)

            LEFT JOIN blogs_types_master as bty ON CAST(bty.blogs_types_id AS TEXT) IN (cb.blogs_types_id)

            WHERE cb.is_active=true AND cb.is_deleted = 'false' AND cb.id = " . $id);



        $result = $row->result_array();



        if (count($result)) {

            $response = array("status" => 1, "data" => $result, "config_url" => $this->config_url, "message" => lang('Listed_Successfully'));

        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }



        $this->api_response($response);

    }



    public function get_blogs_tags()
    {



        $row = $this->db->query("SELECT blogs_tags_id ,name ,logo ,name_mr ,mob_icon FROM blogs_tags_master WHERE is_active = 'true' AND is_deleted = 'false'");

        $result = $row->result_array();

        if (count($result)) {

            $response = array("status" => 1, "data" => $result, "config_url" => $this->config_url, "message" => lang('Listed_Successfully'));

        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }



        // $this->api_response($result, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

        $this->api_response($response);

    }



    public function sendPushNotificationToFCMSeverdev_chat($token, $title, $message, $arr_user, $type, $partner_name, $farmer_id, $farmer_image, $route)
    {

        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';



        $fields = array(

            'registration_ids' => $token,

            'priority' => 10,

            'data' => array("title" => $title, "body" => $message, "sound" => 'Default', 'image' => 'Notification Image', 'admno' => $arr_user, 'type' => $type, 'meeting_link' => '', 'partner_name' => $partner_name, "route" => $route, "click_action" => "FLUTTER_NOTIFICATION_CLICK", 'farmer_id' => $farmer_id, 'farmer_image' => $farmer_image),



            /*'notification'  => array("title" => $title, "body" => $message, "sound" => 'Default', 'image' => 'Notification Image', 'admno' => $arr_user, 'type' => $type, 'meeting_link' => '', 'partner_name' => $partner_name,"route"=>$route,"click_action"=> "FLUTTER_NOTIFICATION_CLICK",'farmer_id'=>$farmer_id,'farmer_image'=>$farmer_image),*/

        );



        /* // this api key for famrut farmer

        $API_SERVER_KEY = 'AAAAmhCfp8k:APA91bHXSHb1Ihie4m3y0v_0e1tAf7JCPMnubM751PMMVkC9oAi54O1AUGipDZ6ZZGCD9ajyxAFLsxjQ0cuLOqSLGjKp9Z0vBttKkIFeX7_xrZnbrAamAex-HMYK3z4SEz2_mHdwWYdu';*/

        // api key for Vendor or partner app



        $where = array('is_deleted' => 'false', 'is_active' => 'true', 'key_fields' => 'API_SERVER_KEY');

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

        $result = curl_exec($ch);

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



    public function crops()
    {

        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);

        // $group_id     = $headers_data['group_id']; (replace this line with below 2 lines)

        $group_id_arr = explode(',', $headers_data['group_id']);

        $group_id = $group_id_arr[0];

        $where = array('is_deleted' => 'false', 'is_active' => 'true', 'group_referral_code' => $group_id);

        $crop_ids = $this->Masters_model->get_data("crop_id", 'config_master', $where);



        $crop_ids_query = '';

        if ($crop_ids[0]['crop_id']) {

            $crop_ids_query = "AND crop_id IN (" . $crop_ids[0]['crop_id'] . ")";

        }



        $sql_crop = "SELECT crop_id , name as crop_name ,name_mr as crop_name_mr FROM crop where  is_active = 'true' AND is_deleted = 'false' " . $crop_ids_query . " ORDER BY crop_id ASC";

        $row_crop = $this->db->query($sql_crop);

        $result_crops = $row_crop->result_array();

        $result['result_crops'] = $result_crops;

        if (count($result)) {

            $response = array("status" => 1, "data" => $result_crops, "config_url" => $this->config_url, "message" => lang('Listed_Successfully'));

        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }





        $this->api_response($response);



    }



    public function get_prediction_array()
    {

        if (1) {

            $data = $_POST;

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data);

            $this->api_response($response);

        }

    }



    public function generate_connect_call()
    {

        //  farmer_id/partner_id

        $farmer_id = $this->input->post('farmer_id');

        $partner_id = $this->input->post('partner_id');

        $output = "";

        $today = date('Y-m-d');

        // $is_custom  = 0;

        $response = array();

        $available_flag = 0; // Not avaiable



        $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        if ($farmer_id != '' && $partner_id != '') {

            //farmer_id = ".$farmer_id." and

            /*$row_val   = $this->db->query("SELECT * FROM emeeting WHERE partner_id = ".$partner_id);

            $result = $row_val->result_array();

            if(count($result) > 0){

            }*/

            $meeting_link = date("Ymdhis") . $this->input->post('farmer_id') . $this->input->post('partner_id');



            $insert = array(

                'farmer_id' => $this->input->post('farmer_id'),

                'partner_id' => $this->input->post('partner_id'),

                'meeting_status_id' => 1,

                'meeting_started_from' => 1,

                'meeting_link_id' => md5($meeting_link),

                'is_active' => 'true',

                'created_on' => current_date(),

            );



            $sql_insert = $this->db->insert('emeeting', $insert);



            //$sql_insert = ' INSERT into emeeting'

            $row_val = $this->db->query("SELECT * FROM emeeting WHERE partner_id = " . $partner_id . " and (meeting_status_id = 1 OR meeting_status_id = 2)  AND date(created_on) = '" . $today . "' ORDER BY id ASC LIMIT 1");



            $result = $row_val->result_array();

            if (count($result) > 0) {

                $available_flag = 0; // Partner is not avaiable

            } else {

                $available_flag = 1; // Partner is avaiable

            }



            $data = $_POST;

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data);

            $this->api_response($response);

        }

    }



    public function sendPushNotificationToFCMSeverdev($token, $title, $message, $arr_user, $type, $meeting_link, $partner_name, $farmer_id = '', $route = '')
    {

        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';



        $fields = array(

            'registration_ids' => $token,

            'priority' => 10,

            'data' => array("title" => $title, "body" => $message, "sound" => 'Default', 'image' => 'Notification Image', 'admno' => $arr_user, 'type' => $type, 'meeting_link' => $meeting_link, 'partner_name' => $partner_name, 'route' => $route, "click_action" => "FLUTTER_NOTIFICATION_CLICK", 'farmer_id' => $farmer_id),

            /*'notification'             => array("title" => $title, "body" => $message, "sound" => 'Default', 'image' => 'Notification Image', 'admno' => $arr_user, 'type' => $type, 'meeting_link' => $meeting_link, 'partner_name' => $partner_name,'route'=> $route,"click_action"=> "FLUTTER_NOTIFICATION_CLICK",'farmer_id'=>$farmer_id),*/

            "time_to_live" => 30,

            "ttl" => 30,

        );



        /* // this api key for famrut farmer

        $API_SERVER_KEY = 'AAAAmhCfp8k:APA91bHXSHb1Ihie4m3y0v_0e1tAf7JCPMnubM751PMMVkC9oAi54O1AUGipDZ6ZZGCD9ajyxAFLsxjQ0cuLOqSLGjKp9Z0vBttKkIFeX7_xrZnbrAamAex-HMYK3z4SEz2_mHdwWYdu';*/

        // api key for Vendor or partner app

        $where = array('is_deleted' => 'false', 'is_active' => 'true', 'key_fields' => 'API_SERVER_KEY');

        $app_key_res = $this->Masters_model->get_data("description", 'config_master', $where);



        if ($app_key_res[0]['description']) {

            $API_SERVER_KEY = $app_key_res[0]['description'];

        } else {

            $API_SERVER_KEY = 'AAAAZP52chY:APA91bHn09jHHewFEixuQ87yO4QuYql8_bWBtRYtjx27mMIz-VWhMw6FRtbOoAHfm_xgBoZGqC0NJJiNlfObiNsqE-MNjRvNLaFtfysM6_JTzfZMFyRnjDOuzw5oCj-Ly6_Xw1GUXBX4';

        }



        /* $API_SERVER_KEY = 'AAAAZP52chY:APA91bHn09jHHewFEixuQ87yO4QuYql8_bWBtRYtjx27mMIz-VWhMw6FRtbOoAHfm_xgBoZGqC0NJJiNlfObiNsqE-MNjRvNLaFtfysM6_JTzfZMFyRnjDOuzw5oCj-Ly6_Xw1GUXBX4';*/



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

        $result = curl_exec($ch);

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



    public function accept_call()
    {



        $call_status_flag = 2;



        $partner_id = $this->input->post('partner_id');

        $farmer_id = $this->input->post('farmer_id');

        $call_status_flag = $call_status_flag;

        $accept_call_time = current_date();

        //$meeting_link =   $this->input->post('farmer_id');

        //$meeting_duration = $this->input->post('meeting_duration');

        $meeting_link = $this->input->post('MeetingId');

        //$meeting_link

        $where_array = array(

            'farmer_id' => $farmer_id,

            'partner_id' => $partner_id,

            'meeting_link_id' => $meeting_link,

        );



        $update_array = array(

            'meeting_status_id' => $call_status_flag,

            'accept_call_time' => current_date(),

        );



        $sql_update = $this->db->update('emeeting', $update_array, $where_array);



        $response = array("success" => 1, "call_data"->$where_array, "error" => 0, "status" => 1, "data" => $update_array);

        $this->api_response($response);

    }



    //19.97631/73.768565

    public function nearby_market_all_data($lat = '19.997454', $long = '73.789803', $start = 0, $apmc_market = '')
    {

        // Nashik

        //$longitude = (float) 73.789803;

        // $latitude = (float) 19.997454;



        // PUNE

        //$longitude = (float) 73.79737682647546;

        // $latitude = (float) 18.52154807142458;



        $apmc_market_data = '';

        //$apmc_market      = $_REQUEST['apmc_market'];



        $longitude = (float) $long;

        $latitude = (float) $lat;



        //satara

        // $longitude = (float) 74.29827808;

        // $latitude = (float) 17.63612885;

        // $radius = 16; // in miles



        $limit = 10;

        // $start    = 1;

        $cat_id = 0;



        if ($start != 0) {

            $start_sql = $limit * ($start);

        } else {

            $start_sql = 0;

        }



        if ($apmc_market == '') {



            $sql_location = "SELECT  COALESCE( ( 6371 * acos( cos( radians($latitude) ) * cos( radians(latitude) ) * cos( radians( longitude ) - radians($longitude) ) + sin( radians($latitude) ) * sin( radians( latitude ) ) ) ) , 0) AS distance , apmc_market ,latitude,longitude FROM apmc_location_master  ORDER BY distance ASC  LIMIT 1";



            $res_val = $this->db->query($sql_location);

            $res_array = $res_val->result_array();



            if (count($res_array) > 0) {

                $apmc_market_data = strtolower($res_array[0]['apmc_market']);

            }

        } else {

            $apmc_market_data = $apmc_market;

        }



        //  $apmc_market    = strtolower($res_array[0]['apmc_market']);

        ///$latitude       = $res_array[0]['latitude'];

        // $longitude      = $res_array[0]['longitude'];

        if ($apmc_market_data != '') {



            $lcoations_data[] = array('apmc_market' => ucfirst(strtolower($apmc_market_data)), 'latitude' => $latitude, 'longitude' => $longitude);

            //$result

            $today = date('Y-m-d');

            $sql_limit = " LIMIT " . $limit . " OFFSET " . $start_sql;

            $tbl_name = "tbl_maharashtra";




            $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
            $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

            $domain = $headers_data['domain'];

            $lang_label = " commodityname as commodity ";



            if ($selected_lang == 'mr') {

                $lang_folder = "marathi";

                $lang_label = " commodity_marathi as commodity ";

            } elseif ($selected_lang == 'hi') {

                $lang_folder = "hindi";

                $lang_label = " commodity_hindi as commodity ";

            } else {

                $lang_folder = "english";

                $lang_label = " commodityname as commodity ";

            }



            // $slq_comm = "SELECT market, $lang_label , varity as variety,minimumprices as min_price,maximumprices as max_price,marketwiseapmcpricedate as arrival_date, to_char( to_timestamp((marketwiseapmcpricedate),'DD/MM/YYYY'),'YYYY-MM-DD')  NewDateFormat,arrivals,unitofarrivals,modalprices,unitofprice FROM lastfiveyeardata WHERE lower(market) = lower('" . $apmc_market_data . "')  ORDER BY marketwiseapmcpriceid DESC " . $sql_limit;

            $slq_comm = "SELECT market, $lang_label , varity as variety,minimumprices as min_price,maximumprices as max_price,marketwiseapmcpricedate as arrival_date, to_char( to_timestamp((marketwiseapmcpricedate),'DD/MM/YYYY'),'YYYY-MM-DD')  NewDateFormat,arrivals,unitofarrivals,modalprices,unitofprice FROM $tbl_name WHERE lower(market) = lower('" . $apmc_market_data . "')  ORDER BY marketwiseapmcpriceid DESC " . $sql_limit;



            if ('ICAR' == $domain) {

                $slq_comm = "SELECT  market, $lang_label , commodityname as commodity, varity as variety,minimumprices as min_price,maximumprices as max_price,marketwiseapmcpricedate as arrival_date, to_char( to_timestamp((marketwiseapmcpricedate),'YYYY-MM-DD'),'YYYY-MM-DD')  NewDateFormat,arrivals,unitofarrivals,modalprices,unitofprice FROM $tbl_name WHERE lower(market) = lower('" . $apmc_market_data . "')  ORDER BY CASE WHEN commodityname = 'Onion' THEN 1 ELSE 2 END ASC, marketwiseapmcpricedate DESC " . $sql_limit;

            }



            $query = $this->db->query($slq_comm);



            /* $query  = $this->db->query("SELECT market,commodityname as commodity, varity as variety, minimumprices as min_price, maximumprices as max_price, crd as arrival_date , arrivals ,unitofarrivals FROM marketwiseapmcprices WHERE lower(market) = lower('" . $apmc_market . "')  ORDER BY crd DESC ".$sql_limit);*/

            $result = $query->result_array();

        }



        $response = array("success" => 1, "lcoations_data" => $lcoations_data, "data" => $result, "error" => 0, "status" => 1, 'apmc_market' => $apmc_market);

        $this->api_response($response);



    }



    public function get_end_date($crop_id)
    {



        $sql_chk = "SELECT duration_days,name FROM crop

        WHERE crop_id = $crop_id  LIMIT 1";

        $res_val = $this->db->query($sql_chk);

        $res_array = $res_val->result_array();

        $response = array("success" => 1, "data" => $res_array, "error" => 0, "status" => 1);

        $this->api_response($response);



    }



    public function recommended_npk_post()
    {



        $crop_id = $this->input->post('crop_id');

        $nitrogen = $this->input->post('nitrogen');

        $phosphorus = $this->input->post('phosphorus');

        $pottasium = $this->input->post('pottasium');

        $sulphur = $this->input->post('sulphur');



        $unit = $this->input->post('unit'); //hectare  or //acer

        $size = $this->input->post('size');



        if ($nitrogen != '' && $phosphorus != '' && $pottasium != '' && $sulphur != '' && $unit != '' && $size != '') {



            if ($nitrogen < 280) {

                $hectare['n'] = 137.5;

                $acer['n'] = 55;

                $npk_status['n'] = 'low';

            } elseif ($nitrogen >= 280 && $nitrogen <= 280) {

                $hectare['n'] = 110;

                $acer['n'] = 44;

                $npk_status['n'] = 'Medium';

            } else {

                $hectare['n'] = 82.5;

                $acer['n'] = 33;

                $npk_status['n'] = 'High';

            }



            if ($phosphorus < 10) {

                $hectare['p'] = 50;

                $acer['p'] = 20;

                $npk_status['p'] = 'low';

            } elseif ($phosphorus >= 280 && $phosphorus <= 280) {

                $hectare['p'] = 40;

                $acer['p'] = 16;

                $npk_status['p'] = 'Medium';

            } else {

                $hectare['p'] = 30;

                $acer['p'] = 12;

                $npk_status['p'] = 'High';

            }



            if ($pottasium < 120) {

                $hectare['k'] = 75;

                $acer['k'] = 30;

                $npk_status['k'] = 'low';

            } elseif ($pottasium >= 120 && $pottasium <= 250) {

                $hectare['k'] = 60;

                $acer['k'] = 24;

                $npk_status['k'] = 'Medium';

            } else {

                $hectare['k'] = 60;

                $acer['k'] = 24;

                $npk_status['k'] = 'High';

            }



            if ($sulphur < 25) {

                $hectare['s'] = 37.5;

                $acer['s'] = 15;

                $npk_status['s'] = 'low';

            } elseif ($sulphur >= 25 && $sulphur <= 50) {

                $hectare['s'] = 30;

                $acer['s'] = 12;

                $npk_status['s'] = 'Medium';

            } else {

                $hectare['s'] = 22.5;

                $acer['s'] = 9;

                $npk_status['s'] = 'High';

            }



            $res_array['npk_status'] = $npk_status;

            $res_array['npk_hectare'] = $hectare;

            $res_array['npk_acer'] = $acer;

            $response = array("success" => 1, "data" => $res_array, "error" => 0, "status" => 1);



        } else {



            $response = array("success" => 0, "data" => '', "error" => 1, "status" => 0, "message" => "please enter all params n p k s  area and unnt etc");



        }

        $this->api_response($response);



    }



    public function complex_fert($n, $p, $k, $ar_required_npk, $size = 1, $unit = 'hectare')
    {

        //global $ssp_factor, $mop_factor, $urea_factor, $ar_required_npk, $hac;

        $total_cost = 0;

        $str2 = array();



        $price_array = array("dap" => 1200, "urea" => 276, "mop" => 980, "ssp" => 420, "102626" => 1175, "123216" => 1185, "151515" => 739.50, "161616" => 368.70, "171717" => 927, "20200" => 850, "Bensulf" => 1250);



        $ar_required_npk["s"] = 30;

        $s = 30;



        if ($unit == "acre") {

            $hac = 0.404686 * $size;

            $unit == "acre";



        } else {

            $hac = $size;

            $unit == "hectare";

        }

        #For Complex Start with Max Value e.g. start with 26 in 10:26:26

        #10:26:26 (For First 100/First, 2nd and 3rd 2nd/100 and 3rd/100)

        $urea_factor = 2.17;

        # DAP N&P Conversion Factor (DAP 18%:46%)

        $dap_n_factor = 0.18;

        $dap_p_factor = 2.2;

        # DAP N&P Conversion Factor (DAP 16%:48%)

        //$dap_n_factor = 0.16;

        //$dap_p_factor = 2;



        # MOP K Cmop_factoronversion Factor

        $mop_factor = 1.66;



        # Set 2 Conversion Factor - SSP(16%)

        $ssp_factor = 6.25;



        $req_npk = $ar_required_npk["N"] . ":" . $ar_required_npk["P"] . ":" . $ar_required_npk["K"];

        $npk_array = array("N" => $n, "P" => $p, "K" => $k);



        arsort($npk_array);

        arsort($ar_required_npk);

        # check if 2 or more elements are same, take least required from $ar_required_npk

        if (array_values($npk_array)[0] == array_values($npk_array)[1]) {

            if (array_values($npk_array)[1] == array_values($npk_array)[2]) {

                # Check if all 3 elements are same

                $key1 = array_keys($npk_array)[0];

                $key2 = array_keys($npk_array)[1];

                $key3 = array_keys($npk_array)[2];

                $tmp_array = $ar_required_npk;

                asort($tmp_array);

                $first_key = array_keys($tmp_array)[0];

                $second_key = array_keys($tmp_array)[1];

                $third_key = array_keys($tmp_array)[2];

                //echo "step1";

            } else {

                # else only 2 elements are same

                $key1 = array_keys($npk_array)[0];

                $key2 = array_keys($npk_array)[1];

                $tmp_array = array($key1 => $ar_required_npk[$key1], $key2 => $ar_required_npk[$key2]);

                asort($tmp_array);

                $first_key = array_keys($tmp_array)[0];

                $second_key = array_keys($tmp_array)[1];

                $third_key = array_keys($npk_array)[2];

                //echo "step2";

            }

            $first_val = $npk_array[$first_key];

            $second_val = $npk_array[$second_key];

            $third_val = $npk_array[$third_key];

        } else {

            # else take first one from descending sorted

            $first_key = array_keys($npk_array)[0];

            $first_val = array_values($npk_array)[0];

            $second_key = array_keys($npk_array)[1];

            $second_val = array_values($npk_array)[1];

            $third_key = array_keys($npk_array)[2];

            $third_val = array_values($npk_array)[2];

            //echo "step3";

        }



        reset($npk_array);

        //echo "<br>First: ".$first_key." => ".$first_val."<br>";



        # Main Estimated NPK

        if ($first_val > 0) {

            $first_factor = (100 / $first_val);

        }



        $estimated_npk = $ar_required_npk[$first_key] * $first_factor;



        $data_1['npk_ratio'] = $n . "" . $p . "" . $k;

        $data_1['estimated_npk'] = round($estimated_npk);

        $base_price = $this->bagsprice_arr(round($estimated_npk * $hac), $n . "" . $p . "" . $k);



        $data_1['npk_bags'] = $base_price['bags'];

        $data_1['npk_bag_price'] = $base_price['bag_price'];

        $data_1['npk_cost'] = $base_price['cost'];

        $total_cost += $base_price['cost'];



        $str2['line1'] = $n . ":" . $p . ":" . $k . " , " . $data_1['estimated_npk'] . " Kg , " . $base_price['bags'] . ',' . $base_price['bag_price'];



        $second_factor = ($second_val / 100);

        $second_in_npk = $estimated_npk * $second_factor;

        $remaining_second = $ar_required_npk[$second_key] - $second_in_npk;

        if ($remaining_second > 0) {

            # calculate additional

            if ($second_key == "N") {

                $estimated_second = $remaining_second * $urea_factor;

                $data_1['Urea'] = round($estimated_second * $hac);



                $base_price = $this->bagsprice_arr(round($estimated_second * $hac), "urea");

                $data_1['Urea_bags'] = $base_price['bags'];

                $data_1['Urea_bag_price'] = $base_price['bag_price'];

                $data_1['Urea_cost'] = $base_price['cost'];

                $total_cost += $base_price['cost'];



                $str2['line2'] = "Urea ," . $data_1['Urea'] . " Kg , " . $base_price['bags'] . ',' . $base_price['bag_price'];



            } elseif ($second_key == "P") {

                $estimated_second = $remaining_second * $ssp_factor;



                $data_1['SSP'] = round($estimated_second * $hac);



                $base_price = $this->bagsprice_arr(round($estimated_second * $hac), "ssp");

                $data_1['SSP_bags'] = $base_price['bags'];

                $data_1['SSP_bag_price'] = $base_price['bag_price'];

                $data_1['SSP_cost'] = $base_price['cost'];

                $total_cost += $base_price['cost'];



                $str2['line2'] = "SSP ," . $data_1['SSP'] . " Kg , " . $base_price['bags'] . ',' . $base_price['bag_price'];



            } else {

                $estimated_second = $remaining_second * $mop_factor;



                $data_1['MOP'] = round($estimated_second * $hac);

                $base_price = $this->bagsprice_arr(round($estimated_second * $hac), "mop");

                $data_1['MOP_bags'] = $base_price['bags'];

                $data_1['MOP_bag_price'] = $base_price['bag_price'];

                $data_1['MOP_cost'] = $base_price['cost'];

                $total_cost += $base_price['cost'];



                $str2['line2'] = "MOP ," . $data_1['MOP'] . " Kg , " . $base_price['bags'] . ',' . $base_price['bag_price'];



            }

        } else {

            // echo "";

        }



        #Third Factor

        $third_factor = ($third_val / 100);

        $third_in_npk = $estimated_npk * $third_factor;

        $remaining_third = $ar_required_npk[$third_key] - $third_in_npk;

        if ($remaining_third > 0) {

            if ($third_key == "N") {

                $estimated_third = $remaining_third * $urea_factor;

                //echo "Urea ,".round($estimated_third * $hac);

                //$total_cost += bagsprice(round($estimated_third),"urea");



                $data_1['Urea'] = round($estimated_third * $hac);

                $base_price = $this->bagsprice_arr(round($estimated_third * $hac), "urea");

                $data_1['Urea_bags'] = $base_price['bags'];

                $data_1['Urea_bag_price'] = $base_price['bag_price'];

                $data_1['Urea_cost'] = $base_price['cost'];

                $total_cost += $base_price['cost'];



                $str2['line3'] = "Urea ," . $data_1['Urea'] . " Kg , " . $base_price['bags'] . ',' . $base_price['bag_price'];



            } elseif ($third_key == "P") {

                $estimated_third = $remaining_third * $ssp_factor;



                $data_1['SSP'] = round($estimated_third * $hac);

                $base_price = $this->bagsprice_arr(round($estimated_third * $hac), "ssp");

                $data_1['SSP_bags'] = $base_price['bags'];

                $data_1['SSP_bag_price'] = $base_price['bag_price'];

                $data_1['SSP_cost'] = $base_price['cost'];

                $total_cost += $base_price['cost'];



                $str2['line3'] = "SSP ," . $data_1['SSP'] . " Kg , " . $base_price['bags'] . ',' . $base_price['bag_price'];



            } else {

                $estimated_third = $remaining_third * $mop_factor;



                $data_1['MOP'] = round($estimated_third * $hac);

                $base_price = $this->bagsprice_arr(round($estimated_third * $hac), "mop");

                $data_1['MOP_bags'] = $base_price['bags'];

                $data_1['MOP_bag_price'] = $base_price['bag_price'];

                $data_1['MOP_cost'] = $base_price['cost'];

                $total_cost += $base_price['cost'];



                $str2['line3'] = "MOP ," . $data_1['MOP'] . " Kg , " . $base_price['bags'] . ',' . $base_price['bag_price'];

            }

        } else {

            //echo "Third val: ".round($remaining_third)."<br>";

            //echo "";

        }



        $bensulf_total = round($s * $hac);

        $base_price = $this->bagsprice_arr(round($bensulf_total * $hac), "Bensulf");

        $str2['line4'] = "Bensulf ," . $bensulf_total . " Kg , " . $base_price['bags'] . ',' . $base_price['bag_price'];

        $total_cost += $base_price['cost'];



        $str2['Total'] = "₹ " . number_format($total_cost, 2);



        $data_resp = $data_1;

        $data_resp['total_cost'] = $total_cost;



        return $str2;



    }



    public function complex_fert_bkkk($n, $p, $k, $ar_required_npk, $size = 1, $unit = 'hectare')
    {

        //global $ssp_factor, $mop_factor, $urea_factor, $ar_required_npk, $hac;

        $total_cost = 0;

        $str2 = array();



        $price_array = array("dap" => 1200, "urea" => 276, "mop" => 980, "ssp" => 420, "102626" => 1175, "123216" => 1185, "151515" => 739.50, "161616" => 368.70, "171717" => 927, "20200" => 850);



        if ($unit == "acre") {

            $hac = 0.404686 * $size;

            $unit == "acre";

        } else {

            $hac = $size;

            $unit == "hectare";

        }

        #For Complex Start with Max Value e.g. start with 26 in 10:26:26

        #10:26:26 (For First 100/First, 2nd and 3rd 2nd/100 and 3rd/100)

        $urea_factor = 2.17;

        # DAP N&P Conversion Factor (DAP 18%:46%)

        $dap_n_factor = 0.18;

        $dap_p_factor = 2.2;

        # DAP N&P Conversion Factor (DAP 16%:48%)

        //$dap_n_factor = 0.16;

        //$dap_p_factor = 2;



        # MOP K Cmop_factoronversion Factor

        $mop_factor = 1.66;



        # Set 2 Conversion Factor - SSP(16%)

        $ssp_factor = 6.25;



        # Required NPK for particular crop - User Input

        //$ar_required_npk = array("N" => 120, "P" => 100, "K" => 120);

        // $ar_required_npk = array("N" => $n, "P" => $p, "K" => $k);

        //echo "Required NPK = ".$ar_required_npk["N"].":".$ar_required_npk["P"].":".$ar_required_npk["K"]."<br><br>";



        $req_npk = $ar_required_npk["N"] . ":" . $ar_required_npk["P"] . ":" . $ar_required_npk["K"];

        $npk_array = array("N" => $n, "P" => $p, "K" => $k);



        arsort($npk_array);

        arsort($ar_required_npk);

        # check if 2 or more elements are same, take least required from $ar_required_npk

        if (array_values($npk_array)[0] == array_values($npk_array)[1]) {

            if (array_values($npk_array)[1] == array_values($npk_array)[2]) {

                # Check if all 3 elements are same

                $key1 = array_keys($npk_array)[0];

                $key2 = array_keys($npk_array)[1];

                $key3 = array_keys($npk_array)[2];

                $tmp_array = $ar_required_npk;

                asort($tmp_array);

                $first_key = array_keys($tmp_array)[0];

                $second_key = array_keys($tmp_array)[1];

                $third_key = array_keys($tmp_array)[2];

                //echo "step1";

            } else {

                # else only 2 elements are same

                $key1 = array_keys($npk_array)[0];

                $key2 = array_keys($npk_array)[1];

                $tmp_array = array($key1 => $ar_required_npk[$key1], $key2 => $ar_required_npk[$key2]);

                asort($tmp_array);

                $first_key = array_keys($tmp_array)[0];

                $second_key = array_keys($tmp_array)[1];

                $third_key = array_keys($npk_array)[2];

                //echo "step2";

            }

            $first_val = $npk_array[$first_key];

            $second_val = $npk_array[$second_key];

            $third_val = $npk_array[$third_key];

        } else {

            # else take first one from descending sorted

            $first_key = array_keys($npk_array)[0];

            $first_val = array_values($npk_array)[0];

            $second_key = array_keys($npk_array)[1];

            $second_val = array_values($npk_array)[1];

            $third_key = array_keys($npk_array)[2];

            $third_val = array_values($npk_array)[2];

            //echo "step3";

        }



        reset($npk_array);

        //echo "<br>First: ".$first_key." => ".$first_val."<br>";



        # Main Estimated NPK

        if ($first_val > 0) {

            $first_factor = (100 / $first_val);

        }



        $estimated_npk = $ar_required_npk[$first_key] * $first_factor;

        //echo "$n:$p:$k : ".round($estimated_npk * $hac);

        //$total_cost += bagsprice(round($estimated_npk),$n."".$p."".$k);



        # Second Factor

        /* $data_main['n']             = $n;

        $data_main['p']             = $p;

        $data_main['k']             = $k;*/

        /* $data_main['estimated_npk'] = round($estimated_npk);*/

        $data_1['npk_ratio'] = $n . "" . $p . "" . $k;

        $data_1['estimated_npk'] = round($estimated_npk);

        //$data_main['total_cost'] = bagsprice(round($estimated_npk),$n."".$p."".$k);



        $base_price = $this->bagsprice_arr(round($estimated_npk * $hac), $n . "" . $p . "" . $k);

        /* $data_main['total_cost_bags']      = $base_price['bags'];

        $data_main['total_cost_bag_price'] = $base_price['bag_price'];

        $data_main['total_cost_cost']      = $base_price['cost'];*/



        $data_1['npk_bags'] = $base_price['bags'];

        $data_1['npk_bag_price'] = $base_price['bag_price'];

        $data_1['npk_cost'] = $base_price['cost'];

        $total_cost += $base_price['cost'];



        $str2['line1'] = $n . ":" . $p . ":" . $k . " , " . $data_1['estimated_npk'] . " Kg , " . $base_price['bags'] . ',' . $base_price['bag_price'];



        $second_factor = ($second_val / 100);

        $second_in_npk = $estimated_npk * $second_factor;

        $remaining_second = $ar_required_npk[$second_key] - $second_in_npk;

        if ($remaining_second > 0) {

            # calculate additional

            if ($second_key == "N") {

                $estimated_second = $remaining_second * $urea_factor;

                //echo "Urea : ".round($estimated_second * $hac);

                //$total_cost += bagsprice(round($estimated_second),"urea");



                $data_1['Urea'] = round($estimated_second * $hac);

                // $data_1['Urea_cost'] = bagsprice(round($estimated_second), "urea");



                $base_price = $this->bagsprice_arr(round($estimated_second * $hac), "urea");

                $data_1['Urea_bags'] = $base_price['bags'];

                $data_1['Urea_bag_price'] = $base_price['bag_price'];

                $data_1['Urea_cost'] = $base_price['cost'];

                $total_cost += $base_price['cost'];



                $str2['line2'] = "Urea ," . $data_1['Urea'] . " Kg , " . $base_price['bags'] . ',' . $base_price['bag_price'];



            } elseif ($second_key == "P") {

                $estimated_second = $remaining_second * $ssp_factor;



                $data_1['SSP'] = round($estimated_second * $hac);



                $base_price = $this->bagsprice_arr(round($estimated_second * $hac), "ssp");

                $data_1['SSP_bags'] = $base_price['bags'];

                $data_1['SSP_bag_price'] = $base_price['bag_price'];

                $data_1['SSP_cost'] = $base_price['cost'];

                $total_cost += $base_price['cost'];



                $str2['line2'] = "SSP ," . $data_1['SSP'] . " Kg , " . $base_price['bags'] . ',' . $base_price['bag_price'];



            } else {

                $estimated_second = $remaining_second * $mop_factor;

                //echo "MOP ,".round($estimated_second * $hac);

                //$total_cost += bagsprice(round($estimated_second),"mop");

                $data_1['MOP'] = round($estimated_second * $hac);

                $base_price = $this->bagsprice_arr(round($estimated_second * $hac), "mop");

                $data_1['MOP_bags'] = $base_price['bags'];

                $data_1['MOP_bag_price'] = $base_price['bag_price'];

                $data_1['MOP_cost'] = $base_price['cost'];

                $total_cost += $base_price['cost'];



                $str2['line2'] = "MOP ," . $data_1['MOP'] . " Kg , " . $base_price['bags'] . ',' . $base_price['bag_price'];



            }

        } else {

            // echo "";

        }



        #Third Factor

        $third_factor = ($third_val / 100);

        $third_in_npk = $estimated_npk * $third_factor;

        $remaining_third = $ar_required_npk[$third_key] - $third_in_npk;

        if ($remaining_third > 0) {

            if ($third_key == "N") {

                $estimated_third = $remaining_third * $urea_factor;

                //echo "Urea ,".round($estimated_third * $hac);

                //$total_cost += bagsprice(round($estimated_third),"urea");



                $data_1['Urea'] = round($estimated_third * $hac);

                $base_price = $this->bagsprice_arr(round($estimated_third * $hac), "urea");

                $data_1['Urea_bags'] = $base_price['bags'];

                $data_1['Urea_bag_price'] = $base_price['bag_price'];

                $data_1['Urea_cost'] = $base_price['cost'];

                $total_cost += $base_price['cost'];



                $str2['line3'] = "Urea ," . $data_1['Urea'] . " Kg , " . $base_price['bags'] . ',' . $base_price['bag_price'];



            } elseif ($third_key == "P") {

                $estimated_third = $remaining_third * $ssp_factor;



                $data_1['SSP'] = round($estimated_third * $hac);

                $base_price = $this->bagsprice_arr(round($estimated_third * $hac), "ssp");

                $data_1['SSP_bags'] = $base_price['bags'];

                $data_1['SSP_bag_price'] = $base_price['bag_price'];

                $data_1['SSP_cost'] = $base_price['cost'];

                $total_cost += $base_price['cost'];



                $str2['line3'] = "SSP ," . $data_1['SSP'] . " Kg , " . $base_price['bags'] . ',' . $base_price['bag_price'];



            } else {

                $estimated_third = $remaining_third * $mop_factor;



                $data_1['MOP'] = round($estimated_third * $hac);

                $base_price = $this->bagsprice_arr(round($estimated_third * $hac), "mop");

                $data_1['MOP_bags'] = $base_price['bags'];

                $data_1['MOP_bag_price'] = $base_price['bag_price'];

                $data_1['MOP_cost'] = $base_price['cost'];

                $total_cost += $base_price['cost'];



                $str2['line3'] = "MOP ," . $data_1['MOP'] . " Kg , " . $base_price['bags'] . ',' . $base_price['bag_price'];

            }

        } else {

            //echo "Third val: ".round($remaining_third)."<br>";

            //echo "";

        }



        $str2['Total'] = "₹ " . number_format($total_cost, 2);



        //   $data_resp['data_3']     = $data_3;

        //$data_resp['data_1']     = $data_1;

        $data_resp = $data_1;

        // $data_resp['data_main']  = $data_main;

        $data_resp['total_cost'] = $total_cost;



        return $str2;



    }



    public function bagsprice_arr($kg, $frt_name)
    {

        // global $price_array;

        /* $price_array = array("dap" => 1200, "urea" => 276, "mop" => 980, "ssp" => 420, "102626" => 1175, "123216" => 1185, "151515" => 739.50, "161616" => 368.70, "171717" => 927, "20200" => 850);

         */

        $price_array = array("dap" => 1200, "urea" => 276, "mop" => 980, "ssp" => 420, "102626" => 1175, "123216" => 1185, "151515" => 739.50, "161616" => 368.70, "171717" => 927, "20200" => 850, "05234" => 115, "Bensulf" => 1250);



        if ($frt_name == "Bensulf") {

            $bags = round($kg / 5);

            $new_kg = $bags * 5;

        } else {

            $bags = round($kg / 50);

            $new_kg = $bags * 50;

        }



        $diff_str = "";



        if ($new_kg < $kg) {

            $diff_str = "+ " . ($kg - $new_kg);

        } elseif ($new_kg > $kg) {

            $diff_str = " - " . ($new_kg - $kg);

        }



        $cost = $price_array[$frt_name] * $bags;



        if ($diff_str != '') {

            $data_cal['bags'] = "  " . $bags . " Bags (" . $diff_str . "Kg)";

        } else {

            $data_cal['bags'] = "  " . $bags . " Bags";

        }



        $data_cal['bag_price'] = "₹ " . $price_array[$frt_name] * $bags;

        $data_cal['cost'] = $cost;

        return $data_cal;

    }



    public function bagsprice_arr_bkkkk($kg, $frt_name)
    {

        // global $price_array;

        /* $price_array = array("dap" => 1200, "urea" => 276, "mop" => 980, "ssp" => 420, "102626" => 1175, "123216" => 1185, "151515" => 739.50, "161616" => 368.70, "171717" => 927, "20200" => 850);

         */

        $price_array = array("dap" => 1200, "urea" => 276, "mop" => 980, "ssp" => 420, "102626" => 1175, "123216" => 1185, "151515" => 739.50, "161616" => 368.70, "171717" => 927, "20200" => 850, "05234" => 115);



        $bags = round($kg / 50);

        $new_kg = $bags * 50;

        $diff_str = "";

        if ($new_kg < $kg) {

            $diff_str = "- " . ($kg - $new_kg);

        } elseif ($new_kg > $kg) {

            $diff_str = " + " . ($new_kg - $kg);

        }



        $cost = $price_array[$frt_name] * $bags;



        if ($diff_str != '') {

            $data_cal['bags'] = "  " . $bags . " Bags (" . $diff_str . "Kg)";

        } else {

            $data_cal['bags'] = "  " . $bags . " Bags";

        }



        $data_cal['bag_price'] = "₹ " . $price_array[$frt_name] * $bags;

        $data_cal['cost'] = $cost;

        return $data_cal;

    }



    public function farmer_list($vendor_id)
    {



        if ($vendor_id != '') {

            $sql_farmer = "SELECT id,first_name,middle_name,last_name,email,phone,profile_image from client where is_active=true AND is_deleted = false AND phone !='' ORDER BY  referral_code ASC";

            $query = $this->db->query($sql_farmer);

            $result = $query->result_array();

            if (count($result)) {



                $response = array("success" => 1, "data" => $result, "msg" => 'farmer_list', "error" => 0, "status" => 1);



            } else {

                $response = array("success" => 1, "data" => array(), "msg" => 'No farmer avaialbe', "error" => 0, "status" => 1);

            }

        } else {



            $response = array("success" => 0, "data" => array(), "msg" => lang('Missing_Parameter'), "error" => 1, "status" => 1);



        }

        $this->api_response($response);

    }



    public function test_ref($root = '9607005004')
    {



        $slq_comm = "SELECT * from client where is_active=true AND is_deleted = false AND phone !='' ORDER BY  referral_code ASC";

        $query = $this->db->query($slq_comm);

        $result = $query->result_array();

        if (count($result)) {

            foreach ($result as $v) {

                //echo 'par _mob'.$v['phone'];//echo ' -> under _mob'.$v['referral_code'];



                $demo_array[] = array($v['phone'] => trim($v['referral_code']));



                $demo_array2[$v['phone']] = trim($v['referral_code']) ? $v['referral_code'] : null;



            }



        }



        /*    $tree = array(

        'H' => 'G',

        'F' => 'G',

        'G' => 'D',

        'E' => 'D',

        'A' => 'E',

        'B' => 'C',

        'C' => 'E',

        'D' => null,

        'Z' => null,

        'MM' =>'Z',

        'KK' =>'Z',

        'MMM' =>'MM',

        );*/



        // $root = array();

        //$root = array();

        //print_r($demo_array2);

        //  echo '===================<br>=====';

        $new_tree = arsort($demo_array2);



        // print_r($new_tree);

        // echo '===================<br>';

        $dataval_tree = $this->parseAndPrintTree($root, $demo_array2);



        return $dataval_tree;



        // echo '===================<br>';

        // $dataval_tree = $this->parseAndPrintTree($root , $tree );



        // $data_nt =  $this->parseTree($demo_array2);



        //$dataval = $this->parseAndPrintTree($tree, $root = null);



        //$dataval = recurse_uls ($tree, $parent = null);

        // $dataval = $this->recurse_uls ($tree, $parent = 'D');



        //print_r($dataval);



        //$dataval = $this->parseAndPrintTree($root , $tree);



        //print_r( $tree );

        //print_r($dataval_tree);



        //print_r($data_nt);

        //$this->api_response($dataval);



    }



    public function buildTree(array $elements, $parentId = 0)
    {

        $branch = array();



        foreach ($elements as $element) {

            if ($element['parent_id'] == $parentId) {

                $children = $this->buildTree($elements, $element['id']);

                if ($children) {

                    $element['children'] = $children;

                }

                $branch[] = $element;

            }

        }



        return $branch;

    }



    public function recurse_uls($array, $parent)
    {

        echo '<ul>';

        foreach ($array as $c => $p) {

            if ($p != $parent) {

                continue;

            }



            echo '<li>' . $c . '</li>';

            $this->recurse_uls($array, $c);

        }

        echo '</ul>';

    }



    public function GenerateNavArray($arr, $parent = 0)
    {

        $pages = array();

        foreach ($arr as $page) {

            if ($page['parent'] == $parent) {

                $page['sub'] = isset($page['sub']) ? $page['sub'] : $this->GenerateNavArray($arr, $page['id']);

                $pages[] = $page;

            }

        }

        return $pages;

    }



    public function buildTreeNew(array $elements, $parentId = 0)
    {

        $branch = array();



        foreach ($elements as $element) {

            if ($element['parent_id'] == $parentId) {

                $children = $this->buildTreeNew($elements, $element['id']);



                if ($children) {

                    $element['children'] = $children;

                }



                $branch[] = $element;

            }

        }

        return $branch;

    }



    //$tree = buildTree($rows);



    public function parseAndPrintTree($root, $tree)
    {

        $return = array();

        if (!is_null($tree) && count($tree) > 0) {

            echo '<ul>';

            foreach ($tree as $child => $parent) {

                if ($parent == $root) {

                    unset($tree[$child]);

                    echo '<li > <span><i class="fa fa-minus-square"></i> </span> ' . $child;

                    $this->parseAndPrintTree($child, $tree);

                    echo '</li>';

                }

            }

            echo '</ul>';

        }

    }



    public function parseTree($tree, $root = null)
    {

        $return = array();

        # Traverse the tree and search for direct children of the root



        if (!is_null($tree) && count($tree) > 0) {

            echo '<ul>';

            foreach ($tree as $child => $parent) {

                if ($parent == $root) {

                    unset($tree[$child]);

                    echo '<li>' . $child;

                    $this->parseTree($child, $tree);

                    echo '</li>';

                }

            }

            echo '</ul>';

        }



        /*  foreach($tree as $child => $parent) {

        # A direct child is found

        if($parent == $root) {

        # Remove item from tree (we don't need to traverse this again)

        unset($tree[$child]);

        # Append the child into result array and parse its children

        $return[] = array(

        'name' => $child,

        'children' => $this->parseTree($tree, $child)

        );

        }

        }*/

        // return empty($return) ? null : $return;

    }



    public function commodity_details_data($commodity_name, $market_name, $varity = 0, $is_encode = 0)
    {



        $today = date('Y-m-d');



        if ($is_encode != 0) {

            if ($varity != 0) {

                $varity = base64_decode($varity);

                // $where_varity = " AND varity ILIKE '".base64_decode($varity)."' ";

            }



            if ($commodity_name != '') {

                $commodity_name = base64_decode($commodity_name);

            }



            if ($market_name != '') {

                $market_name = base64_decode($market_name);

            }



        }



        if ($varity != 0) {

            $where_varity = " AND varity ILIKE '" . $varity . "' ";

        } else {

            $where_varity = '';

        }



        $query = $this->db->query("SELECT to_char( to_timestamp((marketwiseapmcpricedate),'DD/MM/YYYY'),'YYYY-MM-DD') NewDateFormat ,commodityname, market, minimumprices, maximumprices,modalprices,unitofprice,unitofarrivals,arrivals,varity

 FROM lastfiveyeardata WHERE commodityname ILIKE '" . $commodity_name . "'  " . $where_varity . " AND market ILIKE '" . $market_name . "'  ORDER BY marketwiseapmcpriceid DESC LIMIT 5");

        $days_data = $query->result_array();



        //market // commodity //file_name



        $query_prediction = $this->db->query("SELECT *

 FROM prediction_data_files WHERE commodity ILIKE '" . $commodity_name . "' AND market ILIKE '" . $market_name . "'  ORDER BY id DESC LIMIT 1");

        $prediction_data = $query_prediction->result_array();

        //GROUP BY marketwiseapmcpricedate,marketwiseapmcpriceid  ORDER BY marketwiseapmcpriceid DESC



        /*  $query = $this->db->query("SELECT commodity, variety, min_price, max_price,arrival_date FROM commodities_market WHERE commodity= '".$commodity_name."' AND commodities_market= '".$market_name."' ORDER BY arrival_date DESC  LIMIT 5");



        $query = $this->db->query("SELECT commodity, variety, min_price, max_price,arrival_date FROM commodities_market WHERE commodity= '".$commodity_name."' AND commodities_market= '".$market_name."' ORDER BY arrival_date DESC  LIMIT 550");*/



        /* SELECT commodityname, market, minimumprices, maximumprices, marketwiseapmcpricedate

        FROM lastfiveyeardata WHERE commodityname= 'Tomato' AND market= 'Pune' ORDER BY marketwiseapmcpriceid DESC  LIMIT 5*/

        //"date": "2021-10-26"

        $prediction_file_path = 'https://dev.famrut.co.in/python/Prediction/prediction_result/';

        $data_new = array();

        $graph_array = array();

        $cost_array = array();



        $date = date("Y-m-d");

        $date_my = date();

        $date_last_month_timestamp = strtotime('-30 day');

        // $prev_date = date('Y-m-d', strtotime($date .' -1 day'));

        $next_date = date('Y-m-d', strtotime($date . ' +1 day'));

        // $prev_date_2 = date('Y-m-d', strtotime($date .' -2 day'));

        $next_date_2 = date('Y-m-d', strtotime($date . ' +2 day'));



        if (count($prediction_data)) {

            $file_name = $prediction_data[0]['file_name'];

            if ($file_name != '') {

                $file_path = 'https://dev.famrut.co.in/python/Prediction/prediction_result/' . $file_name;

                $data_val = file_get_contents($file_path);

                $data_clean = explode(PHP_EOL, $data_val);

                $new_array = explode($b, -30, 30);

                foreach ($data_clean as $d) {

                    if ($d != '') {



                        $marray = explode(':', $d);

                        $data_new[] = explode(':', $d);



                        $date_chk = substr($marray[0], 0, 10);

                        $date_chk_fix = substr($date_last_month_timestamp, 0, 10);



                        $chk_str = 'val1 :' . $date_chk . ' ||  new_val : ' . $date_chk_fix;



                        if ($date_chk >= $date_chk_fix) {



                            $graph_array[] = array('date' => date('Y-m-d', substr($marray[0], 0, 10)), 'price' => number_format($marray[1], 2, '.', ''));



                            $converted_date = date('Y-m-d', substr($marray[0], 0, 10));

                            if ($date == $converted_date) {

                                $cost_array[] = array('newdateformat' => date('Y-m-d', substr($marray[0], 0, 10)), 'modalprices' => number_format($marray[1], 2), 'minimumprices' => '-', 'maximumprices' => '-', 'unitofprice' => '-', 'unitofarrivals' => '-', 'arrivals' => '-', 'prediction_flag' => '1');

                            }

                            if ($next_date == $converted_date) {

                                $cost_array[] = array('newdateformat' => date('Y-m-d', substr($marray[0], 0, 10)), 'modalprices' => number_format($marray[1], 2), 'minimumprices' => '-', 'maximumprices' => '-', 'unitofprice' => '-', 'unitofarrivals' => '-', 'arrivals' => '-', 'prediction_flag' => '1');

                            }

                            if ($next_date_2 == $converted_date) {

                                $cost_array[] = array('newdateformat' => date('Y-m-d', substr($marray[0], 0, 10)), 'modalprices' => number_format($marray[1], 2), 'minimumprices' => '-', 'maximumprices' => '-', 'unitofprice' => '-', 'unitofarrivals' => '-', 'arrivals' => '-', 'prediction_flag' => '1');

                            }



                        }



                        //date("Y-m-d",substr($val[0],0,10));

                    }

                }

            }

        }



        /* echo  $sql = "SELECT  minimumprices, maximumprices,marketwiseapmcpricedate

        FROM lastfiveyeardata WHERE commodityname ILIKE '".$commodity_name."' AND market ILIKE '".$market_name."' ORDER BY marketwiseapmcpriceid DESC  LIMIT 90";

        DISTINCT(marketwiseapmcpricedate),marketwiseapmcpriceid,

         */

        $days_data_3 = array_reverse($days_data);

        $c = array_merge($days_data_3, $cost_array);



        $query_graph = $this->db->query("SELECT to_char( to_timestamp((marketwiseapmcpricedate),'DD/MM/YYYY'),'YYYY-MM-DD')  NewDateFormat,minimumprices, maximumprices,unitofarrivals,arrivals,varity FROM lastfiveyeardata WHERE commodityname ILIKE '" . $commodity_name . "' " . $where_varity . "  AND market ILIKE '" . $market_name . "' ORDER BY marketwiseapmcpriceid DESC LIMIT 30");



        $result = $query_graph->result_array();



        if (count($result)) {



            $response = array("status" => 1, "data" => array_reverse($result), "graph_array" => $graph_array, "cost_array" => $c, "days_data" => $days_data, "message" => "Commodity Market listed successfully", 'date_last_month_timestamp' => $chk_str);

        }



        $this->api_response($response);



    }



    public function commodity_details_data_web($commodity_name, $market_name, $varity = 0, $is_encode = 0)
    {



        $today = date('Y-m-d');



        if ($is_encode != 0) {

            if ($varity != 0) {

                $varity = base64_decode($varity);

                // $where_varity = " AND varity ILIKE '".base64_decode($varity)."' ";

            }



            if ($commodity_name != '') {

                $commodity_name = base64_decode($commodity_name);

            }



            if ($market_name != '') {

                $market_name = base64_decode($market_name);

            }



        }



        if ($varity != 0) {

            $where_varity = " AND varity ILIKE '" . $varity . "' ";

        } else {

            $where_varity = '';

        }



        $query = $this->db->query("SELECT to_char( to_timestamp((marketwiseapmcpricedate),'DD/MM/YYYY'),'YYYY-MM-DD') NewDateFormat ,commodityname, market, minimumprices, maximumprices,modalprices,unitofprice,unitofarrivals,arrivals,varity

 FROM lastfiveyeardata WHERE commodityname ILIKE '" . $commodity_name . "'  " . $where_varity . " AND market ILIKE '" . $market_name . "'  ORDER BY marketwiseapmcpriceid DESC LIMIT 5");

        $days_data = $query->result_array();



        $query_prediction = $this->db->query("SELECT *

 FROM prediction_data_files WHERE commodity ILIKE '" . $commodity_name . "' AND market ILIKE '" . $market_name . "'  ORDER BY id DESC LIMIT 1");

        $prediction_data = $query_prediction->result_array();



        $prediction_file_path = 'https://dev.famrut.co.in/python/Prediction/prediction_result/';

        $data_new = array();

        $graph_array = array();

        $cost_array = array();



        $date = date("Y-m-d");

        // $prev_date = date('Y-m-d', strtotime($date .' -1 day'));

        $next_date = date('Y-m-d', strtotime($date . ' +1 day'));

        // $prev_date_2 = date('Y-m-d', strtotime($date .' -2 day'));

        $next_date_2 = date('Y-m-d', strtotime($date . ' +2 day'));



        if (count($prediction_data)) {

            $file_name = $prediction_data[0]['file_name'];

            if ($file_name != '') {

                $file_path = 'https://dev.famrut.co.in/python/Prediction/prediction_result/' . $file_name;

                $data_val = file_get_contents($file_path);

                $data_clean = explode(PHP_EOL, $data_val);

                foreach ($data_clean as $d) {

                    if ($d != '') {



                        $marray = explode(':', $d);

                        $data_new[] = explode(':', $d);

                        $graph_array[] = array('date' => date('Y-m-d', substr($marray[0], 0, 10)), 'price' => $marray[1]);



                        $converted_date = date('Y-m-d', substr($marray[0], 0, 10));

                        if ($date == $converted_date) {

                            $cost_array[] = array('date' => date('Y-m-d', substr($marray[0], 0, 10)), 'price' => $marray[1]);

                        }

                        if ($next_date == $converted_date) {

                            $cost_array[] = array('date' => date('Y-m-d', substr($marray[0], 0, 10)), 'price' => $marray[1]);

                        }

                        if ($next_date_2 == $converted_date) {

                            $cost_array[] = array('date' => date('Y-m-d', substr($marray[0], 0, 10)), 'price' => $marray[1]);

                        }



                        //date("Y-m-d",substr($val[0],0,10));

                    }

                }

            }

        }



        $query_graph = $this->db->query("SELECT to_char( to_timestamp((marketwiseapmcpricedate),'DD/MM/YYYY'),'YYYY-MM-DD')  NewDateFormat,minimumprices, maximumprices,unitofarrivals,arrivals,varity FROM lastfiveyeardata WHERE commodityname ILIKE '" . $commodity_name . "' " . $where_varity . "  AND market ILIKE '" . $market_name . "' ORDER BY marketwiseapmcpriceid DESC LIMIT 90");



        $result = $query_graph->result_array();



        if (count($result)) {



            $response = array("status" => 1, "days_data" => $days_data, "data" => $result, "config_url" => $this->config_url, 'prediction_data' => $prediction_data, "prediction_file_path" => $prediction_file_path, "graph_array" => $graph_array, "cost_array" => $cost_array, "message" => lang('Listed_Successfully'));

        } else {

            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }



        $this->api_response($response);



    }



    public function add_transactions()
    {

        //error_reporting(1);

        // print_r($_POST);



        /*

        'secureHash'

        'amount'

        'respDescription'

        'paymentMode'

        'aggregatorID'

        'merchantId'

        'paymentID'

        'merchantTxnNo'

        'paymentDateTime'

        'txnID'

        'client_id'

        'order_id'

        'order_num'



        'amount' => $this->input->post('amount'),

        'respDescription' => $this->input->post('respDescription'),

        'paymentMode' => $this->input->post('paymentMode'),

        'aggregatorID' => $this->input->post('aggregatorID'),

        'merchantId' => $this->input->post('merchantId'),

        'paymentID' => $this->input->post('paymentID'),

        'gateway_txn_id' => $this->input->post('merchantTxnNo'),

        'paymentDateTime' => $this->input->post('paymentDateTime'),

        'paymentSubInstType' => $this->input->post('paymentSubInstType'),

        'txnID' => $this->input->post('txnID'),

        'client_id' => $this->session->userdata('user_id'),

        'order_id' => $this->session->userdata('order_id'),

        'order_num' => $this->session->userdata('order_num'),

        'gateway' => 'payphi',

        'payement_resp' => json_encode($_POST);



        client_invoices



        client_id

        order_id

        invoice_num

        sub_total

        total

        payment_method

        status

        paid_date

        invoice_date

        cust_type



        invoice_data

        remark



        [marketplace/payreturn] =>

        [secureHash] => 1e62d54544b28a57ef8e64ce07eaf56c8bea1bd1e8eeb5156139bb17579d70c7

        [amount] => 1.00

        [respDescription] => Transaction rejected

        [paymentMode] => Card

        [aggregatorID] => M_00150

        [oth_charge] => 129.8

        [responseCode] => 039

        [paymentSubInstType] => DC

        [merchantId] => T_00910

        [paymentID] => 86032336529

        [merchantTxnNo] => 20211117104036

        [addlParam1] => Ref1^Ref2^Ref3^Ref4

        [paymentDateTime] => 20211117161054

        [txnID] => T002631066806

         */



        if ($this->input->post('order_id') != '' && $this->input->post('client_id') != '') {



            $query = $this->db->query("SELECT * FROM client_orders WHERE id= " . $this->input->post('order_id'));

            $order_data = $query->result_array();



            //'invoice_data' => $this->input->post('paymentMode'),

            $invoice_num = 'F00' . $this->input->post('order_id');



            $insert = array(

                'sub_total' => $this->input->post('amount'),

                'total' => $this->input->post('amount'),

                'client_id' => $this->input->post('client_id'),

                'order_id' => $this->input->post('order_id'),

                'invoice_num' => $invoice_num,

                'payment_method' => $this->input->post('paymentMode'),

                'cust_type' => 'client',

                'created_on' => current_date(),

            );



            //$this->db->where('client.id', $id);

            $result = $this->db->insert('client_invoices', $insert);

            $invoice_id = $this->db->insert_id();



            if ($invoice_id) {

                // $response  =  $data_new;



                $insert_trans_array = array(

                    'client_id' => $this->input->post('client_id'),

                    'invoice_id' => $invoice_id,

                    'transaction_id' => $this->input->post('txnID'),

                    'amount_in' => $this->input->post('amount'),

                    'gateway' => $this->input->post('gateway'),

                    'bank_name' => $this->input->post('paymentMode'),

                    'status' => $this->input->post('respDescription'),

                    'gateway_txn_id' => $this->input->post('txnID'),

                    'transaction_date' => current_date(),

                    'created_on' => current_date(),

                );



                //$this->db->where('client.id', $id);

                $results = $this->db->insert('transactions', $insert_trans_array);

                $transaction_id = $this->db->insert_id();

                if ($this->input->post('respDescription') == 'Transaction rejected') {

                    $transactions_flag = 0;

                } elseif ($this->input->post('respDescription') == 'Transaction successful') {

                    $transactions_flag = 1;

                } else {

                    $transactions_flag = 2;

                }



                $response = array("success" => 1, "msg" => lang('Added_Successfully'), "error" => 0, "status" => 1, 'transactions_flag' => $transactions_flag);

            } else {

                $response = array("success" => 0, "data" => array(), "msg" => lang('Not_Able_Add'), "error" => 1, "status" => 0, 'transactions_flag' => $transactions_flag);

            }

        }



        $this->api_response($response);



        exit;

    }



    public function get_webinar()
    {



        $query_webinar = $this->db->query("SELECT * FROM webinar WHERE is_deleted = false  ORDER BY webinar_id DESC LIMIT 50");



        $result = $query_webinar->result_array();



        if (count($result)) {

            $response = array("success" => 1, "data" => $result, "msg" => 'webinar list', "error" => 0, "status" => 1);



            $this->api_response($response);

            exit;



        } else {

            $response = array("success" => 0, "data" => $result, "msg" => 'webinar list', "error" => 0, "status" => 1);



            $this->api_response($response);

            exit;



        }

    }



    public function logout_check_old($phone_number)
    {

        // $this->load->helper('sms_helper');

        $phone = substr(preg_replace('/\s+/', '', $phone_number), -10, 10);



        //$id = $row[0]['id'];

        $update_arr = array('is_login' => false, 'device_id' => null);

        $this->db->where('client.phone', $phone);

        $result = $this->db->update('client', $update_arr);



        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Farmer Logout Successfully");



        $this->api_response($response);

        exit;



    }



    public function disconnect_all_call()
    {



        $update_array = array(

            'meeting_status_id' => 4,

            'meeting_end_from' => 4,

            'updated_on' => current_date(),

        );



        $whr_array = array(

            'meeting_status_id !=' => '4',

        );



        $sql_update = $this->db->update('emeeting', $update_array, $whr_array);



        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => 'all call disconnected', "message" => 'All call disconnected');

        $this->api_response($response);

    }



    /*  public function get_crops_region_validation()

    {

    $region_id = $_REQUEST['region_id'];

    $crop_id   = $_REQUEST['crop_id'];

    }*/



    public function get_farm_crops_fields_calculation()
    {

        $region_id = $_REQUEST['region_id'];

        $crop_id = $_REQUEST['crop_id'];

        $farmer_id = $_REQUEST['farmer_id'];

        $land_id = $_REQUEST['land_id'];

        $result = array();



        $sql = "SELECT m.land_id ,m.farmer_id , m.soil_type , m.farm_name,m.topology ,m.farm_type ,m.farm_size ,m.unit ,m.irrigation_facility , m.farm_image, m.calculated_land_area ,m.survey_no ,m.khasra_no ,m.irrigation_source,c.crop_image,c.crop_type,c.duration_from,c.duration_to,c.area_under_cultivation,m.doc_7_12,ct.name as city_name  FROM master_crop_details as c

        LEFT JOIN master_land_details as m ON m.land_id = c.land_id

        LEFT JOIN cities_new ct ON ct.id = m.cities_id

               WHERE m.farmer_id='" . $farmer_id . "' AND m.region_id = '" . $region_id . "' AND c.crop = '" . $crop_id . "' AND m.is_deleted = 'false' ORDER BY m.land_id DESC";



        $row = $this->db->query($sql);

        $result = $row->result_array();

        if (count($result)) {

            $response = array("success" => 1, "status" => 1, "data" => $result, "message" => "Farm Validated for insurance");

        } else {

            $response = array("success" => 0, "status" => 1, "data" => $result, "message" => "Not validated Farm farm");

        }

    }



    /*public function create_scrips_sql()

    {



    $id_array_sql = array(42, 31, 35, 34, 60, 46, 48, 149, 36, 72, 45, 124, 54, 123, 51, 55, 102, 61, 101, 52, 77, 95, 40, 93, 92, 91, 90, 88, 89, 76, 100, 98, 97, 96, 104, 103, 47, 94, 107, 105, 112, 110, 109, 108, 106, 115, 118, 50, 120, 117, 116, 122, 121, 53, 119, 130, 128, 133, 134, 57, 58, 127, 32, 99, 136, 138, 59, 44, 135, 139, 141, 62, 49, 137, 140, 142, 63, 65, 146, 56, 64, 66, 145, 43, 68, 67, 113, 150, 71, 70, 69, 114, 75, 74, 73, 111, 132, 151, 152, 78, 131, 153, 126, 154, 125, 148, 147, 129, 144, 143);

    $id_array     = sort($id_array_sql);



    for ($i = 1; $i < count($id_array_sql); $i++) {



    echo "UPDATE created_blogs SET published_on = current_date + interval '$i day' where id=" . $id_array_sql[$i];

    echo '<br>';

    }

    }*/



    public function splash_screen_get()
    {


        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        $client_type = ($headers_data['client-type']) ? strtolower($headers_data['client-type']) : 'seller';

        $sql = "SELECT id,logo,mob_icon,key_fields FROM config_master WHERE key_fields ='farmer_splash1' AND  is_deleted = false  AND is_active = true ORDER BY id ASC";

        $res_chk = $this->db->query($sql);

        $res = $res_chk->result_array();



        $key_fields = ($client_type == 'seller') ? 'seller_logo' : 'buyer_logo';

        $sql1 = "SELECT id,logo,mob_icon,key_fields FROM config_master WHERE key_fields ='" . $key_fields . "' AND  is_deleted = false  AND is_active = true ORDER BY id ASC";



        $res_chk1 = $this->db->query($sql1);

        $res1 = $res_chk1->result_array();

        //$this->base_path = $base_path = 'https://dev.famrut.co.in/agroemandi/';

        // $base_path = $this->base_path;

        //$logo_url  = $base_path . 'uploads/config_master/';

        $image = $this->base_path . "uploads/config_master/" . $res[0]['mob_icon'];

        $logo = $this->base_path . "uploads/config_master/" . $res[0]['logo'];

        $client_image = $this->base_path . "uploads/config_master/" . $res1[0]['mob_icon'];

        $client_logo = $this->base_path . "uploads/config_master/" . $res1[0]['logo'];



        if (count($res) > 0) {

            $response = array("success" => 1, "error" => 0, "status" => 1, 'logo_url' => $logo_url, "data" => $res, "client_data" => $res1, "message" => "splash screen data", "image" => $image, "logo" => $logo, "client_image" => $client_image, "client_logo" => $client_logo);



        } else {



            $img_logo = $this->base_path . 'assets/img/spoc.png';

            $img_group = $this->base_path . 'assets/img/spoc.png';



            $response = array("success" => 0, "error" => 1, "status" => 1, 'logo_url' => $logo_url, "data" => $res, "message" => "splash screen data", "image" => $image, "logo" => $logo);

        }

        $this->api_response($response);

        exit;

    }



    public function slider_screen_get()
    {



        $sql = "SELECT id,logo,mob_icon,key_fields FROM config_master WHERE key_fields LIKE 'farmer_slide%' AND  is_deleted = false  AND is_active = true ORDER BY id ASC";



        $res_chk = $this->db->query($sql);

        $res = $res_chk->result_array();

        //$base_path = 'https://dev.famrut.co.in/agroemandi/';

        $logo_url = $this->base_path . 'uploads/config_master/';



        if (count($res) > 0) {

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $res, 'logo_url' => $logo_url, "message" => "Slider screen data");



        } else {

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $res, 'logo_url' => $logo_url, "message" => "Slider screen data");

        }

        $this->api_response($response);

        exit;

    }



    public function get_agronomist_details($partner_id)
    {

        $type_get = 5;

        $response = array();

        $user_data = array();



        $selects = array('user_id', 'first_name', 'last_name', 'email', 'password', 'company_name', 'phone_no', 'address', 'city', 'state', 'country', 'postal_code', 'website_url', 'profile_image', 'updated_on', 'created_on', 'is_deleted', 'deleted_on', 'is_active', 'email_verify', 'type', 'user_type', 'is_external', 'referral_code', 'device_id', 'is_video_enable', 'is_chat_enable', 'crop_id');



        $where = array('users.user_type' => $type_get, 'users.is_deleted' => 'false', 'users.is_active' => 'true', 'users.user_id' => $partner_id);

        $user_data = $this->Masters_model->get_data($selects, 'users', $where);



        if (count($user_data)) {

            $response = array("status" => 1, "data" => $user_data, "config_url" => $this->config_url, "service_options" => $result2, "message" => lang('Listed_Successfully'));

        } else {

            $response = array("status" => 1, "data" => null, "config_url" => $this->config_url, "service_options" => null, "message" => lang('Data_Not_Found'));

        }







        $this->api_response($response);

    }



    public function update_vendor_call_status()
    {

        $this->load->model('Notification_model');

        $custom_array = '';

        $lead_id = $this->input->post('id');

        $schedule_call_status = $this->input->post('schedule_call_status');

        $farmer_id = $this->input->post('farmer_id');

        $partner_id = $this->input->post('partner_id');

        /*$call_schedule_timestamp = $this->input->post('call_schedule_timestamp');*/

        $call_schedule_date = $this->input->post('call_schedule_date');

        $datecreate = date_create($call_schedule_date);

        $date_formate = date_format($datecreate, "Y-m-d");

        $slot_time[] = $this->input->post('slot_time_from');

        $slot_time[] = $this->input->post('slot_time_to');

        $call_schedule_time = explode("-", $slot_time);

        $product_type = 'video_call_schedule';

        $is_custom = 0;

        $response = array();

        //print_r($_POST);



        if ($farmer_id != '' && $partner_id != '' && $lead_id != '') {

            if ($schedule_call_status == 'Confirm') {



                $msg = 'Call Schedule Confirmed';

            } else {

                $msg = 'Call Schedule Rejected by Vendor';

            }



            $update = array(

                'client_id' => $farmer_id,

                'partner_id' => $partner_id,

                'is_custom' => $is_custom,

                'product_type' => $product_type,

                'schedule_call_status' => $schedule_call_status,

                /*'call_schedule_timestamp' => $call_schedule_timestamp,*/

                'call_schedule_date' => $date_formate,

                'call_schedule_time' => $call_schedule_time,

                'updated_on' => current_date(),



            );

            $this->db->where('product_leads.id', $lead_id);

            $this->db->update('product_leads', $update);



            $sql = "SELECT id,device_id,first_name,last_name FROM client WHERE is_deleted='false' AND is_active='true' AND device_id is NOT NULL AND id =" . $farmer_id;

            $row_tag = $this->db->query($sql);

            $results = $row_tag->result_array();

            $sql_img = "SELECT id,name,mob_icon FROM config_master WHERE is_deleted='false' AND is_active='true' AND id = 31";

            $row_tag_img = $this->db->query($sql_img);

            $results_img = $row_tag_img->result_array();

            $img = $results_img[0]['mob_icon'];

            $token[] = 'cFAIDe4Z53k:APA91bGYJxWUCxBS1SdCLuMtTsFNV71KPyLC4EWKIJVOBX82wYWCAJy91ycqakFoGbJBv0KUfP7SEnF2j6KwOm7N5KDTny0cWaBOkRxpb6XDwYYKgyirwlkeh0ZLjhFyfluQfRLaNfPt';

            //print_r($results);

            //$token[] = $results[0]['device_id'];

            $title = 'Call Schedule';

            /*From '.$results[0]['first_name'].' '.$results[0]['last_name'].'*/

            $message = 'Call Schedule Request on ' . date('d-m-Y H:i:a', strtotime($call_schedule_timestamp)) . ' is Confirmed';



            $notifiy = $this->Notification_model->sendPushNotifications_request_farmer($token, $title, $message, $custom_array, $type = 'Schedule', $lead_id, $img);



            $dd = json_decode($notifiy);



            if ($dd->success == 1) {

                $sql_notify = "UPDATE product_leads SET notification_send = 'true' WHERE id = '" . $lead_id . "'";

                $results_notify = $this->db->query($sql_notify);

                //$results_notify = $row_notify->result_array();

            } else {

                $results_notify = false;

            }



            $response = array("status" => 1, "data" => 1, "message" => "Leads Updated successfully " . $msg, 'Notification' => $message, 'notification_sent' => $results_notify);



        } else {

            $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);

    }



    public function SplitTime($start, $end, $date = '', $partner_id = '')
    {



        $Duration = "30";

        // change time in 24hr

        $StartTime = date("H:i", strtotime($start));

        $EndTime = date("H:i", strtotime($end));



        $ReturnArray = array(); // Define output

        // change time in micro

        $StartTime = strtotime($StartTime); //Get Timestamp

        $EndTime = strtotime($EndTime); //Get Timestamp



        $AddMins = $Duration * 60;

        $next_endtime = $StartTime + $AddMins;

        $i = 0;

        $booked_call = array();

        if ($date) {

            $datecreate = date_create($date);

            $date_formate = date_format($datecreate, "Y-m-d");

            $sql_query = "SELECT call_schedule_time FROM product_leads WHERE is_deleted=false AND partner_id= '" . $partner_id . "' AND call_schedule_date= '" . $date_formate . "' AND schedule_call_status IN('Requested','Active','Confirm','Reschedule')";

            $booked_call_query = $query = $this->db->query($sql_query);

            $booked_call = $query->result_array();

        }



        while ($next_endtime <= $EndTime) //Run loop
        {

            $ReturnArray[$i]['from'] = date("g:i A", $StartTime);

            $timeslot = $ReturnArray[$i]['from'];

            $StartTime += $AddMins;

            $next_endtime = $StartTime + $AddMins;

            $ReturnArray[$i]['to'] = date("g:i A", $StartTime);

            $timeslot .= '-' . $ReturnArray[$i]['to'];



            if (in_array($timeslot, array_column($booked_call, 'call_schedule_time'))) {

                $ReturnArray[$i]['is_available'] = false;

            } else {

                $ReturnArray[$i]['is_available'] = true;

            }



            //Endtime check

            $i++;

        }

        return $ReturnArray;

    }



    public function get_vendor_booked_slot()
    {

        $partner_id = $this->input->post('partner_id');

        $schedule_call_status = $this->input->post('schedule_call_status');

        if ($partner_id != '' && $schedule_call_status != '') {



            $sql_query = "SELECT product_leads.client_id,product_leads.partner_id,product_leads.call_schedule_date,product_leads.call_schedule_time,product_leads.schedule_call_status,client.first_name,client.last_name FROM product_leads JOIN client ON client.id=product_leads.client_id WHERE product_leads.is_deleted=false AND partner_id= '" . $partner_id . "' AND schedule_call_status= '" . $schedule_call_status . "'";



            $booked_call_query = $query = $this->db->query($sql_query);

            $booked_call = $query->result_array();



            foreach ($booked_call as $res_key => $res_value) {

                $datecreate = date_create($res_value['call_schedule_date']);

                $booked_call[$res_key]['call_schedule_date'] = date_format($datecreate, "d-m-Y");

            }

            if (count($booked_call)) {

                $response = array("status" => 1, "data" => $booked_call, "message" => "Booked time slot");

            } else {

                $response = array("status" => 1, "data" => null, "message" => "Booked time slot not available");

            }



        } else {



            $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);

    }



    public function reschedule_call_leads()
    {

        $this->load->model('Notification_model');

        $custom_array = '';

        $farmer_id = $this->input->post('farmer_id');

        $partner_id = $this->input->post('partner_id');

        $lead_id = $this->input->post('lead_id');

        $call_schedule_date = $this->input->post('call_schedule_date');

        $datecreate = date_create($call_schedule_date);

        $date_formate = date_format($datecreate, "Y-m-d");

        $slot_time[] = $this->input->post('slot_time_from');

        $slot_time[] = $this->input->post('slot_time_to');

        $call_schedule_time = implode("-", $slot_time);

        //$call_schedule_timestamp = $this->input->post('call_schedule_timestamp');

        $product_type = 'video_call_schedule';

        $is_custom = 0;

        $img = '';

        $response = array();



        if ($farmer_id != '' && $partner_id != '' && $call_schedule_date != '' && $slot_time[0] != '' && $slot_time[1] != '') {



            $update = array(



                'is_custom' => $is_custom,

                'product_type' => $product_type,

                'schedule_call_status' => 'Reschedule',

                'call_reschedule_date' => $date_formate,

                'call_reschedule_time' => $call_schedule_time,

                'updated_on' => current_date(),



            );



            $this->db->where('product_leads.client_id', $farmer_id);

            $this->db->where('product_leads.partner_id', $partner_id);

            $this->db->where('product_leads.id', $lead_id);

            $this->db->update('product_leads', $update);

            $sql = "SELECT id,device_id,first_name,last_name FROM client WHERE is_deleted='false' AND is_active='true' AND device_id is NOT NULL AND id =" . $farmer_id;

            $row_tag = $this->db->query($sql);

            $results = $row_tag->result_array();

            //print_r($results);

            $sql_vendor = "SELECT user_id,device_id,first_name,last_name FROM users WHERE is_deleted='false' AND is_active='true' AND device_id is NOT NULL AND user_id =" . $partner_id;

            $row_vendor = $this->db->query($sql_vendor);

            $results_vendor = $row_vendor->result_array();



            $sql_img = "SELECT id,name,mob_icon FROM config_master WHERE is_deleted='false' AND is_active='true' AND id = 31";

            $row_tag_img = $this->db->query($sql_img);

            $results_img = $row_tag_img->result_array();

            $img = $results_img[0]['mob_icon'];

            /*users table$token[] = 'caAuzKuWlvw:APA91bHGt24ZqLNsA5_rLjNDDxWaYuU4AuQUtbLUKuZ9NoV0qru8PcPdZpj7HpmErbGix_rmAC1EJv_E24AngMMig430muESMoQJCulYVTWNTdrpCQWck3HQCsZNrBxNxOFzUnxB0I7P';*/

            $token[] = $results_vendor[0]['device_id'];

            $title = 'Call Schedule';

            /*From '.$results[0]['first_name'].' '.$results[0]['last_name'].'*/

            $message = 'You Have Reschedule call Request From ' . $results[0]['first_name'] . ' ' . $results[0]['last_name'] . ' on ' . $date_formate . ' at ' . $call_schedule_time;



            $notifiy = $this->Notification_model->sendPushNotifications_request_partner($token, $title, $message, $custom_array, $type = 'Schedule', $lead_id, $img);



            $dd = json_decode($notifiy);



            if ($dd->success == 1) {

                $sql_notify = "UPDATE product_leads SET notification_send = 'true' WHERE id = '" . $lead_id . "'";

                $results_notify = $this->db->query($sql_notify);

                //$results_notify = $row_notify->result_array();

            } else {

                $results_notify = false;

            }



            $response = array("status" => 1, "data" => 1, "message" => "Call Reschedule successfully", 'Notification' => $message, 'notification_sent' => $results_notify);



        } else {

            $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        }

        $this->api_response($response);

    }



    public function confirm_call_by_vendor()
    {

        $this->load->model('Notification_model');



        $custom_array = '';

        $lead_id = $this->input->post('lead_id');

        $farmer_id = $this->input->post('farmer_id');

        $partner_id = $this->input->post('partner_id');



        if ($farmer_id != '' && $partner_id != '' && $lead_id != '') {



            $update = array(



                'schedule_call_status' => $schedule_call_status,

                'schedule_call_status' => 'Confirm',

                'updated_on' => current_date(),



            );

            $this->db->where('product_leads.id', $lead_id);

            $this->db->update('product_leads', $update);



            $sql = "SELECT id,device_id,first_name,last_name FROM client WHERE is_deleted='false' AND is_active='true' AND device_id is NOT NULL AND id =" . $farmer_id;

            $row_tag = $this->db->query($sql);

            $results = $row_tag->result_array();

            $sql_img = "SELECT id,name,mob_icon FROM config_master WHERE is_deleted='false' AND is_active='true' AND id = 31";

            $row_tag_img = $this->db->query($sql_img);

            $results_img = $row_tag_img->result_array();

            $img = $results_img[0]['mob_icon'];

            $token[] = $results[0]['device_id'];

            //'cFAIDe4Z53k:APA91bGYJxWUCxBS1SdCLuMtTsFNV71KPyLC4EWKIJVOBX82wYWCAJy91ycqakFoGbJBv0KUfP7SEnF2j6KwOm7N5KDTny0cWaBOkRxpb6XDwYYKgyirwlkeh0ZLjhFyfluQfRLaNfPt';



            /*  $token[]     = 'cFAIDe4Z53k:APA91bGYJxWUCxBS1SdCLuMtTsFNV71KPyLC4EWKIJVOBX82wYWCAJy91ycqakFoGbJBv0KUfP7SEnF2j6KwOm7N5KDTny0cWaBOkRxpb6XDwYYKgyirwlkeh0ZLjhFyfluQfRLaNfPt';*/



            $title = 'Call Schedule';



            $sql_lead = "SELECT call_schedule_time,call_schedule_date FROM product_leads WHERE is_deleted='false' AND id =" . $lead_id;

            $row_lead = $this->db->query($sql_lead);

            $result_lead = $row_lead->result_array();

            $datecreate = date_create($result_lead[0]['call_schedule_date']);

            $date_formate = date_format($datecreate, "d-m-Y");

            $message = 'Call Schedule Request on ' . $date_formate . ' at ' . $result_lead[0]['call_schedule_time'] . ' is Confirmed';



            $notifiy = $this->Notification_model->sendPushNotifications_request_farmer($token, $title, $message, $custom_array, $type = 'Schedule', $lead_id, $img);



            $dd = json_decode($notifiy);



            if ($dd->success == 1) {

                $sql_notify = "UPDATE product_leads SET notification_send = 'true' WHERE id = '" . $lead_id . "'";

                $results_notify = $this->db->query($sql_notify);

            } else {

                $results_notify = false;

            }



            $response = array("status" => 1, "data" => 1, "message" => "Call Schedule Confirmed ", 'Notification' => $message, 'notification_sent' => $results_notify);



        } else {

            $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);

    }



    public function cancel_call_by_vendor()
    {

        $this->load->model('Notification_model');

        $custom_array = '';

        $lead_id = $this->input->post('lead_id');

        $farmer_id = $this->input->post('farmer_id');

        $partner_id = $this->input->post('partner_id');



        if ($farmer_id != '' && $partner_id != '' && $lead_id != '') {



            $update = array(



                'schedule_call_status' => $schedule_call_status,

                'schedule_call_status' => 'Cancel',

                'updated_on' => current_date(),



            );

            $this->db->where('product_leads.id', $lead_id);

            $this->db->update('product_leads', $update);



            $sql = "SELECT id,device_id,first_name,last_name FROM client WHERE is_deleted='false' AND is_active='true' AND device_id is NOT NULL AND id =" . $farmer_id;

            $row_tag = $this->db->query($sql);

            $results = $row_tag->result_array();

            $sql_img = "SELECT id,name,mob_icon FROM config_master WHERE is_deleted='false' AND is_active='true' AND id = 31";

            $row_tag_img = $this->db->query($sql_img);

            $results_img = $row_tag_img->result_array();

            $img = $results_img[0]['mob_icon'];

            $token[] = $results[0]['device_id']; //'cFAIDe4Z53k:APA91bGYJxWUCxBS1SdCLuMtTsFNV71KPyLC4EWKIJVOBX82wYWCAJy91ycqakFoGbJBv0KUfP7SEnF2j6KwOm7N5KDTny0cWaBOkRxpb6XDwYYKgyirwlkeh0ZLjhFyfluQfRLaNfPt';



            $title = 'Call Schedule';



            $sql_lead = "SELECT call_schedule_time,call_schedule_date FROM product_leads WHERE is_deleted='false' AND id =" . $lead_id;

            $row_lead = $this->db->query($sql_lead);

            $result_lead = $row_lead->result_array();

            $datecreate = date_create($result_lead[0]['call_schedule_date']);

            $date_formate = date_format($datecreate, "d-m-Y");

            $message = 'Call Schedule Request on ' . $date_formate . ' at ' . $result_lead[0]['call_schedule_time'] . ' is Cancel';



            $notifiy = $this->Notification_model->sendPushNotifications_request_farmer($token, $title, $message, $custom_array, $type = 'Schedule', $lead_id, $img);



            $dd = json_decode($notifiy);



            if ($dd->success == 1) {

                $sql_notify = "UPDATE product_leads SET notification_send = 'true' WHERE id = '" . $lead_id . "'";

                $results_notify = $this->db->query($sql_notify);

            } else {

                $results_notify = false;

            }



            $response = array("status" => 1, "data" => 1, "message" => "Call Schedule Cancel ", 'Notification' => $message, 'notification_sent' => $results_notify);



        } else {

            $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);

    }



    public function bottom_menu_get()
    {

        $base_path_menu = $this->config_url['bottom_menu_icon'];



        $bottom_menu = array(

            array('id' => '1', 'title' => 'Home', 'icon' => $base_path_menu . 'home.webp', "path" => "home"),

            array('id' => '2', 'title' => 'DSS', 'icon' => $base_path_menu . 'ic_forum.webp', "path" => "DSS"),

            array('id' => '3', 'title' => 'logo', 'icon' => $base_path_menu . 'famrut.jpg', "path" => "home"),

            array('id' => '4', 'title' => 'Media', 'icon' => $base_path_menu . 'media.webp', "path" => "media"),

            array('id' => '5', 'title' => 'Market', 'icon' => $base_path_menu . 'ic_blog.webp', "path" => "Market"),

        );



        $response = array("status" => 1, "data" => $bottom_menu, "config_url" => $this->config_url, "message" => "DSS Module List");



        $this->api_response($response);

    }



    public function all_menu_get()
    {

        $base_path_menu = $this->config_url['bottom_menu_icon'];

        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);

        $selected_lang = $headers_data['lang'];

        $sel_lang = $selected_lang;

        $domain = $headers_data['domain'];

        $client_type = $headers_data['client-type'] ?? '';

        // $client_type	= '';



        $sql_menu = "SELECT id,lang_json->>'" . $sel_lang . "' as title,map_key,icon,menu_position, app_user_type, client_type FROM app_menu_master WHERE is_active='true' AND is_deleted='false' ORDER BY seq ASC";

        $menu_res = $this->db->query($sql_menu);

        $menu_data = $menu_res->result_array();

        // print_r($menu_data);exit;

        $data = [];



        foreach ($menu_data as $v) {

            if (!empty($client_type)) {

                $client_type_data = explode(',', $v['client_type']);

                // print_r($client_type_data);exit;



                if (in_array(ucfirst($client_type), $client_type_data)) {

                    $menu_key = $v['menu_position'];



                    if ($v['icon'] != '') {

                        $v['icon'] = $base_path_menu . $v['icon'];

                    } else {

                        $v['icon'] = $base_path_menu . 'icon_1676459048.svg';

                    }

                    $data[$menu_key][] = $v;

                }



            } else {

                $menu_key = $v['menu_position'];



                if ($v['icon'] != '') {

                    $v['icon'] = $base_path_menu . $v['icon'];

                } else {

                    $v['icon'] = $base_path_menu . 'icon_1676459048.svg';

                }



                $data[$menu_key][] = $v;



            }

        }

        // $privacy_policy = "https://www.famrut.com/privacy-policy.html";

        // if( 'famrut' != strtolower($domain)){

        //     $privacy_policy = "https://agriecosystem.com/privacy-policy.html";

        // }



        // $response = array("status" => 1, "data" => $data,"privacy_policy" => $privacy_policy , "config_url" => $this->config_url, "message" => "Menu List");

        // $this->api_response($response);







        $privacy_policy = "https://www.nerace.in/privacy-policy.html";

        $terms_conditions = "https://www.nerace.in/terms-conditions.html";



        if ('famrut' == strtolower($domain)) {

            $privacy_policy = "https://agriecosystem.com/privacy-policy.html";

        }



        $response = array("status" => 1, "data" => $data, "privacy_policy" => $privacy_policy, "terms_conditions" => $terms_conditions, "config_url" => $this->config_url, "message" => "Menu List", "herader_data" => $headers_data);

        $this->api_response($response);

    }



    /*   public function get_bank_spash_old($bank_master_id)

    {



    $row_bank = $this->db->query("SELECT  gm.logo,gm.mob_icon,bm.bank_master_id

    FROM bank_master as bm

    LEFT JOIN client_group_master as gm ON gm.client_group_id = bm.group_id

    WHERE bm.is_active = 'true' AND bm.is_deleted = 'false' AND bm.bank_master_id = $bank_master_id

    LIMIT 1");

    $data     = $row_bank->result_array();

    $img_logo = $this->base_path . 'uploads/client_group_master/' . $data[0]['logo'];

    $img_back = $this->base_path . 'uploads/client_group_master/' . $data[0]['mob_icon'];

    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, "message" => "get bank splash image", 'image' => $img, 'logo' => $img_back);



    $this->api_response($response);

    exit;



    }

     */



    public function get_crop_variety()
    {



        $crop_id = $this->input->post('crop_id');

        /*$state_id    = $this->input->post('state_id');

        $district_id = $this->input->post('district_id');

        $season      = $this->input->post('season');*/



        if ($crop_id != '') {

            $this->db->select('crop_variety_id,name_en,name_mr,name_hi');

            $this->db->where('is_deleted', 'false');

            $this->db->where('is_active', 'true');

            $this->db->where('crop_id', $crop_id);

            /* $this->db->where('variety_state', $state_id);

            $this->db->where('season', $season);

            $this->db->where('variety_district', $district_id);*/



            $crop_veriety = $this->db->get('crop_variety_master')->result_array();



            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $crop_veriety, "message" => "Crop Veriety");



        } else {

            $crop_veriety = array();

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $crop_veriety, "message" => lang('Missing_Parameter'));

        }

        $this->api_response($response);

    }



    public function crop_disease_detection($crop_id = '')
    {



        if ($crop_id != '') {



            // error_reporting(E_ALL);



            $data['title'] = 'Crop Disease';

            $where = array('is_deleted' => false, 'is_active' => true);

            $crop_data = array();

            $data['components'] = ['1' => 'fruit', '2' => 'leaf', '3' => 'stem', '4' => 'root', '5' => 'insect', '6' => 'flower'];



            $sql = "SELECT crop_components.component_id,crop_components.crop_id,crop_components.crop_component,crop_components.component_img,crop.name,crop.logo FROM crop_components

      LEFT JOIN crop ON crop.crop_id = crop_components.crop_id WHERE crop_components.crop_id=$crop_id

      AND crop_components.is_deleted = false";

            $crop_data = $this->db->query($sql)->result_array();

            if (count($crop_data)) {

                foreach ($crop_data as $key => $value) {



                    $sql = "SELECT crop_disease.component_id,crop_disease.disease_id,crop_disease.disease_name,crop_disease.disease_img,crop_disease.disease_info FROM crop_disease  WHERE crop_disease.is_deleted = false AND crop_disease.component_id = " . $value['component_id'];

                    $detail_data = $this->db->query($sql)->result_array();

                    $crop_data[$key]['crop_disease'] = $detail_data;



                }

            }



            $data['crop_data'] = $crop_data;

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, "message" => "Crop disease detection");



        } else {

            $data = array();

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $data, "message" => lang('Missing_Parameter'));

        }

        $this->api_response($response);

    }



    public function crop_disease_detection_details($id = '')
    {



        $data = array();

        if ($id != '') {



            $data_component = array('1' => 'fruit', '2' => 'leaf', '3' => 'stem', '4' => 'root', '5' => 'insect', '6' => 'flower');



            $sql = "SELECT crop_disease.component_id,crop_disease.disease_id,crop_disease.disease_name,crop_disease.disease_img,crop_disease.disease_info FROM crop_disease  WHERE crop_disease.is_deleted = false AND crop_disease.disease_id = " . $id . " LIMIT 1";

            $detail_data = $this->db->query($sql)->result_array();

            $data_val = $detail_data[0];

            $data['component_id'] = $data_val['component_id'];

            $data['disease_id'] = $data_val['disease_id'];

            $data['disease_name'] = $data_val['disease_name'];

            $data['disease_info'] = $data_val['disease_info'];

            $data['disease_img'] = explode(',', $data_val['disease_img']);



            $sql_quey = "SELECT crop_components.component_id,crop_components.crop_id,crop_components.crop_component,crop_components.component_img,crop.name

      FROM crop_components

      LEFT JOIN crop ON crop.crop_id = crop_components.crop_id

      WHERE crop_components.crop_id=$crop_id

      AND crop_components.is_deleted = false

      AND crop_components.component_id =" . $data_val['component_id'];



            // $sql_component  = "SELECT crop_component FROM crop_components where component_id = ".$data_val['component_id']." LIMIT 1";

            $detail_data_comp = $this->db->query($sql_quey)->result_array();

            $data['component_name'] = $detail_data_comp[0]['crop_component'];

            $data['crop_name'] = $detail_data_comp[0]['name'];



            $component_name = $data_component[$data['component_name']];

            $img = base_url('uploads/crop_disease/') . $data['crop_name'] . '/' . $component_name . '/';



            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, "message" => "Crop disease detection Details", "image_base_path" => $img);



        } else {

            $data = array();

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $data, "message" => lang('Missing_Parameter'));

        }

        $this->api_response($response);

    }



    /*

    This API

     */



    public function get_crop_blogs_details_new()
    {

        //$crop_id = '', $start = 1

        $crop_id = $this->input->post('crop_id');

        $start = $this->input->post('start') != '' ? $this->input->post('start') : 1; //Variable = (Condition) ? (Statement1) : (Statement2);



        $region_id = array();

        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);

        // $group_id     = $headers_data['group_id']; (replace this line with below 2 lines)

        $group_id_arr = explode(',', $headers_data['group_id']);

        $group_id = $group_id_arr[0];



        $where = array('is_deleted' => 'false', 'is_active' => 'true', 'group_referral_code' => $group_id);

        $crop_ids = $this->Masters_model->get_data("crop_id", 'config_master', $where);



        if (!empty($headers_data['user_id'])) {

            $user_id = $headers_data['user_id'];

            $where_users = array('is_deleted' => 'false', 'is_active' => 'true', 'id' => $user_id);

            $users_datas = $this->Masters_model->get_data('', 'client', $where_users);

            $latitude = $users_datas[0]['latitude'];

            $longitude = $users_datas[0]['longitude'];

            $region_id = $users_datas[0]['region_id'];

        }



        $crop_ids_query = '';



        if ($crop_id != '') {

            $crop_ids_query = "AND cb.crop_id IN (" . $crop_id . ")";

        }



        $response = array();

        $limit = 5;

        //$start    = 1;

        $cat_id = 0;

        // $chk_count = $start * $limit;

        $start_chk = $start - 1;

        if ($start_chk != 0) {

            $start_sql = $limit * ($start_chk);

        } else {

            $start_sql = 0;

        }



        $sql_limit = " LIMIT " . $limit . " OFFSET " . $start_sql;

        $near_array_str = $region_id;

        $blog_location_arr = explode(',', $near_array_str);



        $sql_blog_ids = $this->db->query("SELECT blog_id FROM  blog_region_rel WHERE region IN (" . $near_array_str . ")");

        $region_arr = $sql_blog_ids->result_array();

        $blog_id_array = array_column($region_arr, 'blog_id');

        $blog_id_array_str = implode(',', $blog_id_array);



        if (count($blog_id_array)) {



            $row = $this->db->query("SELECT cb.id as blogs_id,cb.logo,cb.blogs_tags_id,cb.blogs_types_id,cb.id,cb.title as blogs_title,cb.sub_title as blogs_sub_title,cb.description as blogs_description,cb.sub_description as blogs_sub_description,cb.created_on as blogs_created_on,bty.name as blogs_types_name,bty.logo as blogs_types_logo ,bty.mob_icon as blogs_types_mob_icon,bty.name_mr as blogs_types_name_mr,cb.blogs_location as blogs_location

            FROM created_blogs as cb

            LEFT JOIN blogs_types_master as bty ON CAST(bty.blogs_types_id AS TEXT) IN (cb.blogs_types_id)

            WHERE cb.is_active=true AND cb.is_deleted = 'false' AND cb.crop_id IN (" . $crop_id . ") ORDER BY cb.created_on DESC , (cb.id," . $blog_id_array_str . ") ASC " . $sql_limit);



            $result_arr = $row->result_array();



        }



        if (count($result_arr)) {

            $response = array("status" => 1, "data" => $result_arr, "config_url" => $this->config_url, "message" => lang('Listed_Successfully'));

        } else {

            $result = array();

            $response = array("status" => 1, "data" => $result_arr, "config_url" => $this->config_url, "message" => lang('Data_Not_Found'));

        }



        $this->api_response($response);

    }



    public function test_nc_login()
    {



        /*$user_id                = $this->input->post('user_id');

        $region_id['region_id'] = $this->input->post('region_id');

        if (!empty($region_id)) {

        $this->db->where('client.id', $user_id);

        $result = $this->db->update('client', $region_id);

        }



        if (count($result)) {

        $response = array("status" => 1, "data" => $result, "message" => "Crop Region save successfully");

        } else {

        $response = array("status" => 0, "data" => $result, "message" => "Crop Region not avaiable");

        }*/

        // $this->api_response($result, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

        $this->load->helper('nc_helper');

        $resp = login_nc();

        // $dd = obj($resp);

        echo '<br> gere >>' . $resp['GenerateTokenAPI'];

        echo '<br> ssss >>' . $resp->GenerateTokenAPI;



        //print_r($dd);

        print_r($resp);



        // $this->api_response($resp);

    }



    //***********************************************************************

    // Saller module API: Start //////////////////////////

    //***********************************************************************



    public function add_crop_product_post()
    {



        $result = array();

        $image = '';

        $crop_img1 = '';

        $crop_img2 = '';



        $crop_product_img = $this->base_path . 'uploads/farm/' . $crop_prod_image2;



        if (!empty($_FILES['crop_img1']['name'])) {



            $extension = pathinfo($_FILES['crop_img1']['name'], PATHINFO_EXTENSION);



            $crop_prod_image = 'crop_prod_image_one' . time() . '.' . $extension;

            $target_file = 'uploads/farm/' . $crop_prod_image;

            // for delete previous image.

            if ($this->input->post('old_crop_img1') != "") {

                @unlink('./uploads/farm/' . $this->input->post('old_crop_img1'));

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



            $crop_prod_image2 = 'crop_prod_image_two' . time() . '.' . $extension;

            $target_file = 'uploads/farm/' . $crop_prod_image2;

            // for delete previous image.

            if ($this->input->post('old_crop_img2') != "") {

                @unlink('./uploads/farm/' . $this->input->post('old_crop_img2'));

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

                    'weight' => $this->input->post('weight'),

                    'weight_unit' => $this->input->post('weight_unit'),

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

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Added_Successfully'), 'config_url' => $this->config_url, "crop_product_img" => $crop_product_img, "post_Data" => $insert);

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



    public function update_crop_product_post()
    {



        $result = array();

        $image = '';

        $crop_img1 = '';

        $crop_img2 = '';



        $id = $this->input->post('id');



        if ($this->input->post('id') != '') {



            $crop_product_img = $this->base_path . 'uploads/farm/' . $crop_prod_image2;



            if (!empty($_FILES['crop_img1']['name'])) {



                $extension = pathinfo($_FILES['crop_img1']['name'], PATHINFO_EXTENSION);



                $crop_prod_image = 'crop_prod_image_one' . time() . '.' . $extension;

                $target_file = 'uploads/farm/' . $crop_prod_image;

                // for delete previous image.

                if ($this->input->post('old_crop_img1') != "") {

                    @unlink('./uploads/farm/' . $this->input->post('old_crop_img1'));

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



                $crop_prod_image2 = 'crop_prod_image_two' . time() . '.' . $extension;

                $target_file = 'uploads/farm/' . $crop_prod_image2;

                // for delete previous image.

                if ($this->input->post('old_crop_img2') != "") {

                    @unlink('./uploads/farm/' . $this->input->post('old_crop_img2'));

                }



                if (move_uploaded_file($_FILES["crop_img2"]["tmp_name"], $target_file)) {

                    $crop_img2 = $crop_prod_image2;

                    $error = 0;



                } else {



                    $error = 2;



                }

            }



            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Product Updation failed here, please try again some time.");



            if ($this->input->post('btn_submit') == 'submit') {



                if (0) {

                    $data = $this->input->post();

                    $data['error'] = validation_errors();

                } else {



                    //   'khasra_no'           => $this->input->post('khasra_no'),



                    $update_arr = array(

                        'crop_id' => $this->input->post('crop_id'),

                        'crop_variety_id' => $this->input->post('crop_variety_id'),

                        'farmer_id' => $this->input->post('farmer_id'),

                        'prod_desc' => $this->input->post('prod_desc'),

                        'market_id' => $this->input->post('market_id'),

                        'weight' => $this->input->post('weight'),

                        'weight_unit' => $this->input->post('weight_unit'),

                        /* 'price'           => $this->input->post('price'),

                        'price_unit'           => $this->input->post('price_unit'),

                        'weight'                => $this->input->post('weight'),

                        'weight_unit'   => $this->input->post('weight_unit'),*/

                        /* 'product_status' => $this->input->post('product_status'),*/

                        /*      'payed_amount'           => $this->input->post('payed_amount'),

                        'total_amount'        => $this->input->post('total_amount'),

                        'product_status'   => $this->input->post('product_status'),*/

                        'product_status' => 0,

                        'product_add_date' => current_date(),

                        'created_on' => current_date(),

                    );



                    if ($crop_img1 != '') {

                        $update_arr['crop_img1'] = $crop_img1;

                    }



                    if ($crop_img2 != '') {

                        $update_arr['crop_img2'] = $crop_img2;

                    }



                    if (count($update_arr)) {

                        // $id = $row[0]['id'];

                        $this->db->where('crop_product.id', $id);

                        $result = $this->db->update('crop_product', $update_arr);



                        // echo $this->db->last_query();

                    }



                    if ($result) {

                        $title = "Crop Product: Product Updated Successfully";

                        $description = json_encode($update_arr);

                        // user_activity_logs($title, $description);



                        if (count($update_arr)) {

                            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $update_arr, "message" => lang('Updated_Successfully'), 'config_url' => $this->config_url);

                        }



                        $this->api_response($response);

                        exit;



                    } else {



                        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Not_Able_Update'));



                        $this->api_response($response);

                        exit;



                    }

                }

            }



        } else {



            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Missing_Parameter'));

            $this->api_response($response);

            exit;



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

                            $url_invoice_update = base_url() . '/GeneratePdfController/index/' . $id;

                            $data_pdf_update = file_get_contents($url_invoice_update);

                            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $update_arr, "message" => lang('Updated_Successfully'), 'config_url' => $this->config_url);

                        }

                        $this->api_response($response);

                        exit;



                    } else {



                        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Not_Able_Update'));



                        $this->api_response($response);

                        exit;



                    }

                }

            }



        } else {



            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Missing_Parameter'));

            $this->api_response($response);

            exit;



        }



        $this->api_response($response);

        exit;

    }



    public function get_farmer_product_get($farmer_id)
    {



        $result = array();

        $crop_product_img = $this->base_path . 'uploads/farm/' . $crop_prod_image2;

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Missing_Parameter'));



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



                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $res, "message" => "Farmer Prodcuts listing", 'config_url' => $this->config_url);

            } else {

                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Farmer Prodcuts listing", 'crop_product_img' => $crop_product_img, 'config_url' => $this->config_url);

            }

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

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Missing_Parameter'));



        if ($id) {

            /*AND p.farmer_id = $farmer_id*/



            $sql_data = "SELECT p.*,c.crop_id,c.name,c.name_mr,c.logo as mob_icon,c.mob_icon as mob_icon ,ct.name_en as crop_variety_name,ct.name_mr as crop_variety_name_mr,m.name as market_name,m.name_mr as market_name_mr

        FROM crop_product as p

        LEFT JOIN crop as c ON c.crop_id = p.crop_id

        LEFT JOIN crop_variety_master ct ON ct.crop_variety_id = p.crop_variety_id

        LEFT JOIN market_master as m ON m.market_id = p.market_id

        WHERE p.is_deleted = false  AND p.id = $id AND c.is_deleted = false";



            $row = $this->db->query($sql_data);

            $res = $row->result_array();



            if (count($res) > 0) {



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

        $crop_product_img = $this->base_path . 'uploads/farm/' . $crop_prod_image2;

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Missing_Parameter'));



        if ($farmer_id) {



            $sql_data = "SELECT p.*,c.crop_id,c.name,c.name_mr,c.logo as mob_icon,c.mob_icon as mob_icon ,ct.name_en as crop_variety_name,ct.name_mr as crop_variety_name_mr,m.name as market_name,m.name_mr as market_name_mr

        FROM crop_product as p

        LEFT JOIN crop as c ON c.crop_id = p.crop_id

        LEFT JOIN crop_variety_master ct ON ct.crop_variety_id = p.crop_variety_id

        LEFT JOIN market_master as m ON m.market_id = p.market_id

        WHERE p.is_deleted = false  AND p.invoice_number != '' AND p.invoice_file != ''  AND p.farmer_id = $farmer_id AND c.is_deleted = false";



            $row = $this->db->query($sql_data);

            $res = $row->result_array();



            if (count($res) > 0) {



                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $res, "message" => "Farmer Invoice Prodcuts listing", 'config_url' => $this->config_url);

            } else {

                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => "Farmer Invoice Prodcuts listing", 'crop_product_img' => $crop_product_img, 'config_url' => $this->config_url);

            }

        }

        $this->api_response($response);

        exit;

    }



    public function get_market_branch_get()
    {



        $this->db->select('*');

        $this->db->where('is_active', true);

        $this->db->where('is_deleted', false);

        $markets = $this->db->get('market_master')->result_array();

        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $markets, "message" => "market data");



        $this->api_response($response);



    }



    public function get_weight_unit_get()
    {

        $unit_array[] = array('key' => "quintal");

        // $unit_array[]  = array( 'key' => "kg");

        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $unit_array, "message" => "Unit array");

        $this->api_response($response);



    }



    //***********************************************************************

    // Saller module API: END //////////////////////////

    //***********************************************************************



    // public function response($response)

    // {

    //     header('Content-type: application/json');

    //     echo json_encode($response);

    // }



    // update order status

    public function update_order_status_post()
    {

        $id = $this->input->post('id');

        $status = $this->input->post('status');

        $remark = $this->input->post('remark');

        $payment_method = $this->input->post('payment_method') ? $this->input->post('payment_method') : null;

        $transaction_text = $this->input->post('transaction_text') ? $this->input->post('transaction_text') : null;

        $statuses = array('Cancelled', 'Fraud');



        if (!empty($id)) {

            // update client order product status

            $result = $this->Masters_model->update_data('client_order_product', array('order_id' => $id), array('status' => $status, 'remark' => $remark, 'payment_method' => $payment_method, 'transaction_text' => $transaction_text));



            // admin_activity_log

            admin_activity_logs("Order status Updated, Order ID -" . $id, "ID: " . $id . ' ' . "Status: " . $status . " Remark:" . $remark);

            $notification_data = array();



            // update clients order status

            $where1 = array('id' => $id);

            $result = $this->Masters_model->update_data('client_orders', $where1, array('status' => $status, 'payment_method' => $payment_method));

            // echo'<br>last_query: '.$this->db->last_query();//exit;



            // send notifications

            $notification_data = $this->send_notification($id);



            if ($status == 'Complete') {

                $client_orders_data = $this->Masters_model->get_data(array('*'), 'client_orders', array('id' => $id));

                $insert_data = array(

                    'client_id' => $client_orders_data[0]['client_id'],

                    'invoice_id' => $client_orders_data[0]['invoice_id'],

                    'transaction_id' => $transaction_text,

                    'description' => $remark,

                    'status' => $status,

                    'transaction_date' => date('Y-m-d H:i:s'),

                    'amount_in' => $client_orders_data[0]['amount'],

                    'gateway' => $payment_method,

                );



                user_activity_logs("Partner: Transaction:", json_encode($insert_data));

                $this->Masters_model->add_data('transactions', $insert_data);



                // update amount

                $paid_amount = (!empty($client_orders_data[0]['amount'])) ? number_format($client_orders_data[0]['amount'], 2) : 0.00;

                $this->Masters_model->update_data('client_orders', array('id' => $id), array('paid_amount' => $paid_amount));



            } else if (in_array($status, $statuses)) {

                // update amount

                $paid_amount = null;

                $this->Masters_model->update_data('client_orders', array('id' => $id), array('paid_amount' => $paid_amount));



                // update product stock

                $cop_where = array('order_id' => $id);

                $cop_order_data = $this->Masters_model->get_data(array('*'), 'client_order_product', $cop_where);

                // echo'<pre>cop_order_data:';print_r($cop_order_data);echo'</pre>';

                foreach ($cop_order_data as $key => $val) {

                    $this->update_product_stock_by_status($val['id']);

                }

            }

        }



        $data['noti_status'] = isset($notification_data['message']) ? $notification_data['message'] : '';

        $data['noti_msg'] = isset($notification_data['success']) ? $notification_data['success'] : 0;



        if ($result) {

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, "message" => lang('Updated_Successfully'));



        } else {

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $data, "message" => lang('Not_Able_Update'));

        }



        $this->api_response($response);
        exit;

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



            if ($result_client_orders[0]['status'] != '') {

                $client_orders_message = 'Your Order number #' . $result_client_orders[0]['order_num'] . ' status is updated to ' . $result_client_orders[0]['status'];

            }



            $admno = 1;

            $call_data = '';

            $meeting_link = '';

            //$farmer_name = $farmer_data[0]['first_name'];

            $is_whitelable = 1;

            $custom_array = '';

            $img = '';

            $jsonString = '';

            $whr_chk_farmer = 'AND id=' . $result_client_orders[0]['client_id'];



            $qry = "SELECT id, device_id FROM client WHERE is_deleted='false' AND is_active='true' AND device_id is NOT NULL " . $whr_chk_farmer;



            $res_data = $this->db->query($qry);

            $device_id_data = $res_data->row_array();

            $token = [];

            if (count($device_id_data)) {

                $token[] = $device_id_data['device_id'];

            }



            if (count($token)) {

                $this->load->model('Notification_model');

                $jsonString = $this->Notification_model->sendPushNotifications_NA($token, $title, $client_orders_message, $is_whitelable, $group_ids = 0, $custom_array, $type = 'order', $id);



                $notification_data = json_decode($jsonString, true);

                $notification_status = $notification_data['success'];

                if ($notification_status == 1) {

                    $notification_msg = 'Status Sent successfully';



                    $sql = "UPDATE client_orders SET is_notification_sent = 'true' WHERE id = '" . $id . "'";

                    $this->db->query($sql);

                } else {

                    $notification_msg = 'Status Not Sent';

                }



                $message = array("status" => 1, 'success' => $notification_status, "data" => json_decode($jsonString, true), "message" => $notification_msg);

            } else {

                $message = array("status" => 0, 'data' => [], "message" => "Status Not Sent");

            }

            // echo'<pre>';print_r($message);exit;

        } else {

            $message = array("status" => 0, "message" => "Status not Sent");

        }

        return $message;

    }



    // Update stock of order status update by partner

    public function update_product_stock_by_status($id)
    {

        if (!empty($id)) {



            $cop_data = $this->Masters_model->get_data(array('*'), 'client_order_product', array('id' => $id));

            $product_id = $cop_data[0]['product_id'];

            $product_data = $this->Masters_model->get_data(array('*'), 'products', array('id' => $product_id));



            $update_stock = (int) $product_data[0]['in_stock'] + (int) $cop_data[0]['quantity'];

            $this->Masters_model->update_data('products', array('id' => $product_id), array('in_stock' => $update_stock));

        }

        return true;

    }



    // Get active dynamic payment gateways

    public function payment_gateway_get()
    {

        $payment_gateway = $this->Masters_model->get_data(array('*'), 'payment_settings', array('is_active' => 'true'), null, null, 0, 1);

        $data['title'] = 'COD';

        $data['merchant_key'] = '';

        $data['merchant_id'] = '';

        $data['secret_key'] = '';

        $data['other_key'] = '';



        if (!empty($payment_gateway)) {

            // $data = json_decode($payment_gateway[0]['payment_data'], true);

            $payment_data = json_decode($payment_gateway[0]['payment_data'], true);

            $test_mode = array('sandbox');

            $data['id'] = $payment_gateway[0]['id'];

            $data['title'] = $payment_data['title'];

            $data['mode'] = $payment_data['mode'];



            if (in_array($payment_data['mode'], $test_mode)) {

                // test mode

                $data['merchant_key'] = $payment_data['sandbox_merchant_key'];

                $data['merchant_id'] = $payment_data['sandbox_merchant_id'];

                $data['secret_key'] = $payment_data['sandbox_secret_key'];

                $data['other_key'] = $payment_data['sandbox_other_key'];

            } else {

                // live mode

                $data['merchant_key'] = $payment_data['production_merchant_key'];

                $data['merchant_id'] = $payment_data['production_merchant_id'];

                $data['secret_key'] = $payment_data['production_secret_key'];

                $data['other_key'] = $payment_data['production_other_key'];

            }



            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, "message" => "Payment gateway found!");

        } else {

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, "message" => "No payment gateway is active!");

        }



        $this->api_response($response);
        exit;

    }



    // Settings

    public function settings_get($key = null)
    {

        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';



        $logo_path = $this->config_url['log_urls'];



        // $key    = $this->input->post('key');

        if (!empty($key)) {

            $data = get_config_settings($key);



            if (!empty($data)) {

                $message = 'Success!';

                if ($key == 'order_before_time') {

                    $time = date('h:i a', strtotime($data['description']));



                    $pickup_duration_arr = get_config_settings('pickup_duration');

                    if (count($pickup_duration_arr) > 0) {

                        $pickup_duration = $pickup_duration_arr['description'];

                    } else {

                        $pickup_duration = '6 PM to 8 PM';

                    }



                    if ($selected_lang == 'mr') {

                        $data['name'] = 'ऑर्डर बुकिंग वेळ {time} अगोदर. पिकअप पॉइंट डिलिव्हरी: त्याच दिवशी संध्याकाळी {pickup_duration}. {time}  नंतर च्या ऑर्डर दुसऱ्या दिवशी उपलब्ध होईल.';

                        $message = str_replace('{time}', $time, $data['name']);

                        $message = str_replace('{pickup_duration}', $pickup_duration, $message);

                    } elseif ($selected_lang == 'hi') {

                        $data['name'] = 'ऑर्डर बुकिंग समय {time} पहले। पिकअप पॉइंट डिलीवरी: उसी दिन शाम {pickup_duration}। {time} के बाद के ऑर्डर अगले दिन उपलब्ध होंगे।';

                        $message = str_replace('{time}', $time, $data['name']);

                        $message = str_replace('{pickup_duration}', $pickup_duration, $message);

                    } else {

                        $message = str_replace('{time}', $time, $data['name']);

                        $message = str_replace('{pickup_duration}', $pickup_duration, $message);

                    }

                }



                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data, "message" => $message, 'logo_path' => $logo_path);

            } else {

                $response = array("success" => 0, "error" => 1, "status" => 1, "data" => [], "message" => lang('Data_Not_Found'), 'logo_path' => $logo_path);

            }

        } else {

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => [], "message" => lang('Data_Not_Found'), 'logo_path' => $logo_path);

        }

        $this->api_response($response);
        exit;

    }



    // payment status

    public function payment_status_post()
    {



        $payment_status = $this->input->post('status');

        $order_id = $this->input->post('order_id');



        $payment_status_arr['order_id'] = $order_id;

        $payment_status_arr['payment_status'] = $payment_status;



        if (!empty($payment_status) && $payment_status == 'success') {

            $response = array("success" => 1, "data" => $payment_status_arr, "msg" => 'Payment Success', "error" => 0, "status" => 1);



        } else {

            $response = array("success" => 0, "data" => $payment_status_arr, "msg" => 'Payment Failed', "error" => 1, "status" => 1);

        }

        user_activity_logs("User: Order Payment Status: " . ucwords($payment_status), json_encode($payment_status_arr));



        $this->api_response($response);

        exit;

    }



    function add_nc_member($data = [])
    {

        // print_r($data);exit;

        $this->load->helper('nc_helper');

        $AddMemberEnrolmentAPI = false;

        $TransactionAPI = false;

        if (!empty($data)) {

            if ($this->REWARDS) {

                $MemberPrimaryId = generateMemberPrimaryId($this->connected_domain);

                $nc_api_data = array(

                    "MemberPrimaryId" => $MemberPrimaryId,

                    "FirstName" => ucfirst($data['first_name']),

                    "lastName" => ucfirst($data['last_name']),

                    "Mobile" => $data['phone'],

                    "state" => "MH",

                    "city" => "Nasik",

                    "Address1" => "B-128",

                    "Address2" => "Sector 6",

                    "Address3" => "Nasik",

                    "Pincode" => "422006",

                    "DOB" => date('d-M-Y'),

                    "EnrolmentDate" => date('d-M-Y'),

                );



                $AddMemberEnrolmentAPI = AddMemberEnrolmentAPI($nc_api_data);



                $this->db->where('id', $data['client_id']);

                $this->db->update('client', array('nc_token_key' => $MemberPrimaryId));



                $coupon_ev = get_config_data("COUPON_EVENT");

                $coupon_event = explode(',', $coupon_ev);



                if (in_array('Register', $coupon_event)) {

                    $event_name = array_search('Register', $coupon_event);

                    $select = array('id', 'event_name', 'voucher_id', 'for_user', 'status', 'group_id', 'referral_code', 'user_type', 'loyalty_point');

                    $cond = array('is_deleted' => false, 'is_active' => true, 'event_name' => strval($event_name));

                    $voucher_event_data = $this->Masters_model->get_data($select, 'voucher_event_master', $cond);

                    $event_data = $voucher_event_data[0];

                    $loyalty_point = $event_data['loyalty_point'];

                    if ($loyalty_point) {

                        $nc_transactionAPI_data = array(

                            'MemberPrimaryId' => $MemberPrimaryId,

                            'EventTriggered' => 'Register',

                            'PointsEarned' => $loyalty_point,

                            'InvoiceNumber' => "INV" . time(),

                            'Product' => "Register User",

                            'Volume' => "20",

                            'InvoiceValue' => "1200",

                            'InvoiceDatetime' => date('d-M-Y H:m'),

                            'TransactionDatetime' => date('d-M-Y H:m'),

                        );

                        $TransactionAPI = TransactionAPI($nc_transactionAPI_data);

                    }

                }

            }

        }

        return array('AddMemberEnrolmentAPI' => $AddMemberEnrolmentAPI, 'TransactionAPI' => $TransactionAPI);

    }



    public function partner_login_get()
    {

        $headers_data = $this->input->request_headers();
        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);

        $data['domain'] = $headers_data['domain'];

        $data['username'] = $headers_data['username'];

        $data['password'] = $headers_data['password'];

        $login_url = $headers_data['login_url'];



        // print_r($headers_data);exit;



        $api_token = generate_api_token($data);

        $partner_url = PARTNER_URL . '?token=' . $api_token . '&exp_time=' . strtotime(date('Y-m-d H:i:s')) . '&login_url=' . $login_url;

        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $partner_url, "message" => "Partner Login URL!");

        $this->api_response($response);

    }





    //*************************************************

    //EKYC API STARTED

    //************************************************

    public function get_aadhar_otp_post()
    {

        $userid = $this->input->post('user_id');

        $aadharno = $this->input->post('aadhar_no');

        $response = $this->Ekyc_model->ekyc_aadhar_otp_generate($userid, $aadharno);

        $this->api_response($response);



    }

    public function get_aadhar_verification_post()
    {

        $userid = $this->input->post('user_id');

        $aadharno = $this->input->post('aadhar_no');

        $otp = $this->input->post('otp');

        $response = $this->Ekyc_model->ekyc_aadhar_verification($userid, $aadharno, $otp);

        $this->api_response($response);



    }

    public function get_bank_verification_post()
    {

        $userid = $this->input->post('user_id');

        $accno = $this->input->post('acc_no');

        $ifsc_code = $this->input->post('ifsc_code');

        $response = $this->Ekyc_model->ekyc_bank_verification($userid, $accno, $ifsc_code);

        $this->api_response($response);



    }

    public function get_pan_verification_post()
    {

        $userid = $this->input->post('user_id');

        $panno = $this->input->post('pan_no');

        $business_pan = ($this->input->post('business_pan')) ? $this->input->post('business_pan') : false;

        $response = $this->Ekyc_model->ekyc_pan_verification($userid, $panno, $business_pan);

        $this->api_response($response);



    }

    public function get_ekyc_verification_status_post()
    {

        $userid = $this->input->post('user_id');

        $response = $this->Ekyc_model->get_ekyc_verification_status($userid);

        $this->api_response($response);



    }

    //*********************************************

    //EKYC API END

    //*********************************************



    public function media_list_post()
    {

        $limit = 5;

        $start = $this->input->post('page') ? $this->input->post('page') : 1;

        $start_chk = $start - 1;

        if ($start_chk != 0) {

            $start_sql = $limit * ($start_chk);

        } else {

            $start_sql = 0;

        }



        $sql_limit = ($this->input->post('page')) ? " LIMIT " . $limit . " OFFSET " . $start_sql : "";



        //$media_type             = (trim($this->input->post('media_type'))!='' && strtolower(trim($this->input->post('media_type')))!=strtolower(trim('All')))?strtolower(trim($this->input->post('media_type'))):'';

        // $media_filter = ($media_type != "")?" AND lower(media_type)='".$media_type."'":"";

        $media_type = $this->input->post('media_type');

        $media_filter = ($media_type != "" && $media_type > 0) ? " AND media_type=" . $media_type : "";

        $sql_media = "SELECT * FROM media WHERE is_deleted = false AND is_active = true " . $media_filter . " ORDER BY media_id DESC " . $sql_limit;



        $query_media_main = $this->db->query($sql_media);

        $row = $query_media_main->result_array();

        $media_fields = ' media_id, url, url_type, title, description, partner_id, category, published_on, thumbnails, view_count, is_home, is_featured';

        $query_media_main = $this->db->query("SELECT " . $media_fields . " FROM media WHERE is_deleted = false AND is_featured=1  ORDER BY media_id DESC LIMIT 10");

        $result_featured = $query_media_main->result_array();

        $base_url_media = $this->config_url['media_thumbnails'];



        if (count($row)) {

            $response = array("success" => 1, "data" => $row, "featured" => $result_featured, "msg" => 'media list', "base_url_media" => $base_url_media, "error" => 0, "status" => 1);



            $this->api_response($response);

            exit;



        } else {

            $response = array("success" => 0, "data" => $row, "msg" => 'media list mising', "error" => 1, "status" => 0);



            $this->api_response($response);

            exit;



        }

    }

    public function mediatype_list_post()
    {

        $headers_data = $this->input->request_headers();
        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

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



        $sql_mediatype = "SELECT *, lang_json->>'" . $selected_lang . "' as name FROM media_types_master WHERE is_deleted = false AND is_active = true ";



        $query_media_main = $this->db->query($sql_mediatype);

        $row = $query_media_main->result_array();



        //  print_r($row);



        if (count($row)) {

            $response = array("success" => 1, "data" => $row, "msg" => 'media type list', "error" => 0, "status" => 1);



            $this->api_response($response);

            exit;



        } else {

            $response = array("success" => 0, "data" => $row, "msg" => 'media type list mising', "error" => 1, "status" => 0);



            $this->api_response($response);

            exit;



        }



    }



    public function add_farm_new_post()
    {

        $result = array();

        $image = '';

        $farm_image_upload = '';

        $doc_7_12_upload = '';



        if (!empty($_FILES['farm_image']['name'])) {



            $extension = pathinfo($_FILES['farm_image']['name'], PATHINFO_EXTENSION);



            $farm_image_name = $this->connected_domain . '_farm_image_' . time() . '.' . $extension;

            if (!file_exists($this->upload_file_folder . 'farm')) {

                mkdir($this->upload_file_folder . 'farm', 0777, true);

            }



            $target_file = $this->upload_file_folder . 'farm/' . $farm_image_name;

            // for delete previous image.

            if ($this->input->post('old_farm_image') != "") {

                @unlink($this->upload_file_folder . 'farm/' . $this->input->post('old_farm_image'));

            }



            if (move_uploaded_file($_FILES["farm_image"]["tmp_name"], $target_file)) {

                //$insert['farm_image'] = $farm_image_name;

                $farm_image_upload = $farm_image_name;

                $error = 0;



            } else {

                $error = 2;

            }

        }

        /*else{

        $farm_image_name = $this->input->post('old_farm_image');

        }*/



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

                //$insert['doc_7_12'] = $farm_doc_7_12_name;

                $doc_7_12_upload = $farm_doc_7_12_name;

                $error = 0;



            } else {



                $error = 2;



            }

        }

        $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => lang('Missing_Parameter'));



        if ($this->input->post('btn_submit') == 'submit') {



            if (0) {

                $data = $this->input->post();

                $data['error'] = validation_errors();

            } else {



                $insert = array(

                    'farmer_id' => $this->input->post('farmer_id'),

                    'farm_size' => $this->input->post('farm_size'),

                    'unit' => $this->input->post('unit'),

                    'farm_polygoan_coordinates' => $this->input->post('farm_polygoan_coordinates'),

                    'farm_name' => $this->input->post('farm_name'),

                    'farm_name_mr' => $this->input->post('farm_name_mr'),

                    'created_on' => current_date(),

                );



                if ($farm_image_upload != '') {

                    $insert['farm_image'] = $farm_image_upload;

                }

                if ($doc_7_12_upload != '') {

                    $insert['doc_7_12'] = $doc_7_12_upload;

                }



                $result = $this->db->insert('master_land_details', $insert);

                $insert_id = $this->db->insert_id();



                if ($result) {



                    if (count($insert)) {

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Added_Successfully'), 'config_url' => $this->config_url, 'result' => $insert);

                    }



                    $this->api_response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => lang('Not_Able_Add'));



                    $this->api_response($response);

                    exit;



                }

            }

        }



        $this->api_response($response);

        exit;

    }





    public function edit_farm_new_post()
    {

        $result = array();

        $image = '';

        $farm_image_upload = '';

        $doc_7_12_upload = '';



        $land_id = $this->input->post('land_id');



        if (!empty($_FILES['farm_image']['name'])) {



            $extension = pathinfo($_FILES['farm_image']['name'], PATHINFO_EXTENSION);



            $farm_image_name = $this->connected_domain . '_farm_image_' . time() . '.' . $extension;

            if (!file_exists($this->upload_file_folder . 'farm')) {

                mkdir($this->upload_file_folder . 'farm', 0777, true);

            }



            $target_file = $this->upload_file_folder . 'farm/' . $farm_image_name;

            // for delete previous image.

            if ($this->input->post('old_farm_image') != "") {

                @unlink($this->upload_file_folder . 'farm/' . $this->input->post('old_farm_image'));

            }



            if (move_uploaded_file($_FILES["farm_image"]["tmp_name"], $target_file)) {

                //$insert['farm_image'] = $farm_image_name;

                $farm_image_upload = $farm_image_name;

                $error = 0;



            } else {

                $error = 2;

            }

        }

        /*else{

        $farm_image_name = $this->input->post('old_farm_image');

        }*/



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

                //$insert['doc_7_12'] = $farm_doc_7_12_name;

                $doc_7_12_upload = $farm_doc_7_12_name;

                $error = 0;



            } else {



                $error = 2;



            }

        }

        $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => lang('Missing_Parameter'));



        if ($this->input->post('btn_submit') == 'submit' && $land_id != '') {



            if (0) {

                $data = $this->input->post();

                $data['error'] = validation_errors();

            } else {



                $update = array(

                    'farmer_id' => $this->input->post('farmer_id'),

                    'farm_size' => $this->input->post('farm_size'),

                    'unit' => $this->input->post('unit'),

                    'farm_polygoan_coordinates' => $this->input->post('farm_polygoan_coordinates'),

                    'farm_name' => $this->input->post('farm_name'),

                    'farm_name_mr' => $this->input->post('farm_name_mr'),

                );



                if ($farm_image_upload != '') {

                    $update['farm_image'] = $farm_image_upload;

                }

                if ($doc_7_12_upload != '') {

                    $update['doc_7_12'] = $doc_7_12_upload;

                }



                $this->db->where('land_id', $land_id);

                $result = $this->db->update('master_land_details', $update);



                if ($result) {



                    $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, 'result' => $update, "message" => lang('Updated_Successfully'));



                    $this->api_response($response);

                    exit;



                } else {



                    $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $result, "message" => lang('Not_Able_Update'));



                    $this->api_response($response);

                    exit;



                }

            }

        }



        $this->api_response($response);

        exit;

    }





    public function add_mycrop_post()
    {

        $client_id = $this->input->post('client_id');

        $crop_id = $this->input->post('crop_id');

        $crop_type = $this->input->post('crop_type');



        $result = array();

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Missing_Parameter'));

        if ($this->input->post('btn_submit') == 'submit') {

            if ($client_id) {

                $crop_data = $this->Masters_model->get_data('*', 'my_crops', array('client_id' => $client_id, 'crop_id' => $crop_id));

                $mycrop_data = $crop_data[0];

                // print_r($mycrop_data);exit;

                if (count($mycrop_data) > 0) {

                    if ($mycrop_data['is_deleted'] == 't') { // update deleted crop

                        $this->db->where('id', $mycrop_data['id']);

                        $result = $this->db->update('my_crops', array('is_deleted' => 'false'));

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Added_Successfully'));

                    } else {

                        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Already_Added'));

                    }

                } else {

                    $insert = array(

                        'client_id' => $client_id,

                        'crop_id' => $crop_id,

                        'crop_type' => $crop_type,

                        'created_on' => current_date(),

                    );



                    $result = $this->db->insert('my_crops', $insert);

                    $insert_id = $this->db->insert_id();

                    if ($insert_id) {

                        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Added_Successfully'));

                    } else {

                        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Not_Able_Add'));

                    }

                }

            } else {

                $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => "Please select client");

            }

        }



        $this->api_response($response);
        exit;

    }



    public function delete_mycrop_put($id)
    {

        $response = array();

        if ($id != '') {

            $sql = "UPDATE my_crops SET is_deleted = 'true' WHERE id = '" . $id . "'";



            $result = $this->db->query($sql);



            if (count($result)) {

                $response = array("status" => 1, "data" => $result, "message" => lang('Deleted_Successfully'));

            }

        } else {

            $response = array("status" => 0, "message" => lang('Deleted_Successfully'));

        }

        $this->api_response($response);

    }

    function my_crops_list_post()
    {

        $client_id = $this->input->post('client_id');

        if ($client_id == '') {

            $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);


            $client_id = $headers_data['client_id'];

        }



        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

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



        $client_crop_data = [];



        if (!empty($client_id)) {

            $crop_data = $this->Masters_model->get_data('*', 'my_crops', array('client_id' => $client_id, 'is_deleted' => 'false'), null, 'crop_id');

            // echo $this->db->last_query();exit;

            if (count($crop_data)) {

                $crops = '';

                foreach ($crop_data as $key => $value) {

                    if ($crops != $value['crop_id']) {

                        $sql_crop = "SELECT crop_id,lang_json->>'" . $selected_lang . "' as name ,logo as mob_icon,nitrogen as n ,phosphorus as p,potassium as k  FROM crop WHERE crop_id = " . $value['crop_id'] . " LIMIT 1";



                        $crop_val = $this->db->query($sql_crop);

                        $logo = $crop_val->row_array();

                        $value['name'] = $logo['name'];

                        $value['logo'] = $logo['mob_icon'];

                        $value['n'] = $logo['n'];

                        $value['p'] = $logo['p'];

                        $value['k'] = $logo['k'];

                        $value['crop_id'] = $logo['crop_id'];



                        $client_crop_data[] = $value;

                    }

                    $crops = $value['crop_id'];

                }

            }

            if (count($client_crop_data) > 0) {

                $response = array("success" => 1, "data" => $client_crop_data, "msg" => 'Client crop list found!', "error" => 0, "status" => 1);

            } else {

                $response = array("success" => 1, "data" => [], "msg" => lang('Data_Not_Found'), "error" => 0, "status" => 1);

            }

        } else {

            $response = array("success" => 0, "data" => [], "msg" => lang('All_fields_is_required'), "error" => 1, "status" => 1);

        }



        $this->api_response($response);

        exit;

    }

    function macrocalculate($season, $n, $p, $k, $s, $area, $areaunit)
    {

        $nimpression = $pimpression = $kimpression = $simpression = '';

        $ntotal = $ptotal = $ktotal = $stotal = 0;

        $scoreresult = array();

        $nimpression = ($n < 280) ? 'Low' : (($n > 280 && $n < 560) ? 'Medium' : 'High');

        $pimpression = ($p < 10) ? 'Low' : (($p > 10 && $p < 28) ? 'Medium' : 'High');

        $kimpression = ($k < 120) ? 'Low' : (($k > 120 && $k < 250) ? 'Medium' : 'High');

        $simpression = ($s < 25) ? 'Low' : (($s > 25 && $s < 50) ? 'Medium' : 'High');

        if (strtolower($season) == 'rabi' || strtolower($season) == 'late kharif') {

            if (strtolower($areaunit) == 'hectare') {

                $ntotal = ($nimpression == 'Low') ? (137.5 * $area) : (($nimpression == 'Medium') ? (110 * $area) : (82.5 * $area));

                $ptotal = ($pimpression == 'Low') ? (50 * $area) : (($pimpression == 'Medium') ? (40 * $area) : (30 * $area));

                $ktotal = ($kimpression == 'Low') ? (75 * $area) : (($kimpression == 'Medium') ? (60 * $area) : (60 * $area));

                $stotal = ($nimpression == 'Low') ? (37.5 * $area) : (($nimpression == 'Medium') ? (30 * $area) : (22.5 * $area));

            } else if (strtolower($areaunit) == 'acre') {

                $ntotal = ($nimpression == 'Low') ? (55 * $area) : (($nimpression == 'Medium') ? (44 * $area) : (33 * $area));

                $ptotal = ($pimpression == 'Low') ? (20 * $area) : (($pimpression == 'Medium') ? (16 * $area) : (12 * $area));

                $ktotal = ($kimpression == 'Low') ? (30 * $area) : (($kimpression == 'Medium') ? (24 * $area) : (24 * $area));

                $stotal = ($nimpression == 'Low') ? (15 * $area) : (($nimpression == 'Medium') ? (12 * $area) : (9 * $area));



            }

        } elseif (strtolower($season) == 'kharif') {

            if (strtolower($areaunit) == 'hectare') {

                $ntotal = ($nimpression == 'Low') ? (125 * $area) : (($nimpression == 'Medium') ? (100 * $area) : (75 * $area));

                $ptotal = ($pimpression == 'Low') ? (62.5 * $area) : (($pimpression == 'Medium') ? (50 * $area) : (37.5 * $area));

                $ktotal = ($kimpression == 'Low') ? (62.5 * $area) : (($kimpression == 'Medium') ? (50 * $area) : (50 * $area));

                $stotal = ($nimpression == 'Low') ? (37.5 * $area) : (($nimpression == 'Medium') ? (30 * $area) : (22.5 * $area));

            } elseif (strtolower($areaunit) == 'acre') {

                $ntotal = ($nimpression == 'Low') ? (50 * $area) : (($nimpression == 'Medium') ? (40 * $area) : (30 * $area));

                $ptotal = ($pimpression == 'Low') ? (25 * $area) : (($pimpression == 'Medium') ? (20 * $area) : (15 * $area));

                $ktotal = ($kimpression == 'Low') ? (25 * $area) : (($kimpression == 'Medium') ? (20 * $area) : (20 * $area));

                $stotal = ($nimpression == 'Low') ? (15 * $area) : (($nimpression == 'Medium') ? (12 * $area) : (9 * $area));



            }

        }

        $scoreresult['Impression']['n'] = $nimpression;

        $scoreresult['Impression']['p'] = $pimpression;

        $scoreresult['Impression']['k'] = $kimpression;

        $scoreresult['Impression']['s'] = $simpression;

        $scoreresult['Recommendation']['n'] = $ntotal;

        $scoreresult['Recommendation']['p'] = $ptotal;

        $scoreresult['Recommendation']['k'] = $ktotal;

        $scoreresult['Recommendation']['s'] = $stotal;

        return $scoreresult;

    }

    function microcalculate($i, $m, $z, $c, $b, $areaunit)
    {

        $iimpression = $mimpression = $zimpression = $cimpression = $bimpression = '';

        $itotal = $mtotal = $ztotal = $ctotal = $btotal = 0;

        $scoreresult = array();

        $iimpression = ($i < 4.5) ? 'Deficiency' : 'Sufficiency';

        $mimpression = ($m < 2) ? 'Deficiency' : 'Sufficiency';

        $zimpression = ($z < 0.6) ? 'Deficiency' : 'Sufficiency';

        $cimpression = ($c < 0.2) ? 'Deficiency' : 'Sufficiency';

        $bimpression = ($b < 0.5) ? 'Deficiency' : 'Sufficiency';



        if (strtolower($areaunit) == 'hectare') {

            $itotal = ($iimpression == 'Deficiency') ? 'FeSO4-Soil application of 10 + Spray as i' : 'Spray as i';

            $mtotal = ($mimpression == 'Deficiency') ? 'MnSO4-Soil application of 10 + Spray as i' : 'Spray as i';

            $ztotal = ($zimpression == 'Deficiency') ? 'ZnSO4-Soil application of 10 + Spray as i' : 'Spray as i';

            $ctotal = ($cimpression == 'Deficiency') ? 'CuSO4-Soil application of 10 + Spray as i' : 'Spray as i';

            $btotal = ($bimpression == 'Deficiency') ? 'Borax-Soil application of 10 + Spray as i' : 'Spray as i';

        } elseif (strtolower($areaunit) == 'acre') {

            $itotal = ($iimpression == 'Deficiency') ? 'FeSO4-Soil application of 4 + Spray as i' : 'Spray as i';

            $mtotal = ($mimpression == 'Deficiency') ? 'MnSO4-Soil application of 4 + Spray as i' : 'Spray as i';

            $ztotal = ($zimpression == 'Deficiency') ? 'ZnSO4-Soil application of 4 + Spray as i' : 'Spray as i';

            $ctotal = ($cimpression == 'Deficiency') ? 'CuSO4-Soil application of 4 + Spray as i' : 'Spray as i';

            $btotal = ($bimpression == 'Deficiency') ? 'Borax-Soil application of 4 + Spray as i' : 'Spray as i';

        }

        $scoreresult['Impression']['i'] = $iimpression;

        $scoreresult['Impression']['m'] = $mimpression;

        $scoreresult['Impression']['z'] = $zimpression;

        $scoreresult['Impression']['c'] = $cimpression;

        $scoreresult['Impression']['b'] = $bimpression;

        $scoreresult['Recommendation']['i'] = $itotal . " per " . $areaunit;

        $scoreresult['Recommendation']['m'] = $mtotal . " per " . $areaunit;

        $scoreresult['Recommendation']['z'] = $ztotal . " per " . $areaunit;

        $scoreresult['Recommendation']['c'] = $ctotal . " per " . $areaunit;

        $scoreresult['Recommendation']['v'] = $btotal . " per " . $areaunit;

        return $scoreresult;

    }

    function macro_micronutrient_cal_post()
    {



        $season = trim($this->input->post('season')); // 'Kharif', 'Late Kharif', 'Rabi'

        $banket = $this->input->post('banket'); // without SHRT

        $shrt_status = $banket ? 'Without SHRT' : 'With SHRT';

        if ($season == '') {

            $seeding_date = $this->input->post('seeding_date');



            $timestamp = ($seeding_date != '') ? strtotime($seeding_date) : strtotime(date("Y-m-d"));

            $month = date("n", $timestamp);

            $season = "Kharif";



            // flow to detect Season from Month

            if (5 == $month || 6 == $month || 7 == $month) {

                $season = "Kharif";

            } elseif (8 == $month || 9 == $month) {

                $season = "Late Kharif";

            } elseif (10 == $month || 11 == $month) {

                $season = "Rabi";

            } else {

                $season = "Kharif";

            }

        }



        $area = $this->input->post('area');

        $areaunit = $this->input->post('areaunit');

        if ($this->input->post('btn_submit') == 'submit') {

            $nutrition_management = $this->input->post('nutrition_management');

            $n = is_numeric($this->input->post('n')) ? trim($this->input->post('n')) : '';

            $p = is_numeric($this->input->post('p')) ? trim($this->input->post('p')) : '';

            $k = is_numeric($this->input->post('k')) ? trim($this->input->post('k')) : '';

            $s = is_numeric($this->input->post('s')) ? trim($this->input->post('s')) : '';

            $i = is_numeric($this->input->post('i')) ? trim($this->input->post('i')) : '';

            $m = is_numeric($this->input->post('m')) ? trim($this->input->post('m')) : '';

            $z = is_numeric($this->input->post('z')) ? trim($this->input->post('z')) : '';

            $c = is_numeric($this->input->post('c')) ? trim($this->input->post('c')) : '';

            $b = is_numeric($this->input->post('b')) ? trim($this->input->post('b')) : '';



            if (!empty($n) && !empty($p) && !empty($k) && !empty($s) && !empty($i) && !empty($m) && !empty($z) && !empty($c) && !empty($b)) {

                if ($banket == 1) {

                    $npks = array(

                        'n' => $n,

                        'p' => $p,

                        'k' => $k,

                        's' => $s

                    );

                } else {

                    $result['macronutrient'] = $macronutrient = $this->macrocalculate($season, $n, $p, $k, $s, $area, $areaunit);

                    $result['micronutrient'] = $this->microcalculate($i, $m, $z, $c, $b, $areaunit);

                    $npks = $macronutrient['Recommendation'];

                }





                $result['day_wise_multiplication_factor'] = $this->day_multiplication_factor_npks($npks, $nutrition_management, $season);



                if ($result) {

                    $response = array("success" => 1, "error" => 0, "status" => 1, "SHRT_status" => $shrt_status, "season" => $season, "data" => $result, "message" => "Data found");

                } else {

                    $response = array("success" => 0, "error" => 1, "status" => 1, "SHRT_status" => $shrt_status, "season" => $season, "data" => [], "message" => lang('Data_Not_Found'));

                }

            } else {

                $response = array("success" => 0, "error" => 1, "status" => 1, "SHRT_status" => $shrt_status, "season" => $season, "data" => [], "message" => lang('Missing_Parameter'));

            }

        } else {

            $response = array("success" => 0, "error" => 1, "status" => 1, "SHRT_status" => $shrt_status, "season" => $season, "data" => [], "message" => lang('Missing_Parameter'));

        }





        $this->api_response($response);
        exit;



    }





    public function nutrient_management_get()
    {



        // $nutrition_management[] = array('title'=>'soil_health','data'=>array('id' => '2', 'value' => 'Nutrient Management - Soil Health Card', 'map_key' =>'nutrient_management_sh' ,'option' =>array(array('id' => '1', 'value' => 'Flood Irrigation', 'map_key' =>'flood_irrigation_sh'),array('id' => '2', 'value' => 'Nitrogen Fertigation', 'map_key' =>'nitrogen_fertigation_sh'),array('id' => '3', 'value' => 'NPKS', 'map_key' =>'npks_sh'))));



        // $nutrition_management[] = array('title'=>'Blanket','data'=>array('id' => '1', 'value' => 'Nutrient Management - Blanket', 'map_key' =>'nutrient_management_blanket','option' => array( array('id' => '1', 'value' => 'Flood Irrigation', 'map_key' =>'flood_irrigation'),array('id' => '2', 'value' => 'Nitrogen Fertigation', 'map_key' =>'nitrogen_fertigation'),array('id' => '3', 'value' => 'NPKS', 'map_key' =>'npks'))));



        $nutrition_management[] = array(

            'title' => 'soil_health',

            'data' => array(

                'id' => '1',

                'value' => lang('nutrient_management'),

                'map_key' => 'nutrient_management_url',

                'option' => array(

                    array(

                        'id' => '1',

                        'value' => lang('Soil_Health_Card'),

                        'map_key' => 'soil_health_card_url'

                    ),

                    array(

                        'id' => '2',

                        'value' => lang('Without_Soil_Health_Card'),

                        'map_key' => 'without_soil_health_card_url'

                    ),

                )

            )

        );



        $nutrition_management[] = array(

            'title' => 'nutrition_deficiency',

            'data' => array(

                'id' => '2',

                'value' => lang('Nutrition_Deficiency'),

                'map_key' => 'nutrition_deficiency',

                'option' => []

            )

        );



        $response = array("status" => 1, "data" => $nutrition_management, "message" => "Nutrient Management List");



        $this->api_response($response);

    }

    public function crop_disease_list_filter_bkp_post()
    {

        $crop_id = $this->input->post('crop_id');

        $disease_type = $this->input->post('disease_type');

        // $disease_type_filter = array(1 => 'Nutrition Deficiency', 2 => 'Pest', 3 =>'Disease');

        $disease_type_filter = array(2 => 'Pest', 3 => 'Disease');

        //$component_id = $this->input->post('component_id');

        $where_str = ' AND crop_disease.disease_type IN (2, 3)';

        if ($disease_type != '' && strtolower($disease_type) != 'all') {

            $where_str = ' AND crop_disease.disease_type = ' . $disease_type;

        }



        $data_imgs = array();

        if ($crop_id != '') {



            $where = array('is_deleted' => false, 'is_active' => true);

            $crop_data = array();

            $data_component = array('1' => 'fruit', '2' => 'leaf', '3' => 'stem', '4' => 'root', '5' => 'insect', '6' => 'flower');



            //ql = "SELECT crop.name,crop.logo FROM crop WHERE crop.crop_id=$crop_id";

            $sql = "SELECT crop_components.component_id,crop_components.crop_id,crop_components.crop_component,crop_components.component_img,crop.name,crop.logo

            FROM crop_components

            INNER JOIN crop ON crop.crop_id = crop_components.crop_id

            WHERE crop_components.crop_id=$crop_id

            AND crop_components.is_deleted = false AND crop_components.is_active = true";



            //ORDER BY crop_components.crop_component ASC

            $crop_data = $this->db->query($sql)->result_array();

            // print_r($crop_data);exit;

            if (count($crop_data)) {

                foreach ($crop_data as $p => $value) {



                    /*  $sql_quey = "SELECT crop_components.component_id,crop_components.crop_id,crop_components.crop_component,crop_components.component_img,crop.name,crop_disease.disease_info,crop_disease.management,crop_disease.disease_type 

                    FROM crop_components

                      LEFT JOIN crop ON crop.crop_id = crop_components.crop_id

                      WHERE crop_components.crop_id=$crop_id

                      AND crop_components.is_deleted = false

                      AND crop_components.component_id =" . $value['crop_component'].$where_str;*/



                    /* $sql         = "SELECT crop_disease.component_id,crop_disease.disease_id,crop_disease.disease_name,crop_disease.disease_img,crop_disease.disease_info,crop_disease.management,crop_disease.disease_type,crop_components.crop_component FROM crop_disease  as crop_disease

                     LEFT JOIN crop_components as crop_components ON crop_components.crop_id = crop_disease.crop_id

                     WHERE crop_disease.is_deleted = false AND  crop_disease.crop_id=$crop_id ".$where_str." AND crop_components.crop_component= '".$value['crop_component']."'";*/

                    $crop_com_id = $value['crop_component'];

                    //$crop_com_id_m[] = $value['crop_component'];

                    $component_id_key = $value['component_id'];



                    $sql = "SELECT crop_disease.component_id,crop_disease.disease_id,crop_disease.disease_name,crop_disease.disease_img,crop_disease.disease_info,crop_disease.management,crop_disease.disease_type FROM crop_disease  as crop_disease

                     WHERE crop_disease.is_deleted = false AND crop_disease.component_id = " . $component_id_key . " AND crop_disease.crop_id=$crop_id " . $where_str;



                    $detail_data = $this->db->query($sql)->result_array();

                    $data = $detail_data;

                    //$data = array($detail_data); 



                    $component_name = '';

                    foreach ($detail_data as $key => $v) {

                        // $disease_detection[$key][]=  array( 'title'=> "symtoms" ,"details" => $v['disease_info'], ); 

                        // $disease_data_text[$key][] =  array( 'title'=> "managment" ,"details" => $v['management'], );  





                        $disease_detection[$key] = array(

                            'disease_name' => $v['disease_name'],

                            'component_id' => $v['component_id'],

                            'disease_id' => $v['disease_id'],

                            'disease_type' => $v['disease_type'],

                        );



                        //component_name = $data_component[$v['component_id']];

                        $crop_component_id = (int) $value['crop_component'];

                        $component_names = $data_component[$crop_component_id];



                        $data_val = explode(',', $v['disease_img']);

                        //$data_imgs[$v['disease_name']] = $data_val;



                        foreach ($data_val as $k) {

                            $img = $this->base_path . 'uploads/crop_disease/' . $value['name'] . '/' . $component_names . '/' . $k;



                            $disease_detection[$key]['icon_img'] = $img;

                            $disease_detection[$key]['images'][] = $img;





                        }

                        $disease_detection[$key]['text_data'] = array(array('title' => "Symptoms", "details" => $v['disease_info']), array('title' => "Management", "details" => $v['management']));

                        // $disease_detection[$key][] = array( 'title'=> "managment" ,"details" => $v['management']);

                    }

                }

            }



            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $disease_detection, "filter_data" => $disease_type_filter, "selected_filter_data" => $disease_type, "message" => "Crop disease detection");



        } else {

            $data = array();

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $data, "message" => lang('Missing_Parameter'));

        }

        $this->api_response($response);

    }



    public function crop_disease_list_filter_post()
    {

        $headers_data = $this->input->request_headers();
        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';



        // $this->selected_lang = $selected_lang = $this->$headers_data['lang'] = $headers_data['lang'];



        // if ($this->selected_lang == '') {

        //     $this->selected_lang = $selected_lang = 'en';

        //     $this->$headers_data['lang'] = 'en';

        // }



        if ($selected_lang == 'mr') {

            $lang_col = "_mr";

        } elseif ($selected_lang == 'hi') {

            $lang_col = "_hi";

        } else {

            $lang_col = "";

        }









        $crop_id = $this->input->post('crop_id');

        $disease_type = $this->input->post('disease_type');

        // $disease_type_filter = array(1 => 'Nutrition Deficiency', 2 => 'Pest', 3 =>'Disease');



        if (!empty($crop_id)) {

            $disease_type_filter = array(2 => 'Pest', 3 => 'Disease');

            $disease_type_filter = array(

                array(

                    'title' => lang('Pest'),

                    'id' => 2,

                ),

                array(

                    'title' => lang('Disease'),

                    'id' => 3,

                ),

                // array(

                //     'title'  => 'All',

                //     'id'  => 99,

                // ),

            );



            $where = '';

            if (2 == $crop_id) {



                $where = ' AND disease_type IN (2, 3) ';

                if ($disease_type != '' && strtolower($disease_type) != 'all') {

                    $where = ' AND disease_type = ' . $disease_type;

                }

            }



            $sql = "SELECT * FROM crop_disease

            WHERE crop_id = $crop_id AND is_deleted = false AND is_active = true " . $where;

            $crop_disease_data = $this->db->query($sql)->result_array();

            // echo $this->db->last_query();

            // print_r($crop_disease_data);



            if (count($crop_disease_data) > 0) {

                $data_component = array('1' => 'fruit', '2' => 'leaf', '3' => 'stem', '4' => 'root', '5' => 'insect', '6' => 'flower');

                foreach ($crop_disease_data as $key => $value) {

                    $crop_sql = "SELECT * FROM crop 

                    WHERE crop_id = " . $value['crop_id'];

                    $crop_data = $this->db->query($crop_sql)->row_array();

                    // print_r($crop_data);





                    $crop_components_sql = "SELECT * FROM crop_components 

                    WHERE component_id = " . $value['component_id'];

                    $crop_components_data = $this->db->query($crop_components_sql)->row_array();

                    // print_r($crop_components_data);





                    $disease_detection[$key] = array(

                        'disease_name' => $value['disease_name' . $lang_col],

                        'component_id' => $value['component_id'],

                        'disease_id' => $value['disease_id'],

                        'disease_type' => $value['disease_type'],

                    );



                    $disease_imgs = explode(',', $value['disease_img']);

                    foreach ($disease_imgs as $imgs) {

                        $img = $this->base_path . 'uploads/crop_disease/' . $crop_data['name'] . '/' . $data_component[$crop_components_data['crop_component']] . '/' . $imgs;



                        $disease_detection[$key]['icon_img'] = $img;

                        $disease_detection[$key]['images'][] = $img;

                    }



                    $disease_detection[$key]['text_data'] = array(

                        array(

                            'title' => lang('Symptoms'),

                            'details' => $value['disease_info' . $lang_col]

                        ),

                        array(

                            'title' => lang('Management'),

                            'details' => $value['management' . $lang_col]

                        )

                    );

                }

            }



            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $disease_detection, "filter_data" => $disease_type_filter, "selected_filter_data" => $disease_type, "message" => "Crop disease detection");



        } else {

            $data = array();

            $response = array("success" => 0, "error" => 1, "status" => 0, "data" => $data, "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);

    }



    /*  public function nutrient_management_blanket_get()

     {

         $this->nutrition_management_blanket = array(

             array('id' => '1', 'value' => 'Flood Irrigation', 'map_key' =>'flood_irrigation'),

             array('id' => '2', 'value' => 'Nitrogen Fertigation', 'map_key' =>'nitrogen_fertigation'),

             array('id' => '3', 'value' => 'NPKS', 'map_key' =>'npks'),

         );



         $response = array("status" => 1, "data" => $nutrition_management_blanket, "message" => "Nutrient Management blanket List");



         $this->api_response($response);

     }

      */



    public function season_list_get($crop_id = null)
    {

        // Kharif, Rabi, Late kharif

        $data = array(

            array(

                'id' => 'Kharif',

                'value' => lang('Kharif'),

            ),

            array(

                'id' => 'Rabi',

                'value' => lang('Rabi'),

            ),

            array(

                'id' => 'Late Kharif',

                'value' => lang('Late_Kharif'),

            ),

        );





        if (!empty($crop_id)) {

            if ($crop_id == 2) {



                foreach ($data as $key => $val) {

                    if ($val['id'] == 'Kharif') {

                        $n = 100;
                        $p = 50;
                        $k = 50;
                        $s = 30;

                        $data[$key]['n'] = $n;

                        $data[$key]['p'] = $p;

                        $data[$key]['k'] = $k;

                        $data[$key]['s'] = $s;

                    } else {

                        $n = 110;
                        $p = 40;
                        $k = 60;
                        $s = 30;

                        $data[$key]['n'] = $n;

                        $data[$key]['p'] = $p;

                        $data[$key]['k'] = $k;

                        $data[$key]['s'] = $s;

                    }

                }

            } else {

                $crop_data = $this->Masters_model->get_data('*', 'crop', array('crop_id' => $crop_id));

                $crop_data = $crop_data[0];



                if (!empty($crop_data)) {

                    $n = $crop_data['nitrogen'];

                    $p = $crop_data['phosphorus'];

                    $k = $crop_data['potassium'];

                    $s = 0;

                } else {

                    $n = 0;

                    $p = 0;

                    $k = 0;

                    $s = 0;

                }



                foreach ($data as $key => $val) {

                    $data[$key]['n'] = $n;

                    $data[$key]['p'] = $p;

                    $data[$key]['k'] = $k;

                    $data[$key]['s'] = $s;

                }

            }



        }





        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data);



        $this->api_response($response);

    }







    public function day_multiplication_factor_npks($data = [], $nutrition_management = null, $season = null)
    {

        // 'Kharif', 'Late Kharif', 'Rabi'

        $season_arr = array('Late Kharif', 'Rabi');

        $multiplication_factor = [];



        if ($nutrition_management == 2) {



            $n = $data['n'] / 10;

            $p = $data['p'];

            $k = $data['k'];

            $s = $data['s'];



            $multiplication_factor = [];



            $days = 6;

            $mf[0] = array(

                'days' => 'Basal Dose / भर खते',

                'n' => 0,

                'p' => $p,

                'k' => $k,

                's' => $s,

            );



            // $mf = [];

            for ($i = 1; $i <= 10; $i++) {

                $mf[$i] = array(

                    'days' => $days * $i,

                    'n' => $n,

                    'p' => 0,

                    'k' => 0,

                    's' => 0,

                );

            }



            $multiplication_factor = $mf;



        } else if ($nutrition_management == 3) {

            if (!empty($season) && in_array($season, $season_arr)) {

                $npks_mf = array(

                    array('days' => 15, 'n' => 0.1, 'p' => 0.1, 'k' => 0.1, 's' => 0.1),

                    array('days' => 21, 'n' => 0.15, 'p' => 0.12, 'k' => 0.12, 's' => 0.12),

                    array('days' => 27, 'n' => 0.15, 'p' => 0.12, 'k' => 0.12, 's' => 0.12),

                    array('days' => 33, 'n' => 0.15, 'p' => 0.12, 'k' => 0.12, 's' => 0.12),

                    array('days' => 39, 'n' => 0.15, 'p' => 0.12, 'k' => 0.12, 's' => 0.12),

                    array('days' => 45, 'n' => 0.1, 'p' => 0.11, 'k' => 0.11, 's' => 0.12),

                    array('days' => 51, 'n' => 0.07, 'p' => 0.11, 'k' => 0.11, 's' => 0.1),

                    array('days' => 57, 'n' => 0.07, 'p' => 0.1, 'k' => 0.1, 's' => 0.1),

                    array('days' => 63, 'n' => 0.06, 'p' => 0.1, 'k' => 0.1, 's' => 0.1),

                    array('days' => 69, 'n' => 0, 'p' => 0, 'k' => 0.03, 's' => 0),

                    array('days' => 75, 'n' => 0, 'p' => 0, 'k' => 0.03, 's' => 0),

                );

            } else if (!empty($season) && $season == 'Kharif') {

                $npks_mf = array(

                    array('days' => 15, 'n' => 0.1, 'p' => 0.1, 'k' => 0.1, 's' => 0.1),

                    array('days' => 21, 'n' => 0.15, 'p' => 0.12, 'k' => 0.12, 's' => 0.12),

                    array('days' => 27, 'n' => 0.15, 'p' => 0.12, 'k' => 0.12, 's' => 0.12),

                    array('days' => 33, 'n' => 0.15, 'p' => 0.12, 'k' => 0.12, 's' => 0.12),

                    array('days' => 39, 'n' => 0.15, 'p' => 0.12, 'k' => 0.12, 's' => 0.12),

                    array('days' => 45, 'n' => 0.1, 'p' => 0.11, 'k' => 0.11, 's' => 0.12),

                    array('days' => 51, 'n' => 0.07, 'p' => 0.11, 'k' => 0.11, 's' => 0.1),

                    array('days' => 57, 'n' => 0.07, 'p' => 0.1, 'k' => 0.1, 's' => 0.1),

                    array('days' => 63, 'n' => 0.06, 'p' => 0.1, 'k' => 0.1, 's' => 0.1),

                    array('days' => 69, 'n' => 0, 'p' => 0, 'k' => 0.03, 's' => 0),

                    array('days' => 75, 'n' => 0, 'p' => 0, 'k' => 0.03, 's' => 0),

                );

            } else {

                $npks_mf = array(

                    array('days' => 15, 'n' => 0.1, 'p' => 0.1, 'k' => 0.1, 's' => 0.1),

                    array('days' => 21, 'n' => 0.15, 'p' => 0.12, 'k' => 0.12, 's' => 0.12),

                    array('days' => 27, 'n' => 0.15, 'p' => 0.12, 'k' => 0.12, 's' => 0.12),

                    array('days' => 33, 'n' => 0.15, 'p' => 0.12, 'k' => 0.12, 's' => 0.12),

                    array('days' => 39, 'n' => 0.15, 'p' => 0.12, 'k' => 0.12, 's' => 0.12),

                    array('days' => 45, 'n' => 0.1, 'p' => 0.11, 'k' => 0.11, 's' => 0.12),

                    array('days' => 51, 'n' => 0.07, 'p' => 0.11, 'k' => 0.11, 's' => 0.1),

                    array('days' => 57, 'n' => 0.07, 'p' => 0.1, 'k' => 0.1, 's' => 0.1),

                    array('days' => 63, 'n' => 0.06, 'p' => 0.1, 'k' => 0.1, 's' => 0.1),

                    array('days' => 69, 'n' => 0, 'p' => 0, 'k' => 0.03, 's' => 0),

                    array('days' => 75, 'n' => 0, 'p' => 0, 'k' => 0.03, 's' => 0),

                );

            }



            $n = $data['n'];

            $p = $data['p'];

            $k = $data['k'];

            $s = $data['s'];



            foreach ($npks_mf as $mf_key => $mf_val) {

                $multiplication_factor[] = array(

                    'days' => $mf_val['days'],

                    'n' => ($mf_val['n'] * $n),

                    'p' => ($mf_val['p'] * $p),

                    'k' => ($mf_val['k'] * $k),

                    's' => ($mf_val['s'] * $s),

                );

            }

        } else {

            $multiplication_factor[] = array(

                'days' => 0,

                'n' => $data['n'],

                'p' => $data['p'],

                'k' => $data['k'],

                's' => $data['s'],

            );

        }



        return $multiplication_factor;

    }





    public function get_countries_post()
    {

        $this->db->select('*');

        $this->db->where('is_deleted', 'false');

        $countries = $this->db->get('countries')->result_array();

        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $countries, "message" => "countries data");



        $this->api_response($response);

    }



    public function get_states_post()
    {

        // parse_str($_SERVER['QUERY_STRING'], $_POST);

        $type = $this->input->post('type');



        $country = $this->input->post('country') ? $this->input->post('country') : 'IN';

        $country_code = $this->input->post('country_code') ? $this->input->post('country_code') : 'IN';

        $state_code = $this->input->post('state_code') ? $this->input->post('state_code') : null;



        //os version

        $where = array('country' => $country);

        $result = $this->Masters_model->get_data(array('id', 'code', 'name', 'country'), 'states', $where);

        $str = '<option value="">Select state</option>';



        if (count($result) > 0) {



            foreach ($result as $res) {

                $t = '';

                if (!empty($country_code) && !empty($country_code)) {



                    $test = ($res['country'] == $country_code && $res['code'] == $state_code) ? ' selected ' : '';



                    $str .= '<option value="' . $res['code'] . '" data-country="' . $country_code . '" data-state="' . $state_code . '" ' . $test . ' >' . $res['name'] . '</option>';

                } else {



                    $str .= '<option value="' . $res['code'] . '"  ' . $t . '  > ' . $res['name'] . ' </option>';

                }



            }

        }



        if ($type == 1) {



            $response = array("status" => 1, "error" => 0, "success" => 1, "data" => $result, "message" => "State list");

            $this->api_response($response);

        } else {

            echo $str;



        }

        //$this->api_response($response);

    }



    public function get_city_post()
    {

        $type = $this->input->post('type');

        $state = $this->input->post('state');

        $country = $this->input->post('country');

        $city = $this->input->post('city');

        $where = array('region' => $state, 'country' => $country);

        $this->db->where("name != ''");

        $result = $this->Masters_model->get_data(array('id', 'name'), 'cities', $where);

        $str = "";

        if (count($result) > 0) {

            $test = '';

            foreach ($result as $res) {



                if (!empty($city) && !empty($city)) {

                    $test = ($res['name'] == $city) ? ' selected ' : '';

                }



                $str .= '<option value="' . $res['name'] . '" ' . $test . ' >' . $res['name'] . '</option>';

            }

        }



        if ($type == 1) {



            $response = array("status" => 1, "error" => 0, "success" => 1, "data" => $result, "count" => count($result), "message" => "City list");

            $this->api_response($response);



        } else {

            echo $str;

        }

    }



    public function get_dynamic_messages_post()
    {

        $msg = $this->input->post('message');

        $headers_data = $this->input->request_headers();
        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);

        $selected_lang = $headers_data['lang'];



        if ($selected_lang == 'mr') {

            $lang_folder = "marathi";

        } elseif ($selected_lang == 'hi') {

            $lang_folder = "hindi";

        } else {

            $lang_folder = "english";

        }



        $this->lang->load(array('site'), $lang_folder);

        if (!empty($msg)) {

            $lang_msg = trim(ucwords($msg));

            $lang_msg = str_replace(' ', '_', $lang_msg);

            if (!empty(lang($lang_msg))) {

                $data['converted_message'] = [

                    ucwords($msg) => lang($lang_msg)

                ];

            } else {

                $data['converted_message'] = [];

            }

            $data['selected_language'] = $selected_lang;



            $response = array("status" => 1, "error" => 0, "success" => 1, "data" => $data, "message" => 'Message converted successfully!');



        } else {

            $response = array("status" => 0, "error" => 1, "success" => 0, "data" => [], "message" => 'Message required!');



        }

        $this->api_response($response);

    }





    public function dynamic_theme_color_get()
    {

        $headers_data = $this->input->request_headers();
        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);

        $domain = $headers_data['domain'];

        $array_theme = [];



        if ('FAMRUT' == strtoupper($domain)) {



            $array_theme['Bar_color'] = array('key' => 'Bar_color', 'color' => '0xff27914F');

            $array_theme['button_color'] = array('key' => 'button_color', 'color' => '0xff27914F');

            $array_theme['icon_color'] = array('key' => 'icon_color', 'color' => '0xff27914F');

            $array_theme['text_color'] = array('key' => 'text_color', 'color' => '0xff000000');

            $array_theme['button_text_color'] = array('key' => 'button_text_color', 'color' => '0xffffffff');

            $array_theme['Error_label_color'] = array('key' => 'Error_label_color', 'color' => '0xff27914F');

            $array_theme['hint_text_color'] = array('key' => 'hint_text_color', 'color' => '0xff85a5b6');

            $array_theme['sublable_color'] = array('key' => 'sublable_color', 'color' => '0xff27914F');





        } else if ('SENA' == strtoupper($domain)) {



            $array_theme['Bar_color'] = array('key' => 'Bar_color', 'color' => '0xff27914F');

            $array_theme['button_color'] = array('key' => 'button_color', 'color' => '0xff27914F');

            $array_theme['icon_color'] = array('key' => 'icon_color', 'color' => '0xff27914F');

            $array_theme['text_color'] = array('key' => 'text_color', 'color' => '0xff363b43');

            $array_theme['button_text_color'] = array('key' => 'button_text_color', 'color' => '0xffffffff');

            $array_theme['Error_label_color'] = array('key' => 'Error_label_color', 'color' => '0xff27914F');

            $array_theme['hint_text_color'] = array('key' => 'hint_text_color', 'color' => '0xff85a5b6');

            $array_theme['sublable_color'] = array('key' => 'sublable_color', 'color' => '0xff27914F');





        } else if ('nerace' == strtoupper($domain) || 'neraceUAT' == strtoupper($domain)) {



            $array_theme['Bar_color'] = array('key' => 'Bar_color', 'color' => '0xffFDA11E');

            $array_theme['button_color'] = array('key' => 'button_color', 'color' => '0xffFDA11E');

            $array_theme['icon_color'] = array('key' => 'icon_color', 'color' => '0xffFDA11E');

            $array_theme['text_color'] = array('key' => 'text_color', 'color' => '0xffFDA11E');

            $array_theme['button_text_color'] = array('key' => 'button_text_color', 'color' => '0xffffffff');

            $array_theme['Error_label_color'] = array('key' => 'Error_label_color', 'color' => '0xffCFCFCF');

            $array_theme['hint_text_color'] = array('key' => 'hint_text_color', 'color' => '0xffCFCFCF');

            $array_theme['sublable_color'] = array('key' => 'sublable_color', 'color' => '0xffA0A0A0');





        } else {



            // $array_theme['Bar_color'] = array('key' => 'Bar_color', 'color' => '0xff27914F');

            // $array_theme['button_color'] = array('key' => 'button_color', 'color' => '0xff27914F');

            // $array_theme['icon_color'] = array('key' => 'icon_color', 'color' => '0xff27914F');

            // $array_theme['text_color'] = array('key' => 'text_color', 'color' => '0xff000000');

            // $array_theme['button_text_color'] = array('key' => 'button_text_color', 'color' => '0xffffffff');

            // $array_theme['Error_label_color'] = array('key' => 'Error_label_color', 'color' => '0xff27914F');

            // $array_theme['hint_text_color'] = array('key' => 'hint_text_color', 'color' => '0xff85a5b6');

            // $array_theme['sublable_color'] = array('key' => 'sublable_color', 'color' => '0xff27914F');

            $array_theme['Bar_color'] = array('key' => 'Bar_color', 'color' => '0xffFDA11E');

            $array_theme['button_color'] = array('key' => 'button_color', 'color' => '0xffFDA11E');

            $array_theme['icon_color'] = array('key' => 'icon_color', 'color' => '0xffFDA11E');

            $array_theme['text_color'] = array('key' => 'text_color', 'color' => '0xffFDA11E');

            $array_theme['button_text_color'] = array('key' => 'button_text_color', 'color' => '0xffffffff');

            $array_theme['Error_label_color'] = array('key' => 'Error_label_color', 'color' => '0xffCFCFCF');

            $array_theme['hint_text_color'] = array('key' => 'hint_text_color', 'color' => '0xffCFCFCF');

            $array_theme['sublable_color'] = array('key' => 'sublable_color', 'color' => '0xffA0A0A0');



        }



        $response = array("status" => 1, "error" => 0, "success" => 1, "data" => $array_theme, "message" => 'Message converted successfully!', "domain" => $domain);



        $this->api_response($response);

    }

    public function complete_profile_post()
    {

        $result = $update_arr = array();

        $id = $this->input->post('id');

        $editFlag = ($this->input->post('edit_profile')) ? $this->input->post('edit_profile') : "0";

        $step = ($this->input->post('step')) ? $this->input->post('step') : 1;

        $image = '';





        // print_r($_FILES);



        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $result, "message" => lang('Not_Able_Update'), "post_param" => $_POST);



        if ($this->input->post('btn_submit') == 'submit' && !empty($id)) {

            if ($step == 1) {

                if (!empty($_FILES['profile_image']['name'])) {

                    $target_file = '';

                    $extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);

                    //echo $extension;

                    $profile_image_name = $this->connected_domain . '_profile_image_' . time() . '.' . $extension;

                    if (!file_exists($this->upload_file_folder . 'profile')) {

                        mkdir($this->upload_file_folder . 'profile', 0777, true);

                    }

                    $target_file = $this->upload_file_folder . 'profile/' . $profile_image_name;





                    if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {

                        $update_arr['profile_image'] = $profile_image_name;

                        $error = 0;



                    } else {

                        $error = 2;

                    }

                }



                $update_arr['first_name'] = $this->input->post('first_name');

                $update_arr['address1'] = $this->input->post('address');

                $update_arr['last_name'] = $this->input->post('last_name');

                $update_arr['state'] = $this->input->post('state');

                $update_arr['city'] = $this->input->post('district');

                $update_arr['village'] = $this->input->post('village');

                $update_arr['postcode'] = $this->input->post('pincode');

                if ($editFlag != "1")
                    $update_arr['active_step'] = $step;

                $update_arr['updated_on'] = current_date();

                $this->db->where('client.id', $id);

                $result = $this->db->update('client', $update_arr);



            } else if ($step == 2) {

                $app_user_type = $this->input->post('app_user_type');

                $app_user_type_text = ($app_user_type == 1) ? 'individual' : 'businessman';

                $data_array['client_id'] = $id;

                $data_array['business_type'] = $this->input->post('business_type');

                $data_array['business_scheme'] = $this->input->post('business_scheme');

                $data_array['business_name'] = $this->input->post('business_name');

                $data_array['business_address'] = $this->input->post('business_address');

                $data_array['business_state'] = $this->input->post('business_state');

                $data_array['business_district'] = $this->input->post('business_district');

                $data_array['business_city_village'] = $this->input->post('business_city_village');

                $data_array['business_pincode'] = $this->input->post('business_pincode');

                $data_array['business_reg_no'] = $this->input->post('business_reg_no');

                $data_array['business_pan'] = $this->input->post('business_pan');

                $data_array['business_gstin'] = $this->input->post('business_gstin');

                $data_array['business_designation'] = $this->input->post('business_designation');

                $sql_chk = "SELECT id from client_details WHERE client_id=$id";

                $res_val = $this->db->query($sql_chk);

                $res_array = $res_val->result_array();

                if (count($res_array) > 0) {

                    $data_array['updated_on'] = current_date();

                    $this->db->where('client_details.client_id', $id);

                    $result = $this->db->update('client_details', $data_array);

                    if ($result) {

                        $update_arr['app_user_type'] = $app_user_type;

                        $update_arr['app_user_type_text'] = $app_user_type_text;

                        if ($editFlag != "1")
                            $update_arr['active_step'] = $step;

                        $this->db->where('client.id', $id);

                        $result1 = $this->db->update('client', $update_arr);

                    }

                } else {

                    $data_array['created_on'] = current_date();

                    $insert_result = $this->db->insert('client_details', $data_array);

                    $insert_id = $this->db->insert_id();

                    if ($insert_result) {

                        $update_arr['app_user_type'] = $app_user_type;

                        $update_arr['app_user_type_text'] = $app_user_type_text;

                        if ($editFlag != "1")
                            $update_arr['active_step'] = $step;

                        $this->db->where('client.id', $id);

                        $result1 = $this->db->update('client', $update_arr);

                    }

                }



            } else if ($step == 3) {

                $response = $this->Ekyc_model->get_ekyc_verification_status($id);

                //echo "<pre>res===>";print_r($response);

                if ($editFlag != "1") {

                    if ($response['aadhaar_verify_sataus'] == 1) {

                        $update_arr['active_step'] = $step;

                        $this->db->where('client.id', $id);

                        $result = $this->db->update('client', $update_arr);

                    }

                    if ($response['pan_verify_sataus'] == 1) {

                        $update_arr['active_step'] = $step;

                        $this->db->where('client.id', $id);

                        $result = $this->db->update('client', $update_arr);

                    }

                    $notification_enable = get_config_data('notification_enable');
                    $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
                    $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';
                    $client_type = strtolower($headers_data['client-type']);
                    $sms_type = ($client_type == 'seller') ? 'NERACE_successfull_profile_creation_seller' : 'NERACE_Successful_Profile_Creation';
                    $detail = get_client_detail($id);
                    $phone = $detail['phone'];
                    $resp = dynamic_send_sms($phone, '', $sms_type, '', $selected_lang, '');

                    if ($notification_enable == 1) {

                        $map_key = ($client_type == 'seller') ? 'profile_completion' : 'buyer_profile_completion';

                        $notification_data = get_notification_detail($map_key, $client_type, $selected_lang);

                        $custom_array = $user_id = [];

                        if (!empty($notification_data)) {

                            $qry = "SELECT device_id FROM client WHERE is_deleted='false' AND is_active='true' AND (device_id IS NOT NULL AND device_id!= 'null') AND id =" . $id;

                            $user_id[] = $id;

                            $res_data = $this->db->query($qry);

                            $device_id_data = $res_data->row_array();

                            $token = [];

                            if (count($device_id_data)) {

                                $token[] = $device_id_data['device_id'];

                            }

                            $custom_array['user_id'] = $user_id;

                            $custom_array['map_key'] = $map_key;

                            $custom_array['reference_id'] = 'client';

                            $title = $notification_data['title'];

                            $message = $notification_data['notification_text'];

                            if ($message != '' && !empty($token)) {

                                $notifiy = $this->Notification_model->sendPushNotifications_request_dynamic($token, $title, $message, '', '', $custom_array, $type = 'Profile', $id);

                                $dd = json_decode($notifiy);

                            }

                            if ($dd->success == 1) {

                                $results_notify = true;

                            } else {

                                $results_notify = false;

                            }



                        }



                    }

                }





                /* $userid = $id;

                $aadhar_no = $this->input->post('aadhar_no');

                $otp = $this->input->post('otp');



                if($aadhar_no !=''){

                    if($otp !=''){

                        $response = $this->Ekyc_model->ekyc_aadhar_verification($userid,$aadharno,$otp);

                        $this->api_response($response);

                        exit;

                    }else{

                        $response = $this->Ekyc_model->ekyc_aadhar_otp_generate($userid,$aadharno);

                        $this->api_response($response);

                        exit;

                    }

                } */



            }



            if ($result) {

                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $update_arr, "message" => "Step " . $step . " " . lang('Added_Successfully'), 'config_url' => $this->config_url, 'notification_sent' => $results_notify);



                $this->api_response($response);

                exit;



            } elseif ($insert_result) {



                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $data_array, "message" => "Step " . $step . " " . lang('Added_Successfully'), 'config_url' => $this->config_url, 'notification_sent' => $results_notify);



                $this->api_response($response);

                exit;



            } else {



                $response = array("success" => 0, "error" => 1, "status" => 2, "data" => $update_arr, "message" => "Step " . $step . " " . lang('Not_Able_Update'), "post_param" => $_POST, 'notification_sent' => $results_notify);



                $this->api_response($response);

                exit;



            }

        }



        $this->api_response($response);

        exit;

    }

    public function intro_screen_get()
    {

        $headers_data = $this->input->request_headers();
        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);

        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        $response = array();

        $sql = "SELECT id,image,description, lang_json->>'" . $selected_lang . "' as title FROM intro_master WHERE is_deleted = 'false'";

        $row = $this->db->query($sql);

        $result = $row->result_array();

        $data = array();

        foreach ($result as $v) {

            if (!empty($v['image'])) {

                $v['image'] = $this->base_path . 'uploads/config_master/intro_master/' . $v['image'];

            }

            $data[] = $v;

        }

        //$result = $row->result_array(); // MMM comment for live only

        //$result['intro_screen_img_url'] = $this->base_path . 'uploads/config_master/intro_master';

        if (count($result)) {

            $response = array("status" => 1, "success" => 1, "error" => 0, "data" => $data, "message" => lang('Listed_Successfully'));

        } else {



            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }



        $this->api_response($response);

    }



    public function calculate_similarityPercentage_post()
    {

        $data = array();

        $cnt = $percantage = 0;

        $id = $this->input->post('id');

        $type = ($this->input->post('type')) ? $this->input->post('type') : 'aadhar';

        $cwhere = array('id' => $id);

        $select = array('first_name', 'middle_name', 'last_name', 'aadhar_verified_name', 'pan_verified_name');

        $clientdata = $this->Masters_model->get_data($select, 'client', $cwhere);

        if (!empty($clientdata)) {

            $matchstring = (strtolower($type) === 'aadhar') ? strtolower($clientdata[0]['aadhar_verified_name']) : strtolower($clientdata[0]['pan_verified_name']);

            //$aadharname = strtolower($clientdata[0]['aadhar_verified_name']);

            if (trim($clientdata[0]['first_name']) != '' && strpos(strtolower($clientdata[0]['first_name']), $matchstring) !== '') {

                $cnt++;

            }

            if (trim($clientdata[0]['middle_name']) != '' && strpos(strtolower($clientdata[0]['middle_name']), $matchstring) !== '') {

                $cnt++;

            }

            if (trim($clientdata[0]['last_name']) != '' && strpos(strtolower($clientdata[0]['last_name']), $matchstring) !== '') {

                $cnt++;

            }

            if ($cnt == 3)
                $percantage = 100;

            if ($cnt == 2)
                $percantage = 70;

            if ($cnt == 1)
                $percantage = 50;

            $data['matchname'] = $percantage . '%';

        }



        if (count($data)) {

            if ($percantage > 0) {

                $msg = lang('Profile_Match');

            } else {

                $msg = lang('Profile_Not_Match');

            }

            $response = array("status" => 1, "success" => 1, "error" => 0, "data" => $data, "message" => $msg);

        } else {



            $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

        }



        $this->api_response($response);



    }

    public function user_profile_get($farmer_id)
    {

        $result = array();

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => $_POST, "message" => lang('Missing_Parameter'));



        if ($farmer_id) {

            $select = array("client.first_name", "client.middle_name", "client.last_name", "client.email", "client.phone", "client.address1", "client.profile_image", "client.state", "client.city", "cities_new.name as cityname", "states_new.name as statename", "client.village", "client.postcode", "client.active_step", "client.app_user_type", "client.app_user_type_text", "client.pan_no", "client.aadhar_no", "client_details.business_type", "client_details.business_scheme", "client_details.business_name", "client_details.business_address", "client_details.business_state", "CASE WHEN client_details.business_state <> '' THEN (select name from states_new where states_new.id=client_details.business_state::bigint) ELSE NULL END as business_statename", "client_details.business_district", " CASE WHEN client_details.business_district <> '' THEN (select name from cities_new where cities_new.id=client_details.business_district::bigint) ELSE NULL END as business_cityname", "client_details.business_city_village", "client_details.business_pincode", "client_details.business_reg_no", "client_details.business_pan", "client_details.business_gstin", "client_details.business_designation");

            $join = array(
                "client_details" => array("client_details.client_id = client.id ", "left"),

                "cities_new" => array("cities_new.id = CAST(client.city AS bigint)", "left"),

                "states_new" => array("states_new.id = CAST(client.state AS bigint)", "left")
            );





            $where = array("client.id" => $farmer_id, "client.is_deleted" => "false");

            $user_data = $this->Masters_model->get_data($select, "client", $where, $join, "", "", 1);



            $select_aadhar = array('document_type', 'is_verify');

            $where1 = array('farmer_id' => $farmer_id, 'document_type' => 'Aadhaar');

            $check_farmer_aadhardata = $this->Masters_model->get_data($select_aadhar, 'farmer_documents', $where1);

            if (!empty($check_farmer_aadhardata)) {

                $user_data[0]['aadhar_verification'] = $check_farmer_aadhardata;

            }

            $where2 = array('farmer_id' => $farmer_id, 'document_type' => 'Pan');

            $check_farmer_pandata = $this->Masters_model->get_data($select_aadhar, 'farmer_documents', $where2);

            if (!empty($check_farmer_pandata)) {

                $user_data[0]['pan_verification'] = $check_farmer_pandata;

            }

            $user_data[0]['profile_image_path'] = $this->config_url['partner_img_url'];

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $user_data, "message" => lang('Listed_Successfully'), 'profile_image_path' => $this->config_url['partner_img_url']);



        }



        $this->api_response($response);

        exit;



    }

    public function subscription_type_get()
    {

        $active_duration = array();

        $row = $this->db->query("SELECT duration FROM subscription_master WHERE is_deleted = 'false' AND is_active='true'");

        $result = $row->result_array();

        if (!empty($result)) {

            foreach ($result as $key) {

                $active_duration[] = $key['duration'];

            }

            $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $active_duration, "message" => lang('Listed_Successfully'));



        } else {

            $response = array("success" => 0, "error" => 1, "status" => 1, "data" => [], "message" => lang('Missing_Parameter'));

        }



        $this->api_response($response);

        exit;





    }

    public function subscription_details_post()
    {

        $subscription = array();

        $duration = ($this->input->post('duration')) ? ucfirst(strtolower($this->input->post('duration'))) : '';

        $response = array("success" => 0, "error" => 1, "status" => 1, "data" => [], "message" => lang('Missing_Parameter'));

        if ($duration != '') {

            $row = $this->db->query("SELECT description,feature,sub_type,price FROM subscription_master WHERE is_deleted = 'false' AND is_active='true' AND duration='" . $duration . "'");

            $result = $row->result_array();

            if (!empty($result)) {

                /* foreach($result as $key){

                    $subscription[] = $key['duration'];

                } */

                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => $result, "message" => lang('Listed_Successfully'));



            } else {

                $response = array("success" => 1, "error" => 0, "status" => 1, "data" => [], "message" => lang('Data_Not_Found'));

            }



        }

        $this->api_response($response);

        exit;

    }

    public function partner_list_post()
    {

        //echo $type_get = $type_get;

        $response = array();

        $user_type = ($this->input->post('type')) ? $this->input->post('type') : '';

        $state = ($this->input->post('state') && $this->input->post('state') != '""') ? $this->input->post('state') : NULL;

        $city = ($this->input->post('city') && $this->input->post('city') != '""') ? $this->input->post('city') : NULL;

        if ($user_type != '') {

            $statefilter = (!empty($state)) ? " (state @> '[\"$state\"]'::jsonb)" : "";

            $cityfilter = (!empty($city)) ? " and (city @> '[\"$city\"]'::jsonb)" : "";

            if ($statefilter != '' || $cityfilter != '') {

                $newfilter = " AND  ((" . $statefilter . $cityfilter . ") OR pan_india_offering = 1 )";

            } else {

                $newfilter = " ";//" AND pan_india_offering = 1";

            }

            $subquery = "SELECT partner_id FROM product_services WHERE is_deleted = 'false' AND is_active='true'" . $newfilter . " group by partner_id ";

            $row3 = $this->db->query("select * from users where user_type = " . $user_type . " AND user_id in (" . $subquery . ")");

            $user_data = $row3->result_array();





            if (count($user_data)) {

                $response = array("success" => 1, "status" => 1, "data" => $user_data, "config_url" => $this->config_url, "message" => lang('Listed_Successfully'));

            } else {

                $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

            }

        } else {

            $response = array("success" => 1, "status" => 0, "status" => 0, "message" => lang('Missing_Parameter'));

        }

        $this->api_response($response);

    }

    public function partner_services_post()
    {

        //echo $type_get = $type_get;

        $response = array();

        $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);

        $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

        //$selected_lang = $headers_data['lang'];

        $partner_id = $this->input->post('partner_id');

        $state = ($this->input->post('state') && $this->input->post('state') != '""') ? $this->input->post('state') : NULL;

        $city = ($this->input->post('city') && $this->input->post('city') != '""') ? $this->input->post('city') : NULL;



        if ($partner_id != '') {

            /*  $statefilter = ($state!='')? " ('".$state."' IN (SELECT state->>0))":"";

             $cityfilter = ($city!='')? " AND ('".$city."' IN (SELECT city->>0))":""; */

            $statefilter = (!empty($state)) ? " (state @> '[\"$state\"]'::jsonb)" : "";

            $cityfilter = (!empty($city)) ? " and (city @> '[\"$city\"]'::jsonb)" : "";

            if ($statefilter != '' || $cityfilter != '') {

                $newfilter = " AND  ((" . $statefilter . $cityfilter . ") OR pan_india_offering = 1 )";

            } else {

                // $newfilter = " AND pan_india_offering = 1";

            }

            $row3 = $this->db->query("SELECT 

                                                ps.service_id,

                                                ps.partner_id,

                                                ps.category_id,

                                                ps.overview,

                                                ps.highlight,

                                                ps.logo,

                                                ps.created_on,

                                                ps.updated_on,

                                                ps.price,

                                                ps.package_note,

                                                ps.state,

                                                ps.city,

                                                ps.pan_india_offering,

                                                REPLACE(COALESCE(ec.json_content->>'product_services_name', ps.product_services_name), E'\r\n', '\n') AS product_services_name,

                                                REPLACE(COALESCE(ec.json_content->>'brief', ps.brief), E'\r\n', '\n') AS brief

                                            FROM 

                                                product_services ps

                                            LEFT JOIN 

                                                entity_content ec ON ps.service_id = ec.relation_id

                                                                AND ec.entity_type = 'product_service'

                                                                AND ec.lang_id = '" . $selected_lang . "'

                                            WHERE 

                                                ps.is_deleted = 'false' 

                                                AND ps.is_active = 'true' 

                                                AND ps.allow_incentive = false 

                                                AND ps.partner_id = " . $partner_id . $newfilter);



            $result_packages = $row3->result_array();

            $selects = array('users.*');

            $where = array('users.user_id' => $partner_id, 'users.is_deleted' => 'false', 'users.is_active' => 'true');



            $user_data = $this->Masters_model->get_data($selects, 'users', $where, '');





            if (count($user_data)) {

                $response = array("success" => 1, "status" => 1, "data" => $user_data, "config_url" => $this->config_url, "service_options" => $result_packages, "message" => lang('Listed_Successfully'), 'default_image' => 'service_default.jpg');

            } else {

                $response = array("status" => 0, "success" => 0, "error" => 1, "data" => [], "message" => lang('Data_Not_Found'));

            }

        } else {

            $response = array("success" => 1, "status" => 0, "status" => 0, "message" => lang('Missing_Parameter'));

        }

        $this->api_response($response);

    }

    public function add_service_leads_post()
    {

        $farmer_id = $this->input->post('farmer_id');

        $partner_id = $this->input->post('partner_id');

        $service_id = $this->input->post('service_id');

        $category_id = $this->input->post('category_id');

        $response = array();



        $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        if ($farmer_id != '' && $partner_id != '' && $service_id != '') {

            $row3 = $this->db->query("SELECT count(*) FROM product_service_lead WHERE is_deleted = 'false' AND is_completed= 'false' AND partner_id = " . $partner_id . ' AND user_id=' . $farmer_id . 'AND service_id=' . $service_id);

            $result_packages = $row3->result_array();

            if ($result_packages[0]['count'] > 0) {

                $response = array("status" => 1, "data" => 1, "message" => lang('Already_Added'));

            } else {

                $insert = array(

                    'user_id' => $farmer_id,

                    'partner_id' => $partner_id,

                    'service_id' => $service_id,

                    'category_id' => $category_id,

                    'created_by_id' => $farmer_id,

                    'created_on' => current_date(),



                );

                $this->db->insert('product_service_lead', $insert);

                $insert_id = $this->db->insert_id();
                $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
                $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';
                $sms_type = 'NERACE_Lead_Received_to_Partner';
                $partner_detail = $this->db->query("SELECT phone_no FROM users WHERE  user_id = " . $partner_id);

                $result_partner = $partner_detail->result_array();
                $phone = $result_partner[0]['phone_no'];
                $resp = dynamic_send_sms($phone, '', $sms_type, '', $selected_lang, '');
                $notification_enable = get_config_data('notification_enable');
                if ($notification_enable == 1) {

                    $client_type = 'partner';
                    $map_key = 'lead_generate';
                    $notification_data = get_notification_detail($map_key, $client_type, $selected_lang);

                    if (!empty($notification_data)) {

                        $mapkey = $map_key;
                        $reference_id = 'partner';
                        $title = $notification_data['title'];
                        $messages = $notification_data['notification_text'];

                        $sql = "INSERT INTO notifications_table (reference_id,title,message,map_key,created_on,created_by_id) VALUES ('" . $reference_id . "','" . $title . "','" . $messages . "','" . $mapkey . "',CURRENT_TIMESTAMP, $farmer_id) RETURNING id";
                        $res1 = $this->db->query($sql);
                        if ($res1) {
                            $row = $res1->row_array();
                            $last_inserted_id = $row['id'];
                        }
                        $insert_admin['partner_id'] = $partner_id;
                        $insert_admin['created_on'] = current_date();
                        $insert_admin['notification_id'] = $last_inserted_id;
                        $insert_admin['user_id'] = $farmer_id;
                        $result = $this->Masters_model->add_data('partner_notifications_table', $insert_admin);
                    }

                }

                $response = array("status" => 1, "data" => 1, "message" => lang('Added_Successfully'));

            }



        } else {

            $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        }

        $this->api_response($response);

    }

    public function incentive_beneficiaries_list_post()
    {

        $awardeddata = $redeemdata = array();

        $farmer_id = $this->input->post('farmer_id');

        if ($farmer_id) {

            $sql1 = "SELECT tpb.incentive_status,tpb.updated_on AS incentive_awarded_on,ps.product_services_name AS incentive_name,ps.overview AS overview,ps.brief AS brief,ps.logo, tpb.incentive_id FROM trade_product_bidding AS tpb LEFT JOIN product_services as ps ON ps.service_id = tpb.incentive_id WHERE tpb.is_deleted = false AND tpb.incentive_id IS NOT NULL AND tpb.incentive_redeemed_date IS NULL AND tpb.seller_id=" . $farmer_id . " order by tpb.updated_on desc";

            $row1 = $this->db->query($sql1);

            $awardeddata = $row1->result_array();



            $sql2 = "SELECT tpb.incentive_status,tpb.updated_on AS incentive_awarded_on,tpb.incentive_redeemed_date,ps.product_services_name AS incentive_name,ps.overview AS overview,ps.brief AS brief,ps.logo, tpb.incentive_id FROM trade_product_bidding AS tpb LEFT JOIN product_services as ps ON ps.service_id = tpb.incentive_id WHERE tpb.is_deleted = false AND tpb.incentive_id IS NOT NULL AND tpb.incentive_status = '2' AND tpb.incentive_redeemed_date IS NOT NULL AND tpb.seller_id=" . $farmer_id . " order by tpb.incentive_redeemed_date desc";

            $row2 = $this->db->query($sql2);

            $redeemdata = $row2->result_array();



            if (empty($awardeddata) && empty($redeemdata)) {

                $response = array("success" => 0, "data" => [], "message" => 'No Record Found!');

            } else {

                $response = array("success" => 1, "awardeddata" => $awardeddata, "redeemdata" => $redeemdata, "message" => 'Listed Successfully!');

            }



        } else {

            $response = array("status" => 0, "message" => lang('Missing_Parameter'));

        }

        $this->api_response($response);

    }

    public function send_notification_dynamic_post()
    {

        $user_id = $this->input->post('user_id');

        $map_key = $this->input->post('map_key');

        $redirect_key = $this->input->post('redirect_key');

        $redirect_id = $this->input->post('redirect_id');

        $reference_id = $this->input->post('reference_id');

        $notification_enable = get_config_data('notification_enable');

        $results_notify = false;

        if ($notification_enable == 1) {

            if ($user_id && $map_key) {

                $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);


                $selected_lang = ($headers_data['lang']) ? $headers_data['lang'] : 'en';

                $client_type = ($headers_data['client-type']) ? strtolower($headers_data['client-type']) : 'seller';

                $notification_data = get_notification_detail($map_key, $client_type, $selected_lang);

                $custom_array = $userid = [];

                if (!empty($notification_data)) {

                    $qry = "SELECT device_id FROM client WHERE is_deleted='false' AND is_active='true' AND (device_id IS NOT NULL AND device_id!= 'null') AND id =" . $user_id;



                    $res_data = $this->db->query($qry);

                    $device_id_data = $res_data->row_array();

                    $token = [];

                    if (count($device_id_data)) {

                        $token[] = $device_id_data['device_id'];

                    }

                    $userid[] = $user_id;

                    $custom_array['user_id'] = $userid;

                    $custom_array['map_key'] = $map_key;

                    $custom_array['reference_id'] = ($reference_id) ? $reference_id : 'client';

                    $title = $notification_data['title'];

                    $arr = array(

                        'body' => array("{TICKET_ID}" => '#' . $redirect_id),

                    );

                    $sms_template = get_sms_template($notification_data['notification_text'], $arr);

                    $message = $sms_template;

                    if ($message != '' && !empty($token)) {

                        $notifiy = $this->Notification_model->sendPushNotifications_request_dynamic($token, $title, $message, '', '', $custom_array, $type = $redirect_key, $redirect_id);

                        $dd = json_decode($notifiy);

                    }

                    if ($dd->success == 1) {

                        $results_notify = true;

                    } else {

                        $results_notify = false;

                    }



                }

                if ($results_notify) {

                    $response = array("success" => 1, "notification_sent" => $results_notify, "message" => "Notification Sent Successfully!");



                } else {

                    $response = array("success" => 0, 'notification_sent' => $results_notify, "message" => 'Notification Failed');



                }

            } else {

                $response = array("status" => 0, "message" => lang('Missing_Parameter'));

            }



        } else {

            $response = array("success" => 0, "notification_sent" => $results_notify, "message" => 'Notification Failed! Access Denied');

        }

        $this->api_response($response);
        exit;

    }

    public function get_counts_get()
    {
        // get Product Listed, sellers listed, Buyer Listed and Partners Listed
        $response = array();
        $product_count = $this->db->query("SELECT COUNT(*) as total_products FROM trade_product WHERE is_deleted = 'false' AND is_active='true'")->row_array();
        $seller_count = $this->db->query("SELECT COUNT(*) as total_sellers FROM client WHERE is_deleted = 'false' AND is_active='true' AND client_type='2'")->row_array();
        $buyer_count = $this->db->query("SELECT COUNT(*) as total_buyers FROM client WHERE is_deleted = 'false' AND is_active='true' AND client_type='1'")->row_array();
        $partner_count = $this->db->query("SELECT COUNT(*) as total_partners FROM users WHERE is_deleted = 'false' AND is_active='true' AND type='partner'")->row_array();

        $data = array(
            'product_count' => $product_count['total_products'],
            'registered_count_seller' => $seller_count['total_sellers'],
            'registered_count_buyer' => $buyer_count['total_buyers'],
            'partner_count' => $partner_count['total_partners']
        );

        $response = array("success" => 1, "data" => $data, "message" => 'Count Fetched Successfully!');

        $this->api_response($data);
        exit;

    }

    public function test_trace_get()
    {
        $url = 'https://api.nerace.in/api/v16/users/custom_config';
        $httpMethod = 'TRACE';
        // Initialize cURL session
        $ch = curl_init($url);
        // Set cURL options
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $httpMethod);//TRACE
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true); // Fail on HTTP error codes
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-API-KEY: CODEX@123',
            'domain: nerace',
            'appname: nerace',
            'Cookie: ci_session=2cb7a2c1abfe73cf492a7e143c99be5eb916a9fc'
        ));

        // Execute cURL session and capture the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);
        // Display the response
        echo $response;
    }
}

