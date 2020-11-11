<?php
  
  class DPCC{
        
      private $dated;  
      private $seq, $co, $o3, $no, $no2, $nox,$nh3,$so2,$ben,$tol,$pxy,$pm25,$pm10,$at,$rh,$ws,$wd,$vws,$bp,$sr;
        
        public function setDated($dated_){
            $this->dated = $dated_;
        }
        public function getDated(){
            return $this->dated;
        }
        
        public function setSeq($seq_){
            $this->seq = $seq_;   
        }     
        public function getSeq(){
            return $this->seq;
        }

        public function setCO($co_){
            $this->co = $co_;   
        }     
        public function getCO(){
            return $this->co;
        }

        public function setO3($o3_){
            $this->o3 = $o3_;   
        }     
        public function getO3(){
            return $this->o3;
        }

        public function setNO($no_){
            $this->no = $no_;   
        }     
        public function getNO(){
            return $this->no;
        }

        public function setNO2($no2_){
            $this->no2 = $no2_;   
        }     
        public function getNO2(){
            return $this->no2;
        }

        public function setNOX($nox_){
            $this->nox = $nox_;   
        }     
        public function getNOX(){
            return $this->nox;
        }

        public function setNH3($nh3_){
            $this->nh3 = $nh3_;   
        }     
        public function getNH3(){
            return $this->nh3;
        }

        public function setSO2($so2_){
            $this->so2 = $so2_;   
        }     
        public function getSO2(){
            return $this->so2;
        }

        public function setBEN($ben_){
            $this->ben = $ben_;   
        }     
        public function getBEN(){
            return $this->ben;
        }

        public function setTOL($tol_){
            $this->tol = $tol_;   
        }     
        public function getTOL(){
            return $this->tol;
        }

        public function setPXY($pxy_){
            $this->pxy = $pxy_;   
        }     
        public function getPXY(){
            return $this->pxy;
        }

        public function setPM25($pm25_){
            $this->pm25 = $pm25_;   
        }     
        public function getPM25(){
            return $this->pm25;
        }

        public function setPM10($pm10_){
            $this->pm10 = $pm10_;   
        }     
        public function getPM10(){
            return $this->pm10;
        }

        public function setAT($at_){
            $this->at = $at_;   
        }     
        public function getAT(){
            return $this->at;
        }

        public function setRH($rh_){
            $this->rh = $rh_;   
        }     
        public function getRH(){
            return $this->rh;
        }

        public function setWS($ws_){
            $this->ws = $ws_;   
        }     
        public function getWS(){
            return $this->ws;
        }
        
        public function setWD($wd_){
            $this->wd = $wd_;   
        }     
        public function getWD(){
            return $this->wd;
        }

        public function setVWS($vws_){
            $this->vws = $vws_;   
        }     
        public function getVWS(){
            return $this->vws;
        }

        public function setBP($bp_){
            $this->bp = $bp_;   
        }     
        public function getBP(){
            return $this->bp;
        }

        public function setSR($sr_){
            $this->sr = $sr_;   
        }     
        public function getSR(){
            return $this->sr;
        }
}
?>
