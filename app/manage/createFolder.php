<?
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//M2MSynchronizerDataStore.php");

require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils/DropDownUtils.php");



   $folder = new Folder();
   $FDS = FolderDataStore::getInstance();
   $LDS = LocationDataStore ::getInstance();
   $m2mDs = M2MSynchronizerDataStore::getInstance();
   $locations = $LDS->FindAll();


$visible = "";
$nameDisabled = "";
$enable = "";
$opacity = "";
$m2MSiteChecked = "";
$siteCode = "";
$siteCodeDisable = "disabled";
$m2mVendor = "";
$isSuccess = "";
$repositoryURL = "../../Repository/";

if($_POST["submit"] <> ""){
    $folderName = $_POST["folderName"];
    $details = $_POST["details"];
    $isVisible = isset($_POST["isvisible"]) ? 1 : 0;
    $isEnable = isset($_POST["isenable"]) ? 1 : 0;
    $isOpacity= isset($_POST["isopacity"]) ? 1 : 0;
    $folderSeq = $_POST["seq"];
    $editFolderName = $_POST["editFolderName"];
    $locationSeq = $_POST["l_DropDown"];
    $stationType = $_POST["s_DropDown"];
    $isM2MSite = isset($_POST["isM2MSite"]) ? true : false;
    $m2mVendor = $_POST["m2mVendor"];
    $parserType = $_POST["parserType"];
    $cameraUrl = $_POST["cameraurl"];
    $locationName = "";
    $locationObj = null;
    if($locationSeq <> ""  && $locationSeq <> "0"){
        $locationObj = $locations[$locationSeq];
        $locationName = $locationObj->getLocationName();
    }

    $folder->setLocation($location);

    if($folderName <> ""){
         $folderName =  strtolower($folderName);
         $folderName = str_replace(" ","_",$folderName);
     }
      if($locationName <> ""){
         $locationName =  strtolower($locationName);
         $locationName = str_replace(" ","_",$locationName);
      }

    $folder->setFolderName($folderName);
    $folder->setSeq($folderSeq);
    $folder->setDetails($details);
    $folder->setLocationSeq($locationSeq);
    $folder->setIsVisible($isVisible);
    $folder->setIsEnable($isEnable);
    $folder->setStationType($stationType);
    $folder->setParserType($parserType);
    $folder->setIsOpacity($isOpacity);
    $folder->setStationVendor($m2mVendor);
    $folder->setCameraUrl($cameraUrl);
    if(!empty($locationObj->getRepositoryFolder())){
    	$repositoryURL .= $locationObj->getRepositoryFolder()."/";
    }
    $visible = $folder->getIsVisible() == 1 ? "checked" : "";
    $enable = $folder->getIsEnable() == 1 ? "checked" : "";
    $opacity = $folder->getIsOpacity() == 1 ? "checked" : "";
    $m2MSiteChecked = $isM2MSite ? "checked" : "";

    $messageText = "";
    $div = "";

     if($locationSeq == "0"){
         $messageText = "- Please select location<br>";
     }

     $messageText .= validator::validateform("Folder Name",$folderName,256,false);
     if($isM2MSite){
        $siteCodeDisable = '';
        $siteCode = $_POST["siteCode"];
        $messageText .= validator::validateform("Site Code",$siteCode,100,false);

     }

    if($messageText == ""){
        $seq = $FDS->folerExistWithLocation($locationSeq,$folderName);
             if($seq <> ""){
                 if($folderSeq <> $seq)
                 $messageText = "Folder Name already exists under this location . Please choose another folder name or location.";
             }
    }
    try{
        if(!$isM2MSite){
           
            if($messageText == ""){
            if(file_exists($repositoryURL . $locationName . "/" . $folderName)){
               
            }else{
            	
                mkdir($repositoryURL . $locationName . "/" . $folderName);
                mkdir($repositoryURL . $locationName . "/" . $folderName ."/latest");
            }
         }else{
        }
      }

    }catch(Exception $e){
        $logger = Logger::getLogger($ConstantsArray["logger"]);
        $logger->error("Error During Create Directory : - " . $e->getMessage());

    }


    if($messageText != null && $messageText != ""){
        $isSuccess = false;
      $div = "         <div class='ui-widget'>
                       <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'>
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span>
                               <strong>Error during save folder details :</strong> <br/>" . $messageText . "</p>
                       </div></div>" ;
    }else{

        $FDS = FolderDataStore::getInstance();
        $FDS->Save($folder);
        if($isM2MSite){
            $m2mSite = new M2MSite();
            $m2mSite->setFolderSeq($folder->getSeq());
            $m2mSite->setLastSyncedOn(date("Y/m/d H:i:s"));
            $siteCode = $_POST["siteCode"];
            $m2mSite->setSiteCode($_POST["siteCode"]);
            $m2mSite->setVendor($m2mVendor);
            $m2mDs->saveM2MSite($m2mSite);
        }else{
            $m2mDs->delete($folder->getSeq());
        }
        $messageText = "Folder Details Saved Successfully";
        $isSuccess = true;
        $div = "<div class='ui-widget'>
                       <div  class='ui-state-default ui-corner-all' style='padding: 0 .7em;'>
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span>
                               <strong>Message:</strong>&nbsp;" . $messageText . "</p>
                       </div></div>";
       $folder = new Folder();
       header("Location: showFolders.php");
    }


}

    if($_POST["editSeq"] <> "" ){
        $nameDisabled = "readonly";
        $folder = $FDS->FindBySeq($_POST["editSeq"]);
        $locationSeq = $_POST["locationSeq"];
        $visible = $folder->getIsVisible() == "1" ? "checked" : 0;
        $enable = $folder->getIsEnable() == "1" ? "checked" : 0;		$opacity = $folder->getIsOpacity() == 1 ? "checked" : "";
        $siteCode = $folder->getM2MCode();
        if(!empty($siteCode)){
            $m2MSiteChecked = "checked";
            $siteCodeDisable = '';
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
                                <h5>Create Folder</h5>
                            </div>
                        <div class="ibox-content">
                            <form method="post" role="form" name="frm1" id="frm1" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-horizontal">
                                <input type="hidden" name="seq" id="seq" value="<?php echo ($folder->getSeq());?>" />
                                <input type="hidden" name="submit" id="submit" value="submit" >  
                                <input type="hidden" name="editFolderName" id="editFolderName" value="<?php echo ($folder->getFolderName());?>" />
                                <input type="hidden" name="locationName" id="locationName" value="<?php echo ($locationName);?>" />
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Location</label>
                                    <div class="col-sm-10">
                                        <? echo DropDownUtils::getAllLocationsDropDown("l_DropDown","setLocation()",$folder->getLocationSeq()) ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Folder Name</label>
                                    <div class="col-lg-10">
                                        <input name="folderName" <?echo $nameDisabled?> placeholder="Folder Name" required="required" type="text" class="form-control" value="<?php echo($folder->getFolderName());?>" >
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Folder Details</label>
                                    <div class="col-lg-10">
                                        <textarea name="details" id="details" class="form-control" cols="38" rows="4" ><?php echo($folder->getDetails());?></textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Station Type</label>
                                    <div class="col-lg-10">
                                        <? echo DropDownUtils::getFolderTypeDropDown("s_DropDown",$folder->getStationType()) ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Camera URL</label>
                                    <div class="col-lg-10">
                                        <input name="cameraurl" placeholder="Camera URL" type="text" class="form-control" value="<?php echo($folder->getCameraUrl());?>" >
                                    </div>
                                </div>
                                <div class="form-group">  
                                    <div class="col-lg-offset-2 form-inline">
                                         <div class="checkbox m-l m-r-xs">                                          
                                            <label class="i-checks"><input name="isM2MSite" id="isM2MSite"  <?echo $m2MSiteChecked?>  type="checkbox"><i></i> M2Msite </label>
                                         </div>
                                         <div class="form-group">
                                            <label class="col-lg-5 control-label">Site Code</label>
                                            <div class="col-lg-8">
                                                <input name="siteCode" id="siteCode" value="<?echo $folder->getM2MCode()?>" <?echo $siteCodeDisable?> required="required" type="text" class="form-control" >
                                            </div>
                                         </div> 
                                         <div class="form-group">  
                                            <label class="col-lg-2 control-label">Vender</label>
                                            <div class="col-lg-8">
                                                <?echo DropDownUtils::getM2MVendorsDropDown("m2mVendor",$folder->getM2MVendor()) ?>
                                                 
                                            </div>
                                         </div>
                                          <div class="form-group">
                                          		<div class="col-lg-10">
                                        			<label class="i-checks"><input name="isopacity" value="true" <?echo $opacity?>  type="checkbox"><i></i> Opacity </label>
                                     			 </div>
                                     	  </div>
                                       
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Parser Type</label>
                                    <div class="col-sm-10">
                                        <?echo DropDownUtils::getParserTypesDropDown("parserType","",$folder->getParserType()) ?>
                                    </div>
                                </div>
                                 <div class="form-group">  
                                    <div class="col-lg-offset-2 form-inline">
                                     <div class="checkbox m-l m-r-xs">
                                        <label class="i-checks"><input name="isenable" value="true" <?echo $enable?>  type="checkbox"><i></i> Enable </label>
                                     </div> 
                                     <div class="checkbox m-l m-r-xs">
                                        <label class="i-checks"><input name="isvisible" value="true" <?echo $visible?>  type="checkbox"><i></i> Visible </label>
                                     </div> 
                                    </div>
                                 </div>
                                 <div class="form-group">
                                    <div class="col-lg-offset-2 col-lg-10">
                                        <button class="btn btn-primary" type="submit">Save</button>
                                        <button class="btn btn-white" type="reset" >Reset</button>
                                    </div>
                               </div>
                            </form>           
                        </div>
                     </div>
                </div>
            </div>
   
<script language="javascript">
       showResponseNotification("<?echo $isSuccess?>","<?echo $messageText ?>","mainDiv","frm1");  
      function setLocation(){
          var e = document.getElementById("l_DropDown");
          var strLoc = e.options[e.selectedIndex].text;
          document.getElementById('locationName').value =  strLoc ;
      }
     $( document ).ready(function(){
        $('#isM2MSite').on('ifChanged', function(event){
           var isChecked =  $('#isM2MSite').is(':checked'); 
           $("#siteCode").prop("disabled", !isChecked);
        });
     
     })
     function handleSiteCode(value){
        $("#siteCode").prop("disabled", !value);
     }
     $(document).ready(function () {
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });
     });

</script>







    </body>
</html>


