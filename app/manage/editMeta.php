<?include("sessioncheck.php");
$msg="";
$emailMsg="";
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "admin//configuration.php");
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/UserDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/FolderDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/LocationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/Utils/DropDownUtils.php");
require_once($ConstantsArray['dbServerUrl'] . "/BusinessObjects/Folder.php");

//$configuration = new Configuration();
//Session_start();
$managerSession = $_SESSION["managerSession"];
$userDataStore = UserDataStore::getInstance();
$LDS = LocationDataStore::getInstance();
$userSeq =  $managerSession['seq'];
$locSeq = $managerSession['locSeq'];
$FDS = FolderDataStore::getInstance();
$isSuccess = false;
$messageText = "";
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


$folders = $FDS->FindByLocation(implode(",",$locationSeqs));

$folder = new Folder();
$selSeq = 0;

if(!empty($_POST["call"]) && ($_POST["call"]== "loadMeta"))
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
if(!empty($_POST["call"]) && ($_POST["call"] == "updateMeta")){
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
        $folder->setZipcode($_POST["zipcode"]);
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
        $folder->setUploadFrequency($_POST["uploadFrequency"]);
        $folder->setCalliberationURL($_POST["calliberationURL"]);
        $folder->setLoggerId($_POST["loggerId"]);
        if(!empty($_POST["isCalliberationEnabled"])){
        	$folder->setIsCalliberationEnabled(true);
        }else{
        	$folder->setIsCalliberationEnabled(false);
        }
        $FDS->updateMeta($folder);
        $messageText="Meta Information Updated Successfully.";
        $isSuccess = true;
    }
}
$isCallChecked = "";
if($folder->getIsCalliberationEnabled() == true){
	$isCallChecked = "checked";
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
        <div class="wrapper wrapper-content animated fadeInRight">
            <div class="messageDiv">
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Edit Meta Information</h5>
                        </div>
                        <div class="ibox-content">
                            <form name="stationForm" id="stationForm" method="post" action="editMeta.php" class="form-horizontal">
                                <input type="hidden" name="call" id="call" value="loadMeta">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Select Station</label>
                                    <div class="col-sm-6">
                                        <? echo DropDownUtils::getFoldersDropDownWithStationName($folders,"F_DropDown","loadMeta()",$selSeq) ?>
                                    </div>
                                </div>
                            </form>
                            <hr>
                            <form name="frm1" id="frm1" method="post" action="editMeta.php" class="form-horizontal">
                                <input type="hidden" name="selectedFolderSeq" value="<?echo$selSeq?>" >
                                <input type="hidden" name="call" id="call" value="updateMeta">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Category</label>
                                    <div class="col-sm-6">
                                       <? echo DropDownUtils::getCategoryTypeDropDown("category",$folder->getCategory()) ?>
                                    </div>
                                </div>
				<div class="form-group">
                                    <label class="col-sm-2 control-label">Industry Id</label>
                                    <div class="col-sm-6">
                                        <input disabled="disabled" class="form-control" name="indusCode" type="text" size="50" value="<?php echo($folder->getLocationSeq());?>" >
                                    </div>
                                </div>
                                 <div class="form-group">
                                    <label class="col-sm-2 control-label">Industry Code</label>
                                    <div class="col-sm-6">
                                        <input class="form-control" name="indusCode" type="text" size="50" value="<?php echo($folder->getIndustryCode());?>" >
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Logger Id</label>
                                    <div class="col-sm-6">
                                        <input class="form-control" name="loggerId" type="text" size="50" value="<?php echo($folder->getLoggerId());?>" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Industry Name</label>
                                    <div class="col-sm-6">
                                        <input class="form-control" name="indusName" type="text" size="50" value="<?php echo($folder->getIndustryName());?>" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Industry Address</label>
                                    <div class="col-sm-6">
                                        <input class="form-control" name="address" type="text" size="50" value="<?php echo($folder->getAddress());?>" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Industry City</label>
                                    <div class="col-sm-6">
                                        <input class="form-control" name="city" type="text" size="50" value="<?php echo($folder->getCity());?>" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Industry State</label>
                                    <div class="col-sm-6">
                                        <input class="form-control" name="state" type="text" size="50" value="<?php echo($folder->getState());?>" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Industry Zipcode</label>
                                    <div class="col-sm-6">
                                        <input class="form-control" name="zipcode" type="text" size="50" value="<?php echo($folder->getZipcode());?>" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Industry Longitude</label>
                                    <div class="col-sm-6">
                                        <input class="form-control" placeholder="ex 21.936393" name="longitude" type="text" size="50" value="<?php echo($folder->getLongitude());?>" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Industry Latitude</label>
                                    <div class="col-sm-6">
                                        <input class="form-control" name="latitude" placeholder="ex 83.352769" type="text" size="50" value="<?php echo($folder->getLatitude());?>" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Email</label>
                                    <div class="col-sm-6">
                                        <input class="form-control" name="email" type="text" size="50" value="<?php echo($folder->getEmail());?>" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Mobile</label>
                                    <div class="col-sm-6">
                                        <input class="form-control" name="mobile" type="text" size="50" value="<?php echo($folder->getMobile());?>" >
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Station ID</label>
                                    <div class="col-sm-6">
                                        <input disabled="disabled" class="form-control" name="station" type="text" size="50" value="<?php echo($folder->getSeq());?>" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Station Name</label>
                                    <div class="col-sm-6">
                                        <input class="form-control" name="station" type="text" size="50" value="<?php echo($folder->getStationName());?>" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Device Id</label>
                                    <div class="col-sm-6">
                                        <input class="form-control" name="deviceId" type="text" size="50" value="<?php echo($folder->getDeviceId());?>" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Vendor</label>
                                    <div class="col-sm-6">
                                        <input class="form-control" name="vendor" type="text" size="50" value="<?php echo($folder->getVendor());?>" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Make</label>
                                    <div class="col-sm-6">
                                        <input class="form-control" name="make" type="text" size="50" value="<?php echo($folder->getMake());?>" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Model</label>
                                    <div class="col-sm-6">
                                        <input class="form-control" name="model" type="text" size="50" value="<?php echo($folder->getModel());?>" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Certification System</label>
                                    <div class="col-sm-6">
                                        <input class="form-control" name="cSystem" type="text" size="50" value="<?php echo($folder->getCertificationsSystem());?>" >
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Data Upload frequency</label>
                                    <div class="col-sm-6">
                                        <input class="form-control" name="uploadFrequency" type="text" size="50" value="<?php echo($folder->getUploadFrequency());?>" >
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Calibration Enabled</label>
                                    <div class="col-sm-6">
                                    	<input class="checkbox-inline i-checks" name="isCalliberationEnabled" type="checkbox" <?php echo($isCallChecked)?>>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Calibration URL</label>
                                    <div class="col-sm-6">
                                        <input class="form-control" name="calliberationURL" type="text" size="50" value="<?php echo($folder->getCalliberationURL());?>" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-lg-offset-2 col-lg-10">
                                        <button class="btn btn-primary" type="submit" >Update Meta</button>
                                    </div>
                                 </div>



                            </form>
                            </div>
                    </div>
                </div>
            </div>
        </div>
        <?include("footer.php"); ?>
    </div>
    </body>
</html>
<script type="text/javascript">
	$(document).ready(function () {
	    $('.i-checks').iCheck({
	        checkboxClass: 'icheckbox_square-green',
	    });
	 });
    showResponseNotification("<?echo $isSuccess?>","<?echo $messageText ?>","messageDiv","frm1");
    function loadMeta(){
        $("#stationForm").submit();
    }
</script>