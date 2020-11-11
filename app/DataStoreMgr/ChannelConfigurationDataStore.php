<?php

  require_once('IConstants.inc');
   require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/MainDB.php");
  require_once($ConstantsArray['dbServerUrl'] . "/BusinessObjects/ChannelConfiguration.php");
  require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/ChannelGroupDataStore.php");
  


  class ChannelConfigurationDataStore  {
    private static $CCDataStore;
    private static $db;
    private static $INSERT = "insert into channelconfiguration (folderseq,channelnumber,channelname,channelstatusflag,channelstation,channelunit,prescribedlimit,isvisible,deviceid,channelrealname,prescribedlowlimit,createdon,lastmodifiedon,isstatus,statuschno) values(:folderseq,:channelnumber,:channelname,:channelstatusflag,:channelstation,:channelunit,:prescribedlimit,:isvisible,:deviceid,:channelrealname,:prescribedlowlimit,:createdon,:lastmodifiedon,:isstatus,:statuschno)";
    private static $UPDATE = "update channelconfiguration set folderseq=:folderseq,channelnumber=:channelnumber, channelname = :channelname,channelstatusflag=:channelstatusflag,channelunit=:channelunit,channelstation= :channelstation, prescribedlimit=:prescribedlimit, isvisible=:isvisible, deviceid=:deviceid,channelrealname=:channelrealname,prescribedlowlimit=:prescribedlowlimit, lastmodifiedon=:lastmodifiedon,isstatus=:isstatus,statuschno=:statuschno  where configseq=:configseq ";
    private static $FINDBYFOLDERSEQ = "select * from channelconfiguration where folderseq = :folderseq";
    private static $FIND_CH_NAME_BY_FOLDER_SEQ = "select channelname,channelnumber from channelconfiguration where folderseq = :folderseq";
     private static $FIND_CH_STATUS_BY_FOLDER_SEQ = "select channelname,channelnumber,statuschno from channelconfiguration where folderseq = :folderseq and isstatus = 1";
    private static $FIND_BY_FOLDERSEQ_IN_LIST = "select * from channelconfiguration where configseq in ";
    private static $FINDBYFOLDERSEQCHANNELNO =
            "select * from channelconfiguration where folderseq = :folderseq and channelnumber = :channelNo";
     private static $FINDBY_FOLDERSEQ_CHANNELNAME = "select channelnumber from channelconfiguration where folderseq = :folderseq and channelname = :channelname";
    private static $FIND_BY_SEQ = "select * from channelconfiguration where configseq = :configseq";

    private static $DELETE_BY_FOLDERSEQ = "delete from channelconfiguration where folderseq = :folderseq";
    private static $DELETE_BY_SEQ = "delete from channelconfiguration where configseq = :configseq";
    private static $COUNT_BY_FOLDER_SEQ = "select count(*) from channelconfiguration where folderseq = :folderseq";
    
    private static $FIND_BY_SEQS = "select * from channelconfiguration where configseq in ";
  private static $FINDBY_ALL_CH_NO = "select channelnumber from channelconfiguration where folderseq = :folderseq";
  
    public function __construct(){
        self::$db = MainDB::getInstance();
    }

    public static function getInstance()
    {
        if (!self::$CCDataStore)
        {
            self::$CCDataStore = new ChannelConfigurationDataStore();
            return self::$CCDataStore;
        }
        return self::$CCDataStore;
    }
    public function isChannelExist($folderSeq){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$COUNT_BY_FOLDER_SEQ);
        $stmt->bindValue(':folderseq', $folderSeq);
        $stmt->execute();
        $row = $stmt->fetch();
        $count = $row[0];
        return $count > 0;
    }
    
    public function FindByFolderAndChannelNos($folderSeq, $channelNosArr){
        $channlNosStr = implode($channelNosArr,",");
        $conn = self::$db->getConnection();
        $sql = "select * from channelconfiguration where folderseq = :folderseq and channelnumber in( $channlNosStr)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':folderseq', $folderSeq);
        $stmt->execute();
        $CCArray = Array();
        $rows = $stmt->fetchAll();        
        foreach($channelNosArr as $chno){
        	foreach ($rows as $row){
        		$row_chno = $row["channelnumber"];
        		if($row_chno == $chno){
        			$cc = new ChannelConfiguration();
        			$cc =  self::populateObject($row);
        			$CCArray[$cc->getSeq()] = $cc;
        			break;
        		}
        	}
        }
        return $CCArray;
    }
      
      public function getCountByAllStation(){
          $conn = self::$db->getConnection();
          $SQL = "select folderseq,count(*) as total from channelconfiguration where channelconfiguration.isvisible = 1 group by folderseq";
          $stmt = $conn->prepare($SQL);
          $stmt->execute();
          while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
              $folderSeq =  $row["folderseq"];
              $total = $row["total"];
              $CCArray[$folderSeq] = $total;
          }
          return $CCArray;
      }
    
    public function FindByFolderAndChannelNo($folderSeq,$channelNo){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$FINDBYFOLDERSEQCHANNELNO);
        $stmt->bindValue(':folderseq', $folderSeq);
        $stmt->bindValue(':channelNo', $channelNo);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $cc = new ChannelConfiguration();
        $cc =  self::populateObject($row);
        return $cc;
    }
    
    public function FindChNoByFolderAndChName($folderSeq,$channelName){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$FINDBY_FOLDERSEQ_CHANNELNAME);
        $stmt->bindValue(':folderseq', $folderSeq);
        $stmt->bindValue(':channelname', $channelName);
        $stmt->execute();
        $row = $stmt->fetch();
        $err = $stmt->errorInfo();
        $cNo = $row["channelnumber"];
        if(!empty($cNo)){
            $cNo = intval($cNo);
        }
        return $cNo;
    }
    //new flavor to get stationname also
    public function FindByFolderAndChannelNoWithSation($folderSeq,$channelNo){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$FINDBYFOLDERSEQCHANNELNO);
        $stmt->bindValue(':folderseq', $folderSeq);
        $stmt->bindValue(':channelNo', $channelNo);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return $rows;
    }
    public function FindChNameByFolderAndChannelNo($folderSeq,$channelNo){
        $cc = new ChannelConfiguration();
        $cc = self::FindByFolderAndChannelNo($folderSeq,$channelNo);
        return $cc->getChannelName();
    }
    public function saveList($configs){
        foreach($configs as $cc){
            $this->Save($cc);
        }
    }
    public function Save(ChannelConfiguration $cc){
        $isUpdate = false;
        $isVisible = $cc->getIsVisible();
        if($cc->getIsVisible()=="" || $cc->getIsVisible() == null){
            $isVisible = true;
        }
        $seq = $cc->getSeq();
        if(!empty($seq)){
            $isUpdate = true;
        }
        $SQL = $isUpdate ? self::$UPDATE : self::$INSERT;
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare($SQL);
        $stmt->bindValue(':folderseq', $cc->getFolderSeq());
        $stmt->bindValue(':channelnumber', $cc->getChannelNumber());
        $stmt->bindValue(':channelname', $cc->getChannelName());
        $stmt->bindValue(':channelstatusflag', $cc->getChannelStatusFlag());
        $stmt->bindValue(':channelstation', $cc->getChannelStation());
        $stmt->bindValue(':channelunit', $cc->getChannelUnit());
        $stmt->bindValue(':prescribedlimit', $cc->getPrescribedLimit());
        $stmt->bindValue(':isvisible', $cc->getIsVisible());
        $stmt->bindValue(':isstatus', $cc->getIsStatus());
        $stmt->bindValue(':deviceid', $cc->getDeviceId());
        $stmt->bindValue(':channelrealname', $cc->getChannelRealName());
        $stmt->bindValue(':prescribedlowlimit', $cc->getPrescribedLowLimit());
        $stmt->bindValue(':statuschno', $cc->getStatusChNo());
        $currDate = new DateTime();
        $currDate = $currDate->format('Y-m-d H:i:s');
        $stmt->bindValue(':lastmodifiedon', $currDate);
        if($isUpdate){
            $stmt->bindValue(':configseq', $cc->getSeq());
        }else{
        	$stmt->bindValue(':createdon', $currDate);
        }
        try{
            $stmt->execute();
            $err = $stmt->errorInfo();
            if($err[2] <> ""){
                throw new RuntimeException($err[2]);
            }
        }catch(Exception $e){
            $logger = Logger::getLogger($ConstantsArray["logger"]);
            $logger->error("Error during Save ChannelConfiguration : - " . $e->getMessage());
            return $e->getMessage();
        }
    }

    public function Delete($folderSeq){
        $SQL = self::$DELETE_BY_FOLDERSEQ;
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare($SQL);
        $stmt->bindValue(':folderseq', $folderSeq);
        try{
            $stmt->execute();
            $err = $stmt->errorInfo();
        }catch(Exception $e){
            return $e->getMessage();
        }
        $error = $stmt->errorInfo();
    }
    public function DeleteBySeq($seq){
        $SQL = self::$DELETE_BY_SEQ;
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare($SQL);
        $stmt->bindValue(':configseq', $seq);
        try{
            $stmt->execute();
            $err = $stmt->errorInfo();
        }catch(Exception $e){
            return $e->getMessage();
        }
        $error = $stmt->errorInfo();
    }

     public function FindChannelNamesBySeqs($seqs){
        $conn = self::$db->getConnection();
        $query = self::$FIND_BY_FOLDERSEQ_IN_LIST . "($seqs)";
        $stmt = $conn->prepare($query);     
        $stmt->execute();
        $ChNamesArray = Array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {            
            $ccNames =  $row["channelstation"] . " - " .$row["channelname"];            
            array_push($ChNamesArray,$ccNames);
        }
        return $ChNamesArray;
     }
     
     public function FindByFolder($folderSeq){
     	$conn = self::$db->getConnection();
     	$stmt = $conn->prepare(self::$FINDBYFOLDERSEQ);
     	$stmt->bindValue(':folderseq', $folderSeq);
     	$stmt->execute();
     	$CCArray = Array();
     	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
     		$cc = new ChannelConfiguration();
     		$cc =  self::populateObject($row);
     		$CCArray[$cc->getSeq()] = $cc;
     	}
     	return $CCArray;
     }
     
    public function FindByFolderSeq($folderSeq){
        $conn = self::$db->getConnection();
        $query = self::$FINDBYFOLDERSEQ . " order by channelnumber desc";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':folderseq', $folderSeq);
        $stmt->execute();
        $CCArray = Array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cc = new ChannelConfiguration();
            $cc =  self::populateObject($row);
            $CCArray[$cc->getSeq()] = $cc;
        }
         return $CCArray;
     } 
     
    public function FindChNameByFolderSeq($folderSeq){
     	$conn = self::$db->getConnection();
     	$query = self::$FIND_CH_NAME_BY_FOLDER_SEQ . " order by channelnumber desc";
     	$stmt = $conn->prepare($query);
     	$stmt->bindValue(':folderseq', $folderSeq);
     	$stmt->execute(); 
     	$ChNameArray = array();
	    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		   	 $cc = new ChannelConfiguration();
		     $number =  $row['channelnumber'];
		     $name =  $row['channelname'];
		     $ChNameArray[$number] = $name;
	    }     	
     	return $ChNameArray;
    }  
     
    public function FindStatusChNoByFolderSeq($folderSeq){
        $conn = self::$db->getConnection();
        $query = self::$FIND_CH_STATUS_BY_FOLDER_SEQ . " order by channelnumber desc";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':folderseq', $folderSeq);
        $stmt->execute();
        $ChNameArray = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $number =  $row['channelnumber'];
            $name =  $row['channelname'];
            $statusChNo = $row['statuschno'];
            $ChNameArray[$statusChNo] = $name;
        }
        return $ChNameArray;
    }
     
    public function FindByFolderWithDeviceId($folderSeq){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$FINDBYFOLDERSEQ);
        $stmt->bindValue(':folderseq', $folderSeq);
        $stmt->execute();
        $CCArray = Array();        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cc = new ChannelConfiguration();
            $cc =  self::populateObject($row);
            $deviceId = $cc->getDeviceId();
            $chArr = array();
            if(array_key_exists($deviceId,$CCArray)){
                $chArr = $CCArray[$deviceId];
            }
            array_push($chArr,$cc);
            $CCArray[$deviceId] = $chArr;
        }
         return $CCArray;
    }
    
    public function FindByChGroup($folderSeq){
    	$conn = self::$db->getConnection();
    	$CGDS = ChannelGroupDataStore::getInstance();
    	$cgSeqs = $CGDS->getChannelSeqsByGroup($folderSeq);
    	$cgSeqs = implode(",", $cgSeqs);
    	$query = self::$FIND_BY_SEQS . "(".$cgSeqs.")";
    	$stmt = $conn->prepare($query);    	
    	$stmt->execute();
    	$CCArray = Array();
    	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    		$cc = new ChannelConfiguration();
            $cc =  self::populateObject($row);
            $chRealName = $cc->getChannelRealName();
            if(!empty($chRealName)){
            	$cc->setChannelName($chRealName);
            }
            $CCArray[$cc->getSeq()] = $cc;
    	}
    	return $CCArray;
    } 
    
    public function FindBySeq($seq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_BY_SEQ);
            $stmt->bindValue(':configseq', $seq);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $cc =  self::populateObject($row);
            $error = $stmt->errorInfo();
            return $cc;
    }
    public function FindByLocSeqs($locSeqs){
        $conn = self::$db->getConnection();
        $SQL = "select channelconfiguration.* from channelconfiguration INNER JOIN folder ON folder.seq= channelconfiguration.folderseq and folder.locationseq in ($locSeqs)";
        $stmt = $conn->prepare($SQL);
        $stmt->execute();
        $CCArray = Array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cc = new ChannelConfiguration();
            $cc =  self::populateObject($row);
            $CCArray[$cc->getSeq()] = $cc;
        }
         return $CCArray;
    }
    
    public function FindByLocSeqsAndGroupByStation($locSeqs){
    	$conn = self::$db->getConnection();
    	$SQL = "select channelconfiguration.* from channelconfiguration INNER JOIN folder ON folder.seq= channelconfiguration.folderseq and channelconfiguration.isvisible = 1 and folder.locationseq in ($locSeqs)";
    	$stmt = $conn->prepare($SQL);
    	$stmt->execute();
    	$CCArray = Array();
    	$arr = array();
    	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    		$cc = new ChannelConfiguration();
    		$cc =  $row["channelname"];
    		$folderSeq = $row["folderseq"];
    		$arr = array();
    		if(array_key_exists($folderSeq, $CCArray)){
    			$arr = $CCArray[$folderSeq];
    		}
    		array_push($arr, $cc);
    		$CCArray[$folderSeq] = $arr;
    	}
    	return $CCArray;
    }
    
    public function getCountByLocSeqs($locSeqs,$folderType){
    	$conn = self::$db->getConnection();
    	$SQL = "select count(*) from channelconfiguration INNER JOIN folder ON folder.seq= channelconfiguration.folderseq and channelconfiguration.isvisible = 1 and folder.stationtype = '$folderType' and folder.locationseq in ($locSeqs)";
    	$stmt = $conn->prepare($SQL);
    	$stmt->execute();
    	$result = $stmt->fetch(PDO::FETCH_NUM);
        $count = intval($result[0]);
        return $count;    	
    }
    
    public function getCountByFolderSeqs($folderSeqs){
    	$conn = self::$db->getConnection();
    	$SQL = "select count(*) from channelconfiguration where channelconfiguration.isvisible = 1 and folderseq in ($folderSeqs)";
    	$stmt = $conn->prepare($SQL);
    	$stmt->execute();
    	$result = $stmt->fetch(PDO::FETCH_NUM);
    	$count = intval($result[0]);
    	return $count;    	
    }
    
    
    
    
    //public function updateParameters($cc){
