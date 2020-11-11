<?php
    Class WLM{
        private $seq,$time,$ws,$wd,$temp,$rh,$rf,$sol,$bp,$crc,$fileSeq;
        
        public function setSeq($seq_){
            $this->seq = $seq_;
        }
        public function getSeq(){
            return $this->seq;
        }
        
        public function setTime($time_){
            $this->time = $time_;
        }
        public function getTime(){
            return $this->time;
        }
        
        public function setWS($WS_){
            $this->ws = $ws_;
        }
        public function getWS(){
            return $this->ws;
        }
        
        public function setWD($WD_){
            $this->wd = $WD;
        }
        public function getWD(){
            return $this->wd;
        }
        
        public function setTemp($temp_){
            $this->temp = $temp_;
        }
        public function getTemp(){
            return $this->temp;
        }
        public function setRH($rh_){
            $this->rh = $rh_;
        }
        public function getRH(){
            return $this->rh;
        }
        public function setRF($rf_){
            $this->rf = $rf_;
        }
        public function getRF(){
            return $this->rf;
        }
        public function setSOL($sol_){
            $this->sol = $sol_;
        }
        public function getSOL(){
            return $this->sol;
        }
        
        public function setBP($bp_){
            $this->bp = $bp_;
        }
        public function getBP(){
            return $this->bp;
        }
        public function setCRC($CRC_){
            $this->crc = $CRC_;
        }
        public function getCRC(){
            return $this->crc;
        }
        public function setFileSeq($fileSeq_){
            $this->fileSeq = $fileSeq_;
        }
        public function getFileSeq(){
            return $this->fileSeq;
        }
    }
?>
