<?php
defined('BASEPATH') or exit('No direct script access allowed');

error_reporting(E_ERROR | E_PARSE);
//error_reporting(E_ERROR | E_PARSE);
//error_reporting(E_ALL);

require APPPATH . 'libraries/RestController.php';

use chriskacerguis\RestServer\RestController;

class Emeeting extends RestController
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

        // $base_path = base_url();
        //$base_path = 'https://dev.famrut.co.in/agri_ecosystem/';
       // $this->base_path = $base_path = 'https://dev.famrut.co.in/agroemandi/';
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

        /* if (trim($headers_data['group_id']) != '') {

        $group_id = explode(',', $headers_data['group_id']);

        $where      = array('is_deleted' => 'false', 'is_active' => 'true', 'client_group_id' => $group_id[0]);
        $group_name = $this->Masters_model->get_data("name", 'client_group_master', $where);
        //print_r($group_name); die;

        if ($group_name[0]['name'] == "ICAR" && !empty($_POST)) {
        //print_r($this->uri); die;

        // kvstore API url
        $url = base_url() . 'api/ICARV4/' . $this->uri->segment(4);
        // Collection object
        $data = $this->input->post();
        // Initializes a new cURL session
        $curl = curl_init($url);
        // Set the CURLOPT_RETURNTRANSFER option to true
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers_data);
        // Set the CURLOPT_POST option to true for POST request
        curl_setopt($curl, CURLOPT_POST, true);
        // Set the request data as JSON using json_encode function
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        // Execute cURL request with all previous settings
        $group_response = curl_exec($curl);
        // Close cURL session
        curl_close($curl);

        echo $group_response;
        die;

        } else if ($group_name[0]['name'] == "ICAR") {
        $urlpara = '';
        for ($i = 5; $i < 10; $i++) {
        if ($this->uri->segment($i)) {
        $urlpara .= '/' . $this->uri->segment($i);
        }

        }
        redirect(base_url() . 'api/ICARV4/' . $this->uri->segment(4) . $urlpara);
        }

        //redirect(base_url().'api/ICAR/'.$this->uri->segment(4));
        } */

        /*    $this->config_url = array(
        'category_img_url' => $base_path.'uploads/category/',
        'partner_img_url'  => $base_path.'uploads/user_data/profile/',
        'pan_no_doc_url'  => $base_path.'uploads/user_data/aadhar_no/',
        'aadhar_no_doc_url'  => $base_path.'uploads/user_data/pan_no/',
        'farm_image_url'  => $base_path.'uploads/user_data/farm/',
        );*/

        //http://115.124.120.147/marketplace/uploads/advertise_master

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
            'bottom_menu_icon'        => $base_path . 'uploads/bottom_menu_icons/',
            'crop_verity_img_url'     => $base_path . 'uploads/crop_variety_icon/',
            'crop_ferti_img_url'      => $base_path . 'uploads/crops_ferti_image/',
            'soil_health_image'       => $base_path . 'uploads/soil_health_image/',
            'media_thumbnails'        => $base_path . 'uploads/media_thumbnails/',
            'loan_image_url'          => $base_path . 'uploads/' . $this->connected_domain . '/user_data/loan/',
            'crop_image'              => $base_path . 'uploads/' . $this->connected_domain . '/user_data/crop_image/',
        );

         // send app_user_type:  1 = ( not farmer)  &&&&&   0 = farmer.  
         if (isset($headers_data['app_user_type']) || !empty($headers_data['app_user_type'])) {

            if($headers_data['app_user_type'] == 1){

            $this->menu = array(
           /* array('id' => '2', 'title' => lang('Home'), 'map_key' => 'Home', 'icon' => 'home'),       */    
            array('id' => '6', 'title' => lang('My-Orders'), 'map_key' => 'My-Orders', 'icon' => 'order'),          
            array('id' => '15', 'title' => lang('About us'), 'map_key' => 'About-us', 'icon' => 'about_us'),
            array('id' => '16', 'title' => lang('Privacy-Policy'), 'map_key' => 'Privacy-Policy', 'icon' => 'ic_assignment'),           
            array('id' => '17', 'title' => lang('Announcement'), 'map_key' => 'Announcement', 'icon' => 'ic_announcement'),
            array('id' => '18', 'title' => lang('Setting'), 'map_key' => 'Setting', 'icon' => 'seeting'),     
           
        );

        }else{

              $this->menu = array(
           /* array('id' => '2', 'title' => lang('Home'), 'map_key' => 'Home', 'icon' => 'home'),*/
            array('id' => '3', 'title' => lang('My-Farms'), 'map_key' => 'My-Farms', 'icon' => 'my_farm'),
            array('id' => '4', 'title' => lang('Apply-for-Loan'), 'map_key' => 'Apply-for-Loan', 'icon' => 'loan'),
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
          /*  array('id' => '19', 'title' => lang('Rewards'), 'map_key' => 'Rewards', 'icon' => 'rewards'),*/
           
        );

        }


         }else{
            $this->menu = array(
            array('id' => '2', 'title' => lang('Home'), 'map_key' => 'Home', 'icon' => 'home'),
            array('id' => '3', 'title' => lang('My-Farms'), 'map_key' => 'My-Farms', 'icon' => 'my_farm'),
            array('id' => '4', 'title' => lang('Apply-for-Loan'), 'map_key' => 'Apply-for-Loan', 'icon' => 'loan'),
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
           /* array('id' => '19', 'title' => lang('Rewards'), 'map_key' => 'Rewards', 'icon' => 'rewards'),*/
        );

         }

        

        $this->home_message = array('message' => 'Welcome to FAMRUT - 10X Growth solution');

        $this->topology = array(
            array('id' => '1', 'value' => 'High', 'name_mr' => 'उंच'),
            array('id' => '2', 'value' => 'Low', 'name_mr' => 'कमी'),
            array('id' => '3', 'value' => 'Medium', 'name_mr' => 'मध्यम'),
        );
        $this->topology_web    = array('1' => 'High', '2' => 'Low', '3' => 'Medium');
        $this->topology_web_mr = array('1' => 'उंच', '2' => 'कमी', '3' => 'मध्यम');

        $this->farm_type = array(
            array('id' => '1', 'value' => 'Organic Farming', 'name_mr' => 'सेंद्रिय शेती'),
            array('id' => '2', 'value' => 'Conventional Farming', 'name_mr' => 'पारंपारिक शेती'),
            array('id' => '3', 'value' => 'Residue Free Farming', 'name_mr' => 'अवशेष मुक्त शेती'),

        );
        $this->farm_type_web    = array('1' => 'Organic Farming', '2' => 'Conventional Farming', '3' => 'Residue Free Farming');
        $this->farm_type_web_mr = array('1' => 'सेंद्रिय शेती', '2' => 'पारंपारिक शेती', '3' => 'अवशेष मुक्त शेती');

        $this->unit = array(
            array('id' => '2', 'value' => 'Acre', 'name_mr' => 'एकर'),
            array('id' => '3', 'value' => 'Hectare', 'name_mr' => 'हेक्टर'),
        );
        $this->unit_web_mr = array('1' => 'हेक्टर', '2' => 'एकर');
        $this->unit_web    = array('1' => 'Hectare', '2' => 'Acre');

        $this->irri_src = array(
            array('id' => '1', 'value' => 'Well', 'name_mr' => 'विहीर'),
            array('id' => '2', 'value' => 'Borewell', 'name_mr' => 'बोअरवेल'),
            array('id' => '3', 'value' => 'Canal/River', 'name_mr' => 'कालवा / नदी'),
            array('id' => '4', 'value' => 'Farm lake', 'name_mr' => 'शेत तलाव'),
            array('id' => '5', 'value' => 'Others', 'name_mr' => 'इतर'),
        );
        $this->irri_src_web    = array('1' => 'Well', '2' => 'Borewell', '3' => 'Canal/River', '4' => 'Farm lake', '5' => 'Others');
        $this->irri_src_web_mr = array('1' => 'विहीर', '2' => 'बोअरवेल', '3' => 'कालवा / नदी', '4' => 'शेत तलाव', '5' => 'इतर');

        $this->irri_faty = array(
            array('id' => '1', 'value' => 'Pipelines', 'name_mr' => 'पाईपलाईन'),
            array('id' => '2', 'value' => 'Sprinkler Heads', 'name_mr' => 'शिंपडण्याचे प्रमुख'),
            array('id' => '3', 'value' => 'Valves', 'name_mr' => 'वाल्व्ह'),
        );
        $this->irri_faty_web    = array('1' => 'Pipelines', '2' => 'Sprinkler Heads', '3' => 'Valves');
        $this->irri_faty_web_mr = array('1' => 'पाईपलाईन', '2' => 'शिंपडण्याचे प्रमुख', '3' => 'वाल्व्ह');

        $this->crop_type = array(
            array('id' => '1', 'value' => 'Kharif', 'name_mr' => 'खरिफ'),
            array('id' => '2', 'value' => 'Rabi', 'name_mr' => 'रुबी'),
            array('id' => '3', 'value' => 'fruits', 'name_mr' => 'फळे'),
        );
        $this->crop_type_web    = array('1' => 'Kharif', '2' => 'Rabi', '3' => 'fruits');
        $this->crop_type_web_mr = array('1' => 'खरिफ', '2' => 'रुबी', '3' => 'फळे');

        $this->crop_web    = array('1' => 'Pomegranate', '2' => 'Grapes', '3' => 'Capsicum', '4' => 'Othercrops', '5' => 'Floriculture', '6' => 'Orange', '7' => 'Mango', '8' => 'Citrus');
        $this->crop_web_mr = array('1' => 'डाळींब', '2' => 'द्राक्षे', '3' => 'शिमला', '4' => 'इतरपिके', '5' => 'फ्लोरिकल्चर', '6' => 'संत्री', '7' => 'आंबा', '8' => 'मोसंबी');

        $this->soil_type = array(
            array('id' => '1', 'value' => 'Light Clay', 'name_mr' => 'हलकी चिकणमाती'),
            array('id' => '2', 'value' => 'Medium red', 'name_mr' => 'मध्यम लाल'),
            array('id' => '3', 'value' => 'Black', 'name_mr' => 'काळा'),
            array('id' => '4', 'value' => 'Medium black', 'name_mr' => 'मध्यम  काळा'),
            array('id' => '5', 'value' => 'Black solid', 'name_mr' => 'काळा घन'),
            array('id' => '6', 'value' => 'Limestone / Sherwat', 'name_mr' => 'चुनखडी / शेरवत'),
        );
        $this->soil_type_web    = array('1' => 'Light Clay', '2' => 'Medium red', '3' => 'Black', '4' => 'Medium black', '5' => 'Black solid', '6' => 'Limestone / Sherwat');
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
        // echo base_url();exit;
        if (!empty($token)) {
            header('Authorization: ' . $token);
        }
        if (empty($status)) {
            $status = 200;
        }
        echo $this->response($data, $status);exit;
    }

    public function start_call_meeting_post()
    {
        $demotoken      = $this->input->post('token');
        $partner_id     = $this->input->post('partner_id');
        $meeting_link   = $this->input->post('MeetingId');
        $farmer_id      = $this->input->post('farmer_id');
        $today          = date('Y-m-d');

        $sql_chk        = "SELECT * FROM emeeting WHERE partner_id = " . $partner_id . " and farmer_id = " . $farmer_id . " and (meeting_status_id = 1 OR meeting_status_id = 2) AND date(created_on) = '" . $today . "' ORDER BY id ASC LIMIT 1";
        $row_val      = $this->db->query($sql_chk);
        $call_result  = $row_val->result_array();

        // print_r($call_result);exit;
        
        if (count($call_result) > 0) {
            $avaiable_flag = 0; // Partner is not avaiable
            $call_coming   = 1;
            $meeting_link  = $call_result[0]['meeting_link_id'];
        }

        $sql         = "SELECT first_name, last_name,device_id from client where id=" . $farmer_id . "  LIMIT 1";
        $row         = $this->db->query($sql);
        $farmer_data = $row->result_array();
        $farmer_name = $farmer_data[0]['first_name'] . ' ' . $farmer_data[0]['last_name'];

        $type  = 'eMeeting';
        $title          = 'Incoming call';
        $message        = 'Dear ' . $farmer_name . ' have call. Please join meeting ';
        $admno = $partner_id;
        $call_data = array(
            'farmer_id'         => $farmer_id,
            'farmer_name'       => $farmer_name,
            'partner_id'        => $partner_id,
            'meeting_link_id'   => $meeting_link,
        );

        if(!empty($demotoken)){
            $token[]    = $demotoken;
        } else {
            $token[]    = $farmer_data[0]['device_id'];
        }
        // $token[]    = $farmer_data[0]['device_id'];

        //Device Id for Manoj Mobile to test Notification
        //$token[] = 'dcNMWbLMr1c:APA91bGni1E09a6jOEc0IUNlyLA8bixfOjUczIYR26GDlbRLoP48JlMK5ueeLnIqXajh8eMNC97LOVbFzORIpIwUI2q1dnOAqi2cpKK_256hVmq3jplLyxVVqKlTwLUnZrLr53SxLnqE';

        // $token    = [];
        // $token[]    = 'dfR5Z6u3QVi5gvF5CMra2z:APA91bFYJVLXCp9Eb1_oLkYvFtnL-x3jy3jwq4HDHvfVNvx7vmhIZATycMgApmLyafgE4Rm3cbMXqMHgn-bTrD7B2e-b-IJ8xVav1XdrZzAvU0JLG8HDZqqyFz-4d0rRLLhaIFub-L7v';
        // $token[]    = 'cTM_BHa4SPyMrASl3soT-Y:APA91bGfZPEzYiVjUlrDpJTWO4Lk32mEqZY3WYxIA-4wwlfq7F7Kb3h_SB6mpCg_iDs2rrXb-DVK48DazZgEWtvRf8DQ_ZQdFxofI904NdNEtza0OiBmN-PnlfHk6c2RzcHWhpj3BeKe';

        $this->load->model('Notification_model');
        
        $jsonString = $this->Notification_model->sendPushEMeetingNotification($token, $title, $message, $admno, $type, $meeting_link, $partner_name, $call_data);


        $response = array("success" => 1, "error" => 0, "status" => 1, "data" => json_decode($jsonString, true), "message" => "Connect call",'call_data' => $call_data);

        $this->api_response($response);
    }

    public function new_call_farmer($farmer_id)
    {

        $today      = date('Y-m-d');
        $partner_id = $this->session->userdata('user_id');

        $sql_chk = "SELECT * FROM emeeting WHERE partner_id = " . $partner_id . " and farmer_id = " . $farmer_id . " and (meeting_status_id = 1 OR meeting_status_id = 2) AND date(created_on) = '" . $today . "' ORDER BY id ASC LIMIT 1";
        $row_val = $this->db->query($sql_chk);

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
                'is_active'            => 'true',
                'created_on'           => current_date(),
            );

            $sql_insert = $this->db->insert('emeeting', $insert);

        }

        //$data['farmer_id']= $farmer_id;
        //echo 'farmer_data'.$farmer_data[0]['last_name'];
        //exit;
        //$result = $row->result_array();

        //$sql_insert = ' INSERT into emeeting'
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
        $data['subview']      = $this->load->view('partner/all_clients/emeeting', $data, true);
        $this->load->view('include/main', $data);

    }

    public function reconnect_farmer($farmer_id)
    {

        //echo 'Farmer id :'.$farmer_id;
        $partner_id = $this->session->userdata('user_id');

    }

    public function start_call_meeting()
    {

        //echo 'Farmer id :'.$farmer_id;
        $partner_id = $this->session->userdata('user_id');
        //$partner_id = $this->session->userdata('user_id');
        $meeting_link = $this->input->post('MeetingId');
        $farmer_id    = $this->input->post('farmer_id');
        $today        = date('Y-m-d');
        $sql_chk      = "SELECT * FROM emeeting WHERE partner_id = " . $partner_id . " and farmer_id = " . $farmer_id . " and (meeting_status_id = 1 OR meeting_status_id = 2) AND date(created_on) = '" . $today . "' ORDER BY id ASC LIMIT 1";
        $row_val      = $this->db->query($sql_chk);
        $call_result  = $row_val->result_array();

        if (count($call_result) > 0) {
            $avaiable_flag = 0; // Partner is not avaiable
            $call_coming   = 1;
            $meeting_link  = $call_result[0]['meeting_link_id'];

        }

        $sql         = "SELECT first_name, last_name,device_id from client where id=" . $farmer_id . "  LIMIT 1";
        $row         = $this->db->query($sql);
        $farmer_data = $row->result_array();

        $data['title'] = 'eMeeting';

        $title   = 'Incoming call';
        $message = 'Dear ' . $farmer_data[0]['first_name'] . ' ' . $farmer_data[0]['last_name'] . ' have Call from ' . $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name') . ' please join meeting ';

        $admno = $partner_id;

        $call_data = array(
            'farmer_id'       => $farmer_id,
            'farmer_name'     => $farmer_data[0]['first_name'] . ' ' . $farmer_data[0]['last_name'],
            'partner_name'    => $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name'),
            'meeting_link_id' => $meeting_link,
        );

        $partner_name = $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name');

        $token[] = $farmer_data[0]['device_id'];

        //Device Id for Manoj Mobile to test Notification
        //$token[] = 'dcNMWbLMr1c:APA91bGni1E09a6jOEc0IUNlyLA8bixfOjUczIYR26GDlbRLoP48JlMK5ueeLnIqXajh8eMNC97LOVbFzORIpIwUI2q1dnOAqi2cpKK_256hVmq3jplLyxVVqKlTwLUnZrLr53SxLnqE';

        $jsonString = self::sendPushNotificationToFCMSeverdev($token, $title, $message, $admno, $call_data, $meeting_link, $partner_name);
        echo 1;

    }

    public function call_meeting_accepted()
    {

        //echo 'Farmer id :'.$farmer_id;
        $partner_id = $this->session->userdata('user_id');
        //$partner_id = $this->session->userdata('user_id');
        $meeting_link = $this->input->post('MeetingId');
        $farmer_id    = $this->input->post('farmer_id');
        $today        = date('Y-m-d');
        $sql_chk      = "SELECT * FROM emeeting WHERE partner_id = " . $partner_id . " and farmer_id = " . $farmer_id . " and (meeting_status_id = 1 OR meeting_status_id = 2) AND date(created_on) = '" . $today . "' ORDER BY id ASC LIMIT 1";
        $row_val      = $this->db->query($sql_chk);
        $call_result  = $row_val->result_array();

        if (count($call_result) > 0) {
            $avaiable_flag = 0; // Partner is not avaiable
            $call_coming   = 1;
            $meeting_link  = $call_result[0]['meeting_link_id'];

        }

        $sql         = "SELECT first_name, last_name,device_id from client where id=" . $farmer_id . "  LIMIT 1";
        $row         = $this->db->query($sql);
        $farmer_data = $row->result_array();

        $data['title'] = 'eMeeting';

        $title   = 'Meeting accepted';
        $message = 'Dear ' . $farmer_data[0]['first_name'] . ' ' . $farmer_data[0]['last_name'] . ' your Call from ' . $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name') . ' joined';

        $admno = $partner_id;

        $call_data = array(
            'farmer_id'       => $farmer_id,
            'farmer_name'     => $farmer_data[0]['first_name'] . ' ' . $farmer_data[0]['last_name'],
            'partner_name'    => $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name'),
            'meeting_link_id' => $meeting_link,
        );

        $partner_name = $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name');

        $token[] = $farmer_data[0]['device_id'];

        //Device Id for Manoj Mobile to test Notification
        //$token[] = 'dcNMWbLMr1c:APA91bGni1E09a6jOEc0IUNlyLA8bixfOjUczIYR26GDlbRLoP48JlMK5ueeLnIqXajh8eMNC97LOVbFzORIpIwUI2q1dnOAqi2cpKK_256hVmq3jplLyxVVqKlTwLUnZrLr53SxLnqE';

        //$jsonString = self::sendPushNotificationToFCMSeverdev($token, $title, $message, $admno, $call_data, $meeting_link, $partner_name);
        echo 1;

    }

    public function disconnect_farmer()
    {
        //echo 'Farmer id :'.$farmer_id;
        // meeting_end_from = 2  // if its 2 then call end from Partner
       // $partner_id = $this->session->userdata('user_id');
        $farmer_id  = $this->input->post('farmer_id');
        //$meeting_link =   $this->input->post('farmer_id');
        $meeting_duration = trim($this->input->post('meeting_duration'));
        $meeting_link     = trim($this->input->post('MeetingId'));
        //$meeting_link
        // 'partner_id'      => $partner_id,
        $where_array = array(
            'farmer_id'       => $farmer_id,           
            'meeting_link_id' => $meeting_link,
        );

        $update_array = array(
            'meeting_status_id' => 4,
            'meeting_end_from'  => 2,
            'updated_on'        => current_date(),
            'call_duration'     => 0,
        );

        $sql_update = $this->db->update('emeeting', $update_array, $where_array);

        ////////////////////////////////////////
        $sql         = "SELECT first_name, last_name,device_id from client where id=" . $farmer_id . "  LIMIT 1";
        $row         = $this->db->query($sql);
        $farmer_data = $row->result_array();

        $data['title'] = 'eMeeting';

        $title   = 'Disconnect call';
        $message = 'Dear ' . $farmer_data[0]['first_name'] . ' ' . $farmer_data[0]['last_name'] . ' have Call Disconnected from ' . $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name') . ' please stop meeting ';

        $admno     = $partner_id;
        $call_data = array();

        $partner_name = $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name');

        $token[] = $farmer_data[0]['device_id'];

        //Device Id for Manoj Mobile to test Notification
        //$token[] = 'dcNMWbLMr1c:APA91bGni1E09a6jOEc0IUNlyLA8bixfOjUczIYR26GDlbRLoP48JlMK5ueeLnIqXajh8eMNC97LOVbFzORIpIwUI2q1dnOAqi2cpKK_256hVmq3jplLyxVVqKlTwLUnZrLr53SxLnqE';

        $jsonString = self::sendPushNotificationToFCMSeverdev($token, $title, $message, $admno, $call_data, $meeting_link, $partner_name);
        //////////////////////////////////////

        echo 1;
    }

    public function reject_farmer()
    {
        //echo 'Farmer id :'.$farmer_id;
        // meeting_end_from = 2  // if its 2 then call end from Partner
        $partner_id       = $this->session->userdata('user_id');
        $farmer_id        = $this->input->post('farmer_id');
        $meeting_duration = 0;
        //$meeting_link =   $this->input->post('farmer_id');

        $meeting_link = $this->input->post('MeetingId');
        //$meeting_link
        $where_array = array(
            'farmer_id'       => $farmer_id,
            'partner_id'      => $partner_id,
            'meeting_link_id' => $meeting_link,
        );
        // 'meeting_status_id' => 5, to rejeect call detection
        //  $meeting_duration = 0; meeting time set to 0 here
        // 'meeting_end_from'  => 2, here 2 indicate call rejected by partner
        $update_array = array(
            'meeting_status_id' => 5,
            'meeting_end_from'  => 2,
            'updated_on'        => current_date(),
            'call_duration'     => $meeting_duration,
        );

        $sql_update = $this->db->update('emeeting', $update_array, $where_array);

        ////////////////////////////////////////
        $sql         = "SELECT first_name, last_name,device_id from client where id=" . $farmer_id . "  LIMIT 1";
        $row         = $this->db->query($sql);
        $farmer_data = $row->result_array();

        $data['title'] = 'eMeeting';

        $title   = 'Disconnect call';
        $message = 'Dear ' . $farmer_data[0]['first_name'] . ' ' . $farmer_data[0]['last_name'] . ' have Call Rejected from ' . $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name') . ' please stop meeting ';

        $admno     = $partner_id;
        $call_data = array();

        $partner_name = $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name');

        $token[] = $farmer_data[0]['device_id'];

        //Device Id for Manoj Mobile to test Notification
        $token[] = 'dcNMWbLMr1c:APA91bGni1E09a6jOEc0IUNlyLA8bixfOjUczIYR26GDlbRLoP48JlMK5ueeLnIqXajh8eMNC97LOVbFzORIpIwUI2q1dnOAqi2cpKK_256hVmq3jplLyxVVqKlTwLUnZrLr53SxLnqE';

        $jsonString = self::sendPushNotificationToFCMSeverdev($token, $title, $message, $admno, $call_data, $meeting_link, $partner_name);
        //////////////////////////////////////

        echo 1;
    }

    public function farmer_call_action($farmer_id, $meeting_id)
    {
        //$this->session->userdata('user_id');
        $today      = date('Y-m-d');
        $partner_id = $this->session->userdata('user_id');

        $sql_chk     = "SELECT * FROM emeeting WHERE partner_id = " . $partner_id . " and farmer_id = " . $farmer_id . " and (meeting_status_id = 1 OR meeting_status_id = 2) AND meeting_link_id ='" . $meeting_id . "' AND date(created_on) = '" . $today . "' ORDER BY id ASC LIMIT 1";
        $row_val     = $this->db->query($sql_chk);
        $call_result = $row_val->result_array();

        if (count($call_result) > 0) {
            $avaiable_flag         = 1; // Partner is not avaiable
            $call_coming           = 1;
            $data['avaiable_flag'] = 1;
            $meeting_link          = $call_result[0]['meeting_link_id'];

        } else {

            $avaiable_flag         = 0; // Partner is not avaiable
            $call_coming           = 0;
            $meeting_link          = $meeting_id;
            $data['avaiable_flag'] = 0;

            /*$meeting_link = md5(date("Ymdhis") . $farmer_id . $partner_id);

        $insert = array(
        'farmer_id'            => $farmer_id,
        'partner_id'           => $partner_id,
        'meeting_status_id'    => 1,
        'meeting_started_from' => 2,
        'meeting_link_id'      => $meeting_link,
        'is_active'            => 'true',
        'created_on'           => current_date(),
        );

        $sql_insert = $this->db->insert('emeeting', $insert);*/

        }
        //$sql_insert = ' INSERT into emeeting'
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
        $data['subview']      = $this->load->view('partner/all_clients/join_emeeting', $data, true);
        $this->load->view('include/main', $data);

    }

    public function chk_call_status()
    {

        //echo 'Farmer id :'.$farmer_id;
        $this->session->userdata('user_id');
        //echo 'herer';
        $today           = date('Y-m-d');
        $result          = array();
        $farmer_id       = 0;
        $meeting_link_id = 0;
        $partner_id      = $this->session->userdata('user_id');
        $sql_chk         = "SELECT * FROM emeeting WHERE partner_id = " . $partner_id . " and (meeting_status_id = 1 OR meeting_status_id = 2) AND date(created_on) = '" . $today . "' ORDER BY id ASC LIMIT 1";
        $row_val         = $this->db->query($sql_chk);
        $result          = $row_val->result_array();
        if (count($result) > 0) {
            $avaiable_flag   = 0; // Partner is not avaiable
            $call_coming     = 1;
            $farmer_id       = $result[0]['farmer_id'];
            $meeting_link_id = $result[0]['meeting_link_id'];

        } else {
            $avaiable_flag = 1; // Partner is avaiable
            $call_coming   = 0;
        }

        $data = array('avaiable_flag' => $avaiable_flag, 'call_coming' => $call_coming, 'farmer_id' => $farmer_id, 'meeting_link_id' => $meeting_link_id, 'result' => $result);
        echo json_encode($data);
    }

    public function chk_call_active_status($MeetingId)
    {

        $today      = date('Y-m-d');
        $partner_id = $this->session->userdata('user_id');
        //$meeting_link_id = $this->input->post('txtMeetingId');
        //$MeetingId = $this->input->post('MeetingId');

        $sql_chk = "SELECT meeting_status_id FROM emeeting WHERE partner_id = " . $partner_id . " AND   meeting_link_id='" . $MeetingId . "' AND (meeting_status_id = 1 OR meeting_status_id = 2) AND date(created_on) = '" . $today . "' ORDER BY id ASC";
        $row_val = $this->db->query($sql_chk);
        $result  = $row_val->result_array();
        if (count($result) > 0) {

            $avaiable_flag = 1; // Partner is avaiable
            $call_status   = 1;

        } else {
            $avaiable_flag = 0;
            $call_status   = 4;
            $result        = $sql_chk;
        }

        $data = array('avaiable_flag' => $avaiable_flag, 'call_status' => $call_status, 'result' => $result, 'sql_chk' => $sql_chk);
        echo json_encode($data);
    }

    public function new_data()
    {

        $data['title'] = 'Farmer incoming Call';
        // $data['meeting_link'] = $meeting_link;
        // $data['farmer_id'] = $farmer_id;
        $data['subview'] = $this->load->view('partner/all_clients/testsse', $data, true);
        $this->load->view('include/main', $data);

    }

    public function call_history()
    {
        $data['title']     = 'Video Call History';
        $data['call_data'] = array();
        $partner_id        = $this->session->userdata('user_id');
        $sql_chk           = "SELECT * FROM emeeting WHERE partner_id = " . $partner_id . " ORDER BY id ASC";
        $row_val           = $this->db->query($sql_chk);
        $result            = $row_val->result_array();
        if (count($result) > 0) {
            $data['call_data'] = $result;
        }
        // $data['meeting_link'] = $meeting_link;
        // $data['farmer_id'] = $farmer_id;
        $data['subview'] = $this->load->view('partner/all_clients/call_history', $data, true);
        $this->load->view('include/main', $data);

    }

    public function call_data()
    {
        $data['title']     = 'Video Call History';
        $data['call_data'] = array();
        $partner_id        = $this->session->userdata('user_id');
        $sql_chk           = "SELECT * FROM emeeting WHERE partner_id = " . $partner_id . " ORDER BY id ASC";
        $row_val           = $this->db->query($sql_chk);
        $result            = $row_val->result_array();
        if (count($result) > 0) {
            $data['call_data'] = $result;
        }
        // $data['meeting_link'] = $meeting_link;
        // $data['farmer_id'] = $farmer_id;
        $data['subview'] = $this->load->view('partner/all_clients/call_history', $data, true);
        $this->load->view('include/main', $data);

    }


    
}