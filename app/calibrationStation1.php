 <?include("sessioncheck.php");
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//ChannelConfigurationDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "Utils/PrescribedLimitsUtils.php");
  require_once($ConstantsArray['dbServerUrl'] . "Utils/CalibrationService.php");
 
  $folderSeq = $_GET["folderSeq"];
  $userSeq = $_GET["userSeq"];
  $stationCode = $_GET["siteCode"];
  $folder = new Folder();
  $lastDate = "";  
  //$disabled = "disabled";
  $disabled = "";
  if(!empty($folderSeq)){
	  $folderDataStore = FolderDataStore::getInstance();
	  $folder = $folderDataStore->FindBySeq($folderSeq);
	  $lastDate = $folder->getLastSynchedOn();
	  $lastDate = DateUtils::StringToDateByGivenFormat("Y-m-d H:i:s", $lastDate);
	  $lastDate = $lastDate->format("d-m-Y H:i:s");
	  $chConfigDataStore = ChannelConfigurationDataStore::getInstance();
	  $channels = $chConfigDataStore->FindByFolder($folderSeq);
	  if($_GET["call"] == "getCalibrationData"){
	  	if($folderSeq == 38){
	  		$folder->setM2MCode("NPL_A1_PB");
	  	}else if($folderSeq == 287){
	  		$folder->setM2MCode("NPL_A2_PB");
	  	}
	  	$calibrationData = CalibrationService::getCallibrationData($folder->getM2MCode(),$channels);
	  	echo $calibrationData;
	  	return;
	  }
	  if(!empty($folder->getLoggerId())){
	  	 $disabled = "";
	  }
  }
  
  if($_GET["call"] == "markCalibration"){
  	$loggerId = $_GET["loggerId"];
  	$calType = $_GET["calType"];
  	$isStart = $_GET["isStart"];
  	$folderSeq = $_GET["folderSeq"];
  	$userSeq = $_GET["userSeq"];
  	if(empty($isStart)){
  		$isStart = false;
  	}else{
  		$isStart = true;
  	}
  	$response = CalibrationService::makeCalibrationCall($loggerId,$calType,$isStart,$folderSeq,$userSeq);
  	echo $response;
  	return;
  }
  ?>
