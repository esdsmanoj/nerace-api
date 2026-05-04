<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(E_ERROR | E_PARSE);

class Npks extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('log_helper');
        $this->load->model('Masters_model');
    }
    
    public function index()
	{
        $selected_lang  = $_GET['lang'];
        if ($selected_lang == 'mr') {
            $lang_folder = "marathi";
        } elseif ($selected_lang == 'hi') {
            $lang_folder = "hindi";
        } else {
            $lang_folder = "english";
        }
        
        $this->lang->load('npks', $lang_folder);
        
        // $ratio          = $_GET['ratio'];
        // $Combination    = $_GET['Combination'];
        // $Urea           = $_GET['Urea'];
        // $MOP            = $_GET['MOP'];
        // $Bensulf        = $_GET['Bensulf'];
        // $crop_name      = $_GET['crop_name'];
        // $crop_id        = $_GET['crop_id'];
        // $season         = $_GET['season'];
        // $size           = $_GET['size'];
        // $unit           = $_GET['unit'];

        $data = [];
        $data['title'] = lang('Schedule');
        $data['get_data'] = $_GET;
        
        $this->load->view('npks/index', $data);
	}
}