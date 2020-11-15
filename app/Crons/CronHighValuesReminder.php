<?php

  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/FileSystemUtils.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/MailerUtils.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/PrescribedLimitsUtils.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/CPCBUtils.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/SMSApiUtils.php");


  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDFileDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDDataDataStore.php");
  //require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/LocationDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/UserDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/HighValueRuleDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/HighValueRuleReminderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/VirtualChannelConfigurationDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ConfigurationDataStore.php");

  require_once($ConstantsArray['dbServerUrl'] ."/Managers/ReminderMgr.php");
  require_once($ConstantsArray['dbServerUrl'] ."/admin/configuration.php");
  require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDFile.php");
  require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDData.php");
  require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDChannel.php");
  require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/HighValueRuleReminder.php");
  require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/HighValueRuleReminderSMS.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/HighValueRuleReminderSMSDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/NotificationUtil.php");
  require_once($ConstantsArray['dbServerUrl'] .'/log4php/Logger.php');
  Logger::configure('/home/envirote/public_html/app/log4php/log4php.xml');


try{
	$reminderMgr = ReminderMgr::getInstance();
	$notificationUtils = NotificationUtil::getInstance();
	$HVRRDS = HighValueRuleReminderDataStore::getInstance();
    
    $CCDS = ChannelConfigurationDataStore::getInstance();
    $VCCDS = VirtualChannelConfigurationDataStore::getInstance();
    $CDS = ConfigurationDataStore::getInstance();
    $HVRDS = HighValueRuleDataStore::getInstance();
    $HVRSMSDataStore = HighValueRuleReminderSMSDataStore::getInstance();
    $FDS = FolderDataStore::getInstance();
    $LDS = LocationDataStore::getInstance();
    $UDS = UserDataStore::getInstance();
    $bhoomiUser = $UDS->FindByUserName('bhoomi_cpcb');
    $bhoomiUserLocations = $bhoomiUser->getOtherLocationSeqs();

    $eliveUser = $UDS->FindByUserName('cpcb');
    $eliveUserLocations = $eliveUser->getOtherLocationSeqs();

    $notificationLocations = array_merge($bhoomiUserLocations,$eliveUserLocations);
    $highValueRuleReminders = $HVRRDS->FindAll();
    
    if(count($highValueRuleReminders) == 0){
        echo ("No reminders Found");
    }
    foreach($highValueRuleReminders as $highValueRuleReminder){
        echo ("\n<br> Reminder starting to send");
        $highValueRule = $HVRDS->FindBySeq($highValueRuleReminder->getHighValueRuleSeq());
        $virtualChannelSeq = $highValueRule->getVirtualChannelSeq();
        $channelConfiguration = null;
        if(!empty($virtualChannelSeq)){
        	$channelConfiguration = $VCCDS->FindArrBySeq($virtualChannelSeq);
        }else{
        	$channelConfiguration = $CCDS->FindByFolderAndChannelNoWithSation($highValueRuleReminder->getFolderSeq(),
        			$highValueRuleReminder->getHighValueChannelNo());
        }
        
        $folder = $FDS->FindBySeq($highValueRuleReminder->getFolderSeq());        
        $location = $LDS->FindBySeq($folder->getLocationSeq());
        $channelName = $channelConfiguration[0]['channelname'];
        $channelUtil = $channelConfiguration[0]['channelunit'];
        $stationName = $folder->getStationName();
        if($channelConfiguration[0]['channelstation'] != null){
            $stationName =  $channelConfiguration[0]['channelstation'];
        }

       
        $mobiles = $HVRSMSDataStore->getMobilesByReminder($highValueRuleReminder->getSeq());        
        $mobileNumber = "";
        if(!empty($mobiles)){
        	$mobileNumber = implode(",", $mobiles);
        	$mobileNumber = str_replace(' ', '', $mobileNumber);
        }
        
        $highValue = $highValueRuleReminder->getHighValue();
        $ruleType = $highValueRule->getRuleType();
        $emailIds = $highValueRuleReminder->getReminderEmail();       
       // $isNotifyCpcb =  $highValueRule->getIsNotifyCpcb();
//        if((!empty($isNotifyCpcb) )&& (in_array($location->getSeq(),$bhoomiUser->getOtherLocationSeqs()) ||
//                                  in_array($location->getSeq(),$eliveUser->getOtherLocationSeqs()))){
                                    
//             $cpcbEmails = CPCBUtils::getEmailsByIndustry($folder->getCategory());
//             $emailIds = $emailIds . "," .  $cpcbEmails;
//             $cpcbMobileNumbers = CPCBUtils::getSMSNumbersByIndustry($folder->getCategory());
//             $mobileNumber = $mobileNumber .','. $cpcbMobileNumbers;    
//         }
        $subject = "High Value";
        $content = "EXCEEDING";
        $plimit = $channelConfiguration[0]["prescribedlimit"];
        if($ruleType == "lowvalue"){
        	$subject = "Low Value";
        	$content = "DECEEDING";
        	$plimit = $channelConfiguration[0]["prescribedlowlimit"];
        }        
        $subject = $subject . " Notification for ". $folder->getStationName(). " of Channel - ". $channelName;
        $message = "High Value ". $highValue ." is observed for channel ". $channelName ." for station ". $folder->getFolderName();
        $alertEmmission = "Ambient";
        if($folder->getStationType() == "stack"){
            $alertEmmission = "Emission";
        }elseif($folder->getStationType() == "effluent"){
            $alertEmmission = "Effluent";
        }
        
        $mailMessage = "\r\n<br>ALERT :-- ". $alertEmmission;
        $mailMessage .= "\r\n<br>Industry name :-- ". $folder->getIndustryName() .", ". $folder->getCity() .", ". $folder->getState();
        $mailMessage .= "\r\n<br>CAT :-- ". $folder->getCategory();
        $mailMessage .= "\r\n<br>Location :-- ". $stationName;
        $mailMessage .= "\r\n<br>".$content." PARAMETER :-- ". $channelName;
        $mailMessage .= "\r\n<br>VALUE :-- ". $highValue  ;
        if($plimit){
            $mailMessage .=" against Pres. Stand. ". $plimit ." ". $channelUtil ;
        }
        $mailMessage .= "\r\n<br>". date("D, d-M-Y H:i");
        $mailMessage .= "\r\n<br>Average Value for last 15 Minutes";
        $mailMessage .= "\r\n<br>Respond at cems.cpcb@nic.in";
        $cpcbEmail = CPCBUtils::getEmailsByIndustry($folder->getCategory());
		$cpcbReplyEmail = CPCBUtils::getReplyEmailsByIndustry($folder->getCategory());
        if(!empty($cpcbReplyEmail)){
        	$mailMessage .= " & " . $cpcbReplyEmail;
        }
        $isSentEmail = false;
        if($emailIds != ""){
           $isSentEmail = ReminderMgr::sendHighValueNotification($emailIds,$subject, $mailMessage);
        }
        $isSentSMS = false;
        $guid = null;
        if($mobileNumber != ""){
//             $smsMessage = "SMS Alert from CPCB";						
            $mailMessage .= "\r\n<br>& " . $cpcbEmails;
            $smsMessage = $mailMessage;
            $smsMessage = str_replace("<br>","",$smsMessage);
            $smsMessage = str_replace("�g/m�","mg/nm3",$smsMessage);
            $vendor = $folder->getStationVendor();
			if(!empty($vendor)){
				$vendor = strtolower($vendor);
			}
            $response = ReminderMgr::sendSMSByVendor($mobileNumber,$smsMessage,$vendor);
            if(!empty($response)){
            	$guid = $HVRSMSDataStore->updateStatusForSendSMS($vendor,$response, $highValueRuleReminder->getSeq(),$mobileNumber);            	
            }
        }
        echo $smsMessage;
        $HVRRDS->SentStatusChange($isSentEmail,$guid,$highValueRuleReminder->getSeq());
    }// Loop

    //Daily cumulative report for sms sent
    $toDate = new DateTime();
    $fromDate = new DateTime();
    $fromDate = $fromDate->sub(new DateInterval('P1D'));

    $fromDateStr = $fromDate->format("Y/m/d  H:i:s");
    $toDateStr = $toDate->format("Y/m/d  H:i:s");    
    $lowValueOccurences = $HVRRDS->FindByFromToDate($fromDateStr, $toDateStr,0);
    $highValueOccurences = $HVRRDS->FindByFromToDate($fromDateStr, $toDateStr);   
    $occurencesArr = array();
    $occurencesArr["lowValue"] = $lowValueOccurences;
    $occurencesArr["highValue"] = $highValueOccurences;  
    $tbl = "<strong>No Reminder Found</strong>";
    foreach ($occurencesArr as $ruleType=>$oc){
    	$highValueOccurences = $oc;
    	if($ruleType == "lowValue"){
    		$tbl = "</br><strong>No Low Value Reminder Found</strong>";
    	}else{
    		$tbl = "</br><strong>No High Value Reminder Found</strong>";
    	}
    	if(count($highValueOccurences) > 0){
    		//creation of an array first
    		$mainArr = array();
    		foreach($highValueOccurences as $occurence){
    			if(!in_array($occurence['locationseq'],$notificationLocations)){
    				continue;
    			}
    			$subStation = $occurence["channelstation"];
    			if(!empty($subStation)){
    				$subStation = " (". $subStation  .")";
    			}
    			$highValueRuleSeq = $occurence['highvaluerule'];
    			$monthTotal = $HVRRDS->getTotalRemindersByHighValueRuleSeq($highValueRuleSeq);
    			if(empty($monthTotal)){
    				$monthTotal = 0;
    			}else{
    				$monthTotal = $monthTotal[0][0];
    			}    	
    			$mobileNumbers = $HVRRDS->getUniqueMobileNumbersRemindersSent($highValueRuleSeq);
    			$highValueRuleObj =  $HVRDS->FindBySeq($highValueRuleSeq);
    			$virtualChannelSeq = $highValueRuleObj->getVirtualChannelSeq();
    			$stationFullName = $occurence['industryname'] . $subStation;
    			$stationFullNameCleaned = str_replace(array('/', ' '), array('-', ''), $stationFullName);
    			$industryArr = array();
    			if(isset($mainArr[$stationFullNameCleaned])){
    				$industryArr = $mainArr[$stationFullNameCleaned];
    				$industryArr['totalall'] = intval($industryArr['totalall']) + intval($occurence['total']);
    			}else{
    				$industryArr['industryName'] = $stationFullName;
    				$industryArr['category'] = $occurence['category'];
    				$industryArr['details'] = array();
    				//$industryArr['totalall'] = $occurence['total'];
    	
    	
    			}
    			
    			$chName = $occurence['channelname'];
    			$presLimit = $occurence['prescribedlimit'];
    			 
    			if(!empty($virtualChannelSeq)){
    				$virtualChannel = $VCCDS->FindBySeq($virtualChannelSeq);
    				$chName = $virtualChannel->getChannelName();
    				if($ruleType == "lowValue"){
    					$presLimit = $virtualChannel->getPrescribedLowLimit();
    				}else{
    					$presLimit = $virtualChannel->getPrescribedLimit();
    				}
    			}
    			
    			$highValueDetailsArr = array();
    			$highValueDetailsArr['channelname'] = $chName;
    			$highValueDetailsArr['total'] = $occurence['total'];
    			$highValueDetailsArr['presLimit'] = $presLimit;
    			$highValueDetailsArr['stationName'] = $occurence['stationname'];
    			$highValueDetailsArr['mobile'] = $occurence['mobile'];
    			$highValueDetailsArr['totalall'] = $monthTotal;
    			$highValueDetailsArr['mobileNumbers'] = $mobileNumbers;
    			array_push($industryArr['details'], $highValueDetailsArr);
    			$mainArr[$stationFullNameCleaned] = $industryArr;
    		}
    		//print_r($mainArr);    		
    		if($ruleType == "lowValue"){
    			$tbl = "<h4>Low Value Summary<h4>";
    		}else{
    			$tbl = "<h4>High Value Summary<h4>";
    		}
    		$tbl .= "<table border='1'><tr>";
    		$tbl .= "<th>S. No.</th>";
    		$tbl .= "<th style='text-align:left'>Name of Industry</th>";
    		$tbl .= "<th style='text-align:left'>Location(Station Name)</th>";
    		$tbl .= "<th style='text-align:left'>Category</th>";
    		$tbl .= "<th style='text-align:left'>Parameter Std. Limit</th>";
    		$tbl .= "<th style='text-align:left'>Parameter</th>";
    		
    		if($ruleType == "lowValue"){
    			$tbl .= "<th style='text-align:left'>Deceedence</th>";
    		}else{
    			$tbl .= "<th style='text-align:left'>Exceedence</th>";
    		}    		
    		$tbl .= "<th style='text-align:left'>Total SMS in month</th>";
    		$tbl .= "<th style='text-align:left'>SMS delivered to</th>";
    	
    		$tbl .= "</tr>";
    		$count = 1;
    		foreach($mainArr as $arr){
    			$tbl .="<tr>";
    			$tbl .="<td rowspan='". count($arr['details']) ."'>". $count++ ."</td>";
    			$tbl .="<td rowspan='". count($arr['details']) ."'>". $arr['industryName']."</td>";
    			$tbl .="<td rowspan='". count($arr['details']) ."'>". $arr['details'][0]['stationName']."</td>";
    			$tbl .="<td rowspan='". count($arr['details']) ."'>". $arr['category']."</td>";
    	
    	
    			$tbl .="<td>". $arr['details'][0]['presLimit'] ."</td>";
    			$tbl .="<td>". $arr['details'][0]['channelname'] ."</td>";
    			$tbl .="<td>".  $arr['details'][0]['total'] ."</td>";
    			$tbl .="<td>".  $arr['details'][0]['totalall'] ."</td>";
    			$tbl .="<td>".  $arr['details'][0]['mobileNumbers'] ."</td>";
    			$tbl .="</tr>";
    			if(count($arr['details']) > 1){
    				for($i=1;$i<count($arr['details']);$i++){
    					$detail = $arr['details'][$i];
    					$tbl .="<tr>";
    					$tbl .="<td>". $detail['presLimit'] ."</td>";
    					$tbl .="<td>". $detail['channelname'] ."</td>";
    					$tbl .="<td>". $detail['total'] ."</td>";
    					$tbl .="<td>".  $detail['totalall'] ."</td>";
    					$tbl .="<td>".  $detail['mobileNumbers'] ."</td>";
    					$tbl .="</tr>";
    				}
    			}
    		}
    		$tbl .="</table>";
    	}
    	//$message .= $tbl;
    	echo $tbl;
    }
    	$ThatTime = strtotime("15:30:00");
    	$now = time();
    	if ($now >= $ThatTime) {
    		$smsSummaryLastSentOn = $CDS->getConfiguration($CDS::$smsSummaryLastSentOn);
    		$smsSummaryLastSentOn = Date('Y/m/d', strtotime($smsSummaryLastSentOn));
    		if(Date('Y/m/d') > $smsSummaryLastSentOn){
    			$emailTo = $CDS->getConfiguration($CDS::$cpcbEmail);
    			$from = "noreply@envirotechlive.com";
    			$subject = "High Value & Low Value Reminder Summary";
    			MailerUtils::sendMandrillEmailNotification($tbl,$subject,$from,$emailTo);
    			$date = new DateTime();
    			$date =  date_format($date,"Y/m/d H:i:s");
    			$CDS->saveConfig($CDS::$smsSummaryLastSentOn,$date);
    			echo("Sent summary email");
				
				//Send Credits Left Email
				$reminderMgr::sendSMSCreditsNotification();
    		}
    		
    		
    	}	
    	$notificationUtils->sendCPCBFilesWeeklyNotification();
    	$notificationUtils->sendCPCBFilesMonthlyNotification();
}catch(Exception $e){
    $logger = Logger::getLogger($ConstantsArray["logger"]);
    $message = $e->getMessage();
    $logger->error("Error during CronHighValuesReminder " . $message);
}


?>
