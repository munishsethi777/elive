 <?php
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/UserDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
  require_once($ConstantsArray['dbServerUrl'] . "Utils/DropDownUtils.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");
  
   $user = new User();
   $UDS = new UserDataStore();   
   $otherLocations = array();
   if($_POST["editSeq"] <> "" ){
        $UDS = new UserDataStore();
        $user = $UDS->FindBySeq($_POST["editSeq"]);
        $otherLocations = $user->getOtherLocationSeqs();   
   }  
   
  if(isset($_POST["call"]) && $_POST["call"] == "save"){
      $locationSeq = $_POST["locations"];
      $fullName = $_POST["fullName"];      
      $username = $_POST["username"];
      $Password = $_POST["password"];
      $conPassword = $_POST["conPassword"]; 
      $emailId = $_POST["emailId"];
      $active = $_POST["active"];
      $seq = $_POST["seq"];
      $locations = $_POST["otherlocations"];
      if(!empty($locations)){
          $locations = explode(",",$locations);
          $user->setOtherLocationSeqs($locations);
          $otherLocations = $locations;
      }
      $user->setLocationSeq($locationSeq);
      $user->setIsManager(true);
      $user->setUserName($username); 
      $user->setFullName($fullName);
      $encodedPassword = SecurityUtil::Encode($Password);  
      $user->setPassword($encodedPassword);
      $user->setConfirmPassword(SecurityUtil::Encode($conPassword));
      
      $user->setEmailId($emailId);
      $user->setIsActive($active);
      $user->setSeq($seq);
     //------------------------validations--------------------------------- 
    
    $messageText = "";    
    $div = "";
    $messageText = validator::validateform("User Name",$username,56,false);
    if($locationSeq == 0){
        $messageText .= "- Location is Required<br>";    
    }
    
    $messageText .= validator::validateform("Password",$Password,56,false); 
    
    if($Password != $conPassword){
       $messageText .= "- Confirm Password should match with Password."; 
    }   
    $messageText .=  validator::validateform("Email Id",$emailId,256,false); 
       //same user name validation
     
      if($messageText == ""){
            $userseq = $UDS->isExist($username);
            if($userseq != "" && $seq <> $userseq){
                $messageText = "Manager with this user name already exists. Please choose another user name.";  
            }
      }
       
        
     
     //------------------------------************----------------------------------
     
                                                                         
     
    //------------------------Show Validation or save object---------------------------------   
    if($messageText != null && $messageText != ""){
      $div = "         <div class='ui-widget'>
                       <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'> 
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
                               <strong>Error during save user details :</strong> <br/>" . $messageText . "</p>
                       </div></div>" ; 
    }else{ 
         //Creating User Object           
      
     
      //set current date
      $user->setDateOfRegistration(Date("Y/m/d")); 
      $UDS = new UserDataStore();
      $UDS->Save($user);
      $messageText = "User Details Saved Successfully";
      $div = "<div class='ui-widget'>
                       <div  class='ui-state-default ui-corner-all' style='padding: 0 .7em;'> 
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
                               <strong>Message:</strong>&nbsp;" . $messageText . "</p> 
                       </div></div>";
                       
      $user = new User();
    }
    //------------------------------************----------------------------------   
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
        <td class="ui-widget-header" style="padding:10px 10px 10px 10px;">Enter New Manager Details </td>
        </tr>
      <tr>
        <td class="ui-widget-content">        
            <form name="frm1" id="frm1" method="post" action="CreateManagerForm.php">
                 <input type = "hidden" name="otherlocations" id="otherlocations"/>
                 <input type = "hidden" name="call" id="call"/>        
                <input type="hidden" name="seq" id="seq" value="<?php echo ($user->getSeq());?>" / >    
                <table width="100%" border="0" style="padding:10px 10px 10px 10px;">
                 <tr>
                    <td width="22%">Select Location:</td>
                    <td width="78%">
                        <? echo DropDownUtils::getAllLocationsDropDown("locations","",$user->getLocationSeq()) ?>
                        
                    </td>
                  </tr>
                  <tr>
                    <td width="22%">Other Locations:</td>
                    <td width="78%">
                        <b> <? echo DropDownUtils::getAllLocationsMultiDropDown("ol_DropDown","",$user->getOtherLocationSeqs()) ?></b> 
                    </td>
                  </tr>
                 <tr>
                    <td width="22%">Full Name :</td>
                    <td width="78%"><input name="fullName" type="text" value="<?php echo($user->getFullName());?>"  size="50"></td>
                  </tr>
                  <tr>
                    <td width="22%">User Name :</td>
                    <td width="78%"><input name="username" type="text" value="<?php echo($user->getUserName());?>"  size="50"></td>
                  </tr>
                  <tr>
                    <td>Password :</td>
                    <td><input name="password" type="text" value="<?php echo(SecurityUtil::Decode($user->getPassword()));?>" size="50"></td>
                  </tr>
                   <tr>
                    <td>Confirm New Password :</td>
                    <td><input name="conPassword" type="text" value="<?php echo(SecurityUtil::Decode($user->getConfirmPassword()));?>" size="50"></td>
                  </tr>
                  <tr>
                    <td>Email Id :</td>
                    <td><input name="emailId" type="text" value="<?php echo($user->getEmailId());?>" size="50"></td>
                  </tr>
                   <tr>
                    <td>Activate :</td>
                     <?php
                           if($user->getIsActive() == "" || $user->getIsActive() == "1"){
                             $checked_On = "checked";
                           }else{
                              $checked_Off = "checked"; 
                           }
                                
                         ?> 
                    <td><input name="active" value="true" type="radio"  <?php echo ($checked_On); ?> >On
                         <input name="active" value="false" type="radio"  <?php echo ($checked_Off); ?> >Off</td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>
                       
                        <input type="button" onclick="submitForm()" name="savebtn" value="Save">
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
<script type="text/javascript">
    $(document).ready(function(){
        $(".chosen-select").chosen({width:"63%"});
        var values = "<?echo implode(",",$otherLocations)?>";
        if(values.length > 0){
            values = values.split(",");
            $('.chosen-select').val(values).trigger("chosen:updated");
        }
    });
    function submitForm(){
        var vals = [];
        $( '#ol_DropDown :selected' ).each( function( i, selected ) {
            vals[i] = $( selected ).val();
        });
        $("#otherlocations").val(vals);
        $("#call").val("save");
        $("#frm1").submit();
    }
</script>