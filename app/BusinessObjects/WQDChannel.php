<?php
Class WQDChannel{

      private $seq,$fileDataSeq,$channelnumber,$channelname,$channelvalue,$channelstatus;
    
        public function setFileDataSeq($fileDataSseq_){
            $this->fileDataSeq = $fileDataSseq_;
        }
        public function getFileDataSeq(){
            return $this->fileDataSeq;
        }
        public function setSeq($seq_){
            $this->seq = $seq_;
        }
        public function getSeq(){
            return $this->seq;
        }  
        
        public function setChannelNumber($number_){
            $this->channelnumber = $number_;
        }
        public function getChannelNumber(){
            return $this->channelnumber;
        } 
     
        public function setChannelName($name_){
            $this->channelname = $name_;
        }
        public function getChannelName(){
            return $this->channelname;
        } 
         
        public function setChannelValue($channelvalue_){
            $this->channelvalue = $channelvalue_;
        }
        public function getChannelValue(){
            return $this->channelvalue;
        }
     
        public function setChannelStatus($channelstatus_){
            $this->channelstatus = $channelstatus_;
        }
        public function getChannelStatus(){
            return $this->channelstatus;
        } 
}    
?>
