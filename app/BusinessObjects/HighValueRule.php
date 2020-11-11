<?php
  class HighValueRule{
      private $seq, $folderSeq, $email, $mobile, $parameter, $highValue, $frequency, $isActive, $folderName,$industryName, $ruleHits,$lastRuleHitWQDFileDataSeq,$ruleStartWQDFileDataSeq,$channelName,$channelStation,$stationType,$stationName,$isnotifycpcb ;
      private $ruleType,$virtualchannelseq,$lastsentsmson;

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

        public function setEmail($email){
            $this->email = $email;
        }
        public function getEmail(){
            return $this->email;
        }

        public function setMobile($mobile){
            $this->mobile = $mobile;
        }
        public function getMobile(){
            return $this->mobile;
        }

        public function setParameter($parameter){
            $this->parameter = $parameter;
        }
        public function getParameter(){
            return $this->parameter;
        }

        public function setHighValue($hval){
            $this->highValue = $hval;
        }
        public function getHighValue(){
            return $this->highValue;
        }

        public function setFrequency($fre){
            $this->frequency = $fre;
        }
        public function getFrequency(){
            return $this->frequency;
        }

        public function setIsActive($bool){
            $this->isActive = $bool;
        }
        public function getIsActive(){
            return $this->isActive;
        }
        public function setFolderName($folName){
            $this->folderName = $folName;
        }
        public function getFolderName(){
            return $this->folderName;
        }
        public function setIndustryName($industryName_){
            $this->industryName = $industryName_;
        }
        public function getIndustryName(){
            return $this->industryName;
        }
        public function setRuleHits($ruleHits){
            $this->ruleHits = $ruleHits;
        }
        public function getRuleHits(){
            return $this->ruleHits;
        }

        public function setLastRuleHitFileDataSeq($seq){
            $this->lastRuleHitWQDFileDataSeq = $seq;
        }
        public function getLastRuleHitFileDataSeq(){
            return $this->lastRuleHitWQDFileDataSeq;
        }

        public function setRuleStartFileDataSeq($seq){
            $this->ruleStartWQDFileDataSeq = $seq;
        }
        public function getRuleStartFileDataSeq(){
            return $this->ruleStartWQDFileDataSeq;
        }

        public function setChannelName($name){
            $this->channelName = $name;
        }
        public function getChannelName(){
            return $this->channelName;
        }
         public function setStationName($stationName_){
            $this->stationName = $stationName_;
        }
        public function getStationName(){
            return $this->stationName;
        }
        public function setChannelStation($station){
            $this->channelStation = $station;
        }
        public function getChannelStation(){
            return $this->channelStation;
        }
        
        public function setStationType($stationType_){
            $this->stationType = $stationType_;
        }
        public function getStationType(){
            return $this->stationType;
        }
        
        public function setIsNotifyCpcb($isNotify){
            $this->isnotifycpcb = $isNotify;
        }
        public function getIsNotifyCpcb(){
            return $this->isnotifycpcb;
        }
        
        public function setRuleType($type){
        	$this->ruleType = $type;
        }
        public function getRuleType(){
        	return $this->ruleType;
        }
        
        public function setVirtualChannelSeq($vChannelseq_){
             $this->virtualchannelseq = $vChannelseq_;
        }
        public function getVirtualChannelSeq(){
        	return $this->virtualchannelseq;
        }
        
        public function setLastSentSMSOn($sentSMSOn_){
        	$this->lastsentsmson = $sentSMSOn_;
        }
        public function getLastSentSMSOn(){
        	return $this->lastsentsmson;
        }
  }
?>
