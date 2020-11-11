<?php
   require_once('IConstants.inc');
   require_once($ConstantsArray['dbServerUrl'] ."BusinessObjects/FolderUser.php");
   require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/MainDB.php");
   
class FolderUserDataStore{ 
     private static $folderUserDataStore;
     private static $db;
     private static $INSERT = "INSERT INTO folderuser (folderseq,userseq,permission) VALUES(:folderseq, :userseq, :permission)";
     private static $DELETEBYFOLDER = "DELETE from folderuser  where folderseq = :folderseq";
     private static $DELETEBYUSER = "DELETE from folderuser  where userseq = :userseq";      
     private static $SELECTBYFOLDER = "SELECT userseq, permission from folderuser  where folderseq = :folderseq";      
     private static $SELECTBYUSER = "SELECT folderseq, permission from folderuser  where userseq = :userseq";  
     
     public function __construct(){
       self::$db = MainDB::getInstance();       
     }

    public static function getInstance()
    {
        if (!self::$folderUserDataStore)
        {
            self::$folderUserDataStore = new FolderUserDataStore();           
            return self::$folderUserDataStore;
        }
        return self::$folderUserDataStore;        
    }  
   public function DeleteByFolder($folderSeq){
      $SQL = self::$DELETEBYFOLDER;
      $conn = self::$db->getConnection();
      $stmt = $conn->prepare($SQL);
      
      $stmt->bindValue(':folderseq', $folderSeq); 
     
      $stmt->execute();  
      //I will be put code here for throw exception and show on the screen   
      $error = $stmt->errorInfo();
   }
    public function DeleteByUser($userSeq){
      $SQL = self::$DELETEBYUSER;
      $conn = self::$db->getConnection();
      $stmt = $conn->prepare($SQL);
      
      $stmt->bindValue(':userseq', $userSeq); 
     
      $stmt->execute();  
      //I will be put code here for throw exception and show on the screen   
      $error = $stmt->errorInfo();
   }  
    
   public function Save(FolderUser $folderUser){
      try{
          $SQL = self::$INSERT;
          $conn = self::$db->getConnection();
          $stmt = $conn->prepare($SQL);
          
          $stmt->bindValue(':folderseq', $folderUser->getFolderSeq()); 
          $stmt->bindValue(':userseq',$folderUser->getUserSeq());
          $stmt->bindValue(':permission',$folderUser->getPermission());
          $stmt->execute();  
          $error = $stmt->errorInfo();
          if($error[2] <> ""){
              throw new RuntimeException($error[2]);
          }    
      }catch(Exception $e){
          $logger = Logger::getLogger($ConstantsArray["logger"]);
          $logger->error("Error During Save Folder User : - " . $e->getMessage());
      } 
     
      
   }
    public function FindByFolder($folderSeq){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$SELECTBYFOLDER);
        $stmt->bindValue(':folderseq', $folderSeq);
        $stmt->execute();
        $folderUserArray = Array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $folderUser = new FolderUser();
            $folderUser =  self::populateObject($row);
            array_push($folderUserArray, $folderUser);
        }
         return $folderUserArray;
   }
   public function FindByUser($userSeq){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$SELECTBYUSER);
        $stmt->bindValue(':userseq', $userSeq);   
        $stmt->execute();
        $folderUserArray = Array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $folderUser = new FolderUser();
            $folderUser =  self::populateObject($row);
            array_push($folderUserArray, $folderUser);
        }
         return $folderUserArray;
   }
   public function FindAll(){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$SELECTALL);
        $stmt->execute();
        $folderUserArray = Array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $folderUser = new FolderUser();
            $folderUser =  self::populateObject($row);
            array_push($folderUserArray, $folderUser);
        }
         return $folderUserArray;
   }
   public function FindByLocations($locSeqs){
        $conn = self::$db->getConnection();
        $SQL = "select folderuser.* from folderuser INNER JOIN folder ON folder.seq= folderuser.folderseq and folder.locationseq in ($locSeqs)";
        $stmt = $conn->prepare($SQL);
        $stmt->execute();
        $folderUserArray = Array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $folderUser = new FolderUser();
            $folderUser =  self::populateObject($row);
            array_push($folderUserArray, $folderUser);
        }
         return $folderUserArray;
   }
   
   
   public static function populateObject($row){
       $folderUser = new FolderUser();
       $folderUser->setUserSeq($row['userseq']);
       $folderUser->setFolderSeq($row['folderseq']);
       $folderUser->setPermission($row['permission']);
       
       return $folderUser;
   }
}
?>
