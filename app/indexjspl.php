<?
    session_start();
    require_once('IConstants.inc');
    require_once($ConstantsArray['dbServerUrl'] ."/Utils/FileSystemUtils.php");
    require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserWQD.php");  
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDFileDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/LocationDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/UserDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
    require_once($ConstantsArray['dbServerUrl'] . "Utils//StringUtils.php");               
    
    $locationSeq = 6;
    //$locationSeq = 3;
    $location = LocationDataStore::getInstance()->FindBySeq($locationSeq); 
    $div = "";
    $message="";
if($_POST["submit"]<>"")
{
    
    $username = $_POST['username'];
    $password = $_POST['userpassword'];
    
      $message = validator::validateform("User Name",$username,56,false); 
      if($messageText != null && $messageText != ""){
          $messageText = $messageText . "<br/>". validator::validateform("Password",$password,56,false); 
      }else{
           $messageText =  validator::validateform("Password",$password,56,false);
      }
      if($messageText == ""){
          $UDS = UserDataStore::getInstance();
          $user = $UDS->FindByUserName($username);
          if($user != null && $user <> ""){
              if($user->getDecodedPassword() == $password ){
                  
                  $arr = new ArrayObject();
                  $arr['username'] = $user->getUserName();
                  $arr['seq'] = $user->getSeq();
                  $arr['locSeq'] = $user->getLocationSeq();
                  $_SESSION["userlogged"] = $arr;         
                  header("Location:indexjspl.php?locSeq=".$locationSeq); 
              }else{
                 $messageText = "Invalid User Name or Password"; 
              }
          }
      }else{
          $messageText = "Invalid User Name or Password";
      } 
       if($messageText <> "") {
           $div = StringUtils::getMessage("Login",$messageText,true);
       } 
}
    if($_GET['err'] == 'true'){
       $div = StringUtils::getMessage("Data Viewing","Please login to view information",true); 
    }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"

      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="http://www.svennerberg.com/examples/google_maps_3/css/style.css" type="text/css" media="all" />
	<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
</head>
<?include("_jsInclude.php");?>
<body style="margin:0px 0px 0px 0px">
<?include("_includeHeader.php");?>
<?
    if(isset($_SESSION["userlogged"])){
        include("logOutButton.php");
    }
?>
<table width="1200px" align="center"><tr><td>
<div style="width:22%;float:left;height:600px">
    <div class="ui-widget-content" style="width:90%;padding:10px;">
           <?php echo($div) ?>
           <?
                if(!isset($_SESSION["userlogged"])){
           ?>
           <form name="frm1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?locSeq=<?echo $locationSeq?>">        
                <table width="100%" border="0" style="padding:10px 10px 10px 10px;">
                  <tr>
                    <td width="22%">Username:</td>
                    <td width="78%"><input name="username" type="text" size="20">
                      &nbsp;</td>
                  </tr>
                  
                  <tr>
                    <td width="22%">Password:</td>
                    <td width="78%"><input name="userpassword" type="password" size="20">
                      &nbsp;</td>
                  </tr>
                  
                  <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" name="submit" value=" Login " />
                         
                        <input type="reset" name="Reset" value="Reset">
                        
                    
                    </td></tr>
                    
                </table>
              </form> 
              <?
                }else{?>
                Welcome <?
                    echo $_SESSION["userlogged"]["username"]
                    ?><br>
                Click on Station to view its live data
              <?}?>
         </div>
<?
    $FDS= FolderDataStore::getInstance();
    $folders = $FDS->FindByLocation($locationSeq);
    foreach($folders as $folder){
     echo "<br><a class='btn' target='_new' href='showWQDData.php?folSeq=".$folder->getSeq()."'>". strtoupper($folder->getFolderName()) ."</a><br>" ; 
    }
?>    
       <div id="map" style="width:100%;height:400px;margin-top:10px"></div>
            
</div>

<div style="width:77%;float:right;padding:3px;" class="ui-widget-content">
    <?include("jspl/includeHTML.html");?>
</div>
        
 </td></tr></table>        
<script>
    $(".btn").button();
    loadJSPLMap();
	  
	function loadJSPLMap(){
        var icn = "images/factorybig.png";
		var latlng = new google.maps.LatLng(21.89, 83.39);  
				var options = {  
					zoom: 4,
					center: latlng,
					mapTypeId: google.maps.MapTypeId.TERRAIN 
				};  
		
				var map = new google.maps.Map(document.getElementById('map'), options);  
			    var marker_Raigarh = new google.maps.Marker({
                    position: new google.maps.LatLng(21.895001,83.395554), 
                    map: map,
                    title: 'JSPL Raigarh Station',
                    clickable: true,
                    icon: icn
                });
                //event
                google.maps.event.addListener(marker_Raigarh, 'click', function(event) {
                    location.href ="indexjspl.php";
                });
	  }
      
	</script>
    
    
    <? ob_flush(); ?>