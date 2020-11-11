<?php
$m2mSites = array("siteCode"=>array(0=>"ASPL_CG_DUST1"),"lastSyncDate"=>array(0=>"30-06-2018 09:10:01"));
$data = json_encode($m2mSites);

var_dump("<br>m2msites". $data . "<br>");
$ch = curl_init();
$url = "http://in-1.m2mlogger.com/Services/EF/Api/SyncTypeA";
echo "<br/>Api url - " .  $url . "<br/>";
//For ezone call
$username = "api_user@bhoomiltd.com"; //$CDS->getConfiguration(ConfigurationDataStore::$m2mWebApiUsername);
$password = "api@1234"; //$CDS->getConfiguration(ConfigurationDataStore::$m2mWebApiPassword);
$auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $password);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/Json', $auth_header));
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
$result = curl_exec($ch);
echo("Result - :". $result);
curl_close($ch);