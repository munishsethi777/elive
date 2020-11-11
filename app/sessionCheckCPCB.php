<?php
 require_once('IConstants.inc');
 require($ConstantsArray['dbServerUrl'] ."BusinessObjects/User.php");
 
    //session_register("userlogged");
    session_start();
    $isUserLogged = false;
    $userLogged = new User();
    
    if(!isset($_SESSION["userlogged"])){
        $_SESSION['httpUrl'] = $_SERVER['REQUEST_URI'];
        header("location: logincpcb.php");
    }else{
        $userLogged = $_SESSION["userlogged"];
        $isUserLogged = true;
    }    
?>