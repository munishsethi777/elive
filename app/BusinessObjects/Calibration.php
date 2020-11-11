<?php
class Calibration{
	public static $tableName = "calibrations";
	public static $className = "Calibration";
	private $seq,$startedon,$folderseq,$caltype,$calstartedby;
	public function setSeq($seq_){
		$this->seq = $seq_;
	}
	public function getSeq(){
		return $this->seq;
	}
	
	public function setStartedOn($startedOn_){
		$this->startedon = $startedOn_;
	}
	public function getStartedOn(){
		return $this->startedon;
	}
	
	public function setFolderSeq($folderSeq_){
		$this->folderseq = $folderSeq_;
	}
	public function getFolderSeq(){
		return $this->folderseq;
	}
	
	public function setCalType($calType_){
		$this->caltype = $calType_;
	}
	public function getCalType(){
		return $this->caltype;
	}
	
	public function setCalStartedBy($calStartedBy_){
		$this->calstartedby = $calStartedBy_;
	}
	public function getCalStartedBy(){
		return $this->calstartedby;
	}
}