<!DOCTYPE html>
<html>
    <head>
    <? include("jsInclude.php");?>
    <? include("_jsIncludeCpcb.php");?>
    <?include("_InspiniaInclude.php");?>
    <?include("_jqxGridInclude.php");?>  
    </head>
    <body>
      <div id="wrapper">       
            <div class="wrapper wrapper-content animated fadeInRight mainDiv">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h2><?php echo $folder->getFolderName()?> <small> - Remote Calibration.</small></h2>
                                <div>Last Data Syched On - <span id="lastDataSync"></span></div>
                            </div>  
                            <div class="ibox-content">
                                 <form name="frm1" id="calfrm1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-horizontal">
	                                 <input type="hidden" name="call" id="call" value="markCalibration" >
	                                 <input type="hidden" name="loggerId" id="loggerId" value="<?php echo $folder->getLoggerId()?>" >
	                                 <input type="hidden" name="stationCode" id="stationCode" value="<?php echo $stationCode?>" >
	                                 <input type="hidden" name="folderSeq" id="folderSeq" value="<?php echo $folderSeq?>" >
	                                  <input type="hidden" name="userSeq" id="userSeq" value="<?php echo $userSeq?>" >
	                                 <input type="hidden" name="calCmdValue" id="calCmdValue" >
	                                 <input type="hidden" name="lastRuleHitFileDataSeq" id="seq" value="">
	                                 <a href="javascript:submitForm('zero',1)"><button <?php echo $disabled ?> class="btn btn-primary" type="button">Zero</button></a>
	                                 <a href="javascript:submitForm('span',1)"><button id="span" <?php echo $disabled ?> class="btn btn-primary" type="button">Span Start</button></a>
	                                 <a href="javascript:submitForm('span',0)"><button <?php echo $disabled ?> class="btn btn-primary" type="button">Span Stop</button></a>
	                                 <a href="javascript:submitForm('purge',1)"><button id="purge" <?php echo $disabled ?> class="btn btn-primary" type="button">Purge Start</button></a>
	                                 <a href="javascript:submitForm('purge',0)"><button <?php echo $disabled ?> class="btn btn-primary" type="button">Purge Stop</button></a>
                                   	 <div class="ibox-content">
			                            <div class="table-responsive">
			                                <table class="table table-striped">
			                                    <thead>
			                                    <tr>
			                                        <th>Component </th>
			                                        <th>Unit </th>
			                                        <th>Concentration Value</th>
			                                        <th>Status</th>
			                                        <th>Calibration Gas</th>
			                                        <th>Analyzer Range</th>
			                                        <th>Analyzer Status</th>
			                                        <th>Calibration Status</th>
			                                    </tr>
			                                    </thead>
			                                    <tbody>
			                                    <?php foreach ($channels as $ch){
			                                    	$chName = $ch->getChannelName();
			                                    	if(strtolower($chName) != "sox" && strtolower($chName) != "nox"){
			                                    		continue;
			                                    	}
			                                    	$presLimits = PrescribedLimitsUtils::getPrescribedLimit($ch->getChannelName(),$folder->getStationType());
			                                    	$highValue = $presLimits["max"];
			                                    	
			                                    	$chNumber = $ch->getChannelNumber();
			                                    	if(!empty($ch->getChannelStation())){
			                                    		$chName = $ch->getChannelStation() . "." . $ch->getChannelName(); 
			                                    	}
			                                   	?>
				                                    <tr>
				                                        <td><?php echo $chName ?></td>
				                                        <td><span class="calData" id="<?php echo $chNumber . "unit"?>"><i class="fa fa-spinner"></i></span></td>
				                                        <td><span class="calData" id="<?php echo $chNumber . "conValue"?>"><i class="fa fa-spinner"></i></span></td>
				                                        <td><span class="calData" id="<?php echo $chNumber . "conStatus"?>"><i class="fa fa-spinner"></i></span></td>
				                                        <td><span class="calData" id="<?php echo $chNumber . "calGas"?>"><i class="fa fa-spinner"></i></span></td>
				                                        <td><span class="calData" id="<?php echo $chNumber . "analyzerRange"?>"><i class="fa fa-spinner"></i></span></td>
				                                        <td><span class="calData" id="<?php echo $chNumber . "analyzerStatus"?>"><i class="fa fa-spinner"></i></span></td>
				                                        <td><span id="<?php echo $chNumber . "calibrationStatus"?>"><i class="fa fa-spinner"></i></span></td>
				                                    </tr>
			                                   <?php }?>
			                                    </tbody>
			                                </table>
			                            </div>
										<div id="parameters"></div>
										<div id="values"></div>
	                        		</div>
                                 </form>
                                 <div class="ibox-title">
                               		<h3>Calibration History</h3>
                            	</div> 
                            	<div class="ibox-content">
                            		<div id="jqxgrid"></div>
                            	</div>
                            	<div class="ibox-content">
	                            	<div class="tabs-container" id="detailTabs" style="display:none">
				                        <ul class="nav nav-tabs">
				                            <li class="active"><a data-toggle="tab" href="#tab-1"> Table</a></li>
	                            			<li class=""><a data-toggle="tab" href="#tab-2">Graph</a></li>
				                        </ul>
				                        <div class="tab-content">
				                        	<div class="ibox-title" id="historyTitle">
                               					<h3>Calibration History</h3>
                            				</div>
				                            <div id="tab-1" class="tab-pane active">
				                                <div class="panel-body">
				                                    <div id="tableData" class="table-responsive">
						                                
			                            			</div>
				                                </div>
				                            </div>
				                            <div id="tab-2" class="tab-pane">
				                            	<div class="panel-body">
				                                    <div class="mainGraphDiv">
													    <div id="container" style="width:1100px;margin:auto;margin-top:10px;"></div>
													    
													</div>
											    </div>
				                            </div>
				                        </div>
									</div>
								</div>
                            </div>
                        </div>
                    </div>
                </div>
             </Div>
        </Div>
    </body>
