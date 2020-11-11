<?php
      require_once('IConstants.inc');
      require_once($ConstantsArray['dbServerUrl'] ."/Utils/FileSystemUtils.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Utils/ConvertorUtils.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Utils/StringUtils.php");

      require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserWQD.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDFileDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDStackDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Managers/CPCBMgr.php");

      if(empty($_GET['folderSeq'])){
        die;
      }
      $folderSeq = $_GET['folderSeq'];
      $FDS = FolderDataStore::getInstance();
      $folderObj = $FDS->FindBySeq($folderSeq);
      $WQDDataDS = WQDDataDataStore::getInstance();
      $WQDStackDataDS = WQDStackDataStore::getInstance();
      $WQDInfo;
      if($folderObj->getStationType() == "stack" || $folderObj->getStationType() == "effluent"){
            $WQDInfo = $WQDStackDataDS->getChannelsLatestInfo($folderSeq);
      }else{
            $WQDInfo = $WQDDataDS->getChannelsLatestInfo($folderSeq);
      }

      $toDate = new DateTime($WQDInfo['dated']);//current date as todate
      $fromDateClone = clone $toDate;
      $fromDate = $fromDateClone->sub(new DateInterval('P1D'));
      $toDateStr = $toDate->format("Y/m/d  H:i:s");
      $fromDateStr = $fromDate->format("Y/m/d  H:i:s");
      $CCDS = ChannelConfigurationDataStore::getInstance();
      $channelsDetails = $CCDS->FindByFolder($folderSeq);
      $WQDAvgInfo;
      if($folderObj->getStationType() == "stack" || $folderObj->getStationType() == "effluent"){
            $WQDAvgInfo = $WQDStackDataDS->getChannelsAverageInfo($folderSeq,$fromDateStr,$toDateStr,$channelsDetails);
      }else{
            $WQDAvgInfo = $WQDDataDS->getChannelsAverageInfo($folderSeq,$fromDateStr,$toDateStr,$channelsDetails);
      }
      $isLoggedIn = $_GET['$isLoggedIn'];
      $mapsJSON = CPCBMgr::getCPCBMapsJson($isLoggedIn);
      $folder = $FDS->FindBySeq($folderSeq);
      $folderMapInfo = $mapsJSON[$folderSeq];
      $channelsData = $WQDInfo['channelsInfo'];//channel details is arrray of chNo and chValue
      //we need chNo to chName

      $unitName = new ArrayObject();
      foreach($channelsDetails as $channel){
        $unitName[$channel->getChannelName()] = $channel->getChannelUnit();
      }

      foreach($channelsDetails as $channel){
	$chNo = $channel->getChannelNumber();
	$chName = $channel->getChannelName();
	$chUnit = $channel->getChannelUnit();
	$chData = $channelsData['ch'. $chNo .'value'];
	if((float)$chData <= 0 && $chName !='Vertical Wind Speed'){
		$chData ="n.o";
	}
	if((float)$chData ==985 && ($chName =='PM10' || $chName=='PM2.5')){
		$chData = "n.o";
	}

	$channelsData["ch". $chNo ."value"] = ConvertorUtils::getPrescribedValue($chName,$chData);
	$chConvertedUnitVal = ConvertorUtils::getPrescribedUnit($chName);
	    if($chConvertedUnitVal != ""){
		    //$chUnit = $chConvertedUnitVal;
	    }
	//$chUnit = ConvertorUtils::getUTF8Encoded($chUnit);
	$channelsData["ch". $chNo ."unit"] = $chUnit;
      }
      $WQDInfo['channelsInfo'] = $channelsData;
      $str = '<table><tr><td valign="top">';
      $str .= '<table class="mapDataInfoTable"><tr><td class="ui-state-active">Company Name</td><td>'. $folder->getIndustryName().'</td></tr>';      
      $str .= ' <tr><td class="ui-state-active">Location</td><td>'.$folder->getCity() . ", " . $folder->getState().'</td></tr>';
      $str .= ' <tr><td class="ui-state-active">Station</td><td>'.$folder->getStationName().'</td></tr>';
      $str .= ' <tr><td class="ui-state-active">Industry Type</td><td>'.$folder->getCategory().'</tr>';      
      $str .= ' <tr><td class="ui-state-active">Longitude</td><td>'.$folderMapInfo['longitude'].'</td></tr>';
      $str .= ' <tr><td class="ui-state-active">Latitude</td><td>'.$folderMapInfo['latitude'].'</td></tr>';
                    $dat = new DateTime($WQDInfo['dated']);
                    $datStr = $dat->format("d-m-Y H:i");
      $str .= ' <tr><td class="ui-state-active">Last Update On</td><td>'. $datStr .'</td></tr>';

      $str .= ' <tr><td class="ui-state-active">Date of Station Establishment</td><td>'. $folderMapInfo['dateOfEstablishment'] .'</td></tr>';
      $str .= ' <tr><td class="ui-state-active">Data from which data is available</td><td>'. $folderMapInfo['dateOfAvailability'] .'</td></tr>';

      $str .= ' <tr><td colspan="2" style="text-align:center">';
      if($folderObj->getStationType() == "stack"){
            $str .= '<div><a class="btn" href="cpcbStackReportMultiStation.php?lsp='. $folderObj->getLocationSeq() .'">Advance Search</a></div></td></tr></table></td><td>';
      }else if($folderObj->getStationType() == "effluent"){
            $str .= '<div><a class="btn" href="cpcbEffluentReportMultiStation.php?lsp='. $folderObj->getLocationSeq() .'">Advance Search</a></div></td></tr></table></td><td>';
      }else{
          $str .= '<div><a class="btn" href="cpcbReportMultiStation.php?stid='. $folderSeq .'">Advance Search</a></div></td></tr></table></td><td>';
      }

      $str .= '<table border="0" cellpadding="2" cellspacing="0" class="mapDataTable"><tr><td class="ui-state-active">Parameters</td><td class="ui-state-active">Current</td><td class="ui-state-active">Avg <br> 24 Hrs</td><td class="ui-state-active">Max <br> 24 Hrs</td></tr>';
    foreach($channelsDetails as $channelConfig){
        if($channelConfig->getIsVisible() == "1"){
            $chStation = $channelConfig->getChannelStation();
            if(!empty($chStation)){
                $chStation = " - " .  $chStation ;
            }
            $chUnit = $WQDInfo['channelsInfo']['ch'. $channelConfig->getChannelNumber() .'unit'];

            $str .= "<tr>";
            $str .= "<td class='chName'>". $channelConfig->getChannelName() . $chStation ." (". $chUnit .")</td>";
            $str .="<td class='chData'>" ;
            $str .= $WQDInfo['channelsInfo']['ch'. $channelConfig->getChannelNumber() .'value'] ;
            $str .= "</td>";
            $str .="<td class='chData'>" ;
            $str .= $WQDAvgInfo['ch'. $channelConfig->getChannelNumber() .'avg'] ;
            $str .= "</td>";
            $str .="<td class='chData'>" ;
            $str .= $WQDAvgInfo['ch'. $channelConfig->getChannelNumber() .'max'] ;
            $str .= "</td>";
            $str .= "</tr>";
        }
    }
    $str .= "</table></td></tr></table>";
    echo $str;
?>

