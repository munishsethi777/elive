<?
$msg="";
$emailMsg="";
require_once("configuration.php");
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php"); 
$configuration = new Configuration();
if($_POST["submit"]<>"")
{                                   
        $conEmail = $_POST["conEmailId"];
        $email = $_POST["emailId"];     
        $msg =  validator::validateform("Email Id",$email,256,false);

         if($msg == ""){             
          if($email <> $conEmail){
              $msg = "Email id does not match with confirm email id";              
          }   
         }       
         
         
    

      if($msg != null && $msg != ""){
      $div = "         <div class='ui-widget'>
                       <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'> 
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
                               <strong>Error during change email id :</strong> <br/>" . $msg . "</p>
                       </div></div>" ; 
     }else{
         $configuration->saveConfig($configuration->adminEmailId,$email);    
         $msg="Email id updated successfully";
         $div = "<div class='ui-widget'>
                       <div  class='ui-state-default ui-corner-all' style='padding: 0 .7em;'> 
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
                               <strong>Message:</strong>&nbsp;" . $msg . "</p> 
                       </div></div>";
     }
      } else{
         $h = $configuration->getConfiguration($configuration->adminEmailId);
         $email = $h["configvalue"];
         $conEmail   = $email;
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
      
        <td class="ui-widget-header" style="padding:10px 10px 10px 10px;">Change Email Id</td>
        </tr>
      <tr>
        <td class="ui-widget-content">
            <form name="frm1" method="post" action="changeEmailId.php">            
                <table width="100%" border="0" style="padding:10px 10px 10px 10px;">                
                    
                  <tr>
                    <td width="22%">Email Id</td>
                    <td width="78%"><input name="emailId" type="text" size="50" value="<?php echo($email);?>" >
                      &nbsp;</td>
                  </tr>
                   <tr>
                    <td width="22%">Confirm Email Id</td>
                    <td width="78%"><input name="conEmailId" type="text" size="50" value="<?php echo($conEmail);?>" >
                      &nbsp;</td>
                  </tr>
                   <tr>
                    <td>&nbsp;</td>
                    <td>
                         <input type="submit" name="submit" value="Save">
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
