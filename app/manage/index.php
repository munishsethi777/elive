<?php  
require_once('IConstants.inc'); 
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr/UserDataStore.php");
$div = "";
if($_POST["submit"]<>"")
{
    $username = $_POST["username"];
    $password = $_POST["password"];
    $password= SecurityUtil::Encode($password);
    $UDS = UserDataStore::getInstance();
    $manager = $UDS->FindManagerByUsernamePassword($username, $password);

    if($manager != NULL){
            session_start();
            $arr = new ArrayObject();
            $arr['username'] = $manager->getUserName();
            $arr['seq'] = $manager->getSeq();
            $arr['locSeq'] = $manager->getLocationSeq();
            $arr['isAdmin'] = $manager->getUserName() == "admin";
            $_SESSION["managerSession"] = $arr;
                
                header("Location:showFolders.php");
                $msg="Welcome";    
    }
    else
    {
                $msg="-Invalid Password"; 
                 $div = "<div class='ui-widget'>
                       <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'> 
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
                               <strong>Error During Admin login :</strong> <br/>" . $msg . "</p>
                       </div></div>" ;     
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <link type="text/css" href="css/cupertino/jquery-ui-1.8.14.custom.css" rel="stylesheet" />
        <link type="text/css" href="css/custom.css" rel="stylesheet" /> 
        <? include("../_InspiniaInclude.php");?>
    </head> 
    <body class="gray-bg">
        <div class="middle-box text-center loginscreen animated fadeInDown">
            <div>
                <div>
                    <h2 class="logo-name"><img src="images/logo.png" alt=""></h2>
                </div>
                <h3>Welcome to Envirotech Live</h3>
                <p>Manager Login in. To see it in action.</p>
                <form class="m-t" method="post" role="form" name="frm1" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <input type="hidden" value="submit" name="submit">
                <div class="form-group">
                    <input type="text" name="username" class="form-control" placeholder="Username" required="">
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" name="password" placeholder="Password" required="">
                </div>
                <button type="submit" class="btn btn-primary block full-width m-b">Login</button>
                <a href="#"><small>Forgot password?</small></a>
            </form>   
           </div>
        </div> 
    </body>
</html>
<script language="javascript">
    <?if(!empty($div)){?>
        showNotification("Invalid Password")      
    <?}?>
</script>


