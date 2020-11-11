<?php
    require_once('IConstants.inc');
    require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/MainDB.php");
    require_once($ConstantsArray['dbServerUrl'] . "/BusinessObjects/ChannelGroup.php");
    require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/ChannelConfigurationDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/Utils/MailerUtils.php");
    require_once($ConstantsArray['dbServerUrl'] ."/Utils/SessionUtil.php");
    class ChannelGroupDataStore  {
        private static $CGDataStore;
        private static $db;
        private static $FIND_BY_SEQ = "select * from channelgroups where seq = :seq";
        private static $FIND_BY_FOLDER = "select * from channelgroups where folderseq = :folderseq";
        private static $FIND_BY_FOLDER_IN_LIST = "select f.stationname, cg.* from channelgroups cg inner join folder f on cg.folderseq = f.seq where cg.folderseq in ";
        private static $INSERT = "INSERT INTO channelgroups (name ,channelseqs,folderseq,subfolderseq) VALUES(:name, :channelseqs, :folderseq,:subfolderseq)";
        private static $UPDATE = "update channelgroups set name = :name, channelseqs = :channelseqs , folderseq  = :folderseq, subfolderseq = :subfolderseq where seq = :seq";
        private static $FIND_BY_SUB_FOLDER = "select * from channelgroups where subfolderseq = :subfolderseq";
        public function __construct(){
            self::$db = MainDB::getInstance();
        }

        public static function getInstance()
        {
            if (!self::$CGDataStore)
            {
                self::$CGDataStore = new ChannelGroupDataStore();
                return self::$CGDataStore;
            }
            return self::$CGDataStore;
        }
        
        public function FindByFolder($folderSeq,$isAppyFilter = false){
            $conn = self::$db->getConnection();
            $query = self::$FIND_BY_FOLDER;
            if($isAppyFilter){
            	$query = FilterUtil::applyFilter($query);
            }
            $stmt = $conn->prepare($query);          
            $stmt->bindValue(':folderseq', $folderSeq);
            $stmt->execute();
            $CGArray = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cg =  self::populateObject($row);
                $CGArray[$cg->getSeq()] = $cg;
            }
            return $CGArray;
        }
        public function getChannelSeqsByGroup($folderSeq){
        	$conn = self::$db->getConnection();
        	$query = self::$FIND_BY_SUB_FOLDER;
        	$stmt = $conn->prepare($query);
        	$stmt->bindValue(':subfolderseq', $folderSeq);
        	$stmt->execute();
        	$CGSeqsArray = Array();
        	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        		$channelSeqs = $row["channelseqs"];
        		$channelSeqs = explode(",", $channelSeqs);   
        		$CGSeqsArray = $channelSeqs;
        	}
        	return $CGSeqsArray;
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
        
        private function getTotalCountByFolder($folderSeq){
        	$conn = self::$db->getConnection();
        	$query = self::$FIND_BY_FOLDER_IN_LIST . "($folderSeq)";
        	$query = FilterUtil::applyFilter($query,false);
        	$stmt = $conn->prepare($query);        	
        	$stmt->execute();
        	$count = $stmt->rowCount();
        	return $count;
        }
        public function FindArrByFolder($folderSeq){
        	$conn = self::$db->getConnection();
        	$query = self::$FIND_BY_FOLDER_IN_LIST . "($folderSeq)";
        	$query = FilterUtil::applyFilter($query);
        	$stmt = $conn->prepare($query);        	
        	$stmt->execute();
        	$error = $stmt->errorInfo();
        	$objArr = Array();
        	$CCDS = ChannelConfigurationDataStore::getInstance();        	
        	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        		$channelSeqs = $row["channelseqs"];
        		$ChNames = $CCDS->FindChannelNamesBySeqs($channelSeqs);       		
        		$ChNames = implode(", ", $ChNames);
        		$row["channelNames"] = $ChNames;
        		$name = $row["stationname"] . " -- " . $row["name"];
        		$row["name"] = $name;
        		array_push($objArr,$row);
        	}
        	$mainArr["Rows"] = $objArr;
        	$mainArr["TotalRows"] = $this->getTotalCountByFolder($folderSeq);
        	return $mainArr;
        }      
        
    public function sendSubStationNotification($channelGroup){    	
    	$managerSession = $_SESSION["managerSession"];
    	$username = $managerSession["username"];
    	$emailTo = "munishsethi777@gmail.com";
    	$from = "noreply@envirotechlive.com";
    	$subject = "Created New Sub Station";
    	$message = "Create New Sub Station with Name " . $channelGroup->getName() . " By " . $username;
    	MailerUtils::sendMandrillEmailNotification($message,$subject,$from,$emailTo);
    }
    
    public function Save($channelGroup){
      try{
          $SQL = self::$INSERT;
          $isUpdate = false;
          $seq = $channelGroup->getSeq();
          if(!empty($seq)){
             $SQL = self::$UPDATE;
             $isUpdate = true;
          }
          $conn = self::$db->getConnection();
          $stmt = $conn->prepare($SQL);
          $stmt->bindValue(':name', $channelGroup->getName());
          $stmt->bindValue(':channelseqs', $channelGroup->getChannelSeqs());
          $stmt->bindValue(':folderseq',$channelGroup->getFolderSeq());
          $stmt->bindValue(':subfolderseq',$channelGroup->getSubFolderSeq());        
          if($isUpdate){
              $stmt->bindValue(':seq',$seq);              
          }
          $stmt->execute();
          if(!$isUpdate){
             $seq = $conn->lastInsertId();
             $channelGroup->setSeq($seq);
             $this->sendSubStationNotification($channelGroup);
          }
          //I will be put code here for throw exception and show on the screen
          $error = $stmt->errorInfo();
          if($error[2] <> ""){
            throw new Exception($error[2]);
          }
      }catch(Exception $e){
          $logger = Logger::getLogger($ConstantsArray["logger"]);
          $logger->error("Error During Save Channel Group : - " . $e->getMessage());
      }

   }
        public function populateObject($rsItem){
            $seq_ = $rsItem["seq"];
            $name_ = $rsItem["name"];
            $channelSeqs_ = $rsItem["channelseqs"];
            $folderSeq_ = $rsItem["folderseq"];
            $subFolderSeq = $rsItem["subfolderseq"];
            
            $cg = new ChannelGroup();
            $cg->setSeq($seq_);
            $cg->setFolderSeq($folderSeq_);
            $cg->setChannelSeqs(explode(",",$channelSeqs_));
            $cg->setName($name_);
            $cg->setSubFolderSeq($subFolderSeq);
            return $cg;
     }
     
     public function getCountByLocSeqs($locSeqs,$folderType){
     	$conn = self::$db->getConnection();
     	$SQL = "select count(*) from channelgroups INNER JOIN folder ON folder.seq= channelgroups.folderseq and folder.stationtype = '$folderType' and folder.locationseq in ($locSeqs)";
     	$stmt = $conn->prepare($SQL);
     	$stmt->execute();
     	$result = $stmt->fetch(PDO::FETCH_NUM);
     	$count = intval($result[0]);
     	return $count;
     }
     
     public function getCountByFolderSeqs($folderSeqs){
     	$conn = self::$db->getConnection();
     	$SQL = "select count(*) from channelgroups where folderseq in ($folderSeqs)";
     	$stmt = $conn->prepare($SQL);
     	$stmt->execute();
     	$result = $stmt->fetch(PDO::FETCH_NUM);
     	$count = intval($result[0]);
     	return $count;
     }

    }
    
?>
