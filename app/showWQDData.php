<? ob_start(); ?>
<?php
      session_start();
       
      require_once('IConstants.inc');
      require_once($ConstantsArray['dbServerUrl'] ."/Utils/FileSystemUtils.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserWQD.php");  
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDFileDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/LocationDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php"); 
      if(empty($_GET['folSeq'])){
        die;   
      }
      $folderSeq = $_GET['folSeq'];
      $folder = FolderDataStore::getInstance()->FindBySeq($folderSeq);
      $locationSeq = $folder->getLocationSeq();
      $location = LocationDataStore::getInstance()->FindBySeq($locationSeq);
      
      if($location->getIsPrivate()==1){
            if(!isset($_SESSION["userlogged"])){
                $_SESSION['httpUrl'] = $_SERVER['REQUEST_URI'];
                header("location: index.php?err=true&locSeq=". $locationSeq);
            }else if($_SESSION["userlogged"]["locSeq"] != $locationSeq){
                header("location: index.php?err=true&locSeq=". $locationSeq);
                die;
            }
            
      }
?>
<!DOCTYPE html>
<html>
    <head>     
          <?include("_jsInclude.php");?>
  
    
    </head>
    <body>
    <?
        include("_includeHeader.php");
        if(isset($_SESSION["userlogged"])){
             include("logOutButton.php");
        }
    ?>
        <a class="ui-state-active" style="font-size:12px;padding:3px;font-weight:bold" target="_blank" href="showAdvanceChart.php?folSeq=<?echo $folderSeq?>">
                Show Advance Search
            </a>
        <?include("_includeShowWQDData.php");?>
        
    </body>
</html>
<? ob_flush(); ?>