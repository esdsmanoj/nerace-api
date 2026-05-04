<?php
defined('BASEPATH') or exit('No direct script access allowed');

error_reporting(E_ERROR | E_PARSE);
//error_reporting(E_ERROR | E_PARSE);
//error_reporting(E_ALL);

//require APPPATH . 'libraries/RestController.php';

//use chriskacerguis\RestServer\RestController;

class Commodity extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('mongo_db');
    }

    public function index() {
        // Access the MongoDB library's methods
        $data = $this->mongo_db->get('famrut_commodity');
        // ...
    }
}
