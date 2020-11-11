<?php
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/ConfigurationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/LocationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/FolderDataStore.php");

require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/HighValueRuleReminderDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/UserDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/ChannelConfigurationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/Utils/ExportUtils.php");
class NotificationUtil{
	
	private static $notificationUtil;
	
	private static $LIVE = "live";
	private static $DELAY = "delay";
	private static $OFFLINE = "offline";	

	public static $WEEKLY = "weekly";
	public static $MONHTLY = "monthly";
	//----------------
	private static $DAY_OF_WEEK = "mon";//Thu
	private static $DATE_OF_MONTH = "16";
	private static $TIME_OF_DAY = "05:00:00";
	private static $cpcbManagerSeqs  = array(0=>13);
	//----------------
	
	public static function getInstance(){
		if(empty(self::$notificationUtil)){
			self::$notificationUtil = new NotificationUtil();
		}
		return self::$notificationUtil;
	}
	
	public function sendCPCBFilesWeeklyNotification(){
	    $ThatTime = strtotime(self::$TIME_OF_DAY);
		$now = time();
		$date = date('D');		
		if(strtolower($date) == self::$DAY_OF_WEEK && $now >= $ThatTime){
		    try{
				$currentDate = new \DateTime();
				$this->sendHighValueLogsAndStationReports(self::$WEEKLY,$currentDate);
			}catch (Exception $e){
				$message = $e->getMessage();
				echo("Error during send weekly notification :- ". $message);
			}
			
		}
	}
	
	public function sendCPCBFilesMonthlyNotification(){
		$ThatTime = strtotime(self::$TIME_OF_DAY);
		$now = time();
		$date = date('d');
		if($date === self::$DATE_OF_MONTH && $now >= $ThatTime){
			try{
				$currentDate = new \DateTime();
				$this->sendHighValueLogsAndStationReports(self::$MONHTLY,$currentDate);
			}catch (Exception $e){
				$message = $e->getMessage();
				echo("Error during send monthly notification :- ". $message);
			}
		}
	}
	
	public function generateReportInstant($toDateStr,$type){
		$toDate = new DateTime($toDateForm);
		$this->sendHighValueLogsAndStationReports($type,$toDate,true);
	}
	
	
	private function sendHighValueLogsAndStationReports($notificationType,$toDate,$instant = false){
	    if(!$instant){
			echo("sending " . $notificationType . " notification... <br>");
		}
		$CDS = ConfigurationDataStore::getInstance();
		$fromModified = "-1 weeks";	
		$lastSentOn = null;
		$configKey = ConfigurationDataStore::$cpcbFilesLastSentWeekly;
		if($notificationType == "monthly"){
			$fromModified = "-1 months";
			$configKey = ConfigurationDataStore::$cpcbFilesLastSentMonthly;
			$lastSentOn = $CDS->getConfiguration($configKey);			
		}else {
			$lastSentOn = $CDS->getConfiguration($configKey);
		}
		$lastSentOn = Date('Y/m/d', strtotime($lastSentOn));
		if(Date('Y/m/d') > $lastSentOn || $instant){
		    $fromDate = clone $toDate;
			$fromDate = $fromDate->modify($fromModified);
			$fromDateStr = $fromDate->format("Y/m/d  00:00:00");
			$toDateStr = $toDate->format("Y/m/d  00:00:00");			
			$CCDS = ChannelConfigurationDataStore::getInstance();
			$UDS = UserDataStore::getInstance();
			$LDS = LocationDataStore::getInstance();
			$FDS = FolderDataStore::getInstance();
			$HVRRDS = HighValueRuleReminderDataStore::getInstance();
            $managerSession = $_SESSION["managerSession"];
            $seq = self::$cpcbManagerSeqs['0'];
            if($managerSession != null){
                $seq = $managerSession['seq'];
            }
			//foreach (self::$cpcbManagerSeqs as $seq){
				try{
					$manager = $UDS->FindBySeq($seq);
					//$email = "baljeetgaheer@gmail.com";
                    $email = $manager->getEmailId();
					$locationSeqs = $LDS->FindLocationsByUser($seq);
					$locationSeqs =  implode(",",$locationSeqs);
					$folders = $FDS->FindByLocation($locationSeqs);
						
					$folderSeqs = array_keys($folders);
					$folderSeqs = implode(",", $folderSeqs);
					//$logs = $HVRRDS->getHVLogsForExportInCPCBFormat("49","2016/11/15 00:00:00", "2017/01/19 00:00:00");
					$logs = $HVRRDS->getHVLogsForExportInCPCBFormat($folderSeqs,$fromDateStr, $toDateStr,$notificationType == "monthly");
					
					$attachments = array();
					$highValueLogsExcelOutput = null;
					if($notificationType == "monthly"){
						$highValueLogsExcelOutput = ExportUtils::exportMonthlyCPCBReport($logs, $fromDateStr, $toDateStr,true);
					}else{
						$highValueLogsExcelOutput = ExportUtils::exportCPCBReport($logs, $fromDateStr, $toDateStr,true);
					}
					
					$fDate = $fromDate->format("d-m-Y");
					$tDate = (new \DateTime())->modify("-1 days");
					$tDate = $tDate->format("d-m-Y");
					$name = "HighValueReminders_" . $fDate ."_to_".$tDate;
					$attachments[$name] =  $highValueLogsExcelOutput;
					if($notificationType == "monthly"){
						$attachments = $this->generateMonthlyReprotAttachments($locationSeqs,$attachments);
					}else{
						//$email = "fozia.syed06@gmail.com";
						$attachments = $this->generateStationStatusReprotAttachments($locationSeqs,$attachments);
					}
				
				
					MailerUtils::sendHighValueLogsNotification($email, $notificationType,$attachments,$fDate,$tDate);
					if(!$instant){
						echo("sent notification for userseq :- " . $seq . "<br>");
					}
				}catch(Exception $e){
					$message = $e->getMessage();
					if(!$instant){
						echo("Error during send notification for userseq " . $seq . " :- " . $message);
					}else{
						throw $e;
					}
				}
			//}
			if(!$instant){
				$date = new DateTime();
				$date = date_format($date,"Y/m/d H:i:s");
				$CDS->saveConfig($configKey,$date);
				echo($notificationType . " Notification sent till " . $date . "<br>");
			}
		}
	}
	
