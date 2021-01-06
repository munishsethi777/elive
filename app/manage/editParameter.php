
<?include("sessioncheck.php");
$msg="";
$emailMsg="";
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "admin//configuration.php");
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/UserDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/FolderDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/Utils/DropDownUtils.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/LocationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/BusinessObjects/Folder.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/ChannelConfigurationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/WQDStackDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/WQDFileDataStore.php");

//$configuration = new Configuration();
//Session_start();
$managerSession = $_SESSION["managerSession"];
$userDataStore = UserDataStore::getInstance();
$userSeq =  $managerSession['seq'];
$locSeq = $managerSession['locSeq'];
$FDS = FolderDataStore::getInstance();
$CDS = ChannelConfigurationDataStore::getInstance();
$LDS = LocationDataStore::getInstance();
$WSDS = WQDStackDataStore::getInstance();
$WFDS = WQDFileDataStore::getInstance();
$locationSeqs = $LDS->FindLocationsByUser($userSeq);
if($managerSession['username'] == "admin"){ //show all locations for Admin login
	$allLocations = $LDS->FindAll();
	$locationSeqs = array();
	foreach($allLocations as $location){
		array_push($locationSeqs,$location->getSeq());
	}
}else{
	if(!in_array($locSeq,$locationSeqs)){ //adding base location in location seqs
		array_push($locationSeqs,$locSeq);
	}
}
if(!in_array($locSeq,$locationSeqs)){
    array_push($locationSeqs,$locSeq);
}
$folders = $FDS->FindByLocation(implode(",",$locationSeqs));

