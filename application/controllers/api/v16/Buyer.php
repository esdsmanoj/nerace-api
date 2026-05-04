<?php
defined("BASEPATH") or exit("No direct script access allowed");
error_reporting(E_ERROR | E_PARSE);
//error_reporting(E_ERROR | E_PARSE);
//error_reporting(E_ALL);
require APPPATH . "libraries/RestController.php";
use chriskacerguis\RestServer\RestController;
class Buyer extends RestController
{
    public function __construct()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        parent::__construct();
        // $this->load->library('cors');
        // $headers_data = $this->input->request_headers();
        $headers = $this->input->request_headers();
        $headers_data = array_change_key_case($headers, CASE_LOWER);
        // print_r($headers_data);
        // Start: Required headers and there value check
        // if ((!strpos($_SERVER['REQUEST_URI'], 'partner_login')) || (!strpos($_SERVER['REQUEST_URI'], 'dynamic_domain_db_connection'))) {
        // if (!strpos($_SERVER['REQUEST_URI'], 'dynamic_domain_db_connection')) {
        $this->load->model("Notification_model");
        // $headers_data['domain'] = $headers_data['domain'];
        // $headers_data['client-type'] = $headers_data['Client_type'];
        // $headers_data['client-type'] = $headers_data['Client_type'];
        if (
            !strpos($_SERVER["REQUEST_URI"], "dynamic_theme_color") ||
            !strpos($_SERVER["REQUEST_URI"], "dynamic_domain_db_connection")
        ) {
            $require_headers = ["domain", "appname"];
        } else {
            $require_headers = ["domain"];
        }
        $require_header_arr = [];
        $require_header_val = [];
        foreach ($require_headers as $rh_val) {
            if (!array_key_exists($rh_val, $headers_data)) {
                $require_header_arr[] = $rh_val;
            } elseif (empty($headers_data[$rh_val])) {
                $require_header_val[] = $rh_val;
            }
        }
        if (!empty($require_header_arr) && count($require_header_arr) > 0) {
            $require_header_str = implode(", ", $require_header_arr);
            $msg = "Invalid Request";
            $response = [
                "status" => 0,
                "error" => 1,
                "data" => [],
                "message" => $msg,
            ];
            $this->api_response($response);
            exit();
        } elseif (
            !empty($require_header_val) &&
            count($require_header_val) > 0
        ) {
            $require_header_str = implode(", ", $require_header_val);
            $msg = "Invalid Request";
            $response = [
                "status" => 0,
                "error" => 1,
                "data" => [],
                "message" => $msg,
            ];
            $this->api_response($response);
            exit();
        }
        // End: Required headers and there value check
        // Start: Create upload file name and as per database name : Akash
        $this->connected_appname = "";
        $this->connected_domain = "";
        $root_folder = $_SERVER["HOME"] . "/" . UPLOAD_ROOT_FOLDER . "/";
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
        $headers_data = array_change_key_case(
            $this->input->request_headers(),
            CASE_LOWER
        );
        $this->connected_appname = strtolower($headers_data["appname"]); // globaly set connected appname name
        $this->connected_domain = strtolower($headers_data["domain"]); // globaly set connected domain name
        $db_folder = $root_folder . "uploads/" . $this->connected_domain;
        if (!file_exists($db_folder)) {
            mkdir($db_folder, 0777, true);
        }
        if (!file_exists($db_folder . "/user_data")) {
            mkdir($db_folder . "/user_data", 0777, true);
        }
        $this->upload_file_folder = $db_folder . "/" . "user_data/"; // globaly set upload file folder root
        // End: Create upload file name and as per db name : Akash
        // echo $this->upload_file_folder;exit;
        $this->load->library("Token");
        if (!strpos($_SERVER["REQUEST_URI"], "dynamic_domain_db_connection")) {
            $this->load->library("upload");
            $this->load->model("Email_model");
            $this->load->helper("log_helper");
            $this->load->helper("sms_helper");
            $this->load->helper("npks_helper");
            $this->load->model("Masters_model");
            $this->load->model("Ekyc_model");
        }
        $lang_folder = "english";
        if (
            $this->session->userdata("user_site_language") &&
            $this->session->userdata("user_site_language") == "MR"
        ) {
            $lang_folder = "marathi";
        } else {
            $this->session->set_userdata("user_site_language", "EN");
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
        $headers_data["lang"] = isset($headers_data["lang"])
            ? $headers_data["lang"]
            : "en";
        $this->selected_lang = $selected_lang = $headers_data["lang"];
        if ($this->selected_lang == "") {
            $this->selected_lang = $selected_lang = "en";
        }
        if ($selected_lang == "mr") {
            $lang_folder = "marathi";
        } elseif ($selected_lang == "hi") {
            $lang_folder = "hindi";
        } else {
            $lang_folder = "english";
        }
        $this->lang->load(["site"], $lang_folder);
        // echo lang('Data_Not_Found');exit;
        // $group_id     = $headers_data['group_id']; (replace this line with below 2 lines)
        $group_id_arr = explode(",", $headers_data["group_id"]);
        $group_id = $group_id_arr[0];
        // Below array is in used
        $this->config_url = [
            "category_img_url" => $base_path . "uploads/category/",
            "partner_img_url" =>
                $base_path .
                "uploads/" .
                $this->connected_domain .
                "/user_data/profile/",
            "aadhar_no_doc_url" =>
                $base_path .
                "uploads/" .
                $this->connected_domain .
                "/user_data/aadhar_no/",
            "pan_no_doc_url" =>
                $base_path .
                "uploads/" .
                $this->connected_domain .
                "/user_data/pan_no/",
            "farm_image_url" =>
                $base_path .
                "uploads/" .
                $this->connected_domain .
                "/user_data/farm/",
            "Product_image_url" => $base_path . "uploads/productcategory/",
            "market_cat_image_url" => $base_path . "uploads/logo/",
            "service_image_url" => $base_path . "uploads/product_service/",
            "blogs_types_url" => $base_path . "uploads/blogs_types/",
            "media_types" => $base_path . "uploads/media_types/",
            "blogs_tags_url" => $base_path . "uploads/blogs_tags/",
            "created_blogs_url" => $base_path . "uploads/created_blogs/",
            "farmer_documents_url" =>
                $base_path .
                "uploads/" .
                $this->connected_domain .
                "/user_data/verification_documents/",
            "advertise_image_url" => $base_path . "uploads/advertise_master/",
            "whitelabel_image_url" =>
                $base_path . "uploads/client_group_master/",
            "terms_sheet" => $base_path . "uploads/terms_sheet/",
            "farm_doc" =>
                $base_path .
                "uploads/" .
                $this->connected_domain .
                "/user_data/farm_doc/",
            "insurance_company" => $base_path . "uploads/insurance_company/",
            "crop_image_url" => $base_path . "uploads/crops/",
            "crop_type_url" => $base_path . "uploads/crop_type_icon/",
            "notice" => $base_path . "uploads/notice/",
            "announcement" => $base_path . "uploads/announcement/",
            "crop_health_predict_api" => "http://115.124.96.136:8443/predict",
            "dss_module_imageurl" => $base_path . "uploads/dss_module/",
            "bottom_menu_icon" => $base_path . "uploads/app_menu/",
            "crop_verity_img_url" => $base_path . "uploads/crop_variety_icon/",
            "crop_ferti_img_url" => $base_path . "uploads/crops_ferti_image/",
            "soil_health_image" => $base_path . "uploads/soil_health_image/",
            "media_thumbnails" => $base_path . "uploads/media_thumbnails/",
            "loan_type_url" => $base_path . "uploads/loan_type/",
            "loan_image_url" =>
                $base_path .
                "uploads/" .
                $this->connected_domain .
                "/user_data/loan/",
            "crop_image" =>
                $base_path .
                "uploads/" .
                $this->connected_domain .
                "/user_data/crop_image/",
            "trade_products" =>
                $base_path . "uploads/config_master/trade_products",
            "seller_invoice_path" =>
                $base_path . "uploads/config_master/seller_invoice",
            "prod_master_image_path" =>
                $base_path . "uploads/config_master/prod_master",
        ];
    }
    /***********************Working APIs: Start***********************/
    public function api_response($data, $status = null, $token = null)
    {
        // echo $this->base_path;exit;
        if (!empty($token)) {
            header("Authorization: " . $token);
        }
        if (empty($status)) {
            $status = 200;
        }
        $this->save_logs($data); // Save logs
        echo $this->response($data, $status);
        exit();
    }
    //***********************************************************************
    // Buyer module API: Start //////////////////////////
    //***********************************************************************
    public function is_user_regsitered_post()
    {
        $is_profile_complete = 0;
        $headers_data = array_change_key_case(
            $this->input->request_headers(),
            CASE_LOWER
        );
        // if (strtolower($headers_data['domain']) == 'nedfi') {
        if (
            strpos(strtolower($headers_data["domain"]), strtolower(CODE)) !==
            false
        ) {
            $step_list = STEP_LIST;
        } else {
            $step_list = SHORT_STEP_LIST;
        }
        // echo 'show_referral:'.get_config_data('show_referral');exit;
        $show_referral = !empty(get_config_data("show_referral"))
            ? get_config_data("show_referral")
            : "0";
        $registration_lock = !empty(get_config_data("registration_lock"))
            ? get_config_data("registration_lock")
            : "0";
        $registration_lock_messge = !empty(
            get_config_data("registration_lock_messge")
        )
            ? get_config_data("registration_lock_messge")
            : "";
        $app_user_type = !empty(get_config_data("app_user_type"))
            ? get_config_data("app_user_type")
            : "0";
        //echo "<pre>";print_r($_POST);echo "<br>PHONE===>".$this->input->post('phone');exit;
        if ($this->input->post("phone") != "") {
            $response = [
                "success" => 0,
                "error" => 1,
                "status" => 1,
                "data" => [],
                "message" => lang("Already_Register"),
                "is_registered" => 1,
                "app_user_type" => $app_user_type,
            ];
            $phone = substr(
                preg_replace("/\s+/", "", $this->input->post("phone")),
                -10,
                10
            );
            $row = $this->db->query(
                "SELECT *,(select name from cities_new where id::varchar=city) as new_city_name,(select name from states_new where id::varchar=state) as new_state_name FROM client WHERE  is_deleted = 'false' and phone::varchar = '$phone'::varchar "
            );
            $result = $row->result_array();
            $is_profile_complete = $result[0]["active_step"] == 3 ? 1 : 0;
            if (count($result)) {
                // Check buyer login
                if (
                    !empty($result[0]["client_type"]) &&
                    strpos(
                        strtolower($headers_data["domain"]),
                        strtolower(CODE)
                    ) !== false
                ) {
                    if ((int) $result[0]["client_type"] === 1) {
                        if ($result[0]["is_active"] != f) {
                            $response = [
                                "success" => 1,
                                "error" => 0,
                                "status" => 1,
                                "data" => $result,
                                "step_list" => $step_list,
                                "message" => lang("Already_Register"),
                                "is_registered" => 1,
                                "app_user_type" => $app_user_type,
                                "show_referral" => $show_referral,
                                "is_profile_complete" => $is_profile_complete,
                                "registration_lock" => $registration_lock,
                                "registration_lock_messge" => $registration_lock_messge,
                            ];
                        } else {
                            $response = [
                                "success" => 1,
                                "error" => 0,
                                "status" => 0,
                                "data" => null,
                                "step_list" => $step_list,
                                "message" => lang("Mobile_Deactivated"),
                                "is_registered" => 1,
                                "app_user_type" => $app_user_type,
                                "show_referral" => $show_referral,
                                "is_profile_complete" => $is_profile_complete,
                                "registration_lock" => $registration_lock,
                                "registration_lock_messge" => $registration_lock_messge,
                            ];
                        }
                    } else {
                        $response = [
                            "success" => 0,
                            "error" => 1,
                            "status" => 1,
                            "data" => [],
                            "message" => lang("Buyer_Login_Failed"),
                            "app_user_type" => $app_user_type,
                            "show_referral" => $show_referral,
                            "registration_lock" => $registration_lock,
                            "registration_lock_messge" => $registration_lock_messge,
                        ];
                    }
                } else {
                    if ($result[0]["is_active"] != f) {
                        $response = [
                            "success" => 1,
                            "error" => 0,
                            "status" => 1,
                            "data" => $result,
                            "step_list" => $step_list,
                            "message" => lang("Already_Register"),
                            "is_registered" => 1,
                            "app_user_type" => $app_user_type,
                            "show_referral" => $show_referral,
                            "is_profile_complete" => $is_profile_complete,
                            "registration_lock" => $registration_lock,
                            "registration_lock_messge" => $registration_lock_messge,
                        ];
                    } else {
                        $response = [
                            "success" => 1,
                            "error" => 0,
                            "status" => 0,
                            "data" => null,
                            "step_list" => $step_list,
                            "message" => lang("Mobile_Deactivated"),
                            "is_registered" => 1,
                            "app_user_type" => $app_user_type,
                            "show_referral" => $show_referral,
                            "is_profile_complete" => $is_profile_complete,
                            "registration_lock" => $registration_lock,
                            "registration_lock_messge" => $registration_lock_messge,
                        ];
                    }
                }
            } else {
                // show_referral key is set 1 to show referral screen if 0 then hide that screen on app.
                //registration_lock key is set 0 to allow registration if 1 then show registration_lock_messge .
                $response = [
                    "success" => 1,
                    "error" => 0,
                    "status" => 0,
                    "data" => null,
                    "step_list" => $step_list,
                    "message" => lang("Not_Register"),
                    "is_registered" => 0,
                    "app_user_type" => $app_user_type,
                    "show_referral" => $show_referral,
                    "is_profile_complete" => $is_profile_complete,
                    "registration_lock" => $registration_lock,
                    "registration_lock_messge" => $registration_lock_messge,
                ];
            }
        } else {
            $response = [
                "success" => 0,
                "error" => 1,
                "status" => 1,
                "data" => $result,
                "message" => lang("Missing_Parameter"),
                "app_user_type" => $app_user_type,
                "show_referral" => $show_referral,
                "registration_lock" => $registration_lock,
                "registration_lock_messge" => $registration_lock_messge,
            ];
        }
        $this->api_response($response);
        exit();
    }
    public function register_otp_post()
    {
        $client_type_listing = CLIENT_TYPE;
        $client_type = 1;
        // foreach ($client_type_listing as $key => $value) {
        // 	if(strtolower($value['title']) == 'buyer'){
        // 		$client_type = $value['id'];
        // 	}
        // }
        $result = [];
        $response = [
            "success" => 0,
            "error" => 1,
            "status" => 1,
            "data" => $result,
            "message" => lang("Registration_Failed"),
        ];
        $is_whitelabeled = "false";
        if (
            $this->input->post("btn_submit") == "submit" &&
            $this->input->post("phone") != ""
        ) {
            if (0) {
                $data = $this->input->post();
                $data["error"] = validation_errors();
            } else {
                $phone = substr(
                    preg_replace("/\s+/", "", $this->input->post("phone")),
                    -10,
                    10
                );
                $row = $this->db->query(
                    "SELECT * FROM client WHERE is_deleted = 'false' and phone::varchar = '$phone'::varchar "
                );
                $result = $row->result_array();
                if (count($result)) {
                    $response = [
                        "success" => 0,
                        "error" => 1,
                        "status" => 1,
                        "data" => "NULL",
                        "user_id" => $result[0]["id"],
                        "active_step" => $result[0]["active_step"],
                        "message" => lang("Already_Register"),
                    ];
                    $this->api_response($response);
                    exit();
                } else {
                    $opt_number = mt_rand(100000, 999999);
                    $sms_type = "NERACE_OTP_Mobile_Verification"; // for OTP its once
                    // $mobile = 8208953165;
                    $mobile = substr(
                        preg_replace("/\s+/", "", $this->input->post("phone")),
                        -10,
                        10
                    );
                    $headers_data = array_change_key_case(
                        $this->input->request_headers(),
                        CASE_LOWER
                    );
                    $selected_lang = $headers_data["lang"]
                        ? $headers_data["lang"]
                        : "en";
                    if (
                        strtolower($headers_data["domain"]) == "famrut" ||
                        strtolower($headers_data["domain"]) == "famrutd"
                    ) {
                        $text =
                            "Your OTP for Famrut is: " .
                            $opt_number .
                            " mQ5HHzOtTip . Please enter it on the app to confirm your account. Thanks for using Famrut";
                        $otpstring = $opt_number . " mQ5HHzOtTip ";
                    } else {
                        $text =
                            "Your OTP for Famrut is: " .
                            $opt_number .
                            " U5fcG3OYgG2 . Please enter it on the app to confirm your account. Thanks for using Famrut";
                        $otpstring = $opt_number . " U5fcG3OYgG2 ";
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
                        $replace = [
                            "body" => ["{OTP_NUMBER}" => $otpstring],
                        ];
                        $resp = dynamic_send_sms(
                            $mobile,
                            "",
                            $sms_type,
                            "",
                            $selected_lang,
                            $replace
                        );
                    }
                }
                $referral_code = !empty($this->input->post("referral_code"))
                    ? $this->input->post("referral_code")
                    : "";
                if (
                    !empty($referral_code) &&
                    strpos($referral_code, "-") !== false
                ) {
                    $get_arr = explode("-", $referral_code);
                    $referral_code = $get_arr[0];
                    // echo "My string contains Bob";
                }
                $farmer_group_id = "";
                $where = ["is_deleted" => "false", "is_active" => "true"];
                if (!empty($referral_code)) {
                    $where["referral_code"] = $referral_code;
                }
                $group_id = $this->Masters_model->get_data(
                    "client_group_id,",
                    "client_group_master",
                    $where
                );
                if ($group_id[0]["client_group_id"]) {
                    $farmer_group_id = $group_id[0]["client_group_id"];
                    if ($group_id[0]["is_whitelabeled"]) {
                        $is_whitelabeled = $group_id[0]["is_whitelabeled"];
                    }
                } else {
                    $where = ["is_deleted" => "false", "is_active" => "true"];
                    if (!empty($referral_code)) {
                        $where["my_refferal_code"] = $referral_code;
                    }
                    $group_id = $this->Masters_model->get_data(
                        "group_id",
                        "client",
                        $where
                    );
                    $farmer_group_id = $group_id[0]["group_id"];
                }
                $headers_data = array_change_key_case(
                    $this->input->request_headers(),
                    CASE_LOWER
                );
                // if (strtolower($headers_data['domain']) == 'nedfi'){
                if (
                    strpos(
                        strtolower($headers_data["domain"]),
                        strtolower(CODE)
                    ) !== false
                ) {
                    $insert = [
                        "phone" => $this->input->post("phone"),
                        "opt_number" => $opt_number,
                        "is_active" => "true",
                        "is_whitelabeled" => $is_whitelabeled,
                        "client_type" => $client_type,
                        "created_on" => current_date(),
                    ];
                } else {
                    $app_user_type = $this->input->post("app_user_type");
                    $insert = [
                        "phone" => $mobile,
                        "first_name" => $this->input->post("first_name"),
                        "last_name" => $this->input->post("last_name"),
                        "phone" => $this->input->post("phone"),
                        "postcode" => $this->input->post("postcode"),
                        "email" => $this->input->post("email"),
                        "address1" => $this->input->post("address1"),
                        "opt_number" => $opt_number,
                        "is_active" => "true",
                        "is_whitelabeled" => $is_whitelabeled,
                        // 'dob'              => $dob,
                        "gender" => $this->input->post("gender"),
                        "my_refferal_code" => time(),
                        "group_id" => $farmer_group_id,
                        "group_ids" => $farmer_group_id,
                        "app_user_type" => $app_user_type,
                        "created_on" => current_date(),
                        "client_type" => $client_type,
                    ];
                }
                if (trim($this->input->post("dob")) != "") {
                    $insert["dob"] = date(
                        "Y-m-d",
                        strtotime($this->input->post("dob"))
                    );
                }
                if ($this->input->post("device_id")) {
                    $insert["device_id"] = $this->input->post("device_id");
                }
                if (!empty($referral_code)) {
                    $insert["referral_code"] = $referral_code;
                }
                //user_activity_log
                $title = "Client: Registered";
                $description = json_encode($insert);
                user_activity_logs($title, $description);
                // echo strtolower($headers_data['domain']).': '.strtolower(CODE).'<pre>';
                // print_r($insert);exit;
                $result = $this->db->insert("client", $insert);
                $insert_id = $this->db->insert_id();
                if ($result) {
                    $step = $this->db->query(
                        "SELECT active_step FROM client WHERE is_deleted = 'false' and id= " .
                            $insert_id
                    );
                    $step_result = $step->result_array();
                    if (count($insert)) {
                        $response = [
                            "success" => 1,
                            "error" => 0,
                            "status" => 1,
                            "data" => $result,
                            "message" => lang("Register_Successfully"),
                            "opt_number" => $opt_number,
                            "user_id" => $insert_id,
                            "active_step" => $step_result[0]["active_step"],
                        ];
                    }
                    $this->api_response($response);
                    exit();
                } else {
                    $response = [
                        "success" => 0,
                        "error" => 1,
                        "status" => 1,
                        "data" => $result,
                        "message" => lang("Registration_Failed"),
                    ];
                    $this->api_response($response);
                    exit();
                }
            }
        }
        $this->api_response($response);
        exit();
    }
    // List all or single trade products
    public function trade_product_post()
    {
        // echo'<pre>';print_r($_POST);exit;
        // Filter data
        $id = trim($this->input->post("id"));
        $prod_name = $this->input->post("prod_name");
        $prod_variety = $this->input->post("prod_variety");
        $state = $this->input->post("state");
        $city = $this->input->post("city");
        $exp_date_from = $this->input->post("exp_date_from")
            ? date("Y-m-d", strtotime($this->input->post("exp_date_from")))
            : "";
        $exp_date_to = $this->input->post("exp_date_to")
            ? date("Y-m-d", strtotime($this->input->post("exp_date_to")))
            : "";
        $availability_from = $this->input->post("availability_from") ?? "";
        $availability_to = $this->input->post("availability_to") ?? "";
        // echo $availability_from;exit;
        $price_from = $this->input->post("price_from");
        $price_to = $this->input->post("price_to");
        $negotiations = $this->input->post("negotiable") ? "t" : "";
        $certifcations = $this->input->post("certifcations") ? "t" : "";
        $prodCatId = $this->input->post("prod_cat_id");
        $trade_status = $this->input->post("trade_status");
        $buyer_id = $this->input->post("buyer_id");
        $prodType = $this->input->post("prod_type_id");
        // 3:Live, 4: Sold, 5: Complete,6: Expired
        // $display_status	= [3, 4, 5, 6];
        $display_status = [3];
        $selected_lang = $this->selected_lang;
        $table = "trade_product as tp";
        $limit = 10;
        $start = trim($this->input->post("start"));
        if (empty($start)) {
            $start = 1;
        }
        $sort_filter = $this->input->post("sort_filter");
        $start_chk = $start - 1;
        if ($start_chk != 0) {
            $start_sql = $limit * $start_chk;
        } else {
            $start_sql = 0;
        }
        $select_list =
            " tp.id, tp.user_id, tp.prod_cat_id, tp.prod_type_id, pt.title as product_type_title, tp.prod_details, tp.prod_id, pm.title as product_title, pm.logo as product_logo, tp.prod_variety_id, pv.title as product_variety_title, tp.active_till_date, tp.surplus, tp.surplus_unit, tp.other_details, tp.sell_qty, tp.sell_qty_unit, tp.price, tp.price_unit, tp.with_logistic_partner, tp.with_packging, tp.packaging_master_id, pkg.title as packaging_title, tp.storage_type_id, st.title as storage_type_title, s.id as state_id, s.name as state_name, c.id as city_id, c.name as city_name, tp.pickup_location, tp.other_distance, tp.produce_to_highway_distance, tp.advance_payment, tp.negotiations, tp.certifcations, tp.trade_status, tp.partial_trade, tp.status, tp.reason, tp.added_date, tp.expiry_date, tp.approved_date, tp.rejected_date, tp.prod_images";
        $select_query = "SELECT $select_list FROM $table
        LEFT JOIN prod_master as pm ON pm.id = tp.prod_id
        LEFT JOIN prod_variety as pv ON pv.id = tp.prod_variety_id
        LEFT JOIN prod_type as pt ON pt.id = tp.prod_type_id
        LEFT JOIN packaging_master as pkg ON pkg.id = tp.packaging_master_id
		LEFT JOIN storage_type as st ON st.id = tp.storage_type_id
		LEFT JOIN states_new as s ON s.id = tp.state
		LEFT JOIN cities_new as c ON c.id = tp.city
        WHERE tp.is_deleted = 'false' AND tp.is_active = 'true' ";
        if ($prodCatId == 2) {
            // if (!empty($availability_from) && !empty($availability_to)) {
            // 	// (json_column->>'date_key')::date BETWEEN '2024-01-01'::date AND '2024-12-31'::date;
            // 	$select_query  .= " AND (tp.other_details->>'availability_from')::date >= '".$availability_from."' ";
            // 	$select_query  .= " AND (tp.other_details->>'availability_to'):date <= '".$availability_to."' ";
            // }
        } else {
            $select_query .=
                " AND DATE(tp.expiry_date) >= '" . date("Y-m-d") . "' ";
            if (!empty($exp_date_from) && !empty($exp_date_to)) {
                if ($exp_date_from <= $exp_date_to) {
                    // $select_query  .= " AND DATE(tp.expiry_date) BETWEEN '".$exp_date_from."' AND '".$exp_date_to."' ";
                    $select_query .=
                        " AND DATE(tp.expiry_date) >= '" .
                        $exp_date_from .
                        "' ";
                    $select_query .=
                        " AND DATE(tp.expiry_date) <= '" . $exp_date_to . "' ";
                    // echo $exp_date_from.' : '.$exp_date_to;exit;
                } else {
                    $response = [
                        "success" => 0,
                        "data" => [],
                        "message" =>
                            "Expiry from date is always less then expiry to date!",
                    ];
                    $this->api_response($response);
                    exit();
                }
            }
        }
        $select_query .=
            " AND CAST(tp.status AS INTEGER) IN (" .
            implode(", ", $display_status) .
            ") ";
        if (!empty($id)) {
            $select_query .= " AND tp.id = '" . $id . "' ";
        }
        if (!empty($prodType)) {
            $select_query .= " AND tp.prod_type_id = '" . $prodType . "' ";
        }
        if (!empty($prod_name)) {
            $select_query .=
                " AND LOWER(pm.title) LIKE '%" . strtolower($prod_name) . "%' ";
        }
        if (!empty($prod_variety)) {
            $select_query .=
                " AND tp.prod_variety_id = '" . $prod_variety . "' ";
        }
        if (!empty($state)) {
            $select_query .= " AND tp.state = '" . $state . "' ";
        }
        if (!empty($city)) {
            $select_query .= " AND tp.city = '" . $city . "' ";
        }
        if (!empty($price_from)) {
            $select_query .=
                " AND CAST(tp.price AS INTEGER) > " . $price_from . " ";
        }
        if (!empty($price_to)) {
            $select_query .=
                " AND CAST(tp.price AS INTEGER) < " . $price_to . " ";
        }
        if (!empty($negotiations)) {
            $select_query .= " AND tp.negotiations = '" . $negotiations . "' ";
        }
        if (!empty($certifcations)) {
            $select_query .=
                " AND tp.certifcations = '" . $certifcations . "' ";
        }
        if (!empty($prodCatId)) {
            $select_query .= " AND tp.prod_cat_id = '" . $prodCatId . "' ";
            // print_r($_POST);exit;
        } else {
            $prodCatIdArr = [1, 3];
            $select_query .=
                " AND tp.prod_cat_id IN (" .
                implode(", ", $prodCatIdArr) .
                ") ";
        }
        if (!empty($trade_status)) {
            $select_query .= " AND tp.trade_status = '" . $trade_status . "' ";
        }
        $order_query .= " ORDER BY tp.approved_date DESC ";
        $limit_query .= " LIMIT " . $limit . " OFFSET " . $start_sql;
        // echo $select_query;exit;
        // Get total number of rows
        $num_row_query = $this->db->query($select_query . $order_query);
        $num_rows = $num_row_query->num_rows();
        // Get list of all data
        // $row    = $this->db->query($select_query.$order_query.$limit_query);
        $row = $this->db->query($select_query . $order_query);
        $res = $row->result_array();
        $result = [];
        // print_r($res);exit;
        if (!empty($res)) {
            foreach ($res as $key => $value) {
                /* Start Get product details : Akash */
                $prod_details = $value["prod_details"];
                $product_details = array_filter(PROD_DETAILS, function (
                    $product_details_data
                ) use ($prod_details) {
                    return $product_details_data["id"] == $prod_details;
                });
                $product_details = array_values($product_details);
                $value["product_details_title"] = $product_details[0]["title"];
                /* End Get product details */
                /* Start Get product category details : Akash */
                $prod_cat_id = $value["prod_cat_id"];
                $product_category = array_filter(PROD_CAT, function (
                    $product_category_data
                ) use ($prod_cat_id) {
                    return $product_category_data["id"] == $prod_cat_id;
                });
                $product_category = array_values($product_category);
                $value["product_category_title"] =
                    $product_category[0]["title"];
                /* End Get product category details */
                /* Start Get unit details : Akash */
                $get_unit_list_ids = [
                    "surplus_unit",
                    "sell_qty_unit",
                    "price_unit",
                ];
                // $prod_cat_id = $value['prod_cat_id'];
                foreach ($get_unit_list_ids as $unit_id) {
                    $unitId = $value[$unit_id];
                    $product_unit = array_filter(PROD_UNIT, function (
                        $product_unit_data
                    ) use ($unitId) {
                        return $product_unit_data["id"] == $unitId;
                    });
                    $product_unit = array_values($product_unit);
                    $value[$unit_id . "_title"] = $product_unit[0]["title"];
                }
                /* End Get unit details */
                /* Start Get Status details : Akash */
                $get_status_list_ids = ["status", "trade_status"];
                foreach ($get_status_list_ids as $status_id) {
                    $statusId = $value[$status_id];
                    $statusList = array_filter(TRADE_STATUS_LIST, function (
                        $statusList_data
                    ) use ($statusId) {
                        return $statusList_data["id"] == $statusId;
                    });
                    $statusList = array_values($statusList);
                    $value[$status_id . "_title"] = $statusList[0]["title"];
                    // $unitId = $value[$unit_id];
                    // $product_unit   = array_filter(PROD_UNIT, function ($product_unit_data) use ($unitId) {
                    //     return $product_unit_data['id'] == $unitId;
                    // });
                    // $product_unit = array_values($product_unit);
                    // $value[$unit_id.'_title'] = $product_unit[0]['title'];
                }
                /* End Get Status details */
                /* Start Get season details : Akash */
                $other_details = json_decode($value["other_details"], true);
                $season_arr = ["season_from", "season_to"];
                $yield_arr = ["yield_from_unit", "yield_to_unit"];
                $season_text = "";
                foreach ($other_details as $detailsKey => $detailsVal) {
                    if (in_array($detailsKey, $season_arr)) {
                        $season = array_filter(SEASON_LIST, function (
                            $season_data
                        ) use ($detailsVal) {
                            return $season_data["id"] == $detailsVal;
                        });
                        $season = array_values($season);
                        if ($detailsKey == "season_from") {
                            $season_text .=
                                "From - " . $season[0]["title"] . ", ";
                        } else {
                            $season_text .= " To - " . $season[0]["title"];
                        }
                    }
                    if (in_array($detailsKey, $yield_arr)) {
                        $yield = array_filter(PROD_UNIT, function (
                            $yield_data
                        ) use ($detailsVal) {
                            return $yield_data["id"] == $detailsVal;
                        });
                        $yield = array_values($yield);
                        $yield_text = $yield[0]["title"];
                        $other_details[$detailsKey . "_text"] = $yield_text;
                    }
                }
                $value["other_details"] = $other_details;
                $value["season_text"] = $season_text;
                /* End Get season details */
                if ($value["with_logistic_partner"] == "t") {
                    $value["logistic_text"] = "Included";
                } else {
                    $value["logistic_text"] = "Not included";
                }
                if ($value["with_packging"] == "t") {
                    $value["packaging_text"] = $value["packging_title"];
                } else {
                    $value["packaging_text"] = "";
                }
                // $value['active_till_date'] = date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($value['active_till_date'])));
                // $value['added_date'] = date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($value['added_date'])));
                // $value['expiry_date'] = date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($value['expiry_date'])));
                // $prod_images	= json_decode($value['prod_images']);
                // $value['other_details']	= json_decode($value['other_details']);
                $value["other_distance"] = json_decode(
                    $value["other_distance"]
                );
                $prod_images = json_decode($value["prod_images"], true);
                $prodImages = [];
                foreach ($prod_images as $images) {
                    $prodImages = array_merge($prodImages, $images);
                }
                $value["prod_thumbnail"] =
                    $this->config_url["prod_master_image_path"] .
                    "/" .
                    $value["product_logo"];
                $value["all_prod_images"] = $prodImages;
                $value["rating_details"] = show_rating(
                    $value["user_id"],
                    "seller"
                );
                // Highest Bid
                $qry = $this->db->query(
                    "SELECT MAX(bid_price) as highestBid FROM trade_product_bidding WHERE is_deleted = 'false' AND is_active = 'true' AND trade_product_id = " .
                        $value["id"] .
                        " "
                );
                $res = $qry->row_array();
                $value["highestBid"] = $res["highestbid"] ?? $value["price"];
                // Get Trade Product Bidding data
                if (!empty($buyer_id) && $value["prod_cat_id"] != 2) {
                    $value["buyer_intrest_count"] = 0;
                    $select_columns = [
                        "id",
                        "buyer_id",
                        "seller_id",
                        "trade_product_id",
                        "qty",
                        "qty_unit",
                        "bid_price",
                        "bid_date",
                        "bid_count",
                        "seller_action",
                        "seller_action_date",
                        "buyer_action",
                        "buyer_action_date",
                        "bid_status",
                    ];
                    $where_condition = [
                        "trade_product_id" => $value["id"],
                        "is_deleted" => "false",
                        "is_active" => "true",
                    ];
                    // $order_by = ' seller_action asc, buyer_action asc, id desc ';
                    $trade_product_bidding = $this->Masters_model->get_data(
                        $select_columns,
                        "trade_product_bidding",
                        $where_condition
                    );
                    if (!empty($trade_product_bidding)) {
                        $bidding = [];
                        $bidBuyerId = "";
                        foreach (
                            $trade_product_bidding
                            as $bid_key => $bid_val
                        ) {
                            if (
                                (int) $bid_val["buyer_id"] ===
                                    (int) $buyer_id &&
                                (int) $bid_val["buyer_action"] != 3
                            ) {
                                $bidBuyerId = $value["bidBuyerId"] =
                                    $bid_val["buyer_id"];
                            }
                            $bidding[] = $bid_val;
                            // if($bid_val['seller_action'] != 3){
                            // 	$bidding[] = $bid_val;
                            // }
                        }
                        $value["trade_product_bidding_count"] = count($bidding);
                        $value["trade_product_bidding"] = $bidding;
                    } else {
                        $value["trade_product_bidding_count"] = 0;
                        $value["trade_product_bidding"] = [];
                    }
                    if ((int) $bidBuyerId != (int) $buyer_id) {
                        $result[] = $value;
                    }
                } else {
                    $value["trade_product_bidding_count"] = 0;
                    $value["trade_product_bidding"] = [];
                    $select_query =
                        "SELECT COUNT(tpi.id) as count
					FROM trade_product_interest as tpi
					WHERE tpi.is_active = 'true'
					AND tpi.is_deleted = 'false'
					AND tpi.buyer_id = $buyer_id
					AND tpi.trade_product_id = " .
                        $value["id"] .
                        " ";
                    $query_row = $this->db->query($select_query);
                    $interest_data = $query_row->row_array();
                    // echo $interest_data;exit;
                    // $select_query	= "SELECT *
                    // FROM trade_product_interest as tpi
                    // WHERE tpi.is_active = 'true'
                    // AND tpi.buyer_id = $buyer_id
                    // AND tpi.trade_product_id = ".$value['id']." ";
                    // // AND tpi.is_deleted = 'false'
                    // $query_row		= $this->db->query($select_query);
                    // $interest_data	= $query_row->result_array();
                    // $insertDataCount = $this->buyers_interest_count($value['id']);
                    // $value['buyer_intrest_count'] = $interest_data['count'];
                    if ($interest_data["count"] == 0) {
                        $select_query1 =
                            "SELECT COUNT(tpi.id) as count
						FROM trade_product_interest as tpi
						WHERE tpi.is_active = 'true'
						AND tpi.is_deleted = 'false'
						AND tpi.trade_product_id = " .
                            $value["id"] .
                            " ";
                        $query_row1 = $this->db->query($select_query1);
                        $interest_data1 = $query_row1->row_array();
                        $value["buyer_intrest_count"] =
                            $interest_data1["count"];
                        $result[] = $value;
                    }
                    // $select_query	= "SELECT tp.prod_id,tp.prod_cat_id,tpi.buyer_id,tpi.created_on as interest_shown_on ,c.first_name,c.middle_name,c.last_name,c.profile_image, tpi.is_deleted
                    // FROM trade_product_interest as tpi
                    // LEFT JOIN trade_product as tp ON tp.id = tpi.trade_product_id
                    // LEFT JOIN client as c ON c.id = tpi.buyer_id
                    // WHERE tpi.is_active = 'true'
                    // AND tpi.buyer_id != $buyer_id ";
                    // $query_row		= $this->db->query($select_query);
                    // $interest_data	= $query_row->result_array();
                    // // $interestBuyerId = null;
                    // $value['interestBuyerId'] = null;
                    // if(count($interest_data) > 0){
                    // 	// print_r($interest_data);exit;
                    // 	$value['buyer_intrest_count']	= $interest_data['buyer_interest_count'];
                    // 	$value['buyer_intrest']			= $interest_data['user_data'];
                    // 	foreach ($interest_data['user_data'] as $buyerKey => $buyerVal) {
                    // 		if((int)$buyerVal['buyer_id'] != (int)$buyer_id){
                    // 			$value['interestBuyerId']	= $buyerVal['buyer_id'];
                    // 		}
                    // 	}
                    // }
                }
            }
        }
        // echo $this->db->last_query();exit;
        $image_path = $this->config_url["trade_products"];
        $seller_invoice_path = $this->config_url["seller_invoice_path"];
        $client_profile_path = $this->config_url["partner_img_url"];
        $prod_master_image_path = $this->config_url["prod_master_image_path"];
        if (!empty($result)) {
            $response = [
                "success" => 1,
                "data" => $result,
                "message" => lang("Listed_Successfully"),
                "total" => $num_rows,
                "image_path" => $image_path,
                "seller_invoice_path" => $seller_invoice_path,
                "client_profile_path" => $client_profile_path,
                "prod_master_image_path" => $prod_master_image_path,
                "select_query" => $select_query . $order_query,
            ];
        } else {
            $response = [
                "success" => 0,
                "data" => [],
                "message" => lang("Data_Not_Found"),
                "select_query" => $select_query . $order_query,
            ];
        }
        $this->api_response($response);
        exit();
    }
    // List all or single trade products
    public function manage_product_post()
    {
        // Filter data
        $id = trim($this->input->post("id"));
        $prod_name = $this->input->post("prod_name");
        $prod_variety = $this->input->post("prod_variety");
        $state = $this->input->post("state");
        $city = $this->input->post("city");
        $exp_date_from = $this->input->post("exp_date_from")
            ? date("Y-m-d", strtotime($this->input->post("exp_date_from")))
            : "";
        $exp_date_to = $this->input->post("exp_date_to")
            ? date("Y-m-d", strtotime($this->input->post("exp_date_to")))
            : "";
        $price_from = $this->input->post("price_from");
        $price_to = $this->input->post("price_to");
        $negotiations = $this->input->post("negotiations") ?? "";
        $certifcations = $this->input->post("certifcations") ?? "";
        $prodCatId = $this->input->post("prod_cat_id");
        $trade_status = $this->input->post("trade_status") ?? "";
        $buyer_id = $this->input->post("buyer_id");
        $status = $this->input->post("status");
        // 3:Live, 4: Sold, 5: Complete,6: Expired
        $display_status = [3, 4, 5, 6, 7, 9];
        // $display_status	= [3];
        $selected_lang = $this->selected_lang;
        $table = "trade_product as tp";
        $limit = 60;
        $start = trim($this->input->post("start"));
        if (empty($start)) {
            $start = 1;
        }
        $sort_filter = $this->input->post("sort_filter");
        $start_chk = $start - 1;
        if ($start_chk != 0) {
            $start_sql = $limit * $start_chk;
        } else {
            $start_sql = 0;
        }
        $select_list =
            " tp.id, tp.user_id, tp.prod_cat_id, tp.prod_type_id, pt.title as product_type_title, tp.prod_details, tp.prod_id, pm.title as product_title, pm.logo as product_logo, tp.prod_variety_id, pv.title as product_variety_title, tp.active_till_date, tp.surplus, tp.surplus_unit, tp.other_details, tp.sell_qty, tp.sell_qty_unit, tp.price, tp.price_unit, tp.with_logistic_partner, tp.with_packging, tp.packaging_master_id, pkg.title as packaging_title, tp.storage_type_id, st.title as storage_type_title, s.id as state_id, s.name as state_name, c.id as city_id, c.name as city_name, tp.pickup_location, tp.other_distance, tp.produce_to_highway_distance, tp.advance_payment, tp.negotiations, tp.certifcations, tp.trade_status, tp.partial_trade, tp.status, tp.reason, tp.added_date, tp.expiry_date, tp.approved_date, tp.rejected_date, tp.prod_images, tp.updated_on ";
        $select_query = "SELECT $select_list FROM $table
        LEFT JOIN prod_master as pm ON pm.id = tp.prod_id
        LEFT JOIN prod_variety as pv ON pv.id = tp.prod_variety_id
        LEFT JOIN prod_type as pt ON pt.id = tp.prod_type_id
        LEFT JOIN packaging_master as pkg ON pkg.id = tp.packaging_master_id
		LEFT JOIN storage_type as st ON st.id = tp.storage_type_id
		LEFT JOIN states_new as s ON s.id = tp.state
		LEFT JOIN cities_new as c ON c.id = tp.city
        WHERE tp.is_deleted = 'false' AND tp.is_active = 'true' ";
        if (empty($status)) {
            $select_query .=
                " AND CAST(tp.status AS INTEGER) IN (" .
                implode(", ", $display_status) .
                ") ";
        } else {
            $select_query .=
                " AND CAST(tp.status AS INTEGER) = " . $status . " ";
        }
        if (!empty($id)) {
            $select_query .= " AND tp.id = '" . $id . "' ";
        }
        if (!empty($prod_name)) {
            $select_query .= " AND pm.title LIKE '%" . $prod_name . "%' ";
        }
        if (!empty($prod_variety)) {
            $select_query .=
                " AND tp.prod_variety_id = '" . $prod_variety . "' ";
        }
        if (!empty($state)) {
            $select_query .= " AND tp.state = '" . $state . "' ";
        }
        if (!empty($city)) {
            $select_query .= " AND tp.city = '" . $city . "' ";
        }
        if (!empty($exp_date_from) && !empty($exp_date_to)) {
            if ($exp_date_from <= $exp_date_to) {
                $select_query .=
                    " AND tp.expiry_date BETWEEN '" .
                    $exp_date_from .
                    "' AND '" .
                    $exp_date_to .
                    "' ";
            } else {
                $response = [
                    "success" => 0,
                    "data" => [],
                    "message" =>
                        "Expiry from date is always less then expiry to date!",
                ];
                $this->api_response($response);
                exit();
            }
        }
        if (!empty($price_from)) {
            $select_query .=
                " AND CAST(tp.price AS INTEGER) > " . $price_from . " ";
        }
        if (!empty($price_to)) {
            $select_query .=
                " AND CAST(tp.price AS INTEGER) < " . $price_to . " ";
        }
        if (!empty($negotiations)) {
            $select_query .= " AND tp.negotiations = '" . $negotiations . "' ";
        }
        if (!empty($certifcations)) {
            $select_query .=
                " AND tp.certifcations = '" . $certifcations . "' ";
        }
        if (!empty($prodCatId)) {
            $select_query .= " AND tp.prod_cat_id = '" . $prodCatId . "' ";
        }
        // if (!empty($trade_status)) {
        //     $select_query  .= " AND tp.trade_status = '". $trade_status ."' ";
        // }
        // $order_query  .= " ORDER BY tp.updated_on DESC ";
        // $order_query  .= " ORDER BY tp.added_date DESC ";
        // ORDER BY
        $order_query .= " ORDER BY
		CASE
		  WHEN tp.updated_on IS NOT NULL THEN tp.updated_on
		  ELSE tp.added_date
		END DESC ";
        $limit_query .= " LIMIT " . $limit . " OFFSET " . $start_sql;
        // echo $select_query.$order_query.$limit_query;exit;
        // Get total number of rows
        $num_row_query = $this->db->query($select_query . $order_query);
        $num_rows = $num_row_query->num_rows();
        // Get list of all data
        // $row    = $this->db->query($select_query.$order_query.$limit_query);
        $row = $this->db->query($select_query . $order_query);
        $res = $row->result_array();
        $result = [];
        if (!empty($res)) {
            foreach ($res as $key => $value) {
                /* Start Get product details : Akash */
                $prod_details = $value["prod_details"];
                $product_details = array_filter(PROD_DETAILS, function (
                    $product_details_data
                ) use ($prod_details) {
                    return $product_details_data["id"] == $prod_details;
                });
                $product_details = array_values($product_details);
                $value["product_details_title"] = $product_details[0]["title"];
                /* End Get product details */
                /* Start Get product category details : Akash */
                $prod_cat_id = $value["prod_cat_id"];
                $product_category = array_filter(PROD_CAT, function (
                    $product_category_data
                ) use ($prod_cat_id) {
                    return $product_category_data["id"] == $prod_cat_id;
                });
                $product_category = array_values($product_category);
                $value["product_category_title"] =
                    $product_category[0]["title"];
                /* End Get product category details */
                /* Start Get unit details : Akash */
                $get_unit_list_ids = [
                    "surplus_unit",
                    "sell_qty_unit",
                    "price_unit",
                ];
                // $prod_cat_id = $value['prod_cat_id'];
                foreach ($get_unit_list_ids as $unit_id) {
                    $unitId = $value[$unit_id];
                    $product_unit = array_filter(PROD_UNIT, function (
                        $product_unit_data
                    ) use ($unitId) {
                        return $product_unit_data["id"] == $unitId;
                    });
                    $product_unit = array_values($product_unit);
                    $value[$unit_id . "_title"] = $product_unit[0]["title"];
                }
                /* End Get unit details */
                /* Start Get Status details : Akash */
                $get_status_list_ids = ["status", "trade_status"];
                foreach ($get_status_list_ids as $status_id) {
                    $statusId = $value[$status_id];
                    $statusList = array_filter(TRADE_STATUS_LIST, function (
                        $statusList_data
                    ) use ($statusId) {
                        return $statusList_data["id"] == $statusId;
                    });
                    $statusList = array_values($statusList);
                    $value[$status_id . "_title"] = $statusList[0]["title"];
                    $value[$status_id . "_class"] =
                        $statusList[0]["statusClass"];
                }
                /* End Get Status details */
                /* Start Get season details : Akash */
                $other_details = json_decode($value["other_details"], true);
                $season_arr = ["season_from", "season_to"];
                $yield_arr = ["yield_from_unit", "yield_to_unit"];
                $season_text = "";
                foreach ($other_details as $detailsKey => $detailsVal) {
                    if (in_array($detailsKey, $season_arr)) {
                        $season = array_filter(SEASON_LIST, function (
                            $season_data
                        ) use ($detailsVal) {
                            return $season_data["id"] == $detailsVal;
                        });
                        $season = array_values($season);
                        if ($detailsKey == "season_from") {
                            $season_text .=
                                "From - " . $season[0]["title"] . ", ";
                        } else {
                            $season_text .= " To - " . $season[0]["title"];
                        }
                    }
                    if (in_array($detailsKey, $yield_arr)) {
                        $yield = array_filter(PROD_UNIT, function (
                            $yield_data
                        ) use ($detailsVal) {
                            return $yield_data["id"] == $detailsVal;
                        });
                        $yield = array_values($yield);
                        $yield_text = $yield[0]["title"];
                        $other_details[$detailsKey . "_text"] = $yield_text;
                    }
                }
                $value["other_details"] = $other_details;
                $value["season_text"] = $season_text;
                /* End Get season details */
                if ($value["with_logistic_partner"] == "t") {
                    $value["logistic_text"] = "Included";
                } else {
                    $value["logistic_text"] = "Not included";
                }
                if ($value["with_packging"] == "t") {
                    $value["packaging_text"] = $value["packging_title"];
                } else {
                    $value["packaging_text"] = "";
                }
                // $value['active_till_date'] = date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($value['active_till_date'])));
                // $value['added_date'] = date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($value['added_date'])));
                // $value['expiry_date'] = date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($value['expiry_date'])));
                // $prod_images	= json_decode($value['prod_images']);
                // $value['other_details']	= json_decode($value['other_details']);
                $value["other_distance"] = json_decode(
                    $value["other_distance"]
                );
                $prod_images = json_decode($value["prod_images"], true);
                $prodImages = [];
                foreach ($prod_images as $images) {
                    $prodImages = array_merge($prodImages, $images);
                }
                $value["prod_thumbnail"] =
                    $this->config_url["prod_master_image_path"] .
                    "/" .
                    $value["product_logo"];
                $value["all_prod_images"] = $prodImages;
                // Highest Bid
                $qry = $this->db->query(
                    "SELECT MAX(bid_price) as highestBid FROM trade_product_bidding WHERE is_deleted = 'false' AND is_active = 'true' AND trade_product_id = " .
                        $value["id"] .
                        " "
                );
                $res = $qry->row_array();
                $value["highestBid"] = $res["highestbid"] ?? $value["price"];
                // Get Trade Product Bidding data
                if (!empty($buyer_id)) {
                    if ($value["prod_cat_id"] != 2) {
                        $select_columns = [
                            "id",
                            "buyer_id",
                            "seller_id",
                            "trade_product_id",
                            "qty",
                            "qty_unit",
                            "bid_price",
                            "bid_date",
                            "bid_count",
                            "seller_action",
                            "seller_action_date",
                            "buyer_action",
                            "buyer_action_date",
                            "bid_status",
                        ];
                        $where_condition = [
                            "buyer_id" => $buyer_id,
                            "buyer_action != " => "3",
                            "trade_product_id" => $value["id"],
                            "is_deleted" => "false",
                            "is_active" => "true",
                        ];
                        $order_by =
                            " seller_action asc, buyer_action asc, id desc ";
                        $trade_product_bidding = $this->Masters_model->get_data(
                            $select_columns,
                            "trade_product_bidding",
                            $where_condition,
                            null,
                            $order_by
                        );
                        // print_r($trade_product_bidding);exit;
                        if (!empty($trade_product_bidding)) {
                            $bidding = [];
                            $value["bid_revoked_date"] = $value[
                                "bid_rejected_date"
                            ] = null;
                            foreach (
                                $trade_product_bidding
                                as $bid_key => $bid_val
                            ) {
                                $value["revoke_expire"] = $bid_val[
                                    "revoke_expire"
                                ] = false;
                                $value["revoke_time"] = $bid_val[
                                    "revoke_time"
                                ] = null;
                                // if($bid_val['buyer_id'] == $buyer_id){
                                $value["rating_details"] = show_rating(
                                    $bid_val["seller_id"],
                                    "seller"
                                );
                                $value["bidBuyerId"] = $bid_val["buyer_id"];
                                $value["tradeProductBiddingId"] =
                                    $bid_val["id"];
                                $value["buyer_action"] =
                                    $bid_val["buyer_action"];
                                $value["bidStatus"] = $bid_val["bid_status"];
                                $value["bid_price"] = $bid_val["bid_price"];
                                /* Start Get buyer_action_title details : Akash */
                                $buyer_action_status = $bid_val["buyer_action"];
                                $buyer_action = array_filter(
                                    BUYER_TRADE_STATUS,
                                    function ($buyer_action_data) use (
                                        $buyer_action_status
                                    ) {
                                        return $buyer_action_data["id"] ==
                                            $buyer_action_status;
                                    }
                                );
                                $buyer_action = array_values($buyer_action);
                                $value["buyer_action_title"] =
                                    $buyer_action[0]["title"];
                                /* End Get buyer_action_title details */
                                $value["seller_action"] =
                                    $bid_val["seller_action"];
                                /* Start Get seller_action_title details : Akash */
                                $seller_action_status =
                                    $bid_val["seller_action"];
                                $seller_action = array_filter(
                                    SELLER_TRADE_STATUS,
                                    function ($seller_action_data) use (
                                        $seller_action_status
                                    ) {
                                        return $seller_action_data["id"] ==
                                            $seller_action_status;
                                    }
                                );
                                $seller_action = array_values($seller_action);
                                $value["seller_action_title"] =
                                    $seller_action[0]["title"];
                                /* End Get seller_action_title details */
                                // $value['bid_place_date']= date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($bid_val['bid_date'])));
                                $value["bid_place_date"] = $bid_val["bid_date"];
                                // Start: Revoke expire functionality
                                // $seller_action_date		= date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($bid_val['seller_action_date'])));
                                $seller_action_date =
                                    $bid_val["seller_action_date"];
                                if ($bid_val["seller_action"] == 9) {
                                    $get_revoke_time_setting = get_config_settings(
                                        "revoke_time"
                                    );
                                    if (
                                        !empty($get_revoke_time_setting) &&
                                        !empty(
                                            $get_revoke_time_setting[
                                                "description"
                                            ]
                                        )
                                    ) {
                                        $revoke_time = date(
                                            "Y-m-d H:i:s",
                                            strtotime(
                                                $seller_action_date .
                                                    " + " .
                                                    $get_revoke_time_setting[
                                                        "description"
                                                    ] .
                                                    " minute"
                                            )
                                        );
                                    } else {
                                        $revoke_time = date(
                                            "Y-m-d H:i:s",
                                            strtotime(
                                                $seller_action_date .
                                                    " + 20 minute"
                                            )
                                        );
                                    }
                                    $value["revoke_time"] = $bid_val[
                                        "revoke_time"
                                    ] = $revoke_time;
                                    $current_time = strtotime(
                                        date("Y-m-d H:i:s")
                                    );
                                    $expire_time = strtotime($revoke_time);
                                    $value["current_time"] = $bid_val[
                                        "current_time"
                                    ] = date("Y-m-d H:i:s");
                                    if ($expire_time > $current_time) {
                                        $value["revoke_expire"] = $bid_val[
                                            "revoke_expire"
                                        ] = true;
                                        $revoke_time_left =
                                            round(
                                                abs(
                                                    $expire_time - $current_time
                                                ) / 60,
                                                2
                                            ) . " minute";
                                        // Calculate the time difference in seconds
                                        $timeDifferenceInSeconds =
                                            $expire_time - $current_time;
                                        // Convert the time difference to minutes
                                        $timeDifferenceInMinutes =
                                            $timeDifferenceInSeconds / 60;
                                        $value["revoke_time_left"] = $bid_val[
                                            "revoke_time_left"
                                        ] = $timeDifferenceInMinutes;
                                    } else {
                                        $value["revoke_expire"] = $bid_val[
                                            "revoke_expire"
                                        ] = false;
                                        $value["revoke_time_left"] = $bid_val[
                                            "revoke_time_left"
                                        ] = 0;
                                    }
                                }
                                // End: Revoke expire functionality
                                // }
                                $buyer_detail = get_client_detail(
                                    $bid_val["buyer_id"]
                                );
                                $buyer_name =
                                    $buyer_detail["first_name"] .
                                    " " .
                                    $buyer_detail["last_name"];
                                $seller_action_date = date(
                                    "Y-m-d H:i:s",
                                    strtotime($bid_val["seller_action_date"])
                                );
                                $buyer_action_date = date(
                                    "Y-m-d H:i:s",
                                    strtotime($bid_val["buyer_action_date"])
                                );
                                $value["sold_to_buyer_id"] = null;
                                if (
                                    $bid_val["seller_action"] == 1 ||
                                    $bid_val["seller_action"] == 5
                                ) {
                                    $value["sold_to_buyer_id"] =
                                        $bid_val["buyer_id"];
                                    $value["sold_to"] = ucwords($buyer_name);
                                    $value["sold_on"] = $seller_action_date;
                                    $value["bidding_id"] = $bid_val["id"];
                                    $value["sold_price"] =
                                        $bid_val["bid_price"];
                                    $bid_val[
                                        "seller_action_date"
                                    ] = $seller_action_date;
                                    $bid_val[
                                        "buyer_action_date"
                                    ] = $buyer_action_date;
                                } else {
                                    $tpb_query =
                                        "SELECT * FROM trade_product_bidding WHERE is_active = 'true' AND is_deleted = 'false' AND trade_product_id = " .
                                        $value["id"] .
                                        " AND CAST(seller_action AS INTEGER) IN (1, 5) ";
                                    $tpb_row = $this->db->query($tpb_query);
                                    $tpb_res = $tpb_row->row_array();
                                    if (!empty($tpb_res)) {
                                        $buyerDetail = get_client_detail(
                                            $tpb_res["buyer_id"]
                                        );
                                        $value["sold_to_buyer_id"] =
                                            $tpb_res["buyer_id"];
                                        $value["sold_to"] =
                                            ucwords(
                                                $buyerDetail["first_name"]
                                            ) .
                                            " " .
                                            ucwords($buyerDetail["last_name"]);
                                        $value["sold_on"] =
                                            $tpb_res["seller_action_date"];
                                        $value["bidding_id"] = $tpb_res["id"];
                                        $value["sold_price"] =
                                            $tpb_res["bid_price"];
                                    }
                                }
                                $bid_val["buyer_profile_image"] =
                                    $buyer_detail["profile_image"];
                                $bid_val["buyer_name"] = $buyer_name;
                                /* Start Get Status details : Akash */
                                $bidStatusId = $bid_val["bid_status"];
                                $bidStatusList = array_filter(
                                    TRADE_STATUS_LIST,
                                    function ($bidStatusList_data) use (
                                        $bidStatusId
                                    ) {
                                        return $bidStatusList_data["id"] ==
                                            $bidStatusId;
                                    }
                                );
                                $bidStatusList = array_values($bidStatusList);
                                $bid_val["bid_status_title"] =
                                    $bidStatusList[0]["title"];
                                /* End Get Status details */
                                /* Start Get Status details : Akash */
                                $qtyUnitId = $bid_val["qty_unit"];
                                $qtyUnitList = array_filter(
                                    PROD_UNIT,
                                    function ($qtyUnitList_data) use (
                                        $qtyUnitId
                                    ) {
                                        return $qtyUnitList_data["id"] ==
                                            $qtyUnitId;
                                    }
                                );
                                $qtyUnitList = array_values($qtyUnitList);
                                $bid_val["qty_unit_title"] =
                                    $qtyUnitList[0]["title"];
                                /* End Get Status details */
                                /* Start Get Incentive details : Akash */
                                $incentive_status_id =
                                    $bid_val["incentive_status"];
                                $incentive = array_filter(
                                    INCENTIVE_STATUS,
                                    function ($incentive_data) use (
                                        $incentive_status_id
                                    ) {
                                        return $incentive_data["id"] ==
                                            $incentive_status_id;
                                    }
                                );
                                $incentive = array_values($incentive);
                                $bid_val["incentive_title"] =
                                    $incentive[0]["title"];
                                /* End Get Incentive category details */
                                // if($bid_val['seller_action'] != 3){
                                // 	$bidding[] = $bid_val;
                                // }
                                $bidding[] = $bid_val;
                                // Start: Manage Product status
                                $sellerAction = (int) $bid_val["seller_action"];
                                $buyerAction = (int) $bid_val["buyer_action"];
                                $buyerStatus = [1, 2];
                                $sellerStatus = [2, 3];
                                if (
                                    in_array($buyerAction, $buyerStatus) &&
                                    (int) $value["status"] === 3
                                ) {
                                    if (
                                        in_array($sellerAction, $sellerStatus)
                                    ) {
                                        $manageProductStatus = $sellerAction;
                                        $manageProductStatusListing = SELLER_TRADE_STATUS;
                                    } else {
                                        $manageProductStatus = $buyerAction;
                                        $manageProductStatusListing = BUYER_TRADE_STATUS;
                                    }
                                } else {
                                    $manageProductStatus = $value["status"];
                                    $manageProductStatusListing = TRADE_STATUS_LIST;
                                }
                                if ($manageProductStatus == 7) {
                                    $value["sold_to"] = "Sold out of system";
                                }
                                $manageProductStatus =
                                    $manageProductStatus == 7
                                        ? 4
                                        : $manageProductStatus;
                                $value[
                                    "manage_product_status_id"
                                ] = $manageProductStatus;
                                /* Start Get buyer_action_title details : Akash */
                                $manageProdAction = array_filter(
                                    $manageProductStatusListing,
                                    function ($manageProdAction_data) use (
                                        $manageProductStatus
                                    ) {
                                        return $manageProdAction_data["id"] ==
                                            $manageProductStatus;
                                    }
                                );
                                $manageProdAction = array_values(
                                    $manageProdAction
                                );
                                $value["manage_product_status"] =
                                    $manageProdAction[0]["title"];
                                $value["manage_product_status_class"] =
                                    $manageProdAction[0]["statusClass"];
                                /* End Get buyer_action_title details */
                                // End: Manage Product status
                                if ($sellerAction == 3) {
                                    $value["bid_rejected_date"] =
                                        $bid_val["seller_action_date"];
                                }
                                if ($sellerAction == 2) {
                                    $value["bid_revoked_date"] =
                                        $bid_val["seller_action_date"];
                                } elseif ($buyerAction == 2) {
                                    $value["bid_revoked_date"] =
                                        $bid_val["buyer_action_date"];
                                }
                            }
                            // $cond = array('trade_product_id' => $value['id'], 'is_deleted' => 'false', 'is_active' => 'true');
                            // $trade_product_bidding_count	= $this->Masters_model->get_data(array('id'),'trade_product_bidding',$cond);
                            $select_query =
                                "SELECT COUNT(id) as count
							FROM trade_product_bidding
							WHERE is_active = 'true'
							AND is_deleted = 'false'
							AND trade_product_id = " .
                                $value["id"] .
                                " ";
                            $query_row = $this->db->query($select_query);
                            $bidding_count = $query_row->row_array();
                            $value["trade_product_bidding_count"] =
                                $bidding_count["count"];
                            $value["trade_product_bidding"] = $bidding;
                            $value["buyer_intrest_count"] = 0;
                            $value["buyer_intrest"] = [];
                            if (empty($trade_status)) {
                                $result[] = $value;
                            } elseif (
                                !empty($trade_status) &&
                                $manageProductStatus == $trade_status
                            ) {
                                $result[] = $value;
                            }
                        }
                    } else {
                        $value["rating_details"] = show_rating(
                            $value["user_id"],
                            "seller"
                        );
                        $select_query =
                            "SELECT *
						FROM trade_product_interest as tpi
						WHERE tpi.is_active = 'true'
						AND tpi.is_deleted = 'false'
						AND tpi.buyer_id = $buyer_id
						AND tpi.trade_product_id = " .
                            $value["id"] .
                            " ";
                        $query_row = $this->db->query($select_query);
                        $interest_data = $query_row->result_array();
                        $value["interestBuyerId"] = $buyer_id;
                        if (!empty($interest_data)) {
                            $select_query1 =
                                "SELECT *
							FROM trade_product_interest as tpi
							WHERE tpi.is_active = 'true'
							AND tpi.is_deleted = 'false'
							AND tpi.trade_product_id = " .
                                $value["id"] .
                                " ";
                            $query_row1 = $this->db->query($select_query1);
                            $insert_data_count = $query_row1->result_array();
                            $value["buyer_intrest_count"] = count(
                                $insert_data_count
                            );
                            $result[] = $value;
                        }
                        // $interestData = [
                        // 	'trade_product_id'	=> $value['id'],
                        // 	'user_id'			=> $buyer_id,
                        // 	'interest_shown'	=> false,
                        // 	'type'				=> 'manage_product',
                        // ];
                        // $interest_data = $this->buyers_interest_list($interestData);
                        // // print_r($interest_data);exit;
                        // if(!empty($interest_data) && $interest_data['buyer_interest_count'] > 0){
                        // 	$value['buyer_intrest_count']	= $interest_data['buyer_interest_count'];
                        // 	$value['buyer_intrest']			= $interest_data['user_data'];
                        // 	$result[] = $value;
                        // }
                    }
                }
            }
        }
        // echo $this->db->last_query();exit;
        $image_path = $this->config_url["trade_products"];
        $seller_invoice_path = $this->config_url["seller_invoice_path"];
        $client_profile_path = $this->config_url["partner_img_url"];
        $prod_master_image_path = $this->config_url["prod_master_image_path"];
        if (!empty($result)) {
            $response = [
                "success" => 1,
                "data" => $result,
                "message" => lang("Listed_Successfully"),
                "total" => $num_rows,
                "image_path" => $image_path,
                "seller_invoice_path" => $seller_invoice_path,
                "client_profile_path" => $client_profile_path,
                "prod_master_image_path" => $prod_master_image_path,
            ];
        } else {
            $response = [
                "success" => 0,
                "data" => [],
                "message" => lang("Data_Not_Found"),
            ];
        }
        $this->api_response($response);
        exit();
    }
    // Biding process
    public function trade_product_bidding_post()
    {
        // print_r($_POST);exit;
        $product_id = $this->input->post("product_id");
        $buyer_id = $this->input->post("buyer_id");
        $qty = $this->input->post("qty");
        $qty_unit = $this->input->post("qty_unit");
        $bid_price = $this->input->post("bid_price");
        $current_date = date("Y-m-d H:i:s");
        $status_id = array_column(BUYER_TRADE_STATUS, "id");
        $status_title = array_column(BUYER_TRADE_STATUS, "map_key");
        $bid_trade_status = array_combine($status_title, $status_id);
        if (isset($bid_trade_status["bid_placed"])) {
            $buyer_action = $bid_trade_status["bid_placed"];
        } else {
            $buyer_action = 1;
        }
        if (!empty($product_id) && !empty($buyer_id) && !empty($bid_price)) {
            $trade_product = $this->Masters_model->get_data(
                "*",
                "trade_product",
                [
                    "id" => $product_id,
                    "is_deleted" => "false",
                    "is_active" => "true",
                ]
            );
            $seller_id = $trade_product[0]["user_id"];
            if (!empty($trade_product[0])) {
                $trade_product_bidding = $this->Masters_model->get_data(
                    "*",
                    "trade_product_bidding",
                    [
                        "buyer_id" => $buyer_id,
                        "seller_id" => $seller_id,
                        "trade_product_id" => $product_id,
                        "is_deleted" => "false",
                        "is_active" => "true",
                    ]
                );
                // echo'<pre>';print_r($trade_product_bidding);exit;
                if (!empty($trade_product_bidding)) {
                    if (
                        in_array($trade_product_bidding[0]["seller_action"], [
                            1,
                            2,
                            3,
                        ]) ||
                        in_array($trade_product_bidding[0]["buyer_action"], [
                            1,
                            2,
                            3,
                        ])
                    ) {
                        if (
                            strtotime($trade_product[0]["expiry_date"]) <
                            strtotime($current_date)
                        ) {
                            $res_data = [
                                "Expiry Date" =>
                                    $trade_product[0]["expiry_date"],
                                "Current Date" => $current_date,
                            ];
                            $response = [
                                "success" => 0,
                                "data" => $res_data,
                                "message" => "Current product is expired!",
                            ];
                            $this->api_response($response);
                            exit();
                        }
                        if (!empty($qty)) {
                            if ($trade_product[0]["sell_qty"] < $qty) {
                                $res_data = [
                                    "sell_qty" => $trade_product[0]["sell_qty"],
                                    "request_qty" => $qty,
                                ];
                                $response = [
                                    "success" => 0,
                                    "data" => $res_data,
                                    "message" =>
                                        "Request quantity dose not match with sell quantity!",
                                ];
                                $this->api_response($response);
                                exit();
                            }
                        } else {
                            $response = [
                                "success" => 0,
                                "data" => $res_data,
                                "message" => "Request quantity is empty!",
                            ];
                            $this->api_response($response);
                            exit();
                        }
                        if ($trade_product[0]["sell_qty_unit"] != $qty_unit) {
                            $qty_unit = $trade_product[0]["sell_qty_unit"];
                        }
                        $newBidCount =
                            (int) $trade_product_bidding[0]["bid_count"] + 1;
                        $update_data = [
                            "qty" => $qty,
                            "qty_unit" => $qty_unit,
                            "bid_price" => $bid_price,
                            "bid_date" => $current_date,
                            "buyer_action" => $buyer_action,
                            "seller_action" => null,
                            "buyer_action_date" => $current_date,
                            "bid_status" => 1,
                            "bid_count" => $newBidCount,
                        ];
                        // print_r($update_data);exit;
                        $this->db->where("id", $trade_product_bidding[0]["id"]);
                        $this->db->update(
                            "trade_product_bidding",
                            $update_data
                        );
                        $response = [
                            "success" => 1,
                            "data" => $update_data,
                            "message" => "Updated Successfully",
                        ];
                        $this->api_response($response);
                        exit();
                    } else {
                        $response = [
                            "success" => 0,
                            "data" => [],
                            "message" => "Already Bid on this product!",
                        ];
                        $this->api_response($response);
                        exit();
                    }
                }
                if (
                    strtotime($trade_product[0]["expiry_date"]) <
                    strtotime($current_date)
                ) {
                    $res_data = [
                        "Expiry Date" => $trade_product[0]["expiry_date"],
                        "Current Date" => $current_date,
                    ];
                    $response = [
                        "success" => 0,
                        "data" => $res_data,
                        "message" => "Current product is expired!",
                    ];
                    $this->api_response($response);
                    exit();
                }
                if (!empty($qty)) {
                    if ($trade_product[0]["sell_qty"] < $qty) {
                        $res_data = [
                            "sell_qty" => $trade_product[0]["sell_qty"],
                            "request_qty" => $qty,
                        ];
                        $response = [
                            "success" => 0,
                            "data" => $res_data,
                            "message" =>
                                "Request quantity dose not match with sell quantity!",
                        ];
                        $this->api_response($response);
                        exit();
                    }
                } else {
                    $response = [
                        "success" => 0,
                        "data" => $res_data,
                        "message" => "Request quantity is empty!",
                    ];
                    $this->api_response($response);
                    exit();
                }
                if ($trade_product[0]["sell_qty_unit"] != $qty_unit) {
                    $qty_unit = $trade_product[0]["sell_qty_unit"];
                }
                $inser_data = [
                    "buyer_id" => $buyer_id,
                    "seller_id" => $seller_id,
                    "trade_product_id" => $product_id,
                    "qty" => $qty,
                    "qty_unit" => $qty_unit,
                    "bid_price" => $bid_price,
                    "bid_date" => $current_date,
                    "buyer_action" => $buyer_action,
                    "buyer_action_date" => $current_date,
                    "bid_status" => 1,
                    "bid_count" => 1,
                ];
                $result = $this->db->insert(
                    "trade_product_bidding",
                    $inser_data
                );
                $inserted_id = $this->db->insert_id();
                $inser_data["inserted_id"] = $inserted_id;
                $response = [
                    "success" => 1,
                    "data" => $inser_data,
                    "message" => lang("Added_Successfully"),
                    "inserted_id" => $inserted_id,
                ];
                if ($result) {
                    $notification_enable = get_config_data(
                        "notification_enable"
                    );
                    if ($notification_enable == 1) {
                        $headers_data = array_change_key_case(
                            $this->input->request_headers(),
                            CASE_LOWER
                        );
                        $selected_lang = $headers_data["lang"]
                            ? $headers_data["lang"]
                            : "en";
                        $notification_data = get_notification_detail(
                            "bid_received",
                            "seller",
                            $selected_lang
                        );
                        $custom_array = $userid = [];
                        if (!empty($notification_data)) {
                            $qry =
                                "SELECT device_id FROM client WHERE is_deleted='false' AND is_active='true' AND (device_id IS NOT NULL AND device_id!= 'null') AND id =" .
                                $seller_id;
                            $userid[] = $seller_id;
                            $custom_array["user_id"] = $userid;
                            $custom_array["map_key"] = "bid_received";
                            $custom_array["reference_id"] = "client";
                            $res_data = $this->db->query($qry);
                            $device_id_data = $res_data->row_array();
                            $token = [];
                            if (count($device_id_data)) {
                                $token[] = $device_id_data["device_id"];
                            }
                            $title = $notification_data["title"];
                            $arr = [
                                "body" => ["{PRODUCT_ID}" => "#" . $product_id],
                            ];
                            $sms_template = get_sms_template(
                                $notification_data["notification_text"],
                                $arr
                            );
                            $message = $sms_template;
                            if ($message != "" && !empty($token)) {
                                // $notifiy = $this->Notification_model->sendPushNotifications_request_dynamic($token, $title, $message, '','','', $type = 'Product_details', $product_id);
                                $notifiy = $this->Notification_model->sendPushNotifications_request_dynamic(
                                    $token,
                                    $title,
                                    $message,
                                    "",
                                    "",
                                    $custom_array,
                                    $type = "Product_bid_details",
                                    $product_id
                                );
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
            } else {
                $response = [
                    "success" => 0,
                    "data" => [],
                    "message" => "Current product is not available!",
                ];
            }
        } else {
            $response = [
                "success" => 0,
                "data" => [],
                "message" => lang("Missing_Parameter"),
            ];
        }
        $this->api_response($response);
        exit();
    }
    // Applay actions by buyer
    public function buyer_action_post()
    {
        $bid_id = $this->input->post("id");
        $status = $this->input->post("status");
        $product_id = $this->input->post("product_id");
        $seller_id = $this->input->post("seller_id");
        $buyer_id = $this->input->post("buyer_id");
        $current_date = date("Y-m-d H:i:s");
        $status_id = array_column(TRADE_STATUS_LIST, "id");
        $status_title = array_column(TRADE_STATUS_LIST, "title");
        $trade_status = array_combine($status_title, $status_id);
        // print_r($_POST);exit;
        switch ($status) {
            // Revoked bid by buyer
            case "2":
                $reason = "Revoked by buyer";
                $bid_status = $status;
                $trade_product = isset($trade_status["Live"])
                    ? $trade_status["Live"]
                    : 3;
                $is_deleted = false;
                break;
            // Canceled bid by buyer
            case "3":
                $reason = "Canceled by buyer";
                $bid_status = $status;
                $trade_product = isset($trade_status["Live"])
                    ? $trade_status["Live"]
                    : 3;
                $is_deleted = true;
                break;
            default:
                $reason = null;
                $bid_status = null;
                break;
        }
        $update_data = [];
        $update_data = [
            "status" => $trade_product,
            "reason" => $reason,
            "updated_by_id" => $buyer_id,
            "updated_on" => $current_date,
        ];
        $this->db->where("id", $product_id);
        $trade_product_result = $this->db->update(
            "trade_product",
            $update_data
        );
        // update seller_action of trade_product_biding by accept
        $update_data = [];
        $update_data = [
            "seller_action" => null,
            "seller_action_date" => null,
            "buyer_action" => $status,
            "buyer_action_date" => $current_date,
            "bid_status" => $bid_status,
            "updated_by_id" => $buyer_id,
            "updated_on" => $current_date,
            "is_deleted" => $is_deleted,
        ];
        $this->db->where("id", $bid_id);
        $trade_product_result = $this->db->update(
            "trade_product_bidding",
            $update_data
        );
        $data = [];
        $data["trade_product"] = $this->Masters_model->get_data(
            ["id", "user_id", "status", "reason", "prod_id"],
            "trade_product",
            [
                "id" => $product_id,
                "is_deleted" => "false",
                "is_active" => "true",
            ]
        );
        $data["trade_product_bidding"] = $this->Masters_model->get_data(
            [
                "id",
                "buyer_id",
                "seller_id",
                "trade_product_id",
                "seller_action",
                "bid_status",
            ],
            "trade_product_bidding",
            [
                "buyer_id" => $buyer_id,
                "seller_id" => $seller_id,
                "trade_product_id" => $product_id,
                "is_deleted" => "false",
                "is_active" => "true",
            ]
        );
        if ($trade_product_result) {
            $notification_enable = get_config_data("notification_enable");
            $headers_data = array_change_key_case(
                $this->input->request_headers(),
                CASE_LOWER
            );
            $selected_lang = $headers_data["lang"]
                ? $headers_data["lang"]
                : "en";
            if ($status == 2) {
                $sms_type = "NERACE_Bid_Revoked_by_Buyer";
                $buyer_detail = get_client_detail($buyer_id);
                $buyer_name = ucwords(
                    $buyer_detail["first_name"] .
                        " " .
                        $buyer_detail["last_name"]
                );
                $replace = [
                    "body" => [
                        "{PRODUCT_ID}" => $product_id,
                        "{BUYER_NAME}" => $buyer_name,
                    ],
                ];
                $seller_detail = get_client_detail($seller_id);
                $seller_phone = $seller_detail["phone"];
                $resp = dynamic_send_sms(
                    $seller_phone,
                    "",
                    $sms_type,
                    "",
                    $selected_lang,
                    $replace
                );
            }
            if ($notification_enable == 1) {
                $map_key =
                    $status == 2 ? "bid_revoked_by_buyer" : "bid_cancelled";
                $notification_data = get_notification_detail(
                    $map_key,
                    "seller",
                    $selected_lang
                );
                $custom_array = $userid = [];
                if (!empty($notification_data)) {
                    $qry =
                        "SELECT device_id FROM client WHERE is_deleted='false' AND is_active='true' AND (device_id IS NOT NULL AND device_id!= 'null') AND id =" .
                        $seller_id;
                    $res_data = $this->db->query($qry);
                    $device_id_data = $res_data->row_array();
                    $token = [];
                    if (count($device_id_data)) {
                        $token[] = $device_id_data["device_id"];
                    }
                    $userid[] = $seller_id;
                    $custom_array["user_id"] = $userid;
                    $custom_array["map_key"] = $map_key;
                    $custom_array["reference_id"] = "client";
                    $buyer_detail = get_client_detail($buyer_id);
                    $buyer_name = ucwords(
                        $buyer_detail["first_name"] .
                            " " .
                            $buyer_detail["last_name"]
                    );
                    $title = $notification_data["title"];
                    $arr = [
                        "body" => [
                            "{PRODUCT_ID}" => "#" . $product_id,
                            "{BUYER_NAME}" => $buyer_name,
                        ],
                    ];
                    $sms_template = get_sms_template(
                        $notification_data["notification_text"],
                        $arr
                    );
                    $message = $sms_template;
                    if ($message != "" && !empty($token)) {
                        // $notifiy = $this->Notification_model->sendPushNotifications_request_dynamic($token, $title, $message, '','','', $type = 'Product_details', $product_id);
                        $notifiy = $this->Notification_model->sendPushNotifications_request_dynamic(
                            $token,
                            $title,
                            $message,
                            "",
                            "",
                            $custom_array,
                            $type = "Product_bid_details",
                            $product_id
                        );
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
        // Start: Trade activity logs
        $trade_data = [
            "title" => "Buyer Action",
            "description" => "Buyer has taken action on product: " . $reason,
            "userid" => $buyer_id,
            "user_type" => "Buyer",
            "trade_product_id" => $product_id,
            "trade_product_status" => $trade_product,
            "user_action" => $status,
            "reason" => $reason,
        ];
        trade_activity_logs($trade_data);
        // End: Trade activity logs
        if (!empty($data)) {
            $response = [
                "success" => 1,
                "data" => $data,
                "message" => lang("Status_Updated_Successfully"),
            ];
        } else {
            $response = [
                "success" => 0,
                "data" => [],
                "message" => lang("Data_Not_Found"),
            ];
        }
        $this->api_response($response);
        exit();
    }
    // added interest on upcomping product by buyer
    public function add_interest_onproduct_post()
    {
        $buyer_id = $this->input->post("buyer_id");
        $trade_product_id = $this->input->post("trade_product_id");
        if ($buyer_id != "" && $trade_product_id != "") {
            $row = $this->db->query(
                "SELECT * FROM trade_product_interest WHERE trade_product_id = " .
                    $trade_product_id .
                    " AND buyer_id=" .
                    $buyer_id
            );
            $result = $row->row_array();
            if (!empty($result) && count($result) > 0) {
                $seller_id = $result["seller_id"];
                $this->db->where("trade_product_id", $trade_product_id);
                $this->db->where("buyer_id", $buyer_id);
                $res_del = $this->db->delete("trade_product_interest");
                // Update
                // if($result['is_deleted'] == 'f'){
                // 	$update = array(
                // 		'is_deleted'	=> 'true',
                // 		'updated_by_id'	=> $buyer_id,
                // 		'updated_on'	=> current_date(),
                // 	);
                // 	$interestShown = true;
                // } else {
                // 	$update = array(
                // 		'is_deleted'	=> 'false',
                // 		'updated_by_id'	=> $buyer_id,
                // 		'updated_on'	=> current_date(),
                // 	);
                // 	$interestShown = false;
                // }
                // $this->db->where('trade_product_id', $trade_product_id);
                // $this->db->where('buyer_id', $buyer_id);
                // $this->db->update('trade_product_interest', $update);
                $response = [
                    "status" => 1,
                    "data" => [],
                    "message" => lang("Interest_revoke_Msg"),
                ];
            } else {
                // Insert
                // get product details from trade product
                $trade_product_query = $this->db->query(
                    "SELECT user_id FROM trade_product WHERE is_deleted = 'false' AND id = " .
                        $trade_product_id
                );
                $trade_product_result = $trade_product_query->row_array();
                $insert = [
                    "buyer_id" => $buyer_id,
                    "trade_product_id" => $trade_product_id,
                    "seller_id" => $trade_product_result["user_id"],
                    "created_by_id" => $buyer_id,
                    "created_on" => current_date(),
                ];
                $seller_id = $trade_product_result["user_id"];
                $res = $this->db->insert("trade_product_interest", $insert);
                $response = [
                    "status" => 1,
                    "data" => true,
                    "message" => lang("Interest_Shown_Msg"),
                ];
                // Moved to Interest Shown in Manage Product section
            }
            if ($res || $res_del) {
                $notification_enable = get_config_data("notification_enable");
                if ($notification_enable == 1) {
                    $headers_data = array_change_key_case(
                        $this->input->request_headers(),
                        CASE_LOWER
                    );
                    $selected_lang = $headers_data["lang"]
                        ? $headers_data["lang"]
                        : "en";
                    $map_key =
                        $res_del == 1
                            ? "interest_revoked"
                            : "interest_received";
                    $notification_data = get_notification_detail(
                        $map_key,
                        "seller",
                        $selected_lang
                    );
                    $custom_array = $userid = [];
                    if (!empty($notification_data)) {
                        $qry =
                            "SELECT device_id FROM client WHERE is_deleted='false' AND is_active='true' AND (device_id IS NOT NULL AND device_id!= 'null') AND id =" .
                            $seller_id;
                        $res_data = $this->db->query($qry);
                        $device_id_data = $res_data->row_array();
                        $buyer_detail = get_client_detail($buyer_id);
                        $buyer_name = ucwords(
                            $buyer_detail["first_name"] .
                                " " .
                                $buyer_detail["last_name"]
                        );
                        $token = [];
                        if (count($device_id_data)) {
                            $token[] = $device_id_data["device_id"];
                        }
                        $userid[] = $seller_id;
                        $custom_array["user_id"] = $userid;
                        $custom_array["map_key"] = $map_key;
                        $custom_array["reference_id"] = "client";
                        $title = $notification_data["title"];
                        $arr = [
                            "body" => [
                                "{PRODUCT_ID}" => "#" . $trade_product_id,
                                "{BUYER_NAME}" => $buyer_name,
                            ],
                        ];
                        $sms_template = get_sms_template(
                            $notification_data["notification_text"],
                            $arr
                        );
                        $message = $sms_template;
                        if ($message != "" && !empty($token)) {
                            $notifiy = $this->Notification_model->sendPushNotifications_request_dynamic(
                                $token,
                                $title,
                                $message,
                                "",
                                "",
                                $custom_array,
                                $type = "Product_details",
                                $trade_product_id
                            );
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
        } else {
            $response = [
                "status" => 0,
                "data" => [],
                "message" => lang("Missing_Parameter"),
            ];
        }
        $this->api_response($response);
        exit();
    }
    public function buyers_interest_count($trade_product_id)
    {
        $data = [];
        if (!empty($trade_product_id)) {
            $select_query =
                "SELECT *
			FROM trade_product_interest as tpi
			WHERE tpi.is_active = 'true'
			AND tpi.is_deleted = 'false'
			AND tpi.trade_product_id = " .
                $trade_product_id .
                " ";
            $query_row = $this->db->query($select_query);
            $data = $query_row->result_array();
        }
        return count($data);
    }
    // List all new products
    public function new_product_post()
    {
        $prodCatId = $this->input->post("prod_cat_id") ?? 1;
        $status = $this->input->post("status") ?? 3;
        // $buyer_id	= $this->input->post('buyer_id');
        $selected_lang = $this->selected_lang;
        $table = "trade_product as tp";
        $limit = 4;
        $start = trim($this->input->post("start"));
        if (empty($start)) {
            $start = 1;
        }
        $sort_filter = $this->input->post("sort_filter");
        $start_chk = $start - 1;
        if ($start_chk != 0) {
            $start_sql = $limit * $start_chk;
        } else {
            $start_sql = 0;
        }
        $select_list =
            " tp.id, tp.prod_details, tp.prod_id, pm.title as product_title, pm.logo as product_logo, tp.sell_qty, tp.sell_qty_unit, tp.price, tp.price_unit, tp.prod_images, tp.status ";
        $select_query = "SELECT $select_list FROM $table
        LEFT JOIN prod_master as pm ON pm.id = tp.prod_id
        WHERE tp.is_deleted = 'false' AND tp.is_active = 'true' ";
        $select_query .= " AND CAST(tp.status AS INTEGER) = " . $status . " ";
        if (!empty($prodCatId)) {
            $select_query .= " AND tp.prod_cat_id = '" . $prodCatId . "' ";
        }
        $order_query .= " ORDER BY tp.id DESC ";
        $limit_query .= " LIMIT " . $limit . " OFFSET " . $start_sql;
        // Get list of all data
        $row = $this->db->query($select_query . $order_query . $limit_query);
        $res = $row->result_array();
        $result = [];
        if (!empty($res)) {
            foreach ($res as $key => $value) {
                $value["prod_thumbnail"] =
                    $this->config_url["prod_master_image_path"] .
                    "/" .
                    $value["product_logo"];
                // $prod_images				= json_decode($value['prod_images'], true);
                // $prodImages = [];
                // if(!empty($prod_images)){
                // 	foreach ($prod_images as $images) {
                // 		$prodImages = array_merge($prodImages, $images);
                // 	}
                // 	if(!empty($prodImages[0])){
                // 		$value['prod_thumbnail']	= $this->config_url['trade_products'].'/'.$prodImages[0];
                // 	} else {
                // 		$value['prod_thumbnail']	= $this->config_url['prod_master_image_path'].'/'.$value['product_logo'];
                // 	}
                // } else {
                // 	$value['prod_thumbnail']	= $this->config_url['prod_master_image_path'].'/'.$value['product_logo'];
                // }
                $result[] = $value;
            }
        }
        if (!empty($result)) {
            $response = [
                "success" => 1,
                "data" => $result,
                "message" => lang("Listed_Successfully"),
            ];
        } else {
            $response = [
                "success" => 0,
                "data" => [],
                "message" => lang("Data_Not_Found"),
            ];
        }
        $this->api_response($response);
        exit();
    }
    // List all new products
    public function trending_product_post()
    {
        $prodCatId = $this->input->post("prod_cat_id") ?? 1;
        $sold_completed_status = [4, 5];
        $interest_status = [3];
        if ((int) $prodCatId === 2) {
            $table = "trade_product_interest as tpi";
            $select_query =
                "SELECT tp.id, tp.prod_images, COUNT(ti.id) AS bid_count, pm.title as product_title, pm.logo as product_logo
			FROM trade_product tp
			LEFT JOIN trade_product_interest ti ON tp.id = ti.trade_product_id
			LEFT JOIN prod_master as pm ON pm.id = tp.prod_id
			WHERE tp.is_deleted=false AND tp.is_active=true AND ti.is_deleted=false AND ti.is_active=true AND CAST(tp.status AS INTEGER) IN (" .
                implode(", ", $interest_status) .
                ") AND tp.prod_cat_id = " .
                $prodCatId .
                "
			GROUP BY tp.id, tp.prod_images, pm.title, pm.logo
			ORDER BY bid_count DESC
			LIMIT 4";
            // $select_query = "WITH ProductSales AS (
            // 	SELECT tp.prod_id, pm.logo as product_logo, pm.title as product_title, COUNT(ti.*) AS total_sales FROM trade_product tp
            // 	JOIN prod_master pm ON tp.prod_id = pm.id
            // 	JOIN trade_product_interest ti ON tp.id = ti.trade_product_id
            // 	WHERE CAST(tp.status AS INTEGER) IN (".implode(', ', $interest_status).") AND tp.prod_cat_id = ".$prodCatId." AND tp.is_deleted = 'false' AND tp.is_active = 'true' GROUP BY tp.prod_id, pm.logo, pm.title
            // ) SELECT ps.prod_id, ps.product_logo, ps.product_title, ps.total_sales FROM ProductSales ps ORDER BY ps.total_sales DESC LIMIT 4";
        } else {
            $select_query =
                "WITH ProductSales AS ( 
				SELECT tp.prod_id, pm.logo as product_logo, pm.title as product_title, COUNT(*) AS total_sales FROM trade_product tp JOIN prod_master pm ON tp.prod_id = pm.id WHERE CAST(tp.status AS INTEGER) IN (" .
                implode(", ", $sold_completed_status) .
                ") AND tp.prod_cat_id = " .
                $prodCatId .
                " AND tp.is_deleted = 'false' AND tp.is_active = 'true' GROUP BY tp.prod_id, pm.logo, pm.title 
			) SELECT ps.prod_id, ps.product_logo, ps.product_title, ps.total_sales FROM ProductSales ps ORDER BY ps.total_sales DESC LIMIT 4";
        }
        // echo $select_query;exit;
        // Get list of all data
        $row = $this->db->query($select_query);
        $res = $row->result_array();
        $result = [];
        if (!empty($res)) {
            foreach ($res as $key => $value) {
                $value["prod_thumbnail"] =
                    $this->config_url["prod_master_image_path"] .
                    "/" .
                    $value["product_logo"];
                // $prod_images				= json_decode($value['prod_images'], true);
                // $prodImages = [];
                // if(!empty($prod_images)){
                // 	foreach ($prod_images as $images) {
                // 		$prodImages = array_merge($prodImages, $images);
                // 	}
                // 	if(!empty($prodImages[0])){
                // 		$value['prod_thumbnail']	= $this->config_url['trade_products'].'/'.$prodImages[0];
                // 	} else {
                // 		$value['prod_thumbnail']	= $this->config_url['prod_master_image_path'].'/'.$value['product_logo'];
                // 	}
                // } else {
                // 	$value['prod_thumbnail']	= $this->config_url['prod_master_image_path'].'/'.$value['product_logo'];
                // }
                $result[] = $value;
            }
        }
        if (!empty($result)) {
            $response = [
                "success" => 1,
                "data" => $result,
                "message" => lang("Listed_Successfully"),
            ];
        } else {
            $response = [
                "success" => 0,
                "data" => [],
                "message" => lang("Data_Not_Found"),
            ];
        }
        $this->api_response($response);
        exit();
    }
    public function my_stats_post()
    {
        $buyer_id = trim($this->input->post("buyer_id"));
        $category = trim($this->input->post("prod_cat_id")) ?? "";
        $year = $_POST["year"];
        $month = trim($this->input->post("month")) ?? "";
        $day = trim($this->input->post("day")) ?? "";
        if ($year == "") {
            $year = date("Y");
        }
        // echo'year::'.$year;;exit;
        if ((int) $category === 2) {
            // $select_query = "SELECT
            // 	    COUNT(*) AS total_interests,
            // 	SUM(CASE WHEN tpi.is_deleted = 'f' THEN 1 ELSE 0 END) AS total_revoked_bids
            // FROM
            // 	trade_product_interest AS tpi
            // LEFT JOIN
            // 	trade_product AS tp ON tp.id = tpi.trade_product_id
            // WHERE 1=1 AND buyer_id = ".$buyer_id;
            $table = " trade_product_interest as tpi ";
            $select_list =
                " tpi.trade_product_id, tpi.buyer_id, tpi.is_deleted, tpi.is_active ";
            $select_query =
                "SELECT $select_list FROM $table
			LEFT JOIN trade_product as tp ON tp.id = tpi.trade_product_id
			WHERE 1 = 1 AND tpi.buyer_id = " . $buyer_id;
            if (!empty($category)) {
                $select_query .= " AND tp.prod_cat_id = " . $category . " ";
            }
            if (!empty($year)) {
                $select_query .=
                    " AND DATE_PART('year', tpi.created_on) = " . $year . " ";
            }
            if (!empty($month)) {
                $select_query .=
                    " AND DATE_PART('year', tpi.created_on) = " .
                    date("Y") .
                    " ";
                $select_query .=
                    " AND DATE_PART('month', tpi.created_on) = " . $month . " ";
            }
            if (!empty($day)) {
                $select_query .=
                    " AND DATE_PART('year', tpi.created_on) = " .
                    date("Y") .
                    " ";
                $select_query .=
                    " AND DATE_PART('month', tpi.created_on) = " .
                    date("m") .
                    " ";
                $select_query .=
                    " AND DATE_PART('day', tpi.created_on) = " . $day . " ";
            }
            $row = $this->db->query($select_query);
            $result = $row->result_array();
            // echo'<pre>';print_r($result);exit;
            $interest = $revoked = 0;
            foreach ($result as $key => $value) {
                if ($value["is_deleted"] == "f") {
                    $interest++;
                } else {
                    $revoked++;
                }
            }
            $data = [
                [
                    "status" => "1",
                    "row_count" => $interest,
                    "prod_cat_id" => $category,
                    "status_title" => "Interest Shown",
                    "status_class" => "",
                ],
                // ,
                // [
                // 	'status' => '2',
                // 	'row_count' => $revoked,
                // 	'prod_cat_id' => $category,
                // 	'status_title' => 'Interest Revoked',
                // 	'status_class' => '',
                // ],
            ];
            // print_r($data);exit;
        } else {
            $table = "trade_product_bidding as tpib";
            $select_list =
                " tpib.bid_status as status, COUNT(tpib.*) AS row_count, tp.prod_cat_id ";
            $select_query =
                "SELECT $select_list FROM $table
			INNER JOIN trade_product as tp ON tp.id = tpib.trade_product_id
			WHERE tpib.is_deleted = 'false' AND tpib.is_active = 'true' AND tpib.buyer_id = " .
                $buyer_id;
            if (!empty($category)) {
                $select_query .= " AND tp.prod_cat_id = " . $category . " ";
            }
            if (!empty($year)) {
                $select_query .=
                    " AND EXTRACT(YEAR FROM tpib.bid_date) = " . $year . " ";
            }
            if (!empty($month)) {
                $select_query .=
                    " AND EXTRACT(YEAR FROM tpib.bid_date) = " .
                    date("Y") .
                    " ";
                $select_query .=
                    " AND EXTRACT(MONTH FROM tpib.bid_date) = " . $month . " ";
            }
            if (!empty($day)) {
                $select_query .=
                    " AND EXTRACT(YEAR FROM tpib.bid_date) = " .
                    date("Y") .
                    " ";
                $select_query .=
                    " AND EXTRACT(MONTH FROM tpib.bid_date) = " .
                    date("m") .
                    " ";
                $select_query .=
                    " AND EXTRACT(DAY FROM tpib.bid_date) = " . $day . " ";
            }
            $group_by_query .= " GROUP BY tpib.bid_status, tp.prod_cat_id ";
            $order_by_query .= " ORDER BY tpib.bid_status ";
            $row = $this->db->query(
                $select_query . $group_by_query . $order_by_query
            );
            // echo $this->db->last_query();exit;
            // echo $select_query.$group_by_query.$order_by_query;
            $result = $row->result_array();
            $buyer_status_filter = array_column(
                BUYER_TRADE_STATUS_FILTER,
                "id"
            );
            $result_status = array_column($result, "status");
            // echo'<pre>';print_r($result);exit;
            if (!empty($result) && count($result) > 0) {
                foreach ($result as $key => $value) {
                    $statusId = $value["status"];
                    if (!empty($statusId)) {
                        $statusList = array_filter(
                            BUYER_TRADE_STATUS_FILTER,
                            function ($statusList_data) use ($statusId) {
                                return $statusList_data["id"] == $statusId;
                            }
                        );
                        $statusList = array_values($statusList);
                        $value["status_title"] = $statusList[0]["title"];
                        $value["status_class"] = $statusList[0]["statusClass"];
                        $data[] = $value;
                    }
                }
                $difference_data = array_diff(
                    $buyer_status_filter,
                    $result_status
                );
                if (!empty($difference_data) && count($difference_data) > 0) {
                    foreach ($difference_data as $k => $v) {
                        $value["status"] = $v;
                        $value["row_count"] = 0;
                        $value["prod_cat_id"] = $category;
                        $statusId = $v;
                        if (!empty($statusId)) {
                            $statusList = array_filter(
                                BUYER_TRADE_STATUS_FILTER,
                                function ($statusList_data) use ($statusId) {
                                    return $statusList_data["id"] == $statusId;
                                }
                            );
                            $statusList = array_values($statusList);
                            $value["status_title"] = $statusList[0]["title"];
                            $value["status_class"] =
                                $statusList[0]["statusClass"];
                            $data[] = $value;
                        }
                    }
                    // Custom sorting function based on 'status' key
                    usort($data, function ($a, $b) {
                        return $a["status"] - $b["status"];
                    });
                }
            } else {
                foreach (BUYER_TRADE_STATUS_FILTER as $key => $value) {
                    $newvalue["status"] = $value["id"];
                    $newvalue["row_count"] = 0;
                    $newvalue["prod_cat_id"] = $category;
                    $newvalue["status_title"] = $value["title"];
                    $newvalue["status_class"] = $value["statusClass"];
                    $data[] = $newvalue;
                }
                // $data[] = BUYER_TRADE_STATUS_FILTER;
            }
        }
        // $data = $result;
        // echo $this->db->last_query();exit;
        if (!empty($data)) {
            $response = [
                "success" => 1,
                "data" => $data,
                "message" => lang("Listed_Successfully"),
            ];
        } else {
            $response = [
                "success" => 0,
                "data" => [],
                "message" => lang("Data_Not_Found"),
            ];
        }
        $this->api_response($response);
        exit();
    }
    public function get_home_filter_get()
    {
        $report_filter = [
            [
                "id" => 1,
                "title" => "Year",
                "value" => date("Y"),
            ],
            [
                "id" => 2,
                "title" => "Month",
                "value" => date("m"),
            ],
            [
                "id" => 3,
                "title" => "Day",
                "value" => date("d"),
            ],
        ];
        $response = [
            "success" => 1,
            "data" => $report_filter,
            "message" => lang("Listed_Successfully"),
        ];
        $this->api_response($response);
        exit();
    }
    // add Rating fpr product by buyer
    /*
	1:'happy'
	2:'average'	
	3:'poor'
	*/
    public function add_trade_product_rating_post()
    {
        $buyer_id = $this->input->post("buyer_id");
        $trade_product_id = $this->input->post("trade_product_id");
        $rating_id = $this->input->post("rating_id");
        $seller_id = $this->input->post("seller_id");
        if ($trade_product_id != "") {
            if (
                $buyer_id != "" &&
                $trade_product_id != "" &&
                $rating_id != ""
            ) {
                $row = $this->db->query(
                    "SELECT * FROM trade_product_rating WHERE trade_product_id = " .
                        $trade_product_id .
                        " AND buyer_id=" .
                        $buyer_id
                );
                $result = $row->row_array();
                if (!empty($result) && count($result) > 0) {
                    // Update
                    $update = [
                        "rating_id" => $rating_id,
                        "updated_on" => current_date(),
                    ];
                    $this->db->where("trade_product_id", $trade_product_id);
                    $this->db->where("buyer_id", $buyer_id);
                    $this->db->update("trade_product_rating", $update);
                    $response = [
                        "status" => 1,
                        "data" => $interestShown,
                        "message" => lang("Updated_Successfully"),
                    ];
                } else {
                    // Insert
                    // get product details from trade product
                    $trade_product_query = $this->db->query(
                        "SELECT user_id FROM trade_product WHERE is_deleted = 'false' AND id = " .
                            $trade_product_id
                    );
                    $trade_product_result = $trade_product_query->row_array();
                    $insert = [
                        "buyer_id" => $buyer_id,
                        "trade_product_id" => $trade_product_id,
                        "seller_id" => $trade_product_result["user_id"],
                        "created_by_id" => $buyer_id,
                        "rating_id" => $rating_id,
                        "created_on" => current_date(),
                    ];
                    $this->db->insert("trade_product_rating", $insert);
                    $response = [
                        "status" => 1,
                        "data" => true,
                        "message" => lang("Added_Successfully"),
                    ];
                }
            } else {
                $response = [
                    "status" => 0,
                    "data" => [],
                    "message" => lang("Missing_Parameter"),
                ];
            }
        } elseif ($buyer_id != "" && $rating_id != "" && $seller_id != "") {
            $row = $this->db->query(
                "SELECT * FROM trade_product_rating WHERE buyer_id = " .
                    $buyer_id .
                    " AND seller_id=" .
                    $seller_id .
                    " AND trade_product_id = 0 "
            );
            $result = $row->row_array();
            if (!empty($result) && count($result) > 0) {
                $update = [
                    "rating_id" => $rating_id,
                    "updated_on" => current_date(),
                ];
                $this->db->where("trade_product_id", 0);
                $this->db->where("buyer_id", $buyer_id);
                $this->db->where("seller_id", $seller_id);
                $this->db->update("trade_product_rating", $update);
                $response = [
                    "status" => 1,
                    "data" => true,
                    "message" => lang("Updated_Successfully"),
                ];
            } else {
                $insert = [
                    "buyer_id" => $buyer_id,
                    "trade_product_id" => 0,
                    "seller_id" => $seller_id,
                    "created_by_id" => $buyer_id,
                    "rating_id" => $rating_id,
                    "created_on" => current_date(),
                ];
                $this->db->insert("trade_product_rating", $insert);
                $response = [
                    "status" => 1,
                    "data" => true,
                    "message" => lang("Added_Successfully"),
                ];
            }
        } else {
            $response = [
                "success" => 0,
                "data" => [],
                "message" => lang("Missing_Parameter"),
            ];
        }
        $this->api_response($response);
        exit();
    }
    // Show Rating for buyer
    public function show_buyer_rating_get($buyer_id = "")
    {
        //$buyer_id	= $this->input->post('buyer_id');
        if ($buyer_id != "") {
            /*$row	= $this->db->query("SELECT
				buyer_id,
				COUNT(CASE WHEN rating = 'happy' THEN 1 END) as happy_count,
				COUNT(CASE WHEN rating = 'average' THEN 1 END) as average_count,
				COUNT(CASE WHEN rating = 'poor' THEN 1 END) as poor_count
			FROM
				ratings
		    WHERE buyer_id = ". $buyer_id."
			GROUP BY
				user_id"
				1:'happy'
				2:'average'
				3:'poor'
				*/
            $row = $this->db->query(
                "SELECT
				buyer_id,
				COUNT(CASE WHEN rating_id = '1' THEN 1 END) as happy_count,
				COUNT(CASE WHEN rating_id = '2' THEN 1 END) as average_count,
				COUNT(CASE WHEN rating_id = '3' THEN 1 END) as poor_count
			FROM
			trade_product_rating
		    WHERE buyer_id = " .
                    $buyer_id .
                    " AND is_deleted=false AND trade_product_id = 0 GROUP BY buyer_id"
            );
            // $row	= $this->db->query("SELECT * FROM trade_product_rating WHERE buyer_id = " . $buyer_id.' AND is_deleted=false');
            $result = $row->row_array();
            if (!empty($result) && count($result) > 0) {
                $response = [
                    "status" => 1,
                    "data" => $result,
                    "message" => lang("Listed_Successfully"),
                ];
            } else {
                $result = [
                    "buyer_id" => $buyer_id,
                    "happy_count" => 0,
                    "average_count" => 0,
                    "poor_count" => 0,
                ];
                $response = [
                    "status" => 1,
                    "data" => $result,
                    "message" => lang("Listed_Successfully"),
                ];
            }
        } else {
            $response = [
                "success" => 0,
                "data" => [],
                "message" => lang("Missing_Parameter"),
            ];
        }
        $this->api_response($response);
        exit();
    }
    public function delete_buyer_get($buyer_id = "")
    {
        $update_arr = [];
        $update_arr["is_active"] = false;
        $update_arr["is_deleted"] = true;
        $update_arr["deleted_on"] = current_date();
        $update_arr["deleted_by_id"] = $buyer_id;
        $update_arr["is_login"] = false;
        $update_arr["device_id"] = null;
        $this->db->where("client.id", $buyer_id);
        $result = $this->db->update("client", $update_arr);
        $response = [
            "success" => 1,
            "data" => [],
            "message" =>
                "Your account deletion request has been submitted. Once the admin verifies it, your account will be deleted from our platform.",
        ];
        $this->api_response($response);
        exit();
    }
    public function logout_buyer_get($buyer_id = "")
    {
        $where = ["is_deleted" => "true", "id" => $buyer_id];
        $client_id = $this->Masters_model->get_data(
            "id,phone",
            "client",
            $where
        );
        if ($client_id[0]["id"]) {
            $response = [
                "success" => 1,
                "data" => [],
                "message" =>
                    "Your account has already been deleted from our platform.",
            ];
            $this->api_response($response);
            exit();
        }
        $this->logout_check_get($client_id[0]["phone"]);
        $response = ["success" => 0, "data" => [], "message" => "Logout"];
        $this->api_response($response);
        exit();
    }
    public function logout_check_get($phone_number)
    {
        if ($phone_number != "") {
            $phone = substr(preg_replace("/\s+/", "", $phone_number), -10, 10);
            $sql = "SELECT phone,id FROM client where phone :: varchar = $phone_number::varchar AND is_active= true AND is_deleted = false ";
            $res_chk = $this->db->query($sql);
            $res = $res_chk->result_array();
            if (count($res) > 0) {
                $id = $res[0]["id"];
                ///// code to disconnnect call of vendor if any active call
                $where_array = [
                    "farmer_id" => $id,
                    "meeting_status_id !=" => 4,
                ];
                $update_array = [
                    "meeting_status_id" => 4,
                    "meeting_end_from" => 1,
                    "updated_on" => current_date(),
                ];
                $sql_update = $this->db->update(
                    "emeeting",
                    $update_array,
                    $where_array
                );
                //// disconnect call code end ///////////////////////////
                $update_arr = ["is_login" => false, "device_id" => null];
                $this->db->where("client.phone", $phone);
                $result = $this->db->update("client", $update_arr);
                $sql_data = $this->db->last_query();
                $response = [
                    "success" => 0,
                    "error" => 0,
                    "status" => 1,
                    "data" => $result,
                    "message" => lang("Logout_Successfully"),
                ];
            } else {
                $response = [
                    "success" => 0,
                    "error" => 1,
                    "status" => 0,
                    "data" => "",
                    "message" => lang("Data_Not_Found"),
                ];
            }
        } else {
            $response = [
                "success" => 0,
                "error" => 1,
                "status" => 0,
                "data" => "",
                "message" => lang("Missing_Parameter"),
            ];
        }
        $this->api_response($response);
        exit();
    }
    //***********************************************************************
    // Buyer module API: END //////////////////////////
    //***********************************************************************
    /***********************Working APIs:End***********************/
    /***********************Save Logs:Start***********************/
    public function save_logs($response = [])
    {
        $log = [
            "USER" => $_SERVER["REMOTE_ADDR"],
            "DATE" => date("Y-m-d, H:i:s"),
            "URL" => $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"],
            "METHOD" => $_SERVER["REQUEST_METHOD"],
            "REQUEST" => $_REQUEST,
            "RESPONSE" => $response,
        ];
        //Save string to log, use FILE_APPEND to append.
        $log_filename = APPPATH . "logs";
        if (!file_exists($log_filename)) {
            // create directory/folder uploads.
            mkdir($log_filename, 0777, true);
        }
        $log_file_data = $log_filename . "/log_" . date("d-M-Y") . ".log";
        // if you don't add `FILE_APPEND`, the file will be erased each time you add a log
        file_put_contents(
            $log_file_data,
            json_encode($log) . "\n",
            FILE_APPEND
        );
    }
    /***********************Save Logs:End***********************/
}
