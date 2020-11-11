<?include("sessioncheck.php");
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//HighValueRuleReminderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "Utils//StringUtils.php");
  require_once($ConstantsArray['dbServerUrl'] . "Utils//ExportUtils.php");
  require_once($ConstantsArray['dbServerUrl'] . "Utils/DropDownUtils.php");

Session_start();
$managerSession = $_SESSION["managerSession"];
$locSeq = $managerSession['locSeq'];
$LDS = LocationDataStore::getInstance();
$FDS = FolderDataStore::getInstance();
$locationSeqs = $LDS->FindLocationsByUser($managerSession["seq"]);
if(!in_array($locSeq,$locationSeqs)){
    array_push($locationSeqs,$locSeq);    
}
$folders = $FDS->FindByLocation(implode(",",$locationSeqs));

$HVRRDS = HighValueRuleReminderDataStore::getInstance();
$isError = false;
$msg = "";
$logs = "";
$folderSeq = "";
$fromDateForm = "";
$toDateForm = "";
$remindersCount = null;
if ($_GET["call"] == "populatedata"){
    $folderSeq = $_GET["folderSeq"];
    $folder = $folders[$folderSeq];
    $fromDateForm = $_GET["fromDate"];
    $toDateForm = $_GET["toDate"];
    $isLoadGrid = $_GET["isLoadGrid"];
    if($folderSeq == 0){
        $msg = "Select a station from drop down<br>";
        $isError = true;
    }
    if($fromDateForm == null){
        $msg .= "Select From date<br>";
        $isError = true;
    }
    if($toDateForm == null){
        $msg .= "Select To date<br>";
        $isError = true;
    }
    $fromDate = new DateTime($fromDateForm);
    $toDate = new DateTime($toDateForm);
    $toDate = $toDate->add(new DateInterval('P1D'));

    $fromDateStr = $fromDate->format("Y/m/d  H:i:s");
    $toDateStr = $toDate->format("Y/m/d  H:i:s");

    $remindersCount = $HVRRDS->FindByFolderFromToDate($folderSeq,$fromDateStr, $toDateStr);
    if($isLoadGrid == 0){
    	$mainArr["reminderCount"] = $remindersCount;
    	$mainArr["folderName"] = $folder->getFolderName();
    }else{
    	$logs = $HVRRDS->getHighValueReminderLogs($folderSeq,$fromDateStr, $toDateStr);
    	//$logsWithVirtualChannels = $HVRRDS->getHighValueReminderLogsWithVirtualChannels($folderSeq,$fromDateStr, $toDateStr);
    	//if(!empty($logsWithVirtualChannels)){
    	//	$logs = array_merge($logs,$logsWithVirtualChannels);
    	//}
    	//if(!empty($logs)){
    		//$logs = json_encode($logs);
    	//}
    	$mainArr["Rows"] = $logs;
    	$mainArr["TotalRows"] = $HVRRDS->getTotalCount($folderSeq,$fromDateStr, $toDateStr);
    }
    echo json_encode($mainArr);
    return;
}

