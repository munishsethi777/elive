<?php
    
    require_once('IConstants.inc');
    require_once($ConstantsArray['dbServerUrl'] ."/Utils/FileSystemUtils.php");
    require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserWQD.php");  
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDFileDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");       
    if(empty($_GET['locSeq'])){
        die;   
    }
    $locationSeq = $_GET['locSeq'];

    $FDS= FolderDataStore::getInstance();
    $folders = $FDS->FindByLocation($locationSeq);
    foreach($folders as $folder){
     echo "<br><a href='showWQDData.php?folSeq=".$folder->getSeq()."'>". $folder->getFolderName() ."</a><br>" ; 
    }
?>
