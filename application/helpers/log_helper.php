<?php

/**
 * function for use to store admin activity logs.
 * Create Deepak kanmahale
 * @param       $title string | $description string
 * @return      boolean
 */
function admin_activity_logs($title, $description)
{
    $CI = &get_instance();
    if (empty($title) || empty($description)) {
        return false;
    }
    $insert = array(
        'datetime'      => date('Y-m-d H:i:s'),
        'title'         => $title,
        'description'   => $description,
        'userid'        => $CI->session->userdata('user_id'),
        'ip'            => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $CI->input->ip_address(),
        'user_agent'    => $_SERVER['HTTP_USER_AGENT']
    );
    $result = $CI->db->insert('admin_activity_logs', $insert);
    return $result;
}

/**
 * function for use to store admin activity logs.
 * Create Deepak kanmahale
 * @param       $title string | $description string
 * @return      boolean
 */
function referer_activity_logs($product_id,$order_id,$paid,$action = 'add')
{
    $CI = &get_instance();
    $tracking_id    = $CI->session->userdata('tracking_id') ? $CI->session->userdata('tracking_id') : NULL;
    $user_id        = $CI->session->userdata('user_id') ? $CI->session->userdata('user_id') : NULL;
    
    if ($action == 'add') {
    
        
        $referer = NULL;
        if(isset($_SERVER['HTTP_REFERER'])) {
            $http_referer = $_SERVER['HTTP_REFERER'];
            $check_string = "aacovidtest";

            if(strpos($http_referer, $check_string) !== false){
                $referer = $http_referer;
            }
       }

        $CI->db->select('id');
        $CI->db->where('tracking_id',$tracking_id);
        $reffer = $CI->db->get('referer_activity_logs');

        if ($reffer->num_rows() > 1) {
           return false;
        }
        
        $insert = array(
            'tracking_id'=> $tracking_id,
            'product_id' => $product_id,
            'referer'    => $referer,
            'user_id'    => $user_id,
            'ip'         => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $CI->input->ip_address(),
            'datetime'   => date('Y-m-d H:i:s'),

        );
        $CI->db->insert('referer_activity_logs', $insert);
    }
    if ($action == 'update') {
        $conditions = array('tracking_id'=> $tracking_id);
        $insert     = array(
            'user_id'    => $user_id,
            'order_id'   => !empty($order_id) ? $order_id : NULL,
            'paid'       => !empty($paid) ? $paid :NULL
        );
        $CI->db->where($conditions);
        $CI->db->update('referer_activity_logs', $insert);

    }
}

/**
 * function for use to store email activity logs.
 * Create Aayusha kapadni
 * @param       $title string | $description string
 * @param       $description string | $description string
 * @param       $to_mail string | $to_mail string
 * @param       $userid string | $userid string
 * @param       $user_type string | $user_type string
 * @return      boolean
 */
function email_activity_logs($title, $description,$to_mail,$userid=null,$user_type=NULL,$cc_mail=NULL)
{
    $CI = &get_instance();
    if (empty($title) || empty($description)) {
        return false;
    }
    if (is_array($to_mail)) {
       $to_mail = json_encode($to_mail);
    }
    if (is_array($cc_mail)) {
       $cc_mail = json_encode($cc_mail);
    }
    $insert = array(
        'datetime'      => date('Y-m-d H:i:s'),
        'title'         => $title,
        'description'   => $description,
        'userid'        => $userid ,
        'user_type'     => $user_type ,
        'to'            => $to_mail ,
        'cc'            => $cc_mail ,

        'ip'            => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $CI->input->ip_address(),
        'user_agent'    => $_SERVER['HTTP_USER_AGENT']
    );
    $result = $CI->db->insert('email_activity_logs', $insert);
    return $result;
}

/**
 * function for use to store sms activity logs.
 * Create rahul badhe
 * @param       $title string | $description string
 * @return      boolean
 */
function sms_activity_logs($title, $description,$phone_no,$userid=null,$user_type=null)
{
    $CI = &get_instance();
    if (empty($title) || empty($description)) {
        return false;
    }
    $insert = array(
        'datetime'      => date('Y-m-d H:i:s'),
        'title'         => $title,
        'description'   => $description,
        'userid'        => $userid,
        'to'        => $phone_no,
        'user_type'        => $user_type,
        'ip'            => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $CI->input->ip_address(),
        'user_agent'    => $_SERVER['HTTP_USER_AGENT']
    );
    $result = $CI->db->insert('sms_activity_logs', $insert);
    return $result;
}

/**
 * function for use to login activity logs.
 * @param       $title string | $description string
 * @return      boolean
 */
