
<?
    require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/WQDDataDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/ChannelConfigurationDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/FolderDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."Utils/PrescribedLimitsUtils.php");
    require_once($ConstantsArray['dbServerUrl'] ."Utils/ConvertorUtils.php");
    require_once($ConstantsArray['dbServerUrl'] ."Utils/StringUtils.php");

    
    
    $folSeqs = $_GET["folSeq"];
    $folSeqArray =  explode(",", $folSeqs);
    $fromDate = new DateTime($_GET["fromDate"]);
    $toDate = new DateTime($_GET["toDate"]);
    $toDate->setTime(23,59,59);
    
    $chSeq = $_GET["chSeq"];
    $CCDS = ChannelConfigurationDataStore::getInstance();
    $channel = $CCDS->FindBySeq($chSeq);
    $channelNumber = $channel->getChannelNumber();
    $channelName = $channel->getChannelName();
    $channelUnit = $channel->getChannelUnit();
    
    $fromDate =  $fromDate->format("Y/m/d  H:i:s");
    $toDate =  $toDate->format("Y/m/d  H:i:s");
    $WQDS = WQDDataDataStore::getInstance();
    $dailyAverageArray = null;
    foreach($folSeqArray as $folSeq){
      $arr = $WQDS->getDailyAverageValues($fromDate,$toDate,$folSeq,$channelNumber);
      $folder = FolderDataStore::getInstance()->FindBySeq($folSeq);
      $dailyAverageArray[$folder->getFolderName()] = $arr; 
    }
    //get values for the whole day average
    //$channelConfig = ChannelConfigurationDataStore::getInstance()->FindByFolderAndChannelNo($folSeqArray[0],$channelNumber);
    $limit = PrescribedLimitsUtils::getPrescribedLimit($channelName);
    if(ConvertorUtils::getPrescribedUnit($channelName) != null){
        $channelUnit = ConvertorUtils::getPrescribedUnit($channelName);
    }

?>

<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Channel Values Table</title>
        <?include("_jsInclude.php");?>
    </head>
    <body>
    <?if($limit != null){?>
    <h2>Prescribed limit for <?echo $channelName?> is <?echo $limit?> <?echo htmlentities($channelUnit)?></h2>
    <?}?>
    
<?
    if($dailyAverageArray!= null){
          $arrKeys =  array_keys($dailyAverageArray);//looping over foldernames
          
          foreach($arrKeys as $key){
            if($key != null){
              echo "<div style='float:left;margin-left:15px;border:solid thin grey;padding:12px;background:white'>";
              echo "<b style='padding:6px 12px 6px 12px' class='ui-widget-header'>Average Data for ". $key ."</b>";
              echo "<ul>";
              $avrArray =  $dailyAverageArray[$key];
              $totalAverages = 0;
              foreach($avrArray as $avr){
                  $val = $avr[1];
                    if(ConvertorUtils::getPrescribedValue($channelName,$val) != null){
                        $val = ConvertorUtils::getPrescribedValue($channelName,$val);
                    }  
                  echo "<li>". $avr[0] ." - ". round($val,2) ." ". htmlentities($channelUnit) ."</li>";
                  $totalAverages = $totalAverages + round($val,2);
              }
              echo "</ul>";
              echo"</div>";
            }
          }//end of loop over various folders
    }//end of condition?>   
            
            
        
        </table>       
                
    </body>
</html>