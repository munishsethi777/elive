<?php
require_once($ConstantsArray['dbServerUrl'] ."/Utils/MailerUtils.php");
require_once($ConstantsArray['dbServerUrl'] ."/Utils/SMSApiUtils.php");
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ConfigurationDataStore.php");

Class ReminderMgr{

    private static $reminderMgr;
    public static function getInstance(){
        if (!self::$reminderMgr)
        {
            self::$reminderMgr = new ReminderMgr();

            return self::$reminderMgr;
        }
        return self::$reminderMgr;
    }

    Public static function reminderInvoker(Folder $folder){
        $configuration = new Configuration();
        //parameter to know the max gap between last parsing done on the folder.
        $invokeMinutes = $configuration->getConfiguration(ConfigurationKeys::$reminderInvokeMinutes);
        //parameter to know the max gap between notifications
        $intervalMinutes = $configuration->getConfiguration(ConfigurationKeys::$reminderIntervalMinutes);
        $invokeMinutes = intval($invokeMinutes);
        $intervalMinutes = intval($intervalMinutes);

        $parsedSinceMinutes = self::getDatesDifferenceInMinutes($folder->getLastParsedOn(),date("Y-m-d H:i:s"));
        if($parsedSinceMinutes > 0){
            //OK we have some minutes passed with no parsing undertook
            if($parsedSinceMinutes > $invokeMinutes){
                //if reminder already sent, check interval and send again
                if($folder->getLastRemindedOn() != null){
                    $lastRemindedSince = self::getDatesDifferenceInMinutes($folder->getLastRemindedOn(),date("Y-m-d H:i:s"));
                    if($lastRemindedSince > $intervalMinutes){
                        self::sendNotification($folder);
                        FolderDataStore::getInstance()->updateLastReminderDate($folder->getSeq());
                    }

                }else{
                      self::sendNotification($folder);
                      FolderDataStore::getInstance()->updateLastReminderDate($folder->getSeq());
                }

            }else{
                //time still there to remind
            }
        }
    }

    Public static function sendSMSCreditsNotification(){
    	$smsApiUtils = SMSApiUtils::getInstance();
    	$creditsStr = $smsApiUtils->getCredits();
    	$creditsArr= explode(",", $creditsStr);
    	
    	$creditLimit = explode("=", $creditsArr[0])[1];
    	$creditUsed = explode("=", $creditsArr[1])[1];
    	$userStatus = explode("=", $creditsArr[2])[1];
    	
    	$configDS = ConfigurationDataStore::getInstance();
    	$configSMSUsed = $configDS->getConfiguration(ConfigurationDataStore::$smsUsed);
    	$configDS->saveConfig(ConfigurationDataStore::$smsUsed, $creditUsed);
    	
    	$creditUsedToday = $configSMSUsed - $creditUsed;
    	$creditLeft = $creditLimit - $creditUsed;
    	
    	$str = "Credits Used Today : ". $creditUsedToday ."\r\n";
    	$str .= "Credits Left : ". $creditLeft ."\r\n";
    	$str .= "User Status : ". $userStatus ."\r\n";
    	self::sendEmail("munishsethi777@gmail.com", "SMS credits usage report", $str);
    }
    
    public static function sendEmail($email, $subject, $message){
    	$from = "noreply@envirotechlive.com";
    	$cc = null;//"munishsethi777@gmail.com";    	
    	return MailerUtils::sendMandrillEmailNotification($message,$subject,$from,$email,$cc);
    	//return MailerUtils::sendEmail($email, $from, $subject, $message);
    }
    public static function sendHighValueNotification($email, $subject, $message){
    	$from = "noreply@envirotechlive.com";    	
    	return MailerUtils::sendEmailNew($email,$from,$subject,$message);    	
    }
    public static function sendSMSNew($receipientNo,$msg){
    	$smsApiUtils = SMSApiUtils::getInstance();
    	return $smsApiUtils->sendSMS($receipientNo, $msg);
    	echo("<br>".$receipientNo . "<br> " . $msg);
    	return $buffer;
    }
    
    public static function sendSMSByVendor($receipientNo,$msg,$vendor){
    	$smsApiUtils = SMSApiUtils::getInstance();    	
    	return $smsApiUtils->sendSMSByVendor($receipientNo, $msg,$vendor);    	
    }
    
    private static function sendNotification(Folder $folder){
        $managers = UserDataStore::getInstance()->FindAllManagersByLocation($folder->getLocationSeq());
        foreach($managers as $manager){
            $mailMessage = "\r\n<br>Alert: File Upload Failure";
            $mailMessage .= "\r\n<br>Industry Name:". $folder->getDetails();
            $mailMessage .= "\r\n<br>Stn: ". $folder->getFolderName();
            $mailMessage .= "\r\n<br>". date("D, d-M-Y H:ia");
            $subject = "EnvirotechLive Station File upload Failure Notification";
            if($manager->getEmailId()){
                //self::sendEmail($manager->getEmailId(), $subject, $mailMessage);
            }
            if($manager->getMobile()){
                $mailMessage = "SMS Alert from CPCB" .$mailMessage ;
                $smsMessage = str_replace("<br>","",$mailMessage);
                self::sendSMS($manager->getMobile(),$smsMessage);
            }
        }
    }
    private static function getTimeStampFromStr($dateStr){
        $dated = date("m/d/Y H:i",strtotime($dateStr));
        $datedStamp = strtotime($dated);
        return $datedStamp;
    }
    private static function getTSDifferenceInMinutes($fromTime,$toTime){
        $diffTS = $toTime - $fromTime;
        return (int)$diffTS/60;
    }
    private static function getDatesDifferenceInMinutes($fromDate,$toDate){
        $fromTime = self::getTimeStampFromStr($fromDate);
        $toTime = self::getTimeStampFromStr($toDate);
        return self::getTSDifferenceInMinutes($fromTime,$toTime);
    }

    
}
?>