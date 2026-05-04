<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*

	Class Name 		: 	Common Helper

	

	Created By		: 	Bhagwan Sahane

	Created Date 	: 	27-03-2019

	

	Updated By		:	Deepak Kanmahale

	Updated Date	:	05-07-2019

*/





/**

 * is_login function for use to check user is login or not by using session.

 * Create Deepak kanmahale

 * @param       null

 * @return      boolean

 */

function is_login()

{

    $CI = &get_instance();



    if (!$CI->session->userdata('logged_in')) {

        return FALSE;

    } else {

        return TRUE;

    }

}





/**

 * function for use to check page is access

 * Create Deepak kanmahale

 * @param       null

 * @return      boolean

 **/

function is_page_access()

{

    $CI         = &get_instance();

    $user_type  = $CI->session->userdata('user_type');

    $url        = $_SERVER['REQUEST_URI'];



    if ($user_type == 'admin' && strpos($url, 'admin') !== false) :

        return TRUE;



    elseif ($user_type == 'partner' && strpos($url, 'partner') !== false) :

        return TRUE;



    elseif ($user_type == 'client' && strpos($url, 'partner') == false && strpos($url, 'admin') == false) :

        return TRUE;

    elseif ($user_type == 'consultant' && strpos($url, 'consultant') !== false) :

        return TRUE;

    elseif ($user_type == 'distributor' && strpos($url, 'distributor') !== false) :

        return TRUE;



    elseif ($user_type == 'infraadmin' && strpos($url, 'qadmin/infra') == false) :

        return TRUE;

    elseif ($user_type == 'qaadmin' && strpos($url, 'qadmin/qa') == false) :

        return TRUE;

    elseif ($user_type == 'insurance'):

        return TRUE;

          elseif ($user_type == 'bank'):

        return TRUE;

    else :

        return FALSE;

    endif;

}



function userwise_access($section_value,$permission)

{

    $permission_array = isset($_SESSION['permission_array']) ? $_SESSION['permission_array'] :"";

    $error_msg = '<div class="col-sm-12 no_access_msg msg"><b>You dont have permission to access this page</b></div>';

    //print_r($permission_array);

    return (isset($permission_array[$section_value][$permission]) && $permission_array[$section_value][$permission]!='null') ? $permission_array[$section_value][$permission] : $error_msg;

}



/* get url by role */

function get_url_by_role($user_type = '' )

{

    $CI = &get_instance();



    if (empty($user_type)) {

        $user_type = $CI->session->userdata('user_type');

    }



    $url = '';

    if($user_type =='admin'){

        $url = 'admin/dashboard';



    }

    if($user_type =='infraadmin'){

        $url = 'admin/infra/dashboard';



    }

    if($user_type =='qaadmin'){

        $url = 'admin/qa/dashboard';

    }

    if($user_type =='insurance'){

        $url = 'insurance/dashboard';

    }

    if($user_type =='bank'){

        $url = 'bank/dashboard';

    }

   

    return $url;

}





/**

 * function for use string replace with dash and clean string

 * by Deepak k

 * @param        string $string

 * @return       string

 */

function replace_space_with_dash($name)

{   

    $name = str_replace(' ', '-', trim($name));

    return strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $name));

}



/**

 * function for use to encrypt string with sha512 with config key

 * by Deepak k

 * @param        string $string

 * @return       string

 */

function hash_key($string)

{

    return md5( $string.time());

    

    //return hash('sha512', $string . config_item('encryption_key') . time());

}



/**

 * function for use get csrf token

 * by Deepak k

 * @return       string

 */

function get_csrf_token()

{

    $CI     = &get_instance();

    $input  = '';

    $csrf_is_active = $CI->config->item('csrf_is_active');

    if ($csrf_is_active) {



        $input  = '<input type="hidden" name="'.$CI->security->get_csrf_token_name().'" value="'.$CI->security->get_csrf_hash().'" />';

    }

    echo $input;

}



function time_differ($to_time, $from_time = NULL)

{

    if (empty($from_time)) {

        $from_time = date('Y-m-d H:i:s');

    }



    $to_time = strtotime($to_time);

    $from_time = strtotime($from_time);

    return round(abs($to_time - $from_time) / 60, 2);

}



//******************** Encrypt & Decrypt Password *************************//

// Added By		: 	Bhagwan Sahane

// Added Date	:	23-08-2019

function encrypt($string, $encryption_hash)

{

    $key = md5(md5($encryption_hash)) . md5($encryption_hash);

    $hash_key = _hash($key);

    $hash_length = strlen($hash_key);

    $iv = _generate_iv($encryption_hash);

    $out = "";

    for ($c = 0; $c < $hash_length; $c++) {

        $out .= chr(ord($iv[$c]) ^ ord($hash_key[$c]));

    }

    $key = $iv;

    for ($c = 0; $c < strlen($string); $c++) {

        if ($c != 0 && $c % $hash_length == 0) {

            $key = _hash($key . substr($string, $c - $hash_length, $hash_length));

        }



        $out .= chr(ord($key[$c % $hash_length]) ^ ord($string[$c]));

    }

    return base64_encode($out);

}



function decrypt($string, $encryption_hash)

{

    $key = md5(md5($encryption_hash)) . md5($encryption_hash);

    $hash_key = _hash($key);

    $hash_length = strlen($hash_key);

    $string = base64_decode($string);

    $tmp_iv = substr($string, 0, $hash_length);

    $string = substr($string, $hash_length, strlen($string) - $hash_length);

    $iv = "";

    $out = "";

    for ($c = 0; $c < $hash_length; $c++) {

        $ivValue = (isset($tmp_iv[$c]) ? $tmp_iv[$c] : "");

        $hashValue = (isset($hash_key[$c]) ? $hash_key[$c] : "");

        $iv .= chr(ord($ivValue) ^ ord($hashValue));

    }

    $key = $iv;

    for ($c = 0; $c < strlen($string); $c++) {

        if ($c != 0 && $c % $hash_length == 0) {

            $key = _hash($key . substr($out, $c - $hash_length, $hash_length));

        }



        $out .= chr(ord($key[$c % $hash_length]) ^ ord($string[$c]));

    }

    return $out;

}



function _hash($string)

{

    if (function_exists("sha1")) {

        $hash = sha1($string);

    } else {

        $hash = md5($string);

    }



    $out = "";

    $c = 0;

    while ($c < strlen($hash)) {

        $out .= chr(hexdec($hash[$c] . $hash[$c + 1]));

        $c += 2;

    }

    return $out;

}



function _generate_iv($encryption_hash)

{

    srand((float) microtime() * 1000000);

    $iv = md5(strrev(substr($encryption_hash, 13)) . substr($encryption_hash, 0, 13));

    $iv .= rand(0, getrandmax());

    $iv .= serialize(array("key" => md5(md5($encryption_hash)) . md5($encryption_hash)));

    return _hash($iv);

}

//******************** Encrypt & Decrypt Password *************************//





/******************** Datatable Query ***********************************/



// Added By		: 	Deepak Kanmahale

// Added Date	:	10-05-2019



function get_datatables_query($table, $cols, $co)

{

    $CI = &get_instance();

    $CI->db->from($table);



    $i = 0;



    foreach ($cols->column_search as $item) // loop column

    {

        if ($_POST['search']['value']) // if datatable send POST for search

        {



            if ($i === 0) // first loop

            {

                $CI->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.

                $CI->db->like($item, $_POST['search']['value']);

            } else {

                $CI->db->or_like($item, $_POST['search']['value']);

            }



            if (count($cols->column_search) - 1 == $i) //last loop

            {

                $CI->db->group_end();

            }

            //close bracket

        }

        $i++;

    }



    if (isset($_POST['order'])) // here order processing

    {

        $CI->db->order_by($co->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);

    } else if (isset($CI->order)) {

        $order = $co->order;

        $CI->db->order_by(key($order), $order[key($order)]);

    }

}

