<?php
require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/UserDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils//StringUtils.php");

$div = "";
$message="";
if($_POST["submit"]<>"")
{

    $username = $_POST['username'];
    $password = $_POST['userpassword'];
     $message = validator::validateform("User Name",$username,56,false);
      if($messageText != null && $messageText != ""){
          $messageText = $messageText . "<br/>". validator::validateform("Password",$password,56,false);
      }else{
           $messageText =  validator::validateform("Password",$password,56,false);
      }
      if($messageText == ""){
          $UDS = UserDataStore::getInstance();
          $user = $UDS->FindByUserName($username);
          if($user != null && $user <> ""){
              if($user->getDecodedPassword() == $password ){
                  session_start();
                  $_SESSION["userlogged"] = $user;
                  if(isset($_SESSION['httpUrl'])){
                     header("Location:". $_SESSION['httpUrl']);
                  }else{
                      if($user->getSeq() == 206){
                          header("Location:cpcbStackReportMultiStation_raymond.php");
                      }else{
                          header("Location:cpcbMap.php");
                      }
                  }

              }else{
                 $messageText = "Invalid User Name or Password";
              }
          }
      }else{
          $messageText = "Invalid User Name or Password";
      }
       if($messageText <> "") {
           $div = StringUtils::getMessage("Login",$messageText,true);
       }
}
?>



<!DOCTYPE html>
<html>
    <head>
        <link type="text/css" href="admin/css/cupertino/jquery-ui-1.8.14.custom.css" rel="stylesheet" />
        <link type="text/css" href="admin/css/custom.css" rel="stylesheet" />
    </head>
<body>
    <table align="center" width="50%" border="0">
       <tr>
        <td style="padding:10px 10px 10px 10px;"><?php echo($div) ?></td>
       </tr>
      <tr>
        <td class="ui-widget-header" style="padding:10px 10px 10px 10px;"> Bhoomi User Login </td>
      </tr>
      <tr>
      <td>
      <table width="100%" class="ui-widget-content">
        <tr>
            <td style="padding-left:40px;">
                <img src="images/bhoomilogo.jpg" alt="">
            </td>
            <td >
                <form name="frm1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <table width="100%" border="0" style="padding:10px 10px 10px 10px;">
                      <tr>
                        <td width="22%">Username :</td>
                        <td width="78%"><input name="username" type="text" size="30">
                          &nbsp;</td>
                      </tr>

                      <tr>
                        <td width="22%">Password :</td>
                        <td width="78%"><input name="userpassword" type="password" size="30">
                          &nbsp;</td>
                      </tr>

                      <tr>
                        <td>&nbsp;</td>
                        <td><input type="submit" name="submit" value=" Login " />

                            <input type="reset" name="Reset" value="Reset">


                        </td></tr>
                         <tr>
                        <td>&nbsp;</td>
                        <td><!--<a href="forgotPassword.php">Forgot Password</a>-->   </td>


                      </tr>
                    </table>

                  </form>
         </td>
        </tr>
      </table>
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


