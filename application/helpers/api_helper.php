<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/* function call API by using all methods
*  $method POST,GET,PUT,DELETE
*  $url url
*  $data post data
* reffer :https://weichie.com/blog/curl-api-calls-with-php/
*/
function callAPI($url, $method = 'GET', $data = false)
{
     print_r($data);
     exit;

    $CI     = &get_instance();
    $curl   = curl_init();

    switch ($method) {
        case "POST":
        curl_setopt($curl, CURLOPT_POST, 1);
        if ($data)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        break;
        case "PUT":
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        if ($data)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        break;
        default:
        if ($data)
            $url = (strpos($url, "?") ? $url . "&" : $url . "?") . http_build_query($data);
    }



    // OPTIONS:
    curl_setopt($curl, CURLOPT_URL, $url);

    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "authorization: Basic " . $CI->config->item('curl_api_key'),
        //base64_encode(usename:password)
        "cache-control: no-cache"
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    // EXECUTE:
    $result      = curl_exec($curl);
    $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err         = curl_error($curl);

    /*$log_data = str_repeat('=', 40);
    $log_data .= date('Y-m-d H:i:s');
    $log_data .= str_repeat('=', 40) . "\n\n";
    $log_data .= '#Request: ' . json_encode($data) . "\n\n";
    $log_data .= '#Response: ' . $result . "\n\n";
    error_log_file($log_data);*/
    if ($err) {
    } else {
        if ($http_status >= 200 && $http_status < 300) {

            return (is_array($result) ? $result : json_decode($result));
            curl_close($curl);
        }
    }
    if (!$result) {
        die("Connection Failure");
    }
}