function login_activity_logs($title, $description)
{
    $CI = &get_instance();

    $insert = array(

        'date'          => date('Y-m-d H:i:s'),
        'title'         => $title,
        'description'   => $description,
        'user_id'       => $CI->session->userdata('user_id'),
        'user_type'     => $CI->session->userdata('user_type'),
        'ip'            => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $CI->input->ip_address(),
        'user_agent'    => $_SERVER['HTTP_USER_AGENT']
    );
    return $CI->db->insert('login_activity_logs', $insert);
}

/**
 * function for use to store user activity logs.
 * Create Deepak kanmahale
 * @param       $title string | $description string
 * @return      boolean
 */
function user_activity_logs($title, $description)
{
    $CI = &get_instance();
    if (empty($title) || empty($description)) {
        return false;
    }

    $insert = array(
        'datetime'      => date('Y-m-d H:i:s'),
        'title'         => $title,
        'description'   => $description,
        'userid'        => $CI->session->userdata('user_id'),
        'ip'            => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $CI->input->ip_address(),
        'user_type'     => $CI->session->userdata('user_type'),
        'user_agent'    => $_SERVER['HTTP_USER_AGENT']
    );
    $result = $CI->db->insert('user_activity_logs', $insert);
    return $result;
}
/**
 * function for use to store CRONS logs.
 * Create Deepak kanmahale
 * @param       $title string | $description string
 * @return      boolean
 */
function vendor_activity_logs($title, $description)
{
    $CI = &get_instance();
    if (empty($title) || empty($description)) {
        return false;
    }

    $insert = array(
        'datetime'      => date('Y-m-d H:i:s'),
        'title'         => $title,
        'description'   => $description,
        'vendorid'        => $CI->session->userdata('vendor_id'),
        'ip'            => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $CI->input->ip_address(),
        'vendor_type'     => $CI->session->userdata('vendor_type'),
        'vendor_agent'    => $_SERVER['HTTP_USER_AGENT']
    );
    $result = $CI->db->insert('vendor_activity_logs', $insert);
    return $result;
}
/**
 * function for use to store CRONS logs.
 * Create Deepak kanmahale
 * @param       $title string | $description string
 * @return      boolean
 */
function bank_activity_logs($title, $description)
{
    $CI = &get_instance();
    if (empty($title) || empty($description)) {
        return false;
    }

    $insert = array(
        'datetime'      => date('Y-m-d H:i:s'),
        'title'         => $title,
        'description'   => $description,
        'bankid'        => $CI->session->userdata('bank_id'),
        'ip'            => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $CI->input->ip_address(),
        'bank_type'     => $CI->session->userdata('bank_type'),
        'bank_agent'    => $_SERVER['HTTP_USER_AGENT']
    );
    $result = $CI->db->insert('bank_activity_logs', $insert);
    return $result;
}
/**
 * function for use to store CRONS logs.
 * Create Deepak kanmahale
 * @param       $title string | $description string
 * @return      boolean
 */
function crons_activity_logs($title, $request_data, $response)
{
    $CI = &get_instance();

    $insert = array(
        'datetime'      => date('Y-m-d H:i:s'),
        'title'         => $title,
        'response'      => $response,
        'data'          => $request_data
    );
    return $CI->db->insert('crons_activity_logs', $insert);
}
/**
 * function for use to store API logs.
 * Create Deepak kanmahale
 * @param       $title string | $description string
 * @return      boolean
 */
function api_activity_logs($title, $request, $response, $product_data,$userid='')
{
    $CI = &get_instance();

    $insert = array(
        'datetime'      => date('Y-m-d H:i:s'),
        'title'         => $title,
        'product_data'  => $product_data,
        'response'      => $response,
        'request'       => $request,
        'userid'        => ($userid!='')?$userid:$CI->session->userdata('user_id'),
        'ip'            => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $CI->input->ip_address(),
        'user_agent'    => $_SERVER['HTTP_USER_AGENT']
    );
    $result = $CI->db->insert('api_activity_logs', $insert);
    return $result;
}


/**
 * function for use to store API logs.
 * Create Deepak kanmahale
 * @param       $title string | $description string
 * @return      boolean
 */
function covid_portal_api_logs($title, $request, $response)
{
    $CI = &get_instance();

    $insert = array(
        'datetime'      => date('Y-m-d H:i:s'),
        'title'         => $title,
        'response'      => $response,
        'request'       => $request,
        'ip'            => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $CI->input->ip_address(),
    );
    $result = $CI->db->insert('covid_portal_api_logs', $insert);
    return $result;
}

/**
 * function for use to payment log.
 * Create Deepak kanmahale
 * @param       $insert_data array
 * @return      boolean
 */
