<?php
require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/UserDataStore.php");
$UDS = UserDataStore::getInstance();
$user = $UDS->FindByUserName("uppcbuser");
session_start();
$_SESSION["userlogged"] = $user;
header("Location:cpcbReportMultiStation.php");

?>
