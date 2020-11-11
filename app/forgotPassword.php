<?php
    require_once('IConstants.inc'); 
    include("_jsIncludeCpcb.php");
    require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/UserDataStore.php");
    $seq = "";
    if(isset($_REQUEST["id"])){
        $seq = $_REQUEST["id"];
        $user_data_store = UserDataStore::getInstance();
        $user = $user_data_store->FindBySeq($seq);
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <link type="text/css" href="admin/css/cupertino/jquery-ui-1.8.14.custom.css" rel="stylesheet" />
        <link type="text/css" href="admin/css/custom.css" rel="stylesheet" />
    </head>
    <body>
        <div id="notice" style="text-align: center;"></div>
        <table id="mainTable" align="center" width="40%" border="0">
            <tr>
                <td style="padding:10px 10px 10px 10px;"><?php echo($div) ?></td>
            </tr>
            <tr>
                <td class="ui-widget-header" style="padding:10px 10px 10px 10px;"> EnvirotechLive Forgot Password </td>
            </tr>
            <tr>
                <td class="ui-widget-content">
                    <?php if(isset($_REQUEST["id"])){?>
                        <form id="resetPasswordForm" method="post" action="">
                            <table width="100%" border="0" style="padding:10px 10px 10px 10px;font-size:12px;">
                                <tr>
                                    <td width="35%">Enter OTP</td>
                                    <td width="65%"><input type="text" name="otp"></td>
                                </tr>
                                <tr>
                                    <td width="35%">Enter Password</td>
                                    <td width="65%"><input type="password" name="password"></td>
                                </tr>
                                <tr>
                                    <td width="35%">Enter Confirm Password</td>
                                    <td width="54%"><input type="password" name="confirmPassword"></td>
                                </tr>
                                <tr>
                                    <td width="35%"></td>
                                    <td width="65%"><button type="button"onclick="resetPassword()">Reset Password</button></td>
                                </tr>
                            </table>
                        </form>
                    <?}else{?>
                        <form id="sendForgetPasswordEmailForm" name="frm1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            <table width="100%" border="0" style="padding:10px 10px 10px 10px;font-size:12px;">
                                <tr>
                                    <td width="22%">Enter Username :</td>
                                    <td width="78%"><input name="username" id="username" type="text" size="30"></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>
                                        <button type="button" id="sendNotificationButton" class="btn btn-primary" onclick="sendEmail()">Send Notification</button>

                                        <input type="reset" name="Reset" value="Reset">
                                    </td>
                                </tr>
                            </table>
                        </form>
                    <?}?>
                </td>
            </tr>
        </table>
    </body>
    <script>
        function sendEmail(){
            
            data = $("#sendForgetPasswordEmailForm").serialize();
            data += "&call=sendForgotPasswordEmail";
            $.ajax({
                url:"Actions/OTPAction.php",
                data:data,
                type:"get",
                success: function (data,status,xhr) {   // success callback function
					data = JSON.parse(data);
					if(data.success == 1){
    					alert("OTP emailed successfully. Please use it within 3 minutes");
    					$("#username").val("");
					}else{
						alert(data.message);
					}
    			},
    			error: function (jqXhr, textStatus, errorMessage) { // error callback 
    				alert("Failed to email OTP. Please contact system administrator");
    			}
            });
        }
        function resetPassword(){
            var data = $("#resetPasswordForm").serialize();
            data +="&call=forgotPassword";
            data += "&id=<?php echo "$seq";?>";
            $.ajax({
                url:"Actions/OTPAction.php",
                data:data,
                type:"get",
                success:(data)=>{
                    data = JSON.parse(data);
                    var html = "";
                    if(data.success == "1"){
                        var html = "<label style='font-size:18px'>" + data.message + "</label";
                        
                    }else{
                        var html = "<label style='background-color:red;font-size:18px;padding:10px;'>" + data.message + "</label>";
                    }
                    $("#notice").html(html);
                }
            });
        }
    </script>
</html>