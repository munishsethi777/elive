<?php
  require_once("../IConstants.php"); 
  require_once(IConstants::$ServerURL. "DataStoreMgr/mainDB.php"); 
  require_once(IConstants::$ServerURL . "BusinessObjects/DPCC.php");
  
  class DPCCDataStore{
      
    private static $dpccDataStore;
    private static $db;
    
    public function __construct(){
        self::$db = Database::getInstance(); 
    }

    public static function getInstance()
    {
        if (!self::$dpccDataStore)
        {
            self::$dpccDataStore = new DPCCDataStore();           
            return self::$dpccDataStore;
        }
        return self::$dpccDataStore;        
    }
    
    public function SaveData($dpcc){
        
        $dpccObj = new DPCC();
        $dpccObj = $dpcc;
        $dpccDated = "NULL";
        $seq = "NULL";
        $co = "NULL";
        $o3 = "NULL";
        $no = "NULL"; 
        $no2 = "NULL";
        $nox = "NULL";
        $nh3 = "NULL";
        $so2 = "NULL";
        $ben = "NULL";
        $tol = "NULL";
        $pxy = "NULL";
        
        if($dpccObj->getCO()!=null && $dpccObj!="")$co = $dpccObj->getCO();
        if($dpccObj->getO3()!=null && $dpccObj!="")$o3 = $dpccObj->getO3();
        if($dpccObj->getNO()!=null && $dpccObj!="")$no = $dpccObj->getNO();
        if($dpccObj->getNO2()!=null && $dpccObj!="")$no2 = $dpccObj->getNO2();
        if($dpccObj->getNOX()!=null && $dpccObj!="")$nox = $dpccObj->getNOX();
        if($dpccObj->getNH3()!=null && $dpccObj!="")$nh3 = $dpccObj->getNH3();
        if($dpccObj->getSO2()!=null && $dpccObj!="")$so2 = $dpccObj->getSO2();
        if($dpccObj->getBEN()!=null && $dpccObj!="")$ben = $dpccObj->getBEN();
        if($dpccObj->getTOL()!=null && $dpccObj!="")$tol = $dpccObj->getTOL();
        if($dpccObj->getPXY()!=null && $dpccObj!="")$pxy = $dpccObj->getPXY();
        
        
        if($dpccObj->getDated()!= null && $dpccObj->getDated()!=""){
            $dpccDated = "'". $dpccObj->getDated() ."'";
        }else{
            echo "Date blank";
        }
        If($dpccObj->getSeq() || $dpccObj->getSeq()=="" || $dpccObj->getSeq()==0){            
            $SQL = "INSERT INTO `dpcc` (dated,co,o3,no,no2,nox,nh3,so2,ben,tol,pxy)";
            $SQL.= "VALUES (". $dpccDated .",". $co .",". $o3.",". $no;
            $SQL.= ",". $no2 .",". $nox .",". $nh3 .",". $so2;
            $SQL.= ",". $ben .",". $tol .",". $pxy .")";
        }
        
        $dd = self::$db;
        $res = $dd->query($SQL);
        return $res;
    }
      
    public function SaveMetaData($dpcc){
        
        $dpccObj = new DPCC();
        $dpccObj = $dpcc;
        $dpccDated = "NULL";

        $pm25 = "NULL";
        $pm10 = "NULL";
        $at = "NULL";
        $rh = "NULL"; 
        $ws = "NULL";
        $wd = "NULL";
        $vws = "NULL";
        $bp = "NULL";
        $sr = "NULL";    
        
        if($dpccObj->getPM25()!=null && $dpccObj!="")$pm25 = $dpccObj->getPM25();
        if($dpccObj->getPM10()!=null && $dpccObj!="")$pm10 = $dpccObj->getPM10();
        if($dpccObj->getAT()!=null && $dpccObj!="")$at = $dpccObj->getAT();
        if($dpccObj->getRH()!=null && $dpccObj!="")$rh = $dpccObj->getRH();
        if($dpccObj->getWS()!=null && $dpccObj!="")$ws = $dpccObj->getWS();
        if($dpccObj->getWD()!=null && $dpccObj!="")$wd = $dpccObj->getWD();
        if($dpccObj->getVWS()!=null && $dpccObj!="")$vws = $dpccObj->getVWS();
        if($dpccObj->getBP()!=null && $dpccObj!="")$bp = $dpccObj->getBP();
        if($dpccObj->getSR()!=null && $dpccObj!="")$sr = $dpccObj->getSR();
        
        
        if($dpccObj->getDated()!= null && $dpccObj->getDated()!=""){
            $dpccDated = "'". $dpccObj->getDated() ."'";
        }else{
            echo "Date blank";
        }

        If($dpccObj->getSeq() || $dpccObj->getSeq()=="" || $dpccObj->getSeq()==0){            
            $SQL = "UPDATE `dpcc` set pm25 =". $pm25.",pm10=". $pm10.",at=". $at .",";
            $SQL.= "rh=".$rh.",ws=".$ws.",wd=".$wd.",vws=".$vws.",bp=".$bp.",sr=".$sr ;
            $SQL.= " where dated=". $dpccDated;
        }
        
        $dd = self::$db;
        $res = $dd->query($SQL);
        return $res;
    }
    
    
    public function FindAllImages(){
          $SQL = "select * from images";                              
          $result = self::$db->query($SQL);
          $imagesRS = self::$db->fetch_rows($result);
          
          $imagesArray = array();
          foreach($imagesRS as $image){
                $catArray = self::getCategoriesByImage($image['imageseq']);
                $image["imageCategories"] = $catArray;
                array_push($imagesArray, $image);
          } 
          return $imagesArray; 
  }
  
  private function getCategoriesByImage($imageSeq){
    $SQL = "SELECT categoryseq from imagecategories where imageseq = ". $imageSeq;
    $result = self::$db->query($SQL);
    $catArrComplex = self::$db->fetch_rows($result);
    $catArray = array();
    for($i=0;$i<count($catArrComplex);$i++){
           array_push($catArray,$catArrComplex[$i]["categoryseq"]);
    }
    return $catArray;
  }  

  public function populateObject($rsItem){
            $seq_ = $rsItem["seq"] ;
            $title_ =  $rsItem["title"] ;
            $author_ = $rsItem["author"] ;
            $updatedOn_ = $rsItem["updatedon"] ;
            $viewCount_ = $rsItem["viewcount"] ;
            $subscriberCount_ = $rsItem["subscribercount"];
            $thumbURL_ = $rsItem["thumburl"] ;
            $region_ = $rsItem["region"];
            
            $channelEntryObj = new ChannelEntry();
            $channelEntryObj->setSeq($seq_);
            $channelEntryObj->setAuthor($author_);
            $channelEntryObj->setSubscriberCount($subscriberCount_);
            $channelEntryObj->setThumbURL($thumbURL_);
            $channelEntryObj->setTitle($title_);
            $channelEntryObj->setUpdatedOn($updatedOn_);
            $channelEntryObj->setViewCount($viewCount_);
            $channelEntryObj->setRegion($region_);
            return $channelEntryObj; 
   }
  
}
  
  
  
?>
