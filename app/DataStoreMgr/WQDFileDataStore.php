<?php
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/MainDB.php");
  require_once($ConstantsArray['dbServerUrl'] . "BusinessObjects/WQDFile.php");
  require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/WQDDataDataStore.php");
  class WQDFileDataStore  {
  
    private static $WQDFileDataStore;
    private static $db;  
    private static $INSERT = "insert into wqdfiles (wqdfiledate,wqdfilename,wqdfolderseq,wqdlocationseq) values(:date,:name,:folderseq,:locationseq)";
    private static $FIND_CURRENT_BY_FOLDER = "select * from wqdfiledata where wqdfolderseq = :folderseq LIMIT 1";
    
    
    public function __construct(){
        self::$db = MainDB::getInstance();
    }

    public static function getInstance()
    {
        if (!self::$WQDFileDataStore)
        {
            self::$WQDFileDataStore = new WQDFileDataStore();           
            return self::$WQDFileDataStore;
        }
        return self::$WQDFileDataStore;        
    }
    public function findByLocationSeqLastSeq($locSeqs, $lastSeq, $limit){
          try{
            $conn = self::$db->getConnection();
            $sql = "select * from wqdfiles where wqdlocationseq in ($locSeqs) and wqdfileseq > $lastSeq Limit $limit";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return $rows;
          }catch(Exception $e){
            $ee = $e->getMessage();
          }
    }
    public function isFileDataExist($folderSeq){
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare(self::$FIND_CURRENT_BY_FOLDER);
        $stmt->bindValue(':folderseq', $folderSeq);
        $stmt->execute();
        $row_count = $stmt->rowCount();        
        return $row_count > 0;
   }
    public function findByLocationSeqLastSeqs($locSeqs, $lastSeqs, $limit){
	try{
    		$locSeqsArr = explode(",", $locSeqs);
    		$lastSeqsArr = explode(",", $lastSeqs);
    		$sql = "select * from wqdfiles where";// (wqdlocationseq =3 and wqdfileseq >0) or (wqdlocationseq =4 and wqdfileseq >0)";
	    	for($i=0;$i<count($locSeqsArr );$i++){
	    		if($i>0){
    				$sql .= " or";
    			}
    			$sql .= " (wqdlocationseq =$locSeqsArr[$i] and wqdfileseq >$lastSeqsArr[$i])";
    		}
		$sql .= " Limit $limit";
		$conn = self::$db->getConnection();
		$stmt = $conn->prepare($sql);
		$stmt->execute();
		$rows = $stmt->fetchAll();
		return $rows;
	}catch(Exception $e){
		$ee = $e->getMessage();
         }
    }
    public function Save(WQDFile $wqdFile,$filePath){
      $SQL = self::$INSERT;
      $WDD = WQDDataDataStore::getInstance();
      $wqdataArr = array(); 
      $conn = self::$db->getConnection();
      $stmt = $conn->prepare($SQL);
      $stmt->bindValue(':date', $wqdFile->getFiledate()); 
      $stmt->bindValue(':name', $wqdFile->getName());
      $stmt->bindValue(':folderseq',$wqdFile->getFolderSeq());
      $stmt->bindValue(':locationseq',$wqdFile->getLocationSeq());
      try{
        $stmt->execute();
        $err = $stmt->errorInfo();
        $wqdataArr = $wqdFile->getData();
        $seq =  self::$db->getLastInsertedId();
        $WDD->SaveFileData($wqdataArr,$seq);
      }catch(Exception $e){
          return $e->getMessage();
      }
      $error = $stmt->errorInfo();
    }
  }
?>