$folDDown = DropDownUtils::getFoldersDropDownWithStationName($folders,"folder","",$folderSeq);
if (isset($_POST["call"]) && $_POST["call"] == "exportLogs"){    
    $folderSeq = $_POST["folder"];
    $fromDateForm = $_POST["fromDate"];
    $toDateForm = $_POST["toDate"];
    $fromDate = new DateTime($fromDateForm);
    $toDate = new DateTime($toDateForm);
    $toDate = $toDate->add(new DateInterval('P1D'));

    $fromDateStr = $fromDate->format("Y/m/d  H:i:s");
    $toDateStr = $toDate->format("Y/m/d  H:i:s");
    $exportType = $_POST["exporttype"];
    $HVRRDS = HighValueRuleReminderDataStore::getInstance();
    if($exportType == "cpcbExport"){
    	$folderSeqs = array_keys($folders);
    	$folderSeqs = implode(",", $folderSeqs);
    	$logs = $HVRRDS->getHVLogsForExportInCPCBFormat($folderSeqs,$fromDateStr, $toDateStr);
    	//usort($logs, function($a, $b) {
    	//	return $a['Category'] > $b['Category'];
    	//});
    	ExportUtils::exportCPCBReport($logs, $fromDateStr, $toDateStr);
    	
    }else{
    	$logs = $HVRRDS->getHighValueReminderLogsForExport($folderSeq,$fromDateStr, $toDateStr,false);
    	$logsWithVirtualChannels = $HVRRDS->getHighValueReminderLogsForExport($folderSeq,$fromDateStr, $toDateStr,true);
    	if(!empty($logsWithVirtualChannels)){
    		$logs =	array_merge($logs, $logsWithVirtualChannels);
    	}
    	ExportUtils::ExportData($logs);
    }
    
            
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
        <? include("leftButtons.php");
        $highValueRules = null;?>
        <div class="wrapper wrapper-content animated fadeInRight">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <? if($isError == 1){ ?>
                            <div class='ui-widget'>
                               <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'>
                                       <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span>
                                       <strong>Error during date fetching :</strong> <br/><br/><? echo $msg ?></p>
                               </div></div>
                        <? } ?>
                        <div class="ibox-title">
                            <h5>High Value Occurences</h5>
                        </div>
                        <div class="ibox-content">
                               
                            <form action="showHighValueOccurences.php" method="POST" id="highOccurencesForm" name="highOccurencesForm" class="form-horizontal">
                            	<input type="hidden" id="call" name= "call" >
                                <input type="hidden" id="exporttype" name="exporttype" value="export">
                                <input type="hidden" name="action" id="action" value="populatedata">
                                 <div class="form-group">
                                    <label class="col-lg-2 control-label">Station</label>
                                    <div class="col-lg-5">
                                        <? echo $folDDown; ?>
                                    </div>
                                 </div>
                                 <div class="form-group">
                                    <label class="col-lg-2 control-label">From Date</label>
                                    <div class="col-lg-4">
                                        <input type="text" name="fromDate" id="fromDate" placeholder="Select Date" value="<?echo $fromDateForm?>" required="required" class="form-control"> 
                                    </div>
                                 </div>
                                 <div class="form-group">
                                    <label class="col-lg-2 control-label">To Date</label>
                                    <div class="col-lg-4">
                                        <input type="text" name="toDate" id="toDate" placeholder="Select Date"  value="<?echo $toDateForm?>" required="required" class="form-control"> 
                                    </div>
                                 </div>
                                 <div class="form-group">
                                    <div class="col-lg-offset-2 col-lg-10">
                                        <button class="btn btn-primary" type="button" id="populateData" name="populateData">Submit</button>
                                        <button class="btn btn-primary" id="exportCpcbBtn" type="button">Export CPCB Format</button>
                                    </div>                                    
                                 </div>
                            </form>
                           <div id="reminderDetailDiv"></div>
                             
                           <form name="exportLogsForm" id="exportLogsForm" method="post" action="showHighValueOccurences.php" >
                                 <input type="hidden" name= "folderSeq" value="<?echo $folderSeq?>" >
                                 <input type="hidden" name= "fromDate" value="<?echo $fromDateForm?>" >
                                 <input type="hidden" name= "toDate" value="<?echo $toDateForm?>" >
                                 <input type="hidden" id="call" name= "call" value="exportLogs" >
                                 <input type="hidden" id="exporttype" name= "exporttype">
                                 <div id="jqxgrid"></div>
                            </form>                             
                        </div>
                    </div>
                </div> 
            </div>
        </div>
    </div>
    </body>
</html>
 <?include("../_jqxGridInclude.php");?>    
<script type="text/javascript">
        $(document).ready(function (){
        	$("#exportCpcbBtn").click(function(){  
        		$("#call").val("exportLogs");
        		$("#exporttype").val("cpcbExport");
        			exportCPCBLogs()
        	});
        	$("#populateData").click(function(){  
        		//$("#call").val("");
        		folderSeq = $("#folder").val();
        		fromDate = $("#fromDate").val();
        		toDate = $("#toDate").val();
        		showReminderCounts(folderSeq,fromDate,toDate);
        		loadGrid(folderSeq,fromDate,toDate);   		
        		
        	});	
            $('#toDate').datetimepicker({format:"m/d/Y",timepicker:false});
            $('#fromDate').datetimepicker({
                timepicker:false,
                format:'m/d/Y'
            });
        });
          
        function exportCPCBLogs(){        	
        	$("#highOccurencesForm").submit();
        }
        function exportLogs(){      
        	//folderSeq = $("#folder").val();
    		//fromDate = $("#fromDate").val();
    		//toDate = $("#toDate").val();      
        	//$("#exporttype").val("export");
           // $("#exportLogsForm").submit();
        	$("#call").val("exportLogs");
    		$("#exporttype").val("export");
    		$("#highOccurencesForm").submit();
        }
        
        function loadGrid(folderSeq,fromDate,toDate){
        	  var initrowdetails = function (index, parentElement, gridElement, datarecord) {
              	var notes = null;
              	tabsdiv = $($(parentElement).children()[0]);
              	if (tabsdiv != null) {
              		 notes = tabsdiv.find('.notes');
              		 var smsDetail = datarecord.smsDetail;
              		 var html = "<div style='margin: 7px;'><b>GUID :</b> " + datarecord.GUID + "</div>";
                  		 html += "<table style='padding:7px;'><thead><tr><th style='padding:7px;'>Dated</th><th style='padding:7px;'>Mobile</th><th style='padding:7px;'>Status</th></tr></thead><tbody>";
              		 $.each(smsDetail, function(key, value){
              			 html += "<tr><td style='padding:7px;'>" + value.dated + "</td><td style='padding:7px;'>" + value.mobile + "</td><td style='padding:7px;'>" + value.status + "</td>";                         
                       })
                       html += "</tbody></table>";
              		 var notescontainer = $('<div style="white-space: normal; margin: 5px;"><span>'+ html +'</span></div>');
              		 $(notes).append(notescontainer);
              		 $(tabsdiv).jqxTabs({ width: 750, height: 170});
                  }
              }           
              var source =
              {
                  //localdata: data,
                  datatype: "json",
                  pagesize: 10,
                  datafields: [
                      { name: 'Dated', type: 'date' },
                      { name: 'smsDetail', type: 'string' },
                      { name: 'Mobile', type: 'string' },
                      { name: 'Email', type: 'string' },
                      { name: 'Parameter', type: 'string' },
                      { name: 'Highvalue', type: 'string' },
                      { name: 'GUID', type: 'string' }
                  ],
  	            url: 'showHighValueOccurences.php?call=populatedata&folderSeq='+folderSeq + '&fromDate=' + fromDate + '&toDate=' + toDate + '&isLoadGrid= 1',
  	            root: 'Rows',
  	            cache: false,
  	            beforeprocessing: function(data)
  	            {
  	                source.totalrecords = data.TotalRows;
  	            },
  	            filter: function()
  	            {
  	                // update the grid and send a request to the server.
  	                $("#jqxgrid").jqxGrid('updatebounddata', 'filter');
  	            },
  	            sort: function()
  	            {
  	                    // update the grid and send a request to the server.
  	                    $("#jqxgrid").jqxGrid('updatebounddata', 'sort');
  	            },
  	            };
              
              var dataAdapter = new $.jqx.dataAdapter(source);
              $("#jqxgrid").jqxGrid(
              {
            	  source: dataAdapter, 
            	  width: 900,               
                  pageable: true,
                  autoheight: true,
                  showtoolbar: true,
                  sortable: true,
                  filterable: true,
                  columnsresize: true,
                  virtualmode: true, 
                  altrows: true,
                  enabletooltips: true,

                  rowdetails: true,
                  theme: "energyblue",
                  rowdetailstemplate: { rowdetails: "<div style='margin: 10px;'><ul style='margin-left: 30px;'><li>SMS Detail</li></ul><div class='notes'></div></div>", rowdetailsheight: 200 },
                  rendergridrows: function()
                  {
                        return dataAdapter.records;     
                  },  
                  initrowdetails: initrowdetails,             
                  columns: [
                      { text: 'Dated',  datafield: 'Dated', width: "20%", cellsformat: 'dd-MM-yyyy hh:mm:ss tt' },
                    { text: 'Parameter', datafield: 'Parameter', width: '10%' },
                    { text: 'Value',  datafield: 'Highvalue',width: '9%' },                  
                    { text: 'Email Id',  datafield: 'Email', width: '33%' },
                    { text: 'Mobile',  datafield: 'Mobile', width: '25%' }
                  ],
                   renderstatusbar: function (statusbar) {
                      // appends buttons to the status bar.
                                         var container = $("<div style='overflow: hidden; position: relative; margin: 5px;'></div>");
                   var exportButton = $("<div style='float: left;'><i class='fa fa-plus-square'></i><span style='margin-left: 4px; position: relative;'>Export</span></div>");
                   container.append(exportButton);
                   statusbar.append(container);
                   exportButton.jqxButton({  width: 80, height: 25 });                 
                   exportButton.click(function (event) {
                      exportLogs();    
                   })
                  }
              });
        }
        function showReminderCounts(folderSeq,fromDate,todate){
            var url = 'showHighValueOccurences.php?call=populatedata&folderSeq='+folderSeq + '&fromDate=' + fromDate + '&toDate=' + toDate + '&isLoadGrid= 0';
        	$.getJSON(url, function( data ) {
            	reminderCount = data.reminderCount;
            	folderName = data.folderName;
        		var items = [];        		        		
        		html = '<table class="table">';
        		html += '<tr><td><h5>High Value occurences for ' + folderName + ' of various parameters during ' + fromDate + ' and ' + todate + '</h5></td></tr>';                
                html += '<tr><td><table class = "table"><tr><td><b>Parameters</b></td>';                
                    $.each( reminderCount, function( key, val ) {
                    	channelName = val.channelname;
                    	virtualChannelSeq = val.virtualchannelseq;
                    	if(virtualChannelSeq != null && virtualChannelSeq !="" && virtualChannelSeq !=0){
                    		 channelName = val.virtualchannel;
                    	}
                    	
                	html += '<td >'+ channelName +'</td>'        			
        		});               
            	html += '</tr>';
            	html += '<tr><td><b>Number of times exceedances occurred</b></td>';
            	$.each( reminderCount, function( key, val ) {
                	html +='<td>' +  val.totalReminders + '</td>'
            	});
            	html += '</tr></table></td></tr>';
            	html += '</table>';	
                $("#reminderDetailDiv").html(html);
        	});
        }
    </script>

