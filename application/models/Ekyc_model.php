<?php  if (!defined('BASEPATH')) {

    exit('No direct script access allowed');

}

class Ekyc_model extends MY_Model

{

    public function __construct()

    {

        parent::__construct();
        $headers_data = $this->input->request_headers();
        $headers_data['lang'] = isset($headers_data['lang']) ? $headers_data['lang']:'en';
        
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
    }

    public function is_ekyc_enable()

    {

        $sql = "SELECT id,key_fields,description from config_master WHERE  is_active = true  AND key_fields like '%ekyc%' AND  is_deleted = false order by id asc";

        $rest = $this->db->query($sql);

        $ekyc_config_data = $rest->result_array();

        $newconfig = array();

        foreach($ekyc_config_data as $k=>$v){

            foreach($v as $k1=>$v1){

                if($k1=='key_fields')

                $newconfig[$v[$k1]]=$v['description'];

            }

        }

        return $newconfig;

    }

   /*  public function ekyc_aadhar_otp_generate($userid,$aadharno)

    {

        $api_url ='https://live.zoop.one/in/identity/okyc/otp/request';
        $header_arr = array(

            'Content-Type: application/json',

            'api-key:Y3DMTAV-V3N4YNT-J0D303D-TK58NVH',

            'app-id:6421326062edcd001df95955'

        );

        $post_arr = '{

            "data": {

                "customer_aadhaar_number": "'.$aadharno.'",

                "consent": "Y",

                "consent_text": "Approve the values here"

                }

            }';

            $res = $this->postCURL($api_url,$header_arr,$post_arr);

            $api_url_data = json_decode($res, true);
            $result_array = array();

            foreach($api_url_data as $key){

                if($api_url_data['result']['is_otp_sent']==1 && $api_url_data['result']['is_number_linked']==1 && $api_url_data['result']['is_aadhaar_valid']==1 && $api_url_data['success']==1){

                    $result_array['success'] = 1;

                    $result_array['error'] = 0;

                    $result_array['status'] = 1;

                    $result_array['data'] = $api_url_data;

                    $result_array['message'] = lang('OTP_Sent');//'OTP sent to mobile number';

                    $insert['request_id']         = $api_url_data['request_id'];

                    $where = array('farmer_id' => $userid, 'document_type' => 'Aadhaar');

                    $check_farmer = $this->get_data('id', 'farmer_documents', $where);

                    if($check_farmer[0]['id']==''){

                        $insertdata['farmer_id']         = $userid;

                        $insertdata['document_type']         = 'Aadhaar';

                        $insertdata['request_id']         = $api_url_data['request_id'];

                        $result = $this->add_data('farmer_documents', $where, $insertdata);

                    }else{

                        $result = $this->update_data('farmer_documents', $where, $insert);

                    } 



                }

                else{



                    $result_array['success'] = 0;

                    $result_array['error'] = 1;

                    $result_array['status'] = 0;

                    $result_array['data'] = $api_url_data;

                    $result_array['message'] = lang('Invalid_Aadhar_Number');//'Invalid aadhar number';

                }

                $mask_aadharno = str_repeat('X', strlen($aadharno) - 4) . substr($aadharno, -4);

                $post_arrnew = '{

                    "data": {

                        "customer_aadhaar_number": '.$mask_aadharno.',

                        "consent": "Y",

                        "consent_text": "Approve the values here"

                        }

                    }';

            }

            api_activity_logs('EKYC - Aadhar OTP generation',json_encode($post_arrnew),$res,'',$userid);

            return $result_array;

    } */
    public function ekyc_aadhar_otp_generate($userid, $aadharno) {
        $api_url = 'https://live.zoop.one/in/identity/okyc/otp/request';
        $header_arr = array(
            'Content-Type: application/json',
            'api-key:Y3DMTAV-V3N4YNT-J0D303D-TK58NVH',
            'app-id:6421326062edcd001df95955'
        );
    
        $post_arr = json_encode([
            'data' => [
                'customer_aadhaar_number' => $aadharno,
                'consent' => 'Y',
                'consent_text' => 'Approve the values here'
            ]
        ]);
    
        $result_array = array();
    
        try {
            // Call the postCURL method with the timeout settings
            $res = $this->postCURL($api_url, $header_arr, $post_arr);
            $api_url_data = json_decode($res, true);

            // echo'live.zoop.one::::';print_r($res); exit;
    
            if ($api_url_data['result']['is_otp_sent'] == 1 && $api_url_data['result']['is_number_linked'] == 1 && $api_url_data['result']['is_aadhaar_valid'] == 1 && $api_url_data['success'] == 1) {
                $result_array['success'] = 1;
                $result_array['error'] = 0;
                $result_array['status'] = 1;
                $result_array['data'] = $api_url_data;
                $result_array['message'] = lang('OTP_Sent'); //'OTP sent to mobile number';
    
                $insert['request_id'] = $api_url_data['request_id'];
                $where = array('farmer_id' => $userid, 'document_type' => 'Aadhaar');
                $check_farmer = $this->get_data('id', 'farmer_documents', $where);
    
                if (empty($check_farmer)) {
                    $insertdata = [
                        'farmer_id' => $userid,
                        'document_type' => 'Aadhaar',
                        'request_id' => $api_url_data['request_id']
                    ];
                    $result = $this->add_data('farmer_documents', $insertdata);
                } else {
                    $result = $this->update_data('farmer_documents', $where, $insert);
                }
            } else {
                $result_array['success'] = 0;
                $result_array['error'] = 1;
                $result_array['status'] = 0;
                $result_array['data'] = $api_url_data;
                $result_array['message'] = lang('Invalid_Aadhar_Number'); //'Invalid aadhar number';
            }
    
            $mask_aadharno = str_repeat('X', strlen($aadharno) - 4) . substr($aadharno, -4);
            $post_arrnew = json_encode([
                'data' => [
                    'customer_aadhaar_number' => $mask_aadharno,
                    'consent' => 'Y',
                    'consent_text' => 'Approve the values here'
                ]
            ]);
    
        } catch (Exception $e) {
            $result_array['success'] = 0;
            $result_array['error'] = 1;
            $result_array['status'] = 0;
            $result_array['message'] = 'An error occurred: ' . $e->getMessage();
        }
    
        api_activity_logs('EKYC - Aadhar OTP generation', json_encode($post_arrnew), $res ?? '', '', $userid);
        return $result_array;
    }
    

