<?php defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/RestController.php';

class Cors extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->config('cors');
    }

    public function index_options()
    {
        // Preflight response for CORS
        $this->response(null, REST_Controller::HTTP_OK);
    }
}