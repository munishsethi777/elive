<?php
require_once($ConstantsArray['dbServerUrl']. "BusinessObjects/CameraPTZ.php");
require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/BeanDataStore.php");

class CameraPTZActionMgr{
	private static $cameraPTZActionMgr;
	private static $cameraPTZDataStore;
	
	public static function getInstance(){
		if (!self::$cameraPTZActionMgr){
			self::$cameraPTZActionMgr = new CameraPTZActionMgr();
			self::$cameraPTZDataStore = new BeanDataStore(CameraPTZ::$className,CameraPTZ::$tableName);
		}
		return self::$cameraPTZActionMgr;
	}
	
	
	public function saveFromRequest(){
		$folderSeq = $_GET["folderSeq"];
		$action = $_GET["action"];
		$cameraPTZ = new CameraPTZ();
		$cameraPTZ->setAction($action);
		$cameraPTZ->setFolderSeq($folderSeq);
		$cameraPTZ->setDated(new DateTime());
		$this->save($cameraPTZ);
	}
	
	public function save($cameraPTZ){
		$id = self::$cameraPTZDataStore->save($cameraPTZ);
		return $id;
	}
	
	public function findAfterSeq($seq){
		$sql = "select * from cameraptzaction where seq > $seq";
		$actions = self::$cameraPTZDataStore->executeQuery($sql);
		return $actions;
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