<?php
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/MainDB.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/WQDDataChannelDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/FolderDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils/FilterUtil.php");
class WQDAQMSPFileDataStore{
	private static $WQDAQMSPDataStore;
	private static $db;
	private static $INSERT = "insert into wqdaqmspfiledata";
	public function __construct(){
		self::$db = MainDB::getInstance();
	}

	public static function getInstance()
	{
		if (!self::$WQDAQMSPDataStore)
		{
			self::$WQDAQMSPDataStore = new WQDAQMSPFileDataStore();
			return self::$WQDAQMSPDataStore;
		}
		return self::$WQDAQMSPDataStore;
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
    public function SaveAPI(WQDData $wqdOpacity){
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
            throw $e;
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
			$FDS = FolderDataStore::getInstance();
			$FDS->updateLastSyncedOn($syncDate,$folderSeq);
		}
	}
    public function saveSyncedDataViaAPI($wqdDataList,$isM2MSite = false){
        
        $success = 1;
        $message = "";
        $syncDate = "";
        $folderSeq = "";
        foreach($wqdDataList as $wqdData){
            try{
                
                $this->SaveAPI($wqdData);
                $syncDate = $wqdData->getDatadate();
                $folderSeq = $wqdData->getFolderSeq();
                if(!empty($syncDate)){
                    $FDS = FolderDataStore::getInstance();
                    $FDS->updateLastSyncedOn($syncDate,$folderSeq);
                }
                 
            }catch(Exception $e){
                $message = $e->getMessage();
                $success = 0;
            }
        }
        
        
        return array("success"=> $success, "message"=> $message);
        
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
			$fields = $fields . $col .$key. "value,";
	
			//NULL CHECKS IF CHANNELS or STATUS HAVE NO VALUES(empty strings)
			$val = $current[0];
			if(array_key_exists("value",$current)){
				$val = $current["value"];
			}
			$val = str_replace(",","",$val);
			if($val == ""){
				$val = "NULL";
			}
			$values =  $values . $val . ",";
			$it->next();
		}
		$fields =  substr($fields, 0, strlen($fields)-1);
		$values =  substr($values, 0, strlen($values)-1);
		$sql = self::$INSERT . " (" . $fields . ") values (". $values . ")" ;
		return $sql;
	}
	
	public function getChannels($fromDate, $toDate, $folderSeq, $channelNos, $interval,$isExport=false){
		$sql = "Select wqdfiledatadated,";
		for ($i = 0, $l = count($channelNos); $i < $l; ++$i) {
			$sql .= "ch". $channelNos[$i] ."value";
			if($i < $l-1){
				$sql .= ",";
			}
		}
		$sql .= " from wqdaqmspfiledata where wqdfolderseq = :folderseq";
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
		$sql .= "Select count(*) from wqdaqmspfiledata where wqdfolderseq = :folderseq";
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
	private static $FIND_FOLDER = "SELECT * FROM wqdaqmspfiledata inner join folder on wqdaqmspfiledata.wqdfolderseq =  folder.seq where wqdfolderseq = :folderseq and wqdaqmspfiledata.wqdfiledataseq > :lastsyncedseq order by wqdfiledataSeq LIMIT ";
	
	public function getChannelsInfo($folderSeq,$lastSeq, $limit=40){
	    $conn = self::$db->getConnection();
	    $sql = self::$FIND_FOLDER.$limit;
	    $stmt = $conn->prepare($sql);
	    $stmt->bindValue(':folderseq', $folderSeq);
	    $stmt->bindValue(':lastsyncedseq', $lastSeq);
	    $stmt->execute();
	    $dataArr = array();
	    while($rows = $stmt->fetch(PDO::FETCH_ASSOC)){
	        $arrObj = new ArrayObject($rows);
	        $it = $arrObj->getIterator();
	        $WQDCurrentInfo = array();
	        $dated = date("Y/m/d H:i:s",strtotime($rows['wqdfiledatadated']));
	        $WQDCurrentInfo['seq'] = $rows['wqdfiledataseq'];
	        $WQDCurrentInfo['formatedDated'] = date("d/Y/m H:i:s",strtotime($rows['wqdfiledatadated']));
	        $WQDCurrentInfo['dated'] = $dated;
	        $WQDChannelsInfo = array();
	        while( $it->valid()){
	            $key = $it->key();
	            if(substr($key,0,2) == "ch"){
	                $value = $it->current();
	                $WQDChannelsInfo[$key] = $value;
	            }
	            $it->next();
	        }
	        $WQDCurrentInfo['channelsInfo'] = $WQDChannelsInfo;
	        array_push($dataArr,$WQDCurrentInfo);
	    }
	    return $dataArr;
	}
	public function getAQMSPData($fromDate, $folderSeq, $channelNos,$rows){
		$sql = "Select wqdfiledataseq,wqdfiledatadated,";
		for ($i = 0, $l = count($channelNos); $i < $l; ++$i) {
			$sql .= "ch". $channelNos[$i] ."value";
			if($i < $l-1){
				$sql .= ",";
			}
		}
		$sql .= " from wqdaqmspfiledata where wqdfolderseq = :folderseq";
		$sql .= " and wqdfiledatadated >= '" . $fromDate . "'";
		$sql .=" order by wqdfiledatadated asc limit $rows";
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
}
