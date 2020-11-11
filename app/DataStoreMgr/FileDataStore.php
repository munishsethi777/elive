<?php
  require_once('IConstants.inc'); 
  require_once($ConstantsArray['dbServerUrl'] . "BusinessObjects/Files.php");
  require_once("MainDB.php");
   
  class FileDataStore{
    
    private static $fileDataStore;
    private static $db;
    private static $INSERT = "insert into files (filettype,filename,dataofupload,userseq,folderseq) Values(:filettype,:filename,:dataofupload,:userseq,:folderseq)";
    
    private static $SELECT = "select * from files";
    private static $FIND_BY_SEQ = "select * from file where seq=:seq";
    private static $FIND_BY_Folder = "select * from folder where folderseq=:folderseq";     
    
    public function __construct(){
        self::$db = MainDB::getInstance();
         
    }

    public static function getInstance()
    {
        if (!self::$fileDataStore)
        {
            self::$fileDataStore = new FileDataStore();           
            return self::$fileDataStore;
        }
        return self::$fileDataStore;        
    }
    
      public function Save(Files $file){
      $SQL = self::$INSERT;
     
      $conn = self::$db->getConnection();
      $stmt = $conn->prepare($SQL);
     
      $stmt->bindValue(':filetype', $file->getFileType()); 
      $stmt->bindValue(':filename', $file->getFileName());
      $stmt->bindValue(':dataofupload',$file->getDateOfUpload());
      $stmt->bindValue(':userseq',$file->getUserSeq());
      $stmt->bindValue(':folderseq',$file->getFileSeq());
           
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
        $fileArray = Array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fileObj = new SLM();
            $fileObj =  self::populateObject($row);
            $fileArray[$fileObj->getSeq()] = $fileObj;
        }
         return $fileArray;
    }
    
    public function FindBySeq($seq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$FIND_BY_SEQ);
            $stmt->bindValue(':seq', $seq); 
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $fileObj =  self::populateObject($row);
            $error = $stmt->errorInfo(); 
            return $fileObj;
     } 
     public function FindByFolder(){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$FIND_BY_Folder);
        $stmt->execute();
        $fileArray = Array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fileObj = new Folder();
            $fileObj =  self::populateObject($row);
            $fileArray[$fileObj->getSeq()] = $fileObj;
        }
         return $fileArray;
   }
   
    public function populateObject($rsItem){
       
            $seq_ = $rsItem["seq"] ;
            $fileType =  $rsItem["filetype"] ;
            $fileName = $rsItem["filename"] ;
            $date = $rsItem["dateofupload"];
            $userSeq = $rsItem["userseq"] ;
            $folderSeq = $rsItem["folderseq"] ;
            
            $file = new Files();
            $file->setFileSeq($seq_);
            $file->setFileName($fileName);
            $file->setFileType($fileType);
            $file->setDateOfUpload($date);
            $file->setUserSeq($userSeq);
            $file->setFolderSeq($folderSeq);
            return $file; 
     }
  }
?>