/********************End Datatable Query ***********************************/



/**

 * get_name_by_id function for use to get perticular column value by given id

 * by rahul b

 * @param        string      $tbl input string

 * @param        string      $selectName input string

 * @param        string      $where input string

 * @param        int     	 $id input int

 * @return      string 

 */

function get_name_by_id($tbl, $selectName, $where)

{

    $CI     = &get_instance();



    $CI->db->select($selectName);

    $CI->db->from($tbl);

    $CI->db->where($where);



    $result =     $CI->db->get();

    $data     =    $result->result_array();

    return !empty($data[0][$selectName]) ? $data[0][$selectName] : '';

}





/**

 * by Deepak k

 * @param        date      	$date input string

 * @param        fromat      $format input string

 */

function date_converter($date = NULL, $format = 'd-m-Y')

{

    if ($date != NULL) {

        if (strpos($date, '/') !== FALSE) {

            $date = str_replace('/', '-', $date);

        }

        return date($format, strtotime($date));

    } else {

        return '';

    }

}



/**

 * url_exists function for use to Check url is valid or not 

 * Create Deepak kanmahale

 * @param       string $url

 * @return      boolean

 */

function url_exists($url)

{

    //$headers 	=	get_headers($url);

    return false; //stripos($headers[0],"200 OK")?true:false;

}



/**

 * truncate_string function for use to truncate string by characher

 * by Deepak K

 * @param       string

 * @return      string

 */

function truncate_string($string, $limit = 50, $repl = '...')

{

    if (strlen($string) > $limit) {

        return substr($string, 0, $limit) . $repl;

    } else {

        return $string;

    }

}





// get all the option for select box exp. get_option_list('API url heare','user_id','userName','1');

function get_option_list($tblName, $id, $name, $postData = '')

{

    $CI = &get_instance();

    $CI->load->model('Order_model');

    $where    = array('is_active' => true, 'is_deleted' => false);

    $dataArry = $CI->Order_model->get_data(array($id, $name), $tblName, $where);

    $opt = '';

    for ($i = 0; $i < count($dataArry); $i++) {

        $select = '';

        if ($postData == $dataArry[$i][$id]) {

            $select = 'selected';

        }

        $opt .= "<option " . $select . " value='" . $dataArry[$i][$id] . "'>" . $dataArry[$i][$name] . "</option>";

    }

    return $opt;

}





/* Use for return active class for menu by using controller name*/

if (!function_exists('activate_menu')) {

    function activate_menu($controller, $class_name = "active")

    {

        $CI     = &get_instance();

        $class  = $CI->router->fetch_class();

        //$method = $CI->router->fetch_method();

        return ($class == $controller) ? $class_name : '';

    }

}



/**

 * searchForId function use for search value from multiple accosiative array with multiple associate array

 * ref: https://stackoverflow.com/questions/6661530/php-multidimensional-array-search-by-value

 * @param       int $id

 * @param       int $key_valid <optional>

 * @param       array $array

 * @return    	int

 */

function searchForId($id, $array, $key_valid = 'id')

{

    if (empty($array)) return false;

    foreach ($array as $key => $val) {

        if ($val[$key_valid] == $id) {

            return  array($val['amount'], $key);

        }

    }

    return 0;

}



/**

 * date_diff_in_month function use for calculate two date diffrence in month 

 * @param       date $start_date

 * @param       date $end_date

 * @return    	int

 */

function date_diff_in_month($start_date, $end_date)

{

    $ts1 = strtotime($start_date);

    $ts2 = strtotime($end_date);

    $year1 = date('Y', $ts1);

    $year2 = date('Y', $ts2);

    $month1 = date('m', $ts1);

    $month2 = date('m', $ts2);

    return $diff_month = (($year2 - $year1) * 12) + ($month2 - $month1);

}



/**

 * searchForId_license function use for get amount from array from array value  

 * @param       int $id

 * @param       int $value

 * @param       array $array

 * @param       default $key_valid

 * @return    	int

 */

function searchForId_license($id, $value, $array, $key_valid = 'license_id')

{

    if (empty($array)) return false;

    foreach ($array as $key => $val) {

        if ($val[$key_valid] == $id) {



            if (empty($val['to_range']) || $val['to_range'] == 0) {

                return $val['amount'];

            } elseif (empty($val['from_range']) || $val['from_range'] == 0) {

                return $val['amount'];

            }



            if ($val['from_range'] <= $value && $val['to_range'] >= $value) {

                return $val['amount'];

            }

        }

    }







    return 0;

}





/**

 * genrate_license_key function use for genrate randome key

 * @param       int $n

 * @return    	string

 */

if (!function_exists('genrate_license_key')) {

    function genrate_license_key($n = 50)

    {

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $randomString = '';



        for ($i = 0; $i < $n; $i++) {

            $index = rand(0, strlen($characters) - 1);

            $randomString .= $characters[$index];

        }



        return $randomString;

    }

}



/**

 * random number

 */

if (!function_exists('random_number')) {

    function random_number($min = 10000, $max = 999999999)

    {

        return rand($min, $max);

    }

}



/**

 * genrate_license_key function use for genrate randome key

 * @param       int $n

 * @return    	string

 */

if (!function_exists('get_subcategory_list')) {

    function get_subcategory_list($cat_id, $sub_cat)

    {

        $CI     = &get_instance();

        $CI->db->order_by('name', 'ASC');

        $CI->db->where('is_deleted', 'false');

        $rest   = $CI->db->get('sub_categories');

        $result = $rest->result_array();

        $html = '';

        if (!empty($result)) {

            foreach ($result as $rows) {

                $html .= '<li class="' . ($sub_cat == $rows['sub_cat_id'] ? 'child-active' : '') . '"><a href="' . base_url('/category/' . $cat_id . '/' . $rows['sub_cat_id']) . '">' . $rows['name'] . '</a></li>';

            }

        }

        return $html;

    }

}



/**

 * genrate_license_key function use for genrate randome key

 * @param       int $n

 * @return      string

 */

if (!function_exists('service_subcategory')) {

    function service_subcategory($cat_name, $sub_cat)

    {

        $CI     = &get_instance();

        $CI->db->order_by('name', 'ASC');

        $CI->db->where('is_deleted', 'false');

        $rest   = $CI->db->get('service_subcategory');

        $result = $rest->result_array();

        $html = '';

        if (!empty($result)) {

            foreach ($result as $rows) {

                $html .= '<li class="' . ($sub_cat == $rows['id'] ? 'child-active' : '') . '"><a href="' . base_url('services/' . replace_space_with_dash($cat_name) . '/' . replace_space_with_dash($rows['name']) ). '">' . $rows['name'] . '</a></li>';

            }

        }

        return $html;

    }

}



/**

 * category_name_by_product function use get cat name by product id

 * @param       int $n

 * @return      string

 */

if (!function_exists('category_name_by_product')) {

    function category_name_by_product($product_id)

    {

        $CI     = &get_instance();

        $sql = "select string_agg(name, ', ' ORDER BY name) As cat_list from categories where cat_id in ( SELECT unnest(string_to_array(category_id, ','))::int from products where id=" . $product_id . ")";

        $query = $CI->db->query($sql);

        if ($query->num_rows()) {

            $result = $query->result_array();

            return (isset($result[0]) && !empty($result[0]['cat_list']) ? $result[0]['cat_list'] : '');

        }

        return '';

    }

}



/**

 * category_name_by_product_patner function use get cat name by product id

 * @param       int $n

 * @return      string

 */

