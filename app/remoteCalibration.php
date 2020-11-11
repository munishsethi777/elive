<?php
    require_once('IConstants.inc');
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/LocationDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDDataDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/Utils/ConvertorUtils.php");
    require_once($ConstantsArray['dbServerUrl'] ."/Utils/PrescribedLimitsUtils.php");
    require_once($ConstantsArray['dbServerUrl'] ."/Managers/CPCBMgr.php");

    $locSeqParam = $_GET['lsp'];

?>
<?php include("sessioncheckPrivateOnly.php");?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<? $menuItem = "remoteCalibrationMenu";?>
<?include("cpcbHeader.php");?>
<?include("cpcbMenu.php")?>
<head><meta http-equiv="Content-Type" content="text/html; charset=windows-1252">


<script>

// Apply the theme
var options = Highcharts.setOptions(Highcharts.theme);
   var iscombustion = false;
   var reportType = null;
   // var options;
    var tabs;
    var tabContent = $( "#tab_content" );
    var tabTemplate = "<li><a href='#{href}'>#{label}</a> <span class='ui-icon ui-icon-close' role='presentation'>Remove Tab</span></li>";
    tabCounter = 0;

    $(document).ready(function(){
        //renderChart();
        $("#infoType" ).buttonset();
        $("#searchTabs").tabs();
        $(".quickButton").button();
        $("#graphType" ).buttonset();
        $("#exportType" ).buttonset();
        $("#valueType" ).buttonset();
        $(".valueType" ).buttonset();
        $("#exceedingType" ).buttonset();

        $(".button").button();
        $('#fromDate').datetimepicker({
            maxDate: new Date(),
            dateFormat: 'dd-mm-yy',
            changeMonth: true
        });
        $('#toDate').datetimepicker({
            maxDate: new Date(),
            dateFormat: 'dd-mm-yy',
            changeMonth: true
        });

        var currFromDate = new Date();
        currFromDate.setHours(00);
        currFromDate.setMinutes(00);
        $('#fromDate').datetimepicker('setDate', currFromDate);

        var currToDate = new Date();
        $('#toDate').datetimepicker('setDate', currToDate);
        tabs = $(".stationsTab").tabs();

        <?//if($locSeqParam  == null){?>
            getAllLocationsAndStations();
        <?//}else{?>
            //getStationsByLocation(<? //echo $locSeqParam. ",'STACK'" ?>);
        <?//}?>

        $('.quickButton').click(function (){
            submitReport(this.value);
        });
        $('.calibrationLink').click(function (){
            var caliHTML = '<table width="787" border="1" cellspacing="2" cellpadding="2">    <tr style="font-weight:bold">      <td width="20">Calibration Type</td>      <td width="20">JSPL - Raigarh</td>      <td width="20">ACC - Gagal</td>      <td width="20">ITC 1</td>      <td width="20">ITC 2</td>      <td width="20">ITC 3</td>      <td width="20">ITC 4</td>    </tr>    <tr>      <td>ZERO</td>      <td>0 �g/m3</td>      <td>0 �g/m3</td>      <td>0 �g/m3</td>      <td>0 �g/m3</td>      <td>0 �g/m3</td>      <td>0 �g/m3</td>    </tr>    <tr>      <td>SPAN - CO</td>      <td>50 mg/m3</td>      <td>50 mg/m3</td>      <td>50 mg/m3</td>      <td>50 mg/m3</td>      <td>50 mg/m3</td>      <td>50 mg/m3</td>    </tr>    <tr>      <td>SPAN - SO2</td>      <td>1144 �g/m3</td>      <td>1144 �g/m3</td>      <td>1144 �g/m3</td>      <td>1144 �g/m3</td>      <td>1144 �g/m3</td>      <td>1144 �g/m3</td>    </tr>    <tr>      <td>SPAN - NOx</td>      <td>756 �g/m3</td>      <td>756 �g/m3</td>      <td>756 �g/m3</td>      <td>756 �g/m3</td>      <td>756 �g/m3</td>      <td>756 �g/m3</td>    </tr>    <tr>      <td>SPAN - Ozone</td>      <td>n.a</td>      <td>n.a</td>      <td>343 �g/m3</td>      <td>343 �g/m3</td>      <td>429 �g/m3</td>      <td>343 �g/m3</td>    </tr>  </table>';
            TINY.box.show({html:caliHTML,animate:true,close:true,boxid:'success'});
        });
        $('.naaqsLink').click(function (){
            TINY.box.show({url:'naaqs.html',animate:true,close:true,boxid:'success'});
        });

        $('#infoType input:radio').change(function (){
            if(this.value == "graph"){
                $("#graphType").show("slide", {}, 1000);
            }else{
                $("#graphType").hide("slide", {}, 1000);
            }
            resetQuickButton();
        });
        $('#infoType input:radio').change(function (){
            if(this.value == "export"){
                $("#exportType").show("slide", {}, 1000);
            }else{
                $("#exportType").hide("slide", {}, 1000);
            }
            resetQuickButton();
        });
        $('#valueType input:radio').change(function (){
            if(this.value != "normal"){
                $(".calibrationLink").show();
                $(".naaqsLink").hide();
            }else{
                $(".naaqsLink").show();
                $(".calibrationLink").hide();
            }
        });
        $('#exportType input:radio').change(function (){
            resetQuickButton();
        });
        $('#timeBaseQuick').change(function (){
            resetQuickButton();
        });
        $("#isExceedingCheckbox").change(function() {
            isExceedingEnable(this.checked);
        });
        isExceedingEnable(false);
        $("#recentValuesDiv").hide();

    });
    function isExceedingEnable(bool){
        if (bool) {
            $('#exceedingType :input').removeAttr('disabled');
            $("#exceedingType").buttonset({disabled: false}) ;
        } else {
            $('#exceedingType :input').attr('disabled', true);
            $("#exceedingType").buttonset({disabled: true}) ;
        }
    }
    function locationChange($stationType){
        $locSeq = $(".locationsSelect").val();
        if($locSeq == 0){
            getAllLocationsAndStations();//located in the actual file
        }else{
            getStationsByLocation($locSeq,$stationType);
        }
    }
    function getStationsByLocation($locSeq,$stationType){
        $.getJSON("ajax_cpcb.php?method=requestStationsByLocationJSON&locSeq="+$locSeq+"&stationType="+$stationType+"&isCalibration=true",function(data){
            loadStationsDiv(data);
        });
    }
    function getAllLocationsAndStations(){
        $.getJSON("ajax_cpcb.php?method=requestAllCalibrationLocationsAndStationsJSON&folderType=STACK",function(data){
            $(".locationsSelectDiv").html("<select class='locationsSelect' name='locationsSelect' onChange='locationChange(\"STACK\")'></select>");
            $.each(data.locations,function(key,value){
                $('.locationsSelect')
                .append($("<option></option>")
                .attr("value",key)
                .text(value));
            });
            $(".locationsSelect").html($('.locationsSelect option').sort(function(x, y) {
            	if($(y).text() != "All"){
            		return $(x).text() < $(y).text() ? -1 : 1;
                }
            }));
            loadStationsDiv(data.folders);
            <? if($queryFolSeq != null){ ?>
                addStationFolder(<?echo $queryFolSeq?>,"<?echo $queryFolName?>");
            <?}?>
        });
    }

