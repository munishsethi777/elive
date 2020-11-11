<?php
$token = null;
$headers = apache_request_headers();
var_dump($headers);
return;
if(isset($headers['Authorization'])){
	$token = $headers['Authorization'];
}
$params =  var_dump($_POST);
$tt = base64_decode($token);
echo $params;
return;