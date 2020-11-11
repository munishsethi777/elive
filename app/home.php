<?
    session_start();
    require_once('IConstants.inc');
    require_once($ConstantsArray['dbServerUrl'] ."/Utils/FileSystemUtils.php");
    require_once($ConstantsArray['dbServerUrl'] ."/Utils/MailerUtils.php");
    require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserWQD.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDFileDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/LocationDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/UserDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
    require_once($ConstantsArray['dbServerUrl'] . "Utils//StringUtils.php");

if ($_POST['submit'] == " Login "){
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
                  header("Location:index.php?locSeq=".$user->getLocationSeq());
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

?>
<?
if ($_POST['submit'] == "Submit"){
	$name = $_POST['yourName'];
	$phone = $_POST['phoneNumber'];
	$email = $_POST['emailId'];
	$industry = $_POST['industry'];
	$query = $_POST['query'];

	$txt = "Name: ". $name;
	$txt .= "<br>Phone: ". $phone;
	$txt .= "<br>EmailId: ". $email;
	$txt .= "<br>Industry: ". $industry;
	$txt .= "<br>Query: ". $query;

	$to = "munishsethi777@gmail.com";
	$subject = "Demo Request at EnvirotechLive.com";
	$headers = "From: noreply@envirotechlive.com" . "\r\n" .
"CC: amandeepdubey@gmail.com";
    $from = "noreply@envirotechlive.com";
    MailerUtils::sendMandrillEmailNotification($txt,$subject,$from,$to);
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

<table style="width:1200px;" id="mainBody" align="center"><tr><td>
<div style="width:22%;float:left;margin:10px;">
    <div class="ui-widget-content" style="width:100%;">
               <?php echo($div) ?>
               <?
                    if(!isset($_SESSION["userlogged"])){
               ?>
               <form name="frm1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?locSeq=<?echo $locationSeq?>">
                    <table width="100%" border="0" style="padding:5px;">
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
                        //echo $_SESSION["userlogged"]['username'];
                        ?><br>
                    Click on Station to view its live data
                  <?}?>
             </div>
     <h3>List of Stations</h3>
     <div id="map" style="width:100%;height:360px;border:none;float:left;margin-top:5px"></div>
</div>
<div style="float:left;margin-top:10px;width:72%">


    <p class="ui-widget-content" style="clear:both;width:100%;padding:10px;margin-top:0px; background-color:#ECFFEC; background-image:none">

        <label style="float:left;width:50%;font-size:16px;margin:20px;font-family:calibri">Envirotech Live offers a unique technology solution for industries.<br>
A technology that  connects the air quality data produced by Ambient Air Quality Monitoring stations and Stack Emission Monitoring instruments into one web portal that offers complete Accessibility, Analytics and Assurance.
In Compliance with MOEF notification no <br>(No. J-11013/41/2006-IA.II (I)) Dated 6th April 2011
        </label>

        <iframe src="http://player.vimeo.com/video/35742484?title=0&amp;byline=0&amp;portrait=0"
        width="300" height="225" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen>
        </iframe>
    </p>

      <table width="103%" border="0" cellspacing="2" cellpadding="3" style="margin-top:10px;text-align:center;font-weight:bold;clear:both;padding:10px">
      <tr>
        <td class="ui-widget-content">
            <a href="http://www.slideshare.net/Amandeepdubey/envirotech-live-presenations" target="new">
            <img src="images/home_elpresentation.jpg"/><br>
                EnvirotechLive Presentation
            </a>
        </td>
        <td class="ui-widget-content">
            <a href="http://www.slideshare.net/Amandeepdubey/moef-notification-on-online-data-reporting" target="new">
                <img src="images/home_elnotif.jpg"/><br>MOEF Notification on online data reporting
            </a>
        </td>
        <td class="ui-widget-content">
            <a href="http://www.slideshare.net/Amandeepdubey/envirotech-live-brochure" target="new">
                <img src="images/home_elbrochure.jpg"/><br>EnvirotechLive Brochure
            </a>
        </td>
      </tr>
    </table>

</div>

    <table style="clear:both;width:100%;margin:10px;" border="0" cellspacing="2" cellpadding="3">
        <tr colspan="3">
            <td class="ui-widget-content" valign="top" width="25%">
                <b>Contact us at</b><br />
email: Prashant.Dubey@EnvirotechLive.com<br />
phone: +91-9910476179<br />
email: sales@envirotechlive.com<br />
            </td>
            <td valign="top" width="58%"  class="ui-widget-content">
                <b>Request a Demo</b>
				<form action="home.php" name="requestADemo" method="post">
				<table border="0" width="100%" style="font-size:12px">
					<tr><td>Name:</td><td><input type ="text" name="yourName" /></td>
						<td>Industry:</td><td><input type ="text" name="industry" /></td>
					</tr>
					<tr><td>Phone Number:</td><td><input type ="text" name="phoneNumber" /></td>
						<td rowspan="2">Query:</td><td rowspan="2"><textarea name="query"></textarea></td>
					</tr>
					<tr>
						<td>Email Id:</td><td><input type ="text" name="emailId" /></td>
					</tr>
					<tr>
						<td colspan="2" align="right"><input type="submit" name="submit" value="Submit"/></td>
						<td colspan="2" align="left"><input type="reset" name="reset" value="Reset"/></td>
					</tr>
				</table>
				</form>
            </td>
            <td valign="top"  width="25%" >
                <p><a href="images/elbrochure.pdf" target="_blank">Download EnvirotechLive<br />
Brochure here </a></p>
                <p><img src="images/icons_fb.jpg" style="margin:3px;" />
                    <img src="images/icons_tw.jpg" style="margin:3px;"/>
                    <img src="images/icons_yt.jpg" style="margin:3px;"/>
                    <img src="images/icons_in.jpg" style="margin:3px;"/>            </p>
  </td>
        </tr>
    </table>



<td>
<tr>
<table>




<script>
$(".btn").button();


        (function() {
            window.onload = function(){
                loadETechlocations();
            }
        })();

      function loadETechlocations(){
        var icn = "images/factory.png";
        var latlng = new google.maps.LatLng(20.593684, 78.96288);
                var options = {
                    zoom: 4,
                    center: latlng,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };

                var map = new google.maps.Map(document.getElementById('map'), options);
                var marker_Agra = new google.maps.Marker({
                    position: new google.maps.LatLng(27.1766701,78.0080745),
                    map: map,
                    title: 'Agra Station',
                    clickable: true,
                    icon: icn
                });
                //event
                google.maps.event.addListener(marker_Agra, 'click', function(event) {
                    location.href ="http://www.envirotechlive.com/showWQDData.php?folSeq=6";
                });


                var marker_Lucknow = new google.maps.Marker({
                    position: new google.maps.LatLng(26.8465108, 80.9466832),
                    map: map,
                    title: 'Lucknow Station',
                    clickable: true,
                    icon: icn
                });
                //event
                google.maps.event.addListener(marker_Lucknow, 'click', function(event) {
                    location.href ="http://www.envirotechlive.com/showWQDData.php?folSeq=10";
                });


                var marker_Kanpur = new google.maps.Marker({
                    position: new google.maps.LatLng(26.449923, 80.3318736),
                    map: map,
                    title: 'Kanpur Station',
                    clickable: true,
                    icon: icn
                });
                //event
                google.maps.event.addListener(marker_Kanpur, 'click', function(event) {
                    location.href ="http://www.envirotechlive.com/showWQDData.php?folSeq=7";
                });

                var marker_Varanasi = new google.maps.Marker({
                    position: new google.maps.LatLng(25.3176452, 82.9739144),
                    map: map,
                    title: 'Varanasi Station',
                    clickable: true,
                    icon: icn
                });
                //event
                google.maps.event.addListener(marker_Varanasi, 'click', function(event) {
                    location.href ="http://www.envirotechlive.com/showWQDData.php?folSeq=8";
                });
                var marker_MandirMarg = new google.maps.Marker({
                    position: new google.maps.LatLng(28.62960,77.19719),
                    map: map,
                    title: 'Mandir Marg Station',
                    clickable: true,
                    icon: icn
                });
                //event
                google.maps.event.addListener(marker_MandirMarg, 'click', function(event) {
                    location.href ="http://www.envirotechlive.com/showWQDData.php?folSeq=3";
                });


                var marker_PunjabiBagh = new google.maps.Marker({
                    position: new google.maps.LatLng(28.67011,77.13836),
                    map: map,
                    title: 'Punjabi Bagh Station',
                    clickable: true,
                    icon: icn
                });
                //event
                google.maps.event.addListener(marker_PunjabiBagh, 'click', function(event) {
                    location.href ="http://www.envirotechlive.com/showWQDData.php?folSeq=5";
                });


                var marker_RKPuram = new google.maps.Marker({
                    position: new google.maps.LatLng(28.56583,77.17947),
                    map: map,
                    title: 'RK Puram Station',
                    clickable: true,
                    icon: icn
                });
                //event
                google.maps.event.addListener(marker_RKPuram, 'click', function(event) {
                    location.href ="http://www.envirotechlive.com/showWQDData.php?folSeq=4";
                });

                var marker_Raigarh = new google.maps.Marker({
                    position: new google.maps.LatLng(21.895001,83.395554),
                    map: map,
                    title: 'JSPL Raigarh Station',
                    clickable: true,
                    icon: icn
                });
                //event
                google.maps.event.addListener(marker_Raigarh, 'click', function(event) {
                    location.href ="http://www.envirotechlive.com/showWQDData.php?folSeq=6";
                });
      }


    </script>


    <? ob_flush(); ?>
