<?php
   require_once('IConstants.inc');
   require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects//HighValueRule.php");
   require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects//HighValueRuleReminder.php");
   require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr//HighValueRuleReminderSMSDataStore.php");
   require_once($ConstantsArray['dbServerUrl'] ."/Utils/FilterUtil.php");
   
   require_once("MainDB.php");

 class HighValueRuleReminderDataStore{
     private static $highValueRuleReminderDataStore;
     private static $db;
     private static $INSERT = "insert into highvaluerulereminder (folderseq, highvalueruleseq, highvalue, highvaluechannelno, reminderdate, remindermobile, reminderemail, ishighvalue,isrepetitive,fromwqdseq,towqdseq,issent) Values(:folderseq, :highvalueruleseq, :highvalue, :highvaluechannelno, :reminderdate, :remindermobile, :reminderemail,:ishighvalue,:isrepetitive,:fromwqdseq,:towqdseq,:issent)";
     private static $FIND_ALL_UNSENT = "select *,truncate(highvalue,1) as truncatedHighValue from highvaluerulereminder where issent = 0";
     private static $FIND_BY_FOLDER = "select *,truncate(highvalue,1) as truncatedHighValue from highvaluerulereminder where folderseq=:folSeq";
     private static $FIND_BY_FOLDER_IS_SENT = "select *,truncate(highvalue,1) as truncatedHighValue from highvaluerulereminder where folderseq=:folSeq and issent=:isSent";
     private static $SENT_STATUS_CHANGE = "update highvaluerulereminder set issent=:isSent , guid=:guid where seq = :seq";
     private static $FIND_TOTAL_REMINDERS_BY_DATE_FOLDER = "select vcc.channelname as virtualchannel , hvr.virtualchannelseq,count(highvaluerulereminder.seq) as totalReminders, channelconfiguration.channelname from highvaluerulereminder
left join channelconfiguration on channelconfiguration.channelnumber = highvaluerulereminder.highvaluechannelno
and channelconfiguration.folderseq = highvaluerulereminder.folderseq left join highvaluerule hvr on  highvaluerulereminder.highvalueruleseq = hvr.seq left join virtualchannelconfiguration vcc on hvr.virtualchannelseq = vcc.configseq
where highvaluerulereminder.reminderdate >= :fromDate and highvaluerulereminder.reminderdate <= :toDate and highvaluerulereminder.folderseq = :folSeq
group by channelconfiguration.channelname";

    private static $GET_HIGVALUE_LOGS = "select hr.issent issent, hr.seq reminderseq,f.industryname,f.stationname, hr.reminderdate, hr.remindermobile,
cc.channelname, hr.reminderemail,hr.highvalue,hr.guid guid from highvaluerulereminder hr,folder f,channelconfiguration cc where f.seq = cc.folderseq and cc.channelnumber = hr.highvaluechannelno and f.seq = hr.folderseq and hr.reminderdate >= :fromDate and hr.reminderdate <= :toDate and hr.folderseq = :folSeq order by hr.reminderdate asc";
    
    private static $GET_HIGVALUE_LOGS_WITH_VIRTUAL_CHANNEL = "select hvrr.issent, hvrr.seq reminderseq,vcc.channelname, f.seq, f.industryname,f.stationname, hvrr.reminderdate, hvrr.remindermobile, 
hvrr.reminderemail, hvrr.highvalue, hvrr.guid guid from highvaluerulereminder hvrr inner join highvaluerule hrv on hvrr.highvalueruleseq = hrv.seq inner join virtualchannelconfiguration vcc on hrv.virtualchannelseq = vcc.configseq inner join folder f on hvrr.folderseq = f.seq where hvrr.reminderdate >= :fromDate and hvrr.reminderdate <= :toDate and hvrr.folderseq = :folSeq order by hvrr.reminderdate asc";
    private static $GET_LATEST_HIGVALUE_LOGS = "select hr.seq,f.stationname, hr.reminderdate,cc.channelname, hr.highvalue
from highvaluerulereminder hr inner join folder f on hr.folderseq = f.seq inner join channelconfiguration cc on hr.highvaluechannelno = cc.channelnumber and hr.folderseq = cc.folderseq order by hr.reminderdate desc limit 0,5";
    
    private  static $GET_ALL_LOGS = "select hvrr.issent, hvrr.seq reminderseq,channelconfiguration.channelname,vcc.channelname as virtualchannelname, vcc.configseq as virtualchannelseq, f.seq, f.industryname,f.stationname, hvrr.reminderdate, hvrr.remindermobile, 
hvrr.reminderemail, hvrr.highvalue, hvrr.guid guid from highvaluerulereminder hvrr left join folder f on f.seq = hvrr.folderseq
inner join highvaluerule hrv on hvrr.highvalueruleseq = hrv.seq left join virtualchannelconfiguration vcc on  hrv.virtualchannelseq = vcc.configseq
left join channelconfiguration on channelconfiguration.channelnumber = hvrr.highvaluechannelno and channelconfiguration.folderseq = f.seq where hvrr.reminderdate >= :fromDate and hvrr.reminderdate <= :toDate and hvrr.folderseq = :folSeq order by hvrr.reminderdate asc ";
    
    private static $FIND_BY_FOLDERS = "select vcc.channelname as virtualchannel ,highvaluerule.mobile as rulemobile,highvaluerule.ruletype,highvaluerule.virtualchannelseq ,folder.*, count(hr.seq) 
as total, channelconfiguration.channelnumber as channelno, channelconfiguration.prescribedlimit , channelconfiguration.prescribedlowlimit,channelconfiguration.channelname,channelconfiguration.channelstation  from highvaluerulereminder hr
left join folder on folder.seq = hr.folderseq
inner join highvaluerule on  hr.highvalueruleseq = highvaluerule.seq
left join virtualchannelconfiguration vcc on  highvaluerule.virtualchannelseq = vcc.configseq
left join channelconfiguration on channelconfiguration.channelnumber = hr.highvaluechannelno
and channelconfiguration.folderseq = folder.seq where highvaluerule.isnotifycpcb = 1 and hr.reminderdate >= :fromDate and hr.reminderdate <= :toDate and hr.folderseq in ";

    private static $FIND_TOTAL_REMINDERS_BY_DATES = "select folder.*, count(highvaluerulereminder.seq) as total,highvaluerulereminder.highvalue as highvalue, highvaluerulereminder.highvalueruleseq as highvaluerule,channelconfiguration.prescribedlimit,channelconfiguration.channelname,channelconfiguration.channelnumber,channelconfiguration.channelstation  from highvaluerulereminder
left join folder on folder.seq = highvaluerulereminder.folderseq
left join channelconfiguration on channelconfiguration.channelnumber = highvaluerulereminder.highvaluechannelno
and channelconfiguration.folderseq = folder.seq
where ishighvalue = :ishighvalue and folder.seq not in(select folderseq from m2msites) and  highvaluerulereminder.reminderdate >= :fromDate and highvaluerulereminder.reminderdate <= :toDate
group by highvaluerulereminder.highvaluechannelno, highvaluerulereminder.folderseq";
    public function __construct(){
       self::$db = MainDB::getInstance();
     }

    public static function getInstance(){
        if (!self::$highValueRuleReminderDataStore){
            self::$highValueRuleReminderDataStore = new HighValueRuleReminderDataStore();
            return self::$highValueRuleReminderDataStore;
        }
        return self::$highValueRuleReminderDataStore;
    }

//      public function Save(HighValueRuleReminder $highValueRuleReminder){
//          try{
//               $SQL = self::$INSERT;
//               $conn = self::$db->getConnection();
//               $stmt = $conn->prepare($SQL);

//               $stmt->bindValue(':folderseq', $highValueRuleReminder->getFolderSeq());
//               $stmt->bindValue(':highvalueruleseq',$highValueRuleReminder->getHighValueRuleSeq());
//               $stmt->bindValue(':highvalue',$highValueRuleReminder->getHighValue());
//               $stmt->bindValue(':highvaluechannelno',$highValueRuleReminder->getHighValueChannelNo());
//               $stmt->bindValue(':reminderdate',$highValueRuleReminder->getReminderDate());
//               $stmt->bindValue(':remindermobile',$highValueRuleReminder->getReminderMobile());
//               $stmt->bindValue(':reminderemail',$highValueRuleReminder->getReminderEmail());
//               $stmt->bindValue(':ishighvalue',$highValueRuleReminder->getIsHighValue());
//               $stmt->execute();
//               $id = $conn->lastInsertId();
//               if($error[2] <> ""){
//                 throw new Exception($error[2]);
//               }
              
//           }catch(Exception $e){
//               $logger = Logger::getLogger($ConstantsArray["logger"]);
//               $logger->error("Error During Save HighValueRuleReminder : - " . $e->getMessage());
//           }
//           return $id;

//      }
     
     public function Save($highValueRuleReminder){
     	try{
     		$SQL = self::$INSERT;     		
     		$conn = self::$db->getConnection();
     		$stmt = $conn->prepare($SQL);     		
     		$stmt->bindValue(':folderseq', $highValueRuleReminder->getFolderSeq());
            $stmt->bindValue(':highvalueruleseq',$highValueRuleReminder->getHighValueRuleSeq());
            $stmt->bindValue(':highvalue',$highValueRuleReminder->getHighValue());
            $stmt->bindValue(':highvaluechannelno',$highValueRuleReminder->getHighValueChannelNo());
            $stmt->bindValue(':reminderdate',$highValueRuleReminder->getReminderDate());
            $stmt->bindValue(':remindermobile',$highValueRuleReminder->getReminderMobile());
            $stmt->bindValue(':reminderemail',$highValueRuleReminder->getReminderEmail());
            $stmt->bindValue(':ishighvalue',$highValueRuleReminder->getIsHighValue());
            $stmt->bindValue(':isrepetitive',$highValueRuleReminder->getIsReptitive());
            $stmt->bindValue(':fromwqdseq',$highValueRuleReminder->getFromWQDSeq());
            $stmt->bindValue(':towqdseq',$highValueRuleReminder->getToWQDSeq());  
            $stmt->bindValue(':issent',$highValueRuleReminder->getReminderIsSent());
     		$stmt->execute();     		
     		$error = $stmt->errorInfo();
     		if($error[2] <> ""){
     			throw new Exception($error[2]);
     		}
     		$seq = $conn->lastInsertId();
     	}catch(Exception $e){
     		$logger = Logger::getLogger($ConstantsArray["logger"]);
     		$logger->error("Error During Save HighValueRuleReminder : - " . $e->getMessage());
     	}
     	return $seq;
     }
     
     public function SentStatusChange($isSent,$guid,$seq){
        $SQL = self::$SENT_STATUS_CHANGE;
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare($SQL);
        $stmt->bindValue(':isSent',$isSent);
        $stmt->bindValue(':guid',$guid);
        $stmt->bindValue(':seq',$seq);
        $stmt->execute();
        $error = $stmt->errorInfo();
     }
     
     public function FindAll(){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_ALL_UNSENT);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $objArr = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj =  self::populateObject($row);
                $objArr[$obj->getSeq()] = $obj;
            }
            return $objArr;
       }
        public function FindByFolder($folderSeq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_BY_FOLDER);
            $stmt->bindValue(':folSeq', $folderSeq);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $objArr = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj =  self::populateObject($row);
                $objArr[$obj->getSeq()] = $obj;
            }
           return $objArr;
       }
       public function FindByFolderIsSent($folderSeq,$isSent){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_BY_FOLDER_IS_SENT);
            $stmt->bindValue(':folSeq', $folderSeq);
            $stmt->bindValue(':isSent', $isSent);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $objArr = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj =  self::populateObject($row);
                $objArr[$obj->getSeq()] = $obj;
            }
           return $objArr;
       }

       public function FindByFolderFromToDate($folderSeq, $fromDate, $toDate){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_TOTAL_REMINDERS_BY_DATE_FOLDER);
            $stmt->bindValue(':folSeq', $folderSeq);
            $stmt->bindValue(':fromDate',$fromDate);
            $stmt->bindValue(':toDate',$toDate);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $rows = $stmt->fetchAll();
            return $rows;
       }
       
        public function getHVLogsForExportInCPCBFormat($folderSeqs, $fromDate, $toDate,$isMonthly = false){
	       	$hvSMSDS = HighValueRuleReminderSMSDataStore::getInstance();
	       	$conn = self::$db->getConnection();
	       	$sql = self::$FIND_BY_FOLDERS . "($folderSeqs) group by hr.highvaluechannelno,hr.folderseq order by folder.category asc, folder.industryname asc";	       	
	       	$stmt = $conn->prepare($sql);
	       	//$stmt->bindValue(':folSeqs', $folderSeqs);
	       	$stmt->bindValue(':fromDate',$fromDate);
	       	$stmt->bindValue(':toDate',$toDate);
	       	$stmt->execute();
	       	$error = $stmt->errorInfo();
	       	$rows = $stmt->fetchAll();
	       	
	       	$mainArray = array();
	       	foreach($rows as $row){
	       		$array = array();
	       		$folderSeq = $row["seq"];
	       		$array["Category"] = $row["category"];
	       		$array["Category"] = $row["category"];
	       		$address = $row["address"] . " " . $row["city"];
	       		$industryCode = $row["industrycode"];
	       		if(empty($industryCode)){
		    		$industryCode = "n.a";
		    	}
	       		$array["Industry Code"] = $industryCode;
	       		$array["Industry Name"] = $row["industryname"];
	       		$array["Full Address"] = $address;
	       		if($isMonthly){	       			
	       			$array["Contact (In Which SMSAlerts generated)"] = $row["rulemobile"];	       			
	       		}
	       		$array["State"] = $row["state"] ;
	       		$stationName = $row["stationname"];
	       		$channelStation = $row["channelstation"];
	       		if(!empty($channelStation)){
	       			$stationName .= " - " . $channelStation;
	       		}
	       		$array["Station Name"] = $stationName ;
	       		$ruleType = $row["ruletype"];
	       		$limit = $row["prescribedlimit"] ;
	       		if($ruleType == "lowvalue"){
	       			$limit = $row["prescribedlowlimit"];
	       		}
	       		$array["PermissibleHiLimit"] = $limit ;
	       		$channelName = $row["channelname"];
	       		$virtualChannelSeq = $row["virtualchannelseq"];
	       		$totalSms = 0;
	       		if(!empty($virtualChannelSeq)){
	       			$channelName = $row["virtualchannel"];
	       			$totalSms = $hvSMSDS->getTotalByFoldersAndVirtualChannel($virtualChannelSeq,$folderSeq,$fromDate, $toDate);
	       		}else{
	       			$chNumber = $row["channelno"];	       			
	       			$totalSms = $hvSMSDS->getTotalByFoldersAndChannel($chNumber,$folderSeq,$fromDate, $toDate);
	       		}
	       		$array["Parameters"] = $channelName;
	       		$array["Exceedence"] = $row["total"];	       		
	       		$array["Total SMS"] = $row["total"];
	       		$isGangaBasedIndustries = array(0=>45,1=>46,2=>47,3=>35,4=>34);
	       		if($isMonthly){
	       			$isGangaBased = "No";
	       			$locationSeq = $row["locationseq"];
	       			if(in_array($locationSeq, $isGangaBasedIndustries)){
	       				$isGangaBased = "Yes";
	       			}
	       			$array["In Ganga Industry"] = $isGangaBased;
	       		}
	       		array_push($mainArray,$array);
	       	}
       		return $mainArray;
        }
       
        public function getHighValueReminderLogsForExport($folderSeq, $fromDate, $toDate,$isVirtual){
        	$hvSMSDS = HighValueRuleReminderSMSDataStore::getInstance();
        	$conn = self::$db->getConnection();
        	$sql = self::$GET_HIGVALUE_LOGS;
        	if($isVirtual){
        		$sql = self::$GET_HIGVALUE_LOGS_WITH_VIRTUAL_CHANNEL;
        	}
        	$stmt = $conn->prepare($sql);
        	$stmt->bindValue(':folSeq', $folderSeq);
        	$stmt->bindValue(':fromDate',$fromDate);
        	$stmt->bindValue(':toDate',$toDate);
        	$stmt->execute();
        	$error = $stmt->errorInfo();
        	$rows = $stmt->fetchAll();
        	$mainArray = array();
        	foreach($rows as $row){   
        		$emailReminderRow = $row;
        		$emailReminderRow['receiver'] = $row["reminderemail"];
        		$isSentEmail = $row["issent"];
        		$status = "Waiting";
        		if(!empty($isSentEmail)){
        			$status = "Sent";
        		}
        		$emailReminderRow['status'] = $status;
        		$channelName = $row["channelname"];
        		$virtualChannelSeq = $row["virtualchannelseq"];        		
        		if(!empty($virtualChannelSeq)){
        			$channelName = $row["virtualchannel"];
        		}
        		$array = $this->getArrayForExport($emailReminderRow);
        		array_push($mainArray,$array);
        		$reminderSeq = $row["reminderseq"];
        		$reminderSMSList = $hvSMSDS->FindByReminderSeq($reminderSeq);        		
        		if(!empty($reminderSMSList)){        			
        			foreach ($reminderSMSList as $smsReminder){
        				$array = $this->getArrayForExport($emailReminderRow,$smsReminder);
        				array_push($mainArray,$array);
        			}
        		}
        	}
        	return $mainArray;
        }
        
        public function getArrayForExport($row,$smsReminder = null){
        	$array = array();        	
        	$array["IndustryName"] = $row["industryname"];
        	//$array["Mobile"] = $row["mobile"];;
        	$array["Station"] = $row["stationname"];
        	$dated = $row["reminderdate"];
        	$dated = DateUtils::StringToDateByGivenFormat("Y-m-d H:i:s", $dated);
        	$dated = $dated->format("d-m-Y H:i");
        	$array["Dated"] = $dated;
        	$array["Type"] = "Email";
        	$array["Receiver"] = $row["receiver"];
        	$guid = $row["guid"];
        	if(empty($guid)){
        		$guid = "N.A";
        	}
        	$array["GUID"] = $guid;
        	$parameter = $row["channelname"];
        	if(!empty($row["channelstation"])){
        		$parameter ." - ". $row["channelstation"];
        	}
        	$array["Parameter"] =  $parameter ;
        	$array["Value"] = $row["highvalue"];
        	$array["Status"] = $row["status"];
        	$reminderSMSList = array();
        	$type = "Email";
        	if(!empty($smsReminder)){
        		$smsRowArray = $this->getSMSReminderArry($smsReminder);
        		$array["Status"] = $smsRowArray["status"];
        		$array["Receiver"] = $smsRowArray["mobile"];
        		$array["Dated"] = $smsRowArray["dated"];
        		$array["Type"] = "SMS";
        		//$array = array_merge($array,$smsRowArray);
        	}
        	
        	return $array;
        }
        public function getTotalCount($folderSeq,$fromDate,$toDate){
        	$sql = self::$GET_ALL_LOGS;
        	$conn = self::$db->getConnection();
        	$query = FilterUtil::applyFilter($sql,false);
        	$stmt = $conn->prepare($query);
        	$stmt->bindValue(':folSeq', $folderSeq);
        	$stmt->bindValue(':fromDate',$fromDate);
        	$stmt->bindValue(':toDate',$toDate);
        	$stmt->execute();
        	$count = $stmt->rowCount();
        	return $count;
        }
        public function getHighValueReminderLogs($folderSeq, $fromDate, $toDate){
           $mainArray =  $this->findHighValueReminderLogs($folderSeq,$fromDate,$toDate,false);
           return $mainArray;
        }
        
        public function findHighValueReminderLogs($folderSeq, $fromDate, $toDate,$isVirtual){
        	$hvSMSDS = HighValueRuleReminderSMSDataStore::getInstance();        	
        	$conn = self::$db->getConnection();
        	$sql = self::$GET_ALL_LOGS;        	
        	$sql = FilterUtil::applyFilter($sql);
        	$stmt = $conn->prepare($sql);
        	$stmt->bindValue(':folSeq', $folderSeq);
        	$stmt->bindValue(':fromDate',$fromDate);
        	$stmt->bindValue(':toDate',$toDate);
        	$stmt->execute();
        	$error = $stmt->errorInfo();
        	$rows = $stmt->fetchAll();
        	$mainArray = array();
        	$arr = array();
        	foreach($rows as $row){        		
        		$array = array(); 
        		$array["IndustryName"] = $row["industryname"];
        		$array["Station"] = $row["stationname"];
        		$array["Dated"] = $row["reminderdate"];
        		
        		$array["Email"] = $row["reminderemail"];
        		$guid = $row["guid"];
        		if(empty($guid)){
        			$guid = "N.A";
        		}
        		$array["GUID"] = $guid;
        		$parameter = $row["channelname"];
        		$virtualChannelSeq = $row["virtualchannelseq"];
        		if(!empty($virtualChannelSeq)){
        			$parameter = $row["virtualchannelname"];
        		}
        		if(!empty($row["channelstation"])){
        			$parameter ." - ". $row["channelstation"];
        		}
        		
        		$array["Parameter"] =  $parameter;
        		$array["Highvalue"] = $row["highvalue"];
        		$reminderSeq = $row["reminderseq"];
        		$reminderSMS = $hvSMSDS->FindByReminderSeq($reminderSeq);
        		$smsData = $this->getSMSLogsJson($reminderSMS) ;
        		$smsDetail = null;
        		$mobiles = null;
        		if(!empty($smsData)){
        			$smsDetail = $smsData[0];
        			$mobiles = $smsData[1];
        			$mobiles = implode(" , ", $mobiles);
        		}
        		$array["smsDetail"] =  $smsDetail;
        		$array["Mobile"] = $mobiles;
        		array_push($mainArray,$array);
        	}
        	return $mainArray;
        }
        
        private function getSMSLogsJson($reminderSMS){
        	$mainArr = array();
        	$smsData = array();
        	$mobiles = array();
        	foreach ($reminderSMS as $sms){        		
        		$arr = $this->getSMSReminderArry($sms); 
        		array_push($mobiles,$sms->getMobile());
        		array_push($mainArr, $arr);
        	}
        	$smsData[0] = $mainArr;
        	$smsData[1] = $mobiles;        	 
        	return $smsData;
        }
        
        private function getSMSReminderArry($sms){
        	$arr = array();
        	$arr["mobile"] = $sms->getMobile();        
        	$isSent = $sms->getIsSent();
        	$errorCode = $sms->getErrorCode();
        	$status = "Waiting";
        	if(empty($isSent) && !empty($errorCode)){
        		$status = "Failed";
        		$status .= " - " . StringUtils::getSMSStatusMessage($errorCode);
        	}else if(!empty($isSent) && empty($errorCode)){
        		$status = "Sent";
        	}
        	$arr["status"] = $status;
        	$dated = $sms->getDated();
        	if(empty($dated)){
        		$dated = "N.A";
        	}else{
        		$dated = DateUtils::StringToDateByGivenFormat("Y-m-d H:i:s", $dated);
        		$dated = $dated->format("d-m-Y H:i");
        	}
        	$arr["dated"] = $dated;
        	return $arr;
        }
        
        public function getHighValueReminderLogsWithVirtualChannels($folderSeq, $fromDate, $toDate){
        	$mainArray =  $this->findHighValueReminderLogs($folderSeq,$fromDate,$toDate,true);           
        	return $mainArray;
        }
        
       public function getLastestHighValueReminderLogs(){
            $FDS = FolderDataStore::getInstance();
            $folderSeqs = $FDS->FindSeqsByLoggedInUserOrMgr();
            $folderSeqs = implode(",",$folderSeqs);
            $SQL = "select hr.seq,f.stationname, hr.reminderdate,cc.channelname, hr.highvalue
from highvaluerulereminder hr inner join folder f on hr.folderseq = f.seq inner join channelconfiguration cc on hr.highvaluechannelno = cc.channelnumber and hr.folderseq = cc.folderseq where f.seq in ($folderSeqs) order by hr.reminderdate desc limit 0,5";
            $LDS = LocationDataStore::getInstance();
            $locationSeqs = $LDS->getLoggedInUserOrManagerLocationSeqs();
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare($SQL);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $objArr = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj =  self::populateObject($row);
                array_push($objArr,$obj);
            }
           return $objArr;
        }
       public function FindByFromToDate($fromDate,$toDate,$isHighValue = 1){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_TOTAL_REMINDERS_BY_DATES);
            $stmt->bindValue(':ishighvalue',$isHighValue);
            $stmt->bindValue(':fromDate',$fromDate);
            $stmt->bindValue(':toDate',$toDate);            
            $stmt->execute();
            $error = $stmt->errorInfo();
            $rows = $stmt->fetchAll();
            return $rows;
       }

       public function getTotalRemindersByHighValueRuleSeq($highValueRuleSeq){
            $first_day_this_month = date('Y/m/1  00:00:00');
            $last_day_this_month  = date('Y/m/t  23:59:59');
            $sql = "select count(*) from highvaluerulereminder where highvalueruleseq =:highvaluerule and highvaluerulereminder.reminderdate >= :fromDate AND highvaluerulereminder.reminderdate <= :toDate";
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':highvaluerule',$highValueRuleSeq);
            $stmt->bindValue(':fromDate',$first_day_this_month);
            $stmt->bindValue(':toDate',$last_day_this_month);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $rows = $stmt->fetchAll();
            return $rows;
       }
       
       public function getTotalSentRemindersByHighValueRuleSeq($highValueRuleSeq){
	       	$first_day_this_month = date('Y/m/1  00:00:00');
	       	$last_day_this_month  = date('Y/m/t  23:59:59');
	       	$sql = "select count(distinct sms.highvaluerulereminderseq) as totalsms from highvaluerulereminder hvr left join highvalueruleremindersms sms on hvr.seq = sms.highvaluerulereminderseq and sms.issent = 1 where hvr.isrepetitive != 1 and hvr.highvalueruleseq =:highvaluerule and hvr.reminderdate >= :fromDate AND hvr.reminderdate <= :toDate";
	       	$conn = self::$db->getConnection();
	       	$stmt = $conn->prepare($sql);
	       	$stmt->bindValue(':highvaluerule',$highValueRuleSeq);
	       	$stmt->bindValue(':fromDate',$first_day_this_month);
	       	$stmt->bindValue(':toDate',$last_day_this_month);
	       	$stmt->execute();
	       	$error = $stmt->errorInfo();
	       	$rows = $stmt->fetchAll();
	       	return $rows;
       }
       
       //summary email must have unique mobile numbers
       public function getUniqueMobileNumbersRemindersSent($highValueRuleSeq){
            $first_day_this_month = date('Y/m/1  00:00:00');
            $last_day_this_month  = date('Y/m/t  23:59:59');
            $sql = "select DISTINCT remindermobile from highvaluerulereminder where highvalueruleseq =:highvaluerule and highvaluerulereminder.reminderdate >= :fromDate AND highvaluerulereminder.reminderdate <= :toDate";
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':highvaluerule',$highValueRuleSeq);
            $stmt->bindValue(':fromDate',$first_day_this_month);
            $stmt->bindValue(':toDate',$last_day_this_month);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $rows = $stmt->fetchAll();
            $mobileArr = array();
            if(!empty($rows)){
                foreach($rows as $row){
                    $mobiles = $row['remindermobile'];
                    if($mobiles != ""){
                        $mobArr = explode(",",$mobiles);
                        $mobileArr = array_merge($mobileArr,$mobArr);
                    }
                }
            }
            $mobileArr = array_unique($mobileArr);
            return implode(", ",$mobileArr);
       }

       public static function populateObject($row){
           $hvrRem = new HighValueRuleReminder();
           $hvrRem->setSeq($row['seq']);
           $hvrRem->setFolderSeq($row['folderseq']);
           $hvrRem->setHighValueRuleSeq($row['highvalueruleseq']);
           $hvrRem->setHighValue($row['highvalue']);
           if($row['truncatedHighValue']){
               $hvrRem->setHighValue($row['truncatedHighValue']);
           }
           $hvrRem->setHighValueChannelNo($row['highvaluechannelno']);
           $hvrRem->setReminderDate($row['reminderdate']);
           $hvrRem->setReminderMobile($row['remindermobile']);
           $hvrRem->setReminderEmail($row['reminderemail']);
           if(!empty($row['stationname'])){
               $hvrRem->setStationName($row['stationname']);
           }
           if(!empty($row['channelname'])){
               $hvrRem->setChannelName($row['channelname']);
           }
           if(!empty($row['isrepetitive'])){
           	 $hvrRem->setIsRepetitive($row['isrepetitive']);
           }
           if(!empty($row['fromwqdseq'])){
           	$hvrRem->setFromWQDSeq($row['fromwqdseq']);
           }
           if(!empty($row['towqdseq'])){
           	$hvrRem->setToWQDSeq($row['towqdseq']);
           }
           $hvrRem->setIsHighValue($row['ishighvalue']);
           return $hvrRem;
       }
 }
?>
