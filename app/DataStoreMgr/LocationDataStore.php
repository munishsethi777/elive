<?php
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/MainDB.php");
require_once($ConstantsArray['dbServerUrl'] ."BusinessObjects/Location.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils/FilterUtil.php");

class LocationDataStore{

    private static $locationDataStore;
    private static $db;
    private static $INSERT = "insert into location (name,locationfolder,details,isprivate,hasdirectory,repositoryfolder,createdon) Values(:name , :locationfolder , :details,:isprivate,:hasdirectory,:repositoryfolder, :createdon)";
   
    private static $UPDATE = "update location set name = :name , locationfolder = :locationfolder , details = :details,isprivate = :isprivate,hasdirectory=:hasdirectory where seq = :locationSeq";
    private static $DELETE = "delete from location where seq = :locationSeq";
    private static $SELECTALL = "select * from location" ;
    private static $FIND_BY_SEQ = "select * from location where seq = :locationSeq";
    private static $FIND_BY_USER = "select * from folderuser Left JOIN folder ON folderuser.`folderseq` = folder.`seq` left join location on folder.`locationseq` = location.`seq` where folderuser.`userseq` = :userseq ";
    private static $FIND_BY_NAME = "select seq from location where name = :name";


    public function __construct(){
       self::$db = MainDB::getInstance();
    }
    public static function getInstance(){
        if (!self::$locationDataStore)
        {
            self::$locationDataStore = new LocationDataStore();
            return self::$locationDataStore;
        }
        return self::$locationDataStore;
    }
    public function Save(Location $location){
      try{
          $SQL = self::$INSERT;
          
          if($location->getSeq() != null && $location->getSeq()<> "" && $location->getSeq() > 0){
             $SQL = self::$UPDATE;
          }
          $conn = self::$db->getConnection();
          $stmt = $conn->prepare($SQL);
          $stmt->bindValue(':name', $location->getLocationName());
          $stmt->bindValue(':locationfolder',$location->getLocationFolder());
          $stmt->bindValue(':details',$location->getLocationDetails());
          $isPrivate = 0;
          if($location->getIsPrivate()== true || $location->getIsPrivate()==1){
            $isPrivate = 1;
          }
          $stmt->bindValue(':hasdirectory',$location->getHasDirectory());
          $stmt->bindValue(':isprivate',$isPrivate);
         
          if($SQL == self::$UPDATE){
          	$stmt->bindValue(':locationSeq',$location->getSeq());
          }else{
          	$createdOn = new DateTime();
          	$createdOn = $createdOn->format('Y-m-d H:i:s');
          	$stmt->bindValue(':createdon',$createdOn);
          	$stmt->bindValue(':repositoryfolder',$location->getRepositoryFolder());
          }
          $stmt->execute();  
          $error = $stmt->errorInfo(); 
          if($error[2] <> ""){
            throw new Exception($error[2]);
          } 
      }catch(Exception $e){
          $logger = Logger::getLogger($ConstantsArray["logger"]);
          $logger->error("Error During Save Location : - ". $e->getMessage());
      }
     
      //I will be put code here for throw exception and show on the screen
     
   }

