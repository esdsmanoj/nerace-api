<?php defined('BASEPATH') or exit('No direct script access allowed');

 
// $config['allowed_cors_origins'] = ['http://localhost:5173']; // Replace with your frontend URL

// $config['allowed_cors_headers'] = ['Origin', 'Authorization', 'Content-Type', 'X-Requested-With', 'Access-Control-Request-Method', 'Accept'];
// // 'Origin', 'X-Requested-With', 'Content-Type', 'Accept,Access-Control-Request-Method'
// $config['allowed_cors_methods'] = ['GET', 'POST', 'OPTIONS'];


$config['cors'] = [
    'allowed_origins' => ['*'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
    'allowed_headers' => ['Authorization', 'Content-Type'],
    'max_age' => 3600,
];