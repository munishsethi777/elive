<?php
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/MainDB.php");
require_once($ConstantsArray['dbServerUrl'] . "BusinessObjects/M2MSite.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils/MailerUtils.php");
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/BusinessObjects/ChannelConfiguration.php");
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/M2MSynchronizerDataStore.php");
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDStackDataStore.php");
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDStackDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/WQDOpacityFileDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils/StringUtils.php");
require_once($ConstantsArray['dbServerUrl'] . "Managers/CalibrationMgr.php");
//require_once($ConstantsArray['dbServerUrl'] ."/Utils/CalibrationService.php");
class M2MSynchronizerDataStore{
    private static  $m2mSynchronizerDataStore;
    private static $db;
    private static $SELECTALL = "select ms.* from m2msites ms inner join folder f on ms.folderseq = f.seq where f.isenable = 1 and ms.vendor like :vendor";
    private static $UPDATE_LAST_SYNCEDON = "update m2msites set lastsyncedon = :lastsyncedon where folderseq = :folderseq";
    
    private static $INSERT_M2MSITE = "insert into m2msites(folderseq,m2msitecode,lastsyncedon,vendor) values(:folderseq,:sitecode,:lastsyncedon,:vendor)";
    private static $FIND_BY_FOLDER_SEQ = "select * from m2msites where folderseq = :folderseq";
    private static $UPDATE_BY_FOLDER_SEQ = "update m2msites set m2msitecode = :sitecode , vendor = :vendor where folderseq = :folderseq";
    private static $DELETE = "delete from m2msites where folderseq = :folderseq";
    
    
    public function __construct(){
        self::$db = MainDB::getInstance();
    }
    
    public static function getInstance()
    {
        if (!self::$m2mSynchronizerDataStore)
        {
            self::$m2mSynchronizerDataStore = new M2MSynchronizerDataStore();
            return self::$m2mSynchronizerDataStore;
        }
        return self::$m2mSynchronizerDataStore;
    }
    
    private function getChannelNumbers($response,$value,$folderSeq){
        $channels = $value->parameters;
        $units = $value->units;
        $chNoArr = $this->addChannelsIfnotExist($response,$channels,$units,$folderSeq);
        return $chNoArr;
    }
    
    private function getStatusChNumbers($folderSeq){
        $CCDS = ChannelConfigurationDataStore::getInstance();
        $statusChNumbers = $CCDS->FindStatusChNoByFolderSeq($folderSeq);
        return $statusChNumbers;
    }
    
    
    /*private function addChannelsIfnotExist($channels,$units,$folderSeq){
     $i = 0;
     $chNoArr = array();
     $CCDS = ChannelConfigurationDataStore::getInstance();
     $existingChannels = $CCDS->FindByFolderSeq($folderSeq);
     $number = 1;
     if(!empty($existingChannels)){
     $number = intval(current($existingChannels)->getChannelNumber());
     }
     foreach($channels as $ch){
     $isExist = false;
     if(!empty($existingChannels)){
     foreach($existingChannels as $existingChannel){
     $channelName = $existingChannel->getChannelName();
     if(strtolower($channelName) == strtolower($ch)){
     $chNoArr[$ch] = $existingChannel->getChannelNumber();
     $isExist = true;
     break;
     }
     }
     }
     if(!$isExist){
     $chConf = new ChannelConfiguration();
     $chConf->setChannelName($ch);
     $chConf->setChannelNumber($number);
     $chConf->setFolderSeq($folderSeq);
     $chConf->setChannelStatusFlag(1);
     $chConf->setChannelUnit($units[$i]);
     $chConf->setIsVisible(1);
     $CCDS->Save($chConf);
     $chNoArr[$ch] = $number;
     $number++;
     }
     $i++;
     }
     return $chNoArr;
     }*/
    
