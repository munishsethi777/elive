<?php
  require_once('IConstants.inc'); 
  require_once($ConstantsArray['dbServerUrl'] . "BusinessObjects/SLM.php");
  require_once("MainDB.php"); 
  class SLMDataStore{
      
    private static $slmDataStore;
    private static $db;
    private static $INSERT = "insert into slm (dated,leq,min,max,l10,l50,l90,sel,crc,fileseq ) Values(:dated,:leq,:min,:max,:l10,:l50,:l90,:sel,:crc, :fileSeq)";
    private static $SELECT = "select * from slm";
    private static $FIND_BY_SEQ = "select * from slm where seq=:seq";
    
    public function __construct(){
        self::$db = MainDB::getInstance();
         
    }

    public static function getInstance()
    {
        if (!self::$slmDataStore)
        {
            self::$slmDataStore = new SLMDataStore();           
            return self::$slmDataStore;
        }
        return self::$slmDataStore;        
    }
    
   
      
    public function Save(SLM $SLM){
      $SQL = self::$INSERT;
      //if($folder->getSeq() != null && $folder->getSeq()<> "" && $folder->getSeq() > 0){
        // $SQL = self::$UPDATE; 
     // }
      $conn = self::$db->getConnection();
      $stmt = $conn->prepare($SQL);
     
      $stmt->bindValue(':dated', $SLM->getDated()); 
      $stmt->bindValue(':leq', $SLM->getLEQ());
      $stmt->bindValue(':min',$SLM->getMIN());
      $stmt->bindValue(':max',$SLM->getMAX());
      $stmt->bindValue(':l10',$SLM->getL10());
      $stmt->bindValue(':l50',$SLM->getL50());
      $stmt->bindValue(':l90',$SLM->getL90());
      $stmt->bindValue(':sel',$SLM->getSEL());
      $stmt->bindValue(':crc',$SLM->getCRC());
      $stmt->bindValue(':fileSeq',$SLM->getFileSeq()); 
           
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
            $slmObj = new SLM();
            $slmObj =  self::populateObject($row);
            $slmArray[$slmObj->getSeq()] = $slmObj;
        }
         return $slmArray;
    }
    
    public function FindBySeq($seq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_BY_SEQ);
            $stmt->bindValue(':seq', $seq); 
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $slmObj =  self::populateObject($row);
            $error = $stmt->errorInfo(); 
            return $slmObj;
     } 
 
   // dated,leq,min,max,l10,l50,l90,sel,crc   
    public function populateObject($rsItem){
            $seq_ = $rsItem["seq"] ;
            $dated_ =  $rsItem["dated"] ;
            $leq_ = $rsItem["leq"] ;
            $min_ = $rsItem["min"] ;
            $max_ = $rsItem["max"] ;
            $l10_ = $rsItem["l10"] ;
            $l50_ = $rsItem["l50"];
            $l90_ = $rsItem["l90"] ;
            $sel_ = $rsItem["sel"];
            $crc = $rsItem["crc"];
            $fileSeq = $rsItem["fileseq"]; 
            
            $slm = new SLM();
            $slm->setSeq($seq_);
            $slm->setDated($dated_);
            $slm->setLEQ($leq_);
            $slm->setMIN($min_);
            $slm->setMAX($max_);
            $slm->setL10($l10_);
            $slm->setL50($l50_);
            $slm->setL90($l90_);
            $slm->setSEL($sel_);
            $slm->setCRC($crc); 
            $slm->setFileSeq($fileSeq);
            return $slm; 
     }
    
   
  
}
  
  
  
?>
