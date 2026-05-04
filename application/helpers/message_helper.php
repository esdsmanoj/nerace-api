<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
	Class Name 		: 	Message Helper	
	Created By		: 	Bhagwan Sahane
	Created Date 	: 	27-03-2019	
	Updated By		:	Bhagwan Sahane
	Updated Date	:	27-03-2019
*/

/**
   * btn_edit_new function for use to display button.
   * Create Deepak kanmahale
   * @param       string $class | id $data_id | string $tooltip
   * @return      mixed|string
   */
function btn_edit_new($class='',$data_id="",$tooltip="")
{
	return  '<button type="button" class="btn btn-icon-toggle '.$class.'" data-toggle="tooltip" data-placement="top" data-original-title="'.$tooltip.'" data-id="'.$data_id.'"><i class="fa fa-pencil"></i></button>';
}

/**
   * btn_delete_new function for use to display button.
   * Create Deepak kanmahale
   * @param       string $class | id $data_id | string $tooltip
   * @return      mixed|string
   */
function btn_delete_new($class='',$data_id="",$tooltip="")
{
	return '<button type="button" class="btn btn-icon-toggle '.$class.'" data-toggle="tooltip" data-placement="top" data-original-title="'.$tooltip.'" data-id="'.$data_id.'"><i class="fa fa-trash-o"></i></button>';
}

/**
 * button_action function for get button html.
 * create Rahul B
 * @param         string  $url input string
 * @param         string  $class input string
 * @param         int  	  $data_id input intiger
 * @param         string  $tooltip input string
 * @param         string  $fa_fa input string
 * @return     	  string 
 */
function button_action($url="", $class='',$data_id="", $tooltip="", $fa_fa='fa-pencil')
{
	$url1=!empty($url)?$url:"javascript:void(0);";
	return '<a href="'.$url1.'" class="btn btn-icon-toggle '.$class.'" data-toggle="tooltip" data-placement="top" data-original-title="'.$tooltip.'" data-id="'.$data_id.'"><i class="fa '.$fa_fa.'"></i></a>';

}
?>