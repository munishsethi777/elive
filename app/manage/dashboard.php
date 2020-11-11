<?include("sessioncheck.php");
    $managerSession = $_SESSION["managerSession"];
    require_once('IConstants.inc');
    require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//HighValueRuleReminderDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//ChannelConfigurationDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] . "Utils//DropDownUtils.php");
    	
    $HVRRDS = HighValueRuleReminderDataStore::getInstance();
    $FDS = FolderDataStore::getInstance();
    $CCDS = ChannelConfigurationDataStore::getInstance();
    $staions = $FDS->getStationsForDashBoard();
    $highValueOccurences = $HVRRDS->getLastestHighValueReminderLogs();
    $FDS = FolderDataStore::getInstance();	$isAdmin = $managerSession["isAdmin"];
    $folders = null;
    if(!$isAdmin){		
    	//$folders = $FDS->getFolderByUserAllLcoations($managerSession["seq"],$managerSession["locSeq"]);	
    	//$channelConfigs = $CCDS->FindByFolder(current($folders)->getSeq());	
    }
    		
?>
<!DOCTYPE html>
<html>
    <head>
        <?include("_jsAdminInclude.php");?>
        <?include("../_InspiniaInclude.php");?>
    </head>
    <body>
    <div id="wrapper">   
        <? include("leftButtons.php");?>
            <div class="wrapper wrapper-content animated fadeInRight">
                <div class="row">
                     <div class="col-lg-14">
                           <div class="row">
                                <div class="col-lg-6">
                                    <div class="ibox float-e-margins">
                                        <div class="ibox-title">
                                            <h5>Recent High Value Occurences</h5>
                                            <div class="ibox-tools">
                                                <button class="btn btn-w-m btn-link btn-xs" onclick="showMore('showHighValueOccurences')"><i class="fa fa-arrow-right"></i> Show More</button>
                                                <a class="collapse-link">
                                                    <i class="fa fa-chevron-up"></i>
                                                </a>                                                
                                            </div>
                                        </div>
                                        <div class="ibox-content">
                                            <table class="table table-hover no-margins">
                                                <thead>
                                                <tr>
                                                    <th>Dated</th>
                                                    <th>Station</th>
                                                    <th>Pollutant</th>
                                                    <th>HighValue</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?	foreach($highValueOccurences as $highValue){
                                                       $data  = "<tr><td>" . $highValue->getReminderDate() . "</td>";
                                                       $data .= "<td>". $highValue->getStationName() ."</td>";
                                                       $data .= "<td>". $highValue->getChannelName() ."</td>";
                                                       $data .= "<td>". $highValue->getHighValue() . "</td></tr>";
                                                       echo($data);
                                                }?>
                                               
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="ibox float-e-margins">
                                        <div class="ibox-title">
                                            <h5>Stations Status</h5>
                                            <div class="ibox-tools">
                                                <button class="btn btn-w-m btn-link btn-xs" onclick="showMore('showFolders')"><i class="fa fa-arrow-right"></i> Show More</button>
                                                <a class="collapse-link">
                                                    <i class="fa fa-chevron-up"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="ibox-content" style="height: 245px; display: block; overflow-y: scroll;">
                                            <table class="table table-hover no-margins">
                                                <thead>
                                                <tr>
                                                    <th>Status</th>
                                                    <th>Station</th>
                                                    <th>DataSaved Till</th>
                                                    <th>Offline Since</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                     <?foreach($staions as $station){
                                                       $data  = "<tr><td>" . $station["isonline"] . "</td>";
                                                       $data .= "<td>". $station["foldername"] ."</td>";
                                                       $data .= "<td>".  date("d-m-Y H:i",strtotime($station["lastsynchedon"])) ."</td>";
                                                       $data .= "<td>". $station["offlinesince"] . "</td></tr>";
                                                       echo($data);
                                                     }?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                           </div>
                           <div class="row" style="margin-bottom:10px">
                                <div class="col-lg-6">
                                    <div class="" id="graphLblDiv">
                                            
                                    </div>
                                 </div>
								 <div class="col-lg-6">
                                    <div class="" id="graphLblDiv">
                                        <? 
                                        if(!$isAdmin){
	                                        $folDDown = null;
	                                        $folDDown = DropDownUtils::getStationsDropDown($folders,"folder","loadGraph(this.value)",0);
	                                        
	                                        echo $folDDown; 
										}	
										?>    
                                    </div>
                                 </div>
                                
                           </div>
                          
                                <div id="graphDiv"></div>
                            
                           
                     </div>
                </div>    
            </div>
    </div>
    </body>
