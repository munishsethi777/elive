<?
$msg="";
$emailMsg="";
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "admin//configuration.php");
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/UserDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/FolderDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/Utils/DropDownUtils.php");
require_once($ConstantsArray['dbServerUrl'] . "/BusinessObjects/Folder.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/ChannelConfigurationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/WQDStackDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/WQDFileDataStore.php");

//$configuration = new Configuration();
Session_start();
$managerSession = $_SESSION["managerSession"];
$userDataStore = UserDataStore::getInstance();
$userSeq =  $managerSession['seq'];
$locSeq = $managerSession['locSeq'];
$FDS = FolderDataStore::getInstance();
$CDS = ChannelConfigurationDataStore::getInstance();
$WSDS = WQDStackDataStore::getInstance();
$WFDS = WQDFileDataStore::getInstance();
$folders = $FDS->FindByLocation($locSeq);
$isdataExist = false;
$folder = new Folder();
$channlConfigs = array();
$selSeq = 0;
$disabledChNo = "disabled";
if($_POST["submit"] == "Edit")
{
     $slectedFolder = $_POST["F_DropDown"];
     if($slectedFolder == "0"){
        $msg = "Please Select Folder";
     }
     if($msg != null && $msg != ""){
        $div = "         <div class='ui-widget'>
                   <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'>
                           <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span>
                           <strong>Error during Edit Meta</strong> <br/>" . $msg . "</p>
                   </div></div>" ;
     }else{
         $selSeq = intval($slectedFolder);
         $channlConfigs = $CDS->FindByFolder($selSeq);
         $folder = $folders[$selSeq];
         if($folder->getStationType() == "stack"){
             $isdataExist = $WSDS->isStackDataExist($selSeq);
         }else{
             $isdataExist = $WFDS->isFileDataExist($selSeq);
         }
         if(!$isdataExist){
            $disabledChNo = "";
         }
     }
} 
if($_POST["submit"] == "Update")
{
      $channalSeqs = $_POST["chseq"];
      $selSeq = intval($_POST["selectedFolderSeq"]);
      if($selSeq == 0){
            $div = "         <div class='ui-widget'>
                   <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'>
                           <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span>
                           <strong>Error during Edit Meta</strong> <br/>Please Select folder and click on Edit.</p>
                   </div></div>" ;    
      }else{
          $channlConfigs = $CDS->FindByFolder($selSeq);    
          foreach($channalSeqs as $chseq){
            $chConfigObj = $channlConfigs[$chseq];
            $chName = $_POST["chName".$chseq];
            $chUnit = $_POST["chUnit".$chseq];
            $chSubStation = $_POST["substation".$chseq];
            $prescribedLimit = $_POST["prescribedlimit".$chseq];
            $chConfigObj->setChannelName($chName);
            $chConfigObj->setChannelUnit($chUnit);
            $chConfigObj->setChannelStation($chSubStation);
            if(empty($prescribedLimit)){
                $prescribedLimit = 0;
            }
            $chConfigObj->setPrescribedLimit($prescribedLimit);
            $CDS->updateParameters($chConfigObj);
          }
          $folderSeq = intval($_POST["selectedFolderSeq"]);
          $channlConfigs = $CDS->FindByFolder($selSeq);    
          $msg="Parameters Updated Successfully.";
             $div = "<div class='ui-widget'>
                       <div  class='ui-state-default ui-corner-all' style='padding: 0 .7em;'>
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span>
                               <strong>Message:</strong>&nbsp;" . $msg . "</p>
                       </div></div>";       
      }
         
}
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
        <td style="padding:10px 10px 10px 10px;"><?php echo($div) ?></td>
       </tr>
      <tr>

        <td class="ui-widget-header" style="padding:10px 10px 10px 10px;">Edit Parameters</td>
        </tr>
         <tr>
        <td class="ui-widget-content">
            <form name="frm1" method="post" action="editParameter.php">
                <table width="100%" border="0" style="padding:10px 10px 10px 10px;">

                  <tr>
                    <td width="22%">Select Folder</td>
                    <td width="78%">                    
                        <? echo DropDownUtils::getFoldersDropDownWithStationName($folders,"F_DropDown","setLocation()",$selSeq) ?>
                      &nbsp;</td>
                  </tr>
                  </table>
              </form>
         </td>
        </tr>
      <tr>
        <td class="ui-widget-content">
            <form name="frm1" method="post" action="editParameter.php">
                <input type="hidden" name="selectedFolderSeq" value="<?echo$selSeq?>" >
                <table width="100%" border="0" style="padding:10px 10px 10px 10px;">
                 <tr>
                   <td><strong>Channel No</strong></td>
                   <td><strong>Channel Name</strong></td>
                   <td><strong>Channel Unit</strong></td>
                   <td><strong>Sub Station</strong></td>
                   <td><strong>Prescribed Limit</strong></td>
                 </tr>
                 
                        <input name="chseq[]" type="hidden" value="<?echo $ch->getSeq()?>">
                     <tr>
                         <td><input name="chno[]" <?echo $disabledChNo?> type="text" size="5" value="<?echo $ch->getChannelNumber()?>" ></td>
                         <td><input name="chName[]" type="text" size="20" ></td>
                         <td><input name="chUnit[]" type="text" size="15" ></td>
                         <td><input name="substation[]" type="text" size="15"></td>
                         <td><input name="prescribedlimit[]>" type="text" size="15"></td>
                         <td><a href='javascript:Delete(. $ch->getSeq() . ")' title='Delete'>
                            <img src='images/delete.png'  border='0'/> 
                          </a></td>                          
                     </tr>       
                
                 
                
                   <tr>
                    <td>&nbsp;</td>
                    <td>
                        <input type="submit" name="submit" value="Update">
                        <input type="reset" name="Reset" value="Reset">
                            <input style="float: right;" type="button" name="addRow" value="Add">
                    </td>
                  </tr>
                </table>
              </form>
         </td>
        </tr>

    </table>





    </Div>



    </body>
</html>