if (!function_exists('category_name_by_product_patner')) {

    function category_name_by_product_patner($product_id)

    {

        $CI     = &get_instance();

        $sql = "select string_agg(name, ', ' ORDER BY name) As cat_list from pcategories where pcat_id in ( SELECT unnest(string_to_array(category_id, ','))::int from products where id=" . $product_id . ")";

        $query = $CI->db->query($sql);

        if ($query->num_rows()) {

            $result = $query->result_array();

            return (isset($result[0]) && !empty($result[0]['cat_list']) ? $result[0]['cat_list'] : '');

        }

        return '';

    }

}



/**

 * product_category_name_by_product function use get cat name by product id

 * @param       int $n

 * @return      string

 */

if (!function_exists('product_category_name_by_product')) {

    function product_category_name_by_product($product_id)

    {

        $CI     = &get_instance();

        $sql = "select string_agg(name, ', ' ORDER BY name) As cat_list from pcategories where pcat_id in ( SELECT unnest(string_to_array(category_id, ','))::int from products where id=" . $product_id . ")";

        $query = $CI->db->query($sql);

        if ($query->num_rows()) {

            $result = $query->result_array();

            return (isset($result[0]) && !empty($result[0]['cat_list']) ? $result[0]['cat_list'] : '');

        }

        return '';

    }

}



/**

 * category_name_by_product function use get cat name by product id

 * @param       int $n

 * @return      string

 */

if (!function_exists('category_name_by_service')) {

    function category_name_by_service($service_id)

    {

        $CI     = &get_instance();

        $sql = "select string_agg(name, ', ' ORDER BY name) As cat_list from categories where cat_id in ( SELECT unnest(string_to_array(category_id, ','))::int from services where id=" . $service_id . ")";

        $query = $CI->db->query($sql);

        if ($query->num_rows()) {

            $result = $query->result_array();

            return (isset($result[0]) && !empty($result[0]['cat_list']) ? $result[0]['cat_list'] : '');

        }

        return '';

    }

}



/**

 * system_requirementby_product function use get cat name by product id

 * @param       int $n

 * @return      string

 */

if (!function_exists('system_requirementby_product')) {

    function system_requirementby_product($product_id)

    {

        $CI     = &get_instance();



        $sql = "SELECT 

        IR.os_id,IR.os_version_id,IR.os_other_version,IR.db_other_version,IR.os_other,IR.database_id,IR.database_version_id,IR.db_other,os.name as os_name,ios.name as os_version_name,idb.name as database_name,idbv.name as database_version_name

        from infrastructure_requirnments as IR

        left join infra_operating_system as os on os.id = IR.os_id

        left join infra_operating_system as ios on ios.id = IR.os_version_id

        left join infra_database as idb on idb.id = IR.database_id

        left join infra_database as idbv on idbv.id = IR.database_version_id

        where IR.is_deleted=false and IR.product_id =" . $product_id;

        $query = $CI->db->query($sql);

        if ($query->num_rows()) {

            $result = $query->result_array();

            $result = (isset($result[0]) ? $result[0] : array());

            $out = '';

            $os_version = $result['os_id'] == NULL ?  $result['os_other_version'] : $result['os_version_name'];



            if (!empty($result['os_id'])) {

                $out .= $result['os_name'] . '(' . $os_version . '), ';

            } else {

                $out .= $result['os_other'] . '(' . $os_version . '), ';

            }



            $db_version = $result['os_id'] == NULL ?  $result['db_other_version'] : $result['database_version_name'];



            if (!empty($result['database_id'])) {

                $out .= $result['database_name'] . '(' . $db_version . ')';

            } else {

                $out .= $result['db_other'] . '(' . $db_version . ')';

            }

            return $out;

        }

        return '';

    }

}





/**

 * function for use to payment log.

 * Create Deepak kanmahale

 * @param       $insert_data array

 * @return      boolean

 */

function get_gst_calculation($amount)

{

    $CI = &get_instance();

    $tax_rate = $CI->config->item('TAX_RATE');

    $tax    = $amount * ($tax_rate / 100);

    $total  = $amount + $tax;

    $data = array('tax' => $tax, 'tax_rate' => $tax_rate, 'total' => $total);

    return $data;

}



/**

 * function for use to error_log_file manage error log.

 * Create Rahul badhe

 * @param       $data  array

 * @param       $mod  string

 * @return      null

 */

function get_upgrade_status($product_id, $vm_id)

{

    $CI = &get_instance();

    $sql = "SELECT status FROM infra_upgrade_request where product_id='" . $product_id . "' and vm_id='" . $vm_id . "' and status !='Complete' order by id desc";

    //  echo "<br>";

    $result = $CI->db->query($sql);

    $result = $result->result_array();

    //;

    if (!empty($result[0]['status'])) {

        return $result[0]['status'];

    } else {

        return '';

    }

}



function get_product_status($exist_key = 0)

{

    $CI     = &get_instance();

    $role   = $CI->session->userdata('user_type');

    $PRODUCT_STATUS = $CI->config->item('PRODUCT_STATUS');

    $select = '<option value="">-- Select --</option>';

    foreach ($PRODUCT_STATUS as $key => $status) {

        $i = $exist_key == 2 ? 2 : 1;

        if ($role == 'infraadmin') {

            $i = $exist_key == 3 ? 2 : 1;

        }



        if ($role == 'partner') {

            if ($exist_key == 8) {

              if ( $key == 7 ) {    

                    $selected = $key == $exist_key ? 'selected' : '';

                    $select .= '<option ' . $selected . ' value="' . $key . '">'.$status['label'] . '</option>';            

                }



            }else if ( $key > $exist_key && $key <= ($exist_key + $i)) {    

                $selected = $key == $exist_key ? 'selected' : '';

                $select .= '<option ' . $selected . ' value="' . $key . '">'.$status['label'] . '</option>';            

            }

        }else{

            if ($status['user'] == $role && $key > $exist_key && $key <= ($exist_key + $i)) {    

                $selected = $key == $exist_key ? 'selected' : '';

                $select .= '<option ' . $selected . ' value="' . $key . '">'.$status['label'] . '</option>';            

            }

        }

        

       /* if ($status['user'] == $role && $key <= ($exist_key + $i)) {

            $selected = $key == $exist_key ? 'selected' : '';

            $select .= '<option ' . $selected . ' value="' . $key . '">' . $status['label'] . '</option>';

        }*/

        

        

    }

    return $select;

}



function get_status_options($exist_key = 0,$status_array = NULL)

{

    $CI     = &get_instance();

    if (empty($status_array)) {

       $status_array = $CI->config->item('PRODUCT_STATUS');

    }



    $role   = $CI->session->userdata('user_type');



    $select = '<option value="">-- Select --</option>';

    foreach ($status_array as $key => $status) {

        if ($role == 'admin') {

           $up_to = $exist_key + 5;

        }

        if ($role == 'consultant') {

            $up_to = $exist_key + 1;

        }

        

        if ($status['user'] == $role && $key > $exist_key && $key <= $up_to) {   

                $selected = $key == $exist_key ? 'selected' : '';

                $select .= '<option ' . $selected . ' value="' . $key . '">'.$status['label'] . '</option>';            

        }

    }

    return $select;

}



function get_product_score($product_score)

{

    $score = 0;

    $product_score = json_decode($product_score,true);

    if (is_array($product_score) && !empty($product_score)) {

        $list_count = count($product_score)*100;



        foreach ($product_score as $key => $score_array) {

            $score = $score + $score_array['score'];

        }



        //$score    = ($score/$list_count)*100;

        $score    = number_format($score,2);



        return $score;

    }

    return $score;

    

}



/**

 * function for use to get current date.

 * Create Aayusha kapadni

 * @param       $date array

 * @return      boolean

 */

function current_date()

{

    $CI = &get_instance();

    return $CI->config->item('CURRENT_DATE');

}



/**

 * function for use to get currency array.

 * Create Aayusha kapadni

 * @param       $data array

 * @return      boolean

 */



function get_currency($amount=NULL)