if (!function_exists('payment_log')) {
    function payment_log($insert_data)
    {
        $CI = &get_instance();
        if (is_array($insert_data)) {
            $result = $CI->db->insert('payment_log', $insert_data);
            return $result;
        }
        return false;
    }
}

/**
 * function for use to error_log_file manage error log.
 * Create Rahul badhe
 * @param       $data  array
 * @param       $mod  string
 * @return      null
 */
function error_log_file($data, $mod = 'a')
{
    $CI = &get_instance();
    $CI->load->helper('file');
    if (!write_file('error_log.txt', $data . "\n", $mod)) {
        log_message('error', 'Unable to write the file---error_log.txt');
    }
}

/**
 * function for use to store API logs.
 * Create Deepak kanmahale
 * @param       $title string | $description string
 * @return      boolean
 */
function mobile_app_api_logs($title,$response,$request,$request_headers=null,$action='add',$id=null)
{
    $CI = &get_instance();
    $header_array = array( 'request_headers'=> $request_headers, 'SERVER' => $_SERVER  );
    if ($action == 'add') {
        $insert = array(
                'title'             => $title,
                'request_datetime'  => date('Y-m-d H:i:s'),
                'request_headers'   => json_encode($header_array),
                'request'           => $request
            );
        $CI->db->insert('mobile_app_api_logs', $insert);
        return $CI->db->insert_id();
    }

    if ($action == 'update') {
        $update = array(
                'response'          => $response,
                'response_datetime' => date('Y-m-d H:i:s')
            );
        $CI->db->where( array('id' => $id ) );
        $CI->db->update('mobile_app_api_logs', $update);
        return true;
    }

}

/**
 * function for use to store stock activity logs.
 * Create Akash W.
 * @param       $title string | $description string
 * @return      boolean
 */
function stock_activity_logs($stock_data)
{
    $CI = &get_instance();
    if (empty($stock_data)) {
        return false;
    }

    $insert = array(
        'datetime'      => date('Y-m-d H:i:s'),
        'title'         => $stock_data['title'],
        'description'   => $stock_data['description'],
        'userid'        => $CI->session->userdata('user_id'),
        'ip'            => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $CI->input->ip_address(),
        'user_type'     => $CI->session->userdata('user_type'),
        'user_agent'    => $_SERVER['HTTP_USER_AGENT'],
        'product_id'    => $stock_data['product_id'],
        'available_stock'=> $stock_data['available_stock'],
        'status'        => $stock_data['status'],
        'qty'           => $stock_data['qty'],
        'reason'        => $stock_data['reason'],
    );

    $result = $CI->db->insert('stock_activity_logs', $insert);
    return $result;
}

/**
 * function for use to store price activity logs.
 * Create Akash W.
 * @param       $title string | $description string
 * @return      boolean
 */
function price_activity_logs($price_data)
{
    $CI = &get_instance();
    if (empty($price_data)) {
        return false;
    }

    $insert = array(
        'datetime'      => date('Y-m-d H:i:s'),
        'title'         => $price_data['title'],
        'description'   => $price_data['description'],
        'userid'        => $CI->session->userdata('user_id'),
        'ip'            => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $CI->input->ip_address(),
        'user_type'     => $CI->session->userdata('user_type'),
        'user_agent'    => $_SERVER['HTTP_USER_AGENT'],
        'product_id'    => $price_data['product_id'],
        'old_price'     => $price_data['old_price'],
        'new_price'     => $price_data['new_price'],
        'reason'        => $price_data['reason'],
    );
    $result = $CI->db->insert('price_activity_logs', $insert);
    return $result;
}

/**
 * function for use to buyer and seller trade activity logs.
 * Create Akash W.
 * @param       $title string | $description string
 * @return      boolean
 */
function trade_activity_logs($trade_data)
{
    $CI = &get_instance();
    if (empty($trade_data)) {
        return false;
    }

    $insert = array(
        'datetime'      => date('Y-m-d H:i:s'),
        'title'         => $trade_data['title'],
        'description'   => $trade_data['description'],
        'userid'        => $trade_data['user_id'],
        'ip'            => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $CI->input->ip_address(),
        'user_type'     => $trade_data['user_type'],
        'user_agent'    => $_SERVER['HTTP_USER_AGENT'],
        'trade_product_id'=> $trade_data['trade_product_id'],
		'trade_product_status'=> $trade_data['trade_product_status'],
		'user_action'	=> $trade_data['user_action'],
        'reason'        => $trade_data['reason'],
    );
    $result = $CI->db->insert('trade_product_activity_logs', $insert);
    return $result;
}
