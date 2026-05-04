<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
	Class Name 		: 	NPKS Helper	
	Created By		: 	Akash Wagh
	Created Date 	: 	08-06-2023	
*/
function calculation_formula(){
    $calculation_formula = [
        [
            "10:26:26" => "3.84*{{P}}",
            "Urea" => "(({{N}})-((3.84*{{P}})*0.10))*2.17",
            "MOP" => "(({{K}})-((2.857*{{P}})*0.08))*1.66",
            "Bensulf" => "1.11*{{S}}"
        ],
        [
            "DAP" => "2.17*{{P}}",
            "Urea" => "({{N}}-(({{P}}*2.17)*0.18))*2.17",
            "MOP" => "1.66*{{K}}",
            "Bensulf" => "1.11*{{S}}"
        ],
        [
            "SSP" => "6.25*{{P}}",
            "Urea" => "2.17*{{N}}",
            "MOP" => "1.66*{{K}}",
            "Bensulf" => "({{K}}-(({{P}}*6.25)*0.11))*1.11"
        ],
        [
            "14:35:14" => "2.857*{{P}}",
            "Urea" => "(({{N}})-((2.857*{{P}})*0.14))*2.17",
            "MOP" => "(({{K}})-((2.857*{{P}})*0.14))*1.66",
            "Bensulf" => "1.11*{{S}}"
        ],
        [
            "08:24:24" => "4.16*{{P}}",
            "Urea" => "(({{N}})-((4.16*{{P}})*0.08))*2.17",
            "MOP" => "(({{K}})-((2.857*{{P}})*0.08))*1.66",
            "Bensulf" => "1.11*{{S}}"
        ],
        [
            "10:34:00" => "2.94*{{P}}",
            "Urea" => "(({{N}})-((2.94*{{P}})*0.1))*2.17",
            "MOP" => "{{K}}*1.66",
            "Bensulf" => "1.11*{{S}}"
        ],
        [
            "14:28:14" => "3.57*{{P}}",
            "Urea" => "(({{N}})-((3.57*{{P}})*0.14))*2.17",
            "MOP" => "(({{K}})-((3.57*{{P}})*0.14))*1.66",
            "Bensulf" => "1.11*{{S}}"
        ],
        [
            "24:24:00" => "4.166*{{P}}",
            "Urea" => "({{N}}-{{P}})*2.17",
            "MOP" => "{{K}}*1.66",
            "Bensulf" => "1.11*{{S}}"
        ],
        [
            "20:20:00" => "5*{{P}}",
            "Urea" => "({{N}}-{{P}})*2.17",
            "MOP" => "{{K}}*1.66",
            "Bensulf" => "1.11*{{S}}"
        ],
        [
            "15:15:15" => "6.66*P",
            "Urea" => "({{N}}-{{P}})*2.17",
            "MOP" => "({{K}}-{{P}})*1.66",
            "Bensulf" => "1.11*{{S}}"
        ],
        [
            "16:16:16" => "6.25*{{P}}",
            "Urea" => "({{N}}-{{P}})*2.17",
            "MOP" => "({{K}}-{{P}})*1.66",
            "Bensulf" => "1.11*{{S}}"
        ],
        [
            "17:17:17" => "5.88*{{P}}",
            "Urea" => "({{N}}-{{P}})*2.17",
            "MOP" => "({{K}}-{{P}})*1.66",
            "Bensulf" => "1.11*{{S}}"
        ]
    ];

    return $calculation_formula;
}

