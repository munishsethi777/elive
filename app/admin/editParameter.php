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

$FDS = FolderDataStore::getInstance();
$CDS = ChannelConfigurationDataStore::getInstance();
$WSDS = WQDStackDataStore::getInstance();
$WFDS = WQDFileDataStore::getInstance();
$folders = $FDS->FindActiveAll();
$isdataExist = false;
$folder = new Folder();
$channlConfigs = array();
$selSeq = 0;
$errMsg = "";
$msg = "";
$disabledChNo = "disabled";
if($_POST["call"] == "delete"){
    $seq = $_POST["delSeq"];
    $CDS->DeleteBySeq(intval($seq));
}
if($_POST["submit"] == "Edit")
{
     $slectedFolder = $_POST["F_DropDown"];
     if($slectedFolder == "0"){
        $errMsg = "Please Select Folder";
     }
     if($errMsg != null && $errMsg != ""){
        
     }else{
         $selSeq = intval($slectedFolder);
         $channlConfigs = $CDS->FindByFolder($selSeq);
         $folder = $folders[$selSeq];
         if($folder->getStationType() == "stack" || $folder->getStationType() == "effluent"){
             $isdataExist = $WSDS->isStackDataExist($selSeq);
         }else{
             $isdataExist = $WFDS->isFileDataExist($selSeq);
         }
         
     }
} 

