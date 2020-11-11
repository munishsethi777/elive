<?php include("sessioncheckPrivateOnly.php");?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="js/dist/addDropMenu.css" rel="stylesheet" type="text/css">
</head>
<body style="margin:0px 0px 0px 0px" class="fullBody">
	<? $menuItem = "statusReport";?>
	<?include("cpcbHeader.php");?>
	<?include("cpcbMenu.php");?>
	<div style="margin:auto;width:1200px;min-height:250px;margin-top:3px;padding:4px;" class="ui-widget-content">
    <div style="border:1px silver solid;padding:10px;">
        <div class="responseDiv" style="padding:10px;display:none"></div>
        <p>Please Select Station, Pollutant, From and To Dates Ranges to validate data or add comments to it.</p>
        <form name="commentsMasterForm" id="commentsMasterForm" method="POST" action="#">
            <table cellspacing='0' cellpadding='0'  class="comentsMasterFormTable" border="0">
                <tr>
                    <th class="ui-state-active">Type</th>
                    <td align="left" colspan="2">
                        <input name="request" value="live" checked type="radio" />Live
                        <input name="request" value="delay" type="radio" />Delay
                        <input name="request" value="offline" type="radio" />Offline
                    </td>
                    <th class="ui-state-active">From Date</th>
                    <td><input type="text" size="15" name="fromDate" id="fromDate"></td>
                </tr>
                <tr>
                    <td align="right" colspan="3">
                        <input type ="button" value="Export" class="btn submitForm" />
                    </td>
                    <th class="ui-state-active">To Date</th>
                    <td><input type="text" size="15" name="toDate" id="toDate"></td>
                     
                </tr>
                <tr>                    
                   
                </tr>

            </table>
        </form>
    </div>
</body>
</html>
<script src="js/jquery-3.1.0.min.js"></script>
<script src="js/dist/addDropMenu.js"></script>
<script>

$(document).ready(function() {	
    $('#fromDate').datetimepicker({
        maxDate: new Date(),
        dateFormat: 'dd-mm-yy',
        changeMonth: true,

    });
    $('#toDate').datetimepicker({
        maxDate: new Date(),
        dateFormat: 'dd-mm-yy',
        changeMonth: true,

    });
});
</script>