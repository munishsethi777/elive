<?php 
   require_once('IConstants.inc');
    require_once($ConstantsArray['dbServerUrl'] ."/Utils/FileSystemUtils.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDFileDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/LocationDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php");
    require_once($ConstantsArray['dbServerUrl']. "Utils/ExportUtils.php");
    
    
    $folderSeq = 13;
    $fromDateStr = '01/01/2013 00:00';
    $toDateStr = '01/31/2013 23:59';
    $intervalSelected = '1hrs';
    
    
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

//$graph = new WindroseGraph(600,600);
//$graph->title->Set('Windrose graph '. 'for the period between '. $fromDateStr .' and '. $toDateStr);
//$wp = new WindrosePlot($plot_data);

//$graph->Add($wp);
//$graph->Stroke();
?>
<html>
<head>
<script src="http://localhost:8080/admin/js/jquery-1.9.0.js"></script>
<script src="http://code.highcharts.com/highcharts.js"></script>
<script src="http://code.highcharts.com/highcharts-more.js"></script>
<script src="http://code.highcharts.com/modules/data.js"></script>
<script src="http://code.highcharts.com/modules/exporting.js"></script>
<script>
$(function () {
    
    // Parse the data from an inline table using the Highcharts Data plugin
    Highcharts.data({
        table: 'freq',
        startRow: 1,
        endRow: 17,
        endColumn: 7,
        
        complete: function (options) {
            
            // Some further processing of the options
            options.series.reverse(); // to get the stacking right
                
            
            // Create the chart
            window.chart = new Highcharts.Chart(Highcharts.merge(options, {
                
                chart: {
                    renderTo: 'container',
                    polar: true,
                    type: 'column'
                },
                
                title: {
                    text: 'Wind rose Air Quality Data, Raigarh'
                },
                
                subtitle: {
                    text: 'Source: envirotechlive.com'
                },
                
                pane: {
                    size: '85%'
                },
                
                legend: {
                    reversed: true,
                    align: 'right',
                    verticalAlign: 'top',
                    y: 100,
                    layout: 'vertical'
                },
                
                xAxis: {
                    tickmarkPlacement: 'on'
                },
                    
                yAxis: {
                    min: 0,
                    endOnTick: false,
                    showLastLabel: true,
                    title: {
                        text: 'Frequency (%)'
                    },
                    labels: {
                        formatter: function () {
                            return this.value + '%';
                        }
                    }
                },
                
                tooltip: {
                    valueSuffix: '%'
                },
                    
                plotOptions: {
                    series: {
                        stacking: 'normal',
                        shadow: false,
                        groupPadding: 0,
                        pointPlacement: 'on'
                    }
                }
            }));
            
        }
    });
});
</script>
</head>
<body>
<div id="container" style="width: 600px; height: 400px; margin: 0 auto;float:left"></div>
<div style="display:block">
    <table id="freq" border="1" cellspacing="2" cellpadding="2" style="font-family:arial;font-size:10px;">
        <tr nowrap bgcolor="#CCCCFF">
            <th colspan="9" class="hdr">Table of Frequencies (percent)</th>
        </tr>
        <tr nowrap bgcolor="#CCCCFF">
            <th class="freq">Direction</th>
            <th class="freq">&lt; 1 m/s</th>
            <th class="freq">1-5 m/s</th>
            <th class="freq">5-10 m/s</th>
            <th class="freq">10-15 m/s</th>
            <th class="freq">15-20 m/s</th>
            <th class="freq">20-* m/s</th>
            <th class="freq">Total</th>
        </tr>
        <?
            foreach($plot_data as $plotDirection=>$plotArr){
                echo '<tr nowrap>';
                echo '<td class="dir">'.$plotDirection.'</td>';
                foreach($plotArr as $val){
                    echo '<td class="data">'.round($val, 2).'</td>';
                }
                echo '</tr>';
            }
        ?>
        <!--<tr nowrap>
            <td class="dir">N</td>
            <td class="data">1.81</td>
            <td class="data">1.78</td>
            <td class="data">0.16</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">3.75</td>
        </tr>        
        <tr nowrap bgcolor="#DDDDDD">
            <td class="dir">NNE</td>
            <td class="data">0.62</td>
            <td class="data">1.09</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">1.71</td>
        </tr>
        <tr nowrap>
            <td class="dir">NE</td>
            <td class="data">0.82</td>
            <td class="data">0.82</td>
            <td class="data">0.07</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">1.71</td>
        </tr>
        <tr nowrap bgcolor="#DDDDDD">
            <td class="dir">ENE</td>
            <td class="data">0.59</td>
            <td class="data">1.22</td>
            <td class="data">0.07</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">1.88</td>
        </tr>
        <tr nowrap>
            <td class="dir">E</td>
            <td class="data">0.62</td>
            <td class="data">2.20</td>
            <td class="data">0.49</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">3.32</td>
        </tr>
        <tr nowrap bgcolor="#DDDDDD">
            <td class="dir">ESE</td>
            <td class="data">1.22</td>
            <td class="data">2.01</td>
            <td class="data">1.55</td>
            <td class="data">0.30</td>
            <td class="data">0.13</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">5.20</td>
        </tr>
        <tr nowrap>
            <td class="dir">SE</td>
            <td class="data">1.61</td>
            <td class="data">3.06</td>
            <td class="data">2.37</td>
            <td class="data">2.14</td>
            <td class="data">1.74</td>
            <td class="data">0.39</td>
            <td class="data">0.13</td>
            <td class="data">11.45</td>
        </tr>
        <tr nowrap bgcolor="#DDDDDD">
            <td class="dir">SSE</td>
            <td class="data">2.04</td>
            <td class="data">3.42</td>
            <td class="data">1.97</td>
            <td class="data">0.86</td>
            <td class="data">0.53</td>
            <td class="data">0.49</td>
            <td class="data">0.00</td>
            <td class="data">9.31</td>
        </tr>
        <tr nowrap>
            <td class="dir">S</td>
            <td class="data">2.66</td>
            <td class="data">4.74</td>
            <td class="data">0.43</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">7.83</td>
        </tr>
        <tr nowrap bgcolor="#DDDDDD">
            <td class="dir">SSW</td>
            <td class="data">2.96</td>
            <td class="data">4.14</td>
            <td class="data">0.26</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">7.37</td>
        </tr>
        <tr nowrap>
            <td class="dir">SW</td>
            <td class="data">2.53</td>
            <td class="data">4.01</td>
            <td class="data">1.22</td>
            <td class="data">0.49</td>
            <td class="data">0.13</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">8.39</td>
        </tr>
        <tr nowrap bgcolor="#DDDDDD">
            <td class="dir">WSW</td>
            <td class="data">1.97</td>
            <td class="data">2.66</td>
            <td class="data">1.97</td>
            <td class="data">0.79</td>
            <td class="data">0.30</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">7.70</td>
        </tr>
        <tr nowrap>
            <td class="dir">W</td>
            <td class="data">1.64</td>
            <td class="data">1.71</td>
            <td class="data">0.92</td>
            <td class="data">1.45</td>
            <td class="data">0.26</td>
            <td class="data">0.10</td>
            <td class="data">0.00</td>
            <td class="data">6.09</td>
        </tr>
        <tr nowrap bgcolor="#DDDDDD">
            <td class="dir">WNW</td>
            <td class="data">1.32</td>
            <td class="data">2.40</td>
            <td class="data">0.99</td>
            <td class="data">1.61</td>
            <td class="data">0.33</td>
            <td class="data">0.00</td>
            <td class="data">0.00</td>
            <td class="data">6.64</td>
        </tr>
        <tr nowrap>
            <td class="dir">NW</td>
            <td class="data">1.58</td>
            <td class="data">4.28</td>
            <td class="data">1.28</td>
            <td class="data">0.76</td>
            <td class="data">0.66</td>
            <td class="data">0.69</td>
            <td class="data">0.03</td>
            <td class="data">9.28</td>
        </tr>        
        <tr nowrap bgcolor="#DDDDDD">
            <td class="dir">NNW</td>
            <td class="data">1.51</td>
            <td class="data">5.00</td>
            <td class="data">1.32</td>
            <td class="data">0.13</td>
            <td class="data">0.23</td>
            <td class="data">0.13</td>
            <td class="data">0.07</td>
            <td class="data">8.39</td>
        </tr>
        <tr nowrap>
            <td class="totals">Total</td>
            <td class="totals">25.53</td>
            <td class="totals">44.54</td>
            <td class="totals">15.07</td>
            <td class="totals">8.52</td>
            <td class="totals">4.31</td>
            <td class="totals">1.81</td>
            <td class="totals">0.23</td>
            <td class="totals">&nbsp;</td>
        </tr>-->
    </table>
</div>



</body></html>