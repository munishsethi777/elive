 <?include("sessioncheck.php");
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//ChannelConfigurationDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "Utils/PrescribedLimitsUtils.php");
  require_once($ConstantsArray['dbServerUrl'] . "Utils/CalibrationService.php");
 
  $folderSeq = $_GET["folderSeq"];
  $stationCode = $_GET["siteCode"];
  $folder = new Folder();
  $disabled = "disabled";
  if(!empty($folderSeq)){
	  $folderDataStore = FolderDataStore::getInstance();
	  $folder = $folderDataStore->FindBySeq($folderSeq);
	  $chConfigDataStore = ChannelConfigurationDataStore::getInstance();
	  $channels = $chConfigDataStore->FindByFolder($folderSeq);
	  if($_GET["call"] == "getCalibrationData"){
	  	$calibrationData = CalibrationService::getCallibrationData($stationCode,$channels);
	  	echo $calibrationData;
	  	return;
	  }
	  if(!empty($folder->getLoggerId())){
	  	 $disabled = "";
	  }
  }
  
  if($_GET["call"] == "markCalibration"){
  	$loggerId = $_GET["loggerId"];
  	$isSpan = $_GET["isSpan"];
  	$parameter = $_GET["parameter"];
  	$response = CalibrationService::makeCalibrationCall($loggerId, $parameter,$isSpan);
  	echo $response;
  	return;
  }
  ?>
<!DOCTYPE html>
<html>
    <head>
    <? include("_jsAdminInclude.php");?>
    <?include("../_InspiniaInclude.php");?>
    </head>
    <body>
      <div id="wrapper">       
            <? include("leftButtons.php");?>
            <div class="wrapper wrapper-content animated fadeInRight mainDiv">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h2><?php echo $folder->getFolderName()?> <small> - Remote Calibration</small></h2>
                            </div>  
                            <div class="ibox-content">
                                 <form name="frm1" id="calfrm1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-horizontal">
	                                 <input type="hidden" name="call" id="call" value="markCalibration" >
	                                 <input type="hidden" name="loggerId" id="loggerId" value="<?php echo $folder->getLoggerId()?>" >
	                                 <input type="hidden" name="stationCode" id="stationCode" value="<?php echo $stationCode?>" >
	                                 <input type="hidden" name="folderSeq" id="folderSeq" value="<?php echo $folderSeq?>" >
	                                 <input type="hidden" name="calCmdValue" id="calCmdValue" >
	                                 <input type="hidden" name="lastRuleHitFileDataSeq" id="seq" value="">
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
			                                        <th>Zero Calibration</th>
			                                        <th>Span Calibration</th>
			                                        <th>Calibration Status</th>
			                                    </tr>
			                                    </thead>
			                                    <tbody>
			                                    <?php foreach ($channels as $ch){
			                                    	$presLimits = PrescribedLimitsUtils::getPrescribedLimit($ch->getChannelName(),$folder->getStationType());
			                                    	$highValue = $presLimits["max"];
			                                    	$chName = $ch->getChannelName();
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
				                                        <td><a href="javascript:submitForm(0,'<?php echo $chName?>')"><button <?php echo $disabled ?> class="btn btn-primary" type="button">Zero</button></a></td>
				                                        <td><a href="javascript:submitForm(1,'<?php echo $chName?>')"><button <?php echo $disabled ?> class="btn btn-primary" type="button">Span</button></a></td>
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
                            </div>
                        </div>
                    </div>
                </div>
             </Div>
        </Div>
    </body>
</html>
<script type="text/javascript">
     $(document).ready(function () {
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });
        loadCalibrationData()
     });
     function submitForm(isSpan,parameter){
        var loggerId = $("#loggerId").val();
        $url = "calibrationStation.php?call=markCalibration&isSpan="+isSpan+"&loggerId="+loggerId+"&parameter="+parameter;
     	$.getJSON($url, function(data){
         	var message = data.message;
         	var success = "failed"
         	if(data.success == 1){
         		success = "success";
         		loadCalibrationData();		 	
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
     function loadTableData(data){
         	 
         	 data = $.parseJSON(data);
         	 $.each(data.data, function(index , response){
            	 $("#"+index+"unit").text(response.unit);
            	 $("#"+index+"conValue").text(response.gasconcentrationvalue);
            	 $("#"+index+"conStatus").text(response.gasconcentrationstatus);
            	 $("#"+index+"calGas").text(response.calgas);
            	 $("#"+index+"analyzerRange").text(response.anlyzerrange);
            	 $("#"+index+"analyzerStatus").text(response.anlyzerstatus);
            	 $("#"+index+"calibrationStatus").text(response.calibrationstatus);
            	 $("#parameters").html(response.parameters);
            	 $("#values").html(response.values);
        	 });
    		 
     }
</script>
