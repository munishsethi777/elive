<?php
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/MainDB.php");
 
  class Configuration {
      private static $SQL = "select configvalue from configurations where configkey= :configkey"; 
      private static $UPDATE = "update configurations set configvalue= :value where configkey=:key";
      
      private $configKey, $configValue;
        public function getConfiguration($configKey){
            $db = new MainDB();
            $conn = $db->getConnection();
            $stmt = $conn->prepare(self::$SQL);
            $stmt->bindValue(':configkey', $configKey); 
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $error = $stmt->errorInfo();
            $val = $row['configvalue'];
            return $val;
       }
       public function saveConfig($key,$value){
            $db = new MainDB();
            $conn = $db->getConnection();
            $stmt = $conn->prepare(self::$UPDATE);            
            $stmt->bindValue(':value', $value);
            $stmt->bindValue(':key', $key);  
            $stmt->execute();
            $error = $stmt->errorInfo();
       }
  }
  
  class ConfigurationKeys{
      public static $adminPassword = "adminPassword";
      public static $adminEmailId = "adminEmailId";
      public static $reminderInvokeMinutes = "reminderInvokeMinutes";
      public static $reminderIntervalMinutes = "reminderIntervalMinutes";   
  }
?>
