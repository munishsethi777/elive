<?php
      require_once('IConstants.inc');
      require_once($ConstantsArray['dbServerUrl'] ."/Utils/FileSystemUtils.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Utils/ConvertorUtils.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Utils/StringUtils.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserWQD.php");  
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDFileDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php"); 
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php"); 
      if(empty($_GET['folSeq'])){
        die;   
      }
      $folderSeq = $_GET['folSeq'];
      $isConvertUnits = $_GET['isConvertUnits'];
      $WQDDataDS = WQDDataDataStore::getInstance();
      $WQDInfo = $WQDDataDS->getChannelsLatestInfo($folderSeq);
      $CCDS = ChannelConfigurationDataStore::getInstance();
      $channelsDetails = $CCDS->FindByFolder($folderSeq); 
      
      
      $channelsData = $WQDInfo['channelsInfo'];
      
      $unitName = new ArrayObject();
      foreach($channelsDetails as $channel){
        $unitName[$channel->getChannelName()] = $channel->getChannelUnit(); 
      }
      
      foreach($channelsDetails as $channel){
                $chNo = $channel->getChannelNumber();
                $chName = $channel->getChannelName();
                if($isConvertUnits == 1){
                    $chData = $channelsData['ch'. $chNo .'value'];
                    $channelsData["ch". $chNo ."value"] = ConvertorUtils::getPrescribedValue($chName,$chData);
                    $chConvertedUnitVal = ConvertorUtils::getPrescribedUnit($chName);
                    $channelsData["ch". $chNo ."unit"] = ConvertorUtils::getUTF8Encoded($chConvertedUnitVal);;
                }else{
                    $unitVal = $unitName[$chName];
                    $channelsData["ch". $chNo ."unit"] = ConvertorUtils::getUTF8Encoded($unitVal);;
                }
          }
          $WQDInfo['channelsInfo'] = $channelsData;
          //loop over $channelsData and make conversions as per conversion table;
      
     echo (json_encode($WQDInfo));

  
  
?>
