<?php
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr//HighValueRuleReminderDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils//ExportUtils.php");
if (isset($_POST["call"]) && $_POST["call"] == "exportLogs"){
    
    $folderSeq = $_POST["folderSeq"];
    $fromDateForm = $_POST["fromDate"];
    $toDateForm = $_POST["toDate"];
    $fromDate = new DateTime($fromDateForm);
    $toDate = new DateTime($toDateForm);
    $toDate = $toDate->add(new DateInterval('P1D'));

    $fromDateStr = $fromDate->format("Y/m/d  H:i:s");
    $toDateStr = $toDate->format("Y/m/d  H:i:s");
    $HVRRDS = HighValueRuleReminderDataStore::getInstance();
    $logs = $HVRRDS->getHighValueReminderLogs($folderSeq,$fromDateStr, $toDateStr);
    ExportUtils::ExportData($logs);        
}
?>
