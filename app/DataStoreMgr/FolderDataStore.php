<?php
   require_once('IConstants.inc');
   require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/MainDB.php");
   require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/Folder.php");
   require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/M2MSynchronizerDataStore.php");
   require_once($ConstantsArray['dbServerUrl'] . "/Utils/FilterUtil.php");
   require_once($ConstantsArray['dbServerUrl'] . "/Utils/DateUtils.php");
   
   require_once($ConstantsArray['dbServerUrl'] . "/StringConstants.php");


class FolderDataStore{
     private static $folderDataStore;
     private static $db;
     private static $INSERT = "INSERT INTO folder (foldername ,details,locationseq,isvisible,isenable,stationtype,parsertype,parentfolderseq,lastsyncedseq,createdon,isopacity,isonline,stationvendor,cameraurl) VALUES(:foldername, :details, :locationseq,:isvisible,:isenable,:stationtype,:parsertype,:parentfolderseq,:lastsyncedseq,:createdon,:isopacity,:isonline,:stationvendor,:cameraurl)";

     private static $UPDATE = "update folder set foldername = :foldername , details = :details ,locationseq = :locationseq, isvisible=:isvisible,isenable=:isenable, stationtype=:stationtype,parsertype = :parsertype , parentfolderseq=:parentfolderseq, lastsyncedseq=:lastsyncedseq, isopacity = :isopacity, stationvendor=:stationvendor , cameraurl=:cameraurl where seq = :folderseq ";

     private static $UPDATE_META = "update folder set category = :category ,industrycode =:industrycode, industryname = :industryname, address = :address, city = :city, state = :state, zipcode = :zipcode, latitude = :latitude, longitude = :longitude, email = :email , mobile = :mobile, stationname=:stationname, deviceid=:deviceid, vendor=:vendor, make=:make, model=:model, certificationsystem=:certificationsystem, uploadfrequency=:uploadfrequency, iscalliberationenabled=:iscalliberationenabled, calliberationurl=:calliberationurl,loggerid=:loggerid where seq = :folderseq";

     private static $SELECTALL = "SELECT folder.*, location.name as locationname,location.locationfolder as locationfolder,location.repositoryfolder as repositoryfolder from folder, location where folder.locationseq = location.seq and folder.seq not in (select folderseq from m2msites)";
     private static $SELECT_ALL_WITH_M2M_FOLDERS = "SELECT folder.*, location.name as locationname,location.locationfolder as locationfolder from folder, location where folder.locationseq = location.seq";
     private static $SELECT_ACTIVE = "SELECT * from folder where isenable = 1";
     private static $DELETE = "delete from folder where seq = :seq";
     private static $FIND_BY_SEQ = "select * from folder where seq = :seq";
     private static $FIND_BY_LOCATION = "select seq from folder where  foldername = :foldername and locationseq  = :locationseq ";
     private static $FIND_ALL_BY_LOCATION = "select * from folder  left JOIN folderuser ON  folder.seq = folderuser.folderseq where folder.locationseq = :locationseq and folderuser.userseq = :userseq";
     private static $FIND_ALL_BY_LOCATIONSEQ = "SELECT folder.*, location.name as locationname,location.locationfolder as locationfolder from folder, location where folder.locationseq = location.seq and folder.parentfolderseq is NULL ";
     private static $FIND_PARENT_BY_LOCATIONSEQ = "SELECT folder.*, location.name as locationname,location.locationfolder as locationfolder from folder inner join location on folder.locationseq = location.seq inner join channelgroups cg on folder.seq = cg.folderseq where folder.parentfolderseq is NULL ";
     private static $FIND_SEQ_BY_LOCATIONSEQ = "SELECT folder.seq from folder, location where folder.locationseq = location.seq ";
     private static $UPDATE_LAST_SYNCHDATE = "update folder set lastsynchedon = now() where seq = :seq";
     private static $UPDATE_LAST_SYNCEDON = "update folder set lastsynchedon = :lastsynchedon where seq = :seq";
     private statiC $UPDATE_LAST_REMINDED = "update folder set lastremindedon = now() where seq = :seq";
     private static $UPDATE_LAST_PARSED = "update folder set lastparsedon = now() where seq = :seq";
     private static $UPDATE_IS_ENABLED = "update folder set isenable=:isenable where seq = :seq";
     private static $UPDATE_LAST_SENT_SMS_ON = "update folder set lastsentsmson=:lastsentsmson where seq = :seq";
     private static $UPDATE_IS_VISIBLE = "update folder set isvisible=:isvisible where seq = :seq";   
     private static $UPDATE_IS_ONLINE = "update folder set isonline = :isonline where seq = :folderseq";
     private static $UPDATE_IS_ONLINE_AND_HAS_NOTIFY = "update folder set isonline = :isonline , hasnotifyuploadfailure = 0 where seq = :folderseq";
     private static $UPDATE_HAS_NOTIFY_UPLOAD_FAILURE = "update folder set hasnotifyuploadfailure = :hasnotifyuploadfailure where seq = :folderseq";
     private static $FIND_CHILD_BY_LOCATIONSEQ = "SELECT * from folder where folder.parentfolderseq = :parentfolderseq";
     private static $UPDATE_LAST_SYCNED_SEQ = "update  folder set lastsyncedseq = :lastsyncedseq where seq = :seq";
     private static $UPDATE_LAST_SPCB_SYCNED_SEQ = "update  folder set lastspcbsyncedseq = :lastspcbsyncedseq where seq = :seq";
     private static $FIND_OPACITY_BY_LOCATIONS = "select isopacity,locationseq from folder where locationseq in ";
     private static $UPDATE_HAS_PENDING_CPCB_DATA = "update folder set haspendingcpcbdata = :haspendingcpcbdata where seq = :seq";
     private static $UPDATE_AQMS_LAST_UPLOADED_DATE = "update folder set aqmslastuploadeddate = :lastDate where seq = :folderseq";
      
