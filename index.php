<?php
include_once('config.php');
include_once('api.php');

$API = new Api();

//Register Token
$data = array(
    'client_id' => CLIENT_ID,
    'email' => EMAIL,
    'name' => NAME
);
print_r($API->curl_connect('register', 'POST', $data));