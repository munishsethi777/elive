
<?
    ini_set( "display_errors", 0);
    require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/WQDDataDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/ChannelConfigurationDataStore.php");
    $folSeq = $_GET["folSeq"];
    $fromDate = new DateTime($_GET["fromDate"]);
    $toDate = new DateTime($_GET["toDate"]);
    $chNumber = $_GET["channelNumber"];
    $CCDS = ChannelConfigurationDataStore::getInstance();
    $chName = $CCDS->FindChNameByFolderAndChannelNo($folSeq,$chNumber);
    
    $fromDate =  $fromDate->format("Y/m/d  H:i:s");
    $toDate =  $toDate->format("Y/m/d  H:i:s");
    $WQDS = WQDDataDataStore::getInstance();
    $arr = $WQDS->getChannelZeroCheckValues($fromDate,$toDate,$folSeq,$chNumber);
    $jsonData = null;
    
    //manipulate data to show only last observation for CO ,Nox, So2, Ozone
    if($chName == "CO" || $chName == "NOx" || $chName == "NO2" || $chName == "SO" || $chName == "SO2" || $chName == "Ozone"){
        $dateCounter = 0;
        $currDate = null;
        $lastDate = null;
        $newArr = array();
        foreach($arr as $val){
            if($lastDate == null){
                $lastDate = substr($val[0],0,10);
                $dateCounter++;
                continue;
            }
            $currDate = substr($val[0],0,10);
            if($currDate == $lastDate){
                $dateCounter++;
            }else{
                $dateCounter = 1;
            }
           
            if($dateCounter == 2){
                array_push($newArr,$val);   
            }
            if($dateCounter >2){
                unset($newArr[$val]); 
            }
            $lastDate = substr($val[0],0,10); 
        }
        $jsonData = $WQDS->getReadingJsonFromArray($newArr);
    }else{
        $jsonData = $WQDS->getReadingJsonFromArray($arr);
    }
?>

<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Zero Values Chart for the period between <?echo $fromDate?> and <?echo $toDate?></title>
        
        
        <!-- 1. Add these JavaScript inclusions in the head of your page -->
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
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
                        text: 'Zero Values Chart for the period between <?echo $fromDate?> and <?echo $toDate?>',
                        x: 0 //center
                    },
                   
                   xAxis: {
                       labels: {rotation:90,y:40},
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
                    legend:{
                        enabled :0,
                        style:{display:'none'}  
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
        
        <!-- 3. Add the container -->
        <div id="container" style="width: 100%; height: 80%; margin: 0 auto"></div>
        
                
    </body>
</html>

