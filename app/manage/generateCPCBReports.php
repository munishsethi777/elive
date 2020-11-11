<?php 
	include("sessioncheck.php");
	require_once('IConstants.inc');
	require_once($ConstantsArray['dbServerUrl'] . "Utils/NotificationUtil.php");
	$managerSession = $_SESSION["managerSession"]; 
	$isSuccess = false;

	if(isset($_POST["call"])){
	    
		if($_POST["call"] == "generateMonthly"){			
			$type = NotificationUtil::$MONHTLY;
		}else{			
			$type = NotificationUtil::$WEEKLY;
		}
		
		$toDateStr = $_POST["toDate"];
		try{
			$errorMessage = null;
			$notificationUtil = NotificationUtil::getInstance();
			$notificationUtil->generateReportInstant($toDateStr, $type);
			$isSuccess = true;
			$messageText = "Reports Sent Successfully";
		}catch (Exception $e){
			$errorMessage = $e->getMessage();		
			$messageText = "Error : - " .$errorMessage;
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
		<?include("_jsAdminInclude.php");?>
        <?include("../_InspiniaInclude.php");?>
   </head>
    <body>
    <div id="wrapper">
        <? include("leftButtons.php");?>
        <div class="wrapper wrapper-content animated fadeInRight mainDiv">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>CPCB Reports</h5>
                        </div>
                        <div class="ibox-content">
                            <form name="frm1" id="frm1" method="post" action="generateCPCBReports.php" class="form-horizontal">                               
                                <input type = "hidden" name="call" id="call" value="generateWeekly"/>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Select Date For Weekly</label>
                                    <div class="col-lg-4">
                                        <input type="text" name="toDate" id="toDate" placeholder="Select Date" required="required" class="toDate form-control"> 
                                    </div>
                                 </div>                                 
                                <div class="form-group">
                                    <div class="col-lg-offset-2 col-lg-10">
                                        <button class="btn btn-primary" type="submit" onclick="submitForm()">Generate</button>                                        
                                    </div>
                                </div>
                            </form>  
                        </div>
                         <div class="ibox-content">
                            <form name="frm2" id="frm2" method="post" action="generateCPCBReports.php" class="form-horizontal">                                
                                <input type = "hidden" name="call" id="call"/>
                                <input type = "hidden" name="call" id="call" value="generateMonthly"/>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Select Date For Monthly</label>
                                    <div class="col-lg-4">
                                        <input type="text" name="toDate" placeholder="Select Date" required="required" class="toDate form-control"> 
                                    </div>
                                 </div>                                 
                                <div class="form-group">
                                    <div class="col-lg-offset-2 col-lg-10">
                                        <button class="btn btn-primary" type="submit" onclick="submitForm()">Generate</button>                                        
                                    </div>
                                </div>
                            </form>  
                        </div>
                    </div>
                </div>
            </div>
        </div>
   </div>      
  </body>
</html>
<script type="text/javascript">
showResponseNotification("<?echo $isSuccess?>","<?echo $messageText ?>","mainDiv","frm1");
$(document).ready(function (){
	$('.toDate').datetimepicker({format:"m/d/Y",timepicker:false});
});
function generateMonthlyReport(){
	
}
</script>