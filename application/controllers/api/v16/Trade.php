<?php
defined("BASEPATH") or exit("No direct script access allowed");
error_reporting(E_ERROR | E_PARSE);
//error_reporting(E_ERROR | E_PARSE);
//error_reporting(E_ALL);
require APPPATH . "libraries/RestController.php";
use chriskacerguis\RestServer\RestController;
class Trade extends RestController
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
        // $headers_data['domain'] = $headers_data['Domain'];
        // $headers_data['client-type'] = $headers_data['Client_type'];
        // $headers_data['client-type'] = $headers_data['Client_type'];
        $this->load->model("Ekyc_model");
        $this->load->model("Notification_model");
        // Start: Required headers and there value check
        // if ((!strpos($_SERVER['REQUEST_URI'], 'partner_login')) || (!strpos($_SERVER['REQUEST_URI'], 'dynamic_domain_db_connection'))) {
        // if (!strpos($_SERVER['REQUEST_URI'], 'dynamic_domain_db_connection')) {
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
        $this->root_folder = $root_folder =
            $_SERVER["HOME"] . "/" . UPLOAD_ROOT_FOLDER . "/";
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
        // $headers_data = array_change_key_case($this->input->request_headers(), CASE_LOWER);
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
        // echo lang('Added_Successfully');exit;
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
    // Saller module API: Start //////////////////////////
    //***********************************************************************
    public function get_listing_get($listing_name = null)
    {
        $selected_lang = $this->selected_lang;
        if (!empty($listing_name)) {
            switch ($listing_name) {
                case "product_category":
                    $result = PROD_CAT;
                    break;
                case "season":
                    $result = SEASON_LIST;
                    break;
                case "product_unit":
                    $result = PROD_UNIT;
                    break;
                case "product_payment":
                    $result = PROD_PAYMENT;
                    break;
                case "trade_status_list":
                    $result = TRADE_STATUS_LIST;
                    break;
                case "buyer_status":
                    $result = BUYER_TRADE_STATUS;
                    break;
                case "seller_status":
                    $result = SELLER_TRADE_STATUS;
                    break;
                case "user_type":
                    $result = USER_TYPE;
                    break;
                case "business_type":
                    $result = BUSINESS_TYPE;
                    break;
                case "prod_details":
                    $result = PROD_DETAILS;
                    break;
                case "business_scheme":
                    $result = BUSINESS_SCHEME;
                    break;
                case "demand_type":
                    $result = DEMAND_TYPE;
                    break;
                case "buyer_trade_status_filter":
                    $result = BUYER_TRADE_STATUS_FILTER;
                    break;
                case "upcominig_trade_status":
                    $result = UPCOMING_TRADE_STATUS_LIST;
                    break;
                case "fpc_business_scheme":
                    $result = FPC_BUSINESS_SCHEME;
                    break;
                default:
                    $result = [];
                    break;
            }
        } else {
            $result["product_category"] = PROD_CAT;
            $result["season"] = SEASON_LIST;
            $result["product_unit"] = PROD_UNIT;
            $result["product_payment"] = PROD_PAYMENT;
            $result["trade_status_list"] = TRADE_STATUS_LIST;
            $result["buyer_status"] = BUYER_TRADE_STATUS;
            $result["seller_status"] = SELLER_TRADE_STATUS;
            $result["user_type"] = USER_TYPE;
            $result["business_type"] = BUSINESS_TYPE;
            $result["prod_details"] = PROD_DETAILS;
            $result["business_scheme"] = BUSINESS_SCHEME;
            $result["demand_type"] = DEMAND_TYPE;
            $result["buyer_trade_status_filter"] = BUYER_TRADE_STATUS_FILTER;
            $result["upcominig_trade_status"] = UPCOMING_TRADE_STATUS_LIST;
            $result["fpc_business_scheme"] = FPC_BUSINESS_SCHEME;
        }
        // echo $this->db->last_query();exit;
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
    public function product_type_get()
    {
        $selected_lang = $this->selected_lang;
        $table = "prod_type";
        $query =
            "SELECT id, title, lang_json->>'" .
            $selected_lang .
            "' as title_" .
            $selected_lang .
            ", lang_json FROM $table WHERE is_deleted = 'false' AND is_active = 'true' ORDER BY id DESC";
        $row = $this->db->query($query);
        $result = $row->result_array();
        // echo $this->db->last_query();exit;
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
    public function product_data_post()
    {
        $selected_lang = $this->selected_lang;
        $product_category = $this->input->post("product_category");
        $product_type = $this->input->post("product_type");
        // print_r($_POST);exit;
        $result = [];
        // if (!empty($product_type) && !empty($product_category)) { // Remove temporary
        if (!empty($product_type)) {
            $table = "prod_master";
            $query =
                "SELECT id, title, commodity_title, lang_json->>'" .
                $selected_lang .
                "' as title_" .
                $selected_lang .
                ", lang_json, prod_cat, prod_type_id FROM " .
                $table .
                " WHERE is_deleted = 'false' AND is_active = 'true' AND prod_type_id = " .
                $product_type .
                " ORDER BY title ASC";
            $row = $this->db->query($query);
            $result = $row->result_array();
            $data = [];
            if (!empty($product_category) && !empty($result)) {
                foreach ($result as $key => $value) {
                    $prod_cat = !empty($value["prod_cat"])
                        ? json_decode($value["prod_cat"], true)
                        : [];
                    if (
                        !empty($prod_cat) &&
                        in_array($product_category, $prod_cat)
                    ) {
                        $data[] = $value;
                    }
                }
            } else {
                $data = $result;
            }
            // echo'<pre>';print_r($data);exit;
            if (count($data) > 0) {
                $response = [
                    "success" => 1,
                    "data" => $data,
                    "message" => lang("Listed_Successfully"),
                ];
            } else {
                $response = [
                    "success" => 1,
                    "data" => [],
                    "message" => lang("Data_Not_Found"),
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
    public function product_variety_get($product_id = null)
    {
        if (!empty($product_id)) {
            $selected_lang = $this->selected_lang;
            $table = "prod_variety";
            $query =
                "SELECT id, title, lang_json->>'" .
                $selected_lang .
                "' as title_" .
                $selected_lang .
                ", lang_json, prod_master_id FROM $table WHERE is_deleted = 'false' AND is_active = 'true' AND prod_master_id = " .
                $product_id .
                " ORDER BY id DESC";
            $row = $this->db->query($query);
            $result = $row->result_array();
            // echo $this->db->last_query();exit;
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
    public function packaging_list_get()
    {
        $selected_lang = $this->selected_lang;
        $table = "packaging_master";
        $query =
            "SELECT id, title, lang_json->>'" .
            $selected_lang .
            "' as title_" .
            $selected_lang .
            ", lang_json FROM $table WHERE is_deleted = 'false' AND is_active = 'true' ORDER BY id DESC";
        $row = $this->db->query($query);
        $result = $row->result_array();
        // echo $this->db->last_query();exit;
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
    public function storage_type_get()
    {
        $selected_lang = $this->selected_lang;
        $table = "storage_type";
        $query =
            "SELECT id, title, lang_json->>'" .
            $selected_lang .
            "' as title_" .
            $selected_lang .
            ", lang_json FROM $table WHERE is_deleted = 'false' AND is_active = 'true' ORDER BY id DESC";
        $row = $this->db->query($query);
        $result = $row->result_array();
        // echo $this->db->last_query();exit;
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
    // Add and update trade product
    public function add_trade_product_post()
    {
        $id = $this->input->post("id");
        $step = $this->input->post("step");
        $postdata = $this->input->post();
        $postval = [
            "user_id",
            "prod_cat_id",
            "prod_type_id",
            "prod_id",
            "prod_variety_id",
            "surplus",
            "surplus_unit",
            "sell_qty",
            "sell_qty_unit",
            "price",
            "price_unit",
            "with_logistic_partner",
            "storage_type_id",
            "state",
            "city",
            "pickup_location",
            "produce_to_highway_distance",
            "advance_payment",
            "negotiations",
            "certifcations",
        ];
        if (!empty($_POST["prod_cat_id"]) && $_POST["prod_cat_id"] == 2) {
            $postval[] = "prod_details";
        }
        // $convertojson = array('other_details', 'other_distance');
        foreach ($postdata as $key => $value) {
            if (!empty($value) && in_array($key, $postval)) {
                $insertdata[$key] = $value;
            }
        }
        if ($step == 1) {
            // 'with_packging', 'packaging_master_id',
            if (
                isset($postdata["with_packging"]) &&
                $postdata["with_packging"] == "true"
            ) {
                $insertdata["with_packging"] = true;
                $insertdata["packaging_master_id"] =
                    $postdata["packaging_master_id"];
            } else {
                $insertdata["with_packging"] = false;
                $insertdata["packaging_master_id"] = null;
            }
            if (!empty($_POST["prod_cat_id"]) && $_POST["prod_cat_id"] == 2) {
                $other_details["season_from"] = "";
                $other_details["season_to"] = "";
                $other_details["availability_from"] = $this->input->post(
                    "availability_from"
                )
                    ? $this->input->post("availability_from")
                    : "";
                $other_details["availability_to"] = $this->input->post(
                    "availability_to"
                )
                    ? $this->input->post("availability_to")
                    : "";
                $other_details["yield_from"] = $this->input->post("yield_from")
                    ? $this->input->post("yield_from")
                    : "";
                $other_details["yield_from_unit"] = $this->input->post(
                    "yield_from_unit"
                )
                    ? $this->input->post("yield_from_unit")
                    : "";
                $other_details["yield_to"] = $this->input->post("yield_to")
                    ? $this->input->post("yield_to")
                    : "";
                $other_details["yield_to_unit"] = $this->input->post(
                    "yield_to_unit"
                )
                    ? $this->input->post("yield_to_unit")
                    : "";
            } else {
                $other_details["season_from"] = $this->input->post(
                    "season_from"
                )
                    ? $this->input->post("season_from")
                    : "";
                $other_details["season_to"] = $this->input->post("season_to")
                    ? $this->input->post("season_to")
                    : "";
                $other_details["availability_from"] = "";
                $other_details["availability_to"] = "";
                $other_details["yield_from"] = "";
                $other_details["yield_from_unit"] = "";
                $other_details["yield_to"] = "";
                $other_details["yield_to_unit"] = "";
            }
            $insertdata["other_details"] = json_encode($other_details);
            if (isset($postdata["active_till_date"])) {
                $active_till_date = date(
                    "Y-m-d H:i:s",
                    strtotime(
                        $postdata["active_till_date"] . " " . date("H:i:s")
                    )
                );
                // echo $active_till_date;exit;
                $insertdata["active_till_date"] = $active_till_date;
                $insertdata["expiry_date"] = $active_till_date;
            } else {
                $insertdata["active_till_date"] = null;
                $insertdata["expiry_date"] = null;
            }
            // print_r($insertdata);exit;
            // echo 'Post Date: '.$postdata['active_till_date'].', Converted Date: '.$active_till_date;exit;
            // print_r($postdata['active_till_date']);
            // print_r($insertdata);exit;
        }
        if ($step == 2) {
            $other_distance["railway"] = $this->input->post("railway")
                ? $this->input->post("railway")
                : "";
            $other_distance["airport"] = $this->input->post("airport")
                ? $this->input->post("airport")
                : "";
            $other_distance["post_office"] = $this->input->post("post_office")
                ? $this->input->post("post_office")
                : "";
            $other_distance["godown"] = $this->input->post("godown")
                ? $this->input->post("godown")
                : "";
            $other_distance["national_highway"] = $this->input->post(
                "national_highway"
            )
                ? $this->input->post("national_highway")
                : "";
            $other_distance["state_highway"] = $this->input->post(
                "state_highway"
            )
                ? $this->input->post("state_highway")
                : "";
            $insertdata["other_distance"] = json_encode($other_distance);
        }
        if (empty($id)) {
            // 1: 'Pending', 2: 'Rejected', 3: 'Live', 4: 'Sold', 5: 'Completed', 6: 'Expired', 7: 'Approved'
            // insert
            $insertdata["trade_status"] = 8; // save in draft
            $insertdata["status"] = 8;
            $insertdata["added_date"] = date("Y-m-d H:i:s");
            $insertdata["created_on"] = date("Y-m-d H:i:s");
            $insertdata["created_by_id"] = $postdata["user_id"];
            // print_r($insertdata);exit;
            $result = $this->db->insert("trade_product", $insertdata);
            $insert_id = $this->db->insert_id();
            $msg = lang("Added_Successfully");
        } else {
            $insertdata["updated_by_id"] = $id;
            $insertdata["updated_on"] = date("Y-m-d H:i:s");
            // print_r($insertdata);
            /***Start: Check any change made by seller****/
            $query = "SELECT * FROM trade_product WHERE id = $id";
            $row = $this->db->query($query);
            $row_data = $row->row_array();
            // echo'<pre>';print_r($row_data);echo'</pre>';
            // echo'<pre>';print_r($insertdata);echo'</pre>';
            /**
             * other_details
             * other_distance
             * active_till_date
             * with_logistic_partner
             * expiry_date
             * updated_on
             * certifcations
             * negotiations
             *
             */
            $json_encoded_data = ["other_details", "other_distance"];
            $compare_date_data = ["active_till_date", "expiry_date"];
            $checkbox_data = [
                "with_logistic_partner",
                "certifcations",
                "negotiations",
            ];
            $skip_data = ["updated_on", "updated_by_id"];
            $update_product[] = "false";
            foreach ($insertdata as $key => $val) {
                if (!in_array($key, $skip_data)) {
                    if (in_array($key, $json_encoded_data)) {
                        $compare_json = $this->are_json_objects_equal(
                            $row_data[$key],
                            $val
                        );
                        if ($compare_json) {
                            $update_product[] = "false";
                        } else {
                            $update_product[] = "true";
                            // echo $key.':'.$row_data[$key].'==='.$val;exit;
                        }
                    } elseif (in_array($key, $compare_date_data)) {
                        $date1 = date("Y-m-d", strtotime($row_data[$key]));
                        $date2 = date("Y-m-d", strtotime($val));
                        if ($date1 == $date2) {
                            $update_product[] = "false";
                        } else {
                            $update_product[] = "true";
                            // echo $key.':'.$date1.'---'.$date2;exit;
                        }
                    } elseif (in_array($key, $checkbox_data)) {
                        $posted_value =
                            $row_data[$key] == "t" ? "true" : "false";
                        if ($posted_value == $val) {
                            $update_product[] = "false";
                        } else {
                            $update_product[] = "true";
                            // echo $key.':'.$posted_value.'==='.$val;exit;
                        }
                    } else {
                        if ($row_data[$key] == $val) {
                            $update_product[] = "false";
                        } else {
                            $update_product[] = "true";
                            // echo $key.':'.$row_data[$key].'==='.$val;exit;
                        }
                    }
                }
            }
            // print_r($update_product);exit;
            /***End: Check any change made by seller****/
            // exit;
            if (in_array("true", $update_product)) {
                // update
                if (
                    $row_data["status"] != 8 &&
                    (isset($_POST["status"]) && !empty($_POST["status"]))
                ) {
                    $insertdata["status"] = $_POST["status"];
                }
                $this->db->where("id", $id);
                $result = $this->db->update("trade_product", $insertdata);
                $insert_id = $id;
                $msg = lang("Updated_Successfully");
            } else {
                $msg = lang("No_Changes_Made");
                $response = [
                    "success" => 1,
                    "status" => 1,
                    "data" => $insert_id,
                    "message" => $msg,
                ];
                $this->api_response($response);
                exit();
            }
        }
        if (!empty($result)) {
            $response = [
                "success" => 1,
                "status" => 1,
                "data" => $insert_id,
                "message" => $msg,
            ];
        } else {
            $response = [
                "success" => 0,
                "status" => 0,
                "data" => [],
                "message" => lang("Data_Not_Found"),
            ];
        }
        $this->api_response($response);
        exit();
    }
    function are_json_objects_equal($json_str1, $json_str2)
    {
        // Decode JSON strings into PHP arrays
        $obj1 = json_decode($json_str1, true); // Set second parameter to true for associative arrays
        $obj2 = json_decode($json_str2, true);
        // Deep comparison
        return $this->deep_array_compare($obj1, $obj2);
    }
    function deep_array_compare($array1, $array2)
    {
        // Compare array lengths
        if (count($array1) !== count($array2)) {
            return false;
        }
        foreach ($array1 as $key => $value) {
            if (!array_key_exists($key, $array2)) {
                return false;
            }
            if (is_array($value)) {
                // Recursive comparison for nested arrays
                if (!deep_array_compare($value, $array2[$key])) {
                    return false;
                }
            } else {
                // Compare values
                if ($value !== $array2[$key]) {
                    return false;
                }
            }
        }
        return true;
    }
    // Upload images of trade products
    public function upload_trade_images_post()
    {
        // print_r($_FILES);exit;
        $id = $this->input->post("id");
        $img_files = $img_errors = [];
        // Allowed file extensions
        $allowed_extensions = ["jpg", "jpeg", "png", "webp"];
        $quality = 80; // WebP quality
        $no = 1;
        // Logo upload
        $response = [
            "success" => 0,
            "data" => ["uploaded_image" => []],
            "message" => lang("Not_Able_Update"),
            "error" => 1,
        ];
        if (isset($_FILES)) {
            // Specify the upload directory
            $current_month_yr = date("m_Y");
            $trade_products_folder =
                $this->root_folder . "uploads/config_master/trade_products";
            if (!file_exists($trade_products_folder)) {
                mkdir($trade_products_folder, 0777, true);
                chmod($trade_products_folder, 0777);
            }
            $trade_products_og_folder =
                $this->root_folder . "uploads/config_master/trade_products/og";
            if (!file_exists($trade_products_og_folder)) {
                mkdir($trade_products_og_folder, 0777, true);
                chmod($trade_products_og_folder, 0777);
            }
            $upload_folder = $trade_products_folder . "/" . $current_month_yr;
            if (!file_exists($upload_folder)) {
                mkdir($upload_folder, 0777, true);
                chmod($upload_folder, 0777);
            }
            // echo $upload_folder;exit;
            foreach ($_FILES as $key => $value) {
                for ($i = 0; $i < count($value["name"]); $i++) {
                    if ($i < 5) {
                        // File properties
                        $img_name = $value["name"][$i];
                        $img_tmp = $value["tmp_name"][$i];
                        $img_size = $value["size"][$i];
                        $img_error = $value["error"][$i];
                        // Get the file extension
                        $img_ext = strtolower(
                            pathinfo($img_name, PATHINFO_EXTENSION)
                        );
                        // Check if the file has a valid extension
                        if (in_array($img_ext, $allowed_extensions)) {
                            // Check for file upload errors
                            if ($img_error === 0) {
                                // Generate a unique file name
                                $new_file_name =
                                    "trade_" .
                                    $key .
                                    "_" .
                                    date("YmdHis") .
                                    "_" .
                                    $no++ .
                                    "." .
                                    $img_ext;
                                // Move the uploaded file to the destination directory
                                $destination =
                                    $trade_products_og_folder .
                                    "/" .
                                    $new_file_name;
                                $originalImage = imagecreatefromstring(
                                    file_get_contents($img_tmp)
                                );
                                // Convert to WebP and optimize
                                ob_start();
                                if (function_exists("imagewebp")) {
                                    imagewebp($originalImage, null, $quality);
                                } else {
                                    imagejpeg($originalImage, null, $quality);
                                }
                                $webpContents = ob_get_contents();
                                ob_end_clean();
                                $outputFileName =
                                    pathinfo(
                                        $new_file_name,
                                        PATHINFO_FILENAME
                                    ) . ".webp";
                                $outputPathAndFileName =
                                    rtrim($trade_products_og_folder, "/") .
                                    "/" .
                                    $outputFileName;
                                file_put_contents(
                                    $outputPathAndFileName,
                                    $webpContents
                                );
                                // Free up memory
                                imagedestroy($originalImage);
                                // if (move_uploaded_file($img_tmp, $destination)) {
                                //     $img_files[$key][]    = $current_month_yr.'/'.$new_file_name;
                                // } else {
                                //     $img_errors[] = "Error uploading the file.";
                                // }
                                $inputImagePath = $outputPathAndFileName;
                                $outputImagePath =
                                    rtrim($upload_folder, "/") .
                                    "/" .
                                    $outputFileName;
                                $maxWidth = 300;
                                $maxHeight = 150;
                                $compressionQuality = 80; // 0 (worst quality, small file) to 100 (best quality, large file)
                                $this->reduceImageSize(
                                    $inputImagePath,
                                    $outputImagePath,
                                    $maxWidth,
                                    $maxHeight,
                                    $compressionQuality
                                );
                                $img_files[$key][] =
                                    $current_month_yr . "/" . $outputFileName;
                                $success = 1;
                                $message = lang("Image_Uploaded_Successfully");
                            } else {
                                $success = 0;
                                $message = "Error: " . $img_error;
                                $img_errors[] = "Error: " . $img_error;
                            }
                        } else {
                            $success = 0;
                            $message = lang("Invalid_File_Extention");
                            $img_errors[] = lang("Invalid_File_Extention");
                        }
                    }
                }
            }
            // print_r($_FILES);exit;
            if (!empty($id)) {
                $query = "SELECT id, prod_images, trade_status, status, certifcations  FROM trade_product WHERE id = $id";
                $row = $this->db->query($query);
                $result = $row->row_array();
                $upload_images = $new_images = [];
                if (!empty($result)) {
                    $prod_images = $result["prod_images"];
                    if (!empty($prod_images)) {
                        $json_prod_images = json_decode($prod_images, true);
                        foreach ($json_prod_images as $img_key => $img_val) {
                            if (!empty($img_val)) {
                                if (
                                    in_array($img_key, array_keys($img_files))
                                ) {
                                    $upload_images[$img_key] = array_merge(
                                        $img_files[$img_key],
                                        $img_val
                                    );
                                } else {
                                    $upload_images[$img_key] = $img_val;
                                }
                            }
                        }
                        foreach ($img_files as $file_key => $file_value) {
                            if (
                                !in_array($file_key, array_keys($upload_images))
                            ) {
                                $new_images[$file_key] = $img_files[$file_key];
                            }
                        }
                        if (!empty($new_images)) {
                            $img_array_merged = array_merge(
                                $upload_images,
                                $new_images
                            );
                        } else {
                            $img_array_merged = $upload_images;
                        }
                        // print_r($img_array_merged);exit;
                    } else {
                        $img_array_merged = $img_files;
                    }
                    if ($result["trade_status"] == 8) {
                        if ($result["certifcations"] == "t") {
                            if (
                                isset($img_array_merged["certificate"]) &&
                                $img_array_merged["certificate"] != ""
                            ) {
                                $update_data["trade_status"] = 1;
                            }
                        } else {
                            $update_data["trade_status"] = 1;
                        }
                    }
                    if (
                        $result["status"] == 8 ||
                        $result["status"] == 2 ||
                        $result["status"] == 6
                    ) {
                        if ($result["certifcations"] == "t") {
                            if (
                                isset($img_array_merged["certificate"]) &&
                                $img_array_merged["certificate"] != ""
                            ) {
                                $update_data["status"] = 1;
                            }
                        } else {
                            $update_data["status"] = 1;
                        }
                    }
                }
                $update_data["prod_images"] = json_encode($img_array_merged);
                // print_r($update_data);exit;
                $this->db->where("id", $id);
                $this->db->update("trade_product", $update_data);
            }
            unlink($trade_products_og_folder);
            $prod_imagesData = [];
            if (!empty($id)) {
                $nquery = "SELECT id, prod_images, trade_status, status FROM trade_product WHERE id = $id";
                $nrow = $this->db->query($nquery);
                $nresult = $nrow->row_array();
                if (!empty($nresult)) {
                    $prod_imagesData = json_decode(
                        $nresult["prod_images"],
                        true
                    );
                }
            }
            $response = [
                "success" => $success,
                "data" => ["uploaded_image" => $prod_imagesData],
                "message" => $message, //lang('Image_Uploaded_Successfully')
                "error" => implode(", ", $img_errors),
            ];
        }
        $this->api_response($response);
        exit();
    }
    // Image reduce with its size while uploading images
    public function reduceImageSize(
        $inputImagePath,
        $outputImagePath,
        $maxWidth,
        $maxHeight,
        $compressionQuality
    ) {
        // Get the original image's dimensions
        list($originalWidth, $originalHeight) = getimagesize($inputImagePath);
        // Calculate new dimensions while maintaining aspect ratio
        $aspectRatio = $originalWidth / $originalHeight;
        $newWidth = min($maxWidth, $originalWidth);
        $newHeight = $newWidth / $aspectRatio;
        if ($newHeight > $maxHeight) {
            $newHeight = $maxHeight;
            $newWidth = $newHeight * $aspectRatio;
        }
        // Load the original image
        $originalImage = imagecreatefromstring(
            file_get_contents($inputImagePath)
        );
        // Create a new image with the calculated dimensions
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        // Resize the image
        imagecopyresampled(
            $resizedImage,
            $originalImage,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );
        // Save the resized image with compression quality
        imagejpeg($resizedImage, $outputImagePath, $compressionQuality);
        // Free up memory
        // unlink($originalImage);
        // unlink($resizedImage);
        // imagedestroy($originalImage);
        // imagedestroy($resizedImage);
    }
    // Remove trade trade product
    public function remove_trade_product_get($id = null)
    {
        if (!empty($id)) {
            $query = "SELECT id FROM trade_product WHERE id = $id AND is_deleted='false' AND is_active='true'";
            $row = $this->db->query($query);
            $result = $row->row_array();
            if (!empty($result)) {
                $update_data = ["is_deleted" => "true"];
                $this->db->where("id", $id);
                $result = $this->db->update("trade_product", $update_data);
                $response = [
                    "success" => 1,
                    "data" => $result,
                    "message" => lang("Deleted_Successfully"),
                ];
            } else {
                $response = [
                    "success" => 0,
                    "data" => [],
                    "message" => lang("Data_Not_Found"),
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
    // List all or single trade products
    public function trade_product_post()
    {
        $user_id = trim($this->input->post("user_id"));
        $id = trim($this->input->post("id"));
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
        $prod_cat_id = $this->input->post("prod_cat_id");
        $trade_status = $this->input->post("trade_status");
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
        if (!empty($user_id)) {
            $select_query .= " AND tp.user_id = '" . $user_id . "' ";
        }
        if (!empty($prod_cat_id)) {
            $select_query .= " AND tp.prod_cat_id = '" . $prod_cat_id . "' ";
        }
        if (!empty($trade_status)) {
            $select_query .= " AND tp.status = '" . $trade_status . "' ";
        }
        if (!empty($id)) {
            $select_query .= " AND tp.id = '" . $id . "' ";
        }
        // $order_query  .= " ORDER BY tp.id DESC ";
        $order_query .= " ORDER BY 
		CASE 
			WHEN tp.updated_on IS NOT NULL THEN tp.updated_on 
			ELSE '0001-01-01'
		END DESC, 
		tp.id DESC ";
        $limit_query .= " LIMIT " . $limit . " OFFSET " . $start_sql;
        // echo $select_query.$order_query.$limit_query;exit;
        // Get total number of rows
        $num_row_query = $this->db->query($select_query . $order_query);
        $num_rows = $num_row_query->num_rows();
        // Get list of all data
        $row = $this->db->query($select_query . $order_query . $limit_query);
        $res = $row->result_array();
        // print_r($res);exit;
        $result = [];
        if (!empty($res)) {
            foreach ($res as $key => $value) {
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
                /* Start Get prod_details details : Akash */
                $prod_details_id = $value["prod_details"];
                $prod_details = array_filter(PROD_DETAILS, function (
                    $prod_details_data
                ) use ($prod_details_id) {
                    return $prod_details_data["id"] == $prod_details_id;
                });
                $prod_details = array_values($prod_details);
                $value["prod_details_title"] = $prod_details[0]["title"];
                /* End Get prod_details details */
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
                $get_status_list_ids = ["status", "trade_status", "bid_status"];
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
                // green-status
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
                // if($value['with_packging'] == 't'){
                //     $value['packaging_text'] = $value['packging_title'];
                // } else {
                //     $value['packaging_text'] = '';
                // }
                // $value['active_till_date'] = date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($value['active_till_date'])));
                $value["active_till_date"] = date(
                    "d-m-Y",
                    strtotime($value["active_till_date"])
                );
                //$value['added_date'] = date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($value['added_date'])));
                $value["added_date"] = date(
                    "Y-m-d H:i:s",
                    strtotime($value["added_date"])
                );
                //$value['expiry_date'] = date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($value['expiry_date'])));
                $value["expiry_date"] = date(
                    "Y-m-d H:i:s",
                    strtotime($value["expiry_date"])
                );
                $value["rejected_date"] = $value["rejected_date"]
                    ? $value["rejected_date"]
                    : "";
                //$value['updated_on'] = $value['updated_on'] ? date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($value['updated_on']))) : '';
                $value["updated_on"] = $value["updated_on"]
                    ? date("Y-m-d H:i:s", strtotime($value["updated_on"]))
                    : "";
                // $prod_images	= json_decode($value['prod_images']);
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
                $value["revoke_expire"] = false;
                // Get Trade Product Bidding data
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
                    "incentive_id",
                    "incentive_status",
                    "incentive_redeemed_date",
                    "seller_invoice",
                    "updated_on",
                ];
                $where_condition = [
                    "seller_id" => $value["user_id"],
                    "trade_product_id" => $value["id"],
                    "buyer_action !=" => "3",
                    "is_deleted" => "false",
                    "is_active" => "true",
                ];
                // $order_by = ' CASE WHEN seller_action = 1 THEN seller_action desc ELSE id desc ';
                $order_by = " seller_action asc, buyer_action asc, id desc ";
                $trade_product_bidding = $this->Masters_model->get_data(
                    $select_columns,
                    "trade_product_bidding",
                    $where_condition,
                    null,
                    $order_by
                );
                if (!empty($trade_product_bidding)) {
                    $bidding = [];
                    foreach ($trade_product_bidding as $bid_key => $bid_val) {
                        //$bid_val['bid_date']	= date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($bid_val['bid_date'])));
                        $bid_val["bid_date"] = date(
                            "Y-m-d H:i:s",
                            strtotime($bid_val["bid_date"])
                        );
                        $bid_val["rating_details"] = show_rating(
                            $bid_val["buyer_id"],
                            "buyer"
                        );
                        $buyer_detail = get_client_detail($bid_val["buyer_id"]);
                        $buyer_name = ucwords(
                            $buyer_detail["first_name"] .
                                " " .
                                $buyer_detail["last_name"]
                        );
                        //$seller_action_date	= date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($bid_val['seller_action_date'])));
                        $seller_action_date = date(
                            "Y-m-d H:i:s",
                            strtotime($bid_val["seller_action_date"])
                        );
                        $buyer_action_date = date(
                            "Y-m-d H:i:s",
                            strtotime($bid_val["buyer_action_date"])
                        );
                        //$buyer_action_date	= date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($bid_val['buyer_action_date'])));
                        // $bid_val['revoke_time_left']	= null;
                        $value["sold_to_buyer_id"] = null;
                        if (
                            $bid_val["seller_action"] == 1 ||
                            $bid_val["seller_action"] == 5
                        ) {
                            $value["sold_to_buyer_id"] = $bid_val["buyer_id"];
                            $value["sold_to"] = $buyer_name;
                            $value["sold_on"] = $seller_action_date;
                            $value["bidding_id"] = $bid_val["id"];
                            $value["sold_bid_date"] = $bid_val["bid_date"];
                            $value["sold_price"] = $bid_val["bid_price"];
                            $bid_val[
                                "seller_action_date"
                            ] = $seller_action_date;
                            $bid_val["buyer_action_date"] = $buyer_action_date;
                            if (
                                $bid_val["seller_action"] == 1 &&
                                $bid_val["buyer_action"] == 1
                            ) {
                                $get_revoke_time_setting = get_config_settings(
                                    "revoke_time"
                                );
                                // $addingFiveMinutes= strtotime('2020-10-30 10:10:20 + 5 minute');echo date('Y-m-d H:i:s', $addingFiveMinutes);
                                if (
                                    !empty($get_revoke_time_setting) &&
                                    !empty(
                                        $get_revoke_time_setting["description"]
                                    )
                                ) {
                                    $unix_timestamp = strtotime(
                                        $seller_action_date
                                    );
                                    $next_24_hours =
                                        $unix_timestamp +
                                        $get_revoke_time_setting["description"];
                                    $next_24_hours_date = date(
                                        "Y-m-d H:i:s",
                                        $next_24_hours
                                    );
                                    $revoke_time = $next_24_hours_date;
                                    //$revoke_time	= date('Y-m-d H:i:s', strtotime($seller_action_date.' + '.$get_revoke_time_setting['description'].' minute'));
                                } else {
                                    $revoke_time = date(
                                        "Y-m-d H:i:s",
                                        strtotime(
                                            $seller_action_date . " + 20 minute"
                                        )
                                    );
                                }
                                $bid_val["revoke_time"] = $revoke_time;
                                //$current_time	= strtotime(date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime(date('Y-m-d H:i:s')))));
                                $current_time = strtotime(date("Y-m-d H:i:s"));
                                $expire_time = strtotime($revoke_time);
                                //$bid_val['current_time']	= date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime(date('Y-m-d H:i:s'))));
                                $bid_val["current_time"] = date("Y-m-d H:i:s");
                                if ($expire_time > $current_time) {
                                    $value["revoke_expire"] = $bid_val[
                                        "revoke_expire"
                                    ] = true;
                                    $revoke_time_left =
                                        round(
                                            abs($expire_time - $current_time) /
                                                60,
                                            2
                                        ) . " minute";
                                    // Calculate the time difference in seconds
                                    $timeDifferenceInSeconds =
                                        $expire_time - $current_time;
                                    // Convert the time difference to minutes
                                    $timeDifferenceInMinutes =
                                        $timeDifferenceInSeconds / 60;
                                    // echo 'Sec: '.$timeDifferenceInSeconds.' - Min:'.$timeDifferenceInMinutes;
                                    $bid_val[
                                        "revoke_time_left"
                                    ] = $timeDifferenceInMinutes;
                                }
                                // else {
                                // 	$value['revoke_expire'] = $bid_val['revoke_expire']		= false;
                                // 	$bid_val['revoke_time_left']	= 0;
                                // }
                            }
                        }
                        $bid_val["buyer_profile_image"] =
                            $buyer_detail["profile_image"];
                        $bid_val["buyer_name"] = $buyer_name;
                        /* Start Get Status details : Akash */
                        $bidStatusId = $bid_val["bid_status"];
                        $bidStatusList = array_filter(
                            TRADE_STATUS_LIST,
                            function ($bidStatusList_data) use ($bidStatusId) {
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
                        $qtyUnitList = array_filter(PROD_UNIT, function (
                            $qtyUnitList_data
                        ) use ($qtyUnitId) {
                            return $qtyUnitList_data["id"] == $qtyUnitId;
                        });
                        $qtyUnitList = array_values($qtyUnitList);
                        $bid_val["qty_unit_title"] = $qtyUnitList[0]["title"];
                        /* End Get Status details */
                        /* Start Get Incentive details : Akash */
                        $incentive_status_id = $bid_val["incentive_status"];
                        $incentive = array_filter(INCENTIVE_STATUS, function (
                            $incentive_data
                        ) use ($incentive_status_id) {
                            return $incentive_data["id"] ==
                                $incentive_status_id;
                        });
                        $incentive = array_values($incentive);
                        $bid_val["incentive_title"] = $incentive[0]["title"];
                        /* End Get Incentive category details */
                        // Start: Trade Product status
                        $sellerAction = (int) $bid_val["seller_action"];
                        $buyerAction = (int) $bid_val["buyer_action"];
                        $sellerStatus = [2, 3];
                        $buyerStatus = [2, 3];
                        if (in_array($buyerAction, $buyerStatus)) {
                            $tradeProductStatus = $buyerAction;
                            $tradeProductStatusListing = BUYER_TRADE_STATUS;
                        } elseif (in_array($sellerAction, $sellerStatus)) {
                            $tradeProductStatus = $sellerAction;
                            $tradeProductStatusListing = SELLER_TRADE_STATUS;
                        } else {
                            $tradeProductStatus = null;
                            $tradeProductStatusListing = null;
                        }
                        /* Start Get buyer_action_title details : Akash */
                        if (
                            !empty($tradeProductStatus) &&
                            !empty($tradeProductStatusListing)
                        ) {
                            $bid_val[
                                "trade_product_status_id"
                            ] = $tradeProductStatus;
                            $manageProdAction = array_filter(
                                $tradeProductStatusListing,
                                function ($manageProdAction_data) use (
                                    $tradeProductStatus
                                ) {
                                    return $manageProdAction_data["id"] ==
                                        $tradeProductStatus;
                                }
                            );
                            $manageProdAction = array_values($manageProdAction);
                            $bid_val["trade_product_status"] =
                                $manageProdAction[0]["title"];
                            $bid_val["trade_product_status_class"] =
                                $manageProdAction[0]["statusClass"];
                        } else {
                            $bid_val["trade_product_status_id"] = null;
                            $bid_val["trade_product_status"] = null;
                            $bid_val["trade_product_status_class"] = null;
                        }
                        /* End Get buyer_action_title details */
                        // End: Trade Product status
                        $bidding[] = $bid_val;
                    }
                    $value["trade_product_bidding_count"] = count($bidding);
                    $value["trade_product_bidding"] = $bidding;
                } else {
                    $value["trade_product_bidding_count"] = 0;
                    $value["trade_product_bidding"] = [];
                }
                // if (!empty($id)) {
                //     $interest_data = $this->buyers_interest_list($user_id,$id);
                // }else{
                //     $interest_data = $this->buyers_interest_list($user_id,$value['trade_product_id'],$prod_cat_id);
                // }
                if ($prod_cat_id == 2) {
                    $interest_data = $this->buyers_interest_list(
                        $user_id,
                        $value["id"],
                        $prod_cat_id
                    );
                    $value["rating_details"] = show_rating(
                        $value["buyer_id"],
                        "buyer"
                    );
                } else {
                    $interest_data = "";
                }
                // print_r($interest_data);
                if (!empty($interest_data)) {
                    $value["buyer_intrest_count"] =
                        $interest_data["buyer_interest_count"];
                    $value["buyer_intrest"] = $interest_data["user_data"];
                } else {
                    $value["buyer_intrest_count"] = 0;
                    $value["buyer_intrest"] = [];
                }
                $result[] = $value;
            }
        }
        // print_r($result);
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
                "image_path" => $image_path,
                "client_profile_path" => $client_profile_path,
            ];
        }
        $this->api_response($response);
        exit();
    }
    // Applay actions by seller
    public function seller_action_post()
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
        switch ($status) {
            // Accept bid by seller
            case "1":
                $reason = "Accepted by Seller";
                $bid_status = $trade_product = isset($trade_status["Sold"])
                    ? $trade_status["Sold"]
                    : 4;
                break;
            // Revok bid by seller
            case "2":
                $reason = "Revoked by Seller";
                $bid_status = $status;
                $trade_product = isset($trade_status["Live"])
                    ? $trade_status["Live"]
                    : 3;
                break;
            // Reject bid by seller
            case "3":
                $reason = "Reject by Seller";
                $bid_status = $status;
                $trade_product = isset($trade_status["Live"])
                    ? $trade_status["Live"]
                    : 3;
                break;
            // Complete bid by seller
            case "5":
                $reason = "Completed by Seller";
                $bid_status = $status;
                $trade_product = isset($trade_status["Completed"])
                    ? $trade_status["Completed"]
                    : 5;
                break;
            // Self sold by seller
            case "7":
                $reason = "Sold by out of system";
                //$bid_status		= $status;
                $trade_product = isset($trade_status["Self Sold"])
                    ? $trade_status["Self Sold"]
                    : 7;
                break;
            // Bid Lock
            case "9":
                $reason = "Bid locked";
                $bid_status = $status;
                $trade_product = isset($trade_status["Bid Locked"])
                    ? $trade_status["Bid Locked"]
                    : 9;
                break;
            default:
                $reason = null;
                $bid_status = null;
                break;
        }
        // update status of trade_product by sold
        $update_data = [];
        $update_data = [
            "status" => $trade_product,
            "reason" => $reason,
            "updated_by_id" => $seller_id,
            "updated_on" => $current_date,
        ];
        $this->db->where("id", $product_id);
        $trade_product_result = $this->db->update(
            "trade_product",
            $update_data
        );
        if ($status != "6") {
            // update seller_action of trade_product_biding by accept
            $update_data = [];
            $update_data = [
                "seller_action" => $status,
                "seller_action_date" => $current_date,
                "bid_status" => $bid_status,
                "updated_by_id" => $seller_id,
                "updated_on" => $current_date,
            ];
            $this->db->where("id", $bid_id);
            $trade_product_result = $this->db->update(
                "trade_product_bidding",
                $update_data
            );
        }
        $data = [];
        $data["trade_product"] = $this->Masters_model->get_data(
            ["id", "user_id", "status", "reason"],
            "trade_product",
            [
                "id" => $product_id,
                "is_deleted" => "false",
                "is_active" => "true",
            ]
        );
        if ($status != "6") {
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
        }
        if ($trade_product_result) {
            if ($status == "1" || $status == "2" || $status == "3") {
                $notification_enable = get_config_data("notification_enable");
                $headers_data = array_change_key_case(
                    $this->input->request_headers(),
                    CASE_LOWER
                );
                $selected_lang = $headers_data["lang"]
                    ? $headers_data["lang"]
                    : "en";
                $map_key =
                    $status == 1
                        ? "bid_accepted_by_seller"
                        : ($status == 2
                            ? "bid_revoked_by_seller"
                            : "bid_rejected_by_seller");
                $sms_type =
                    $status == 1
                        ? "NERACE_Bid_Accepted_by_Seller"
                        : "NERACE_Bid_Revoked_Rejected_by_Seller";
                $replace = [
                    "body" => ["{PRODUCT_ID}" => $product_id],
                ];
                $buyer_detail = get_client_detail($buyer_id);
                $buyer_phone = $buyer_detail["phone"];
                $resp = dynamic_send_sms(
                    $buyer_phone,
                    "",
                    $sms_type,
                    "",
                    $selected_lang,
                    $replace
                );
                if ($notification_enable == 1) {
                    $notification_data = get_notification_detail(
                        $map_key,
                        "buyer",
                        $selected_lang
                    );
                    $custom_array = $userid = [];
                    if (!empty($notification_data)) {
                        $qry =
                            "SELECT device_id FROM client WHERE is_deleted='false' AND is_active='true' AND (device_id IS NOT NULL AND device_id!= 'null') AND id =" .
                            $buyer_id;
                        $res_data = $this->db->query($qry);
                        $device_id_data = $res_data->row_array();
                        $token = [];
                        if (count($device_id_data)) {
                            $token[] = $device_id_data["device_id"];
                        }
                        $userid[] = $buyer_id;
                        $custom_array["user_id"] = $userid;
                        $custom_array["map_key"] = $map_key;
                        $custom_array["reference_id"] = "client";
                        $seller_detail = get_client_detail($seller_id);
                        $seller_name = ucwords(
                            $seller_detail["first_name"] .
                                " " .
                                $seller_detail["last_name"]
                        );
                        $title = $notification_data["title"];
                        $arr = [
                            "body" => [
                                "{PRODUCT_ID}" => "#" . $product_id,
                                "{SELLER_NAME}" => $seller_name,
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
            if ($status == "9") {
                $notification_enable = get_config_data("notification_enable");
                if ($notification_enable == 1) {
                    $headers_data = array_change_key_case(
                        $this->input->request_headers(),
                        CASE_LOWER
                    );
                    $selected_lang = $headers_data["lang"]
                        ? $headers_data["lang"]
                        : "en";
                    $notification_data = get_notification_detail(
                        "other_buyer_bid_accepted",
                        "buyer",
                        $selected_lang
                    );
                    $custom_array = [];
                    if (!empty($notification_data)) {
                        $bidqry =
                            "SELECT buyer_id FROM trade_product_bidding WHERE is_deleted='false' AND is_active='true' AND buyer_id!=" .
                            $buyer_id .
                            " AND trade_product_id =" .
                            $product_id;
                        $bidres_data = $this->db->query($bidqry);
                        $trade_bidding_buyer_list = $bidres_data->row_array();
                        // echo "<pre>trade_bidding_buyer_list====";print_r($trade_bidding_buyer_list);
                        $user_id = [];
                        if (!empty($trade_bidding_buyer_list)) {
                            foreach ($trade_bidding_buyer_list as $val) {
                                $user_id[] =
                                    $trade_bidding_buyer_list["buyer_id"];
                            }
                        }
                        $user_id_list = !empty($user_id)
                            ? implode(", ", $user_id)
                            : "";
                        $custom_array["user_id"] = $user_id;
                        $custom_array["map_key"] = "other_buyer_bid_accepted";
                        $custom_array["reference_id"] = "client";
                        if (!empty($user_id_list)) {
                            $qry =
                                "SELECT device_id FROM client WHERE is_deleted='false' AND is_active='true' AND (device_id IS NOT NULL AND device_id!= 'null') AND id IN (" .
                                $user_id_list .
                                ")";
                            $res_data = $this->db->query($qry);
                            $device_id_data = $res_data->row_array();
                            // echo "<pre>device_id_data====";print_r($device_id_data);
                            $token = [];
                            if (count($device_id_data)) {
                                $token[] = $device_id_data["device_id"];
                            }
                            // echo "<pre>token====";print_r($token);
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
                                $notifiy = $this->Notification_model->sendPushNotifications_request_dynamic(
                                    $token,
                                    $title,
                                    $message,
                                    "",
                                    "",
                                    $custom_array,
                                    $type = "Product_details",
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
            }
        }
        // Start: Trade activity logs
        $trade_data = [
            "title" => "Seller Action",
            "description" => "Seller has taken action on product: " . $reason,
            "userid" => $seller_id,
            "user_type" => "Seller",
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
    // List all or single trade bidding
    public function trade_bidding_post()
    {
        $product_id = trim($this->input->post("product_id"));
        $id = trim($this->input->post("id"));
        $bidding = [];
        if (!empty($product_id)) {
            // Get Trade Product Bidding data
            $table = "trade_product_bidding";
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
                " id, buyer_id, seller_id, trade_product_id, qty, qty_unit, bid_price, bid_date, bid_count, seller_action, seller_action_date, buyer_action, buyer_action_date, bid_status, seller_invoice, incentive_id, incentive_status, incentive_redeemed_date ";
            $select_query =
                "SELECT $select_list FROM $table
			WHERE is_deleted = 'false'
			AND is_active = 'true' 
			AND trade_product_id = '" .
                $product_id .
                "' ";
            if (!empty($id)) {
                $select_query .= " AND id = '" . $id . "' ";
            }
            $order_query .= " ORDER BY id DESC ";
            $limit_query .= " LIMIT " . $limit . " OFFSET " . $start_sql;
            // Get total number of rows
            $num_row_query = $this->db->query($select_query . $order_query);
            $num_rows = $num_row_query->num_rows();
            // Get list of all data
            $row = $this->db->query(
                $select_query . $order_query . $limit_query
            );
            $trade_product_bidding = $row->result_array();
            if (!empty($trade_product_bidding)) {
                foreach ($trade_product_bidding as $bid_key => $bid_val) {
                    $buyer_detail = get_client_detail($bid_val["buyer_id"]);
                    $buyer_name = ucwords(
                        $buyer_detail["first_name"] .
                            " " .
                            $buyer_detail["last_name"]
                    );
                    $bid_val["buyer_profile_image"] =
                        $buyer_detail["profile_image"];
                    $bid_val["buyer_name"] = $buyer_name;
                    /* Start Get Status details : Akash */
                    $bidStatusId = $bid_val["bid_status"];
                    $bidStatusList = array_filter(TRADE_STATUS_LIST, function (
                        $bidStatusList_data
                    ) use ($bidStatusId) {
                        return $bidStatusList_data["id"] == $bidStatusId;
                    });
                    $bidStatusList = array_values($bidStatusList);
                    $bid_val["bid_status_title"] = $bidStatusList[0]["title"];
                    /* End Get Status details */
                    /* Start Get Status details : Akash */
                    $qtyUnitId = $bid_val["qty_unit"];
                    $qtyUnitList = array_filter(PROD_UNIT, function (
                        $qtyUnitList_data
                    ) use ($qtyUnitId) {
                        return $qtyUnitList_data["id"] == $qtyUnitId;
                    });
                    $qtyUnitList = array_values($qtyUnitList);
                    $bid_val["qty_unit_title"] = $qtyUnitList[0]["title"];
                    /* End Get Status details */
                    $bidding[] = $bid_val;
                }
            }
        }
        /* Start Get Highest Bid */
        $qry = $this->db->query(
            "SELECT MAX(bid_price) as highestBid FROM trade_product_bidding WHERE is_deleted = 'false' AND is_active = 'true' AND trade_product_id = " .
                $product_id .
                " "
        );
        $res = $qry->row_array();
        $highestBid = $res["highestbid"];
        /* End Get Highest Bid */
        if (!empty($bidding)) {
            $response = [
                "success" => 1,
                "data" => $bidding,
                "message" => lang("Listed_Successfully"),
                "num_rows" => $num_rows,
                "highestBid" => $highestBid,
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
    // Remove uploaded images
    public function remove_image_post()
    {
        $id = $this->input->post("id");
        $image = $this->input->post("image");
        $type = $this->input->post("type");
        if (!empty($id) && !empty($image) && !empty($type)) {
            $query = "SELECT id, user_id, prod_images FROM trade_product WHERE id = $id";
            $row = $this->db->query($query);
            $result = $row->row_array();
            if (!empty($result)) {
                $prod_images = json_decode($result["prod_images"], true);
                // foreach ($prod_images as $key => $value) {
                // 	if (($img_key = array_search($image, $value)) !== false) {
                // 		unset($prod_images[$key][$img_key]);
                // 	}
                // }
                $valuesToRemove = [$image];
                $modifiedArray = [];
                $status = "";
                foreach ($prod_images as $key => $subArray) {
                    $modifiedSubArray = [];
                    foreach ($subArray as $value) {
                        // Check if the current value should be removed
                        if (!in_array($value, $valuesToRemove)) {
                            $modifiedSubArray[] = $value;
                        }
                    }
                    // Move to draft if product images are null.
                    if ($key == "product" && empty($modifiedSubArray)) {
                        $status = 8;
                    }
                    $modifiedArray[$key] = $modifiedSubArray;
                }
                // echo'<pre>';print_r($modifiedArray);exit;
                $update_data = [];
                if (!empty($modifiedArray) && is_array($modifiedArray)) {
                    $update_data = [
                        "prod_images" => json_encode($modifiedArray),
                        "updated_by_id" => $result["user_id"],
                        "updated_on" => date("Y-m-d H:i:s"),
                    ];
                    if (!empty($status)) {
                        $update_data["status"] = $status;
                    }
                }
                // print_r($update_data);exit;
                $this->db->where("id", $id);
                $update = $this->db->update("trade_product", $update_data);
                $response = [
                    "success" => 1,
                    "data" => $modifiedArray,
                    "message" => lang("Deleted_Successfully"),
                ];
            } else {
                $response = [
                    "success" => 0,
                    "data" => [],
                    "message" => lang("Data_Not_Found"),
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
    public function incentive_list_get()
    {
        $data = $this->Masters_model->get_data("*", "product_services", [
            "allow_incentive" => "true",
            "is_deleted" => "false",
            "is_active" => "true",
        ]);
        $service_image_path = $this->config_url["service_image_url"];
        if (!empty($data)) {
            $response = [
                "success" => 1,
                "data" => $data,
                "message" => "Listed Successfully!",
                "service_image_path" => $service_image_path,
            ];
        } else {
            $response = [
                "success" => 0,
                "data" => [],
                "message" => "No Record Found!",
                "service_image_path" => $service_image_path,
            ];
        }
        $this->api_response($response);
    }
    public function apply_for_incentive_post()
    {
        $id = $this->input->post("incentive_id");
        $trade_id = $this->input->post("trade_bidding_id");
        $user_id = $this->input->post("user_id");
        if (!empty($trade_id)) {
            $trade = $this->Masters_model->get_data(
                "*",
                "trade_product_bidding",
                [
                    "id" => $trade_id,
                    "is_deleted" => "false",
                    "is_active" => "true",
                ]
            );
            // print_r($trade);exit;
            if (!empty($id) && !empty($trade)) {
                $product_services = $this->Masters_model->get_data(
                    "*",
                    "product_services",
                    [
                        "service_id" => $id,
                        "is_deleted" => "false",
                        "is_active" => "true",
                    ]
                );
                if (!empty($product_services)) {
                    $update_data = [
                        "incentive_id" => $id,
                        "incentive_status" => 1,
                        "updated_by_id" => $user_id,
                        "updated_on" => date("Y-m-d H:i:s"),
                    ];
                    // print_r($update_data);exit;
                    $this->db->where("id", $trade_id);
                    $update = $this->db->update(
                        "trade_product_bidding",
                        $update_data
                    );
                    if ($update) {
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
                                "incentive_awarded",
                                "seller",
                                $selected_lang
                            );
                            $custom_array = $userid = [];
                            if (!empty($notification_data)) {
                                $qry =
                                    "SELECT device_id FROM client WHERE is_deleted='false' AND is_active='true' AND (device_id IS NOT NULL AND device_id!= 'null') AND id =" .
                                    $user_id;
                                $res_data = $this->db->query($qry);
                                $device_id_data = $res_data->row_array();
                                $token = [];
                                if (count($device_id_data)) {
                                    $token[] = $device_id_data["device_id"];
                                }
                                $userid[] = $user_id;
                                $custom_array["user_id"] = $userid;
                                $custom_array["map_key"] = "incentive_awarded";
                                $custom_array["reference_id"] = "client";
                                $title = $notification_data["title"];
                                $arr = [
                                    "body" => ["{INCENTIVE_ID}" => "#" . $id],
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
                                        $type = "Incentive",
                                        $trade_id
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
                    $response = [
                        "success" => 1,
                        "data" => $update_data,
                        "message" => "Updated successfully!",
                    ];
                } else {
                    $response = [
                        "success" => 0,
                        "data" => [],
                        "message" => "Select Incentive not found!",
                    ];
                }
            } else {
                $response = [
                    "success" => 0,
                    "data" => [],
                    "message" => "Parameter Missing!",
                ];
            }
        } else {
            $response = [
                "success" => 0,
                "data" => [],
                "message" => "Parameter Missing!",
            ];
        }
        $this->api_response($response);
    }
    // Upload images of trade products
    public function upload_invoice_post()
    {
        // print_r($_POST);
        // print_r($_FILES);exit;
        $id = $this->input->post("trade_bidding_id");
        $action_by = $this->input->post("action_by")
            ? $this->input->post("action_by")
            : "seller_invoice";
        $img_errors = [];
        $img_files = "";
        // Allowed file extensions
        $allowed_extensions = ["jpg", "jpeg", "png", "webp"];
        $quality = 100; // WebP quality
        $no = 1;
        // Logo upload
        if (isset($_FILES)) {
            // Specify the upload directory
            $current_month_yr = date("m_Y");
            $invoice_folder =
                $this->root_folder . "uploads/config_master/" . $action_by;
            if (!file_exists($invoice_folder)) {
                mkdir($invoice_folder, 0777, true);
                chmod($invoice_folder, 0777);
            }
            $invoice_og_folder =
                $this->root_folder .
                "uploads/config_master/" .
                $action_by .
                "/og";
            if (!file_exists($invoice_og_folder)) {
                mkdir($invoice_og_folder, 0777, true);
                chmod($invoice_og_folder, 0777);
            }
            $upload_folder = $invoice_folder . "/" . $current_month_yr;
            if (!file_exists($upload_folder)) {
                mkdir($upload_folder, 0777, true);
                chmod($upload_folder, 0777);
            }
            // File properties
            $img_name = $_FILES["invoice"]["name"];
            $img_tmp = $_FILES["invoice"]["tmp_name"];
            $img_size = $_FILES["invoice"]["size"];
            $img_error = $_FILES["invoice"]["error"];
            // Get the file extension
            $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
            // Check if the file has a valid extension
            if (in_array($img_ext, $allowed_extensions)) {
                // Check for file upload errors
                if ($img_error === 0) {
                    // Generate a unique file name
                    $new_file_name =
                        "invoice_" .
                        $action_by .
                        "_" .
                        date("YmdHis") .
                        "." .
                        $img_ext;
                    // Move the uploaded file to the destination directory
                    $destination = $invoice_og_folder . "/" . $new_file_name;
                    $originalImage = imagecreatefromstring(
                        file_get_contents($img_tmp)
                    );
                    // Convert to WebP and optimize
                    ob_start();
                    imagewebp($originalImage, null, $quality);
                    $webpContents = ob_get_contents();
                    ob_end_clean();
                    $outputFileName =
                        pathinfo($new_file_name, PATHINFO_FILENAME) . ".webp";
                    $outputPathAndFileName =
                        rtrim($invoice_og_folder, "/") . "/" . $outputFileName;
                    file_put_contents($outputPathAndFileName, $webpContents);
                    // Free up memory
                    imagedestroy($originalImage);
                    $inputImagePath = $outputPathAndFileName;
                    $outputImagePath =
                        rtrim($upload_folder, "/") . "/" . $outputFileName;
                    $maxWidth = 900;
                    $maxHeight = 600;
                    $compressionQuality = 120; // 0 (worst quality, small file) to 100 (best quality, large file)
                    $this->reduceImageSize(
                        $inputImagePath,
                        $outputImagePath,
                        $maxWidth,
                        $maxHeight,
                        $compressionQuality
                    );
                    $img_files = $current_month_yr . "/" . $outputFileName;
                } else {
                    $img_errors = "Error: " . $img_errors;
                }
            } else {
                $img_errors =
                    "Invalid file extension. Only " .
                    implode(", ", $allowed_extensions) .
                    " files are allowed.";
            }
            if (!empty($id)) {
                $query = "SELECT id, buyer_id,trade_product_id, seller_invoice, buyer_invoice FROM trade_product_bidding WHERE id = $id";
                $row = $this->db->query($query);
                $result = $row->row_array();
                if (!empty($result)) {
                    if ($action_by == "seller_invoice") {
                        $update_data = ["seller_invoice" => $img_files];
                    } elseif ($action_by == "buyer_invoice") {
                        $update_data = ["buyer_invoice" => $img_files];
                    }
                    $this->db->where("id", $id);
                    $this->db->update("trade_product_bidding", $update_data);
                    $headers_data = array_change_key_case(
                        $this->input->request_headers(),
                        CASE_LOWER
                    );
                    $selected_lang = $headers_data["lang"]
                        ? $headers_data["lang"]
                        : "en";
                    $sms_type = "NERACE_Receipt_Uploaded_by_Seller";
                    $buyer_detail = get_client_detail($result["buyer_id"]);
                    $buyer_phone = $buyer_detail["phone"];
                    $replace = [
                        "body" => [
                            "{PRODUCT_ID}" => $result["trade_product_id"],
                        ],
                    ];
                    $resp = dynamic_send_sms(
                        $buyer_phone,
                        "",
                        $sms_type,
                        "",
                        $selected_lang,
                        $replace
                    );
                    $sms_type1 = "NERACE_Sold_Product_Bid_Completed";
                    $resp1 = dynamic_send_sms(
                        $buyer_phone,
                        "",
                        $sms_type1,
                        "",
                        $selected_lang,
                        $replace
                    );
                    $response = [
                        "success" => 1,
                        "data" => $update_data,
                        "message" => "Invoice uploaded successfully",
                        "error" => $img_errors,
                    ];
                } else {
                    $response = [
                        "success" => 0,
                        "data" => [],
                        "message" => "Trade Bidding not found!",
                        "error" => $img_errors,
                    ];
                }
            } else {
                $response = [
                    "success" => 0,
                    "data" => [],
                    "message" => "Missing Parameter!",
                    "error" => $img_errors,
                ];
            }
            unlink($invoice_og_folder);
        } else {
            $response = [
                "success" => 0,
                "data" => [],
                "message" => "Missing Parameter!",
                "error" => $img_errors,
            ];
        }
        // print_r($response);exit;
        $this->api_response($response);
        exit();
    }
    // added interest on upcomping product by buyer
    public function add_interest_onproduct_post()
    {
        $buyer_id = $this->input->post("buyer_id");
        $trade_product_id = $this->input->post("trade_product_id");
        //$product_id		= $this->input->post('product_id');
        $response = [];
        if ($buyer_id != "" && $trade_product_id != "") {
            $row3 = $this->db->query(
                "SELECT count(*) FROM trade_product_interest WHERE is_deleted = 'false' AND trade_product_id = " .
                    $trade_product_id .
                    " AND buyer_id=" .
                    $buyer_id
            );
            $result_interest = $row3->result_array();
            if ($result_interest[0]["count"] > 0) {
                $response = [
                    "status" => 1,
                    "data" => 1,
                    "message" => lang("Already_Added"),
                ];
            } else {
                $row = $this->db->query(
                    "SELECT user_id FROM trade_product WHERE is_deleted = 'false' AND id = " .
                        $trade_product_id
                );
                $result_product = $row->result_array();
                $insert = [
                    "buyer_id" => $buyer_id,
                    "trade_product_id" => $trade_product_id,
                    "seller_id" => $result_product[0]["user_id"],
                    "created_by_id" => $buyer_id,
                    "created_on" => current_date(),
                ];
                $this->db->insert("trade_product_interest", $insert);
                $response = [
                    "status" => 1,
                    "data" => 1,
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
        $this->api_response($response);
    }
    // buyers interest product list
    public function buyers_interest_product_list_post()
    {
        $table = "trade_product as tp";
        $seller_id = $this->input->post("seller_id");
        $trade_product_id = $this->input->post("trade_product_id");
        $data = $response = [];
        if ($seller_id != "" && $trade_product_id != "") {
            $select_list =
                " tp.id, tp.user_id, tp.prod_cat_id, tp.prod_type_id, pt.title as product_type_title, tp.prod_details, tp.prod_id, pm.title as product_title, tp.prod_variety_id, pv.title as product_variety_title, tp.active_till_date, tp.surplus, tp.surplus_unit, tp.other_details, tp.sell_qty, tp.sell_qty_unit, tp.price, tp.price_unit, tp.with_logistic_partner, tp.with_packging, tp.packaging_master_id, pkg.title as packaging_title, tp.storage_type_id, st.title as storage_type_title, s.id as state_id, s.name as state_name, c.id as city_id, c.name as city_name, tp.pickup_location, tp.other_distance, tp.produce_to_highway_distance, tp.advance_payment, tp.negotiations, tp.certifcations, tp.trade_status, tp.partial_trade, tp.status, tp.reason, tp.added_date, tp.expiry_date, tp.approved_date, tp.rejected_date, tp.prod_images";
            $sql =
                "SELECT $select_list FROM $table
            LEFT JOIN prod_master as pm ON pm.id = tp.prod_id
            LEFT JOIN prod_variety as pv ON pv.id = tp.prod_variety_id
            LEFT JOIN prod_type as pt ON pt.id = tp.prod_type_id
            LEFT JOIN packaging_master as pkg ON pkg.id = tp.packaging_master_id
            LEFT JOIN storage_type as st ON st.id = tp.storage_type_id
            LEFT JOIN states_new as s ON s.id = tp.state
            LEFT JOIN cities_new as c ON c.id = tp.city
            WHERE tp.is_deleted = 'false' AND tp.is_active = 'true' AND tp.user_id = '" .
                $seller_id .
                "' ";
            if (!empty($trade_product_id)) {
                $sql .= " AND tp.id = '" . $trade_product_id . "' ";
            }
            $row2 = $this->db->query($sql);
            $res = $row2->result_array();
            if (!empty($res)) {
                foreach ($res as $key => $value) {
                    $prod_cat_id = $value["prod_cat_id"];
                    $product_category = array_filter(PROD_CAT, function (
                        $product_category_data
                    ) use ($prod_cat_id) {
                        return $product_category_data["id"] == $prod_cat_id;
                    });
                    $product_category = array_values($product_category);
                    $res[0]["product_category_title"] =
                        $product_category[0]["title"];
                    $get_unit_list_ids = [
                        "surplus_unit",
                        "sell_qty_unit",
                        "price_unit",
                    ];
                    foreach ($get_unit_list_ids as $unit_id) {
                        $unitId = $value[$unit_id];
                        $product_unit = array_filter(PROD_UNIT, function (
                            $product_unit_data
                        ) use ($unitId) {
                            return $product_unit_data["id"] == $unitId;
                        });
                        $product_unit = array_values($product_unit);
                        $res[0][$unit_id . "_title"] =
                            $product_unit[0]["title"];
                    }
                    $get_status_list_ids = [
                        "status",
                        "trade_status",
                        "bid_status",
                    ];
                    foreach ($get_status_list_ids as $status_id) {
                        $statusId = $value[$status_id];
                        $statusList = array_filter(TRADE_STATUS_LIST, function (
                            $statusList_data
                        ) use ($statusId) {
                            return $statusList_data["id"] == $statusId;
                        });
                        $statusList = array_values($statusList);
                        $res[0][$status_id . "_title"] =
                            $statusList[0]["title"];
                        $res[0][$status_id . "_class"] =
                            $statusList[0]["statusClass"];
                    }
                    //$res[0]['added_date'] = date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($value['added_date'])));
                    $res[0]["added_date"] = date(
                        "Y-m-d H:i:s",
                        strtotime($value["added_date"])
                    );
                    $prod_details_id = $value["prod_details"];
                    $prod_details = array_filter(PROD_DETAILS, function (
                        $prod_details_data
                    ) use ($prod_cat_id) {
                        return $prod_details_data["id"] == $prod_cat_id;
                    });
                    $prod_details = array_values($prod_details);
                    $res[0]["prod_details_title"] = $prod_details[0]["title"];
                }
            }
            $data["prod_interest_data"] = $res;
            $interestdata = $this->buyers_interest_list(
                $seller_id,
                $trade_product_id
            );
            $data["buyer_interest_count"] =
                $interestdata["buyer_interest_count"];
            $data["buyer_interest_list"] = $interestdata["user_data"];
            $data["profile_image_path"] = $this->config_url["partner_img_url"];
            if (empty($data)) {
                $response = [
                    "success" => 0,
                    "data" => [],
                    "message" => "No Record Found!",
                ];
            } else {
                $response = [
                    "success" => 1,
                    "data" => $data,
                    "message" => "Listed Successfully!",
                ];
            }
        } else {
            $response = [
                "status" => 0,
                "data" => [],
                "message" => lang("Missing_Parameter"),
            ];
        }
        $this->api_response($response);
    }
    public function buyers_interest_list(
        $seller_id,
        $trade_product_id,
        $prod_cat_id = ""
    ) {
        $table = "trade_product as tp";
        /*          $seller_id				= $this->input->post('seller_id');
         $product_id				= $this->input->post('product_id'); */
        $response = [];
        if ($seller_id != "") {
            $sql1 =
                "SELECT tp.prod_id,tp.prod_cat_id,tpi.buyer_id,tpi.created_on as interest_shown_on ,c.first_name,c.middle_name,c.last_name,c.profile_image FROM trade_product_interest as tpi
			LEFT JOIN trade_product as tp ON tp.id = tpi.trade_product_id
			LEFT JOIN client as c ON c.id = tpi.buyer_id
			WHERE tpi.is_deleted = 'false' AND tpi.is_active = 'true' AND tpi.seller_id = '" .
                $seller_id .
                "'";
            if (!empty($trade_product_id)) {
                $sql1 .= " AND tp.id = '" . $trade_product_id . "' ";
            }
            if (!empty($prod_cat_id)) {
                $sql1 .= " AND tp.prod_cat_id = '" . $prod_cat_id . "' ";
            }
            $sql1 .= " ORDER BY tpi.created_on desc";
            $row3 = $this->db->query($sql1);
            $user_data = $row3->result_array();
            $data[
                "buyer_interest_count"
            ] = $this->Masters_model->get_query_count($sql1);
            $user_data_new = [];
            foreach ($user_data as $key => $value) {
                //$value['interest_shown_on'] = date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($value['interest_shown_on'])));
                $value["interest_shown_on"] = date(
                    "Y-m-d H:i:s",
                    strtotime($value["interest_shown_on"])
                );
                $value["rating_details"] = show_rating(
                    $value["buyer_id"],
                    "buyer"
                );
                $user_data_new[] = $value;
            }
            $data["user_data"] = $user_data_new;
        }
        return $data;
    }
    public function upcoming_product_list_get($seller_id)
    {
        $select_list =
            "tp.id, pm.title as product_title, pv.title as product_variety_title";
        $select_query = "SELECT $select_list FROM trade_product as tp
		LEFT JOIN prod_master as pm ON pm.id = tp.prod_id
		LEFT JOIN prod_variety as pv ON pv.id = tp.prod_variety_id
		WHERE tp.is_deleted = 'false' AND tp.is_active = 'true'";
        $select_query .=
            " AND tp.prod_cat_id = '2' AND tp.user_id = '" .
            $seller_id .
            "' AND tp.id in (select trade_product_id from trade_product_interest where seller_id='" .
            $seller_id .
            "' GROUP BY trade_product_id)";
        //$select_query .= 'order by tp.created_on desc';
        // $select_query  .= "group by tp.prod_id,pm.title,pv.title";
        //echo " select_query===>". $select_query;
        $select_query .=
            "	ORDER BY ( SELECT MAX(tpi.created_on) FROM trade_product_interest as tpi WHERE tpi.seller_id = '279' AND tpi.trade_product_id = tp.id) DESC, tp.id DESC;";
        $row2 = $this->db->query($select_query);
        $upcoming_product_list = $row2->result_array();
        if (empty($upcoming_product_list)) {
            $response = [
                "success" => 0,
                "data" => [],
                "message" => "No Record Found!",
            ];
        } else {
            $response = [
                "success" => 1,
                "data" => $upcoming_product_list,
                "message" => "Listed Successfully!",
            ];
        }
        $this->api_response($response);
    }
    // added demand product by buyer
    public function add_demand_product_post()
    {
        $buyer_id = $this->input->post("buyer_id");
        $demand_type = $this->input->post("demand_type");
        $product_id = $this->input->post("product_id");
        $prod_cat_id = $this->input->post("prod_cat_id");
        $prod_detail = $this->input->post("prod_detail");
        $prod_type_id = $this->input->post("prod_type_id");
        $prod_variety_id = $this->input->post("prod_variety_id");
        $price_from = $this->input->post("price_from");
        $price_to = $this->input->post("price_to");
        $price_unit = $this->input->post("price_unit");
        $available_from = $this->input->post("available_from");
        $available_to = $this->input->post("available_to");
        $quantity = $this->input->post("quantity");
        $response = [];
        if ($buyer_id != "") {
            $insert = [
                "buyer_id" => $buyer_id,
                "demand_type" => $demand_type,
                "prod_id" => $product_id,
                "prod_cat_id" => $prod_cat_id,
                "sub_type" => $prod_detail,
                "prod_type_id" => $prod_type_id,
                "prod_variety_id" => $prod_variety_id,
                "price_from" => $price_from,
                "price_to" => $price_to,
                "price_unit" => $price_unit,
                "created_by_id" => $buyer_id,
                "created_on" => current_date(),
                "available_from" => $available_from ? $available_from : null,
                "available_to" => $available_to ? $available_to : null,
                "quantity" => $quantity,
            ];
            $res = $this->db->insert("trade_product_demand", $insert);
            if ($res) {
                $notification_enable = get_config_data("notification_enable");
                if ($notification_enable == 1) {
                    $headers_data = array_change_key_case(
                        $this->input->request_headers(),
                        CASE_LOWER
                    );
                    $selected_lang = $headers_data["lang"]
                        ? $headers_data["lang"]
                        : "en";
                    $notification_data = get_notification_detail(
                        "demand_placed_by_the_buyer",
                        "seller",
                        $selected_lang
                    );
                    $custom_array = $userid = [];
                    if (!empty($notification_data)) {
                        $select_query =
                            "SELECT title as product_title FROM prod_master 
                    WHERE is_deleted = 'false' AND is_active = 'true' AND id=" .
                            $product_id;
                        $row2 = $this->db->query($select_query);
                        $product_detail = $row2->row_array();
                        $qry =
                            "SELECT id,device_id FROM client WHERE is_deleted='false' AND is_active='true' AND (device_id IS NOT NULL AND device_id!= 'null') AND client_type=2";
                        //$qry = "SELECT device_id FROM client WHERE is_deleted='false' AND is_active='true' AND (device_id IS NOT NULL AND device_id!= 'null') AND client_type=2 AND id IN (select Distinct(user_id) from trade_product where prod_id=".$product_id.")";
                        $res_data = $this->db->query($qry);
                        $device_id_data = $res_data->result_array();
                        $token = [];
                        if (count($device_id_data)) {
                            foreach ($device_id_data as $val) {
                                $token[] = $val["device_id"];
                                $userid[] = $val["id"];
                            }
                        }
                        $custom_array["user_id"] = $userid;
                        $custom_array["map_key"] = "demand_placed_by_the_buyer";
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
                                "{PRODUCT_NAME}" =>
                                    $product_detail["product_title"],
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
                                $type = "Demand",
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
            $response = [
                "status" => 1,
                "data" => 1,
                "message" => lang("Added_Successfully"),
            ];
        } else {
            $response = [
                "status" => 0,
                "data" => [],
                "message" => lang("Missing_Parameter"),
            ];
        }
        $this->api_response($response);
    }
    public function buyers_demand_product_list_post()
    {
        $prod_cat_id = $this->input->post("prod_cat_id");
        $product_id = $this->input->post("product_id");
        $demand_type = $this->input->post("demand_type");
        $demand_product_list = $response = [];
        $select_list =
            "tpd.id,c.first_name,c.middle_name,c.last_name,c.profile_image,tpd.demand_type,tpd.prod_cat_id,tpd.prod_type_id,tpd.prod_variety_id,tpd.prod_id,tpd.sub_type,tpd.created_on as posted_on ,tpd.price_from,tpd.price_to,tpd.price_unit,tpd.available_from,tpd.available_to,tpd.quantity, pm.title as product_title, pv.title as product_variety_title, pt.title as product_type_title";
        $select_query = "SELECT $select_list FROM trade_product_demand as tpd
            LEFT JOIN prod_master as pm ON pm.id = tpd.prod_id
            LEFT JOIN prod_variety as pv ON pv.id = tpd.prod_variety_id
            LEFT JOIN prod_type as pt ON pt.id = tpd.prod_type_id
            LEFT JOIN client as c ON c.id = tpd.buyer_id
            WHERE tpd.is_deleted = 'false' AND tpd.is_active = 'true'";
        if (!empty($prod_cat_id)) {
            $select_query .= " AND tpd.prod_cat_id = '" . $prod_cat_id . "'";
        }
        if (!empty($product_id)) {
            $select_query .= " AND tpd.prod_id = '" . $product_id . "'";
        }
        if (!empty($demand_type)) {
            $select_query .= " AND tpd.demand_type = '" . $demand_type . "'";
        }
        $select_query .= "order by tpd.created_on desc";
        $row2 = $this->db->query($select_query);
        $result = $row2->result_array();
        $demand_product_list = [];
        if (!empty($result)) {
            foreach ($result as $key => $value) {
                $prod_cat_id = $value["prod_cat_id"];
                $product_category = array_filter(PROD_CAT, function (
                    $product_category_data
                ) use ($prod_cat_id) {
                    return $product_category_data["id"] == $prod_cat_id;
                });
                $product_category = array_values($product_category);
                $value["product_category_title"] =
                    $product_category[0]["title"];
                $unitId = $value["price_unit"];
                $product_unit = array_filter(PROD_UNIT, function (
                    $product_unit_data
                ) use ($unitId) {
                    return $product_unit_data["id"] == $unitId;
                });
                $product_unit = array_values($product_unit);
                $value["price_unit_title"] = $product_unit[0]["title"];
                //$value['posted_on'] = date('Y-m-d H:i:s', strtotime('+5 hour +30 minutes', strtotime($value['posted_on'])));
                $value["posted_on"] = date(
                    "Y-m-d H:i:s",
                    strtotime($value["posted_on"])
                );
                $demand_product_list[$key] = $value;
            }
        }
        if (empty($demand_product_list)) {
            $response = [
                "success" => 0,
                "data" => [],
                "message" => "No Record Found!",
            ];
        } else {
            $response = [
                "success" => 1,
                "data" => $demand_product_list,
                "message" => "Listed Successfully!",
            ];
        }
        $this->api_response($response);
    }
    public function product_list_post()
    {
        $selected_lang = $this->selected_lang;
        $product_category = $this->input->post("product_category");
        // $product_type       = $this->input->post('product_type');
        $result = [];
        // if (!empty($product_type)) {
        $table = "prod_master";
        $query =
            "SELECT id, title, commodity_title, lang_json->>'" .
            $selected_lang .
            "' as title_" .
            $selected_lang .
            ", lang_json, prod_cat, prod_type_id FROM " .
            $table .
            " WHERE is_deleted = 'false' AND is_active = 'true' ORDER BY title ASC";
        $row = $this->db->query($query);
        $res = $row->result_array();
        if (!empty($product_category) && !empty($res)) {
            foreach ($res as $key => $value) {
                $prod_cat = !empty($value["prod_cat"])
                    ? json_decode($value["prod_cat"], true)
                    : [];
                if (
                    !empty($prod_cat) &&
                    in_array($product_category, $prod_cat)
                ) {
                    $result[] = $value;
                }
            }
        }
        if (count($result) > 0) {
            $response = [
                "success" => 1,
                "data" => $result,
                "message" => lang("Listed_Successfully"),
            ];
        } else {
            $response = [
                "success" => 1,
                "data" => [],
                "message" => lang("Data_Not_Found"),
            ];
        }
        /*  } else {
             $response = array("success" => 0, "data" => [], "message" => lang('Missing_Parameter'));
         } */
        $this->api_response($response);
        exit();
    }
    public function trade_product_report_post()
    {
        try {
            $user_id = $this->input->post("user_id");
            $year = $this->input->post("year")
                ? trim($this->input->post("year"))
                : date("Y");
            $month = $this->input->post("month")
                ? trim($this->input->post("month"))
                : "";
            $day = $this->input->post("day")
                ? trim($this->input->post("day"))
                : "";
            if (empty($user_id)) {
                throw new Exception("User id is required.");
            }
            $table = "trade_product";
            $select_list = " status, COUNT(*) AS row_count ";
            $select_query =
                "SELECT $select_list FROM $table
            WHERE is_deleted = 'false' AND is_active = 'true' AND user_id = '" .
                $user_id .
                "' ";
            if (!empty($year)) {
                $select_query .=
                    " AND DATE_PART('year', added_date) = " . $year . " ";
            }
            if (!empty($month)) {
                $select_query .=
                    " AND DATE_PART('year', added_date) = " . date("Y") . " ";
                $select_query .=
                    " AND DATE_PART('month', added_date) = " . $month . " ";
            }
            if (!empty($day)) {
                $select_query .=
                    " AND DATE_PART('year', added_date) = " . date("Y") . " ";
                $select_query .=
                    " AND DATE_PART('month', added_date) = " . date("m") . " ";
                $select_query .=
                    " AND DATE_PART('day', added_date) = " . $day . " ";
            }
            $group_by_query .= " GROUP BY status ";
            $order_by_query .= " ORDER BY status ";
            $row = $this->db->query(
                $select_query . $group_by_query . $order_by_query
            );
            $result = $row->result_array();
            $notInStatus = [8];
            foreach ($result as $key => $value) {
                $statusId = $value["status"];
                if (!empty($statusId) && !in_array($statusId, $notInStatus)) {
                    $statusList = array_filter(TRADE_STATUS_LIST, function (
                        $statusList_data
                    ) use ($statusId) {
                        return $statusList_data["id"] == $statusId;
                    });
                    $statusList = array_values($statusList);
                    $value["status_title"] = $statusList[0]["title"];
                    $value["status_class"] = $statusList[0]["statusClass"];
                    $data[] = $value;
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
        } catch (Exception $e) {
            $response = [
                "success" => 0,
                "data" => [],
                "message" => $e->getMessage(),
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
    // List all or single trade products
    public function marketable_surplus_post()
    {
        $user_id = trim($this->input->post("user_id"));
        $id = trim($this->input->post("id"));
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
        $prod_cat_id = $this->input->post("prod_cat_id");
        $prod_id = $this->input->post("prod_id");
        $prod_type_id = $this->input->post("prod_type_id");
        // $trade_status   = $this->input->post('trade_status');
        $status = ["3", "4", "5"];
        $select_list =
            " tp.id, tp.user_id, tp.prod_cat_id, tp.prod_type_id, pt.title as product_type_title, tp.prod_id, pm.title as product_title, tp.prod_variety_id, pv.title as product_variety_title, tp.surplus, tp.surplus_unit, tp.sell_qty, tp.sell_qty_unit, tp.price, tp.price_unit, tp.status ";
        $select_query =
            "SELECT $select_list FROM $table
        LEFT JOIN prod_master as pm ON pm.id = tp.prod_id
        LEFT JOIN prod_variety as pv ON pv.id = tp.prod_variety_id
        LEFT JOIN prod_type as pt ON pt.id = tp.prod_type_id
        WHERE tp.is_deleted = 'false' AND tp.is_active = 'true' AND tp.user_id = '" .
            $user_id .
            "'  ";
        // AND tp.status IN (".implode(',',$status).")
        if (!empty($prod_cat_id)) {
            $select_query .= " AND tp.prod_cat_id = '" . $prod_cat_id . "' ";
        } else {
            $select_query .= " AND tp.prod_cat_id != '2' ";
        }
        if (!empty($prod_id)) {
            $select_query .= " AND tp.prod_id = '" . $prod_id . "' ";
        }
        if (!empty($prod_type_id)) {
            $select_query .= " AND tp.prod_type_id = '" . $prod_type_id . "' ";
        }
        if (!empty($id)) {
            $select_query .= " AND tp.id = '" . $id . "' ";
        }
        $order_query .= " ORDER BY tp.id DESC ";
        $limit_query .= " LIMIT " . $limit . " OFFSET " . $start_sql;
        // Get total number of rows
        $num_row_query = $this->db->query($select_query . $order_query);
        $num_rows = $num_row_query->num_rows();
        // Get list of all data
        $row = $this->db->query($select_query . $order_query);
        $res = $row->result_array();
        $result = [];
        if (!empty($res)) {
            foreach ($res as $key => $value) {
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
                $get_status_list_ids = ["status"];
                $statusId = $value["status"];
                $statusList = array_filter(TRADE_STATUS_LIST, function (
                    $statusList_data
                ) use ($statusId) {
                    return $statusList_data["id"] == $statusId;
                });
                $statusList = array_values($statusList);
                $value["status_title"] = $statusList[0]["title"];
                $value["status_class"] = $statusList[0]["statusClass"];
                $total = (int) $value["surplus"];
                $sold = $listed = 0;
                if (in_array($value["status"], [4, 5])) {
                    $sold = (int) $value["sell_qty"];
                    $available = $total - $sold;
                } else {
                    $listed = (int) $value["sell_qty"];
                    $available = $total;
                }
                $value["surplus_total"] = $total;
                $value["sell_qty_sold"] = $sold;
                $value["surplus_available"] = $available;
                $value["listed"] = $listed;
                if ($total != $listed) {
                    $result[] = $value;
                }
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
    // List all or single trade products
    public function marketable_surplus_new_post()
    {
        $user_id = trim($this->input->post("user_id"));
        $id = trim($this->input->post("id"));
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
        $prod_cat_id = $this->input->post("prod_cat_id");
        $prod_id = $this->input->post("prod_id");
        $prod_type_id = $this->input->post("prod_type_id");
        // $trade_status   = $this->input->post('trade_status');
        $status = ["3", "4", "5"];
        $select_list =
            " tp.id, tp.user_id, tp.prod_cat_id, tp.prod_type_id, pt.title as product_type_title, tp.prod_id, pm.title as product_title, tp.prod_variety_id, pv.title as product_variety_title, tp.surplus, tp.surplus_unit, tp.sell_qty, tp.sell_qty_unit, tp.price, tp.price_unit, tp.status ";
        $select_query =
            "SELECT $select_list FROM $table
        LEFT JOIN prod_master as pm ON pm.id = tp.prod_id
        LEFT JOIN prod_variety as pv ON pv.id = tp.prod_variety_id
        LEFT JOIN prod_type as pt ON pt.id = tp.prod_type_id
        WHERE tp.is_deleted = 'false' AND tp.is_active = 'true' AND tp.user_id = '" .
            $user_id .
            "'  ";
        // AND tp.status IN (".implode(',',$status).")
        if (!empty($prod_cat_id)) {
            $select_query .= " AND tp.prod_cat_id = '" . $prod_cat_id . "' ";
        } else {
            $select_query .= " AND tp.prod_cat_id != '2' ";
        }
        if (!empty($prod_id)) {
            $select_query .= " AND tp.prod_id = '" . $prod_id . "' ";
        }
        if (!empty($prod_type_id)) {
            $select_query .= " AND tp.prod_type_id = '" . $prod_type_id . "' ";
        }
        if (!empty($id)) {
            $select_query .= " AND tp.id = '" . $id . "' ";
        }
        $order_query .= " ORDER BY tp.id DESC ";
        $limit_query .= " LIMIT " . $limit . " OFFSET " . $start_sql;
        // Get total number of rows
        $num_row_query = $this->db->query($select_query . $order_query);
        $num_rows = $num_row_query->num_rows();
        // Get list of all data
        $row = $this->db->query($select_query . $order_query);
        $res = $row->result_array();
        $result = $data = [];
        if (!empty($res)) {
            foreach ($res as $key => $value) {
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
                $get_status_list_ids = ["status"];
                $statusId = $value["status"];
                $statusList = array_filter(TRADE_STATUS_LIST, function (
                    $statusList_data
                ) use ($statusId) {
                    return $statusList_data["id"] == $statusId;
                });
                $statusList = array_values($statusList);
                $value["status_title"] = $statusList[0]["title"];
                $value["status_class"] = $statusList[0]["statusClass"];
                $total = (int) $value["surplus"];
                $sold = $listed = 0;
                if (in_array($value["status"], [4, 5])) {
                    $sold = (int) $value["sell_qty"];
                    $available = $total - $sold;
                } else {
                    $listed = (int) $value["sell_qty"];
                    $available = $total;
                }
                $value["surplus_total"] = $total;
                $value["sell_qty_sold"] = $sold;
                $value["surplus_available"] = $available;
                $value["listed"] = $listed;
                if ($total != $listed) {
                    // $result[] = $value;
                    $result[$value["prod_id"]][] = $value;
                }
            }
        }
        foreach ($result as $key => $value) {
            // "surplus_total": 5,
            // "sell_qty_sold": 0,
            // "surplus_available": 5,
            // "listed": 4
            $total = $sold = $listed = $available = 0;
            $new_val = [];
            foreach ($value as $val) {
                $new_val["surplus_total"] += $val["surplus_total"];
                $new_val["sell_qty_sold"] += $val["sell_qty_sold"];
                $new_val["surplus_available"] += $val["surplus_available"];
                $new_val["listed"] += $val["listed"];
                $new_val["prod_id"] = $val["prod_id"];
            }
            $data[] = $new_val;
        }
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
    public function self_sold_post()
    {
        $product_id = $this->input->post("product_id");
        $current_date = date("Y-m-d H:i:s");
        $reason = "Self Out Of System";
        $trade_product = $this->Masters_model->get_data("*", "trade_product", [
            "id" => $product_id,
            "is_deleted" => "false",
            "is_active" => "true",
        ]);
        $trade_product_result = [];
        if (!empty($trade_product)) {
            if ($trade_product[0]["status"] == 3) {
                // update status of trade_product by sold
                $update_data = [];
                $update_data = [
                    "status" => 7,
                    "reason" => $reason,
                    "updated_by_id" => $trade_product[0]["user_id"],
                    "updated_on" => $current_date,
                ];
                $this->db->where("id", $product_id);
                $trade_product_result = $this->db->update(
                    "trade_product",
                    $update_data
                );
                // Start: Trade activity logs
                $trade_data = [
                    "title" => "Seller Action",
                    "description" =>
                        "Seller has taken action on product: " . $reason,
                    "userid" => $trade_product[0]["user_id"],
                    "user_type" => "Seller",
                    "trade_product_id" => $product_id,
                    "trade_product_status" => 4,
                    "user_action" => "Self Sold",
                    "reason" => $reason,
                ];
                trade_activity_logs($trade_data);
            } else {
                $trade_product_result = [];
            }
        }
        // End: Trade activity logs
        if (!empty($trade_product_result)) {
            $response = [
                "success" => 1,
                "data" => $trade_data,
                "message" => "Status updated successfully!",
            ];
        } else {
            $response = [
                "success" => 0,
                "data" => [],
                "message" => "Status not updated!",
            ];
        }
        $this->api_response($response);
        exit();
    }
    //***********************************************************************
    // Saller module API: END //////////////////////////
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
