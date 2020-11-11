<?
require_once('IConstants.inc'); 
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");


   $location = new Location();
   $LDS = LocationDataStore::getInstance();
    
        if($_POST["editSeq"] <> "" ){
        
        $location = $LDS->FindBySeq($_POST["editSeq"]);   
    }  
   
   
if($_POST["submit"]<>""){
    $locationName = $_POST["location"];
    $details = $_POST["details"];
    $locationSeq = $_POST["seq"];
    $folderName = $_POST["folderName"];
    $hasCreateDirStr = $_POST["isCreateDir"];
    
    $location->setLocationName($locationName);
    $isPrivateStr = $_POST["isPrivate"];
    $isPrivate = $isPrivateStr =="on" ? 1 : 0;
    $hasCreateDir= $hasCreateDirStr =="on" ? 1 : 0;
    $location->setIsPrivate($isPrivate);
     if($locationName <> ""){
         $folderName =  strtolower($locationName);
         $folderName = str_replace(" ","_",$folderName);
     }
    $location->setLocationFolder($folderName);       
    $location->setSeq($locationSeq);
    $location->setLocationDetails($details);
    $location->setHasDirectory($hasCreateDir);
        
        
    $messageText = "";    
    $div = "";
    $messageText = validator::validateform("Location Name",$locationName,256,false);
    //if($messageText != null && $messageText != ""){
//          $messageText = $messageText . "<br/>". validator::validateform("Location Details",$details,500,false); 
//    }else{
//      $messageText =   validator::validateform("Loation Details",$details,500,false);
//    }
    
    if($messageText == ""){
        $seq = $LDS->isExist($locationName);
           if($seq <> ""){
             if($locationSeq <> $seq) {
                $messageText = "Location is saved with same Location Name . please choose another Location name";
             }
           }
        
    }  
    if($messageText != null && $messageText != ""){
      $div = "         <div class='ui-widget'>
                       <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'> 
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
                               <strong>Error during save Location :</strong> <br/>" . $messageText . "</p>
                       </div></div>" ; 
    }else{
       try{
           $isCreateDir = $_POST["isCreateDir"];
           if($isCreateDir == "on"){
               if($locationSeq == "" && $messageText == ""){
                    if(file_exists("../../Repository/" . $folderName)){
                        $messageText = "Folder with same name already exists on server";     
                    }else{
                        mkdir("../../Repository/" . $folderName);
                    }
               }else{
                        if($messageText == "") {
                            $oldName = $_POST["folderName"];
                            if(!file_exists("../../Repository/" . $folderName)){          
                                rename("../../Repository/" . $oldName , "../../Repository/" . $folderName); 
                            }
                        }
               }        
           } 
       }catch(Exception $e){
           $logger = Logger::getLogger($ConstantsArray["logger"]);
           $logger->error("Error During Create Directory : - " . $e->getMessage()); 
       } 
        
      
        $LDS = LocationDataStore::getInstance();
        $LDS->Save($location);
        $messageText = "Location Saved Successfully";
        $div = "<div class='ui-widget'>
                       <div  class='ui-state-default ui-corner-all' style='padding: 0 .7em;'> 
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
                               <strong>Message:</strong>&nbsp;" . $messageText . "</p> 
                       </div></div>";
       $location = new Location();
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
      
        <td class="ui-widget-header" style="padding:10px 10px 10px 10px;">Enter New Location Details </td>
        </tr>
      <tr>
        <td class="ui-widget-content">
            <form name="frm1" method="post" action="CreateLocation.php">
            <input type="hidden" name="seq" id="seq" value="<?php echo ($location->getSeq());?>" /> 
             <input type="hidden" name="folderName" id="folderName" value="<?php echo ($location->getLocationFolder());?>" />    
                <table width="100%" border="0" style="padding:10px 10px 10px 10px;">                
                    
                  <tr>
                    <td width="22%">Location Name :</td>
                    <td width="40%"><input name="location" type="text" size="50" value="<?php echo($location->getLocationName());?>" >
                    </td><td><span style="color:#FF0000; margin-bottom:15px;" >*</span></td>
                  </tr>
                  <tr>
                    <td>Location Details :</td>
                    <td><textarea name="details" id="details" cols="38" rows="4" ><?php echo($location->getLocationDetails());?></textarea>
                      &nbsp;</td>
                  </tr>
                  <tr>
                    <td>Create Directory</td>
                    <? 
                        $checkedStr = $location->getHasDirectory() == 1 ? "checked" : '';
                    ?>
                    <td><input type="checkbox" id="isCreateDir" name="isCreateDir" <?echo $checkedStr?> ></td>
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

    
    
    
    
   
     
      

    </body>
</html>


