 <?include("sessioncheck.php");
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//ChannelGroupDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//ChannelConfigurationDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
  require_once($ConstantsArray['dbServerUrl'] . "Utils/DropDownUtils.php");
  require_once($ConstantsArray['dbServerUrl'] . "BusinessObjects/ChannelGroup.php");
      
  $isSuccess = true;
  $messageText = "";
  $channelGroup = new ChannelGroup();
  $channelGroup->setFolderSeq(0);
  $isSuccess = true;
  $messageText = "";  
  $CGDS = ChannelGroupDataStore::getInstance();
  $FDS = FolderDataStore::getInstance();
  $folder = new Folder();
  $selectedChannelSeqs= array();	
  if(isset($_POST["editSeq"]) && $_POST["editSeq"] <> "" ){
  	$channelGroup = $CGDS->FindBySeq($_POST["editSeq"]);
  	$selectedChannelSeqs = $channelGroup->getChannelSeqs();  	
  }
    
  if(isset($_POST["call"]) && $_POST["call"] == "save"){
  	$folderSeq = 0;
  	if(isset($_POST["folder"])){
  		$folderSeq = $_POST["folder"];
  	}else{
  		$folderSeq = $_POST["folderSeq"];
  	}
  	
  	$parentFolder = $FDS->FindBySeq($folderSeq);
  	$subStationName = $_POST["subStationName"];
  	$channelSeqs = $_POST["channelSeqs"];
  	$seq = $_POST["seq"];
  	$subFolderSeq = $_POST["subFolderSeq"];
  	$messageText .=  validator::validateform("Sub Station Name",$subStationName,256,false);
  	
  	
  	
  	//------------------------validations---------------------------------
  
  	$messageText = "";
  	$div = "";
  	if($folderSeq == 0){
  		$messageText .= "- Station is Required<br>";
  	}
  	
  	
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
	  	$locationSeq = $parentFolder->getLocationSeq();
	  	$folder->setStationName($subStationName);
	  	$folder->setParentSeq($folderSeq);
	  	$folder->setFolderName($subStationName);
	  	$folder->setDetails($subStationName);
	  	$folder->setLocationSeq($locationSeq);
	  	$folder->setSeq($subFolderSeq);
	  	$FDS->Save($folder);  	
	  	
	  	$subFolderSeq = $folder->getSeq();
	  	$channelGroup->setSeq($seq);
	  	$channelGroup->setName($subStationName);
	  	$channelGroup->setChannelSeqs($channelSeqs);
	  	$channelGroup->setFolderSeq($folderSeq);
	  	$channelGroup->setSubFolderSeq($subFolderSeq);
	  	$CGDS->Save($channelGroup);
  		$messageText = "Channel Group Saved Successfully";
  		$div = "<div class='ui-widget'>
                       <div  class='ui-state-default ui-corner-all' style='padding: 0 .7em;'>
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span>
                               <strong>Message:</strong>&nbsp;" . $messageText . "</p>
                       </div></div>";
  		if(!empty($_POST["seq"])){
  			header('Location: showChannelGroups.php');
  		}else{
  			$channelGroup = new $channelGroup();
  			$channelGroup->setFolderSeq(0);
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
            <?    include("leftButtons.php");
                  $locSeq = $managerSession['locSeq'];
                  $FDS = FolderDataStore::getInstance();
                  $LDS = LocationDataStore::getInstance();
                  $CCDS = ChannelConfigurationDataStore::getInstance();
                  $locationSeqs = $LDS->FindLocationsByUser($managerSession["seq"]);
                  if(!in_array($locSeq,$locationSeqs)){
                  	array_push($locationSeqs,$locSeq);    
                  }
                  if($isAdmin){
                  	$folders = $FDS->FindAllForAdmin();
                  }else{
                  	$folders = $FDS->FindByLoggedInUserOrMgr();
                  }
                  
                  $folDDown = DropDownUtils::getFoldersDropDownWithStationName($folders,"folder","changeStation()",$channelGroup->getFolderSeq());
                  $chDDown = "<h5>Select a Station to load Parameters</h5>";
                  if($channelGroup->getName() != null){
                    $channelConfigs = $CCDS->FindByFolder($channelGroup->getFolderSeq());
                    $chDDown = DropDownUtils::getChannelsMultiDropDown($channelConfigs,"channels","",$channelGroup->getName());
                  }     
            ?>
            
            <div class="wrapper wrapper-content animated fadeInRight mainDiv">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5>Create Channel Group</h5>
                            </div>  
                            <div class="ibox-content">
                                 <form name="frm1" id="frm1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-horizontal">
                                  <input type="hidden" name="call" id="call"/>       
								 <input type="hidden" name="folderSeq" id="folderSeq" value="<?php echo $channelGroup->getFolderSeq()?>"  >                                                            
                                 <input type="hidden" name="channelSeqs" id="channelSeqs"/>
                                 <input type="hidden" name="subFolderSeq" id="subFolderSeq" value="<?php echo $channelGroup->getSubFolderSeq()?>"  >
                              
                                 <input type="hidden" name="seq" id="seq" value="<?php echo $channelGroup->getSeq()?>"  >
                                
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">Parent Station</label>
                                        <div class="col-lg-10">
                                            <? echo $folDDown; ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">Sub Station Name</label>
                                        <div class="col-lg-10">
                                            <input type="text" name="subStationName" id="subStationName" placeholder="Sub Station Name" value="<?php echo $channelGroup->getName()?>" required="required" class="form-control"> 
                                        </div>
                                    </div>                                   
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">Select Channels</label>
                                        <div class="col-lg-10 parameterTD">
                                            <? echo $chDDown; ?> 
                                        </div>
                                    </div>                                    
                                    <div class="form-group">
                                        <div class="col-lg-offset-2 col-lg-10">
                                            <button class="btn btn-primary" type="button" onclick="submitForm()">Submit</button>
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
    	$("#dropdown").prop("disabled", false);  
    	if(<?php echo $channelGroup->getFolderSeq()?> > 0){
    		$("#folder").change();	
    		 $("#folder").prop("disabled", true);
    	}		      
     });
     
     function submitForm(){
         var vals = [];
         $( '#channels :selected' ).each( function( i, selected ) {
             vals[i] = $( selected ).val();
         });
         $("#channelSeqs").val(vals);
         $("#call").val("save");
         $("#frm1").submit();
     }
     
     function changeStation(){
         var folSeq = $("#folder").val();           
         $(".parameterTD").html("<img src='images/ajax.gif'> loading station channels...");
         $.getJSON("ajaxCalls.php?action=getAllChannelNamesNotInGroups&folSeq="+folSeq, function(json){   
        	 var values = "<?echo implode(",", $selectedChannelSeqs)?>";         
             var html = getMultiDropDown( json, "channels",values.length > 0); 
             $(".parameterTD").html(html);             
             $(".chosen-select").chosen({width:"100%"});            
             if(values.length > 0){
                 values = values.split(",");
                 $('.chosen-select').val(values).trigger("chosen:updated");
             }                    
         });
 	 }
 	 
     function getMultiDropDown(keyValueArray,$selectName,isEdit){
         var html = "<select required id ="+ $selectName +" name="+ $selectName +" multiple class='chosen-select form-control m-b "+ $selectName +"'>";
         $.each(keyValueArray, function(index, value){
             if(index != 0 && value != null) {
               chinfo = value.channelInfo;
               var disable = "";
               if(value.disabled && !isEdit){
            	   disable = "disabled";
               }
               html+= "<option " + disable  + "  value='"+ index +"'>"+ chinfo +"</option>";
             }
         });
         html += "</select>"
         return html;
     }
     
</script>