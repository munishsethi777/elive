<?php
require_once('../IConstants.inc');
require_once($ConstantsArray['dbServerUrl']. "/Utils/DataPullAPIUtil.php");
require_once($ConstantsArray['dbServerUrl']. "/DataStoreMgr/UserDataStore.php");
require_once($ConstantsArray['dbServerUrl']. "/DataStoreMgr/LocationDataStore.php");
require_once($ConstantsArray ['dbServerUrl'] . "log4php/Logger.php");
Logger::configure ( $ConstantsArray ['dbServerUrl'] . "log4php/log4php.xml" );
$logger = Logger::getLogger ( "DataFetchAPILogger" );
$logger->info("Calling Data Fetch Api");
$call = ""; 
if(isset($_REQUEST['call'])){
	$call = $_REQUEST['call'];
}
$fullArray=array();
$userName = "";
$password = "";
$isAuthorized = false;
$user = null;
$token = null;
$headers = apache_request_headers();
if(isset($headers['Authorization'])){
	$token = $headers['Authorization'];
	$token = str_replace("Basic ", "", $token);
	$token = base64_decode($token);
	$authData = explode(":",$token);
	$userName = $authData[0];
	$password = $authData[1];
	$userDataStore = UserDataStore::getInstance();
	$user = $userDataStore->validateUserLogin($userName, $password);
	if(!empty($user)){
		$isAuthorized = true;
	}
	
}
if(!$isAuthorized){
    $errorMessage = "Request Failed due error - Unauthorized Action";
    echo $errorMessage;
	$logger->error($errorMessage);
	return;
}
if($call == "GetStationData"){
   	try{
		$fromDate = ""; 
		$folderSeq = 0;
		if(isset($_REQUEST['fromDate'])){
			$fromDate = $_REQUEST['fromDate'];
		}
		if(isset($_REQUEST['folderSeq'])){
			$folderSeq = $_REQUEST['folderSeq'];
		}
		$apiUtil = DataPullAPIUtil::getInstance();
		$data = $apiUtil->getData($fromDate, $folderSeq,$user);
		echo json_encode($data);
	}catch (Exception $e){
		$message = $e->getMessage();
		echo "Request Failed due to error - " . $message;
		$logger->error($message,$e);
	}
}
?>
