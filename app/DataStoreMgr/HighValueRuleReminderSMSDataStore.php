<?php
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/MainDB.php");
require_once($ConstantsArray['dbServerUrl'] . "/BusinessObjects/HighValueRuleReminderSMS.php");
class HighValueRuleReminderSMSDataStore{
	private static $smsDataStore;
	private static $db;
	private static $INSERT = "insert into highvalueruleremindersms (highvaluerulereminderseq,mobile,errorcode,issent,dated) values(:highvaluerulereminderseq,:mobile,:errorcode,:issent,:dated)";
	private static $FIND_BY_SEQ = "select * from highvalueruleremindersms where highvaluerulereminderseq = :reminderseq ";
	private static $FIND_UNSENT_BY_REMINDERSEQ = "select * from highvalueruleremindersms where highvaluerulereminderseq = :reminderseq and issent = 0 ";
	
	private static $SENT_STATUS_CHANGE = "update highvalueruleremindersms set errorcode = :errorcode , issent=:issent,dated = :dated where mobile = :mobile and highvaluerulereminderseq = :reminderSeq";
	
	private static $GET_DISTINCT_COUNT_SENT_SMS_BY_CHANNEL = "select count(distinct sms.highvaluerulereminderseq) as totalsms from highvalueruleremindersms sms inner join highvaluerulereminder hr on sms.highvaluerulereminderseq = hr.seq
where hr.highvaluechannelno = :channelno and hr.reminderdate >= :fromDate and hr.reminderdate <= :toDate and hr.isrepetitive = 0 and sms.issent = 1 and hr.folderseq in ";
	
	private static $GET_DISTINCT_COUNT_SENT_SMS_BY_VIRTUAL_CHANNEL = "select count(distinct sms.highvaluerulereminderseq) from highvalueruleremindersms sms
inner join highvaluerulereminder hr on sms.highvaluerulereminderseq = hr.seq inner join highvaluerule hv on hr.highvalueruleseq = hv.seq
inner join virtualchannelconfiguration vcc on hv.virtualchannelseq = vcc.configseq where hr.isrepetitive = 0 and vcc.configseq = :channelseq and hr.reminderdate >= :fromDate and hr.reminderdate <= :toDate 
and sms.issent = 1 and hr.folderseq in ";
	
	public function __construct(){
		self::$db = MainDB::getInstance();
	}
	
	public static function getInstance()
	{
		if (!self::$smsDataStore)
		{
			self::$smsDataStore = new HighValueRuleReminderSMSDataStore();
			return self::$smsDataStore;
		}
		return self::$smsDataStore;
	}
	
	public function Save($hvrSMS){
		$isUpdate = false;
		$isSent = $hvrSMS->getIsSent();
		if(!empty($isSent)){
			$isSent = true;
		}
		$seq = $hvrSMS->getSeq();		
		$SQL = self::$INSERT;
		$conn = self::$db->getConnection();
		$stmt = $conn->prepare($SQL);
		$stmt->bindValue(':highvaluerulereminderseq', $hvrSMS->getHighValueRuleReminderSeq());
		$stmt->bindValue(':mobile', $hvrSMS->getMobile());
		$stmt->bindValue(':errorcode', $hvrSMS->getErrorCode());		
		$stmt->bindValue(':issent', $hvrSMS->getIsSent());
		$dated = $hvrSMS->getDated();
		$value = null;
		if(!empty($dated)){
			$value = $dated->format('Y-m-d H:i:s');
		}
		$stmt->bindValue(':dated', $value);
		try{
			$stmt->execute();
			$err = $stmt->errorInfo();
			if($err[2] <> ""){
				throw new RuntimeException($err[2]);
			}
		}catch(Exception $e){
			$logger = Logger::getLogger($ConstantsArray["logger"]);
			$logger->error("Error during Save HighValueRuleReminderSMS : - " . $e->getMessage());
			return $e->getMessage();
		}
	}
	
	
	
	public function FindByReminderSeq($reminderSeq){
		$conn = self::$db->getConnection();
		$stmt = $conn->prepare(self::$FIND_BY_SEQ);
		$stmt->bindValue(':reminderseq', $reminderSeq);		
		$stmt->execute();
		$smsArr = array();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {			
			$obj =  self::populateObject($row);			
			array_push($smsArr, $obj);
		}
		return $smsArr;
	}
	
