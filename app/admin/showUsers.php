<?
require_once('IConstants.inc'); 
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//UserDataStore.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");
      
require_once($ConstantsArray['dbServerUrl'] . "Utils/DropDownUtils.php");

$LDS = LocationDataStore ::getInstance();
$locations = $LDS->FindAll();

 
$UDS = UserDataStore::getInstance();
if (isset($_POST["formAction"]) && $_POST["formAction"] == "delete" ){
    $UDS->deleteBySeq($_POST['editSeq']);
    $users = $UDS->FindAll(); 
    
    
}else if(isset($_POST["formAction"]) && $_POST["formAction"] == "populateRows" ) {
   $locationSeq = $_POST["locationSeq"];
   if($locationSeq <> "0" ){
     $users = $UDS->FindUsersByLocSeqs($locationSeq);
   }else{
       $users = $UDS->FindAll();
   }   
}else{
    $users = $UDS->FindAll();  
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
               <input type="hidden" name="path" id="path" /> 
                <input type="hidden" name="locationSeq" id="locationSeq" />      
				  <table width="100%" border="1" bordercolor="silver" style="border-style:dashed;border-width:thin;border:thin;border-color:#CCCCCC">
                  <tr>
                    <td width="20%" class="ui-widget-header">User Name</td>
                    <td width="20%" class="ui-widget-header">Password</td>
                    <td width="35%" class="ui-widget-header">Email Id</td>
                    <td width="5%" class="ui-widget-header">Active</td>
                    <td align="center" width="10%" class="ui-widget-header">Action</td>
                  </tr>
                  <? foreach($users as $user){
                         $isActive = "Off";
                         $title = "Assign Premission to user (" . $user->getUserName() . ")" ; 
                         if($user->getIsActive() == "1"){
                                 $isActive = "On";      
                          }
                          echo "<tr>
                            <td>". $user->getUserName() ."</td>
                            <td>". SecurityUtil::Decode($user->getPassword())."</td>
                            <td>". $user->getEmailId() ."</td>
                            <td>". $isActive  ."</td> 
                            <td align='center'>
                               <a href='javascript:Edit(". $user->getSeq() . ")' title='Edit'>
                                <img src='images/edit.png' border='0'/>
                               </a>
                               <a href='javascript:Delete(". $user->getSeq() . ")' title='Delete'>
                                <img src='images/delete.png'  border='0'/> 
                               </a>
                                 </a>
                                 <!--<a href='javascript:showUsers(".  $user->getSeq() . "," . '"' . $title . '"' . ");' title='Permissions'><img src='images/users.png' border=0/></a>-->
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
        <div id="showFolders_loadingAjax"/>
           <img src='images/ajax.gif' />  
        </div>
        <Div class="msg"></div>
        
        <Div class="UserDivs"></div>
            <label id="folderSeqSelected" style="display:none"></label>
            
        </div>
    </DIV>
        <script language="javascript"> 
           function Edit(seq,locationSeq){
                 document.folderForm.action = "CreateUserForm.php"; 
                 document.getElementById('locationSeq').value =  locationSeq                 
                 document.getElementById('editSeq').value =  seq ;
                 document.folderForm.submit();
           }
           function Delete(seq,path){ 
                 var r=confirm("Do you really want to delete this user.");
                 if(r == true){ 
                     document.folderForm.action = "showUsers.php";                   
                     document.getElementById('editSeq').value =  seq ;
                     document.getElementById('path').value =  path ;
                     document.getElementById('formAction').value =  'delete' ; 
                     document.folderForm.submit();
                 }
           }
           function populateRows(locationSeq){
                    document.getElementById('locationSeq').value =  locationSeq;      
                     document.folderForm.action = "showUsers.php";
                     document.getElementById('formAction').value =  'populateRows' ; 
                     document.folderForm.submit();
                
           }
       </script>
    
    </body>
</html>


