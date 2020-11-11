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
$enable = "";
$m2MSiteChecked = "";
$siteCode = "";
$siteCodeDisable = "disabled";
$m2mVendor = "";
if($_POST["submit"]<>""){
    $folderName = $_POST["folderName"];
    $details = $_POST["details"];
    $isVisible = isset($_POST["isvisible"]) ? 1 : 0;
    $isEnable = isset($_POST["isenable"]) ? 1 : 0;
    $folderSeq = $_POST["seq"];
    $editFolderName = $_POST["editFolderName"];
    $locationSeq = $_POST["l_DropDown"];
    $stationType = $_POST["s_DropDown"];
    $isM2MSite = isset($_POST["isM2MSite"]) ? true : false;
    $m2mVendor = $_POST["m2mVendor"];
    $locationName = "";
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
    $visible = $folder->getIsVisible() == 1 ? "checked" : "";
    $enable = $folder->getIsEnable() == 1 ? "checked" : "";
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
            if(!file_exists("../../Repository/" . $locationName)){
                 $messageText = "Parent Location does not exists";
            }
            if($folderSeq == "" && $messageText == ""){
            if(file_exists("../../Repository/" . $locationName . "/" . $folderName)){
               $messageText = "Folder with same name already exists on server";
            }else{
                mkdir("../../Repository/" . $locationName . "/" . $folderName);
                mkdir("../../Repository/" . $locationName . "/" . $folderName ."/latest");
            }
         }else{

            if($messageText == "") {
            $oldName = $editFolderName;
            if(!file_exists("../../Repository/" . $locationName . "/" . $folderName)){
                if(!file_exists("../../Repository/" . $locationName . "/" . $oldName)){
                    mkdir("../../Repository/" . $locationName . "/" . $oldName);
                }else{
                    rename("../../Repository/" . $locationName . "/" . $oldName , "../../Repository/" . $locationName . "/" . $folderName);
                }
            }
         }
        }
      }

    }catch(Exception $e){
        $logger = Logger::getLogger($ConstantsArray["logger"]);
        $logger->error("Error During Create Directory : - " . $e->getMessage());

    }


    if($messageText != null && $messageText != ""){
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
        $folder = $FDS->FindBySeq($_POST["editSeq"]);
        $locationSeq = $_POST["locationSeq"];
        $visible = $folder->getIsVisible() == "1" ? "checked" : 0;
        $enable = $folder->getIsEnable() == "1" ? "checked" : 0;
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

        <td class="ui-widget-header" style="padding:10px 10px 10px 10px;">Enter New Folder Details </td>
        </tr>
      <tr>
        <td class="ui-widget-content">
            <form name="frm1" method="post" action="createFolder.php">
            <input type="hidden" name="seq" id="seq" value="<?php echo ($folder->getSeq());?>" />
            <input type="hidden" name="editFolderName" id="editFolderName" value="<?php echo ($folder->getFolderName());?>" />
            <input type="hidden" name="locationName" id="locationName" value="<?php echo ($locationName);?>" />

                <table width="100%" border="0" style="padding:10px 10px 10px 10px;">
                    <tr>
                    <td width="22%">Location :</td>
                    <td width="78%">
                    <? echo DropDownUtils::getAllLocationsDropDown("l_DropDown","setLocation()",$locationSeq) ?>
                        </td>
                  </tr>
                  <tr>
                    <td width="22%">Folder Name :</td>
                    <td width="78%"><input name="folderName" type="text" size="50" value="<?php echo($folder->getFolderName());?>" >
                      &nbsp;</td>
                  </tr>
                  <tr>
                    <td>Folder Details :</td>
                    <td><textarea name="details" id="details" cols="38" rows="4" ><?php echo($folder->getDetails());?></textarea>
                      &nbsp;</td>
                  </tr>
                  <tr>
                    <td width="22%">Type :</td>
                    <td width="78%">
                        <? echo DropDownUtils::getFolderTypeDropDown("s_DropDown",$folder->getStationType()) ?>
                    </td>
                  </tr>
                  <tr>
                    <td width="22%">M2MSite :</td>
                    <td width="78%">
                        <input name="isM2MSite" id="isM2MSite" <?echo $m2MSiteChecked?> type="checkbox">
                        <span id="siteCodeSpan">
                            Site Code : <input name="siteCode" id="siteCode" <?echo $siteCodeDisable?>  type="text" size="10" value="<?php echo($siteCode);?>" >
                        </span>
                        Vendor
                        <?
                        echo DropDownUtils::getM2MVendorsDropDown("m2mVendor",$folder->getM2MVendor()) ?>
                    </td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>Enable <input name="isenable" value="true" <?echo $enable?> type="checkbox">
                        Visible <input name="isvisible" value="true" <?echo $visible?> type="checkbox"></td>
                  </tr>

                  <tr>
                    <td>&nbsp;</td>
                    <td>
                         <input type="submit" name="submit" value="Save"  >
                        <input type="reset" name="Reset" value="Reset"  >

                    </td>
                  </tr>

                </table>
              </form>
         </td>
        </tr>

    </table>

        <script language="javascript">
              function setLocation(){
                  var e = document.getElementById("l_DropDown");
                  var strLoc = e.options[e.selectedIndex].text;
                  document.getElementById('locationName').value =  strLoc ;
              }
             $( document ).ready(function(){
                $('#isM2MSite').change(function(){
                    $("#siteCode").prop("disabled", !$(this).is(':checked'));
                });
             })


        </script>







    </body>
</html>


