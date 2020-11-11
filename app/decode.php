<?php

    require_once('IConstants.inc');
    require_once($ConstantsArray['dbServerUrl'] . "SecurityUtil/SecurityUtil.php");
    $expression = $_GET["val"];
    if($expression != null){
        echo SecurityUtil::Decode($expression);
    }
    die;


?>
