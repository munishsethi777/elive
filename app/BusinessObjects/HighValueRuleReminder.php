<?php
  class HighValueRuleReminder{
       private $seq, $folderSeq, $highValueRuleSeq, $highValue, $highValueChannelNo, $reminderDate, $reminderMobile, $reminderEmail, $reminderIsSent;
       private $stationName,$channelname,$ishighvalue,$guid,$isrepetitive,$fromlastseq,$fromwqdseq,$towqdseq;
       
        public function setSeq($seq_){
            $this->seq = $seq_;
        }
        public function getSeq(){
            return $this->seq;
        }
        
        public function setFolderSeq($fseq_){
            $this->folderSeq = $fseq_;
        }
        public function getFolderSeq(){
            return $this->folderSeq;
        }
        
        public function setHighValueRuleSeq($hvseq_){
            $this->highValueRuleSeq = $hvseq_;
        }
        public function getHighValueRuleSeq(){
            return $this->highValueRuleSeq;
        }
        
        public function setHighValue($highValue){
            $this->highValue = $highValue;
        }
        public function getHighValue(){
            return $this->highValue;
        }
        
        public function setHighValueChannelNo($highValueChannelNo){
            $this->highValueChannelNo = $highValueChannelNo;
        }
        public function getHighValueChannelNo(){
            return $this->highValueChannelNo;
        }
        
        public function setReminderDate($reminderDate){
            $this->reminderDate = $reminderDate;
        }
        public function getReminderDate(){
            return $this->reminderDate;
        }
        
        public function setReminderMobile($reminderMob){
            $this->reminderMobile = $reminderMob;
        }
        public function getReminderMobile(){
            return $this->reminderMobile;
        }
        
        public function setReminderEmail($reminderEmail){
            $this->reminderEmail = $reminderEmail;
        }
        public function getReminderEmail(){
            return $this->reminderEmail;
        }
        
        public function setReminderIsSent($isSent){
            $this->reminderIsSent = $isSent;
        }
        public function getReminderIsSent(){
            return $this->reminderIsSent;
        }
        public function setStationName($stationName_){
            $this->stationName = $stationName_;
        }
        public function getStationName(){
            return $this->stationName;
        }
        public function setChannelName($channelName_){
            $this->channelname = $channelName_;
        }
        public function getChannelName(){
            return $this->channelname;
        }
        
        public function setIsHighValue($isHighValue_){
        	$this->ishighvalue = $isHighValue_;
        }
        public function getIsHighValue(){
        	return $this->ishighvalue;
        }
        
        public function setGUID($id_){
        	$this->guid = $id_;
        }
        public function getGUID(){
        	return $this->guid;
        }
        
        public function setIsRepetitive($isRepetitive_){
        	$this->isrepetitive = $isRepetitive_;
        }
        public function getIsReptitive(){
        	return $this->isrepetitive;
        }
        
        public function getFromWQDSeq(){
        	return $this->fromwqdseq;		
        }
        public function setFromWQDSeq($fromWqdSeq_){
        	$this->fromwqdseq = $fromWqdSeq_;
        }
        
        public function getToWQDSeq(){
        	return $this->towqdseq;
        }
        public function setToWQDSeq($toWqdSeq_){
        	$this->towqdseq = $toWqdSeq_;
        }
        
  }
?>