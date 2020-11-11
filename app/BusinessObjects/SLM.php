<?php
  class SLM{
      
        private $dated;
        private $seq,$leq,$min,$max,$l10,$l50,$l90,$sel,$crc,$fileSeq;
      
        public function setSeq($seq_){
            $this->seq = $seq_;
        }
        public function getSeq(){
            return $this->seq;
        }
        public function setDated($dated_){
            $this->dated = $dated_;
        }
        public function getDated(){
            return $this->dated;
        }
        
        public function setLEQ($leq_){
            $this->leq = $leq_;
        }
        public function getLEQ(){
            return $this->leq;
        }
      
        public function setMIN($min_){
            $this->min = $min_;
        }
        public function getMIN(){
            return $this->min;
        }
      
        public function setMAX($max_){
            $this->max = $max_;
        }
        public function getMAX(){
            return $this->max;
        }
      
        public function setL10($l10_){
            $this->l10 = $l10_;
        }
        public function getL10(){
            return $this->l10;
        }
      
        public function setL50($l50_){
            $this->l50 = $l50_;
        }
        public function getL50(){
            return $this->l50;
        }
      
        public function setL90($l90_){
            $this->l90 = $l90_;
        }
        public function getL90(){
            return $this->l90;
        }

        public function setSEL($sel_){
            $this->sel = $sel_;
        }
        public function getSEL(){
            return $this->sel;
        }

        public function setCRC($crc_){
            $this->crc = $crc_;
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
