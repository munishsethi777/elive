<?php
require_once($ConstantsArray['dbServerUrl'] . "Managers//CalibrationMgr.php");
$calMgr = CalibrationMgr::getInstance();
$call = $_GET["call"];
if($call == "getCalibrationHistory"){
	$userSeq = $_GET["userSeq"];
	$folderSeq = $_GET["folderSeq"];
	$calibrationHistory = $calMgr->getCalibrationHistoryForGrid($userSeq,$folderSeq);
	$calibrationHistory = json_encode($calibrationHistory);
	echo $calibrationHistory;
	return;
}
//$locations = $LDS->FindAll();
?>


