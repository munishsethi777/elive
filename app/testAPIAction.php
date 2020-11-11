<?php
$data = array(
		"call" => "GetStationData",
		"fromDate" => "20-11-2018 08:20:43",
		"folderSeq" => "39"
);
$ch = curl_init();
$username = "ltuser";
$password = "system";
$auth_header = 'Authorization:' . $username . ':' . $password;
curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth_header));
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_URL, "http://www.envirotechlive.com/app/webservice.php");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
$result = curl_exec($ch);
echo curl_error($crl);
echo($result);
curl_close($ch);