//        $SQL = self::$UPDATE_PARAMETERS;
//        $conn = self::$db->getConnection();
//        $stmt = $conn->prepare($SQL);
//        $stmt->bindValue(':configseq', $cc->getSeq());
//        $stmt->bindValue(':channelname', $cc->getChannelName());
//        $stmt->bindValue(':channelstation', $cc->getChannelStation());
//        $stmt->bindValue(':channelunit', $cc->getChannelUnit());
//        $stmt->bindValue(':prescribedlimit', $cc->getPrescribedLimit());
//        try{
//            $stmt->execute();
//            $err = $stmt->errorInfo();
//        }catch(Exception $e){
//            return $e->getMessage();
//        }
//        $error = $stmt->errorInfo();
//    }
     public function populateObject($rsItem){
        
        $seq_ = $rsItem["configseq"] ;
        $folderSeq_ = $rsItem["folderseq"] ;
        $channelNumber_ = $rsItem["channelnumber"] ;
        $channelName_ = $rsItem["channelname"];
        $channelStatusFlag_ = $rsItem["channelstatusflag"] ;
        $channelUnit_ = $rsItem["channelunit"] ;
        $channelStation_ = $rsItem["channelstation"] ;
        $prescribedLimit_ = $rsItem["prescribedlimit"] ;
        $isVisible_ = $rsItem["isvisible"] ;
        $deviceID = $rsItem["deviceid"];
        $channelRealName = $rsItem["channelrealname"];
        $prescribedLowLimit_ = $rsItem["prescribedlowlimit"];
        $isVisible_ = $rsItem["isvisible"];
        $statusChNo = $rsItem["statuschno"];
        $isStatus = $rsItem["isstatus"];
        
        $cc = new ChannelConfiguration();
        $cc->setSeq($seq_);
        $cc->setFolderSeq($folderSeq_);
        $cc->setChannelNumber($channelNumber_);
        $cc->setChannelName($channelName_);
        $cc->setChannelStatusFlag($channelStatusFlag_);
        $cc->setChannelUnit($channelUnit_);
        $cc->setChannelStation($channelStation_);
        $cc->setPrescribedLimit($prescribedLimit_);
        $cc->setPrescribedLowLimit($prescribedLowLimit_);
        $cc->setIsVisible($isVisible_);
        $cc->setDeviceId($deviceID);
        $cc->setChannelRealName($channelRealName);
        $cc->setStatusChNo($statusChNo);
        $cc->setIsStatus($isStatus);
        return $cc;
    }
     
     public function FindChNOsByFolderSeq($folderSeq){
     	$conn = self::$db->getConnection();
     	$query = self::$FINDBY_ALL_CH_NO . " and isvisible != 0 order by channelnumber asc";
     	$stmt = $conn->prepare($query);
     	$stmt->bindValue(':folderseq', $folderSeq);
     	$stmt->execute(); 
     	$ChNoArray = array();
	    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		   	 $cc = new ChannelConfiguration();
		     $number =  $row['channelnumber'];
		     array_push($ChNoArray, $number);
	    }     	
     	return $ChNoArray;
    }
    public function FindStatusChNoByFolder($folderSeq){
    	$conn = self::$db->getConnection();
    	$query = "select * from channelconfiguration order by channelnumber desc";
    	$stmt = $conn->prepare($query);
    	$stmt->bindValue(':folderseq', $folderSeq);
    	$stmt->execute();
    	$ChNoArray = array();
    	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    		$cc = new ChannelConfiguration();
    		$chName =  $row['channelname'];
    		$number =  $row['channelnumber'];
    		if(strtolower($chName) == "status"){
    			return $number;
    		}
    	}
    	return null;
    }




  }
?>
