<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
	Class Name 		: 	Net carrot Helper	
	Created By		: 	Akash Wagh
	Created Date 	: 	06-04-2023	
*/

function generateMemberPrimaryId($domain_name=''){
    if($domain_name == ''){
        $domain_name = 'FAMRUT';
    }
    
    $MemberPrimaryId    = strtoupper($domain_name).time();
    return $MemberPrimaryId;
}

function get_nc_api_settings()
{
    // $nc_api_settings = config_setting('nc_api_settings');
    // if(!empty($nc_api_settings)){
    //     $api_settings = json_decode($nc_api_settings['description'], true);
    //     if($api_settings['demo']['status']){
    //         // demo API
    //         $data['url']        = $api_settings['demo']['url'];
    //         $data['credentials']= $api_settings['demo']['credentials'];
    //     } else {
    //         // live API
    //         $data['url']        = $api_settings['demo']['url'];
    //         $data['credentials']= $api_settings['demo']['credentials'];
    //     }

    //     return $data;
    // }

    $data['url']        = NETCARROTS.'API/AllService.svc/';
    $data['credentials']= array('UserId'=>'famrut', 'Password' => 'famrut@123#');
    return $data;
}

function GenerateTokenAPI()
{
    $nc_api_settings = get_nc_api_settings();
    if(!empty($nc_api_settings)){
        $api_settings = $nc_api_settings;
        $url        = $api_settings['url'];
        $credentials= $api_settings['credentials'];

        
        $data['url']        = $url.'GenerateTokenAPI';
        $data['method']     = 'POST';
        $data['postdata']   = $credentials;
        $data['headers']    = array('Content-Type: application/json');

        $response   = cURL($data);
        $res        = json_decode($response, true);
        $token = false;
        if($res['GenerateTokenAPI']['ErrorCode'] == 0){
            $token = $res['GenerateTokenAPI']['Response'][0]['Token'];
        }
        return $token;
    }
    return false;
}

function AddMemberEnrolmentAPI($data=[])
{
    $nc_api_settings    = get_nc_api_settings();
    $token  = GenerateTokenAPI();
    if(!empty($nc_api_settings) && !empty($data) && $token){
        $api_settings = $nc_api_settings;
        $url        = $api_settings['url'];
        
        $data['url']        = $url.'AddMemberEnrolmentAPI';
        $data['method']     = 'POST';
        $data['postdata']   = $data;
        $data['headers']    = array(
                                'Authorization: Bearer '.$token,
                                'Content-Type: application/json'
                            );
        $response   = cURL($data);
        $res        = json_decode($response, true);
        $result = false;
        if($res['AddMemberEnrolmentAPI']['ErrorCode'] == 0){
            return true;
        }
        return $result;
    }
    return false;
}

function TransactionAPI($data=[])
{
    $nc_api_settings    = get_nc_api_settings();
    $token  = GenerateTokenAPI();
    if(!empty($nc_api_settings) && !empty($data) && $token){
        $api_settings = $nc_api_settings;
        $url        = $api_settings['url'];
        
        $data['url']        = $url.'TransactionAPI';
        $data['method']     = 'POST';
        $data['postdata']   = $data;
        $data['headers']    = array(
                                'Authorization: Bearer '.$token,
                                'Content-Type: application/json'
                            );
        $response   = cURL($data);
        $res        = json_decode($response, true);
        $result = false;
        if($res['TransactionAPI']['ErrorCode'] == '0'){
            return $res['TransactionAPI']['Response'];
        }
        return $result;
    }
    return false;
}

function cURL($data=[])
{
    if(!empty($data)){
        $url        = $data['url'];
        $method     = $data['method'];
        $postdata   = json_encode($data['postdata']);
        $headers    = $data['headers'];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $postdata,
            CURLOPT_HTTPHEADER => $headers,
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);

        if ($err) {
            // echo "cURL Error #:" . $err;
            return false;
        } else {
            return $response;
        }
    }

    return false;
}