</script>
<script type="text/javascript">

</script>
<body style="margin:0px 0px 0px 0px" class="fullBody">
<div id="toTop">^ Back to Top</div>
<form name="stationReportForm" id="stationReportForm" method="post" action="#">
<input type="hidden" name="isMultiStation" value="1" />
<input type="hidden" name="stationType" id="stationType" value="stack" />
<input id="lastDataDate" type="hidden" name="lastDataDate" >
<input type="hidden" name="pagenum" id="pagenum" value="1" />
<input type="hidden" name="pagesize" id="pagesize" value="50" />
<div style="margin:auto;width:1200px;min-height:250px;margin-top:3px;padding:4px;" class="ui-widget-content">
    <div class="ui-widget-content" style="width:70%;height:46px;float:left">
        <div id="graphType" style="margin-left:10px;display:none;width:510px;float: right;position:absolute;left:520px;">
                Select a Graph Type:
                <input id="line" type="radio" name="graphTypeRadio" value="line" checked="checked">
                <label class="graphTypeRadioLabel" for="line">Line</label>

                <input id="spline" type="radio" name="graphTypeRadio" value="spline" >
                <label class="graphTypeRadioLabel" for="spline">SPLine</label>

                <input id="area" type="radio" name="graphTypeRadio" value="area" >
                <label class="graphTypeRadioLabel" for="area">Area</label>

                <input id="windrose" type="radio" name="graphTypeRadio" value="windrose" >
                <label class="graphTypeRadioLabel" for="windrose">WindRose</label>
        </div>
        <div id="exportType" style="margin-left:10px;display:none;width:510px;float: right;position:absolute;left:520px;">
                Select Export Format:
                <input id="csv" type="radio" name="exportTypeRadio" value="csv" checked="checked">
                <label class="graphTypeRadioLabel" for="csv">CSV</label>

                <input id="html" type="radio" name="exportTypeRadio" value="html" >
                <label class="graphTypeRadioLabel" for="html">HTML</label>

                <input id="xls" type="radio" name="exportTypeRadio" value="xls" >
                <label class="graphTypeRadioLabel" for="xls">EXCEL</label>

                <input id="pdf" type="radio" name="exportTypeRadio" value="pdf" >
                <label class="graphTypeRadioLabel" for="pdf">PDF</label>
        </div>

    </div>




    <div style="width:830px;min-height:185px;padding:5px;margin-top:5px;display: inline-table;" class="ui-widget-content">
        <div style="width:100%;padding:2px" class="ui-state-default">
                Select an Owner and Station and Click add to see its air data. You may add multiple stations and select the parameters.
        </div>
        <label>Industry :</label>
        <span class="locationsSelectDiv"></span>

        <br />
        <label>Station :</label>
        <span class="stationsSelectDiv"></span>
        <span class="addStation buttonSmall button" onClick="javascript:loadCalibrationData()">Calibration</span>
        
    </div>
</div>
</form>
<div style="clear:both"></div>
<div style="display:block;width:1200px;margin:auto;margin-top:10px;">
    <div class ="reportTitle" style="float:left;"></div><br/>
    <?php include "pagger.php"?>
    <div class ="legends" style="float:right">n.o -  *Not Observed</div>
    <br/>
    
</div>
<div id="mainGraphDiv" class="mainGraphDiv">
    <div id="graphDiv" style="display:none;width:1200px;margin:auto;margin-top:10px;"></div>
</div>
<div class="stationReport" ></div>

</body>
</html>
<script type="text/javascript">
	function loadData(pageNumber){
		$("#pagenum").val(pageNumber);
		if(iscombustion){
			submitCombustionReport(null,true);
		}else{
			submitReport(reportType,true);
		}	
	}
	function loadCalibrationData(){
		var folSeq = $(".stationsSelect").val();
		if(folSeq > 0){
			var folderName = $(".stationsSelect option:selected").text();
	    	window.open('calibrationStation.php?folderSeq='+folSeq, '_blank');
		}else{
			alert("Select Station");
		}
	}
</script>