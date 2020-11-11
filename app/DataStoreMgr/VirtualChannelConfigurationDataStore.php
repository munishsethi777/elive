<?php
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/MainDB.php");
require_once($ConstantsArray['dbServerUrl'] . "/BusinessObjects/VirtualChannelConfiguration.php");




class VirtualChannelConfigurationDataStore {
	private static $VCCDataStore;
	private static $db;
	private static $FINDBYFOLDERSEQ = "select * from virtualchannelconfiguration where folderseq = :folderseq";
	private static $FIND_BY_SEQ = "select * from virtualchannelconfiguration where configseq = :configseq";
	
	public function __construct(){
		self::$db = MainDB::getInstance();
	}
	
	public static function getInstance()
	{
		if (!self::$VCCDataStore)
		{
			self::$VCCDataStore = new VirtualChannelConfigurationDataStore();
			return self::$VCCDataStore;
		}
		return self::$VCCDataStore;
	}
	
	public function FindByFolder($folderSeq){
		$conn = self::$db->getConnection();
		$stmt = $conn->prepare(self::$FINDBYFOLDERSEQ);
		$stmt->bindValue(':folderseq', $folderSeq);
		$stmt->execute();
		$VCCArray = Array();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {		
			$vcc =  self::populateObject($row);
			$VCCArray["0_".$vcc->getSeq()] = $vcc;
		}
		return $VCCArray;
	}
	
	public function FindBySeq($seq){
		$conn = self::$db->getConnection();
		$stmt = $conn->prepare(self::$FIND_BY_SEQ);
		$stmt->bindValue(':configseq', $seq);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$cc =  self::populateObject($row);
		$error = $stmt->errorInfo();
		return $cc;
	}
	
	public function FindArrBySeq($seq){
		$conn = self::$db->getConnection();
		$stmt = $conn->prepare(self::$FIND_BY_SEQ);
		$stmt->bindValue(':configseq', $seq);
		$stmt->execute();
		$rows = $stmt->fetchAll();
		return $rows;
	}
	
	public function populateObject($rsItem){
	
		$seq_ = $rsItem["configseq"] ;
		$folderSeq_ = $rsItem["folderseq"] ;		
		$channelName_ = $rsItem["channelname"];		
		$channelUnit_ = $rsItem["channelunit"] ;
		$channelStation_ = $rsItem["channelstation"] ;
		$prescribedLimit_ = $rsItem["prescribedlimit"] ;
		$isVisible_ = $rsItem["isvisible"] ;
		$deviceID = $rsItem["deviceid"];
		$channelRealName = $rsItem["channelrealname"];
		$prescribedLowLimit_ = $rsItem["prescribedlowlimit"];
	
		$cc = new VirtualChannelConfiguration();
		$cc->setSeq($seq_);
		$cc->setFolderSeq($folderSeq_);
		$cc->setChannelName($channelName_);
		$cc->setChannelUnit($channelUnit_);
		$cc->setChannelStation($channelStation_);
		$cc->setPrescribedLimit($prescribedLimit_);
		$cc->setPrescribedLowLimit($prescribedLowLimit_);
		$cc->setIsVisible($isVisible_);
		$cc->setDeviceId($deviceID);
		$cc->setChannelRealName($channelRealName);
		return $cc;
	}
	
	
}