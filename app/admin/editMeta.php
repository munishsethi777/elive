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



//$configuration = new Configuration();
Session_start();
$FDS = FolderDataStore::getInstance();
$folders = $FDS->FindActiveAll();
$folder = new Folder();
$selSeq = 0;
if($_POST["submit"] == "Edit")
{
     $slectedFolder = $_POST["F_DropDown"];
     if($slectedFolder == "0"){
        $msg = "Please Select Folder.";
     }
     if($msg != null && $msg != ""){
        $div = "         <div class='ui-widget'>
                   <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'>
                           <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span>
                           <strong>Error during Edit Meta</strong> <br/>" . $msg . "</p>
                   </div></div>" ;
     }else{
         $selSeq = intval($slectedFolder);
         $folder = $folders[$selSeq];
     }
} 
if($_POST["submit"] == "Update"){    
    $folderSeq = intval($_POST["selectedFolderSeq"]);
    if($folderSeq == 0){
        $div = "<div class='ui-widget'>
                   <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'>
                           <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span>
                           <strong>Error during Edit Meta</strong> <br/>Select Folder and click on Edit.</p>
                   </div></div>" ;
    }else{
        $selSeq  = $folderSeq;
        $folder = $folders[$folderSeq];
        $folder->setCategory($_POST["category"]); 
        $folder->setIndustryCode($_POST["indusCode"]); 
        $folder->setIndustryName($_POST["indusName"]);
        $folder->setAddress($_POST["address"]);
        $folder->setCity($_POST["city"]); 
        $folder->setState($_POST["state"]);
        $folder->setZipcode($_POST["zipCode"]);
        $folder->setLongitude($_POST["longitude"]); 
        $folder->setLatitude($_POST["latitude"]); 
        $folder->setEmail($_POST["email"]);
        $folder->setMobile($_POST["mobile"]);
        $folder->setStationName($_POST["station"]);  
        $folder->setDeviceId($_POST["deviceId"]);
        $folder->setVendor($_POST["vendor"]);
        $folder->setMake($_POST["make"]);
        $folder->setModel($_POST["model"]);
        $folder->setCertificationsSystem($_POST["cSystem"]);
        $FDS->updateMeta($folder);
        $msg="Meta Information Updated Successfully.";
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

        <td class="ui-widget-header" style="padding:10px 10px 10px 10px;">Edit Meta</td>
        </tr>
      <tr>
        <td class="ui-widget-content">
            <form name="frm1" method="post" action="editMeta.php">
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
            <form name="frm1" method="post" action="editMeta.php">
                <input type="hidden" name="selectedFolderSeq" value="<?echo$selSeq?>" >
                <table width="100%" border="0" style="padding:10px 10px 10px 10px;">
                  <tr>
                    <td width="22%">Category</td>
                    <td width="78%"><input name="category" type="text" size="50" value="<?php echo($folder->getCategory());?>" >
                      &nbsp;</td>
                  </tr>  
                  <tr>
                    <td width="22%">Industry Code</td>
                    <td width="78%"><input name="indusCode" type="text" size="50" value="<?php echo($folder->getIndustryCode());?>" >
                      &nbsp;</td>
                  </tr>
                  <tr>
                    <td width="22%">Industry Name</td>
                    <td width="78%"><input name="indusName" type="text" size="50" value="<?php echo($folder->getIndustryName());?>" >
                      &nbsp;</td>
                  </tr>
                  <tr>
                    <td width="22%">Address</td>
                    <td width="78%"><input name="address" type="text" size="50" value="<?php echo($folder->getAddress());?>" >
                      &nbsp;</td>
                  </tr> 
                  <tr>
                    <td width="22%">City</td>
                    <td width="78%"><input name="city" type="text" size="50" value="<?php echo($folder->getCity());?>" >
                      &nbsp;</td>
                  </tr> 
                  <tr>
                    <td width="22%">State</td>
                    <td width="78%"><input name="state" type="text" size="50" value="<?php echo($folder->getState());?>" >
                      &nbsp;</td>
                  </tr>
                  <tr>
                    <td width="22%">Zipcode</td>
                    <td width="78%"><input name="zipCode" type="text" size="50" value="<?php echo($folder->getZipcode());?>" >
                      &nbsp;</td>
                  </tr> 
                  <tr>
                    <td width="22%">Longitude</td>
                    <td width="78%"><input name="longitude" type="text" size="50" value="<?php echo($folder->getLongitude());?>" >
                      &nbsp;</td>
                  </tr> 
                  <tr>
                    <td width="22%">Latitude</td>
                    <td width="78%"><input name="latitude" type="text" size="50" value="<?php echo($folder->getLatitude());?>" >
                      &nbsp;</td>
                  </tr> 
                  <tr>
                    <td width="22%">Email</td>
                    <td width="78%"><input name="email" type="text" size="50" value="<?php echo($folder->getEmail());?>" >
                      &nbsp;</td>
                  </tr> 
                  <tr>
                    <td width="22%">Mobile</td>
                    <td width="78%"><input name="mobile" type="text" size="50" value="<?php echo($folder->getMobile());?>" >
                      &nbsp;</td>
                  </tr> 
                  <tr>
                    <td width="22%">Station</td>
                    <td width="78%"><input name="station" type="text" size="50" value="<?php echo($folder->getStationName());?>" >
                      &nbsp;</td>
                  </tr> 
                  <tr>
                    <td width="22%">Device Id</td>
                    <td width="78%"><input name="deviceId" type="text" size="50" value="<?php echo($folder->getDeviceId());?>" >
                      &nbsp;</td>
                  </tr>
                   <tr>
                    <td width="22%">Vendor</td>
                    <td width="78%"><input name="vendor" type="text" size="50" value="<?php echo($folder->getVendor());?>" >
                      &nbsp;</td>
                  </tr>
                  <tr>
                    <td width="22%">Make</td>
                    <td width="78%"><input name="make" type="text" size="50" value="<?php echo($folder->getMake());?>" >
                      &nbsp;</td>
                  </tr>
                   <tr>
                    <td width="22%">Model</td>
                    <td width="78%"><input name="model" type="text" size="50" value="<?php echo($folder->getModel());?>" >
                      &nbsp;</td>
                  </tr>
                  <tr>
                    <td width="22%">Certification System</td>
                    <td width="78%"><input name="cSystem" type="text" size="50" value="<?php echo($folder->getCertificationsSystem());?>" >
                      &nbsp;</td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>
                     <input type="submit" name="submit" value="Update">
                     <input type="reset" name="Reset" value="Reset">

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
