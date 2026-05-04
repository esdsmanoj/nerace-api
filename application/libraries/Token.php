<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Token {
    public function ganerate_key()
	{
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        for ($i = 0; $i < 20; $i++) {
            $randstring .= $characters[rand(0, strlen($characters))];
        }
        return $randstring;
    }

    public function get_token($data)
	{
        $jwt=new JWT;

        $jwtSecrateKye = 'apmcsecratekey';
        $token = $jwt->encode($data, $jwtSecrateKye, 'HS256');
        return $token;
	}

    public function decode_token()
	{
        $token =  $this->uri->segment(4);
        $jwt=new JWT;

        $jwtSecrateKye = 'apmcsecratekey';
        $decoded_token = $jwt->decode($token,$jwtSecrateKye,'HS256');
        // echo '<pre>';print_r($decoded_token);
        
        return $jwt->jsonEncode($decoded_token);
	}
}