{

    $CI = &get_instance();

    $currency_arr = $CI->config->item('CURRENCY');

    $def_currency = $CI->config->item('DEFAULT_CURRENCY');

    $data = array('code' =>  $currency_arr[$def_currency]['code'],'symbol' => $currency_arr[$def_currency]['symbol']);

    $amount = !empty($amount) ? $amount : 0;

    return $currency_arr[$def_currency]['symbol'].' '.$amount;

    

}







function get_email_body($title,$replace_array=array())

{

    $CI     = &get_instance();

    $CI->load->model('masters_model');

    $result = $CI->masters_model->get_data('*','emailer', array( 'name' => $title));





    if(!empty($replace_array['body'])){

        $body=strtr($result[0]['message'], $replace_array['body']);

    }else{

        $body=$result[0]['message'];

    } 



    if(!empty($replace_array['subject'])){

        $subject=html_entity_decode(strtr($result[0]['subject'], $replace_array['subject']));

        //'=?UTF-8?B?'.base64_encode($subject).'?=';

    }else{

        $subject=html_entity_decode($result[0]['subject']);

    }



    return array('body'=>$body,'subject'=>$subject,'from_mail'=>$result[0]['from_mail']);

}



function email_by_admin_role($role = 'spochubadmin')

{

    $CI     = &get_instance();

    $CI->load->model('masters_model');

    $result = $CI->masters_model->get_data('first_name,last_name,email','admins', array( 'admin_role' => $role,'mail_sent' => TRUE));



    $email = array();

    if (!empty($result)) {

        foreach ($result as $admin ) {

            $email[] = array(

                'email'     => $admin['email'],

                'full_name' => ucfirst($admin['first_name'].' '.$admin['last_name']),

            );

        }

    }

    return $email;

}



//auto_increment_master

if (!function_exists('auto_increment_master')) {

    function auto_increment_master($name = 'invoice_auto_increment')

    {

        $CI     = &get_instance();

        $invoice_num_format = $CI->config->item('invoice_num_format');



        $sql = "SELECT (value::int +1) as value FROM site_config WHERE name='" . $name . "';";

        $query = $CI->db->query($sql);

        if ($query->num_rows()) {

            $result = $query->result_array();

            $value = (isset($result[0]) && !empty($result[0]['value']) ? $result[0]['value'] : 0);



            $CI->db->where(array('name' =>$name ));

            $CI->db->update('site_config', array('value' => $value));

            

            $number = $invoice_num_format.str_pad($value, 4, 0, STR_PAD_LEFT);

            return $number;

        }

        return FALSE;

    }

}



//auto_increment_order

if (!function_exists('auto_increment_order')) {

    function auto_increment_order($order_id)

    {

        $CI     = &get_instance();

        $order_num_format = $CI->config->item('order_num_format');



        if (date('m') >= 3) {

            $year = date('y')."-".(date('y') +1);

        }else {

            $year = (date('y')-1)."-".date('y');

        }

        //$year = date('y').'-'.date("y",strtotime("+1 year"));



        return $order_num_format.$year.'/'.str_pad($order_id, 3, 0, STR_PAD_LEFT);

    }

}



if (!function_exists('getallheaders'))

{

    function getallheaders()

    {

       $headers = [];

       foreach ($_SERVER as $name => $value)

       {

           if (substr($name, 0, 5) == 'HTTP_')

           {

               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;

           }

       }

       return $headers;

   }

}

function get_product_count($user_id,$user_type='client')

{

    $CI     = &get_instance();

    $CI->load->model('masters_model');

    if($user_type=='client'){

        $join     = array('client_order_product cp' => 'cp.client_id = c.id');

        $where=array( 'c.id' => $user_id,'c.is_deleted'=>false);

        $result = $CI->masters_model->get_data('count(cp.id)as active_product','client c', $where,$join);

        $product_count = $result[0]['active_product'];

    }else{

        $join     = array('products p' => 'p.partner_id = u.user_id');

        $where=array( 'u.user_id' => $user_id,'p.status'=>7,'p.is_deleted'=>false);

        $result = $CI->masters_model->get_data('count(p.id)as active_product','users u', $where,$join);

        $product_count = $result[0]['active_product'];

    }

    return $product_count;

}



function get_product_counts($user_id)

{

    $CI     = &get_instance();

    $CI->load->model('masters_model');

    

    $join     = array('client_order_product cp' => 'cp.client_id = c.id','products p' =>' p.id=cp.product_id');

    $where=array( 'c.id' => $user_id,'p.partner_id' =>$CI->session->userdata('user_id'));

    $result = $CI->masters_model->get_data('count(cp.id)as active_product','client c', $where,$join);

    $product_count = $result[0]['active_product'];

    

    return $product_count;

}

function get_user_name_logs($user_id,$user_type)

{

    $CI     = &get_instance();

    $CI->load->model('masters_model');

    $user_name='';

    if($user_type=='client' && !empty($user_id)){

       $where=array( 'id' => $user_id);

       $result = $CI->masters_model->get_data('first_name,last_name','client', $where);

        if(!empty($result)){    

            $user_name = '<a class="text-info" href="'.base_url('admin/clients/details/').$user_id.'">'.ucwords($result[ 0]['first_name'].' '.$result[0]['last_name']).'</a>';

        }

    }elseif($user_type=='partner' || $user_type=='consultant' || $user_type=='distributor' && !empty($user_id)){

       $where=array( 'user_id' => $user_id);

       $result = $CI->masters_model->get_data('first_name,last_name','users', $where);

       if(!empty($result)){        

            $user_name = '<a  class="text-info" href="'.base_url('admin/partners/details/').$user_id.'">'.ucwords($result[0]['first_name'].' '.$result[0]['last_name']).'</a>';

       }

    }elseif(!empty($user_id) && !empty($user_type)){

        $where=array( 'id' => $user_id);

        $result = $CI->masters_model->get_data('first_name,last_name','admins', $where);

        $user_name = ucwords(@$result[0]['first_name']);

    }

    return $user_name;

}



function get_captcha(){

    $CI     = &get_instance();

    $CI->load->helper('captcha');

    $config = array(

        'img_path'      => './uploads/captcha/',

        'img_url'       => base_url().'./uploads/captcha/',

        'font_path'     => 'system/fonts/texb.ttf',

        'img_width'     => '140',

        'img_height'    => 40,

        'word_length'   => 5,

        'expiration'    => 7200,

        'font_size'     => 15,

        'colors'        => array(

            'background' => array(246,246,246),

            'border' => array(0,0,0),

            'text'  => array(0,0,0),

            'grid' => array(135,206,235)

        )

    );

    $captcha = create_captcha($config);



        // Unset previous captcha and set new captcha word

    $CI->session->unset_userdata('captchaCode');

    $CI->session->set_userdata('captchaCode', $captcha['word']);

    return $captcha['image'];

}

