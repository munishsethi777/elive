<?php
  
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/MainDB.php");
  require_once($ConstantsArray['dbServerUrl'] . "/BusinessObjects/Edition.php");
 
  
  class ChannelConfigurationDataStore  {
    private static $CCDataStore;
    private static $db;  
    private static $INSERT = "insert into channelconfiguration (folderseq,channelnumber,channelname,channelstatusflag,channelunit) values(:folderseq,:channelnumber,:channelname,:channelstatusflag,:channelunit)";
    private static $FINDBYFOLDERSEQ = "select * from channelconfiguration where folderseq = :folderseq";
    private static $FINDBYFOLDERSEQCHANNELNO = 
            "select * from channelconfiguration where folderseq = :folderseq and channelnumber = :channelNo";
    private static $FIND_BY_SEQ = "select * from channelconfiguration where configseq = :configseq";
    
    private static $DELETE_BY_FOLDERSEQ = "delete from channelconfiguration where folderseq = :folderseq";
    
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
    public function FindByFolderAndChannelNos($folderSeq, $channelNosArr){
        $channlNosStr = implode($channelNosArr,",");
        $conn = self::$db->getConnection();
        $sql = "select * from channelconfiguration where folderseq = :folderseq and channelnumber in( $channlNosStr)";
        $stmt = $conn->prepare($sql);
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
    public function FindChNameByFolderAndChannelNo($folderSeq,$channelNo){
        $cc = new ChannelConfiguration();
        $cc = self::FindByFolderAndChannelNo($folderSeq,$channelNo);
        return $cc->getChannelName();
    }
    public function Save(ChannelConfiguration $cc){
        $SQL = self::$INSERT;
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare($SQL);
        $stmt->bindValue(':folderseq', $cc->getFolderSeq()); 
        $stmt->bindValue(':channelnumber', $cc->getChannelNumber());
        $stmt->bindValue(':channelname', $cc->getChannelName());
        $stmt->bindValue(':channelstatusflag', $cc->getChannelStatusFlag());
        $stmt->bindValue(':channelunit', $cc->getChannelUnit());
        try{
            $stmt->execute();
            $err = $stmt->errorInfo();
        }catch(Exception $e){
            return $e->getMessage();
        }
        $error = $stmt->errorInfo();
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
   
    public function populateObject($rsItem){
        
            $seq_ = $rsItem["configseq"] ;
            $folderSeq_ = $rsItem["folderseq"] ;
            $channelNumber_ = $rsItem["channelnumber"] ;
            $channelName_ = $rsItem["channelname"];
            $channelStatusFlag_ = $rsItem["channelstatusflag"] ;
            $channelUnit_ = $rsItem["channelunit"] ;
            
            $cc = new ChannelConfiguration();
            $cc->setSeq($seq_);
            $cc->setFolderSeq($folderSeq_);
            $cc->setChannelNumber($channelNumber_);
            $cc->setChannelName($channelName_);
            $cc->setChannelStatusFlag($channelStatusFlag_);
            $cc->setChannelUnit($channelUnit_);
            
            return $cc; 
     }
    
    
    
    
  }
?>