<?include("_jsIncludeCpcb.php");?>
<style>
.stationReportTable td{
    padding:4px;
}
</style>
<div style="height:80px;width:100%;margin-bottom:0px;background-color:white;"/>

<?
session_start();
$userName = "";
if(isset($_SESSION["userlogged"])){
    $user = $_SESSION["userlogged"];
    $userName = $user->getUserName();
}

if ($userName == "jk"){ ?>
    <table width="1200px" align="center" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding:2px;">
                <img height="100%" src="images/jkLogo.jpg" alt="">
            </td>
            <td align="left" style="font-style:calibri;font-size:26px;vertical-align: middle;">
                    JK LAKSHMI CEMENT LTD. (Jhajjar - Unit)
                     <label style="font-size:12px;display:block">
                        Village-Bajitpur, Tehsil-Matainhail, Dist-Jhajjar HARYANA - 124142
                     </label>
            </td>
        </tr>
    </table>
<? }elseif (strpos($userName,'bhoomi') !== false){?>
    <table width="1200px" align="center" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding:2px;width:150px;">
                <img src="images/bhoomilogo.jpg" alt="">
            </td>
            <td align="left" style="font-style:calibri;font-size:26px;padding-left:20px;color:#2A9D98">
                    Bhoomi Live : Real Time Environment Data Monitoring
    <marquee behavior="scroll" scrollamount="3" direction="left" width="80%">
    <label style="font-size:12pt;display:block;color:red">
    Data available at this portal is as per CPCB prescribed procedure published at <a href="http://www.cpcb.nic.in">cpcb.nic.in</a>
    </label>
    </marquee>

            </td>
        </tr>
    </table>
<? }elseif (strpos($userName,'heat') !== false){?>
    <table width="1200px" align="center" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding:2px;width:150px;">
                <img src="images/heatlogo.jpg" alt="" height="80px">
            </td>
            <td align="left" style="font-style:calibri;font-size:26px;padding-left:20px;color:#2A9D98">
                    Heat Technologies : Real Time Environment Data Monitoring
            </td>
        </tr>
    </table>

<?  }else{  ?>
    <table width="1200px" align="center" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding:2px;">
                <img src="images/logoSmall.png"/>
            </td>
            <td align="left" style="font-style:calibri;font-size:26px;color:green;vertical-align: bottom;">

                    Technology to Keep track of your Environment From Anywhere, Real Time.
                    <marquee behavior="scroll" scrollamount="3" direction="left" width="80%">
                        <label style="font-size:12pt;display:block;color:red">
	            Data available at this portal is as per CPCB prescribed procedure published at <a href="http://www.cpcb.nic.in">cpcb.nic.in</a>
	            </label>
	        </marquee>
            </td>
        </tr>
        
    </table>

<?  }   ?>

<? if (strpos($userName,'heat') !== false){?>
        <div style="top:0px;position:absolute;right:0px">
        	<img src="images/cecblogo.jpg"/>
        </div>
<?}else{?>
		<!--<div style="top:0px;position:absolute;right:0px">
        	<img src="images/cpcblogo.jpg" width="60" height="80"/>
        </div>-->
<?}?>
</div>
