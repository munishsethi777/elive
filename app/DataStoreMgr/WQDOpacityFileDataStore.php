<?php
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/MainDB.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/WQDDataChannelDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "BusinessObjects/WQDOpacityFile.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/FolderDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils/FilterUtil.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/M2MSynchronizerDataStore.php");
class WQDOpacityFileDataStore{
	private static $WQDOpacityDataStore;
	private static $db;
	private static $INSERT = "insert into wqdopacityfiledata";
	public function __construct(){
		self::$db = MainDB::getInstance();
	}
	
	public static function getInstance()
	{
		if (!self::$WQDOpacityDataStore)
		{
			self::$WQDOpacityDataStore = new WQDOpacityFileDataStore();
			return self::$WQDOpacityDataStore;
		}
		return self::$WQDOpacityDataStore;
	}
	
	public function Save(WQDData $wqdOpacity){
		$fields = "wqdfolderseq,wqdfiledatadated,";
		$values = ":folderseq,:dated,";
		$conn = self::$db->getConnection();
		$WCD = WQDDataChannelDataStore::getInstance();
		$wqdChannlsArr = array();
		try{
			$wqdChannlsArr = $wqdOpacity->getChannels();
			$SQL = self::GenerateSqlToSave($wqdChannlsArr,$fields,$values);
			$stmt = $conn->prepare($SQL);
			$stmt->bindValue(':folderseq', $wqdOpacity->getFolderSeq());
			$stmt->bindValue(':dated',$wqdOpacity->getDatadate());			
			$stmt->execute();
			$error = $stmt->errorInfo();
			if($error[2] <> ""){
				throw new Exception($error[2]);
			}
		}catch(Exception $e){
			$message = $e->getMessage();
			if(strpos($message, "Duplicate entry") === 0){
			}else{
				throw $e;
			}
		}
	}
	public function getWQDOpacityDataByLocationSeqsAndLastSeqs($locationSeqs, $lastSeqs, $limit){
		try{
			$conn = self::$db->getConnection();
			$locSeqsArr = explode(",", $locationSeqs);
			$lastSeqsArr = explode(",", $lastSeqs);
			$sql = "SELECT * FROM wqdopacityfiledata INNER JOIN folder ON folder.seq = wqdopacityfiledata.wqdfolderseq AND (";
			for($i=0;$i<count($locSeqsArr );$i++){
				if($i>0){
					$sql .= " or";
				}
				$sql .= " (folder.locationseq =$locSeqsArr[$i] AND wqdopacityfiledata.wqdfiledataseq >$lastSeqsArr[$i] )";
			}
	
			$sql .= ") Limit $limit";
			$stmt = $conn->prepare($sql );
			$stmt->execute();
			$rows = $stmt->fetchAll();
			return $rows;
		}catch(Exception $e){
			$ee = $e->getMessage();
		}
	}
	public function saveSyncedData($wqdDataList,$isM2MSite = false){
		$syncDate = "";
		$folderSeq = "";
		foreach($wqdDataList as $wqdData){
			try{
				$this->Save($wqdData);
				$syncDate = $wqdData->getDatadate();
				$folderSeq = $wqdData->getFolderSeq();
			}catch(Exception $e){
				$message = $e->getMessage();
				if(strpos($message, "Duplicate entry") === 0){
				}else{
					throw $e;
				}
			}
		}
		if(!empty($syncDate)){
			if($isM2MSite){
				$M2MDs = M2MSynchronizerDataStore::getInstance();
				$M2MDs->updateLastSyncedOn($syncDate,$folderSeq);
			}else{
				$FDS = FolderDataStore::getInstance();
				$FDS->updateLastSyncedOn($syncDate,$folderSeq);
			}
	
		}
	}
	
	 public function getChannels($fromDate, $toDate, $folderSeq, $channelNos, $interval,$isExport = false){
		$sql = "Select wqdfiledatadated,";
		for ($i = 0, $l = count($channelNos); $i < $l; ++$i) {
			$sql .= "ch". $channelNos[$i] ."value";
			$sql .= ",ch". $channelNos[$i] ."status";
			if($i < $l-1){
				$sql .= ",";
			}
		}
		$sql .= " from wqdopacityfiledata where wqdfolderseq = :folderseq";
		$sql .= " and wqdfiledatadated >= '" . $fromDate . "'";
		$sql .=" and wqdfiledatadated <= '" . $toDate . "' ";
		$sql .=" order by wqdfiledatadated asc";
		if(!$isExport){
			$sql = FilterUtil::appendPagger($sql);
		}
		
		try{
			$conn = self::$db->getConnection();
			$stmt = $conn->prepare($sql);
			$stmt->bindValue(':folderseq', $folderSeq);
			$stmt->execute();
			$error = $stmt->errorInfo();
			$rows = $stmt->fetchAll();
			return $rows;
		}catch(Exception $e){
			$error = $e;
		}
	 }
	 public function getTotalCount($fromDate, $toDate, $folderSeq, $channelNos){
	 	$sql = "Select count(*) from wqdopacityfiledata where wqdfolderseq = :folderseq";
	 	$sql .= " and wqdfiledatadated >= '" . $fromDate . "'";
	 	$sql .=" and wqdfiledatadated <= '" . $toDate . "' ";
	 	$sql .=" order by wqdfiledatadated asc";
	 	try{
	 		$conn = self::$db->getConnection();
	 		$stmt = $conn->prepare($sql);
	 		$stmt->bindValue(':folderseq', $folderSeq);
	 		$stmt->execute();
	 		$result = $stmt->fetch(PDO::FETCH_NUM);
	 		$count = intval($result[0]);
	 		return $count;
	 	}catch(Exception $e){
	 		$error = $e;
	 	}
	 }
	public function GenerateSqlToSave($wqdChannlsArr,$fields,$values){
		$fieldsStr = "";
		$valueStr = "";
		$arrObj = new ArrayObject($wqdChannlsArr);
		$it = $arrObj->getIterator();
		while( $it->valid()){
			$col = "ch";
			$key = $it->key();
			if(strstr($key, 'N')){
				$key =  substr($key, 1);
			}
			$current = $it->current();
			$fields = $fields . $col .$key. "value, " . $col  . $key . "status,";
	
			//NULL CHECKS IF CHANNELS or STATUS HAVE NO VALUES(empty strings)
			$val = $current[0];
			$sta = $current[1];
            if(array_key_exists("value",$current)){
                $val = $current["value"];
            }
            if(array_key_exists("status",$current)){
                $sta = $current["status"];
            }
			//$val = $current["value"] == null ? $current[0] : $current["value"];
			//$sta = $current["status"] == null ? $current[1] : $current["status"];
            $val = str_replace(",","",$val);
			if($val == ""){
				$val = "NULL";
			}
			if($sta == ""){
				$sta = "NULL";
			}
	
	
			$values =  $values . $val . "," . $sta . ",";
			$it->next();
		}
		$fields =  substr($fields, 0, strlen($fields)-1);
		$values =  substr($values, 0, strlen($values)-1);
		$sql = self::$INSERT . " (" . $fields . ") values (". $values . ")" ;
		return $sql;
	
	}
}
