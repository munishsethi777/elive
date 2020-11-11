<?php 
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDStackDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDOpacityFileDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
  
  
  $limit = 500;
  $locationSeqs = $_GET["locs"];
  $lastSeq = $_GET['lastSeq'];
   
   
  $XML = "<?xml version='1.0' encoding='UTF-8'?>";
  $parentTag = "ELiveFullDataCall";
  
  $XML .= '<'. $parentTag .' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">';   
  if($lastSeq != ""){
  	    $FDS = FolderDataStore::getInstance();
  		$folders = $FDS->getIsOpacityByLocations($locationSeqs);
  		$opacityLocations = array();
  		$stackLocations = array();
  		foreach ($folders as $folder){
  			$isOpacity = $folder["isopacity"];
  			$lSeq = $folder["locationseq"];
  			if(!empty($isOpacity)){
  				if(!in_array($lSeq, $opacityLocations)){
  					array_push($opacityLocations, $lSeq);
  				}
  			}else{
  				if(!in_array($lSeq, $stackLocations)){
  					array_push($stackLocations, $lSeq);
  				}
  			}
  		}
  		$opacityFiles = array();
  		if(!empty($opacityLocations)){
  			$WQDOpacityDS = WQDOpacityFileDataStore::getInstance();
  			$locationSeqs = implode(",", $opacityLocations);
  			$opacityFiles = $WQDOpacityDS->getWQDOpacityDataByLocationSeqsAndLastSeqs($locationSeqs,$lastSeq,$limit);
  		}
  		$stackFiles = array();
  		if(!empty($stackLocations)){
  			$WQDStackDS = WQDStackDataStore::getInstance();
  			$locationSeqs = implode(",", $stackLocations);
  			$stackFiles = $WQDStackDS->getWQDDataByLocationSeqsAndLastSeqs($locationSeqs,$lastSeq,$limit);
  		}
        $files = array_merge($opacityFiles,$stackFiles);
        $XML .= "<WQDFilesData>";
        foreach($files as $file){
            $XML .= getWQDDataXML($file);
        }
        $XML .= "</WQDFilesData>"; 
  }
      
  $XML .= "</". $parentTag .">";
  header('Content-Type: text/xml');
  //header("HTTP/1.0 200 OK");
  echo $XML;
  die;
  
  

  
  function getWQDDataXML($row){
  		$reportNo =  $row["wqdfiledatareportno"];
  		if(empty($reportNo)){
  			$reportNo = 1;
  		}
  		$checkSum =  $row["wqdfiledatachecksum"];
  		if(empty($checkSum)){
  			$checkSum = 0;
  		}
        $XML .= "<wqdfiledata>";
            $XML .= "<wqdfiledataseq>". $row["wqdfiledataseq"] ."</wqdfiledataseq>";
            $XML .= "<wqdfolderseq>". $row["wqdfolderseq"] ."</wqdfolderseq>";
            $XML .= "<wqdfiledatadated>". $row["wqdfiledatadated"] ."</wqdfiledatadated>";
            $XML .= "<wqdfiledatareportno>". $reportNo ."</wqdfiledatareportno>";
            $XML .= "<wqdfiledatachecksum>". $checkSum ."</wqdfiledatachecksum>";
            
            for($i=1;$i<=15;$i++){
                $val =  $row["ch".$i."value"];
                $status = $row["ch".$i."status"];
                $xsi = getXSI($val);
                $XML .= "<ch".$i."value ". $xsi .">". $val ."</ch".$i."value>";
                $XML .= "<ch".$i."status ". $xsi .">". $status ."</ch".$i."status>";
            }               
        $XML .= "</wqdfiledata>";
        return $XML;
  }
  function getXSI($val){
    $xsi = "";
    if($val == ""){
        $xsi = "xsi:null=\"true\"";
    }
    return $xsi;  
  }
  
?>