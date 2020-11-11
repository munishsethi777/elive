<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
require_once('IConstants.inc'); 
require_once($ConstantsArray['dbServerUrl'] . "Utils/CPCBService.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils/MailerUtils.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/ConfigurationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] .'/log4php/Logger.php');
Logger::configure('/home/envirote/public_html/app/log4php/log4php.xml');
$logger = Logger::getLogger("CronAPIDailyLogger");

$cpcbLogger = Logger::getLogger("CronCPCBApiDailyAppender");

$LDS = LocationDataStore::getInstance();
$FDS = FolderDataStore::getInstance();
$eliveLocations = "15,12,18,16,5,157,"; //elive stations
$eliveLocations .= "33,22,24,25,26,27,28,29,30,31,32,34,36,45,46,47,53,35,181"; //FLOW EXTRA PARAM ,46 - Status 0 //ezone stations

$bhoomiLocations = "0";
//$eliveLocations = "18";

       
//Ezone Stations
//Stopped Station --  33
$vendorLocations = array("elive"=>$eliveLocations,"Bhoomi"=>$bhoomiLocations);
$cc = ConfigurationDataStore::getInstance();
$errMessage = "";
$COUNT = 3;
$delayLocations = array(15,19,52,5,157,27);
$cpcbSerive = CPCBService::getInstance(true);
foreach($vendorLocations as $vendorKey=>$value){
    try{
        	$locations = $LDS->FindBySeqs($value);
			foreach($locations as $location){
	            $locationSeq = $location->getSeq();
	            $vendorCode = $vendorKey;
	            $counter = 1;
	            if(in_array($locationSeq, $delayLocations)){
	            	$counter = $COUNT;
	            }
	            for($i=0;$i<$counter;$i++){
	            	$cpcbSerive =   CPCBService::getInstance(true);
	            	$detail = $cpcbSerive->getCPCBJson($locationSeq,$vendorCode);
	            	$data = $detail["data"];
                   // var_dump($data);
	            	$hasPendingCpcbData = $detail["hasPendingCpcbData"];
	            	$hoursDiff = $detail['hoursDiff'];
	            	$updatedTill = $detail['updatedTill'];
	            	if(empty($data)){
                        //if (is_array($hasPendingCpcbData) || is_object($hasPendingCpcbData)){
                            foreach ($hasPendingCpcbData as $key=>$value){
                                $hasPending = $hasPendingCpcbData[$key];
                                $FDS->updateHasPendingCpcbData($hasPending, $key);
                                echo("<br>\r\n Updated haspending flag as $hasPending for station seq ". $key. "<br>");
                            }
                            //echo("No new data found for location ". $location->getLocationName() . "<br>\r\n");
                            continue;
                        //}
                    }
	            	if(!empty($data)){
	            		foreach($hoursDiff as $key=>$value){
	            			if ($value["hoursDiff"] >3 && $value["hoursDiff"] <12){
                                echo("<br> Delay Happened for Hrs:". $value["hoursDiff"]);
	            				//MailerUtils::sendDelayNotification($value["hoursDiff"],$key,$value['stationName'], null);
	            			}
	            		}
                        foreach($data as $key=>$value){
                            unset($data[$key]["hoursDiff"]);
                            unset($data[$key]["updatedTill"]);
                        }
	            	}
                    //unset($data["hoursDiff"]);
                    //unset($data["updatedTill"]);
                    
	            	$data = json_encode($data);
	            	//echo("<br>Request :- ".$data);
	            	$apiKey = $cc->getCPCBApiKey($vendorCode);
	            	$auth_header = 'Authorization: Basic ' . base64_encode($apiKey);
	            	
	            	$ch = curl_init();
                    $url = "http://cpcbrtdms.nic.in/v1.0/industry/". $locationSeq ."/data";
	            	echo("<br> \r\n URL :- " . $url . "<br>");
	            	echo($vendorCode ." - " .$apiKey . "<br>\r\n");
	            	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/Json', $auth_header));
	            	curl_setopt($ch, CURLOPT_POST, TRUE);
	            	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	            	curl_setopt($ch, CURLOPT_URL, $url);
	            	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	            	//echo("<br>CURLSTART". date("Y-m-d H:i:s"));
                    $result = curl_exec($ch);
	            	if(!empty(curl_error($ch))){
	            		echo 'error:' . curl_error($ch);
	            	}
                    //echo("<br>CURLEND". date("Y-m-d H:i:s"));
                    
                    $resultJson = json_decode($result);
                    $msg = $resultJson->msg;
	            	$status = $resultJson->status;
	            	curl_close($ch);
	            	echo("Response for New URL- ".$result);
	            	$reqLog = $location->getLocationName() ."(". $locationSeq .") - Request: ". $data;
	            	$resLog = $location->getLocationName() ."(". $locationSeq .") - Response: ". $result;
	            	$log = $reqLog  . $resLog;
                    if($vendorCode =="Bhoomi"){
                        $logger->info($log);
                    }
                    if($locationSeq == 5){
                    	$cpcbLogger->info($log);
                    }
	            	$hasError = false;
	            	if($status == 1 && $msg == "success"){
	            		$lastSyncedDates = $detail["lastsynceddates"];
	            		foreach ($lastSyncedDates as $key=>$value){
	            			$FDS->updateLastSyncedSeq($value, $key);
	            			echo("<br>\r\n Updated ". $location->getLocationName() ." with dated ". $updatedTill[$key] . "<br>");
	            			if($vendorCode =="Bhoomi"){
	            			    $logger->info($location->getLocationName() ." is now uploaded till ". $updatedTill[$key]);
	            			}
	            			if($locationSeq == 5){
	            				$cpcbLogger->info($location->getLocationName() ." is now uploaded till ". $updatedTill[$key]);
	            			}
	            		}
	            	}else{
	            		$locationName = $location->getLocationName();
	            		$request = "Location Name :- ".$locationName . "<br>";
	            		$request .= "<br>Request :- ".$data . "<br>";
	            		$response = "<br>Response :- ".$result . "<br>";
	            		$message = $request . $response;
	            		$subject = "Error During Send data to CPCB for location :- " . $locationSeq;
	            		$errMessage .= $message;
	            		$hasError = true;
	            		
	            	}
	            	if(!$hasError){
		            	foreach ($hasPendingCpcbData as $key=>$value){
		            		$hasPending = $hasPendingCpcbData[$key];
		            		$FDS->updateHasPendingCpcbData($hasPending, $key);
		            		echo("<br>\r\n Updated haspending flag as $hasPending for station seq ". $key. "<br>");
		            	}
	            	}
	            }    
       	 }        
    }catch(Exception $e){    	
    	echo("<br>\r\n Error occured Cron API :- ". $e->getMessage());
        MailerUtils::sendError($e->getMessage(), $subject,false);
    }
    
}
    if($errMessage != ""){
        //MailerUtils::sendError($errMessage, "Error During Send data to CPCB",false);
    }
?>