function execute_calculations($data=[]){
    if(!empty($data) && count($data) > 0){
        $npks_data = [];

        // get calculation formula from calculation_formula() function
        $converted_to_json = json_encode(calculation_formula());

        // get dynamic npks values
        $n = $data['n']; $p = $data['p']; $k = $data['k']; $s = $data['s'];
        
        // replace values of npks in formula
        $find_str = ['{{N}}', '{{P}}', '{{K}}', '{{S}}'];
        $replacement_str = [$n, $p, $k, $s];
        $replace_string = str_replace($find_str,$replacement_str,$converted_to_json);

        $npks_final_formula = json_decode($replace_string, true);
        $combination_arr = ['10:26:26', 'DAP', 'SSP', '14:35:14', '08:24:24', '10:34:00', '14:28:14', '24:24:00', '20:20:00', '15:15:15', '16:16:16', '17:17:17'];
        foreach($npks_final_formula as $main_key => $values){
            $line = 1;
            $total_cost = 0;
            $get_url = [];
            foreach($values as $key=>$val){
                $calculations = eval("return $val;");
                // $calculations = number_format($calculations, 2);
                $calculations = round($calculations, 2);
                if($calculations <= 0){
                    $calculations = 0;
                    $base_price = bag_price(0, $key);
                } else {
                    $base_price = bag_price(round($calculations), $key);
                }
                // $cost_bensulf['bensulf_total_cost_bags']      = $base_price['bags'];
                // $cost_bensulf['bensulf_total_cost_bag_price'] = $base_price['bag_price'];
                $total_cost += $base_price['cost'];

                $line_content = '';
                $line_content .= $key;
                $line_content .= ', ' . $calculations . 'Kg';
                $line_content .= ', ' . $base_price['bags'];
                $line_content .= ', ₹' . $base_price['cost'];

                $map_key = in_array($key, $combination_arr) ? 'Combination' : $key;
                
                if($map_key == 'Combination' ){
                    $get_url["ratio"] = $replace_string = str_replace(':','_',$key);
                }

                $get_url[$map_key] =  $calculations;

                if($key == 'Bensulf'){
                    if($data['crop_id'] == 2){
                        $npks_data[$main_key]['line'.$line++] = $line_content;
                    }
                } else {
                    $npks_data[$main_key]['line'.$line++] = $line_content;
                }
            }

            $get_url['lang']        =  $data['lang'] ? trim($data['lang']) : 'en';
            $get_url['crop_name']   =  $data['crop_name'] ? trim($data['crop_name']) : '';
            $get_url['crop_id']     =  $data['crop_id'] ? trim($data['crop_id']) : '';
            $get_url['season']      =  $data['season'] ? trim($data['season']) : 'Rabi';
            $get_url['size']        =  $data['size'] ? trim($data['size']) : '1';
            $get_url['unit']        =  $data['unit'] ? trim($data['unit']) : 'hectare';

            // print_r($get_url);exit;
            $npks_data[$main_key]['Total'] = '₹'.$total_cost;
            if($data['crop_id'] == 2){
                $npks_data[$main_key]['url'] = base_url().'npks/index?'.http_build_query($get_url);
            }
        }
    }
    return $npks_data;
}


function bag_price($kg, $frt_name)
{
    // $price_array = array("dap" => 1200, "urea" => 276, "mop" => 980, "ssp" => 420, "10:26:26" => 1175, "12:32:16" => 1185, "15:15:15" => 739.50, "16:16:16" => 368.70, "17:17:17" => 927, "20:20:00" => 850, "00:52:34" => 115, "bensulf" => 1250);


    $price_array = array(
        "10:26:26"  => 1175, // rcf 50kg
        "urea"      => 266, // rcf 45kg
        "mop"       => 1750, // 50kg
        "bensulf"   => 750, // 10kg
        "dap"       => 1350, // 50kg
        "ssp"       => 650, // 50kg
        "14:35:14" => 1500, // 50kg
        "08:24:24" => 1750, // 40kg
        "10:34:00" => 0,
        "14:28:14" => 1790, // 50kg
        "15:15:15" => 1470, // 50kg
        "16:16:16" => 1450, // IPL 50kg
        "17:17:17" => 1250, // IPL 50kg
        "12:32:16" => 1185, // 50kg - old
        "20:20:00" => 850,  // 50kg - old
        "00:52:34" => 115,  // 50kg - old
    );
    
    if (strtolower($frt_name) == "bensulf") {
        $bags   = round($kg / 10);
        $new_kg = $bags * 10;
    } elseif (strtolower($frt_name) == "urea") {
        $bags   = round($kg / 45);
        $new_kg = $bags * 45;
    } elseif (strtolower($frt_name) == "08:24:24") {
        $bags   = round($kg / 40);
        $new_kg = $bags * 40;
    } else {
        $bags   = round($kg / 50);
        $new_kg = $bags * 50;
    }

    $diff_str = "";

    if ($new_kg < $kg) {
        $diff_str = "+ " . ($kg - $new_kg);
    } elseif ($new_kg > $kg) {
        $diff_str = " - " . ($new_kg - $kg);
    }

    $cost = $price_array[strtolower($frt_name)] * $bags;

    if ($diff_str != '') {
        $data_cal['bags'] = "  " . $bags . " Bags (" . $diff_str . "Kg)";
    } else {
        $data_cal['bags'] = "  " . $bags . " Bags";
    }

    $data_cal['bag_price'] = "₹ " . $price_array[strtolower($frt_name)] * $bags;
    $data_cal['cost']      = $cost;
    return $data_cal;
}