function get_custom_field_form($product_id){

    $CI     = &get_instance();

    $CI->load->model('masters_model');

    $custom_fields_data = $CI->masters_model->get_data('*', 'custom_fields',array('type' => 'product','real_id' =>$product_id,'type'=>'product','show_on_order'=>true),'','sort_order asc');

    $html='';

    if(!empty($custom_fields_data)){

        $html.='<div class="col-md-12"><h5 class="text-info">Additional fields required to complete this order:</h5></div>';

        foreach ($custom_fields_data as $value) {



            switch ($value['field_type']) {

                case 'input':

                    if(strpos($value['field_name'], '|')){

                        $field_name=explode('|',$value['field_name']);

                        $field_label=@$field_name[1];



                    }else{

                        $field_label=$value['field_name'];

                    }

                    

                    $req='';

                    $req1='';

                    if($value['is_required']=='t'){

                        $req="required";

                        $req1='<span class="required">*</span>';

                    }

                    $descp='';

                    if(!empty($value['description'])){

                       

                        $descp='<small>('.$value['description'].')</small>';

                    }

                    $pattern='';

                    if(!empty($value['reg_exp'])){

                        $pattern='pattern='.$value['reg_exp'];

                        

                    }

                    $html.='<div class="col-md-6">

                    <div class="form-group">';

                    $html.='<label><b>'.@$field_label.': '.$req1.'</b></label>';

                    $html.='<input placeholder="'.@$field_label.'" type="text" '.$pattern.' class="form-control nospace" name="custom_field['.$value['id'].']" value="" '.$req.' >

                    '.$descp.'

                    </div>

                    </div>';

                    break; 

                case 'password':

                    if(strpos($value['field_name'], '|')){

                        $field_name=explode('|',$value['field_name']);

                        $field_label=@$field_name[1];



                    }else{

                        $field_label=$value['field_name'];

                    }

                    

                    $req='';

                    $req1='';

                    if($value['is_required']=='t'){

                        $req="required";

                        $req1='<span class="required">*</span>';

                    }

                    $pattern='';

                    if(!empty($value['reg_exp'])){

                        $pattern='pattern='.$value['reg_exp'];

                        

                    }

                    $descp='';

                    if(!empty($value['description'])){

                       

                        $descp='<small>('.$value['description'].')</small>';

                    }

                    $html.='<div class="col-md-6">

                    <div class="form-group">';

                    $html.='<label><b>'.@$field_label.': '.$req1.'</b></label>';

                    $html.='<input placeholder="'.@$field_label.'" type="password" class="form-control nospace" '.$pattern.' name="custom_field['.$value['id'].']" value="" '.$req.' >

                    '.$descp.'

                    </div>

                    </div>';

                    break; 

                case 'link':

                   if(strpos($value['field_name'], '|')){

                        $field_name=explode('|',$value['field_name']);

                        $field_label=@$field_name[1];



                    }else{

                        $field_label=$value['field_name'];

                    }



                    $req='';

                    $req1='';

                    if($value['is_required']=='t'){

                        $req="required";

                        $req1='<span class="required">*</span>';

                    }

                    $pattern='';

                    if(!empty($value['reg_exp'])){

                        $pattern='pattern='.$value['reg_exp'];

                        

                    }

                    $descp='';

                    if(!empty($value['description'])){

                       

                        $descp='<small>('.$value['description'].')</small>';

                    }

                    $html.='<div class="col-md-6">

                    <div class="form-group">';

                    $html.='<label><b>'.@$field_label.': '.$req1.'</b></label>';

                    $html.='<input placeholder="'.@$field_label.'" type="url" '.$pattern.' class="form-control nospace" name="custom_field['.$value['id'].']" value="" '.$req.' >

                    '.$descp.'

                    </div>

                    </div>';

                    break; 

                case 'checkbox':

                    if(strpos($value['field_name'], '|')){

                        $field_name=explode('|',$value['field_name']);

                        $field_label=@$field_name[1];



                    }else{

                        $field_label=$value['field_name'];

                    }



                    $req='';

                    $req1='';

                    if($value['is_required']=='t'){

                        $req="required";

                        $req1='<span class="required">*</span>';

                    }

                    $descp='';

                    if(!empty($value['description'])){

                       

                        $descp='<small>('.$value['description'].')</small>';

                    }

                    $html.='<div class="col-md-6">

                    <div class="form-group">';

                    $html.='<label><b>'.@$field_label.': '.$req1.'</la</b>bel><br>';

                    $html.='<input placeholder="'.@$field_label.'" type="checkbox" class="" name="custom_field['.$value['id'].']" value="true" '.$req.' >

                    '.$descp.'

                    </div>

                    </div>';

                    break; 

                case 'textarea':

                    if(strpos($value['field_name'], '|')){

                        $field_name=explode('|',$value['field_name']);

                        $field_label=@$field_name[1];



                    }else{

                        $field_label=$value['field_name'];

                    }



                    $req='';

                    $req1='';

                    if($value['is_required']=='t'){

                        $req="required";

                        $req1='<span class="required">*</span>';

                    }

                    $descp='';

                    if(!empty($value['description'])){

                       

                        $descp='<small>('.$value['description'].')</small>';

                    }

                    $html.='<div class="col-md-6">

                    <div class="form-group">';

                    $html.='<label><b>'.@$field_label.': '.$req1.'</b></label>';

                    $html.='<textarea placeholder="'.@$field_label.'" class="form-control nospace" name="custom_field['.$value['id'].']" '.$req.' ></textarea>

                    '.$descp.'

                    </div>

                    </div>';

                    break; 

                case 'select':

                    if(strpos($value['field_name'], '|')){

                        $field_name=explode('|',$value['field_name']);

                        $field_label=@$field_name[1];



                    }else{

                        $field_label=$value['field_name'];

                    }



                    if(strpos($value['field_option'], ',')){

                        $field_option=explode(',',$value['field_option']);

                    }

                    $req='';

                    $req1='';

                    if($value['is_required']=='t'){

                        $req="required";

                        $req1='<span class="required">*</span>';

                    }

                    $descp='';

                    if(!empty($value['description'])){

                       

                        $descp='<small>('.$value['description'].')</small>';

                    }

                    $opt_val='';

                    if(!empty($field_option)){

                        foreach ($field_option as $val) {

                            if(!empty($val)){

                                $opt_val.='<option value='.$val.'>'.$val.'</option>';

                            }

                        }

                    }



                    $html.='<div class="col-md-6">

                    <div class="form-group">';

                    $html.='<label><b>'.@$field_label.': '.$req1.'</b></label>';

                    $html.='<select class="form-control" name="custom_field['.$value['id'].']" '.$req.' >

                    <option value="">-Select-</option>

                    '.$opt_val.'

                    </select>

                    '.$descp.'

                    </div>

                    </div>';

                    break;



                default:

                           $html.='';

                    break;

            }

        }

    }

    echo $html;



}

 function validate_promo_code($promo_code,$product_id,$package_id,$flag='Order'){

    $CI     = &get_instance();

    $CI->load->model('masters_model');

    $whr=array('code' =>$promo_code,'is_deleted'=>false,'item'=>'Product');

    $coupon_data = $CI->masters_model->get_data('*', 'coupon_master',$whr);



    // check coupon code is valid

    if(!empty($coupon_data))

    {   

        $check_expiry=true;

        //start date and end date is not null

        if(empty($coupon_data[0]['start_date']) && empty($coupon_data[0]['expiration_date'])){

            $check_expiry=true;

        }else{



            $start_date='';

            if(!empty($coupon_data[0]['start_date'])){

                $start_date=date('Y-m-d',strtotime($coupon_data[0]['start_date']));

            }



            $expiration_date='';

            if($coupon_data[0]['expiration_date']){

                $expiration_date=date('Y-m-d',strtotime($coupon_data[0]['expiration_date']));

            }

             //check start date is not null and end date is null

            if(!empty($start_date) && empty($expiration_date)){ 

                //check start date is less than current date

                if($start_date <= date('Y-m-d') ){

                    $check_expiry=true;

                }else{

                    return $json_data=array('status'=>'0','message'=>'The coupon code is not valid.');

                }

            }

            //check end date is not null and start date is nul

            if(!empty($expiration_date) && empty($start_date)){ 

                //check end date is greater than current date

                if(empty($start_date) && $expiration_date >= date('Y-m-d')){

                    $check_expiry=true;

                }else{

                     $check_expiry=false;

                }                                           

            }



            //check end date and start date is not null

            if(!empty($start_date) && !empty($expiration_date) ){ 

                //check start date is less than current date

                if($start_date <= date('Y-m-d') ){

                     //check end date is greater than current date

                   if($expiration_date >= date('Y-m-d')){

                        $check_expiry=true;

                   }else{

                        $check_expiry=false;

                   }

                }else{

                    return $json_data=array('status'=>'0','message'=>'The coupon code is not valid.');

                }                                           

            }

        }



        // check coupon code expiry

        if($check_expiry)

        {

            $whr=array('promo_code' =>$promo_code,'status !='=>'Cancelled'); // here is not considers is_deleted order

            $order_data = $CI->masters_model->get_data('count(promo_code) as promo_code_count', 'client_orders',$whr);    

            if($flag=="Invoice"){

                $con=!empty($order_data) && ($order_data[0]['promo_code_count'] <= $coupon_data[0]['max_uses'] || $coupon_data[0]['max_uses']==0);

            }else{

                  $con=!empty($order_data) && ($order_data[0]['promo_code_count'] < $coupon_data[0]['max_uses'] || $coupon_data[0]['max_uses']==0);

            }

             // check coupon code uses count to match with used count. === 0 unlimited          

            if($con)

            { 

                $coupon_product_id=array_keys(json_decode($coupon_data[0]['applies_to'],true));             

                $coupon_product_pkg_id=json_decode($coupon_data[0]['applies_to'],true);

                // check coupon code is applied for this product

                if(in_array($product_id, $coupon_product_id))

                {

                    // check if packages is exist against product               

                    if(!empty($coupon_product_pkg_id[$product_id]))

                    {

                        $prod_pkg_id=array_values($coupon_product_pkg_id[$product_id]);



                        // check coupon code is applied for this product package

                        if(in_array($package_id,$prod_pkg_id))

                        {

                            $json_data=array('status'=>'1','message'=>'Your coupon code has been applied.');

                        }else{

                            $json_data=array('status'=>'0','message'=>'Coupon code is invalid.');

                        } // pkg check end



                    }else{

                        $json_data=array('status'=>'1','message'=>'Your coupon code has been applied.');

                    } // check product pkg exist end



                }else{

                    $json_data=array('status'=>'0','message'=>'Coupon not apply for this product');

                }// check product end



            }else{

                $json_data=array('status'=>'0','message'=>'Coupon code already used');

            } // check coupon code order count end      

        }else{

            $json_data=array('status'=>'0','message'=>'The coupon code is expired.');

        }// check coupon code expiry end    

                



    }else{



        $json_data=array('status'=>'0','message'=>'The coupon code is not valid.');

    } // coupon code is valid end

    return $json_data;

 }

 

