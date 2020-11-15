<?php

class SMSApiUtils{
	private static $SMSApiUtils;
	private static $username = "learntechapi";
	private static $password = "api12345";
	private static $senderID = "ELIVE";
	private static $apiURL = "http://203.212.70.200/smpp/";
	
	public static function getInstance(){
		if (!self::$SMSApiUtils)
		{
			self::$SMSApiUtils = new SMSApiUtils();
	
			return self::$SMSApiUtils;
		}
		return self::$SMSApiUtils;
	}
	public function getCredits(){
		$ch = curl_init();
		$user="learntechapi";
		$password = "api12345";
		$senderID="ENLIVE";
		curl_setopt($ch,CURLOPT_URL,  self::$apiURL."creditstatus.jsp");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "user=".self::$username."&password=".self::$password);
		$buffer = curl_exec($ch);
		curl_close($ch);
		return $buffer;
	}
	
	public function sendSMS($receipientNo,$msg){
		$msg = urlencode($msg);
    	$ch = curl_init();

    	$receipientno=$receipientNo;
    	$senderID="ENLIVE";
    	curl_setopt($ch,CURLOPT_URL,  self::$apiURL."sendsms");
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_POST, 1);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, "username=".self::$username."&password=".self::$password."&to=$receipientno&from=".self::$senderID."&text=$msg&category=bulk");
    	$buffer = curl_exec($ch);
    	curl_close($ch);
		echo("<br>".$receipientNo . "<br> " . $msg);
    	return $buffer;
	}

	public function sendSMSByVendor($receipientNo,$msg,$vendor){
		switch ($vendor){
			case "bhoomi":
			    $msg = "SMS Alert from BHOOMI ANALYZER ".$msg;
				return $this->sendSMSBhoomi($receipientNo, $msg);				//return this->sendSMS($receipientNo, $msg);
				break;
			case "envirozone":
			    $msg = "SMS Alert from ENVIROZONE ".$msg;
 				return $this->sendSMSEnvirozone($receipientNo, $msg);
// 				break;
// 			case "elive" :
// 				$this->sendSMSElive();
// 				break;
// 			case "alamin":
// 				$this->sendSMSEnvirozone();
// 				break;
// 			case "heat":
// 				$this->sendSMSHeat();
// 				break;
			default:
			    $msg = "SMS Alert from ELIVE ".$msg;
				return $this->sendSMS($receipientNo, $msg);
		}
	}
	
	public function sendSMSBhoomi($receipientno, $msg){
		$msg = urlencode($msg);
		$ch = curl_init();
		//$senderID="BHOOMI";
		$senderID="BHUUMI";
		$apiUrl = "http://sms.bulksmsserviceproviders.com/api/send_http.php";
		$authKey = "b53b06c874fbeeea426edc4ac8950af9&mobiles=9814600356";
		curl_setopt($ch,CURLOPT_URL,  $apiUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "authkey=".$authKey."&mobiles=".$receipientno."&message=".$msg."&sender=".$senderID."&route=4");
		$buffer = curl_exec($ch);
		curl_close($ch);
		echo($buffer."<br>".$receipientno . "<br> " . $msg);
		return $buffer;
	}
	public function sendSMSEnvirozone($receipientno, $msg){
		$msg = urlencode($msg);
    	$ch = curl_init();
    	$apiUrl = "http://nimbusit.co.in/api/swsend.asp";
    	$senderID="ENVIRO";
    	$username = "t1envirozone";
    	$password="27052857";
    	curl_setopt($ch,CURLOPT_URL,  $apiUrl);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_POST, 1);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, "username=".$username."&password=".$password."&sender=".$senderID."&sendto=".$receipientno . "&message=".$msg);
    	$buffer = curl_exec($ch);
    	curl_close($ch);
		echo("<br>".$receipientNo . "<br> " . $msg);
		return $buffer;
	}
	
	public function sendSMSElive(){
			
	}
	public function sendSMSAlAmin(){
			
	}
	public function sendSMSHeat(){
			
	}
	
	
}
//http://203.212.70.200/smpp/sendsms?username=xxxx&password=xxxx&to=xxxx&from=text&text=this is test message&category=bulk
//http://203.212.70.200/smpp/sendsms?username=apitbpdemo&password=del@12345&to=9814600356,9417265865&from=E-LIVE&text=this%20is%20test%20message&category=bulk
?>
