<?include("sessioncheck.php");
$managerSession = $_SESSION["managerSession"];
$isAdmin = $managerSession["isAdmin"]; 
require_once('IConstants.inc'); 
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//UserDataStore.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils/DropDownUtils.php"); 
require($ConstantsArray['dbServerUrl'] . "Utils//StringUtils.php"); 
$msg = "";
$UDS = UserDataStore::getInstance();
$LDS = LocationDataStore::getInstance();
$seq = $managerSession['seq']; 
$locationSeq = $_GET["locationSeq"]; 
if ($_POST["call"] == "delete" ){
    $UDS->deleteBySeq($_POST['editSeq']);
    $msg = StringUtils::getMessage("Location","User deleted successfully",false);   
}
    $call = $_GET["call"];
    if($call == "getUsers"){
        if(!empty($locationSeq)){
            $Users = $UDS->FindAllUsersArr($locationSeq);    
        }else{
            if($isAdmin){
                $Users = $UDS->FindAllUsersByAdmin(); 
            }else{
                $locationSeqs = $LDS->FindLocationsByUser($seq);
                $lseq = $managerSession['locSeq'];
                if(!in_array($lseq,$locationSeqs)){
                    array_push($locationSeqs,$lseq);    
                }
                $Users = $UDS->FindAllUsersArr(implode(",",$locationSeqs));     
            }
            
        } 
        $userJson = json_encode($Users);
        echo $userJson;
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
          <div class="wrapper wrapper-content animated fadeInRight">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Show Users</h5>
                        </div>
                        <div class="ibox-content">
                            <form method="post" role="form" name="userForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-horizontal">
                                <input type="hidden" name="locationSeq" id="locationSeq" value="<?echo $locationSeq?>" /> 
                                <input type="hidden" name="editSeq" id="editSeq"/> 
                                <input type="hidden" name="call" id="call" /> 
                                <div class="form-group">
                                    <label class="col-sm-1 control-label">Location</label>
                                    <div class="col-sm-5">                                    
                                        <?if($isAdmin){
                                            echo DropDownUtils::getAllLocationsDropDown("l_DropDown","loadGrid(this.value)",$locationSeq,"All Locations");
                                        }else{ 
                                            echo DropDownUtils::getUserLocationsDropDown($seq,"l_DropDown","loadGrid(this.value)",$locationSeq,"All Locations");    
                                        }?> 
                                        
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
        var source =
        {
            datatype: "json",
            pagesize: 20,
            datafields: [
                { name: 'seq', type: 'integer' },
                { name: 'username', type: 'string' },
                { name: 'name', type: 'string' },
                { name: 'password', type: 'string' },
                { name: 'emailid', type: 'string' },
                { name: 'isactive', type: 'string' },
                { name: 'actions', type: 'string' },
            ],
            url: 'showUsers.php?call=getUsers&locationSeq='+locationSeq,
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
        var cellsrenderer = function (row, columnfield, value, defaulthtml, columnproperties) {
                if (value == 1) {
                    return '<div style="text-align: center; margin-top: 5px;"><i class="fa fa-check-square-o" title="Active"></i></div>';
                }
                else {
                    return '<div style="text-align: center; margin-top: 5px;"><i class="fa fa-square-o" title="InActive"></i></div>';
                }
        }
        var columnrenderer = function (value) {
                return '<div style="text-align: center; margin-top: 5px;">' + value + '</div>';       
        }
        var actions = function (row, columnfield, value, defaulthtml, columnproperties) {
                data = $('#jqxgrid').jqxGrid('getrowdata', row);
                var html = "<div style='text-align: center; margin-top: 5px;'><a href='javascript:Edit("+ data['seq'] + ")' ><i class='fa fa-pencil-square-o' title='Edit'></i></a>";
                    html += "<span style='margin-left:5px;'><a href='javascript:Delete("+ data['seq'] + ', \"' +  data['name'] + "\")' ><i class='fa fa-times' title='Delete'></i></a></span></div>";
                
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
              { text: 'Active',  datafield: 'isactive', width: "8%",renderer:columnrenderer,cellsrenderer: cellsrenderer},  
              { text: 'id', datafield: 'seq' , hidden:true},
              { text: 'User Name',  datafield: 'username', width: "50%"},
              { text: 'name',  datafield: 'name', hidden:true},  
              { text: 'Password',  datafield: 'password', width: "17%" },
              { text: 'Email',  datafield: 'emailid', width: "15%" },
              { text: 'Actions',  datafield: 'action', width: "10%", renderer:columnrenderer,cellsrenderer:actions}
            ]
        });
    }  
    function Edit(seq){ 
        document.userForm.action = "createUserForm.php";                   
        document.getElementById('editSeq').value =  seq ;
        document.userForm.submit();
    }
    function Delete1(seq){ 
         bootbox.confirm("Are you sure?", function(result){ 
            if(result){
                 if(seq > 0){
                    $.ajax({
                        type: 'POST',
                        url: "showUsers.php",
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
    function Delete(seq,username){
         deleteObj(seq,username,"User","showUsers.php");
    }
</script>
