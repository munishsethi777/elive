<?php
require_once("IConstants.inc");
require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/UserDataStore.php");
require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/UserActionDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils//StringUtils.php");
require($ConstantsArray['dbServerUrl'] . "Utils//MailerUtils.php");
require_once($ConstantsArray['dbServerUrl'] . "enums//UserActionType.php");


$div = "";
$message="";
$submit;
$otp;
if($_POST["submit"]<>""){
    $otp = $_POST['otp'];
    $submit = $_POST["submit"];
}
session_start();
$userSeq = $_SESSION["userseq"];
$UDS = UserDataStore::getInstance();
$user = null;
$userEmail = "";
try{
    if($userSeq != null){
        $user = $user = $UDS->FindBySeq($userSeq);
        $userEmail = $user->getEmailId();
        $em   = explode("@",$userEmail);
        $name = implode('@', array_slice($em, 0, count($em)-1));
        $len  = floor(strlen($name)/2);
        $userEmail =  substr($name,0, $len) . str_repeat('*', $len) . "@" . end($em);
        $message = '<label style="color:green;">OTP is emailed to the id  '.$userEmail .'. 
                  	If you do not have access to this email pls contact the system administrator</label>';
    }else{
        throw new Exception("Invalid Execution, go back to main page and login again");
    }
}catch(Exception $e){
    $message = '<label style="color:red">'.$e->getMessage().'</label>';
}
if($submit<>""){
      //if($messageText == ""){
          try{
              $UADS = UserActionDataStore::getInstance();
              if($user == null){
                  throw new Exception("Invalid Execution, go back to main page and login again");
              }
              if($user != null && $user <> ""){
                    $time = new DateTime();
                    $otptime = new DateTime($user->getLoginOTPGenerateDate());
                    $validationtime = $otptime->modify('+3 minutes');
                    if ($time > $validationtime) {
                        throw new Exception("OTP has Expired");
                    }
                    if ($user->getLoginOTP() != $otp) {
                        throw new Exception("OTP does not match");
                    }
                    $userAction = new UserAction();
                    $userAction->setUserSeq($user->getSeq());
                    $userAction->setActionName(UserActionType::login);
                    $UADS->saveAction($userAction);
                    session_start();
                    $_SESSION["userlogged"] = $user;
                    if (isset($_SESSION['httpUrl'])) {
                        header("Location:" . $_SESSION['httpUrl']);
                    } else {
                        if ($user->getSeq() == 206) {
                            header("Location:cpcbStackReportMultiStation_raymond.php");
                        } else {
                            header("Location:cpcbStackReportMultiStation.php");
                        }
                    }
                }
            }catch(Exception $e){
                $message = '<label style="color:red">'.$e->getMessage().'</label>';
            }
      //}
        
}
              
          
if($messageText <> "") {
   $div = StringUtils::getMessage("Login",$messageText,true);
}

?>



<!DOCTYPE html>
<html>
    <head>
        <link type="text/css" href="admin/css/cupertino/jquery-ui-1.8.14.custom.css" rel="stylesheet" />
        <link type="text/css" href="admin/css/custom.css" rel="stylesheet" />
    </head>
    <table align="center" width="40%" border="0">
       
        <td class="ui-widget-header" style="padding:10px 10px 10px 10px;"> EnvirotechLive User Login </td>
        </tr>
      <tr>
        <td class="ui-widget-content">
            <form name="frm1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <table width="100%" border="0" style="padding:10px 10px 10px 10px;">
                  <tr>
                  	<td colspan="2" style="padding-bottom:20px"><?php echo $message;?></td>
                  </tr>
                  <tr>
                    <td width="22%">OTP :</td>
                    <td width="78%"><input name="otp" type="text" size="30">
                      &nbsp;</td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" name="submit" value=" Continue " />
                    </td></tr>
                     <tr>
                    <td>&nbsp;</td>
                    
                  </tr>
                </table>
              </form>
              <center><a href="index.php">Back to Main Page</a></center>
         </td>
        </tr>
    </table>
    
    </Div>

       <script language="javascript">
    function submitform()
    {
        if(document.frm1.adminPassword.value=="")
        {
            alert("enter the password");
            return false;
        }
        else
        {
            return true;
        }

    }
</script>

    </body>
</html>