$isSuccess = false;
$isdataExist = false;
$folder = new Folder();
$channlConfigs = array();
$selSeq = 0;
$errMsg = "";
$msg = "";
$disabledChNo = "disabled";
$messageText = "";
if(!empty($_POST["call"]) && ($_POST["call"] == "delete")){
    $seq = $_POST["delSeq"];
    $CDS->DeleteBySeq(intval($seq));
}
if(!empty($_POST["call"]) && ($_POST["call"] == "edit")){
     $slectedFolder = $_POST["F_DropDown"];
     if(!empty($slectedFolder)){
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

if(!empty($_POST["action"]) && ($_POST["action"] == "update"))
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
            if(!$chConfigObj){
                continue;
            }
            $chNo = $_POST["chno".$count];
            $chName = $_POST["chName".$count];
            $chRealName = $_POST["chRealName".$count];
            $chUnit = $_POST["chUnit".$count];
			$chDeviceId = $_POST["chDeviceId".$count];
            $chSubStation = $_POST["substation".$count];
            $prescribedLimit = $_POST["prescribedlimit".$count];
            $prescribedlowLimit = $_POST["prescribedlowlimit".$count];
            $isStatus = $_POST["isstatus".$count];
            $statusChNo = $_POST["statuschno".$count];
            $isVisible = $_POST["isVisible".$count];
            $isCpcb = $_POST["isCpcb".$count];
            $chConfigObj->setChannelName($chName);
            $chConfigObj->setChannelRealName($chRealName);
            $chConfigObj->setChannelNumber($chNo);
            $chConfigObj->setChannelUnit($chUnit);
            $chConfigObj->setChannelStation($chSubStation);
			$chConfigObj->setDeviceId($chDeviceId);
            if(empty($prescribedLimit)){
                $prescribedLimit = 0;
            }
            if(empty($prescribedlowLimit)){
            	$prescribedlowLimit = 0;
            }
            $chConfigObj->setPrescribedLowLimit($prescribedlowLimit);
            $chConfigObj->setPrescribedLimit($prescribedLimit);
            if($isVisible == "on"){
                $isVisible = 1;
            }else{
                $isVisible = 0;
            }
            if($isStatus == "on"){
                $isStatus = 1;
            }else{
                $isStatus = 0;
            }
            if($isCpcb == "on"){
                $isCpcb = 1;
            }else{
                $isCpcb = 0;
            }
            $chConfigObj->setIsVisible($isVisible);
            $chConfigObj->setIsCpcb($isCpcb);
            $chConfigObj->setIsStatus($isStatus);
            $chConfigObj->setStatusChNo($statusChNo);
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
           $messageText = $errMsg;
      }else{
              $CDS->saveList($channlConfigs);
              $folderSeq = intval($_POST["selectedFolderSeq"]);
              $channlConfigs = $CDS->FindByFolder($selSeq);
              $messageText="Parameters Updated Successfully.";
              $isSuccess = true;

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
?>
<html>
    <head>
        <? include("_jsAdminInclude.php");?>
        <?include("../_InspiniaInclude.php");?>
        <style type="text/css">
            .form-control{font-size:12px !important;padding:3px !important;}
            table td{padding:2px !important;}
        </style>
    </head>
    <body>
    <div id="wrapper">
        <? include("leftButtons.php");?>
        <div class="wrapper wrapper-content animated fadeInRight">
           <div class="messageDiv"></div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Edit Parameters</h5>
                        </div>
                        <div class="ibox-content">
                             <form name="form1" id="form1" method="post" action="editParameter.php">
                                <input type="hidden" name="call" id="call">
                                 <div class="form-group">
                                    <label class="col-sm-1 control-label">Station</label>
                                    <div class="col-sm-10">
                                        <? echo DropDownUtils::getFoldersDropDownWithStationName($folders,"F_DropDown","editParameters()",$selSeq) ?>
                                    </div>
                                </div>
                             </form>

                            <form name="chform" id="chform" method="post" action="editParameter.php" class="form-inline" style="font-size:12px">
                                <input type="hidden" name="action" id="action">
                                <input type="hidden" name="selectedFolderSeq" value="<?echo$selSeq?>" >
                                <input type="hidden" name="delSeq" id = "delSeq">
								
                                <table id="chTable" class="table" style='font-size:12px'>

                                     <thead>
                                         <tr>
                                           <th width="4%"><label>No.</label></th>
                                           <th width="10%">Channel Name</th>
                                           <th width="10%">Real Name</th>
                                           <th width="7%">Channel Unit</th>
                                           <th width="13%">Device Id</th>
                                           <th width="12%">Sub Station</th>
                                           <th width="5%">Prescribed Limit</th>
                                           <th width="5%">Prescribed Low Limit</th>
                                           <th width="4%">Status</th>
                                           <th width="6%">Status Ch No.</th>
                                           <th width="4%">Visible</th>
                                           <th width="4%">Cpcb</th>
                                         </tr>
                                     </thead>
                                     <tbody>
                                     <?$index = 0;?>

                                     <?foreach($channlConfigs as $ch){
                                         $index++;
                                         $checked = "";
                                         $checkedCpcb = "";
                                         $statusChecked = "";
                                         if($ch->getIsVisible() == 1){
                                            $checked = "checked";
                                         }
                                         if($ch->getIsCpcb()== 1){
                                             $checkedCpcb = "checked";
                                         }
                                         if($ch->getIsStatus() == 1){
                                             $statusChecked = "checked";
                                         }
                                         ?>
                                         <input name="chseq[]" type="hidden" value="<?echo $ch->getSeq()?>">
                                         <tr id="row<?echo $index?>">
                                             <td><input class="form-control" name="chno<?echo $index?>" type="text" size="1" value="<?echo $ch->getChannelNumber()?>" ></td>
                                             <td><input class="form-control" name="chName<?echo $index?>" type="text" size="10" value="<?echo $ch->getChannelName()?>" ></td>
                                             <td><input class="form-control" name="chRealName<?echo $index?>" type="text" size="10" value="<?echo $ch->getChannelRealName()?>" ></td>
                                             <td><input class="form-control" name="chUnit<?echo $index?>" type="text" size="6" value="<?echo $ch->getChannelUnit()?>" ></td>
                                             <td><input class="form-control" name="chDeviceId<?echo $index?>" type="text" size="15" value="<?echo $ch->getDeviceId()?>" ></td>
                                             <td><input class="form-control" name="substation<?echo $index?>" type="text" size="15" value="<?echo $ch->getChannelStation()?>" ></td>
                                             <td><input class="form-control" name="prescribedlimit<?echo $index?>" type="text" size="7" value="<?echo $ch->getPrescribedLimit()?>" ></td>
                                             <td><input class="form-control" name="prescribedlowlimit<?echo $index?>" type="text" size="7" value="<?echo $ch->getPrescribedLowLimit()?>" ></td>
                                             <td><input class="checkbox-inline i-checks" name="isstatus<?echo $index?>" type="checkbox" <?echo $statusChecked?>></td>
                                             <td><input class="form-control" name="statuschno<?php echo $index?>" type="text" size="1" value="<?echo $ch->getStatusChNo()?>" ></td>
                                             <td><input class="checkbox-inline i-checks" name="isVisible<?echo $index?>" type="checkbox" <?echo $checked?>></td>
                                             <td><input class="checkbox-inline i-checks" name="isCpcb<?echo $index?>" type="checkbox" <?echo $checkedCpcb?>></td>
                                             <td><a href='javascript:Delete(<?echo $ch->getSeq()?>)' title='Delete'>
                                                	<img src='images/delete.png'  border='0'/>
                                              </a></td>
                                              
                                              <td></td>
                                         </tr>
                                    	<?}?>
                                     </tbody>
                                 </table>
                                 
                                 <div class="form-group">
                                        <div class="col-lg-20">
                                            <button class="btn btn-primary" onclick="update()" type=“button”><i class="fa fa-check"></i>&nbsp;Update</button>
                                            <button class="btn btn-w-m btn-success" onclick="javascript:addRows()" type="button" onclick="cancel()">Add Row</button>
                                             <button class="btn btn-white" type="reset">Cancel</button>
                                        </div>
                                 </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </body>
</html>
 <script language="javascript">
    $(document).ready(function () {
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
        });
     });
    showResponseNotification("<?echo $isSuccess?>","<?echo $messageText ?>","messageDiv","chform");
    var index = "<?echo $index?>";

    function addRows(){
        index = parseInt(index) + 1;
        var html = '<tr id="row' + index + '"><input class="form-control"  name="chseq[]" type="hidden"><td><input class="form-control"  name="chno'+ index + '" type="text" size="1"></td>';
                    html +='<td><input class="form-control"  name="chName'+ index + '" type="text" size="10"></td>';
                    html +='<td><input class="form-control"  name="chRealName'+ index + '" type="text" size="10"></td>';
                    html +='<td><input class="form-control"  name="chUnit'+ index + '" type="text" size="6"></td>'
                    html +='<td><input class="form-control"  name="chDeviceId'+ index + '" type="text" size="15"></td>'
                    html +='<td><input class="form-control"  name="substation'+ index + '" type="text" size="15"></td>'
                    html +='<td><input class="form-control"  name="prescribedlimit'+ index + '" type="text" size="7"></td>'
                    html +='<td><input class="form-control"  name="prescribedlowlimit'+ index + '" type="text" size="7"></td>'
                    html += '<td><input class="checkbox-inline i-checks" name="isstatus'+ index + '" type="checkbox"></td>'
                    html +='<td><input class="form-control"  name="statuschno'+ index + '" type="text" size="1"></td>';
                    html += '<td><input class="checkbox-inline i-checks" name="isVisible'+ index + '" type="checkbox" checked></td>'
                    html +='<td><a href="javascript:Delete(0)" title=Delete><img src="images/delete.png"  border="0"/></a></td>'
                    html +='</tr>';

       $('#chTable > tbody:last-child').append(html);
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
        });
    }
    function editParameters(){
         $("#call").val("edit");
         $("form[name='form1']").submit();
    }
    function update(){
         $("#action").val("update");
         $("form[name='chform']").submit();
    }
    function Delete(seq){
        bootbox.confirm("Are you sure?", function(result) {
            if(result){
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
                            $("#jqxgrid").jqxGrid('updatebounddata');
                        }
                    });
                 }
             }
         });
    }
</script>