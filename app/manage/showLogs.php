<?php
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//AppLogDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils//ExportUtils.php");
 $logDataStore = AppLogDataStore::getInstance();
 if($_GET["call"] == "getAllLogs"){   
    $logsJson = $logDataStore->getAllLogsJson();
    echo $logsJson;
    return;
 }
 if($_POST["call"] == "exportLogs"){
    $logsJson = $logDataStore->getAllLogs();
    ExportUtils::ExportData($logsJson);  
 } 
?>
<html>
    <head>
        <? include("_jsAdminInclude.php");?>
        <?include("../_InspiniaInclude.php");?>   
    </head>
    <body>
    <div id="wrapper"> 
        <? include("leftButtons.php");?>
         <div class="wrapper wrapper-content animated fadeInRight">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Show Logs</h5>
                        </div>
                        <div class="ibox-content">
                            <form name="exportLogsForm" id="exportLogsForm" method="post" action="showLogs.php" >
                                 <input type="hidden" name= "call" value="exportLogs" >
                                 <div id="jqxgrid"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
         </div>
    </div> 
    
       
 </html>

 <?include("../_jqxGridInclude.php");?>
<script type="text/javascript">
        $(document).ready(function (){
           
            var source =
            {
                datatype: "json",
                pagesize: 20,
                datafields: [
                    { name: 'timestamp', type: 'string' },
                    { name: 'level', type: 'string' },
                    { name: 'message', type: 'string' },
                    { name: 'file', type: 'string' },
                    { name: 'line', type: 'string' }
                ],
                url: 'showLogs.php?call=getAllLogs',
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
                width: 800,
                source: dataAdapter,                
                pageable: true,
                autoheight: true,
                showtoolbar: true,
                sortable: true,
                filterable: true,
                columnsresize: true,
                altrows: true,
                enabletooltips: true,
                altrows: true, 
                theme: "energyblue",
                virtualmode: true,
                rendergridrows: function()
                {
                      return dataAdapter.records;     
                },               
                columns: [
                  { text: 'Dated',  datafield: 'timestamp', width: 150},
                  { text: 'Level',  datafield: 'level', width: 70 },
                  { text: 'Message',  datafield: 'message', width: 320 },
                  { text: 'File', datafield: 'file', width: 200 },
                  { text: 'Line No',  datafield: 'line',width: 60 },
                ],
                 renderstatusbar: function (statusbar) {
                    // appends buttons to the status bar.
                    var container = $("<div style='overflow: hidden; position: relative; margin: 5px;height:30px'></div>");
                    var exportButton = $("<div style='float: left;'><i class='fa fa-plus-square'></i><span style='margin-left: 4px; position: relative;'>Export</span></div>");
                 container.append(exportButton);
                 statusbar.append(container);
                 exportButton.jqxButton({  width: 65, height: 18 });                 
                 exportButton.click(function (event) {
                     $("#jqxgrid").jqxGrid('exportdata', 'csv', 'Logs');
                     exportLogs();    
                 })
                }
            });
            
        });
        function exportLogs(){
            $("#exportLogsForm").submit();
        }
    </script>