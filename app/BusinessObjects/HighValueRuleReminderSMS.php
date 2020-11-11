<?php
class HighValueRuleReminderSMS{
	private $seq,$highvaluerulereminderseq,$mobile,$errorcode,$issent,$dated;
	
	
	public function setSeq($seq_){
		$this->seq = $seq_;
	}
	public function getSeq(){
		return $this->seq;
	}
	
	public function setHighValueRuleReminderSeq($ruleReminderSeq){
		$this->highvaluerulereminderseq = $ruleReminderSeq;
	}
	public function getHighValueRuleReminderSeq(){
		return $this->highvaluerulereminderseq;
	}
	
	public function setMobile($mobile_){
		$this->mobile = $mobile_;
	}
	public function getMobile(){
		return $this->mobile;
	}
	
	public function setErrorCode($errorCode_){
		$this->errorcode = $errorCode_;
	}
	public function getErrorCode(){
		return $this->errorcode;
	}
	
	public function setIsSent($isSent_){
		$this->issent = $isSent_;
	}
	public function getIsSent(){
		return $this->issent;
	}
	
	
	
	public function setDated($dated_){
		$this->dated = $dated_;
	}
	public function getDated(){
		return $this->dated;
	}
	
	
	
}