<?include("sessioncheck.php");?>
<?
$messageText="";
$div = "";
$user = $_SESSION["userlogged"];
$fullName =   $user->getFullName();
$emailId = $user->getEmailId();
if($_POST["submit"]<>"")
{
    
    $fullName = $_POST["fullName"];
    $emailId = $_POST["emailId"];
    require_once('IConstants.inc'); 
    require_once($ConstantsArray['dbServerUrl'] . "Utils//StringUtils.php");     
    require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//UserDataStore.php"); 
    require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");  
    
    
    
    $UDS = UserDataStore::getInstance(); 
    $messageText = validator::validateform("Full Name",$fullName,100,true);
    if($messageText != null && $messageText != ""){
          $messageText = $messageText . "<br/>". validator::validateform("Email Id",$emailId,200,false); 
    }else{
      $messageText = validator::validateform("Email Id",$emailId,200,false);
    }
    
    
   
    if($messageText != null && $messageText != ""){
         $div =  StringUtils::getMessage("Update Profile" ,$messageText,true);
    }else{
         $user->setFullName($fullName);
         $user->setEmailId($emailId); 
         $UDS->Save($user);
         $messageText="Profile updated successfully";
         $div = StringUtils::getMessage("" ,$messageText,false);
     }
}
?>


 
<!DOCTYPE html>
<html>
    <head>
      
    </head>
    <body>
    
    <?include("leftButtons.php");?>
    
    <Div class="rightAdminPanel">
       
    
         
    <table width="80%" border="0">
       <tr>       
        <td style="padding:10px 10px 10px 10px;"><?php echo($div) ?></td>
       </tr>  
      <tr>
      <tr>
        <td class="ui-widget-header" style="padding:10px 10px 10px 10px;">Update Profile</td>
        </tr>
      <tr>
        <td class="ui-widget-content">
            <form name="frm1" method="post" action="updateProfile.php">    
                <table width="100%" border="0" style="padding:10px 10px 10px 10px;">
                  <tr>
                    <td width="22%">Full Name :</td>
                    <td width="78%"><input name="fullName" type="text" value="<?echo $fullName;?>" size="50">
                      &nbsp;</td>
                  </tr>
                  <tr>
                    <td>Email Id :</td>
                    <td><input name="emailId" type="text" value="<?echo $emailId;?>" size="50">  
                      &nbsp;</td>
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
</script>
    </body>
</html>