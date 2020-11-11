<?include("sessioncheck.php");
  require_once('IConstants.inc');
  $managerSession = $_SESSION["managerSession"];
  $isAdmin = $managerSession["isAdmin"];  
  require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/UserDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
  require_once($ConstantsArray['dbServerUrl'] . "Utils/DropDownUtils.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");
  $user = new User();
  $UDS = new UserDataStore();
  $otherLocationSeqs = "";
  $locationSeq = "";
  $isSuccess = false;
  $messageText = "";
  if($_POST["editSeq"] <> "" ){
        $UDS = new UserDataStore();
        $user = $UDS->FindBySeq($_POST["editSeq"]);
        $locationSeq = $user->getLocationSeq();
        $otherLocation = $user->getOtherLocationSeqs();
        if(!empty($otherLocation)){
           $otherLocationSeqs = implode(",",$otherLocation);
        }
   }

  if($_POST["call"]=="save"){
     // $locationSeq = $_POST["locSeq"];
      $fullName = $_POST["fullName"];
      $username = $_POST["username"];
      $Password = $_POST["password"];
      //$conPassword = $_POST["conPassword"];
      $emailId = $_POST["emailId"];
      $active = $_POST["active"];
      $seq = $_POST["seq"];
      $otherLocations = $_POST["otherLocationSeqs"];    
      //$locationSeq = $_POST["l_DropDown"];
      $locationSeq = 0;
      $user->setLocationSeq($locationSeq);
      $user->setIsManager(false);
      $user->setUserName($username);
      $user->setFullName($fullName);
      $encodedPassword = SecurityUtil::Encode($Password);
      $user->setPassword($encodedPassword);
      $user->setConfirmPassword(SecurityUtil::Encode($conPassword));
      if(!empty($otherLocations)){
         $user->setOtherLocationSeqs(explode(",",$otherLocations));
      }
      $user->setEmailId($emailId);
      $user->setIsActive($active);
      $user->setSeq($seq);
     //------------------------validations---------------------------------

    $messageText = "";
    $div = "";
    $messageText = validator::validateform("User Name",$username,56,false);
    if(empty($otherLocations)){
        $messageText .= "Select atleast one location!<br/>";
    }
    $messageText .= validator::validateform("Password",$Password,56,false);

   // if($Password != $conPassword){
     //  $messageText .= "- Confirm Password should match with Password.";
   // }
    $messageText .=  validator::validateform("Email Id",$emailId,256,false);
       //same user name validation

      if($messageText == ""){
            $userseq = $UDS->isExist($username);
            if($userseq != "" && $seq <> $userseq){
                $messageText = "User with this username already exists. Please choose another user name.";
            }
      }



     //------------------------------************----------------------------------



    //------------------------Show Validation or save object---------------------------------
    if($messageText != null && $messageText != ""){
    }else{
       //Creating User Object
      //set current date
      $user->setDateOfRegistration(Date("Y/m/d"));
      $UDS = new UserDataStore();
      $UDS->Save($user);
      $messageText = "User Details Saved Successfully";
      $isSuccess = true;
      if(!(empty($seq))){
          header("Location: showUsers.php");
      }
      $user = new User();
    }
    //------------------------------************----------------------------------
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

            <? include("leftButtons.php");
            $seq = $managerSession['seq'];
            $LDS = LocationDataStore::getInstance();
            $location = $LDS->FindBySeq($managerSession['locSeq']); // finding location from the current sessio

            ?>

            <div class="wrapper wrapper-content animated fadeInRight mainDiv">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="ibox float-e-margins">
                                <div class="ibox-title">
                                    <h5>User Form</h5>
                                </div>
                                <div class="ibox-content">
                                    <form method="post" role="form" name="frm1" id="frm1" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-horizontal">
                                      
                                       
                                        <input type = "hidden" name="call" id="call"/>  
                                        <input type="hidden" name="seq" id="seq" value="<?php echo ($user->getSeq());?>" />
                                        <input type="hidden" name="otherLocationSeqs" id="otherLocationSeqs" value="<?php echo ($otherLocationSeqs);?>" />
                                        <input type="hidden" name="locSeq" id="locSeq" value="<?php echo ($location->getSeq());?>" />
                                         <div class="form-group">
                                            <label class="col-sm-2 control-label">Location</label>
                                            <div class="col-sm-10">
                                                <?if($isAdmin){
                                                    echo DropDownUtils::getAllLocationsMultiDropDown("l_DropDown","",$locationSeq);
                                                }else{
                                                     $locations = $LDS->FindLocationArrByUser($seq);  
                                                     echo DropDownUtils::getUserLocationsMultiDropDown($locations,"l_DropDown","",$locationSeq,"chosen-select");    
                                                }?> 
                                                
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Full Name</label>
                                            <div class="col-lg-10">
                                                <input type="text" name="fullName" placeholder="Full Name" required="required" value="<?php echo($user->getFullName())?>" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">User Name</label>
                                            <div class="col-lg-10">
                                                <input type="text" name="username" placeholder="User Name" required="required" value="<?php echo($user->getUserName());?>" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Password</label>
                                            <div class="col-lg-10">
                                                <input type="text" name="password" placeholder="Password" required="required" value="<?php echo(SecurityUtil::Decode($user->getPassword()))?>" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Email</label>
                                            <div class="col-lg-10">
                                                <input type="email" name="emailId" placeholder="Email" required="required" value="<?php echo($user->getEmailId());?>" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-lg-offset-2 col-lg-10">
                                                <?php
                                                   if($user->getIsActive() == "" || $user->getIsActive() == "1"){
                                                     $checked_On = "checked";
                                                   }else{
                                                     $checked_Off = "checked";
                                                   }
                                                 ?>
                                                 
                                                    <label class="checkbox-inline i-checks"><input type="radio"  <?php echo ($checked_On)?> value="1" name="active"> <i></i>Active</label>
                                                 
                                                 
                                                    <label class="checkbox-inline i-checks"><input type="radio" <?php echo ($checked_Off); ?> value="0" name="active"> <i></i>InActive</label>
                                                 
                                                
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-lg-offset-2 col-lg-10">
                                                <button class="btn btn-primary" type="button" onclick="submitForm()">Save</button>
                                                <button class="btn btn-white" type="submit" onclick="cancel()">Cancel</button>
                                                
                                                <!--button class="btn btn-primary ladda-button" data-style="expand-right" id="saveButton" type="button">
                                                    <span class="ladda-label">Create</span>
                                                </button-->
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
<script type="text/javascript">
    showResponseNotification("<?echo $isSuccess?>","<?echo $messageText ?>","mainDiv","frm1");
    function cancel(){
        location.href = "showUsers.php";
    }
     $(document).ready(function () {
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });
       
       $(".chosen-select").chosen({width:"63%"});
        var values = "<?echo $otherLocationSeqs?>";
        if(values.length > 0){
            values = values.split(",");
            $('.chosen-select').val(values).trigger("chosen:updated");
        }
       
     });
     
     function submitForm(){
        var vals = [];
        $( '#l_DropDown :selected' ).each( function( i, selected ) {
            vals[i] = $( selected ).val();
        });
        $("#otherLocationSeqs").val(vals);
        $("#call").val("save");
        $("#frm1").submit();
    }
    
</script>