     public function __construct(){
       self::$db = MainDB::getInstance();
     }

   public static function getInstance(){
        if (!self::$folderDataStore)
        {
            self::$folderDataStore = new FolderDataStore();
            return self::$folderDataStore;
        }
        return self::$folderDataStore;
    }
   private static function updateCurrentDateAction($SQL, $seq){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare($SQL);
        $stmt->bindValue(":seq",$seq);
        $stmt->execute();
   }
   
   public function getIsOpacityByLocations($locations){
	   	$conn = self::$db->getConnection();	   
	   	$sql = self::$FIND_OPACITY_BY_LOCATIONS . "($locations)";
	   	$stmt = $conn->prepare($sql);	   
	   	$stmt->execute();
	   	$row = $stmt->fetchAll();
	   	return $row;
   }
   
   public function updateLastSyncedOn($lastSyncedOn,$folderSeq){
        $conn = self::$db->getConnection();
        //$logger = Logger::getLogger ( "eliveLogger" );
        //$logger->info("updating lastSynchedOn - ". $lastSyncedOn . " for folderSeq-".$folderSeq);
        $stmt = $conn->prepare(self::$UPDATE_LAST_SYNCEDON);
        $stmt->bindValue(':lastsynchedon', $lastSyncedOn);
        $stmt->bindValue(':seq', $folderSeq);
        $stmt->execute();
        $error = $stmt->errorInfo();
    }
     
    public function updateLastSyncedSeq($lastSyncedSeq,$folderSeq){
     	$conn = self::$db->getConnection();
     	$stmt = $conn->prepare(self::$UPDATE_LAST_SYCNED_SEQ);
     	$stmt->bindValue(':lastsyncedseq', $lastSyncedSeq);
     	$stmt->bindValue(':seq', $folderSeq);
     	$stmt->execute();
     	$error = $stmt->errorInfo();
    }
    
    public function updateLastSpcbSyncedSeq($lastSyncedSeq,$folderSeq){
    	$conn = self::$db->getConnection();
    	$stmt = $conn->prepare(self::$UPDATE_LAST_SPCB_SYCNED_SEQ);
    	$stmt->bindValue(':lastspcbsyncedseq', $lastSyncedSeq);
    	$stmt->bindValue(':seq', $folderSeq);
    	$stmt->execute();
    	$error = $stmt->errorInfo();
   }
   