    private function addChannelsIfnotExist($response,$channels,$units,$folderSeq){
        $i = 0;
        $chNoArr = array();
        $CCDS = ChannelConfigurationDataStore::getInstance();
        $existingChannels = $CCDS->FindChNameByFolderSeq($folderSeq);
        $number = 1;
        if(empty($existingChannels)){
            foreach($channels as $ch){
                $chConf = new ChannelConfiguration();
                $chConf->setChannelName($ch);
                $chConf->setChannelNumber($number);
                $chConf->setFolderSeq($folderSeq);
                $chConf->setChannelStatusFlag(1);
                $chConf->setChannelUnit($units[$i]);
                $chConf->setIsVisible(1);
                $CCDS->Save($chConf);
                $chNoArr[strtolower($ch)] = $number;
                $number++;
            }
        }else{
            $channelArr = array_map('strtolower', $channels);
            if(count($channelArr) != count($existingChannels)){
                //if($folderSeq != 103){
                $this->channelMismatch($response,$existingChannels,$channels,$folderSeq);
                throw new RuntimeException("Channel Mismatch Exception");
                //}
            }
            foreach($existingChannels as $no=>$existingChannel){
                if(!in_array(strtolower($existingChannel), $channelArr)){
                    //if($folderSeq != 103){
                    $this->channelMismatch($response,$existingChannels,$channels,$folderSeq);
                    $chNoArr = array();
                    throw new RuntimeException("Channel Mismatch Exception");
                    //}
                }
                $chNoArr[strtolower($existingChannel)] = $no;
            }
        }
        return $chNoArr;
    }
    
    
    private function channelMismatch($response,$oldChannels,$newChannels,$folderSeq){
        $folderDataStore = FolderDataStore::getInstance();
        $folder = $folderDataStore->FindBySeq($folderSeq);
        $folderDataStore->updateIsEnable($folderSeq, 0);
        $oldChannels = implode(", ", $oldChannels);
        $newChannels = implode(", ", $newChannels);
        echo "<br>Response :- " . $response;
        echo "<br>Old Channels :- " . $oldChannels;
        echo "<br>New Channels :- " . $newChannels;
        MailerUtils::sendChannelMismatchNotification($response,$oldChannels,$newChannels,$folder);
    }
    
    private function addChannels($channels,$units,$folderSeq){
        $i = 0;
        $chNoArr = array();
        $CCDS = ChannelConfigurationDataStore::getInstance();
        foreach($channels as $ch){
            $chConf = new ChannelConfiguration();
            $chConf->setChannelName($ch);
            $number = $i + 1;
            $chConf->setChannelNumber($number);
            $chConf->setFolderSeq($folderSeq);
            $chConf->setChannelStatusFlag(1);
            $chConf->setChannelUnit($units[$i]);
            $CCDS->Save($chConf);
            $chNoArr[$ch] = $number;
            $i++;
        }
        return $chNoArr;
    }
    private function updateLastSyncedOnWithAdd90Min($lastSyncedDate,$folderSeq){
        $syncdate = DateTime::createFromFormat('d-m-Y H:i:s', $lastSyncedDate);
        $syncdate = $syncdate->modify("+90 minutes");
        if($syncdate < new DateTime()){
            $d = $syncdate->format('Y-m-d H:i:s');
            $this->updateLastSyncedOn($d,$folderSeq,false);
            echo(" <br>Updated Last sync date to with 90Mins :- " .$d. " for folder".$folderSeq);
        }
        
    }
    
    private function getPast90MinTime(){
        $now = new DateTime();
        $now = $now->modify("-90 minutes");
        return $now;
    }
    