function change_requests_by_partner($post_data)

{

    $CI     = &get_instance();

    $CI->load->model('masters_model');

    if(!empty($post_data)){        

        $result = $CI->masters_model->add_data('change_requests',$post_data);

        if($result){

            return true;

        }else{

            return false;

        }

    }else{

        return false;

    }



}



function get_previous_billing($from_date,$to_date=null,$product_id=null,$user_id=null,$min_frequency=null,$flag=true)

{

    $CI     = &get_instance();

    $CI->load->model('masters_model');



    //$pre_from_date1 = date('Y-m-d',  strtotime($from_date)); 

    $pre_from_date1 = date('Y-m-d', strtotime('-'.$min_frequency.' months', strtotime($from_date))); 



    $pre_day = date('d',  strtotime($from_date)); 

    

        $sql = "select generate_series(

        (select min(date(created_on)) from billing_details where order_product_id =".$product_id."  

        )::timestamp,

        '".$pre_from_date1."'::timestamp,  '1 month') as pre_from_date";

        $oldbilling_data = $CI->masters_model->get_query($sql);

       

        $get_pre_consum_data = array();

        if (!empty($oldbilling_data)) {

            foreach ($oldbilling_data as $key => $old_val) {

                $pre_from_date  = date('Y-m', strtotime($old_val['pre_from_date'])).'-'.$pre_day;

                $pre_to_date    = date('Y-m-d', strtotime('+'.$min_frequency.' months', strtotime($pre_from_date)));



                $get_pre_consum_data[] = array(

                    'pre_from_date' =>$pre_from_date,

                    'pre_to_date'   =>$pre_to_date

                );

            }

           return array_reverse($get_pre_consum_data);

        }else{

            return $get_pre_consum_data;

        }





    /*$pre_bill=array();

    $user_type = $CI->session->userdata('user_type');



    $pre_from_date = date('Y-m-d', strtotime('-'.$min_frequency.' months', strtotime($from_date))); 

    

          



       

    if($user_type == 'partner'){



        $sql = "SELECT l.order_id,l.billing_type,l.field_name,sum(l.field_value) as consumption

            FROM billing_details l

            join client_order_product cop on cop.id=l.order_product_id

            join products p on p.id=cop.product_id

            WHERE date(l.created_on) >= '$pre_from_date' and date(l.created_on) < '$from_date' 

            AND l.order_product_id ='$product_id'  and p.partner_id='$user_id' 

            group by l.field_name,l.billing_type,l.order_id ";



    }elseif($user_type =='client'){



        $sql = "SELECT order_id,billing_type,field_name,sum(field_value) as consumption

            FROM billing_details WHERE client_id = '$user_id' AND

            date(created_on) >= '$pre_from_date' and date(created_on) < '$from_date' 

            AND order_product_id = '$product_id' group by field_name,billing_type,order_id";

    }else{



        $sql = "SELECT l.order_id,l.billing_type,l.field_name,sum(l.field_value) as consumption

            FROM billing_details l

            join client_order_product cop on cop.id=l.order_product_id

            join products p on p.id=cop.product_id

            WHERE date(l.created_on) >= '$pre_from_date' and date(l.created_on) < '$from_date' 

            AND l.order_product_id ='$product_id'   group by l.field_name,l.billing_type,l.order_id ";

    }



    $billing_data = $CI->masters_model->get_query($sql);



    if(!empty($billing_data)){



        

        $pre_bill[] = array( 'pre_from_date'=>$pre_from_date,'pre_to_date'=>$from_date);

        

        

        $pre_billing_data = get_previous_billing($pre_from_date,null,$product_id,$user_id,$min_frequency,false);



        if(!empty(array_filter($pre_billing_data))){

              

            $pre_bill[]=array(

                'pre_from_date'=>$pre_billing_data[0]['pre_from_date'],

                'pre_to_date'=>$pre_billing_data[0]['pre_to_date'],

                

            );

        }

    }



    return $pre_bill;*/

}

/**

 * function use for download csv 

 * Created by Rahul B

 * @param       $file_name string

 * @param       $header array

 * @param       $data data

 * @return      CSV file

 */



function download_csv($file_name,Array $header,$data)

{

     ob_end_clean();

    header('Content-Encoding: UTF-8');

    header('Content-Type: text/csv; charset=utf-8' );

    header("Content-Disposition: attachment; filename=$file_name"); 

    header('Content-Transfer-Encoding: binary');

    header('Expires: 0');

    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

    header('Pragma: public');

    



    // file creation 

    $file = fopen('php://output', 'w');

    fputs( $file, "\xEF\xBB\xBF"); // UTF-8 BOM !!!!!

    fputcsv($file, $header);

    foreach ($data as $key=>$line){ 

        fputcsv($file,$line); 

    }

    fclose($file); 

    exit; 

}





/**

 * function for get products api data

 * Created by Deepak K

 * @param       $product id int

 * @param       $api_type string

 */

function get_product_api_details($data,$api_type = 'onbording')

{

    $CI     = &get_instance();

    $CI->load->model('masters_model');

    $api_deta = array();

    

    $select = array('api_type','auth_type','api_url','username','password');

    $where  =   array( 'product_id' => $data['product_id'],'api_type' =>$api_type,'is_deleted'=>false);

    $result = $CI->masters_model->get_data($select,'product_api_details', $where);

    if (!empty($result)) {

        $api_deta = $result[0];



        $base_url = $data['register_api'] ;//for multi tentant 



        if ($data['type'] == 'Instance Base') {

            $select = array('instance_url','instance_vm_ip','instance_vm_name');

            $where  = array( 'order_id' => $data['order_id']);

            $result = $CI->masters_model->get_data($select,'instance_details', $where);

            if (!empty($result)) {

                $result     = $result[0];

                $base_url   = $result['instance_url']; 

            }

        }



        $lastChar   = substr($base_url, -1);

        $base_url   = ($lastChar == '/' ? substr($base_url, 0,-1) : $base_url ) ; 



        $api_url = $api_deta['api_url'];

        $firstChar  = substr($api_url, 0, 1);

        $slugurl    = ($firstChar == '/' ? $api_url : '/'.$api_url ) ; 



        $api_deta['api_url'] = $base_url.$slugurl;

    }

    return $api_deta;

}





