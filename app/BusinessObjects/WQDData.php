<?php
Class WQDData{
     private  $seq,$fileSeq,$folderSeq,$date,$reportno,$totalchannels,$checksum ,$channels;
      
        public function setFileSeq($fileseq_){
            $this->fileSeq = $fileseq_;
        }
        public function getFileSeq(){
            return $this->fileSeq;
        }
        public function setFolderSeq($folderseq_){
            $this->folderSeq = $folderseq_;
        }
        public function getFolderSeq(){
            return $this->folderSeq;
        }
        
        public function setSeq($seq_){
            $this->seq = $seq_;
        }
        public function getSeq(){
            return $this->seq;
        }  
        
        public function setDataDate($date_){
            $this->date = $date_;
        }
        public function getDatadate(){
            return $this->date;
        } 
         
        public function setReportNo($reportno_){
            $this->reportno = $reportno_;
        }
        public function getReportNo(){
            return $this->reportno;
        } 
         
        public function setTotalChannels($totalChannels_){
            $this->totalchannels = $totalChannels_;
        }
        public function getTotalChannels(){
            return $this->totalchannels;
        }  
           public function setChecksum($checksum_){
            $this->checksum = $checksum_;
        }
        public function getChecksum(){
            return $this->checksum;
        } 
        
        public function setChannels(array $channels_){
             $this->channels = $channels_; 
        }
         public function getChannels(){
             return $this->channels; 
        } 
    

}
?>
