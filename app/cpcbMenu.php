<?
    require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/FolderDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/UserDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."BusinessObjects/User.php");
    $lsp = $_GET['lsp'];
   $FDS = FolderDataStore::getInstance();
   $folders = $FDS->FindByLocation($lsp);


?>
<script>
  $(function() {
        $('.dynamicData').click(function() {
             $('<form action="cpcbDynamicData.php" method="POST"/>')
                .append($('<input type="hidden" name="locSeq" value="' + this.id + '">'))
                .appendTo($(document.body)) //it has to be added somewhere into the <body>
                .submit();
        });
        $(".menuDiv a").button();
        

        
        $(".uiMenu").menu();
        $("#<?echo $menuItem?>").addClass("activeMenu");

 });
</script>
  <style>

    .menuDiv,.menuDiv1{
      width:1200px;
      border:0px solid silver;
      margin:auto;
      
    }
    .menuDiv a, .settingMenu{
      margin:0px 5px 0px 0px;
      padding:4px 20px 4px 20px;
      display:inline-block;
      font-size:11px;
    }
    .menuDiv a:hover,.menuActive{
      color:#EEE;
    }
    .menuDiv .ui-button-text-only .ui-button-text {
       padding:0px;
    }
	
	/*new styles for new menu*/
	.uiMenu {
		float:left;
		margin:3px 0px 6px 0px;
	}
	.uiMenu li{
		display: inline;
		margin-right:4px !important;
  		
  	}
  	.ui-menu-item a{
  		font-size:11px;
  		font-family:Trebuchet MS,Tahoma,Verdana,Arial,sans-serif;
  		color:#555555;
  		text-decoration:none
  	}
  	.uiMenu .ui-menu{
  		width:120px;
  		line-height:30px;
  	}
  	.activeMenu,.settingMenu{
        border: 1px solid #d9d6c4 !important;
        background: #007BC7 url("admin/css/flatblue/images/ui-bg_fine-grain_0_007BC7_60x60.png") 50% 50% repeat !important;
        font-weight: normal !important;
        color: #ffffff !important;
    }
    .activeMenu a{
    	color:#EEE;
    }
  </style>

<?
$userDataStore = UserDataStore::getInstance();
$user= $_SESSION["userlogged"];
$userSeq =  $user->getSeq();
$stationtypes = $userDataStore->getAllStationType($userSeq);?>
<div class="menuOuterDiv ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header">
	
	
	<!-- New menu goes here -->
	<div class="menuDiv1">
		<ul class="uiMenu">
		  <li id="mapMenu"><a href="cpcbMap.php">Live Map</a></li>
		  <?foreach($stationtypes as $type){
		        if($type == "aqms"){
		            echo '<li id="reportAQMSMenu"><a href="cpcbReportMultiStation.php">AQMS</a></li>';
		        }else if($type == "stack"){
		            echo '<li id="reportCEMSMenu"><a href="cpcbStackReportMultiStation.php">CEMS</a></li>';
		        }else if($type == "effluent"){
		            echo '<li id="reportEFFLUENTMenu"><a href="cpcbEffluentReportMultiStation.php">EFFLUENT</a></li>';
		        }else if($type == "aqmsp"){
		            echo '<li id="reportAQMSPMenu"><a href="cpcbAQMSPReportMultiStation.php">AQMSP</a></li>';
		        }
		    }?>
		  <li id="comments"><a  href="cpcbValidationExemptionsComments.php">Data Valdations & Comments</a></li>
		  <?
		    session_start();
		    $userLogged = new User();
		    $userLogged = $_SESSION["userlogged"];
		    $userName = "";
		    if($userLogged){
		        $userName = $userLogged->getUserName();
		    }
		
		
		if($userName  == "cpcb" || $userName == "bhoomi_cpcb"){
			$url = "ajax_cpcb.php?method=exportStatusReport&userSeq=".$user->getSeq();
			?>
		    <li id="exemption"><a  href="cpcbExemptions.php">Data Exemption</a></li>
			<li><a href="<?echo $url?>">Download Status</a>
				<!--  <ul>
			      <li><a href="<?echo $url?>&reportType=online">Live Stations</a></li>
			      <li><a href="<?echo $url?>&reportType=delay">Delayed Stations</a></li>
			      <li><a href="<?echo $url?>&reportType=offline">Offline Stations</a></li>
			    </ul>-->
			</li>
		  <?}?>
		   <?php if($userName = "ltuser" || $userName  == "cpcb" || $userName == "bhoomi_cpcb"){?>
		  		<li id="remoteCalibrationMenu"><a  href="remoteCalibration.php">Remote Calibration</a></li>		
		  <?php }?>
		</ul>
		
	<?
	    session_start();
	    if(isset($_SESSION["userlogged"])){
	        $user = $_SESSION["userlogged"];
	        echo ("<a class='settingMenu' style='float:right' href='ChangeUserPassword.php'>Change Password</a>");
	        echo ("<a class='settingMenu' style='float:right' href='logout.php'><b>". $user->getUserName() ."</b> - Logout</a>");
	    }
	?>
	</div>
	<!-- New menu ends here -->
</div>