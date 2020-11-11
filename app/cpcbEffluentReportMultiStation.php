<?
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
<? $menuItem = "reportEFFLUENTMenu";?>
<?include("cpcbHeader.php");?>
<?include("cpcbMenu.php")?>
<head><meta http-equiv="Content-Type" content="text/html; charset=windows-1252">


<script>
var reportType = null;
// Apply the theme
var options = Highcharts.setOptions(Highcharts.theme);

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
            changeMonth: true,
            //onClose: function( selectedDate ) {
                //$( "#toDate" ).datepicker( "option", "minDate", selectedDate );
            //}
        });
        $('#toDate').datetimepicker({
            maxDate: new Date(),
            dateFormat: 'dd-mm-yy',
            changeMonth: true,
            //onClose: function( selectedDate ) {
              //  $( "#fromDate" ).datepicker( "option", "maxDate", selectedDate );
           // }
        });
        //var currFromDate = new Date("2013-01-31");
        var currFromDate = new Date();
        currFromDate.setHours(00);
        currFromDate.setMinutes(00);
        $('#fromDate').datetimepicker('setDate', currFromDate);

        var currToDate = new Date();
        $('#toDate').datetimepicker('setDate', currToDate);
        tabs = $(".stationsTab").tabs();

        <?if($locSeqParam  == null){?>
            getAllLocationsAndStations();
        <?}else{?>
            getStationsByLocation(<?echo $locSeqParam. ",'EFFLUENT'" ?>);
        <?}?>

        $('.quickButton').click(function (){
            submitReport(this.value);
        });
        $('.calibrationLink').click(function (){
            var caliHTML = '<table width="787" border="1" cellspacing="2" cellpadding="2">    <tr style="font-weight:bold">      <td width="20">Calibration Type</td>      <td width="20">JSPL - Raigarh</td>      <td width="20">ACC - Gagal</td>      <td width="20">ITC 1</td>      <td width="20">ITC 2</td>      <td width="20">ITC 3</td>      <td width="20">ITC 4</td>    </tr>    <tr>      <td>ZERO</td>      <td>0 µg/m3</td>      <td>0 µg/m3</td>      <td>0 µg/m3</td>      <td>0 µg/m3</td>      <td>0 µg/m3</td>      <td>0 µg/m3</td>    </tr>    <tr>      <td>SPAN - CO</td>      <td>50 mg/m3</td>      <td>50 mg/m3</td>      <td>50 mg/m3</td>      <td>50 mg/m3</td>      <td>50 mg/m3</td>      <td>50 mg/m3</td>    </tr>    <tr>      <td>SPAN - SO2</td>      <td>1144 µg/m3</td>      <td>1144 µg/m3</td>      <td>1144 µg/m3</td>      <td>1144 µg/m3</td>      <td>1144 µg/m3</td>      <td>1144 µg/m3</td>    </tr>    <tr>      <td>SPAN - NOx</td>      <td>756 µg/m3</td>      <td>756 µg/m3</td>      <td>756 µg/m3</td>      <td>756 µg/m3</td>      <td>756 µg/m3</td>      <td>756 µg/m3</td>    </tr>    <tr>      <td>SPAN - Ozone</td>      <td>n.a</td>      <td>n.a</td>      <td>343 µg/m3</td>      <td>343 µg/m3</td>      <td>429 µg/m3</td>      <td>343 µg/m3</td>    </tr>  </table>';
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
    function getAllLocationsAndStations(){
        $.getJSON("ajax_cpcb.php?method=requestAllLocationsAndStationsJSON&folderType=EFFLUENT",function(data){
            $(".locationsSelectDiv").html("<select class='locationsSelect' name='locationsSelect' onChange='locationChange(\"Effluent\")'></select>");
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
<form name="stationReportForm" id="stationReportForm" method="POST" action="#">
<input type="hidden" name="isMultiStation" value="1" />
<input type="hidden" name="stationType" id="stationType" value="stack" />
<input type="hidden" name="stationType2" id="stationType2" value="effluent" />
<input type="hidden" name="pagenum" id="pagenum" value="1" />
<input type="hidden" name="pagesize" id="pagesize" value="50" />
<div style="margin:auto;width:1200px;min-height:250px;margin-top:3px;padding:4px;" class="ui-widget-content">
    <div class="ui-widget-content" style="width:70%;height:46px;float:left">
        <div style="float:left;line-height:17px;height:30px;padding:10px 0px 0px 4px;">Select Data Type</div>
        <div id="infoType">
                <input id="grid" type="radio" name="infoTypeRadio" value="grid" checked="checked">
                <label class="infoTypeRadioLabel" for="grid">Grid</label>

                <input id="graph" type="radio" name="infoTypeRadio" value="graph" >
                <label class="infoTypeRadioLabel" for="graph">Graph</label>

                <input id="export" type="radio" name="infoTypeRadio" value="export" >
                <label class="infoTypeRadioLabel" for="export">Export</label>
        </div>

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

    <div id="searchTabs" style="width:330px;padding:5px;float:right">
        <ul>
            <li><a href="#advanceSearch">Advance Search</a></li>
            <li><a href="#quickReports">Quick Reports</a></li>
        </ul>
        <div id="advanceSearch" style="padding:10px;width:auto;height:auto">
            <label class="formLabel">From :</label><input type="text" size="15" name="fromDate" id="fromDate"><br />
            <label class="formLabel">To :</label><input type="text" size="15" name="toDate" id="toDate"><br />
            <label class="formLabel">Time Base :</label>
            <select name="timeBase" id="timeBase">
                <option value="instant">Instant</option>
                <option value="5min">5 Minutes</option>
                <option value="10min">10 Minutes</option>
                <option value="15min">15 Minutes</option>
                <option value="30min">30 Minutes</option>
                <option value="1hour" selected="selected">1 Hour</option>
                <option value="3hours">3 Hours</option>
                <option value="4hours">4 Hours</option>
                <option value="6hours">6 Hours</option>
                <option value="8hours">8 Hours</option>
                <option value="12hours">12 Hours</option>
                <option value="24hours">24 Hours</option>
            </select>
           <!-- <label class="isAverage"><input value="on" type="checkbox" id="isAverage" name="isAverage" disabled="disabled" />Average</label><br />

           --> <label class="formLabel" style="clear:both;margin-top:10px;">Value Type :</label>
            <div id="valueType" style="width:100%;margin-top:6px;margin-left:31px;">
                    <input id="normal" type="radio" name="valueTypeRadio" value="normal" checked="checked" >
                    <label class="valueTypeRadioLabel" for="normal">Normal</label>

                    <!--<input id="zero" type="radio" name="valueTypeRadio" value="zero" >
                    <label class="valueTypeRadioLabel" for="zero">Zero</label>

                    <input id="span" type="radio" name="valueTypeRadio" value="span" >
                    <label class="valueTypeRadioLabel" for="span">Span</label>-->
            </div>

            <div style="width:100%;margin-top:6px;">
                <label class="isExceedingLabel"><input type="checkbox" id="isExceedingCheckbox" />Exceeding</label>
                <span id="exceedingType">
                    <input id="above" type="radio" name="exceedingTypeRadio" value="above" checked >
                    <label class="exceedingTypeRadioLabel" for="above">Above</label>

                    <input id="below" type="radio" name="exceedingTypeRadio" value="below" >
                    <label class="exceedingTypeRadioLabel" for="below">Below</label>

                    <input type="text" style="width:50px" id="exceedingValue"/>
                </span>
            </div>
            <div>
                <!--<label class="isExceedingLabel"><input type="checkbox" id="isValidated" name="isValidated" />Only Validated</label>
                    -->
            </div>
            <p>
                <div class="button" onClick="javascript:submitReport(null)" style="margin-top:5px;margin-left:5px;">
                    Generate Report
                </div>
                <div class="button" onClick="javascript:submitCumulativeReport()" style="margin-top:5px;margin-left:5px;">
                    Daily Cumulative Flow
                </div>
            </p>
            <!--<div class="calibrationLink linkSmall" align="center" style="display:none;margin-top:5px;">
                click to see calibration values
            </div>
            <div class="naaqsLink linkSmall" align="center" style="margin-top:5px;">
                click to see NAAQS values
            </div> -->
        </div>
        <div id="quickReports" style="padding:2px;width:100%">
            <div id="periodType" style="width:100%;margin-top:10px;">
                <p>
                    <label class="formLabel">Time Base :</label>
                    <select name="timeBaseQuick" id="timeBaseQuick">
                    <option value="5min">5 Minutes</option>
                    <option value="10min">10 Minutes</option>
                    <option value="15min">15 Minutes</option>
                    <option value="30min">30 Minutes</option>
                    <option value="1hour">1 Hour</option>
                    <option value="3hours">3 Hours</option>
                    <option value="4hours">4 Hours</option>
                    <option value="6hours">6 Hours</option>
                    <option value="8hours">8 Hours</option>
                    <option value="12hours">12 Hours</option>
                    <option value="24hours">24 Hours</option>
                    </select>
                </p>

                <div>
                    <button style="width:48%;" class="quickButton" value="recent" id="recent" type="button">Recent</button>
                    <button style="width:48%" class="quickButton" value="today" id="today" type="button">Today</button>
                </div>
                <div>
                    <button style="width:48%;" class="quickButton" value="last7days" id="last7days" type="button">Last 7 Days</button>
                    <button style="width:48%;" class="quickButton" value="last30days" id="last30days"type="button">Last 1 Month</button>

                </div>
                <!--  div>
                    <button style="width:48%;" class="quickButton" value="last6months" id="last6months"type="button">Last 6 Months</button>
                    <button style="width:48%;" class="quickButton" value="thisyear" id="thisyear"type="button">This Year</button>
                </div>-->
            </div>
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
        <span class="addStation buttonSmall button" onClick="javascript:addStation()">Add</span>
        <label>Add multiple stations to view its data</label>
         
        <div class="stationsTab" style="min-height:110px;width:98%">
            <ul>

            </ul>
        </div>
		<div class="channelButtons" style="margin-top:10px;float:right">
            <div class="button buttonSmall" onClick="javascript:selectAllChannels()">Select All</div>
            <div class="button buttonSmall" onClick="javascript:unselectAllChannels()">Unselect All</div>
        </div>
    </div>
</div>
</form>
<div style="clear:both"></div>
<div style="display:block;width:1200px;margin:auto;margin-top:10px;">
    <div class ="reportTitle" style="float:left;"></div><br/>
     <?php include "pagger.php"?>
    <div class ="legends" style="float:right">n.o -  *Not Observed</div>
   
</div>
<div id="mainGraphDiv" class="mainGraphDiv">
    <div id="graphDiv" style="display:none;width:1200px;margin:auto;margin-top:10px;"></div>
</div>
    <div style="clear:both;float:left" class="stationReport" ></div>
    <div id="flowVideoDiv" style="float:right;margin-bottom:50px;"></div> 
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
</script>