$dupNoChArr = array();
$chNoArr = array();
if($_POST["submit"] == "Update")
{
      $channalSeqs = $_POST["chseq"];
      $selSeq = intval($_POST["selectedFolderSeq"]);
      if($selSeq == 0){
          $errMsg = "Please Select folder and click on Edit.";
      }else{
         $folder = $folders[$selSeq];
         if($folder->getStationType() == "stack" || $folder->getStationType() == "effluent"){
             $isdataExist = $WSDS->isStackDataExist($selSeq);
         }else{
             $isdataExist = $WFDS->isFileDataExist($selSeq);
         }
          $channlConfigs = $CDS->FindByFolder($selSeq);  
          $chSeqs = $_POST["chseq"];
          $count = 1;
          foreach($chSeqs as $chseq){           
            if(!empty($chseq)){
                $chConfigObj = $channlConfigs[intval($chseq)];                                                            
            }else{
                 $chConfigObj = new ChannelConfiguration();
                 $chConfigObj->setFolderSeq($selSeq);                 
                 $chConfigObj->setChannelStatusFlag(1);                 
            }
            $chNo = $_POST["chno".$count];            
            $chName = $_POST["chName".$count];
            $chUnit = $_POST["chUnit".$count];
            $chSubStation = $_POST["substation".$count];
            $prescribedLimit = $_POST["prescribedlimit".$count];
            $chConfigObj->setChannelName($chName);
            $chConfigObj->setChannelNumber($chNo);
            $chConfigObj->setChannelUnit($chUnit);
            $chConfigObj->setChannelStation($chSubStation);
            if(empty($prescribedLimit)){
                $prescribedLimit = 0;
            }
            $chConfigObj->setPrescribedLimit($prescribedLimit);
            if(!empty($chseq)){
                $channlConfigs[intval($chseq)] = $chConfigObj;   
            }else{
                array_push($channlConfigs,$chConfigObj);
            }
            if(!empty($chNo)){
                 if(!in_array($chNo,$chNoArr)){
                    array_push($chNoArr,$chNo);    
                }else{
                    array_push($dupNoChArr,$chNo);
                }    
             }
           
            $errMsg .=  validator::validateNumeric("Row No. $count - Channel Number",$chNo,11,false);
            $errMsg .=  validator::validateform("Row No. $count - Channel Name",$chName,255,false);
            $errMsg .=  validator::validateform("Row No. $count - Channel Unit",$chUnit,10,false);
            $count ++;
          }
          
          if(count($dupNoChArr)> 0){
              $errMsg .= "Duplicate Channel Number(s) :- ". implode(",",$dupNoChArr);
          }
      }
      if(!empty($errMsg)){
          $div = " <div class='ui-widget'>
                   <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'>
                           <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span>
                           <strong>Error during Edit Meta</strong> <br/>" . $errMsg . "</p>
                   </div></div>" ;
      }else{
              $CDS->saveList($channlConfigs);
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

function checkChNoUniqueValidation(){
    $chSeqs = $_POST["chseq"];
    $chNumbers = array();
    $count = 1;
    foreach($chSeqs as $chseq){
         $chNo = $_POST["chno".$count];
         if(in_array($chNo,$chNumbers)){
             return false;
         }
         array_push($chNumbers,$chNo);
         $count++;
    }
    return true;
}
$isdataExist = false;
?>



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
                          <input type="submit" name="submit" value="Edit">
                      &nbsp;</td>
                  </tr>
                  </table>
              </form>
         </td>
        </tr>
      <tr>
        <td class="ui-widget-content">
            <form name="chform" id="chform" method="post" action="editParameter.php">
                <input type="hidden" name="selectedFolderSeq" value="<?echo$selSeq?>" >
                <input type="hidden" name="delSeq" id = "delSeq">
                <input type="hidden" name="call" id="call" >
                <table id="chTable" width="100%" border="0" style="padding:10px 10px 10px 10px;">
                 <tr>
                   <td><strong>Channel No</strong></td>
                   <td><strong>Channel Name</strong></td>
                   <td><strong>Channel Unit</strong></td>
                   <td><strong>Sub Station</strong></td>
                   <td><strong>Prescribed Limit</strong></td>
                 </tr>
                 <?$index = 0;?>
                  
                    <?foreach($channlConfigs as $ch){$index++;?>
                     <input name="chseq[]" type="hidden" value="<?echo $ch->getSeq()?>">       
                     <tr id="row<?echo $index?>">
                         <td><input name="chno<?echo $index?>" type="text" size="5" value="<?echo $ch->getChannelNumber()?>" ></td>
                         <td><input name="chName<?echo $index?>" type="text" size="20" value="<?echo $ch->getChannelName()?>" ></td>
                         <td><input name="chUnit<?echo $index?>" type="text" size="15" value="<?echo $ch->getChannelUnit()?>" ></td>
                         <td><input name="substation<?echo $index?>" type="text" size="15" value="<?echo $ch->getChannelStation()?>" ></td>
                          <td><input name="prescribedlimit<?echo $index?>" type="text" size="15" value="<?echo $ch->getPrescribedLimit()?>" ></td>
                         <?if(!$isdataExist){?>
                          <td><a href='javascript:Delete(<?echo $ch->getSeq()?>)' title='Delete'>
                            <img src='images/delete.png'  border='0'/> 
                          </a></td>
                          <?}?>
                     </tr>
                 <?}?>
                 </table>
                 <table style="padding:10px 10px 10px 10px;">
                   <tr>
                    <td>&nbsp;</td>
                    <td>
                        <input type="submit" name="submit" value="Update">
                        <input type="reset" name="Reset" value="Reset">
                        <?if(!$isdataExist){?>
                            <input style="float: right;" onclick="javascript:addRows()" type="button" name="addRow" value="Add">    
                        <?}?>
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
 <script language="javascript">
    var index = "<?echo $index?>"; 
    function addRows(){ 
        index = parseInt(index) + 1;       
        var html = '<tr id="row' + index + '"><input name="chseq[]" type="hidden"><td><input name="chno'+ index + '" type="text" size="5"></td>';
                    html +='<td><input name="chName'+ index + '" type="text" size="20"></td>';
                    html +='<td><input name="chUnit'+ index + '" type="text" size="15"></td>'
                    html +='<td><input name="substation'+ index + '" type="text" size="15"></td>'
                    html +='<td><input name="prescribedlimit'+ index + '" type="text" size="15"></td>'
                         
                    html +='<td><a href="javascript:Delete(0)" title=Delete><img src="images/delete.png"  border="0"/></a></td>'
                    html +='</tr>';
                    
       $('#chTable > tbody:last-child').append(html);
      
    }
    function Delete(seq){
        var r = confirm("Are you realy want to delete?");
        if(r){
            $('#row' + index).remove();
             index = parseInt(index) - 1 ;
             if(seq > 0){
                $("#call").val("delete");
                $("#delSeq").val(seq);
                $.ajax({
                type: 'POST',
                url: "editParameter.php",
                data: {
                    call: "delete",
                    delSeq: seq,
                },
                complete: function () {
                    //window.location.reload(true);
                }
});  
             }
         } 
    }
</script>