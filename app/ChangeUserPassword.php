<?php 
include("sessioncheckPrivateOnly.php");
require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/UserDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils//StringUtils.php");
$messageText="";
if($_POST["submit"]<>"")
{

    $earlierPassword = $_POST["earlierPassword"];
    $newPassword = $_POST["newPassword"];
    $confirmNewPassword = $_POST["confirmNewPassword"];
    $otp = $_REQUEST["otp"];
    $user = $_SESSION["userlogged"];
    $oldPassword = $user->getDecodedPassword();

    $UDS = UserDataStore::getInstance();
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
        $messageText="-New password and confirm password does not match";

        }

    if($oldPassword != $earlierPassword){
        if($messageText != null && $messageText != ""){
          $messageText = $messageText . "<br/>". "-Earlier Password does not match with the one in the database";
        }else{
           $messageText =  "-Earlier Password does not match with the one in the database";
         }
       }
    }
    $user = $UDS->FindBySeq($user->getSeq());
    $time = new DateTime();
    $otptime = new DateTime($user->getOTPGenerateDate());
    $validationtime = $otptime->modify('+3 minutes');
    if($time > $validationtime){
      $messageText = "OTP Expired";
    }
    if($user->getOTP() != $_REQUEST["otp"]){
      $messageText = "OTP IS INCORRECT";
    }
    if($messageText != null && $messageText != ""){
         $div =  StringUtils::getMessage("Change Passowrd" ,$messageText,true);
    }else{
         $newPassword = SecurityUtil::Encode($newPassword);
         $user->setPassword($newPassword);
         $UDS->updatePassword($user);
         $messageText="Password updated successfully";
         $div = StringUtils::getMessage("" ,$messageText,false);
     }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<?include("cpcbHeader.php");?>
<?include("cpcbMenu.php")?>
<body style="margin:0px 0px 0px 0px" class="fullBody">




    <table width="80%" border="0" align="center">
       <tr>
        <td style="padding:10px 10px 10px 10px;"><?php echo($div) ?></td>
       </tr>
      <tr>
      <tr>
        <td class="ui-widget-header" style="padding:10px 10px 10px 10px;">Change Password </td>
        </tr>
      <tr>
        <td class="ui-widget-content">
            <form name="frm1" id="changeUserPasswordForm" method="post" action="ChangeUserPassword.php">
               <input type="hidden" name="id" value="<?php echo($user->getSeq() != ""?$user->getSeq():"\"\"");?>"> 
                <table width="100%" border="0" style="padding:10px 10px 10px 10px;">
                  <tr> 
                    <td width="22%"></td>
                    <td>
                      <button type="button" class="btn btn-primary" onclick="generateOTP()">Send OTP(OTP will expire within 3 minutes)</button>
                    </td>
                  </tr>
                  <tr>
                    <td width="22%">Enter OTP:</td>
                    <td width="78%">
                      <input type="text" placeholder="Enter OTP" name="otp" size="50">
                    </td>
                  </tr>
                  <tr>
                    <td width="22%">Earlier Password :</td>
                    <td width="78%"><input name="earlierPassword" type="password" size="50"></td>
                  </tr>
                  <tr>
                    <td>New Password :</td>
                    <td><input name="newPassword" type="password" size="50"></td>
                  </tr>
                   <tr>
                    <td>Confirm New Password :</td>
                    <td><input name="confirmNewPassword" type="password" size="50"></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>
                         <input type="submit" name="submit" value="submit">
                        <input type="reset" name="Reset" value="Reset">

                    </td>
                  </tr>
                </table>
              </form>
         </td>
        </tr>

    </table>





    </Div>


      <script language="javascript">
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
function generateOTP(){
		var data = $("#changeUserPasswordForm").serialize();
		$.ajax({
			url:"Actions/OTPAction.php?call=generateOTP",
			type:"POST",
			data:data,
			success: function (data,status,xhr) {   // success callback function
				alert("OTP emailed successfully. Please use it within 3 minutes");
			},
			error: function (jqXhr, textStatus, errorMessage) { // error callback 
				alert("Failed to email OTP. Please contact system administrator");
			}
		});
}
</script>
    </body>
</html>