
<?
    $locSeq = 0;
    if($location != null){
        $locSeq = $location->getSeq();
    }
    if($locSeq == 0 && $_GET != null){
        $locSeq = $_GET["locSeq"];
    }
    if($locSeq == "6"){
?>
<div align="center" style="width:100%;background-color: rgb(204,229,249);"/>
    <img src="images/jsplbanner.jpg" align="middle">
</div>
<?
    }else{
?>
<div style="background-image: url(images/top_back.jpg);height:140px;width:100%"/>
    <table width="1200px" align="center" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding:6px;">
                <img src="images/logo.png"/>
            </td>
            <td align="left" style="font-style:calibri;font-size:26px;color:green">
                
                    Technology to Keep track of your Environment From Anywhere, Real Time. 
                        <br><label style="font-size:11px">(beta ver 1.0)</label>
            </td>
        </tr>
    </table>
</div>
<?
    }
 ?>