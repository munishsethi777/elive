<?php
    require_once('IConstants.inc');
    require_once($ConstantsArray['dbServerUrl'] ."BusinessObjects/User.php");
    session_start();
    $userName = "";
    if(isset($_SESSION["userlogged"])){
        $user = $_SESSION["userlogged"];
        $userName = $user->getUserName();
    }
    session_destroy();
    if (strpos($userName,'bhoomi') !== false){
        header("Location:indexbhoomi.php");
    }else{
        header("Location:index.php");
    }

?>
