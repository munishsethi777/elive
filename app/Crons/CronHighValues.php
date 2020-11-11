<?php 

  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/FileSystemUtils.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/VirtualParameterUtil.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/CPCBUtils.php");
  //require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDFileDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDDataDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDStackDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/UserDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/LocationDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/HighValueRuleDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/HighValueRuleReminderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/HighValueRuleReminderSMSDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/HighValueRuleReminderSMS.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php");

  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserConfig.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Managers/ReminderMgr.php");
  require_once($ConstantsArray['dbServerUrl'] ."/admin/configuration.php");
  require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDFile.php");
  require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDData.php");
  require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDChannel.php");

  require_once($ConstantsArray['dbServerUrl'] .'/log4php/Logger.php');
  Logger::configure('/home/envirote/public_html/app/log4php/log4php.xml');
 
  $FolderDS = FolderDataStore::getInstance();
  $WQDDataDS = WQDDataDataStore::getInstance();
  $WQDStackDataDS = WQDStackDataStore::getInstance();
  $HVRRDS = HighValueRuleReminderDataStore::getInstance();
  $HVRDS = HighValueRuleDataStore::getInstance();
  $CLP_LOCATION_SEQ = 18;
  //$highValueRules = $HVRDS->FindAllForHighValueCron(false,$CLP_LOCATION_SEQ);
    $highValueRules = $HVRDS->FindAll();
    
  $UDS = UserDataStore::getInstance();
  $bhoomiUser = $UDS->FindByUserName('bhoomi_cpcb');
  $bhoomiUserLocations = $bhoomiUser->getOtherLocationSeqs();
  $LDS = LocationDataStore::getInstance();
  $eliveUser = $UDS->FindByUserName('cpcb');
  $eliveUserLocations = $eliveUser->getOtherLocationSeqs();
  $vendors = array(0=>"bhoomi",1=>"envirozone");
  foreach($highValueRules as $highValueRule){
  	 $isTurnOnRepetitive = true;
     echo("\n<br> Rule for station ". $highValueRule->getIndustryName(). " : " . $highValueRule->getStationName() ." (Type : - " . $highValueRule->getStationType() . ") for channel ". $highValueRule->getChannelName());
	 
     $ruleFolderSeq =  $highValueRule->getFolderSeq();     
     $folder = $FolderDS->FindBySeq($ruleFolderSeq);
     $stationVendor = $folder->getStationVendor();
     if(!empty($stationVendor)){
     	$stationVendor = strtolower($stationVendor);
     	if(in_array($stationVendor, $vendors)){
     		$isTurnOnRepetitive = false;
     	}	
     }
     $location = $LDS->FindBySeq($folder->getLocationSeq());
     $stationType = $folder->getStationType();
     $parameter = $highValueRule->getParameter();
     $highValue = $highValueRule->getHighValue();
     $virtualChannelSeq = $highValueRule->getVirtualChannelSeq();     
     $ruleType = $highValueRule->getRuleType();
     $lastWQDSeq = $highValueRule->getLastRuleHitFileDataSeq();
     if($lastWQDSeq == NULL){
        $lastWQDSeq = 0;
     }
     $arr = null;
     if(!empty($virtualChannelSeq)){
     	 $virtualParameterUtil = VirtualParameterUtil::getInstance();
     	 $data = $virtualParameterUtil->getVirtualParameterValue($virtualChannelSeq, $lastWQDSeq);
     	 $arr[0] = $data;  		
     }else{
     	if($stationType == "stack" || $stationType == "effluent"){
     		$arr = $WQDStackDataDS->getHighValueOccurencies($ruleFolderSeq,$lastWQDSeq,$highValue, $parameter);
     	}else{
     		$arr = $WQDDataDS->getHighValueOccurencies($ruleFolderSeq,$lastWQDSeq,$highValue, $parameter);
     	}
     }
	 $maxWQD = $arr[0]['wqdfiledataseq'];	 
	 $avgValue =  $arr[0][1];
	 if(empty($maxWQD) && empty($avgValue)){
	 	continue;
	 }
	 echo (" - Average value found :". $avgValue . " from seq ". $lastWQDSeq ." to lastSeq :- " . $maxWQD);
	 if($maxWQD != 0){
		$highValueRule->setLastRuleHitFileDataSeq($maxWQD);
	 }
	 
	 //Update lastWQDSeq during inActive Case
	 if($highValueRule->getIsActive() == 0){
	 	echo (" - skipping as inactive");
	 	$HVRDS->Save($highValueRule);
	 	continue;
	 }
	 
	 $flag = false;
	 if($ruleType == "highvalue"){
	 	$flag = $avgValue > $highValue;
	 }else if($ruleType == "lowvalue"){
	 	$flag = $avgValue < $highValue;
	 }
	 if($flag){
		echo ("\nFound high value occurence" );
		$isNotifyCpcb =  $highValueRule->getIsNotifyCpcb();
		$emailIds = $highValueRule->getEmail();
		$mobileNumber = $highValueRule->getMobile();
		if((!empty($isNotifyCpcb) )&& (in_array($location->getSeq(),$bhoomiUser->getOtherLocationSeqs()) ||
                                 in_array($location->getSeq(),$eliveUser->getOtherLocationSeqs()))){		
			$cpcbEmails = CPCBUtils::getEmailsByIndustry($folder->getCategory());
			$emailIds = $emailIds . "," .  $cpcbEmails;
			$cpcbMobileNumbers = CPCBUtils::getSMSNumbersByIndustry($folder->getCategory());
			$mobileNumber = $mobileNumber .','. $cpcbMobileNumbers;
			//$mobileNumber = $mobileNumber . "," . "7042498410,9555619639";
		}
		//$mobileNumber = $mobileNumber . "," . "9814600356";
		
		
		$frequency = $highValueRule->getFrequency();
		$hits = $highValueRule->getRuleHits();
		$totHits = $hits + 1;
		$highValueRule->setRuleHits($totHits);		
		$date = (new \DateTime())->modify('-12 hours');
		if($folder->getLocationSeq() == 18){
			//$date = (new \DateTime())->modify('-1 hours');
		}	
		if($totHits >= $frequency){
			echo ("\nTotal Hits: $totHits and frequency is $frequency");
			$lastSentSMSOn = $highValueRule->getLastSentSMSOn();
			if(!empty($lastSentSMSOn)){
				$lastSentSMSOn = new DateTime($lastSentSMSOn);
			}
			$isRepetitive = 0;
			$isSentSms = 0;
			if(!empty($lastSentSMSOn) && $lastSentSMSOn > $date && $isTurnOnRepetitive){
				$isRepetitive = 1;
				$isSentSms = 1;
			}else{
				$highValueRule->setLastSentSMSOn(new DateTime());
			}
			$highValueReminder = new HighValueRuleReminder();
			$highValueReminder->setFolderSeq($ruleFolderSeq);
			$highValueReminder->setIsRepetitive($isRepetitive);
			$highValueReminder->setHighValue($avgValue);
			$isHighValue = $ruleType == "highvalue" ? 1 : 0;		
			$highValueReminder->setIsHighValue($isHighValue);			
			$highValueReminder->setHighValueChannelNo($parameter);
			$highValueReminder->setHighValueRuleSeq($highValueRule->getSeq());
			$highValueReminder->setReminderDate(date("Y-m-d  H:i:s"));
			$highValueReminder->setReminderEmail($emailIds);
			$highValueReminder->setReminderMobile($mobileNumber);
			$highValueReminder->setReminderIsSent($isSentSms);
			$highValueRule->setRuleHits(0);
			$highValueReminder->setFromWQDSeq($lastWQDSeq);
			$highValueReminder->setToWQDSeq($maxWQD);
			$id = $HVRRDS->Save($highValueReminder);
			$mobileNumberArr = explode(",", $mobileNumber);
			if(!empty($id)){
				foreach ($mobileNumberArr as $mobile){
					if(!empty($mobile)){
						$hRRSMS = new HighValueRuleReminderSMS();
						$hRRSMS->setErrorCode(0);						
						$hRRSMS->setIsSent($isSentSms);
						if(!empty($isSentSms)){
							$hRRSMS->setDated(new DateTime());
						}
						$hRRSMS->setHighValueRuleReminderSeq($id);
						$hRRSMS->setMobile($mobile);
						$hRRSMSDataStore =  HighValueRuleReminderSMSDataStore::getInstance();
						$hRRSMSDataStore->Save($hRRSMS);
					}
				}	
			}
			echo("\nSaved Reminder now");
		}
	 }
	 //var_dump($highValueRule);
	 $HVRDS->Save($highValueRule);
  }
?>
