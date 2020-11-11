<?include("sessioncheck.php");
require_once('IConstants.inc'); 
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils//FileSystemUtils.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils//StringUtils.php"); 


$LDS = LocationDataStore ::getInstance(); 
$msg = ""; 
if (isset($_POST["call"]) && $_POST["call"] == "delete" ){
    $pathL = $_POST['locationPath'];
    $isLocationEmpty = FileSystemUtils::isDirEmpty($pathL);
    if($isLocationEmpty){
         $LDS->deleteBySeq($_POST['editSeq']);
          FileSystemUtils::delete_NestedDirectory($pathL);
         $msg = StringUtils::getMessage("Location","Location deleted successfully",false);  
    }else{
       $msg = StringUtils::getMessage("Delete Location","-The Location that you are trying to delete is related to folders.",true);
    }
   
    //Delte folder from location
}
    $call = $_GET["call"];
    if($call == "getLocations"){
        $locations = $LDS->FindAllArr();
        $locationJson = json_encode($locations);
        echo $locationJson;
        return;
    }
//$locations = $LDS->FindAll();
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
                                    <input type="hidden" name="locationPath" id="locationPath" /> 
                                    <input type="hidden" name="formAction" id="formAction" />
                                    <input type="hidden" name="locationSeq" id="locationSeq" />      
                                    <input type="hidden" name="editSeq" id="editSeq"/> 
                                    <input type="hidden" name="call" id="call" />                             
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
            function openFolder(path){
            alert(path) 
                 window.open(path,"Folder Path");
           }
           
            $( document ).ready(function() {
                loadGrid();
            });
           
           
            function loadGrid(locationSeq){
        var source =
        {
            datatype: "json",
            pagesize: 20,
            datafields: [
                { name: 'seq', type: 'integer' },
                { name: 'name', type: 'string' },
                { name: 'path', type: 'string' },
                { name: 'details', type: 'string' },            
                { name: 'actions', type: 'string' },
            ],
            url: 'showLocations.php?call=getLocations',
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
                    return '<div style="text-align: center; margin-top: 5px;"><i class="fa fa-check-square-o" ></i></div>';
                }
                else {
                    return '<div style="text-align: center; margin-top: 5px;"><i class="fa fa-square-o" ></i></div>';
                }    
             
        }
        var columnrenderer = function (value) {
                return '<div style="text-align: center; margin-top: 5px;">' + value + '</div>';       
        }
        var actions = function (row, columnfield, value, defaulthtml, columnproperties) {
                data = $('#jqxgrid').jqxGrid('getrowdata', row);
                var html = "<div style='text-align: center; margin-top: 5px;'><a href='javascript:Edit("+ data['seq'] + ")' ><i class='fa fa-pencil-square-o' title='Edit'></i></a>";
                    html += "</div>";
                
                return html;
        }
        var pathRenderer = function (row, columnfield, value, defaulthtml, columnproperties) {
                data = $('#jqxgrid').jqxGrid('getrowdata', row);
                var html = '<div style="margin-left: 5px;">';
                html += '<a target="_blank" href="'+value+'">'+data["name"]+'</a></div>';
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
              { text: 'id', datafield: 'seq' , hidden:true},
              { text: 'name', datafield: 'name' , hidden:true},
              { text: 'Location Name',  datafield: 'path', width: "40%",cellsrenderer: pathRenderer},
              { text: 'Details',  datafield: 'details', width: "50%"},                
              { text: 'Actions',  datafield: 'action', width: "10%", renderer:columnrenderer,cellsrenderer:actions}
            ]
        });
    }  
    function Edit(seq){ 
        document.userForm.action = "createLocation.php";                   
        document.getElementById('editSeq').value =  seq ;
        document.userForm.submit();
    }
    function Delete1(seq,objValue){ 
        swal({
                title: "Are you sure you want to delete " + objValue + " Location ?",
                text:  "You will not be able to recover " +  objValue + " Location !",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                closeOnConfirm: false
            }, function () {
                $.ajax({
                    type: 'POST',
                    url: "showLocations.php",
                    data: {
                        call: "delete",
                        editSeq: seq,
                        locationPath: objValue 
                    },
                    complete: function () {
                        $("#jqxgrid").jqxGrid('updatebounddata');
                        swal("Deleted!","Location Deleted Successfully.", "success"); 
                    }
                });  
                
        });
    }
    function Delete(seq,username){
         Delete1(seq,username);
    }
       </script>