	public function getTotalByFoldersAndChannel($chNumber,$folderseqs,$fromDate, $toDate){
		$conn = self::$db->getConnection();
		$sql = self::$GET_DISTINCT_COUNT_SENT_SMS_BY_CHANNEL . "($folderseqs)";		
		$stmt = $conn->prepare($sql);
		$stmt->bindValue(':channelno',$chNumber);
		$stmt->bindValue(':fromDate',$fromDate);
		$stmt->bindValue(':toDate',$toDate);
		$stmt->execute();
		$smsArr = array();
	    $result = $stmt->fetch(PDO::FETCH_NUM);
	    $error = $stmt->errorInfo();
        $count = intval($result[0]);
		return $count;
	}
	public function getTotalByFoldersAndVirtualChannel($virtualChannelSeq ,$folderseqs,$fromDate, $toDate){
		$conn = self::$db->getConnection();
		$sql = self::$GET_DISTINCT_COUNT_SENT_SMS_BY_VIRTUAL_CHANNEL . "($folderseqs)";
		$stmt = $conn->prepare($sql);
		$stmt->bindValue(':channelseq',$virtualChannelSeq);
		$stmt->bindValue(':fromDate',$fromDate);
		$stmt->bindValue(':toDate',$toDate);
		$stmt->execute();
		$smsArr = array();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		$error = $stmt->errorInfo();
		$count = intval($result[0]);
		return $count;
	}
	public function updateStatusForSendSMS($vendor,$response, $reminderSeq,$mobiles){
		$guid = null;
		if($vendor == "bhoomi"){
			$guid = $this->sentBhoomiSMSStatusChange($reminderSeq, $response, $mobiles);	
		}else if($vendor == "envirozone"){
			$guid = $this->sentEnviroSMSStatusChange($reminderSeq, $response, $mobiles);
		}else{
			$guid = $this->SentSMSStatusChange($response, $reminderSeq);
		}
		return $guid;
	}
	public function SentSMSStatusChange($response, $reminderSeq){
		$mainArr = $this->parseSMSResponse($response);
		$errorCodes = $mainArr[0];
		$mobiles = $mainArr[1];
		$guid = $mainArr[2];
		$i = 0;
		foreach ($errorCodes as $key=>$code){			
			$isSent = 0;
			if($code == 0){
				$isSent = 1;
			}
			$mobile = $mobiles[$i];
			$this->updateStatus($isSent,$code,$mobile,$reminderSeq);
			$i++;	
		}
		return $guid;
	}
	
	function startsWith($haystack, $needle)
	{
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}
	public function updateStatus($isSent,$errorCode,$mobile,$reminderSeq){
		$SQL = self::$SENT_STATUS_CHANGE;
		$conn = self::$db->getConnection();
		$stmt = $conn->prepare($SQL);
		$dated = new DateTime();
		$value = $dated->format('Y-m-d H:i:s');
		$stmt->bindValue(':dated', $value);
		$stmt->bindValue(':errorcode',$errorCode);		
		$stmt->bindValue(':issent',$isSent);
		if($this->startsWith($mobile, "91")){
			$mobile = substr($mobile, 2);
		}
		$stmt->bindValue(':mobile',$mobile);
		$stmt->bindValue(':reminderSeq',$reminderSeq);
		$stmt->execute();
		$error = $stmt->errorInfo();
	}
	private function sentBhoomiSMSStatusChange($reminderSeq,$response,$mobiles){
		$isSent = 1;
		$code = 0;
		$guid = $response;
		$guid = str_replace('"',"", $guid);
		if (strpos($response, 'message') !== false) {
			$response = json_decode($response);
			$code = $response->code;
			$isSent = 0;
			$guid = null;
		}
		if(!empty($mobiles)){
			$mobiles = explode(",", $mobiles);
		}else{
			$mobiles = array();
		}
		foreach ($mobiles as $mobile){
			$this->updateStatus($isSent,$code,$mobile,$reminderSeq);
		}
		return $guid;
	}
   private function sentEnviroSMSStatusChange($reminderSeq,$response,$mobiles){
		$isSent = 1;
		$code = 0;
		$guid = $response;
		if (strpos($response, 'LogID') !== false) {
			$logId  = explode("=", $response);
			$guid = $logId[1];
		}else{
			$isSent = 0;	
		}
		if(!empty($mobiles)){
			$mobiles = explode(",", $mobiles);
		}else{
			$mobiles = array();
		}
		foreach ($mobiles as $mobile){
			$this->updateStatus($isSent,$code,$mobile,$reminderSeq);
		}
		return $guid;
	}
	
	private function parseSMSResponse($response){
		$str = $response;
		$strpos = strpos($str, '&errorcode=');
		$guid = substr($str,5,$strpos-5);
		$username = substr($str,strpos($str, 'errorcode=') + 10);
		$pos1 = strpos($str, '&seqno');
		if($pos1 !== false){
			$pos = strpos($username, '&seqno');
			$errorCodes = substr($username,0,$pos);
			$errorCodes = explode(",", $errorCodes);
			$mobileNos = substr($str,$pos1 + 7);
			$mobileNos = explode(",", $mobileNos);
			$mainArr = array();
			array_push($mainArr, $errorCodes);
			array_push($mainArr, $mobileNos);
			array_push($mainArr, $guid);
			return $mainArr;
		}
		return null;
	}
	
	public function getMobilesByReminder($reminderSeq){
		$conn = self::$db->getConnection();
		$stmt = $conn->prepare(self::$FIND_UNSENT_BY_REMINDERSEQ);
		$stmt->bindValue(':reminderseq', $reminderSeq);
		$stmt->execute();
		$mobiles = array();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {			
			$mobile =  $row["mobile"];			
			array_push($mobiles, $mobile);
		}
		return $mobiles;
	}
	
	public function populateObject($rsItem){	
		$seq_ = $rsItem["seq"];
		$highValueRuleReminderSeq = $rsItem["highvaluerulereminderseq"];
		$mobile = $rsItem["mobile"];
		$errorCode = $rsItem["errorcode"];
		$isSent = $rsItem["issent"];
		$dated = $rsItem["dated"];
		
		$highValueRuleSMS = new HighValueRuleReminderSMS();
		$highValueRuleSMS->setErrorCode($errorCode);
		$highValueRuleSMS->setMobile($mobile);
		$highValueRuleSMS->setErrorCode($errorCode);
		$highValueRuleSMS->setIsSent($isSent);
		$highValueRuleSMS->setDated($dated);
		return $highValueRuleSMS;
	}
	
}