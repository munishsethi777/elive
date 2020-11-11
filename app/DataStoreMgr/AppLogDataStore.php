<?php
require_once('IConstants.inc');

require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/MainDB.php");
require_once($ConstantsArray['dbServerUrl'] . "/Utils/FilterUtil.php");
  class AppLogDataStore{      
      private static $logDataStore;
      private static $db;
      
      private static $SELECT_ALL = "select * from my_log";
      private $INSERT_ = "insert into applogs (message,stacktrace,type,dated) values(:message,:stacktrace,:type,:dated) ";
      public function __construct(){
        self::$db = MainDB::getInstance();   
      }

    public static function getInstance()
    {
        if (!self::$logDataStore)
        {
            self::$logDataStore = new AppLogDataStore();           
            return self::$logDataStore;
        }
        return self::$logDataStore;        
    }
    
     private function getTotalCount(){
        $conn = self::$db->getConnection();
        $query = FilterUtil::applyFilter(self::$SELECT_ALL,false);
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $count = $stmt->rowCount();
        return $count;  
     }
     public function getAllLogsJson(){
        $mainArray = $this->getAllLogs();
        $jsonArr["Rows"] = $mainArray;
        $count = $this->getTotalCount();
        $jsonArr["TotalRows"] = $count;
        return json_encode($jsonArr);
     }
   
     public function getAllLogs(){
        $conn = self::$db->getConnection();
        $query = FilterUtil::applyFilter(self::$SELECT_ALL);
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $error = $stmt->errorInfo();
        $rows = $stmt->fetchAll();
        $mainArray = array();
        foreach($rows as $row){
            $array = array();
            $array["timestamp"] = $row["timestamp"];
            $array["message"] = $row["message"];
            $array["level"] = $row["level"];
            $array["file"] = $row["file"];
            $array["line"] = $row["line"];
            array_push($mainArray,$array);
        }
        return $mainArray; 
     }
    public function populateObject($rsItem){
            $seq_ = $rsItem["seq"] ;
            $message =  $rsItem["message"] ;
            $stackTrace = $rsItem["stacktrace"] ;
            $type = $rsItem["type"];
            $dated = $rsItem["dated"] ;
            
            $log = new AppLog();
            $log->setDated($dated);
            $log->setLogType($type);
            $log->setStackTrace($stackTrace);
            $log->setMessage($message);
            return $log; 
     }
  }
  
?>