	private function generateMonthlyReprotAttachments($locationSeqs,$attachments){
		$toDate = new DateTime();
		$toDate = $toDate->format("d-m-Y");
		$CCDS = ChannelConfigurationDataStore::getInstance();
		$FDS = FolderDataStore::getInstance();
	
		$statesAndstationsOffline = $FDS->FindStationsForStatusReprtByLocation($locationSeqs,self::$OFFLINE);
		$channels = $CCDS->FindByLocSeqsAndGroupByStation($locationSeqs);
		
		$name_offline = "Latest offline Industry";
		$stationExcelOutput = ExportUtils::getOfflineStationsStatusReport($statesAndstationsOffline,$channels,false);
		$attachments[$name_offline] =   $stationExcelOutput;
		
		$backup_offline = "Monthly_offline_backup";
		$backup_stationExcelOutput = ExportUtils::getOfflineStationsStatusReport($statesAndstationsOffline,$channels,true);
		$attachments[$backup_offline] =   $backup_stationExcelOutput;
		return $attachments;
	}
	
	private function generateStationStatusReprotAttachments($locationSeqs,$attachments){
		$toDate = new DateTime();
		$toDate = $toDate->format("d-m-Y");
		$CCDS = ChannelConfigurationDataStore::getInstance();
		$FDS = FolderDataStore::getInstance();
		$statesAndstationsLive = $FDS->FindStationsForStatusReprtByLocation($locationSeqs,self::$LIVE);
		//$liveStationExcelOutput =  ExportUtils::exportStationsStatusReport($statesAndstationsLive, self::$LIVE,true);
		//$name_live = "IndustryStatus_Live_".$toDate;
		//$attachments[$name_live] =  $liveStationExcelOutput;
		
		$statesAndstationsDelay = $FDS->FindStationsForStatusReprtByLocation($locationSeqs,self::$DELAY);
		//$delayStationExcelOutput =  ExportUtils::exportStationsStatusReport($statesAndstationsDelay, self::$DELAY,true);
		//$name_delay = "IndustryStatus_Delay_".$toDate;
		//$attachments[$name_delay] =   $delayStationExcelOutput;
		
		$statesAndstationsOffline = $FDS->FindStationsForStatusReprtByLocation($locationSeqs,self::$OFFLINE);
		//$channels = $CCDS->FindByLocSeqsAndGroupByStation($locationSeqs);
		//$offlineStationExcelOutput =  ExportUtils::exportOfflineStationsStatusReport($statesAndstationsOffline, $channels,true);
		//$name_offline = "IndustryStatus_Offline_".$toDate;
		$folders = array();
		$folders["Live"] = $statesAndstationsLive;
		$folders["Offline"] = $statesAndstationsOffline;
		$folders["Delay"] = $statesAndstationsDelay;		
		$stationExcelOutput = ExportUtils::exportAllTypeStatusReport($folders,true);
		$attachments["IndustryStatus"] =   $stationExcelOutput;
		return $attachments;
	}
	
}
