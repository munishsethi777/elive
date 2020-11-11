<?
require_once('IConstants.inc'); 
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");
require($ConstantsArray['dbServerUrl'] . "Utils//FileSystemUtils.php");      
require_once($ConstantsArray['dbServerUrl'] . "Utils/DropDownUtils.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//M2MSynchronizerDataStore.php");

$LDS = LocationDataStore ::getInstance();
$locations = $LDS->FindAll();
$locationSeq = $_POST["locationSeq"];
 
$FDS = FolderDataStore::getInstance();
if ($_POST["formAction"] == "changeStatus" ){
       $isEnabled  = $_POST["isEnabled"];
       $seq  = $_POST["editSeq"];
       $FDS->updateIsEnable($seq,$isEnabled);
       
       
}
if ($_POST["formAction"] == "delete" ){
    $FolderPath  = $_POST["path"];
    //FileSystemUtils::delete_NestedDirectory($FolderPath);
    $FDS->deleteBySeq($_POST['editSeq']);
    //$folders = $FDS->FindAll(); 
    
    
}else if($_POST["formAction"] == "populateRows" ) {
   $locationSeq = $_POST["locationSeq"];
   if($locationSeq <> "0" ){
     $folders = $FDS->FindByLocation($locationSeq);      
   }else{
       $folders = $FDS->FindAll(true); 
   }
   
}
    if(!empty($locationSeq)){
          $folders = $FDS->FindByLocation($locationSeq);    
    }else{
        $folders = $FDS->FindAll(true);
    }  