    public function ekyc_aadhar_verification($userid,$aadharno,$otp)

    {

        $select              = array('request_id');

        $where               = array('farmer_id' => $userid, 'document_type' => 'Aadhaar');

        $farmer_data = $this->get_data($select, 'farmer_documents', $where);

        $requestid = $farmer_data[0]['request_id'];

        $api_url_new ='https://live.zoop.one/in/identity/okyc/otp/verify';

        $header_arr = array(

            'Content-Type: application/json',

            'api-key:Y3DMTAV-V3N4YNT-J0D303D-TK58NVH',

            'app-id:6421326062edcd001df95955'

        );

        $post_arr_new = '{

            "data": {

                "customer_aadhaar_number": "'.$aadharno.'",

                "request_id":"'.$requestid.'",

                "otp": "'.$otp.'",

                "consent": "Y",

                "consent_text": "I hear by declare my consent agreement for fetching my information via ZOOP API."

              }

            }';//echo "<pre>POST===>";print_r($post_arr_new);

            $res1 = $this->postCURL($api_url_new,$header_arr,$post_arr_new);

            $api_url_data = json_decode($res1, true); //echo "<pre>RES===>";print_r($api_url_data);

            $result_array = array();

            $mask_aadharno = str_repeat('X', strlen($aadharno) - 4) . substr($aadharno, -4);

