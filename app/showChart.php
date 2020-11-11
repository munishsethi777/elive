<?php 
    session_start();
    require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/WQDDataDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/Utils/ConvertorUtils.php");
    require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/FolderDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/LocationDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/ChannelConfigurationDataStore.php");
    $chSeq= $_GET["chSeq"];
    $channelUnit = "";
    $CCDS = ChannelConfigurationDataStore::getInstance();
    $channel = $CCDS->FindBySeq($chSeq);
    $channelNumber = $channel->getChannelNumber();
    $channelName = $channel->getChannelName();
    $channelUnit = $channel->getChannelUnit();
    $folderSeq = $_GET["folderSeq"];
    $isPL = 0;
    if($_GET["isPL"] != null){
    	$isPL = $_GET["isPL"];
    }
    
    
    $toDate = new DateTime($_GET["fromDate"]); //get todate from post
    $toDateFormatted =  $toDate->format("m/d/Y H:i:s");//formatting date to add interval
    $interval = '24';
    $fromDate = date("m/d/Y  h:i:s A",strtotime($toDateFormatted . " - " . $interval . " hour"));//from date dateformat
    $fromDate = new DateTime($fromDate);
    $fromDate =  $fromDate->format("Y/m/d  H:i:s");
    $toDateStr =  $toDate->format("Y/m/d  H:i:s"); 
    $WQDS = WQDDataDataStore::getInstance();
    $arr = $WQDS->getChannel($fromDate,$toDateStr,$folderSeq,$channelNumber,"1hrs");
    $jsonData = $WQDS->getReadingJsonFromArrayWithPrescribedLimits($arr,$channelName,$isPL );
    
    $folder = FolderDataStore::getInstance()->FindBySeq($folderSeq);
    $locationSeq = $folder->getLocationSeq();
    if($_SESSION["userlogged"]["locSeq"] != $locationSeq){
        header("location: index.php?err=true&locSeq=". $locationSeq);
        die;
    }
    
    $location = LocationDataStore::getInstance()->FindBySeq($locationSeq);
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
        <title>Channel Values Chart</title>
        
        
        <!-- 1. Add these JavaScript inclusions in the head of your page -->
        <script type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
        <script type="text/javascript" src="js/highcharts.js"></script>
        
      
        <script type="text/javascript">
        
            var chart;
            $(document).ready(function() {
                chart = new Highcharts.Chart({
                    chart: {
                        renderTo: 'container',
                        defaultSeriesType: 'line',

                    },
                    title: {
                        text: '',
                        x: 0 //center
                    },
                   
                    xAxis: {
                        labels: {rotation:90,y:40}
                    },
                    yAxis: {
                        title: {
                            text: '<?echo $chName?>',
                            y:0
                        },
                        plotLines: [{
                            value: 0,
                            width: 1,
                            color: '#808080'
                        }]
                    },
                    tooltip: {
                        formatter: function() {
                                return '<b>'+ this.series.name +'</b><br/>'+
                                this.x +': '+ this.y ;
                        }
                    },
                    plotOptions: {
                        line: {
                            dataLabels: {
                                enabled: true
                            },
                            enableMouseTracking: true
                        }
                    },
                    xAxis: {
                        categories: <?echo $jsonData[0]?>  
                    },
                    legend: {
                        layout: 'vertical',
                        align: 'none',
                        verticalAlign: 'top',
                        x: -10,
                        y: 100,
                        borderWidth: 0
                    },
                    series: [{
                        name: 'Channel Values',
                        data: <?echo $jsonData[1]?>
                    }]
                });
                
                
            });
                
        </script>
        
    </head>
    <body>
     <?include("_includeHeader.php");?>   
        <!-- 3. Add the container -->
        <Div style="font-family:verdana;size:11px;font-weight:bold;margin-top:10px;color:#3E576F" align="center"><?echo $channelName?> Values For Last 24 hours (in <?echo htmlentities($channelUnit)?>)</Div>
        <div id="container" style="width: 100%; height: 80%; margin: 0 auto"></div>
        
                
    </body>
</html>