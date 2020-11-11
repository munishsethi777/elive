<?php

   require_once('IConstants.inc');
   require_once($ConstantsArray['dbServerUrl'] ."//BusinessObjects//HighValueRule.php");
   require_once($ConstantsArray['dbServerUrl'] . "//DataStoreMgr//WQDDataDataStore.php");
   require_once($ConstantsArray['dbServerUrl'] . "//DataStoreMgr//WQDStackDataStore.php");
   require_once($ConstantsArray['dbServerUrl'] . "//DataStoreMgr//MainDB.php");

 class HighValueRuleDataStore{
     private static $highValueDataStore;
     private static $db;
     private static $INSERT = "insert into highvaluerule (folderseq, email, mobile, parameterchannelno, highvalue, isactive,rulehits,lastrulehitwqdfiledataseq,rulestartwqdfiledataseq,isnotifycpcb,ruletype,virtualchannelseq,lastsentsmson) Values(:folderseq, :email, :mobile, :parameterchannelno, :highvalue, :isactive,:rulehits,:lastrulehitwqdfiledataseq,:rulestartwqdfiledataseq,:isnotifycpcb,:ruletype,:virtualchannelseq,:lastsentsmson)";
     private static $UPDATE = "update highvaluerule set folderseq=:folderseq, email=:email, mobile=:mobile, parameterchannelno=:parameterchannelno, highvalue=:highvalue, isactive=:isactive,rulehits=:rulehits,lastrulehitwqdfiledataseq=:lastrulehitwqdfiledataseq, isnotifycpcb = :isnotifycpcb, ruletype = :ruletype , virtualchannelseq = :virtualchannelseq ,lastsentsmson=:lastsentsmson where seq = :seq";
     private static $DELETE = "delete from highvaluerule where seq = :seq";
    // private static $SELECTALL = "select highvaluerule.*,folder.foldername,folder.industryname,folder.stationtype,folder.stationname,channelconfiguration.channelname from highvaluerule,folder,channelconfiguration where folder.seq = highvaluerule.folderseq" ;
     private static $SELECTALL = "select hrv.*,f.foldername,f.industryname,f.stationtype,f.stationname,cc.channelname from highvaluerule hrv inner join folder f on hrv.folderseq = f.seq left join channelconfiguration cc on hrv.folderseq = cc.folderseq and hrv.parameterchannelno = cc.channelnumber where f.isenable = 1 and hrv.isactive = 1" ;
    
     private static $FIND_BY_SEQ = "select * from highvaluerule where seq = :seq";

     private static $FIND_BY_FOLDER = "select highvaluerule.*,channelconfiguration.channelname,channelconfiguration.channelstation,folder.foldername from highvaluerule,folder,channelconfiguration where folder.seq = channelconfiguration.folderseq and channelconfiguration.channelnumber = highvaluerule.parameterchannelno and folder.seq = highvaluerule.folderseq and highvaluerule.folderseq in (:folderseq)";
     private static $FIND_BY_FOLDER_SEQ = "select folder.stationname,folder.foldername, highvaluerule.*,channelconfiguration.channelname,channelconfiguration.channelstation,folder.foldername from highvaluerule,folder,channelconfiguration where folder.seq = channelconfiguration.folderseq and channelconfiguration.channelnumber = highvaluerule.parameterchannelno and folder.seq = highvaluerule.folderseq and highvaluerule.folderseq in ";
     private static $FIND_VIRTUAL_BY_FOLDER_SEQ = "select f.stationname,f.foldername, hv.*,vcc.channelname,f.foldername from highvaluerule hv inner join virtualchannelconfiguration vcc on hv.virtualchannelseq = vcc.configseq inner join folder f on hv.folderseq = f.seq where hv.folderseq in ";
     private static $FIND_BY_LOCATIONSEQ = "select highvaluerule.*,channelconfiguration.channelname,channelconfiguration.channelstation, folder.foldername from highvaluerule,folder,channelconfiguration where folder.seq = channelconfiguration.folderseq and channelconfiguration.channelnumber = highvaluerule.parameterchannelno and folder.seq = highvaluerule.folderseq and highvaluerule.folderseq in(select seq from folder where folder.locationseq = :locSeq)";
     private static $HITRULE = "update highvaluerule set rulehits=rulehits+1 ,lastrulehitwqdfiledataseq = :wqdseq where seq = :seq";

     private static $UPDATE_LAST_SENT_SMS_ON = "update highvaluerule set lastsentsmson=:lastsentsmson where seq = :seq";
     public function __construct(){
       self::$db = MainDB::getInstance();
     }

    public static function getInstance(){
        if (!self::$highValueDataStore){
            self::$highValueDataStore = new HighValueRuleDataStore();
            return self::$highValueDataStore;
        }
        return self::$highValueDataStore;
    }

     public function Save(HighValueRule $highValueRule){
     try{
      $SQL = self::$INSERT;
      if($highValueRule->getSeq() != null && $highValueRule->getSeq()<> "" && $highValueRule->getSeq() > 0){
         $SQL = self::$UPDATE;
      }
      $conn = self::$db->getConnection();
      $stmt = $conn->prepare($SQL);

      $stmt->bindValue(':folderseq', $highValueRule->getFolderSeq());
      $stmt->bindValue(':email',$highValueRule->getEmail());
      $stmt->bindValue(':mobile',$highValueRule->getMobile());
      $stmt->bindValue(':parameterchannelno',$highValueRule->getParameter());
      $stmt->bindValue(':highvalue',$highValueRule->getHighValue());
      $isActive = 0;
      if($highValueRule->getIsActive() == "true" || $highValueRule->getIsActive()==1){
            $isActive = 1;
      }
      $stmt->bindValue(':isactive',$isActive);
      $stmt->bindValue(':rulehits',$highValueRule->getRuleHits());
      $stmt->bindValue(':ruletype',$highValueRule->getRuleType());
      $stmt->bindValue(':virtualchannelseq',$highValueRule->getVirtualChannelSeq());
      $type = $highValueRule->getStationType();
      $maxSeq = $highValueRule->getLastRuleHitFileDataSeq();
      $seq = $highValueRule->getSeq();
      if(empty($seq)){
         if($type == "stack" || $type == "effluent"){
             $wqdsds = WQDStackDataStore::getInstance();
             $maxSeq = $wqdsds->getMaxSeq();  
          }else{
             $WQDDS = WQDDataDataStore::getInstance();
             $maxSeq = $WQDDS->getMaxSeq();   
          }    
      }
     
      $highValueRule->setLastRuleHitFileDataSeq($maxSeq);
      
      //$stmt->bindValue(':lastrulehitwqdfiledataseq',$maxSeq);
      $stmt->bindValue(':lastrulehitwqdfiledataseq',$highValueRule->getLastRuleHitFileDataSeq());
      $stmt->bindValue(':isnotifycpcb',$highValueRule->getIsNotifyCpcb());
      $dateValue = $highValueRule->getLastSentSMSOn();
      if(!empty($dateValue) &&  $dateValue instanceof DateTime){
      	$dateValue = $dateValue->format('Y-m-d H:i:s');
      }
      $stmt->bindValue(":lastsentsmson",$dateValue);
      if($SQL == self::$UPDATE){
            $stmt->bindValue(':seq',$highValueRule->getSeq());
      }else{
          $stmt->bindValue(':rulestartwqdfiledataseq',$maxSeq);
      }
      $stmt->execute();
      $error = $stmt->errorInfo();
      if($error[2] <> ""){
            throw new Exception($error[2]);
      } 
      }catch(Exception $e){
          $logger = Logger::getLogger($ConstantsArray["logger"]);
          $logger->error("Error During Save High Value Rule : - " . $e->getMessage());
      }
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
     
     public function HitHighValueRule($wqdseq,$seq){
        $SQL = self::$HITRULE;
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare($SQL);
        $stmt->bindValue(':wqdseq',$wqdseq);
        $stmt->bindValue(':seq',$seq);
        $stmt->execute();
        $error = $stmt->errorInfo();
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
            $obj =  self::populateObject($row);
            $error = $stmt->errorInfo();
            return $obj;
       }

        public function FindByFolder($folderSeq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_BY_FOLDER);
            $stmt->bindValue(':folderseq', $folderSeq);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $objArr = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objArr =  self::populateObject($row);
                $objArr[$obj->getSeq()] = $obj;
            }
           return $objArr;
       }
         private function getTotalCountByFolder($folderSeq){
            $conn = self::$db->getConnection();
            $query = self::$FIND_BY_FOLDER_SEQ .= "($folderSeq)";
            $query = FilterUtil::applyFilter($query,false);
            $stmt = $conn->prepare($query);
            $stmt->bindValue(':folderseq', $folderSeq);
            $stmt->execute();
            $count = $stmt->rowCount();
            return $count;  
          } 
        public function FindArrByFolder($folderSeq){
            $conn = self::$db->getConnection();
            $query = self::$FIND_BY_FOLDER_SEQ . "($folderSeq)";
            $query = FilterUtil::applyFilter($query);
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':folderseq', $folderSeq);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $objArr = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $stationName = "";
                if(count(explode(",",$folderSeq)) > 1){
                     $stationName = $row["stationname"] . " : ";
                    if(empty($stationName)){
                        $stationName = $row["foldername"] . " : ";
                    }
                }
                $subStation = $row["channelstation"];
                if(!empty($subStation)){
                    $subStation =  " (" . $subStation .")" ;
                }        
                $ruleType = $row["ruletype"];
                $content = " with value more than ";
                if(strtolower($ruleType) == "lowvalue"){
                	$content = " with value less than ";
                }
                $row["rule"] =  $stationName . $row["channelname"] . $subStation .$content . $row["highvalue"];
                array_push($objArr,$row);
            }
           
            $virtualChannelRules = $this->FindVirtualByFolder($folderSeq);
            $total = $this->getTotalCountByFolder($folderSeq);
            if(!empty($virtualChannelRules)){
            	$objArr = array_merge($objArr,$virtualChannelRules);
            	$total = $total + count($virtualChannelRules);
            }
            $mainArr["Rows"] = $objArr;
            $mainArr["TotalRows"] = $total;
            return $mainArr; 
       }
       
       public function FindVirtualByFolder($folderSeq){
       	$conn = self::$db->getConnection();
       	$query = self::$FIND_VIRTUAL_BY_FOLDER_SEQ . "($folderSeq)";
       	$query = FilterUtil::applyFilter($query);
       	$stmt = $conn->prepare($query);
       	$stmt->bindParam(':folderseq', $folderSeq);
       	$stmt->execute();
       	$error = $stmt->errorInfo();
       	$objArr = Array();
       	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
       		$stationName = "";
       		if(count(explode(",",$folderSeq)) > 1){
       			$stationName = $row["stationname"] . " : ";
       			if(empty($stationName)){
       				$stationName = $row["foldername"] . " : ";
       			}
       		}
       		
       		$row["rule"] =  $stationName . $row["channelname"] ." with value more than ". $row["highvalue"];
       		array_push($objArr,$row);
       	}
       	return $objArr;
       }
       
        public function FindAll(){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$SELECTALL);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $objArr = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj =  self::populateObject($row);
                $objArr[$obj->getSeq()] = $obj;
            }
           return $objArr;
        }
        public function FindAllForHighValueCron($isClp,$clpIndiaLocationSeq){
            $conn = self::$db->getConnection();
            $query = self::$SELECTALL;
            if($isClp){
                $query .= " and f.locationseq = $clpIndiaLocationSeq";
            }else{
                $query .= " and f.locationseq != $clpIndiaLocationSeq";
            }
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $objArr = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj =  self::populateObject($row);
                $objArr[$obj->getSeq()] = $obj;
            }
            return $objArr;
        }
        
        public function FindByLocationSeq($locationSeq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_BY_LOCATIONSEQ);
            $stmt->bindValue(':locSeq', $locationSeq);

            $stmt->execute();
            $error = $stmt->errorInfo();
            $objArr = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj =  self::populateObject($row);
                $objArr[$obj->getSeq()] = $obj;
            }
           return $objArr;
        }
       public function FindByLocationSeqs($locationSeqs){

            $conn = self::$db->getConnection();
            $SQL = "select * from highvaluerule INNER JOIN folder ON folder.seq=highvaluerule.folderseq and folder.locationseq in ($locationSeqs)";
            $stmt = $conn->prepare($SQL);
            $stmt->execute();

            $error = $stmt->errorInfo();
            $objArr = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj =  self::populateObject($row);
                $objArr[$obj->getSeq()] = $obj;
            }

           return $objArr;
        }
       public static function populateObject($row){
           $highValueRule = new HighValueRule();
           $highValueRule->setSeq($row['seq']);
           $highValueRule->setEmail($row['email']);
           $highValueRule->setFolderSeq($row['folderseq']);
           $highValueRule->setHighValue($row['highvalue']);
           $highValueRule->setIsActive($row['isactive']);
           $highValueRule->setMobile($row['mobile']);
           $highValueRule->setParameter($row['parameterchannelno']);
           $highValueRule->setChannelName($row['channelname']);
           if(array_key_exists("channelstation",$row)){
               $highValueRule->setChannelStation($row['channelstation']);
           }
           $highValueRule->setFolderName($row['foldername']);
           $highValueRule->setIndustryName($row['industryname']);
           $highValueRule->setStationName($row['stationname']);
           $highValueRule->setStationType($row['stationtype']);
           
           $ruleType = "";
           if(!empty($row["ruletype"])){
           	$ruleType = $row["ruletype"];
           }
           $vChannelseq = 0;
           if(!empty($row["virtualchannelseq"])){
           		$vChannelseq = $row["virtualchannelseq"];
           }
           if(!empty($row['lastsentsmson'])){
           	$highValueRule->setLastSentSMSOn($row['lastsentsmson']);
           }
           $highValueRule->setRuleType($ruleType);
           $highValueRule->setRuleHits($row['rulehits']);
           $highValueRule->setLastRuleHitFileDataSeq($row['lastrulehitwqdfiledataseq']);
           $highValueRule->setRuleStartFileDataSeq($row['rulestartwqdfiledataseq']);
           $highValueRule->setVirtualChannelSeq($vChannelseq);
           if(!empty($row["isnotifycpcb"])){
                $highValueRule->setIsNotifyCpcb($row['isnotifycpcb']);
           }
           return $highValueRule;
       }
 }
?>
