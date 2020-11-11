<?include("sessioncheck.php");
require_once('IConstants.inc'); 
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils/DropDownUtils.php");

$location = new Location();
$LDS = LocationDataStore::getInstance();
 $nameDisabled = "";
    if($_POST["editSeq"] <> "" ){
        $location = $LDS->FindBySeq($_POST["editSeq"]);
        $nameDisabled = "readonly";   
    }  
$isSuccess = false;
$messageText = "";

if($_POST["submit"]<>""){
    $locationName = $_POST["location"];
    $details = $_POST["details"];
    $locationSeq = $_POST["seq"];
    $folderName = $_POST["folderName"];
    $hasCreateDirStr = $_POST["isCreateDir"]; 
    $location->setLocationName($locationName);
    $isPrivateStr = $_POST["isPrivate"];
    $isPrivate = $isPrivateStr =="on" ? 1 : 0;
    $hasCreateDir= $hasCreateDirStr =="on" ? 1 : 0;
    $vendor = $_POST["vendor"];
    
    $location->setIsPrivate($isPrivate);
     if($locationName <> ""){
         $folderName =  strtolower($locationName);
         $folderName = str_replace(" ","_",$folderName);
     }
    $location->setLocationFolder($folderName);       
    $location->setSeq($locationSeq);
    $location->setLocationDetails($details);
    $location->setHasDirectory($hasCreateDir);
    $location->setRepositoryFolder($vendor);    
        
    $messageText = "";    
    $div = "";
    $messageText = validator::validateform("Location Name",$locationName,256,false);
    
    if($messageText == ""){
        $seq = $LDS->isExist($locationName);
           if($seq <> ""){
             if($locationSeq <> $seq) {
                $messageText = "Location is saved with same Location Name . please choose another Location name";
             }
           }
        
    }  
    if($messageText != null && $messageText != ""){
      $div = "         <div class='ui-widget'>
                       <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'> 
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
                               <strong>Error during save Location :</strong> <br/>" . $messageText . "</p>
                       </div></div>" ; 
    }else{
    	if(empty($locationSeq)){
	       try{
				
				$isCreateDir = "";
	          	$repositoryURL = "../../Repository/";
	          	if(!empty($vendor)){
	          		$repositoryURL .= $vendor ."/";
	          	}
	           	if(isset($_POST["isCreateDir"])){
	               $isCreateDir = "on";
	               if($messageText == ""){
	                    if(file_exists($repositoryURL . $folderName)){                             
	                    }else{
	                        mkdir($repositoryURL . $folderName);
	                    }
	               }        
	           	} 
	       }catch(Exception $e){
	           $logger = Logger::getLogger($ConstantsArray["logger"]);
	           $logger->error("Error During Create Directory : - " . $e->getMessage()); 
	       }
    	}
        
      
        $LDS = LocationDataStore::getInstance();
        $LDS->Save($location);
        $isSuccess = true;
        $messageText = "Location Saved Successfully";
        $div = "<div class='ui-widget'>
                       <div  class='ui-state-default ui-corner-all' style='padding: 0 .7em;'> 
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
                               <strong>Message:</strong>&nbsp;" . $messageText . "</p> 
                       </div></div>";
       $location = new Location();
    }  
    
   
} 
?>   
<!DOCTYPE html>
<html>
    <head>
        <?include("_jsAdminInclude.php");?>
        <?include("../_InspiniaInclude.php");?>
    </head>
    <body>
    <div id="wrapper">
        <? include("leftButtons.php");?>
        <div class="wrapper wrapper-content animated fadeInRight mainDiv">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Create Location</h5>
                        </div>
                        <div class="ibox-content">
                            <form method="post" role="form" name="frm1" id="frm1" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-horizontal">
                                <input type="hidden" name="seq" id="seq" value="<?php echo ($location->getSeq());?>" /> 
                                <input type="hidden" name="folderName" id="folderName" value="<?php echo ($location->getLocationFolder());?>" /> 
                                 <input type="hidden" name="submit" value="submit"/> 
                               <div class="form-group">
                                    <label class="col-lg-2 control-label">Location Name</label>
                                    <div class="col-lg-10">
                                        <input name="location" <?echo $nameDisabled?> type="text" placeholder="Location Name" required="required" class="form-control" value="<?php echo($location->getLocationName());?>" >
                                    </div>
                               </div>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Location Details</label>
                                    <div class="col-lg-10">                   
                                        <textarea name="details" id="details" placeholder="Location Details" class="form-control" cols="38" rows="4" ><?php echo($location->getLocationDetails());?></textarea>
                                    </div>
                               </div>
                            
                               
                               <div class="form-group">  
                                            <label class="col-lg-2 control-label">Vender</label>
                                            <div class="col-lg-5">
                                                <?echo DropDownUtils::getM2MVendorsDropDown("vendor",$location->getRepositoryFolder()) ?>
                                            </div>
                                         </div>
                                         
                               <? 
                                $checkedStr = $location->getHasDirectory() == 1 ? "checked" : '';
                               ?>
                               <div class="form-group">
                                    <div class="col-lg-offset-2 col-lg-10">     
                                         <label class="checkbox-inline i-checks"><input type="checkbox"  <?php echo ($checkedStr)?> name="isCreateDir"> <i></i>Create Directory</label>
                                    </div>
                               </div>
                               
                               
                               <div class="form-group">
                                    <div class="col-lg-offset-2 col-lg-10">
                                        <button class="btn btn-primary" type="submit">Save</button>
                                        <button class="btn btn-white" type="button" onclick="cancel()">Cancel</button>
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
<script type="text/javascript">
    showResponseNotification("<?echo $isSuccess?>","<?echo $messageText ?>","mainDiv","frm1");  
     $(document).ready(function () {
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });
     });
     function cancel(){
        location.href="showLocations.php";    
     }
</script>

