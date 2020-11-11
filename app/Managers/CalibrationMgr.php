<?php
require_once($ConstantsArray['dbServerUrl']. "BusinessObjects/Calibration.php");
require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/BeanDataStore.php");
class CalibrationMgr{
	private static $calibrationMgr;
	private static $calibrationDataStore;
	
	public static function getInstance(){
		if (!self::$calibrationMgr){
			self::$calibrationMgr = new CalibrationMgr();
			self::$calibrationDataStore = new BeanDataStore(Calibration::$className,Calibration::$tableName);
		}
		return self::$calibrationMgr;
	}
	
	public function save($calibration){
		$id = self::$calibrationDataStore->save($calibration);
		return $id;
	}
	
	 public function getCalibrationHistoryForGrid($userSeq,$folderSeq){
        $colValue["calstartedby"] = $userSeq;
        $colValue["folderseq"] = $folderSeq;
        $calibrations = self::$calibrationDataStore->findArrWithCondition($colValue,true);
        $calibrationArr = array();
        foreach ($calibrations as $calibration){
            $calType = $calibration["caltype"];
            $calTypeCaption = "Zero Calibration";
            if($calType == "purge"){
                $calTypeCaption = "Purge";
            }else if($calType == "span"){
                $calTypeCaption = "Span";
            }else if($calType == "calibration"){
                $calTypeCaption = "Calibration";
            }
            $calibration["caltype"] = $calTypeCaption;
            array_push($calibrationArr, $calibration);
        }
        $mainArr["Rows"] = $calibrationArr;
        $total = $this->getCountForGrid($userSeq,$folderSeq);
        $mainArr["TotalRows"] = $total;
        return $mainArr;
    }
	
	public function getCountForGrid($userSeq,$folderSeq){
		$colValue["calstartedby"] = $userSeq;
		$colValue["folderseq"] = $folderSeq;
		$count = self::$calibrationDataStore->executeCountQuery($colValue,true);
		return $count;
	}
	
	public function findBySeq($seq){
	    $cal = self::$calibrationDataStore->findBySeq($seq);
		return $cal;
	}
	
	public function saveCalibrationInfo($calType,$folderSeq,$userSeq,$date){
	    try{
    		$date = DateTime::createFromFormat('d-m-Y H:i:s', $date);
    		$calibration = new Calibration();
    		$calibration->setStartedOn($date);
    		$calibration->setCalType($calType);
    		$calibration->setCalStartedBy($userSeq);
    		$calibration->setFolderSeq($folderSeq);
    		$this->save($calibration);
	    }catch (Exception $e){
			$message = $e->getMessage();
		}
	}
	
	
	
}