/**

 * get_json function use for get array from table by order id

 * @param       int $order_id

 * @return      array

 */

function get_json($order_id)

{

    $CI     = &get_instance();



    $CI->db->where('id', $order_id);

    $rest = $CI->db->get('client_orders');

    $result = $rest->result_array();

    return json_decode($result[0]['plan_details'], true);

}



/**

 * get_json function use for get array from table by order id

 * @param       int $order_id

 * @return      array

 */

function get_order_json($order_id,$start_date,$end_date,$is_real_time=false)

{

    $CI     = &get_instance();

    $CI->load->model('Orders_model');

    return $CI->Orders_model->get_order_json($order_id,$start_date,$end_date,$is_real_time);

}





// get min frequency by package data

function get_frequencys($json_data)

{

   $package_type    = $json_data['package_type'];



    $frequencys_array = array('Monthly' => 1, 'Quarterly' => 3, 'Half-yearly' => 6, 'Annually' => 12);

    $frequency = array();



    if (array_key_exists($json_data['transaction_frequency'], $frequencys_array)  && $package_type == 'custom') {

        $frequency[] = $frequencys_array[$json_data['transaction_frequency']];



    } elseif (array_key_exists($json_data['license_frequency'], $frequencys_array) && $package_type == 'custom') {

        $frequency[] = $frequencys_array[$json_data['license_frequency']];



    } elseif (array_key_exists($json_data['support_frequency'], $frequencys_array) && $package_type == 'custom') {

        $frequency[] = $frequencys_array[$json_data['support_frequency']];



    } elseif (array_key_exists($json_data['features_frequency'], $frequencys_array) && $package_type == 'custom') {

        $frequency[] = $frequencys_array[$json_data['features_frequency']];

    }



    //Free package

    //if ($package_type == 'free') {

        $min_frequency = NULL;

    //}

    

    if ($package_type == 'custom' && !empty($frequency)) {

        $min_frequency = !empty($frequency) ? min(array_filter($frequency)) : NULL; // minimum frequncy of plan

    }



    

    return $min_frequency;

}





function get_invoice_json($plan_details,$first_time = TRUE)

{

    if (!is_array($plan_details)) {

       $plan_details = json_decode($plan_details,true);

    }



    $implementation = array('billable' =>FALSE,'amount' =>0,'frequency'=>'','bill'=>'prepaid');

    $features       = array('billable' =>FALSE,'amount' =>0,'frequency'=>'','bill'=>'prepaid');

    $support        = array('billable' =>FALSE,'amount' =>0,'frequency'=>'','bill'=>'postpaid');

    $license        = array('billable' =>FALSE,'amount' =>0,'frequency'=>'','bill'=>'postpaid','data' =>array());

    $transaction    = array('billable' =>FALSE,'amount' =>0,'frequency'=>'','bill'=>'postpaid','data' =>array());



 

    if (!empty($plan_details['implementation_amount']) && $plan_details['implementation_amount'] >0 && !empty($plan_details['implementaion_frequency']) ) {

        $implementation = array(

                    'billable'  =>FALSE,

                    'amount'    =>number_format($plan_details['implementation_amount'],2),

                    'frequency' =>$plan_details['implementaion_frequency'],

                    'bill'      =>'prepaid'

                );

    }



    if (!empty($plan_details['features_amount']) && $plan_details['features_amount'] >0 && !empty($plan_details['features_frequency']) ) {

        $features = array(

                    'billable'  =>FALSE,

                    'amount'    =>number_format($plan_details['implementation_amount'],2),

                    'frequency' =>$plan_details['features_frequency'],

                    'bill'      =>'prepaid'

                );

    }







    if (!empty($plan_details['support_amount']) && $plan_details['support_amount'] >0  && !empty($plan_details['support_frequency'])) {

        $support = array(

                    'billable'  =>FALSE,

                    'amount'    =>number_format($plan_details['support_amount'],2),

                    'frequency' =>$plan_details['support_frequency'],

                    'bill'      =>'postpaid'

                );

    }



    



    if (!empty($plan_details['package_transaction_list'])  && !empty($plan_details['transaction_frequency']) ) {



        $tran_list  = json_decode($plan_details['package_transaction_list'],true);

        $data       = array();

        foreach ($tran_list as $tran) {

            $data[$tran['definition']] =array('rate' => 0,'qty'=>0) ;

        }

        $transaction = array(

                    'billable'  =>FALSE,

                    'amount'    =>0,

                    'frequency' =>$plan_details['transaction_frequency'],

                    'bill'      =>'postpaid',

                    'data'      =>$data);

    }



    if (!empty($plan_details['package_license_list']) && !empty($plan_details['license_frequency']) ) {



        $lic_list   = json_decode($plan_details['package_license_list'],true);

        $data       = array();

        foreach ($lic_list as $lic) {

            $li_name =  get_name_by_id('package_license_element', 'license_name', 

                array('id' => $lic['license_id']));

            $data[$li_name] =array('rate' => 0,'qty'=>0) ;

        }



        $license = array(

                    'billable'  =>FALSE,

                    'amount'    =>0,

                    'frequency' =>$plan_details['license_frequency'],

                    'bill'      =>'postpaid',

                    'data'      =>$data);

    }

    

    //First time bill only

    if ($first_time) {

        $implementation['billable'] = TRUE;

        $features['billable'] = TRUE;

    }



    //recuring or consuption base bill only

    if (!$first_time) {

        $transaction['billable'] = TRUE;

        $license['billable'] = TRUE;

        $support['billable'] = TRUE;

    }





    $json = array(

        'implementation' =>$implementation,

        'support'        =>$support,

        'transaction'    =>$transaction,

        'license'        =>$license,

        'features'       =>$features,

      );

    return json_encode($json);

}







function get_top_five_products($id='',$cat_id = '')

