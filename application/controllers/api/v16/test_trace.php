<?php

// URL to send the TRACE request to
$url = 'https://api.nerace.in/api/v16/users/custom_config';

// Initialize cURL session
$ch = curl_init($url);
// Set cURL options
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');//TRACE
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'X-API-KEY: CODEX@123',
    'domain: nerace',
    'appname: nerace',
    'Cookie: ci_session=2cb7a2c1abfe73cf492a7e143c99be5eb916a9fc'
));

// Execute cURL session and capture the response
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
}

// Close cURL session
curl_close($ch);

// Display the response
echo $response;

?>