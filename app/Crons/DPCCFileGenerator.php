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
  
  require_once($ConstantsArray['dbServerUrl'] ."/Generators/APPCBGenerator.php");
  require_once($ConstantsArray['dbServerUrl'] .'/log4php/Logger.php');
  Logger::configure('/home/envirote/public_html/app/log4php/log4php.xml');
  
  $appcbGenerator = APPCBGenerator::getInstance();
  $appcbGenerator->generateFile();
  $repColSize = new ArrayObject();
  $repColSize["CO"] = 4 ;
  $repColSize["Ozone"] = 5;
  $repColSize["Ozone(O3)"] = 5;
  $repColSize["NO"] = 5;
  $repColSize["NO2"] = 5;
  $repColSize["NOx"] = 5;
  $repColSize["NH3"] = 5;
  $repColSize["SO2"] = 4;
  $repColSize["Benzene"] = 5;
  $repColSize["Toluene"] = 5;
  $repColSize["Xylene"] = 5;
  $repColSize["P-Xylene"] = 5;
  
  $matRepColSize = new ArrayObject();
  $matRepColSize["PM2.5"] = 7;
  $matRepColSize["PM10"] = 5;
  $matRepColSize["Air Temperature"] = 6;
  $matRepColSize["Amb. Temperature"] = 6;
  $matRepColSize["Relative Humidity"] = 5;
  $matRepColSize["Wind Speed"] = 5; 
  $matRepColSize["Wind Direction"] = 6; 
  $matRepColSize["Vertical Wind Speed"] = 5; 
  $matRepColSize["Barometric Pressure"] = 5; 
  $matRepColSize["Solar Radiation"] = 6; 
  
  
  $stationSuffix[3] = "MM";
  $stationSuffix[4] = "RK";
  $stationSuffix[5] = "PB";
  $stationSuffix[11] = "FR";
  $stationSuffix[17] = "AV";
  
  $roundingNoDecimalChannels = array("Relative Humidity", "Barometric Pressure", "Solar Radiation");
  
  try{ 
  $CCDS = ChannelConfigurationDataStore::getInstance();
  $FDS = FolderDataStore::getInstance();
  $WQDS = WQDDataDataStore::getInstance();
  $folders = $FDS->FindByLocation(3);
  echo ("<br><br> DPCC Generator Starts");
  foreach($folders as $folder){
        $folSeq = $folder->getSeq();
        $toDate = new DateTime();
        $toDateStr = DateUtils::getSQLDateFromDateObj($toDate);
        $fromDate = new DateTime();
        $latestDataRows = $WQDS->getChannelsLatestInfo($folSeq);
        if(count($latestDataRows)>0){
            $toDateStr = $latestDataRows['dated'];
            $fromDate = new DateTime($latestDataRows['dated']);
        }
        echo ("To:". $fromDate ->format('Y-m-d H:i:s')."<br>\n");
        echo ("FolderSeq:" . $folSeq ." FolderName:".$folder->getFolderName() ."<br>\n");

	$fromDate->modify('-1 day');
	echo ("From:". $fromDate ->format('Y-m-d H:i:s')."<br>\n");
//        $fromDate->add(DateInterval::createFromDateString('-1Day'));
        $fromDateStr = DateUtils::getSQLDateFromDateObj($fromDate);
        $dataRows = $WQDS->getAllDataByFol($fromDateStr, $toDateStr, $folSeq);
        if(count($dataRows) > 1){
            $channelConfigs = $CCDS->FindByFolder($folSeq);
            $totalChannels = count($channelConfigs);
            $repStr = "                 CO   O3    NO    NO2   NOx   NH3   SO2  Ben   Tol   PXY\r\n";
            $repStr .="                  mg/m³µg/m³ µg/m³ µg/m³ µg/m³µg/m³ µg/m³ µg/m³ µg/m³ µg/m³\r\n";
            
            $matRepStr = "                   PM2.5  PM10     AT    RH    WS     WD   VWS    BP     SR\r\n";
            $matRepStr .="                   µg/m³ µg/m³     °C     %   m/s    deg   m/s  mmHg   W/m²\r\n";
            
            foreach($dataRows as $row){
                $dated = new DateTime($row['wqdfiledatadated']);
                $repStr .= "   ".$dated->format("d/m/y H:i");
                $matRepStr .= "".$dated->format("d/m/Y H:i");
                foreach($channelConfigs as $channelConfiguration){
                    $chNo = $channelConfiguration->getChannelNumber();
                    $chUnit = $channelConfiguration->getChannelUnit();
                    $chName = $channelConfiguration->getChannelName();
                    $chStatus = $row['ch'.$chNo.'status'];
		    $chValue = "";
                    if($chStatus == 128 || $chStatus == 129){
	                    $chValue = $row['ch'.$chNo.'value'];
	                    $chValue = ConvertorUtils::getPrescribedValue($chName,$chValue);                    
                    }
                    if (strpos($chValue ,'.') !== false) {
		       $chValue =  number_format($chValue , 1, ".", "");
	            }
                    if(in_array($chName , $roundingNoDecimalChannels)){
                       	$chValue = round($chValue,0);
                    }
                    
                    $chValueLength = strlen($chValue);
                    $spacesToAdd = 0;
                    if(array_key_exists($chName, $repColSize)){
                        $spacesToAdd = ($repColSize[$chName] - $chValueLength) + 1;
                        for($i=0;$i<$spacesToAdd;$i++){
                            $repStr .= " ";    
                        }
                        $repStr .= $chValue; 
                    }else if(array_key_exists($chName, $matRepColSize)){
                        $spacesToAdd = ($matRepColSize[$chName] - $chValueLength) + 1;
                        for($j=0;$j<$spacesToAdd;$j++){
                            $matRepStr .= " ";    
                        }
                        $matRepStr .= $chValue; 
                    }  
                }
                $repStr .= "\r\n";
                $matRepStr .= "\r\n";      
            }
            $stSuffix = "AV";
            if($stationSuffix[$folSeq] != null){
                $stSuffix = $stationSuffix[$folSeq];    
            }
            $repFileName = "/home/envirote/public_html/Generated/DPCC/". $folSeq ."/datareport". $stSuffix .".TXT";
            $matRepFileName = "/home/envirote/public_html/Generated/DPCC/". $folSeq ."/metdatareport". $stSuffix .".TXT";
            
            if(!file_exists(dirname($repFileName))){
                mkdir(dirname($repFileName), 0777, true);
            }
            $fh = fopen($repFileName, 'w') or die("can't open file");
            fwrite($fh, $repStr);
            fclose($fh);  
            
            if(!file_exists(dirname($matRepFileName))){
                mkdir(dirname($matRepFileName), 0777, true);
            }
            $fh1 = fopen($matRepFileName, 'w') or die("can't open file");
            fwrite($fh1, $matRepStr);
            fclose($fh1);
            //echo $matRepStr;
        }   
  }
}catch(Exception $e){echo $e;}



echo "done";  
?>