<?
    session_start();
    require_once('IConstants.inc');
    require_once($ConstantsArray['dbServerUrl'] ."/Utils/FileSystemUtils.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDFileDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/LocationDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php");
    require_once($ConstantsArray['dbServerUrl']. "/Utils/ExportUtils.php");
    require_once($ConstantsArray['dbServerUrl'] ."/Utils/ConvertorUtils.php");
    require_once($ConstantsArray['dbServerUrl'] ."/Utils/StringUtils.php");  
    
    if(!empty($_GET['folSeq'])){
        $folderSeq = $_GET['folSeq'];   
    }
    if(empty($folderSeq) && !empty($_POST['submit'])){
        $folderSeq = $_POST['folSeq'];
    }
    if(empty($folderSeq)){
        die;   
    }
    $folder = FolderDataStore::getInstance()->FindBySeq($folderSeq);
    $CCDS = ChannelConfigurationDataStore::getInstance();
    $ChannelsInfo = $CCDS->FindByFolder($folderSeq); 
    $locationSeq = $folder->getLocationSeq();
    
    $allFolders = null;
    if($locationSeq != null){
        $allFolders = FolderDataStore::getInstance()->FindByLocation($locationSeq);
    }
    if($_SESSION["userlogged"]["locSeq"] != $locationSeq){
        header("location: index.php?err=true&locSeq=". $locationSeq);
        die;
    }
    
    
    $jsonDatesPlots = 'null';
    $jsonDataOtherFols = null;
    $chNumber = null;
    
    $channelSelected = null;
    $fromDateSelected = null;
    $toDateSelected = null;
    $graphTypeSelected = null;
    $msgDisplay = null;
    $fromDate = null;
    $toDate = null;
    $foldersSelected = null;
    $dailyAverageArray = null;
    $channelObject = null;
    $channelName = null;
    $isPrescribedLimits = 0;
    $isPrescribedLimitsChecked ="";
    $channelUnit = "null";
    $channelSeq = 0;
    if(!empty($_POST['submit'])){
        $channelSelected = $_POST["channel"];
        $fromDateSelected = $_POST["fromDate"];
        $toDateSelected = $_POST["toDate"];
        $graphTypeSelected = $_POST["graphType"];
        $intervalSelected = $_POST["interval"];
        $foldersSelected = $_POST["OtherFol"];
        if($_POST["isPrescribedLimits"] == "on"){
            $isPrescribedLimits = 1;
            $isPrescribedLimitsChecked = "checked";
        }
        
        $chNumber =$_POST["channel"];
        foreach($ChannelsInfo as $channelConfig){
            if($channelConfig->getChannelNumber() == $channelSelected){
                $channelName = $channelConfig->getChannelName();
                $channelUnit = $channelConfig->getChannelUnit();
                $channelSeq  = $channelConfig->getSeq();             
                break;
            }   
        }
        $fromDate = new DateTime($_POST["fromDate"]);
        $toDate = new DateTime($_POST["toDate"]);
        $toDate->setTime(23,59,59);
        if($fromDate >= $toDate){
            $msgDisplay = "From Date cant be bigger than To Date";
        }
        
        if(empty($msgDisplay)){
             $WQDS = WQDDataDataStore::getInstance();
            
            $fromDate =  $fromDate->format("Y/m/d  H:i:s");
            $toDate =  $toDate->format("Y/m/d  H:i:s");
            $arr = $WQDS->getChannel($fromDate,$toDate,$folderSeq,$chNumber,$intervalSelected);
            $jsonDatesPlots = $WQDS->getDatesJson($arr);
            
            if($foldersSelected != null){
                foreach($foldersSelected as $folderSeqSelected){//LOOP OVER ALL FOLDERS SELECTED
                    $arr = $WQDS->getChannel($fromDate,$toDate,$folderSeqSelected,$chNumber,$intervalSelected);
                    //$jsonDataOtherFols[$folderSeqSelected] = $WQDS->getReadingJsonFromArray($arr);
                    $jsonDataOtherFols[$folderSeqSelected] = 
                            $WQDS->getReadingJsonFromArrayWithPrescribedLimits($arr,$channelName,$isPrescribedLimits);
                    foreach($allFolders as $folder){//LOOPING OVER LIST OF FOLDERS TO SET FOLDER NAME HERE
                        if($folder->getSeq() == $folderSeqSelected){
                            $jsonDataOtherFols[$folderSeqSelected][2] = $folder->getFolderName();
                        }   
                    }
                    $averageDailyArr = $WQDS->getDailyAverageValues($fromDate,$toDate,$folderSeqSelected,$chNumber);
                    $dailyAverageArray[$jsonDataOtherFols[$folderSeqSelected][2]] = $averageDailyArr;
                }
            }
     
        }
    }  
    $location = LocationDataStore::getInstance()->FindBySeq($locationSeq);
    if($location->getIsPrivate()==1){
        session_start();
        if(isset($_SESSION["userlogged"])){
               
        }else{  
            $_SESSION['httpUrl'] = $_SERVER['REQUEST_URI'];
            header("location: index.php?err=true&locSeq=". $locationSeq);
        }
    }
    if($isPrescribedLimits == 1){
        if(ConvertorUtils::getPrescribedUnit($channelName) != null){
            $channelUnit = ConvertorUtils::getPrescribedUnit($channelName);
        }
    }
     