?>   
<!DOCTYPE html>
<html>
    <head>
    <? include("_jsAdminInclude.php");?>
    <script type="text/javascript">
        var showFolders_FoldersJSON = null;
        var showFolders_UsersJSON = null;
        var showFolders_UserDivId = 0;
        $(function(){
            buildShowUsersDialog(true);
            buildUsersJson();
            //buildUsersFoldersJsons();
        });
        
    </script>
    </head>
    <body>
    
    <? include("leftButtons.php");?>
    
    <Div class="rightAdminPanel">
        <? include("logOutButton.php"); ?>
    
         
    <table width="80%" border="0">
      <tr>
     
        <td>
            Select Location :
            <? echo DropDownUtils::getAllLocationsDropDown("l_DropDown","populateRows(this.value)",$locationSeq) ?>
            

         </td> 
        </tr>
      <tr>
        <td class="ui-widget-header" style="padding:10px 10px 10px 10px;">List of Available Location Folders</td>
        </tr>
      <tr>
        <td class="ui-widget-content">
          <form name="folderForm" method="post" action="" > 
            <input type="hidden" name="editSeq" id="editSeq" />
               <input type="hidden" name="formAction" id="formAction" />
               <input type="hidden" name="isEnabled" id="isEnabled" />
               <input type="hidden" name="path" id="path" />  
                <input type="hidden" name="locationSeq" id="locationSeq" value="<?echo $locationSeq?>" /> 
				<table width="100%" border="1" bordercolor="silver" style="border-style:dashed;border-width:thin;border:thin;border-color:#CCCCCC">
				  <tr>
                    <td width="2%" class="ui-widget-header">Status</td>
					<td width="12%" class="ui-widget-header">Folder</td> 
                    <td width="5%" class="ui-widget-header">M2MStation Code</td>                   
                    <td width="10%" class="ui-widget-header">Last Synched</td>
                    <td width="10%" class="ui-widget-header">Last Reminder</td>
					<td width="5%" class="ui-widget-header" align="center">Action</td>
				  </tr>
				  <? foreach($folders as $folder){
                      $synchedOnStr = "";
                      $parsedOnStr = "";
                      $remindedOnStr = "";
                      
                      if($folder->getLastSynchedOn()!= ""){
                            $lastSynchedOn = new DateTime($folder->getLastSynchedOn(), new DateTimeZone('Asia/Kolkata'));
                            $synchedOnStr = $lastSynchedOn->format('Y-m-d H:i:s');
                      }
                      if($folder->getLastParsedOn()!= ""){
                            $lastParsedOn = new DateTime($folder->getLastParsedOn(), new DateTimeZone('Asia/Kolkata'));
                            $parsedOnStr = $lastParsedOn->format('Y-m-d H:i:s');
                      }
                      if($folder->getLastSynchedOn()!= ""){
                            $lastRemindedOn = new DateTime($folder->getLastRemindedOn(), new DateTimeZone('Asia/Kolkata'));
                            $remindedOnStr = $lastRemindedOn->format('Y-m-d H:i:s');
                      }
                      
                      
                      $loationObj = new Location();
                      $loationObj = $locations[$folder->getLocationSeq()];
                      $locationName = $loationObj->getLocationFolder();
                      $fullPath = $ConstantsArray['rootURL']. "Repository/" . $locationName . "/" . $folder->getActualName();                      
                      $path = $ConstantsArray['rootURL']. "Repository/"   . $locationName . "/" . $folder->getFolderName() ;    
                      $title = "Assign Permissions on Folder (" . $folder->getFolderName() . ") location of (" . $locationName . ")";
                      $statusCol = "<tr><td><a href='javascript:changeStatus(". $folder->getSeq() ."," . "0)'  title='Click to Disable'>
                                <img src='images/tick.png' border='0'/>
                               </a></td>";
                      if( $folder->getIsEnable() == "0"){
                           $statusCol = "<tr><td><a href='javascript:changeStatus(". $folder->getSeq() .","."1)' title='Click to Enable'>
                                <img src='images/tick_grey.png' border='0'/>
                               </a></td>";
                      }  
                      $pathCol = "<a target='_blank' href='" . $fullPath . "'>" . $folder->getFolderName() ." (". $folder->getIsOnline() .")</a>";
                      $m2mSiteCode = $folder->getM2MCode();
                      if(!empty($m2mSiteCode)){
                          $pathCol = $folder->getFolderName() ." (". $folder->getIsOnline() .")";   
                      }
						  echo $statusCol .
                            "<td>" . $pathCol ."</td> 
                             <td>" . $folder->getM2MCode() ."</a></td>
                            <td>". $synchedOnStr ."</td>
                            <td>". $remindedOnStr ."</td>
                                
							<td align='center'>
                               <a href='javascript:Edit(". $folder->getSeq() . "," . $folder->getLocationSeq(). ")' title='Edit'>
                                <img src='images/edit.png' border='0'/>
                               </a>
                               <a href='javascript:Delete(". $folder->getSeq() . "," . '"' . $fullPath . '"' . ")' title='Delete'>
                                <img src='images/delete.png' border='0'/> 
                               </a>
                               <!--<a href='javascript:showUsers(".  $folder->getSeq() . "," . '"' . $title . '"' . ");' title='Permissions'><img src='images/users.png' border=0/></a>-->
                            </td>
						  </tr>";
				  	}
				  ?>
				</table>
               </form>
         </td>
        </tr>
    </table>
    </Div>
    <DIV id="showUsersDialog">
        <div id ="showUsersDialogData">
        <div id="showFolders_loadingAjax" style="display: none;"/>
           <img src='images/ajax.gif' />  
        </div>
        <Div class="msg"></div>
        
        <Div class="UserDivs"></div>
            <label id="folderSeqSelected" style="display:none"></label>
            
        </div>
    </DIV>
        <script language="javascript"> 
           function changeStatus(seq,isEnabled){
                document.folderForm.action = "showFolders.php";                   
                document.getElementById('editSeq').value =  seq ;
                document.getElementById('isEnabled').value =  isEnabled ;
                document.getElementById('formAction').value =  'changeStatus' ; 
                document.folderForm.submit();
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
                     document.folderForm.action = "showFolders.php";                   
                     document.getElementById('editSeq').value =  seq ;
                     document.getElementById('path').value =  path ;
                     document.getElementById('formAction').value =  'delete' ; 
                     document.folderForm.submit();
                 }
           }
           function populateRows(locationSeq){
                    document.getElementById('locationSeq').value =  locationSeq;      
                     document.folderForm.action = "showFolders.php";
                     document.getElementById('formAction').value =  'populateRows' ; 
                     document.folderForm.submit();
                
           }
       </script>
    
    </body>
</html>


