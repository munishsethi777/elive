<?php
   require_once('IConstants.inc');
   require_once($ConstantsArray['dbServerUrl'] ."BusinessObjects/User.php");

   //NEW CODE FOR LOGIN CHECK Starts
   session_start();
   if($_SESSION["userlogged"]==""){
        $_SESSION['httpUrl'] = $_SERVER['REQUEST_URI'];
        header("location: index.php");
        die;
   }else{
        $userLogged = $_SESSION["userlogged"];
        $isUserLogged = true;
   }
   //NEW CODE FOR LOGIN CHECK Ends

    /**if($locSeqParam != null){
        $LDS = LocationDataStore::getInstance();
        $location = $LDS->FindBySeq($locSeqParam);
        $isPrivate = $location->getIsPrivate();
        if($isPrivate == "1"){
            session_start();
            $isUserLogged = false;
            $userLogged = new User();

            if($_SESSION["userlogged"]=="")
            {
                $_SESSION['httpUrl'] = $_SERVER['REQUEST_URI'];
                header("location: login.php");

            }else{
                $userLogged = $_SESSION["userlogged"];
                $isUserLogged = true;
            }
        }
    }**/
?>