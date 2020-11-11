<?php
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "/Utils/DateUtils.php");
$dateStr = date("Y-m-d", mktime(0,0,0,0,0,0));
echo $dateStr;