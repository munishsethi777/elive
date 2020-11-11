
<?
    session_start();
    require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/WQDDataDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/FolderDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/LocationDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/ChannelConfigurationDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/Utils/ConvertorUtils.php");
    
    $chSeq = $_GET["chSeq"];
    $isPL = 0;
    if($_GET["isPL"] != null){
    	$isPL = $_GET["isPL"];
    }
    
    $CCDS = ChannelConfigurationDataStore::getInstance();
    $channel = $CCDS->FindBySeq($chSeq);
    $channelName = $channel->getChannelName();
    $toDate = new DateTime($_GET["fromDate"]); //get todate from post
    $toDateFormatted =  $toDate->format("m/d/Y H:i:s");//formatting date to add interval
    $interval = '24';
    $fromDate = date("m/d/Y  h:i:s A",strtotime($toDateFormatted . " - " . $interval . " hour"));//from date dateformat
    $fromDate = new DateTime($fromDate);
    $fromDate =  $fromDate->format("Y/m/d  H:i:s");
    $toDateStr =  $toDate->format("Y/m/d  H:i:s");
    
    
    $WQDS = WQDDataDataStore::getInstance();
    $arr = $WQDS->getChannel($fromDate,$toDateStr,$channel->getFolderSeq(),$channel->getChannelNumber(),"1hrs");
    
    $folder = FolderDataStore::getInstance()->FindBySeq($channel->getFolderSeq());
    $locationSeq = $folder->getLocationSeq();
    $location = LocationDataStore::getInstance()->FindBySeq($locationSeq);
    if($_SESSION["userlogged"]["locSeq"] != $locationSeq){
        header("location: index.php?err=true&locSeq=". $locationSeq);
        die;
    }  
	$channelUnit = $channel->getChannelUnit();
	if($isPL == 1){
	        if(ConvertorUtils::getPrescribedUnit($channelName) != null){
	            $channelUnit = ConvertorUtils::getPrescribedUnit($channelName);
	        }
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
    <?include("_includeHeader.php");?>
     <Div style="font-family:verdana;size:11px;font-weight:bold;margin-top:10px;margin-bottom:10px;color:#3E576F">Last 24 Hours Data for <?echo $channelName ;?> (in <?echo htmlentities($channelUnit) ;?>)   </Div>
       <table width="400" border="1">
            <tr>
                <td class="ui-widget-header">Date & Time</td>
                <td class="ui-widget-header">Channel Reading</td>
            </tr>
 <?
    foreach($arr as $value){
    	$val = $value['1'];
    	if($isPL == 1){
	        if(ConvertorUtils::getPrescribedValue($channelName,$val) != null){
	            $val = ConvertorUtils::getPrescribedValue($channelName,$val);
	        }
    	}
        echo ("<tr>");
        echo ("<td class='ui-widget-content'>". $value['wqdfiledatadated'] ."</td>");
        echo ("<td style='text-transform:lowercase' class='ui-widget-content'>". $val .' '. htmlentities($channelUnit) ."</td>");
        echo ("</tr>");

     }

?>    
            
            
        
        </table>       
                
    </body>
</html>