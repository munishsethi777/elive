<?php
$data = array(
		"call" => "GetStationData",
		"fromDate" => "20-12-2018 00:00:00",
		"folderSeq" => "283"
);
$ch = curl_init();
$username = "";
$password = "";
$auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $password);
curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth_header));
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_URL, "http://www.envirotechlive.com/app/Actions/DataPullAPIAction.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
$result = curl_exec($ch);
echo($result);
curl_close($ch);
