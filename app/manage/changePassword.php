
<?include("sessioncheck.php");
//require_once("configuration.php");
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/UserDataStore.php");

Session_start();
$managerSession = $_SESSION["managerSession"];
$userDataStore = UserDataStore::getInstance();
$userSeq =  $managerSession['seq'];
$isSuccess = false;
$messageText="";
if($_POST["submit"]<>"")
{

    $earlierPassword = $_POST["earlierPassword"];
    $newPassword = $_POST["newPassword"];
    $confirmNewPassword = $_POST["confirmNewPassword"];

    $user = $userDataStore->getUserByseq($userSeq);
    $savedPasswordEncoded = $user->getPassword();
    $savedPasswordDecoded = SecurityUtil::Decode($savedPasswordEncoded);
    $messageText = "";
    $div = "";
    $messageText = validator::validateform("Earlier Password",$earlierPassword,256,false);
    if($messageText != null && $messageText != ""){
          $messageText = $messageText . "<br/>". validator::validateform("New Password",$newPassword,256,false);
    }else{
      $messageText =  validator::validateform("New Password",$newPassword,256,false);
    }

     if($messageText != null && $messageText != ""){
          $messageText = $messageText . "<br/>". validator::validateform("Confirm Password",$confirmNewPassword,256,false);
    }else{
      $messageText =  validator::validateform("Confirm New Password",$confirmNewPassword,256,false);
    }

    if($messageText == ""){
        if($newPassword != $confirmNewPassword){
            $messageText="New password and confirm password does not match";
        }
        if($savedPasswordDecoded != $earlierPassword){
            if($messageText != null && $messageText != ""){
                $messageText = $messageText . "<br/>". "Earlier Password does not match with the one in the database";
            }else{
                $messageText =  "Earlier Password does not match with the one in the database";
            }
         }
    }


    if($messageText != null && $messageText != ""){
        
    }else{
        $user = new User();
        $user->setPassword(SecurityUtil::Encode($newPassword));
        $user->setSeq($userSeq);
        $userDataStore->updatePassword($user);

         //$configuration->saveConfig($configuration->adminPassword,$newPassword);
         $messageText="Password updated successfully";
         $isSuccess = true;
     
    }
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
            <? include("leftButtons.php");?>
                <div class="wrapper wrapper-content animated fadeInRight">
                    <div class="messageDiv"></div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="ibox float-e-margins">
                                <div class="ibox-title">
                                    <h5>Change Password</h5>
                                </div>  
                                <div class="ibox-content">
                                     <form name="frm1" id="frm1" method="post" action="changePassword.php" class="form-horizontal">
                                        <input type="hidden" name="submit" value="submit"/>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Earlier Password</label>
                                            <div class="col-lg-10">
                                                <input type="password" name="earlierPassword" placeholder="Earlier Password" required="required" class="form-control"> 
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">New Password</label>
                                            <div class="col-lg-10">
                                                <input type="password" name="newPassword" placeholder="New Password" required="required" class="form-control"> 
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Confirm Password</label>
                                            <div class="col-lg-10">
                                                <input type="password" name="confirmNewPassword" placeholder="Confirm Password" required="required" class="form-control"> 
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-lg-offset-2 col-lg-10">
                                                <button class="btn btn-sm btn-primary m-t-n-xs" type="submit">Submit</button>
                                            </div>
                                        </div>
                                     </form> 
                                </div>
                            </div>
                        </div> 
                    </div>
                </div>
                <? include("footer.php"); ?>
        </div>
    </body>
</html>
<script language="javascript">
    showResponseNotification("<?echo $isSuccess?>","<?echo $messageText ?>","messageDiv","frm1");
    function submitform()
    {
    if(document.frm1.earlierPassword.value=="")
    {
        alert("enter your old password");
        return false;
    }
    if(document.frm1.newPassword.value=="")
    {
        alert("enter new password");
        return false;
    }
    if(document.frm1.confirmNewPassword.value=="")
    {
        alert("enter confirm password");
        return false;
    }
    else
    {
        return true;
    }
}
</script>