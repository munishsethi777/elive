<?php
  require_once('IConstants.inc'); 
  require_once($ConstantsArray['dbServerUrl'] . "BusinessObjects/WLM.php");
  require_once("MainDB.php"); 
  class WLMDataStore{
    
    private static $wlmDataStore;
    private static $db;
    private static $INSERT = "insert into wlm (dated,ws,wd,temp,rh,rf,sol,bp,crc,fileseq ) Values(:dated,:ws,:wd,:temp,:rh,:rf,:sol,:bp,:crc, :fileSeq)";
    private static $SELECT = "select * from wlm";
    private static $FIND_BY_SEQ = "select * from wlm where seq=:seq";
    
    public function __construct(){
        self::$db = MainDB::getInstance();
         
    }

    public static function getInstance()
    {
        if (!self::$wlmDataStore)
        {
            self::$wlmDataStore = new WLMDataStore();           
            return self::$wlmDataStore;
        }
        return self::$wlmDataStore;        
    }
    
      public function Save(WLM $WLM){
      $SQL = self::$INSERT;
      //if($folder->getSeq() != null && $folder->getSeq()<> "" && $folder->getSeq() > 0){
        // $SQL = self::$UPDATE; 
     // }
      //     `seq`       int,

      $conn = self::$db->getConnection();
      $stmt = $conn->prepare($SQL);
     
      $stmt->bindValue(':dated', $WLM->getTime()); 
      $stmt->bindValue(':ws', $WLM->getWS());
      $stmt->bindValue(':wd',$WLM->getWD());
      $stmt->bindValue(':temp',$WLM->getTemp());
      $stmt->bindValue(':rh',$WLM->getRH());
      $stmt->bindValue(':rf',$WLM->getRF());
      $stmt->bindValue(':sol',$WLM->getSOL());
      $stmt->bindValue(':bp',$WLM->getBP());
      $stmt->bindValue(':crc',$WLM->getCRC());
      $stmt->bindValue(':fileSeq',$WLM->getFileSeq()); 
           
      //if($SQL == self::$UPDATE){
//       $stmt->bindValue(':folderseq',$folder->getSeq());    
//      }
      $stmt->execute();  
      //I will be put code here for throw exception and show on the screen   
      $error = $stmt->errorInfo();
   }
    
     public function FindAll(){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$SELECT);
        $stmt->execute();
        $slmArray = Array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $wlmObj = new SLM();
            $wlmObj =  self::populateObject($row);
            $wlmArray[$slmObj->getSeq()] = $wlmObj;
        }
         return $wlmArray;
    }
    
    public function FindBySeq($seq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_BY_SEQ);
            $stmt->bindValue(':seq', $seq); 
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $wlmObj =  self::populateObject($row);
            $error = $stmt->errorInfo(); 
            return $wlmObj;
     } 
 
   
    public function populateObject($rsItem){
       
            $seq_ = $rsItem["seq"] ;
            $dated_ =  $rsItem["dated"] ;
            $ws_ = $rsItem["ws"] ;
            $wd_ = $rsItem["wd"];
            $temp_ = $rsItem["temp"] ;
            $rh_ = $rsItem["rh"] ;
            $rf_ = $rsItem["rf"] ;
            $sol_ = $rsItem["sol"];
            $bp_ = $rsItem["bp"] ;
            $crc = $rsItem["crc"];
            $fileSeq = $rsItem["fileseq"];
            
            $wlm = new WLM();
            $wlm->setSeq($seq_);
            $wlm->setTime($dated_);
            $wlm->setWS($ws_);
            $wlm->setWD($wd);
            $wlm->setTemp($temp_);
            $wlm->setRH($rh_);
            $wlm->setRF($rf_);                      
            $wlm->setSOL($sol_);
            $wlm->setBP($bp_); 
            $wlm->setCRC($crc);
            $wlm->setFileSeq($fileSeq); 
            return $wlm; 
     }
  }
?>
