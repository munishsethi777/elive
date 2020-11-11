<?php
  
  Class ExemptionDetail{

     private $seq,$exemptionSeq,$channelNumber,$channelName;
     
     
     public function setSeq($seq_){
        $this->seq = $seq_;
     }
     public function getSeq(){
        return $this->seq;
     }
     
     public function setExemptionSeq($eseq_){
        $this->exemptionSeq = $eseq_;
     }
     public function getExemptionSeq(){
        return $this->exemptionSeq;
     }
     
     public function setChannelNumber($chNo){
        $this->channelNumber = $chNo;
     }
     public function getChannelNumber(){
        return $this->channelNumber;
     }
     
     public function setChannelName($chName){
        $this->channelName = $chName;
     }
     public function getChannelName(){
        return $this->channelName;
     }
     
     
     
  }
?>
