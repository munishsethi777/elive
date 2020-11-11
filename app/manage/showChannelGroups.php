<?php
 include("sessioncheck.php");
 $managerSession = $_SESSION["managerSession"];
 $isAdmin = $managerSession["isAdmin"];
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//ChannelGroupDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils/DropDownUtils.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils//StringUtils.php");
$msg = "";
$CGDS = ChannelGroupDataStore::getInstance();
$FDS = FolderDataStore::getInstance();
$LDS = LocationDataStore::getInstance();
$folders = $FDS->FindAllParents($isAdmin);
$folderSeq = $_GET["folderseq"];
if ($_POST["call"] == "delete"){
	$CGDS->deleteBySeq($_POST['editSeq']);
	$msg = StringUtils::getMessage("Channel Group","Group deleted successfully",false);
}
if($_GET["call"] == "getGroups"){
	if(!empty($folderSeq)){
		$folderSeqs =  $folderSeq;
	}else{
		$folderSeqs = array_map(create_function('$o', 'return $o->getSeq();'), $folders);
		$folderSeqs = implode(",",$folderSeqs);
	}
	$groups = $CGDS->FindArrByFolder($folderSeqs);
	$groups = json_encode($groups);
	echo $groups;
	return;
}?>
<!DOCTYPE html>
<html>
<head>
<? include("_jsAdminInclude.php");?>
        <?include("../_InspiniaInclude.php");?>
    </head>
    <body>
        <div id="wrapper">   
              <? include("leftButtons.php");
                $folDDown = DropDownUtils::getFoldersDropDownWithStationName($folders,"folder","loadGrid(this.value)",'');
              ?>
              <div class="wrapper wrapper-content animated fadeInRight">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5>List of Available Channel Groups</h5>
                            </div>
                            <div class="ibox-content">
                                <form method="post" role="form" name="cgForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-horizontal">                                     
                                    <input type="hidden" name="editSeq" id="editSeq"/> 
                                    <input type="hidden" name="call" id="call" /> 
                                    <div class="form-group">
                                        <label class="col-sm-1 control-label">Location</label>
                                        <div class="col-sm-5">
                                            <? echo $folDDown?>
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
    function Edit(seq){
         document.cgForm.action = "CreateChannelGroup.php";
         document.getElementById('editSeq').value =  seq ;
         document.cgForm.submit();
    }
    function Delete(seq){
         deleteObj(seq,"This","Channel Group","showChannelGroups.php");
    }
    $( document ).ready(function() {
            loadGrid(0);
    });
        
    function loadGrid(folderseq){
        var source =
        {
            datatype: "json",
            pagesize: 20,
            datafields: [
                { name: 'seq', type: 'integer' },   
                { name: 'subfolderseq', type: 'integer' },	                          
                { name: 'name', type: 'string' },
                { name: 'channelNames', type: 'string' },
                { name: 'actions', type: 'string' },
            ],
            url: 'showChannelGroups.php?call=getGroups&folderseq='+folderseq,
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
                var html = "<div style='text-align: center; margin-top:1px;font-size:18px'><a href='javascript:Edit("+ data['seq'] + ")' ><i class='fa fa-pencil-square-o' title='Edit'></i></a>";
                    html += "</div>";
                
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
            sortable: false,
            filterable: true,
            columnsresize: true,
            enabletooltips: true,
            theme: "energyblue",
            virtualmode: true,
            rendergridrows: function()
            {
                  return dataAdapter.records;     
            },               
            columns: [  
			  { text: 'Id',  datafield: 'subfolderseq', width: "5%" },             
              { text: 'Sub Station Name',  datafield: 'name', width: "42%" },
              { text: 'id', datafield: 'seq' , hidden:true},  
              { text: 'Channels',  datafield: 'channelNames', width: "45%"},
              { text: 'Actions',  datafield: 'action', width: "8%", renderer:columnrenderer,cellsrenderer:actions}
            ]
        });
    }
</script>