{

        $CI     = &get_instance();

        $sub = '';

        $sub1 = '';

        if (!empty($id)) {

            $sub = ' cross join unnest(string_to_array(category_id, \',\')) AS k(cat_id)';

           $sub1 = "trim(cat_id) = ANY ( string_to_array('$id', ',') )  AND ";

        }

        

        $sql = "SELECT p.product_name as product_name,p.id as product_id  from products p $sub 

        left join users u on u.user_id=p.partner_id

        WHERE  $sub1 p.status = '7' AND  p.is_deleted = FALSE AND u.is_active = true ";



        if (!empty($cat_id) && is_int($cat_id)) {

            $sql .= " AND p.sub_category_id=$cat_id ";

        }



        $sql .= " group by p.id ORDER BY p.product_kind,p.product_name ASC  ";

        

        $sql .= " limit  4 ";

        

        $rest = $CI->db->query($sql);

        return $rest->result_array();

    }



    function text_clean($search_key)

    {

        $search_key = html_entity_decode($search_key);

        $search_key = stripcslashes ($search_key);

        $search_key = addslashes ($search_key);

        $search_key = htmlentities($search_key);

        return $search_key = strip_tags($search_key);

    }



    function get_config_data($key,$type="text",$val1="description",$val2="logo",$val3="mob_icon")

    {

        $CI = &get_instance();

        $sql = "SELECT * from config_master 

            WHERE  is_active = true  AND  is_deleted = false AND key_fields = '".$key."'";



        $rest = $CI->db->query($sql);

        $data = $rest->result_array(); 



        if($type=="text"){

            return $data[0][$val1];

        }else{

            return $data[0][$val2];

        }

    }



    function get_config_settings($key)

    {

        $CI = &get_instance();

        $fields = ' name, key_fields, logo, mob_icon, created_on, is_active, created_by_id, description, is_whitelablel ';

        $sql = "SELECT ".$fields." from config_master WHERE  is_active = true  AND  is_deleted = false AND key_fields = '".$key."'";



        $rest = $CI->db->query($sql);

        $data = $rest->row_array();

        return $data;

    }



    function get_distance($lat = '19.9959911', $long = '73.7470536', $lat_column_name=null, $long_column_name=null){

        $longitude      = (float) $long;

        $latitude       = (float) $lat;

        $lat_name       = !empty($lat_column_name) ? $lat_column_name : 'lat';

        $long_name      = !empty($long_column_name) ? $long_column_name : 'long';

        // $minimum_radius = $this->get_config_settings('minimum_radius');



        $distance = ' (6371 * acos (cos (radians('.$latitude.'))* cos(radians('.$lat_name.'))* cos( radians('.$longitude.') - radians('.$long_name.') )+ sin (radians('.$latitude.') )* sin(radians('.$lat_name.')))) ';



        return $distance;

    }



    function get_user_address($lat,$lng){

        $url = "https://nominatim.openstreetmap.org/reverse?lat=$lat&lon=$lng&format=json";



        // return $url;



        // Make a GET request to the API

        $response = file_get_contents($url);



        // Decode the JSON response

        $json = json_decode($response, true);

        return $json;



        // Extract the formatted address from the response

        $address = $json['display_name'];



        return $address;

    }



    function generate_api_token($data=[]){

        $CI = &get_instance();

        $encryption_hash    = $CI->config->item('encryption_key');



        if(!empty($data)){

            $encrpt_string  = implode('/', $data);

            // $encryption_key = encrypt($encrpt_string, $encryption_hash);

            $encryption_key = encryptString($encrpt_string, 'encrypt', false, $encryption_hash);

            // $decryption_key = decrypt($encryption_key, $encryption_hash);

            // $res = array(

            //     'encryption_key' => $encryption_key,

            //     'decryption_key' => $decryption_key,

            // );

            return $encryption_key;

        }

    }



    function encryptString($string, $action, $baseIP = 'false', $extraKey = ''){

        global $flag;

    

        $encryptedIP = '';

    

        if($baseIP){

            $encryptedIP = encryptString($_SERVER['REMOTE_ADDR'], 'encrypt', false);

        }

    

        $output = false;

    

        $encrypt_method = "AES-256-CBC";

        $secret_key = $flag['2nd-encrypt-key'].$encryptedIP.'-'.$extraKey;

        $secret_iv = $flag['2nd-encrypt-secret'].$encryptedIP.'-'.$extraKey;

    

        $key = hash('sha256', $secret_key);

        $iv = substr(hash('sha256', $secret_iv), 0, 16);

    

        $output;

    

        if($action == 'encrypt'){

            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);

            $output = base64_encode($output);

            //replace equal signs with char that hopefully won't show up

            $output = str_replace('=', '[equal]', $output);

        }else if($action == 'decrypt'){

            //put back equal signs where your custom var is

            $setString = str_replace('[equal]', '=', $string);

            $output = openssl_decrypt(base64_decode($setString), $encrypt_method, $key, 0, $iv);

        }

    

        return $output;

    }



	function get_client_detail($clinet_id=null,$check_device_id=0)

    {

        $CI = &get_instance();

		if(!empty($clinet_id)){

			$sql = "SELECT * from client 

            WHERE  is_active = true  AND  is_deleted = false AND id = '".$clinet_id."'";

            if($check_device_id == 1) $sql .= " AND (device_id IS NOT NULL AND device_id!= 'null') ";

			$rest = $CI->db->query($sql);

			$data = $rest->row_array(); 

		} else {

			$data = [];

		}



        return $data;

    }

    function get_sms_template($text,$replace_array=array())

    {

        if(!empty($replace_array['body'])){

            $body=strtr($text, $replace_array['body']);

        }else{

            $body=$text;

        } 

        return $body;

    }



	function show_rating($id, $user_type='buyer')

	{

		$CI = &get_instance();



		if ($id != '') {

			// 1:'happy'

			// 2:'average'

			// 3:'poor'

			if($user_type == 'buyer'){

				$select = ' buyer_id ';

			}



			if($user_type == 'seller'){

				$select = ' seller_id ';

			}



			$sql = "SELECT $select,

			COUNT(CASE WHEN rating_id = '1' THEN 1 END) as happy_count,

			COUNT(CASE WHEN rating_id = '2' THEN 1 END) as average_count,

			COUNT(CASE WHEN rating_id = '3' THEN 1 END) as poor_count

			FROM trade_product_rating

			WHERE is_deleted = false ";

			if($user_type == 'buyer'){

				$sql .= " AND buyer_id = $id";

				$sql .= " AND trade_product_id = 0 ";

			}



			if($user_type == 'seller'){

				$sql .= " AND seller_id = $id";

				$sql .= " AND trade_product_id != 0 ";

			}

			$sql .= " GROUP BY $select";



			$row = $CI->db->query($sql);

			$result	= $row->row_array(); 

			if(!empty($result) && count($result) > 0){					 

				return $result;

			}else{

				return ['happy_count' => 0, 'average_count' => 0, 'poor_count' => 0];

			}

		} else {

			return ['happy_count' => 0, 'average_count' => 0, 'poor_count' => 0];

		}

	}

    function get_notification_detail($map_key='',$usertype='seller',$lang_key='en')

    {

        $CI = &get_instance();

		if(!empty($map_key)){

			$sql = "SELECT title,notification_text from notification_master 

            WHERE  is_active = true  AND  is_deleted = false AND map_key = '".$map_key."' AND user_type='".$usertype."' AND lang_key='".$lang_key."'";

			$rest = $CI->db->query($sql);

			$data = $rest->row_array(); 

            if(empty($data)){

                $sql1 = "SELECT title,notification_text from notification_master 

                WHERE  is_active = true  AND  is_deleted = false AND map_key = '".$map_key."' AND user_type='".$usertype."' AND lang_key='en'";

                $rest1 = $CI->db->query($sql1);

                $data = $rest1->row_array(); 

            }

		} else {

			$data = [];

		}



        return $data;

    }

    function  add_notification_detail($title, $messages,$user_id,$mapkey,$reference_id,$other_details){

        $CI = &get_instance();

        //$msg = '"'.$messages.'"';

        $loggedin_user = ($CI->session->userdata('user_id'))?$CI->session->userdata('user_id'):'9999999';

        $sql = "INSERT INTO notifications_table (reference_id,title,message,map_key,created_on,created_by_id,other_details) VALUES ('".$reference_id."','".$title."','".$messages."','".$mapkey."',CURRENT_TIMESTAMP,$loggedin_user,'".$other_details."') RETURNING id";

        

        $res1 = $CI->db->query($sql);

         if($res1){

             $row = $res1->row_array(); ;

             $last_inserted_id = $row['id'];

             if (!empty($user_id) && is_array($user_id)) {

                 // Loop through each user_id and insert into user_notifications_table

                 foreach ($user_id as $user) {

                     $sql1 = "INSERT INTO user_notifications_table (user_id, notification_id, is_read, created_on, created_by_id) VALUES ($user, $last_inserted_id, '0', CURRENT_TIMESTAMP, $loggedin_user)";

                     $res = $CI->db->query($sql1); 

                    //  if (!$res) {

                    //      echo "Error while inserting user notification ";

                    //      // Handle error as needed

                    //  }else{

                    //      echo "Inserted successfully";

                    //  }

                 }

             }    

         }

     } 

    