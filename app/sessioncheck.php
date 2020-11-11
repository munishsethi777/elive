<?php
   require_once('IConstants.inc');
   require_once($ConstantsArray['dbServerUrl'] ."BusinessObjects/User.php");

    if($locSeqParam != null){
        $LDS = LocationDataStore::getInstance();
        $location = $LDS->FindBySeq($locSeqParam);
        $isPrivate = $location->getIsPrivate();
        if($isPrivate == "1"){
            session_start();//("userlogged");
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
    }
?>