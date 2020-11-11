<?php
require_once("../IConstants.inc");
require_once($ConstantsArray["dbServerUrl"] . "DataStoreMgr/UserDataStore.php");
require_once($ConstantsArray['dbServerUrl'] ."/Utils/MailerUtils.php");
$call = "";
if(isset($_REQUEST["call"])){
    $call = $_REQUEST["call"];
}
$response = array();
switch($call){
    case "generateOTP":{
        try{
            $seq = $_REQUEST["id"]; 
            $user_data_store = UserDataStore::getInstance();
            $user = $user_data_store->FindBySeq($seq);
            $otp = rand(100000,1000000);
            $otpgenearatedate = new DateTime();
            $user->setOTP($otp);
            $user->setOTPGenerateDate($otpgenearatedate);
            $user_data_store->updateOtp($user);
            $content = file_get_contents("../EmailTemplates/otpsend.html");
            $phAnValues = array();
            $phAnValues["OTP"] = $otp;
            $phAnValues["TIME"] = $otpgenearatedate->format('Y-m-d H:i:s');
            $phAnValues["USER"] = $user->getUserName();
            $content = MailerUtils::replacePlaceholders($phAnValues,$content);
            $to = $user->getOTPEmailId();
            //$to = "rajkaran.singh.grewal5@gmail.com";
            $subject = "OTP Request at EnvirotechLive.com";
	        $headers = "From: noreply@envirotechlive.com" . "\r\n" . "CC: amandeepdubey@gmail.com";
            $from = "noreply@envirotechlive.com";
            $result = MailerUtils::sendEmailNew($to,$from,$subject,$content);
            if(!$result){
                throw new Exception("Mail Sent Failed");
            }
            $response["success"] = "1";
            $response["message"] = "$otp";
        }catch(Exception $e){
            $response["success"] = "0";
            $response["message"] = $e->getMessage();
        }
        echo json_encode($response);
        break;
    }
    case "sendForgotPasswordEmail":{
        try{
            $username = $_REQUEST["username"];
            if(empty($username)){
                throw new Exception("Invalid Execution. Username needs to be entered");
            }
            $user_data_store = UserDataStore::getInstance();
            $user = $user_data_store->FindByUserName($username);
            if(empty($user)){
                throw new Exception("Invalid Execution. User not found");
            }
            $otp = rand(100000,1000000);
            $otpgenearatedate = new DateTime();
            $user->setOTP($otp);
            $user->setOTPGenerateDate($otpgenearatedate);
            $user_data_store->updateOtp($user);
            $content = file_get_contents("../EmailTemplates/forgotpassword.html");
            $phAnValues = array();
            $phAnValues["OTP"] = $otp;
            $phAnValues["TIME"] = $otpgenearatedate->format('Y-m-d H:i:s');
            $phAnValues["USER"] = $user->getUserName();
            $phAnValues["LINK"] = $ConstantsArray["applicationURL"] . "forgotPassword.php?id=" . $user->getSeq();
            $content = MailerUtils::replacePlaceholders($phAnValues,$content);
            $to = $user->getOTPEmailId();
	        $subject = "Forgot Password Request at EnvirotechLive.com";
	        $headers = "From: noreply@envirotechlive.com";
            $from = "noreply@envirotechlive.com";
            $result = MailerUtils::sendEmailNew($to,$from,$subject,$content);
            if(!$result){
                throw new Exception("Mail Sent Failed");
            }
            $response["success"] = "1";
            $response["message"] = "Mail Sent Sucessfully";
        }catch(Exception $e){
            $response["success"] = "0";
            $response["message"] = $e->getMessage();
        }
        ob_end_clean();
        echo json_encode($response);
        break;
    }
    case "forgotPassword":{
        try{
            $id = $_REQUEST["id"];
            $otp = $_REQUEST["otp"];
            $password = $_REQUEST["password"];
            $confirm_password = $_REQUEST["confirmPassword"];
            if($password != $confirm_password){
                throw new Exception("passwords do not match");
            }
            $user_data_store = UserDataStore::getInstance();
            $user = $user_data_store->FindBySeq($id);
            $time = new DateTime();
            $otptime = new DateTime($user->getOTPGenerateDate());
            $validationtime = $otptime->modify('+3 minutes');
            if($time > $validationtime){
                throw new Exception("OTP has Expired");
            }
            if($user->getOTP() != $otp){
                throw new Exception("OTP does not match");
            }
            $user->setPassword(SecurityUtil::Encode($password));
            $user_data_store->updatePassword($user);
            $response["success"] = "1";
            $response["message"] = "User Password has been updated";
        }catch(Exception $e){
            $response["success"] = "0";
            $response["message"] = $e->getMessage();
        }
        echo json_encode($response);
        break;
    }
}