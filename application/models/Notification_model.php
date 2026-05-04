<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
	Class Name 		: 	Email model
	Created By		: 	aayusha k
	Created Date 	: 	25-06-2019

*/
class Notification_model extends MY_Model
{
    function sendPushNotifications_All($token = array(), $title, $message,$is_whitelable,$group_ids = 0,$custom_array ,$type='blog',$farmer_id = ''){

 		if(1){

            // print_r($device_id_data);exit;

 			$whr_chk = '';
 			$whr_chk_farmer = '';
           // $device_id_data	= $token;
	    	
			if($is_whitelable == 1){
				$whr_chk = '';	 //$group_ids
			}

			if($farmer_id){
				$whr_chk_farmer = ' AND id = '.$farmer_id;
			}

			/*if($device_id_data){
				$token = $device_id_data;
			}*/

			$qry 		     = "SELECT device_id FROM client WHERE is_deleted='false' AND is_active='true' AND device_id is NOT NULL ".$whr_chk_farmer;
			$res_data        = $this->db->query($qry);
            $device_id_data  = $res_data->result_array();
            if(count($device_id_data)){
                $token = array();

                foreach ($device_id_data as $value) {
                    $token[] = $value['device_id'];
                }
            }
        }
		//
		//print_r($token);
		//exit;
        //print_r($token);
       // $tokens = array();
       // $tokens[] ='dp_fnrPOSqE:APA91bH5m9RU98vWDVYE1eHrz8BKuSsNCD0N0TItPqETE8yMZbwwogXb1kO5XmkX96VitS3z0Wle42jXD2zDVW0ICqArgE9H8DBBY15x40palirYZUO6cnGhk2AktG_phA18P-GTjmX_';
       /*   $token[] = "eIbQgRZ07Ig:APA91bFoQSA4ySchPUycYNANy3yRvf3ykuyAVASeVU-dE8_TV7HHSloHsGa8_XPo4rMn7bH9X4y8CgsvVHwA1d8AWLYreLxE2P_s_L94jmU9l6qHrLnkxq-Lb1fxkqlibQXQTijppZU2";
        $token[] = "d7W1233yGqc:APA91bGpuHCZVM0BzhfBSobnyV3rnjZ3EMr6gBtsmla1j-TZC4nIcVPKZxbZAKnsKF4jmNmffcr2Me1LwitgW8JSFoPMGSHHgNN3wTpG-iy-u355bGcxUDLKaYZCdG9NGy-rLR_Zl3u_";
        $toekn[] = "e00s4kIBEdU:APA91bGoJfeNdw3Rd2EZHHwaUsZS0NkiXr-wp7QjTyUsGyMc1cEWiitOJpkfFugKPbdUTYVwgX7TkVsWG0KdN_sFHBM5VPhO6ZzBYKvLXUQ7TP2qbORXOXpve5KSz8qShLJe53AX6IpL"; */

        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';
        $fields = array(
            'registration_ids' => $tokens,
            'priority'         => 9,
            'data'     => array("title" => $title, "body" => $message, "sound" => 'Default', 'image' => 'Notification Image', 'type' => $type),
           

        );

       // $API_SERVER_KEY = 'AAAAmhCfp8k:APA91bHXSHb1Ihie4m3y0v_0e1tAf7JCPMnubM751PMMVkC9oAi54O1AUGipDZ6ZZGCD9ajyxAFLsxjQ0cuLOqSLGjKp9Z0vBttKkIFeX7_xrZnbrAamAex-HMYK3z4SEz2_mHdwWYdu';

        //New APP key for Agroecosystem
        // $API_SERVER_KEY = 'AAAAJRsb6G4:APA91bGwslta0Hk72hMKpXaof-PJlzLXnlepgG9r-db0vsMNdJOyXu86foR-acgN3rhFu7-MNmkkBeB20Q1B2DER10EzhvT8X-AdnwS0ksenSeTvMvhR94wQ0Qh9TpWgWWhO62pqVsuf';
        if(get_config_data('API_SERVER_KEY')){
            $API_SERVER_KEY = get_config_data('API_SERVER_KEY');
        } else {
            $API_SERVER_KEY = API_SERVER_KEY;
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
        $result       = curl_exec($ch);
        $current_date = date("Y-m-d h:i:s");

        if (curl_errno($ch)) {
            $is_send = 0;
        } else {
            $is_send = 1;
        }
        // Close connection
        curl_close($ch);
        return $is_send;

        // echo 'is_send'.$is_send;
        // return $result;
    }


    public function sendPushNotifications($token = array(), $title, $message,$is_whitelable,$group_ids = 0,$custom_array ,$type='blog',$blog_id = '',$img)
    {
        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';
        $arr_user = 1;
        // $type = 1;
        $partner_name = 'Farmer';
       // $token = array();

    /*    $title = 'Blogs';
        $message = 'Dear user you have a New Message test.';
*/
/*        $token = array('dp_fnrPOSqE:APA91bH5m9RU98vWDVYE1eHrz8BKuSsNCD0N0TItPqETE8yMZbwwogXb1kO5XmkX96VitS3z0Wle42jXD2zDVW0ICqArgE9H8DBBY15x40palirYZUO6cnGhk2AktG_phA18P-GTjmX_');

       $token[] = "dp_fnrPOSqE:APA91bH5m9RU98vWDVYE1eHrz8BKuSsNCD0N0TItPqETE8yMZbwwogXb1kO5XmkX96VitS3z0Wle42jXD2zDVW0ICqArgE9H8DBBY15x40palirYZUO6cnGhk2AktG_phA18P-GTjmX_";


        $token[]    ='dcNMWbLMr1c:APA91bGni1E09a6jOEc0IUNlyLA8bixfOjUczIYR26GDlbRLoP48JlMK5ueeLnIqXajh8eMNC97LOVbFzORIpIwUI2q1dnOAqi2cpKK_256hVmq3jplLyxVVqKlTwLUnZrLr53SxLnqE';

      $toekn[] = "e00s4kIBEdU:APA91bGoJfeNdw3Rd2EZHHwaUsZS0NkiXr-wp7QjTyUsGyMc1cEWiitOJpkfFugKPbdUTYVwgX7TkVsWG0KdN_sFHBM5VPhO6ZzBYKvLXUQ7TP2qbORXOXpve5KSz8qShLJe53AX6IpL"; */

        $fields = array(
            'registration_ids' => $token,
            'priority'         => 10,

           /* 'notification'  => array("title" => $title, "body" => $message, "sound" => 'Default', 'image' => 'Notification Image', 'admno' => $arr_user, 'type' => $type ,'partner_name' => $partner_name),*/

          /*  'notification'     => array('title' => $title, 'body' => $message, 'sound' => 'Default', 'image' => 'Notification Image', 'admno' => $arr_user, 'type' => $type),*/
        
           'data'  => array("title" => 'Blog', "body" => $title, "sound" => 'Default', 'image' => $img, 'admno' => $arr_user, 'type' => $type , 'route'=>$type , 'id' => $blog_id),
        );

        // $API_SERVER_KEY = 'AAAAmhCfp8k:APA91bHXSHb1Ihie4m3y0v_0e1tAf7JCPMnubM751PMMVkC9oAi54O1AUGipDZ6ZZGCD9ajyxAFLsxjQ0cuLOqSLGjKp9Z0vBttKkIFeX7_xrZnbrAamAex-HMYK3z4SEz2_mHdwWYdu';

         //New APP key for Agroecosystem
// $API_SERVER_KEY = 'AAAAJRsb6G4:APA91bGwslta0Hk72hMKpXaof-PJlzLXnlepgG9r-db0vsMNdJOyXu86foR-acgN3rhFu7-MNmkkBeB20Q1B2DER10EzhvT8X-AdnwS0ksenSeTvMvhR94wQ0Qh9TpWgWWhO62pqVsuf';
        if(get_config_data('API_SERVER_KEY')){
            $API_SERVER_KEY = get_config_data('API_SERVER_KEY');
        } else {
            $API_SERVER_KEY = API_SERVER_KEY;
        }


        $headers = array(
            'Authorization:key=' .$API_SERVER_KEY,
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


    public function sendPushNotifications_NA($token = array(), $title, $message,$is_whitelable,$group_ids = 0,$custom_array ,$type='blog',$id = '',$img='')
    {
        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';
        $arr_user = 1;
       // $type = 1;
        $partner_name = 'Farmer';

        $fields = array(
            'registration_ids' => $token,
            'priority'         => 10,

           
           'data'  => array("title" => $title, "body" => $message, "sound" => 'Default', 'image' => $img, 'admno' => $arr_user, 'type' => $title , 'route'=>$type ,'id' => $id),
        );

        // echo'<pre>';print_r($fields);

        // $API_SERVER_KEY = 'AAAAmhCfp8k:APA91bHXSHb1Ihie4m3y0v_0e1tAf7JCPMnubM751PMMVkC9oAi54O1AUGipDZ6ZZGCD9ajyxAFLsxjQ0cuLOqSLGjKp9Z0vBttKkIFeX7_xrZnbrAamAex-HMYK3z4SEz2_mHdwWYdu';

         //New APP key for Agroecosystem
         // $API_SERVER_KEY = 'AAAAJRsb6G4:APA91bGwslta0Hk72hMKpXaof-PJlzLXnlepgG9r-db0vsMNdJOyXu86foR-acgN3rhFu7-MNmkkBeB20Q1B2DER10EzhvT8X-AdnwS0ksenSeTvMvhR94wQ0Qh9TpWgWWhO62pqVsuf';
        if(get_config_data('API_SERVER_KEY')){
            $API_SERVER_KEY = get_config_data('API_SERVER_KEY');
        } else {
            $API_SERVER_KEY = API_SERVER_KEY;
        }

        $headers = array(
            'Authorization:key=' .$API_SERVER_KEY,
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


// this notification will be sent to parneter / vendor 
    public function sendPushNotifications_request_partner($token = array(), $title, $message,$custom_array ,$type='Schedule',$lead_id = '',$img)
    {
        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';
        $arr_user = 1;
        // $type = 1;
        $partner_name = 'Farmer';
      

        $fields = array(
            'registration_ids' => $token,
            'priority'         => 10,          
        
           'data'  => array("title" => 'Schedule', "body" => $title, "sound" => 'Default', 'image' => $img, 'admno' => $arr_user, 'type' => $type ,'blog_id' => $blog_id),
        );

        // FARMER KEY
        // $API_SERVER_KEY = 'AAAAmhCfp8k:APA91bHXSHb1Ihie4m3y0v_0e1tAf7JCPMnubM751PMMVkC9oAi54O1AUGipDZ6ZZGCD9ajyxAFLsxjQ0cuLOqSLGjKp9Z0vBttKkIFeX7_xrZnbrAamAex-HMYK3z4SEz2_mHdwWYdu';
        // api key for Vendor or partner app
       /* $API_SERVER_KEY = 'AAAAZP52chY:APA91bHn09jHHewFEixuQ87yO4QuYql8_bWBtRYtjx27mMIz-VWhMw6FRtbOoAHfm_xgBoZGqC0NJJiNlfObiNsqE-MNjRvNLaFtfysM6_JTzfZMFyRnjDOuzw5oCj-Ly6_Xw1GUXBX4';
*/
        // $API_SERVER_KEY = 'AAAAJRsb6G4:APA91bGwslta0Hk72hMKpXaof-PJlzLXnlepgG9r-db0vsMNdJOyXu86foR-acgN3rhFu7-MNmkkBeB20Q1B2DER10EzhvT8X-AdnwS0ksenSeTvMvhR94wQ0Qh9TpWgWWhO62pqVsuf';
        if(get_config_data('API_SERVER_KEY')){
            $API_SERVER_KEY = get_config_data('API_SERVER_KEY');
        } else {
            $API_SERVER_KEY = API_SERVER_KEY;
        }


        $headers = array(
            'Authorization:key=' .$API_SERVER_KEY,
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


       public function vendor_call_notification($token = array(), $title, $message,$custom_array ,$type='Schedule',$lead_id = '',$img)
    {
        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';
        $arr_user = 1;
        //$type = 1;
        $partner_name = 'Farmer';
      

        $fields = array(
            'registration_ids' => $token,
            'priority'         => 10,          
        
           'data'  => array("title" => $title, "body" => $message, "sound" => 'Default', 'image' => $img, 'admno' => $arr_user, 'type' => $type , 'blog_id' => $blog_id ,'route'=>$type ,'id' => $id),
        );

       /*  $API_SERVER_KEY = 'AAAAmhCfp8k:APA91bHXSHb1Ihie4m3y0v_0e1tAf7JCPMnubM751PMMVkC9oAi54O1AUGipDZ6ZZGCD9ajyxAFLsxjQ0cuLOqSLGjKp9Z0vBttKkIFeX7_xrZnbrAamAex-HMYK3z4SEz2_mHdwWYdu';
*/
        // $API_SERVER_KEY = 'AAAAJRsb6G4:APA91bGwslta0Hk72hMKpXaof-PJlzLXnlepgG9r-db0vsMNdJOyXu86foR-acgN3rhFu7-MNmkkBeB20Q1B2DER10EzhvT8X-AdnwS0ksenSeTvMvhR94wQ0Qh9TpWgWWhO62pqVsuf';
        if(get_config_data('API_SERVER_KEY')){
            $API_SERVER_KEY = get_config_data('API_SERVER_KEY');
        } else {
            $API_SERVER_KEY = API_SERVER_KEY;
        }


        $headers = array(
            'Authorization:key=' .$API_SERVER_KEY,
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



   //This function will be used to send notification to farmer on action of confrimation or rejected by partenr 
    public function sendPushNotifications_request_farmer($token = array(), $title, $message,$custom_array ,$type='Schedule',$lead_id = '',$img)
    {
        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';
        $arr_user = 1;
        //$type = 1;
        $partner_name = 'Farmer';
      

        $fields = array(
            'registration_ids' => $token,
            'priority'         => 10,          
        
           'data'  => array("title" => 'Schedule', "body" => $title, "sound" => 'Default', 'image' => $img, 'admno' => $arr_user, 'type' => $type ,'blog_id' => $blog_id, 'route'=>$type ,'id' => $id),
        );

    /*     $API_SERVER_KEY = 'AAAAmhCfp8k:APA91bHXSHb1Ihie4m3y0v_0e1tAf7JCPMnubM751PMMVkC9oAi54O1AUGipDZ6ZZGCD9ajyxAFLsxjQ0cuLOqSLGjKp9Z0vBttKkIFeX7_xrZnbrAamAex-HMYK3z4SEz2_mHdwWYdu';*/

         // $API_SERVER_KEY = 'AAAAJRsb6G4:APA91bGwslta0Hk72hMKpXaof-PJlzLXnlepgG9r-db0vsMNdJOyXu86foR-acgN3rhFu7-MNmkkBeB20Q1B2DER10EzhvT8X-AdnwS0ksenSeTvMvhR94wQ0Qh9TpWgWWhO62pqVsuf';
        if(get_config_data('API_SERVER_KEY')){
            $API_SERVER_KEY = get_config_data('API_SERVER_KEY');
        } else {
            $API_SERVER_KEY = API_SERVER_KEY;
        }

        $headers = array(
            'Authorization:key=' .$API_SERVER_KEY,
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
    public function sendPushNotifications_request_dynamic($token = array(), $title, $message,$is_whitelable,$group_ids = 0,$custom_array ,$type='blog',$blog_id = '',$img='')
    {
        $other_details = array();
        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';
        $arr_user = 1;
        if(!empty($custom_array)){
            $fields = array(
                'registration_ids' => $token,
                'priority'         => 10,
                'data'  => array("title" => $title, "body" => $message, "sound" => 'Default', 'image' => $img, 'admno' => $arr_user, 'type' => $type , 'route'=>$type , 'id' => $blog_id ,'custom_array' => $custom_array),
            );
            $other_details = $custom_array;
            $other_details['type'] = $type;
            $other_details['redirect_id'] = $blog_id;
    
        }else{
            $fields = array(
                'registration_ids' => $token,
                'priority'         => 10,
                'data'  => array("title" => $title, "body" => $message, "sound" => 'Default', 'image' => $img, 'admno' => $arr_user, 'type' => $type , 'route'=>$type , 'id' => $blog_id),
            );
            $other_details['type'] = $type;
            $other_details['redirect_id'] = $blog_id;
    
        }
      
        if(get_config_data('API_SERVER_KEY')){
            $API_SERVER_KEY = get_config_data('API_SERVER_KEY');
        } else {
            $API_SERVER_KEY = API_SERVER_KEY;
        }
        $headers = array(
            'Authorization:key=' .$API_SERVER_KEY,
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
        //echo "<pre>result====>";print_r($result);
        if (curl_errno($ch)) {
            $is_send = 0;
        } else {
            $is_send = 1;
        }
        $response = json_decode($result, true);
        if (isset($response['success']) && $response['success'] > 0) {
            // Notification sent successfully
            $other_details = json_encode($other_details, JSON_UNESCAPED_SLASHES);
            add_notification_detail($title, $message,$custom_array['user_id'],$custom_array['map_key'],$custom_array['reference_id'],$other_details);
        } 
        // Close connection
        curl_close($ch);      
        return $result;

    }
	
}
?>
