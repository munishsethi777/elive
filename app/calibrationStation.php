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
  $locationSeq = 0;
  if(!empty($folderSeq)){
	  $folderDataStore = FolderDataStore::getInstance();
	  $folder = $folderDataStore->FindBySeq($folderSeq);
	  $locationSeq = $folder->getLocationSeq();
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
    <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
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
                            	<?php 
                            		$html = "<h2>". $folder->getIndustryName() ."-". $folder->getStationName() ."</h2><br>";
                            		$html .= $folder->getAddress().", ". $folder->getCity() ."<br>";
                            		$html .= $folder->getState() .", ". $folder->getZipcode();
                            	
                            	
                            	?>
                                <?php echo $html?>
                                <p style="margin-top:10px">Last Data Syched On - <span id="lastDataSync"></span></p>
                            </div>  
                            <div class="ibox-content">
                                 <form name="frm1" id="calfrm1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-horizontal">
	                                 <input type="hidden" name="call" id="call" value="markCalibration" >
	                                 <input type="hidden" name="loggerId" id="loggerId" value="<?php echo $folder->getLoggerId()?>" >
	                                 <input type="hidden" name="stationCode" id="stationCode" value="<?php echo $stationCode?>" >
	                                 <input type="hidden" name="folderSeq" id="folderSeq" value="<?php echo $folderSeq?>" >
	                                  <input type="hidden" name="userSeq" id="userSeq" value="<?php echo $userSeq?>" >
	                                  <input type="hidden" name="detailSeq" id="detailSeq" value="" >
	                                  <input type="hidden" name="detailType" id="detailType" value="" >
	                                 <input type="hidden" name="calCmdValue" id="calCmdValue" >
	                                 <input type="hidden" name="lastRuleHitFileDataSeq" value="">
	                                 <?php if($locationSeq == 18) {?>
	                                 	   <a href="javascript:submitForm('calibration',1)"><button <?php echo $disabled ?> class="btn btn-primary" type="button">Calibration</button></a>
    	                             <?php }else{?>
    	                                 <a href="javascript:submitForm('zero',1)"><button <?php echo $disabled ?> class="btn btn-primary" type="button">Zero</button></a>
    	                                 <a href="javascript:submitForm('span',1)"><button id="span" <?php echo $disabled ?> class="btn btn-primary" type="button">Span Start</button></a>
    	                                 <a href="javascript:submitForm('span',0)"><button <?php echo $disabled ?> class="btn btn-primary" type="button">Span Stop</button></a>
    	                                 <a href="javascript:submitForm('purge',1)"><button id="purge" <?php echo $disabled ?> class="btn btn-primary" type="button">Purge Start</button></a>
    	                                 <a href="javascript:submitForm('purge',0)"><button <?php echo $disabled ?> class="btn btn-primary" type="button">Purge Stop</button></a>
	                                 <?php }?>
                                   	 <div class="ibox-content">
			                            <div class="table-responsive">
			                                <table class="table table-striped">
			                                    <thead>
			                                    <tr>
			                                        <th>Component </th>
			                                        <th>Unit </th>
			                                        <th>Concentration Value</th>
			                                        <!-- <th>Status</th>
			                                        <th>Calibration Gas</th>
			                                        <th>Analyzer Range</th>
			                                        <th>Analyzer Status</th>
			                                        <th>Calibration Status</th> -->
			                                    </tr>
			                                    </thead>
			                                    <tbody>
			                                    <?php foreach ($channels as $ch){
			                                    	$chName = $ch->getChannelName();
			                                    	if(strtolower($chName) != "sox" && strtolower($chName) != "nox" && strtolower($chName) != "so2"){
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
				                                        <!--<td><span class="calData" id="<?php //echo $chNumber . "conStatus"?>"><i class="fa fa-spinner"></i></span></td>
				                                         <td><span class="calData" id="<?php //echo $chNumber . "calGas"?>"><i class="fa fa-spinner"></i></span></td>
				                                        <td><span class="calData" id="<?php //echo $chNumber . "analyzerRange"?>"><i class="fa fa-spinner"></i></span></td>
				                                        <td><span class="calData" id="<?php //echo $chNumber . "analyzerStatus"?>"><i class="fa fa-spinner"></i></span></td>
				                                        <td><span id="<?php //echo $chNumber . "calibrationStatus"?>"><i class="fa fa-spinner"></i></span></td> -->
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
        <form id="exportForm" name="exportForm" method="GET" action="ajax_cpcb.php">
	     	<input type="hidden" id="method" name="method" value="exportCalibrationDetail"/>
	     	<input type="hidden" id="seq" name="seq"/>
	     	<input type="hidden" id="exportDetail" name="exportDetail"/>
   		</form>
    </body>
</html>
<script src="js/showCalibrations.js"></script>
<script type="text/javascript">
     $(document).ready(function () {
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });
        setInterval(refreshData,60000);
        refreshData();	
     });

     function refreshData(){
    	var userSeq = $("#userSeq").val();
        var folderSeq = $("#folderSeq").val();
    	loadCalibrationData();	
  		loadCalibrationGrid(userSeq,folderSeq);
  		var seq = $("#detailSeq").val();
        var calType = $("#detailType").val();
        if(seq != ""){
  			showDetail(seq,calType);
        }
        
     }
     charts = [];
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
 		  	 exporting: {
 		        enabled: false
 		    },
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
    	 charts[divId] = chart;
     }
     EXPORT_WIDTH = 1000;
     function save_chart(chart, filename) {
    	    var render_width = EXPORT_WIDTH;
    	    var render_height = render_width * chart.chartHeight / chart.chartWidth

    	    var svg = chart.getSVG({
    	        exporting: {
    	            sourceWidth: chart.chartWidth,
    	            sourceHeight: chart.chartHeight
    	        }
    	    });

    	    var canvas = document.createElement('canvas');
    	    canvas.height = render_height;
    	    canvas.width = render_width;

    	    var image = new Image;
    	    image.onload = function() {
    	        canvas.getContext('2d').drawImage(this, 0, 0, render_width, render_height);
    	        var data = canvas.toDataURL("image/png")
    	        download(data, filename + '.png');
    	    };
    	    image.src = 'data:image/svg+xml;base64,' + window.btoa(svg);
    	}

    	function download(data, filename) {
    	    var a = document.createElement('a');
    	    a.download = filename;
    	    a.href = data
    	    document.body.appendChild(a);
    	    a.click();
    	    a.remove();
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
            	 //$("#"+index+"unit").text(response.unit);
            	 $("#"+index+"unit").text("mg/Nm³");
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
            	 if(status == "32,768.00"){
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
    	 charts = [];
      	 $("#tableData").html("");
      	 $("#detailSeq").val(seq)
      	 $("#detailType").val(calType)
         var url = "ajax_cpcb.php?method=requestStationReportCalibration&seq="+seq;
  		 $.get(url, function(jsonString){
  	  		var jsonData = $.parseJSON(jsonString);
  	  		var text = calType  +" detail for " + jsonData.dataDate;
  	  		var exportButton = "<a href='javascript:exportData("+seq+",\""+ text  + "\")'><button class='btn btn-primary' type='button'>Export</button></a>";
  	  		$("#historyTitle").html("<h3>"+ text +"</h3> ");
  			 var data = jsonData.data;
  			 var channels = jsonData.channels;
  			 var html = exportButton + '<table class="table table-striped">'
                 html+= '<thead>';
             	 html += '<tr>';
                 html += '<th>Date </th>';
            $.each(channels, function(chNo , chName){
            	if(chName.toLowerCase() == "sox" || chName.toLowerCase() == "nox" || chName.toLowerCase() == "so2"){
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
  					if(chName.toLowerCase() == "sox" || chName.toLowerCase() == "nox" || chName.toLowerCase() == "so2"){
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

	 function exportData(calSeq,calDetail){
	 	 $("#seq").val(calSeq);
	 	 $("#exportDetail").val(calDetail);
	 	 $('#exportForm').submit();
	 } 

	 function exportGraph(divId){
		 var chart = charts[divId];
		 save_chart(chart, 'chart');
	 }
	 
     
     function loadChartData(jsonData,datesArr,exportButton){
    	 var graphData = jsonData.graphData;
    	  $(".mainGraphDiv").html("");
    	 html = "";
    	 var i = 1;
    	 $.each(graphData, function(chName,data){
			if(chName.toLowerCase() == "sox" || chName.toLowerCase() == "nox" || chName.toLowerCase() == "so2"){
				var divId = "container" + i;
				var exportGraph = "<a href='javascript:exportGraph(\""+ divId  + "\")'><button class='btn btn-primary' type='button'>Export "+chName+"</button></a>";
				html = exportGraph + '<div id="'+divId+'" style="width:1100px;margin:auto;margin-top:10px;"></div>';
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