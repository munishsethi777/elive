 <?include("sessioncheck.php");
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//HighValueRuleDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//ChannelConfigurationDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//VirtualChannelConfigurationDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
  require_once($ConstantsArray['dbServerUrl'] . "Utils/DropDownUtils.php");
   
   $isSuccess = true;
   $messageText = "";
   $highValueRule = new HighValueRule();
   $HVRDS = HighValueRuleDataStore::getInstance();
   $FDS = FolderDataStore::getInstance();    
   $selectedChannel = "";
   if($_POST["editSeq"] <> "" ){
        $highValueRule = $HVRDS->FindBySeq($_POST["editSeq"]);   
        $virtualChannelSeq = $highValueRule->getVirtualChannelSeq();
        $selectedChannel = $highValueRule->getParameter();
        if(!empty($virtualChannelSeq)){
        	$selectedChannel = "0_" . $virtualChannelSeq;
        }
   }  
  $notify_checked = "";
  $notify = $highValueRule->getIsNotifyCpcb();
  if(!empty($notify)){
       $notify_checked = "checked";
  } 
  if($_POST["submit"]<>""){
      $folderSeq = $_POST["folder"];
      $folder = $FDS->FindBySeq($folderSeq);
      $emailIds = $_POST["emailIds"];      
      $mobileNos = $_POST["mobileNos"];
      $parameter = $_POST["channelNames"];
      $highValue = $_POST["highValue"]; 
      $frequency = $_POST["frequency"];      
      $lastRuleHitFileDataSeq = $_POST["lastRuleHitFileDataSeq"];
      
      $active = $_POST["active"];
      $seq = $_POST["seq"];
      $ruleType = $_POST["ruleType"];
      
      $isNotifyCpcb = 0;
      if(isset($_POST["notifyCpcb"])){
          $isNotifyCpcb = 1;
          $notify_checked = "checked";
      }
      $highValueRule->setSeq($seq);
      $highValueRule->setFolderSeq($folderSeq);
      $highValueRule->setEmail($emailIds);
      if(!empty($mobileNos)){
      		$mobileNos = str_replace(' ', '', $mobileNos); 
      }
      $highValueRule->setMobile($mobileNos);
      
      $highValueRule->setHighValue($highValue);
      $highValueRule->setStationType($folder->getStationType());
      $highValueRule->setIsActive($active);
      $highValueRule->setLastRuleHitFileDataSeq($lastRuleHitFileDataSeq);
      $highValueRule->setRuleHits(0);
      $highValueRule->setIsNotifyCpcb($isNotifyCpcb);
      $highValueRule->setRuleType($ruleType);
      $pos = strpos($parameter, '0_');
      $virtualChannelSeq = 0;
      if($pos !== false){
      	 $virtualChannelSeq = substr($parameter, $pos+2);
      	 $parameter = 0;
      }
      $highValueRule->setParameter($parameter);
      $highValueRule->setVirtualChannelSeq($virtualChannelSeq);
      
     //------------------------validations--------------------------------- 
    
    $messageText = "";    
    $div = "";
    if($folderSeq == 0){
        $messageText .= "- Station is Required<br>";    
    }
    $messageText .=  validator::validateform("Parameter",$parameter,256,false); 
    $messageText .=  validator::validateform("Highest Value",$highValue,256,false); 
       
     //------------------------------************----------------------------------
     
                                                                         
     
    //------------------------Show Validation or save object---------------------------------   
    if($messageText != null && $messageText != ""){
      $div = "         <div class='ui-widget'>
                       <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'> 
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
                               <strong>Error during save user details :</strong> <br/>" . $messageText . "</p>
                       </div></div>" ; 
                       $isSuccess = false;
    }else{ 
        $HVRDS->Save($highValueRule);
        $messageText = "High Value Rule Saved Successfully";
        $div = "<div class='ui-widget'>
                       <div  class='ui-state-default ui-corner-all' style='padding: 0 .7em;'> 
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
                               <strong>Message:</strong>&nbsp;" . $messageText . "</p> 
                       </div></div>";
        if(!empty($_POST["seq"])){
             header('Location: showHighValueRules.php');    
        }else{
             $highValueRule = new HighValueRule();    
        }               
       
    }
   
    //------------------------------************----------------------------------   
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
            <? include("leftButtons.php");
                  $locSeq = $managerSession['locSeq'];
                  $FDS = FolderDataStore::getInstance();
                  $LDS = LocationDataStore::getInstance();
                  $CCDS = ChannelConfigurationDataStore::getInstance();
                  $VCCDS = VirtualChannelConfigurationDataStore::getInstance();
                  $locationSeqs = $LDS->FindLocationsByUser($managerSession["seq"]);
                    if(!in_array($locSeq,$locationSeqs)){
                        array_push($locationSeqs,$locSeq);    
                    }
                    $folders = $FDS->FindByLocation(implode(",",$locationSeqs));
                  
                  $folDDown = DropDownUtils::getFoldersDropDownWithStationName($folders,"folder","changeStation()",$highValueRule->getFolderSeq());
                  $chDDown = "<h5>Select a Station to load Parameters</h5>";
                  if(!empty($selectedChannel)){
                    $channelConfigs = $CCDS->FindByFolder($highValueRule->getFolderSeq());
                    $virtualChannelConfigs = $VCCDS->FindByFolder($highValueRule->getFolderSeq());
                    if(!empty($virtualChannelConfigs)){
                    	 $channelConfigs = array_merge($channelConfigs,$virtualChannelConfigs);
                    }
                    $chDDown = DropDownUtils::getChannelsDropDown($channelConfigs,"channelNames","",$selectedChannel);
                  }     
                  $ruleTypeDD = DropDownUtils::getRuleTypeDropDown("ruleType", "", $highValueRule->getRuleType());
            ?>
            <div class="wrapper wrapper-content animated fadeInRight mainDiv">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5>Create HighValue Rule</h5>
                            </div>  
                            <div class="ibox-content">
                                 <form name="frm1" id="frm1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-horizontal">
                                 <input type="hidden" name="submit" value="submit"/>
                                 <input type="hidden" name="seq" id="seq" value="<?php echo ($highValueRule->getSeq());?>" / >
                                 <input type="hidden" name="lastRuleHitFileDataSeq" id="seq" value="<?php echo ($highValueRule->getLastRuleHitFileDataSeq());?>" / >
                                 <div class="form-group">
                                        <label class="col-lg-2 control-label">Station</label>
                                        <div class="col-lg-10">
                                            <? echo $folDDown; ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">Rule Type</label>
                                        <div class="col-lg-10">
                                            <? echo $ruleTypeDD; ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">Email id(s)</label>
                                        <div class="col-lg-10">
                                            <input type="text" name="emailIds" placeholder="Email" value="<?php echo($highValueRule->getEmail());?>" required="required" class="form-control"> 
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">Mobile No</label>
                                        <div class="col-lg-10">
                                            <input type="text" name="mobileNos" placeholder="Mobile" value="<?php echo($highValueRule->getMobile());?>" required="required" class="form-control"> 
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">Notify CPCB</label>
                                        <div class="col-lg-10">
                                            <input class="i-checks" type="checkbox"  <?php echo ($notify_checked)?>  name="notifyCpcb">
                                        </div>
                                    </div> 
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">Parameter</label>
                                        <div class="col-lg-10 parameterTD">
                                            <? echo $chDDown; ?> 
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">Value</label>
                                        <div class="col-lg-10">
                                            <input type="text" name="highValue" placeholder="Value" value="<?php echo($highValueRule->getHighValue());?>" required="required" class="form-control">  
                                        </div>
                                    </div>
                                    <div class="form-group">
                                       
                                        <div class="col-lg-offset-2 col-lg-10">
                                            <?php
                                               if($highValueRule->getIsActive() == "" || $highValueRule->getIsActive() == "1"){
                                                 $checked_On = "checked";
                                               }else{
                                                  $checked_Off = "checked"; 
                                               }   
                                            ?>
                                                    <label class="checkbox-inline i-checks"><input type="radio"  <?php echo ($checked_On)?> value="1" name="active"> <i></i>Active</label>
                                                 
                                                 
                                                    <label class="checkbox-inline i-checks"><input type="radio" <?php echo ($checked_Off); ?> value="0" name="active"> <i></i>InActive</label>
                                                    <br/><h5>The notification is sent basis on the average of last 15 min values</h5>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-lg-offset-2 col-lg-10">
                                            <button class="btn btn-primary" type="submit">Submit</button>
                                            <button class="btn btn-white" type="submit" onclick="cancel()">Cancel</button>
                                        </div>
                                    </div>     
                                 </form>
                            </div>
                        </div>
                    </div>
                </div>
             </Div>
        </Div>
    </body>
</html>
<script type="text/javascript">
    showResponseNotification("<?echo $isSuccess?>","<?echo $messageText ?>","mainDiv","frm1");
    function cancel(){
        location.href = "showHighValueRules.php"
    }
     $(document).ready(function () {
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });
     });
</script>