   public function updateLastSynchDate($seq){
        $SQL = self::$UPDATE_LAST_SYNCHDATE;
        self::updateCurrentDateAction($SQL,$seq);
   }
   public function updateLastParseDate($seq){
        $SQL = self::$UPDATE_LAST_PARSED;
        self::updateCurrentDateAction($SQL,$seq);
   }
   public function updateLastReminderDate($seq){
        $SQL = self::$UPDATE_LAST_REMINDED;
        self::updateCurrentDateAction($SQL,$seq);
   }
   public function updateIsEnable($seq,$isEnabled){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$UPDATE_IS_ENABLED);
        $stmt->bindValue(":isenable",$isEnabled);
        $stmt->bindValue(":seq",$seq);
        $stmt->execute();
        $error = $stmt->errorInfo(); 
        MailerUtils::sendUpdateStationStatus($seq, $isEnabled);
   }
   
   public function updateLastSentSMSOn($seq,$sentSmsOn){
	   	$conn = self::$db->getConnection();
	   	$stmt = $conn->prepare(self::$UPDATE_LAST_SENT_SMS_ON);
	   	$dateValue = null;
	   	if(!empty($sentSmsOn)){
	   		$dateValue = $sentSmsOn->format('Y-m-d H:i:s');
	   	}
	   	$stmt->bindValue(":lastsentsmson",$dateValue);
	   	$stmt->bindValue(":seq",$seq);
	   	$stmt->execute();
	   	$error = $stmt->errorInfo();
	   	if($error[2] <> ""){
	   		$logger = Logger::getLogger($ConstantsArray["logger"]);
	   		$logger->error("Error During update Last SentSMSOn : - " . $error[2]);
	   	}
   }
   
   public function updateIsVisible($seq,$isEnabled){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$UPDATE_IS_VISIBLE);
        $stmt->bindValue(":isvisible",$isEnabled);
        $stmt->bindValue(":seq",$seq);
        $stmt->execute();
        $error = $stmt->errorInfo();

   }
   public function Save(Folder $folder){
      try{
          $SQL = self::$INSERT;
          $isUpdate = false;
          if($folder->getSeq() != null && $folder->getSeq()<> "" && $folder->getSeq() > 0){
             $SQL = self::$UPDATE;
             $isUpdate = true;
          }
          $conn = self::$db->getConnection();
          $stmt = $conn->prepare($SQL);
          $stmt->bindValue(':foldername', $folder->getFolderName());
          $stmt->bindValue(':locationseq', $folder->getLocationSeq());
          $stmt->bindValue(':details',$folder->getDetails());
          $stmt->bindValue(':isvisible',$folder->getIsVisible());
          $stmt->bindValue(':isenable',$folder->getIsEnable());
          $stmt->bindValue(':stationtype',$folder->getStationType());
          $stmt->bindValue(':parsertype',$folder->getParserType());  
          $stmt->bindValue(':parentfolderseq',$folder->getParentSeq());
          $stmt->bindValue(':lastsyncedseq',$folder->getLastSynchedOn());  
          $stmt->bindValue(':isopacity',$folder->getIsOpacity());   
          $stmt->bindValue(':stationvendor',$folder->getStationVendor());
          $stmt->bindValue(':cameraurl',$folder->getCameraUrl());
          if($isUpdate){
              $stmt->bindValue(':folderseq',$folder->getSeq());
              $seq = $folder->getSeq();
          }else{
	          	$createdOn = new DateTime();
	          	$createdOn = $createdOn->format('Y-m-d H:i:s');
	          	$stmt->bindValue(':createdon',$createdOn);
	          	$stmt->bindValue(':isonline',0);
          }
          $stmt->execute();
          if(!$isUpdate){
             $seq = $conn->lastInsertId();
             $folder->setSeq($seq);
          }
          //I will be put code here for throw exception and show on the screen
          $error = $stmt->errorInfo();
          if($error[2] <> ""){
            throw new Exception($error[2]);
          }
      }catch(Exception $e){
          $logger = Logger::getLogger($ConstantsArray["logger"]);
          $logger->error("Error During Save Folder : - " . $e->getMessage());
      }

   }
    public function getAllStationsForCPCBApi(){
        $conn = self::$db->getConnection();
        $sql = self::$SELECT_ACTIVE . " and haspendingcpcbdata = 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $folderArray = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $folderObj = new Folder();
            $folderObj =  self::populateObject($row);
            $locationSeq = $folderObj->getLocationSeq();
            $array = array();
            if(array_key_exists($locationSeq, $folderArray)){
                $array = $folderArray[$locationSeq];
            }else{
                $array = array();
            }
            array_push($array, $folderObj);
            $folderArray[$locationSeq] = $array;
        }
        return $folderArray;
    }
    
    public function updateHasPendingCpcbData($hasPending,$folderSeq){
       //echo("<br>\r\n Updating haspending flag ". $folderSeq);
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$UPDATE_HAS_PENDING_CPCB_DATA);
        $stmt->bindValue(':haspendingcpcbdata', $hasPending);
        $stmt->bindValue(':seq', $folderSeq);
        $stmt->execute();
        $error = $stmt->errorInfo();
    } 
   public function updateMeta($folder){
      $SQL = self::$UPDATE_META;
      $conn = self::$db->getConnection();
      $stmt = $conn->prepare($SQL);
      $category = $folder->getCategory();
      $stmt->bindValue(':category',empty($category) ? null : $category);
      $industryCode = $folder->getIndustryCode();
      $stmt->bindValue(':industrycode',empty($industryCode) ? null : $industryCode);
      $loggerId = $folder->getLoggerID();
      $stmt->bindValue(':loggerid',empty($loggerId) ? null : $loggerId);
      $industryName = $folder->getIndustryName();
      $stmt->bindValue(':industryname',empty($industryName) ? null : $industryName);
      $address = $folder->getAddress();
      $stmt->bindValue(':address',empty($address) ? null : $address);
      $city = $folder->getCity();
      $stmt->bindValue(':city',empty($city) ? null : $city);
      $state = $folder->getState();
      $stmt->bindValue(':state',empty($state) ? null : $state);
      $zipCode = $folder->getZipcode();
      $stmt->bindValue(':zipcode',empty($zipCode) ? null : $zipCode);
      $latitude = $folder->getLatitude();
      $stmt->bindValue(':latitude',empty($latitude) ? null : $latitude);
      $longitude = $folder->getLongitude();
      $stmt->bindValue(':longitude',empty($longitude) ? null : $longitude);
      $email = $folder->getEmail();
      $stmt->bindValue(':email',empty($email) ? null : $email);
      $mobile = $folder->getMobile();
      $stmt->bindValue(':mobile',empty($mobile) ? null : $mobile);
      $stationName = $folder->getStationName();
      $stmt->bindValue(':stationname',empty($stationName) ? null : $stationName);
      $deviceId = $folder->getDeviceId();
      $stmt->bindValue(':deviceid',empty($deviceId) ? null : $deviceId);
      $vendor = $folder->getVendor();
      $stmt->bindValue(':vendor',empty($vendor) ? null : $vendor);
      $make = $folder->getMake();
      $stmt->bindValue(':make',empty($make) ? null : $make);
      $model = $folder->getModel();
      $stmt->bindValue(':model',empty($model) ? null : $model);
      $certificationSystem = $folder->getCertificationsSystem();
      $stmt->bindValue(':certificationsystem',empty($certificationSystem) ? null : $certificationSystem);
      $isCalliberationEnabled = $folder->getIsCalliberationEnabled();
      $stmt->bindValue(':iscalliberationenabled', $isCalliberationEnabled);
      $calliberationURL = $folder->getCalliberationURL();
      $stmt->bindValue(':calliberationurl', $calliberationURL);
      $stmt->bindValue(':uploadfrequency',empty($uploadFrequency) ? null : $uploadFrequency);
      $stmt->bindValue(':folderseq',$folder->getSeq());
      $stmt->execute();
   }
   public function FindAll($isM2MShow = false){
        $conn = self::$db->getConnection();
        $sql = self::$SELECTALL;
        if($isM2MShow){
             $sql = self::$SELECT_ALL_WITH_M2M_FOLDERS;
        }
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $folderArray = Array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $folderObj = new Folder();
            $folderObj =  self::populateObject($row);
            $folderArray[$folderObj->getSeq()] = $folderObj;
        }
         return $folderArray;
   }
   
   public function FindActiveAll(){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$SELECT_ACTIVE);
        $stmt->execute();
        $folderArray = Array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $folderObj = new Folder();
            $folderObj =  self::populateObject($row);
            $folderArray[$folderObj->getSeq()] = $folderObj;
        }
         return $folderArray;
   }

      public function deleteBySeq($seq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$DELETE);
            $stmt->bindValue(':seq', $seq);
            $stmt->execute();
            $error = $stmt->errorInfo();
          }


       public function FindBySeq($seq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_BY_SEQ);
            $stmt->bindValue(':seq', $seq);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $FolderObj =  self::populateObject($row);
            return $FolderObj;
       }
        public function FindByLoationSeq($locationSeq,$userSeq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_ALL_BY_LOCATION);
            $stmt->bindValue(':locationseq', $locationSeq);
            $stmt->bindValue(':userseq', $userSeq);
            $stmt->execute();
            $folderArray = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $folderObj = new Folder();
                $folderObj =  self::populateObject($row);
                $folderArray[$folderObj->getSeq()] = $folderObj;
            }
         return $folderArray;
       }

      public function FindAllForAdmin(){
	       	$conn = self::$db->getConnection();
	       	$query = self::$FIND_ALL_BY_LOCATIONSEQ;
	       	$stmt = $conn->prepare($query);
	       	//$stmt->bindValue(':locationseq', $locationSeq);
	       	$stmt->execute();
	       	$folderArray = Array();
	       	//$count = $stmt->fetchAll();
	       	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	       		$folderObj = new Folder();
	       		$folderObj =  self::populateObject($row);
	       		$folderArray[$folderObj->getSeq()] = $folderObj;
	       	}
	       	return $folderArray;
       }
       
      public function FindAllParents($isAmin){      	
       	$conn = self::$db->getConnection();       
       	$query = self::$FIND_PARENT_BY_LOCATIONSEQ;
       	if(!$isAmin){
       		$LDS = LocationDataStore::getInstance();
       		$locationSeqs = $LDS->getLoggedInUserOrManagerLocationSeqs();
       		$locationSeqs = implode(",",$locationSeqs);
       		$query = $query . " and folder.locationseq in ($locationSeqs)";
       	}
       	$stmt = $conn->prepare($query);
       	//$stmt->bindValue(':locationseq', $locationSeq);
       	$stmt->execute();
       	$folderArray = Array();
       	//$count = $stmt->fetchAll();
       	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
       		$folderObj = new Folder();
       		$folderObj =  self::populateObject($row);
       		$folderArray[$folderObj->getSeq()] = $folderObj;
       	}
       	return $folderArray;
       }
       
       public function FindByLocation($locationSeq){
            $conn = self::$db->getConnection();
            $query = self::$FIND_ALL_BY_LOCATIONSEQ . " and folder.locationseq in ($locationSeq) order by locationname";
            $stmt = $conn->prepare($query);
            //$stmt->bindValue(':locationseq', $locationSeq);
            $stmt->execute();
            $folderArray = Array();
            //$count = $stmt->fetchAll();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $folderObj = new Folder();
                $folderObj =  self::populateObject($row);
                $folderArray[$folderObj->getSeq()] = $folderObj;
            }
         return $folderArray;
       }
       
      public function FindChildStations($parentStationSeq){
	       	$conn = self::$db->getConnection();
	       	$query = self::$FIND_CHILD_BY_LOCATIONSEQ ;
	       	$stmt = $conn->prepare($query);
	       	$stmt->bindValue(':parentfolderseq', $parentStationSeq);
	       	$stmt->execute();
	       	$folderArray = Array();
	       	//$count = $stmt->fetchAll();
	       	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	       		$folderObj = new Folder();
	       		$folderObj =  self::populateObject($row);
	       		$folderArray[$folderObj->getSeq()] = $folderObj;
	       	}
	       	return $folderArray;
       }	
       
       public function FindByStationType($locationSeq,$stationType){
            $conn = self::$db->getConnection();
            $query = self::$FIND_ALL_BY_LOCATIONSEQ . " and stationtype='$stationType' and folder.locationseq in ($locationSeq)";
            $stmt = $conn->prepare($query);
            //$stmt->bindValue(':locationseq', $locationSeq);
            $stmt->execute();
            $folderArray = Array();
            //$count = $stmt->fetchAll();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $folderObj = new Folder();
                $folderObj =  self::populateObject($row);
                $folderArray[$folderObj->getSeq()] = $folderObj;
            }
         return $folderArray;
       }

       public function FindSeqsByLoggedInUserOrMgr(){
            $LDS = LocationDataStore::getInstance();
            $locationSeqs = $LDS->getLoggedInUserOrManagerLocationSeqs();
            $locationSeqs = implode(",",$locationSeqs);
            $conn = self::$db->getConnection();
            $query = self::$FIND_SEQ_BY_LOCATIONSEQ . " and folder.locationseq in ($locationSeqs)";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $folderSeqArray = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            	 if(empty($row["parentfolderseq"])){
            	 	array_push($folderSeqArray,$row["seq"]);
            	 }
            }
            return $folderSeqArray;
       }
       
       public function FindByLoggedInUserOrMgr(){
       		$LDS = LocationDataStore::getInstance();
       		$locationSeqs = $LDS->getLoggedInUserOrManagerLocationSeqs();
       		$locationSeqs = implode(",",$locationSeqs);
	        $conn = self::$db->getConnection();
	       	$query = self::$FIND_ALL_BY_LOCATIONSEQ . " and  folder.locationseq in ($locationSeqs)";
	       	$stmt = $conn->prepare($query);
	       	//$stmt->bindValue(':locationseq', $locationSeq);
	       	$stmt->execute();
	       	$folderArray = Array();
	       	//$count = $stmt->fetchAll();
	       	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	       		if(empty($row["parentfolderseq"])){
	       			$folderObj = new Folder();
	       			$folderObj =  self::populateObject($row);
	       			$folderArray[$folderObj->getSeq()] = $folderObj;
	       		}
	       	}
	       	return $folderArray;
       }
       
       private function getTotalCount($sql){
        $conn = self::$db->getConnection();
        $query = FilterUtil::applyFilter($sql,false);
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $count = $stmt->rowCount();
        return $count;
     }
       public function FindJsonByLocationSeqs($locationSeqs,$isM2MSites = false){
            $conn = self::$db->getConnection();
            $FIND_BY_LOCATION_SEQS = "select m.m2msitecode, wqd.wqdfiledatadated as dataDated, l.name as locationname,l.locationfolder, l.repositoryfolder,l.hasdirectory, folder.* from folder inner join location l on folder.locationseq = l.seq left join m2msites m on folder.seq = m.folderseq  left join wqdstackfiledata wqd on folder.lastsyncedseq = wqd.wqdfiledataseq where folder.locationseq in ($locationSeqs) and folder.parentfolderseq is NULL";
            if($isM2MSites){
            	$FIND_BY_LOCATION_SEQS .= " and m.m2msitecode is not null";
            }
            $query = FilterUtil::applyFilter($FIND_BY_LOCATION_SEQS);
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $arr = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                 array_push($arr,$this->getJsonArray($row,$locationSeqs));
            }
            $mainArr["Rows"] = $arr;
            $mainArr["TotalRows"] = $this->getTotalCount($FIND_BY_LOCATION_SEQS);
            return json_encode($mainArr);
       }
       
     public function FindStationsForStatusReprtByLocation($locationSeqs,$reportType){
       	$conn = self::$db->getConnection();
       	$query = "select wqd.wqdfiledatadated as dataDated, l.name as locationname, folder.* from folder inner join location l on folder.locationseq = l.seq left join wqdstackfiledata wqd on folder.lastsyncedseq = wqd.wqdfiledataseq where folder.isvisible = 1 and folder.stationtype != 'aqms' and  folder.locationseq in ($locationSeqs) and folder.parentfolderseq is NULL and lastsynchedon >= DATE_SUB(NOW(), INTERVAL 4 HOUR) order by folder.state,lastsynchedon desc ";       
       	if($reportType == "delay"){
       		$query = "select wqd.wqdfiledatadated as dataDated, l.name as locationname, folder.* from folder inner join location l on folder.locationseq = l.seq left join wqdstackfiledata wqd on folder.lastsyncedseq = wqd.wqdfiledataseq where folder.isvisible = 1 and folder.stationtype != 'aqms' and folder.locationseq in ($locationSeqs) and folder.parentfolderseq is NULL and lastsynchedon <= DATE_SUB(NOW(), INTERVAL 4 HOUR) and 
 lastsynchedon >= DATE_SUB(NOW(), INTERVAL 48 HOUR) order by folder.state,lastsynchedon desc";
       	}else if($reportType == "offline"){
       		$query = "select wqd.wqdfiledatadated as dataDated, l.name as locationname, folder.* from folder inner join location l on folder.locationseq = l.seq left join wqdstackfiledata wqd on folder.lastsyncedseq = wqd.wqdfiledataseq where folder.isvisible = 1 and folder.stationtype != 'aqms' and folder.locationseq in ($locationSeqs) and folder.parentfolderseq is NULL and lastsynchedon <= DATE_SUB(NOW(), INTERVAL 48 HOUR) order by folder.state ,lastsynchedon desc";
       	}
       	$stmt = $conn->prepare($query);
       	$stmt->execute();       
       	$mainArr = array();
      	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	       	$folderObj = new Folder();
	       	$folderObj =  self::populateObject($row);
	       	$state = $folderObj->getState();
	       	if(!empty($state)){
	       		$arr = array();
	       		if(array_key_exists($state, $mainArr)){
	       			$arr = $mainArr[$state];
	       		}
	       		array_push($arr, $folderObj);
	       		$mainArr[$state] = $arr;
	       	}
	   	}    	
       	return $mainArr;
       }
       
      
       
       public function FindAllJson($isM2MSite = false){
            $conn = self::$db->getConnection();
            $FIND_ALL = "select m.m2msitecode ,l.name as locationname,l.locationfolder, l.repositoryfolder,l.hasdirectory, folder.* from folder inner join location l on folder.locationseq = l.seq left join m2msites m on folder.seq = m.folderseq";
            if($isM2MSite){
            	$FIND_ALL .= " where m.m2msitecode is not null";
            }
            $query = FilterUtil::applyFilter($FIND_ALL);
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $arr = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                 array_push($arr,$this->getJsonArray($row,$locationSeqs));
            }
            $mainArr["Rows"] = $arr;
            $mainArr["TotalRows"] = $this->getTotalCount($FIND_ALL);
            return json_encode($mainArr);
       }
       
       public function getStationsForDashBoard(){
           $managerSession = $_SESSION["managerSession"];
           $loggedInManagerSeq =  $managerSession["seq"];
           $LDS = LocationDataStore::getInstance();
           $locationSeqs = $LDS->FindLocationsByUser($loggedInManagerSeq);
           $lseq = $managerSession['locSeq'];
           if(!in_array($lseq,$locationSeqs)){
               array_push($locationSeqs,$lseq);
           }
           $locationSeqs = implode(",",$locationSeqs);
           $conn = self::$db->getConnection();
           $query = self::$FIND_ALL_BY_LOCATIONSEQ . " and folder.locationseq in ($locationSeqs) order by folder.lastsynchedon desc";
           $stmt = $conn->prepare($query);
           $stmt->execute();
           $folderArray = Array();
           while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                array_push($folderArray,$this->getJsonArray($row,$locationSeqs));
           }
           return $folderArray;
       }
       
       private function getLastSpcbSynchedData($folder){
       		$lastSyncedSpcbSeq = $folder["lastspcbsyncedseq"];
       		if(!empty($lastSyncedSpcbSeq)){
	       		$stationType = $folder["stationtype"];
	       		$FIND_DATADATED = "select wqdfiledatadated as dataDated from wqdstackfiledata where wqdfiledataseq = ".$lastSyncedSpcbSeq;
	       		if($stationType == "aqms"){
	       			$FIND_DATADATED = "select wqdfiledatadated as dataDated from wqdfiledata where wqdfiledataseq = ".$lastSyncedSpcbSeq;
	       		}else if($stationType == "aqmsp"){
	       		    $FIND_DATADATED = "select wqdfiledatadated as dataDated from wqdaqmspfiledata where wqdfiledataseq = " . $lastSyncedSpcbSeq;
	       		}
	       		$conn = self::$db->getConnection();
	       		$stmt = $conn->prepare($FIND_DATADATED);
	       		$stmt->execute();
	       		$row = $stmt->fetch(PDO::FETCH_ASSOC);
	       		$dataDated = $row["dataDated"];
	       		return $dataDated;
       		}
       		return null;
       }
       
       private function getJsonArray($row,$locationSeqs){
            $array = array();
            $array["seq"] = $row["seq"];
            $folderName = $row["foldername"];
            $stationName = $row["stationname"];
            $dataDated = $row["dataDated"];
            $m2mSiteCode = null;
            if(array_key_exists("m2msitecode", $row)){
            	$m2mSiteCode = $row["m2msitecode"];
            }
            $vendor = null;
            if(array_key_exists("stationvendor", $row)){
            	$vendor = $row["stationvendor"];
            }
            $folderCol = "";
            if(!empty($stationName)){
                $folderCol = $stationName;
            }else{
                $folderCol = $folderName;
            }
            $locationName = null;
            if(array_key_exists("locationname", $row)){
            	//if(count(explode(",",$locationSeqs)) > 1){
            	$locationName = $row["locationname"];
            }
            if(!empty($locationName)){
            	$folderCol = $row["locationname"] . " : " . $folderCol;
            }
            if(!empty($m2mSiteCode)){
             	$folderCol = $folderCol . " - " . $m2mSiteCode;
            }
            
            $array["foldername"] = $folderCol;
            $statusCol = "<i class='fa fa-check-square-o'></i>";
            if($row["isenable"] == "0"){
               $statusCol = "<i class='fa fa-square-o'></i>";
            }
            $visibleCol = "<i  class='fa fa-eye'></i>";
            if($row["isvisible"] == "0"){
               $visibleCol = "<i class='fa fa-eye-slash'></i>";
            }
            $isOnlineCol = "<span class='label label-success'>Online". str_repeat('&nbsp;', 1)   ."</span>";
            $array["offlinesince"] = "--";
            if(empty($row["isonline"])){
               $isOnlineCol = "<span class='label label-danger'>Offline</span>";
               $date = new DateTime($row["lastsynchedon"]);
               $array["offlinesince"] = DateUtils::getTimeDiffTillNow($date);
            }
            $array["isenable"] = $row["isenable"];
            $array["isonline"] = $isOnlineCol;
            $array["isvisible"] = $row["isvisible"];
            $array["vendor"] = $vendor;
            $array["lastsynchedon"] = $row["lastsynchedon"];
            $array["m2msitecode"] = $m2mSiteCode;
            $cpcbSyncedDate = "--";
            if(!empty($dataDated)){
            	$cpcbSyncedDate = $row["dataDated"];
            }
            $array["dataDated"] = $cpcbSyncedDate;
            $array["lastremindedon"] = $row["lastremindedon"];
            $spcbSynchedDate = $this->getLastSpcbSynchedData($row);;
            if(empty($spcbSynchedDate)){
            	$spcbSynchedDate = "--";
            }
            $array["spcbSynchedDate"] = $spcbSynchedDate;
            $respositoryUrl = "";
            if(!empty($row["hasdirectory"])){
            	$repositoryURL =  "<a target='new' href='". StringConstants::$applicationURL .'Repository/' .$row["repositoryfolder"].'/' .$row["locationfolder"]  .'/' .$row["foldername"] ."'>";
            	$repositoryURL .= "<i class='fa fa-folder' title='Repository'></i></a>";
            }
            
                   
            $array["repositoryURL"] = $repositoryURL;
            return $array;
       }
       
       function secondsToTime($seconds) {
            $dtF = new DateTime("@0");
            $dtT = new DateTime("@$seconds");
            return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
       }
       public function getFolderByUserAllLcoations($seq,$defaultLocationSeq){
             $LDS = LocationDataStore::getInstance();
             $locationSeqs = $LDS->FindLocationsByUser($seq);
             if(!in_array($defaultLocationSeq,$locationSeqs)){
                array_push($locationSeqs,$defaultLocationSeq);
             }
            $folders = $this->FindByLocation(implode(",",$locationSeqs));
            return $folders;
       }
       public function FindByLocationSeqs($locationSeqs){
            $conn = self::$db->getConnection();
            $FIND_BY_LOCATION_SEQS = "select * from folder where parentfolderseq is NULL and locationseq in($locationSeqs) order by locationseq ASC";
            $stmt = $conn->prepare($FIND_BY_LOCATION_SEQS);

            $stmt->execute();
            $folderArray = Array();
                        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $folderObj = new Folder();
                $folderObj =  self::populateObject($row);
                $folderArray[$folderObj->getSeq()] = $folderObj;
            }
         return $folderArray;
       }
       public function FindBySeqs($seqsArr){
            $seqsStr = implode($seqsArr,",");
            $conn = self::$db->getConnection();
            $FIND_BY_SEQS = "select * from folder where seq in($seqsStr) order by seq ASC";
            $stmt = $conn->prepare($FIND_BY_SEQS);

            $stmt->execute();
            $folderArray = Array();
                        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $folderObj = new Folder();
                $folderObj =  self::populateObject($row);
                $folderArray[$folderObj->getSeq()] = $folderObj;
            }
         return $folderArray;
       }

       public function getFolderByStationType($stationType,$seq,$defaultLocationSeq){
            $LDS = LocationDataStore::getInstance();
            $locationSeqs = $LDS->FindLocationsByUser($seq);
            if(!in_array($defaultLocationSeq,$locationSeqs)){
                array_push($locationSeqs,$defaultLocationSeq);
            }
            $folders = $this->FindByStationType(implode(",",$locationSeqs),$stationType);
            return $folders;
       }

       public static function populateObject($row){
           $folder = new Folder();
           $folder->setSeq($row['seq']);
           $name =  $row['foldername'];
           if($name == "raigarh"){
           	$name = "North Side Plant";
           }
           $actualName = self::getActualName($row['foldername']);
           $folder->setFolderName($name);
           $folder->setActualName($actualName);
           $locationName = "";
           if(array_key_exists("locationname", $row)){
           		$locationName = $row['locationname'];
           }
           
           $locationFolder = "";
           if(array_key_exists("locationfolder", $row)){
           		$locationFolder = $row['locationfolder'];
           }
           $repositoryFolder = "";
           if(array_key_exists("repositoryfolder", $row)){
           		$repositoryFolder = $row['repositoryfolder'];
           }
           $folder->setLocation($locationName);
           $folder->setLocationFolder($locationFolder);
           $folder->setRepositoryFolder($repositoryFolder);
           $folder->setParentSeq($row['parentfolderseq']);
           $folder->setDetails($row['details']);
           $folder->setLocationSeq($row['locationseq']);
           $folder->setLastSynchedOn($row['lastsynchedon']);
           $folder->setLastParsedOn($row['lastparsedon']);
           $folder->setLastRemindedOn($row['lastremindedon']);
           $folder->setStationType($row['stationtype']);
           $folder->setStationName($row['stationname']);
           $folder->setCategory($row['category']);
           $folder->setIndustryCode($row['industrycode']);
           $folder->setIndustryName($row['industryname']);
           $folder->setAddress($row['address']);
           $folder->setCity($row['city']);
           $folder->setState($row['state']);
           $folder->setZipcode($row['zipcode']);
           $folder->setLatitude($row['latitude']);
           $folder->setLongitude($row['longitude']);
           $folder->setEmail($row['email']);
           $folder->setMobile($row['mobile']);
           $folder->setStationName($row['stationname']);
           $folder->setDeviceId($row['deviceid']);
           $folder->setVendor($row['vendor']);
           $folder->setMake($row['make']);
           $folder->setModel($row['model']);
           $folder->setIsEnable($row['isenable']);
           $folder->setIsVisible($row['isvisible']);
           $folder->setCertificationsSystem($row['certificationsystem']);
           $folder->setUploadFrequency($row['uploadfrequency']);
           $folder->setParserType($row['parsertype']);
           $folder->setLastSyncedSeq($row["lastsyncedseq"]);
           $folder->setCreatedOn($row["createdon"]);
           $folder->setStationVendor($row["stationvendor"]);
           $folder->setCameraUrl($row["cameraurl"]);
           $lastSentSMSOn = null;
           if(!empty($row["lastsentsmson"])){
           		$lastSentSMSOn = $row["lastsentsmson"];           		
           }
           $isOpacity = 0;
           if(!empty($row["isopacity"])){
           	$isOpacity = $row["isopacity"];
           }
           $hasNotify = 0;
           if(!empty($row["hasnotifyuploadfailure"])){
           	$hasNotify = $row["hasnotifyuploadfailure"];
           }
           $folder->setHasNotifyUploadFailure($hasNotify);
           $folder->setIsOpacity($isOpacity);
           $folder->setLastSentSMSOn($lastSentSMSOn);
           $m2mDs = M2MSynchronizerDataStore::getInstance();
           $m2mSite = $m2mDs->FindByFolderSeq($folder->getSeq());
           if(!empty($m2mSite)){
               $folder->setM2MCode($m2mSite->getSiteCode());
               $folder->setM2MVendor($m2mSite->getVendor());
           }
           $hasPendingCpcbData = 0;
           if(!empty($row["haspendingcpcbdata"])){
               $hasPendingCpcbData = $row["haspendingcpcbdata"];
           }
           $folder->setHasPendingCpcbData($hasPendingCpcbData);
           $folder->setIsOnline($row["isonline"]);
           $folder->setIsCalliberationEnabled($row["iscalliberationenabled"]);
           $folder->setCalliberationURL($row["calliberationurl"]);
           $lastSpcbSyncedSeq = 0;
           if(!empty($row["lastspcbsyncedseq"])){
           		$lastSpcbSyncedSeq = $row["lastspcbsyncedseq"];
           }
           if(isset($row["aqmslastuploadeddate"] )){
           	   $folder->setAQMSLastUploadedDate($row["aqmslastuploadeddate"]);
           }
           $folder->setLastSpcbSyncedSeq($lastSpcbSyncedSeq);
           $loggerid = $row["loggerid"];
           $folder->setLoggerId($loggerid);
           return $folder;
       }
       
       
       private static function getActualName($name){
         if($name <> null && $name <>""){
           $folderName =  strtolower($name);
           $folderName = str_replace(" ","_",$folderName);
           return $folderName;
          }
          return null;
       }
       
       public function updateIsOnline($folderSeq,$isOnline){
            $conn = self::$db->getConnection();
            $query = self::$UPDATE_IS_ONLINE;
            if(!empty($isOnline)){
            	$query = self::$UPDATE_IS_ONLINE_AND_HAS_NOTIFY;
            }
            $stmt = $conn->prepare($query);
            $stmt->bindValue(':isonline', $isOnline);
            $stmt->bindValue(':folderseq', $folderSeq);
            $stmt->execute();
            $error = $stmt->errorInfo();
            if($error[2] <> ""){
                throw new RuntimeException($error[2]);
            }
            
       }
       
       public function markAsOffline($folderSeq,$lastSynchedOn){     	          	   
       	    $dateBefore4Hours = (new \DateTime())->modify('-48 hours');       	    
       	    if($lastSynchedOn <= $dateBefore4Hours){
       	    	
       	    	$folder = $this->FindBySeq($folderSeq);
       	    	if($folder->getIsOnline()){
	       	    	//$folderEmail = $folder->getEmail();
	       	    	$this->updateIsOnline($folderSeq,0);
	       	    	echo("Marking Offline folderSeq : - " . $folderSeq);
	       	    	//if(!empty($folderEmail)){
	       	    		//MailerUtils::sendStationMarkedOffline($folder);
	       	    	//}
       	    	}
       	    }
       }
       
       public function updateHasNotifyUploadFailure($folder){
       	    $folderSeq = $folder->getSeq();
       	    $hasNotify = $folder->getHasNotifyUploadFailure();
	       	$conn = self::$db->getConnection();
	       	$stmt = $conn->prepare(self::$UPDATE_HAS_NOTIFY_UPLOAD_FAILURE);
	       	$stmt->bindValue(':hasnotifyuploadfailure', $hasNotify);
	       	$stmt->bindValue(':folderseq', $folderSeq);
	       	$stmt->execute();
	       	$error = $stmt->errorInfo();
	       	if($error[2] <> ""){
	       		throw new RuntimeException($error[2]);
	       	}			
       }
       
       public function notifyUploadFailure($folder,$lastSynchedOn){
       	if(empty($folder->getHasNotifyUploadFailure())){
	       	$dateBefore4Hours = (new \DateTime())->modify('-4 hours');
	       	if($lastSynchedOn <= $dateBefore4Hours){	       		
	       		MailerUtils::sendStationMarkedOffline($folder);
	       		$folder->setHasNotifyUploadFailure(1);
	       		$this->updateHasNotifyUploadFailure($folder);
	       	}
       	}
       }
       
       public function notifyUploadStarted($folder){
       		MailerUtils::sendStationMarkedOnline($folder);
       }
       
        public function updateAQMSLastUploadedDate($folderSeq,$dateStr){
	       	$conn = self::$db->getConnection();
	       	$query = self::$UPDATE_AQMS_LAST_UPLOADED_DATE;
	       	$stmt = $conn->prepare($query);
	       	$stmt->bindValue(':lastDate', $dateStr);
	       	$stmt->bindValue(':folderseq', $folderSeq);
	       	$stmt->execute();
	       	$error = $stmt->errorInfo();
	       	if($error[2] <> ""){
	       		throw new RuntimeException($error[2]);
	       	}
       }
       
       public function folerExistWithLocation($locationseq , $folderName){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_BY_LOCATION);
            $stmt->bindValue(':foldername', $folderName);
            $stmt->bindValue(':locationseq', $locationseq);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $seqExist = "";
            if($row){
             $seqExist =  $row['seq'];
            }
            return $seqExist;
       }
        private static $FIND_STATION_NAME_BY_LOCATION = "select f.seq,f.stationname,cc.configseq,cc.channelname from folder f inner join channelconfiguration cc on f.seq = cc.folderseq where locationseq in ";
    public function getSationNameAndChannelsByLocation($locationSeqs){
        $conn = self::$db->getConnection();
        $query = self::$FIND_STATION_NAME_BY_LOCATION . "($locationSeqs)";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $error = $stmt->errorInfo();
        $arr = array();
        $stationArr = array();
        $ccArr = array();
        $dataArr = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stationName = $row["stationname"];
            $stationSeq = $row["seq"];
            $ccSeq = $row["configseq"];
            $channelname = $row["channelname"];
            if(!in_array($stationName,$stationArr)){
                array_push($stationArr,$stationName);
                $ccArr = array();
                //$dataArr = array();
            }
            $ccArr[$ccSeq] = $channelname;
            $dataArr[$stationName."_".$stationSeq] =  $ccArr;
            //$arr[$stationSeq] = $dataArr;
        }
        return $dataArr;
    }
}

?>
