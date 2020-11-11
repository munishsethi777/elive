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
    
    $LDS = LocationDataStore::getInstance();
    $FDS = FolderDataStore::getInstance();
    $bhoomiLocations = "19,38,44,50,52,54,55,56,60,64,65,66,69,70,71,72,20,75,73,74,76,77,78,98,117,130,138,80,116,131,151,90,139,150,149,148,152,153,162,165,178"; //149
    //$bhoomiLocations = "165";
    $eliveLocations = "0";
    
    
    //Ezone Stations
    //Stopped Station --  33
    $vendorLocations = array("Bhoomi"=>$bhoomiLocations);
    $cc = ConfigurationDataStore::getInstance();
    $errMessage = "";
    $COUNT = 3;
    $delayLocations = array(19,52,151,71,64,20,60,74,66,50,55,56,44,149,165);
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
                    $hasPendingCpcbData = $detail["hasPendingCpcbData"];
                    $hoursDiff = $detail['hoursDiff'];
                    $updatedTill = $detail['updatedTill'];
                    if(empty($data)){
                       // echo("empty data");
                        //if (is_array($hasPendingCpcbData) || is_object($hasPendingCpcbData)){
                        //if(is_array($hasPendingCpcbData)){
                            foreach ($hasPendingCpcbData as $key=>$value){
                                $hasPending = $hasPendingCpcbData[$key];
                                $FDS->updateHasPendingCpcbData($hasPending, $key);
                                echo("<br>\r\n Updated haspending flag as $hasPending for station seq ". $key. "<br>");
                            }
                        //}
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
                    
                    //EXTRA CHUNK FOR NEW SERVER
	            	//$ch1 = curl_init();
                    //$url1 = "http://182.75.69.206:8080/v1.0/industry/". $locationSeq ."/data";
                    //echo("<br> \r\n URL :- " . $url1 . "<br>");
	            	//echo($vendorCode ." - " .$apiKey . "<br>\r\n");
	            	//curl_setopt($ch1, CURLOPT_HTTPHEADER, array('Content-Type: application/Json', $auth_header));
	            	//curl_setopt($ch1, CURLOPT_POST, TRUE);
	            	//curl_setopt($ch1, CURLOPT_POSTFIELDS, $data);
	            	//curl_setopt($ch1, CURLOPT_URL, $url1);
	            	//curl_setopt($ch1, CURLOPT_RETURNTRANSFER, TRUE);
	            	//$result1 = curl_exec($ch1);
	            	//if(!empty(curl_error($ch1))){
	            		//echo 'error:' . curl_error($ch1);
	            	//}
	            	//echo("Response for old URL - ".$result1);
	            	//$resultJson1 = json_decode($result1);
                    //$msg1 = $resultJson1->msg;
                    //$status1 = $resultJson1->status;
	            	//if($status1 != "1"){
                        //$logger->error("Old Portal has issues while uploading to Industry :". $location->getLocationName() ." with error ". $result1);
                    //}
                    //ENDS EXTRA CHUNK
                    
                    
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
                    echo("<br>Response - ".$result);
                    if($vendorCode =="Bhoomi"){
                        $reqLog = $location->getLocationName() ."(". $locationSeq .") - Request: ". $data;
                        $resLog = $location->getLocationName() ."(". $locationSeq .") - Response: ". $result;
                        $log = $reqLog  . $resLog;
                        $logger->info($log);
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
                        }
                    }else{
                        $logger->error("New Portal has issues while uploading to Industry :". $location->getLocationName() ." with error ". $result);
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
