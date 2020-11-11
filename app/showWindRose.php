
<?
    require_once('IConstants.inc');
    require_once($ConstantsArray['dbServerUrl'] ."/Utils/FileSystemUtils.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDFileDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/LocationDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php");
    require_once($ConstantsArray['dbServerUrl']. "Utils/ExportUtils.php");
    require_once ('jpgraph/jpgraph/jpgraph.php');
    require_once ('jpgraph/jpgraph/jpgraph_windrose.php');
    
    
    $folderSeq = $_GET['folSeq'];
    $fromDateStr = $_GET['fromDate'];
    $toDateStr = $_GET['toDate'];
    $intervalSelected = $_GET['interval'];
    
    
    $WSChannelNo = 0;
    $WDChannelNo = 0;
    $CCDS = ChannelConfigurationDataStore::getInstance();
    $ChannelsInfo = $CCDS->FindByFolder($folderSeq);  
    foreach($ChannelsInfo as $channel){
        if($channel->getChannelName() == "Wind Speed"){
            $WSChannelNo = $channel->getChannelNumber();  
        }else if($channel->getChannelName() == "Wind Direction"){
            $WDChannelNo = $channel->getChannelNumber(); 
        }
    }
    
    $fromDate = new DateTime($fromDateStr);
    $toDate = new DateTime($toDateStr);
    $toDate->setTime(23,59,59);
    
    $fromDate =  $fromDate->format("Y/m/d  H:i:s");
    $toDate =  $toDate->format("Y/m/d  H:i:s");
    
    $WQDS = WQDDataDataStore::getInstance();
    $WSArrayDB = $WQDS->getChannel($fromDate,$toDate,$folderSeq,$WSChannelNo,$intervalSelected);
    $WDArrayDB = $WQDS->getChannel($fromDate,$toDate,$folderSeq,$WDChannelNo,$intervalSelected);
    
    $WS = array();
    $WD = array();
    
    $i = 0;
    foreach($WSArrayDB as $ws){
       $WS[$i++] = floatval($ws[1]);
    }
    $i= 0;
    foreach($WDArrayDB as $wd){
       $WD[$i++] = floatval($wd[1]) ;
    }


//$WD = array(10,20,30,40,55,67,70,120,300,200,311,150,100,90,200,100,30,23,232,300);
//$WS = array(0.9,1,2,3,1,2.3,1.3,2,3,0.9,1.3,1.5,2,4,2.5,3,2,1.4,1.2,5);

for ($ii = 0; $ii < count($WD); $ii++){
    switch($WD[$ii]){
        case ($WD[$ii] >= 348.75 or $WD[$ii] < 11.25):
            $direction_array["N"][] = $WS[$ii];
            break;
        case ($WD[$ii] >= 11.25 and $WD[$ii] < 33.75):
            $direction_array["NNE"][] = $WS[$ii];
            break;
        case ($WD[$ii] >= 33.75 and $WD[$ii] < 56.25):
            $direction_array["NE"][] = $WS[$ii];
            break;
        case ($WD[$ii] >= 56.25 and $WD[$ii] < 78.75):
            $direction_array["ENE"][] = $WS[$ii];
            break;
        case ($WD[$ii] >= 78.75 and $WD[$ii] < 101.25):
            $direction_array["E"][] = $WS[$ii];
            break;
        case ($WD[$ii] >= 101.25 and $WD[$ii] < 123.75):
            $direction_array["ESE"][] = $WS[$ii];
            break;
        case ($WD[$ii] >= 123.75 and $WD[$ii] < 146.25):
            $direction_array["SE"][] = $WS[$ii];
            break;
        case ($WD[$ii] >= 146.25 and $WD[$ii] < 168.75):
            $direction_array["SSE"][] = $WS[$ii];
            break;
        case ($WD[$ii] >= 168.75 and $WD[$ii] < 191.25):
            $direction_array["S"][] = $WS[$ii];
            break;
        case ($WD[$ii] >= 191.25 and $WD[$ii] < 213.75):
            $direction_array["SSW"][] = $WS[$ii];
            break;
        case ($WD[$ii] >= 213.75 and $WD[$ii] < 236.25):
            $direction_array["SW"][] = $WS[$ii];
            break;
        case ($WD[$ii] >= 236.25 and $WD[$ii] < 258.75):
            $direction_array["WSW"][] = $WS[$ii];
            break;
        case ($WD[$ii] >= 258.75 and $WD[$ii] < 281.25):
            $direction_array["W"][] = $WS[$ii];
            break;
        case ($WD[$ii] >= 281.25 and $WD[$ii] < 303.75):
            $direction_array["WNW"][] = $WS[$ii];
            break;
        case ($WD[$ii] >= 303.75 and $WD[$ii] < 326.25):
            $direction_array["NW"][] = $WS[$ii];
            break;
        case ($WD[$ii] >= 326.25 and $WD[$ii] < 348.75):
            $direction_array["NNW"][] = $WS[$ii];
            break;
    }
}
$direction_keys = array_keys($direction_array);
$max_wind = round(max($WS),0);
$num_data = count($WS);
$wind_range_max = $max_wind < 20 ? 25:$max_wind;
$data_range_array = array(1,5,10,15,20,$wind_range_max);
foreach ($direction_keys as $direction) {
        for ($ii = 0; $ii <= 5; $ii++){
            $count_data[$ii] = 0;
        }
        $raw_data = $direction_array[$direction];
        foreach ($raw_data as $temp_speed) {
            if ($temp_speed >= 0 and $temp_speed < $data_range_array[0]) {
                $count_data[0]++;
            } elseif ($temp_speed >= $data_range_array[0] and $temp_speed < $data_range_array[1]) {
                $count_data[1]++;
            } elseif ($temp_speed >= $data_range_array[1] and $temp_speed < $data_range_array[2]) {
                $count_data[2]++;
            } elseif ($temp_speed >= $data_range_array[2] and $temp_speed < $data_range_array[3]) {
                $count_data[3]++;
            } elseif ($temp_speed >= $data_range_array[3] and $temp_speed < $data_range_array[4]) {
                $count_data[4]++;
            } elseif ($temp_speed >= $data_range_array[4]) {
                $count_data[5]++;
            }
        }
        for ($ii = 0; $ii <= 5; $ii++) {
            $plot_data[$direction][$ii] = 0;   
        }
        for ($ii = 0; $ii <= 5; $ii++) {
            $plot_data[$direction][$ii] = ($count_data[$ii] / $num_data) * 100;
        }
}

$graph = new WindroseGraph(600,600);
$graph->title->Set('Windrose graph '. 'for the period between '. $fromDateStr .' and '. $toDateStr);
$wp = new WindrosePlot($plot_data);

$graph->Add($wp);
$graph->Stroke();
?>
