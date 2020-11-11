<?php
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/DateUtils.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/FileSystemUtils.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/ConvertorUtils.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/StringUtils.php");
  
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDFileDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDDataDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php");
  

  require_once($ConstantsArray['dbServerUrl'] ."/Generators/ManikgarhGenerator.php");
  date_default_timezone_set('Asia/Calcutta');
  $manigarhGenerator = ManikgarhGenerator::getInstance();
  $manigarhGenerator->generateFile();
  
  //$toDateStr = date('Y/m/d H:00:00', strtotime('-1 hour'));
  //echo $toDateStr;
  ///usr/local/php5/bin/php5 
  echo "done";  
?>