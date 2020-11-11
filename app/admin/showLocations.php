<?
require_once('IConstants.inc'); 
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php"); 
require($ConstantsArray['dbServerUrl'] . "Utils//FileSystemUtils.php");
require($ConstantsArray['dbServerUrl'] . "Utils//StringUtils.php"); 


$LDS = LocationDataStore ::getInstance(); 
$msg = ""; 
if ($_POST["formAction"] == "delete" ){     
    
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

$locations = $LDS->FindAll();
?>   
<!DOCTYPE html>
<html>
    <head>
    <? include("_jsAdminInclude.php");?>
    </head>
    <body>
    
    <? include("leftButtons.php");?>
    
    <Div class="rightAdminPanel">
        <? include("logOutButton.php"); ?>
    
         
    <table width="80%" border="0">
       <tr>       
            <td style="padding:10px 10px 10px 10px;"><?php echo($msg) ?></td>
       </tr>
      <tr>
        <td class="ui-widget-header" style="padding:10px 10px 10px 10px;">List of Available Location Folders</td>
        </tr>
      <tr>
        <td class="ui-widget-content">
          <form name="folderForm" method="post" action="" > 
            <input type="hidden" name="editSeq" id="editSeq" />
               <input type="hidden" name="locationPath" id="locationPath" /> 
               <input type="hidden" name="formAction" id="formAction" />
                <input type="hidden" name="locationSeq" id="locationSeq" />      
                <table width="100%" border="1" bordercolor="silver" style="border-style:dashed;border-width:thin;border:thin;border-color:#CCCCCC">
                  <tr>                     
                    <td width="15%" class="ui-widget-header">Location Name</td>
                    <td width="35%" class="ui-widget-header">Details</td>
                                        <td width="5%" class="ui-widget-header">Private</td>
                    <td  width="8%" class="ui-widget-header">Action</td>
                  </tr>
				 
                  <? foreach($locations as $location){
                      $privateStr = ((bool) $location->getIsPrivate()); 
                      $privateStr =  $privateStr ? 'True' : 'False';
                      $fullPath = $repositoryFullUrl['serverURL'] . $location->getLocationFolder();
                      $path = $ConstantsArray['rootURL']. "Repository/" . $location->getLocationFolder();;
                      $name = "<a target='_blank' href='" . $path . "'> ".  $location->getLocationName() . "</a>";
                      if($location->getHasDirectory() == 0){
                         $name = $location->getLocationName();    
                      }
                          echo "<tr>
                            <td>". $name . "</td>  
                            <td>". $location->getLocationDetails() ."</td>
                            <td>". $privateStr ."</td>
                            <td align='center'>
                               <a href='javascript:Edit(". $location->getSeq() . ")' title='Edit'>
                                <img src='images/edit.png' border='0'/>
                               </a>
                               <a href='javascript:Delete(". $location->getSeq() . "," . '"' . $fullPath . '"' . ")' title='Delete'>
                                <img src='images/delete.png' border='0'/> 
                               </a>                                 
                            </td>
                          </tr>";
                      }
                  ?>
                </table>
               </from>
         </td>
        </tr>
    </table>
    </Div>
    <DIV id="showUsersDialog">
        <div id ="showUsersDialogData">
        <div align="center" id ="showFolders_loadingAjax" style="display:none"><img src="images/ajax.gif"/></div>
        <Div class="UserDivs"></div>
            <label id="folderSeqSelected" style="display:none"></label>
            
        </div>
    </DIV>
        <script language="javascript"> 
           function Edit(seq,locationSeq){
                 document.folderForm.action = "CreateLocation.php"; 
                 document.getElementById('locationSeq').value =  locationSeq                 
                 document.getElementById('editSeq').value =  seq ;
                 document.folderForm.submit();
           }
           function Delete(seq,path){ 
                 var r=confirm("Do you realy want to delete this location");
                 if(r == true){ 
                     //document.folderForm.action = "showLocations.php";                   
//                     document.getElementById('editSeq').value =  seq ;
//                     document.getElementById('formAction').value =  'delete' ;
//                     document.getElementById('locationPath').value =  path;
//                     document.folderForm.submit();
                 }
           }
		    function openFolder(path){
            alert(path) 
                 window.open(path,"Folder Path");
           }
       </script>
    
    </body>
</html>