    public function SaveSyncedWQDStackData($jsonString,$folderSeqs,$lastSynchDates){
        if(!StringUtils::is_json($jsonString)){
            throw new RuntimeException("Invalid Json String");
        }
        $arr = json_decode($jsonString);
        $fileDataObjArr = array();
        $WQDSDS = WQDStackDataStore::getInstance();
        $WOpacityDS = WQDOpacityFileDataStore::getInstance();
        $FDS = FolderDataStore::getInstance();
        $calibrationMgr = CalibrationMgr::getInstance();
        $message = "";
        $onlineStations = array_fill_keys(array_values($folderSeqs), 0);
        $isDataExistForStations = array_fill_keys(array_values($folderSeqs), false);
        
        if(!empty($arr)){
            foreach($arr as $key=>$value){
                $stationCode = $value->siteCode;
                $folderSeq = $folderSeqs[$stationCode];
                
                try{
                    $folder =  $FDS->FindBySeq($folderSeq);
                    $isOpacity = $folder->getIsOpacity();
                    $locationSeq = $folder->getLocationSeq();
                    $dummyIndex = null;
                    $onlineStations[$folderSeq] = 1;
                    $isDataExistForStations[$folderSeq] = true;
                    echo("<br/><br/>Station Code :- " . $stationCode);
                    //var_dump($value);
                    $channels = $value->parameters;
                    if(!empty($isOpacity)){
                        if (in_array('dummy', $channels))
                        {
                            
                            $dummyIndex = array_search('dummy',$channels);
                            unset($channels[$dummyIndex]);
                        }else if (in_array('Dummy', $channels))
                        {
                            $dummyIndex = array_search('Dummy',$channels);
                            unset($channels[$dummyIndex]);
                        }
                    }
                    $value->parameters = $channels;
                    $data = $value->data;
                    $lastSyncDb = $lastSynchDates[$folderSeq];
                    $count = count($data);
                    echo(" Rows found :- " . $count);
                    if($count == 1){
                        $now = $this->getPast90MinTime();
                        $sdate = DateTime::createFromFormat('d-m-Y H:i:s',$lastSyncDb);
                        if($lastSyncDb == $data[0]->Key && $sdate < $now){
                            $this->updateLastSyncedOnWithAdd90Min($lastSyncDb,$folderSeq);
                            continue;
                        }
                    }
                    $chNoArr = $this->getChannelNumbers($jsonString,$value,$folderSeq);
                    $statusChNoArr = $this->getStatusChNumbers($folderSeq);
                    if(empty($chNoArr)){
                        continue;
                    }
                    $syncDate = "";
                    $fileDataObjArr = array();
                    $lastValue = "";
                    $calLasValue = "";
                    $isFirstRow = true;
                    foreach($data as $key=>$val){
                        $valueArr = $val->Value;
                        $syncdate = $val->Key;
                        $fileDataObj = $this->getFileDataObj($syncdate,$folderSeq);
                        $chValueStatusArr = array();
                        $i = 0;
                        foreach($channels as $ch){
                            $index = $chNoArr[strtolower($ch)];
                            //saving status value from status parameter
                            if(isset($statusChNoArr[$index])){
                                $statusChName = $statusChNoArr[$index];
                                $statusIndex= array_search($statusChName, $channels);
                                $status = $valueArr[$statusIndex][0];
                                $valueArr[$i][1] = $status;
                            }
                            if(!empty($isOpacity)){
                                unset($valueArr[$dummyIndex]);
                                $i++;
                                $chValueStatusArr[$index]= $valueArr[$i];
                            }else{
                                $chValueStatusArr[$index]= $valueArr[$i];
                                
                            }
                            if(strtolower($ch) == "status"){
                                $val = $valueArr[$i][0];
                                if((!$isFirstRow) && ($lastValue == "0.00" && $val != "0.00")){
                                    $calType = "";
                                    if($val == "3,072.00" || $val == "2,049.00"){
                                        $calType = "zero";
                                    }else if($val == "32,768.00" || $val == "-32,768.00"){
                                        $calType = "span";
                                    }else if($val == "8,192.00"){
                                        $calType = "purge";
                                    }
                                    $calibrationMgr->saveCalibrationInfo($calType, $folderSeq, 0,$syncdate);
                                    echo ("<br>Saved Calibration History for folderseq - " . $folderSeq . " <br>calibration type is - " . $calType . "<br>");
                                }
                                $lastValue = $valueArr[$i][0];
                            }
                            if($locationSeq == 18 && !$isFirstRow && strtolower($ch) == "no2.flag" ){
                                $no2FlagIndex= array_search("NO2.FLAG", $channels);
                                $no2FlagValue = $valueArr[$no2FlagIndex][0];
                                $this->saveCalibrationHistory($no2FlagValue,$calLasValue,$folderSeq, $syncdate);
                                $calLasValue = $no2FlagValue;
                            }
                            if($locationSeq == 18 && $isFirstRow && strtolower($ch) == "no2.flag" ){
                                $no2FlagIndex= array_search("NO2.FLAG", $channels);
                                $no2FlagValue = $valueArr[$no2FlagIndex][0];
                                $calLasValue = $no2FlagValue;
                            }
                            $i++;
                        }
                        if($isFirstRow){
                            $isFirstRow  = false;
                            if($folderSeq == 287 || $folderSeq == 169){
                                continue;
                            }
                        }
                        $fileDataObj->setChannels($chValueStatusArr);
                        array_push($fileDataObjArr,$fileDataObj);
                    }
                    
                    if(!empty($data) && $folderSeq == 314){
                        $lastData = end($data);
                        $this->uploadFile($lastData);
                    }
                    if(empty($isOpacity)){
                        $WQDSDS->saveSyncedData($fileDataObjArr,true);
                    }else{
                        $WOpacityDS->saveSyncedData($fileDataObjArr,true);
                    }
                    
                }catch(Exception $e){
                    $message .= "Error During M2MSynchronizer for SiteCode " . $stationCode . " : -" . $e->getMessage() . "<br/>";
                    //var_dump($fileDataObjArr);
                    // $logger = Logger::getLogger("myDBLogger");
                    // $logger->error($message);
                    $FDS->updateIsEnable($folderSeq,0);
                    $FDS->updateIsOnline($folderSeq,0);
                    $onlineStations[$folderSeq] = 0;
                }
            }
        }
        if(!empty($message)){
            MailerUtils::sendError($message,"Error During M2MSynchronizer");
        }
        
        $M2MDs = M2MSynchronizerDataStore::getInstance();
        foreach($onlineStations as $folderSeq=>$isOnline){
            $lastSyncDate = $lastSynchDates[$folderSeq] ;
            $sdate = DateTime::createFromFormat('d-m-Y H:i:s',$lastSyncDate);
            $folder = $FDS->FindBySeq($folderSeq);
            if(empty($isOnline)){
                $folderLastSynched = $folder->getLastSynchedOn();
                $folderLastSynched = new DateTime($folderLastSynched);
                $FDS->markAsOffline($folderSeq,$folderLastSynched);
            }else{
                $isNotifyFailure = $folder->getHasNotifyUploadFailure();
                if(!empty($isNotifyFailure)){
                    $FDS->notifyUploadStarted($folder);
                }
                $FDS->updateIsOnline($folderSeq,$isOnline);
            }
            $lastSyncDate = $lastSynchDates[$folderSeq] ;
            $sdate = DateTime::createFromFormat('d-m-Y H:i:s',$lastSyncDate);
            $now = $this->getPast90MinTime();
            $isDataExist = $isDataExistForStations[$folderSeq];
            if(!$isDataExist && $sdate < $now){
                $this->updateLastSyncedOnWithAdd90Min($lastSyncDate,$folderSeq);
            }
        }
    }
    private function uploadFile($data){
        $date = $data->Key; //20-11-2019 01:25:50
        $values = $data->Value;
        $dateObj = DateUtils::StringToDateByGivenFormat("d-m-Y H:i:s", $date);
        $dateStr = $dateObj->format("dmYHis");
        $dataFile = fopen("/home/envirote/public_html/Repository/elive/iitdelhi/latest/latest.csv", "w") or die("Unable to open file!");
        $dataRowArr = array();
        foreach ($values as $value){
           array_push($dataRowArr,$value[0]);
        }
        $dataRow = "";
        if(!empty($dataRowArr)){
            $dataRow = implode(",", $dataRowArr);
        }
        $txt = $dateStr . ",". $dataRow;
        fwrite($dataFile, $txt);
        fclose($dataFile);
    }
     private function saveCalibrationHistory($val,$lastValue,$folderSeq,$syncdate){
        $lastIsCalStarted = CalibrationService::isCalbirationStart($lastValue);
        if(!$lastIsCalStarted){
            $isCalStarted =  CalibrationService::isCalbirationStart($val);
            if($isCalStarted){
                $calibrationMgr = CalibrationMgr::getInstance();
                $calibrationMgr->saveCalibrationInfo("calibration", $folderSeq, 0, $syncdate);
            }
        }
    }
    private function getFileDataObj($synchDate,$folderSeq){
        $fileDataObj = new WQDData();
        $fileDataObj->setChecksum(0);
        $syncdate = DateTime::createFromFormat('d-m-Y H:i:s', $synchDate);
        $d = $syncdate->format('Y-m-d H:i:s');
        $fileDataObj->setDataDate($d);
        $fileDataObj->setFolderSeq($folderSeq);
        $fileDataObj->setReportNo(1);
        return  $fileDataObj;
    }
    