</html>
<script>
    $(document).ready(function() {
        var stationSeq = $("#folder").val();
        loadGraph(stationSeq);
    });
    function showMore(lName){
        location.href = lName + ".php";
    }
    function loadGraph(stationSeq){
        var station = $("#folder")[0].selectedOptions[0].label;
        
        $("#graphDiv").html("");
        var mainHtml = "";
        $.get("../ajax_cpcb.php?method=requestDashboardStationReport&folderSeq=" + stationSeq,function(data){
                var jsonData = $.parseJSON(data);
                arr = jsonData.chData
                dated = jsonData.dated;
                isOnline = jsonData.status;
                var status =  "<span class='label label-danger'>Offline</span>";
                if(isOnline == "1"){
                    status = "<span class='label label-success'>Online</span>";
                }
                $("#graphLblDiv").html("<h4>" + station + " on "+ dated +  " " + status + " </h4>");
                i = 2 ;
                var graphDivs = [];
                var lineDataArr = [];
                var labelOptionsArr = [];
                $.each( arr, function( index, value ){
                    labels_ = Object.keys(value);
                    data_ = [];
                    $.each( value, function( key, val ){ 
                        if(val == null){
                            val = "0.0";
                        }
                        data_.push(val);
                    });
                    var lineData = {
                        labels: labels_,//["January", "February", "March", "April", "May", "June", "July","January", "February", "March", "April", "May", "June", "July","January", "February", "March", "April", "May", "June", "July","May", "June", "July"],
                        datasets: [
                            {
                                label: "Example dataset",
                                fillColor: "rgba(26,180,148,0.5)",
                                strokeColor: "rgba(26,180,148,0.7)",
                                pointColor: "rgba(26,180,148,1)",
                                pointStrokeColor: "#fff",
                                pointHighlightFill: "#fff",
                                pointHighlightStroke: "rgba(26,179,148,1)",
                                data: data_
                            }
                        ]
                    };
                    
                    var lineOptions = {
                        scaleShowGridLines: true,
                        scaleGridLineColor: "rgba(0,0,0,.05)",
                        scaleGridLineWidth: 1,
                        bezierCurve: true,
                        scaleBeginAtZero:true,
                        bezierCurveTension: 0.4,
                        pointDot: true,
                        pointDotRadius: 4,
                        pointDotStrokeWidth: 1,
                        pointHitDetectionRadius: 20,
                        datasetStroke: true,
                        datasetStrokeWidth: 2,
                        datasetFill: true,
                        responsive: true,
                    };
                        if(i%2 == 0){
                             mainHtml += '<div class="row">';    
                        }
                        
                        var html = '<div class="col-lg-6"><div class="ibox float-e-margins">';
                        html += '<div class="ibox-content">';
                        html += '<div>'
                        html += '<h4 class="font-bold no-margins">';
                        html +=  index;
                        html += '</h4>'
                        html += '</div>';
                        html += '<div><canvas id="lineChart' + index + '"></canvas></div>'
                        html += '</div>';
                        html += '</div></div>'
                        mainHtml += html;
                        if(i%2 == 0){ 
                        }else{
                             mainHtml += '</div>'; 
                        }
                        graphDivs.push("lineChart" + index);
                        lineDataArr["lineChart" + index] = lineData;
                        labelOptionsArr["lineChart" + index] = lineOptions;
                        
                    i++;
                })
                 $("#graphDiv").html(mainHtml);
                 $.each( graphDivs, function( index, value ){
                    var ctx = document.getElementById(value).getContext("2d");
                    lData = lineDataArr[value];
                    lOptions = labelOptionsArr[value];
                    var myNewChart = new Chart(ctx).Line(lData, lOptions);
                 });
            });
    }
</script>