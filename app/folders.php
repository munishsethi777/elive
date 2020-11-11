<?include("sessioncheck.php");?>
<?php
  $locationSeq = $_POST['locationSeq'];
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
  $FDS = FolderDataStore::getInstance();
  $user = $_SESSION["userlogged"]; 
  $folderArr = $FDS->FindByLoationSeq($locationSeq,$user->getSeq());       
?>
   <!DOCTYPE html>
<html>
    <head>
    <?php include("_jsInclude.php");?>
    </head>
    <body>
   <? include("leftButtons.php");?>
    
    <Div class="rightAdminPanel">
        <? include("logOutButton.php"); ?>
    
         
    <table width="80%" border="0">
     
      <tr>
        <td class="ui-widget-header" style="padding:10px 10px 10px 10px;">List of Available Location Folders</td>
        </tr>
      <tr>
        <td class="ui-widget-content">
         <form name="folderForm" method="post" action="" >
               <input type="hidden" name="locationSeq" id="locationSeq" />
               <table width="100%" border="1" bordercolor="silver" style="border-style:dashed;border-width:thin;border:thin;border-color:#CCCCCC">              
                  <? foreach($folderArr as $folder){
                          echo "<tr>                            
                            <td><a href='javascript:getFolders(". $folder->getSeq() . ")'> ". $folder->getFolderName() . "</a></td>" ;
                          "</tr>";
                      }
                  ?>
                </table>
               </from>
               </td>
        </tr>
    </table>
    </Div>
   
    
    </body>
</html>