<?
require_once('IConstants.inc'); 
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//UserDataStore.php");
require($ConstantsArray['dbServerUrl'] . "Utils//StringUtils.php"); 



$msg = "";
$UDS = UserDataStore::getInstance(); 
if ($_POST["formAction"] <> "" ){
    $UDS->deleteBySeq($_POST['editSeq']);
    $msg = StringUtils::getMessage("Location","User deleted successfully",false);   
}

$Users = $UDS->FindAllManagers();
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
            buildShowUsersDialog(false);
            buildFoldersJson();
        });
        
    </script> 
   
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
        <td class="ui-widget-header" style="padding:10px 10px 10px 10px;">List of Available Managers</td>
        </tr>
      <tr>
        <td class="ui-widget-content">
        
         <form name="userForm" method="post" action="" >
               <input type="hidden" name="editSeq" id="editSeq" />
               <input type="hidden" name="formAction" id="formAction" />                
                   
                <table width="100%" border="1" bordercolor="silver" style="border-style:dashed;border-width:thin;border:thin;border-color:#CCCCCC">
                  <tr>
                    <td width="20%" class="ui-widget-header">Location</td>
                    <td width="20%" class="ui-widget-header">User Name</td>
                    <td width="20%" class="ui-widget-header">Password</td>
                    <td width="35%" class="ui-widget-header">Email Id</td>
                    <td width="5%" class="ui-widget-header">Active</td>
                    <td align="center" width="10%" class="ui-widget-header">Action</td>
                  </tr>
                  <? foreach($Users as $user){
                         $isActive = "Off";
                         $title = "Assign Premission to user (" . $user->getUserName() . ")" ; 
                         if($user->getIsActive() == "1"){
                                 $isActive = "On";      
                          }
                          echo "<tr>
                            <td>". $user->getLocationName() ."</td>
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
        <div id="showFolders_loadingAjax" style="display: none;"/>
           <img src='images/ajax.gif' />  
        </div>
        <Div class="msg"></div>  
        <Div class="UserDivs"></div>
            <label id="folderSeqSelected" style="display:none"></label>
            
        </div>
    </DIV>
       <script language="javascript"> 
           function Edit(seq){ 
                 document.userForm.action = "CreateManagerForm.php";                   
                 document.getElementById('editSeq').value =  seq ;
                 document.userForm.submit();
           }
           function Delete(seq){ 
                 var r=confirm("Are you realy want to delete this user");
                 if(r == true){ 
                     document.userForm.action = "showManagers.php";                   
                     document.getElementById('editSeq').value =  seq ;
                     document.getElementById('formAction').value =  'delete' ; 
                     document.userForm.submit();
                 }
           }
       </script>
    </body>
</html>


