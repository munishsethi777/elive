<?php
include("sessioncheck.php");
$managerSession = $_SESSION["managerSession"];
$isAdmin = $managerSession["isAdmin"];
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");
require($ConstantsArray['dbServerUrl'] . "Utils//FileSystemUtils.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils/DropDownUtils.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//M2MSynchronizerDataStore.php");

$LDS = LocationDataStore ::getInstance();
$locations = $LDS->FindAll();
$locationSeq = $_GET["locationSeq"];
$FDS = FolderDataStore::getInstance();
$seq = $managerSession['seq'] ;
$call = $_GET["call"];
if(empty($call)){
	$call = $_POST["call"];
}
$isAdmin = $managerSession["isAdmin"];
if($call == "getFolders"){
	if(empty($locationSeq) && !$isAdmin){
		$locationSeqs = $LDS->FindLocationsByUser($seq);
		$lseq = $managerSession['locSeq'];
		if(!in_array($lseq,$locationSeqs)){
			array_push($locationSeqs,$lseq);
		}
		$locationSeqs = implode(",",$locationSeqs);
	}else{
		$locationSeqs = $locationSeq;
	}
	if(empty($locationSeq) && $isAdmin ){
		$folders = $FDS->FindAllJson(true);
	} else{
		$folders = $FDS->FindJsonByLocationSeqs($locationSeqs,true);
	}
	echo $folders;
	return;
}
if ($call == "delete" ){
	$FolderPath  = $_POST["path"];
	//FileSystemUtils::delete_NestedDirectory($FolderPath);
	//$FDS->deleteBySeq($_POST['editSeq']);
	//$folders = $FDS->FindAll();


}
if ($call == "changeStatus" ){
	$isEnabled  = $_GET["isEnabled"];
	$seq  = $_GET["editSeq"];
	$respone["result"] ="success";
	try{
		$FDS->updateIsEnable($seq,$isEnabled);
	}catch (Exception $e){
		$respone["result"] = $e->getMessage();
	}
	echo json_encode($respone);
	return;
	 
}
if ($call == "changeVisible" ){
	$isEnabled  = $_GET["isEnabled"];
	$seq  = $_GET["editSeq"];
	$respone["result"] ="success";
	try{
		$FDS->updateIsVisible($seq,$isEnabled);
	}catch (Exception $e){
		$respone["result"] = $e->getMessage();
	}
	echo json_encode($respone);
	return;
	 
}
?>
<!DOCTYPE html>
<html>
    <head>
        <? include("_jsAdminInclude.php");
        include("../_InspiniaInclude.php");?>     
    </head>
    <body>
    <div id="wrapper">   
        <? include("leftButtons.php");?>
        <div class="wrapper wrapper-content animated fadeInRight">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Show Stations</h5>
                        </div>
                        <div class="ibox-content">
                            <form method="post" role="form" name="folderForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-horizontal">
                                <input type="hidden" name="locationSeq" id="locationSeq" value="<?echo $locationSeq?>" />
                                <input type="hidden" name="editSeq" id="editSeq"/> 
                                 <input type="hidden" name="isEnabled" id="isEnabled" />
                                 <input type="hidden" name="call" id="call" /> 
                                <div class="form-group">
                                    <label class="col-sm-1 control-label">Location</label>
                                    <div class="col-sm-5">
                                        <?  if($isAdmin){
                                            echo DropDownUtils::getAllLocationsDropDown("l_DropDown","loadGrid(this.value)",$locationSeq,"All Locations");    
                                        }else{
                                            echo DropDownUtils::getUserLocationsDropDown($seq,"l_DropDown","loadGrid(this.value)",$locationSeq,"All Locations");
                                        }
                                            
                                        ?>
                                    </div>
                                </div>
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
 <script language="javascript">
        var showFolders_FoldersJSON = null;
        var showFolders_UsersJSON = null;
        var showFolders_UserDivId = 0;
        $( document ).ready(function() {
            loadGrid(0);
        });
        
        function loadGrid(locationSeq){
            var hiddenFlag = true;
            var width = "17%";
            if(("<?echo $isAdmin?>") == "1"){
                hiddenFlag = false;
                width = "7%";
            }
            var source =
            {
                datatype: "json",
                pagesize: 20,
                datafields: [
                    { name: 'seq', type: 'integer' },    
                    { name: 'isenable', type: 'string' },
                    { name: 'isonline', type: 'string' },
                    { name: 'isvisible', type: 'string' },
                    { name: 'm2msitecode', type: 'string' },
                    { name: 'foldername', type: 'string' },
                    { name: 'lastsynchedon', type: 'date' },                   
                    { name: 'dataDated', type: 'date' }
                     
                ],
                url: 'showM2MSites.php?call=getFolders&locationSeq='+locationSeq,
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
           
            var columnrenderer = function (value) {
                    return '<div style="text-align: center; margin-top: 5px;">' + value + '</div>';       
            }
            var actions = function (row, columnfield, value, defaulthtml, columnproperties) {
                    data = $('#jqxgrid').jqxGrid('getrowdata', row);
                    var html = "<div style='text-align: center; margin-top: 5px;'><a href='javascript:Edit("+ data['seq'] + ")' ><i class='fa fa-pencil-square-o' title='Edit'></i></a>";
                        html += "</div>";
                    
                    return html;
            }
            var actionEnable = function (row, columnfield, value, defaulthtml, columnproperties) {
                    data = $('#jqxgrid').jqxGrid('getrowdata', row);
                    
                    if(value == 1){
                        
                        var html = "<div style='text-align: center; margin-top: 5px;'>";
                        if(("<?echo $isAdmin?>") == "1"){
                            html += "<div id='enableDiv"+data['seq']+"'><a href='javascript:changeStatus("+ data['seq'] + ', \"' +  0 + "\")' ><i class='fa fa-check-square-o' title='Disable'></i></a></div>";   
                        }else{
                            html += "<i class='fa fa-check-square-o' title='Disable'></i>";
                        }
                        html += "</div>";    
                    }else{
                        var html = "<div style='text-align: center; margin-top: 5px;'>";
                        
                        if(("<?echo $isAdmin?>") == "1"){
                            html += "<div id='enableDiv"+data['seq']+"'><a href='javascript:changeStatus("+ data['seq'] + ', \"' +  1 + "\")' ><div id='enableDiv"+data['seq']+"'><i class='fa fa-square-o' title='Enable'></i></a></div>";   
                        }else{
                            html += "<i class='fa fa-square-o' title='Disable'></i>";
                        }
                        html += "</div>";    
                    }
                    
                    
                    return html;
            }
            var actionVisible = function (row, columnfield, value, defaulthtml, columnproperties) {
                    data = $('#jqxgrid').jqxGrid('getrowdata', row);
                   
                    if(value == 1){
                        var html = "<div style='text-align: center; margin-top: 5px;'>";
                        if(("<?echo $isAdmin?>") == "1"){
                            html += "<div id='visibleDiv"+data['seq']+"'><a href='javascript:changeVisible("+ data['seq'] + ', \"' +  0 + "\")' ><i class='fa fa-eye' title='InVisible'></i></a></div>";   
                        }else{
                            html += "<i class='fa fa-eye' title='InVisible'>";
                        }
                        html += "</div>";    
                    }else{
                        var html = "<div style='text-align: center; margin-top: 5px;'>";
                        if(("<?echo $isAdmin?>") == "1"){
                            html += "<div id='visibleDiv"+data['seq']+"'><a href='javascript:changeVisible("+ data['seq'] + ', \"' +  1 + "\")' ><i class='fa fa fa-eye-slash' title='Visible'></i></a></div>";   
                        }else{
                            html += "<i class='fa fa fa-eye-slash' title='Visible'></i>";
                        }
                        
                        html += "</div>";    
                    }
                    
                    
                    return html;
            }
            
            var dataAdapter = new $.jqx.dataAdapter(source);
            
            $("#jqxgrid").jqxGrid(
            {
                width: "100%",
                source: dataAdapter,                
                pageable: true,
                autoheight: true,
                showtoolbar: false,
                sortable: true,
                filterable: true,
                columnsresize: true,
                virtualmode: true,
                rendergridrows: function()
                {
                      return dataAdapter.records;     
                },               
                columns: [
                  { text: 'seq',  datafield: 'seq', hidden:true},     
                  { text: 'Enabled',  datafield: 'isenable', width: "5%", renderer:columnrenderer,cellsrenderer:actionEnable},
                  { text: 'Visible',  datafield: 'isvisible', width: "5%",renderer:columnrenderer,cellsrenderer:actionVisible },
                  { text: 'Status',  datafield: 'isonline', width: "7%" },
                  { text: 'Site Code',  datafield: 'm2msitecode', width: "16%" },
                  { text: 'Station',  datafield: 'foldername', width: "35%" },
                  { text: 'Data Saved Till', datafield: 'lastsynchedon', width: "16%",cellsformat: 'dd-MM-yyyy hh:mm:ss tt' },
                  
                  { text: 'CPCB Synced Till',  datafield: 'dataDated',width: "16%",cellsformat: 'dd-MM-yyyy hh:mm:ss tt' },
				 
                 
                ]
            });
        }
          
        function changeStatus(seq,isEnabled){
            $("#enableDiv"+seq).html("<i class='fa fa-spinner' aria-hidden='true' title='wait'>")
            url = "showM2MSites.php?call=changeStatus&editSeq="+seq+"&isEnabled="+isEnabled;
        	$.getJSON(url, function( data ) {
            	if(data.result == "success"){
                	if(isEnabled == "0"){
                		$("#enableDiv"+seq).html("<a href='javascript:changeStatus("+ seq + ', \"' +  1 + "\")' ><i class='fa fa-square-o' title='Enable'></a>")
                	}else{                		
                		$("#enableDiv"+seq).html("<a href='javascript:changeStatus("+ seq + ', \"' +  0 + "\")' ><i class='fa fa-check-square-o' title='Enable'></i></a>")
                	}		
            	}
        	});
           
        }
        function changeVisible(seq,isVisible){
        	$("#visibleDiv"+seq).html("<i class='fa fa-spinner' aria-hidden='true' title='wait'>")
            url = "showM2MSites.php?call=changeVisible&editSeq="+seq+"&isEnabled="+isVisible;
        	$.getJSON(url, function( data ) {
            	if(data.result == "success"){
                	if(isVisible == "0"){
                		$("#visibleDiv"+seq).html("<a href='javascript:changeVisible("+ seq + ', \"' +  1 + "\")' ><i class='fa fa fa-eye-slash' title='Visible'></i></a>")
                	}else{                		
                		$("#visibleDiv"+seq).html("<a href='javascript:changeVisible("+ seq + ', \"' +  0 + "\")' ><i class='fa fa-eye' title='InVisible'></i></a>")
                	}		
            	}
        	});
        } 
        function Edit(seq,locationSeq){
            document.folderForm.action = "createFolder.php"; 
            document.getElementById('locationSeq').value =  locationSeq                 
            document.getElementById('editSeq').value =  seq ;
            document.folderForm.submit();
        }
        function Delete(seq,path){ 
            var r=confirm("Do you really want to delete this folder.");
            if(r == true){ 
                document.folderForm.action = "showM2MSites.php";                   
                document.getElementById('editSeq').value =  seq ;
                document.getElementById('path').value =  path ;
                document.getElementById('formAction').value =  'delete' ; 
                document.folderForm.submit();
                
            }
        }
        
        function Edit(seq){ 
        document.folderForm.action = "createFolder.php";                   
        document.getElementById('editSeq').value =  seq ;
        document.folderForm.submit();
    }
        function Delete1(seq){ 
             bootbox.confirm("Are you sure?", function(result){ 
                if(result){
                     if(seq > 0){
                        $.ajax({
                            type: 'POST',
                            url: "showM2MSites.php",
                            data: {
                                call: "delete",
                                editSeq: seq,
                            },
                            complete: function () {
                               $("#jqxgrid").jqxGrid('updatebounddata');
                              showNotification("Deleted Successfully","success");
                            }
                        });  
                     }
                 } 
        });
        }
        function Delete(seq,folderName){
             deleteObj(seq,folderName,"Folder","showM2MSites.php");
        }
        
 </script>