</html>
<script src="js/showCalibrations.js"></script>
<script type="text/javascript">
     $(document).ready(function () {
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });
        //setInterval(loadCalibrationData,10000);
        var userSeq = $("#userSeq").val();
        var folderSeq = $("#folderSeq").val();
        loadCalibrationData();	
 		loadCalibrationGrid(userSeq,folderSeq);	
     });
     function loadCharts(divId,parameterName,datesArr,values){
    	 chart = new Highcharts.Chart({
             chart: {
                 renderTo: divId,
             },
             title: {
 		        text: ' Calibration Detail ' + parameterName  
 		    },
 		    xAxis: {
 			    text: 'xAxis',
 			    categories: datesArr
 			},
 		    yAxis: {
 		        title: {
 		            text: ''
 		        }
 		    },
 		    legend: {
 		        layout: 'vertical',
 		        align: 'right',
 		        verticalAlign: 'middle'
 		    },

 		    plotOptions: {
 		    	series: {
 		            label: {
 		                connectorAllowed: false
 		            }
 		        }
 		    },

 		    series: [{
 		        name: parameterName,
 		        data: values
 		    }],

 		    responsive: {
 		        rules: [{
 		            condition: {
 		                maxWidth: 500
 		            },
 		            chartOptions: {
 		                legend: {
 		                    layout: 'horizontal',
 		                    align: 'center',
 		                    verticalAlign: 'bottom'
 		                }
 		            }
 		        }]
 		    }
         });
     }
     function startCalibration(){
         alert("Calibration Started");
     }
     function submitForm(calType,isStart){
        var loggerId = $("#loggerId").val();
        var folderSeq = $("#folderSeq").val();
        var userSeq = $("#userSeq").val();
        if(isStart){
        	$("#"+calType).addClass('btn-danger');
        }else{
        	$("#"+calType).removeClass("btn-danger"); 
        }
        //return;
        $url = "calibrationStation.php?call=markCalibration&calType="+calType+"&loggerId="+loggerId+"&isStart="+isStart+"&folderSeq="+folderSeq+'&userSeq='+userSeq;
     	$.getJSON($url, function(data){
         	var message = data.message;
         	var success = "failed"
         	if(data.success == 1){
         		success = "success";
         		loadCalibrationData();	
         		loadCalibrationGrid(userSeq,folderSeq);	 	
         	}
         	showNotification(message,success);
     	})
     }
     
     function loadCalibrationData(){
    	$(".calData").html('<i class="fa fa-spinner"></i>'); 
    	$("#parameters").html("");
   		 $("#values").html("");
        var folderSeq = $("#folderSeq").val();
        var siteCode = $("#stationCode").val();
    	$url = "calibrationStation.php?call=getCalibrationData&folderSeq="+folderSeq+"&siteCode="+siteCode;
 		$.get($url, function(data){
 			loadTableData(data);
 		})
     }
     
     function loadTableData(jsonString){
         	 var jsonData = $.parseJSON(jsonString);
         	 var data = jsonData.data
         	 var lastDataDate = jsonData.lastSynchedDate;
         	 var status = jsonData.status;
         	 $.each(data, function(index , response){
            	 $("#"+index+"unit").text(response.unit);
            	 $("#"+index+"conValue").text(response.gasconcentrationvalue);
            	 $("#"+index+"conStatus").text(response.gasconcentrationstatus);
            	 $("#"+index+"calGas").text(response.calgas);
            	 $("#"+index+"analyzerRange").text(response.anlyzerrange);
            	 $("#"+index+"analyzerStatus").text(response.anlyzerstatus);
            	 $("#"+index+"calibrationStatus").text("--");
            	 //$("#parameters").html(response.parameters);
            	 //$("#values").html(response.values);
        	 });
        	 var calType = "";
        	 if(status != "0.00"){
            	 if($status == "32,768.00"){
            		 calType = "span";	 
            	 }else if(status == "8,192.00"){
            		 calType = "purge";
            	 }
            	 $("#"+calType).addClass('btn-danger');
        	 }else{
        		 $("#"+calType).removeClass("btn-danger"); 
        	 }
        	 $("#lastDataSync").html(lastDataDate);
     }
     function showDetail(seq,calType){
      	 $("#tableData").html("");
         var url = "ajax_cpcb.php?method=requestStationReportCalibration&seq="+seq;
  		 $.get(url, function(jsonString){
  	  		 var jsonData = $.parseJSON(jsonString);
  	  		$("#historyTitle").html("<h3>"+ calType  +" Calibration detail for " + jsonData.dataDate +"</h3>");
  			 var data = jsonData.data;
  			 var channels = jsonData.channels;
  			 var html = '<table class="table table-striped">'
                 html+= '<thead>';
             	 html += '<tr>';
                 html += '<th>Date </th>';
            $.each(channels, function(chNo , chName){
            	if(chName.toLowerCase() == "sox" || chName.toLowerCase() == "nox"){
            	 	html += '<th>'+chName+'</th>';
                 }
            });    
            html += '</tr>';
            html += '</thead>';
            html += '</thead>';
            html += '<tbody>';
            var chValueArr = [];
            var datesArr = [];
            $.each(data, function(index , response){
            	html += '<tr>';
		        html += '<td>'+index+'</td>';
		        datesArr.push(index);
  				$.each(channels, function(chNo,chName){
  					if(chName.toLowerCase() == "sox" || chName.toLowerCase() == "nox"){
  				    	html += '<td>'+response[chNo]+'</td>';
  					}
  		        });
  		        html += '</tr>';		
  			})
  			html += '</tbody></table>';
  			$("#tableData").html(html);
  			loadChartData(jsonData,datesArr);
  			$("#detailTabs").show();
  			
     	});
      	
     }
     function loadChartData(jsonData,datesArr){
    	 var graphData = jsonData.graphData;
    	 html = "";
    	 var i = 1;
    	 $.each(graphData, function(chName,data){
			if(chName.toLowerCase() == "sox" || chName.toLowerCase() == "nox"){
				var divId = "container" + i;
				html = '<div id="'+divId+'" style="width:1100px;margin:auto;margin-top:10px;"></div>';
				$(".mainGraphDiv").append(html);
				var stationName = chName
				var valuesArr = [];
				$.each(data, function(date,value){
					var inVal = parseInt(value);
					valuesArr.push(inVal)
				});
				loadCharts(divId,stationName,datesArr,valuesArr);
				i++;
			}
			
	    });  
    	
     }
</script>
