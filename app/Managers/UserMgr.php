<?php
require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/UserDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "FormValidator//validator.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils//StringUtils.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils//MailerUtils.php");
require_once($ConstantsArray['dbServerUrl'] . "StringConstants.php");
Class UserMgr{

    private static $userMgr;
    public static function getInstance(){
        if (!self::$userMgr)
        {
            self::$userMgr = new UserMgr();
            return self::$userMgr;
        }
        return self::$userMgr;
    }

    public function createAndSendLoginOTP($user){
        $UDS = UserDataStore::getInstance();
        $otp = rand(100000,1000000);
        $otpgenearatedate = new DateTime();
        $user->setOTP($otp);
        $user->setOTPGenerateDate($otpgenearatedate);
        $UDS->updateLoginOtp($user);
        
        $phAnValues = array();
        $phAnValues["OTP"] = $otp;
        $phAnValues["TIME"] = $otpgenearatedate->format('Y-m-d H:i:s');
        $phAnValues["USER"] = $user->getUserName();
        $content = file_get_contents(StringConstants::$applicationPath ."/EmailTemplates/otpsend.html");
        $content = MailerUtils::replacePlaceholders($phAnValues,$content);
        $to = $user->getEmailId();
        $subject = "Login Request at EnvirotechLive.com";
        $headers = "From: noreply@envirotechlive.com";
        $from = "noreply@envirotechlive.com";
        $result = MailerUtils::sendEmailNew($to,$from,$subject,$content);
        if(!$result){
            throw new Exception("Mail Sent Failed");
        }
        return true;
    }
    
}
?>