    public function FindAll($vendor = "envirozone"){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$SELECTALL);
        $stmt->bindValue(':vendor', $vendor);
        $stmt->execute();
        $m2mSites = array();
        $siteCodes = array();
        $folderSeqs = array();
        $lastSyncDates = array();
        $lastSyncDateBySation = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            
            $siteCode = $row["m2msitecode"];
            array_push($siteCodes,$siteCode);
            $lastSynedOn = Date('d-m-Y H:i:s', strtotime($row["lastsyncedon"]));
            
            //$lastSynedOn = DateTime::createFromFormat('Y-m-d H:i:s', $row["lastsyncedon"])->modify('+1 seconds');
            //$lastSynedOn = $lastSynedOn->format('d-m-Y H:i:s');
            
            array_push($lastSyncDates,$lastSynedOn);
            $folderSeqs[$siteCode] = $row["folderseq"];
            $lastSyncDateBySation[$row["folderseq"]] = $lastSynedOn;
            
        }
        $stmt->closeCursor();
        $m2mSites["siteCode"] = $siteCodes;
        $m2mSites["lastSyncDate"] = $lastSyncDates;
        $mainArr = array();
        array_push($mainArr,$m2mSites);
        array_push($mainArr,$folderSeqs);
        array_push($mainArr,$lastSyncDateBySation);
        return $mainArr;
    }
    public function FindByFolderSeq($seq){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$FIND_BY_FOLDER_SEQ);
        $stmt->bindValue(':folderseq', $seq);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $m2mSiteObj = null;
        if(!empty($row)){
            $m2mSiteObj =  self::populateObject($row);
        }
        $error = $stmt->errorInfo();
        return $m2mSiteObj;
    }
    
    public function isAlreadyExist($seq){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$FIND_BY_FOLDER_SEQ);
        $stmt->bindValue(':folderseq', $seq);
        $stmt->execute();
        $rowCount = $stmt->rowCount();
        return $rowCount > 0;
    }
    public function updateLastSyncedOn($lastSyncedOn,$folderSeq,$isDataExist = true){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$UPDATE_LAST_SYNCEDON);
        $stmt->bindValue(':lastsyncedon', $lastSyncedOn);
        $stmt->bindValue(':folderseq', $folderSeq);
        $stmt->execute();
        $error = $stmt->errorInfo();
        if($isDataExist){
            $FDS = FolderDataStore::getInstance();
            $FDS->updateLastSyncedOn($lastSyncedOn,$folderSeq);
        }
    }
    public function delete($folderSeq){
        try{
            $SQL = self::$DELETE;
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare($SQL);
            $stmt->bindValue(':folderseq', $folderSeq);
            $stmt->execute();
            $error = $stmt->errorInfo();
            if($error[2] <> ""){
                throw new RuntimeException($error[2]);
            }
        }catch(Exception $e){
            $logger = Logger::getLogger($ConstantsArray["logger"]);
            $logger->error("Error During Delete M2MSite forlderseq $folderSeq : - ". $e->getMessage());
        }
    }
    public function saveM2MSite($m2mSite){
        try{
            $isExists = $this->isAlreadyExist($m2mSite->getFolderSeq());
            $SQL = self::$INSERT_M2MSITE;
            $conn = self::$db->getConnection();
            $isUpdate = false;
            if($isExists){
                $SQL = self::$UPDATE_BY_FOLDER_SEQ;
            }
            $stmt = $conn->prepare($SQL);
            $stmt->bindValue(':folderseq', $m2mSite->getFolderSeq());
            $stmt->bindValue(':sitecode',$m2mSite->getSiteCode());
            $stmt->bindValue(':vendor',$m2mSite->getVendor());
            if(!$isExists){
                $stmt->bindValue(':lastsyncedon',$m2mSite->getLastSyncedOn());
            }
            //echo $SQL . "<br>Site Code - " .$m2mSite->getSiteCode();
            //return;
            $stmt->execute();
            $error = $stmt->errorInfo();
            if($error[2] <> ""){
                throw new RuntimeException($error[2]);
            }
        }catch(Exception $e){
            $logger = Logger::getLogger($ConstantsArray["logger"]);
            $logger->error("Error During Save M2MSite : - ". $e->getMessage());
        }
    }
    public function populateObject($rsItem){
        $seq_ = $rsItem["seq"] ;
        $folderSeq = $rsItem["folderseq"];
        $siteCode = $rsItem["m2msitecode"];
        $lastSyncedOn = $rsItem["lastsyncedon"];
        $vendor = $rsItem["vendor"];
        
        
        $m2mSite = new M2MSite();
        $m2mSite->setSeq($seq_);
        $m2mSite->setLastSyncedOn($lastSyncedOn);
        $m2mSite->setSiteCode($siteCode);
        $m2mSite->setFolderSeq($folderSeq);
        $m2mSite->setVendor($vendor);
        
        return $m2mSite;
        
    }
}
?>