            foreach($api_url_data as $key){

                if($api_url_data['success']==1){

                    $result_array['success'] = 1;

                    $result_array['error'] = 0;

                    $result_array['status'] = 1;

                    $result_array['data'] = $api_url_data;

                    $result_array['message'] = lang('Aadhar_Verified_Successfully');//'Aadhar verified successfully';

                    $insert['is_verify']         = true;

                    $where = array('farmer_id' => $userid, 'document_type' => 'Aadhaar');

                    $check_farmer = $this->get_data('id', 'farmer_documents', $where);

                    if($check_farmer[0]['id']==''){

                        $insertdata['farmer_id']         = $userid;

                        $insertdata['document_type']         = 'Aadhaar';

                        $insertdata['is_verify']         = true;

                        $result = $this->add_data('farmer_documents', $where, $insertdata);

                    }else{

                        $result = $this->update_data('farmer_documents', $where, $insert);

                    } 

                    $cwhere = array('id' => $userid);

                    $check_farmer_jsondata = $this->get_data('other_json', 'client', $cwhere);

                    if($check_farmer_jsondata[0]['other_json']!=''){

                        $oldjsondata = json_decode($check_farmer_jsondata[0]['other_json'], true);

                    }

                    $clientdata['aadhar_no']         = $mask_aadharno;

                    $clientdata['aadhar_verified_name']         = $api_url_data['result']['user_full_name'];

                    $jsondata = array('Aadhaar' => str_replace(PHP_EOL, '', $api_url_data['result']));

                    if(!empty($oldjsondata)){

                        $mergeres = array_merge( $oldjsondata,$jsondata);

                        $clientdata['other_json']         =  json_encode($mergeres, TRUE);

                    }else{

                        $clientdata['other_json']         =  json_encode($jsondata, TRUE);

                    }

                    $client_result = $this->update_data('client', $cwhere, $clientdata);



                }else{



                    $result_array['success'] = 0;

                    $result_array['error'] = 1;

                    $result_array['status'] = 0;

                    $result_array['data'] = $api_url_data;

                    $result_array['message'] = lang('Invalid_Aadhar_Number');//'Invalid aadhar number';

                }

                $post_arrnew = '{

                    "data": {

                        "customer_aadhaar_number": "'.$mask_aadharno.'",

                        "request_id":"'.$requestid.'",

                        "otp": "'.$otp.'",

                        "consent": "Y",

                        "consent_text": "I hear by declare my consent agreement for fetching my information via ZOOP API."

                      }

                    }';

            }//echo "<pre>result_array===>";print_r($result_array);

            api_activity_logs('EKYC - Aadhar number verification',json_encode($post_arrnew,true),$res1,'',$userid);

            return $result_array;

    }

    public function ekyc_bank_verification($userid,$accno,$ifsc_code)

    {

        $api_url ='https://live.zoop.one/api/v1/in/financial/bav/lite';

        $header_arr = array(

            'Content-Type: application/json',

            'api-key:Y3DMTAV-V3N4YNT-J0D303D-TK58NVH',

            'app-id:6421326062edcd001df95955'

        );

        $post_arr = '{

                "data": {

                "account_number": "'.$accno.'",

                "ifsc": "'.$ifsc_code.'",

                "consent": "Y",

                "consent_text" : "Here i declare above information is correct!."

                }

            }';

            $res = $this->postCURL($api_url,$header_arr,$post_arr);

            $api_url_data = json_decode($res, true);

           

            $result_array = array();

            foreach($api_url_data as $key){

                if($api_url_data['result']['transaction_remark']=='Transaction Successful' && $api_url_data['result']['verification_status']=='VERIFIED' && $api_url_data['success']==1){

                    $result_array['success'] = 1;

                    $result_array['error'] = 0;

                    $result_array['status'] = 1;

                    $result_array['data'] = $api_url_data;

                    $result_array['message'] = lang('Bank_Verification_Successfully');

                    $insert['is_verify']         = true;

                    $where = array('farmer_id' => $userid, 'document_type' => 'Bank');

                    $check_farmer = $this->get_data('id', 'farmer_documents', $where);

                    if($check_farmer[0]['id']==''){

                        $insertdata['farmer_id']         = $userid;

                        $insertdata['document_type']         = 'Bank';

                        $insertdata['is_verify']         = true;

                        $result = $this->add_data('farmer_documents', $where, $insertdata);

                    }else{

                        $result = $this->update_data('farmer_documents', $where, $insert);

                    } 

                    $cwhere = array('id' => $userid);

                    $check_farmer_jsondata = $this->get_data('other_json', 'client', $cwhere);

                    if($check_farmer_jsondata[0]['other_json']!=''){

                        $oldjsondata = json_decode($check_farmer_jsondata[0]['other_json'], true);

                    }

                    $clientdata['ifsc_code']         = $ifsc_code;

                    $clientdata['acc_no']         =  $accno;

                    $jsondata = array('Bank' => str_replace(PHP_EOL, '', $api_url_data['result']));

                    if(!empty($oldjsondata)){

                        $mergeres = array_merge( $oldjsondata,$jsondata);

                        $clientdata['other_json']         =  json_encode($mergeres, TRUE);

                    }else{

                        $clientdata['other_json']         =  json_encode($jsondata, TRUE);

                    }

                   

                    $client_result = $this->update_data('client', $cwhere, $clientdata);



                }else{



                    $result_array['success'] = 0;

                    $result_array['error'] = 1;

                    $result_array['status'] = 0;

                    $result_array['data'] = $api_url_data;

                    $result_array['message'] = lang('Invalid_Bank_Details');

                }

            }

            api_activity_logs('EKYC - Bank verification',json_encode($post_arr,true),$res,'',$userid);

            return $result_array;

    }
    function compareStrings($string1, $string2, $threshold = 80) {
        // Calculate the similarity percentage
        similar_text($string1, $string2, $percent);
    
        // Check if the similarity percentage meets or exceeds the threshold
        return $percent >= $threshold;
    }
    public function ekyc_pan_verification($userid,$panno,$business_pan='false')

    {

        //$api_url = 'https://test.zoop.one/api/v1/in/identity/pan/lite'; //'https://live.zoop.one/api/v1/in/identity/pan/lite';

        $api_url = 'https://live.zoop.one/api/v1/in/identity/pan/lite';
        $header_arr = array(

            'Content-Type: application/json',

            // 'api-key:7N6YJ2E-YWE4DXR-H90NF8Y-3KXVPAF', //Y3DMTAV-V3N4YNT-J0D303D-TK58NVH',

            // 'app-id:61fd30fe93ae0f001e04ce8b' //6421326062edcd001df95955'
            
            
            'api-key:Y3DMTAV-V3N4YNT-J0D303D-TK58NVH',

            'app-id:6421326062edcd001df95955'

        );

        $post_arr = '{

                "data": {

                "customer_pan_number": "'.$panno.'",

                "consent": "Y",

                "consent_text" : "Here i declare above information is correct!."

                }

            }';

            $res = $this->postCURL($api_url,$header_arr,$post_arr);

            $api_url_data = json_decode($res, true);

           

            $result_array = array();

            foreach($api_url_data as $key){
                if($api_url_data['result']['pan_status']=='VALID' && $api_url_data['success']==1){
                    $doc_type = ($business_pan=='true')?'Businesspan':'Pan';
                    if($doc_type =='Businesspan'){
                        $dwhere = array('client_id' => $userid);
                        $ekyc_config_data = $this->is_ekyc_enable();    
                        if($ekyc_config_data['ekyc_business_name_match'] == 1){
                            $clientdetails = $this->get_data('business_name', 'client_details', $dwhere);
                            $business_name = $clientdetails[0]['business_name'];
                            $business_pan_name = $api_url_data['result']['user_full_name'];
                            $threshold = $ekyc_config_data['ekyc_business_name_percentage'];
                            if ($this->compareStrings($business_name, $business_pan_name, $threshold)) {
                                $result_array['success'] = 1;
                                $result_array['error'] = 0;
                                $result_array['status'] = 1;
                                $result_array['data'] = $api_url_data;
                                $result_array['message'] = lang('Pan_Verification_Successfully');//'Pan verification successfully';
                                $insert['is_verify']         = true;
                                $doc_type = ($business_pan=='true')?'Businesspan':'Pan';
                                $where = array('farmer_id' => $userid, 'document_type' => $doc_type);
                                $check_farmer = $this->get_data('id', 'farmer_documents', $where);
                                if($check_farmer[0]['id']==''){
                                    $insertdata['farmer_id']         = $userid;
                                    $insertdata['document_type']         = $doc_type;
                                    $insertdata['is_verify']         = true;
                                    $result = $this->add_data('farmer_documents', $where, $insertdata);
                                }else{
                                    $result = $this->update_data('farmer_documents', $where, $insert);
                                } 
                                $cwhere = array('id' => $userid);
                                $check_farmer_jsondata = $this->get_data('other_json', 'client', $cwhere);
                                if($check_farmer_jsondata[0]['other_json']!=''){
                                    $oldjsondata = json_decode($check_farmer_jsondata[0]['other_json'], true);
                                }
                                $clientdata['pan_no']         =  $panno;
                                $clientdata['pan_verified_name']         = $api_url_data['result']['user_full_name'];
                                $jsondata = array($doc_type => str_replace(PHP_EOL, '', $api_url_data['result']));
                                if(!empty($oldjsondata)){
                                    $mergeres = array_merge( $oldjsondata,$jsondata);
                                    $clientdata['other_json']         =  json_encode($mergeres, TRUE);
                                }else{
                                    $clientdata['other_json']         =  json_encode($jsondata, TRUE);
                                }
                            
                                $client_result = $this->update_data('client', $cwhere, $clientdata);
                                if($business_pan=='true'){
                                    $insertdata1['business_pan']         = $panno;
                                    $clientdetails_result = $this->update_data('client_details', $cwhere, $insertdata1);
                                }
                            } else {
                                $result_array['success'] = 0;
                                $result_array['error'] = 1;
                                $result_array['status'] = 0;
                                $api_url_data['result']['pan_status']='INVALID';
                                $api_url_data['success'] = false;
                                $result_array['data'] = $api_url_data;
                                $result_array['message'] = lang('Pan_Name_Missmatch');//'Invalid pan details';
                                api_activity_logs('EKYC - Pan verification',json_encode($post_arr,true),$res,'',$userid);
                                return $result_array;
                            }
                        }
                    }else{
                        $result_array['success'] = 1;
                        $result_array['error'] = 0;
                        $result_array['status'] = 1;
                        $result_array['data'] = $api_url_data;
                        $result_array['message'] = lang('Pan_Verification_Successfully');//'Pan verification successfully';
                        $insert['is_verify']         = true;
                        $where = array('farmer_id' => $userid, 'document_type' => $doc_type);
                        $check_farmer = $this->get_data('id', 'farmer_documents', $where);
                        if($check_farmer[0]['id']==''){
                            $insertdata['farmer_id']         = $userid;
                            $insertdata['document_type']         = $doc_type;
                            $insertdata['is_verify']         = true;
                            $result = $this->add_data('farmer_documents', $where, $insertdata);
                        }else{
                            $result = $this->update_data('farmer_documents', $where, $insert);
                        } 
                        $cwhere = array('id' => $userid);
                        $check_farmer_jsondata = $this->get_data('other_json', 'client', $cwhere);
                        if($check_farmer_jsondata[0]['other_json']!=''){
                            $oldjsondata = json_decode($check_farmer_jsondata[0]['other_json'], true);
                        }
                        $clientdata['pan_no']         =  $panno;
                        $clientdata['pan_verified_name']         = $api_url_data['result']['user_full_name'];
                        $jsondata = array($doc_type => str_replace(PHP_EOL, '', $api_url_data['result']));
                        if(!empty($oldjsondata)){
                            $mergeres = array_merge( $oldjsondata,$jsondata);
                            $clientdata['other_json']         =  json_encode($mergeres, TRUE);
                        }else{
                            $clientdata['other_json']         =  json_encode($jsondata, TRUE);
                        }
                    
                        $client_result = $this->update_data('client', $cwhere, $clientdata);
                        if($business_pan=='true'){
                            $insertdata1['business_pan']         = $panno;
                            $clientdetails_result = $this->update_data('client_details', $cwhere, $insertdata1);
                        }
                    }
                }else{



                    $result_array['success'] = 0;

                    $result_array['error'] = 1;

                    $result_array['status'] = 0;

                    $result_array['data'] = $api_url_data;

                    $result_array['message'] = lang('Invalid_Pan');//'Invalid pan details';

                }

            }

            api_activity_logs('EKYC - Pan verification',json_encode($post_arr,true),$res,'',$userid);

            return $result_array;

    }

    public function postCURL($_url,$header,$postdata){

            $curl = curl_init();

            curl_setopt_array($curl, array(

            CURLOPT_URL => $_url,

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_ENCODING => '',

            CURLOPT_MAXREDIRS => 10,

            CURLOPT_FOLLOWLOCATION => true,

            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

            CURLOPT_CUSTOMREQUEST => 'POST',

            CURLOPT_POSTFIELDS =>$postdata,

            CURLOPT_HTTPHEADER => $header,

            ));

            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                throw new Exception('cURL Error: ' . curl_error($curl));
            }

            curl_close($curl);

            return $response;

        }

        public function get_ekyc_verification_status($userid)

        {

            $ekyc_status = array();

            $ekyc_config_data = $this->is_ekyc_enable();

            $ekyc_status = $ekyc_config_data;

            $ekyc_status['Aadhaar'] = $ekyc_status['Bank'] = $ekyc_status['Pan'] = $ekyc_status['Businesspan'] = NULL;

            if($ekyc_config_data['ekyc']==1){

                if($ekyc_config_data['ekyc_aadhar_verify']==1){

                    $where = array('farmer_id' => $userid, 'document_type' => 'Aadhaar');

                    $get_status = $this->get_data('is_verify', 'farmer_documents', $where);

                    $ekyc_status['aadhaar_verify_sataus'] = ($get_status[0]['is_verify']== 't')?"1":"0";

                    $dwhere = array('id' => $userid);

                    $get_aadhar_data = $this->get_data("other_json->'Aadhaar' as aadhaar_data", 'client', $dwhere);

                    if($get_aadhar_data[0]['aadhaar_data']!=''){

                        $aadhaar_data = json_decode($get_aadhar_data[0]['aadhaar_data'],true);

                        $ekyc_status['Aadhaar']['user_aadhaar_number'] = $aadhaar_data['user_aadhaar_number'];

                        $ekyc_status['Aadhaar']['user_aadhaar_name'] = $aadhaar_data['user_full_name'];

                    }

                    else{

                        $ekyc_status['Aadhaar'] = NULL;

                    }

                }

                if($ekyc_config_data['ekyc_pan_verify']==1){

                    $where = array('farmer_id' => $userid, 'document_type' => 'Pan');

                    $get_status = $this->get_data('is_verify', 'farmer_documents', $where);

                    $ekyc_status['pan_verify_status'] = ($get_status[0]['is_verify']== 't')?"1":"0";

                    $dwhere = array('id' => $userid);

                    $get_pan_data = $this->get_data("app_user_type,other_json->'Pan' as pan_data", 'client', $dwhere);

                    if($get_pan_data[0]['pan_data']!=''){

                        $pan_data = json_decode($get_pan_data[0]['pan_data'],true);

                        $ekyc_status['Pan']['pan_number'] = $pan_data['pan_number'];

                        $ekyc_status['Pan']['user_full_name'] = $pan_data['user_full_name'];

                        $ekyc_status['app_user_type'] = $get_pan_data[0]['app_user_type'];

                    }

                    else{

                        $ekyc_status['Pan'] = NULL;

                        $ekyc_status['app_user_type'] = $get_pan_data[0]['app_user_type'];

                    }



                    // $where = array('farmer_id' => $userid, 'document_type' => 'Businesspan');

                    // $get_status = $this->get_data('is_verify', 'farmer_documents', $where);

                    // $ekyc_status['business_pan_verify_status'] = ($get_status[0]['is_verify']== 't')?"1":"0";

                    // $dwhere = array('id' => $userid);

                    // $get_pan_data = $this->get_data("app_user_type,other_json->'Businesspan' as Businesspan_data", 'client', $dwhere);

                    // if($get_pan_data[0]['Businesspan_data']!=''){

                    //     $pan_data = json_decode($get_pan_data[0]['Businesspan_data'],true);

                    //     $ekyc_status['Businesspan']['pan_number'] = $pan_data['pan_number'];

                    //     $ekyc_status['Businesspan']['user_full_name'] = $pan_data['user_full_name'];

                    //     $ekyc_status['app_user_type'] = $get_pan_data[0]['app_user_type'];

                    // }

                    // else{

                    //     $ekyc_status['Businesspan'] = NULL;

                    //     $ekyc_status['app_user_type'] = $get_pan_data[0]['app_user_type'];

                    // }

                }
                if($ekyc_config_data['ekyc_business_pan_verify']==1){
                    $where = array('farmer_id' => $userid, 'document_type' => 'Businesspan');
                    $get_status = $this->get_data('is_verify', 'farmer_documents', $where);
                    $ekyc_status['business_pan_verify_status'] = ($get_status[0]['is_verify']== 't')?"1":"0";
                    $dwhere = array('id' => $userid);
                    $get_pan_data = $this->get_data("app_user_type,other_json->'Businesspan' as Businesspan_data", 'client', $dwhere);
                   
                    if($get_pan_data[0]['businesspan_data']!=''){
                        $pan_data = json_decode($get_pan_data[0]['businesspan_data'],true);
                        $ekyc_status['Businesspan']['pan_number'] = $pan_data['pan_number'];
                        $ekyc_status['Businesspan']['user_full_name'] = $pan_data['user_full_name'];
                        $ekyc_status['app_user_type'] = $get_pan_data[0]['app_user_type'];
                    }
                    else{
                        $ekyc_status['Businesspan'] = NULL;
                        $ekyc_status['app_user_type'] = $get_pan_data[0]['app_user_type'];
                    }

                }
                if($ekyc_config_data['ekyc_bank_verify']==1){

                    $where = array('farmer_id' => $userid, 'document_type' => 'Bank');

                    $get_status = $this->get_data('is_verify', 'farmer_documents', $where);

                    $ekyc_status['bank_verify_status'] = ($get_status[0]['is_verify']== 't')?"1":"0";

                    $dwhere = array('id' => $userid);

                    $dselect           = array('acc_no','ifsc_code');

                    $get_bank_data = $this->get_data($dselect, 'client', $dwhere);

                    if($get_bank_data[0]!=''){

                        $ekyc_status['Bank']['user_account_number'] = $get_bank_data[0]['acc_no'];

                        $ekyc_status['Bank']['ifsc_code'] = $get_bank_data[0]['ifsc_code'];

                        $url     = 'https://ifsc.razorpay.com/'.$get_bank_data[0]['ifsc_code'];

                        $curl            = curl_init($url);

                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                        curl_setopt($curl, CURLOPT_POST, false);

                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

                        $curl_response   = curl_exec($curl);

                        curl_close($curl);

                        $ifscdata    = json_decode($curl_response,true);

                        if($ifscdata!=''){

                            $ekyc_status['Bank']['bank'] = $ifscdata['BANK'];

                            $ekyc_status['Bank']['branch_name'] = $ifscdata['BRANCH'];

                        }

                        else{

                            $ekyc_status['Bank']['bank'] = '';

                            $ekyc_status['Bank']['branch_name'] = '';

                        }

                    }

                    else{

                        $ekyc_status['Bank'] = NULL;

                    }

                }



            }

           return $ekyc_status;

        }



}





?>