?>

<!DOCTYPE HTML>
<html>
    <head>
        <?include("_jsInclude.php");?>
        <style>
            select,input{
                font-size:12px;   
            }
        </style>
        <script type="text/javascript" src="js/highcharts.js"></script>
        <script type="text/javascript">

            var chart;
            $(document).ready(function(){
                $('#graphType').val('<?echo $graphTypeSelected;?>');
                $('#interval').val('<?echo $intervalSelected;?>');
                $('#fromDate').datepicker({maxDate: new Date()});
                $('#toDate').datepicker({maxDate: new Date()});
                $('.submit').button();
                $('.btn').button();
                <?
                    if($arr == null || count($arr) == 0){
                        $msgDisplay = "No Data found";
                ?>
                       $("#containerOuter").css('display','none'); 
                        
                <?}elseif($arr != null && count($arr)<=100){?>
                       $('#container').css('width','100%');
                       renderChart();
                <?}else{?>
                        
                       $('#container').css('width','<?echo (count($arr)*18)?>px');
                       renderChart();
                <?}?>
                
                
            });
        function showPrescribedLimits(chSeq){
            var fromDate = $("#fromDate")[0].value
            var toDate = $("#toDate")[0].value
            var channelNumber = $("#channel")[0].value;
            var folSeq = $("#folSeqs")[0].value;
            window.open("showPrescribedLimits.php?fromDate=" + fromDate + "&toDate=" + toDate + "&chSeq="+chSeq+ "&folSeq=" + folSeq +"$isPU="+ <?echo $isPrescribedLimits?> ,'_blank');        
        }
        
        function showSpanValues(){
            var fromDate = $("#fromDate")[0].value
            var toDate = $("#toDate")[0].value
            var channelNumber = $("#channel")[0].value;
            var folSeq = $("#folSeq")[0].value;
            window.open("showSpanValues.php?fromDate=" + fromDate + "&toDate=" + toDate + "&channelNumber=" + channelNumber + "&folSeq=" + folSeq,'_blank');        
        }
        function showZeroCheckValues(){
            var fromDate = $("#fromDate")[0].value
            var toDate = $("#toDate")[0].value
            var channelNumber = $("#channel")[0].value;
            var folSeq = $("#folSeq")[0].value;
            window.open("showZeroCheckValues.php?fromDate=" + fromDate + "&toDate=" + toDate + "&channelNumber=" + channelNumber + "&folSeq=" + folSeq,'_blank');        
        }        
            
        chart = null
        function fitToScreen(){
            $('#container').css('width','100%');
            renderChart();
        }
        function renderChart(){
            chart = new Highcharts.Chart({
                chart: {
                    renderTo: 'container',
                    defaultSeriesType: '<?echo $graphTypeSelected?>',

                },
                title: {
                    text: '<?echo $chName?> Values For Last 24 hours',
                    style:{display:'none'}
                },
               
                xAxis: {
                   labels: {rotation:90,y:40},
                   categories: <?echo $jsonDatesPlots?>
                },
                yAxis: {
                    title: {
                        text: $("#channel :selected").text() + ' in  <?echo $channelUnit?>' ,
                        style:{display:'block'}
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
                            this.x +': '+ this.y + '<?echo $channelUnit?>';
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
                legend:{
                    enabled :1,
                    style:{}  
                },
                series: [
                    <? 
                        $counter = 0;
						if($jsonDataOtherFols != null){
                            foreach($jsonDataOtherFols as $jsonDataOtherFol){
							    $counter++;
								if($counter >1){
									echo(",");
								}
                                echo("{name: '". $jsonDataOtherFol[2] ."',data:". $jsonDataOtherFol[1] ."}");
                            }
                        }
                    ?>
                ]
            });
            
        }
         
        function windRose(){
            var fromDate = $("#fromDate")[0].value
            var toDate = $("#toDate")[0].value
            var folSeq = $("#folSeq")[0].value;
            var interval = $("#interval")[0].value;
            window.open("showWindRose.php?fromDate=" + fromDate + "&toDate=" + toDate + "&folSeq=" + folSeq +"&interval="+ interval,'_blank');        
        }
        </script>
        
    </head>
    <body>
    <?include("_includeHeader.php");
    if(isset($_SESSION["userlogged"])){
             include("logOutButton.php");
      }
    ?>
<?
     include("_includeExportData.php");
 ?>
<div class="ui-widget-header" style="height:30px;padding:10px;">
    <!--<Div style="float:left;margin-top:7px;font-size:14px">Advance Search for <?//echo $folder->getFolderName()?></Div>-->
 
<div>
        
        <form method="post" action="showAdvanceChart.php" name="chartForm" id="chartForm">
            <input type="hidden" id="folSeq" name="folSeq" value="<?echo $folderSeq?>"/>
            
            <?
                $foldersSelectedStr = "";
                if($foldersSelected!=null){
                   foreach($foldersSelected as $fol){
                        $foldersSelectedStr .= $fol."," ;  
                   }
                }
            ?>
            <input type="hidden" id="folSeqs" name="folSeqs" value="<?echo $foldersSelectedStr?>"/>
             
             <?
                if($allFolders != null){
                    foreach($allFolders as $folder1){
                        if($foldersSelected!= null && in_array($folder1->getSeq(),$foldersSelected)){
                        echo ("<input style='margin-left:12px;' type='checkbox' checked name='OtherFol[]'  value='".$folder1->getSeq()."' />" .strtoupper ($folder1->getFolderName()));                    
                        }else{
                            echo ("<input style='margin-left:12px;' type='checkbox' name='OtherFol[]'  value='".$folder1->getSeq()."' />" .strtoupper ($folder1->getFolderName()));                    
                        }
                    }
                }
            ?>
            
            
            
        <div style="float:right">
            Channel:<select name="channel" id="channel">
            <?
            $selected = "";
            foreach($ChannelsInfo as $channelConfig){
                if($channelConfig->getChannelNumber() == $channelSelected){
                    $selected = "selected";
                }
                echo ("<option value='". $channelConfig->getChannelNumber() ."' id='". $channelConfig->getChannelName() ."' name='". $channelConfig->getChannelName() ."'" . $selected .">". $channelConfig->getChannelName()."</option>");
                $selected = "";
            }?>
            </select>
            From: <input type="text" size="8" name="fromDate" id="fromDate" value="<?echo $fromDateSelected?>">
            To: <input type="text" size="8" name="toDate" id="toDate" value="<?echo $toDateSelected?>">
            Graph: <select name="graphType" id="graphType">
                         <option value="line">Line</option>
                         <option value="column">Column</option>
                         <option value="spline">SP Line</option>
                         <option value="area">Area</option>
                    </select>
            Interval: <select name="interval" id="interval">
                        <option value="5min">5 mins</option>
                         <option value="10min">10 mins</option>
                         <option value="15min">15 mins</option>
                         <option value="30min">30 mins</option>
                         <option value="1hrs">1 hour</option>
                    </select>
            <input type="checkbox" name="isPrescribedLimits" class="isPrescribedLimits" <?echo $isPrescribedLimitsChecked?>/>Prescribed Units
            <input type="submit" class="submit" name="submit"/>
            </div>
        </form>
        
    </div>
</div>
        <label style="color:red"><?echo $msgDisplay;?></label>
               
<div id="containerOuter" style="width: 100%; height:600px; margin: 0 0 10px 0px;overflow-x:scroll;overflow-y:hidden">
    <div id="container" style="width:100%;height:95%" ></div>
</div>
<div style="float:left">        
<?
    if($dailyAverageArray!= null){
          $arrKeys =  array_keys($dailyAverageArray);//looping over foldernames
          
          foreach($arrKeys as $key){
            echo "<div style='float:left;margin-left:15px;border:solid thin grey;padding:12px;background:white'>";
            echo "<b>Daily Averages for ". $key ."</b>";
            echo "<ul>";
            $avrArray =  $dailyAverageArray[$key];
            $totalAverages = 0;
            foreach($avrArray as $avr){
                $val = $avr[1];
                if($isPrescribedLimits == 1){
                if(ConvertorUtils::getPrescribedUnit($channelName) != null){
                        $val = ConvertorUtils::getPrescribedValue($channelName,$val);
                    }
                }
                echo "<li>". $avr[0] ." - ". round($val,2) ." ". $channelUnit ."</li>";
                $totalAverages = $totalAverages + round($val,2);
            }
            echo "</ul>";
            echo "<font style='font-size:15px'>";
                echo "Total Average = ";
                if($avrArray != null){
                    echo round($totalAverages/count($avrArray),2) ." ".$channelUnit;
                }else{
                    echo "--";
                }
		
            echo "</font>";
            echo"</div>";
          }//end of loop over various folders
    }//end of condition?> 
</div>         
<div class="buttonsBottom" style="float:right">
   <a class="btn" id="fitToScreen" onClick="javascript:fitToScreen();">Fit to Screen</a>
   <a class="btn" id="prescribedLimits" onClick="javascript:showPrescribedLimits(<?echo $channelSeq;?>);">Prescribed Limits</a>
   <a class="btn" id="showSpanValues" onClick="javascript:showSpanValues();">Show Span Values</a>
   <a class="btn" id="showZeroCheck" onClick="javascript:showZeroCheckValues();">Zero Check Values</a>
   <a class="btn" id="exportData" onClick="javascript:exportData();">ExportData</a>
   <a class="btn" id="windrose" onClick="javascript:windRose();">WindRose</a>
</div>     
    </body>
</html>