    public function deleteBySeq($seq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$DELETE);
            $stmt->bindValue(':locationSeq', $seq);
            $stmt->execute();
            $error = $stmt->errorInfo();
    }
    public function FindBySeq($seq){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$FIND_BY_SEQ);
        $stmt->bindValue(':locationSeq', $seq);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $LocationObj =  self::populateObject($row);
        $error = $stmt->errorInfo();
        return $LocationObj;
    }
    public function FindBySeqs($seqs){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare("select * from location where seq in($seqs) order by name ASC");
        $stmt->execute();
        $locationArray = Array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $locationObj = new Location();
            $locationObj =  self::populateObject($row);
            $locationArray[$locationObj->getSeq()] = $locationObj;
        }
       return $locationArray;
    }
    public function FindByUser($userSeq){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$FIND_BY_USER);
        $stmt->bindValue(':userseq', $userSeq);
        $stmt->execute();
        $error = $stmt->errorInfo();
        $locationArray = Array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $location = new Location();
            $location =  self::populateObject($row);
            $locationArray[$location->getSeq()] = $location;
        }
       return $locationArray;
    }
    public function FindAll($orderBy = null){
    	$sql = self::$SELECTALL;
    	if(!empty($orderBy)){
    		$sql .= " order by ". $orderBy;
    	}
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $error = $stmt->errorInfo();
        $locationArray = Array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $locationObj =  self::populateObject($row);
            $locationArray[$locationObj->getSeq()] = $locationObj;
        }
       return $locationArray;
    }
    
    public function FindAllArr(){
        $conn = self::$db->getConnection();
        $query = FilterUtil::applyFilter(self::$SELECTALL); 
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $error = $stmt->errorInfo();
        $locationArray = Array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $private = $row["isprivate"];
            $folder = $row["locationfolder"];
            $isPrivate = false;
            if(!empty($private)){
                $isPivate = true;
            }
            $row["isprivate"] = $isPrivate;
            $path = "http://envirotechlive.com/Repository/" . $folder;
            //$name = "<a target='_blank' href='" . $path . "'> ". $row["name"] . "</a>";
            $row["path"] = $path ;
            array_push($locationArray,$row);
            
        }
       $mainArr["Rows"] = $locationArray;
       $total =    $this->getTotalCount(self::$SELECTALL); 
       $mainArr["TotalRows"] = $total;
       return $mainArr;
    }
    
    private function getTotalCount($sql){
            $conn = self::$db->getConnection();
            $query = FilterUtil::applyFilter($sql,false);
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $count = $stmt->rowCount();
            return $count;  
    } 
     
    public function isExist($locationName){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$FIND_BY_NAME);
        $stmt->bindValue(':name', $locationName);
        $stmt->execute();
        $error = $stmt->errorInfo();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $seqExist = "";
        if($row){
         $seqExist =  $row['seq'];
        }
        return $seqExist;
      }
    public static function populateObject($row){
       $location = new Location();
       $location->setSeq($row['seq']);
       $location->setLocationName($row['name']);
       $location->setLocationDetails($row['details']);
       $location->setLocationFolder($row['locationfolder']);
       $location->setIsPrivate($row['isprivate']);
       $location->setHasDirectory($row['hasdirectory']);
       $location->setRepositoryFolder($row['repositoryfolder']);
       $location->setCreatedOn($row['createdon']);
       return $location;
    }


    //LocationUsers table api starts here//-----------------------

    private static $FIND_LOCATIONS_BY_USER = "select * from locationusers where userseq = :userseq";
    public function FindLocationsByUser($userSeq){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$FIND_LOCATIONS_BY_USER);
        $stmt->bindValue(':userseq', $userSeq);
        $stmt->execute();
        $error = $stmt->errorInfo();
        $locationArray = Array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($locationArray, $row["locationseq"]);
        }
       return $locationArray;
    }
    
     private static $FIND_LOCATIONS_USER = "select l.* from locationusers lu inner join location l on lu.locationseq = l.seq where userseq = :userseq order by l.name";
     public function FindLocationArrByUser($userSeq){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$FIND_LOCATIONS_USER);
        $stmt->bindValue(':userseq', $userSeq);
        $stmt->execute();
        $error = $stmt->errorInfo();
        $locationArray = Array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $location = new Location();
            $location =  self::populateObject($row);
            $locationArray[$location->getSeq()] = $location;
        }
       return $locationArray;
    } 
    
    public function getLoggedInUserOrManagerLocationSeqs(){
       $managerSession = $_SESSION["managerSession"];
       $loggedInManagerSeq =  $managerSession["seq"];   
       $LDS = LocationDataStore::getInstance();    
       $locationSeqs = $LDS->FindLocationsByUser($loggedInManagerSeq);    
       $lseq = $managerSession['locSeq'];
       if(!in_array($lseq,$locationSeqs)){
           array_push($locationSeqs,$lseq);    
       }
       return $locationSeqs;
    }
    private static $FIND_BY_CATEGORY_TYPE = "select distinct l.* from folder f inner join location l on f.locationseq = l.seq  where  f.category = :category";
    public function FindByCategoryType($category,$userSeq,$defaultLocationSeq){
        $locationSeqs = $this->FindLocationsByUser($userSeq);
        if(!in_array($defaultLocationSeq,$locationSeqs)){
            array_push($locationSeqs,$defaultLocationSeq);    
        }
        $locationSeqs = implode(",",$locationSeqs);
        $conn = self::$db->getConnection();
        $query = self::$FIND_BY_CATEGORY_TYPE . " and f.locationseq in($locationSeqs)";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':category', $category);
        $stmt->execute();
        $error = $stmt->errorInfo();
        $locationArray = Array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $location = new Location();
            $location =  self::populateObject($row);
            $locationArray[$location->getSeq()] = $location;
        }
       return $locationArray;
    }
    
    private static $FIND_CALIBRATION_USER = "select l.* from locationusers lu inner join location l on lu.locationseq = l.seq inner join folder f on l.seq = f.locationseq where f.iscalliberationenabled = 1 and userseq = :userseq order by l.name";
    
    public function FindCalibrationLocationArrByUser($userSeq){
    	$conn = self::$db->getConnection();
    	$stmt = $conn->prepare(self::$FIND_CALIBRATION_USER);
    	$stmt->bindValue(':userseq', $userSeq);
    	$stmt->execute();
    	$error = $stmt->errorInfo();
    	$locationArray = Array();
    	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    		$location = new Location();
    		$location =  self::populateObject($row);
    		$locationArray[$location->getSeq()] = $location;
    	}
    	return $locationArray;
    }
    
    private static $FIND_LOCATIONS_COUNT_BY_USER = "select count(*) from locationusers where userseq = :userseq and locationseq = :locationseq";
    public function isLocationExistForUser($userSeq,$locationSeq){
    	$conn = self::$db->getConnection();
    	$stmt = $conn->prepare(self::$FIND_LOCATIONS_COUNT_BY_USER);
    	$stmt->bindValue(':userseq', $userSeq);
    	$stmt->bindValue(':locationseq', $locationSeq);
    	$stmt->execute();
    	$error = $stmt->errorInfo();
    	$result = $stmt->fetch ( PDO::FETCH_NUM );
		$count = intval ( $result [0] );
		return $count > 0;
    }
    

}
?>
