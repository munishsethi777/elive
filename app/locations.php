<?include("sessioncheck.php");?>
<?
   $user =  $user = $_SESSION["userlogged"];
   require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");   
   $LDS = LocationDataStore::getInstance(); 
   $locations = $LDS->FindByUser($user->getSeq());
?>

<!DOCTYPE html>
<html>
    <head>
    <?php include("_jsInclude.php");?>
    </head>
    <body>
    
    <?php include("leftButtons.php");?>
    
    <Div class="rightAdminPanel">
        <?php include("logOutButton.php"); ?>
    
         
    <table width="80%" border="0">
      <tr>
        <td class="ui-widget-header" style="padding:10px 10px 10px 10px;">Various Locations</td>
        </tr>
      <tr>
        <td class="ui-widget-content">
               <form name="locationForm" method="post" action="" >
               <input type="hidden" name="locationSeq" id="locationSeq" />
               <table width="100%" border="1" bordercolor="silver" style="border-style:dashed;border-width:thin;border:thin;border-color:#CCCCCC">    
                 <? foreach($locations as $location){
                        $path = $serverUrl['serverURL'] . $location->getLocationFolder();
                          echo "<tr>                            
                            <td><a href='javascript:getFolders(". $location->getSeq() . ")'> ". $location->getLocationName() ."</a></td>
                          </tr>";
                      }
                  ?>
               </table>   
               </form>
        
        </td>
        </tr>
    </table>

    
    
    
    
    </Div>
     
     <script language="javascript"> 
           function getFolders(locationSeq){
                 document.locationForm.action = "folders.php"; 
                 document.getElementById('locationSeq').value =  locationSeq
                 document.locationForm.submit();
           }
     </script>
    </body>
</html>


